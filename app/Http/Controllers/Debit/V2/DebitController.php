<?php

namespace App\Http\Controllers\Debit\V2;

use App\Commons\Helper;
use App\Exceptions\QueueRedis;
use App\Helpers\dBug;
use App\Http\Controllers\BuildingController;
use App\Http\Requests\ApartmentServicePrice\ApartmentServicePriceRequest;
use App\Http\Requests\Debit\EditDebitDetailRequest;
use App\Models\ApartmentGroups\ApartmentGroup;
use App\Models\Apartments\Apartments;
use App\Models\BdcApartmentServicePrice\ApartmentServicePrice;
use App\Models\BdcBills\Bills;
use App\Models\BdcDebitDetail\DebitDetail;
use App\Models\Building\Building;
use App\Models\Building\BuildingPlace;
use App\Models\Customers\Customers;
use App\Models\PublicUser\UserInfo;
use App\Models\Service\Service;
use App\Repositories\Apartments\ApartmentGroupRepository;
use App\Repositories\Apartments\ApartmentsRespository;
use App\Repositories\BdcApartmentDebit\ApartmentDebitRepository;
use App\Repositories\BdcApartmentServicePrice\ApartmentServicePriceRepository;
use App\Repositories\BdcBills\BillRepository;
use App\Repositories\BdcBuildingDebit\BuildingDebitRepository;
use App\Repositories\BdcDebitDetail\V2\DebitDetailRepository;
use App\Repositories\BdcV2DebitDetail\DebitDetailRepository as DebitDetailRepository2;
use App\Repositories\BdcV2DebitDetail\DebitDetailRepository as BdcV2DebitDetailDebitDetailRepository;
use App\Repositories\Building\BuildingPlaceRepository;
use App\Repositories\Building\BuildingRepository;
use App\Repositories\CronJobManager\CronJobManagerRepository;
use App\Repositories\Customers\CustomersRespository;
use App\Repositories\Service\ServiceRepository;
use App\Services\CronJobService;
use App\Services\FCM\SendNotifyFCMService;
use App\Services\SendTelegram;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Cache;
use Exception;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Redis;
use Maatwebsite\Excel\Facades\Excel;
use PHPExcel_Style_Fill;
use App\Services\ServiceSendMailV2;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\BdcAccountingVouchers\AccountingVouches;
use App\Models\BdcElectricMeter\ElectricMeter;
use App\Repositories\BdcCoin\BdcCoinRepository;
use App\Repositories\BdcV2LogCoinDetail\LogCoinDetailRepository;
use App\Repositories\Vehicles\VehiclesRespository;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\Auth;
use App\Models\CronJobManager\CronJobManager;
use App\Models\BdcPaymentDetails\PaymentDetail;
use App\Models\BdcReceipts\Receipts;
use App\Models\BdcV2DebitDetail\DebitDetail as BdcV2DebitDetailDebitDetail;
use App\Models\BdcV2LogCoinDetail\LogCoinDetail;
use App\Models\Campain;
use App\Models\Fcm\Fcm;
use App\Models\SentStatus;
use App\Models\VehicleCategory\VehicleCategory;
use App\Models\Vehicles\Vehicles;
use App\Repositories\BdcLockCyclename\BdcLockCyclenameRepository;
use App\Repositories\ElectricMeter\ElectricMeterRespository;
use App\Repositories\PublicUsers\PublicUsersProfileRespository;
use App\Util\Debug\LogAction;

class DebitController extends BuildingController
{
    public $debitRepo;
    public $serviceRepo;
    public $buildingRepo;
    public $apartmentServiceRepo;
    public $apartmentRepo;
    public $apartmentDebitRepo;
    public $buildingDebitRepo;
    public $billRepository;
    public $_buildingPlaceRepository;
    public $_customersRespository;
    public $_sendNotifyFCMService;
    public $modelApartmentGroup;
    private $_electricMeterRespository;

    use ApiResponse;

    public function __construct(
        Request $request,
        DebitDetailRepository $debitRepo,
        ServiceRepository $serviceRepo,
        BuildingRepository $buildingRepo,
        ApartmentServicePriceRepository $apartmentServiceRepo,
        ApartmentsRespository $apartmentRepo,
        ApartmentDebitRepository $apartmentDebitRepo,
        BuildingDebitRepository $buildingDebitRepo,
        BillRepository $billRepository,
        BuildingPlaceRepository $buildingPlaceRepository,
        CustomersRespository $customersRespository,
        SendNotifyFCMService $sendNotifyFCMService,
        ApartmentGroupRepository $modelApartmentGroup,
        ElectricMeterRespository $electricMeterRespository
    )
    {
        //$this->middleware('auth', ['except' => []]);
        //$this->middleware('route_permision');
        $this->debitRepo = $debitRepo;
        $this->serviceRepo = $serviceRepo;
        $this->buildingRepo = $buildingRepo;
        $this->apartmentServiceRepo = $apartmentServiceRepo;
        $this->apartmentRepo = $apartmentRepo;
        $this->apartmentDebitRepo = $apartmentDebitRepo;
        $this->buildingDebitRepo = $buildingDebitRepo;
        $this->billRepository = $billRepository;
        $this->_buildingPlaceRepository = $buildingPlaceRepository;
        $this->_customersRespository = $customersRespository;
        $this->_sendNotifyFCMService = $sendNotifyFCMService;
        $this->modelApartmentGroup = $modelApartmentGroup;
        $this->_electricMeterRespository = $electricMeterRespository;
        parent::__construct($request);
    }


    public function index(Request $request)
    {
        $data['meta_title'] = 'Quản lý công nợ';
        $data['per_page'] = Cookie::get('per_page', 10);
        $data['buildings'] = $this->buildingRepo->getBuildingOfUser($this->building_active_id);
        $data['paymentDeadlineBuilding'] = Carbon::now()->addDays(3)->toDateString();
        $data['debits'] = $this->apartmentDebitRepo->getAll($this->building_active_id, $data['per_page']);
        $apartmentService = $this->apartmentServiceRepo->getServiceApartment($this->building_active_id);
        $data['serviceBuildings'] = $this->serviceRepo->getServiceOfApartment2($apartmentService);
        // $data['dashboard'] = $this->billRepository->dashboardBill($this->building_active_id,$this->debitRepo);
        $data['filter'] = $request->all();
        if ($request['bdc_building_id']) {
            $data['apartments'] = $apartment = $this->apartmentRepo->getApartmentOfBuildingDebit($request['bdc_building_id']);
        }

        if ($request->all()) {
            $data['debits'] = $this->apartmentDebitRepo->filterDebit($request->all(), $data['per_page']);
        }

        return view('debit.v2.index', $data);
    }

    public function detail(Request $request)
    {
        $data['meta_title'] = 'Chi tiết công nợ';
        $month = ($request->month && $request->month <= 12 && $request->month > 0) ? $request->month : date('m');
        $data['buildings'] = $this->buildingRepo->getBuildingOfUser($this->building_active_id);
        if ($request->bdc_building_id) {
            $data['apartments'] = $this->apartmentRepo->getApartmentOfBuildingDebit($request->bdc_building_id);
        }
        $data['dashboard'] = $this->billRepository->dashboardBill($this->building_active_id,$this->debitRepo);
        $data['filter'] = $request->all();
        $buildingId = $request->bdc_building_id ? $request->bdc_building_id : $this->building_active_id;

        //can ho su dung dich vu
        $apartmentsUseService = $this->apartmentServiceRepo->findAllIdApartmentUseService($buildingId);
        if ($request->bdc_apartment_id != '') {
            if ($this->apartmentRepo->find($request->bdc_apartment_id)) {
                $apartmentsUseService = [$request->bdc_apartment_id];
            }
        }

        $apartmentService = $this->apartmentServiceRepo->getServiceApartment($buildingId);
        $data['serviceBuildingFilter'] = $this->serviceRepo->getServiceOfApartment($apartmentService, $buildingId);
        if ($request->bdc_service_id != '') {
            if (in_array($request->bdc_service_id, $apartmentService)) {
                $apartmentService = [$request->bdc_service_id];
            }
        }
        //dich vu dươc su dung boi can ho

        $data['serviceBuildings'] = $this->serviceRepo->getServiceOfApartment2($apartmentService);
        // Get the current page from the url if it's not set default to 1
        $page = $request->page ? $request->page : 1;

        // Number of items per page
        $perPage = Cookie::get('per_page_debit_detail', 10);
        $data['per_page'] = $perPage;

        // Start displaying items from this number;
        $offSet = ($page * $perPage) - $perPage; // Start displaying items from this number
        $debits = $this->debitRepo->findMaxVersionByMonth($buildingId, $apartmentsUseService, $apartmentService, $month);
        $itemsForCurrentPage = array_slice($debits['debits'], $offSet, $perPage, true);

        $data['debits'] = new LengthAwarePaginator($itemsForCurrentPage, count($debits['debits']), $perPage, $page, ['path' => route('admin.v2.debit.detail')]);
        $data['apartmentShow'] = $this->apartmentRepo->getApartmentById($apartmentsUseService);
        $data['apartmentsUseService'] = $debits['apartmentsUseService'];
        $data['paymentDeadlineBuilding'] = Carbon::now()->addDays(3)->toDateString();
        $data['month'] = $month;
        return view('debit.v2.detail', $data);
    }

    public function detailHandling(Request $request)
    {
        $cycleName = $request->cycle_year . $request->cycle_month;
        $action = Helper::getAction();
        $debugmess ='action: '.json_encode($action).'cyclename: '.json_encode($cycleName);
        if($action){
            $check_lock_cycle = BdcLockCyclenameRepository::checkLock($this->building_active_id,$cycleName,$action);
            if($check_lock_cycle){
                return redirect()->route('admin.v2.debit.debitLogs')->with('warning', "Kỳ $cycleName đã được khóa.");
            }
        }
        if($request->check_all_apartment == 'nhom_can_ho' && !isset($request->nhom_can_ho) || ($request->check_all_apartment == 'can_ho' && !isset($request->can_ho))) {
            return redirect()->route('admin.v2.debit.debitLogs')->with('warning', 'Hãy chọn tất cả căn hộ hoặc căn hộ để thiết lập tính công nợ.');
        }
        $debit = $this->debitRepo->handlingDebitDetail($request->all(), $this->building_active_id);
        $debugmess.= 'debit: '. json_encode($debit);
        if($debit)
        {
            return redirect()->route('admin.v2.debit.debitLogs')->with('success', 'Thiết lập công nợ thành công.');
        }
        return redirect()->route('admin.v2.debit.debitLogs')->with('error', 'Tiến trình xử lý công nợ đã được thiết lập.');
    }
    public function detailHandlingYear(Request $request)
    {
        $debit = $this->debitRepo->handlingDebitDetailYear($request->all(), $this->building_active_id);
        if($debit)
        {
            return redirect()->route('admin.v2.debit.debitLogs')->with('success', 'Thiết lập công nợ thành công.');
        }
        return redirect()->route('admin.v2.debit.debitLogs')->with('error', 'Tiến trình xử lý công nợ đã được thiết lập.');
    }

    public function getApartment(Request $request)
    {
        $apartment = $this->apartmentRepo->getApartmentOfBuildingDebit($request->id);
        return response()->json($apartment);
    }

    public function export()
    {

        try {
            return $debit = $this->apartmentDebitRepo->excelDebitIndex($this->building_active_id);
        }catch (Exception $e){
            return redirect()->back()->with('warning','đã có lỗi xảy ra.');
        }

    }

    public function exportFilter(Request $request) {

        try {
            return $debit = $this->apartmentDebitRepo->excelDebitFilter($this->building_active_id,$request->all());
        }catch (Exception $e){
            return redirect()->back()->with('warning','đã có lỗi xảy ra.');
        }
    }

    public function show($id, Request $request)
    {
        $data['meta_title'] = 'Chi tiết công nợ căn hộ';
        $data['per_page'] = Cookie::get('per_page', 10);
        // $data['dashboard'] = $this->billRepository->dashboardBill($this->building_active_id,$this->debitRepo);
        $data['debits'] = collect($this->debitRepo->findByBuildingApartmentId($this->building_active_id, $id));
        // dd($data['debits']);
        // $debits = collect($data['debits'])->pluck('id')->toArray();
        // $paid = collect();
        // $merge = collect($data['debits']);
        // foreach ($debits as $debit) {
        //     $debitInfo = $this->debitRepo->findDebitById($debit);
        //     $bills = $this->debitRepo->findMaxVersionPaid($debitInfo->bdc_bill_id);
        //     foreach ($bills as $bill) {
        //         $paid->push($bill->total_paid);
        //     }

        // }
        // // dd($paid);
        // foreach ($merge as $key => $de) {
        //     $de = (array)$de;
        //     $de[] = $paid[$key];
        //     $merge[$key] = (object)$de;
        // }
        // $merge = $merge->toArray();
        // $data['debits'] = $merge;
        // $data['filter'] = $request->all();
        // // Get the current page from the url if it's not set default to 1
        // $page = $request['page'];

        // // Number of items per page
        // $perPage = $data['per_page'];

        // // Start displaying items from this number;
        // $offSet = ($page * $perPage) - $perPage; // Start displaying items from this number
        // $basicQuery = $merge;
        // // Get only the items you need using array_slice (only get 10 items since that's what you need)
        // $itemsForCurrentPage = array_slice($basicQuery, $offSet, $perPage, true);

        // $data['pagination'] = new \Illuminate\Pagination\LengthAwarePaginator($itemsForCurrentPage, count($basicQuery), $perPage, $page);

        // if ($request->name) {
        //     $data['debits'] = $this->debitRepo->findService($id, $request->name, $data['per_page']);
        // }
        $data['apartment'] = $this->debitRepo->showDebitApartmentOne($id);
        $data['apartmentDebit'] = $this->apartmentRepo->findById($id);
        $data['apartmentRepository'] = $this->apartmentRepo;
        return view('debit.v2.show', $data);
    }

    public function exportDetailShowApartment($id)
    {
        return $this->debitRepo->excelDebitShowApartment($id);
    }

    public function processDebitDetail()
    {
        $data['meta_title'] = 'Tiến trình xử lý công nợ';
        // $data['dashboard'] = $this->billRepository->dashboardBill($this->building_active_id,$this->debitRepo);
        $data["data"] = CronJobService::get("debit_processing_$this->building_active_id");
        return view('debit.v2.process_debit_detail', $data);
    }

    public function reloadProcessDebitDetail()
    {
        $view = view("debit.v2._reload_process_debit_detail", [
            'data' => CronJobService::get("debit_processing_$this->building_active_id")
        ])->render();
        return $this->responseSuccess([
            'html' => $view
        ]);
    }

    public function action(Request $request)
    {
        if ($request->has('per_page')) {
            $per_page = $request->input('per_page', 10);
            Cookie::queue('per_page', $per_page, 60 * 24 * 30);
        }
        return redirect()->back();
    }

    public function detailDebit(Request $request)
    {
        $data['meta_title'] = 'Chi tiết công nợ';
        $buildingId = $this->building_active_id;
        $data['filter'] = $request->all();
        $data['per_page'] = Cookie::get('per_page', 10);
        if(isset($data['filter']['bdc_apartment_id'])){
            $data['get_apartment'] = $this->apartmentRepo->findById($data['filter']['bdc_apartment_id']);
        }
        if(isset($data['filter']['ip_place_id'])){
            $data['get_place_building'] = $this->_buildingPlaceRepository->findById($data['filter']['ip_place_id']);
        }

        $data['serviceBuildingFilter'] = $this->serviceRepo->getServiceOfApartment_v4($buildingId);

        $data['cycle_names'] = BdcV2DebitDetailDebitDetailRepository::getCycleName($buildingId);

        $data['debits'] = BdcV2DebitDetailDebitDetailRepository::getDebitByBuilding($request, $buildingId)->paginate($data['per_page']);

        $sum_total = BdcV2DebitDetailDebitDetailRepository::sumTotalDebitByBuilding($request, $buildingId);

        $data['sum_total'] = $sum_total;

        $data['filter']['cycle_name'] = $request->cycle_name ? array_filter($request->cycle_name): null;

        return view('debit.v2.detail_service', $data);
    }

    public function detailDebitActionRecord(Request $request)
    {
        $data['meta_title'] = 'Chi tiết công nợ';

        $buildingId = $this->building_active_id;

        // $data['dashboard'] = $this->billRepository->dashboardBill($buildingId,$this->debitRepo);
        $data['buildings'] = $this->buildingRepo->getBuildingOfUser($buildingId);
        $data['apartments'] = $this->apartmentRepo->getApartmentOfBuildingDebit($buildingId);
        $data['filter'] = $request->all();
        if(isset($data['filter']['bdc_apartment_id'])){
            $data['get_apartment'] = $this->apartmentRepo->findById($data['filter']['bdc_apartment_id']);
         }
         if(isset($data['filter']['ip_place_id'])){
             $data['get_place_building'] = $this->_buildingPlaceRepository->findById($data['filter']['ip_place_id']);
         }
        //can ho su dung dich vu
        $apartmentsUseService = $this->apartmentServiceRepo->findAllIdApartmentUseService($buildingId);
        if ($request->bdc_apartment_id != '') {
            if ($this->apartmentRepo->find($request->bdc_apartment_id)) {
                $apartmentsUseService = [$request->bdc_apartment_id];
            }
        }

        //dich vu dươc su dung boi can ho
        $apartmentService = $this->apartmentServiceRepo->getServiceApartment($buildingId);
        $data['serviceBuildingFilter'] = $this->serviceRepo->getServiceOfApartment($apartmentService, $buildingId);
        if ($request->bdc_service_id != '') {
            if (in_array($request->bdc_service_id, $apartmentService)) {
                $apartmentService = [$request->bdc_service_id];
            }
        }

        $data['serviceBuildings'] = $this->serviceRepo->getServiceOfApartment($apartmentService, $buildingId);
        // Get the current page from the url if it's not set default to 1
        $page = $request->page ? $request->page : 1;

        // Number of items per page
        $perPage = Cookie::get('per_page_debit_detail_service', 10);
        $data['per_page'] = $perPage;

        // Start displaying items from this number;
        $offSet = ($page * $perPage) - $perPage; // Start displaying items from this number
        $debit_details = $this->debitRepo->findMaxVersionByCurrentMonthVersionStatusNotConfirm($buildingId, $apartmentService, $apartmentsUseService, $request);
        $itemsForCurrentPage = array_slice($debit_details, $offSet, $perPage, true);
        $data['debits'] = new LengthAwarePaginator($itemsForCurrentPage, count($debit_details), $perPage, $page, ['path' => route('admin.v2.debit.detail_service_action')]);
        $data['apartmentShow'] = $this->apartmentRepo->getApartmentById($apartmentsUseService);
        $data['cycle_names'] = $this->debitRepo->getCycleName();
        $data['bills'] = $this->billRepository->getBill($buildingId);
        return view('debit.v2.detail_service_action', $data);
    }

