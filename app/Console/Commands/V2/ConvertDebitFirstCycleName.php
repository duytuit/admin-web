<?php

namespace App\Console\Commands\V2;

use App\Exceptions\QueueRedis;
use App\Models\BdcApartmentServicePrice\ApartmentServicePrice;
use App\Models\BdcV2DebitDetail\DebitDetail;
use App\Models\Service\Service;
use App\Util\Debug\Log;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ConvertDebitFirstCycleName extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'convertdebitfirstcyclename_cron';

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
            $discount_total=0;
            do {
                $value = QueueRedis::getItemForQueue("add_queue_insert_debit_v4_1");
                $value = (object)$value;
                if(!$value){
                    break;
                }
                if ($value && @$value->sumery > 0) {
                    $discount = $value->sumery;
                    $bdc_building_id =  $value->bdc_building_id;
                    $bdc_apartment_id =  $value->bdc_apartment_id;
                    $bdc_apartment_service =  $value->bdc_apartment_service_price_id;
                    $cycle_name =  $value->cycle_name;
                    $sql_distinct = "SELECT distinct cycle_name FROM bdc_debit_detail where bdc_building_id= $bdc_building_id and cycle_name >= $cycle_name and deleted_at is null";
                    $distinct_cycle_name = DB::select(DB::raw($sql_distinct));
                    foreach ($distinct_cycle_name as $key => $value_cycle_name) {
                        $cycle_name = (int)$value_cycle_name->cycle_name;
                        $sql = "SELECT 
                        tb.cycle_name,tb.bdc_bill_id,tb.bdc_apartment_service_price_id
                         FROM
                         bdc_debit_detail AS tb
                             inner JOIN
                         (SELECT 
                             MAX(version) as version,bdc_bill_id , bdc_apartment_service_price_id , bdc_apartment_id
                         FROM
                             bdc_debit_detail
                         WHERE
                                 bdc_building_id = $bdc_building_id
                                 AND bdc_apartment_id = $bdc_apartment_id
                                 AND cycle_name = $cycle_name
                                 AND deleted_at IS NULL
                         GROUP BY bdc_bill_id , bdc_apartment_service_price_id , bdc_apartment_id) AS tb1 ON 
                                 tb1.bdc_apartment_service_price_id = tb.bdc_apartment_service_price_id
                             AND tb1.bdc_apartment_id = tb.bdc_apartment_id
                             AND tb1.bdc_bill_id = tb.bdc_bill_id
                             AND tb1.version = tb.version
                             AND tb.deleted_at IS NULL and tb.new_sumery <= 0 and tb.sumery > 0";
                 
                        $rs_2 = DB::select(DB::raw($sql));
                        if($discount > 0 && $rs_2){
                            $cycle_name_array = null;
                            $bdc_apartment_service_price_id_array = null;
                            $bdc_bill_id_array = null;
                            foreach ($rs_2 as $key => $value_2) {
                                if ($value_2->cycle_name) {
                                   $cycle_name_array[] = $value_2->cycle_name;
                                }
                                if ($value_2->bdc_apartment_service_price_id) {
                                   $bdc_apartment_service_price_id_array[] = $value_2->bdc_apartment_service_price_id;
                                }
                                if ($value_2->bdc_bill_id) {
                                   $bdc_bill_id_array[] = $value_2->bdc_bill_id;
                                }
                            }
                            $result_cycle_name = array_unique($cycle_name_array);
                            $result_bdc_apartment_service_price_id = array_unique($bdc_apartment_service_price_id_array);
                            $result_bdc_bill_id = array_unique($bdc_bill_id_array);
                            
                            $ser = Service::where('type',3)->where(['bdc_building_id'=>$bdc_building_id])->pluck('id')->toArray();
                            $apartment_ser =  ApartmentServicePrice::whereIn('bdc_service_id',$ser)->where('bdc_apartment_id',$bdc_apartment_id)->pluck('id')->toArray();
                    
                            $debit_v2 = DebitDetail::where([
                                'bdc_building_id' => $bdc_building_id,
                                'bdc_apartment_id' => $bdc_apartment_id
                            ])
                            ->whereIn('cycle_name', $result_cycle_name)
                                ->whereIn('bdc_apartment_service_price_id', $result_bdc_apartment_service_price_id)
                                ->whereIn('bdc_bill_id', $result_bdc_bill_id)
                                ->where('bdc_bill_id', '>', 0)
                                ->orderBy('cycle_name')
                                ->get();
                            $data_debit = [];
                            foreach ($debit_v2 as $key => $value_3) {
                                if (in_array($value_3->bdc_apartment_service_price_id, $apartment_ser)) {
                                    array_unshift($data_debit, $value_3);
                                } else {
                                    $data_debit[] = $value_3;
                                }
                            }
       
                         
                           $total_sumery = 0;
                           foreach ($data_debit as $key => $value_1) {
                               $total_sumery += ($value_1->sumery);
                               $total_sumery_1 = ($value_1->sumery);
       
                               $rs_debit =  DebitDetail::find($value_1->id);
                               if ($discount > 0 && $rs_debit->discount == 0 && $total_sumery_1 > $discount) {
                                   $rs_debit->discount_note = $rs_debit->sumery;
                                   $rs_debit->sumery = $rs_debit->sumery - $discount;
                                   $rs_debit->discount_type = 0;
                                   $rs_debit->discount = $discount;
                                   $rs_debit->save();
                                   $discount -= $discount;
                               }
                               if ($discount > 0 && $rs_debit->discount == 0 && $total_sumery_1 <= $discount) {
                                   $rs_debit->discount_note = $rs_debit->sumery;
                                   $rs_debit->sumery = $rs_debit->sumery - $total_sumery_1;
                                   $rs_debit->discount_type = 0;
                                   $rs_debit->discount = $total_sumery_1;
                                   $rs_debit->save();
                                   $discount -= $total_sumery_1;
                               }
                              
                           }
                           
                        }
                    }
                    if($discount > 0){
                        echo "Can : $value->bdc_apartment_id con : $discount dich vu : $bdc_apartment_service"."</br>";
                    }
                    $discount_total+=$discount;
                }
               
            } while ($value);
            echo $discount_total . "</br>";
        } catch (\Exception $e) {
            echo "$e\n";
        }
    }
}
