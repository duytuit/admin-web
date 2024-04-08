<?php

namespace App\Console\Commands;

use App\Repositories\MailTemplates\MailTemplateRepository;
use App\Repositories\SettingSendMails\SettingSendMailRepository;
use App\Services\SendSMSSoap;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Repositories\PublicUsers\PublicUsersProfileRespository;
use App\Commons\Helper;
use App\Repositories\Building\CompanyRepository;
use Illuminate\Support\Facades\DB;


class UpdateCustomeCodeCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bdc:customerCode';
    private  $profile;
    private  $company;
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Tao ma khach hang. Co the dung de update neu thay trong database ma khach hang bi tao thieu';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(PublicUsersProfileRespository $profile, CompanyRepository $company)
    {

        parent::__construct();
        $this->profile = $profile;
        $this->company = $company;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
     public function handle()
    {
        $limit = 1000;
        $offset = 0;
        $active_building = [13,17,20,28,30,37,60,61,62,63,64,65];
        try {
            do {
                $data = null;
                $data = $this->getListData($limit, $offset, 'pub_user_profile');
                if (!count($data)) {
                    break;
                }

                foreach ($data as $value) {
                    if (in_array($value->bdc_building_id, $active_building)) {


                        $code = Helper::getCustomerCode($value->pub_user_id, $value->bdc_building_id);
                        dump($code);
                        $this->profile->update($code, $value->id);
                    }

                }
                $offset = $limit + $offset;

            } while ($data != null);
        } catch (\Exception $e) {

            echo"\nERROR: ". $e->getMessage()."\n";
        }
        echo "\n DONE.";
    }


    private function getListData($limit, $offset, $table)
    {
        return DB::connection('mysql')->table($table)->where('type', 1)->offset($offset)
                ->limit($limit)
                ->get();
    }
}
