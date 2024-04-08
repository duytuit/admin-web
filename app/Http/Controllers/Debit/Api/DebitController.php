<?php

namespace App\Http\Controllers\Debit\Api;

use App\Http\Controllers\BuildingController;
use App\Repositories\Apartments\ApartmentsRespository;
use App\Repositories\BdcApartmentDebit\ApartmentDebitRepository;
use App\Repositories\BdcApartmentServicePrice\ApartmentServicePriceRepository;
use App\Repositories\BdcBills\BillRepository;
use App\Repositories\BdcBuildingDebit\BuildingDebitRepository;
use App\Repositories\BdcDebitDetail\DebitDetailRepository;
use App\Repositories\Building\BuildingRepository;
use App\Repositories\Config\ConfigRepository;
use App\Repositories\Customers\CustomersRespository;
use App\Repositories\Service\ServiceRepository;
use App\Services\CronJobService;
use App\Traits\ApiResponse;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Cache;
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

        $view = view("debit.modal._load_form_receipt_previous", [
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
        ConfigRepository $config)
    {
        $input = $request->all();
        $building_id = $this->building_active_id;
        $apartmentId = $input['apartmentId'];
        $sumery = str_replace(',', '', $input['sumery']);
        $customerName = $input['customerName'];
        $customerAddress = $input['customerAddress'];
        $toDate = $input['toDatePrevious'];
        $fromDate = $input['fromDatePrevious'];
        $cycleName = $input['cycleName'];
        $serviceId = $input['serviceId'];

        \DB::beginTransaction();
        try{
            $apartmentServicePrice = $apartmentServicePriceRepository->findById($serviceId);
            $service = $apartmentServicePrice->service;
            // Lấy thông tin dịch vụ phương tiện đi lại
            $vehicle = $apartmentServicePrice->vehicle;
            if($vehicle != null) {
                $apartmentServicePrice->name = $vehicle->number;
            }
            
            // $debitDetail = $this->debitRepo->findServiceBetweenDate($building_id, $apartmentId, $serviceId, $toDate, $fromDate);
            // if($debitDetail != null){
            //     return $this->responseError('Thời gian thiết lập công nợ này đã tồn tại ngày.'.$debitDetail->to_date, 301); 
            // }

            $_firstTimeActive = Carbon::parse($fromDate);
            $_toDate = Carbon::parse($toDate);
            $_lastTimePay = Carbon::parse($apartmentServicePrice->last_time_pay);
            // $endTime = $xx->diff($xxx)->days;
            if($_firstTimeActive < $_lastTimePay) {
                if($_toDate < $_lastTimePay) {
                    $formatDate  = $_lastTimePay->format('d/m/Y');
                    return $this->responseError("Thời gian công nợ mới nhất của dịch vụ $apartmentServicePrice->name là ngày $formatDate.", 301); 
                }
                $fromDate = $apartmentServicePrice->last_time_pay;
            }
            else if($_firstTimeActive > $_lastTimePay) {
                $fromDate = $fromDate;
            }
            else{
                $fromDate = $apartmentServicePrice->last_time_pay;
            }

            $dateUsing = Carbon::parse($toDate)->diffInDays(Carbon::parse($fromDate));
            $quantity = $dateUsing;
            $onePrice = $sumery / $dateUsing;

          

            $billResult = $bill->create([
                'bdc_apartment_id' => $apartmentId,
                'bdc_building_id' => $building_id,
                'bill_code' => $bill->autoIncrementBillCode($config, $building_id),
                'cost' => $sumery,
                'customer_name' => $customerName,
                'customer_address' => $customerAddress != null ? $customerAddress : "",
                'deadline' => Carbon::parse($toDate),
                'provider_address' => 'Banking',
                'is_vat' => 0,
                'status' => $bill::WAIT_TO_SEND,
                'notify' => 0,
                'cycle_name' => $cycleName,
                'user_id' => auth()->user()->id
            ]);

            $urlPdf = "admin/bill/detail/".$billResult->bill_code;
            $billResult->url = $urlPdf;
            $billResult->save();

            // $paid = $sumery < 0 ? abs($sumery) : 0;
            $paid = 0;

            $this->debitRepo->create([
                'bdc_building_id' => $building_id,
                'bdc_bill_id' => $billResult->id,
                'bdc_apartment_id' => $apartmentId,
                'bdc_service_id' => $service->id,
                'bdc_apartment_service_price_id' => $serviceId,
                'title' => $apartmentServicePrice->name,
                'from_date' =>  Carbon::parse($fromDate),
                'to_date' => Carbon::parse($toDate),
                'detail' => 'test',
                'version' => 0,
                'sumery' => $sumery,
                'new_sumery' => $sumery,
                'previous_owed' => 0,
                'paid' => $paid,
                'is_free' => 0,
                'cycle_name' => $cycleName,
                'price' => abs($onePrice),
                'quantity' => $quantity,
                'bdc_price_type_id' => 1,
                'price_current' => $apartmentServicePrice->price
            ]);

            //$apartmentServicePriceRepository->update(['last_time_pay' =>date('Y-m-d', strtotime($toDate . "+1 days"))], $serviceId);
            $apartmentServicePriceRepository->update(['last_time_pay' => $_toDate], $serviceId);
            \DB::commit();
            return $this->responseSuccess([], 'Thêm công nợ thành công'); 
        }catch(\Exception $e){
            return $this->responseError($e->getMessage(), 500);
            \DB::rollBack();
        }
    }
}
