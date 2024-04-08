<?php

namespace App\Http\Controllers\Receipt;

use App\Http\Controllers\BuildingController;
use App\Http\Requests\Receipt\ReceiptRequest;
use App\Repositories\BdcBills\BillRepository;
use App\Repositories\BdcDebitDetail\DebitDetailRepository;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\Apartments\ApartmentsRespository;
use App\Repositories\BdcApartmentServicePrice\ApartmentServicePriceRepository;
use App\Repositories\BdcReceipts\ReceiptRepository;
use App\Repositories\Config\ConfigRepository;
use App\Repositories\Service\ServiceRepository;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade as PDF;
use Illuminate\Support\Facades\Cookie;
use App\Services\ConvertMoney;
use App\Commons\Helper;
use App\Models\BdcAccountingAccounts\AccountingAccounts;
use App\Models\BdcPaymentDetails\PaymentDetail;
use App\Models\PaymentInfo\PaymentInfo;
use App\Models\TransactionPayment\TransactionPayment;
use App\Repositories\BdcV2LogCoinDetail\LogCoinDetailRepository;
use Exception;
use App\Repositories\Building\BuildingPlaceRepository;
use App\Repositories\TransactionPayment\TransactionPaymentRepository;
use Illuminate\Support\Facades\DB;

class ReceiptController extends BuildingController
{
    public $receiptRepo;
    public $apartmentRepo;
    public $billRepo;
    public $debitRepo;
    public $_configRepository;
    private $modelBuildingPlace;
    private $_transactionPaymentRepository;

    public function __construct(
        Request $request,
        ReceiptRepository $receiptRepo,
        ApartmentsRespository $apartmentRepo,
        BillRepository $billRepo,
        DebitDetailRepository $debitRepo,
        ConfigRepository $configRepository,
        BuildingPlaceRepository $modelBuildingPlace,
        TransactionPaymentRepository $transactionPaymentRepository
    ) {
        parent::__construct($request);
        $this->receiptRepo = $receiptRepo;
        $this->apartmentRepo = $apartmentRepo;
        $this->billRepo = $billRepo;
        $this->debitRepo = $debitRepo;
        $this->_configRepository = $configRepository;
        $this->modelBuildingPlace = $modelBuildingPlace;
        $this->_transactionPaymentRepository = $transactionPaymentRepository;
        $this->middleware('auth', ['except' => []]);
        //$this->middleware('route_permision');
        Carbon::setLocale('vi');
    }

    public function index(Request $request)
    {
        $data['meta_title'] = 'Quản lý phiếu thu';
        $data['per_page'] = Cookie::get('per_page', 10);
        $data['apartments'] = $this->apartmentRepo->getApartmentOfBuilding($this->building_active_id);
        $data['filter'] = $request->all();
        if(isset($data['filter']['bdc_apartment_id'])){
            $data['get_apartment'] = $this->apartmentRepo->findById($data['filter']['bdc_apartment_id']);
        }
        if(isset($data['filter']['ip_place_id'])){
            $data['get_place_building'] = $this->modelBuildingPlace->findById($data['filter']['ip_place_id']);
        }
        $data['receipts'] = $this->receiptRepo->filterReceipt($request->all(), $this->building_active_id)->paginate($data['per_page']);
        $data['sumPrice'] = $data['receipts']->sum('cost');
        $data['sumPriceTotal'] = $this->receiptRepo->getAllReceiptBuilding(null, $this->building_active_id)->sum('cost');
        return view('receipt.index', $data);
    }

