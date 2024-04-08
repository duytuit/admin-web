<?php

namespace App\Console\Commands;

use App\Commons\Util\Debug\Log;
use App\Models\Campain;
use App\Models\CampainDetail;
use App\Services\FCM\SendNotifyFCMService;
use App\Services\SendTelegram;
use Illuminate\Console\Command;
use App\Services\RedisCommanService;

class SendNotificationV2 extends Command
{
    const AVATAR_SYSTEM = 'avatar/system/01.png';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bdc:notify_v2';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send Notification for User';

    protected $fcm;

       /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(SendNotifyFCMService $fcm)
    {
        parent::__construct();
        $this->fcm = $fcm;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $prioryty = 'normal';
        $content_available = true;
        try {
            $time_start = microtime(true);
            Log::info('check_send_notify_1','begin');
            do {
                $campains = Campain::findByType('app');
                //SendTelegram::SupersendTelegramMessage('data Noti'.$campains);
                if ($campains) {
                    foreach ($campains as $key => $value) {
                        // $check_campain = RedisCommanService::exitsKey([env('REDIS_QUEUE_PREFIX') . 'REDIS_SEND_APP_Campain_' .$value->id]);
                        // if($check_campain == 1){
                           $this->fcm->send($prioryty, $content_available,$value);
                        // }
                        // $check_campain_detail = CampainDetail::where(['campain_id' => $value->id, 'type' => 'app'])->first();
                        // if ($check_campain == 0 && $check_campain_detail) {
                        //     Campain::updateStatus($value->id, 'app');
                        //     RedisCommanService::delKey(env('REDIS_QUEUE_PREFIX') . 'REDIS_SEND_APP_Campain_running');
                        // }
                    }
                }
                $time_end = microtime(true);
                $time = $time_end - $time_start;
            } while ($time < 60);
            echo 'This command loaded in ', $time, ' seconds';  

        } catch (\Exception $e) {
             echo"\nERROR: ". $e->getMessage()."\n";
             echo"\nERROR: ". $e->getTraceAsString()."\n";
             Log::info('check_send_app','_1'.$e->getMessage());
             SendTelegram::SupersendTelegramMessage('Ex noti app V2:'.json_encode($e->getMessage()).$e->getLine());
        }
    }

}
