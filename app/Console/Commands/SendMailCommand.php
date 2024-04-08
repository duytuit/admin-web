<?php

namespace App\Console\Commands;

use App\Models\Campain;
use App\Models\CampainDetail;
use App\Repositories\MailTemplates\MailTemplateRepository;
use App\Repositories\SettingSendMails\SettingSendMailRepository;
use App\Services\RedisCommanService;
use App\Services\SendTelegram;
use App\Services\ServiceSendMailV2;
use Exception;
use Illuminate\Console\Command;

class SendMailCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dxmb:sendmail';

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

    public function handle(SettingSendMailRepository $sendMailRepository, MailTemplateRepository $mailTemplateRepository)
    {
        try {
            $time_start = microtime(true);
            do {
                $campains = Campain::findByType('email');
                if ($campains) {
                    echo json_encode($campains) .'</br>';
                    foreach ($campains as $key => $value) {
                        echo json_encode($value) .'</br>';
                        $check_campain = RedisCommanService::exitsKey([env('REDIS_QUEUE_PREFIX') . 'REDIS_SEND_MAIL_Campain_'. $value->id]);
                        if($check_campain == 1){
                            ServiceSendMailV2::sendMail($sendMailRepository, $mailTemplateRepository, $value);
                        }
                        $check_campain_detail = CampainDetail::where(['campain_id' => $value->id, 'type' => 'email'])->first();
                        if($check_campain == 0 && $check_campain_detail){
                            Campain::updateStatus($value->id,'email');
                            RedisCommanService::delKey(env('REDIS_QUEUE_PREFIX') . 'REDIS_SEND_MAIL_Campain_running');
                            SendTelegram::SupersendTelegramMessage('REDIS_SEND_MAIL_Campain_running');
                        }
                    }
                }
                $time_end = microtime(true);
                $time = $time_end - $time_start;
            } while ($time < 30);
            echo 'This command loaded in ', $time, ' seconds';  
        } catch (Exception $e) {
           // Log::info('check_send_mail','_error'.$e->getMessage());
           SendTelegram::SupersendTelegramMessage('Fail send mail'.$e->getMessage());
        }
       
       
    }
}
