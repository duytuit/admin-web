<?php

namespace App\Http\Controllers\Bill\Api;

use App\Http\Controllers\BuildingController;
use App\Repositories\Banks\BanksRespository;
use App\Repositories\Building\BuildingRepository;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Repositories\BdcBills\BillRepository;
use App\Repositories\Service\ServiceRepository;
use App\Repositories\Apartments\ApartmentsRespository;
use App\Repositories\BdcDebitDetail\DebitDetailRepository;
use App\Repositories\BdcReceipts\ReceiptRepository;
use App\Repositories\PaymentInfo\PaymentInfoRepository;
use Barryvdh\DomPDF\Facade as PDF;
use Carbon\Carbon;
use App\Commons\Helper;
use App\Commons\Util\Debug\Log;
use App\Models\BdcBills\Bills;
use App\Models\Building\Building;
use App\Models\Service\Service;
use App\Repositories\BdcApartmentServicePrice\ApartmentServicePriceRepository;
use App\Repositories\BdcV2DebitDetail\DebitDetailRepository as BdcV2DebitDetailDebitDetailRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class BillController extends BuildingController
{
  use ApiResponse;

  const DUPLICATE     = 19999;
    const LOGIN_FAIL    = 10000;

    private $_model;
    private $_service;
    private $_apartment;
    private $_debit_detail;
    private $_payment_info;
    private $_receiptRepository;

    public function __construct(
      Request $request,
      BillRepository $bill,
      ServiceRepository $service,
      ApartmentsRespository $apartment,
      DebitDetailRepository $debit_detail,
      PaymentInfoRepository $payment_info,
      ReceiptRepository $receiptRepository
    )
    {
      $this->_model        = $bill;
      $this->_service      = $service;
      $this->_apartment    = $apartment;
      $this->_debit_detail = $debit_detail;
      $this->_payment_info = $payment_info;
      $this->_receiptRepository = $receiptRepository;
      //$this->middleware('jwt.auth');
      parent::__construct($request);
    }

    public function index(Request $request)
    {
      $validator = Validator::make($request->all(), [
        'bdc_apartment_id' => 'required'
      ]);

      if ($validator->fails()) {
          return $this->validateFail($validator->errors());
      }

      $_apartment = @$this->_apartment->find($request->bdc_apartment_id);

        $building = Building::get_detail_building_by_building_id($_apartment->building_id);

          if ( $building && @$building->config_menu == 1 ) { // kế toán v1
                $_apartment =  $_apartment->billsV2;
                if($_apartment){
                  $bills = $_apartment->toArray();
                }
                foreach($bills as $val) {
                    $findsumMery = $this->_debit_detail->findMaxVersionSumeryByBillId($val['id']);
                    $sumery = 0;
                    foreach($findsumMery as $value) {
                        $sumery += (int)$value->sumery;
                    }
                  $data[] = [
                    'id'        => $val['id'],
                    'bill_code' => $val['bill_code'],
                    'deadline'  => $val['deadline'],
                    'cost'      => $sumery,
                    'status'    => $this->_debit_detail->checkStatusAppBK($val['status'],$val['id'],$val['deadline']),
                    'status_text'    => $this->_debit_detail->checkStatusApp($val['status'],$val['id'],$val['deadline'])['status'],
                  ];
                }
          }
          if ( $building && @$building->config_menu == 2 && @$building->id != 17){          // kế toán v2
                $_apartment =  $_apartment->billsV3;
        
                if($_apartment){
                  $bills = $_apartment->toArray();
                }
                foreach($bills as $val) {
                  $findsumMery =  BdcV2DebitDetailDebitDetailRepository::findByBillId($val['id']);
                  $sumery = 0;
                  foreach($findsumMery as $value) {
                      $sumery += (int)$value->sumery;
                  }
                  $data[] = [
                    'id'        => $val['id'],
                    'bill_code' => $val['bill_code'],
                    'deadline'  => $val['deadline'],
                    'cost'      => $sumery,
                    'status'    => BdcV2DebitDetailDebitDetailRepository::checkStatusAppBKV2($val['id']),
                    'status_text'    => BdcV2DebitDetailDebitDetailRepository::checkStatusAppV2($val['id'])['status'],
                  ];
                }
          }
          if ($building && @$building->config_menu == 2 && @$building->id == 17) {  // kế toán v2

  
            if ($_apartment) {
              $bills = Bills::where('bdc_apartment_id',$request->bdc_apartment_id)->where('status', '>=',-2)->orderBy('updated_at', 'desc')->get();
            }
            foreach ($bills as $val) {
              $findsumMery =  BdcV2DebitDetailDebitDetailRepository::findByBillId($val['id']);
              if ($findsumMery->count() > 0) {
                $sumery = 0;
                foreach ($findsumMery as $value) {
                  $sumery += (int)$value->sumery;
                }
                $data[] = [
                  'id'        => $val['id'],
                  'bill_code' => $val['bill_code'],
                  'deadline'  => $val['deadline'],
                  'cost'      => $sumery,
                  'status'    => BdcV2DebitDetailDebitDetailRepository::checkStatusAppBKV2($val['id']),
                  'status_text'    => BdcV2DebitDetailDebitDetailRepository::checkStatusAppV2($val['id'])['status'],
                ];
              }
              // ======================================================================================================
              $findsumMery_v1 = $this->_debit_detail->findMaxVersionSumeryByBillId($val['id']);
              if (count($findsumMery_v1) > 0) {
                $sumery_1 = 0;
                foreach ($findsumMery_v1 as $value_1) {
                  $sumery_1 += (int)$value_1->sumery;
                }
                $data[] = [
                  'id'        => $val['id'],
                  'bill_code' => $val['bill_code'],
                  'deadline'  => $val['deadline'],
                  'cost'      => $sumery_1,
                  'status'    => $this->_debit_detail->checkStatusAppBK($val['status'], $val['id'], $val['deadline']),
                  'status_text'    => $this->_debit_detail->checkStatusApp($val['status'], $val['id'], $val['deadline'])['status'],
                ];
              }
            }
  
          }
          if(count($bills) == 0){
            $data[] = [
              'id'        => "",
              'bill_code' => "",
              'deadline'  => "",
              'cost'      => "",
              'status'    => ""
            ];
          }
        return $this->responseSuccess($data, 'Success', 200);


    }

    public function show(Request $request, $id)
    {
      $validator = Validator::make($request->all(), [
        'bdc_apartment_id' => 'required',
        'building_id'      => 'required'
      ]);

      if ($validator->fails()) {
          return $this->validateFail($validator->errors());
      }

      $info = Auth::guard('public_user_v2')->user()->infoApp;

      if($info) {
        $bill          = $this->_model->find($id);
        if(!$bill){
          return $this->responseError(['Không có dữ liệu.'], 200,[] );
        }
        $bill_show = null;
        $debit_details = null;
        $building = Building::get_detail_building_by_building_id($request->building_id);
        if ( $building && @$building->config_menu == 1 ) { // kế toán v1

          $debit_details = $this->_debit_detail->findMaxVersionByBillId1($id);
          $bill_show = $this->_debit_detail->checkStatusApp($bill->status,$bill->id,$bill->deadline);

        }
        if ( $building && @$building->config_menu == 2 && @$building->id != 17){ // kế toán v2
          $debit_details = BdcV2DebitDetailDebitDetailRepository::findByBillId($id);
          $bill_show = BdcV2DebitDetailDebitDetailRepository::checkStatusAppV2($bill->id);

        }

        $urlPdf = "admin/bill/detail/".$bill->bill_code;

        if ( $building && @$building->config_menu == 2 && @$building->id == 17){ // kế toán v2 áp dụng cho tòa 17
          $debit_details = BdcV2DebitDetailDebitDetailRepository::findByBillId($id);
          $bill_show = BdcV2DebitDetailDebitDetailRepository::checkStatusAppV2($bill->id);
          if($debit_details->count() == 0){
            $debit_details = $this->_debit_detail->findMaxVersionByBillId1($id);
            $bill_show = $this->_debit_detail->checkStatusApp($bill->status,$bill->id,$bill->deadline);
          }else{
            $urlPdf = "admin/bill/detail/".$bill->bill_code.'?version=2';
          }
        }
      

        $payment_infos = $this->_payment_info->findByBuilding($request->building_id)->toArray();
        $total = 0;

        $details = array();
        foreach( $debit_details as $val ) {
          $service_apart =  ApartmentServicePriceRepository::getInfoServiceApartmentById($val->bdc_apartment_service_price_id);
          $service =  Service::get_detail_bdc_service_by_bdc_service_id($service_apart->bdc_service_id);
          $details[] = [
            'name'   => @$service->name,
            'sumery' => $val->sumery,
            'url_image' => $val->image,
          ];
          $total+=$val->sumery;
        }
       
       
        $data = [
          'cost'          => $bill_show['cost'],
          'bill_code'     => $bill->bill_code,
          'customer_name' => $info->full_name,
          'deadline'      => $bill->deadline,
          'cycle_name'    => $bill->cycle_name,
          'status'        => $bill_show['status'],
          'payment_info'  => $payment_infos,
          'details'       => $details,
          'total'         => $total,
          'created_at'     => $bill->created_at->format('d-m-Y'),
          'file_url'      => $urlPdf
        ];

        return $this->responseSuccess($data, 'Success', 200);
      }

      return $this->responseError(['Không lấy được thông tin User'], self::LOGIN_FAIL);
    }

    public function checkStatus($status)
    {
      if( $status < $this->_model::PAYING ) {
        return "Chưa thanh toán";
      }

      if( $status == $this->_model::PAYING ) {
        return "Chờ thanh toán";
      }

      if( $status == $this->_model::PAID ) {
        return "Đã thanh toán thanh toán";
      }

      if( $status == $this->_model::OUT_OF_DATE ) {
        return "Đã quá hạn";
      }
    }

    public function payments(Request $request,BuildingRepository $building,BanksRespository $banks, $id)
    {
        $validator = Validator::make($request->all(), [
            'bdc_apartment_id' => 'required',
            'building_id'      => 'required',
        ]);

        $note = Helper::convert_vi_to_en($request->note);

        if ($validator->fails()) {
            return $this->validateFail($validator->errors());
        }

            $bill          = $this->_model->find($id);
            $bill_show = $this->_debit_detail->checkStatusApp($bill->status,$bill->id,$bill->deadline);
            $builing_info = $building->getActiveBuilding($request->building_id);
            $list_banks = $banks->searchBy($request->building_id,$request,[],100);
            $data_banks=[];
            foreach ($list_banks as $b){
                $data_banks[]=[
                    'id'=>$b->id,
                    'title'=>$b->title,
                    'alias'=>$b->alias,
                    'url'=>$b->url,
                    'logo'=>url('/').'/'.$b->logo,
                    'app_name'=>$b->app_name,
                    'status'=>$b->status,
                ];
            }
            //$url_payment = $this->createPayment($bill->bill_code, $request->paid_money, $note, 2 , $request->building_id, $request->bank);
            $url_payment = null;
            $data = [
                'cash'      => [
                    'place' => $builing_info->name,
                    'address' => $builing_info->address,
                    'phone' => $builing_info->phone,
                    'email' => $builing_info->email,
                    'time'=>'từ '.date('d-m-Y',strtotime($bill->created_at)) . ' đến '. date('d-m-Y',strtotime($bill->deadline))
                ],
                'vnpay'     => ['url'=> $url_payment ,'status'=> $builing_info->vnp_secret ? true : false],
                'banks'     => $data_banks,
                'total'     => $bill_show['cost']
            ];
            return $this->responseSuccess($data, 'Lấy thông tin thanh toán thành công', 200);
    }
    public function listAccountBanksTest(Request $request,BuildingRepository $building)
    {
      $builing_info = $building->getActiveBuilding($request->building_id);
      $data['status'] = $builing_info->vnp_secret ? true : false;
      return $this->responseSuccess($data, 'Lấy thông tin thanh toán thành công', 200);
    }
    public function listAccountBanks(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'bdc_apartment_id' => 'required',
            'building_id'      => 'required'
        ]);

        if ($validator->fails()) {
            return $this->validateFail($validator->errors());
        }

        // $info = Auth::guard('public_user')->user()->info;

        // if($info) {
        //     $payment_infos = $this->_payment_info->findByBuilding($request->building_id)->toArray();
        //     return $this->responseSuccess($payment_infos, 'Lấy thông tin tài khoản thành công', 200);
        // }
        return $this->responseError(['Không lấy được thông tin User'], self::LOGIN_FAIL);
    }
    public function pay(Request $request)
    {
        // $info = Auth::guard('public_user')->user()->info;

        // if($info) {
        //     return $this->responseSuccess([], 'Đã gửi yêu cầu xác nhận thanh toán', 200);
        // }
        return $this->responseError(['Không lấy được thông tin User'], self::LOGIN_FAIL);
    }

    public function receipts(Request $request, $id){
        $input = $request->all();
        $validator = Validator::make($input, [
            'bdc_apartment_id' => 'required',
            'building_id'      => 'required',
            'type_payment'      => 'required'
        ]);

        if ($validator->fails()) {
            return $this->validateFail($validator->errors());
        }

        $bill = $this->_model->findBillById($id);
        if(!$bill){
            return $this->responseError('Mã hóa đơn không chính xác.', 400);
        }
        $base_url=((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == "on") ? "https" : "http"). "://". @$_SERVER['HTTP_HOST'];
        $receipt = $this->_receiptRepository->filterByBillId($bill->bill_code, $input['building_id'], $input['bdc_apartment_id'], $input['type_payment']);
        $data = $receipt->toArray();
        foreach ($data as $key => $value) {
          $data[$key]['url'] = $base_url."/admin/receipt/getReceipt/".$value['receipt_code'];
        }
        return $this->responseSuccess($data, 'Lấy thông tin thành công', 200);
    }

    public function getOrCreatePDF( $bill )
    {
      if ($bill->url) {
        return url($bill->url);
      }
      $pdfName = $bill->bill_code;
      $pathPdf = $_SERVER['DOCUMENT_ROOT'] . "/bang-ke/$pdfName.pdf";
      $urlPdf = "bang-ke/$pdfName.pdf";
      $_bill = $this->_model->find($bill->id);
      $_bill->url = $urlPdf;
      $_bill->save();
      $building = $bill->building;
      $apartment = $bill->apartment;
      $debit_detail = $this->_debit_detail->getDetailBillId($bill->id);
      $totalPaymentDebit = $this->_debit_detail->findMaxVersionWithBuildingApartment($this->building_active_id, $bill->bdc_apartment_id, $bill->id);
      $pdf = PDF::loadView('bill.pdf', compact('debit_detail', 'building', 'apartment', 'bill', 'totalPaymentDebit'));
      $pdf->save($pathPdf);

      return  url("/bang-ke/$pdfName.pdf");
    }
    public function listbank(Request $request)
    {
        $banks = Helper::banks();
        return $this->responseSuccess(['bank' => $banks], 'Danh sách ngân hàng thanh toán', 200);
    }
}