<?php

namespace App\Console\Commands\V2;

use App\Exceptions\QueueRedis;
use App\Models\BdcApartmentServicePrice\ApartmentServicePrice;
use App\Models\BdcDebitDetail\DebitDetail as BdcDebitDetailDebitDetail;
use App\Models\BdcV2DebitDetail\DebitDetail;
use App\Repositories\BdcCoin\BdcCoinRepository;
use App\Repositories\Customers\CustomersRespository;
use App\Util\Debug\Log;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ConvertDebit extends Command
{
    /**
     * The name and signature of the console command.
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'convertdebit_cron';

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
                $value = QueueRedis::getItemForQueue("add_queue_insert_debit_v3");
                if(!$value){
                    break;
                }
                $value = (object)$value;
                $rs_debit = BdcDebitDetailDebitDetail::where(['bdc_apartment_service_price_id' => $value->bdc_apartment_service_price_id, 'cycle_name' => $value->cycle_name, 'version' => 0])->get();
                $bdc_building_id = 0;
                $bdc_bill_id = 0;
                $bdc_apartment_id = 0;
                $bdc_apartment_service_price_id = 0;
                $from_date = 0;
                $to_date = 0;
                $detail = 0;
                $cycle_name = null;
                $quantity = 0;
                $price = 0;
                $so_dau = 0;
                $so_cuoi = 0;
                $progressive = null;
                $dataJson=[];
                $sumery = 0;
                foreach ($rs_debit as $key_1 => $value_1) {
                    if ($key_1 == 0) {
                        $bdc_building_id = $value_1->bdc_building_id;
                        $bdc_bill_id = $value_1->bdc_bill_id;
                        $bdc_apartment_id = $value_1->bdc_apartment_id;
                        $bdc_apartment_service_price_id = $value_1->bdc_apartment_service_price_id;
                        $from_date = $value_1->from_date;
                        if ($value->bdc_price_type_id == 2) {
                            $detail = json_decode($value_1->detail);
                            $so_dau = @$detail->so_dau ?? 0;
                            $_apartmentServicePrice = DB::table('bdc_apartment_service_price')->find($value_1->bdc_apartment_service_price_id);
                            $progressive = DB::table('bdc_progressives')->find($_apartmentServicePrice->bdc_progressive_id);
                        }
                    }
                    // if($value_1->sumery < 0 && $value_1->sumery = $value_1->paid){
                    //      continue;
                    // }
                    // if($value_1->sumery < 0 && $value_1->sumery < $value_1->paid){
                    //     $excess_cash = abs($value_1->sumery) -  abs($value_1->paid);
                    //     $_customer = CustomersRespository::findApartmentIdV2($value->bdc_apartment_id, 0);
                    //     BdcCoinRepository::addCoin($bdc_building_id,$bdc_apartment_id,0,Carbon::now()->format('Ym'),$_customer->pub_user_profile_id,$excess_cash,1,1,0,'v1->v2');
                    //     continue;
                    // }
                    if ($value->bdc_price_type_id == 2) {
                        $detail_1 = json_decode($value_1->detail);
                        $so_cuoi = @$detail_1->so_cuoi ?? 0;
                        if(@$detail_1->data){
                            $dataJson = array_merge($dataJson,$detail_1->data);
                        }
                    }
                    $cycle_name = $value_1->cycle_name;
                    $sumery += $value_1->sumery;
                    $to_date =  $value_1->to_date;
                }
                if($sumery == 0 || $sumery < 0){
                    continue;
                }
                echo "$sumery \n";
                if ($value->bdc_price_type_id == 2) {
                        $progressivePrices =  DB::table('bdc_progressive_price')->where('progressive_id',$progressive->id)->get();
                        $price = 0;
                        $totalPrice = 0;
                        $dataArray = array();
                        $_dataArray = array();
                        $soDau = 0;
                        $soCuoi = 0;
                        $totalNumber = 0;
                       
                        foreach ($progressivePrices as $progressivePrice) {
                            // tính tổng tiền cho dich vụ điện nước
                            $soDau = $so_dau;
                            $soCuoi = $so_cuoi;
                            $totalNumber = $soCuoi - $soDau;
                            if ($progressivePrice->to >= $totalNumber) {
                                $price = ($totalNumber - $progressivePrice->from + 1) * $progressivePrice->price;
                                $_dataArray["from"] = $progressivePrice->from;
                                $_dataArray["to"] = $totalNumber;
                                $_dataArray["price"] = $progressivePrice->price;
                                $_dataArray["total_price"] = $price;
                                $totalPrice += $price;
                                array_push($dataArray, $_dataArray);
                                break;
                            } else {
                                $price = ($progressivePrice->to - $progressivePrice->from + 1) * $progressivePrice->price;
                                $_dataArray["from"] = $progressivePrice->from;
                                $_dataArray["to"] = $progressivePrice->to;
                                $_dataArray["price"] = $progressivePrice->price;
                                $_dataArray["total_price"] = $price;
                                $totalPrice += $price;
                                array_push($dataArray, $_dataArray);
                            }
                        }
                        $dataJson = json_encode($dataJson);
                        $dataString = '{"so_dau": ' . $soDau . ', "so_cuoi": ' . $soCuoi . ', "tieu_thu": ' . $totalNumber . ', "data":' . $dataJson . "}";
                       
                        $debit_v2 = DebitDetail::where([
                            'bdc_building_id' => $bdc_building_id,
                            'bdc_apartment_service_price_id' => $bdc_apartment_service_price_id,
                            'cycle_name' => $cycle_name])->first();
                        if($debit_v2){
                            $debit_v2->bdc_bill_id = $bdc_bill_id;
                            $debit_v2->from_date = $from_date;
                            $debit_v2->to_date = $to_date;
                            $debit_v2->sumery = $sumery;
                            $debit_v2->discount = 0;
                            $debit_v2->discount_type = null;
                            $debit_v2->discount_note = '';
                            $debit_v2->note = 'v1->v2'.'|'.$value->id;
                            $debit_v2->save();
                            continue;
                        }
                      
                        $check = DebitDetail::create([
                            'bdc_building_id' => $bdc_building_id,
                            'bdc_bill_id' => $bdc_bill_id,
                            'bdc_apartment_id' => $bdc_apartment_id,
                            'bdc_apartment_service_price_id' => $bdc_apartment_service_price_id,
                            'from_date' => $from_date,
                            'to_date' => $to_date,
                            'detail' => $dataString,
                            'previous_owed' => 0,
                            'cycle_name' => $cycle_name,
                            'quantity' => 0,
                            'price' => 0,
                            'sumery' => $sumery,
                            'note' =>  'v1->v2'.'|'.$value->id,
                        ]);
                        $count++;
                } else {
                    $debit_v2 = DebitDetail::where([
                        'bdc_building_id' => $bdc_building_id,
                        'bdc_apartment_service_price_id' => $bdc_apartment_service_price_id,
                        'cycle_name' => $cycle_name])->first();
                    if($debit_v2){
                        $debit_v2->bdc_bill_id = $bdc_bill_id;
                        $debit_v2->from_date = $from_date;
                        $debit_v2->to_date = $to_date;
                        $debit_v2->sumery = $sumery;
                        $debit_v2->discount = 0;
                        $debit_v2->discount_type = null;
                        $debit_v2->discount_note = '';
                        $debit_v2->note = 'v1->v2'.'|'.$value->id;
                        $debit_v2->save();
                        continue;
                    }
                    DebitDetail::create([
                        'bdc_building_id' => $bdc_building_id,
                        'bdc_bill_id' => $bdc_bill_id,
                        'bdc_apartment_id' => $bdc_apartment_id,
                        'bdc_apartment_service_price_id' => $bdc_apartment_service_price_id,
                        'from_date' => $from_date,
                        'to_date' => $to_date,
                        'detail' => '[]',
                        'previous_owed' => 0,
                        'cycle_name' => $cycle_name,
                        'quantity' => 1,
                        'price' => 0,
                        'sumery' => $sumery,
                        'note' => 'v1->v2'.'|'.$value->id,
                    ]);
                    $count++;
                }
            } while ($value);
        } catch (\Exception $e) {
            Log::info('result_insert_debit','1_:'.$e->getMessage().'|'.$e->getLine());
        }
    }
}
