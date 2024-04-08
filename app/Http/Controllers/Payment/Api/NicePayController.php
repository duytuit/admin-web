<?php

namespace App\Http\Controllers\Payment\Api;

use App\Exceptions\QueueRedis;
use App\Helpers\dBug;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Apartments\V2\UserApartments;
use App\Models\BdcBills\Bills;
use App\Models\BdcV2DebitDetail\DebitDetail;
use App\Models\Building\Building;
use App\Models\Campain;
use App\Models\Payment\ApartmentBank;
use App\Models\Payment\Payment;
use App\Models\Payment\PaymentOrder;
use App\Models\Payment\PaymentSuccess;
use App\Repositories\Apartments\ApartmentsRespository;
use App\Repositories\BdcCoin\BdcCoinRepository;
use App\Repositories\BdcReceipts\V2\ReceiptRepository;
use App\Repositories\BdcV2DebitDetail\DebitDetailRepository;
use App\Repositories\BdcV2PaymentDetail\PaymentDetailRepository;
use App\Repositories\Config\ConfigRepository;
use App\Repositories\Customers\CustomersRespository;
use App\Repositories\PublicUsers\PublicUsersProfileRespository;
use App\Services\FCM\V2\SendNotifyFCMService;
use App\Util\Debug\Log;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;

class NicePayController extends Controller
{

    public function saveTransactionVirtualAccount(Request $request, ReceiptRepository $receiptRepository, ConfigRepository $config)
    {
        // Get base64 encoded public key.
        // NOTE: this is just for testing the code, final production code stores the public key in a db.
        $pubkey = 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAsR2KK1Rfhx6bofPSUTa+
        qMAAMx2zVv8v+/YXWeTLWsFwUyDrWVbzXGnwgi5xIn1kUiXqA581Sl/NvhqE3sHD
        3hm3iCz7lwZo/fkwRcZlIuSM+xdz1HQYdTTIsiJJuA4jDfZYEAZJh5JpYqzn3K2B
        Yj4rOCc+X8Hr+n+i0P4NCUaShI6J+LJ38+6fQ4sF/wlK9E/MfbGZRdKvDCSiqz5T
        tRYzFtzijTMxFD81g2TM0ts1pArpwOaJu88tjYERq/yhMcFPZny78mra7O87GVO5
        378TV9HzXrcKoRogXG1SwcVhhcAxzEMcWzTX6C8nJWbJzZOuDh3GoccMyj2puCio
        zwIDAQAB';
        Log::info('check_import_receipt_9pay_','trace_start_'.json_encode($_POST));
        // Convert pubkey in to PEM format (don't forget the line breaks).
        $pubkey_pem = "-----BEGIN PUBLIC KEY-----\n$pubkey\n-----END PUBLIC KEY-----";
        $signature = $request->signature;
        $request_id = $request->get('request_id','');

        dBug::trackingPhpErrorV2($request_id);

        $msg = $request_id.'|'.$request->partner_id.'|'.$request->trans_id.'|'.$request->request_amount.'|'.$request->fee.'|'.$request->amount.'|'.$request->transfer_code.'|'.$request->status.'|'.$request->created_at;
        $key = openssl_pkey_get_public($pubkey_pem);
        if ($key == 0) {
            $result = "Bad key zero.";
            return response()->json(['success' =>false,'msg'=>$result],422);
        } elseif ($key == false) {
            $result = "Bad key false.";
            return response()->json(['success' =>false,'msg'=>$result],422);
        } else {
            // Verify signature (use the same algorithm used to sign the msg).
            $result = openssl_verify($msg, base64_decode($signature), $key, OPENSSL_ALGO_SHA256);
        }

        if ($result == 1) {
            $base_url = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == "on") ? "https" : "http") . "://" . @$_SERVER['HTTP_HOST'];
            
