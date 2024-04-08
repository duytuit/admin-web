<?php

namespace App\Http\Controllers\Bill\V2;

use App\Commons\Helper;
use App\Exceptions\QueueRedis;
use App\Http\Controllers\BuildingController;
use App\Models\BdcBills\Bills;
use App\Models\BdcElectricMeter\ElectricMeter;
use App\Models\BdcV2DebitDetail\DebitDetail;
use App\Models\Building\Building;
use App\Models\Building\BuildingPlace;
use App\Models\Campain;
use App\Models\CronJobManager\CronJobManager;
use App\Models\PaymentInfo\PaymentInfo;
use App\Models\PublicUser\UserInfo;
use App\Models\PublicUser\Users;
use App\Models\SentStatus;
use App\Repositories\Apartments\ApartmentsRespository;
use App\Repositories\BdcBills\V2\BillRepository;
use App\Repositories\BdcDebitDetail\V2\DebitDetailRepository;
use App\Repositories\BdcLockCyclename\BdcLockCyclenameRepository;
use App\Repositories\BdcV2DebitDetail\DebitDetailRepository as BdcV2DebitDetailDebitDetailRepository;
use App\Repositories\Building\BuildingRepository;
use App\Repositories\Config\ConfigRepository;
use App\Repositories\CronJobManager\CronJobManagerRepository;
use App\Repositories\Customers\CustomersRespository;
use App\Repositories\PublicUsers\PublicUsersProfileRespository;
use App\Services\SendTelegram;
use App\Services\ServiceSendMail;
use App\Services\ServiceSendMailV2;
use App\Util\Debug\Log;
use Barryvdh\DomPDF\Facade as PDF;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use App\Repositories\Building\BuildingPlaceRepository;
use Illuminate\Pagination\LengthAwarePaginator;

class BillController extends BuildingController
{
    public $billRepo;
    public $buildingRepo;
    public $apartmentRepo;
    public $customerRepo;
    public $profileRepo;
    private $modelBuildingPlace;
    public $debitDetailRepo;

    public function __construct(
        Request $request,
        BillRepository $billRepo,
        BuildingRepository $buildingRepo,
        ApartmentsRespository $apartmentRepo,
        CustomersRespository $customerRepo,
        PublicUsersProfileRespository $profileRepo,
        BuildingPlaceRepository $modelBuildingPlace,
        DebitDetailRepository $debitDetailRepo
    ) {
        parent::__construct($request);
        //$this->middleware('route_permision');
        $this->debitDetailRepo = $debitDetailRepo;
        $this->billRepo = $billRepo;
        $this->buildingRepo = $buildingRepo;
        $this->apartmentRepo = $apartmentRepo;
        $this->customerRepo = $customerRepo;
        $this->profileRepo = $profileRepo;
        $this->modelBuildingPlace = $modelBuildingPlace;
    }

    public function index(Request $request)
    {
        $data['meta_title'] = 'Quản lý hóa đơn';
        //$data['buildings'] = $this->buildingRepo->getBuildingOfUser($this->building_active_id);
        $data['per_page'] = Cookie::get('per_page', 10);
        $data['filter'] = $request->all();
        if(isset($data['filter']['bdc_apartment_id'])){
            $data['get_apartment'] = $this->apartmentRepo->findById($data['filter']['bdc_apartment_id']);
         }
         if(isset($data['filter']['ip_place_id'])){
             $data['get_place_building'] = $this->modelBuildingPlace->findById($data['filter']['ip_place_id']);
         }
         
        $data['bills'] = $this->billRepo->getBillv2($this->building_active_id, $request->all())->paginate($data['per_page']);
        return view('bill.v2.indexv2', $data);
    }

