<?php

namespace App\Console\Commands;

use App\Commons\Util\Debug\Log;
use App\Models\Campain;
use App\Models\CampainDetail;
use App\Services\RedisCommanService;
use Illuminate\Console\Command;
use App\Services\SendSMSSoapV2;
use Illuminate\Support\Carbon;

class SendSMSCommandV2 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bdc:sendsms_v2';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send SMS';

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
    public function handle()
    {
        $result = [];
        try {
            $time_start = time();
            Log::info('check_send_sms','begin.');
            do {
                $campains = Campain::findByType('sms');
                if ($campains->count()) {
                    foreach ($campains as $key => $value) {
                        Log::info('check_send_sms','log 1.1 '.\GuzzleHttp\json_encode($value));

                        $check_campain = RedisCommanService::exitsKey([env('REDIS_QUEUE_PREFIX') . 'REDIS_SEND_SMS_Campain_' . $value->id]);

                        Log::info('check_send_sms','log 1.2 '.$check_campain);

                        $data_payload = NULL;
                        if ($check_campain == 1) {
                            $data_payload = SendSMSSoapV2::getItemForQueueV2($value->id);
                            if ($data_payload == NULL) {
                                Campain::updateStatus($value->id, 'sms');
                                RedisCommanService::delKey(env('REDIS_QUEUE_PREFIX') . 'REDIS_SEND_SMS_Campain_running');
                                break;
                            }
                            echo json_encode($data_payload) . '</br>';
                            SendSMSSoapV2::sendSMS($data_payload['content'], $data_payload['target'], $data_payload['building_id'], $data_payload['type'], null, $value);
                        }
                        Log::info('check_send_sms','log 1.3 ');
                        if ($check_campain == 0 && ($data_payload || !$value->created_at || (Carbon::now()->timestamp - $value->created_at->timestamp >= 60*60)) ) { // nếu campain đã tạo 1 tiếng rồi mà queue ko có thì update đã gửi xong
                            Campain::updateStatus($value->id, 'sms');
                            RedisCommanService::delKey(env('REDIS_QUEUE_PREFIX') . 'REDIS_SEND_SMS_Campain_running');
                        }
                        Log::info('check_send_sms','log 1.4 '.(time() - $time_start));
                        if(time() - $time_start > 50){
                            break;
                        }
                    }
                }
            } while (time() - $time_start < 50 && $campains->count());
            Log::info('check_send_sms', 'end' . (time() - $time_start));
            echo 'This command loaded in ', (time() - $time_start), ' seconds';
        } catch (\Exception $e) {
            Log::info('check_send_sms','ERROR || '. $e->getMessage());
            echo"\nERROR: ". $e->getMessage()."\n";
        }
        return $result;
    }
}