            $payment = Payment::where('code_pay', $request->remark)->first();
            if($payment){
                $payment_success_check = PaymentSuccess::where(['trans_id' => $payment->trans_id])->first();
                Log::info('check_import_receipt_9pay_','trace_start_1'.json_encode($payment_success_check));
                if($payment_success_check){
                    $result = 'This transaction already exists';
                    dBug::trackingPhpErrorV2('Đã tồn tại :'.$payment->trans_id);
                    return response()->json(['success' => true, 'msg' => $result], 200);
                }
            }
            $payment_success = PaymentSuccess::where(['code_pay' => $request->remark])->first();
            if ($payment_success) {
                $result = 'This transaction already exists';
                return response()->json(['success' => true, 'msg' => $result], 200);
            }
            $payment_success = PaymentSuccess::create([
                'trans_id'           => $request->trans_id,
                'code_pay'           => $request->remark,
                'type'               => 1,
                'status'             => 0,
                'trans_id_partner'   => $request->trans_id,
                'data'               => json_encode($request->all()),
                'number_bank'        => $request->bank_account_no,
                'bank'               => $request->bank_name,
                'paid'               => $request->amount
            ]);
            $so_tien_total = $request->amount;
          
            if (!$payment) {
                $apartmentBank = ApartmentBank::where('number_bank',$request->bank_account_no)->first();
                dBug::trackingPhpErrorV2($request->bank_account_no);
                if (!$apartmentBank) {
                    $result = 'Not found apartment bank';
                    return response()->json(['success' => true, 'msg' => $result], 422);
                }
                try {
                    DB::beginTransaction();
                    $customer = CustomersRespository::findApartmentIdV2($apartmentBank->apartment_id, 0);
                    $pubUserProfile = $customer ? PublicUsersProfileRespository::getInfoUserById($customer->user_info_id) : null;
                    $apartment = ApartmentsRespository::getInfoApartmentsById($apartmentBank->apartment_id);
                    $building = Building::get_detail_building_by_building_id($apartment->building_id);
                    $code_receipt = $receiptRepository->autoIncrementCreditTransferReceiptCode($config, $apartment->bdc_building_id);
                    $receipt = $receiptRepository->create([
                        'bdc_apartment_id' => $apartmentBank->bdc_apartment_id,
                        'bdc_building_id' => $apartment->bdc_building_id,
                        'receipt_code' => $code_receipt,
                        'cost' => $so_tien_total,
                        'cost_paid' => $so_tien_total,
                        'customer_name' => $pubUserProfile->full_name,
                        'customer_address' => $apartment->name,
                        'provider_address' => 'Banking',
                        'bdc_receipt_total' => 'test',
                        'logs' => 'phieu_thu',
                        'description' => $request->bank_account_no,
                        'ma_khach_hang' => $apartment->code_customer,
                        'ten_khach_hang' =>  $apartment->name_customer,
                        'type_payment' => 'chuyen_khoan',
                        'url' => $base_url . "/admin/receipt/getReceipt/" . $code_receipt,
                        'user_id' =>  0,
                        'type' => 'phieu_bao_co',
                        'status' => 1,
                        'create_date' => Carbon::now(),
                        'config_type_payment' => 2,
                        'trans_id' => $request->trans_id
                    ]);
                    if ($so_tien_total > 0) {
                        BdcCoinRepository::addCoin($apartment->bdc_building_id, $apartmentBank->bdc_apartment_id, 0, Carbon::now()->format('Ym'), @$customer->user_info_id ?? 0, $so_tien_total, 0, 1, $receipt->id);
                        $receipt->account_balance = $so_tien_total;
                        $receipt->save();
                    }
                    DB::commit();
                    $list_userinfo_id = UserApartments::getListUserPushNotify($apartment->id);
                    Log::info('check_import_receipt_9pay_','apartment :'.json_encode($apartment));
                    Log::info('check_import_receipt_9pay_','user :'.json_encode($list_userinfo_id));
                    if(count($list_userinfo_id)>0){
                        $total = ['email'=>0, 'app'=> count($list_userinfo_id) , 'sms'=> 0];
                        $campain = Campain::updateOrCreateCampain("Xác nhận thanh toán", config('typeCampain.NOTIFY_TRANSACTION_PAYMENT'), $apartment->id, $total, $apartment->building_id, 0, 0);
                        foreach ($list_userinfo_id as $key => $value) {
                            $this->sendNotify($apartment, $building, $value, $request->amount, $campain->id, $receipt);
                        }
                       
                    }

                    $result = "Successful transaction processing";
                    return response()->json(['success' => true, 'msg' => $result], 200);
                } catch (Exception $e) {
                    Log::info('check_import_receipt_9pay_','trace_1_'.json_encode($e->getMessage()));
                    Log::info('check_import_receipt_9pay_','trace_2_:'.json_encode($e->getLine()));
                    DB::rollBack();
                }
            }
            $paymentOrder = PaymentOrder::where('order_id', $payment->order_id)->get();