    public function waitForConfirm(Request $request, DebitDetailRepository $xx)
    {
        $data['meta_title'] = 'Duyệt số liệu';
        $data['buildings'] = $this->buildingRepo->getBuildingOfUser($this->building_active_id);
        $data['per_page'] = Cookie::get('per_page', 10);
        $data['bills'] = $this->billRepo->filterWaitForConfirm($this->building_active_id, $request->all(), $data['per_page']);
        $data['status'] = BillRepository::WAIT_FOR_CONFIRM;
        $data['filter'] = $request->all();
        if(isset($data['filter']['bdc_apartment_id'])){
            $data['get_apartment'] = $this->apartmentRepo->findById($data['filter']['bdc_apartment_id']);
         }
         if(isset($data['filter']['ip_place_id'])){
             $data['get_place_building'] = $this->modelBuildingPlace->findById($data['filter']['ip_place_id']);
         }
        if ($this->building_active_id)
        {
            $data['apartments'] = $this->apartmentRepo->getApartmentOfBuildingDebit($this->building_active_id);
        }

        return view('bill.v2.wait_for_confirm', $data);
    }
    public function waitForConfirmEditDateline(Request $request, DebitDetailRepository $xx)
    {
        $data['meta_title'] = 'Duyệt số liệu';
        $data['per_page'] = Cookie::get('per_page', 10);
        $data['filter'] = $request->all();
        if(isset($data['filter']['bdc_apartment_id'])){
            $data['get_apartment'] = $this->apartmentRepo->findById($data['filter']['bdc_apartment_id']);
         }
         if(isset($data['filter']['ip_place_id'])){
             $data['get_place_building'] = $this->modelBuildingPlace->findById($data['filter']['ip_place_id']);
         }
        $page = $request->page ? $request->page : 1;
        $perPage = $data['per_page'];
        $offSet = ($page * $perPage) - $perPage;
        $bills_filter = $this->billRepo->filterBillv3($this->building_active_id, $request->all());
        $itemsForCurrentPage = array_slice($bills_filter, $offSet, $perPage, true);
        $data['bills'] = new LengthAwarePaginator($itemsForCurrentPage, count($bills_filter), $perPage, $page,['path' => route('admin.v2.bill.waitForConfirmEditDateline')]);

        return view('bill.v2.wait_for_confirm_edit_dateline', $data);
    }

    public function waitToSend(Request $request)
    {
        $data['meta_title'] = 'Gửi thông báo';
        $data['buildings'] = $this->buildingRepo->getBuildingOfUser($this->building_active_id);
        $data['per_page'] = Cookie::get('per_page', 10);
        $cycle_names = BdcV2DebitDetailDebitDetailRepository::getCycleName($this->building_active_id);
        $data['cycle_names'] = $cycle_names;
        $data['chose_cycle_name'] = $request->cycle_name ? $request->cycle_name : ($cycle_names ? $cycle_names[0] : '');
        $data['bills'] = $this->billRepo->filterWaitToSend($this->building_active_id,$data['chose_cycle_name'],$request->all(), $data['per_page']);
        $data['status'] = BillRepository::WAIT_TO_SEND;
        $data['filter'] = $request->all();

        if(isset($data['filter']['bdc_apartment_id'])){
            $data['get_apartment'] = $this->apartmentRepo->findById($data['filter']['bdc_apartment_id']);
         }
         if(isset($data['filter']['ip_place_id'])){
             $data['get_place_building'] = $this->modelBuildingPlace->findById($data['filter']['ip_place_id']);
         }
        if ($this->building_active_id)
        {
            $data['apartments'] = $this->apartmentRepo->getApartmentOfBuildingDebit($this->building_active_id);
        }

        return view('bill.v2.wait_to_send', $data);
    }