    public function kyquy(Request $request)
    {
        $data['meta_title'] = 'Quản lý phiếu thu';
        $data['per_page'] = Cookie::get('per_page', 10);
        $data['apartments'] = $this->apartmentRepo->getApartmentOfBuilding($this->building_active_id);
        $data['configs'] = $this->_configRepository->findByMultiKeyByReceiptDeposit($this->building_active_id);
        $data['filter'] = $request->all();
        if(isset($data['filter']['bdc_apartment_id'])){
            $data['get_apartment'] = $this->apartmentRepo->findById($data['filter']['bdc_apartment_id']);
        }
        if(isset($data['filter']['ip_place_id'])){
            $data['get_place_building'] = $this->modelBuildingPlace->findById($data['filter']['ip_place_id']);
        }

        $data['receipts'] = $this->receiptRepo->filterReceiptDeposit($request->all(), $this->building_active_id)->paginate($data['per_page']);

        $data['sumPrice'] = $data['receipts']->sum('cost');

        $fromDate = isset($request['created_at_from_date']) ? $request['created_at_from_date'] : null;
        $toDate = isset($request['created_at_to_date']) ? $request['created_at_to_date'] : null;
        $daukyPhieuThuTruoc = $fromDate != null ? $this->receiptRepo->dauKy($this->building_active_id, ReceiptRepository::PHIEUTHU_KYQUY, $fromDate)->sum('cost') : 0;
        $daukyPhieuChiKhac = $fromDate != null ? $this->receiptRepo->dauKy($this->building_active_id, ReceiptRepository::PHIEUHOAN_KYQUY, $fromDate)->sum('cost') : 0;
        $data['totalPhieuThuTruoc'] = $totalPhieuThuTruoc = $this->receiptRepo->totalCost($this->building_active_id, ReceiptRepository::PHIEUTHU_KYQUY, $fromDate, $toDate)->sum('cost');
        $data['totalPhieuChiKhac'] = $totalPhieuChiKhac = $this->receiptRepo->totalCost($this->building_active_id, ReceiptRepository::PHIEUHOAN_KYQUY, $fromDate, $toDate)->sum('cost');
        $data['totalDauKy'] = $totalDauKy = $daukyPhieuThuTruoc - $daukyPhieuChiKhac;
        $data['totalCuoiKy'] = $totalDauKy + $totalPhieuThuTruoc - $totalPhieuChiKhac;
        $data['sumPriceTotal'] = $this->receiptRepo->getAllReceiptBuildingKyQuy(null, $this->building_active_id)->sum('cost');
        return view('receipt.kyquy', $data);
    }

    public function create(Request $request, ApartmentsRespository $apartmentsRespository, ServiceRepository $serviceRepository)
    {
       
        $meta_title = 'Tạo phiếu thu';
        $services = $serviceRepository->filterBuildingId($this->building_active_id);
        $apartments = $apartmentsRespository->getApartmentOfBuildingV3($this->building_active_id);
        if(isset($request->apartmentId)){
            $transactionPaymentReceipt = $this->_transactionPaymentRepository->transactionPaymentReceiptAmount($this->building_active_id,$request->apartmentId);
        }
        return view('receipt.create', [
            'meta_title' => 'Tạo phiếu thu',
            'apartments' => $apartments,
            'apartmentId' => @$request->apartmentId, 
            'vi_can_ho' => isset($transactionPaymentReceipt[0]) ? (int)$transactionPaymentReceipt[0]->so_du : 0,
            'building_id' => $this->building_active_id,
            'services' => $services,
            'active_building' => null,
            'banks' => Helper::banks()
            // 'user' => Auth::user()->BDCprofile
        ]);
    }

    public function create_old(Request $request, ApartmentsRespository $apartmentsRespository, ServiceRepository $serviceRepository)
    {
       
        $meta_title = 'Tạo phiếu thu';
        $services = $serviceRepository->filterBuildingId($this->building_active_id);
        $apartments = $apartmentsRespository->getApartmentOfBuildingV3($this->building_active_id);
        if(isset($request->apartmentId)){
            $transactionPaymentReceipt = $this->_transactionPaymentRepository->transactionPaymentReceiptAmount($this->building_active_id,$request->apartmentId);
        }
        $tai_khoan_ngan_hang = PaymentInfo::lists($this->building_active_id);
        $tai_khoan_ke_toan_phieu_thu = AccountingAccounts::lists($this->building_active_id);
        return view('receipt.create_v2_old', [
            'meta_title' => 'Tạo phiếu thu',
            'apartments' => $apartments,
            'apartmentId' => @$request->apartmentId, 
            'vi_can_ho' => isset($transactionPaymentReceipt[0]) ? (int)$transactionPaymentReceipt[0]->so_du : 0,
            'building_id' => $this->building_active_id,
            'services' => $services,
            'active_building' => null,
            'banks' => Helper::banks(),
            'tai_khoan_ngan_hang' => $tai_khoan_ngan_hang,
            'tai_khoan_ke_toan_phieu_thu' => $tai_khoan_ke_toan_phieu_thu,
        ]);
    }

    public function createPhieuChi(ApartmentsRespository $apartmentsRespository, ServiceRepository $serviceRepository)
    {
       
        $meta_title = 'Tạo phiếu thu';
        $apartments = $apartmentsRespository->findByBuildingId($this->building_active_id);
        $services = $serviceRepository->filterBuildingId($this->building_active_id);
        return view('receipt.create_phieu_chi', [
            'meta_title' => 'Tạo phiếu chi',
            'apartments' => $apartments,
            'building_id' => $this->building_active_id,
            'services' => $services,
            'banks' => Helper::banks()
            // 'user' => Auth::user()->BDCprofile
        ]);
    }

