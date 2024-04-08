<?php

namespace App\Http\Controllers\Receipt\Api;

use App\Http\Controllers\BuildingController;
use Illuminate\Http\Request;
use App\Repositories\BdcBills\BillRepository;
use App\Repositories\BdcDebitDetail\DebitDetailRepository;
use App\Repositories\BdcReceiptLogs\ReceiptLogsRepository;
use App\Repositories\BdcReceipts\ReceiptRepository;
use App\Repositories\Config\ConfigRepository;
use App\Repositories\Customers\CustomersRespository;
use App\Repositories\Service\ServiceRepository;
use App\Traits\ApiResponse;
use Illuminate\Support\Carbon;
use Barryvdh\DomPDF\Facade as PDF;
use App\Commons\Helper;
use App\Models\Apartments\V2\UserApartments;
use DB;
use App\Models\Building\Building;
use App\Models\TransactionPayment\TransactionPayment;
use App\Repositories\Apartments\ApartmentsRespository;
use App\Repositories\TransactionPayment\TransactionPaymentRepository;
use App\Util\Debug\Log;
use Illuminate\Support\Facades\Auth;

class ReceiptController extends BuildingController
{
    use ApiResponse;

    const TYPE_BILL = 1;
    const TYPE_DEBIT = 2;
    const TYPE_RECETPT_PREVIOUS = 3;

    private $debitDetail;
    private $billRepo;
    private $model;
    private $apartmentRepo;
    private $billRepository;
    private $_transactionPaymentRepository;

    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct(Request $request, 
        ReceiptRepository $model, 
        DebitDetailRepository $debitDetailRepository,
        BillRepository $billRepo,
        ApartmentsRespository $apartmentRepo,
        TransactionPaymentRepository $transactionPaymentRepository
        )
    {
        //$this->middleware('auth', ['except'=>[]]);
        //$this->middleware('route_permision');
        Carbon::setLocale('vi');
        $this->debitDetail = $debitDetailRepository;
        $this->billRepo = $billRepo;
        $this->model = $model;
        $this->apartmentRepo = $apartmentRepo;
        $this->billRepository = $billRepo;
        $this->_transactionPaymentRepository = $transactionPaymentRepository;
        parent::__construct($request);
    }

    public function filterByBill(
        Request $request,
        BillRepository $billRepository, 
        CustomersRespository $customer, 
        ReceiptRepository $receiptRepository,
        ServiceRepository $serviceRepository,
        $apartment_id, 
        $service_id, 
        $type)
    {
        $input = $request->all();
        $toDate = @$input['to_date'];
        $fromDate = @$input['from_date'];
        $provisionalReceipt = $input['provisional_receipt'];
        $building_id = $this->building_active_id;
        $bills = $billRepository->findBuildingApartmentId($building_id, $apartment_id);
        $receipts = $receiptRepository->findBuildingApartmentId($building_id, $apartment_id);
        $provisionalReceipts = $this->model->filterApartmentId($apartment_id);
        // dd($receipts);
        // lấy chủ hộ của căn hộ
        $_customer = UserApartments::getPurchaser($apartment_id, 0);
        if(!$_customer){
            return $this->responseError('Không tìm thấy chủ căn hộ.', 404);
        }
        
        $customerInfo = $_customer->user_info_first;
        if(!$customerInfo) {
            return $this->responseError('Không tìm thấy thông tin chủ hộ.', 404);
        }
        $customer_name = $customerInfo->full_name;
        $customer_address = $customerInfo->address;
        $apartment = $_customer->bdcApartment;
        $debitDetails = $this->debitDetail->findMaxVersionWithNewSumeryDiffZero_v2($building_id, $apartment_id, $service_id, $toDate, $fromDate);

        $transactionPaymentReceipt = $this->_transactionPaymentRepository->transactionPaymentReceiptAmount($this->building_active_id,$apartment_id);
       
        if($type == self::TYPE_BILL)
        {            
            $view = view("receipt._bill_apartment", [
                'bills' => $bills,
                'receipts' => $receipts
            ])->render();
            return $this->responseSuccess([
                'html' => $view,
                'billRepository' => $billRepository,
                'customer_name' => $customer_name,
                'vi_can_ho' => isset($transactionPaymentReceipt[0]) ? (int)$transactionPaymentReceipt[0]->so_du : 0,
                'customer_address' => @$apartment->name,
                'ma_khach_hang' => @$apartment->code_customer,
                'ten_khach_hang' => @$apartment->name_customer,
                'provisionalReceipts' => $provisionalReceipts,
            ]);
        }
        else if($type == self::TYPE_DEBIT && $provisionalReceipt == 0)
        {
            $view = view("receipt._bill_services", [
                'bills' => $bills,
                'billRepository' => $billRepository,
                'receipts' => $receipts,
                'debitDetails' => $debitDetails,
                'serviceRepository' => $serviceRepository,
                'buildingId' => $this->building_active_id
            ])->render();
            return $this->responseSuccess([
                'html' => $view,
                'customer_name' => $customer_name,
                'customer_address' => @$apartment->name,
                'ma_khach_hang' => @$apartment->code_customer,
                'ten_khach_hang' => @$apartment->name_customer,
                'vi_can_ho' => isset($transactionPaymentReceipt[0]) ? (int)$transactionPaymentReceipt[0]->so_du : 0,
                'provisionalReceipts' => $provisionalReceipts,
            ]);
        }
        else if($type == self::TYPE_DEBIT && $provisionalReceipt > 0)
        {
            $receipt = $receiptRepository->findByIdIsNotComplete($provisionalReceipt);
            if(!$receipt){
                return $this->responseError('Mã hóa đơn không hợp lệ.', 404);
            }
            $customer_name = $receipt->customer_name;
            $customer_address = $receipt->customer_address;
            $paid_money = $receipt->cost;
            $view = view("receipt._bill_services", [
                'bills' => $bills,
                'billRepository' => $billRepository,
                'receipts' => $receipts,
                'debitDetails' => $debitDetails,
                'serviceRepository' => $serviceRepository,
                'buildingId' => $this->building_active_id
            ])->render();
            return $this->responseSuccess([
                'html' => $view,
                'customer_name' => $customer_name,
                'customer_address' => @$apartment->name,
                'ma_khach_hang' => @$apartment->code_customer,
                'ten_khach_hang' => @$apartment->name_customer,
                'vi_can_ho' => isset($transactionPaymentReceipt[0]) ? (int)$transactionPaymentReceipt[0]->so_du : 0,
                'paid_money' => $paid_money,
                'provisionalReceipts' => $provisionalReceipts,
            ]);
        }
        else
        {
            return $this->responseError([
                'html' => ''
            ], 500);
        }
    }