    public function listPay(Request $request)
    {
        $data['meta_title'] = 'Danh sách bảng kê';
        $data['buildings'] = $this->buildingRepo->getBuildingOfUser($this->building_active_id);
        $data['per_page'] = Cookie::get('per_page', 10);
        $request->request->add(['status' =>  $request->input('status',-2)]);
        $cycle_names = BdcV2DebitDetailDebitDetailRepository::getCycleName($this->building_active_id);
        $data['cycle_names'] = $cycle_names;
        $data['bills'] = $this->billRepo->getBillv2($this->building_active_id, $request->all())->paginate($data['per_page']);
        $data['status'] = BillRepository::WAIT_TO_SEND;
        $data['filter'] = $request->all();
        if(isset($data['filter']['bdc_apartment_id'])){
            $data['get_apartment'] = $this->apartmentRepo->findById($data['filter']['bdc_apartment_id']);
         }
         if(isset($data['filter']['ip_place_id'])){
             $data['get_place_building'] = $this->modelBuildingPlace->findById($data['filter']['ip_place_id']);
         }
        if ($this->building_active_id)
        {
            $data['apartments'] = $this->apartmentRepo->getApartmentOfBuildingDebit($this->building_active_id);
        }

        return view('bill.v2.list_pay', $data);
    }
    public function destroyBill($id)
    {
        $get_list_debit =  BdcV2DebitDetailDebitDetailRepository::findByBillId($id);

        foreach ($get_list_debit as $key => $value) {
            if($value->paid > 0){
                return redirect()->route('admin.v2.bill.listPay')->with('warning', 'bảng kê này đang được thanh toán!');
            }
            $check_last_time_pay = BdcV2DebitDetailDebitDetailRepository::findServiceCheckFromDate($value->bdc_apartment_id,$value->bdc_apartment_service_price_id,$value->to_date);

            if($check_last_time_pay){
                return redirect()->route('admin.v2.bill.listPay')->with('warning', 'hãy kiểm tra bảng kê có cho tiết dịch vụ có ngày tính cuối!'.$check_last_time_pay->to_date);
            }
        }
        try {
            $resultBillDel = $this->billRepo->find($id);
            $action = Helper::getAction();
            if($action){
                $check_lock_cycle = BdcLockCyclenameRepository::checkLock($this->building_active_id,$resultBillDel->cycle_name,$action);
                if($check_lock_cycle){
                    return redirect()->back();
                }
            }
            $resultBillDel->deleted_by = auth()->user()->id;
            $resultBillDel->save();
            $resultBillDel->delete();
            foreach ($get_list_debit as $key => $value) {
                $value->deleted_by = auth()->user()->id;
                $value->sumery = 0;
                $value->bdc_bill_id = 0;
                $value->discount = 0;
                $value->quantity = 0;
                $value->paid = 0;
                $value->price = 0;
                $value->save();
                $diennuoc = json_decode(@$value->detail);
                if(@$diennuoc->data_detail){
                    @$data_detail = @$diennuoc->data_detail;
                    foreach ($data_detail as $key_1 => $value_1) {
                      $electric =  ElectricMeter::find($value_1->id);
                      if($electric)$electric->update(['status'=>0]);
                    }
                }
                $apartmentServicePrice  = @$value->apartmentServicePrice;
                if($apartmentServicePrice){
                    $apartmentServicePrice->last_time_pay = date('Y-m-d', strtotime($value->from_date));
                    $apartmentServicePrice->save();
                }
                QueueRedis::setItemForQueue('add_queue_stat_payment_', [
                    "apartmentId" => $value->bdc_apartment_id,
                    "service_price_id" => $value->bdc_apartment_service_price_id,
                    "cycle_name" => $value->cycle_name,
                ]);
                $value->delete();
            }
           
            return redirect()->route('admin.v2.bill.listPay')->with('success', 'xóa bảng kê thành công!');
        } catch (Exception $e) {
            return redirect()->route('admin.v2.bill.listPay')->with('error', 'xóa bảng kê thất bại!');
        }
       
    }
    public function destroyBySelectItem(Request $request)
    {
        $ids = $request->ids;
        if ($ids) {
            $count = 0;
            foreach ($ids as $key => $id) {

                $get_list_debit =  BdcV2DebitDetailDebitDetailRepository::findByBillId($id);

                foreach ($get_list_debit as $key_1 => $value) {
                    if ($value->paid > 0) {
                        continue;
                    }
                    $check_last_time_pay = BdcV2DebitDetailDebitDetailRepository::findServiceCheckFromDate($value->bdc_apartment_id, $value->bdc_apartment_service_price_id, $value->to_date);

                    if ($check_last_time_pay) {
                        continue;
                    }
                }
                try {
                    $resultBillDel = $this->billRepo->find($id);
                    $action = Helper::getAction();
                    if($action){
                        $check_lock_cycle = BdcLockCyclenameRepository::checkLock($this->building_active_id,$resultBillDel->cycle_name,$action);
                        if($check_lock_cycle){
                            continue;
                        }
                    }
                    $resultBillDel->deleted_by = auth()->user()->id;
                    $resultBillDel->save();
                    $resultBillDel->delete();
                    foreach ($get_list_debit as $key_2 => $value_2) {
                        $value_2->deleted_by = auth()->user()->id;
                        $value_2->sumery = 0;
                        $value_2->bdc_bill_id = 0;
                        $value_2->discount = 0;
                        $value_2->quantity = 0;
                        $value_2->paid = 0;
                        $value_2->price = 0;
                        $value_2->save();
                        $diennuoc = json_decode(@$value_2->detail);
                        if(@$diennuoc->data_detail){
                            @$data_detail = @$diennuoc->data_detail;
                            foreach ($data_detail as $key_3 => $value_3) {
                              $electric =  ElectricMeter::find($value_3->id);
                              if($electric)$electric->update(['status'=>0]);
                            }
                        }
                        QueueRedis::setItemForQueue('add_queue_stat_payment_', [
                            "apartmentId" => $value_2->bdc_apartment_id,
                            "service_price_id" => $value_2->bdc_apartment_service_price_id,
                            "cycle_name" => $value_2->cycle_name,
                        ]);
                        $apartmentServicePrice  = @$value_2->apartmentServicePrice;
                        if ($apartmentServicePrice) {
                            $apartmentServicePrice->last_time_pay = date('Y-m-d', strtotime($value_2->from_date));
                            $apartmentServicePrice->save();
                        }
                        $value_2->delete();
                    }
                } catch (Exception $e) {
                    continue;
                }
                $count++;
            }
            return $count;
        }
        return false;
    }
    public function show($id, Request $request)
    {
        if( $id == 0 ) {
            // dùng khi truyền param bill_code
            // admin/bill/show/0?bil_code=DKM_0000003
            $bill = $this->billRepo->findBillCode($this->building_active_id, $request->input('bill_code'));
            $id = $bill->id;
        } else {
            $bill = $this->billRepo->find($id);
        }

        if (!$bill) {
            return abort(404);
        }
        $data['meta_title'] = 'Chi tiết hóa đơn';
        $data['bill'] = $bill;
        $data['debit_detail'] = BdcV2DebitDetailDebitDetailRepository::getDetailBillId($id);
        return view('bill.v2.show', $data);
    }

