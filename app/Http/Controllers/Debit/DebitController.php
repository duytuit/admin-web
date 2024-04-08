<?php

namespace App\Http\Controllers\Debit;

use App\Commons\Helper;
use App\Http\Controllers\BuildingController;
use App\Http\Requests\ApartmentServicePrice\ApartmentServicePriceRequest;
use App\Http\Requests\Debit\EditDebitDetailRequest;
use App\Models\Apartments\Apartments;
use App\Models\BdcDebitDetail\DebitDetail;
use App\Models\Building\Building;
use App\Models\Building\BuildingPlace;
use App\Models\Campain;
use App\Models\Fcm\Fcm;
use App\Models\SentStatus;
use App\Repositories\Apartments\ApartmentsRespository;
use App\Repositories\BdcApartmentDebit\ApartmentDebitRepository;
use App\Repositories\BdcApartmentServicePrice\ApartmentServicePriceRepository;
use App\Repositories\BdcBills\BillRepository;
use App\Repositories\BdcBuildingDebit\BuildingDebitRepository;
use App\Repositories\BdcDebitDetail\DebitDetailRepository;
use App\Repositories\Building\BuildingPlaceRepository;
use App\Repositories\Building\BuildingRepository;
use App\Repositories\Customers\CustomersRespository;
use App\Repositories\Service\ServiceRepository;
use App\Services\CronJobService;
use App\Services\FCM\SendNotifyFCMService;
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
        SendNotifyFCMService $sendNotifyFCMService
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

        return view('debit.index', $data);
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

        $data['debits'] = new LengthAwarePaginator($itemsForCurrentPage, count($debits['debits']), $perPage, $page, ['path' => route('admin.debit.detail')]);
        $data['apartmentShow'] = $this->apartmentRepo->getApartmentById($apartmentsUseService);
        $data['apartmentsUseService'] = $debits['apartmentsUseService'];
        $data['paymentDeadlineBuilding'] = Carbon::now()->addDays(3)->toDateString();
        $data['month'] = $month;
        return view('debit.detail', $data);
    }

    public function detailHandling(Request $request)
    {
        $debit = $this->debitRepo->handlingDebitDetail($request->all(), $this->building_active_id);
        if($debit)
        {
            return redirect()->route('admin.debit.debitLogs')->with('success', 'Thiết lập công nợ thành công.');
        }
        return redirect()->route('admin.debit.debitLogs')->with('error', 'Tiến trình xử lý công nợ đã được thiết lập.');
    }
    public function detailHandlingYear(Request $request)
    {
        $debit = $this->debitRepo->handlingDebitDetailYear($request->all(), $this->building_active_id);
        if($debit)
        {
            return redirect()->route('admin.debit.debitLogs')->with('success', 'Thiết lập công nợ thành công.');
        }
        return redirect()->route('admin.debit.debitLogs')->with('error', 'Tiến trình xử lý công nợ đã được thiết lập.');
    }

    public function getApartment(Request $request)
    {
        $apartment = $this->apartmentRepo->getApartmentOfBuildingDebit($request->id);
        return response()->json($apartment);
    }

    public function export()
    {
        return $debit = $this->apartmentDebitRepo->excelDebitIndex($this->building_active_id);
    }

    public function exportFilter(Request $request) {
        return $debit = $this->apartmentDebitRepo->excelDebitFilter($this->building_active_id,$request->all());
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
        return view('debit.show', $data);
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
        return view('debit.process_debit_detail', $data);
    }

    public function reloadProcessDebitDetail()
    {
        $view = view("debit._reload_process_debit_detail", [
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
            Cookie::queue('per_page_debit_detail', $per_page, 60 * 24 * 30);
        }
        return redirect()->back();
    }

    public function detailDebit(Request $request)
    {
        $data['meta_title'] = 'Chi tiết công nợ';

        $buildingId = $this->building_active_id;
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

        $debit_details = $this->debitRepo->findMaxVersionByCurrentMonthVersion5($buildingId, $apartmentService, $apartmentsUseService, $request, $data['per_page']);

        $data['debits'] = $debit_details;
        $data['apartmentShow'] = $this->apartmentRepo->getApartmentById($apartmentsUseService);
        $data['cycle_names'] = $this->debitRepo->getCycleNameV2($buildingId);
        $data['bills'] = $this->billRepository->getBill($buildingId);
        return view('debit.detail_service', $data);
    }

    public function detailDebitActionRecord(Request $request)
    {
        $data['meta_title'] = 'Chi tiết công nợ';

        $buildingId = $this->building_active_id;

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

        $debit_details = $this->debitRepo->findMaxVersionByCurrentMonthVersionStatusNotConfirm($buildingId, $apartmentService, $apartmentsUseService, $request, $data['per_page']);

        $data['debits'] = $debit_details;
        $data['apartmentShow'] = $this->apartmentRepo->getApartmentById($apartmentsUseService);
        $data['cycle_names'] = $this->debitRepo->getCycleName();
        $data['bills'] = $this->billRepository->getBill($buildingId);
        return view('debit.detail_service_action', $data);
    }

    public function detailDebitAction(Request $request)
    {
        if ($request->has('per_page')) {
            $per_page = $request->input('per_page', 10);
            Cookie::queue('per_page_debit_detail_service', $per_page, 60 * 24 * 30);
        }
        return back();
    }
    public function ActionRecordDebit(Request $request)
    {
        if ($request->has('per_page')) {
            $per_page = $request->input('per_page', 10);
            Cookie::queue('per_page_debit_detail_service', $per_page, 60 * 24 * 30);
        }
        return $this->debitRepo->action($request);
    }
    public function export_meter_water(Request $request)
    {
        $data['filter'] = $request->all();
        if (!$request->cycle_name) { 
            return redirect()->back()->with('warning', "bạn chưa chọn kỳ.");
        }
       
        if (!$request->electric_meter_type) { 
            return redirect()->back()->with('warning', "bạn chưa chọn loại.");
        }
        $electric_meter_type =$request->electric_meter_type;
        $detbit =  DebitDetail::where(['bdc_building_id' => $this->building_active_id, 'cycle_name' => $request->cycle_name,'bdc_price_type_id'=> 2])->where('title','like','%phí '.$electric_meter_type.'%')->get();
       $result = Excel::create('Danh sách', function ($excel) use ($detbit,$request) {
           $excel->setTitle('Danh sách');
           $excel->sheet('Danh sách', function ($sheet) use ($detbit,$request) {
               $row = 1;
               $sheet->row($row, [
                   'mã căn hộ(*)',
                   'chỉ số đầu(*)',
                   'chỉ số cuối(*)',
                   'ngày chôt số(*)',
                   'loại dịch vụ(*)',
                   'kỳ tháng(*)'
               ]);
               foreach ($detbit as $key => $value) {
                   if($value->detail == '[]'){
                        continue;
                   }
                   $row++;
                   $data_detail = json_decode($value->detail);
                   $apartment = Apartments::get_detail_apartment_by_apartment_id($value->bdc_apartment_id);
                   $data = [
                       $apartment->code,
                       $data_detail->so_dau,
                       $data_detail->so_cuoi,
                       Carbon::parse($value->from_date)->format('d/m/Y'),
                       @$value->pubConfig->title == null ? "" : @$value->pubConfig->title,
                       $request->electric_meter_type == 'điện' ? 0 : 1,
                       $value->cycle_name
                   ];

                   $sheet->row($row, $data);
                   
               }
           });
       })->store('xlsx',storage_path('exports/'));
$file     = storage_path('exports/'.$result->filename.'.'.$result->ext);
return response()->download($file)->deleteFileAfterSend(true);
             
    }
    public function exportExcel(Request $request)
    {
        $apartmentsUseService = $this->apartmentServiceRepo->findAllIdApartmentUseService($this->building_active_id);
        $apartmentService = $this->apartmentServiceRepo->getServiceApartment($this->building_active_id);
        if((isset($request->bdc_service_id) && $request->bdc_service_id != null) || (isset($request->bdc_apartment_id) && $request->bdc_apartment_id != null)){
            $arr_bdc_service_id[]=$request->bdc_service_id;
            $arr_bdc_apartment_id[]=$request->bdc_apartment_id;
            return $this->debitRepo->export($this->building_active_id, $request->bdc_service_id ? $arr_bdc_service_id : $apartmentService,$request->bdc_apartment_id ? $arr_bdc_apartment_id : $apartmentsUseService, $request);
        }  
        return $this->debitRepo->export($this->building_active_id, $apartmentService, $apartmentsUseService, $request);
    }

    public function detailDebitEdit(Request $request)
    {
        $billDetail = $this->debitRepo->find($request->id);

        

        return view('debit.modal.detail_service', compact('billDetail'));
    }

     public function detailDebitEditVersion(Request $request)
    {
         $data['meta_title'] = 'Chi tiết version công nợ';

         $data['billDetail']  = $this->debitRepo->filterBillIdApartment($request->bdc_bill_id,$request->bdc_apartment_id);

         return view('debit.modal.detail_service_version', $data);
    }

    public function destroydebitDetail($id)
    {
        DB::beginTransaction();
        try 
        {
            $debit = $this->debitRepo->findDebitById($id);
            $checkFromDate = $this->debitRepo->filterFromDate($this->building_active_id, $debit->bdc_apartment_id, $debit->bdc_service_id, $debit->from_date);
            if(!$checkFromDate->isEmpty()) {
                $toDate = Carbon::parse($debit->to_date)->format('d/m/Y');
                DB::rollBack();
                return redirect()->back()->with('error', "Không thể xóa do đã phát sinh Bảng kê dịch vụ có ngày bắt đầu lớn hơn $toDate.");
            }
            $this->debitRepo->deleteByBillId($this->building_active_id, $debit->bdc_apartment_id, $debit->bdc_service_id, $debit->bdc_bill_id);
            if($debit->bdc_price_type_id == 1) {
                $this->apartmentServiceRepo->updateLastTimePay($debit->from_date, $debit->bdc_apartment_service_price_id);
            }
            $debit->delete();
            DB::commit();
            return redirect()->back()->with(['success' => 'Xóa chi tiết bảng kê thành công!']);
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
    public function destroydebitDetailV2($id)
    {
        try 
        {
            $debit = $this->debitRepo->findDebitById($id)->delete();
            
            //$this->debitRepo->deleteByBillIdV2($this->building_active_id, $debit->bdc_apartment_id, $debit->bdc_service_id, $debit->bdc_bill_id);

            return redirect()->back()->with(['success' => 'Xóa chi tiết bảng kê thành công!']);
        } catch (Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
        
    }
    public function destroydebitDetailV3($id)
    {
        try 
        {
            $debit = $this->debitRepo->findDebitById($id);

            return redirect()->back()->with(['success' => 'Xóa chi tiết bảng kê thành công!']);
        } catch (Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
        
    }

    public function detailDebitUpdate(EditDebitDetailRequest $request, BillRepository $billRepository, DebitDetailRepository $debitDetailRepository)
    {
        $this->debitRepo->updateRecord($request->id, $request->price,str_replace(',', '', $request->previous_owed),str_replace(',', '',  $request->paid), $request->version, $request->cycle_name, str_replace(',', '', $request->sumery),str_replace(',', '',  $request->new_sumery), $request->from_date, $request->to_date, $request->quantity);
        $debitDetail = $debitDetailRepository->find($request->id);
        $bill = $billRepository->findBillById($debitDetail->bdc_bill_id);
        $bill->update(['status' => BillRepository::WAIT_FOR_CONFIRM,'deadline'=>Carbon::parse($request->deadline)]);
        $responseData = [
            'success' => true,
            'message' => 'Cập nhật thành công!'
        ];

        return response()->json($responseData);
    }

    public function debitLogs(Request $request)
    {
        $data['meta_title'] = 'Quản lý công nợ';
        $data['per_page'] = Cookie::get('per_page', 10);
        $data['buildings'] = $this->buildingRepo->getBuildingOfUser($this->building_active_id);
        $data['paymentDeadlineBuilding'] = Carbon::now()->addDays(3)->toDateString();
        $apartmentService = $this->apartmentServiceRepo->getServiceApartment($this->building_active_id);
        $data['serviceBuildings_cycle_month'] = $this->serviceRepo->getServiceOfApartment3($apartmentService)->where('bdc_period_id',1)->get();// chu kỳ tháng
        $data['serviceBuildings_cycle_year'] = $this->serviceRepo->getServiceOfApartment3($apartmentService)->where('bdc_period_id',6)->get();// chu kỳ năm

        return view('debit.debit_logs', $data);
    }

    public function total(Request $request, CustomersRespository $customersRespository)
    {
        $data['meta_title'] = 'Tổng hợp công nợ';
        $data['per_page'] = Cookie::get('per_page', 10);
        $data['buildings'] = $this->buildingRepo->getBuildingOfUser($this->building_active_id);
        $page = $request->page ? $request->page : 1;
        $perPage = $data['per_page'];
        $data['filter'] = $request->all();        
        $data['apartments'] = $this->apartmentRepo->getApartmentOfBuildingDebit($this->building_active_id);
        // Start displaying items from this number;
        $offSet = ($page * $perPage) - $perPage; // Start displaying items from this number
        $debits = $this->debitRepo->GeneralAccountantAll($this->building_active_id);
        if ($request->all()) {
            if (isset($request['from_date']) && isset($request['to_date']) && $request['from_date'] != null && $request['to_date'] != null) 
            {
                $data['from_date'] = $fromDate = $request['from_date'];
                $data['to_date'] = $toDate = $request['to_date'];
                $data['bdc_apartment_id'] = $apartmentId = $request['bdc_apartment_id'];
                $data['du_no_cuoi_ky'] = $duNoCuoiKy = $request['du_no_cuoi_ky'];
                $debits = $this->debitRepo->GeneralAccountant($this->building_active_id, $fromDate, $toDate, $apartmentId, $duNoCuoiKy);
            }
            else if(isset($request['bdc_apartment_id']) && $request['bdc_apartment_id'] != null)
            {
                $data['bdc_apartment_id'] = $apartmentId = $request['bdc_apartment_id'];
                $data['du_no_cuoi_ky'] = $duNoCuoiKy = $request['du_no_cuoi_ky'];
                $debits = $this->debitRepo->GeneralAccountantApartment($this->building_active_id, $apartmentId, $duNoCuoiKy);
            } 
            else if (isset($request['du_no_cuoi_ky']) && $request['du_no_cuoi_ky'] > 0) 
            {
                $data['du_no_cuoi_ky'] = $duNoCuoiKy = $request['du_no_cuoi_ky'];
                $debits = $this->debitRepo->GeneralAccountantDuNoCuoiKy($this->building_active_id, $duNoCuoiKy);
            }
        }
        $data['sumDayKy_all'] = array_sum(array_column($debits, 'dau_ky'));
        $data['sumPsTrongKy_all'] = array_sum(array_column($debits, 'ps_trongky'));
        $data['sumThanhToan_all'] = array_sum(array_column($debits, 'thanh_toan'));
        $itemsForCurrentPage = array_slice($debits, $offSet, $perPage, true);
        $data['debits'] = new LengthAwarePaginator($itemsForCurrentPage, count($debits), $perPage, $page, ['path' => route('admin.debit.total')]);
        $data['buildingPlaceRepository'] = $this->_buildingPlaceRepository;
        return view('debit.total', $data);
    }

    public function sendMessage(Request $request)
    {
        $building = Building::get_detail_building_by_building_id($this->building_active_id);
        $apartmentIds = $request->apartmentIds;
        $apartmentArr = explode(',', $apartmentIds);
        try {
            foreach($apartmentArr as $_apartment) {
                $customers = $this->_customersRespository->getUserInApartmentV2($_apartment);
                $data['apartment'] = $apartment = $this->apartmentRepo->findById($_apartment);

                $total = ['email'=>0, 'app'=> 0, 'sms'=> 0];
                if($request->sendMail == "true" ) 
                    $total['email'] = sizeof($customers);
                if($request->sendSms == "true")
                    $total['sms'] = sizeof($customers);
                if($request->sendApp == "true") {
                    $data_list_user_id = [];
                    foreach ($customers as $value) {
                        array_push($data_list_user_id, $value['pub_user_profile']['pub_user_id']);
                    }
                    $countTokent = (int)Fcm::getCountTokenbyUserId($data_list_user_id);  
                    $total['app'] = $countTokent;   
                }
                if($total['sms']> 0 || $total['app']> 0 || $total['email'] > 0){
                    $campain = Campain::updateOrCreateCampain('Thông báo Nhắc phí '.now()->format('m/Y'), config('typeCampain.NHAC_NO'), null, $total, $this->building_active_id, 0, 0);
                }
                foreach($customers as $_customer) {
                    if($request->sendMail == "true" && $_customer['pub_user_profile'] != null && isset($_customer['pub_user_profile']['email']) && $_customer['pub_user_profile']['email'] != null) {
                        $debitDetails = $this->debitRepo->GeneralAccountantDetailAlls($this->building_active_id, $_apartment);
                        $data['debitDetails'] = $debitDetails;
                        // $data['debitDetails'] = $this->debitRepo->GeneralAccountantApartment($this->building_active_id, $apartmentIds);
                        $data['customer'] = $_customer;
                        $data['message'] = $request->message;
                        $view = view('debit._send_mail', $data)->render();
                        $headerParams = [
                            "Content-Type" => "application/json",
                            "ClientId" => env('ClientId_bdc'),
                            "ClientSecret" => env('ClientSecret_bdc'),
                        ];
                        $base64EndCode = base64_encode($view);
                        $displayName = $_customer['pub_user_profile']['display_name'];
                        $message = '{"ten_khach_hang": "' . $displayName . '", "noi_dung": "' . $base64EndCode . '"}';

                        $month = Carbon::now()->month;
                        switch($month) {
                            case 1: $_month = "01"; break;
                            case 2: $_month = "02"; break;
                            case 3: $_month = "03"; break;
                            case 4: $_month = "04"; break;
                            case 5: $_month = "05"; break;
                            case 6: $_month = "06"; break;
                            case 7: $_month = "07"; break;
                            case 8: $_month = "08"; break;
                            case 9: $_month = "09"; break;
                            default: $_month = $month; break;
                        }
                        
                        $cycleName = Carbon::now()->year . $_month;
                        $bills = $this->billRepository->getCurrentCycleName($cycleName, $apartment->id);
                        $attachFileArr = [];
                        foreach($bills as $_bill) {
                            $url = $_bill != null && $_bill->url != null ? env('APP_URL') . "/" . $_bill->url : null;
                            if($url != null) {
                                array_push($attachFileArr, $url);
                            }
                        }
                        if($debitDetails){
                           $sum_du_no_cuoi_ky=0;
                           foreach ($debitDetails as $key => $value) {
                             $sum_du_no_cuoi_ky+=$value->du_no_cuoi_ky;
                           }
                        }
                        $attachFile = json_encode($attachFileArr);
                        $data_send_email = [
                            'params' => [
                                '@tenkhachhang' => $displayName,
                                '@canho' => $debitDetails[0]->name,
                                '@kyhoadon' => Carbon::now()->year .'-'. $_month,
                                '@attachFile' => $attachFile,
                                '@message' => $request->message,
                                '@dunocuoiky' => number_format($sum_du_no_cuoi_ky)
                            ],
                            'cc' => $_customer['pub_user_profile']['email'],
                            'building_id' => $this->building_active_id,
                            'type' => 70,
                            'status' => 'paid',
                            'campain_id' => $campain->id
                        ];
                        ServiceSendMailV2::setItemForQueue($data_send_email);
                    }
                    
                    if($request->sendSms == "true" && $_customer['pub_user_profile'] != null && isset($_customer['pub_user_profile']['phone']) && $_customer['pub_user_profile']['phone'] != null) {
                        $headerParams = [
                            "Content-Type" => "application/json",
                            "ClientId" => env('ClientId_bdc'),
                            "ClientSecret" => env('ClientSecret_bdc'),
                        ];
                        $strip_html = Helper::convert_vi_to_en(strip_tags($request->message));
                        $data = [
                            "phone" => $_customer['pub_user_profile']['phone'],
                            "message" => $strip_html,
                        ];
                        $client = new \GuzzleHttp\Client();
                        $requestClient = $client->request('POST', env('API_SEND_SMS_BDC'), ['headers' => $headerParams, 'body' => json_encode($data)]);
                        $response = json_decode((string) $requestClient->getBody(), true);
                    }
                    if($request->sendApp == "true") {
                        $data_payload['message'] = strip_tags($request->message);
                        $data_payload['user_id'] = $_customer['pub_user_profile']['pub_user_id'];
                        $data_payload['building_id'] = $this->building_active_id;
                        $data_payload['type'] = SendNotifyFCMService::NHAC_NO;
                        $data_payload['title'] = "Gửi thông báo";
                        $data_noti = [
                            "message" => strip_tags($request->message),
                            "building_id"=> $this->building_active_id,
                            "title"=>'['.$building->name."]_". $apartment->name,
                            "action_name"=> SendNotifyFCMService::NHAC_NO,
                            'type'=> SendNotifyFCMService::NHAC_NO,
                            'avatar' => "avatar/system/01.png",
                            'campain_id' => $campain->id,
                            'app'=>'v1'
                        ];
                        SendNotifyFCMService::setItemForQueueNotify(array_merge($data_noti,['user_id'=>$_customer['pub_user_profile']['pub_user_id'],'app_config'=>@$building->template_mail == 'asahi' ? 'asahi' : 'cudan']));
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
        // $data['debits'] = new LengthAwarePaginator($itemsForCurrentPage, count($debits), $perPage, $page, ['path' => route('admin.debit.total')]);
        $data['debits'] = collect($debitsTotal);
        $result = Excel::create('Tổng hợp công nợ', function($excel) use ($data) {
            $excel->sheet('Tổng hợp công nợ', function($sheet) use ($data) {
                $sheet->loadView('debit._export_excel_total', $data);
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
return response()->download($file)->deleteFileAfterSend(true);
             
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
        // tính ra đầu kỳ
        // $daukyDebits = $this->debitRepo->filterAllDauky($this->building_active_id, $request->all());
        // $totalPs = array_sum(array_column($daukyDebits, 'sumery'));
        // $totalThu = array_sum(array_column($daukyDebits, 'cost'));
        
        
        $data['sumPs_all'] = array_sum(array_column($debits, 'sumery'));
        $data['sumThu_all'] = array_sum(array_column($debits, 'cost'));

        $data['sumDauky_all'] = isset($request["from_date"]) && isset($request["to_date"]) && $request["from_date"] != null && $request["to_date"] != null ? $data['sumPs_all'] - $data['sumThu_all'] : 0;

        $itemsForCurrentPage = array_slice($debits, $offSet, $perPage, true);
        $data['debits'] = new LengthAwarePaginator($itemsForCurrentPage, count($debits), $perPage, $page, ['path' => route('admin.debit.detailVersion2')]);

        return view('debit.detail_version2', $data);
    }

    public function generalDetail(Request $request, CustomersRespository $customersRespository, DebitDetailRepository $debitDetailRepository)
    {
        set_time_limit(0);
        $data['meta_title'] = 'Tổng hợp công nợ';
        $data['per_page'] = Cookie::get('per_page', 10);
        $data['buildings'] = $this->buildingRepo->getBuildingOfUser($this->building_active_id);
        $page = $request->page ? $request->page : 1;
        $perPage = $data['per_page'];
        $data['filter'] = $request->all();     
        if(isset($data['filter']['bdc_apartment_id'])){
            $data['get_apartment'] = $this->apartmentRepo->findById($data['filter']['bdc_apartment_id']);
         }
         if(isset($data['filter']['ip_place_id'])){
             $data['get_place_building'] = $this->_buildingPlaceRepository->findById($data['filter']['ip_place_id']);
         }   
        $data['apartments'] = $this->apartmentRepo->getApartmentOfBuildingDebit($this->building_active_id);
        $data['customers'] = $customersRespository;
        $data['debitDetailRepository'] = $debitDetailRepository;
        $data['from_date'] = isset($request['from_date']) && $request['from_date'] != null ? $request['from_date'] : null;
        $data['to_date'] = isset($request['to_date']) && $request['to_date'] != null ? $request['to_date'] : null;
        // Start displaying items from this number;
        $offSet = ($page * $perPage) - $perPage; // Start displaying items from this number
        $debitsTotal = $this->debitRepo->GeneralAccountantAll($this->building_active_id);
        $tongDauky = array();
        if ($request->all()) 
        {
            $fromDate = $request['from_date'];
            $toDate = $request['to_date'];
            $apartmentId = $request['bdc_apartment_id'];
            $duNoCuoiKy = $request['du_no_cuoi_ky'];
            $apartments = $apartmentId == null 
                ? $this->apartmentRepo->getApartmentOfBuilding($this->building_active_id) 
                : $this->apartmentRepo->findByIdBuildingId($this->building_active_id, $apartmentId);
                
            if(($fromDate != null && $toDate != null) || $apartmentId != null)
            {
                $debitsTotal = [];

                $apartmentIds = $apartments->map(function($debit){
                    return $debit->id;
                });
                
                $apartmentIds = implode(",", $apartmentIds->toArray());
                $debits = $fromDate != null && $toDate != null 
                        ? $this->debitRepo->GeneralAccountant($this->building_active_id, $fromDate, $toDate, $apartmentId, $duNoCuoiKy)
                        : $this->debitRepo->GeneralAccountantApartment($this->building_active_id, $apartmentId, $duNoCuoiKy);
                $debits = collect($debits);
                $debitsTotal = $debits->toArray();
                // dd($debitsTotal->toArray());
                // foreach($apartments as $apartment)
                // {
                //     $_debits = $debits->where('bdc_apartment_id', $apartment->id);
                //     $debit = $_debits->isEmpty() ? [] : $_debits->first();
                //     if(empty($debit))
                //     {
                //         if($duNoCuoiKy > 0) {
                //             continue;
                //         }
                //         $debit["bdc_apartment_id"] = $apartment->id;
                //         $debit["name"] = $apartment->name;
                //         $debit["bdc_building_id"] = $this->building_active_id;
                //         $debit["ps_trongky"] = 0;
                //         $debit["dau_ky"] = 0;
                //         $debit["thanh_toan"] = 0;
                //         $debit = (object)$debit;
                //     }
                //     array_push($debitsTotal, $debit);
                // }
                $tongDauky = $this->debitRepo->TongDauKy($this->building_active_id, $fromDate, $apartmentId);
            }
        }
        $tongDauky = !empty($tongDauky) ? array_shift($tongDauky)->dau_ky : 0;
        $data['sumDayKy_all'] = $tongDauky;
        $data['sumPsTrongKy_all'] = array_sum(array_column($debitsTotal, 'ps_trongky'));
        $data['sumThanhToan_all'] = array_sum(array_column($debitsTotal, 'thanh_toan'));
        $data['building_id'] = $this->building_active_id;
        $itemsForCurrentPage = array_slice($debitsTotal, $offSet, $perPage, true);
        $data['debits'] = new LengthAwarePaginator($itemsForCurrentPage, count($debitsTotal), $perPage, $page, ['path' => route('admin.debit.generalDetail')]);
        return view('debit.general_detail', $data);
    }

    public function exportExcelGeneralDetail(Request $request, CustomersRespository $customersRespository, DebitDetailRepository $debitDetailRepository)
    {
        set_time_limit(0);
        $data['meta_title'] = 'Chi tiết công nợ tổng hợp';
        $data['buildings'] = $this->buildingRepo->getBuildingOfUser($this->building_active_id);
        $data['filter'] = $request->all();        
        $data['apartments'] = $this->apartmentRepo->getApartmentOfBuildingDebit($this->building_active_id);
        $data['customers'] = $customersRespository;
        $data['debitDetailRepository'] = $debitDetailRepository;
        $data['from_date'] = isset($request['from_date']) && $request['from_date'] != null ? $request['from_date'] : null;
        $data['to_date'] = isset($request['to_date']) && $request['to_date'] != null ? $request['to_date'] : null;
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
                $apartmentIds = $apartments->map(function($debit){
                    return $debit->id;
                });
                $apartmentIds = implode(",", $apartmentIds->toArray());
                $debits = $fromDate != null && $toDate != null 
                        ? $this->debitRepo->GeneralAccountants($this->building_active_id, $fromDate, $toDate, $apartmentIds)
                        : $this->debitRepo->GeneralAccountantApartments($this->building_active_id, $apartmentIds);
                $debits = collect($debits);
                foreach($apartments as $apartment)
                {
                    // $debits = $fromDate != null && $toDate != null 
                    //     ? $this->debitRepo->GeneralAccountants($this->building_active_id, $fromDate, $toDate, $apartment->id)
                    //     : $this->debitRepo->GeneralAccountantApartments($this->building_active_id, $apartmentId);
                    //$debits = $this->debitRepo->GeneralAccountant($this->building_active_id, $fromDate, $toDate, $apartment->id);
                    // dd($debits);
                    $_debits = $debits->where('bdc_apartment_id', $apartment->id);
                    
                    $debit = $_debits->isEmpty() ? $_debits : $_debits->first();
                    
                    if(!isset($debit->bdc_apartment_id))
                    {
                        $_debit["bdc_apartment_id"] = $apartment->id;
                        $_debit["name"] = $apartment->name;
                        $_debit["bdc_building_id"] = $this->building_active_id;
                        $_debit["ps_trongky"] = 0;
                        $_debit["dau_ky"] = 0;
                        $_debit["thanh_toan"] = 0;
                        $debit = (object)$_debit;
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
        $data['building_id'] = $this->building_active_id;
        // $data['debits'] = new LengthAwarePaginator($itemsForCurrentPage, count($debits), $perPage, $page, ['path' => route('admin.debit.total')]);
        $data['debits'] = collect($debitsTotal);
        // dd($data);
        $result = Excel::create('Chi tiết công nợ tổng hợp', function($excel) use ($data) {
            $excel->sheet('Chi tiết công nợ tổng hợp', function($sheet) use ($data) {
                $sheet->loadView('debit._export_excel_general_detail', $data);
                $sheet->getStyle('A1:H1')->applyFromArray(array(
                    'fill' => array(
                        'type'  => PHPExcel_Style_Fill::FILL_SOLID,
                        'color' => array('rgb' => '337ab7')
                    )
                ));
            });
        })->store('xlsx',storage_path('exports/'));
$file     = storage_path('exports/'.$result->filename.'.'.$result->ext);
return response()->download($file)->deleteFileAfterSend(true);
             
    }
}
