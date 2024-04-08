<?php

namespace App\Console\Commands\V2;

use App\Exceptions\QueueRedis;
use App\Models\BdcV2DebitDetail\DebitDetail;
use App\Repositories\BdcCoin\BdcCoinRepository;
use App\Repositories\BdcV2DebitDetail\DebitDetailRepository;
use App\Repositories\Customers\CustomersRespository;
use App\Util\Debug\Log;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ConvertDebitExcessCash extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'convertdebitexcesscash_cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cập nhật detbit v1 - > v2';

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
        try {
            do {
                $value = QueueRedis::getItemForQueue("add_queue_insert_debit_excess_cash_v1");
                $value = (object)$value;
                if($value->new_sumery < 0){
                    // echo json_encode($value)."\n";
                     $_customer = CustomersRespository::findApartmentIdV2($value->bdc_apartment_id, 0);
                    // echo $ApartmentServiceId."\n";
                     $rs = BdcCoinRepository::addCoin($value->bdc_building_id,$value->bdc_apartment_id,$value->bdc_apartment_service_price_id,Carbon::now()->format('Ym'),@$_customer->user_info_id??0,abs($value->new_sumery),1,7,0,'v1->v2');
                     echo json_encode($rs)."\n";
                    }
            } while ($value);
        } catch (\Exception $e) {
            Log::info('result_insert_excess_cash_debit','1_1:'.$e->getMessage().'|'.$e->getLine());
        }
    }
}