    public function export(Request $request)
    {
        return $this->billRepo->export($this->building_active_id, $request);
    }

    public function exportFilter(Request $request) {
        return $this->billRepo->filterBillExport($this->building_active_id, $request->all());
    }
    public function exportFilterBangKeKhachHang(Request $request) {
        return $this->billRepo->filterBillExportBangKeKhachHang($this->building_active_id, $request->all());
    }

    public function changeStatus(Request $request)
    {
        if ($request->ids)
        {
            $status = $this->billRepo->changeMultiStatus($request->ids);
            return response()->json($status);
        }
    }

    public function postChangeStatus(Request $request, DebitDetailRepository $debitRepo, CronJobManagerRepository $cronJobManager)
    {
        if ($request->ids)
        {
            try {
                $bills = $this->billRepo->postChangeMultiStatus($request->ids,$debitRepo, $this->building_active_id);
                if($bills)
                {
                    QueueRedis::setItemForQueue('add_queue_auto_payment_from_coin_', $request->ids);
                }
            } catch (\Exception $e) {

                    return $e->getMessage();
            }
            $dataResponse = [
                'success' => true,
                'message' => 'Thay đổi trạng thái thành công!',
            ];

            return response()->json($dataResponse);
        }
    }
    public function postChangeStatusV2(Request $request)
    {
        $ids = json_decode($request->ids);
        if (count($ids) > 0)
        {
            try {
                $bills = $this->billRepo->postChangeMultiStatus($ids,$request);
                if($bills)
                {
                    QueueRedis::setItemForQueue('add_queue_auto_payment_from_coin_', $ids);
                }
            } catch (\Exception $e) {
                    Log::info('check_change_status_bill',$e->getTraceAsString());
                    return false;
            }
            return true;
        }
    }