            if (!$paymentOrder) {
                $apartmentBank = ApartmentBank::where('number_bank',$request->bank_account_no)->first();
                if (!$apartmentBank) {
                    $result = 'Not found apartment bank';
                    return response()->json(['success' => true, 'msg' => $result], 422);
                }
                try {
                    DB::beginTransaction();
                    $customer = CustomersRespository::findApartmentIdV2($apartmentBank->apartment_id, 0);
                    $pubUserProfile = $customer ? PublicUsersProfileRespository::getInfoUserById($customer->user_info_id) : null;
                    $apartment = ApartmentsRespository::getInfoApartmentsById($apartmentBank->apartment_id);
                    $building = Building::get_detail_building_by_building_id($apartment->building_id);
                    $code_receipt = $receiptRepository->autoIncrementCreditTransferReceiptCode($config, $apartment->bdc_building_id);
                    $receipt = $receiptRepository->create([
                        'bdc_apartment_id' => $apartmentBank->bdc_apartment_id,
                        'bdc_building_id' => $apartment->bdc_building_id,
                        'receipt_code' => $code_receipt,
                        'cost' => $so_tien_total,
                        'cost_paid' => $so_tien_total,
                        'customer_name' => $pubUserProfile->full_name,
                        'customer_address' => $apartment->name,
                        'provider_address' => 'Banking',
                        'bdc_receipt_total' => 'test',
                        'logs' => 'phieu_thu',
                        'description' => $request->bank_account_no,
                        'ma_khach_hang' => $apartment->code_customer,
                        'ten_khach_hang' =>  $apartment->name_customer,
                        'type_payment' => 'chuyen_khoan',
                        'url' => $base_url . "/admin/receipt/getReceipt/" . $code_receipt,
                        'user_id' =>  0,
                        'type' => 'phieu_bao_co',
                        'status' => 1,
                        'create_date' => Carbon::now(),
                        'config_type_payment' => 2,
                        'trans_id' => $request->trans_id
                    ]);
                    if ($so_tien_total > 0) {
                        BdcCoinRepository::addCoin($apartment->bdc_building_id, $apartmentBank->bdc_apartment_id, 0, Carbon::now()->format('Ym'), @$customer->user_info_id ?? 0, $so_tien_total, 0, 1, $receipt->id);
                        $receipt->account_balance = $so_tien_total;
                        $receipt->save();
                    }
                    DB::commit();
                    $list_userinfo_id = UserApartments::getListUserPushNotify($apartment->id);
                    Log::info('check_import_receipt_9pay_','apartment :'.json_encode($apartment));
                    Log::info('check_import_receipt_9pay_','user :'.json_encode($list_userinfo_id));
                    if(count($list_userinfo_id)>0){
                        $total = ['email'=>0, 'app'=> count($list_userinfo_id) , 'sms'=> 0];
                        $campain = Campain::updateOrCreateCampain("Xác nhận thanh toán", config('typeCampain.NOTIFY_TRANSACTION_PAYMENT'), $apartment->id, $total, $apartment->building_id, 0, 0);
                        foreach ($list_userinfo_id as $key => $value) {
                            $this->sendNotify($apartment,$building,$value,$request->amount,$campain->id,$receipt);
                        }
                       
                    }

                    $result = "Successful transaction processing";
                    return response()->json(['success' => true, 'msg' => $result], 200);
                } catch (Exception $e) {
                    Log::info('check_import_receipt_9pay_','trace_3_'.json_encode($e->getMessage()));
                    Log::info('check_import_receipt_9pay_','trace_4_:'.json_encode($e->getLine()));
                    DB::rollBack();
                }
            }
            try {
                DB::beginTransaction();
                // Tạo phiếu thu
                $log = null;
                $billIds = array();
                $receipt = null;
                $customer = null;
                $apartment = null;
                $building = null;
                $debit_details = null;
                foreach ($paymentOrder as $key => $value) {
                    $debitDetail = DebitDetail::find($value->debit_id);

                    if ($key == 0) {
                        $code_receipt = $receiptRepository->autoIncrementCreditTransferReceiptCode($config, $debitDetail->bdc_building_id);
                        $customer = CustomersRespository::findApartmentIdV2($debitDetail->bdc_apartment_id, 0);
                        $pubUserProfile = $customer ? PublicUsersProfileRespository::getInfoUserById($customer->user_info_id) : null;
                        $apartment = ApartmentsRespository::getInfoApartmentsById($debitDetail->bdc_apartment_id);
                        $building = Building::get_detail_building_by_building_id($debitDetail->bdc_building_id);
                        $receipt = $receiptRepository->create([
                            'bdc_apartment_id' => $debitDetail->bdc_apartment_id,
                            'bdc_building_id' => $debitDetail->bdc_building_id,
                            'receipt_code' => $code_receipt,
                            'cost' => $so_tien_total,
                            'cost_paid' => $so_tien_total,
                            'customer_name' => $pubUserProfile->full_name,
                            'customer_address' => $apartment->name,
                            'provider_address' => 'Banking',
                            'bdc_receipt_total' => 'test',
                            'description' => $request->bank_account_no,
                            'ma_khach_hang' => $apartment->code_customer,
                            'ten_khach_hang' =>  $apartment->name_customer,
                            'type_payment' => 'chuyen_khoan',
                            'url' => $base_url . "/admin/receipt/getReceipt/" . $code_receipt,
                            'user_id' =>  0,
                            'type' => 'phieu_bao_co',
                            'status' => 1,
                            'create_date' => Carbon::now(),
                            'config_type_payment' => 2,
                            'trans_id' => $request->trans_id
                        ]);
                    }


                    $bill_code = Bills::get_detail_bill_by_apartment_id($debitDetail->bdc_bill_id)->bill_code;
                    array_push($billIds, $bill_code);
                    $paid_service = 0;
                    $sumery = $debitDetail->sumery - $debitDetail->paid;
                    if ($sumery == 0) {
                        continue;
                    }
                    if ($so_tien_total > $sumery) {
                        $paid_service = $sumery;
                        $so_tien_total -= $paid_service;
                    } else {
                        $paid_service = $so_tien_total;
                        $so_tien_total -= $paid_service;
                    }

                    // bắt đầu hạch toán
                    $paymentdetail = PaymentDetailRepository::createPayment(
                        $debitDetail->bdc_building_id,
                        $debitDetail->bdc_apartment_id,
                        $debitDetail->bdc_apartment_service_price_id,
                        Carbon::now()->format('Ym'),
                        $debitDetail->id,
                        $paid_service,
                        Carbon::now(),
                        $receipt->id,
                        0
                    );

                    $debitDetail->paid = $debitDetail->paid + $paid_service;
                    $debitDetail->save();
                    $debit_details[]=$debitDetail;
                    QueueRedis::setItemForQueue('add_queue_stat_payment_',[
                        "apartmentId" => $debitDetail->bdc_apartment_id,
                        "service_price_id" => $debitDetail->bdc_apartment_service_price_id,
                        "cycle_name" => Carbon::now()->format('Ym'),
                    ]);

                    $log[] = [
                        'debit_id' => $debitDetail->id,
                        'paid' => $paid_service,
                    ];
                }
                // xử lý thu thừa tiền
                if ($so_tien_total > 0) {
                    BdcCoinRepository::addCoin($debitDetail->bdc_building_id, $debitDetail->bdc_apartment_id, 0, Carbon::now()->format('Ym'), @$customer->user_info_id ?? 0, $so_tien_total, 0, 1, $receipt->id);
                    $receipt->account_balance = $so_tien_total;
                }
                $receipt->logs = $log ? json_encode($log) : null;
                $strBillIds = serialize($billIds);
                $receipt->bdc_bill_id = $strBillIds;
                $receipt->metadata = json_encode($debit_details);
                $receipt->save();
                $payment_success = PaymentSuccess::where(['code_pay' => $request->remark, 'status' => 0])->update(['status' => 1]);
                DB::commit();
                $apartId[] = $apartment->id; 
                $list_userinfo_id = UserApartments::getListUserPushNotify($apartId);
                Log::info('check_import_receipt_9pay_','apartment :'.json_encode($apartment));
                Log::info('check_import_receipt_9pay_','user :'.json_encode($list_userinfo_id));
                if (count($list_userinfo_id) > 0) {
                    $total = ['email' => 0, 'app' => count($list_userinfo_id), 'sms' => 0];
                    $campain = Campain::updateOrCreateCampain("Xác nhận thanh toán", config('typeCampain.NOTIFY_TRANSACTION_PAYMENT'), $apartment->id, $total, $apartment->building_id, 0, 0);
                    foreach ($list_userinfo_id as $key => $value) {
                        $this->sendNotify($apartment, $building, $value, $request->amount, $campain->id,$receipt);
                    }
                }
                $result = "Successful transaction processing";
                return response()->json(['success' => true, 'msg' => $result], 200);
            } catch (\Exception $e) {
                DB::rollBack();
                Log::info('check_import_receipt_9pay_','trace_5_'.json_encode($e->getMessage()));
                Log::info('check_import_receipt_9pay_','trace_6_:'.json_encode($e->getLine()));
                return response()->json(['success' => false, 'msg' => $e->getMessage()], 422);
            }
        } 
       
        return response()->json(['success' =>false,'msg'=>$result],422);
    }
    public function saveTransaction(Request $request, ReceiptRepository $receiptRepository, ConfigRepository $config,SendNotifyFCMService $sendNotifyFCMService)
    {
        Log::info('check_import_receipt_9pay_transaction','trace_start_'.json_encode($_POST));
        // $secretKeyChecksum = env('SECRET_NICE_PAY',"ljDeHNFF0McTR8SC3nSkAgX1CpXvE33z");    
        $secretKeyChecksum = env('secret_nice_pay','uB2JXiCdKg9LEHxFAhisNmEJNXRQa5B9');
        //$secretKeyChecksum = 'uB2JXiCdKg9LEHxFAhisNmEJNXRQa5B9';

        $result = $request->result;
        $checksum = $request->checksum;
        
        $hashChecksum = strtoupper(hash('sha256', $result . $secretKeyChecksum));
    
        if ($hashChecksum === $checksum)
        {
            $arrayParams = json_decode($this->urlsafeB64Decode($result));
            if($arrayParams){
                $base_url = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == "on") ? "https" : "http") . "://" . @$_SERVER['HTTP_HOST'];
            
                $payment = Payment::where('trans_id', $arrayParams->invoice_no)->first();
                if($payment){
                    $payment_success_check = PaymentSuccess::where(['trans_id' => $payment->trans_id])->first();
                    Log::info('check_import_receipt_9pay_transaction','trace_start_1'.json_encode($payment_success_check));
                    if($payment_success_check){
                        $result = 'This transaction already exists';
                        dBug::trackingPhpErrorV2('Đã tồn tại :'.$payment->trans_id);
                        return response()->json(['success' => true, 'msg' => $result], 200);
                    }
                }
                $payment_success =  PaymentSuccess::where([ 'trans_id'=> $payment->trans_id,'status'=>2])->first();
                if(!$payment_success){
                    $payment_success = PaymentSuccess::create([
                        'trans_id'           => $payment->trans_id,
                        'type'               => 1,
                        'status'             => 0,
                        'data'               => json_encode($request->all()),
                        'number_bank'        => @$arrayParams->card_info->card_number,
                        'bank'               => @$arrayParams->bank ?? '',
                        'paid'               => $arrayParams->amount
                    ]);
                }
              
                $so_tien_total = $arrayParams->amount;
              
                if (!$payment) {
                    $result = 'This transaction does not exist or does not exist';
                    return response()->json(['success' => true, 'msg' => $result], 200);
                }
                $paymentOrder = PaymentOrder::where('order_id', $payment->order_id)->get();
                if (!$paymentOrder) {
                    $result = 'This transaction does not exist or does not exist';
                    return response()->json(['success' => true, 'msg' => $result], 200);
                }
                try {
                    DB::beginTransaction();
                    // Tạo phiếu thu
                    $log = null;
                    $billIds = array();
                    $receipt = null;
                    $customer = null;
                    $apartment = null;
                    $building = null;
                    $debit_details = null;
                    foreach ($paymentOrder as $key => $value) {
                        $debitDetail = DebitDetail::find($value->debit_id);
    
                        if ($key == 0) {
                            $code_receipt = $receiptRepository->autoIncrementCreditTransferReceiptCode($config, $debitDetail->bdc_building_id);
                            $customer = CustomersRespository::findApartmentIdV2($debitDetail->bdc_apartment_id, 0);
                            $pubUserProfile = $customer ? PublicUsersProfileRespository::getInfoUserById($customer->user_info_id) : null;
                            $apartment = ApartmentsRespository::getInfoApartmentsById($debitDetail->bdc_apartment_id);
                            $building = Building::get_detail_building_by_building_id($debitDetail->bdc_building_id);
                            $receipt = $receiptRepository->create([
                                'bdc_apartment_id' => $debitDetail->bdc_apartment_id,
                                'bdc_building_id' => $debitDetail->bdc_building_id,
                                'receipt_code' => $code_receipt,
                                'cost' => $so_tien_total,
                                'cost_paid' => $so_tien_total,
                                'customer_name' => $pubUserProfile->full_name,
                                'customer_address' => $apartment->name,
                                'provider_address' => 'Banking',
                                'bdc_receipt_total' => 'test',
                                'description' => $arrayParams->invoice_no,
                                'ma_khach_hang' => $apartment->code_customer,
                                'ten_khach_hang' =>  $apartment->name_customer,
                                'type_payment' => 'chuyen_khoan',
                                'url' => $base_url . "/admin/receipt/getReceipt/" . $code_receipt,
                                'user_id' =>  0,
                                'type' => 'phieu_bao_co',
                                'status' => 1,
                                'create_date' => Carbon::now(),
                                'config_type_payment' => 2,
                                'trans_id' => $payment->trans_id
                            ]);
                        }
    
    
                        $bill_code = Bills::get_detail_bill_by_apartment_id($debitDetail->bdc_bill_id)->bill_code;
                        array_push($billIds, $bill_code);
                        $paid_service = 0;
                        $sumery = $debitDetail->sumery - $debitDetail->paid;
                        if ($sumery == 0) {
                            continue;
                        }
                        if ($so_tien_total > $sumery) {
                            $paid_service = $sumery;
                            $so_tien_total -= $paid_service;
                        } else {
                            $paid_service = $so_tien_total;
                            $so_tien_total -= $paid_service;
                        }
    
                        // bắt đầu hạch toán
                        $paymentdetail = PaymentDetailRepository::createPayment(
                            $debitDetail->bdc_building_id,
                            $debitDetail->bdc_apartment_id,
                            $debitDetail->bdc_apartment_service_price_id,
                            Carbon::now()->format('Ym'),
                            $debitDetail->id,
                            $paid_service,
                            Carbon::now(),
                            $receipt->id,
                            0
                        );
    
                        $debitDetail->paid = $debitDetail->paid + $paid_service;
                        $debitDetail->save();
                        $debit_details[]=$debitDetail;
                        QueueRedis::setItemForQueue('add_queue_stat_payment_',[
                            "apartmentId" => $debitDetail->bdc_apartment_id,
                            "service_price_id" => $debitDetail->bdc_apartment_service_price_id,
                            "cycle_name" => Carbon::now()->format('Ym'),
                        ]);
    
                        $log[] = [
                            'debit_id' => $debitDetail->id,
                            'paid' => $paid_service,
                        ];
                    }
                    // xử lý thu thừa tiền
                    if ($so_tien_total > 0) {
                        BdcCoinRepository::addCoin($debitDetail->bdc_building_id, $debitDetail->bdc_apartment_id, 0, Carbon::now()->format('Ym'), @$customer->user_info_id ?? 0, $so_tien_total, 0, 1, $receipt->id);
                        $receipt->account_balance = $so_tien_total;
                    }
                    $receipt->logs = $log ? json_encode($log) : null;
                    $strBillIds = serialize($billIds);
                    $receipt->bdc_bill_id = $strBillIds;
                    $receipt->metadata = json_encode($debit_details);
                    $receipt->save();

                    PaymentSuccess::where([ 'trans_id'=> $payment->trans_id, 'type' => $request->method == 'ATM_CARD' ? 2 : 3])->update(['status'=>1]);

                    dBug::trackingPhpErrorV2('Thành công:'.$payment->trans_id);
                    DB::commit();
                    $apartId[] = $apartment->id; 
                    $list_userinfo_id = UserApartments::getListUserPushNotify($apartId);
                    Log::info('check_import_receipt_9pay_transaction','apartment :'.json_encode($apartment));
                    Log::info('check_import_receipt_9pay_transaction','user :'.json_encode($list_userinfo_id));
                    if (count($list_userinfo_id) > 0) {
                        $total = ['email' => 0, 'app' => count($list_userinfo_id), 'sms' => 0];
                        $campain = Campain::updateOrCreateCampain("Xác nhận thanh toán", config('typeCampain.NOTIFY_TRANSACTION_PAYMENT'), $apartment->id, $total, $apartment->building_id, 0, 0);
                        foreach ($list_userinfo_id as $key => $value) {
                            $this->sendNotify($apartment, $building, $value, $arrayParams->amount, $campain->id,$receipt);
                        }
                    }
                    $result = "Successful transaction processing";
                    return response()->json(['success' => true, 'msg' => $result], 200);
                } catch (\Exception $e) {
                    DB::rollBack();
                    Log::info('check_import_receipt_9pay_transaction','trace_5_'.json_encode($e->getMessage()));
                    Log::info('check_import_receipt_9pay_transaction','trace_6_:'.json_encode($e->getLine()));
                    return response()->json(['success' => false, 'msg' => $e->getMessage()], 422);
                }
            }
        }
       
       // echo $arrayParams;
       
    }

    function urlsafeB64Decode($input)
    {
        $remainder = \strlen($input) % 4;
        if ($remainder) {
            $padlen = 4 - $remainder;
            $input .= \str_repeat('=', $padlen);
        }
        return \base64_decode(\strtr($input, '-_', '+/'));
    }
   public function sendNotify($apartment, $building , $user_id , $amount = 0, $campainId ,$receipt)
    {
        $data_noti=[
            'message' => 'Ban quản lý đã nhận được số tiền : '. number_format($amount) .'vnd',
            'building_id' => @$building->id,
            'title' => '['.@$building->name."]_" .$apartment->name,
            'action_name' => SendNotifyFCMService::NOTIFY_TRANSACTION_PAYMENT,
            'image' => null,
            'type' => SendNotifyFCMService::NOTIFY_TRANSACTION_PAYMENT,
            'screen' => null,
            'id' => $receipt->id,
            'user_id' => $user_id,
            'app_config' => "cudan",
            'avatar' => "avatar/system/01.png",
            'campain_id' => $campainId
        ];

        SendNotifyFCMService::send('Ban quản lý đã nhận được số tiền : '. number_format($amount) .'vnd',$user_id,$data_noti,'['.@$building->name."]_" .$apartment->name,$campainId);
    } 
	
}