    public function show($id)
    {
        $data['meta_title'] = 'Chi tiết phiếu thu';
        $data['receiptRepo'] = $this->receiptRepo;
        $data['receipt'] = $this->receiptRepo->findReceiptById($id);
        $data['bill'] = unserialize($data['receipt']->bdc_bill_id)[0];
        $data['number_cost'] = ConvertMoney::NumberToWords($data['receipt']->cost);
        // dd($data);
        return view('receipt.show', $data);
    }


    public function demo()
    {
        $dfgfdg = [
                [
                    'id'  => 51100,
                    'bdc_service_id'  => 753,
                    'bdc_price_type_id'  => 1,
                    'bdc_apartment_id'  => 8155,
                    'name'  => "29P1-28831",
                    'price'  => 0,
                    'first_time_active'  => "0000-00-00",
                    'last_time_pay'  => "2021-12-01",
                    'bdc_vehicle_id'  => 5295,
                    'bdc_building_id'  => 71,
                    'bdc_progressive_id'  => 225,
                    'created_at'  => "2021-08-13 19:27:17",
                    'updated_at'  => "2021-11-04 10:33:21",
                    'deleted_at'  => null,
                    'description'  => null,
                    'floor_price'  => null,
                    'status'  => 1,
                    'user_id'  => 0,
                    'customer_name'  => "Lê Thị Minh Hiền",
                    'customer_address'  => null,
                    'provider_address'  => "test",
                    'deadline'  => "2021-11-19",
                    'from_date'  => "2021-12-01",
                    'to_date'  => "2021-12-1",
                    'free'  => 0,
                    'dateUsing'  => 0,
                    'service_name'  => "Phí dịch vụ - Xe máy cư dân - 1",
                    'apartment_name'  => "2302",
                    'quantity'  => 0,
                    'one_price'  => 2666,
                    'isNextCycle'  => true,
                    'cycle_name'  => "202111" 
                ],
                [
                    'id' => 68551,
                    'bdc_service_id' => 754,
                    'bdc_price_type_id' => 1,
                    'bdc_apartment_id' => 8155,
                    'name' => "29L5-46751",
                    'price' => 80000,
                    'first_time_active' => "2021-11-01",
                    'last_time_pay' => "2021-11-01",
                    'bdc_vehicle_id' => 11327,
                    'bdc_building_id' => 71,
                    'bdc_progressive_id' => 225,
                    'created_at' => "2021-11-16 10:00:31",
                    'updated_at' => "2021-11-16 10:00:31",
                    'deleted_at' => null,
                    'description' => null,
                    'floor_price' => null,
                    'status' => 1,
                    'user_id' => 0,
                    'customer_name' => "Lê Thị Minh Hiền",
                    'customer_address' => null,
                    'provider_address' => "test",
                    'deadline' => "2021-11-19",
                    'from_date' => "2021-11-01",
                    'to_date' => "2021-12-1",
                    'free' => 0,
                    'dateUsing' => 30,
                    'service_name' => "Phí dịch vụ - Xe máy cư dân - 2",
                    'apartment_name' => "2302",
                    'quantity' => 30,
                    'one_price' => 2666,
                    'isNextCycle' => false,
                    'cycle_name' => "202111",
                ],
                [
                    'id' => 68552,
                    'bdc_service_id' => 754,
                    'bdc_price_type_id' => 1,
                    'bdc_apartment_id' => 8155,
                    'name' => "29L5-46751",
                    'price' => 80000,
                    'first_time_active' => "2021-11-01",
                    'last_time_pay' => "2021-11-01",
                    'bdc_vehicle_id' => 11327,
                    'bdc_building_id' => 71,
                    'bdc_progressive_id' => 225,
                    'created_at' => "2021-11-16 10:00:31",
                    'updated_at' => "2021-11-16 10:00:31",
                    'deleted_at' => null,
                    'description' => null,
                    'floor_price' => null,
                    'status' => 1,
                    'user_id' => 0,
                    'customer_name' => "Lê Thị Minh Hiền",
                    'customer_address' => null,
                    'provider_address' => "test",
                    'deadline' => "2021-11-19",
                    'from_date' => "2021-11-01",
                    'to_date' => "2021-12-1",
                    'free' => 0,
                    'dateUsing' => 0,
                    'service_name' => "Phí dịch vụ - Xe máy cư dân - 2",
                    'apartment_name' => "2302",
                    'quantity' => 30,
                    'one_price' => 2666,
                    'isNextCycle' => false,
                    'cycle_name' => "202111",
                ]
        ];
        $dk = (object)collect($dfgfdg)->sortBy('dateUsing', SORT_REGULAR, true)->sortBy('isNextCycle')->toArray();
        dd($dk);
    }