    public function action(Request $request)
    {
        $method = $request->input('method','');
        if ($method == 'per_page') {
            $this->per_page($request);
            return back();
        }else if($method == 'change_status_need_confirmation') {
            Bills::whereIn('id',$request->ids)->update(['status'=>-3]); // chuyển trạng thái cần xác nhận
            $debitdetal = DebitDetail::whereIn('bdc_bill_id',$request->ids)->get();
            if($debitdetal){
                foreach ($debitdetal as $key => $value) {
                    QueueRedis::setItemForQueue('add_queue_stat_payment_', [
                        "apartmentId" => $value->bdc_apartment_id,
                        "service_price_id" => $value->bdc_apartment_service_price_id,
                        "cycle_name" => $value->cycle_name,
                    ]);
                }
            }
            return back()->with('success', 'chuyển trạng thái thành công!' );
        }
        else if($method == 'change_notice_needed'){
            Bills::whereIn('id',$request->ids)->update(['status'=>-2]);// chuyển trạng thái Chờ gửi
            $debitdetal = DebitDetail::whereIn('bdc_bill_id',$request->ids)->get();
            if($debitdetal){
                foreach ($debitdetal as $key => $value) {
                    QueueRedis::setItemForQueue('add_queue_stat_payment_', [
                        "apartmentId" => $value->bdc_apartment_id,
                        "service_price_id" => $value->bdc_apartment_service_price_id,
                        "cycle_name" => $value->cycle_name,
                    ]);
                }
            }
            return back()->with('success', 'chuyển trạng thái thành công!');
        }
        else if($method == 'change_paying'){
            Bills::whereIn('id',$request->ids)->update(['status'=>1]);// chuyển trạng thái Chờ thanh toán
            $debitdetal = DebitDetail::whereIn('bdc_bill_id',$request->ids)->get();
            if($debitdetal){
                foreach ($debitdetal as $key => $value) {
                    QueueRedis::setItemForQueue('add_queue_stat_payment_', [
                        "apartmentId" => $value->bdc_apartment_id,
                        "service_price_id" => $value->bdc_apartment_service_price_id,
                        "cycle_name" => $value->cycle_name,
                    ]);
                }
            }
            return back()->with('success', 'chuyển trạng thái thành công!');
        }
        else if($method == 'delete_select_item'){
            $check_del = $this->destroyBySelectItem($request);
            if($check_del > 0){
                return back()->with('success', 'đã xóa ' .$check_del.' bản ghi !');
            }else{
                return back()->with('error', 'xóa thất bại!');
            }
            
        }
        else if($method == 'confirm_notice_needed'){
            $check_change_status = $this->postChangeStatusV2($request);
            if($check_change_status  == true){
                $message = [
                    'status' => true,
                    'message'    => 'Cập nhật trạng thái thành công!',
                ];
                return response()->json($message);
            }else{
                $message = [
                    'status' => false,
                    'message'    => 'Cập nhật trạng thái thất!',
                ];
                return response()->json($message);
            }
            
        }
       
    }
    public function sendMail($email, $type=69){

        $total = ['email'=>1, 'app'=> 0, 'sms'=> 0];
        $campain = Campain::updateOrCreateCampain("Gửi email bill cho ".$email, config('typeCampain.BILL'), null, $total, $this->building_active_id, 0, 0);

         
        $data = [
          'params' => [
              '@ngay' => date('d/m/Y',time()),
          ],
          'cc'=>$email,
          'type'=>$type,
          'status'=>'success',
          'campain_id' => $campain->id
        ];
        try {
            ServiceSendMailV2::setItemForQueue($data);
            return;
        } catch (\Exception $e){
            return $e->getMessage();
        }
    }

