<?php

namespace App\Console\Commands;

use App\Commons\Util\Redis;
use App\Models\Campain;
use App\Models\CampainDetail;
use App\Repositories\MailTemplates\MailTemplateRepository;
use App\Repositories\SettingSendMails\SettingSendMailRepository;
use App\Services\RedisCommanService;
use App\Services\SendTelegram;
use Illuminate\Console\Command;
use App\Services\ServiceSendMailV2;
use App\Util\Debug\Log;
use Exception;

class SendMailV2Command extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dxmb:sendmail_v2';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
    public function handle(SettingSendMailRepository $sendMailRepository, MailTemplateRepository $mailTemplateRepository)
    {
        try {
            $time_start = microtime(true);
            $count = 0;
            Log::info('check_send_mail','begin');
            $check_campain = RedisCommanService::exitsKey(['add_queue_stat_payment_']);
            do {
                // echo 'check status: '. $check_campain;  
                $campains = Campain::findByType('email');
                // echo json_encode($campains) .'</br>';
                // Log::info('check_send_mail','_1'.json_encode($campains));
                if ($campains) {
                    foreach ($campains as $key => $value) {
                        // Log::info('check_send_mail','_1_campain'.$check_campain.$value->type);
                        if ($check_campain == 1 && $value->type == 6) {  // nếu như còn đang chạy tổ hợp thì chưa cho chạy gửi email
                            break;
                        }
                        // Log::info('check_send_mail','_2_'.json_encode($value));
                        // $check_campain = RedisCommanService::exitsKey([env('REDIS_QUEUE_PREFIX') . 'REDIS_SEND_MAIL_Campain_'. $value->id]);
                        // if($check_campain == 1){
                        ServiceSendMailV2::sendMail($sendMailRepository, $mailTemplateRepository, $value);
                        // }
                        // $check_campain_detail = CampainDetail::where(['campain_id' => $value->id, 'type' => 'email'])->first();
                        // if($check_campain == 0 && $check_campain_detail){
                        //     Campain::updateStatus($value->id,'email');
                        //     RedisCommanService::delKey(env('REDIS_QUEUE_PREFIX') . 'REDIS_SEND_MAIL_Campain_running');
                        // }
                    }
                }
                $time_end = microtime(true);
                $time = $time_end - $time_start;
            } while ($time < 30);
            echo 'This command loaded in ', $time, ' seconds';  
        } catch (Exception $e) {
            Log::info('check_send_mail','_error'.$e->getMessage());
            SendTelegram::SupersendTelegramMessage('Handle Mail V2:'.$e->getMessage().':'.$e->getLine());
        }
    }
}
