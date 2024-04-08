<?php

namespace App\Console\Commands\V2;

use App\Exceptions\QueueRedis;
use App\Models\BdcBills\Bills;
use App\Models\BdcV2DebitDetail\DebitDetail;
use App\Models\BdcV2LogCoinDetail\LogCoinDetail;
use App\Models\BdcV2PaymentDetail\PaymentDetail;
use App\Repositories\BdcV2DebitDetail\DebitDetailRepository;
use App\Repositories\CronJobLogs\CronJobLogsRepository;
use App\Repositories\CronJobManager\CronJobManagerRepository;
use App\Util\Debug\Log;
use App\Util\Redis;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class WarningStatPaymentProcess extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'warning_stat_payment_process:cron {time?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cảnh báo lỗi tổ hợp công nợ';

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
        $start_time = time();
        $while_list = [92];
        $time = $this->argument('time');
        if ($time) {
            $time = (int)$time;
        } else {
            $time = 50;
        }
        if ($time < 50 || $time > 24 * 60 * 60) $time = 50;
        $data = false;
        $count = 0;
        try {
            $seconds = time();
            $allKey = Redis::zRANGEBYSCORE("warningUpdatePaidByCycleNameFromReceipt", $seconds - 30 * 24 * 60 * 60, $seconds - 45 * 60);
            $countAll = count($allKey);

            $count_stat_payment = Redis::getLenList("add_queue_stat_payment_");

            if($count_stat_payment < 500) { // đang còn tổ hợp nhiều thì không cảnh bảo vội
                foreach ($allKey as $v) {
                    if($countAll > 10000){
                        $checkNext = Redis::get("checkHandle_".$v);
                        if ($checkNext) continue;
                        Redis::setAndExpire("checkHandle_".$v,1, 10);
                    }
                    $data = unserialize($v);
                    if ($data) {
                        if(((int) $data['cycle_name']) < 209001) {
                            $check = DebitDetailRepository::warningUpdatePaidByCycleNameFromReceipt($data['apartment_id'], $data['service_price_id'], $data['cycle_name']);
                            if(!$check) {
                                $secondsNew = time() + 24 * 60 * 60; // 1 ngày sau kiểm tra lại
                                $data['time'] = $secondsNew;
                                Redis::zAdd("warningUpdatePaidByCycleNameFromReceipt", $secondsNew, serialize($data)); // add key to list
                                $count++;
                            }
                        }
                    }
                    Redis::zRem("warningUpdatePaidByCycleNameFromReceipt", $v);
                    if (((time() - $start_time) > $time) || $count >= 10) break;
                }
            }

            $timeCurrent = Carbon::now();
            $timeSchedule = Carbon::parse("2022-09-22 20:00:00");
            if ($timeCurrent->format('His.u') >= $timeSchedule->format('His.u')) {
                $keyCheck = "checkPushWarningEndDate_" . $timeCurrent->toDateString();
                $check = Redis::get($keyCheck);
                if (!$check) {
                    $tempCheck = [];
                    $seconds = time();
                    $timeCurrentTo = $timeCurrent->addDay(1);
                    $to = $timeCurrentTo->toDateString();
                    $timeCurrentFrom = $timeCurrent->subDay(1);
                    $from = $timeCurrentFrom->toDateString();

                    Log::info("tandc", "from: $from");
                    Log::info("tandc", "to: $to");

                    // bill
                    $abc = Bills::withTrashed()->whereBetween('updated_at', [$from, $to])->get();
                    foreach ($abc as $item2) {
                        $bc = DebitDetail::where(['bdc_bill_id' => $item2->id])->get();
                        foreach ($bc as $item) {
                            $dataPush2 = [
                                "time" => $seconds,
                                "apartment_id" => $item->bdc_apartment_id,
                                "service_price_id" => $item->bdc_apartment_service_price_id,
                                "cycle_name" => $item->cycle_name,
                            ];
                            if(!in_array(serialize($dataPush2),$tempCheck) && !in_array($item->bdc_building_id,$while_list)){
                                Redis::zAdd("warningUpdatePaidByCycleNameFromReceipt", $seconds, serialize($dataPush2)); // add key to list
                                $tempCheck[] = serialize($dataPush2);
                            }
                        }
                    }
                    // debit

                    $bc = DebitDetail::withTrashed()->whereBetween('updated_at', [$from, $to])->get();
                    foreach ($bc as $item) {
                        $dataPush2 = [
                            "time" => $seconds,
                            "apartment_id" => $item->bdc_apartment_id,
                            "service_price_id" => $item->bdc_apartment_service_price_id,
                            "cycle_name" => $item->cycle_name,
                        ];

                        if(!in_array(serialize($dataPush2),$tempCheck) && !in_array($item->bdc_building_id,$while_list)){
                            Redis::zAdd("warningUpdatePaidByCycleNameFromReceipt", $seconds, serialize($dataPush2)); // add key to list
                            $tempCheck[] = serialize($dataPush2);
                        }
                    }
                    // payment

                    $bc = PaymentDetail::withTrashed()->whereBetween('updated_at', [$from, $to])->get();
                    foreach ($bc as $item) {
                        $dataPush2 = [
                            "time" => $seconds,
                            "apartment_id" => $item->bdc_apartment_id,
                            "service_price_id" => $item->bdc_apartment_service_price_id,
                            "cycle_name" => $item->cycle_name,
                        ];

                        if(!in_array(serialize($dataPush2),$tempCheck) && !in_array($item->bdc_building_id,$while_list)){
                            Redis::zAdd("warningUpdatePaidByCycleNameFromReceipt", $seconds, serialize($dataPush2)); // add key to list
                            $tempCheck[] = serialize($dataPush2);
                        }
                    }

                    // log coin

                    $bc = LogCoinDetail::withTrashed()->whereBetween('updated_at', [$from, $to])->get();
                    foreach ($bc as $item) {
                        $dataPush2 = [
                            "time" => $seconds,
                            "apartment_id" => $item->bdc_apartment_id,
                            "service_price_id" => $item->bdc_apartment_service_price_id,
                            "cycle_name" => $item->cycle_name,
                        ];
                        if(!in_array(serialize($dataPush2),$tempCheck) && !in_array($item->bdc_building_id,$while_list)){
                            Redis::zAdd("warningUpdatePaidByCycleNameFromReceipt", $seconds, serialize($dataPush2)); // add key to list
                            $tempCheck[] = serialize($dataPush2);
                        }
                    }

                    Redis::setAndExpire($keyCheck, 1, 60 * 60 * 8);

                    Log::info("tandc", "count tempCheck: ".count($tempCheck));
                }
            }

        } catch (\Exception $e) {
            echo $e->getMessage();
            Log::log("tandc", "Error warning_stat_payment_process || " . $e->getMessage());
            Log::log("tandc", "Error warning_stat_payment_process data || " . \GuzzleHttp\json_encode($data));
        }
        return true;
    }
}
