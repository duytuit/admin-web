<?php

namespace App\Console\Commands;

use App\Commons\Util\Debug\Log;
use App\Exceptions\QueueRedis;
use App\Repositories\BdcApartmentServicePrice\ApartmentServicePriceRepository;
use App\Repositories\BdcBills\BillRepository;
use App\Repositories\BdcDebitDetail\DebitDetailRepository;
use App\Repositories\BdcDebitLogs\DebitLogsRepository;
use App\Repositories\Config\ConfigRepository;
use App\Repositories\CronJobLogs\CronJobLogsRepository;
use App\Repositories\CronJobManager\CronJobManagerRepository;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CreateDebitCronV2 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create_debit_process_v2:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Tạo hóa đơn và thiết lập công nợ';

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
    public function handle(
        CronJobManagerRepository $cronJobManager, 
        DebitDetailRepository $debitDetail, 
        BillRepository $bill, 
        CronJobLogsRepository $cronJobLogsRepository,
        ApartmentServicePriceRepository $apartmentServicePriceRepository,
        ConfigRepository $config,
        DebitLogsRepository $debitLogs)
    {
//        sleep(2);
        Log::info('tandc2','create_debit_process_v2:cron');
        $cronJobs = $cronJobManager->findSignature($this->signature)->get();
        $apartmentServicePrice = null;
        try
        {
            $flag_cronjob = QueueRedis::getFlagCronjob();
            if(!$flag_cronjob){
                QueueRedis::setFlagCronjob(1);
                foreach($cronJobs as $cronJob) {
                    do
                    {
                        $apartmentServicePrice = QueueRedis::getItemForQueue("add_queue_apartment_service_price_z{$cronJob->building_id}");
                        if (!empty($apartmentServicePrice)) {
                            echo "Start....";
                            
                            $apartmentServicePriceCollectConvert = collect($apartmentServicePrice);
    
                            $apartmentServicePrice = (object)collect($apartmentServicePrice);
    
                            foreach($apartmentServicePrice as $key => $_apartmentServicePrice) {
                                $_apartmentServicePrice = (object)$_apartmentServicePrice;
                                //Log::info("create_debit_process_v2", "apartmentId_: " . $_apartmentServicePrice->bdc_apartment_id);
                                echo "\Next Cycle : " . $_apartmentServicePrice->isNextCycle;
                                if($_apartmentServicePrice->dateUsing == 0 || $_apartmentServicePrice->isNextCycle){
                                    $debitLogs->create([
                                        'bdc_building_id' => $cronJob->building_id,
                                        'bdc_apartment_id' => $_apartmentServicePrice->bdc_apartment_id,
                                        'bdc_service_id' => $_apartmentServicePrice->bdc_service_id,
                                        'key' => "create_debit_process_v2:cron",
                                        'cycle_name' => $_apartmentServicePrice->cycle_name,
                                        'input' => json_encode($_apartmentServicePrice),
                                        'data' => "",
                                        'message' => "Thời gian sử dụng dịch vụ = 0",
                                        'status' => 110
                                    ]);
                                    continue;
                                }
                                echo "\nkey=xxxx" . $key;
                                // Tìm hóa đơn của tháng hiện tại đã tạo hay chưa
                                $BillApartment = $bill->findBuildingApartmentIdV2($_apartmentServicePrice->bdc_building_id, $_apartmentServicePrice->bdc_apartment_id, $_apartmentServicePrice->cycle_name);
                                //Tao hóa đơn
                                if($BillApartment) {
                                    try {
                                        $debitDetailMaxVersion = $debitDetail->findByBuildingApartmentServiceId(
                                            $_apartmentServicePrice->bdc_building_id, 
                                            $_apartmentServicePrice->bdc_apartment_id, 
                                            $_apartmentServicePrice->bdc_service_id);
                                        $previousOwed = 0;
                                        if ($debitDetailMaxVersion) {
                                            $previousOwed = $debitDetailMaxVersion->previous_owed;
                                        }
                                        // Tạo công nợ
                                        $debitDetail->create([
                                            'bdc_building_id' => $_apartmentServicePrice->bdc_building_id,
                                            'bdc_bill_id' => $BillApartment->id,
                                            'bdc_apartment_id' => $_apartmentServicePrice->bdc_apartment_id,
                                            'bdc_service_id' => $_apartmentServicePrice->bdc_service_id,
                                            'bdc_apartment_service_price_id' => $_apartmentServicePrice->id,
                                            'title' => $_apartmentServicePrice->name,
                                            'from_date' => $_apartmentServicePrice->from_date,
                                            'to_date' => $_apartmentServicePrice->to_date,
                                            'detail' => '[]',
                                            'version' => 0,
                                            'sumery' => $_apartmentServicePrice->price,
                                            'new_sumery' => $_apartmentServicePrice->price,
                                            'previous_owed' => $previousOwed,
                                            'paid' => 0,
                                            'is_free' => $_apartmentServicePrice->free,
                                            'cycle_name' => $_apartmentServicePrice->cycle_name,
                                            'price' => $_apartmentServicePrice->one_price,
                                            'quantity' => $_apartmentServicePrice->quantity,
                                            'bdc_price_type_id' => $_apartmentServicePrice->bdc_price_type_id,
                                            'price_current' => $_apartmentServicePrice->price_current
                                        ]);
                                        // Cập nhật last_time_pay
                                        $apartmentServicePriceRepository->update(['last_time_pay' => $_apartmentServicePrice->to_date], $_apartmentServicePrice->id);
                                    } catch (\Exception $e) {
                                        $debitLogs->create([
                                            'bdc_building_id' => $_apartmentServicePrice->bdc_building_id,
                                            'bdc_apartment_id' => $_apartmentServicePrice->bdc_apartment_id,
                                            'bdc_service_id' => $_apartmentServicePrice->bdc_service_id,
                                            'key' => "create_debit_process_v2:cron",
                                            'cycle_name' => $_apartmentServicePrice->cycle_name,
                                            'input' => json_encode($_apartmentServicePrice),
                                            'data' => '',
                                            'message' => $e->getMessage(),
                                            'status' => 110
                                        ]);
                                        continue;
                                    }
                                }else{
                                    $sum = $apartmentServicePriceCollectConvert->sum('price');
                                    $sumFree = $apartmentServicePriceCollectConvert->where('free', '=', '1')->sum('price');
                                    try {
                                        //DB::beginTransaction();
                                        $billResult = $bill->create([
                                            'bdc_apartment_id' => $_apartmentServicePrice->bdc_apartment_id,
                                            'bdc_building_id' => $_apartmentServicePrice->bdc_building_id,
                                            'bill_code' => $bill->autoIncrementBillCode($config, $cronJob->building_id),
                                            'cost' => $sum,
                                            'cost_free' => $sumFree,
                                            'customer_name' => $_apartmentServicePrice->customer_name,
                                            'customer_address' => $_apartmentServicePrice->customer_address == null ? "" : $_apartmentServicePrice->customer_address,
                                            'deadline' => $_apartmentServicePrice->deadline,
                                            'provider_address' => 'Banking',
                                            'is_vat' => 0,
                                            'status' => $bill::WAIT_FOR_CONFIRM,
                                            'notify' => 0,
                                            'cycle_name' => $_apartmentServicePrice->cycle_name,
                                            'user_id' => $cronJob->user_id,
                                        ]);
                                        $debitDetailMaxVersion = $debitDetail->findByBuildingApartmentServiceId(
                                            $_apartmentServicePrice->bdc_building_id, 
                                            $_apartmentServicePrice->bdc_apartment_id, 
                                            $_apartmentServicePrice->bdc_service_id);
                                        $previousOwed = 0;
                                        if ($debitDetailMaxVersion) {
                                            $previousOwed = $debitDetailMaxVersion->previous_owed;
                                        }
                                        
                                        // Tạo công nợ
                                        $debitDetail->create([
                                            'bdc_building_id' => $_apartmentServicePrice->bdc_building_id,
                                            'bdc_bill_id' => $billResult->id,
                                            'bdc_apartment_id' => $_apartmentServicePrice->bdc_apartment_id,
                                            'bdc_service_id' => $_apartmentServicePrice->bdc_service_id,
                                            'bdc_apartment_service_price_id' => $_apartmentServicePrice->id,
                                            'title' => $_apartmentServicePrice->name,
                                            'from_date' => $_apartmentServicePrice->from_date,
                                            'to_date' => $_apartmentServicePrice->to_date,
                                            'detail' => '[]',
                                            'version' => 0,
                                            'sumery' => $_apartmentServicePrice->price,
                                            'new_sumery' => $_apartmentServicePrice->price,
                                            'previous_owed' => $previousOwed,
                                            'paid' => 0,
                                            'is_free' => $_apartmentServicePrice->free,
                                            'cycle_name' => $_apartmentServicePrice->cycle_name,
                                            'price' => $_apartmentServicePrice->one_price,
                                            'quantity' => $_apartmentServicePrice->quantity,
                                            'bdc_price_type_id' => $_apartmentServicePrice->bdc_price_type_id,
                                            'price_current' => $_apartmentServicePrice->price_current
                                        ]);
                                        // Cập nhật last_time_pay
                                        $apartmentServicePriceRepository->update(['last_time_pay' => $_apartmentServicePrice->to_date], $_apartmentServicePrice->id);
                                        //DB::commit();
                                    } catch (\Exception $e) {
                                       // DB::rollBack();
                                        $debitLogs->create([
                                            'bdc_building_id' => $_apartmentServicePrice->bdc_building_id,
                                            'bdc_apartment_id' => $_apartmentServicePrice->bdc_apartment_id,
                                            'bdc_service_id' => $_apartmentServicePrice->bdc_service_id,
                                            'key' => "create_debit_process_v2:cron",
                                            'cycle_name' => $_apartmentServicePrice->cycle_name,
                                            'input' => json_encode($_apartmentServicePrice),
                                            'data' => "",
                                            'message' =>'line_1:'.$e->getLine().'|'. $e->getMessage(),
                                            'status' => 110
                                        ]);
                                        continue;
                                    }
                                   
                                }
                                $debitLogs->create([
                                    'bdc_building_id' => $cronJob->building_id,
                                    'bdc_apartment_id' => $_apartmentServicePrice->bdc_apartment_id,
                                    'bdc_service_id' => $_apartmentServicePrice->bdc_service_id,
                                    'key' => "create_debit_process_v2:cron",
                                    'cycle_name' => $_apartmentServicePrice->cycle_name,
                                    'input' => json_encode($_apartmentServicePrice),
                                    'data' => "",
                                    'message' => "Xử lý thành công.",
                                    'status' => 200
                                ]);
                            }
                            echo "\nIsSucces :";
                        }
                    }
                    while($apartmentServicePrice != null);
                    echo "\nStart update cron job : $cronJob->id\n";
                    $cronJobManager->update(['status' => 1], $cronJob->id);
                    echo "\nEnd update cron job : $cronJob->id\n";
                }   
                QueueRedis::forgetFlagCronjob();     
            }
           
        }
        catch(\Exception $e){
            $cronJobLogsRepository->create([
                'bdc_building_id' => 0,
                'signature' => 'create_debit_process_v2:cron',
                'input_data' => 'Input Error',
                'output_data' => $e->getMessage(),
                'status' => 501
            ]);
        }
    }
}