    public function exportPDF()
    {
        $pdf = PDF::loadView('receipt.pdf');
        $pdf->save($_SERVER['DOCUMENT_ROOT'] . '/phieuthu/xxx.pdf');
        return $pdf->stream('demo.pdf');
    }

    public function createReceiptPrevious(ApartmentsRespository $apartmentsRespository, ConfigRepository $configRepository)
    {
        $meta_title = 'Tạo phiếu thu';
        $apartments = $apartmentsRespository->findByBuildingId($this->building_active_id);
        $configs = $configRepository->findByKey($this->building_active_id, $configRepository::PROVISIONAL_RECEIPT);
        return view('receipt.create_apartment', [
            'meta_title' => 'Tạo phiếu thu',
            'apartments' => $apartments,
            'configs' => $configs,
            'building_id' => $this->building_active_id,
            // 'user' => Auth::user()->BDCprofile
        ]);
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $receipt = $this->receiptRepo->findReceiptById($id);
            if ($receipt) {
                $bills = unserialize($receipt->data);
                if($bills){
                    foreach ($bills as $_bill) {
                        $billId = $_bill['bill_id'];
                        $billCode = $_bill['bill_code'];
                        $serviceId = $_bill['service_id'];
                        $version = $_bill['version'];
                        $checkVersion = $this->debitRepo->checkBillIdVersion($billId, $serviceId, $version);
                        if($checkVersion) {
                            DB::rollBack();
                            return redirect()->route('admin.receipt.index')->with('error', "Không thể xóa phiếu thu do có liên quan đến phiếu thu khác của Bảng kê $billCode chưa được xử lý.");
                        }
                        $this->debitRepo->filterBillIdVersion($billId, $serviceId, $version)->delete();
                    }
                }
                $check_transactionPayment = TransactionPayment::where('bdc_receipt_id',$id)->first();
                if($check_transactionPayment){
                    TransactionPayment::create([
                        'bdc_apartment_id' => $receipt->bdc_apartment_id,
                        'bdc_receipt_id' => $receipt->id,
                        'amount' => $receipt->cost,
                        'note' =>  $receipt->receipt_code,
                        'status' => 1, //duyệt
                        'type' => 'hoan_tien'
                    ]);
                }
                $receipt->delete();
                DB::commit();
                return redirect()->route('admin.receipt.index')->with('success', 'Xóa phiếu thu thành công.');
            }
            return redirect()->route('admin.receipt.index')->with('error', "Mã phiếu thu $id không tồn tại.");
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->route('admin.receipt.index')->with('error', $e->getMessage());
        }
    }

    public function export()
    {
        return $this->receiptRepo->excelReceiptIndex($this->building_active_id);
    }

    public function exportFilterSoQuyTienMat(Request $request)
    {
        set_time_limit(0);
        return $this->receiptRepo->filterReceiptExcelNew($this->building_active_id, $request->all());
    }
    public function exportFilterThuChi(Request $request)
    {
        set_time_limit(0);
        return $this->receiptRepo->filterReceiptExcel($this->building_active_id, $request->all());
    }

    public function exportFilterReceiptDeposit(Request $request)
    {
        set_time_limit(0);
        return $this->receiptRepo->filterReceiptDepositExcel($this->building_active_id, $request->all());
    }

    public function exportDetailFilter(Request $request, DebitDetailRepository $debitDetailRepository, ServiceRepository $serviceRepository)
    {
        set_time_limit(0);
        return $this->receiptRepo->filterReceiptExcelDetail($this->building_active_id, $request->all(), $debitDetailRepository);
    }

    public function edit($id)
    {
        $data['meta_title'] = 'Sửa phiếu thu';
        $data['receipt'] = $this->receiptRepo->findReceiptById($id);
        return view('receipt.edit', $data);
    }

    public function update($id, ReceiptRequest $request)
    {
        $this->receiptRepo->updateReceipt($id, $request);
        return redirect()->route('admin.receipt.index')->with('success', 'Sửa phiếu thu thành công.');
    }

    public function reload_pdf(ServiceRepository $serviceRepository,$id)
    {
        // $data['meta_title'] = 'reload pdf phiếu thu';

        // $receipt = $this->receiptRepo->findReceiptById($id);
        
        // $building = $receipt->building;

        // $apartment = $receipt->apartment;

        // $name_building = @$building->name;
        // $nameTemp = $receipt->receipt_code . "-" . Carbon::now()->timestamp;

        // $pathPdf = $_SERVER['DOCUMENT_ROOT'] .'/'. $directory."/$nameTemp.pdf";

        // $get_bill = unserialize($receipt->data);

        // $listService = (array)$this->debitRepo->findMaxVersionByBillId($get_bill[0]['bill_id']);

        // $receipt->url = $directory."/$nameTemp.pdf";

        // $receipt->save();

        // switch($receipt->type_payment){
        //     case 'tien_mat':
        //         $receipt->type_payment_name = 'Tiền mặt';
        //         break;
        //     case 'chuyen_khoan':
        //         $receipt->type_payment_name = 'Chuyển khoản';
        //         break;
        //     case 'vnpay':
        //         $receipt->type_payment_name = 'VNPay';
        //         break;
        // }

        //return view("receipt.pdf_v3", compact('receipt', 'building', 'apartment', 'listService', 'serviceRepository'));

        // $pdf = PDF::loadView('receipt.pdf_v3', compact('receipt', 'building', 'apartment', 'listService', 'serviceRepository'));

        // $pdf->save($pathPdf);

    }

    public function view_receipt(ServiceRepository $serviceRepository,ConfigRepository $configRepository,$code)
    {
        // view_receipt_new

       // view_receipt_new_1
       $data['meta_title'] = 'reload pdf phiếu thu';
       $listService = null;
       $receipt = $this->receiptRepo->findReceiptCodePay($code, $this->building_active_id);
       if(!$receipt){
           return response('Không tìm thấy phiếu thu hoặc phiếu thu đã bị xóa.');
       }
       $building = $receipt->building;

       $apartment = $receipt->apartment;

       $name_building = @$building->name;
     
       $get_bill = unserialize($receipt->data);

  
       if ($get_bill && $building->config_menu == 1) {
        foreach ($get_bill as $key => $value) {
            $value = (object)$value;
            $debitDetail = isset($value->new_debit_id) ? $this->debitRepo->findDebitById($value->new_debit_id) :(@$value->apartment_service_price_id ? $this->debitRepo->filterServiceBillIdWithVersionV2($value->bill_id,$value->service_id,$value->apartment_service_price_id,$value->version) : $this->debitRepo->filterServiceBillIdWithVersion($receipt->bdc_building_id, $value->bill_id, $value->service_id, $value->version)) ;
            if($debitDetail){
                $listService[] =$debitDetail;
            }
        }
        $receipt->type_payment_name = Helper::loai_danh_muc[$receipt->type_payment];
        return view("receipt.pdf_v4", compact('receipt', 'building', 'apartment', 'listService', 'serviceRepository'));
        }else{
           if(!$receipt->PaymentDetail){
               return response('Không tìm thấy phiếu thu hoặc phiếu thu đã bị xóa.');
           }
           $nguon_hach_toan =null;
           $PaymentDetail = $receipt->PaymentDetail;
           $sum_total_paid = 0;

           $getTienThua =  LogCoinDetailRepository::getDataByFromId($receipt->bdc_apartment_id,1,$receipt->id);
         
           foreach ($PaymentDetail as $key => $value) {
              $value->type = 1; // chi tiêt tiền thừa
              $listService[] = $value;
              $sum_total_paid += $value->paid;
               if($getTienThua){
                   foreach ($getTienThua as $key_1 => $value_1) {
                       $value_1->type = 2; // chi tiêt tiền thừa
                       if($value->bdc_apartment_service_price_id == $value_1->bdc_apartment_service_price_id){
                           $getTienThua->forget($key_1);
                       }
                   }
               }
           }
           $coin = LogCoinDetailRepository::sum_coin($receipt->id);
           $tien_thua = LogCoinDetailRepository::sum_coin_by_accounting(@$receipt->id);
           $sum_total_paid += $coin;
           $sum_total_paid -= $tien_thua;
           $sum_total_paid = $receipt->type == 'phieu_thu_truoc' || $receipt->type == 'phieu_chi_khac' || $receipt->type == 'phieu_thu_ky_quy' || $receipt->type == 'phieu_hoan_ky_quy' || $receipt->type == 'phieu_bao_co' || $receipt->deleted_at != null ? $receipt->cost : $sum_total_paid;
      
           if($receipt->type == 'phieu_ke_toan'){
               foreach ($PaymentDetail as $key => $value) {
                   $get_accounting_source = LogCoinDetailRepository::get_accounting_source($value->bdc_receipt_id,$value);
                   if($get_accounting_source)
                   {
                       $get_accounting_source->coin = 0 - $get_accounting_source->coin;
                       $nguon_hach_toan[] = $get_accounting_source;
                   }
                  
                }
           }
           if($receipt->type == 'phieu_dieu_chinh'){
               $get_detail = isset($receipt->logs)? json_decode($receipt->logs) : null;
               if($get_detail){
                   foreach ($get_detail as $key => $value) {
                       if(str_contains($value->service_apartment_id, 'tien_thua_') ){ 
                           $service_apartment_id = explode('tien_thua_',$value->service_apartment_id)[1];
   
                           $get_accounting_source = LogCoinDetailRepository::get_accounting_source_service_apartment_id($receipt->id,$service_apartment_id);
                           if($get_accounting_source)
                           {
                               $get_accounting_source->coin = 0 - $get_accounting_source->coin;
                               $nguon_hach_toan[] = $get_accounting_source;
                           }
                       }
                    }
               }
           }
           if($receipt->type == 'phieu_chi'){
               $get_detail = isset($receipt->logs)? json_decode($receipt->logs) : null;
               if($get_detail){
                   foreach ($get_detail as $key => $value) {
                       if(str_contains($value->service_apartment_id, 'tien_thua_') ){ 
                           $service_apartment_id = explode('tien_thua_',$value->service_apartment_id)[1];
   
                           $get_accounting_source = LogCoinDetailRepository::get_accounting_source_service_apartment_id_by_payment_slip($receipt->id,$service_apartment_id);
                           if($get_accounting_source)
                           {
                               $get_accounting_source->coin = 0 - $get_accounting_source->coin;
                               $nguon_hach_toan[] = $get_accounting_source;
                           }
                       }
                    }
                   $sum_total_paid = $receipt->cost;
               }

           }
           $nguon_hach_toan_v1 = LogCoinDetailRepository::get_by_from_id_accounting($receipt->id);

           $receipt->type_payment_name = Helper::loai_danh_muc[$receipt->type_payment];
           $configPdfBill = $configRepository->findByKeyActiveFirst($building->id, $configRepository::RECEIPT_VIEW);
           if($configPdfBill) {
               return view('receipt.'.$configPdfBill->value,compact('receipt', 'building', 'apartment', 'listService', 'serviceRepository','nguon_hach_toan','sum_total_paid','nguon_hach_toan_v1','getTienThua'));
           } else {
               return view('receipt.pdf_v5', compact('receipt', 'building', 'apartment', 'listService', 'serviceRepository','nguon_hach_toan','sum_total_paid','nguon_hach_toan_v1','getTienThua'));
           }
        }
        

    }

    public function editBill(
        $id,
        Request $request,
        ApartmentsRespository $apartmentsRespository,
        ServiceRepository $serviceRepository,
        BillRepository $billRepository,
        ReceiptRepository $receiptRepository,
        DebitDetailRepository $debitDetailRepository
    ) {
        $meta_title = 'Cập nhật phiếu thu';

        $input = $request->all();

        $apartments = $apartmentsRespository->findByBuildingId($this->building_active_id);
        $services = $serviceRepository->filterBuildingId($this->building_active_id);
        $receipt = $receiptRepository->findReceiptById($id);

        $apartmentId = $receipt->bdc_apartment_id;
        $building_id = $this->building_active_id;
        $serviceId = $input != null ? $input["service_id"] : 0;

        $bills = $billRepository->findBuildingApartmentId($building_id, $apartmentId);
        $receipts = $receiptRepository->findBuildingApartmentId($building_id, $apartmentId);
        $debitDetails = $debitDetailRepository->findMaxVersionWithNewSumeryDiffZero($building_id, $apartmentId, $serviceId, null, null);

        return view('receipt.edit_bill', [
            'meta_title' => 'Tạo phiếu thu',
            'apartments' => $apartments,
            'building_id' => $this->building_active_id,
            'services' => $services,
            'receipt' => $receipt,
            'billRepository' => $billRepository,
            'receipts' => $receipts,
            'bills' => $bills,
            'debitDetails' => $debitDetails,
            'id' => $id,
        ]);
    }
}
