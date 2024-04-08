<?php

namespace App\Console\Commands;

use App\Repositories\MailTemplates\MailTemplateRepository;
use App\Repositories\SettingSendMails\SettingSendMailRepository;
use App\Services\SendSMSSoap;
use App\Util\Debug\Log;
use Illuminate\Console\Command;

class SendSMSCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bdc:sendsms';

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
        // try {
        //     Log::info('check_send_mail_sms','_error');
        // } catch (\Exception $e) {
        // }
    }
}
