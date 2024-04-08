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
use App\Util\Debug\Log;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;
use Webpatser\Uuid\Uuid;
use Carbon\Carbon;

class DebitProcessCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'debitprocess:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Xử lý công nợ';

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
        ApartmentServicePriceRepository $apartmentServicePrice,
        CustomersRespository $customer,
        ServiceRepository $service,
        CronJobLogsRepository $cronJobLogsRepository,
        DebitLogsRepository $debitLogs
    ) {
        $cronJobs = $cronJobManager->findSignature($this->signature)->get();

        echo "\nStart...\n";
        $flag_cronjob = QueueRedis::getFlagCronjob();
        if (!$flag_cronjob) {
            QueueRedis::setFlagCronjob(1);
            foreach ($cronJobs as $cronJob) {
                $data = $cronJob->data;
                $serviceIds = [];
                foreach ($data as $key => $value) {
                    array_push($serviceIds, $value["bdc_service_id"]);
                }
                $apartmentServicePriceList = $apartmentServicePrice->findBuildingId($cronJob->building_id, $serviceIds)->get();
                // thêm queue cho từng building id
                $arrApartmentSericePrice = array();
                $arrApartmentSericePriceList = $apartmentServicePriceList->toArray();
                echo "\nStart ApartmentServicePriceList...\n";
                $log_input_data = null;
                foreach ($apartmentServicePriceList as $keyApartmentServicePrice => $apartmentServicePrice) {
                    try {
                        $resultDataJson = json_encode($apartmentServicePrice);
                        // Lấy thông tin căn hộ
                        $apartment = $apartmentServicePrice->apartment;
                        if ($apartment == null) {
                            $debitLogs->create([
                                'bdc_building_id' => $cronJob->building_id,
                                'bdc_apartment_id' => $apartmentServicePrice->bdc_apartment_id,
                                'bdc_service_id' => $apartmentServicePrice->bdc_service_id,
                                'key' => "debitprocess:cron",
                                'input' => $resultDataJson,
                                'data' => "",
                                'message' => "Mã căn hộ $apartmentServicePrice->bdc_apartment_id không tồn tại",
                                'status' => 101
                            ]);
                            continue;
                        }
                        // Lấy thông tin dịch vụ phương tiện đi lại
                        $vehicle = $apartmentServicePrice->vehicle;
                        if ($vehicle != null) {
                            $check_status_vehicle = $apartmentServicePrice->vehicle()->where(['bdc_apartment_id' => $apartmentServicePrice->bdc_apartment_id, 'status' => 1])->first();
                            if (!$check_status_vehicle) {
                                $debitLogs->create([
                                    'bdc_building_id' => $cronJob->building_id,
                                    'bdc_apartment_id' => $apartmentServicePrice->bdc_apartment_id,
                                    'bdc_service_id' => $apartmentServicePrice->bdc_service_id,
                                    'key' => "debitprocess:cron",
                                    'input' => $resultDataJson,
                                    'data' => "",
                                    'message' => "Trạng thái phương tiện chưa được kích hoạt",
                                    'status' => 101
                                ]);
                                continue;
                            }
                            $apartmentServicePrice->name = $vehicle->number;
                        }
                        if ($apartmentServicePrice->bdc_vehicle_id > 0 && !$vehicle) {
                            $debitLogs->create([
                                'bdc_building_id' => $cronJob->building_id,
                                'bdc_apartment_id' => $apartmentServicePrice->bdc_apartment_id,
                                'bdc_service_id' => $apartmentServicePrice->bdc_service_id,
                                'key' => "debitprocess:cron",
                                'input' => $resultDataJson,
                                'data' => "",
                                'message' => "Căn hộ không tồn tại phương tiện này",
                                'status' => 101
                            ]);
                            continue;
                        }
                        // lấy chủ hộ của căn hộ
                        // $_customer = UserApartments::getPurchaser($apartmentServicePrice->bdc_apartment_id, 0);
                        $_customer = UserApartments::getPurchaser($apartmentServicePrice->bdc_apartment_id, 0);
                        if (!$_customer) {
                            echo "\nCan ho khong co chu ho\n";
                            $debitLogs->create([
                                'bdc_building_id' => $cronJob->building_id,
                                'bdc_apartment_id' => $apartmentServicePrice->bdc_apartment_id,
                                'bdc_service_id' => $apartmentServicePrice->bdc_service_id,
                                'key' => "debitprocess:cron",
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
                        foreach ($data as $key => $value) {
                            if ($value['bdc_service_id'] == $apartmentServicePrice->bdc_service_id) {
                                $free = $value['free'];
                                $processAgain = $value['process_again'];
                                $firstTimeActive = $value['fist_time_active'];
                                $cycleName = $value['cycle_name'];
                            }
                        }

                        $_firstTimeActive = Carbon::parse($firstTimeActive);
                        $_lastTimePay = Carbon::parse($apartmentServicePrice->last_time_pay);

                        if ($_firstTimeActive < $_lastTimePay) {
                            echo "1";
                            $fromDate = $apartmentServicePrice->last_time_pay;
                        } else if ($_firstTimeActive > $_lastTimePay) {
                            echo "2";
                            $fromDate = $firstTimeActive;
                            $cronJobLogsRepository->create([
                                'bdc_building_id' => $cronJob->building_id,
                                'signature' => 'debitprocess:cron',
                                'input_data' => "LastTimePay : {$apartmentServicePrice->last_time_pay} < FromDate : {$firstTimeActive}",
                                'output_data' => 'LastTimePay < FromDate',
                                'status' => 301
                            ]);
                        } else {
                            echo "3";
                            $fromDate = $apartmentServicePrice->last_time_pay;
                        }

                        // Lấy ra to_date
                        //$current =   Carbon::now();
                        $current = ServiceRepository::getTinhCongNo($cronJob->building_id) == 'custom_month' ?  Carbon::parse($cycleName . '01') : Carbon::now();

                        $toDate = "{$current->year}-{$current->month}-{$_service->bill_date}";
                        // Mặc định là cho phép tạo công nợ tháng tới với isNextCycle = false
                        $isNextCycle = false;
                        echo  "TD = " . $toDate . "\n";
                        echo "FD = " . $fromDate . "\n";

                        $checkDuplicateBillCycleName = DebitDetailRepository::findServiceCheckFromDate($apartmentServicePrice->bdc_service_id, $apartmentServicePrice->bdc_apartment_id, $apartmentServicePrice->id, $fromDate);

                        if (Carbon::parse($toDate)->diffInDays(Carbon::parse($fromDate)) > 31 || $checkDuplicateBillCycleName) {
                            $debitLogs->create([
                                'bdc_building_id' => $cronJob->building_id,
                                'bdc_apartment_id' => $apartmentServicePrice->bdc_apartment_id,
                                'bdc_service_id' => $apartmentServicePrice->bdc_service_id,
                                'key' => "debitprocess:cron",
                                'input' => json_encode($apartmentServicePrice),
                                'data' => "",
                                'message' => "Thời gian tính" . $fromDate . '->' . $toDate,
                                'status' => 110
                            ]);
                            continue;
                        }
                        $fromDate = Carbon::parse($fromDate);
                        $toDate   = Carbon::parse($toDate);
                        if ($toDate <= $fromDate) {
                            $nextMonth = $current->addMonths(1);
                            $toDate = Carbon::parse("{$nextMonth->year}-{$nextMonth->month}-{$_service->bill_date}");
                            // Không sinh công nợ tháng tới set isNextCycle = true
                            if ($processAgain == DebitDetailRepository::PROCESS_AGAIN) {
                                $isNextCycle = true;
                            }
                        }

                        // echo "\nFT = " . $_firstTimeActive . "\n";
                        // echo "LT = " . $_lastTimePay . "\n";
                        // echo  "TD = " . $toDate . "\n";
                        // echo "FD = " . $fromDate . "\n";

                        // Log::info("tu_debitprocess_v1", "check_: " .json_encode($apartmentServicePrice));
                        // Log::info("tu_debitprocess_v1", "tinh tu_: " .json_encode($fromDate) .'den :'.json_encode($toDate));
                        // if(!$fromDate && !$toDate){
                        //    continue;
                        // }
                        $lastMonth = $current->subMonths(1);
                        $fromLastMoth = Carbon::parse("{$lastMonth->year}-{$lastMonth->month}-{$_service->bill_date}");
                        $dateUsing = $toDate->diffInDays($fromDate);
                        $chuky = $toDate->diffInDays($fromLastMoth);
                        $customerInfo = $_customer->user_info_first;

                        $log_input_data = 'ToDate = ' . $toDate->format('Y-m-d') . ' - FromDate = ' . $fromDate->format('Y-m-d') . ' - LTATV = ' . $_lastTimePay . '- FTATV = ' . $_firstTimeActive . ' - Service = ' . $apartmentServicePrice->bdc_service_id . ' - Price = ' . $apartmentServicePrice->price . ' - ChuKy = ' . $chuky . ' - Ngaysudung = ' . $dateUsing . ' - Ngaybatdau = ' . $fromLastMoth->format('Y-m-d');

                        if ($customerInfo == null) {
                            echo "\nCan ho khong co thong tin cu dan\n";
                            $debitLogs->create([
                                'bdc_building_id' => $cronJob->building_id,
                                'bdc_apartment_id' => $apartmentServicePrice->bdc_apartment_id,
                                'bdc_service_id' => $apartmentServicePrice->bdc_service_id,
                                'key' => "debitprocess:cron",
                                'input' => $resultDataJson,
                                'data' => "",
                                'message' => "Mã căn hộ $apartmentServicePrice->bdc_apartment_id không có thông tin cư dân",
                                'status' => 101
                            ]);
                            continue;
                        }
                        $quantity = null;
                        if (isset($_service->ngay_chuyen_doi)) {
                            $ngay_chuyen_doi = null;
                            $chuyen_doi_from_date = Carbon::parse("{$fromLastMoth->year}-{$fromLastMoth->month}-{$_service->ngay_chuyen_doi}");
                            $chuyen_doi_to_date = Carbon::parse("{$toDate->year}-{$toDate->month}-{$_service->ngay_chuyen_doi}");
                            if (($chuyen_doi_from_date >= $fromLastMoth) && ($chuyen_doi_from_date <= $toDate)) {
                                $ngay_chuyen_doi = $chuyen_doi_from_date;
                            } else {
                                $ngay_chuyen_doi = $chuyen_doi_to_date;
                            }
                            if ($ngay_chuyen_doi < $_lastTimePay) {                               // tính nửa tháng
                                $sumeryPrice = ($apartmentServicePrice->price) / 2;
                                $quantity = '1/2';
                            } else {                                                               // tính cả tháng
                                $sumeryPrice = $apartmentServicePrice->price;
                                $quantity = '1';
                            }
                        } else {
                            $sumeryPrice = ($apartmentServicePrice->price / $chuky) * $dateUsing;
                            $quantity = $dateUsing;
                        }


                        $onePrice = $apartmentServicePrice->price / $chuky;

                        echo "--------------------\n";
                        echo $apartmentServicePrice->bdc_service_id . "\n";
                        echo $apartmentServicePrice->price . "\n";
                        echo $chuky . "\n";
                        echo $dateUsing . 'ngay su dung' . "\n";
                        echo $sumeryPrice . "\n";
                        echo $sumeryPrice . "\n";
                        echo "--------------------\n";

                        $cronJobLogsRepository->create([
                            'bdc_building_id' => $cronJob->building_id,
                            'signature' => 'debitprocess:cron',
                            'input_data' => 'ToDate = ' . $toDate->format('Y-m-d') . ' - FromDate = ' . $fromDate->format('Y-m-d') . ' - LTATV = ' . $_lastTimePay . '- FTATV = ' . $_firstTimeActive . ' - Service = ' . $apartmentServicePrice->bdc_service_id . ' - Price = ' . $apartmentServicePrice->price . ' - ChuKy = ' . $chuky . ' - Ngaysudung = ' . $dateUsing . ' - TongTien = ' . $sumeryPrice,
                            'output_data' => '',
                            'status' => 501
                        ]);

                        $apartmentServicePrice->customer_name = $customerInfo->full_name;
                        $apartmentServicePrice->customer_address = $customerInfo->address;
                        $apartmentServicePrice->provider_address = 'test';
                        $apartmentServicePrice->deadline = $cronJob->deadline;
                        $apartmentServicePrice->from_date = $fromDate->format('Y-m-d');
                        $apartmentServicePrice->to_date = $toDate->format('Y-m-d');
                        $apartmentServicePrice->free = $free;
                        $apartmentServicePrice->dateUsing = $dateUsing;
                        $apartmentServicePrice->price_current = $apartmentServicePrice->price;
                        $apartmentServicePrice->price = $sumeryPrice;
                        $apartmentServicePrice->service_name = $_service->name;
                        $apartmentServicePrice->apartment_name = $apartment->name;
                        $apartmentServicePrice->quantity = $quantity;
                        $apartmentServicePrice->one_price = $onePrice;
                        $apartmentServicePrice->bdc_price_type_id = 1;
                        $apartmentServicePrice->isNextCycle = $isNextCycle;

                        $apartmentServicePrice->cycle_name = $cycleName;

                        if (empty($arrApartmentSericePrice)) {
                            array_push($arrApartmentSericePrice, $apartmentServicePrice->toArray());
                            if (count($serviceIds) == 1) {
                                QueueRedis::setItemForQueue("add_queue_apartment_service_price_z{$apartmentServicePrice->bdc_building_id}", $arrApartmentSericePrice);
                                $arrApartmentSericePrice = array();
                            }
                        } else {
                            $isExistsKey = array_search($apartmentServicePrice->bdc_apartment_id, array_column($arrApartmentSericePrice, 'bdc_apartment_id'), false);
                            if ($isExistsKey === false) {
                                echo "queue 1 : $apartmentServicePrice->bdc_apartment_id \n";
                                QueueRedis::setItemForQueue("add_queue_apartment_service_price_z{$apartmentServicePrice->bdc_building_id}", $arrApartmentSericePrice);
                                $arrApartmentSericePrice = array();
                                array_push($arrApartmentSericePrice, $apartmentServicePrice->toArray());
                            } else {
                                echo "queue 2 : $apartmentServicePrice->bdc_apartment_id \n";
                                array_push($arrApartmentSericePrice, $apartmentServicePrice->toArray());
                                $lastKey = array_key_last($arrApartmentSericePriceList);
                                if ($keyApartmentServicePrice == $lastKey) {
                                    QueueRedis::setItemForQueue("add_queue_apartment_service_price_z{$apartmentServicePrice->bdc_building_id}", $arrApartmentSericePrice);
                                    $arrApartmentSericePrice = array();
                                }
                            }
                        }

                        $rsDataJson = $arrApartmentSericePrice ? json_encode($arrApartmentSericePrice) : null;
                        $debitLogs->create([
                            'bdc_building_id' => $cronJob->building_id,
                            'bdc_apartment_id' => $apartmentServicePrice->bdc_apartment_id,
                            'bdc_service_id' => $apartmentServicePrice->bdc_service_id,
                            'key' => "debitprocess:cron",
                            'input' => $resultDataJson,
                            'data' => $rsDataJson,
                            'message' => "Khởi tạo công nợ thành công.",
                            'status' => 200
                        ]);
                    } catch (\Exception $e) {
                        $debitLogs->create([
                            'bdc_building_id' => $cronJob->building_id,
                            'bdc_apartment_id' => $apartmentServicePrice->bdc_apartment_id,
                            'bdc_service_id' => $apartmentServicePrice->bdc_service_id,
                            'key' => "debitprocess:cron",
                            'input' => $resultDataJson,
                            'data' => $e->getMessage(),
                            'message' => "Lỗi hệ thống." . $log_input_data,
                            'status' => 501
                        ]);
                    }
                }
                if ($arrApartmentSericePrice) {
                    QueueRedis::setItemForQueue("add_queue_apartment_service_price_z{$cronJob->building_id}", $arrApartmentSericePrice);
                    $arrApartmentSericePrice = array();
                }
                // đóng cron job hiện tại
                $cronJobManager->update(['status' => 1], $cronJob->id);
                // tạo cron job create_debit_process:cron
                $cronJobManager->create([
                    'building_id' => $cronJob->building_id,
                    'user_id' => $cronJob->user_id,
                    'signature' => 'create_debit_process_v2:cron',
                    'status' => 0
                ]);
            }
            QueueRedis::forgetFlagCronjob();
        }
    }
}
