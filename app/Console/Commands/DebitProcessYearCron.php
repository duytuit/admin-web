<?php

namespace App\Console\Commands;

use App\Exceptions\QueueRedis;
use App\Models\Apartments\V2\UserApartments;
use App\Repositories\BdcApartmentServicePrice\ApartmentServicePriceRepository;
use App\Repositories\BdcDebitDetail\DebitDetailRepository;
use App\Repositories\BdcDebitLogs\DebitLogsRepository;
use App\Repositories\CronJobLogs\CronJobLogsRepository;
use App\Repositories\CronJobManager\CronJobManagerRepository;
use App\Repositories\Customers\CustomersRespository;
use App\Repositories\Service\ServiceRepository;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;
use Webpatser\Uuid\Uuid;
use Carbon\Carbon;
use App\Repositories\BdcBills\BillRepository;
use App\Repositories\Config\ConfigRepository;

class DebitProcessYearCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'debitprocessyear:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Xử lý công nợ năm';

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
    public function handle(CronJobManagerRepository $cronJobManager, 
        ApartmentServicePriceRepository $apartmentServicePrice, 
        CustomersRespository $customer, 
        ServiceRepository $service,
        CronJobLogsRepository $cronJobLogsRepository,
        DebitLogsRepository $debitLogs,
        DebitDetailRepository $debitDetail, 
        BillRepository $bill,
        ConfigRepository $config)
    {
        $cronJobs = $cronJobManager->findSignature($this->signature)->get();
        try
        {
            echo "\nStart...\n";
            $arrApartmentSericePrice = array();
            foreach($cronJobs as $cronJob) {
                $data = $cronJob->data;
                $serviceIds = [];
                foreach($data as $key => $value) {
                    array_push($serviceIds, $value["bdc_service_id"]);
                }
                $apartmentServicePriceList = $apartmentServicePrice->findBuildingId($cronJob->building_id, $serviceIds)->get();
                // thêm queue cho từng building id
               
                echo "\nStart ApartmentServicePriceList...\n";
                foreach ($apartmentServicePriceList as $keyApartmentServicePrice => $apartmentServicePrice)
                {
                    $resultDataJson = json_encode($apartmentServicePrice);
                    // Lấy thông tin căn hộ
                    $apartment = $apartmentServicePrice->apartment;
                    if($apartment == null)
                    {
                        $debitLogs->create([
                            'bdc_building_id' => $cronJob->building_id,
                            'bdc_apartment_id' => $apartmentServicePrice->bdc_apartment_id,
                            'bdc_service_id' => $apartmentServicePrice->bdc_service_id,
                            'key' => "debitprocessyear:cron",
                            'input' => $resultDataJson,
                            'data' => "",
                            'message' => "Mã căn hộ $apartmentServicePrice->bdc_apartment_id không tồn tại",
                            'status' => 101
                        ]);
                        continue;
                    }
                    // Lấy thông tin dịch vụ phương tiện đi lại
                    $vehicle = $apartmentServicePrice->vehicle;
                    if($vehicle != null) {
                        $apartmentServicePrice->name = $vehicle->number;
                    }
                    // lấy chủ hộ của căn hộ
                    $_customer = UserApartments::getPurchaser($apartmentServicePrice->bdc_apartment_id, 0);
                    if(!$_customer) {
                        echo "\nCan ho khong co chu ho\n";
                        $debitLogs->create([
                            'bdc_building_id' => $cronJob->building_id,
                            'bdc_apartment_id' => $apartmentServicePrice->bdc_apartment_id,
                            'bdc_service_id' => $apartmentServicePrice->bdc_service_id,
                            'key' => "debitprocessyear:cron",
                            'input' => $resultDataJson,
                            'data' => "",
                            'message' => "Mã căn hộ $apartmentServicePrice->bdc_apartment_id không có chủ hộ",
                            'status' => 101
                        ]);
                        continue;
                    }
                    // Lấy ra chi tiết dịch vụ
                    $_service = $service->findService($apartmentServicePrice->bdc_service_id);
                    echo "\nStart service...\n";
                    $free = 0;
                    $processAgain = 0;
                    $firstTimeActive = '';
                    $cycleName = 0;
                    //Lấy thời gian chốt công nợ from_date
                    foreach($data as $key => $value) {
                        if($value['bdc_service_id'] == $apartmentServicePrice->bdc_service_id) {
                            $free = $value['free'];
                            $processAgain = $value['process_again'];
                            $firstTimeActive = $apartmentServicePrice->first_time_active;
                            $cycleName = $value['cycle_name'];
                        }
                    }
                    // check xem ngày hiện tại có nằm trong khoảng để tính công nợ năm không

                    $startDate = date('Y-m-d', strtotime($firstTimeActive));
                    $EndDate =  date('Y-m-d', strtotime($apartmentServicePrice->last_time_pay));
                    // today! 
                    $current = Carbon::now()->format('Y-m-d');
                        
                    if (($current >= $startDate) && ($current <= $EndDate)){
                        echo "is between";
                    }else{
                        echo "no is between";
                        continue;  
                    }

                    $_firstTimeActive = Carbon::parse($firstTimeActive);
                    $_lastTimePay = Carbon::parse($apartmentServicePrice->last_time_pay);

                    // $endTime = $xx->diff($xxx)->days;
                    if($_firstTimeActive < $_lastTimePay) {
                        echo "1";
                        $fromDate = $apartmentServicePrice->last_time_pay;
                    }
                    else if($_firstTimeActive > $_lastTimePay) {
                        echo "2";
                        $fromDate = $firstTimeActive;
                        $cronJobLogsRepository->create([
                            'bdc_building_id' => $cronJob->building_id,
                            'signature' => 'debitprocessyear:cron',
                            'input_data' => "LastTimePay : {$apartmentServicePrice->last_time_pay} < FromDate : {$firstTimeActive}",
                            'output_data' => 'LastTimePay < FromDate',
                            'status' => 301
                        ]);
                    }
                    else{
                        echo "3";
                        $fromDate = $apartmentServicePrice->last_time_pay;
                    }
                    

                    $dateUsing = $_lastTimePay->diffInDays($_firstTimeActive);

                    $customerInfo = $_customer->user_info_first;
                    
                    if($customerInfo == null)
                    {
                        echo "\nCan ho khong co thong tin cu dan\n";
                        $debitLogs->create([
                            'bdc_building_id' => $cronJob->building_id,
                            'bdc_apartment_id' => $apartmentServicePrice->bdc_apartment_id,
                            'bdc_service_id' => $apartmentServicePrice->bdc_service_id,
                            'key' => "debitprocessyear:cron",
                            'input' => $resultDataJson,
                            'data' => "",
                            'message' => "Mã căn hộ $apartmentServicePrice->bdc_apartment_id không có thông tin cư dân",
                            'status' => 101
                        ]);
                        continue;
                    }

                    $sumeryPrice = ($apartmentServicePrice->price / 365) * $dateUsing;

                    $quantity = $dateUsing;
                    $onePrice = $apartmentServicePrice->price / 365;

                    echo "--------------------\n";
                    echo $apartmentServicePrice->bdc_service_id. "\n";
                    echo $apartmentServicePrice->price . "\n";
                    echo $dateUsing . "\n";
                    echo $sumeryPrice . "\n";
                    echo (int)$sumeryPrice . "\n";
                    echo "--------------------\n";
                    
                    $cronJobLogsRepository->create([
                        'bdc_building_id' => $cronJob->building_id,
                        'signature' => 'debitprocessyear:cron',
                        'input_data' => 'ToDate = ' . $apartmentServicePrice->last_time_pay . ' - FromDate = ' . $fromDate . ' - LTATV = ' . $_lastTimePay . '- FTATV = ' . $_firstTimeActive . ' - Service = ' . $apartmentServicePrice->bdc_service_id . ' - Price = ' . $apartmentServicePrice->price . ' - Ngaysudung = ' . $dateUsing . ' - TongTien = ' . $sumeryPrice,
                        'output_data' => '',
                        'status' => 501
                    ]);

                    $apartmentServicePrice->customer_name = $customerInfo->display_name ;
                    $apartmentServicePrice->customer_address = $customerInfo->address;
                    $apartmentServicePrice->provider_address = 'test';
                    $apartmentServicePrice->deadline = $cronJob->deadline;
                    $apartmentServicePrice->from_date = $firstTimeActive;
                    $apartmentServicePrice->to_date = $apartmentServicePrice->last_time_pay;
                    $apartmentServicePrice->free = $free;
                    $apartmentServicePrice->dateUsing = $dateUsing;
                    $apartmentServicePrice->price = $sumeryPrice;
                    $apartmentServicePrice->service_name = $_service->name;
                    $apartmentServicePrice->apartment_name = $apartment->name;
                    $apartmentServicePrice->quantity = $quantity;
                    $apartmentServicePrice->one_price = $onePrice;
                    $apartmentServicePrice->bdc_price_type_id = 1;
                    $apartmentServicePrice->cycle_name = $cycleName;
                    $apartmentServicePrice->user_id = $cronJob->user_id;

                    array_push($arrApartmentSericePrice, $apartmentServicePrice->toArray());
                   

                    $rsDataJson = json_encode($arrApartmentSericePrice);
                    $debitLogs->create([
                        'bdc_building_id' => $cronJob->building_id,
                        'bdc_apartment_id' => $apartmentServicePrice->bdc_apartment_id,
                        'bdc_service_id' => $apartmentServicePrice->bdc_service_id,
                        'key' => "debitprocessyear:cron",
                        'input' => $resultDataJson,
                        'data' => $rsDataJson,
                        'message' => "Khởi tạo công nợ thành công.",
                        'status' => 200
                    ]);
                }
                
                // đóng cron job hiện tại
                $cronJobManager->update(['status' => 1], $cronJob->id);
            }
            if(count($arrApartmentSericePrice) > 0){
                \DB::beginTransaction();
                foreach ($arrApartmentSericePrice as $key => $value) {
                    try {
                        $value = (object)$value;
                        $billResult = $bill->create([
                            'bdc_apartment_id' => $value->bdc_apartment_id,
                            'bdc_building_id' => $value->bdc_building_id,
                            'bill_code' => $bill->autoIncrementBillCode($config, $cronJob->building_id),
                            'cost' => $value->price,
                            'cost_free' => $value->free,
                            'customer_name' => $value->customer_name,
                            'customer_address' => $value->customer_address == null ? "" : $value->customer_address,
                            'deadline' => $value->deadline,
                            'provider_address' => 'Banking',
                            'is_vat' => 0,
                            'status' => $bill::WAIT_FOR_CONFIRM,
                            'notify' => 0,
                            'cycle_name' => $value->cycle_name,
                            'user_id' => $value->user_id,
                        ]);
                        // Tạo công nợ
                        $debitDetailMaxVersion = $debitDetail->findByBuildingApartmentServiceId(
                            $value->bdc_building_id, 
                            $value->bdc_apartment_id, 
                            $value->bdc_service_id);
                        $previousOwed = 0;
                        if ($debitDetailMaxVersion) {
                            $previousOwed = $debitDetailMaxVersion->previous_owed;
                        }
                        $debitDetail->create([
                            'bdc_building_id' => $value->bdc_building_id,
                            'bdc_bill_id' => $billResult->id,
                            'bdc_apartment_id' => $value->bdc_apartment_id,
                            'bdc_service_id' => $value->bdc_service_id,
                            'bdc_apartment_service_price_id' => $value->id,
                            'title' => $value->name,
                            'from_date' => $value->from_date,
                            'to_date' => $value->to_date,
                            'detail' => '[]',
                            'version' => 0,
                            'sumery' => $value->price,
                            'new_sumery' => $value->price,
                            'previous_owed' => $previousOwed,
                            'paid' => 0,
                            'is_free' => $value->free,
                            'cycle_name' => $value->cycle_name,
                            'price' => $value->one_price,
                            'quantity' => $value->quantity,
                            'bdc_price_type_id' => $value->bdc_price_type_id
                        ]);
                        // Cập nhật last_time_pay
                        $last_time_pay_new = Carbon::parse($value->to_date)->addYear();
                        $apartmentServicePrice->find($value->id)->update(['first_time_active'=>$value->to_date, 'last_time_pay' => $last_time_pay_new->format('Y-m-d')]);
                        \DB::commit();
                    } catch(\Exception $e) {
                        \DB::rollBack();
                        $debitLogs->create([
                            'bdc_building_id' => $cronJob->building_id,
                            'bdc_apartment_id' => $value->bdc_apartment_id,
                            'bdc_service_id' => $value->bdc_service_id,
                            'key' => "debitprocessyear:cron",
                            'input' => json_encode($value),
                            'data' => "",
                            'message' => $e->getMessage(),
                            'status' => 110
                        ]);
                        continue;
                    }
                }
            }

        }
        catch(\Exception $e)
        {
            $debitLogs->create([
                'bdc_building_id' => $cronJob->building_id,
                'bdc_apartment_id' => $apartmentServicePrice->bdc_apartment_id,
                'bdc_service_id' => $apartmentServicePrice->bdc_service_id,
                'key' => "debitprocessyear:cron",
                'input' => $resultDataJson,
                'data' => $e->getMessage(),
                'message' => "Lỗi hệ thống.",
                'status' => 501
            ]);
        } 
    }
}