    public function reloadPdf(Request $request, ConfigRepository $configRepository)
    {
        $bill = $this->billRepo->findBillCode($this->building_active_id, $request->billCode);
        try {
            if($bill)
            {
                $pdfName = $bill->bill_code;
                $pathPdf = $_SERVER['DOCUMENT_ROOT'] . "/bang-ke/$pdfName.pdf";
                $urlPdf = "bang-ke/$pdfName.pdf";
                $_bill = $this->billRepo->find($bill->id);
                $_bill->url = $urlPdf;
                $_bill->save();
                $building = $bill->building;
                $apartment = $bill->apartment;
                $debit_detail = $this->debitDetailRepo->getDetailBillId($bill->id);
                $totalPaymentDebit = $this->debitDetailRepo->findMaxVersionWithBuildingApartment($this->building_active_id, $bill->bdc_apartment_id, $bill->id);
                $configPdfBill = $configRepository->findByKeyActiveFirst($this->building_active_id, $configRepository::BANGKE_PDF);
                $debitDetails = $this->debitDetailRepo->findMaxVersionPaid($bill->id);
                if($configPdfBill) {
                    if($configPdfBill->value == "mau_1") {
                        $pdf = PDF::loadView('bill.v2.pdf_mau1', compact('debit_detail', 'building', 'apartment', 'bill', 'debitDetails', 'totalPaymentDebit'));
                    } else {
                        $pdf = PDF::loadView('bill.v2.pdf_mau2', compact('debit_detail', 'building', 'apartment', 'bill', 'debitDetails', 'totalPaymentDebit'));
                    }
                } else {
                    $pdf = PDF::loadView('bill.v2.pdf_mau1', compact('debit_detail', 'building', 'apartment', 'bill', 'debitDetails', 'totalPaymentDebit'));
                }
                $pdf->save($pathPdf);
                $dataResponse = [
                    'success' => true,
                    'message' => 'Cập nhật file PDF thành công!',
                ];
                return response()->json($dataResponse);       
            }
        } catch (\Exception $e) {
            return $e->getMessage();
        }
        $dataResponse = [
            'success' => false,
            'message' => "Mã bảng kê $request->billCode không tồn tại.",
        ];
        return response()->json($dataResponse);       
    }


