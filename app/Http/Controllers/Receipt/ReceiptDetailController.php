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
use App\Repositories\BdcV2LogCoinDetail\LogCoinDetailRepository;
use Exception;
use App\Repositories\Building\BuildingPlaceRepository;

class ReceiptDetailController extends Controller
{
    public $receiptRepo;
    public $apartmentRepo;
    public $billRepo;
    public $debitRepo;
    public $_configRepository;
    private $modelBuildingPlace;

    public function __construct(
        Request $request,
        ReceiptRepository $receiptRepo,
        ApartmentsRespository $apartmentRepo,
        BillRepository $billRepo,
        DebitDetailRepository $debitRepo,
        ConfigRepository $configRepository,
        BuildingPlaceRepository $modelBuildingPlace
    ) {
        $this->receiptRepo = $receiptRepo;
        $this->apartmentRepo = $apartmentRepo;
        $this->billRepo = $billRepo;
        $this->debitRepo = $debitRepo;
        $this->_configRepository = $configRepository;
        $this->modelBuildingPlace = $modelBuildingPlace;
        Carbon::setLocale('vi');
    }

    public function view_receipt(ServiceRepository $serviceRepository,ConfigRepository $configRepository,$code)
    {
       // view_receipt_new_1
       $data['meta_title'] = 'reload pdf phiếu thu';
       $listService = null;
       $receipt = $this->receiptRepo->findReceiptCodePay($code);
       if(!$receipt){
           return response('Không tìm thấy phiếu thu hoặc phiếu thu đã bị xóa.');
       }
       $building = $receipt->building;

       $apartment = $receipt->apartment;

       $name_building = @$building->name;
     
       $get_bill = unserialize($receipt->data);


       if ($get_bill) {
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
}
