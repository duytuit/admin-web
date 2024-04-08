<?php

namespace App\Console\Commands\V2;

use App\Exceptions\QueueRedis;
use App\Models\BdcV2DebitDetail\DebitDetail;
use App\Models\BdcV2LogCoinDetail\LogCoinDetail;
use App\Models\BdcV2PaymentDetail\PaymentDetail;
use App\Models\DatabaseLog\DatabaseLog;
use App\Models\RequestLog\RequestLog;
use App\Repositories\BdcV2DebitDetail\DebitDetailRepository;
use App\Repositories\CronJobLogs\CronJobLogsRepository;
use App\Repositories\CronJobManager\CronJobManagerRepository;
use App\Util\Debug\Log;
use App\Util\Redis;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class LogProcess extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'handle_log:cron {time?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Xử lý log';

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
        $time = $this->argument('time');
        if ($time) {
            $time = (int)$time;
        } else {
            $time = 50;
        }
        if ($time < 50 || $time > 24 * 60 * 60) $time = 50;
        try {
            do {
                $rs = false;
                $data = QueueRedis::getItemForQueue("add_log_action");
                if (!empty($data) && isset($data['type'])) {

                    switch ($data['type']) {
                        case 1:
                            $rLog = [
                                "tool_id" => $data['toolId'] ?? 0,
                                "action" => $data['action'] ?? "",
                                "by" => $data['by'] ?? 0,
                                "time" => isset($data['time']) ? Carbon::createFromTimestamp($data['time'])->toDateTimeString() : "",
                                "url" => $data['url'] ?? "",
                                "param" => $data['param'] ?? "",
                                "building_id" => $data['buildingId'] ?? 0,
                                "status" => $data['status'] ?? 1,
                                "error" => "",
                                "request_id" => $data['requestId'] ?? 0,
                                "type" => 0,
                                "timestamp" => $data['time'] ?? 0
                            ];
                            if($data['action'] === "view"){
                                break;
                            }
                            $rs = RequestLog::create($rLog);
                            break;
                        case 2:
                            $rs = RequestLog::where(["request_id" => $data['requestId']])->update(["error" => $data['mess'], "status" => 0]);
                            break;
                        case 3:
                            $dLog = [
                                "table" => $data['table'] ?? "",
                                "action" => $data['action'] ?? "",
                                "by" => $data['by'] ?? 0,
                                "time" => isset($data['time']) ? Carbon::createFromTimestamp($data['time'])->toDateTimeString() : "",
                                "data_old" => $data['dataOld'] ?? "",
                                "data_new" => $data['dataNew'] ?? "",
                                "building_id" => $data['buildingId'] ?? 0,
                                "request_id" => $data['requestId'] ?? 0,
                                "sql" => $data['sql'] ?? "",
                                "row_id" => $data['rowId'] ?? 0,
                                "timestamp" => $data['time'] ?? 0
                            ];
                            $rs = DatabaseLog::create($dLog);

                            // kiểm tra liên quan đến công nợ thì đẩy vào queue kiểm tra

                            try {
                                if (in_array($data['table'] ?? "",["bdc_v2_debit_detail","bdc_v2_payment_detail"]) && in_array($data['action'] ?? "",["insert", "update", "delete", "import"])) {

                                    $seconds = (int)floor(microtime(true));
                                    $dataPush = false;

                                    switch ($data['table'] ?? "") {
                                        case "bdc_v2_log_coin_detail":
                                        case "bdc_v2_debit_detail":
                                        case "bdc_v2_payment_detail":
                                            switch ($data['action'] ?? "") {
                                                case "delete":
                                                case "insert":
                                                    $dataNew = \GuzzleHttp\json_decode($data['dataNew']);
                                                    if(!$dataNew || !isset($dataNew->bdc_apartment_id) || !isset($dataNew->bdc_apartment_service_price_id) || !isset($dataNew->cycle_name)) goto nextItem;
                                                $dataPush = [
                                                        "time" => $seconds,
                                                        "apartment_id" =>  $dataNew->bdc_apartment_id,
                                                        "service_price_id" => $dataNew->bdc_apartment_service_price_id,
                                                        "cycle_name" => $dataNew->cycle_name,
                                                    ];
                                                    break;
                                                case "update":
                                                    $dataNew = \GuzzleHttp\json_decode($data['dataNew']);
                                                    if(!$dataNew || !isset($dataNew->bdc_apartment_id) || !isset($dataNew->bdc_apartment_service_price_id) || !isset($dataNew->cycle_name)) goto nextItem;
                                                    $dataPush = [
                                                        "time" => $seconds,
                                                        "apartment_id" =>  $dataNew->bdc_apartment_id,
                                                        "service_price_id" => $dataNew->bdc_apartment_service_price_id,
                                                        "cycle_name" => $dataNew->cycle_name,
                                                    ];
                                                    $dataOld = \GuzzleHttp\json_decode($data['dataOld']);
                                                    if(!$dataOld || !isset($dataOld->bdc_apartment_id) || !isset($dataOld->bdc_apartment_service_price_id) || !isset($dataOld->cycle_name)) goto nextItem;
                                                    if($dataNew->bdc_apartment_id != $dataOld->bdc_apartment_id || $dataNew->bdc_apartment_service_price_id != $dataOld->bdc_apartment_service_price_id || $dataNew->cycle_name != $dataOld->cycle_name){
                                                        $dataPush2 = [
                                                            "time" => $seconds,
                                                            "apartment_id" =>  $dataOld->bdc_apartment_id,
                                                            "service_price_id" => $dataOld->bdc_apartment_service_price_id,
                                                            "cycle_name" => $dataOld->cycle_name,
                                                        ];
                                                        Redis::zAdd("warningUpdatePaidByCycleNameFromReceipt", $seconds, serialize($dataPush2)); // add key to list
                                                    }
                                                    break;
//                                                case "import":
//                                                    break;
                                                default:
                                                    break;
                                            }
                                            break;
                                        case "bdc_bills":
                                            switch ($data['action'] ?? "") {
                                                case "delete":
                                                    $dataNew = \GuzzleHttp\json_decode($data['dataNew']);
                                                    if(!$dataNew) goto nextItem;
                                                    $bc = DebitDetail::where(['bdc_bill_id' => $dataNew->id])->get();
                                                    foreach ($bc as $item) {
                                                        $dataPush2 = [
                                                            "time" => $seconds,
                                                            "apartment_id" =>  $item->bdc_apartment_id,
                                                            "service_price_id" => $item->bdc_apartment_service_price_id,
                                                            "cycle_name" => $item->cycle_name,
                                                        ];
                                                        Redis::zAdd("warningUpdatePaidByCycleNameFromReceipt", $seconds, serialize($dataPush2)); // add key to list
                                                    }
                                                    goto nextItem;
                                                    break;
                                                case "update":
                                                    $dataNew = \GuzzleHttp\json_decode($data['dataNew']);
                                                    if(!$dataNew) goto nextItem;
                                                    $dataOld = \GuzzleHttp\json_decode($data['dataOld']);
                                                    if(!$dataOld) goto nextItem;
                                                    if ($dataNew->status != $dataOld->status) {
                                                        $bc = DebitDetail::where(['bdc_bill_id' => $dataNew->id])->get();
                                                        foreach ($bc as $item) {
                                                            $dataPush2 = [
                                                                "time" => $seconds,
                                                                "apartment_id" =>  $item->bdc_apartment_id,
                                                                "service_price_id" => $item->bdc_apartment_service_price_id,
                                                                "cycle_name" => $item->cycle_name,
                                                            ];
                                                            Redis::zAdd("warningUpdatePaidByCycleNameFromReceipt", $seconds, serialize($dataPush2)); // add key to list
                                                        }
                                                    }
                                                    goto nextItem;
                                                    break;
//                                                case "import":
//                                                    break;
                                                default:
                                                    break;
                                            }
                                            break;
                                        case "bdc_receipts":
                                            switch ($data['action'] ?? "") {
                                                case "delete":
                                                    $dataNew = \GuzzleHttp\json_decode($data['dataNew']);
                                                    if(!$dataNew) goto nextItem;

                                                    $bc = PaymentDetail::where(['bdc_receipt_id' => $dataNew->id])->orderBy('id', 'asc')->get();
                                                    foreach ($bc as $item) {
                                                        $dataPush2 = [
                                                            "time" => $seconds,
                                                            "apartment_id" =>  $item->bdc_apartment_id,
                                                            "service_price_id" => $item->bdc_apartment_service_price_id,
                                                            "cycle_name" => $item->cycle_name,
                                                        ];
                                                        Redis::zAdd("warningUpdatePaidByCycleNameFromReceipt", $seconds, serialize($dataPush2)); // add key to list
                                                    }

                                                    $bc = LogCoinDetail::where(['from_id' => $dataNew->id])->whereIn("from_type", [1,5,6,9])->get();
                                                    foreach ($bc as $item) {
                                                        $dataPush2 = [
                                                            "time" => $seconds,
                                                            "apartment_id" =>  $item->bdc_apartment_id,
                                                            "service_price_id" => $item->bdc_apartment_service_price_id,
                                                            "cycle_name" => $item->cycle_name,
                                                        ];
                                                        Redis::zAdd("warningUpdatePaidByCycleNameFromReceipt", $seconds, serialize($dataPush2)); // add key to list
                                                    }

                                                    goto nextItem;
                                                    break;
                                                default:
                                                    break;
                                            }
                                            break;
                                        default:
                                            break;
                                    }

                                    if($dataPush === false) goto nextItem;
                                    Redis::zAdd("warningUpdatePaidByCycleNameFromReceipt", $seconds, serialize($dataPush)); // add key to list
                                }
                            } catch (\Exception $e) {
                                echo $e->getMessage();
                                Log::log("tandc", "Error LogProcess 2 || " . $e->getMessage());
                                Log::log("tandc", "Error LogProcess data || " . \GuzzleHttp\json_encode($dLog));
                            }
                            nextItem:

                            break;
                        default:
                            break;
                    }
                }
            } while ($data != null && ((time() - $start_time) < $time));
        } catch (\Exception $e) {
            echo $e->getMessage();
            Log::log("tandc", "Error LogProcess || " . $e->getMessage());
        }
    }
}
