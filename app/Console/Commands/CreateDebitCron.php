<?php

namespace App\Console\Commands;

use App\Exceptions\QueueRedis;
use App\Repositories\BdcBills\BillRepository;
use App\Repositories\BdcDebitDetail\DebitDetailRepository;
use App\Repositories\CronJobLogs\CronJobLogsRepository;
use App\Repositories\CronJobManager\CronJobManagerRepository;
use App\Repositories\Service\ServiceRepository;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Webpatser\Uuid\Uuid;

class CreateDebitCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create_debit_process:cron';

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
        ServiceRepository $service,
        CronJobLogsRepository $cronJobLogsRepository)
    {
        $cronJobs = $cronJobManager->findSignature($this->signature)->get();
        $apartmentServicePrice = null;
        $isFlag = false;
        $isSuccess = true;
        try
        {
            foreach($cronJobs as $cronJob) {
                $count = 0;
                do
                {
                    \DB::beginTransaction();
                    // Lấy queue thông tin dịch vụ của tòa nhà
                    $apartmentServicePrice = QueueRedis::getItemForQueue('add_queue_apartment_service_price_' . $cronJob->building_id);
                    try {
                        if (!empty($apartmentServicePrice)) {
                            $apartmentServicePrice = (object)$apartmentServicePrice;
                            // kiểm tra đã tạo hóa đơn hay chưa
                            if(!$isFlag) {
                                // tạo hóa đơn
                                $billResult = $bill->create([
                                    'bdc_apartment_id' => $apartmentServicePrice->bdc_apartment_id,
                                    'bdc_building_id' => $apartmentServicePrice->bdc_building_id,
                                    'bill_code' => (string)Uuid::generate(),
                                    'cost' => $apartmentServicePrice->price,
                                    'customer_name' => $apartmentServicePrice->customer_name,
                                    'customer_address' => $apartmentServicePrice->customer_address,
                                    'deadline' => $apartmentServicePrice->deadline,
                                    'provider_address' => 'Banking',
                                    'is_vat' => 0,
                                    'status' => 0,
                                    'notify' => 0
                                ]);
                                $isFlag = true;
                            }
                            // Tìm công nợ dịch vụ tháng trước
                            $debitDetailMaxVersion = $debitDetail->findByBuildingApartmentServiceId(
                                $apartmentServicePrice->bdc_building_id, 
                                $apartmentServicePrice->bdc_apartment_id, 
                                $apartmentServicePrice->bdc_service_id);
                            $previousOwed = 0;
                            if ($debitDetailMaxVersion) {
                                $previousOwed = $debitDetailMaxVersion->previous_owed;
                                //$debitDetail->update(['previous_owed' => 0], $debitDetailMaxVersion->id);
                            }
                            $sumery = 0;
                            if ($apartmentServicePrice->dateUsing >= 30) {
                                $sumery = $apartmentServicePrice->price;
                            } else {
                                // $dateUsing = Carbon::parse($apartmentServicePrice->to_date)->diffInDays(Carbon::parse($firstTimeActive));
                                $sumery = ($apartmentServicePrice->price / 30) * $apartmentServicePrice->dateUsing;
                            }
                            // dd($billResult);
                            // Tạo công nợ
                            $debitDetail->create([
                                'bdc_building_id' => $apartmentServicePrice->bdc_building_id,
                                'bdc_bill_id' => $billResult->id,
                                'bdc_apartment_id' => $apartmentServicePrice->bdc_apartment_id,
                                'bdc_service_id' => $apartmentServicePrice->bdc_service_id,
                                'bdc_apartment_service_price_id' => $apartmentServicePrice->id,
                                'title' => $apartmentServicePrice->name,
                                'from_date' => $apartmentServicePrice->from_date,
                                'to_date' => $apartmentServicePrice->to_date,
                                'detail' => 'test',
                                'version' => 0,
                                'sumery' => $sumery,
                                'new_sumery' => $sumery,
                                'previous_owed' => $previousOwed,
                                'paid' => 0,
                                'is_free' => $apartmentServicePrice->free
                            ]);
                        }
                    } catch (\Exception $e) {
                        \DB::rollBack();
                        $count++;
                        if($count < 3)
                        {
                            QueueRedis::setItemForQueue('add_queue_apartment_service_price_' . $cronJob->building_id, $apartmentServicePrice);
                            $cronJobLogsRepository->create([
                                'bdc_building_id' => $cronJob->building_id,
                                'signature' => 'create_debit_process:cron',
                                'input_data' => $apartmentServicePrice,
                                'output_data' => $e->getMessage(),
                                'status' => 500
                            ]);
                            $isSuccess = false;
                        }
                        else{
                            $isSuccess = true;
                        }
                        // throw new \Exception("register ERROR: ". $e->getMessage(), 1);
                    }
                    \DB::commit();
                }
                while($apartmentServicePrice != null);
                // Cập nhật trạng thái cron job
                if($isSuccess) {
                    $cronJobManager->update(['status' => 1], $cronJob->id);
                }
            }
        }
        catch(\Exception $e){
            $cronJobLogsRepository->create([
                'bdc_building_id' => 0,
                'signature' => 'create_debit_process:cron',
                'input_data' => 'Input Error',
                'output_data' => $e->getMessage(),
                'status' => 501
            ]);
        }
    }
}
