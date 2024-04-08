<?php

namespace App\Console\Commands\V2;

use App\Exceptions\QueueRedis;
use App\Models\BdcV2LogCoinDetail\LogCoinDetail;
use App\Models\BdcV2PaymentDetail\PaymentDetail;
use App\Repositories\BdcV2DebitDetail\DebitDetailRepository;
use App\Repositories\BdcV2LogCoinDetail\LogCoinDetailRepository;
use App\Repositories\BdcV2PaymentDetail\PaymentDetailRepository;
use App\Repositories\CronJobLogs\CronJobLogsRepository;
use App\Repositories\CronJobManager\CronJobManagerRepository;
use App\Repositories\Customers\CustomersRespository;
use App\Util\Debug\Log;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ConvertPaymentProcess extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'convert_payment_process:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cập nhật lại số tiền đã thanh toán';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(
        CronJobManagerRepository $cronJobManager,
        CronJobLogsRepository    $cronJobLogsRepository)
    {
        ini_set('memory_limit', '-1');
//        $cronJobs = $cronJobManager->findSignature($this->signature)->get();
        $listAllow = [/*72*/];
        try {
//            foreach ($cronJobs as $cronJob) {
                do {
                    $receipt = QueueRedis::getItemForQueue("add_queue_convert_payment");
//                    continue;
                    if ($receipt) {
//                        dd(123);
                        $receipt = (object) $receipt;
                        if (!$receipt->data || !$receipt->id) continue;
                        $building_id = $receipt->bdc_building_id;

                        if(!in_array($building_id,$listAllow)) continue;

                        try {
                            $arr_id = [];
                            $arr_hachtoan = [];
                            $data_debit = unserialize($receipt->data);
                            $total_sub = 0;
                            $total = 0;
//                dd($data_debit);
                            $next = false;
                            $listDebitSelect = [];
                            foreach ($data_debit as $item2 => $value2) {
                                if($next) continue;
                                $bill_id = isset($value2["bill_id"]) ? $value2["bill_id"] : false;
                                $apartment_service_price_id = isset($value2["apartment_service_price_id"]) ? $value2["apartment_service_price_id"] : false;
                                $service_id = isset($value2["service_id"]) ? $value2["service_id"] : false;
                                $version = isset($value2["version"]) ? $value2["version"] : false;
                                $new_debit_id = isset($value2["new_debit_id"]) ? $value2["new_debit_id"] : false;
                                if ($new_debit_id) {
                                    $sql2 = "SELECT * from bdc_debit_detail WHERE id = " . $new_debit_id . " AND deleted_at is null  ";
                                    $index = 0;
                                } elseif ($apartment_service_price_id) {
                                    $sql2 = "SELECT * from bdc_debit_detail WHERE bdc_building_id = " . $building_id . " AND deleted_at is null AND bdc_bill_id = " . $bill_id . " AND bdc_apartment_service_price_id = " . $apartment_service_price_id . " AND version = " . $version;
                                    if(isset($listDebitSelect["_".$building_id.$bill_id.$apartment_service_price_id.$version])){
                                        $index = count($listDebitSelect["_".$building_id.$bill_id.$apartment_service_price_id.$version]);
                                    } else {
                                        $index = 0;
                                    }
                                    $listDebitSelect["_".$building_id.$bill_id.$apartment_service_price_id.$version][]=1;
                                } else {
                                    $sql2 = "SELECT * from bdc_debit_detail WHERE bdc_building_id = " . $building_id . " AND deleted_at is null AND bdc_bill_id = " . $bill_id . " AND bdc_service_id = " . $service_id . " AND version = " . $version;
                                    $check_service = DB::table('bdc_services')->where('id', $service_id)->first();
                                    $check_pt_type = $check_service ? strpos($check_service->name, "Phí dịch vụ - Xe") : false;
                                    if ($building_id == 71 && $check_service && ($check_service->type == 4 || $check_pt_type !== false)) {
                                            $sql3 = "SELECT * FROM receipt_logs WHERE bill_id = ".$bill_id." AND bdc_service_id = ".$service_id;
                                            $check_reciept_log = DB::select(DB::raw($sql3)); // check convert phuong tien
                                            if (!$check_reciept_log) continue;
                                            $input_reciept_log = json_decode($check_reciept_log[0]->input);
                                            $apartment_service_price_id = $input_reciept_log->apartment_service_price_id;
                                            $sql2 = "SELECT * from bdc_debit_detail WHERE bdc_building_id = " . $building_id . " AND deleted_at is null AND bdc_bill_id = " . $bill_id . " AND bdc_apartment_service_price_id = " . $apartment_service_price_id . " AND version = " . $version;
                                    }

                                    if (isset($listDebitSelect["_" . $building_id . $bill_id . $service_id . $version])) {
                                        $index = count($listDebitSelect["_" . $building_id . $bill_id . $service_id . $version]);
                                    } else {
                                        $index = 0;
                                    }
                                    $listDebitSelect["_" . $building_id . $bill_id . $service_id . $version][] = 1;
                                }

                                $debitDetail = DB::select(DB::raw($sql2));
                                if (!$debitDetail) continue;

//                                $debitDetail = $debitDetail[$index];
                                $debitDetail = $debitDetail[$index] ?? $debitDetail[0];

                                $arr_id[] = $debitDetail->id;

                                if ($debitDetail->paid == 0) continue;

                                if ($debitDetail->paid > 0){ // bill bỏ qua
                                    $sql3 = "SELECT * from bdc_bills WHERE id =  " . $debitDetail->bdc_bill_id." AND deleted_at is null ";
                                    $checkBill = DB::select(DB::raw($sql3));
                                    if (!$checkBill) continue;
                                    $debitbillCheck = $checkBill[0];
                                    if (!($debitbillCheck->status >= -2)) continue;
                                }

                                /*QueueRedis::setItemForQueue('add_queue_stat_payment_', [
                                    "apartmentId" => $debitDetail->bdc_apartment_id,
                                    "service_price_id" => $debitDetail->bdc_apartment_service_price_id,
                                    "cycle_name" => $debitDetail->cycle_name,
                                ]);*/

                                if ($receipt->cost < 0) { // bỏ túi đồng lẻ
                                    $sql3 = "SELECT * from bdc_v2_payment_detail WHERE bdc_building_id =  " . $building_id . " AND bdc_apartment_service_price_id = " . $debitDetail->bdc_apartment_service_price_id . " AND cycle_name = " . $debitDetail->cycle_name . " AND bdc_receipt_id = " . $receipt->id . " AND created_at <= '" . \Carbon\Carbon::now()->subSeconds(3)->format('Y-m-d H:i:s') . "'";
                                    $checkPayDetail = DB::select(DB::raw($sql3));
                                    if (!$checkPayDetail) {

                                        // tìm loại bỏ coin thừa
                                        $sql3 = "SELECT * from bdc_v2_log_coin_detail WHERE from_type = 1 AND bdc_building_id = " . $building_id . " AND bdc_apartment_service_price_id = " . $debitDetail->bdc_apartment_service_price_id . " AND coin = " . abs($receipt->cost);
                                        $checkLogCoin = DB::select(DB::raw($sql3));
                                        if($checkLogCoin) { // nếu có thì triệt tiêu
                                            $checkLogCoin = $checkLogCoin[0];
                                            LogCoinDetail::where(['id'=> $checkLogCoin->id])->delete(); // xóa
                                        } else {
                                            $checkDebitV2 = DebitDetailRepository::getDebitByApartmentAndServiceAndCyclename($debitDetail->bdc_apartment_id, $debitDetail->bdc_apartment_service_price_id, $debitDetail->cycle_name, false);
                                            PaymentDetailRepository::createPayment(
                                                $debitDetail->bdc_building_id,
                                                $debitDetail->bdc_apartment_id,
                                                $debitDetail->bdc_apartment_service_price_id, // dịch vụ được cấn trừ
                                                Carbon::parse($receipt->create_date)->format('Ym'),
                                                $checkDebitV2 ? $checkDebitV2->id : 0,
                                                $receipt->cost, // chú ý
                                                $receipt->create_date,
                                                $receipt->id,
                                                0
                                            );
                                        }

                                    } else {
                                        $checkPayDetail = $checkPayDetail[0];
                                        if ($checkPayDetail->bdc_debit_detail_id == 0) {
                                            $checkDebitV2 = DebitDetailRepository::getDebitByApartmentAndServiceAndCyclename($debitDetail->bdc_apartment_id, $debitDetail->bdc_apartment_service_price_id, $debitDetail->cycle_name, false);
                                            if ($checkDebitV2) {
                                                $sql = "UPDATE bdc_v2_payment_detail SET bdc_debit_detail_id = ".$checkDebitV2->id." WHERE id = " . $checkPayDetail->id;
                                                DB::update($sql);
                                            }
                                        }
                                        $sql3 = "SELECT * from bdc_v2_log_coin_detail WHERE from_type = 1 AND bdc_building_id = " . $building_id . " AND bdc_apartment_service_price_id = " . $debitDetail->bdc_apartment_service_price_id . " AND coin = " . abs($receipt->cost);
                                        $checkLogCoin = DB::select(DB::raw($sql3));
                                        if($checkLogCoin) { // nếu có thì triệt tiêu
                                            $checkLogCoin = $checkLogCoin[0];
                                            LogCoinDetail::where(['id'=> $checkLogCoin->id])->delete(); // xóa
                                            PaymentDetail::where(['id'=> $checkPayDetail->id])->delete(); // xóa
                                        }
                                    }

                                    /*$note = "v1->v2-" . $receipt->id;
                                    $sql3 = "SELECT * from bdc_v2_log_coin_detail WHERE from_type = 4 AND bdc_building_id = " . $building_id . " AND bdc_apartment_service_price_id = " . $debitDetail->bdc_apartment_service_price_id . " AND cycle_name = " . $debitDetail->cycle_name . " AND note = '" . $note . "'";
                                    $checkLogCoin = DB::select(DB::raw($sql3));
                                    $_customer = CustomersRespository::findApartmentIdV2($debitDetail->bdc_apartment_id, 0);

                                    if (!$checkLogCoin) $log = LogCoinDetailRepository::createLogCoin(
                                        $debitDetail->bdc_building_id,
                                        $debitDetail->bdc_apartment_id,
                                        $debitDetail->bdc_apartment_service_price_id, // dịch vụ được cấn trừ
                                        $debitDetail->cycle_name,
                                        $_customer ? $_customer->pub_user_profile_id : "",
                                        abs($receipt->cost), 0, 0, 4, 0, "", $note);*/

                                    $next = true;
                                    continue;
                                }

                                if ($debitDetail->paid < 0 && $debitDetail->sumery < 0) {
                                    continue;
                                }

                                if ($debitDetail->paid < 0) $total_sub += abs($debitDetail->paid); else {

                                    $checkDebitV2 = DebitDetailRepository::getDebitByApartmentAndServiceAndCyclename($debitDetail->bdc_apartment_id, $debitDetail->bdc_apartment_service_price_id, $debitDetail->cycle_name, false);

                                    if (!$checkDebitV2) {
                                        echo " <p style='color: red'>---------------------------------------------------------------------------------------------------- kiểm tra lỗi ko có debit v2 ------" . $receipt->id . "</p>";
                                        echo "</br>";

                                        $paid = $debitDetail->paid;
                                        $paidCoin = 0;
                                        if($debitDetail->new_sumery < 0){
                                            $paid = ($debitDetail->paid + $debitDetail->new_sumery);
                                            $paidCoin = abs($debitDetail->new_sumery);
                                            if($paid < 0) { // nạp thêm tiền
                                                $paid = 0;
                                                $paidCoin = $debitDetail->paid;
                                            }
                                        }

                                        if($paid!=0) PaymentDetailRepository::createPayment(
                                            $debitDetail->bdc_building_id,
                                            $debitDetail->bdc_apartment_id,
                                            $debitDetail->bdc_apartment_service_price_id, // dịch vụ được cấn trừ
                                            Carbon::parse($receipt->create_date)->format('Ym'),
                                            0,
                                            $paid, // chú ý
                                            $receipt->create_date,
                                            $receipt->id,
                                            0
                                        );

                                        if($debitDetail->new_sumery < 0){
                                            $note = "v1->v2-" . $receipt->id;
                                            $sql3 = "SELECT * from bdc_v2_log_coin_detail WHERE from_type = 1 AND bdc_building_id = " . $building_id . " AND bdc_apartment_service_price_id = " . $debitDetail->bdc_apartment_service_price_id . " AND cycle_name = " . $debitDetail->cycle_name . " AND note = '" . $note . "'";
                                            $checkLogCoin = DB::select(DB::raw($sql3));
                                            if (!$checkLogCoin) {
                                                $_customer = CustomersRespository::findApartmentIdV2($debitDetail->bdc_apartment_id, 0);
                                                LogCoinDetailRepository::createLogCoin(
                                                    $debitDetail->bdc_building_id,
                                                    $debitDetail->bdc_apartment_id,
                                                    $debitDetail->bdc_apartment_service_price_id, // dịch vụ được cấn trừ
                                                    $debitDetail->cycle_name,
                                                    $_customer ? @$_customer->user_info_id : "",
                                                    $paidCoin, 1, 0, 1, $receipt->id, "", $note);
                                            }
                                        }

                                        continue;
                                    }

                                    $sql3 = "SELECT * from bdc_v2_payment_detail WHERE bdc_building_id =  " . $building_id . " AND bdc_apartment_service_price_id = " . $debitDetail->bdc_apartment_service_price_id . " AND cycle_name = " . $debitDetail->cycle_name . " AND bdc_receipt_id = " . $receipt->id . " AND created_at <= '" . \Carbon\Carbon::now()->subSeconds(3)->format('Y-m-d H:i:s') . "'";
                                    $checkPayDetail = DB::select(DB::raw($sql3));

                                    if (!$checkPayDetail) {
                                        $paid = $debitDetail->paid;
                                        $paidCoin = 0;
                                        if($debitDetail->new_sumery < 0){
                                            $paid = ($debitDetail->paid + $debitDetail->new_sumery);
                                            $paidCoin = abs($debitDetail->new_sumery);
                                            if($paid < 0) { // nạp thêm tiền
                                                $paid = 0;
                                                $paidCoin = $debitDetail->paid;
                                            }
                                        }

                                        if($paid!=0) PaymentDetailRepository::createPayment(
                                            $debitDetail->bdc_building_id,
                                            $debitDetail->bdc_apartment_id,
                                            $debitDetail->bdc_apartment_service_price_id, // dịch vụ được cấn trừ
                                            Carbon::parse($receipt->create_date)->format('Ym'),
                                            $checkDebitV2->id,
                                            $paid, // chú ý
                                            $receipt->create_date,
                                            $receipt->id,
                                            0
                                        );

                                        if($debitDetail->new_sumery < 0) {
                                            $note = "v1->v2-" . $receipt->id;
                                            $sql3 = "SELECT * from bdc_v2_log_coin_detail WHERE from_type = 1 AND bdc_building_id = " . $building_id . " AND bdc_apartment_service_price_id = " . $debitDetail->bdc_apartment_service_price_id . " AND cycle_name = " . $debitDetail->cycle_name . " AND note = '" . $note . "'";
                                            $checkLogCoin = DB::select(DB::raw($sql3));
                                            if (!$checkLogCoin) {
                                                $_customer = CustomersRespository::findApartmentIdV2($debitDetail->bdc_apartment_id, 0);
                                                LogCoinDetailRepository::createLogCoin(
                                                    $debitDetail->bdc_building_id,
                                                    $debitDetail->bdc_apartment_id,
                                                    $debitDetail->bdc_apartment_service_price_id, // dịch vụ được cấn trừ
                                                    $debitDetail->cycle_name,
                                                    $_customer ? @$_customer->user_info_id : "",
                                                    $paidCoin, 1, 0, 1, $receipt->id, "", $note);
                                            }
                                        }
                                    } else {
                                        if($debitDetail->new_sumery < 0) {

                                            $paid = ($debitDetail->paid + $debitDetail->new_sumery);
                                            $paidCoin = abs($debitDetail->new_sumery);
                                            if($paid < 0) { // nạp thêm tiền
                                                $paid = 0;
                                                $paidCoin = $debitDetail->paid;
                                            }

                                            $checkPayDetail = $checkPayDetail[0];
                                            $sql = "UPDATE bdc_v2_payment_detail SET paid = ".$paid." WHERE id = " . $checkPayDetail->id;
                                            if($paid != $checkPayDetail->paid) DB::update($sql);


                                            $note = "v1->v2-" . $receipt->id;
                                            $sql3 = "SELECT * from bdc_v2_log_coin_detail WHERE from_type = 1 AND bdc_building_id = " . $building_id . " AND bdc_apartment_service_price_id = " . $debitDetail->bdc_apartment_service_price_id . " AND cycle_name = " . $debitDetail->cycle_name . " AND note = '" . $note . "'";
                                            $checkLogCoin = DB::select(DB::raw($sql3));
                                            if (!$checkLogCoin) {
                                                $_customer = CustomersRespository::findApartmentIdV2($debitDetail->bdc_apartment_id, 0);
                                                LogCoinDetailRepository::createLogCoin(
                                                    $debitDetail->bdc_building_id,
                                                    $debitDetail->bdc_apartment_id,
                                                    $debitDetail->bdc_apartment_service_price_id, // dịch vụ được cấn trừ
                                                    $debitDetail->cycle_name,
                                                    $_customer ? @$_customer->user_info_id : "",
                                                    $paidCoin, 1, 0, 1, $receipt->id, "", $note);
                                            } else {
                                                $checkLogCoin = $checkLogCoin[0];
                                                $sql = "UPDATE bdc_v2_log_coin_detail SET coin = ".$paidCoin." WHERE id = " . $checkLogCoin->id;
                                                if($paidCoin != $checkLogCoin->coin) DB::update($sql);
                                            }
                                        }
//                                        echo " not insert ";
//                                        echo "</br>";
                                    }

                                    $arr_hachtoan[] = $debitDetail;
//                                    echo "Thanh toán payment : " . $debitDetail->paid;
//                                    echo "</br>";
                                    $total += $debitDetail->paid;
                                }
                            }

//                if(false){
                            if ($total_sub > 0) {
//                    echo $total_sub;
//                    echo "</br>";
//                    dd($arr_hachtoan);

                                foreach ($arr_hachtoan as $item => $value) {
                                    $debitDetail = $value;
                                    if ($value->paid > $total_sub) {
                                        $paid = $total_sub;
                                        $total_sub = 0;
                                    } else {
                                        $paid = $value->paid;
                                        $total_sub -= $value->paid;
                                    }
                                    $total -= $paid;


                                    $_customer = CustomersRespository::findApartmentIdV2($debitDetail->bdc_apartment_id, 0);

                                    $note = "v1->v2-" . $receipt->id;
                                    $sql3 = "SELECT * from bdc_v2_log_coin_detail WHERE from_type = 4 AND bdc_building_id = " . $building_id . " AND bdc_apartment_service_price_id = " . $debitDetail->bdc_apartment_service_price_id . " AND cycle_name = " . $debitDetail->cycle_name . " AND note = '" . $note . "'";
                                    $checkLogCoin = DB::select(DB::raw($sql3));

                                    if (!$checkLogCoin) $log = LogCoinDetailRepository::createLogCoin(
                                        $debitDetail->bdc_building_id,
                                        $debitDetail->bdc_apartment_id,
                                        $debitDetail->bdc_apartment_service_price_id, // dịch vụ được cấn trừ
                                        $debitDetail->cycle_name,
                                        $_customer ? @$_customer->user_info_id : "",
                                        abs($paid), 0, 0, 4, 0, "", $note);

//                                    echo "<p style='color: orange'>------------------------------------------------------------> Thanh toán log coid : " . $paid . "</p>";
//                                    echo "</br>";
                                    if ($total_sub <= 0) break;
                                }

                            }
//                            echo " --------------------------------------------------------(" . $receipt->cost . ")--------------(" . $total . ")----------------- ";
//                            echo "</br>";

                            $list_next = [91158];
                            if ($receipt->cost != $total && $receipt->cost > 0 && !in_array($receipt->id, $list_next)) {
                                echo " <p style='color: red'>---------------------------------------------------------------------------------------------------- kiểm tra lỗi ------" . $receipt->id . "</p>";
                                echo "</br>";
                                continue;
                            }
                        } catch (Exception $e) {

                        }
                    }
                } while ($receipt != null);
//                echo "\nStart update cron job : $cronJob->id\n";
//                $cronJobManager->update(['status' => 1], $cronJob->id);
//                echo "\nEnd update cron job : $cronJob->id\n";
//            }
        } catch (\Exception $e) {
            echo $e->getMessage();
            $cronJobLogsRepository->create([
                'bdc_building_id' => 0,
                'signature' => $this->signature,
                'input_data' => 'Input Error',
                'output_data' => $e->getMessage(),
                'status' => 501
            ]);
        }
    }
}