    public function detailDebitAction(Request $request)   
    {
        $method = $request->input('method','');

        if ($method == 'del_debit') {
            if (!$request->ids) {
                return back()->with('warning', 'Chưa có công nợ nào được chọn!');
            }
            $debits = BdcV2DebitDetailDebitDetail::whereIn('id', $request->ids)->get();
            if ($debits) {
                foreach ($debits as $key => $value) {
                    $action = Helper::getAction();
                    if ($action) {
                        $check_lock_cycle = BdcLockCyclenameRepository::checkLock($this->building_active_id, $value->cycle_name, $action);
                        if ($check_lock_cycle) {
                            return redirect()->back()->with(['warning' => "Kỳ $value->cycle_name đã được khóa"]);
                        }
                    }
                    if ($value->paid > 0) {
                        return redirect()->back()->with('warning', 'dịch vụ này đang được thanh toán!');
                    }
                    $checkFromDate = BdcV2DebitDetailDebitDetailRepository::findServiceCheckFromDate($value->bdc_apartment_id, $value->bdc_apartment_service_price_id, $value->to_date);
                    if ($checkFromDate) {
                        return redirect()->back()->with('warning', "Không thể xóa do đã phát sinh dịch vụ có ngày bắt đầu lớn hơn $value->to_date.");
                    }
                    $apartmentServicePrice  = @$value->apartmentServicePrice;
                    if ($apartmentServicePrice) {
                        $apartmentServicePrice->last_time_pay = date('Y-m-d', strtotime($value->from_date));
                        $apartmentServicePrice->save();
                    }

                    $value->sumery = 0;
                    $value->bdc_bill_id = 0;
                    $value->discount = 0;
                    $value->quantity = 0;
                    $value->paid = 0;
                    $value->price = 0;
                    $value->save();
                    $value->delete();
                    $diennuoc = json_decode(@$value->detail);
                    if(@$diennuoc->data_detail){
                        @$data_detail = @$diennuoc->data_detail;
                        foreach ($data_detail as $key_1 => $value_1) {
                          $electric =  ElectricMeter::find($value_1->id);
                          if($electric)$electric->update(['status'=>0]);
                        }
                    }
                    QueueRedis::setItemForQueue('add_queue_stat_payment_', [
                        "apartmentId" => $value->bdc_apartment_id,
                        "service_price_id" => $value->bdc_apartment_service_price_id,
                        "cycle_name" => $value->cycle_name,
                    ]);
                    $count_bill =  BdcV2DebitDetailDebitDetail::where('bdc_bill_id', $value->bdc_bill_id)->count();
                    if ($count_bill == 0) {
                        Bills::destroy($value->bdc_bill_id);
                    }
                }
            }
            return back()->with('success', 'Xóa thành công!' );
        }else if( $request->has('per_page')) {
            $per_page = $request->input('per_page', 10);
            Cookie::queue('per_page', $per_page, 60 * 24 * 30);
            return back()->with('success', 'chuyển trạng thái thành công!' );
        }
        return back();
    }
    public function generalDetailDebitAction(Request $request)
    {
        if ($request->has('per_page')) {
            $per_page = $request->input('per_page', 10);
            Cookie::queue('per_page', $per_page, 60 * 24 * 30);
        }
        return back();
    }
    public function totalDebitAction(Request $request)
    {
        if ($request->has('per_page')) {
            $per_page = $request->input('per_page', 10);
            Cookie::queue('per_page', $per_page, 60 * 24 * 30);
            return back();
        }
        $_add_queue_stat_payment=null;
        if($request->has('ngay_hach_toan') && $request->has('log_coin_b_id') && $request->has('log_coin_a_id')){


            $logcoin_b =  LogCoinDetail::find($request->log_coin_b_id);

            $cycle = $logcoin_b->cycle_name;
            $action = Helper::getAction();
            if ($action) {
                $check_lock_cycle = BdcLockCyclenameRepository::checkLock($logcoin_b->bdc_building_id, $cycle, $action);
                if ($check_lock_cycle) {
                    return response()->json(['success' =>false,'mess'=>"Kỳ $cycle đã bị khóa"], 200);
                }
            }
            $cycle = \Illuminate\Support\Carbon::parse($request->ngay_hach_toan)->format('Ym');
            $action = Helper::getAction();
            if ($action) {
                $check_lock_cycle = BdcLockCyclenameRepository::checkLock($this->building_active_id, $cycle, $action);
                if ($check_lock_cycle) {
                    return response()->json(['success' =>false,'mess'=>"Kỳ $cycle đã bị khóa"], 200);
                }
            }
             if($logcoin_b){
                $_add_queue_stat_payment[] = [
                    "apartmentId" => $logcoin_b->bdc_apartment_id,
                    "service_price_id" => $logcoin_b->bdc_apartment_service_price_id,
                    "cycle_name" =>  $logcoin_b->cycle_name
                ]; 
                $_add_queue_stat_payment[] = [
                    "apartmentId" => $logcoin_b->bdc_apartment_id,
                    "service_price_id" => $logcoin_b->bdc_apartment_service_price_id,
                    "cycle_name" =>  Carbon::parse($request->ngay_hach_toan)->format('Ym')
                ];    
                $logcoin_b->update([ 'cycle_name' => Carbon::parse($request->ngay_hach_toan)->format('Ym')]);

             }
             $logcoin_a =  LogCoinDetail::find($request->log_coin_a_id);
             if($logcoin_a){
                $_add_queue_stat_payment[] = [
                    "apartmentId" => $logcoin_a->bdc_apartment_id,
                    "service_price_id" => $logcoin_a->bdc_apartment_service_price_id,
                    "cycle_name" =>  $logcoin_a->cycle_name
                ]; 
                $_add_queue_stat_payment[] = [
                    "apartmentId" => $logcoin_a->bdc_apartment_id,
                    "service_price_id" => $logcoin_a->bdc_apartment_service_price_id,
                    "cycle_name" =>  Carbon::parse($request->ngay_hach_toan)->format('Ym')
                ];    
                $logcoin_a->update([ 'cycle_name' => Carbon::parse($request->ngay_hach_toan)->format('Ym')]);

             }
             if($_add_queue_stat_payment && count($_add_queue_stat_payment) > 0){
                foreach ($_add_queue_stat_payment as $key => $value) {
                   QueueRedis::setItemForQueue('add_queue_stat_payment_', $value);
                }
            }
             return response()->json(['success' =>true], 200);
        }else{
            return response()->json(['success' =>false], 402);
        }
       
    }
    public function ActionRecordDebit(Request $request)
    {
        if ($request->has('per_page')) {
            $per_page = $request->input('per_page', 10);
            Cookie::queue('per_page_debit_detail_service', $per_page, 60 * 24 * 30);
        }
        return $this->debitRepo->action($request);
    }

    public function exportExcel(Request $request)
    {
        try {
            set_time_limit(0);
            $debits = BdcV2DebitDetailDebitDetailRepository::getDebitByBuilding($request, $this->building_active_id)->get();
            $result = Excel::create('Hóa Đơn Tổng Hợp', function ($excel) use ($debits) {
                $excel->setTitle('Hóa Đơn Tổng Hợp');
                $excel->sheet('Hóa Đơn Tổng Hợp', function ($sheet) use ($debits) {
                    // $bills = [];
                    $row = 1;
                    $sheet->row($row, [
                        'STT',
                        'Mã BK',
                        'Kỳ BK',
                        'Căn hộ',
                        'Mã Căn hộ',
                        'Dịch vụ',
                        'Sản phẩm',
                        'Mã Thu',
                        'Đơn giá',
                        'SL',
                        'Thành tiền',
                        'Giảm trừ',
                        'Đã thu',
                        'Còn nợ',
                        'Ngày chốt',
                        'Ngày lập',
                        'Ngày duyệt',
                        'Thời gian',
                        'Trước thuế',
                        'Thuế VAT 10%',
                        'Trước thuế tiền nước',
                        'Phí môi trường',
                        'Tổng trước thuế',
                        'Thuế VAT 5%',
                        'Diện tích',
                        'CSĐK',
                        'CSCK',
                        'Loại xe',
                        'Biển số'
                    ]);
                    foreach ($debits as $key => $debit) {
                        $row++;
                        $apartmentServicePrice = ApartmentServicePrice::get_detail_bdc_apartment_service_price_by_apartment_id($debit->bdc_apartment_service_price_id);
                        $bill = Bills::get_detail_bill_by_apartment_id($debit->bdc_bill_id);
                        $apartment = Apartments::get_detail_apartment_by_apartment_id($debit->bdc_apartment_id);
                        $service =$apartmentServicePrice ? Service::get_detail_bdc_service_by_bdc_service_id($apartmentServicePrice->bdc_service_id) : null;

                        $vehicle =$apartmentServicePrice ?  Vehicles::get_detail_vehicle_by_id($apartmentServicePrice->bdc_vehicle_id) : null;
                        $vehicle_category = $vehicle ?  VehicleCategory::get_detail_vehicles_category_by_id($vehicle->vehicle_category_id) : null;
                        $data = [
                            (string)$debit->id,
                            $bill->bill_code,
                            $debit->cycle_name,
                            @$apartment->name,
                            @$apartment->code,
                            @$service->name,
                            $vehicle ? @$vehicle->number : @$apartmentServicePrice->name,
                            $service->code_receipt,
                            (string)$debit->price,
                            (string)$debit->quantity,
                            (string)$debit->sumery,
                            $debit->discount,
                            (string)$debit->paid,
                            (string)$debit->sumery - $debit->paid,
                            (string)@$bill->bill_date,
                            (string)date('d/m/Y', strtotime(@$debit->created_at)),
                            (string)date('d/m/Y', strtotime(@$bill->deadline)),
                            (string)date('d/m/Y', strtotime(@$debit->from_date)) . ' - ' . date('d/m/Y', strtotime($debit->to_date)),
                            null,
                            null,
                            null,
                            null,
                            null,
                            null,
                            $apartment->area,
                            null,
                            null,
                            $vehicle_category ? @$vehicle_category->name : null,
                            $vehicle ? @$vehicle->number : null
                        ];
                        $sheet->row($row, $data);

                    }
                });
            })->store('xlsx',storage_path('exports/'));
            ob_end_clean();
            $file     = storage_path('exports/'.$result->filename.'.'.$result->ext);
            return response()->download($file)->deleteFileAfterSend(true);
        }catch (Exception $e){
            return redirect()->back()->with('warning','đã có lỗi xảy ra.');
        }

             
    }

    public function exportExcel_v2(Request $request,ServiceRepository $ServiceRepository, ApartmentsRespository $apartmentsRespository,VehiclesRespository $vehiclesRespository)
    {
        try {
            set_time_limit(0);
            $building = Building::find($this->building_active_id);

            if($request->type_service != null){
                if($request->type_service == ServiceRepository::DIEN){

                    return $this->debitRepo->exportDien($this->building_active_id, $request, $building);

                }
                if( $request->type_service == ServiceRepository::NUOC){

                    return $this->debitRepo->exportNuoc($this->building_active_id, $request, $building);

                }
                if($request->type_service == ServiceRepository::DICHVU){

                    return $this->debitRepo->exportSan($this->building_active_id, $request, $building);

                }
                if($request->type_service == ServiceRepository::PHUONG_TIEN){

                    return $this->debitRepo->exportPhuongtien($this->building_active_id, $request, $building);

                }
                if($request->type_service == ServiceRepository::PHI_KHAC){

                    return $this->debitRepo->exportPhiKhac($this->building_active_id, $request, $building);

                }
            }
            return $this->debitRepo->export_v2($this->building_active_id, $request, $building);
        }catch (Exception $e){
            return redirect()->back()->with('warning','đã có lỗi xảy ra.');
        }

        
}

    public function detailDebitEdit(Request $request)
    {
        $billDetail = BdcV2DebitDetailDebitDetail::find($request->id);
        return view('debit.v2.modal.detail_service', compact('billDetail'));
    }

     public function detailDebitEditVersion(Request $request)
    {
         $data['meta_title'] = 'Chi tiết version công nợ';

         $data['billDetail']  = $this->debitRepo->filterBillIdApartment($request->bdc_bill_id,$request->bdc_apartment_id);

         return view('debit.v2.modal.detail_service_version', $data);
    }

