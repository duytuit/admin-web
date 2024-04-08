<?php

namespace App\Http\Controllers\Debit\V2\Api;

use App\Commons\Helper;
use App\Exceptions\QueueRedis;
use App\Http\Controllers\BuildingController;
use App\Models\BdcApartmentServicePrice\ApartmentServicePrice;
use App\Models\BdcV2DebitDetail\DebitDetail;
use App\Repositories\Apartments\ApartmentsRespository;
use App\Repositories\BdcApartmentDebit\ApartmentDebitRepository;
use App\Repositories\BdcApartmentServicePrice\ApartmentServicePriceRepository;
use App\Repositories\BdcBills\BillRepository;
use App\Repositories\BdcBuildingDebit\BuildingDebitRepository;
use App\Repositories\BdcCoin\BdcCoinRepository;
use App\Repositories\BdcV2DebitDetail\DebitDetailRepository;
use App\Repositories\Building\BuildingRepository;
use App\Repositories\Config\ConfigRepository;
use App\Repositories\Customers\CustomersRespository;
use App\Repositories\Service\ServiceRepository;
use App\Repositories\BdcDebitLogs\DebitLogsRepository;
use App\Repositories\BdcLockCyclename\BdcLockCyclenameRepository;
use App\Services\CronJobService;
use App\Traits\ApiResponse;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Cache;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DebitController extends BuildingController
{
    use ApiResponse;
    
    public $debitRepo;
    public $serviceRepo;
    public $buildingRepo;
    public $apartmentServiceRepo;
    public $apartmentRepo;
    public $apartmentDebitRepo;
    public $buildingDebitRepo;
    public $customersRespository;

    public function __construct(
        Request $request,
        DebitDetailRepository $debitRepo,
        ServiceRepository $serviceRepo,
        BuildingRepository $buildingRepo,
        ApartmentServicePriceRepository $apartmentServiceRepo,
        ApartmentsRespository $apartmentRepo,
        ApartmentDebitRepository $apartmentDebitRepo,
        BuildingDebitRepository $buildingDebitRepo,
        CustomersRespository $customersRespository
    ) {
        //$this->middleware('auth', ['except'=>[]]);
        //$this->middleware('route_permision');
        $this->debitRepo = $debitRepo;
        $this->serviceRepo = $serviceRepo;
        $this->buildingRepo = $buildingRepo;
        $this->apartmentServiceRepo = $apartmentServiceRepo;
        $this->apartmentRepo = $apartmentRepo;
        $this->apartmentDebitRepo = $apartmentDebitRepo;
        $this->buildingDebitRepo = $buildingDebitRepo;
        $this->customersRespository = $customersRespository;
        parent::__construct($request);
    }

    public function reloadProcessDebitDetail() {
        $view = view("debit._reload_process_debit_detail", [
            'data' => CronJobService::get("debit_processing_$this->building_active_id")
        ])->render();
        return $this->responseSuccess([
            'html' => $view
        ]);
    }

    public function loadFormReceiptPrevious($apartmentId)
    {
        $building_id = $this->building_active_id;
        $apartment = $this->apartmentRepo->findById($apartmentId);
        $services = $this->apartmentServiceRepo->filterApartmentId($building_id, $apartmentId);
        if (!$services)
        {
            return $this->responseError("Căn hộ chưa đăng ký sử dụng dịch vụ", 500);
        }
        // lấy chủ hộ của căn hộ
        $_customer = CustomersRespository::findApartmentIdV2($apartmentId, 0);

        $view = view("debit.v2.modal._load_form_receipt_previous", [
            'apartment' => $apartment,
            'services' => $services,
            'customer' => $_customer
        ])->render();

        return $this->responseSuccess([
            'html' => $view
        ]); 
    }

    public function createDebitPrevious(
        Request $request, 
        BillRepository $bill, 
        ApartmentServicePriceRepository $apartmentServicePriceRepository, 
        ConfigRepository $config,
        DebitLogsRepository $debitLogs)
    {
        $input = $request->all();
        $form_list_cong_no = json_decode($input['form_list_cong_no']);
        $building_id = $this->building_active_id;
        $apartmentId = $input['apartmentId'];
        $customerName = $input['customerName'];
        $customerAddress = $input['customerAddress'];
        $serviceId = $input['serviceId'];
        $check_cong_no = true;
        DB::beginTransaction();
        try{
           
            foreach ($form_list_cong_no as $key => $value) {
                $cycle_name = substr($value->cycle_name,0,4).sprintf("%'.02d",substr($value->cycle_name,4,strlen($value->cycle_name)));
                $action = Helper::getAction();
                if ($action) {
                    $check_lock_cycle = BdcLockCyclenameRepository::checkLock($this->building_active_id, $cycle_name, $action);
                    if ($check_lock_cycle) {
                        DB::rollBack();
                        return $this->responseSuccess([], "Kỳ $cycle_name đã được khóa", 200);
                    }
                }
                $apartmentServicePrice = $apartmentServicePriceRepository->findById($serviceId);
                // Lấy thông tin dịch vụ phương tiện đi lại
                $vehicle = $apartmentServicePrice->vehicle;
                if($vehicle != null) {
                    $apartmentServicePrice->name = $vehicle->number;
                }
                $_lastTimePay = Carbon::parse($apartmentServicePrice->last_time_pay);

                $from_date = Carbon::parse($value->from_date);
                $to_date = Carbon::parse($value->to_date);
                if($from_date < $_lastTimePay){
                    DB::rollBack();
                    return $this->responseSuccess([], "thời gian tính '.$value->from_date .' < '. 'thời gian last time pay '.$apartmentServicePrice->last_time_pay"); 
                }
                $dateUsing = $to_date->diffInDays($from_date);
                $quantity = $dateUsing;
                $onePrice = (int)str_replace(',', '', $value->phi_phat_sinh) / $dateUsing;

                $billResult = $bill->create([
                    'bdc_apartment_id' => $apartmentId,
                    'bdc_building_id' => $building_id,
                    'bill_code' => $bill->autoIncrementBillCode($config, $building_id),
                    'cost' =>  isset($value->chiet_khau) ? (int)str_replace(',', '', $value->phi_phat_sinh) - (int)str_replace(',', '', $value->chiet_khau) : (int)str_replace(',', '', $value->phi_phat_sinh),
                    'customer_name' => $customerName,
                    'customer_address' => $customerAddress != null ? $customerAddress : "",
                    'deadline' => $to_date,
                    'provider_address' => 'Banking',
                    'confirm_date' => Carbon::now(),
                    'user_id' =>auth()->user()->id,
                    'accounting_date' =>auth()->user()->id,
                    'is_vat' => 0,
                    'status' => $bill::WAIT_TO_SEND,
                    'notify' => 0,
                    'cycle_name' => $cycle_name
                ]);
    
                $urlPdf = "admin/bill/detail/".$billResult->bill_code;
                $billResult->url = $urlPdf;
                $billResult->save();
    
                // $paid = $sumery < 0 ? abs($sumery) : 0;
                $paid = 0;
                $debit = $this->debitRepo->getDebitByApartmentAndServiceAndCyclenameWithTrashed($apartmentId,$serviceId,$cycle_name);
                // $check_debit = DebitDetail::where([
                //     'bdc_apartment_service_price_id'=>$serviceId,
                //     'cycle_name'=>$cycle_name
                // ])->whereHas('bill',function($query) {
                //     $query->where('status', '>=', -2);
                // })->first();
                // if($check_debit){
                //     DB::rollBack();
                //     return $this->responseSuccess([], "kỳ $cycle_name  đã tồn tại công nợ cho dịch vụ $apartmentServicePrice->name"); 
                // }
                if($debit){
                    if ($debit->deleted_at) {
                        $this->debitRepo->restoreDebitByApartmentAndServiceAndCyclename($debit);
                    }
                    $this->debitRepo->updateDebitRestore(
                    $debit->id,
                    $billResult->id,
                    $from_date,
                    $to_date,
                    '[]',
                    false,
                    false,
                    $quantity,
                    abs($onePrice),
                    isset($value->chiet_khau) ? (int)str_replace(',', '', $value->phi_phat_sinh) - (int)str_replace(',', '', $value->chiet_khau) : (int)str_replace(',', '', $value->phi_phat_sinh),
                    isset($value->chiet_khau) ? (int)str_replace(',', '', $value->chiet_khau) : 0,
                    isset($value->chiet_khau) ? 0 :1,
                    false);
                   
                }else{
                    // Tạo công nợ
                    $this->debitRepo->createDebit(
                        $building_id,
                        $apartmentId,
                        $billResult->id,
                        $serviceId,
                        $cycle_name,
                        $from_date,
                        $to_date,
                        '[]',
                        $quantity,
                        abs($onePrice),
                        isset($value->chiet_khau) ? (int)str_replace(',', '', $value->phi_phat_sinh) - (int)str_replace(',', '', $value->chiet_khau) : (int)str_replace(',', '', $value->phi_phat_sinh),
                        0,
                        isset($value->chiet_khau) ? (int)str_replace(',', '', $value->chiet_khau) : 0,
                        isset($value->chiet_khau) ? 0 :1,
                        "lên công nợ tay",
                        0);
                }
              
                $apartmentServicePriceRepository->update(['last_time_pay' => $to_date], $serviceId);
                QueueRedis::setItemForQueue('add_queue_stat_payment_', [
                    "apartmentId" => $apartmentId,
                    "service_price_id" => $serviceId,
                    "cycle_name" =>$cycle_name,
                ]);
            }
            DB::commit();
            return $this->responseSuccess([], 'Thêm công nợ thành công'); 
          
        }catch(\Exception $e){
            return $this->responseError($e->getMessage(), 500);
            DB::rollBack();
        }
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
                                $_coin = BdcCoinRepository::subCoin((int)$request->building_id,$request->apartmentId,$value->tu_chi_dinh, $cycle_name,$_customer->user_info_id,$so_tien,0,3,$value->den_chi_dinh);
                               // update tăng coin đến chỉ định
                                 BdcCoinRepository::addCoin((int)$request->building_id,$request->apartmentId,$value->den_chi_dinh,$cycle_name,$_customer->user_info_id,$so_tien,0,3,$value->tu_chi_dinh,@$_coin['log']);
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
}
