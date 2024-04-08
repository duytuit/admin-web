<?php

namespace App\Console\Commands\V2;

use App\Exceptions\QueueRedis;
use App\Models\BdcV2DebitDetail\DebitDetail;
use App\Util\Debug\Log;
use Illuminate\Console\Command;

class ConvertDebitOnePrice extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'convertdebitoneprice_cron';

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
            $count = 0;
            do {
                $flag_cronjob = QueueRedis::getFlagQueue();
                if($flag_cronjob){
                    break;
                }
                $value = QueueRedis::getItemForQueue("add_queue_insert_debit_v3_1");
                if(!$value){
                      break;
                }
                $value = (object)$value;
                $debit_v2 = DebitDetail::where([
                    'bdc_building_id' => $value->bdc_building_id,
                    'bdc_apartment_service_price_id' => $value->bdc_apartment_service_price_id,
                    'cycle_name' => $value->cycle_name])->first();
                echo "$value->id \n";
                // echo  json_encode($debit_v2). " \n";
                if($debit_v2){
                    $debit_v2->bdc_bill_id = $value->bdc_bill_id;
                    $debit_v2->from_date = $value->from_date;
                    $debit_v2->to_date = $value->to_date;
                    $debit_v2->sumery = $value->sumery;
                    $debit_v2->discount = 0;
                    $debit_v2->discount_type = null;
                    $debit_v2->discount_note = '';
                    $debit_v2->note = 'v1->v2'.'|'.$value->id;
                    $debit_v2->save();
                    continue;
                }
                DebitDetail::create([
                    'bdc_building_id' => $value->bdc_building_id,
                    'bdc_bill_id' => $value->bdc_bill_id,
                    'bdc_apartment_id' => $value->bdc_apartment_id,
                    'bdc_apartment_service_price_id' => $value->bdc_apartment_service_price_id,
                    'from_date' => $value->from_date,
                    'to_date' => $value->to_date,
                    'detail' => '[]',
                    'previous_owed' => 0,
                    'cycle_name' => $value->cycle_name,
                    'quantity' => $value->quantity,
                    'price' => $value->price,
                    'sumery' => $value->sumery,
                    'note' => 'v1->v2'.'|'.$value->id,
                ]);
                $count++;
            } while ($value);
        } catch (\Exception $e) {
            Log::info('result_insert_debit','1_1:'.$e->getMessage().'|'.$e->getLine());
        }
    }
}
