<?php

namespace App\Console\Commands\V2;

use App\Exceptions\QueueRedis;
use App\Repositories\BdcV2DebitDetail\DebitDetailRepository;
use App\Repositories\CronJobLogs\CronJobLogsRepository;
use App\Repositories\CronJobManager\CronJobManagerRepository;
use App\Services\SendTelegram;
use App\Util\Debug\Log;
use Illuminate\Console\Command;

class StatPaymentProcess extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create_stat_payment_process:cron {time?}';

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
        $start_time = time();
        $time = $this->argument('time');
        if ($time) {
            $time = (int) $time;
        } else {
            $time = 50;
        }
        if($time < 50 || $time > 24*60*60) $time = 50;
        try {
                do {
                    $data = QueueRedis::getItemForQueue("add_queue_stat_payment_");
                    if (!empty($data) && isset($data['apartmentId']) && isset($data['service_price_id']) && isset($data['cycle_name'])) {
                        DebitDetailRepository::updatePaidByCycleNameFromReceipt($data['apartmentId'], $data['service_price_id'], $data['cycle_name'], $data['update_before_after'] ?? true);
                    }
                } while ($data != null && ((time() - $start_time) < $time));
        } catch (\Exception $e) {
            echo $e->getMessage();
            Log::log("tandc", "Error create_stat_payment_process || " . $e->getMessage());
            if(isset($data) && !empty($data)) QueueRedis::setItemForQueue('add_queue_stat_payment_', $data);
        }
    }
}