    public function reloadpdfv2(Request $request, ConfigRepository $configRepository)
    {
        try {
            $method = $request->input('method', '');
          
            if ($method == 'download_pdf') {
                if (isset($request->ids)) {
                    $list_ids = $request->ids;
                    if(count($list_ids) > 100){
                        return redirect()->route('admin.v2.bill.listPay')->with('warning', 'tải tối đa 100 hóa đơn.');
                    }
                    $data_bill = null;
                    $building = Building::get_detail_building_by_building_id($this->building_active_id);
                    $building->manager_building = UserInfo::where('pub_user_id',$building->manager_id)->where('type',Users::USER_WEB)->first();
                    foreach ($list_ids as $key => $value) {
                        $_bill = $this->billRepo->find($value);
                        $building_payment_info = PaymentInfo::where('bdc_building_id',$_bill->bdc_building_id)->orderBy('updated_at','desc')->first();
                        if ($building_payment_info) {
                            $data_bill[$value]['building_payment_info'] = $building_payment_info;
                        }
                        $data_bill[$value]['building'] = $building;
                        if ($building && @$building->config_menu == 1) { // kế toán v1
                            $apartment = $_bill->apartment;
                            $debit_detail = $this->debitDetailRepo->getDetailBillId($_bill->id);
                            $totalPaymentDebit = $this->debitDetailRepo->findMaxVersionWithBuildingApartment(@$_bill->building->id, $_bill->bdc_apartment_id, $_bill->id);
                            $total_paid = $this->debitDetailRepo->findMaxVersionPaid_v2(@$_bill->building->id, $_bill->bdc_apartment_id, $_bill->id);
                            if (@$request->version == 2) {
            
                                $debit_detail = BdcV2DebitDetailDebitDetailRepository::getDetailBillId($_bill->id);
                                $data_bill[$value]['debit_detail'] = $debit_detail;
                                $data_bill[$value]['apartment'] = $apartment;
                                $data_bill[$value]['bill'] = $_bill;
                                $data_bill[$value]['buildingPlace'] = BuildingPlace::get_detail_bulding_place_by_bulding_place_id($apartment->building_place_id);
                            }
                        }  
                        if ($building && @$building->config_menu == 2 && @$building->id != 17) { // kế toán v2
                            $apartment = $_bill->apartment;
                            $debit_detail = BdcV2DebitDetailDebitDetailRepository::getDetailBillId($_bill->id);
                                $data_bill[$value]['debit_detail'] = $debit_detail;
                                $data_bill[$value]['apartment'] = $apartment;
                                $data_bill[$value]['bill'] = $_bill;
                                $data_bill[$value]['buildingPlace'] = BuildingPlace::get_detail_bulding_place_by_bulding_place_id($apartment->building_place_id);
                        }
                        if ($building && @$building->config_menu == 2 && @$building->id == 17) { // kế toán v2 áp dụng tòa 17
                            $apartment = $_bill->apartment;
                            $debit_detail = BdcV2DebitDetailDebitDetailRepository::getDetailBillId($_bill->id);
        
                            $data_bill[$value]['debit_detail'] = $debit_detail;
                            $data_bill[$value]['apartment'] = $apartment;
                            $data_bill[$value]['bill'] = $_bill;
                            $data_bill[$value]['buildingPlace'] = BuildingPlace::get_detail_bulding_place_by_bulding_place_id($apartment->building_place_id);
                        }
                    }
                    $configPdfBill = $configRepository->findByKeyActiveFirst($building->id, $configRepository::BANGKE_PDF);
                    if($configPdfBill) {
                        return view('bill.'.$configPdfBill->value,compact('data_bill'));
                    } else {
                        return view('bill.v2.print_list_bill', compact('data_bill'));
                    }
                 
                }
            } else {
                if (isset($request->ids)) {
                    $list_ids = $request->ids;
                    foreach ($list_ids as $key => $value) {
                        $_bill = $this->billRepo->find($value);
                        $urlPdf = "admin/bill/detail/" . $_bill->bill_code;
                        $_bill->url = $urlPdf;
                        $_bill->save();
                    }
                }
                return redirect()->route('admin.v2.bill.listPay')->with('success', 'reload view thành công!!');
            }
        } catch (Exception $e) {
            dd($e);
            return redirect()->route('admin.v2.bill.listPay')->with('error', 'reload view không thành công!!');
        }
          
    }
    public function reloadpdfv3(Request $request, ConfigRepository $configRepository)
    {
            try {
                if(isset($request->ids)){
                    $list_ids = $request->ids;
                    foreach ($list_ids as $key => $value) {
                           $_bill = $this->billRepo->find($value);
                           $pdfName = $_bill->bill_code;
                           $pathPdf = $_SERVER['DOCUMENT_ROOT'] . "/bang-ke/$pdfName.pdf";
                           $urlPdf = "bang-ke/$pdfName.pdf";
                           $_bill->url = $urlPdf;
                           $_bill->save();
                           $building = $_bill->building;
                           $apartment = $_bill->apartment;
                           $debit_detail = $this->debitDetailRepo->getDetailBillId($_bill->id);
                           $totalPaymentDebit = $this->debitDetailRepo->findMaxVersionWithBuildingApartment($this->building_active_id, $_bill->bdc_apartment_id, $_bill->id);
                           $configPdfBill = $configRepository->findByKeyActiveFirst($this->building_active_id, $configRepository::BANGKE_PDF);
                           $debitDetails = $this->debitDetailRepo->findMaxVersionPaid($_bill->id);
                           if($configPdfBill) {
                               if($configPdfBill->value == "mau_1") {
                                   $pdf = PDF::loadView('bill.v2.pdf_mau1', compact('debit_detail', 'building', 'apartment', '_bill', 'debitDetails', 'totalPaymentDebit'));
                               } else {
                                   $pdf = PDF::loadView('bill.v2.pdf_mau2', compact('debit_detail', 'building', 'apartment', '_bill', 'debitDetails', 'totalPaymentDebit'));
                               }
                           } else {
                               $pdf = PDF::loadView('bill.v2.pdf_mau1', compact('debit_detail', 'building', 'apartment', '_bill', 'debitDetails', 'totalPaymentDebit'));
                           }
                           $pdf->save($pathPdf);
                    }
                }
                return redirect()->route('admin.v2.bill.listPay')->with('success', 'Cập nhật file PDF thành công!!');
            } catch (Exception $e) {
                return redirect()->route('admin.v2.bill.listPay')->with('error', 'Cập nhật file PDF không thành công!!');
            }
          
    }

    public function delete(Request $request, DebitDetailRepository $debitDetailRepository)
    {
        try {
            $bill = $this->billRepo->deleteBill($request->id, $debitDetailRepository);
            if($bill) {
                return redirect()->back()->with(['success' => 'Xóa bảng kê thành công!']);
            } else {
                return redirect()->back()->with(['error' => 'Không thể xóa bảng kê khi chưa xóa hết Chi tiết Bảng kê - Dịch vụ.']);
            }            
        } catch (Exception $e) {
            return redirect()->back()->with(['error' => $e->getMessage()]);
        }
        
    }
}