    public function filterByBill_old(
        Request $request,
        BillRepository $billRepository, 
        CustomersRespository $customer, 
        ReceiptRepository $receiptRepository,
        ServiceRepository $serviceRepository,
        $apartment_id, 
        $service_id, 
        $type)
    {
        $input = $request->all();
        $toDate = @$input['to_date'];
        $fromDate = @$input['from_date'];
        $provisionalReceipt = $input['provisional_receipt'];
        $building_id = $this->building_active_id;
        $bills = $billRepository->findBuildingApartmentId($building_id, $apartment_id);
        $receipts = $receiptRepository->findBuildingApartmentId($building_id, $apartment_id);
        $provisionalReceipts = $this->model->filterApartmentId($apartment_id);
        // dd($receipts);
        // lấy chủ hộ của căn hộ
        $_customer = UserApartments::getPurchaser($apartment_id, 0);
        if(!$_customer){
            return $this->responseError('Không tìm thấy chủ căn hộ.', 404);
        }
        
        $customerInfo = $_customer->user_info_first;
        if(!$customerInfo) {
            return $this->responseError('Không tìm thấy thông tin chủ hộ.', 404);
        }
        $customer_name = $customerInfo->full_name;
        $customer_address = $customerInfo->address;
        $apartment = $_customer->bdcApartment;
        $debitDetails = $this->debitDetail->findMaxVersionWithNewSumeryDiffZero_v2($building_id, $apartment_id, $service_id, $toDate, $fromDate);

        $transactionPaymentReceipt = $this->_transactionPaymentRepository->transactionPaymentReceiptAmount($this->building_active_id,$apartment_id);
       
        if($type == self::TYPE_BILL)
        {            
            $view = view("receipt._bill_apartment", [
                'bills' => $bills,
                'receipts' => $receipts
            ])->render();
            return $this->responseSuccess([
                'html' => $view,
                'billRepository' => $billRepository,
                'customer_name' => $customer_name,
                'vi_can_ho' => isset($transactionPaymentReceipt[0]) ? (int)$transactionPaymentReceipt[0]->so_du : 0,
                'customer_address' => @$apartment->name,
                'ma_khach_hang' => @$apartment->code_customer,
                'ten_khach_hang' => @$apartment->name_customer,
                'provisionalReceipts' => $provisionalReceipts,
            ]);
        }
        else if($type == self::TYPE_DEBIT && $provisionalReceipt == 0)
        {
            $view = view("receipt._bill_services_v2_old", [
                'bills' => $bills,
                'billRepository' => $billRepository,
                'receipts' => $receipts,
                'debitDetails' => $debitDetails,
                'serviceRepository' => $serviceRepository,
                'buildingId' => $this->building_active_id
            ])->render();
            return $this->responseSuccess([
                'html' => $view,
                'customer_name' => $customer_name,
                'customer_address' => @$apartment->name,
                'ma_khach_hang' => @$apartment->code_customer,
                'ten_khach_hang' => @$apartment->name_customer,
                'vi_can_ho' => isset($transactionPaymentReceipt[0]) ? (int)$transactionPaymentReceipt[0]->so_du : 0,
                'provisionalReceipts' => $provisionalReceipts,
            ]);
        }
        else if($type == self::TYPE_DEBIT && $provisionalReceipt > 0)
        {
            $receipt = $receiptRepository->findByIdIsNotComplete($provisionalReceipt);
            if(!$receipt){
                return $this->responseError('Mã hóa đơn không hợp lệ.', 404);
            }
            $customer_name = $receipt->customer_name;
            $customer_address = $receipt->customer_address;
            $paid_money = $receipt->cost;
            $view = view("receipt._bill_services_v2_old", [
                'bills' => $bills,
                'billRepository' => $billRepository,
                'receipts' => $receipts,
                'debitDetails' => $debitDetails,
                'serviceRepository' => $serviceRepository,
                'buildingId' => $this->building_active_id
            ])->render();
            return $this->responseSuccess([
                'html' => $view,
                'customer_name' => $customer_name,
                'customer_address' => @$apartment->name,
                'ma_khach_hang' => @$apartment->code_customer,
                'ten_khach_hang' => @$apartment->name_customer,
                'vi_can_ho' => isset($transactionPaymentReceipt[0]) ? (int)$transactionPaymentReceipt[0]->so_du : 0,
                'paid_money' => $paid_money,
                'provisionalReceipts' => $provisionalReceipts,
            ]);
        }
        else
        {
            return $this->responseError([
                'html' => ''
            ], 500);
        }
    }

    public function filterByBillPhieuChi(
        Request $request,
        BillRepository $billRepository, 
        CustomersRespository $customer, 
        ReceiptRepository $receiptRepository,
        ServiceRepository $serviceRepository,
        $apartment_id, 
        $service_id)
    {
        $input = $request->all();
        $toDate = Carbon::now()->addMonth(-3)->format('Y-m-d');
        $fromDate = Carbon::now()->format('Y-m-d');
        // $provisionalReceipt = $input['provisional_receipt'];
        $building_id = $this->building_active_id;
        $bills = $billRepository->findBuildingApartmentId($building_id, $apartment_id);
        $receipts = $receiptRepository->findBuildingApartmentId($building_id, $apartment_id);
        $provisionalReceipts = $this->model->filterApartmentId($apartment_id);
        // dd($receipts);
        // lấy chủ hộ của căn hộ
        $_customer = UserApartments::getPurchaser($apartment_id, 0);
        if(!$_customer){
            return $this->responseError('Không tìm thấy chủ căn hộ.', 404);
        }
        
        $customerInfo = $_customer->user_info_first;
        if(!$customerInfo) {
            return $this->responseError('Không tìm thấy thông tin chủ hộ.', 404);
        }
        $customer_name = $customerInfo->full_name;
        $customer_address = $customerInfo->address;
        $apartment = $_customer->bdcApartment;
        $debitDetails = $this->debitDetail->findMaxVersionWithPhieuChi($building_id, $apartment_id, $service_id, null, null);
        $view = view("receipt._bill_services_phieu_chi", [
            'bills' => $bills,
            'billRepository' => $billRepository,
            'receipts' => $receipts,
            'debitDetails' => $debitDetails,
            'serviceRepository' => $serviceRepository,
            'buildingId' => $this->building_active_id
        ])->render();
        return $this->responseSuccess([
            'html' => $view,
            'customer_name' => $customer_name,
            'customer_address' => @$apartment->name,
            'provisionalReceipts' => $provisionalReceipts,
        ]);
    }

