<?php

namespace App\Http\Controllers\Vnpay;

use App\Http\Controllers\BuildingController;
use App\Models\Vnpay\VnpayLog;
use App\Repositories\BdcReceipts\ReceiptRepository;
use App\Repositories\Vnpay\VnpayRespository;
use App\Repositories\Vnpay\VnpayReturnRespository;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use App\Repositories\BdcBills\BillRepository;
use App\Repositories\BdcDebitDetail\DebitDetailRepository;
use App\Repositories\BdcReceiptLogs\ReceiptLogsRepository;
use App\Repositories\Config\ConfigRepository;
use App\Repositories\Service\ServiceRepository;
use DB;
use Barryvdh\DomPDF\Facade as PDF;
use App\Models\Vnpay\VnpayReturnLog;

class VnpayController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function __construct(Request $request)
    {
        // $this->middleware('auth', ['except'=>[]]);
        ////$this->middleware('route_permision');
        //parent::__construct($request);
    }
    public function index(Request $request, VnpayReturnRespository $vnpayReturn, ReceiptRepository $receiptRepository)
    {
        $data['vnp_SecureHash'] = $request['vnp_SecureHash'];
        $info = array();
        foreach ($request->all() as $key => $value) {
            if (substr($key, 0, 4) == "vnp_") {
                $info[$key] = $value;
            }
        }
        unset($info['vnp_SecureHashType']);
        unset($info['vnp_SecureHash']);
        ksort($info);

        $i = 0;
        $hashData = "";
        foreach ($info as $key => $value) {
            if ($i == 1) {
                $hashData = $hashData . '&' . $key . "=" . $value;
            } else {
                $hashData = $hashData . $key . "=" . $value;
                $i = 1;
            }
        }
        $data['info'] = $info;

        $VNPayInfo = $receiptRepository->findReceiptCodePay($request->vnp_TxnRef);

        $data['meta_title'] = $data['heading'] = 'Kết quả thanh toán VNPAY';

        $vnpayReturn->create([
            'messages' => 'Kết quả thanh toán VNPAY',
            'receipt_code' => $request['vnp_TxnRef'] . '|vnp_Returnurl: ' . $request['vnp_ReturnUrl'],
            'building_id' => @$VNPayInfo->building->id,
            'content' => $request->fullUrl(),
            'created_date' => Carbon::now()->toDateTimeString(),
        ]);
        return view('vnpay.index', $data);
    }
    public function indexCustomer(Request $request, 
        VnpayReturnRespository $vnpayReturn,
        ReceiptRepository $receiptRepository,
        BillRepository $bill, 
        DebitDetailRepository $debit_detail_new,
        ConfigRepository $config,
        ReceiptLogsRepository $receiptLogsRepository,
        ServiceRepository $serviceRepository)
    {

        $info = array();
        foreach ($request->all() as $key => $value) {
            if (substr($key, 0, 4) == "vnp_") {
                $info[$key] = $value;
            }
        }
        unset($info['vnp_SecureHashType']);
        unset($info['vnp_SecureHash']);
        ksort($info);

        $i = 0;
        $hashData = "";
        foreach ($info as $key => $value) {
            if ($i == 1) {
                $hashData = $hashData . '&' . $key . "=" . $value;
            } else {
                $hashData = $hashData . $key . "=" . $value;
                $i = 1;
            }
        }
        $data['info'] = $info;

        $bill_result          = $bill->findBillCode_v1($request->vnp_TxnRef);

        $directory = 'phieuthu/'.@$bill_result->building->name;
            if (!is_dir($directory)) {
                mkdir($directory);
        }

        $data['meta_title'] = $data['heading'] = 'Kết quả thanh toán VNPAY';

        $createdDate =  Carbon::now();

        if ($request['vnp_ResponseCode'] == '00') {
            \DB::beginTransaction();
            try {
                $listService = array();
                $strData = array();
                $strBillIds = array();
                $debit_details = $debit_detail_new->findMaxVersionByBillId1($bill_result->id);
                $strBillIds = serialize($bill_result->bill_code);
                if(!$debit_details){
                     throw new \Exception("Không tìm thấy chi tiết bảng kê", 0);
                }
                 // Tạo Debit Detail
                    foreach($debit_details as $value) {
                        if ($value->is_free == 1) {
                            continue;
                        }
                        $value->id = null;
                        $value->paid = $request->vnp_Amount / 100;
                        $value->new_sumery = $value->new_sumery - $request->vnp_Amount / 100;
                        $value->created_at = $createdDate;
                        $value->updated_at = $createdDate;
                        $value->version = $value->version + 1;
                        $value->create_date = Carbon::now();

                        $newDebitDetail = (array)$value;

                        array_push($listService, $newDebitDetail);

                        $debit_detail_new->create($newDebitDetail);

                       
                        $strData = serialize($value);

                        $receiptLogsRepository->create([
                        'bdc_building_id' =>  $bill_result->bdc_building_id,
                        'bill_id' => $bill_result->id,
                        'bill_code' => $request->vnp_TxnRef,
                        'bdc_service_id' => $value->bdc_service_id,
                        'key' => "create_debit_detail",
                        'input' => json_encode($strData),
                        'message' => "Tạo công nợ thành công",
                        'status' => 200
                         ]);
                    }
               

                $code_receipt = $receiptRepository->autoIncrementCreditTransferReceiptCode($config, $bill_result->bdc_building_id);
                
                $nameTemp = $code_receipt . "-" . Carbon::now()->timestamp;

                $receipt = $receiptRepository->create([
                    'bdc_apartment_id' => $bill_result->bdc_apartment_id,
                    'bdc_building_id' => $bill_result->bdc_building_id,
                    'receipt_code' => $code_receipt,
                    'cost' => $request->vnp_Amount / 100,
                    'cost_paid' => $request->vnp_Amount / 100,
                    'customer_name' => $bill_result->customer_name,
                    'customer_address' => $bill_result->customer_address,
                    'provider_address' => 'Banking',
                    'bdc_receipt_total' => 'test',
                    'logs' => 'payment_app',
                    'description' => $request->vnp_OrderInfo,
                    'type_payment' => 'VNPay',
                    'bdc_bill_id' => $strBillIds,
                    'url' => $directory."/$nameTemp.pdf",
                    'user_id' => $bill_result->user_id,
                    'type' => 'phieu_bao_co',
                    'status' => 1,
                    'url_payment' => $request->fullUrl(),
                    'data' => $strData,
                    'create_date' => $createdDate
                ]);
                $receiptLogsRepository->create([
                    'bdc_building_id' =>  $bill_result->bdc_building_id,
                    'bill_id' => $bill_result->id,
                    'bill_code' => $request->vnp_TxnRef,
                    'key' => "create_debit_detail",
                    'input' => json_encode($strData),
                    'data' => json_encode($receipt),
                    'message' => "Tạo phiếu thu thành công",
                    'status' => 200
                ]);
                $check_bill_his->status = 1;
                $check_bill_his->save();

                $building = $receipt->building;
                $apartment = $receipt->apartment;
                $pathPdf = $_SERVER['DOCUMENT_ROOT'] .'/'. $directory."/$nameTemp.pdf";
                $urlPdf = asset($directory."/$nameTemp.pdf");
                $pdf = PDF::loadView('receipt.pdf', compact('receipt', 'building', 'apartment', 'listService', 'serviceRepository'));
                $pdf->save($pathPdf);
            } catch (\Exception $e) {
                \DB::rollBack();
                throw new \Exception("ERROR: " . $e->getMessage(), 1);
            }
            \DB::commit();
        }

        $vnpayReturn->create([
            'messages' => $request['vnp_ResponseCode'] == '00' ?'Kết quả thanh toán VNPAY: Thành công !' : 'Kết quả thanh toán VNPAY: Thất bại',
            'receipt_code' =>  $request['vnp_TxnRef'],
            'building_id' => $bill_result->bdc_building_id,
            'content' => $request->fullUrl(),
            'created_date' => Carbon::now()->toDateTimeString(),
        ]);
        return view('vnpay.indexcustomer', $data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function returnIpnPayment(Request $request, ReceiptRepository $receipt, VnpayRespository $vnpay)
    {
        $inputData = array();
        $returnData = array();
        $data = $request->all();
        foreach ($data as $key => $value) {
            if (substr($key, 0, 4) == "vnp_") {
                $inputData[$key] = $value;
            }
        }

        $vnp_SecureHash = $inputData['vnp_SecureHash'];
        unset($inputData['vnp_SecureHashType']);
        unset($inputData['vnp_SecureHash']);
        ksort($inputData);
        $i = 0;
        $hashData = "";
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashData = $hashData . '&' . $key . "=" . $value;
            } else {
                $hashData = $hashData . $key . "=" . $value;
                $i = 1;
            }
        }
        $vnpTranId = $inputData['vnp_TransactionNo']; //Mã giao dịch tại VNPAY
        $vnp_BankCode = $inputData['vnp_BankCode']; //Ngân hàng thanh toán
        //$secureHash = md5($vnp_HashSecret . $hashData);
        $VNPayInfo = $this->getVNPayInfo($this->building_active_id);

        $secureHash = hash('sha256', $VNPayInfo['vnp_secret'] . $hashData);
        $Status = 0;
        $orderId = $inputData['vnp_TxnRef'];
        $order = $receipt->findReceiptCodePay($orderId);
        if ($order) {
        } else {
            $vnpay->create(['urlipn' => $request->fullUrl()]);
        }

        try {
            //Check Orderid
            //Kiểm tra checksum của dữ liệu
            if ($secureHash == $vnp_SecureHash) {
                //Lấy thông tin đơn hàng lưu trong Database và kiểm tra trạng thái của đơn hàng, mã đơn hàng là: $orderId
                //Việc kiểm tra trạng thái của đơn hàng giúp hệ thống không xử lý trùng lặp, xử lý nhiều lần một giao dịch
                //Giả sử: $order = mysqli_fetch_assoc($result);

                if ($order != NULL) {
                    if ($order->cost == $inputData['vnp_Amount'] / 100) {
                        if ($order->status == 0) {
                            if ($inputData['vnp_ResponseCode'] == '00') {
                                $Status = 1;
                                $receipt->updateAttribute($order->id, ['vnp_status' => $Status, 'status' => 1]);
                            } else {
                                $Status = 2;
                                $receipt->updateAttribute($order->id, ['vnp_status' => $Status, 'status' => 0]);
                            }

                            //Cài đặt Code cập nhật kết quả thanh toán, tình trạng đơn hàng vào DB
                            //
                            //
                            //
                            //Trả kết quả về cho VNPAY: Website TMĐT ghi nhận yêu cầu thành công
                            $returnData['RspCode'] = '00';
                            $returnData['Message'] = 'Confirm Success';
                        } else {
                            $returnData['RspCode'] = '02';
                            $returnData['Message'] = 'Order already confirmed';
                        }
                    } else {
                        $returnData['RspCode'] = '04';
                        $returnData['Message'] = 'Invalid amount';
                    }
                } else {
                    $returnData['RspCode'] = '01';
                    $returnData['Message'] = 'Order not found';
                }
            } else {
                $returnData['RspCode'] = '97';
                $returnData['Message'] = 'Chu ky khong hop le';
            }
        } catch (Exception $e) {
            $returnData['RspCode'] = '99';
            $returnData['Message'] = 'Unknow error';
        }
        if ($order) {
            $vnpay->create(['urlipn' => $request->fullUrl(), 'payment_server_status' => $order->status, 'receipt_code' => $order->receipt_code, 'RspCode' => $returnData['RspCode']]);
            if ($returnData['RspCode'] != '02') {
                $receipt->updateAttribute($order->id, [
                    'vnp_responsecode' => $inputData['vnp_ResponseCode'] == '00' ? $returnData['RspCode'] : $inputData['vnp_ResponseCode'] ?? null,
                    'vnp_bank_code' => $inputData['vnp_BankCode'] ?? null,
                    'vnp_banktranno' => $inputData['vnp_BankTranNo'] ?? null,
                    'vnp_cardtype' => $inputData['vnp_CardType'] ?? null,
                    'vnp_paydate' => $inputData['vnp_PayDate'] ?? null,
                    'vnp_transactionno' => $inputData['vnp_TransactionNo'] ?? null,
                    'vnp_currcode' => $inputData['vnp_CurrCode'] ?? null
                ]);
            }
        } else {
            $vnpay->create(['urlipn' => $request->fullUrl(), 'RspCode' => $returnData['RspCode']]);
        }


        echo json_encode($returnData);
    }
}