    public function destroydebitDetail($id)
    {
        try 
        {
            $debit = BdcV2DebitDetailDebitDetail::find($id);
            $action = Helper::getAction();
            if($action){
                $check_lock_cycle = BdcLockCyclenameRepository::checkLock($this->building_active_id,$debit->cycle_name,$action);
                if($check_lock_cycle){
                    return redirect()->back()->with(['warning' => "Kỳ $debit->cycle_name đã được khóa"]);
                }
            }
            if($debit->paid > 0){
                return redirect()->back()->with('warning', 'dịch vụ này đang được thanh toán!');
            }
            $checkFromDate = BdcV2DebitDetailDebitDetailRepository::findServiceCheckFromDate($debit->bdc_apartment_id, $debit->bdc_apartment_service_price_id, $debit->to_date);
            if($checkFromDate) {
                return redirect()->back()->with('warning', "Không thể xóa do đã phát sinh dịch vụ có ngày bắt đầu lớn hơn $debit->to_date.");
            }
            $apartmentServicePrice  = @$debit->apartmentServicePrice;
            if($apartmentServicePrice){
                $apartmentServicePrice->last_time_pay = date('Y-m-d', strtotime($debit->from_date));
                $apartmentServicePrice->save();
            }
    
            $debit->bdc_bill_id = 0;
            $debit->sumery = 0;
            $debit->quantity = 0;
            $debit->paid = 0;
            $debit->price = 0;
            $debit->save();
            $diennuoc = json_decode(@$debit->detail);
            if(@$diennuoc->data_detail){
                @$data_detail = @$diennuoc->data_detail;
                foreach ($data_detail as $key_1 => $value_1) {
                  $electric =  ElectricMeter::find($value_1->id);
                  if($electric)$electric->update(['status'=>0]);
                }
            }
            $debit->delete();
            QueueRedis::setItemForQueue('add_queue_stat_payment_', [
                "apartmentId" => $debit->bdc_apartment_id,
                "service_price_id" => $debit->bdc_apartment_service_price_id,
                "cycle_name" => $debit->cycle_name,
            ]);
            $count_bill =  BdcV2DebitDetailDebitDetail::where('bdc_bill_id',$debit->bdc_bill_id)->count();
            if($count_bill == 0){
                Bills::destroy($debit->bdc_bill_id);
            }
            return redirect()->back()->with(['success' => 'Xóa chi tiết bảng kê thành công!']);
        } catch (Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
    public function destroydebitDetailV2($id)
    {
        try 
        {
            $debit = $this->debitRepo->findDebitById($id);
            
            $this->debitRepo->deleteByBillIdV2($this->building_active_id, $debit->bdc_apartment_id, $debit->bdc_service_id, $debit->bdc_bill_id);

            return redirect()->back()->with(['success' => 'Xóa chi tiết bảng kê thành công!']);
        } catch (Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
        
    }

    public function detailDebitUpdate(EditDebitDetailRequest $request, BillRepository $billRepository, DebitDetailRepository $debitDetailRepository)
    {
        try {
            DB::beginTransaction();
            $debitDetail = BdcV2DebitDetailDebitDetail::find($request->id);
            $action = Helper::getAction();
            if($action){
                $check_lock_cycle = BdcLockCyclenameRepository::checkLock($this->building_active_id,$debitDetail->cycle_name,$action);
                if($check_lock_cycle){
                    $responseData = [
                        'success' => true,
                        'message' => "Kỳ $debitDetail->cycle_name đã được khóa."
                    ];
                    return response()->json($responseData);
                }
            }
            if(Auth::user()->isadmin == 0){
                // $cycle_name = BdcV2DebitDetailDebitDetail::where(['bdc_building_id' => $debitDetail->bdc_building_id, 'bdc_apartment_id' => $debitDetail->bdc_apartment_id, 'bdc_apartment_service_price_id' => $debitDetail->bdc_apartment_service_price_id])->where('bdc_bill_id', '>', 0)->max('cycle_name');
                // if($cycle_name && ($debitDetail->cycle_name != $cycle_name || $debitDetail->paid > 0 || $debitDetail->price == 0)){
                //     $responseData = [
                //         'success' => true,
                //         'message' => 'Chỉ được sửa công nợ mới nhất và không phải phí điện nước!'.$cycle_name
                //     ];
                //     return response()->json($responseData);
                // }
                $check_to_date = BdcV2DebitDetailDebitDetail::where(['bdc_building_id'=>$debitDetail->bdc_building_id,'bdc_apartment_id'=>$debitDetail->bdc_apartment_id,'bdc_apartment_service_price_id' => $debitDetail->bdc_apartment_service_price_id])->where('cycle_name','<>',$debitDetail->cycle_name)->where('to_date','>',$request->from_date)->first();
                if($check_to_date){
                    $responseData = [
                        'success' => true,
                        'message' => 'Thời gian bắt đầu không được nằm trong khoảng của kỳ trước.'
                    ];
                    return response()->json($responseData);
                }
            }
            BdcV2DebitDetailDebitDetailRepository::updateDebitRestore($request->id, false, $request->from_date,$request->to_date,false,str_replace(',', '', $request->previous_owed),$request->cycle_name,false,false,isset($request->sumery) ? str_replace(',', '',$request->sumery): false,isset($request->discount) ? str_replace(',', '',$request->discount): false,isset($request->discount) ? 0 : false,false,false,false,@$request->discount_note,$request->note);
           
            $bill = $billRepository->findBillById($debitDetail->bdc_bill_id);
            $bill->update(['status' => BillRepository::WAIT_FOR_CONFIRM,'deadline'=>  $request->deadline ? Carbon::parse($request->deadline) : $bill->deadline]);
            $debitdetal = BdcV2DebitDetailDebitDetail::where('bdc_bill_id',$debitDetail->bdc_bill_id)->get();
            ApartmentServicePrice::find($debitDetail->bdc_apartment_service_price_id)->update(['last_time_pay'=>$request->to_date]);
            DB::commit();
            if($debitdetal){
                foreach ($debitdetal as $key => $value) {
                    QueueRedis::setItemForQueue('add_queue_stat_payment_', [
                        "apartmentId" => $value->bdc_apartment_id,
                        "service_price_id" => $value->bdc_apartment_service_price_id,
                        "cycle_name" => $value->cycle_name,
                    ]);
                }
            }
            $responseData = [
                'success' => true,
                'message' => 'Cập nhật thành công!'
            ];
    
            return response()->json($responseData);
        } catch (Exception $e) {
            DB::rollBack();
            $responseData = [
                'success' => true,
                'message' => $e->getMessage()
            ];
    
            return response()->json($responseData);
        }
       
    }

    public function debitLogs(Request $request)
    {
        $data['meta_title'] = 'Quản lý công nợ';
        $data['per_page'] = Cookie::get('per_page', 10);
        $data['buildings'] = $this->buildingRepo->getBuildingOfUser($this->building_active_id);
        $data['paymentDeadlineBuilding'] = Carbon::now()->addDays(3)->toDateString();
        $apartmentService = $this->apartmentServiceRepo->getServiceApartment($this->building_active_id);
        $data['apartmentGroup'] = $this->modelApartmentGroup->searchByV2(['select'=>['id','name']],$this->building_active_id);
        $data['serviceBuildings_cycle_month'] = $this->serviceRepo->getServiceOfApartment3($apartmentService)->where('bdc_period_id',1)->get();// chu kỳ tháng
        $data['serviceBuildings_cycle_year'] = $this->serviceRepo->getServiceOfApartment3($apartmentService)->where('bdc_period_id',6)->get();// chu kỳ năm
        $data['apartments'] = $this->apartmentRepo->getApartmentOfBuildingDebit($this->building_active_id);
        $data['cycle_names_electric'] = $this->_electricMeterRespository->getCycleNameAll($this->building_active_id,0);
        $data['cycle_names_meter'] = $this->_electricMeterRespository->getCycleNameAll($this->building_active_id,1);
        $data['cycle_names_meter_hot'] = $this->_electricMeterRespository->getCycleNameAll($this->building_active_id,2);


        //dd($data['cycle_names'] );

        return view('debit.v2.debit_logs', $data);
    }

    public function total_tienthua(Request $request)
    {
        $data['meta_title'] = 'Quản lý tiền thừa';
        $data['per_page'] = Cookie::get('per_page', 10);
        $data['filter'] = $request->all();
        if(isset($data['filter']['bdc_apartment_id'])){
        $data['get_apartment'] = $this->apartmentRepo->findById($data['filter']['bdc_apartment_id']);
        }
        if(isset($data['filter']['ip_place_id'])){
            $data['get_place_building'] = $this->_buildingPlaceRepository->findById($data['filter']['ip_place_id']);
        }
        $data['serviceBuildingFilter'] = $this->serviceRepo->getServiceOfApartment_v2($this->building_active_id);

        $cycle_names = $this->debitRepo->getCycleNameV2($this->building_active_id);
        $data['cycle_names'] = $cycle_names;
        $data['chose_cycle_name'] = $request->cycle_name ? $request->cycle_name : '';

        $data['total_tienthua'] = BdcCoinRepository::getCoinByBuilding($request,$this->building_active_id)->paginate($data['per_page']);
        $data['sum_all'] = BdcCoinRepository::getCoinTotalByBuilding($this->building_active_id,$request);

        return view('debit.v2.total_tienthua', $data);
    }

    public function export_total_excess_cash(Request $request)
    {
        try {
            $total_tienthua = BdcCoinRepository::getCoinByBuilding($request,$this->building_active_id)->get();

            $result = Excel::create('tien_thua_can_ho', function ($excel) use ($total_tienthua) {
                $excel->setTitle('danh sách');
                $excel->sheet('danh sách', function ($sheet) use ($total_tienthua) {
                    $contents = [];
                    $apartmentId = null;
                    foreach ($total_tienthua as $key => $value) {

                        $_customer = CustomersRespository::findApartmentIdV3($value->bdc_apartment_id, 0);
                        $pubUserProfile = @$_customer->pubUserProfile;
                        if ($apartmentId != $value->bdc_apartment_id){
                            $apartmentId = $value->bdc_apartment_id;
                            $sum_total = BdcCoinRepository::getCoinTotal($value->bdc_apartment_id);
                            $contents[] = [
                                'STT'=> ($key +1),
                                'Căn hộ'=> @$value->apartment->name ,
                                'Mã căn hộ'=> @$value->apartment->code,
                                'Tòa'=> @$value->apartment->buildingPlace->name,
                                'Khách hàng'=> @$pubUserProfile->display_name,
                                'Tổng'=>  (int)@$sum_total ,
                                'Dịch vụ'=> $value->bdc_apartment_service_price_id != 0 ? @$value->apartmentServicePrice->service->name .' - '. @$value->apartmentServicePrice->vehicle->number : 'Chưa chỉ định' ,
                                'Tiền thừa hiện tại'=> (int)@$value->coin,
                            ];
                        }else{
                            $contents[] = [
                                'STT'=> ($key +1),
                                'Căn hộ'=> '',
                                'Mã căn hộ'=> '',
                                'Tòa'=> '',
                                'Khách hàng'=> '',
                                'Tổng'=> '',
                                'Dịch vụ'=> $value->bdc_apartment_service_price_id != 0 ? @$value->apartmentServicePrice->service->name .' - '. @$value->apartmentServicePrice->vehicle->number : 'Chưa chỉ định' ,
                                'Tiền thừa hiện tại'=> (int)@$value->coin,
                            ];
                        }

                    }
                    $sheet->setAutoSize(true);

                    // data of excel
                    if ($contents) {
                        $sheet->fromArray($contents);
                    }
                    // add header
                });
            })->store('xlsx',storage_path('exports/'));
            $file     = storage_path('exports/'.$result->filename.'.'.$result->ext);
            return response()->download($file)->deleteFileAfterSend(true);
        }catch (Exception $e){
            return redirect()->back()->with('warning','đã có lỗi xảy ra.');
        }

             
    }
    public function show_tienthua(Request $request)
    {
        $coin_apartment = BdcCoinRepository::getCoinByApartment($request->apartment_id);
      
        $apartment = Apartments::get_detail_apartment_by_apartment_id($request->apartment_id);

        $dich_vu_chi_dinh = null;  

        foreach($coin_apartment as $value){

            $apart_service = $value->bdc_apartment_service_price_id != 0 ? ApartmentServicePrice::find($value->bdc_apartment_service_price_id) : null;

            $dich_vu_chi_dinh[] = [
                'value' => $value->bdc_apartment_service_price_id,
                'name' => $value->bdc_apartment_service_price_id == 0 ? 'Chưa chỉ định' : (@$apart_service->bdc_vehicle_id > 0 ? @$apart_service->vehicle->number : @$apart_service->service->name)
            ];
        }
        $apartment_service = $this->apartmentServiceRepo->findByApartment_v3($request->apartment_id);  
        $den_chi_dinh = null;  

        foreach($apartment_service as $value){

            $den_chi_dinh[] = [
                'value' => $value->id,
                'name' => @$value->bdc_vehicle_id > 0 ? @$value->vehicle->number : @$value->service->name
            ];
        }
        $den_chi_dinh[] = [
            'value' => 0,
            'name' => 'Chưa chỉ định'
        ];
        if($coin_apartment){
            $view = view("debit.v2.modal._chi_tiet_tienthua", [
                'coin_apartment' => $coin_apartment,
                'apartment' => $apartment,
            ])->render();
            return $this->sendResponse([ 
                'html' => $view,
                'dich_vu_chi_dinh'=>$dich_vu_chi_dinh,
                'den_chi_dinh'=> $den_chi_dinh
            ],'Thành công.');
        }
        return $this->sendErrorResponse('Thất bại.');
    }

    public function save_phanbo(Request $request)
    {
           try {
                $_add_queue_stat_payment = null;
                DB::beginTransaction();
                 
                    if($request->apartmentId && $request->form_list_phan_bo){
                        // lấy thông tin chủ hộ
                        $_customer = CustomersRespository::findApartmentIdV2($request->apartmentId, 0);
                        $form_list_phan_bo = json_decode($request->form_list_phan_bo);
                        foreach ($form_list_phan_bo as $key => $value) {
                          
                            if($value->tu_chi_dinh == $value->den_chi_dinh){
                                DB::rollBack();
                                return $this->sendErrorResponse('Nguồn từ chỉ định không được trùng đến chỉ định');
                            }
                            $check_coin = BdcCoinRepository::getCoin($request->apartmentId,$value->tu_chi_dinh);
                            $so_tien = str_replace(',', '', $value->so_tien);
                            if($so_tien == 0){
                                DB::rollBack();
                                return $this->sendErrorResponse('Chưa nhập số tiền cần phân bổ');
                            }
                            if($check_coin->coin < $so_tien ){
                                DB::rollBack();
                                $apart_service = $value->tu_chi_dinh != 0 ? ApartmentServicePrice::find($value->tu_chi_dinh) : null;
                                return $this->sendErrorResponse($apart_service ? @$apart_service->service->name .' không đủ để phân bổ' : 'Không đủ tiền để phân bổ');
                            }
                                $cycle_name =  $value->ngay_hach_toan ? Carbon::parse($value->ngay_hach_toan)->format('Ym') : Carbon::now()->format('Ym');
                               // update giam coin từ chỉ định
                                $_coin = BdcCoinRepository::subCoin($this->building_active_id,$request->apartmentId,$value->tu_chi_dinh, $cycle_name,$_customer->user_info_id,$so_tien,Auth::user()->id,3,$value->den_chi_dinh);
                               // update tăng coin đến chỉ định
                                 BdcCoinRepository::addCoin($this->building_active_id,$request->apartmentId,$value->den_chi_dinh,$cycle_name,$_customer->user_info_id,$so_tien,Auth::user()->id,3,$value->tu_chi_dinh,@$_coin['log']);
                                $_add_queue_stat_payment[] = [
                                    "apartmentId" => $request->apartmentId,
                                    "service_price_id" => $value->tu_chi_dinh,
                                    "cycle_name" => $cycle_name
                                ];
                                $_add_queue_stat_payment[] = [
                                    "apartmentId" => $request->apartmentId,
                                    "service_price_id" => $value->den_chi_dinh,
                                    "cycle_name" =>  $cycle_name
                                ];       
                        }
                        DB::commit();
                        if($_add_queue_stat_payment && count($_add_queue_stat_payment) > 0){
                            foreach ($_add_queue_stat_payment as $key => $value) {
                               QueueRedis::setItemForQueue('add_queue_stat_payment_', $value);
                            }
                        }
                        // return
                        return $this->sendResponse([],'Phân bổ tiền thành công.');
                    }
           } catch (Exception $e) {
                DB::rollBack();
                //throw new \Exception("Đã có lỗi xẩy ra khi tạo mới cư dân.( 1402 ) ".$e->getMessage(), 1);
                return $this->sendErrorResponse($e->getMessage());
           }
    }

    public function detail_tienthua(Request $request,DebitDetailRepository2 $debitRepo)
    {
        $data['meta_title'] = 'Quản lý tiền thừa';
        $data['per_page'] = Cookie::get('per_page', 10);
        $data['filter'] = $request->all();

        if(isset($data['filter']['bdc_apartment_id'])){
        $data['get_apartment'] = $this->apartmentRepo->findById($data['filter']['bdc_apartment_id']);
        }
        if(isset($data['filter']['ip_place_id'])){
            $data['get_place_building'] = $this->_buildingPlaceRepository->findById($data['filter']['ip_place_id']);
        }

        $cycle_names =   $debitRepo->getCycleNameV2($this->building_active_id);
        
        $data['cycle_names'] = $cycle_names;

        $data['serviceBuildingFilter'] = $this->serviceRepo->getServiceOfApartment_v2($this->building_active_id);
    
        $data['chose_cycle_name'] = $request->cycle_name ? $request->cycle_name : '';
        
        $data['detail_tienthua'] = LogCoinDetailRepository::getLogCoinByBuilding($request,$this->building_active_id)->paginate($data['per_page']);

        return view('debit.v2.detail_tienthua', $data);
    }

    public function export_excess_cash(Request $request){

        try {
            $detail_tienthua = LogCoinDetailRepository::getLogCoinByBuilding($request,$this->building_active_id)->get();


            $result = Excel::create('chi_tiet_tien_thua', function ($excel) use ($detail_tienthua) {
                $excel->setTitle('danh sách');
                $excel->sheet('danh sách', function ($sheet) use ($detail_tienthua) {
                    $contents = [];
                    foreach ($detail_tienthua as $key => $value) {
                        $_customer = CustomersRespository::findApartmentIdV3($value->bdc_apartment_id, 0);
                        $pubUserProfile = @$_customer->pubUserProfile;
                        if($value->from_type == 4){
                            $_receipt = Receipts::get_detail_receipt_by_receipt_id($value->note);
                        }
                        $code_receipt = null;
                        $description = null;
                        if ($value->from_type == 1 || $value->from_type == 6){
                            $code_receipt = @$value->receipt_trashed->receipt_code;
                            $description = @$value->receipt_trashed->description;
                        }
                        else if($value->from_type == 2){
                            $code_receipt = 'Hạch toán tự động';
                            $description = 'Hạch toán tự động';
                        }
                        else if($value->from_type == 5){
                            $code_receipt = @$value->receipt_trashed->receipt_code;
                            $description = 'Huỷ phiếu thu_'.@$value->receipt_trashed->description;
                        }
                        else if($value->from_type == 4) {
                            $code_receipt = @$_receipt->receipt_code;
                            $description = @$value->receipt_trashed->description;
                        }else
                            $code_receipt =  'Phân bổ';
                        $description = @$value->note;

                        $contents[] = [
                            'STT'  =>  ($key + 1),
                            'Ngày hạch toán'  =>  date('d/m/Y', strtotime(@$value->created_at)),
                            'Mã chứng từ'  =>  $code_receipt,
                            'Khách hàng'  =>  @$pubUserProfile->display_name,
                            'Căn hộ'  =>  @$value->apartment->name,
                            'Kỳ'  =>   @$value->cycle_name ,
                            'Dịch vụ'  =>  @$value->bdc_apartment_service_price_id != 0 ? @$value->apartmentServicePrice->service->name .' - '. @$value->apartmentServicePrice->vehicle->number : 'Chưa chỉ định',
                            'Diễn giải'  =>  $description,
                            'Tăng trong kỳ'  =>  @$value->type == 1 ? (int)@$value->coin : '',
                            'Giảm trong kỳ'  =>  @$value->type == 0 ? (int)@$value->coin : ''
                        ];
                    }
                    $sheet->setAutoSize(true);

                    // data of excel
                    if ($contents) {
                        $sheet->fromArray($contents);
                    }
                    // add header
                });
            })->store('xlsx',storage_path('exports/'));
            $file     = storage_path('exports/'.$result->filename.'.'.$result->ext);
            return response()->download($file)->deleteFileAfterSend(true);
        }catch (Exception $e){
            return redirect()->back()->with('warning','đã có lỗi xảy ra.');
        }

    }

    public function total(Request $request, CustomersRespository $customersRespository, DebitDetailRepository2 $debitRepo)
    {
        $data['meta_title'] = 'Tổng hợp công nợ';
        $data['per_page'] = Cookie::get('per_page', 10);
        $data['buildings'] = $this->buildingRepo->getBuildingOfUser($this->building_active_id);
        $data['filter'] = $request->all();        
        $data['apartments'] = $this->apartmentRepo->getApartmentOfBuildingDebit($this->building_active_id);
        $page = $request->page ? $request->page : 1;
        $cycle_names = $debitRepo->getCycleNameV2($this->building_active_id);
        $data['cycle_names'] = $cycle_names;
        $data['chose_cycle_name'] = $request->cycle_name ? $request->cycle_name : '';
        $debitsTotal = null;
        if($cycle_names && (!isset($request->cycle_name) || $request->cycle_name == null)){
            $data['filter']['cycle_name'] = $request->cycle_name =  $cycle_names[0];
            //cycle_name_more
        } elseif (!isset($request->cycle_name)){
            $data['filter']['cycle_name'] = $request->cycle_name = Carbon::now()->format('Ym');
        }

        $isMoreCycleName = false;
        if(@$data['filter']['filter_custom'] === 'ky_bang_ke') unset($data['filter']['cycle_name_more']);
        if(isset($data['filter']['cycle_name_more'])) $isMoreCycleName = true;
        $getServiceApartments2 = $debitRepo::getAllApartmentLastTime($this->building_active_id,$isMoreCycleName ? $request->cycle_name_more : $request->cycle_name,$request->bdc_apartment_id,isset($data['filter']['du_no_cuoi_ky']) ? $data['filter']['du_no_cuoi_ky'] : false,$data['per_page'],(int)$page);
        if(!$getServiceApartments2) {
            return view('debit.v2.total', $data);
        }

        $temp_dau_ky =  DebitDetailRepository2::getTotalSumeryByCycleNameCus($this->building_active_id,$request->bdc_apartment_id ? $request->bdc_apartment_id : false,$request->cycle_name,"<",$request->to_date,$request->from_date);
        $temp_dau_ky_tt =  DebitDetailRepository2::getTotalSumeryByCycleNameCusNotStatusBill($this->building_active_id,$request->bdc_apartment_id ? $request->bdc_apartment_id : false,$request->cycle_name,"<",$request->to_date,$request->from_date);
//        $temp_trong_ky = DebitDetailRepository2::getTotalSumeryByCycleNameCus($this->building_active_id,$request->bdc_apartment_id ? $request->bdc_apartment_id : false,$request->cycle_name,"=");
        if ($isMoreCycleName) {
            $temp_trong_ky = DebitDetailRepository2::getTotalSumeryByMoreCycleNameCus1($this->building_active_id,$request->bdc_apartment_id ? $request->bdc_apartment_id : false,$request->cycle_name,$request->cycle_name_more);
            $temp_trong_ky_tt = DebitDetailRepository2::getTotalSumeryByMoreCycleNameCusNotStatusBill1($this->building_active_id,$request->bdc_apartment_id ? $request->bdc_apartment_id : false,$request->cycle_name,$request->cycle_name_more);
        } else {
            $temp_trong_ky = DebitDetailRepository2::getTotalSumeryByCycleNameCus($this->building_active_id,$request->bdc_apartment_id ? $request->bdc_apartment_id : false,$request->cycle_name,"=",$request->to_date,$request->from_date);
            $temp_trong_ky_tt = DebitDetailRepository2::getTotalSumeryByCycleNameCusNotStatusBill($this->building_active_id,$request->bdc_apartment_id ? $request->bdc_apartment_id : false,$request->cycle_name,"=",$request->to_date,$request->from_date);
        }

        $tong_dau_ky = !isset($temp_dau_ky_tt->tong_thanh_toan_ky) ? 0 : $temp_dau_ky->tong_phat_sinh-$temp_dau_ky_tt->tong_thanh_toan_ky;
        $tong_phat_sinh = !isset($temp_trong_ky->tong_phat_sinh) ? 0 : $temp_trong_ky->tong_phat_sinh;
        $tong_thanh_toan = !isset($temp_trong_ky_tt->tong_thanh_toan_ky) ? 0 : $temp_trong_ky_tt->tong_thanh_toan_ky;
        $tong_cuoi_ky = $tong_dau_ky+$tong_phat_sinh-$tong_thanh_toan;
        foreach ($getServiceApartments2 as $key => $value) {
            $building = Building::get_detail_building_by_building_id($value->bdc_building_id); // lấy ra dự án
            $apartment = Apartments::get_detail_apartment_by_apartment_id($value->bdc_apartment_id);
            $_customer = CustomersRespository::findApartmentIdV2($value->bdc_apartment_id, 0);
            $pubUserProfile = @$_customer ? PublicUsersProfileRespository::getInfoUserById($_customer->user_info_id) : null;
//            if(isset($request->cycle_name) && $request->cycle_name != null) {
//            }
           
            if($isMoreCycleName){
                $tempSumDauKy = isset($request->cycle_name) && $request->cycle_name != null ? DebitDetailRepository2::getTotalSumeryByCycleNameCus($this->building_active_id,$value->bdc_apartment_id,$request->cycle_name,"<",$request->to_date,$request->from_date) : 0;
                $tempSumTrongKy = DebitDetailRepository2::getTotalSumeryByMoreCycleNameCus1($this->building_active_id,$value->bdc_apartment_id,$request->cycle_name,$request->cycle_name_more);
                $tempSumTrongKy_tt = DebitDetailRepository2::getTotalSumeryByMoreCycleNameCusNotStatusBill1($this->building_active_id,$value->bdc_apartment_id,$request->cycle_name,$request->cycle_name_more);
                $dauky = isset($tempSumDauKy->tong_thanh_toan_ky) ? $tempSumDauKy->tong_phat_sinh - $tempSumDauKy->tong_thanh_toan_ky : 0;
                $trong_ky = !isset($tempSumTrongKy->tong_phat_sinh) ? 0 : $tempSumTrongKy->tong_phat_sinh;
                $thanh_toan = !isset($tempSumTrongKy_tt->tong_thanh_toan_ky) ? 0 : $tempSumTrongKy_tt->tong_thanh_toan_ky;
                $cuoi_ky = $dauky + $trong_ky - $thanh_toan;
                if($dauky == 0 &&  $trong_ky == 0 && $thanh_toan ==0){
                    continue;
                }
                $detail = [
                    'id' => isset($apartment) ? $apartment->id : '',
                    'ma_khach_hang' => isset($apartment) ? $apartment->code_customer : '',
                    'ten_khach_hang' => $pubUserProfile ? $pubUserProfile->full_name : null,
                    'can_ho' => $apartment ? $apartment->name : null,
                    'ma_du_an' => $building ? $building->building_code_manage : null,
                    'ma_san_pham' => $apartment ? $apartment->code : null,
                    'ten_du_an' => $building->name,
                    'dau_ky' =>  $dauky,
                    'trong_ky' => $trong_ky,
                    'thanh_toan' => $thanh_toan,
                    'cuoi_ky' =>  $cuoi_ky,
                ];
            }else {
                $tempSumDauKy = isset($request->cycle_name) && $request->cycle_name != null ? DebitDetailRepository2::getTotalSumeryByCycleNameCus($this->building_active_id,$value->bdc_apartment_id,$request->cycle_name,"<",$request->to_date,$request->from_date) : 0;
                $tempSumDauKy_tt = isset($request->cycle_name) && $request->cycle_name != null ? DebitDetailRepository2::getTotalSumeryByCycleNameCusNotStatusBill($this->building_active_id,$value->bdc_apartment_id,$request->cycle_name,"<",$request->to_date,$request->from_date) : 0;
                $tempSumTrongKy = DebitDetailRepository2::getTotalSumeryByCycleNameCus($this->building_active_id,$value->bdc_apartment_id,$request->cycle_name,"=",$request->to_date,$request->from_date);
                $tempSumTrongKy_tt = DebitDetailRepository2::getTotalSumeryByCycleNameCusNotStatusBill($this->building_active_id,$value->bdc_apartment_id,$request->cycle_name,"=",$request->to_date,$request->from_date);
                $dauky = isset($tempSumDauKy->tong_phat_sinh) ? $tempSumDauKy->tong_phat_sinh - $tempSumDauKy_tt->tong_thanh_toan_ky : ( isset($tempSumDauKy_tt->tong_thanh_toan_ky) ? - $tempSumDauKy_tt->tong_thanh_toan_ky : 0);
                $trong_ky = !isset($tempSumTrongKy->tong_phat_sinh) ? 0 : $tempSumTrongKy->tong_phat_sinh;
                $thanh_toan = !isset($tempSumTrongKy_tt->tong_thanh_toan_ky) ? 0 : $tempSumTrongKy_tt->tong_thanh_toan_ky;
                $cuoi_ky = $dauky + $trong_ky - $thanh_toan;
                if($dauky == 0 &&  $trong_ky == 0 && $thanh_toan ==0){
                    continue;
                }
                $detail = [
                    'id' => isset($apartment) ? $apartment->id : '',
                    'ma_khach_hang' => isset($apartment) ? $apartment->code_customer : '',
                    'ten_khach_hang' => $pubUserProfile ? $pubUserProfile->full_name : null,
                    'can_ho' => $apartment ? $apartment->name : null,
                    'ma_du_an' => $building ? $building->building_code_manage : null,
                    'ma_san_pham' => $apartment ? $apartment->code : null,
                    'ten_du_an' => $building->name,
                    'dau_ky' =>  $dauky,
                    'trong_ky' => $trong_ky,
                    'thanh_toan' => $thanh_toan,
                    'cuoi_ky' =>  $cuoi_ky,
                ];
            }
            $debitsTotal[] = collect($detail);
        }

        $data['debitsTotal'] =  $debitsTotal;
        $data['getServiceApartments'] =  $getServiceApartments2;
        $data['tong_dau_ky'] = $tong_dau_ky;
        $data['tong_trong_ky'] =  $tong_phat_sinh;
        $data['tong_cuoi_ky'] =   $tong_cuoi_ky;
        $data['tong_thanh_toan'] =   $tong_thanh_toan;
        return view('debit.v2.total', $data);
    }

    public function total_v2(Request $request, CustomersRespository $customersRespository, DebitDetailRepository2 $debitRepo)
    {
        // dd(1);
        $data['meta_title'] = 'Tổng hợp công nợ';
        $data['per_page'] = Cookie::get('per_page', 10);
        $data['filter'] = $request->all();     
        if(isset($data['filter']['bdc_apartment_id'])){
            $data['get_apartment'] = $this->apartmentRepo->findById($data['filter']['bdc_apartment_id']);
         }
         if(isset($data['filter']['ip_place_id'])){
             $data['get_place_building'] = $this->_buildingPlaceRepository->findById($data['filter']['ip_place_id']);
         } 

        $data['apartments'] = $this->apartmentRepo->getApartmentOfBuildingDebit($this->building_active_id);
        $cycle_names = $debitRepo->getCycleNameV2($this->building_active_id);

        $data['cycle_names'] = $cycle_names;

        $data['chose_cycle_name'] = $request->cycle_name ? $request->cycle_name : '';
        if($cycle_names && (!isset($request->cycle_name) || $request->cycle_name == null)){
            $request->cycle_name = $cycle_names[0];
            $data['filter']['cycle_name'] = $cycle_names[0];
        }

        $get_Debit_Detail = $debitRepo::getTotalDebitDetail($request,$this->building_active_id)->paginate($data['per_page']);

        $total_Debit_Detail = $debitRepo::getTotalDebitDetail($request,$this->building_active_id)->get();
        
        $total_Debit_Detail = collect($total_Debit_Detail);

        $debitsTotal = null;

        $apartmentId = null;

        $total_tien_thua = 0;

        $building = Building::get_detail_building_by_building_id($this->building_active_id); // lấy ra dự án

        foreach ($get_Debit_Detail as $key => $value) {
            $customer = Customers::get_detail_customer_by_apartment_id($value->bdc_apartment_id); // lấy ra chủ hộ
            if($customer){
                $user_info = UserInfo::get_detail_user_info_by_id($customer->pub_user_profile_id);
            }
            $apartment = Apartments::get_detail_apartment_by_apartment_id($value->bdc_apartment_id);
            $servicePrice = ApartmentServicePriceRepository::getInfoServiceApartmentById($value->bdc_apartment_service_price_id);
            $get_tien_thua = PaymentDetail::check_total_cost($this->building_active_id,$value->bdc_apartment_id,0,$data['filter']['cycle_name']);
            $service = $value->bdc_apartment_service_price_id > 0 ? Service::get_detail_bdc_service_by_bdc_service_id($servicePrice->bdc_service_id) : null;
            // if($apartmentId != $value->bdc_apartment_id && $get_tien_thua > 0){
            //     $apartmentId = $value->bdc_apartment_id;
            //     $total_tien_thua += 0 - $get_tien_thua;
            //     $detail = [
            //         'id' => isset($apartment) ? $apartment->id : '',
            //         'ma_khach_hang' => isset($apartment) ? $apartment->code_customer : '',
            //         'ten_khach_hang' => $apartment ? $apartment->name_customer : null,
            //         'can_ho' => $apartment ? $apartment->name : null,
            //         'ma_du_an' => $building ? $building->building_code_manage : null,
            //         'ma_san_pham' => 'Tiền thừa',
            //         'ten_du_an' => $building->name,
            //         'dau_ky' =>  0 - $get_tien_thua,
            //         'trong_ky' => null,
            //         'thanh_toan' => null,
            //         'cuoi_ky' =>  0 - $get_tien_thua,
            //      ];
            //      $debitsTotal[] = collect($detail);
            // }else{
                $new_sumery = $value->sumery - $value->discount;
                $detail = [
                    'id' => isset($apartment) ? $apartment->id : '',
                    'ma_khach_hang' => isset($apartment) ? $apartment->code_customer : '',
                    'ten_khach_hang' => $apartment ? $apartment->name_customer : null,
                    'can_ho' => $apartment ? $apartment->name : null,
                    'ma_du_an' => $building ? $building->building_code_manage : null,
                    'ma_san_pham' => $apartment ? $apartment->code : null,
                    'ten_du_an' => $building->name,
                    'dau_ky' =>  $value->dau_ky,
                    'trong_ky' => $value->trong_ky,
                    'thanh_toan' => $value->thanh_toan,
                    'cuoi_ky' =>  $value->dau_ky + $value->trong_ky - $value->thanh_toan,
                ];
                $debitsTotal[] = collect($detail);
            //}
           
        }
        $data['debitsTotal'] = $debitsTotal;
        $data['getServiceApartments'] =  $get_Debit_Detail;
        $data['tong_dau_ky'] = $total_Debit_Detail->sum('dau_ky') + $total_tien_thua;
        $data['tong_trong_ky'] =  $total_Debit_Detail->sum('trong_ky');
        $data['tong_thanh_toan'] = $total_Debit_Detail->sum('thanh_toan');
        $data['tong_cuoi_ky'] =   $data['tong_dau_ky'] + $data['tong_trong_ky'] - $data['tong_thanh_toan'];

         return view('debit.v2.total', $data);
    }

    public function sendMessage(Request $request)
    {
        $building = Building::get_detail_building_by_building_id($this->building_active_id);
        $apartmentIds = $request->apartmentIds;
        $apartmentArr = explode(',', $apartmentIds);
        try {
            $total = ['email' => 0, 'app' => 0, 'sms' => 0];
            if ($request->sendMail == "true")
                $total['email'] = 1;
            if ($request->sendSms == "true")
                $total['sms'] = 1;
            if ($request->sendApp == "true") {
                $total['app'] = 1;
            }
            if ($total['sms'] > 0 || $total['app'] > 0 || $total['email'] > 0) {
                $campain = Campain::updateOrCreateCampain('Thông báo Nhắc phí ' . now()->format('m/Y'), config('typeCampain.NHAC_NO'), null, $total, $this->building_active_id, 0, 0);
            }
            foreach ($apartmentArr as $_apartment) {
                $customers = $this->_customersRespository->findResidentApartment($_apartment, $request->send_to == 0 ? 0 : null);
                $apartment = Apartments::get_detail_apartment_by_apartment_id($_apartment);
                if ($customers) {
                    foreach ($customers as $key_1 => $value_customer) {
                        $pubUserProfile = $value_customer ? PublicUsersProfileRespository::getInfoUserById($value_customer->user_info_id) : null;
                     
                        if ($request->sendMail == "true" &&  filter_var($pubUserProfile->email_contact, FILTER_VALIDATE_EMAIL)) {
                            // lấy tổng dư nợ cuối kỳ
                            $getServiceApartments = BdcV2DebitDetailDebitDetailRepository::getAllApartmentDetailLastTime3($this->building_active_id, $request->cycle_name, $_apartment, false, false, 100, 1);
                           
                            $debitsTotal = null;
                            $view = null;
                            $sum_du_no_cuoi_ky = 0;
                            if ($getServiceApartments) {
                                foreach ($getServiceApartments as $key => $value) {
                                    $servicePrice = ApartmentServicePriceRepository::getInfoServiceApartmentById($value->bdc_apartment_service_price_id);
                                    $Vehicles = null;
                                    if ($value->bdc_apartment_service_price_id == 0) {
                                        $service = (object) ["code_receipt" => "", "name" => "Tiền thừa"];
                                    } else {
                                        $service = Service::get_detail_bdc_service_by_bdc_service_id($servicePrice->bdc_service_id);
                                        if ($servicePrice->bdc_vehicle_id > 0) {
                                            $Vehicles = Vehicles::get_detail_vehicle_by_id($servicePrice->bdc_vehicle_id);
                                        }
                                    }
                                    $detail = [
                                        'ten_khach_hang' => @$pubUserProfile->full_name,
                                        'can_ho' =>  @$apartment->name,
                                        'ma_san_pham' => @$apartment->code,
                                        'ma_thu' =>  $service->code_receipt,
                                        'dich_vu' => isset($Vehicles) ? $service->name . ' - ' . $Vehicles->number  : $service->name,
                                        'dau_ky' =>  $value->before_cycle_name,
                                        'trong_ky' => @$value->sumery ?? 0,
                                        'thanh_toan' => @$value->paid_by_cycle_name ?? 0,
                                        'cuoi_ky' => $value->after_cycle_name,
                                    ];
                                    $sum_du_no_cuoi_ky += $value->after_cycle_name;
                                    if($value->after_cycle_name > 0){
                                       $debitsTotal[] = collect($detail);
                                    }
                                }
                            }
                            if ($debitsTotal) {
                                $view = view('bill.v2._send_mail', compact('debitsTotal'))->render();
                            }

                            $data_send_email = [
                                'params' => [
                                    '@tenkhachhang' => @$pubUserProfile->full_name,
                                    '@canho' => $apartment->name,
                                    '@kyhoadon' => $request->cycle_name ?? 'hiện tại',
                                    '@noidung' => $view,
                                    '@message' => $request->message,
                                    '@message' => $request->message,
                                    '@dunocuoiky' => number_format($sum_du_no_cuoi_ky)
                                ],
                                'cc' => $pubUserProfile->email_contact,
                                'building_id' => $this->building_active_id,
                                'type' => ServiceSendMailV2::NHAC_NO,
                                'status' => 'paid',
                                'campain_id' => $campain->id
                            ];
                            ServiceSendMailV2::setItemForQueue($data_send_email);
                        }

                        if ($request->sendSms == "true" && $pubUserProfile && $pubUserProfile->phone_contact) {
                            $headerParams = [
                                "Content-Type" => "application/json",
                                "ClientId" => env('ClientId_bdc'),
                                "ClientSecret" => env('ClientSecret_bdc'),
                            ];
                            $strip_html = Helper::convert_vi_to_en(strip_tags($request->message));
                            $data = [
                                "phone" => $pubUserProfile->phone_contact,
                                "message" => $strip_html,
                            ];
                            $client = new \GuzzleHttp\Client();
                            $requestClient = $client->request('POST', env('API_SEND_SMS'), ['headers' => $headerParams, 'body' => json_encode($data)]);
                        }

                        if ($request->sendApp == "true" && $value_customer && $pubUserProfile->user_id) {
                            $data_payload['message'] = strip_tags($request->message);
                            $data_payload['user_id'] = $pubUserProfile->user_id;
                            $data_payload['building_id'] = $this->building_active_id;
                            $data_payload['type'] = SendNotifyFCMService::NHAC_NO;
                            $data_payload['title'] = "Gửi thông báo";
                            $data_noti = [
                                "message" => strip_tags($request->message),
                                "building_id" => $this->building_active_id,
                                "title" => '[' . $building->name . "]_" . $apartment->name,
                                "action_name" => SendNotifyFCMService::NHAC_NO,
                                'type' => SendNotifyFCMService::NHAC_NO,
                                'avatar' => "avatar/system/01.png",
                                'campain_id' => $campain->id,
                                'app' => 'v2'
                            ];
                            SendNotifyFCMService::setItemForQueueNotify(array_merge($data_noti, ['user_id' => $pubUserProfile->user_id, 'app_config' => @$building->template_mail == 'asahi' ? 'asahi' : 'cudan']));
                        }
                    }
                }
            }
            
            return response()->json([
                'success' => true,
                'status' => 200,
                'message' => "Gửi thông báo thành công.",
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'status' => 500,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function exportExcelTotal(Request $request, CustomersRespository $customersRespository)
    {
        try {
            $data['meta_title'] = 'Tổng hợp công nợ';
            $data['buildings'] = $this->buildingRepo->getBuildingOfUser($this->building_active_id);
            $data['filter'] = $request->all();
            $data['apartments'] = $this->apartmentRepo->getApartmentOfBuildingDebit($this->building_active_id);
            $data['customers'] = $customersRespository;
            // Start displaying items from this number;
            $debitsTotal = $this->debitRepo->GeneralAccountantAll($this->building_active_id);
            $tongDauky = array();
            if ($request->all())
            {
                $fromDate = $request['from_date'];
                $toDate = $request['to_date'];
                $apartmentId = $request['bdc_apartment_id'];
                $apartments = $apartmentId == null
                    ? $this->apartmentRepo->getApartmentOfBuilding($this->building_active_id)
                    : $this->apartmentRepo->findByIdBuildingId($this->building_active_id, $apartmentId);

                if(($fromDate != null && $toDate != null) || $apartmentId != null)
                {
                    $debitsTotal = [];
                    foreach($apartments as $apartment)
                    {
                        $debits = $fromDate != null && $toDate != null
                            ? $this->debitRepo->GeneralAccountant($this->building_active_id, $fromDate, $toDate, $apartment->id)
                            : $this->debitRepo->GeneralAccountantApartment($this->building_active_id, $apartmentId);
                        //$debits = $this->debitRepo->GeneralAccountant($this->building_active_id, $fromDate, $toDate, $apartment->id);
                        $debit = empty($debits) ? $debits : $debits[0];
                        if(empty($debit))
                        {
                            $debit["bdc_apartment_id"] = $apartment->id;
                            $debit["name"] = $apartment->name;
                            $debit["bdc_building_id"] = $this->building_active_id;
                            $debit["ps_trongky"] = 0;
                            $debit["dau_ky"] = 0;
                            $debit["thanh_toan"] = 0;
                            $debit = (object)$debit;
                        }
                        array_push($debitsTotal, $debit);
                    }
                    $tongDauky = $this->debitRepo->TongDauKy($this->building_active_id, $fromDate, $apartmentId);
                }
            }
            $tongDauky = !empty($tongDauky) ? array_shift($tongDauky)->dau_ky : 0;
            $data['sumDayKy_all'] = $tongDauky;
            $data['sumPsTrongKy_all'] = array_sum(array_column($debitsTotal, 'ps_trongky'));
            $data['sumThanhToan_all'] = array_sum(array_column($debitsTotal, 'thanh_toan'));
            // $data['debits'] = new LengthAwarePaginator($itemsForCurrentPage, count($debits), $perPage, $page, ['path' => route('admin.v2.debit.total')]);
            $data['debits'] = collect($debitsTotal);
            $result = Excel::create('Tổng hợp công nợ', function($excel) use ($data) {
                $excel->sheet('Tổng hợp công nợ', function($sheet) use ($data) {
                    $sheet->loadView('debit.v2._export_excel_total', $data);
                    $sheet->getStyle('A7:G7')->applyFromArray(array(
                        'fill' => array(
                            'type'  => PHPExcel_Style_Fill::FILL_SOLID,
                            'color' => array('rgb' => '337ab7')
                        )
                    ));
                    $sheet->getStyle('A6:G6')->applyFromArray(array(
                        'fill' => array(
                            'type'  => PHPExcel_Style_Fill::FILL_SOLID,
                            'color' => array('rgb' => '337ab7')
                        )
                    ));
                });
            })->store('xlsx',storage_path('exports/'));
            ob_end_clean();
            $file     = storage_path('exports/'.$result->filename.'.'.$result->ext);
            ob_end_clean();
            return response()->download($file)->deleteFileAfterSend(true);
        }catch (Exception $e){
            return redirect()->back()->with('warning','đã có lỗi xảy ra.');
        }

             
    }

    public function detailVersion2(Request $request, CustomersRespository $customersRespository)
    {
        $data['meta_title'] = 'Chi tiết công nợ';
        $data['per_page'] = Cookie::get('per_page', 10);
        $data['buildings'] = $this->buildingRepo->getBuildingOfUser($this->building_active_id);
        $data['filter'] = $request->all(); 
        if(isset($data['filter']['bdc_apartment_id'])){
            $data['get_apartment'] = $this->apartmentRepo->findById($data['filter']['bdc_apartment_id']);
         }
         if(isset($data['filter']['ip_place_id'])){
             $data['get_place_building'] = $this->_buildingPlaceRepository->findById($data['filter']['ip_place_id']);
         }
        $data['apartments'] = $this->apartmentRepo->getApartmentOfBuildingDebit($this->building_active_id);
        $data['customers'] = $customersRespository;       
        // Start displaying items from this number;
        $page = $request->page ? $request->page : 1;
        $perPage = $data['per_page'];
        $offSet = ($page * $perPage) - $perPage; // Start displaying items from this number
        $debits = $this->debitRepo->filterAll($this->building_active_id, $request->all());
        $data['sumPs_all'] = array_sum(array_column($debits, 'sumery'));
        $data['sumThu_all'] = array_sum(array_column($debits, 'cost'));

        $data['sumDauky_all'] = isset($request["from_date"]) && isset($request["to_date"]) && $request["from_date"] != null && $request["to_date"] != null ? $data['sumPs_all'] - $data['sumThu_all'] : 0;

        $itemsForCurrentPage = array_slice($debits, $offSet, $perPage, true);
        $data['debits'] = new LengthAwarePaginator($itemsForCurrentPage, count($debits), $perPage, $page, ['path' => route('admin.v2.debit.detailVersion2')]);

        return view('debit.v2.detail_version2', $data);
    }

    public function generalDetail(Request $request, DebitDetailRepository2 $debitRepo)
    {
        $data['meta_title'] = 'Tổng hợp công nợ chi tiết';
        $data['per_page'] = Cookie::get('per_page', 10);
        $data['filter'] = $request->all();
        if(isset($data['filter']['bdc_apartment_id'])){
            $data['get_apartment'] = $this->apartmentRepo->findById($data['filter']['bdc_apartment_id']);
         }
         if(isset($data['filter']['ip_place_id'])){
             $data['get_place_building'] = $this->_buildingPlaceRepository->findById($data['filter']['ip_place_id']);
         }
        $page = $request->page ? $request->page : 1;

        $cycle_names = $debitRepo->getCycleNameV2($this->building_active_id);
        $data['cycle_names'] = $cycle_names;
        $data['chose_cycle_name'] = $request->cycle_name ? $request->cycle_name : '';
        
        if($cycle_names && (!isset($request->cycle_name) || $request->cycle_name == null)){
            $data['filter']['cycle_name'] = $request->cycle_name =  $cycle_names[0];
        } elseif (!isset($request->cycle_name)){
            $data['filter']['cycle_name'] = $request->cycle_name = Carbon::now()->format('Ym');
        }

        $services = ServiceRepository::getAllServiceByBuildingId($this->building_active_id);
        $data['services'] = $services;

        $planceId = false;

        if(isset($request->ip_place_id) && $request->ip_place_id != null){
            $listApartments = $this->apartmentRepo->findByPlaceId($this->building_active_id, $request->ip_place_id);
            if(count($listApartments) > 0) $planceId = $listApartments->toArray();
        }

        $filterService = false;
        if((isset($request->service) && $request->service != null) || isset($request->type_service) && $request->type_service != null){
            $listSevices = ApartmentServicePriceRepository::getServicePriceByServiceIdV2($request);
            if($listSevices) $filterService = $listSevices->pluck('id')->toArray();
        }

        $isMoreCycleName = false;
        if(@$data['filter']['filter_custom'] === 'ky_bang_ke') unset($data['filter']['cycle_name_more']);
        if(isset($data['filter']['cycle_name_more'])) $isMoreCycleName = true;
        $getServiceApartments = $debitRepo::getAllApartmentDetailLastTime4($this->building_active_id, $isMoreCycleName ? $request->cycle_name_more : $request->cycle_name, $planceId, $request->bdc_apartment_id, isset($data['filter']['du_no_cuoi_ky']) ? $data['filter']['du_no_cuoi_ky'] : false, $filterService, $data['per_page'], (int)$page);


        if(!$getServiceApartments){
            return view('debit.v2.general_detail', $data);
        }
        $debitsTotal = null;
        $temp_dau_ky = DebitDetailRepository2::getTotalSumeryByCycleNameCus($this->building_active_id, $request->bdc_apartment_id ? $request->bdc_apartment_id : false, $request->cycle_name, "<", $request->to_date, $request->from_date,$filterService);
        $temp_dau_ky_tt = DebitDetailRepository2::getTotalSumeryByCycleNameCusNotStatusBill($this->building_active_id, $request->bdc_apartment_id ? $request->bdc_apartment_id : false, $request->cycle_name, "<", $request->to_date, $request->from_date,$filterService);
        if ($isMoreCycleName) {
            $temp_trong_ky = DebitDetailRepository2::getTotalSumeryByMoreCycleNameCus1($this->building_active_id, $request->bdc_apartment_id ? $request->bdc_apartment_id : false, $request->cycle_name, $request->cycle_name_more,$filterService);
            $temp_trong_ky_tt = DebitDetailRepository2::getTotalSumeryByMoreCycleNameCusNotStatusBill1($this->building_active_id, $request->bdc_apartment_id ? $request->bdc_apartment_id : false, $request->cycle_name, $request->cycle_name_more,$filterService);
        } else {
            $temp_trong_ky = DebitDetailRepository2::getTotalSumeryByCycleNameCus($this->building_active_id, $request->bdc_apartment_id ? $request->bdc_apartment_id : false, $request->cycle_name, "=", $request->to_date, $request->from_date, $filterService);
            $temp_trong_ky_tt = DebitDetailRepository2::getTotalSumeryByCycleNameCusNotStatusBill($this->building_active_id, $request->bdc_apartment_id ? $request->bdc_apartment_id : false, $request->cycle_name, "=", $request->to_date, $request->from_date, $filterService);
        }

        $tong_dau_ky = !isset($temp_dau_ky_tt->tong_thanh_toan_ky) ? 0 : $temp_dau_ky->tong_phat_sinh-$temp_dau_ky_tt->tong_thanh_toan_ky;
        $tong_phat_sinh = !isset($temp_trong_ky->tong_phat_sinh) ? 0 : $temp_trong_ky->tong_phat_sinh;
        $tong_thanh_toan = !isset($temp_trong_ky_tt->tong_thanh_toan_ky) ? 0 : $temp_trong_ky_tt->tong_thanh_toan_ky;
        $tong_cuoi_ky = $tong_dau_ky+$tong_phat_sinh-$tong_thanh_toan;



        foreach ($getServiceApartments as $key => $value) {
            $_customer = CustomersRespository::findApartmentIdV2($value->bdc_apartment_id, 0);
            $pubUserProfile = @$_customer ? PublicUsersProfileRespository::getInfoUserById($_customer->user_info_id) : null;
            $apartment = Apartments::get_detail_apartment_by_apartment_id($value->bdc_apartment_id);
            $servicePrice = ApartmentServicePriceRepository::getInfoServiceApartmentById($value->bdc_apartment_service_price_id);
            $Vehicles = null;
            if($value->bdc_apartment_service_price_id == 0){
                $service = (object) ["code_receipt" =>"","name"=>"Tiền thừa"];
            }else{
                $service = Service::get_detail_bdc_service_by_bdc_service_id($servicePrice->bdc_service_id);
                if ($servicePrice->bdc_vehicle_id > 0) {
                    $Vehicles = Vehicles::get_detail_vehicle_by_id($servicePrice->bdc_vehicle_id);
                }
            }
           

            if($isMoreCycleName){
                $phatsinh = DebitDetailRepository2::getTotalSumeryByCycleNameMoreApartmentServiceCus($this->building_active_id,$request->bdc_apartment_id ? $request->bdc_apartment_id : false,$value->bdc_apartment_service_price_id,$request->cycle_name,$request->cycle_name_more);
                $thanhtoan = DebitDetailRepository2::getTotalSumeryByCycleNameMoreApartmentServiceCusNotStatusBill($this->building_active_id,$request->bdc_apartment_id ? $request->bdc_apartment_id : false,$value->bdc_apartment_service_price_id,$request->cycle_name,$request->cycle_name_more);
                $dau_ky = DebitDetailRepository2::getTotalSumeryByCycleNameApartmentServiceCus($this->building_active_id, $request->bdc_apartment_id ? $request->bdc_apartment_id : false, $value->bdc_apartment_service_price_id, $request->cycle_name,"<");
               
                $detail = [
                    'ten_khach_hang' =>  isset($pubUserProfile) ? $pubUserProfile->full_name : '',
                    'can_ho' =>  $apartment ? $apartment->name : null,
                    'ma_san_pham' =>  $apartment ? $apartment->code : null,
                    'ma_thu' =>  $service->code_receipt,
                    'dich_vu' => isset($Vehicles) ? @$service->name .' - '.@$Vehicles->number  : @$service->name,
                    'dau_ky' =>  !isset($dau_ky->tong_thanh_toan_ky) ? 0 : $dau_ky->tong_phat_sinh-$dau_ky->tong_thanh_toan_ky,
                    'trong_ky' => $phatsinh->tong_phat_sinh ?? 0,
                    'thanh_toan' => $thanhtoan->tong_thanh_toan_ky ?? 0,
                    'cuoi_ky' => $value->after_cycle_name,
                ];
                if($detail['dau_ky'] == 0 &&  $detail['trong_ky'] == 0 && $detail['thanh_toan'] ==0){
                    continue;
                }
            } else {
                $getBill = \App\Repositories\BdcBills\V2\BillRepository::getBillById($value->bdc_bill_id);
                $checkBill = false;
                if($getBill){
                   if( $getBill->status >= -2) $checkBill = true;
                }
                if(!$checkBill){
                    $value->sumery = 0;
                }
                $detail = [
                    'ten_khach_hang' =>  isset($pubUserProfile) ? $pubUserProfile->full_name : '',
                    'can_ho' =>  $apartment ? $apartment->name : null,
                    'ma_san_pham' =>  $apartment ? $apartment->code : null,
                    'ma_thu' =>  $service->code_receipt,
                    'dich_vu' => isset($Vehicles) ? @$service->name .' - '.@$Vehicles->number  : @$service->name,
                    'dau_ky' =>  $value->cycle_name == $request->cycle_name ? $value->before_cycle_name : $value->after_cycle_name,
                    'trong_ky' => $value->cycle_name == $request->cycle_name ? $value->sumery : 0,
                    'thanh_toan' => $value->cycle_name == $request->cycle_name ? $value->paid_by_cycle_name : 0,
                    'cuoi_ky' => $value->after_cycle_name,
                ];
//                if($detail['dau_ky'] == 0 &&  $detail['trong_ky'] == 0 && $detail['thanh_toan'] ==0){
//                    continue;
//                }
            }
            $debitsTotal[] = collect($detail);
        }
        $data['debits'] = $debitsTotal;
        $data['getServiceApartments'] =  $getServiceApartments;
        $data['tong_dau_ky'] = $tong_dau_ky;
        $data['tong_trong_ky'] =  $tong_phat_sinh;
        $data['tong_cuoi_ky'] =   $tong_cuoi_ky;
        $data['tong_thanh_toan'] =  $tong_thanh_toan;
        return view('debit.v2.general_detail', $data);
    }

    public function generalDetailBK(Request $request, DebitDetailRepository2 $debitRepo)
    {
        $data['meta_title'] = 'Tổng hợp công nợ chi tiết';
        $data['per_page'] = Cookie::get('per_page', 10);
        $data['filter'] = $request->all();
        if(isset($data['filter']['bdc_apartment_id'])){
            $data['get_apartment'] = $this->apartmentRepo->findById($data['filter']['bdc_apartment_id']);
         }
         if(isset($data['filter']['ip_place_id'])){
             $data['get_place_building'] = $this->_buildingPlaceRepository->findById($data['filter']['ip_place_id']);
         }
        $page = $request->page ? $request->page : 1;
        $cycle_names = $debitRepo->getCycleNameV2($this->building_active_id);
        $data['cycle_names'] = $cycle_names;
        $data['chose_cycle_name'] = $request->cycle_name ? $request->cycle_name : '';
        if($cycle_names && (!isset($request->cycle_name) || $request->cycle_name == null)){
            $request->cycle_name = $cycle_names[0];
            $data['filter']['cycle_name'] = $cycle_names[0];
        }

        $services = ServiceRepository::getAllServiceByBuildingId($this->building_active_id);
        $data['services'] = $services;

        $filterService = false;
        if(isset($data['filter']['service'])){
            $listSevices = ApartmentServicePriceRepository::getServicePriceByServiceId($data['filter']['service']);
            if($listSevices) $filterService = $listSevices->pluck('id')->toArray();
        }

        $getServiceApartments = $debitRepo::getAllApartmentDetailLastTime($this->building_active_id,$request->cycle_name,$request->bdc_apartment_id,isset($data['filter']['du_no_cuoi_ky']) ? $data['filter']['du_no_cuoi_ky'] : false,$filterService,$data['per_page'],(int)$page);
        if(!$getServiceApartments){
            return view('debit.v2.general_detail', $data);
        }
        $debitsTotal = null;


        /*if(isset($request->cycle_name) && $request->cycle_name != null) {

        } else {
            $temp_dau_ky =  DebitDetailRepository2::getTotalSumeryByCycleNameCus($this->building_active_id,false, false);
            $tong_dau_ky = 0;
            $tong_phat_sinh = !isset($temp_dau_ky->tong_phat_sinh) ? 0 : $temp_dau_ky->tong_phat_sinh;
            $tong_thanh_toan = !isset($temp_dau_ky->tong_thanh_toan) ? 0 : $temp_dau_ky->tong_thanh_toan;
        }*/
        $temp_dau_ky =  DebitDetailRepository2::getTotalSumeryByCycleNameCus($this->building_active_id,$request->bdc_apartment_id ? $request->bdc_apartment_id : false,$request->cycle_name,"<", false, false,$filterService);
        $temp_trong_ky = DebitDetailRepository2::getTotalSumeryByCycleNameCus($this->building_active_id,$request->bdc_apartment_id ? $request->bdc_apartment_id : false,$request->cycle_name,"=",false, false ,$filterService);
        $tong_dau_ky = !isset($temp_dau_ky->tong_thanh_toan_ky) ? 0 : $temp_dau_ky->tong_phat_sinh-$temp_dau_ky->tong_thanh_toan_ky;
        $tong_phat_sinh = !isset($temp_trong_ky->tong_phat_sinh) ? 0 : $temp_trong_ky->tong_phat_sinh;
        $tong_thanh_toan = !isset($temp_trong_ky->tong_thanh_toan_ky) ? 0 : $temp_trong_ky->tong_thanh_toan_ky;
        $tong_cuoi_ky = $tong_dau_ky+$tong_phat_sinh-$tong_thanh_toan;

        foreach ($getServiceApartments as $key => $value) {
            $customer = Customers::get_detail_customer_by_apartment_id($value->bdc_apartment_id); // lấy ra chủ hộ
            if($customer){
                $user_info = UserInfo::get_detail_user_info_by_id($customer->pub_user_profile_id);
            }
            $apartment = Apartments::get_detail_apartment_by_apartment_id($value->bdc_apartment_id);
            $servicePrice = ApartmentServicePriceRepository::getInfoServiceApartmentById($value->bdc_apartment_service_price_id);

            if($value->bdc_apartment_service_price_id == 0){
                $service = (object) ["code_receipt" =>"","name"=>"Tiền thừa"];
            }else{
                $service = Service::get_detail_bdc_service_by_bdc_service_id($servicePrice->bdc_service_id);
            }

            $tempSumDauKy = DebitDetailRepository2::getTotalSumeryByCycleNameApartmentServiceCus($this->building_active_id,$value->bdc_apartment_id,$value->bdc_apartment_service_price_id,$request->cycle_name,"<");
//            $tempSumTrongKy = DebitDetailRepository2::getTotalSumeryByCycleNameCus($this->building_active_id,$value->bdc_apartment_id,$request->cycle_name,"=");

            $dauky = !isset($request->cycle_name) || $request->cycle_name == null || !isset($tempSumDauKy->tong_thanh_toan_ky) ? 0 : $tempSumDauKy->tong_phat_sinh-$tempSumDauKy->tong_thanh_toan_ky;
//            $trong_ky = !isset($tempSumTrongKy->tong_phat_sinh) ? 0 : $tempSumTrongKy->tong_phat_sinh;
//            $thanh_toan = !isset($tempSumTrongKy->tong_thanh_toan) ? 0 : $tempSumTrongKy->tong_thanh_toan;
            $cuoi_ky = $value->cycle_name == $request->cycle_name ? $dauky + $value->sumery - $value->paid_by_cycle_name : $dauky;
            $detail = [
               'ten_khach_hang' =>  isset($user_info) ? $user_info->display_name : '',
               'can_ho' =>  $apartment ? $apartment->name : null,
               'ma_san_pham' =>  $apartment ? $apartment->code : null,
               'ma_thu' =>  $service->code_receipt,
               'dich_vu' =>  $service ? $service->name : null,
               'dau_ky' =>  $dauky,
               'trong_ky' => $value->cycle_name == $request->cycle_name ? $value->sumery : 0,
               'thanh_toan' => $value->cycle_name == $request->cycle_name ? $value->paid_by_cycle_name : 0,
               'cuoi_ky' =>  $cuoi_ky,
            ];
            $debitsTotal[] = collect($detail);
        }
        $data['debits'] = $debitsTotal;
        $data['getServiceApartments'] =  $getServiceApartments;
        $data['tong_dau_ky'] = $tong_dau_ky;
        $data['tong_trong_ky'] =  $tong_phat_sinh;
        $data['tong_cuoi_ky'] =   $tong_cuoi_ky;
        $data['tong_thanh_toan'] =  $tong_thanh_toan;
        return view('debit.v2.general_detail', $data);
    }

    public function generalDetail_v2(Request $request, DebitDetailRepository2 $debitRepo)
    {
        $data['meta_title'] = 'Tổng hợp công nợ';
        $data['per_page'] = Cookie::get('per_page', 10);
        $data['filter'] = $request->all();     
        if(isset($data['filter']['bdc_apartment_id'])){
            $data['get_apartment'] = $this->apartmentRepo->findById($data['filter']['bdc_apartment_id']);
         }
         if(isset($data['filter']['ip_place_id'])){
             $data['get_place_building'] = $this->_buildingPlaceRepository->findById($data['filter']['ip_place_id']);
         } 


        $cycle_names = $debitRepo->getCycleNameV2($this->building_active_id);

        $data['cycle_names'] = $cycle_names;

        $data['chose_cycle_name'] = $request->cycle_name ? $request->cycle_name : '';
        if($cycle_names && (!isset($request->cycle_name) || $request->cycle_name == null)){
            $request->cycle_name = $cycle_names[0];
            $data['filter']['cycle_name'] = $cycle_names[0];
        }

        $get_Debit_Detail = $debitRepo::getListDebitDetail($request,$this->building_active_id)->paginate($data['per_page']);
        $total_Debit_Detail = $debitRepo::getListDebitDetail($request,$this->building_active_id)->get();
        
        $total_Debit_Detail = collect($total_Debit_Detail);

        $debitsTotal = null;

        $apartmentId = null;

        $total_tien_thua = 0;

        foreach ($get_Debit_Detail as $key => $value) {
            $customer = Customers::get_detail_customer_by_apartment_id($value->bdc_apartment_id); // lấy ra chủ hộ
            if($customer){
                $user_info = UserInfo::get_detail_user_info_by_id($customer->pub_user_profile_id);
            }
            $apartment = Apartments::get_detail_apartment_by_apartment_id($value->bdc_apartment_id);
            $servicePrice = ApartmentServicePriceRepository::getInfoServiceApartmentById($value->bdc_apartment_service_price_id);
            $get_tien_thua = PaymentDetail::check_total_cost($this->building_active_id,$value->bdc_apartment_id,0,$data['filter']['cycle_name']);
            $service = $value->bdc_apartment_service_price_id > 0 ? Service::get_detail_bdc_service_by_bdc_service_id($servicePrice->bdc_service_id) : null;
            if($apartmentId != $value->bdc_apartment_id && $get_tien_thua > 0){
                $apartmentId = $value->bdc_apartment_id;
                $total_tien_thua += 0 - $get_tien_thua;
                $detail = [
                    'ten_khach_hang' =>  isset($user_info) ? $user_info->display_name : '',
                    'can_ho' =>  $apartment ? $apartment->name : null,
                    'ma_san_pham' =>  $apartment ? $apartment->code : null,
                    'ma_thu' =>  '',
                    'dich_vu' =>  'Tiền thừa',
                    'dau_ky' =>  0 - $get_tien_thua,
                    'trong_ky' => null,
                    'thanh_toan' => null,
                    'cuoi_ky' =>   0 - $get_tien_thua,
                 ];
                 $debitsTotal[] = collect($detail);
            }else{
                $new_sumery = $value->sumery - $value->discount;
                $detail = [
                   'ten_khach_hang' =>  isset($user_info) ? $user_info->display_name : '',
                   'can_ho' =>  $apartment ? $apartment->name : null,
                   'ma_san_pham' =>  $apartment ? $apartment->code : null,
                   'ma_thu' =>  $service->code_receipt,
                   'dich_vu' =>  $service ? $service->name : null,
                   'dau_ky' =>  $value->dau_ky,
                   'trong_ky' => $new_sumery,
                   'thanh_toan' => $value->thanh_toan,
                   'cuoi_ky' =>  $value->dau_ky + $new_sumery - $value->thanh_toan,
                ];
                $debitsTotal[] = collect($detail);
            }
           
        }
        $data['debits'] = $debitsTotal;
        $data['getServiceApartments'] =  $get_Debit_Detail;
        $data['tong_dau_ky'] = $total_Debit_Detail->sum('dau_ky') + $total_tien_thua;
        $data['tong_trong_ky'] =  $total_Debit_Detail->sum('sumery') + $total_Debit_Detail->sum('discount');
        $data['tong_thanh_toan'] = $total_Debit_Detail->sum('thanh_toan');
        $data['tong_cuoi_ky'] =   $data['tong_dau_ky'] + $data['tong_trong_ky'] - $data['tong_thanh_toan'];
        return view('debit.v2.general_detail', $data);
    }
    public function exportExcelGeneralDetail(Request $request, DebitDetailRepository2 $debitRepo)
    {
        try {
            $data['meta_title'] = 'Chi tiết công nợ tổng hợp';
            $data['filter'] = $request->all();
            $data['per_page'] = Cookie::get('per_page', 10);
            $page = @$request->page ? $request->page : null;

            $planceId = false;

            if(isset($request->ip_place_id) && $request->ip_place_id != null){
                $listApartments = $this->apartmentRepo->findByPlaceId($this->building_active_id, $request->ip_place_id);
                if(count($listApartments) > 0) $planceId = $listApartments->toArray();
            }

            $filterService = false;

            if((isset($request->service) && $request->service != null) || isset($request->type_service) && $request->type_service != null){
                $listSevices = ApartmentServicePriceRepository::getServicePriceByServiceIdV2($request);
                if($listSevices) $filterService = $listSevices->pluck('id')->toArray();
            }

            $isMoreCycleName = false;
            if(isset($data['filter']['cycle_name_more'])) $isMoreCycleName = true;

            $getServiceApartments = $debitRepo::getAllApartmentDetailLastTime3Export($this->building_active_id,$isMoreCycleName ? $request->cycle_name_more : $request->cycle_name,$planceId,$request->bdc_apartment_id,isset($data['filter']['du_no_cuoi_ky']) ? $data['filter']['du_no_cuoi_ky'] : false, $filterService);

            if(!$getServiceApartments){
                return redirect()->back();
            }
            $get_building = Building::get_detail_building_by_building_id($this->building_active_id); // lấy ra dự án
            $result = Excel::create('Hóa đơn tổng hợp', function ($excel) use ($getServiceApartments,$request,$page, $isMoreCycleName, $get_building) {
                $excel->setTitle('Hóa đơn tổng hợp');
                $excel->sheet('danh sách', function ($sheet) use ($getServiceApartments,$request,$page, $isMoreCycleName, $get_building) {
                    $row = 12;
                    $sheet->getStyle('A12')->getAlignment()->setWrapText(true);
                    $sheet->cells('A12', function ($cells) {
                        $cells->setFontSize(11);
                        $cells->setFontWeight('bold');
                        $cells->setValue('STT');
                        $cells->setValignment('center');
                        $cells->setAlignment('center');
                        $cells->setBorder('thin', 'thin', 'thin', 'thin');
                    });
                    $sheet->getStyle('B12')->getAlignment()->setWrapText(true);
                    $sheet->cells('B12', function ($cells) {
                        $cells->setFontSize(11);
                        $cells->setFontWeight('bold');
                        $cells->setValue('Tên KH');
                        $cells->setValignment('center');
                        $cells->setAlignment('center');
                        $cells->setBorder('thin', 'thin', 'thin', 'thin');
                    });
                    $sheet->getStyle('C12')->getAlignment()->setWrapText(true);
                    $sheet->cells('C12', function ($cells) {
                        $cells->setFontSize(11);
                        $cells->setFontWeight('bold');
                        $cells->setValue('Căn hộ');
                        $cells->setValignment('center');
                        $cells->setAlignment('center');
                        $cells->setBorder('thin', 'thin', 'thin', 'thin');
                    });
                    $sheet->getStyle('D12')->getAlignment()->setWrapText(true);
                    $sheet->cells('D12', function ($cells) {
                        $cells->setFontSize(11);
                        $cells->setFontWeight('bold');
                        $cells->setValue('Mã sản phẩm');
                        $cells->setValignment('center');
                        $cells->setAlignment('center');
                        $cells->setBorder('thin', 'thin', 'thin', 'thin');
                    });
                    $sheet->getStyle('E12')->getAlignment()->setWrapText(true);
                    $sheet->cells('E12', function ($cells) {
                        $cells->setFontSize(11);
                        $cells->setFontWeight('bold');
                        $cells->setValue('Mã thu');
                        $cells->setValignment('center');
                        $cells->setAlignment('center');
                        $cells->setBorder('thin', 'thin', 'thin', 'thin');
                    });

                    $sheet->getStyle('F12')->getAlignment()->setWrapText(true);
                    $sheet->cells('F12', function ($cells) {
                        $cells->setFontSize(11);
                        $cells->setFontWeight('bold');
                        $cells->setValue('Dịch vụ');
                        $cells->setValignment('center');
                        $cells->setAlignment('center');
                        $cells->setBorder('thin', 'thin', 'thin', 'thin');
                    });

                    $sheet->getStyle('F12')->getAlignment()->setWrapText(true);
                    $sheet->cells('F12', function ($cells) {
                        $cells->setFontSize(11);
                        $cells->setFontWeight('bold');
                        $cells->setValue('Dịch vụ');
                        $cells->setValignment('center');
                        $cells->setAlignment('center');
                        $cells->setBorder('thin', 'thin', 'thin', 'thin');
                    });
                    $sheet->cells('G12', function ($cells) {
                        $cells->setFontSize(11);
                        $cells->setFontWeight('bold');
                        $cells->setValue('Số dư đầu kỳ');
                        $cells->setValignment('center');
                        $cells->setAlignment('center');
                        $cells->setBorder('thin', 'thin', 'thin', 'thin');
                    });
                    $sheet->cells('H12', function ($cells) {
                        $cells->setFontSize(11);
                        $cells->setFontWeight('bold');
                        $cells->setValue('Phát sinh trong kỳ');
                        $cells->setValignment('center');
                        $cells->setAlignment('center');
                        $cells->setBorder('thin', 'thin', 'thin', 'thin');
                    });
                    $sheet->cells('I12', function ($cells) {
                        $cells->setFontSize(11);
                        $cells->setFontWeight('bold');
                        $cells->setValue('Thanh toán');
                        $cells->setValignment('center');
                        $cells->setAlignment('center');
                        $cells->setBorder('thin', 'thin', 'thin', 'thin');
                    });
                    $sheet->cells('J12', function ($cells) {
                        $cells->setFontSize(11);
                        $cells->setFontWeight('bold');
                        $cells->setValue('Số dư cuối kỳ');
                        $cells->setValignment('center');
                        $cells->setAlignment('center');
                        $cells->setBorder('thin', 'thin', 'thin', 'thin');
                    });

                    $sheet->mergeCells('A6:J6');
                    $sheet->cells('A6', function ($cells) use ($request){
                        $cells->setFontSize(13);
                        $cells->setFontWeight('bold');
                        $cells->setValue('TỔNG HỢP CÔNG NỢ CHI TIẾT');
                        $cells->setValignment('center');
                        $cells->setAlignment('center');
                    });

                    $sheet->mergeCells('A7:J7');
                    $sheet->cells('A7', function ($cells) use ($request){
                        $cells->setFontSize(13);
                        $cells->setFontWeight('Iatalic');
                        $cells->setValue('Tháng: '.$request->cycle_name);
                        $cells->setAlignment('center');
                        $cells->setValignment('center');
                        if(isset($request->from_date) && $request->from_date !=null && isset($request->to_date) && $request->to_date !=null){
                            $cells->setFontSize(13);
                            $cells->setFontWeight('Iatalic');
                            $cells->setValue('Từ: '.$request->from_date.' đến: '.$request->to_date );
                            $cells->setAlignment('center');
                            $cells->setValignment('center');
                        }
                    });

                    $sheet->cells('A2', function ($cells) {
                        $cells->setFontSize(11);
                        $cells->setFontWeight('bold');
                        $cells->setValue('Đơn vị:');
                    });

                    $sheet->cells('A3', function ($cells) {
                        $cells->setFontSize(11);
                        $cells->setFontWeight('bold');
                        $cells->setValue('Địa chỉ:');
                    });

                    $sheet->cells('B2', function ($cells) use ($get_building) {
                        $cells->setFontSize(11);
                        $cells->setValue($get_building->name);
                    });

                    $sheet->cells('B3', function ($cells) use ($get_building) {
                        $cells->setFontSize(11);
                        $cells->setValue($get_building->address);
                    });
                    if($getServiceApartments){
                        foreach ($getServiceApartments as $key => $value) {
                            $row++;
                            $_customer = CustomersRespository::findApartmentIdV2($value->bdc_apartment_id, 0);
                            $pubUserProfile = @$_customer ? PublicUsersProfileRespository::getInfoUserById($_customer->user_info_id) : null;
                            $apartment = Apartments::get_detail_apartment_by_apartment_id($value->bdc_apartment_id);
                            $servicePrice = ApartmentServicePriceRepository::getInfoServiceApartmentById($value->bdc_apartment_service_price_id);

                            if($value->bdc_apartment_service_price_id == 0){
                                $service = (object) ["code_receipt" =>"","name"=>"Tiền thừa"];
                            }else{
                                $service = Service::get_detail_bdc_service_by_bdc_service_id($servicePrice->bdc_service_id);
                            }
                            $Vehicles = @$servicePrice->bdc_vehicle_id > 0 ? Vehicles::get_detail_vehicle_by_id($servicePrice->bdc_vehicle_id) : null;

                            if($isMoreCycleName){
                                $phatsinh = DebitDetailRepository2::getTotalSumeryByCycleNameMoreApartmentServiceCus($this->building_active_id,$request->bdc_apartment_id ? $request->bdc_apartment_id : false,$value->bdc_apartment_service_price_id,$request->cycle_name,$request->cycle_name_more);
                                $dau_ky = DebitDetailRepository2::getTotalSumeryByCycleNameApartmentServiceCus($this->building_active_id, $request->bdc_apartment_id ? $request->bdc_apartment_id : false, $value->bdc_apartment_service_price_id, $request->cycle_name,"<");
                                $data = [
                                    (string) ($key + 1),
                                    (string) isset($pubUserProfile) ? $pubUserProfile->full_name : '',// lấy ra chủ hộ
                                    $apartment ? (string) $apartment->name : null,
                                    $apartment ? (string) $apartment->code : null,
                                    (string) $service->code_receipt,
                                    $Vehicles ? (string) $service->name .'-'.$Vehicles->number : (string) $service->name,
                                    (string) (!isset($dau_ky->tong_thanh_toan_ky) ? 0 : $dau_ky->tong_phat_sinh-$dau_ky->tong_thanh_toan_ky),
                                    (string) ($phatsinh->tong_phat_sinh ?? 0),
                                    (string) ($phatsinh->tong_thanh_toan_ky ?? 0),
                                    (string) $value->after_cycle_name,
                                ];
                            }else{
                                $getBill = \App\Repositories\BdcBills\V2\BillRepository::getBillById($value->bdc_bill_id);
                                $checkBill = false;
                                if($getBill){
                                    if( $getBill->status >= -2) $checkBill = true;
                                }
                                if(!$checkBill){
                                    $value->sumery = 0;
                                }
                                $data = [
                                    (string) ($key + 1),
                                    (string) isset($pubUserProfile) ? $pubUserProfile->full_name : '',// lấy ra chủ hộ
                                    $apartment ? (string) $apartment->name : null,
                                    $apartment ? (string) $apartment->code : null,
                                    (string) $service->code_receipt,
                                    $Vehicles ? (string) $service->name .'-'.$Vehicles->number : (string) $service->name,
                                    (string) ($value->cycle_name == $request->cycle_name ? $value->before_cycle_name : $value->after_cycle_name),
                                    (string) ($value->cycle_name == $request->cycle_name ? $value->sumery : 0),
                                    (string) ($value->cycle_name == $request->cycle_name ? $value->paid_by_cycle_name : 0),
                                    (string) $value->after_cycle_name,
                                ];
                            }

                            $sheet->row($row, $data);
                        }
                    }

                    $total_row_last = ($row + 1);
                    $range_new_last = 'A13:J' . $total_row_last;
                    $sheet->getStyle($range_new_last)->applyFromArray(
                        array(
                            'borders' => array(
                                'allborders' => array(
                                    'style' => \PHPExcel_Style_Border::BORDER_THIN,
                                    'color' => array('rgb' => 'FFFF0000'),
                                )
                            ),
                            'font' => [
                                'size' => 11
                            ]
                        )
                    );

                    $sheet->cells('A12:J12', function ($cells) {
                        $cells->setBackground('#cfe2f3');
                        $cells->setValignment('center');
                        $cells->setAlignment('center');
                    });

                    $sheet->cells('A' . ($row + 1), function ($cells) {
                        $cells->setFontSize(11);
                        $cells->setFontWeight('bold');
                        $cells->setValue('Tổng');
                    });

                    $attrs = [];
                    $attrs['t'] = 'array';

                    $arrSum = ["G","H","J","I"];

                    foreach ($arrSum as $item){
                        $sheet->cells($item.($row + 1), function($cells) use ($sheet, $row) {
                            $cells->setFontSize(11);
                            $cells->setFontWeight('bold');
                        });
                        $sheet->getCell($item.($row + 1))->setFormulaAttributes($attrs);
                        $sheet->setCellValue($item.($row + 1),'=SUM('.$item.'13:'.$item.$row.')');
                    }


                    $sheet->setColumnFormat(array(
                        'G13:J'.($row + 1) => "#,##0",
                        'C13:C500' => \PHPExcel_Style_NumberFormat::FORMAT_TEXT,
                    ));
                    $sheet->setHeight(array(
                        6     =>  50,
                        7     =>  40,
                        8     =>  5,
                        9     =>  5,
                        10     =>  5,
                        11     =>  5,
                    ));


                    $sheet->setAutoSize(true);


                    $sheet->setWidth(array(
                        'A'     =>  10,
                        'B'     =>  20,
                        'C'     =>  30,
                        'D'     =>  20,
                        'F'     =>  40,
                        'G'     =>  15,
                        'H'     =>  15,
                        'I'     =>  15,
                        'J'     =>  15,
                    ));
                });
            })->store('xlsx',storage_path('exports/'));
            ob_end_clean();
            $file     = storage_path('exports/'.$result->filename.'.'.$result->ext);
            return response()->download($file)->deleteFileAfterSend(true);
        }catch (Exception $e){
            return redirect()->back()->with('warning','đã có lỗi xảy ra.');
        }

             
    }
    public function exportExcelGeneralDetailTotal(Request $request, DebitDetailRepository2 $debitRepo)
    {
        $data['meta_title'] = 'Chi tiết công nợ tổng hợp';
        $data['filter'] = $request->all();        
        $data['per_page'] = Cookie::get('per_page', 10);
        $page = @$request->page ? $request->page : 1;

        $isMoreCycleName = false;
        if(isset($data['filter']['cycle_name_more'])) $isMoreCycleName = true;

        $getServiceApartments = $debitRepo::getAllApartmentLastTimeExport($this->building_active_id,$isMoreCycleName ? $request->cycle_name_more : $request->cycle_name,$request->bdc_apartment_id,isset($data['filter']['du_no_cuoi_ky']) ? $data['filter']['du_no_cuoi_ky'] : false);

        if(!$getServiceApartments){
            return redirect()->back();
        }
        $get_building = Building::get_detail_building_by_building_id($this->building_active_id); // lấy ra dự án

        $result = Excel::create('Hóa Đơn Tổng Hợp', function ($excel) use ($getServiceApartments,$request,$page,$isMoreCycleName,$get_building) {
            $excel->setTitle('Hóa Đơn Tổng Hợp');
            $excel->sheet('danh sách', function ($sheet) use ($getServiceApartments,$request,$page,$isMoreCycleName, $get_building) {
                $row = 12;
                $sheet->getStyle('A12')->getAlignment()->setWrapText(true);
                $sheet->cells('A12', function ($cells) {
                    $cells->setFontSize(11);
                    $cells->setFontWeight('bold');
                    $cells->setValue('STT');
                    $cells->setValignment('center');
                    $cells->setAlignment('center');
                    $cells->setBorder('thin', 'thin', 'thin', 'thin');
                });
                $sheet->getStyle('B12')->getAlignment()->setWrapText(true);
                $sheet->cells('B12', function ($cells) {
                    $cells->setFontSize(11);
                    $cells->setFontWeight('bold');
                    $cells->setValue('Mã KH/NCC');
                    $cells->setValignment('center');
                    $cells->setAlignment('center');
                    $cells->setBorder('thin', 'thin', 'thin', 'thin');
                });
                $sheet->getStyle('C12')->getAlignment()->setWrapText(true);
                $sheet->cells('C12', function ($cells) {
                    $cells->setFontSize(11);
                    $cells->setFontWeight('bold');
                    $cells->setValue('Tên KH/NCC');
                    $cells->setValignment('center');
                    $cells->setAlignment('center');
                    $cells->setBorder('thin', 'thin', 'thin', 'thin');
                });
                $sheet->getStyle('D12')->getAlignment()->setWrapText(true);
                $sheet->cells('D12', function ($cells) {
                    $cells->setFontSize(11);
                    $cells->setFontWeight('bold');
                    $cells->setValue('Căn hộ');
                    $cells->setValignment('center');
                    $cells->setAlignment('center');
                    $cells->setBorder('thin', 'thin', 'thin', 'thin');
                });
                $sheet->getStyle('E12')->getAlignment()->setWrapText(true);
                $sheet->cells('E12', function ($cells) {
                    $cells->setFontSize(11);
                    $cells->setFontWeight('bold');
                    $cells->setValue('Mã sản phẩm');
                    $cells->setValignment('center');
                    $cells->setAlignment('center');
                    $cells->setBorder('thin', 'thin', 'thin', 'thin');
                });

                $sheet->getStyle('F12')->getAlignment()->setWrapText(true);
                $sheet->cells('F12', function ($cells) {
                    $cells->setFontSize(11);
                    $cells->setFontWeight('bold');
                    $cells->setValue('Mã dự án');
                    $cells->setValignment('center');
                    $cells->setAlignment('center');
                    $cells->setBorder('thin', 'thin', 'thin', 'thin');
                });

                $sheet->getStyle('G12')->getAlignment()->setWrapText(true);
                $sheet->cells('G12', function ($cells) {
                    $cells->setFontSize(11);
                    $cells->setFontWeight('bold');
                    $cells->setValue('Tên dự án');
                    $cells->setAlignment('center');
                    $cells->setBorder('thin', 'thin', 'thin', 'thin');
                });

                $sheet->getStyle('H12')->getAlignment()->setWrapText(true);
                $sheet->cells('H12', function ($cells) {
                    $cells->setFontSize(11);
                    $cells->setFontWeight('bold');
                    $cells->setValue('Số dư đầu kỳ');
                    $cells->setAlignment('center');
                    $cells->setBorder('thin', 'thin', 'thin', 'thin');
                });

                $sheet->getStyle('I12')->getAlignment()->setWrapText(true);
                $sheet->cells('I12', function ($cells) {
                    $cells->setFontSize(11);
                    $cells->setFontWeight('bold');
                    $cells->setValue('Phát sinh');
                    $cells->setAlignment('center');
                    $cells->setBorder('thin', 'thin', 'thin', 'thin');
                });

                $sheet->getStyle('J12')->getAlignment()->setWrapText(true);
                $sheet->cells('J12', function ($cells) {
                    $cells->setFontSize(11);
                    $cells->setFontWeight('bold');
                    $cells->setValue('Thanh Toán');
                    $cells->setAlignment('center');
                    $cells->setBorder('thin', 'thin', 'thin', 'thin');
                });

                $sheet->getStyle('K12')->getAlignment()->setWrapText(true);
                $sheet->cells('K12', function ($cells) {
                    $cells->setFontSize(11);
                    $cells->setFontWeight('bold');
                    $cells->setValue('số dư cuối kỳ');
                    $cells->setAlignment('center');
                    $cells->setBorder('thin', 'thin', 'thin', 'thin');
                });

                $sheet->mergeCells('A6:K6');
                $sheet->cells('A6', function ($cells) use($request) {
                    $cells->setFontSize(13);
                    $cells->setFontWeight('bold');
                    $cells->setValue('TỔNG HỢP CÔNG NỢ');
                    $cells->setAlignment('center');
                    $cells->setValignment('center');
//                    $cells->setBorder('thin', 'thin', 'thin', 'thin');
                });

                $sheet->mergeCells('A7:K7');
                $sheet->cells('A7', function ($cells) use($request) {
                    $cells->setFontSize(11);
                    $cells->setFontWeight('Iatalic');
                    $cells->setValue('Tháng: '.$request->cycle_name);
                    $cells->setAlignment('center');
                    $cells->setValignment('center');
                    if(isset($request->from_date) && $request->from_date !=null && isset($request->to_date) && $request->to_date !=null){
                        $cells->setFontSize(13);
                        $cells->setFontWeight('Iatalic');
                        $cells->setValue('Từ: '.$request->from_date.' đến: '.$request->to_date );
                        $cells->setAlignment('center');
                        $cells->setValignment('center');
                    }
                });

                $sheet->cells('A2', function ($cells) {
                    $cells->setFontSize(11);
                    $cells->setFontWeight('bold');
                    $cells->setValue('Đơn vị:');
                });

                $sheet->cells('A3', function ($cells) {
                    $cells->setFontSize(11);
                    $cells->setFontWeight('bold');
                    $cells->setValue('Địa chỉ:');
                });
                $sheet->cells('B2', function ($cells) use ($get_building) {
                    $cells->setFontSize(11);
                    $cells->setValue($get_building->name);
                });

                $sheet->cells('B3', function ($cells) use ($get_building) {
                    $cells->setFontSize(11);
                    $cells->setValue($get_building->address);
                });
                if($getServiceApartments){
                    foreach ($getServiceApartments as $key => $value) {
                        $row++;
                        $building = Building::get_detail_building_by_building_id($value->bdc_building_id); // lấy ra dự án
                        $apartment = Apartments::get_detail_apartment_by_apartment_id($value->bdc_apartment_id);
                        $_customer = CustomersRespository::findApartmentIdV2($value->bdc_apartment_id, 0);
                        $pubUserProfile = @$_customer ? PublicUsersProfileRespository::getInfoUserById($_customer->user_info_id) : null;
                        if($isMoreCycleName){
                            $tempSumDauKy = isset($request->cycle_name) && $request->cycle_name != null ? DebitDetailRepository2::getTotalSumeryByCycleNameCus($this->building_active_id,$value->bdc_apartment_id,$request->cycle_name,"<",$request->to_date,$request->from_date) : 0;
                            $tempSumDauKy_tt = isset($request->cycle_name) && $request->cycle_name != null ? DebitDetailRepository2::getTotalSumeryByCycleNameCusNotStatusBill($this->building_active_id,$value->bdc_apartment_id,$request->cycle_name,"<",$request->to_date,$request->from_date) : 0;
                            $tempSumTrongKy = DebitDetailRepository2::getTotalSumeryByMoreCycleNameCus1($this->building_active_id,$value->bdc_apartment_id,$request->cycle_name,$request->cycle_name_more);
                            $tempSumTrongKy_tt = DebitDetailRepository2::getTotalSumeryByMoreCycleNameCusNotStatusBill1($this->building_active_id,$value->bdc_apartment_id,$request->cycle_name,$request->cycle_name_more);
                            $dauky = isset($tempSumDauKy_tt->tong_thanh_toan_ky) ? $tempSumDauKy->tong_phat_sinh - $tempSumDauKy_tt->tong_thanh_toan_ky : 0;
                            $trong_ky = !isset($tempSumTrongKy->tong_phat_sinh) ? 0 : $tempSumTrongKy->tong_phat_sinh;
                            $thanh_toan = !isset($tempSumTrongKy_tt->tong_thanh_toan_ky) ? 0 : $tempSumTrongKy_tt->tong_thanh_toan_ky;
                            $cuoi_ky = $dauky + $trong_ky - $thanh_toan;

                            $detail = [
                                'id' => (string) ($key +1) ,
                                'ma_khach_hang' => (string) (isset($apartment) ? $apartment->code_customer : ''),
                                'ten_khach_hang' => (string) ($pubUserProfile ? $pubUserProfile->full_name : null),
                                'can_ho' => (string) ($apartment ? $apartment->name : null),
                                'ma_san_pham' => (string) ($apartment ? $apartment->code : null),
                                'ma_du_an' => (string) ($building ? $building->building_code_manage : null),
                                'ten_du_an' => (string) $building->name,
                                'dau_ky' =>  (string) $dauky,
                                'trong_ky' => (string) $trong_ky,
                                'thanh_toan' => (string) $thanh_toan,
                                'cuoi_ky' =>  (string) $cuoi_ky,
                            ];
                        }else {
                            $tempSumDauKy = isset($request->cycle_name) && $request->cycle_name != null ? DebitDetailRepository2::getTotalSumeryByCycleNameCus($this->building_active_id, $value->bdc_apartment_id, $request->cycle_name, "<",$request->to_date,$request->from_date) : 0;
                            $tempSumDauKy_tt = isset($request->cycle_name) && $request->cycle_name != null ? DebitDetailRepository2::getTotalSumeryByCycleNameCusNotStatusBill($this->building_active_id, $value->bdc_apartment_id, $request->cycle_name, "<",$request->to_date,$request->from_date) : 0;
                            $tempSumTrongKy = DebitDetailRepository2::getTotalSumeryByCycleNameCus($this->building_active_id, $value->bdc_apartment_id, $request->cycle_name, "=",$request->to_date,$request->from_date);
                            $tempSumTrongKy_tt = DebitDetailRepository2::getTotalSumeryByCycleNameCusNotStatusBill($this->building_active_id, $value->bdc_apartment_id, $request->cycle_name, "=",$request->to_date,$request->from_date);
                            $dauky = isset($tempSumDauKy_tt->tong_thanh_toan_ky) ? $tempSumDauKy->tong_phat_sinh - $tempSumDauKy_tt->tong_thanh_toan_ky : 0;
                            $trong_ky = !isset($tempSumTrongKy->tong_phat_sinh) ? 0 : $tempSumTrongKy->tong_phat_sinh;
                            $thanh_toan = !isset($tempSumTrongKy_tt->tong_thanh_toan_ky) ? 0 : $tempSumTrongKy_tt->tong_thanh_toan_ky;
                            $cuoi_ky = $dauky + $trong_ky - $thanh_toan;

                            $detail = [
                                'id' => (string) ($key + 1) ,
                                'ma_khach_hang' => (string) isset($apartment) ? $apartment->code_customer : '',
                                'ten_khach_hang' => $pubUserProfile ? (string) $pubUserProfile->full_name : null,
                                'can_ho' => $apartment ? (string) $apartment->name : null,
                                'ma_san_pham' => ($apartment ? (string) $apartment->code : null),
                                'ma_du_an' => ($building ? (string) $building->building_code_manage : null),
                                'ten_du_an' => (string) $building->name,
                                'dau_ky' => (string) $dauky,
                                'trong_ky' => (string) $trong_ky,
                                'thanh_toan' => (string) $thanh_toan,
                                'cuoi_ky' => (string) $cuoi_ky,
                            ];
                        }
                        $sheet->row($row, $detail);
                    }
                }


                $sheet->cells('A' . ($row + 1), function ($cells) {
                    $cells->setFontSize(11);
                    $cells->setFontWeight('bold');
                    $cells->setValue('Tổng');
                });

                $attrs = [];
                $attrs['t'] = 'array';

                $arrSum = ["H","J","I","K"];

                foreach ($arrSum as $item){
                    $sheet->cells($item.($row + 1), function($cells) use ($sheet, $row) {
                        $cells->setFontSize(11);
                        $cells->setFontWeight('bold');
                    });
                    $sheet->getCell($item.($row + 1))->setFormulaAttributes($attrs);
                    $sheet->setCellValue($item.($row + 1),'=SUM('.$item.'13:'.$item.$row.')');
                }

                $sheet->cells('A12:K12', function ($cells) {
                    $cells->setBackground('#cfe2f3');
                    $cells->setValignment('center');
                    $cells->setAlignment('center');
                });

                $sheet->setHeight(array(
                    6     =>  50,
                    7     =>  40,
                    8     =>  5,
                    9     =>  5,
                    10     =>  5,
                    11     =>  5,
                    12     =>  30,
                ));


                $sheet->setAutoSize(true);

                $sheet->setWidth(array(
                    'A'     =>  10,
                    'B'     =>  20,
                    'C'     =>  30,
                    'D'     =>  20,
                    'F'     =>  15,
                    'G'     =>  20,
                    'H'     =>  15,
                    'I'     =>  15,
                    'J'     =>  15,
                    'K'     =>  15,
                ));

                $sheet->setColumnFormat(array(
                    'A12:L'.($row + 1) => "#,##0",
                    'D13:D500' => \PHPExcel_Style_NumberFormat::FORMAT_TEXT,
                ));

                $total_row_last = ($row + 1);
                $range_new_last = 'A12:K' . $total_row_last;
                $sheet->getStyle($range_new_last)->applyFromArray(
                    array(
                        'borders' => array(
                            'allborders' => array(
                                'style' => \PHPExcel_Style_Border::BORDER_THIN,
                                'color' => array('rgb' => 'FFFF0000'),
                            )
                        ),
                        'font' => [
                            'size' => 11
                        ]
                    )
                );
            });
        })->store('xlsx',storage_path('exports/'));
        ob_end_clean();
        $file     = storage_path('exports/'.$result->filename.'.'.$result->ext);
        return response()->download($file)->deleteFileAfterSend(true);
                    
    }
    public function exportExcelGeneralDetailTotalByTypeApartment(Request $request, DebitDetailRepository2 $debitRepo)
    {
        $data['meta_title'] = 'Chi tiết công nợ tổng hợp';
        $data['filter'] = $request->all();        
        $planceId = false;

        if(isset($request->ip_place_id) && $request->ip_place_id != null){
            $listApartments = $this->apartmentRepo->findByPlaceId($this->building_active_id, $request->ip_place_id);
            if(count($listApartments) > 0) $planceId = $listApartments->toArray();
        }

        $filterService = false;

        if((isset($request->service) && $request->service != null) || isset($request->type_service) && $request->type_service != null){
            $listSevices = ApartmentServicePriceRepository::getServicePriceByServiceIdV2($request);
            if($listSevices) $filterService = $listSevices->pluck('id')->toArray();
        }

        $isMoreCycleName = false;
        if(isset($data['filter']['cycle_name_more'])) $isMoreCycleName = true;

        $getServiceApartments = $debitRepo::getAllApartmentDetailLastTime3Export($this->building_active_id,$isMoreCycleName ? $request->cycle_name_more : $request->cycle_name,$planceId,$request->bdc_apartment_id,isset($data['filter']['du_no_cuoi_ky']) ? $data['filter']['du_no_cuoi_ky'] : false, $filterService);

        if(!$getServiceApartments){
            return redirect()->back();
        }
        $result = Excel::create('Hóa đơn tổng hợp ', function ($excel) use ($getServiceApartments,$request,$isMoreCycleName) {
            $excel->setTitle('Hóa đơn tổng hợp ');
            $excel->sheet('danh sách', function ($sheet) use ($getServiceApartments,$request,$isMoreCycleName) {
                $row = 4;
                $sheet->mergeCells('A1:I1');
                $sheet->getStyle('A1')->getAlignment()->setWrapText(true);
                $sheet->cells('A1', function ($cells) {
                    $cells->setFontSize(18);
                    $cells->setFontWeight('bold');
                    $cells->setValue('TỔNG HỢP CÔNG NỢ');
                    $cells->setValignment('center');
                    $cells->setAlignment('center');
                });
                $sheet->mergeCells('A2:I2');
                $sheet->getStyle('A2')->getAlignment()->setWrapText(true);
                $sheet->cells('A2', function ($cells)use($request){
                    $year = substr($request->cycle_name, 0, -2);
                    $month = substr($request->cycle_name, 4);
                    $cells->setFontSize(11);
                    $cells->setFontWeight('bold');
                    $cells->setValue('Tài khoản:<<Tất cả>>;Loại tiền: VND; Tháng'." $month năm $year");
                    $cells->setValignment('center');
                    $cells->setAlignment('center');
                });
                $sheet->mergeCells('A3:A4');
                $sheet->getStyle('A3')->getAlignment()->setWrapText(true);
                $sheet->cells('A3', function ($cells) {
                    $cells->setFontSize(11);
                    $cells->setFontWeight('bold');
                    $cells->setValue('Mã khách hàng');
                    $cells->setValignment('center');
                    $cells->setAlignment('center');
                    $cells->setBorder('thin', 'thin', 'thin', 'thin');
                });
                $sheet->mergeCells('B3:B4');
                $sheet->getStyle('B3')->getAlignment()->setWrapText(true);
                $sheet->cells('B3', function ($cells) {
                    $cells->setFontSize(11);
                    $cells->setFontWeight('bold');
                    $cells->setValue('Tên dịch vụ');
                    $cells->setValignment('center');
                    $cells->setAlignment('center');
                    $cells->setBorder('thin', 'thin', 'thin', 'thin');
                });

                $sheet->mergeCells('C3:C4');
                $sheet->getStyle('C3')->getAlignment()->setWrapText(true);
                $sheet->cells('C3', function ($cells) {
                    $cells->setFontSize(11);
                    $cells->setFontWeight('bold');
                    $cells->setValue('Mã thu');
                    $cells->setValignment('center');
                    $cells->setAlignment('center');
                    $cells->setBorder('thin', 'thin', 'thin', 'thin');
                });

                $sheet->mergeCells('D3:E3');
                $sheet->getStyle('D3')->getAlignment()->setWrapText(true);
                $sheet->cells('D3', function ($cells) {
                    $cells->setFontSize(11);
                    $cells->setFontWeight('bold');
                    $cells->setValue('Số dư đầu kỳ');
                    $cells->setValignment('center');
                    $cells->setAlignment('center');
                    $cells->setBorder('thin', 'thin', 'thin', 'thin');
                });

                $sheet->cells('D4', function ($cells) {
                    $cells->setFontSize(11);
                    $cells->setFontWeight('bold');
                    $cells->setValue('Nợ');
                    $cells->setValignment('center');
                    $cells->setAlignment('center');
                    $cells->setBorder('thin', 'thin', 'thin', 'thin');
                });

                $sheet->cells('E4', function ($cells) {
                    $cells->setFontSize(11);
                    $cells->setFontWeight('bold');
                    $cells->setValue('Có');
                    $cells->setValignment('center');
                    $cells->setAlignment('center');
                    $cells->setBorder('thin', 'thin', 'thin', 'thin');
                });


                $sheet->mergeCells('F3:G3');
                $sheet->getStyle('F3')->getAlignment()->setWrapText(true);
                $sheet->cells('F3', function ($cells) {
                    $cells->setFontSize(11);
                    $cells->setFontWeight('bold');
                    $cells->setValue('Số phát sinh');
                    $cells->setValignment('center');
                    $cells->setAlignment('center');
                    $cells->setBorder('thin', 'thin', 'thin', 'thin');
                });

                $sheet->cells('F4', function ($cells) {
                    $cells->setFontSize(11);
                    $cells->setFontWeight('bold');
                    $cells->setValue('Nợ');
                    $cells->setValignment('center');
                    $cells->setAlignment('center');
                    $cells->setBorder('thin', 'thin', 'thin', 'thin');
                });

                $sheet->cells('G4', function ($cells) {
                    $cells->setFontSize(11);
                    $cells->setFontWeight('bold');
                    $cells->setValue('Có');
                    $cells->setValignment('center');
                    $cells->setAlignment('center');
                    $cells->setBorder('thin', 'thin', 'thin', 'thin');
                });


                $sheet->mergeCells('H3:I3');
                $sheet->getStyle('H3')->getAlignment()->setWrapText(true);
                $sheet->cells('H3', function ($cells) {
                    $cells->setFontSize(11);
                    $cells->setFontWeight('bold');
                    $cells->setValue('Số dư cuối kỳ');
                    $cells->setValignment('center');
                    $cells->setAlignment('center');
                    $cells->setBorder('thin', 'thin', 'thin', 'thin');
                });
              
                $sheet->cells('H4', function ($cells) {
                    $cells->setFontSize(11);
                    $cells->setFontWeight('bold');
                    $cells->setValue('Nợ');
                    $cells->setValignment('center');
                    $cells->setAlignment('center');
                    $cells->setBorder('thin', 'thin', 'thin', 'thin');
                });

                $sheet->cells('I4', function ($cells) {
                    $cells->setFontSize(11);
                    $cells->setFontWeight('bold');
                    $cells->setValue('Có');
                    $cells->setValignment('center');
                    $cells->setAlignment('center');
                    $cells->setBorder('thin', 'thin', 'thin', 'thin');
                });

                $sheet->cells('A3:I4', function ($cells) {
                    $cells->setBackground('#C5D9F1');
                });
                $sheet->setColumnFormat([
                    'D'=>'#,##0',
                    'E'=>'#,##0',
                    'F'=>'#,##0',
                    'G'=>'#,##0',
                    'H'=>'#,##0',
                    'I'=>'#,##0',
                    'J'=>'#,##0',
                    'K'=>'#,##0'
                ]);
                if($getServiceApartments){
                    $apartmentId = null;
                    $count_apartment=0;
                    $total_dau_ky_no=0;
                    $total_dau_ky_co=0;
                    $total_trong_ky_no=0;
                    $total_trong_ky_co=0;
                    $total_cuoi_ky_no=0;
                    $total_cuoi_ky_co=0;

                    $total_all_dau_ky_no=0;
                    $total_all_dau_ky_co=0;
                    $total_all_trong_ky_no=0;
                    $total_all_trong_ky_co=0;
                    $total_all_cuoi_ky_no=0;
                    $total_all_cuoi_ky_co=0;
                    foreach ($getServiceApartments as $key => $value) {
                       
                        $apartment = Apartments::get_detail_apartment_by_apartment_id($value->bdc_apartment_id);
                        $servicePrice = ApartmentServicePriceRepository::getInfoServiceApartmentById($value->bdc_apartment_service_price_id);

                        if($value->bdc_apartment_service_price_id == 0){ // tiền thừa không chỉ định
                            $service = (object) ["code_receipt" =>"","name"=>"Tiền thừa"];
                        }else{
                            $service = Service::get_detail_bdc_service_by_bdc_service_id($servicePrice->bdc_service_id);
                        }

                        if($isMoreCycleName){
                            $phatsinh = DebitDetailRepository2::getTotalSumeryByCycleNameMoreApartmentServiceCus($this->building_active_id,$request->bdc_apartment_id ? $request->bdc_apartment_id : false,$value->bdc_apartment_service_price_id,$request->cycle_name,$request->cycle_name_more);
                            $dau_ky = DebitDetailRepository2::getTotalSumeryByCycleNameApartmentServiceCus($this->building_active_id, $request->bdc_apartment_id ? $request->bdc_apartment_id : false, $value->bdc_apartment_service_price_id, $request->cycle_name,"<");
                            $dau_ky = (!isset($dau_ky->tong_thanh_toan_ky) ? 0 : $dau_ky->tong_phat_sinh-$dau_ky->tong_thanh_toan_ky);
                            $phat_sinh = $phatsinh->tong_phat_sinh ?? 0;
                            $thanh_toan = $phatsinh->tong_thanh_toan_ky ?? 0;
                            $cuoi_ky = $value->after_cycle_name;

                            $data = [
                                $apartment ? (string) $apartment->code : null, // mã code căn hộ 
                                $service ? (string) @$service->name : null, // tên căn hộ 
                                (string) $service->code_receipt, // mã dịch vụ
                                (string) $dau_ky > 0 ? $dau_ky : null,
                                (string) $dau_ky < 0 ? $dau_ky : null,
                                (string) $phatsinh,
                                (string) $thanh_toan,
                                (string) $cuoi_ky > 0 ? $cuoi_ky : null,
                                (string) $cuoi_ky < 0 ? $cuoi_ky : null
                            ];
                         
                        }else{
                            $getBill = Bills::get_detail_bill_by_apartment_id($value->bdc_bill_id);
                            $checkBill = false;
                            if($getBill){
                                if( $getBill->status >= -2) $checkBill = true;
                            }
                            if(!$checkBill){
                                $value->sumery = 0;
                            }
                            $dau_ky = ($value->cycle_name == $request->cycle_name ? $value->before_cycle_name : $value->after_cycle_name);
                            $phat_sinh = ($value->cycle_name == $request->cycle_name ? $value->sumery : 0);
                            $thanh_toan = ($value->cycle_name == $request->cycle_name ? $value->paid_by_cycle_name : 0);
                            $cuoi_ky = $value->after_cycle_name;
                            $data = [
                                $apartment ? (string) $apartment->code : null,// mã code căn hộ 
                                $service ? (string) @$service->name : null, // tên căn hộ 
                                (string) $service->code_receipt,// mã dịch vụ
                                (string) $dau_ky > 0 ? abs($dau_ky)  : 0,
                                (string) $dau_ky < 0 ? abs($dau_ky) : 0,
                                (string) $phat_sinh,
                                (string) $thanh_toan,
                                (string) $cuoi_ky > 0 ? abs($cuoi_ky) : 0,
                                (string) $cuoi_ky < 0 ? abs($cuoi_ky) : 0
                            ];
                          
                        }
                        if($dau_ky == 0 && $phat_sinh ==0 && $thanh_toan ==0 && $cuoi_ky==0){
                            continue;
                        }
                        if ($apartmentId != $value->bdc_apartment_id) {
                            $apartment = Apartments::get_detail_apartment_by_apartment_id($apartmentId);
                            $apartmentId = $value->bdc_apartment_id;
                            if ($key != 0 && $apartment) {
                            
                                $range_row = 'A'.($row+1).':B'.($row+1);
                                $range_full_row = 'A'.($row+1).':I'.($row+1);
                                $sheet->cells($range_full_row, function ($cells) {
                                    $cells->setBackground('#C5D9F1');
                                });
                                $sheet->mergeCells($range_row);
                                $new_row = 'A'.($row+1);
                                $sheet->getStyle($new_row)->getAlignment()->setWrapText(true);
                                $sheet->cells($new_row, function ($cells) use($apartment,$count_apartment) {
                                    $cells->setFontSize(11);
                                    $cells->setFontWeight('bold');
                                    $cells->setValue('Tên khách hàng:'.@$apartment->name."($count_apartment)");
                                    $cells->setValignment('center');
                                    $cells->setAlignment('center');
                                });
                                $sheet->setCellValueByColumnAndRow(3, $row+1, $total_dau_ky_no);
                                $sheet->setCellValueByColumnAndRow(4, $row+1, $total_dau_ky_co);
                                $sheet->setCellValueByColumnAndRow(5, $row+1, $total_trong_ky_no);
                                $sheet->setCellValueByColumnAndRow(6, $row+1, $total_trong_ky_co);
                                $sheet->setCellValueByColumnAndRow(7, $row+1, $total_cuoi_ky_no);
                                $sheet->setCellValueByColumnAndRow(8, $row+1, $total_cuoi_ky_co);
                                $total_dau_ky_no=0;
                                $total_dau_ky_co=0;
                                $total_trong_ky_no=0;
                                $total_trong_ky_co=0;
                                $total_cuoi_ky_no=0;
                                $total_cuoi_ky_co=0;
                             
                                $count_apartment=0;
                               
                                $row++;
                            } 
                          
                        }
                        if($apartmentId == $value->bdc_apartment_id){
                            $sheet->getRowDimension($row+1)
                            ->setOutlineLevel(1)
                            ->setVisible(false)
                            ->setCollapsed(true);
                            $total_dau_ky_no += $dau_ky > 0 ? abs($dau_ky) : 0;
                            $total_dau_ky_co += $dau_ky < 0 ? abs($dau_ky) : 0;
                            $total_trong_ky_no += $phat_sinh;
                            $total_trong_ky_co += $thanh_toan;
                            $total_cuoi_ky_no += $cuoi_ky > 0 ? abs($cuoi_ky) : 0;
                            $total_cuoi_ky_co += $cuoi_ky < 0 ? abs($cuoi_ky) : 0;

                            $total_all_dau_ky_no+= $dau_ky > 0 ? abs($dau_ky) : 0;
                            $total_all_dau_ky_co+= $dau_ky < 0 ? abs($dau_ky) : 0;
                            $total_all_trong_ky_no+= $phat_sinh;
                            $total_all_trong_ky_co+= $thanh_toan;
                            $total_all_cuoi_ky_no+= $cuoi_ky > 0 ? abs($cuoi_ky) : 0;
                            $total_all_cuoi_ky_co+= $cuoi_ky < 0 ? abs($cuoi_ky) : 0;
                        }
                        $count_apartment++;
                        $row++;
                        $sheet->row($row, $data);
                    }
                    $apartment = Apartments::get_detail_apartment_by_apartment_id($apartmentId);
                    $range_row = 'A'.($row+1).':B'.($row+1);
                    $range_full_row = 'A'.($row+1).':I'.($row+1);
                    $sheet->cells($range_full_row, function ($cells) {
                        $cells->setBackground('#C5D9F1');
                    });
                    $sheet->mergeCells($range_row);
                    $new_row = 'A'.($row+1);
                    $sheet->getStyle($new_row)->getAlignment()->setWrapText(true);
                    $sheet->cells($new_row, function ($cells) use($apartment,$count_apartment) {
                        $cells->setFontSize(11);
                        $cells->setFontWeight('bold');
                        $cells->setValue('Tên khách hàng:'.@$apartment->name."($count_apartment)");
                        $cells->setValignment('center');
                        $cells->setAlignment('center');
                    });
                    $sheet->setCellValueByColumnAndRow(3, $row+1, $total_dau_ky_no);
                    $sheet->setCellValueByColumnAndRow(4, $row+1, $total_dau_ky_co);
                    $sheet->setCellValueByColumnAndRow(5, $row+1, $total_trong_ky_no);
                    $sheet->setCellValueByColumnAndRow(6, $row+1, $total_trong_ky_co);
                    $sheet->setCellValueByColumnAndRow(7, $row+1, $total_cuoi_ky_no);
                    $sheet->setCellValueByColumnAndRow(8, $row+1, $total_cuoi_ky_co);
                    $total_dau_ky_no=0;
                    $total_dau_ky_co=0;
                    $total_trong_ky_no=0;
                    $total_trong_ky_co=0;
                    $total_cuoi_ky_no=0;
                    $total_cuoi_ky_co=0;
                    $count_apartment=0;
                    $row++;
                    $range_row = 'A'.($row+1).':B'.($row+1);
                    $range_full_row = 'A'.($row+1).':I'.($row+1);
                    $sheet->cells($range_full_row, function ($cells) {
                        $cells->setBackground('#C5D9F1');
                    });
                    $sheet->mergeCells($range_row);
                    $new_row = 'A'.($row+1);
                    $new_row_c = 'C'.($row+1);
                    $sheet->getStyle($new_row)->getAlignment()->setWrapText(true);
                    $sheet->cells($new_row, function ($cells) {
                        $cells->setFontSize(11);
                        $cells->setFontWeight('bold');
                        $cells->setValue('Tổng');
                        $cells->setValignment('center');
                        $cells->setAlignment('center');
                    });
                    $sheet->cells($new_row_c, function ($cells) use($row) {
                        $cells->setFontSize(11);
                        $cells->setFontWeight('bold');
                        $cells->setValue('Số dòng:'.($row));
                        $cells->setValignment('center');
                        $cells->setAlignment('center');
                    });
                    $sheet->setCellValueByColumnAndRow(3, $row+1, $total_all_dau_ky_no);
                    $sheet->setCellValueByColumnAndRow(4, $row+1, $total_all_dau_ky_co);
                    $sheet->setCellValueByColumnAndRow(5, $row+1, $total_all_trong_ky_no);
                    $sheet->setCellValueByColumnAndRow(6, $row+1, $total_all_trong_ky_co);
                    $sheet->setCellValueByColumnAndRow(7, $row+1, $total_all_cuoi_ky_no);
                    $sheet->setCellValueByColumnAndRow(8, $row+1, $total_all_cuoi_ky_co);
                }
                $sheet->setWidth(array(
                    'A'     =>  15,
                    'B'     =>  15,
                    'C'     =>  15,
                    'D'     =>  15,
                    'E'     =>  15,
                    'F'     =>  15,
                    'G'     =>  15,
                    'H'     =>  15,
                    'I'     =>  15,
                    'J'     =>  15,
                    'K'     =>  15,
                ));

            });
        })->store('xlsx',storage_path('exports/'));
        ob_end_clean();
        $file     = storage_path('exports/'.$result->filename.'.'.$result->ext);
        return response()->download($file)->deleteFileAfterSend(true);
                    
    }
    public function exportExcelGeneralDetailTotalByTypeService(Request $request, DebitDetailRepository2 $debitRepo)
    {
        $data['meta_title'] = 'Chi tiết công nợ tổng hợp';
        $data['filter'] = $request->all();        
        $planceId = false;

        if(isset($request->ip_place_id) && $request->ip_place_id != null){
            $listApartments = $this->apartmentRepo->findByPlaceId($this->building_active_id, $request->ip_place_id);
            if(count($listApartments) > 0) $planceId = $listApartments->toArray();
        }

        $filterService = false;

        if((isset($request->service) && $request->service != null) || isset($request->type_service) && $request->type_service != null){
            $listSevices = ApartmentServicePriceRepository::getServicePriceByServiceIdV2($request);
            if($listSevices) $filterService = $listSevices->pluck('id')->toArray();
        }
      
        $isMoreCycleName = false;
        if(isset($data['filter']['cycle_name_more'])) $isMoreCycleName = true;

        $getServiceApartments = $debitRepo::getAllApartmentDetailLastTime3ExportByTypeService($this->building_active_id,$isMoreCycleName ? $request->cycle_name_more : $request->cycle_name,$planceId,$request->bdc_apartment_id,isset($data['filter']['du_no_cuoi_ky']) ? $data['filter']['du_no_cuoi_ky'] : false, $filterService);
        if(!$getServiceApartments){
            return redirect()->back();
        }
        $result = Excel::create('Hóa đơn tổng hợp ', function ($excel) use ($getServiceApartments,$request,$isMoreCycleName) {
            $excel->setTitle('Hóa đơn tổng hợp ');
            $excel->sheet('danh sách', function ($sheet) use ($getServiceApartments,$request,$isMoreCycleName) {
                $row = 4;
                $sheet->mergeCells('A1:H1');
                $sheet->getStyle('A1')->getAlignment()->setWrapText(true);
                $sheet->cells('A1', function ($cells) {
                    $cells->setFontSize(18);
                    $cells->setFontWeight('bold');
                    $cells->setValue('TỔNG HỢP CÔNG NỢ PHẢI THU');
                    $cells->setValignment('center');
                    $cells->setAlignment('center');
                });
                $sheet->mergeCells('A2:H2');
                $sheet->getStyle('A2')->getAlignment()->setWrapText(true);
                $sheet->cells('A2', function ($cells)use($request){
                    $year = substr($request->cycle_name, 0, -2);
                    $month = substr($request->cycle_name, 4);
                    $cells->setFontSize(11);
                    $cells->setFontWeight('bold');
                    $cells->setValue('Tài khoản:<<Tất cả>>;Loại tiền: VND; Tháng'." $month năm $year");
                    $cells->setValignment('center');
                    $cells->setAlignment('center');
                });
                $sheet->mergeCells('A3:A4');
                $sheet->getStyle('A3')->getAlignment()->setWrapText(true);
                $sheet->cells('A3', function ($cells) {
                    $cells->setFontSize(11);
                    $cells->setFontWeight('bold');
                    $cells->setValue('Mã dịch vụ');
                    $cells->setValignment('center');
                    $cells->setAlignment('center');
                    $cells->setBorder('thin', 'thin', 'thin', 'thin');
                });
                $sheet->mergeCells('B3:B4');
                $sheet->getStyle('B3')->getAlignment()->setWrapText(true);
                $sheet->cells('B3', function ($cells) {
                    $cells->setFontSize(11);
                    $cells->setFontWeight('bold');
                    $cells->setValue('Mã khách hàng');
                    $cells->setValignment('center');
                    $cells->setAlignment('center');
                    $cells->setBorder('thin', 'thin', 'thin', 'thin');
                });

                $sheet->mergeCells('C3:D3');
                $sheet->getStyle('C3')->getAlignment()->setWrapText(true);
                $sheet->cells('C3', function ($cells) {
                    $cells->setFontSize(11);
                    $cells->setFontWeight('bold');
                    $cells->setValue('Số dư đầu kỳ');
                    $cells->setValignment('center');
                    $cells->setAlignment('center');
                    $cells->setBorder('thin', 'thin', 'thin', 'thin');
                });

                $sheet->cells('C4', function ($cells) {
                    $cells->setFontSize(11);
                    $cells->setFontWeight('bold');
                    $cells->setValue('Nợ');
                    $cells->setValignment('center');
                    $cells->setAlignment('center');
                    $cells->setBorder('thin', 'thin', 'thin', 'thin');
                });

                $sheet->cells('D4', function ($cells) {
                    $cells->setFontSize(11);
                    $cells->setFontWeight('bold');
                    $cells->setValue('Có');
                    $cells->setValignment('center');
                    $cells->setAlignment('center');
                    $cells->setBorder('thin', 'thin', 'thin', 'thin');
                });


                $sheet->mergeCells('E3:F3');
                $sheet->getStyle('E3')->getAlignment()->setWrapText(true);
                $sheet->cells('E3', function ($cells) {
                    $cells->setFontSize(11);
                    $cells->setFontWeight('bold');
                    $cells->setValue('Số phát sinh');
                    $cells->setValignment('center');
                    $cells->setAlignment('center');
                    $cells->setBorder('thin', 'thin', 'thin', 'thin');
                });

                $sheet->cells('E4', function ($cells) {
                    $cells->setFontSize(11);
                    $cells->setFontWeight('bold');
                    $cells->setValue('Nợ');
                    $cells->setValignment('center');
                    $cells->setAlignment('center');
                    $cells->setBorder('thin', 'thin', 'thin', 'thin');
                });

                $sheet->cells('F4', function ($cells) {
                    $cells->setFontSize(11);
                    $cells->setFontWeight('bold');
                    $cells->setValue('Có');
                    $cells->setValignment('center');
                    $cells->setAlignment('center');
                    $cells->setBorder('thin', 'thin', 'thin', 'thin');
                });


                $sheet->mergeCells('G3:H3');
                $sheet->getStyle('G3')->getAlignment()->setWrapText(true);
                $sheet->cells('G3', function ($cells) {
                    $cells->setFontSize(11);
                    $cells->setFontWeight('bold');
                    $cells->setValue('Số dư cuối kỳ');
                    $cells->setValignment('center');
                    $cells->setAlignment('center');
                    $cells->setBorder('thin', 'thin', 'thin', 'thin');
                });
              
                $sheet->cells('G4', function ($cells) {
                    $cells->setFontSize(11);
                    $cells->setFontWeight('bold');
                    $cells->setValue('Nợ');
                    $cells->setValignment('center');
                    $cells->setAlignment('center');
                    $cells->setBorder('thin', 'thin', 'thin', 'thin');
                });

                $sheet->cells('H4', function ($cells) {
                    $cells->setFontSize(11);
                    $cells->setFontWeight('bold');
                    $cells->setValue('Có');
                    $cells->setValignment('center');
                    $cells->setAlignment('center');
                    $cells->setBorder('thin', 'thin', 'thin', 'thin');
                });

                $sheet->cells('A3:H4', function ($cells) {
                    $cells->setBackground('#C5D9F1');
                });
                $sheet->setColumnFormat([
                    'C'=>'#,##0',
                    'D'=>'#,##0',
                    'E'=>'#,##0',
                    'F'=>'#,##0',
                    'G'=>'#,##0',
                    'H'=>'#,##0',
                    'I'=>'#,##0',
                    'J'=>'#,##0',
                    'K'=>'#,##0'
                ]);
                if($getServiceApartments){
                    $serviceId = null;
                    $apartmentId = null;
                    $count_service_id=0;
                    $total_dau_ky_no=0;
                    $total_dau_ky_co=0;
                    $total_trong_ky_no=0;
                    $total_trong_ky_co=0;
                    $total_cuoi_ky_no=0;
                    $total_cuoi_ky_co=0;

                    $total_all_dau_ky_no=0;
                    $total_all_dau_ky_co=0;
                    $total_all_trong_ky_no=0;
                    $total_all_trong_ky_co=0;
                    $total_all_cuoi_ky_no=0;
                    $total_all_cuoi_ky_co=0;
                    // dd($getServiceApartments);
                    $check_excess_cash=null;
                    foreach ($getServiceApartments as $key => $value) {
                        
                        $apartment = Apartments::get_detail_apartment_by_apartment_id($value->bdc_apartment_id);
                      
                        $service = Service::get_detail_bdc_service_by_bdc_service_id($value->bdc_service_id);

                        if($isMoreCycleName){
                            $phatsinh = DebitDetailRepository2::getTotalSumeryByCycleNameMoreApartmentServiceCus($this->building_active_id,$request->bdc_apartment_id ? $request->bdc_apartment_id : false,$value->bdc_apartment_service_price_id,$request->cycle_name,$request->cycle_name_more);
                            $dau_ky = DebitDetailRepository2::getTotalSumeryByCycleNameApartmentServiceCus($this->building_active_id, $request->bdc_apartment_id ? $request->bdc_apartment_id : false, $value->bdc_apartment_service_price_id, $request->cycle_name,"<");
                            $dau_ky = (!isset($dau_ky->tong_thanh_toan_ky) ? 0 : $dau_ky->tong_phat_sinh-$dau_ky->tong_thanh_toan_ky);
                            $phat_sinh = $phatsinh->tong_phat_sinh ?? 0;
                            $thanh_toan = $phatsinh->tong_thanh_toan_ky ?? 0;
                            $cuoi_ky = $value->after_cycle_name;

                            $data = [
                                @$service->code_receipt, // mã code căn hộ 
                                $apartment ? (string) $apartment->code : null, // tên căn hộ 
                                (string) $dau_ky > 0 ? $dau_ky : null,
                                (string) $dau_ky < 0 ? $dau_ky : null,
                                (string) $phatsinh,
                                (string) $thanh_toan,
                                (string) $cuoi_ky > 0 ? $cuoi_ky : null,
                                (string) $cuoi_ky < 0 ? $cuoi_ky : null
                            ];
                         
                        }else{
                            $getBill = Bills::get_detail_bill_by_apartment_id($value->bdc_bill_id);
                            $checkBill = false;
                            if($getBill){
                                if( $getBill->status >= -2) $checkBill = true;
                            }
                            if(!$checkBill){
                                $value->sumery = 0;
                            }
                            $dau_ky = ($value->cycle_name == $request->cycle_name ? $value->before_cycle_name : $value->after_cycle_name);
                            $phat_sinh = ($value->cycle_name == $request->cycle_name ? $value->sumery : 0);
                            $thanh_toan = ($value->cycle_name == $request->cycle_name ? $value->paid_by_cycle_name : 0);
                            $cuoi_ky = $value->after_cycle_name;
                            $data = [
                                @$service->code_receipt, // mã code căn hộ 
                                $apartment ? (string) $apartment->code : null, // tên căn hộ 
                                (string) $dau_ky > 0 ? abs($dau_ky)  : 0,
                                (string) $dau_ky < 0 ? abs($dau_ky) : 0,
                                (string) $phat_sinh,
                                (string) $thanh_toan,
                                (string) $cuoi_ky > 0 ? abs($cuoi_ky) : 0,
                                (string) $cuoi_ky < 0 ? abs($cuoi_ky) : 0
                            ];
                          
                        }
                        if($dau_ky == 0 && $phat_sinh ==0 && $thanh_toan ==0 && $cuoi_ky==0){
                            continue;
                        }
                        if($value->bdc_apartment_service_price_id == 0){  // tiền thừa không chỉ định
                            $check_excess_cash[] = $data;
                            continue;
                        }
                        if ($serviceId != $value->bdc_service_id) {

                            $service = Service::get_detail_bdc_service_by_bdc_service_id($serviceId);
                            $serviceId = $value->bdc_service_id;

                           if ($key != 0 && $service) {

                                $range_row = 'A'.($row+1).':B'.($row+1);
                                $range_full_row = 'A'.($row+1).':H'.($row+1);
                                $sheet->cells($range_full_row, function ($cells) {
                                    $cells->setBackground('#C5D9F1');
                                });
                                $sheet->mergeCells($range_row);
                                $new_row = 'A'.($row+1);
                                $sheet->getStyle($new_row)->getAlignment()->setWrapText(true);
                                $sheet->cells($new_row, function ($cells) use($service,$count_service_id) {
                                    $cells->setFontSize(11);
                                    $cells->setFontWeight('bold');
                                    $cells->setValue(@$service->name.' | '.@$service->code_receipt ."($count_service_id)");
                                    $cells->setValignment('center');
                                    $cells->setAlignment('center');
                                });
                                $sheet->setCellValueByColumnAndRow(2, $row+1, $total_dau_ky_no);
                                $sheet->setCellValueByColumnAndRow(3, $row+1, $total_dau_ky_co);
                                $sheet->setCellValueByColumnAndRow(4, $row+1, $total_trong_ky_no);
                                $sheet->setCellValueByColumnAndRow(5, $row+1, $total_trong_ky_co);
                                $sheet->setCellValueByColumnAndRow(6, $row+1, $total_cuoi_ky_no);
                                $sheet->setCellValueByColumnAndRow(7, $row+1, $total_cuoi_ky_co);
                                $total_dau_ky_no=0;
                                $total_dau_ky_co=0;
                                $total_trong_ky_no=0;
                                $total_trong_ky_co=0;
                                $total_cuoi_ky_no=0;
                                $total_cuoi_ky_co=0;
                                $count_service_id=0;
                                $row++;
                           } 
                          
                        }
                        if($serviceId == $value->bdc_service_id){
                            $sheet->getRowDimension($row+1)
                            ->setOutlineLevel(1)
                            ->setVisible(false)
                            ->setCollapsed(true);
                            $total_dau_ky_no += $dau_ky > 0 ? abs($dau_ky) : 0;
                            $total_dau_ky_co += $dau_ky < 0 ? abs($dau_ky) : 0;
                            $total_trong_ky_no += $phat_sinh;
                            $total_trong_ky_co += $thanh_toan;
                            $total_cuoi_ky_no += $cuoi_ky > 0 ? abs($cuoi_ky) : 0;
                            $total_cuoi_ky_co += $cuoi_ky < 0 ? abs($cuoi_ky) : 0;
                          
                          
                            $total_all_dau_ky_no+= $dau_ky > 0 ? abs($dau_ky) : 0;
                            $total_all_dau_ky_co+= $dau_ky < 0 ? abs($dau_ky) : 0;
                            $total_all_trong_ky_no+= $phat_sinh;
                            $total_all_trong_ky_co+= $thanh_toan;
                            $total_all_cuoi_ky_no+= $cuoi_ky > 0 ? abs($cuoi_ky) : 0;
                            $total_all_cuoi_ky_co+= $cuoi_ky < 0 ? abs($cuoi_ky) : 0;
                        }
                        $count_service_id++;
                        $row++;
                        $sheet->row($row, $data);
                    }
                    $service = Service::get_detail_bdc_service_by_bdc_service_id($serviceId);
                    $range_row = 'A'.($row+1).':B'.($row+1);
                    $range_full_row = 'A'.($row+1).':H'.($row+1);
                    $sheet->cells($range_full_row, function ($cells) {
                        $cells->setBackground('#C5D9F1');
                    });
                    $sheet->mergeCells($range_row);
                    $new_row = 'A'.($row+1);
                    $sheet->getStyle($new_row)->getAlignment()->setWrapText(true);
                    $sheet->cells($new_row, function ($cells) use($service,$count_service_id) {
                        $cells->setFontSize(11);
                        $cells->setFontWeight('bold');
                        $cells->setValue(@$service->name.' | '.@$service->code_receipt ."($count_service_id)");
                        $cells->setValignment('center');
                        $cells->setAlignment('center');
                    });
                    $sheet->setCellValueByColumnAndRow(2, $row+1, $total_dau_ky_no);
                    $sheet->setCellValueByColumnAndRow(3, $row+1, $total_dau_ky_co);
                    $sheet->setCellValueByColumnAndRow(4, $row+1, $total_trong_ky_no);
                    $sheet->setCellValueByColumnAndRow(5, $row+1, $total_trong_ky_co);
                    $sheet->setCellValueByColumnAndRow(6, $row+1, $total_cuoi_ky_no);
                    $sheet->setCellValueByColumnAndRow(7, $row+1, $total_cuoi_ky_co);
                    $total_dau_ky_no=0;
                    $total_dau_ky_co=0;
                    $total_trong_ky_no=0;
                    $total_trong_ky_co=0;
                    $total_cuoi_ky_no=0;
                    $total_cuoi_ky_co=0;
                    $count_service_id=0;
                    $row++;
                    if($check_excess_cash){
                        foreach ($check_excess_cash as $key => $value) {
                            $sheet->getRowDimension($row+1)
                            ->setOutlineLevel(1)
                            ->setVisible(false)
                            ->setCollapsed(true);
                            $total_dau_ky_no += $value[2];
                            $total_dau_ky_co += $value[3];
                            $total_trong_ky_no += $value[4];
                            $total_trong_ky_co += $value[5];
                            $total_cuoi_ky_no += $value[6];
                            $total_cuoi_ky_co += $value[7];

                            $total_all_dau_ky_no+= $value[2];
                            $total_all_dau_ky_co+= $value[3];
                            $total_all_trong_ky_no+= $value[4];
                            $total_all_trong_ky_co+= $value[5];
                            $total_all_cuoi_ky_no+= $value[6];
                            $total_all_cuoi_ky_co+= $value[7];
                            $row++;
                            $sheet->row($row, $value);
                        }
                        $range_row = 'A'.($row+1).':B'.($row+1);
                        $range_full_row = 'A'.($row+1).':H'.($row+1);
                        $sheet->cells($range_full_row, function ($cells) {
                            $cells->setBackground('#C5D9F1');
                        });
                        $sheet->mergeCells($range_row);
                        $new_row = 'A'.($row+1);
                        $sheet->getStyle($new_row)->getAlignment()->setWrapText(true);
                        $sheet->cells($new_row, function ($cells) use($check_excess_cash) {
                            $cells->setFontSize(11);
                            $cells->setFontWeight('bold');
                            $cells->setValue('Tiền thừa ('.count($check_excess_cash).')');
                            $cells->setValignment('center');
                            $cells->setAlignment('center');
                        });
                        $sheet->setCellValueByColumnAndRow(2, $row+1, $total_dau_ky_no);
                        $sheet->setCellValueByColumnAndRow(3, $row+1, $total_dau_ky_co);
                        $sheet->setCellValueByColumnAndRow(4, $row+1, $total_trong_ky_no);
                        $sheet->setCellValueByColumnAndRow(5, $row+1, $total_trong_ky_co);
                        $sheet->setCellValueByColumnAndRow(6, $row+1, $total_cuoi_ky_no);
                        $sheet->setCellValueByColumnAndRow(7, $row+1, $total_cuoi_ky_co);
                    }
                    $row++;
                    $range_row = 'A'.($row+1).':B'.($row+1);
                    $range_full_row = 'A'.($row+1).':H'.($row+1);
                    $sheet->cells($range_full_row, function ($cells) {
                        $cells->setBackground('#C5D9F1');
                    });
                    $sheet->mergeCells($range_row);
                    $new_row = 'A'.($row+1);
                    $new_row_b = 'B'.($row+1);
                    $sheet->cells($new_row, function ($cells) {
                        $cells->setFontSize(11);
                        $cells->setFontWeight('bold');
                        $cells->setValue('Tổng');
                        $cells->setValignment('center');
                        $cells->setAlignment('center');
                    });
                    $sheet->cells($new_row_b, function ($cells) use($row) {
                        $cells->setFontSize(11);
                        $cells->setFontWeight('bold');
                        $cells->setValue('Số dòng'.($row+1));
                        $cells->setValignment('center');
                        $cells->setAlignment('center');
                    });
                    $sheet->setCellValueByColumnAndRow(2, $row+1, $total_all_dau_ky_no);
                    $sheet->setCellValueByColumnAndRow(3, $row+1, $total_all_dau_ky_co);
                    $sheet->setCellValueByColumnAndRow(4, $row+1, $total_all_trong_ky_no);
                    $sheet->setCellValueByColumnAndRow(5, $row+1, $total_all_trong_ky_co);
                    $sheet->setCellValueByColumnAndRow(6, $row+1, $total_all_cuoi_ky_no);
                    $sheet->setCellValueByColumnAndRow(7, $row+1, $total_all_cuoi_ky_co);
                }
                $sheet->setWidth(array(
                    'A'     =>  15,
                    'B'     =>  15,
                    'C'     =>  15,
                    'D'     =>  15,
                    'E'     =>  15,
                    'F'     =>  15,
                    'G'     =>  15,
                    'H'     =>  15,
                    'I'     =>  15,
                    'J'     =>  15,
                ));

            });
        })->store('xlsx',storage_path('exports/'));
        ob_end_clean();
        $file     = storage_path('exports/'.$result->filename.'.'.$result->ext);
      
        return response()->download($file)->deleteFileAfterSend(true);
                    
    }
}