    public function create(
        Request $request, 
        BillRepository $billRepository, 
        ReceiptRepository $receiptRepository, 
        DebitDetailRepository $debitDetailRepository,
        ConfigRepository $config,
        ServiceRepository $serviceRepository,
        ReceiptLogsRepository $receiptLogsRepository)
    {
        $input = $request->all();
        $customerFullname = $input['customer_fullname'];//
        $dataReceipts = json_decode($input['data_receipt']);
        $customerAddress = $input['customer_address'];//
        $customerDescription = Helper::convert_vi_to_en($input['customer_description']);
        $customerTotalPaid = $input['customer_total_paid'];//
        $typePayment = $input['type_payment']; //
        $type = $input['type'];
        $typeReceipt = $input['typeReceipt'];
        $paid_money = $input['paid_money'];
        $customer_paid_string = $input['customer_paid_string'];
        $provisionalReceipt = $input['provisional_receipt'];
        $vnpay_payment = '';
        $currentTime = Carbon::now()->hour . ':' . Carbon::now()->minute . ':' .Carbon::now()->second;
        $createdDate = $input['created_date'] == null ? Carbon::now() : Carbon::parse($input['created_date']);
        if(Carbon::parse($input['created_date']) > Carbon::now()->addDays(1)) {
            return $this->responseError('Ngày hạch toán không được lớn hơn ngày lập phiếu', 405);
        }
        if($dataReceipts == null) {
            return $this->responseError('Cần lựa chọn dịch vụ để tạo phiếu thu.', 405);
        }
        if($typePayment == 'vi'){
            $transactionPaymentReceipt = $this->_transactionPaymentRepository->transactionPaymentReceiptAmount($this->building_active_id,$request->apartment_id);
            $so_du_can_ho = isset($transactionPaymentReceipt[0]) ? (int)$transactionPaymentReceipt[0]->so_du : 0;
            if($so_du_can_ho - (int)$paid_money < 0){
                return $this->responseError('Số dư trong ví không đủ để thánh toán hóa đơn!', 402);
            }
        }
        if($customer_paid_string < $paid_money){
            return $this->responseError('Số tiền nộp phải lớn hơn hoặc bằng số tiền thanh toán!', 402);
        }
        $name_building= Building::where('id',$this->building_active_id)->first()->name??'';
        $dataReceipts = collect($dataReceipts)->unique('debit_id');
        $paidInt = 0;
        $debitDetail = null;
        \DB::beginTransaction();
        try {
            $listService = array();
            if($type == 1) { // Hóa đơn
                $billIds = array();
                $data = array();
                foreach($dataReceipts as $dataReceipt) {
                    $billCode = $dataReceipt->bill_code;
                    $version = $dataReceipt->version + 1;
                    $serviceId = $dataReceipt->service_id;
                    $bill = $billRepository->findBillCode($this->building_active_id, $billCode);
                    $debitDetails = $bill->debitDetail;
                    $billId = $bill->id;
                    // Tạo Debit Detail
                    foreach($debitDetails as $debitDetail) {
                        if ($debitDetail->is_free == 1) {
                            continue;
                        }
                        $debitDetail->id = null;
                        $debitDetail->paid = $debitDetail->new_sumery;
                        $debitDetail->new_sumery = 0;
                        $debitDetail->created_at = $createdDate;
                        $debitDetail->updated_at = $createdDate;
                        $debitDetail->version = $debitDetail->version + 1;
                        $debitDetail->create_date = Carbon::now();
                        $newDebitDetail = $debitDetail->toArray();
                        $debitDetailRepository->create($newDebitDetail);

                        array_push($listService, $debitDetailRepository);
                        $receiptLogsRepository->create([
                            'bdc_building_id' => $this->building_active_id,
                            'bill_id' => $billId,
                            'bill_code' => $billCode,
                            'bdc_service_id' => $serviceId,
                            'key' => "create_debit_detail",
                            'input' => json_encode($dataReceipt),
                            'data' => json_encode($debitDetailRepository),
                            'message' => "Tạo version công nợ thành công",
                            'status' => 200
                        ]);
                    }
                    $_data = [
                        "bill_code" => "$billCode",
                        "bill_id" => "$billId",
                        "service_id" => "$serviceId",
                        "version" => "$version"
                    ];
                    array_push($billIds, $billCode);
                    array_push($data, $_data);
                    $strBillIds = serialize($billIds);
                    $strData = serialize($data);
                    // $array = unserialize($strBillIds);
                    // dd($billIds);
                }
            } else { // Dịch vụ
                $billIds = array();
                $data = array();
                // xử lý khi tạo phiếu thu trùng lặp
                $receipted = $receiptRepository->LatestRecordDatabaseByDatetime($customerTotalPaid,$request->apartment_id,$typePayment);
                $user_id = auth()->user()->id;
                if($receipted && $receipted->user_id !=  $user_id){
                    return $this->responseError('Hệ thống đã hủy phiếu thu bị trùng!', 402);
                } 
                $base_url=((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == "on") ? "https" : "http"). "://". @$_SERVER['HTTP_HOST'];
                foreach($dataReceipts as $dataReceipt) {
                    $bill = $billRepository->findBillCodeServiceId($this->building_active_id, $dataReceipt->bill_code, $dataReceipt->apartment_service_price_id);
                    $debitDetail = $debitDetailRepository->findDebitById($dataReceipt->debit_id);
                    $billCode = $bill->bill_code;
                    $billId = $bill->id;
                    $version = $dataReceipt->version + 1;
                    $serviceId = $dataReceipt->service_id;
                    $paidInt = str_replace(',', '', $dataReceipt->paid);
                    if($typeReceipt == 'phieu_chi') {
                        $paidInt = $paidInt * -1;
                    }
                    // Tạo Debit Detail
                    if(!$debitDetail){
                        $_data = [
                            "bill_code" => "$billCode",
                            "bill_id" => "$billId",
                            "service_id" => "$serviceId",
                            "version" => "$version",
                            "apartment_service_price_id"=>$dataReceipt->apartment_service_price_id,
                            "message" => "Không tìm thấy bảng kê.".$dataReceipt->debit_id
                        ];
                        array_push($billIds, $billCode);
                        array_push($data, $_data);
                        $strBillIds = serialize($billIds);
                        $strData = serialize($data);
                        continue;
                    }

                    $debitDetail->id = null;
                    $debitDetail->paid = $paidInt;
                    $debitDetail->new_sumery = (int)$debitDetail->new_sumery - (int)$paidInt;
                    $debitDetail->created_at = $createdDate;
                    $debitDetail->updated_at = $createdDate;
                    $debitDetail->version = $debitDetail->version + 1;
                    $debitDetail->create_date = Carbon::now()->format('Y-m-d');
                    $newDebitDetail = $debitDetail->toArray();
                    $result_debitDetail = $debitDetailRepository->create($newDebitDetail);
                    
                    array_push($listService, $newDebitDetail);
                    $receiptLogsRepository->create([
                        'bdc_building_id' => $this->building_active_id,
                        'bill_id' => $billId,
                        'bill_code' => $billCode,
                        'bdc_service_id' => $serviceId,
                        'key' => "create_debit_detail",
                        'input' => json_encode($dataReceipt),
                        'data' => json_encode($debitDetailRepository),
                        'message' => "Tạo version công nợ thành công",
                        'status' => 200
                    ]);
                    
                    $_data = [
                        "bill_code" => "$billCode",
                        "bill_id" => "$billId",
                        "service_id" => "$serviceId",
                        "version" => "$version",
                        "new_debit_id" => $result_debitDetail->id,
                        "apartment_service_price_id"=>$dataReceipt->apartment_service_price_id
                    ];
                    array_push($billIds, $billCode);
                    array_push($data, $_data);
                    $strBillIds = serialize($billIds);
                    $strData = serialize($data);
                    // $array = unserialize($strBillIds);
                    // dd($billIds);
                }
            }
            
            // Xử lý Phiếu thu khác hay tạo mới phiếu thu
            if($paid_money > 0 && $provisionalReceipt > 0){
                if($paid_money < $customerTotalPaid){
                    return $this->responseError('Tiền thanh toán dịch vụ không được lớn hơn số tiền nộp.', 402);
                }
                $receipt = $receiptRepository->findByIdIsNotComplete($provisionalReceipt);
                if(!$receipt){
                    return $this->responseError('Mã hóa đơn không hợp lệ.', 404);
                }
                $nameTemp = $receipt->receipt_code . "-" . Carbon::now()->timestamp;
                $receipt->update(['bdc_bill_id' => $strBillIds, 'url' => $directory."/$nameTemp.pdf", 'status' => $receiptRepository::COMPLETED]);
            }else{
                // Tạo phiếu thu
                if ($typeReceipt == $receiptRepository::PHIEUKETOAN) {
                    $code_receipt = $receiptRepository->autoIncrementAccountingReceiptCode($config, $this->building_active_id);
                } else if ($typeReceipt == $receiptRepository::PHIEUBAOCO) {
                    $code_receipt = $receiptRepository->autoIncrementCreditTransferReceiptCode($config, $this->building_active_id);
                } else if ($typeReceipt == $receiptRepository::PHIEUCHI) {
                    $code_receipt = $receiptRepository->autoIncrementReceiptPaymentSlipCode($config, $this->building_active_id);
                } else {
                    $code_receipt = $receiptRepository->autoIncrementReceiptCode($config, $this->building_active_id);
                }
                $status = $receiptRepository::COMPLETED;
                if($typePayment=='vnpay'){
                    if(!isset($input['bank'])){
                        return $this->responseError('Chưa chọn ngân hàng.', 404);
                    }
                    $vnpay_payment = $this->createPayment($code_receipt,$customerTotalPaid,$customerDescription,1,null,$input['bank']);
                    $status = $receiptRepository::NOTCOMPLETED;
                }
                $nameTemp = $code_receipt . "-" . Carbon::now()->timestamp;
                $receipt = $receiptRepository->create([
                    'bdc_apartment_id' => $dataReceipt->apartment_id,
                    'bdc_building_id' => $this->building_active_id,
                    'receipt_code' => $code_receipt,
                    'cost' => $customerTotalPaid,
                    'cost_paid' => $paid_money,
                    'customer_name' => $customerFullname,
                    'customer_address' => $customerAddress,
                    'provider_address' => 'Banking',
                    'bdc_receipt_total' => 'test',
                    'logs' => json_encode($dataReceipts),
                    'description' => $customerDescription,
                    'type_payment' => $typePayment,
                    'bdc_bill_id' => $strBillIds,
                    'url' => $base_url."/admin/receipt/getReceipt/".$code_receipt,
                    'user_id' => \Auth::id(),
                    'type' => $typeReceipt,
                    'status' => $status,
                    'url_payment' =>$vnpay_payment,
                    'data' => $strData,
                    'create_date' => $createdDate
                ]);

                switch($typePayment){
                    case 'tien_mat':
                        $receipt->type_payment_name = 'Tiền mặt';
                        break;
                    case 'chuyen_khoan':
                        $receipt->type_payment_name = 'Chuyển khoản';
                        break;
                    case 'vnpay':
                        $receipt->type_payment_name = 'VNPay';
                        break;
                }
                //$this->del_check_duplicate_receipt(\Auth::id());

                $receiptLogsRepository->create([
                    'bdc_building_id' => $this->building_active_id,
                    'bill_id' => $billId,
                    'bill_code' => $billCode,
                    'bdc_service_id' => $serviceId,
                    'key' => "create_debit_detail",
                    'input' => json_encode($dataReceipt),
                    'data' => json_encode($receipt),
                    'message' => "Tạo phiếu thu thành công",
                    'status' => 200
                ]);
            }
            
            if($typePayment == 'vi'){
                TransactionPayment::create([
                    'bdc_apartment_id' => $receipt->bdc_apartment_id,
                    'bdc_receipt_id' => $receipt->id,
                    'amount' => $customerTotalPaid,
                    'note' => $code_receipt,
                    'status' => 1, //duyệt
                    'type' => 'chi_tien'
                ]);
            }
           
           
            $urlPdf=$base_url."/admin/receipt/getReceipt/".$receipt->receipt_code;
            
        } catch (\Exception $e) {
            Log::info('check_payment_receipt',$paidInt.'|'.$debitDetail->new_sumery.'|'.json_encode($e->getTraceAsString()));
            \DB::rollBack();
            throw new \Exception("register ERROR: ". $e->getMessage(), 1);
        }
        \DB::commit();
        if($typeReceipt == 'phieu_chi') {
            return $this->responseSuccess(['bill_code' => $billCode, 'url_pdf' => $urlPdf, 'vnpay' => $vnpay_payment], 'Tạo phiếu chi thành công.', 200);
        } else {
            return $this->responseSuccess(['bill_code' => $billCode, 'url_pdf' => $urlPdf, 'vnpay' => $vnpay_payment], 'Tạo phiếu thu thành công.', 200);
        }
    }
    public function reviewReceipt(Request $request, 
    BillRepository $billRepository, 
    ReceiptRepository $receiptRepository, 
    DebitDetailRepository $debitDetailRepository,
    ConfigRepository $config,
    ServiceRepository $serviceRepository,
    ReceiptLogsRepository $receiptLogsRepository)
    {
        $input = $request->all();
        $customerFullname = $input['customer_fullname'];//
        $dataReceipts = json_decode($input['data_receipt']);
        $customerAddress = $input['customer_address'];//
        $customerDescription = Helper::convert_vi_to_en($input['customer_description']);
        $customerTotalPaid = $input['customer_total_paid'];//
        $typePayment = $input['type_payment']; //
        $type = $input['type'];
        $typeReceipt = $input['typeReceipt'];
        $paid_money = $input['paid_money'];
        $provisionalReceipt = $input['provisional_receipt'];
        $vnpay_payment = '';
        $currentTime = Carbon::now()->hour . ':' . Carbon::now()->minute . ':' .Carbon::now()->second;
        $createdDate = $input['created_date'] == null ? Carbon::now() : Carbon::parse($input['created_date']);
        
        if($dataReceipts == null) {
            return $this->responseError('Cần lựa chọn dịch vụ để tạo phiếu thu.', 405);
        }
        if($typePayment == 'vi'){
            $transactionPaymentReceipt = $this->_transactionPaymentRepository->transactionPaymentReceiptAmount($this->building_active_id,$request->apartment_id);
            $so_du_can_ho = isset($transactionPaymentReceipt[0]) ? (int)$transactionPaymentReceipt[0]->so_du : 0;
            if($so_du_can_ho - (int)$paid_money < 0){
                return $this->responseError('Số dư trong ví không đủ để thánh toán hóa đơn!', 402);
            }
        } 
        $billIds = array();
        $data = array();
        $listService = array();
        foreach($dataReceipts as $dataReceipt) {
            $bill = $billRepository->findBillCodeServiceId($this->building_active_id, $dataReceipt->bill_code, $dataReceipt->apartment_service_price_id);
            $debitDetail = $debitDetailRepository->findDebitById($dataReceipt->debit_id);
            $billCode = $bill->bill_code;
            $billId = $bill->id;
            $version = $dataReceipt->version + 1;
            $serviceId = $dataReceipt->service_id;
            $paidInt = str_replace(',', '', $dataReceipt->paid);
            if($typeReceipt == 'phieu_chi') {
                $paidInt = $paidInt * -1;
            }
            // Tạo Debit Detail
            if(!$debitDetail){
                $_data = [
                    "bill_code" => "$billCode",
                    "bill_id" => "$billId",
                    "service_id" => "$serviceId",
                    "version" => "$version",
                    "apartment_service_price_id"=>$dataReceipt->apartment_service_price_id,
                    "message" => "Không tìm thấy bảng kê.".$dataReceipt->debit_id
                ];
                array_push($billIds, $billCode);
                array_push($data, $_data);
                $strBillIds = serialize($billIds);
                $strData = serialize($data);
                continue;
            }

            $debitDetail->id = null;
            $debitDetail->paid = $paidInt;
            $debitDetail->new_sumery = $debitDetail->new_sumery - $paidInt;
            $debitDetail->created_at = $createdDate;
            $debitDetail->updated_at = $createdDate;
            $debitDetail->version = $debitDetail->version + 1;
            $debitDetail->create_date = Carbon::now()->format('Y-m-d');
            $newDebitDetail = $debitDetail->toArray();
            
            array_push($listService, $newDebitDetail);
            
            $_data = [
                "bill_code" => "$billCode",
                "bill_id" => "$billId",
                "service_id" => "$serviceId",
                "version" => "$version",
                "apartment_service_price_id"=>$dataReceipt->apartment_service_price_id
            ];
            array_push($billIds, $billCode);
            array_push($data, $_data);
            $strBillIds = serialize($billIds);
            $strData = serialize($data);
            // $array = unserialize($strBillIds);
            // dd($billIds);
        }
    
        // Xử lý Phiếu thu khác hay tạo mới phiếu thu
        if($paid_money > 0 && $provisionalReceipt > 0){
            if($paid_money < $customerTotalPaid){
                return $this->responseError('Tiền thanh toán dịch vụ không được lớn hơn số tiền nộp.', 402);
            }
            $receipt = $receiptRepository->findByIdIsNotComplete($provisionalReceipt);
            if(!$receipt){
                return $this->responseError('Mã hóa đơn không hợp lệ.', 404);
            }
        }else{
            // Tạo phiếu thu
            if ($typeReceipt == $receiptRepository::PHIEUKETOAN) {
                $code_receipt = $receiptRepository->autoIncrementAccountingReceiptCode($config, $this->building_active_id);
            } else if ($typeReceipt == $receiptRepository::PHIEUBAOCO) {
                $code_receipt = $receiptRepository->autoIncrementCreditTransferReceiptCode($config, $this->building_active_id);
            } else if ($typeReceipt == $receiptRepository::PHIEUCHI) {
                $code_receipt = $receiptRepository->autoIncrementReceiptPaymentSlipCode($config, $this->building_active_id);
            } else {
                $code_receipt = $receiptRepository->autoIncrementReceiptCode($config, $this->building_active_id);
            }
            $status = $receiptRepository::COMPLETED;
            $nameTemp = $code_receipt . "-" . Carbon::now()->timestamp;
            $receipt = [
                'bdc_apartment_id' => $dataReceipt->apartment_id,
                'bdc_building_id' => $this->building_active_id,
                'receipt_code' => $code_receipt,
                'cost' => (int)$customerTotalPaid,
                'cost_paid' => (int)$paid_money,
                'customer_name' => $customerFullname,
                'customer_address' => $customerAddress,
                'provider_address' => 'Banking',
                'bdc_receipt_total' => 'test',
                'logs' => 'test',
                'description' => $customerDescription,
                'type_payment' => $typePayment,
                'bdc_bill_id' => $strBillIds,
                'url' => '',
                'user_id' => \Auth::id(),
                'type' => $typeReceipt,
                'status' => $status,
                'url_payment' =>$vnpay_payment,
                'data' => $strData,
                'create_date' => $createdDate
            ];

            switch($typePayment){
                case 'tien_mat':
                    $receipt['type_payment_name'] = 'Tiền mặt';
                    break;
                case 'chuyen_khoan':
                    $receipt['type_payment_name'] = 'Chuyển khoản';
                    break;
                case 'vnpay':
                    $receipt['type_payment_name'] = 'VNPay';
                    break;
            }
            $receipt = collect($receipt);
        }
        $apartment = $this->apartmentRepo->findById($dataReceipt->apartment_id);
        $view = view("receipt.pdf_v2",compact('receipt','apartment', 'listService', 'serviceRepository'))->render();
        return $this->responseSuccess([
            'html' => $view,
        ]);

    }
    public function reloadPdfBill($billCode, $config ,$apartment)
    {

        $bill = $this->billRepo->findBillCode($this->building_active_id, $billCode);
        try {
            if($bill)
            {
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
                //$apartment = $this->apartmentRepo->findById($bill->bdc_apartment_id);
                $bills = $this->billRepository->getCurrentCycleName($cycleName, $apartment->id);
                
                foreach($bills as $_bill) {
                    $pdfName = $_bill->bill_code;
                    $pathPdf = $_SERVER['DOCUMENT_ROOT'] . "/bang-ke/$pdfName.pdf";
                    $urlPdf = "bang-ke/$pdfName.pdf";
                    $bill = $this->billRepo->find($_bill->id);
                    $bill->url = $urlPdf;
                    $bill->save();
                    $building = $bill->building;
                    $apartment = $bill->apartment;
                    $debit_detail = $this->debitDetail->getDetailBillId($_bill->id);
                    $totalPaymentDebit = $this->debitDetail->findMaxVersionWithBuildingApartment($this->building_active_id, $bill->bdc_apartment_id, $bill->id);
                    $debitDetails = $this->debitDetail->findMaxVersionPaid($_bill->id);
                    $configPdfBill = $config->findByKeyActiveFirst($this->building_active_id, $config::BANGKE_PDF);
                    if($configPdfBill) {
                        if($configPdfBill->value == "mau_1") {
                            $pdf = PDF::loadView('bill.pdf_mau1', compact('debit_detail', 'building', 'apartment', 'bill', 'debitDetails', 'totalPaymentDebit'));
                        } else {
                            $pdf = PDF::loadView('bill.pdf_mau2', compact('debit_detail', 'building', 'apartment', 'bill', 'debitDetails', 'totalPaymentDebit'));
                        }
                    } else {
                        $pdf = PDF::loadView('bill.pdf_mau1', compact('debit_detail', 'building', 'apartment', 'bill', 'debitDetails', 'totalPaymentDebit'));
                    }
                    $pdf->save($pathPdf);
                }
                return true;
            }
        } catch (\Exception $e) {
            dd($e->getMessage());
            return false;
        }
    }

    public function update(
        $id,
        Request $request, 
        BillRepository $billRepository, 
        ReceiptRepository $receiptRepository, 
        DebitDetailRepository $debitDetailRepository,
        ConfigRepository $config,
        ServiceRepository $serviceRepository)
    {
        $input = $request->all();
        $customerFullname = $input['customer_fullname'];
        $dataReceipts = json_decode($input['data_receipt']);
        $customerAddress = $input['customer_address'];
        $customerDescription = $input['customer_description'];
        $customerTotalPaid = $input['customer_total_paid'];
        $typePayment = $input['type_payment'];
        $type = $input['type'];
        $paid_money = $input['paid_money'];
        // $provisionalReceipt = $input['provisional_receipt'];
        $vnpay_payment = '';
        if($dataReceipts == null) {
            return $this->responseError('Cần lựa chọn dịch vụ để tạo phiếu thu.', 405);
        }
        $name_building= Building::where('id',$this->building_active_id)->first()->name??'';
        \DB::beginTransaction();
        try {
            $listService = array();
            if($type == 1) {
                $billIds = array();
                foreach($dataReceipts as $dataReceipt) {
                    $billCode = $dataReceipt->bill_code;
                    $bill = $billRepository->findBillCode($this->building_active_id, $billCode);
                    $debitDetails = $bill->debitDetail;
                    // Tạo Debit Detail
                    foreach($debitDetails as $debitDetail) {
                        if ($debitDetail->is_free == 1) {
                            continue;
                        }
                        $debitDetail->id = null;
                        $debitDetail->paid = $debitDetail->new_sumery;
                        $debitDetail->new_sumery = 0;
                        $debitDetail->created_at = $debitDetail->created_at;
                        $debitDetail->version = $debitDetail->version + 1;
                        $newDebitDetail = $debitDetail->toArray();
                        $debitDetailRepository->create($newDebitDetail);
                        array_push($listService, $debitDetailRepository);
                    }
                    array_push($billIds, $billCode);
                    $strBillIds = serialize($billIds);
                    // $array = unserialize($strBillIds);
                    // dd($billIds);
                }
            } else {
                $billIds = array();
                foreach($dataReceipts as $dataReceipt) {
                    $bill = $billRepository->findBillCodeServiceId($this->building_active_id, $dataReceipt->bill_code, $dataReceipt->apartment_service_price_id);
                    $debitDetails = $debitDetailRepository->findMaxVersionByBillApartmentServiceId($bill->id, $dataReceipt->apartment_service_price_id);
                    $billCode = $bill->bill_code;
                    $paidInt = str_replace(',', '', $dataReceipt->paid);
                    // Tạo Debit Detail
                    foreach($debitDetails as $debitDetail){
                        $debitDetail->id = null;
                        $debitDetail->paid = $paidInt;
                        $debitDetail->new_sumery = $debitDetail->new_sumery - $paidInt;
                        $debitDetail->created_at = $debitDetail->created_at;
                        $debitDetail->version = $debitDetail->version + 1;
                        $newDebitDetail = (array)$debitDetail;
                        $debitDetailRepository->create($newDebitDetail);
                        array_push($listService, $newDebitDetail);
                    }
                    array_push($billIds, $billCode);
                    $strBillIds = serialize($billIds);
                    // $array = unserialize($strBillIds);
                    // dd($billIds);
                }
            }
            // Xử lý Phiếu thu khác hay tạo mới phiếu thu
            if($paid_money > 0 && $id > 0){
                if($paid_money > $customerTotalPaid){
                    \DB::rollBack();
                    return $this->responseError('Tiền thanh toán dịch vụ không được lớn hơn số tiền nộp.', 402);
                }
                $receipt = $receiptRepository->findByIdIsNotComplete($id);
                if(!$receipt){
                    \DB::rollBack();
                    return $this->responseError('Mã hóa đơn không hợp lệ.', 404);
                }
                $nameTemp = $receipt->receipt_code . "-" . Carbon::now()->timestamp;
                $receipt->update(['bdc_bill_id' => $strBillIds, 'url' => $directory."/$nameTemp.pdf", 'status' => $receiptRepository::COMPLETED]);
            }else{
                if($paid_money == 0)
                {
                    \DB::rollBack();
                    return $this->responseError('Số tiền thanh toán phải lớn hơn 0.', 405);
                }
                else
                {
                    \DB::rollBack();
                    return $this->responseError('Mã chứng từ thu không chính xác.', 405);
                }
            }
            
            $building = $receipt->building;
            $apartment = $receipt->apartment;
            $pathPdf = $_SERVER['DOCUMENT_ROOT'] . '/'.$directory."/$nameTemp.pdf";
            $urlPdf = asset($directory."/$nameTemp.pdf");
            $pdf = PDF::loadView('receipt.pdf', compact('receipt', 'building', 'apartment', 'listService', 'serviceRepository'));
            $pdf->save($pathPdf);
        } catch (\Exception $e) {
            \DB::rollBack();
            throw new \Exception("register ERROR: ". $e->getMessage(), 1);
        }
        \DB::commit();
        return $this->responseSuccess(['bill_code' => $billCode, 'url_pdf' => $urlPdf, 'vnpay' => $vnpay_payment], 'Tạo phiếu thu thành công.', 200);
    }
    public function save_old(
        Request $request, 
        BillRepository $billRepository, 
        ReceiptRepository $receiptRepository, 
        DebitDetailRepository $debitDetailRepository,
        ConfigRepository $config,
        ServiceRepository $serviceRepository,
        ReceiptLogsRepository $receiptLogsRepository)
    {
        $input = $request->all();
        $customerFullname = $input['customer_fullname'];//
        $dataReceipts = json_decode($input['data_receipt']);
        $customerAddress = $input['customer_address'];//
        $customerDescription = Helper::convert_vi_to_en($input['customer_description']);
        $customerTotalPaid = $input['customer_total_paid'];//
        $typePayment = $input['type_payment']; //
        $type = $input['type'];
        $typeReceipt = $input['typeReceipt'];
        $paid_money = $input['paid_money'];
        $customer_paid_string = $input['customer_paid_string'];
        $provisionalReceipt = $input['provisional_receipt'];
        $vnpay_payment = '';
        $currentTime = Carbon::now()->hour . ':' . Carbon::now()->minute . ':' .Carbon::now()->second;
        $createdDate = $input['created_date'] == null ? Carbon::now() : Carbon::parse($input['created_date']);
        $total_paid = collect($dataReceipts)->sum('paid');
        if($dataReceipts == null) {
            return $this->responseError('Cần lựa chọn dịch vụ để tạo phiếu thu.', 405);
        }
        if($typePayment == 'vi'){
            $transactionPaymentReceipt = $this->_transactionPaymentRepository->transactionPaymentReceiptAmount($this->building_active_id,$request->apartment_id);
            $so_du_can_ho = isset($transactionPaymentReceipt[0]) ? (int)$transactionPaymentReceipt[0]->so_du : 0;
            if($so_du_can_ho - $total_paid < 0){
                return $this->responseError('Số dư trong ví không đủ để thánh toán hóa đơn!', 402);
            }
        }
        if($customer_paid_string < $total_paid){
            return $this->responseError('Số tiền nộp phải lớn hơn hoặc bằng số tiền thanh toán!', 402);
        }
        $name_building= Building::where('id',$this->building_active_id)->first()->name??'';
        \DB::beginTransaction();
        try {
            $listService = array();
            if($type == 1) { // Hạch toán dịch vụ
                $billIds = array();
                $data = array();
            } else {         // Thu tiền dịch vụ
                $billIds = array();
                $data = array();

                $base_url=((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == "on") ? "https" : "http"). "://". @$_SERVER['HTTP_HOST'];

                foreach($dataReceipts as $dataReceipt) {
                    $bill = $billRepository->findBillCodeServiceId($this->building_active_id, $dataReceipt->bill_code, $dataReceipt->apartment_service_price_id);
                    $debitDetail = $debitDetailRepository->findDebitById($dataReceipt->debit_id);
                    $billCode = $bill->bill_code;
                    $billId = $bill->id;
                    $version = $dataReceipt->version + 1;
                    $serviceId = $dataReceipt->service_id;
                    $paidInt = str_replace(',', '', $dataReceipt->paid);
                    if($typeReceipt == 'phieu_chi') {
                        $paidInt = $paidInt * -1;
                    }
                    // Tạo Debit Detail
                    if(!$debitDetail){
                        $_data = [
                            "bill_code" => "$billCode",
                            "bill_id" => "$billId",
                            "service_id" => "$serviceId",
                            "version" => "$version",
                            "apartment_service_price_id"=>$dataReceipt->apartment_service_price_id,
                            "message" => "Không tìm thấy bảng kê.".$dataReceipt->debit_id
                        ];
                        array_push($billIds, $billCode);
                        array_push($data, $_data);
                        $strBillIds = serialize($billIds);
                        $strData = serialize($data);
                        continue;
                    }
        
                    $debitDetail->id = null;
                    $debitDetail->paid = $paidInt;
                    $debitDetail->new_sumery = $debitDetail->new_sumery - $paidInt;
                    $debitDetail->created_at = $createdDate;
                    $debitDetail->updated_at = $createdDate;
                    $debitDetail->version = $debitDetail->version + 1;
                    $debitDetail->create_date = Carbon::now()->format('Y-m-d');
                    $newDebitDetail = $debitDetail->toArray();
                    $debitDetailRepository->create($newDebitDetail);
                    
                    array_push($listService, $newDebitDetail);
                    $receiptLogsRepository->create([
                        'bdc_building_id' => $this->building_active_id,
                        'bill_id' => $billId,
                        'bill_code' => $billCode,
                        'bdc_service_id' => $serviceId,
                        'key' => "create_debit_detail",
                        'input' => json_encode($dataReceipt),
                        'data' => json_encode($debitDetailRepository),
                        'message' => "Tạo version công nợ thành công",
                        'status' => 200
                    ]);
                    
                    $_data = [
                        "bill_code" => "$billCode",
                        "bill_id" => "$billId",
                        "service_id" => "$serviceId",
                        "version" => "$version",
                        "apartment_service_price_id"=>$dataReceipt->apartment_service_price_id
                    ];
                    array_push($billIds, $billCode);
                    array_push($data, $_data);
                    $strBillIds = serialize($billIds);
                    $strData = serialize($data);
                    // $array = unserialize($strBillIds);
                    // dd($billIds);
                }
            }
            
            // Xử lý Phiếu thu khác hay tạo mới phiếu thu
            if($paid_money > 0 && $provisionalReceipt > 0){
                if($paid_money < $customerTotalPaid){
                    return $this->responseError('Tiền thanh toán dịch vụ không được lớn hơn số tiền nộp.', 402);
                }
                $receipt = $receiptRepository->findByIdIsNotComplete($provisionalReceipt);
                if(!$receipt){
                    return $this->responseError('Mã hóa đơn không hợp lệ.', 404);
                }
                $nameTemp = $receipt->receipt_code . "-" . Carbon::now()->timestamp;
                $receipt->update(['bdc_bill_id' => $strBillIds, 'url' => $directory."/$nameTemp.pdf", 'status' => $receiptRepository::COMPLETED]);
            }else{
                // Tạo phiếu thu
                if ($typeReceipt == $receiptRepository::PHIEUKETOAN) {
                    $code_receipt = $receiptRepository->autoIncrementAccountingReceiptCode($config, $this->building_active_id);
                } else if ($typeReceipt == $receiptRepository::PHIEUBAOCO) {
                    $code_receipt = $receiptRepository->autoIncrementCreditTransferReceiptCode($config, $this->building_active_id);
                } else if ($typeReceipt == $receiptRepository::PHIEUCHI) {
                    $code_receipt = $receiptRepository->autoIncrementReceiptPaymentSlipCode($config, $this->building_active_id);
                } else {
                    $code_receipt = $receiptRepository->autoIncrementReceiptCode($config, $this->building_active_id);
                }
                $status = $receiptRepository::COMPLETED;
                if($typePayment=='vnpay'){
                    if(!isset($input['bank'])){
                        return $this->responseError('Chưa chọn ngân hàng.', 404);
                    }
                    $vnpay_payment = $this->createPayment($code_receipt,$customerTotalPaid,$customerDescription,1,null,$input['bank']);
                    $status = $receiptRepository::NOTCOMPLETED;
                }
                $nameTemp = $code_receipt . "-" . Carbon::now()->timestamp;
                $receipt = $receiptRepository->create([
                    'bdc_apartment_id' => $dataReceipt->apartment_id,
                    'bdc_building_id' => $this->building_active_id,
                    'receipt_code' => $code_receipt,
                    'cost' => $customerTotalPaid,
                    'cost_paid' => $paid_money,
                    'customer_name' => $customerFullname,
                    'customer_address' => $customerAddress,
                    'provider_address' => 'Banking',
                    'bdc_receipt_total' => 'test',
                    'logs' => 'test',
                    'description' => $customerDescription,
                    'ma_khach_hang' => $request->ma_khach_hang,
                    'ten_khach_hang' =>  $request->ten_khach_hang,
                    'tai_khoan_co' => $request->tai_khoan_co,
                    'tai_khoan_no' =>  $request->tai_khoan_no,
                    'ngan_hang' =>  $request->ngan_hang,
                    'type_payment' => $typePayment,
                    'bdc_bill_id' => $strBillIds,
                    'url' => $base_url."/admin/receipt/getReceipt/".$code_receipt,
                    'user_id' => \Auth::id(),
                    'type' => $typeReceipt,
                    'status' => $status,
                    'url_payment' =>$vnpay_payment,
                    'data' => $strData,
                    'create_date' => $createdDate
                ]);

                switch($typePayment){
                    case 'tien_mat':
                        $receipt->type_payment_name = 'Tiền mặt';
                        break;
                    case 'chuyen_khoan':
                        $receipt->type_payment_name = 'Chuyển khoản';
                        break;
                    case 'vnpay':
                        $receipt->type_payment_name = 'VNPay';
                        break;
                }

                $receiptLogsRepository->create([
                    'bdc_building_id' => $this->building_active_id,
                    'bill_id' => $billId,
                    'bill_code' => $billCode,
                    'bdc_service_id' => $serviceId,
                    'key' => "create_debit_detail",
                    'input' => json_encode($dataReceipt),
                    'data' => json_encode($receipt),
                    'message' => "Tạo phiếu thu thành công",
                    'status' => 200
                ]);
            }
            
            if($typePayment == 'vi'){
                TransactionPayment::create([
                    'bdc_apartment_id' => $receipt->bdc_apartment_id,
                    'bdc_receipt_id' => $receipt->id,
                    'amount' => $customerTotalPaid,
                    'note' => $code_receipt,
                    'status' => 1, //duyệt
                    'type' => 'chi_tien'
                ]);
            }
           
           
            $urlPdf=$base_url."/admin/receipt/getReceipt/".$receipt->receipt_code;
            
        } catch (\Exception $e) {
            \DB::rollBack();
            throw new \Exception("register ERROR: ". $e->getMessage(), 1);
        }
        \DB::commit();
        if($typeReceipt == 'phieu_chi') {
            return $this->responseSuccess(['bill_code' => $billCode, 'url_pdf' => $urlPdf, 'vnpay' => $vnpay_payment], 'Tạo phiếu chi thành công.', 200);
        } else {
            return $this->responseSuccess(['bill_code' => $billCode, 'url_pdf' => $urlPdf, 'vnpay' => $vnpay_payment], 'Tạo phiếu thu thành công.', 200);
        }
    }
    public function reviewReceipt_old(Request $request, 
    BillRepository $billRepository, 
    ReceiptRepository $receiptRepository, 
    DebitDetailRepository $debitDetailRepository,
    ConfigRepository $config,
    ServiceRepository $serviceRepository,
    ReceiptLogsRepository $receiptLogsRepository)
    {
        $input = $request->all();
        $customerFullname = $input['customer_fullname'];//
        $dataReceipts = json_decode($input['data_receipt']);
        $customerAddress = $input['customer_address'];//
        $customerDescription = Helper::convert_vi_to_en($input['customer_description']);
        $customerTotalPaid = $input['customer_total_paid'];//
        $typePayment = $input['type_payment']; //
        $type = $input['type'];
        $typeReceipt = $input['typeReceipt'];
        $paid_money = $input['paid_money'];
        $provisionalReceipt = $input['provisional_receipt'];
        $vnpay_payment = '';
        $currentTime = Carbon::now()->hour . ':' . Carbon::now()->minute . ':' .Carbon::now()->second;
        $createdDate = $input['created_date'] == null ? Carbon::now() : Carbon::parse($input['created_date']);
        
        if($dataReceipts == null) {
            return $this->responseError('Cần lựa chọn dịch vụ để tạo phiếu thu.', 405);
        }
        if($typePayment == 'vi'){
            $transactionPaymentReceipt = $this->_transactionPaymentRepository->transactionPaymentReceiptAmount($this->building_active_id,$request->apartment_id);
            $so_du_can_ho = isset($transactionPaymentReceipt[0]) ? (int)$transactionPaymentReceipt[0]->so_du : 0;
            if($so_du_can_ho - (int)$paid_money < 0){
                return $this->responseError('Số dư trong ví không đủ để thánh toán hóa đơn!', 402);
            }
        } 
        $billIds = array();
        $data = array();
        $listService = array();
        foreach($dataReceipts as $dataReceipt) {
            $bill = $billRepository->findBillCodeServiceId($this->building_active_id, $dataReceipt->bill_code, $dataReceipt->apartment_service_price_id);
            $debitDetail = $debitDetailRepository->findDebitById($dataReceipt->debit_id);
            $billCode = $bill->bill_code;
            $billId = $bill->id;
            $version = $dataReceipt->version + 1;
            $serviceId = $dataReceipt->service_id;
            $paidInt = str_replace(',', '', $dataReceipt->paid);
            if($typeReceipt == 'phieu_chi') {
                $paidInt = $paidInt * -1;
            }
            // Tạo Debit Detail
            if(!$debitDetail){
                $_data = [
                    "bill_code" => "$billCode",
                    "bill_id" => "$billId",
                    "service_id" => "$serviceId",
                    "version" => "$version",
                    "apartment_service_price_id"=>$dataReceipt->apartment_service_price_id,
                    "message" => "Không tìm thấy bảng kê.".$dataReceipt->debit_id
                ];
                array_push($billIds, $billCode);
                array_push($data, $_data);
                $strBillIds = serialize($billIds);
                $strData = serialize($data);
                continue;
            }

            $debitDetail->id = null;
            $debitDetail->paid = $paidInt;
            $debitDetail->new_sumery = $debitDetail->new_sumery - $paidInt;
            $debitDetail->created_at = $createdDate;
            $debitDetail->updated_at = $createdDate;
            $debitDetail->version = $debitDetail->version + 1;
            $debitDetail->create_date = Carbon::now()->format('Y-m-d');
            $newDebitDetail = $debitDetail->toArray();
            $debitDetailRepository->create($newDebitDetail);
            
            array_push($listService, $newDebitDetail);
            $receiptLogsRepository->create([
                'bdc_building_id' => $this->building_active_id,
                'bill_id' => $billId,
                'bill_code' => $billCode,
                'bdc_service_id' => $serviceId,
                'key' => "create_debit_detail",
                'input' => json_encode($dataReceipt),
                'data' => json_encode($debitDetailRepository),
                'message' => "Tạo version công nợ thành công",
                'status' => 200
            ]);
            
            $_data = [
                "bill_code" => "$billCode",
                "bill_id" => "$billId",
                "service_id" => "$serviceId",
                "version" => "$version",
                "apartment_service_price_id"=>$dataReceipt->apartment_service_price_id
            ];
            array_push($billIds, $billCode);
            array_push($data, $_data);
            $strBillIds = serialize($billIds);
            $strData = serialize($data);
            // $array = unserialize($strBillIds);
            // dd($billIds);
        }
    
            // Xử lý Phiếu thu khác hay tạo mới phiếu thu
            if($paid_money > 0 && $provisionalReceipt > 0){
                if($paid_money < $customerTotalPaid){
                    return $this->responseError('Tiền thanh toán dịch vụ không được lớn hơn số tiền nộp.', 402);
                }
                $receipt = $receiptRepository->findByIdIsNotComplete($provisionalReceipt);
                if(!$receipt){
                    return $this->responseError('Mã hóa đơn không hợp lệ.', 404);
                }
            }else{
                // Tạo phiếu thu
                if ($typeReceipt == $receiptRepository::PHIEUKETOAN) {
                    $code_receipt = $receiptRepository->autoIncrementAccountingReceiptCode($config, $this->building_active_id);
                } else if ($typeReceipt == $receiptRepository::PHIEUBAOCO) {
                    $code_receipt = $receiptRepository->autoIncrementCreditTransferReceiptCode($config, $this->building_active_id);
                } else if ($typeReceipt == $receiptRepository::PHIEUCHI) {
                    $code_receipt = $receiptRepository->autoIncrementReceiptPaymentSlipCode($config, $this->building_active_id);
                } else {
                    $code_receipt = $receiptRepository->autoIncrementReceiptCode($config, $this->building_active_id);
                }
                $status = $receiptRepository::COMPLETED;
                $nameTemp = $code_receipt . "-" . Carbon::now()->timestamp;
                $receipt = [
                    'bdc_apartment_id' => $dataReceipt->apartment_id,
                    'bdc_building_id' => $this->building_active_id,
                    'receipt_code' => $code_receipt,
                    'cost' => (int)$customerTotalPaid,
                    'cost_paid' => (int)$paid_money,
                    'customer_name' => $customerFullname,
                    'customer_address' => $customerAddress,
                    'provider_address' => 'Banking',
                    'bdc_receipt_total' => 'test',
                    'logs' => 'test',
                    'description' => $customerDescription,
                    'type_payment' => $typePayment,
                    'bdc_bill_id' => $strBillIds,
                    'url' => '',
                    'user_id' => Auth::id(),
                    'type' => $typeReceipt,
                    'status' => $status,
                    'url_payment' =>$vnpay_payment,
                    'data' => $strData,
                    'create_date' => $createdDate
                ];

                switch($typePayment){
                    case 'tien_mat':
                        $receipt['type_payment_name'] = 'Tiền mặt';
                        break;
                    case 'chuyen_khoan':
                        $receipt['type_payment_name'] = 'Chuyển khoản';
                        break;
                    case 'vnpay':
                        $receipt['type_payment_name'] = 'VNPay';
                        break;
                }
                $receipt = collect($receipt);
            }
            $apartment = $this->apartmentRepo->findById($dataReceipt->apartment_id);
            $view = view("receipt.pdf_v2",compact('receipt','apartment', 'listService', 'serviceRepository'))->render();
            return $this->responseSuccess([
                'html' => $view,
            ]);

    }
   
}
