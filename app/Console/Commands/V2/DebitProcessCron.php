<?php

namespace App\Console\Commands\V2;

use App\Exceptions\QueueRedis;
use App\Models\BdcV2UserApartment\UserApartment;
use App\Models\BdcV2UserInfo\UserInfo;
use App\Repositories\Apartments\ApartmentsRespository;
use App\Repositories\BdcApartmentServicePrice\ApartmentServicePriceRepository;
use App\Repositories\BdcDebitLogs\DebitLogsRepository;
use App\Repositories\CronJobLogs\CronJobLogsRepository;
use App\Repositories\CronJobManager\CronJobManagerRepository;
use App\Repositories\Customers\CustomersRespository;
use App\Repositories\Service\ServiceRepository;
use App\Repositories\Vehicles\VehiclesRespository;
use Illuminate\Console\Command;
use Carbon\Carbon;
use App\Util\Debug\Log;

class DebitProcessCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'debitprocess_v2:cron';

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
        ApartmentServicePriceRepository $apartmentServicePriceRepository,
        CustomersRespository $customer,
        ServiceRepository $service,
        CronJobLogsRepository $cronJobLogsRepository,
        DebitLogsRepository $debitLogs,
        ApartmentsRespository $apartments,
        VehiclesRespository $vehiclesRespository
    ) {
//        sleep(3);
        Log::info('tandc2','debitprocess_v2:cron');
        Log::info('debitprocess_v2','start');
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

                if ($cronJob->group_apartment_id) {  // trường hợp tính theo nhóm căn hộ
                    $apartmentIds = $apartments->findByGroup($cronJob->building_id, $cronJob->group_apartment_id);
                    $apartmentServicePriceList = $apartmentServicePriceRepository->findBuildingIdByGroupApartment($cronJob->building_id, $serviceIds, $apartmentIds)->get();
                } else if ($cronJob->apartment_ids) {
                    $apartmentIds = json_decode($cronJob->apartment_ids);
                    $apartmentServicePriceList = $apartmentServicePriceRepository->findBuildingIdByGroupApartment($cronJob->building_id, $serviceIds, $apartmentIds)->get();
                } else //// tính tất cả các căn hộ
                {
                    $apartmentServicePriceList = $apartmentServicePriceRepository->findBuildingId($cronJob->building_id, $serviceIds)->get();
                }
                // thêm queue cho từng building id
                $arrApartmentSericePrice = array();
                $arrApartmentSericePriceList = $apartmentServicePriceList->toArray();
                echo "\nStart ApartmentServicePriceList...\n";
                $apartmentId = null;
                $log_input_data = null;
                $array_apartment_service = [];
                foreach ($apartmentServicePriceList as $keyApartmentServicePrice => $apartmentServicePrice) {
                    try {
                        $resultDataJson = json_encode($apartmentServicePrice);
                        $start_date = null;
                        $end_date = null;
                        $discount_check = null;
                        $discount_note = null;
                        $discount = null;
                        $cycleName = 0;
                        //Lấy thời gian chốt công nợ from_date
                        foreach ($data as $key => $value) {
                            if ($value['bdc_service_id'] == $apartmentServicePrice->bdc_service_id) {
                                $start_date = Carbon::parse($value['start']);
                                $end_date = Carbon::parse($value['end']);
                                $discount_check = $value['discount_check'];
                                $discount_note = $value['discount_note'];
                                $discount = $value['discount'];
                                $cycleName = $value['cycle_name'];
                            }
                        }
                        // Lấy thông tin căn hộ
                        $apartment = $apartmentServicePrice->apartment;
                        if ($apartment == null) {
                            $debitLogs->create([
                                'bdc_building_id' => $cronJob->building_id,
                                'bdc_apartment_id' => $apartmentServicePrice->bdc_apartment_id,
                                'bdc_service_id' => $apartmentServicePrice->bdc_service_id,
                                'key' => "debitprocess_v2:cron",
                                'cycle_name' => $cycleName,
                                'input' => $resultDataJson,
                                'data' => "",
                                'message' => "Mã căn hộ $apartmentServicePrice->bdc_apartment_id không tồn tại",
                                'status' => 101
                            ]);
                            continue;
                        }
                        // Lấy thông tin dịch vụ phương tiện đi lại
                        $vehicle = $apartmentServicePrice->vehicle;
                        $check_status_vehicle = null;
                        if ($vehicle != null) {
                            $check_status_vehicle = $apartmentServicePrice->vehicle()->where('status', 1)->first();
                            if (!$check_status_vehicle) {
                                $debitLogs->create([
                                    'bdc_building_id' => $cronJob->building_id,
                                    'bdc_apartment_id' => $apartmentServicePrice->bdc_apartment_id,
                                    'bdc_service_id' => $apartmentServicePrice->bdc_service_id,
                                    'key' => "debitprocess_v2:cron",
                                    'cycle_name' => $cycleName,
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
                                'key' => "debitprocess_v2:cron",
                                'cycle_name' => $cycleName,
                                'input' => $resultDataJson,
                                'data' => "",
                                'message' => "Căn hộ không tồn tại phương tiện này",
                                'status' => 101
                            ]);
                            continue;
                        }
                        // lấy chủ hộ của căn hộ
                        $_customer  = UserApartment::where(["apartment_id" => $apartmentServicePrice->bdc_apartment_id,"type" => 0])->first();
//                        $_customer = UserApartments::getPurchaser($apartmentServicePrice->bdc_apartment_id, 0);
                        if (!$_customer) {
                            echo "\nCan ho khong co chu ho\n";
                            $debitLogs->create([
                                'bdc_building_id' => $cronJob->building_id,
                                'bdc_apartment_id' => $apartmentServicePrice->bdc_apartment_id,
                                'bdc_service_id' => $apartmentServicePrice->bdc_service_id,
                                'key' => "debitprocess_v2:cron",
                                'cycle_name' => $cycleName,
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
                       

                        $_lastTimePay = Carbon::parse($apartmentServicePrice->last_time_pay);
                        $_finish_date = Carbon::parse($apartmentServicePrice->finish);

                        // Lấy ra ngày theo kỳ
                        //$current =  Carbon::parse($cycleName.'01');

                        // Mặc định là cho phép tạo công nợ tháng tới với isNextCycle = false

                        $isNextCycle = false;
                        echo  "TD = " . $start_date . "\n";
                        echo "FD = " . $end_date . "\n";

                        $customerInfo  = UserInfo::where(["id" => $_customer->user_info_id])->first();

                        if ($customerInfo == null) {
                            echo "\nCan ho khong co thong tin cu dan\n";
                            $debitLogs->create([
                                'bdc_building_id' => $cronJob->building_id,
                                'bdc_apartment_id' => $apartmentServicePrice->bdc_apartment_id,
                                'bdc_service_id' => $apartmentServicePrice->bdc_service_id,
                                'key' => "debitprocess_v2:cron",
                                'cycle_name' => $cycleName,
                                'input' => $resultDataJson,
                                'data' => "",
                                'message' => "Mã căn hộ $apartmentServicePrice->bdc_apartment_id không có thông tin cư dân",
                                'status' => 101
                            ]);
                            continue;
                        }
                        // begin _lấy thông tin xe inactive có ngày kết thúc > ngày start date
                        if ($apartmentId != $apartmentServicePrice->bdc_apartment_id) {
                            $list_apart_ser = $apartmentServicePriceRepository->getServiceApartmentInactive($apartmentId, $start_date);
                            if ($list_apart_ser) {
                                foreach ($list_apart_ser as $key => $value) {
                                    array_push($array_apartment_service, $value);
                                }
                            }
                        }
                        // end _lấy thông tin xe inactive có ngày kết thúc > ngày start date
                        $quantity = null;
                        $start_date_new = null; // lấy khoảng ngày tính đầu phí mới
                        $end_date_new = null;   // lấy khoảng ngày tính cuối phí mới

                        if ($start_date < $_lastTimePay) {
                            $start_date_new = $_lastTimePay;
                        }
                        // kiểm tra ngày tính phí tiếp theo nếu > ngày tính cuối thì không tính phí
                        if ($_lastTimePay >= $end_date) {
                            $debitLogs->create([
                                'bdc_building_id' => $cronJob->building_id,
                                'bdc_apartment_id' => $apartmentServicePrice->bdc_apartment_id,
                                'bdc_service_id' => $apartmentServicePrice->bdc_service_id,
                                'key' => "debitprocess_v2:cron",
                                'cycle_name' => $cycleName,
                                'input' => json_encode($apartmentServicePrice),
                                'data' => "",
                                'message' => "Last time pay" . $_lastTimePay->format('Y-m-d') . " >= ngày tính cuối" . $end_date->format('Y-m-d'),
                                'status' => 110
                            ]);
                            continue;
                        }
                        if ($apartmentServicePrice->finish) {
                            // kiểm tra ngày kết thúc nếu < ngày tính đầu thì không tính phí

                            if ($_finish_date <= $start_date) {
                                $debitLogs->create([
                                    'bdc_building_id' => $cronJob->building_id,
                                    'bdc_apartment_id' => $apartmentServicePrice->bdc_apartment_id,
                                    'bdc_service_id' => $apartmentServicePrice->bdc_service_id,
                                    'key' => "debitprocess_v2:cron",
                                    'cycle_name' => $cycleName,
                                    'input' => json_encode($apartmentServicePrice),
                                    'data' => "",
                                    'message' => "Ngày kết thúc" . $_finish_date->format('Y-m-d') . " > ngày tính đầu" . $start_date->format('Y-m-d'),
                                    'status' => 110
                                ]);
                                continue;
                            }


                            // kiểm tra ngày kết thúc nếu < ngày tính cuối và last time pay < ngày kết thúc thì không tính phí

                            if ($_finish_date < $end_date && $_lastTimePay > $_finish_date) {
                                $debitLogs->create([
                                    'bdc_building_id' => $cronJob->building_id,
                                    'bdc_apartment_id' => $apartmentServicePrice->bdc_apartment_id,
                                    'bdc_service_id' => $apartmentServicePrice->bdc_service_id,
                                    'key' => "debitprocess_v2:cron",
                                    'cycle_name' => $cycleName,
                                    'input' => json_encode($apartmentServicePrice),
                                    'data' => "",
                                    'message' => "Ngày kết thúc" . $_finish_date->format('Y-m-d') . " > ngày tính đầu" . $start_date->format('Y-m-d'),
                                    'status' => 110
                                ]);
                                continue;
                            } else if ($_finish_date > $end_date) {    // kiểm tra ngày kết thúc nếu > ngày tính cuối thì tính phí

                                if ($_finish_date > $end_date && $_lastTimePay < $start_date) {
                                    $start_date_new = $start_date;
                                    $end_date_new = $end_date;
                                } else {
                                    $start_date_new = $_lastTimePay;
                                    $end_date_new = $end_date;
                                }
                            } else if ($_finish_date < $end_date && $_lastTimePay < $_finish_date) {

                                if ($_lastTimePay < $start_date) {
                                    $start_date_new = $start_date;
                                    $end_date_new = $_finish_date;
                                } else {
                                    $start_date_new = $_lastTimePay;
                                    $end_date_new = $_finish_date;
                                }

                                // tự động inactive status phương tiện
                                if ($check_status_vehicle) {
                                    $vehiclesRespository->change_status($check_status_vehicle->id);
                                }
                            }
                        } else {
                            $start_date_new = $start_date;   // lấy khoảng ngày tính đầu phí mới
                            $end_date_new   = $end_date;     // lấy khoảng ngày tính cuối phí mới
                        }

                        if ($start_date < $_lastTimePay) {
                            $start_date_new = $_lastTimePay;
                        }
                        if (!$start_date_new && !$end_date_new) {
                            continue;
                        }

                        echo $start_date_new . '\n';
                        echo "--------------------\n";
                        echo 'end_date:' . $end_date_new;

                        $end_date_new = $end_date_new ?? $end_date;

                        $dateUsing = $end_date_new->diffInDays($start_date_new);
                        $temp_start_date = Carbon::parse("{$start_date_new->year}-{$start_date_new->month}-{$start_date_new->day}");
                        $nextMonth = $temp_start_date->addMonths(1);
                        $chuky = $nextMonth->diffInDays($start_date_new);

                        $log_input_data = 'ToDate = ' . $end_date_new->format('Y-m-d') . ' - FromDate = ' . $start_date_new->format('Y-m-d') . ' - LTATV = ' . $_lastTimePay . ' - Service = ' . $apartmentServicePrice->id . ' - Price = ' . $apartmentServicePrice->price . ' - ChuKy = ' . $chuky . ' - Ngaysudung = ' . $dateUsing . ' - Ngaybatdau = ' . $nextMonth->format('Y-m-d');

                        if (isset($_service->ngay_chuyen_doi) && ($chuky <= 31)) {  // dưới 31 ngày thì áp dụng ngày chuyển đổi
                            $ngay_chuyen_doi = null;
                            $chuyen_doi_from_date = Carbon::parse("{$start_date->year}-{$start_date->month}-{$_service->ngay_chuyen_doi}");
                            $chuyen_doi_to_date = Carbon::parse("{$end_date->year}-{$end_date->month}-{$_service->ngay_chuyen_doi}");

                            if (($chuyen_doi_from_date >= $start_date) && ($chuyen_doi_from_date <= $end_date)) {
                                $ngay_chuyen_doi = $chuyen_doi_from_date;
                            } else {
                                $ngay_chuyen_doi = $chuyen_doi_to_date;
                            }
                            if($_service->ngay_chuyen_doi > $chuky){
                                $sumeryPrice = ($apartmentServicePrice->price) / 2;
                                $quantity = 15;
                            }else{
                                if($start_date < $ngay_chuyen_doi && $ngay_chuyen_doi < $end_date){
                                    if ($ngay_chuyen_doi < $_lastTimePay) {
                                        $sumeryPrice = ($apartmentServicePrice->price) / 2;
                                        $quantity = 15;
                                    } else {                                                               // tính cả tháng
                                        $sumeryPrice = $apartmentServicePrice->price;
                                        $quantity = 1;
                                    }
                                }else {                                                               // tính cả tháng
                                    $sumeryPrice = $apartmentServicePrice->price;
                                    $quantity = 1;
                                }

                            }
                            if($apartmentServicePrice->finish){
                                if ($ngay_chuyen_doi > $_finish_date) {
                                    $sumeryPrice = ($apartmentServicePrice->price) / 2;
                                    $quantity = 15;
                                } else {                                                               // tính cả tháng
                                    $sumeryPrice = $apartmentServicePrice->price;
                                    $quantity = 1;
                                }
                            }
                        } else {
                            $sumeryPrice = ($apartmentServicePrice->price / $chuky) * $dateUsing;
                            $quantity = $dateUsing;
                        }
                        $discountPrice = 0;
                        // sau khi tính được giá thì check xem có Giảm trừ không

                        if ($discount_check == 'phan_tram') {
                            $discountPrice = (round($sumeryPrice) / 100) * $discount;
                        }

                        if ($discount_check == 'gia_tien') {
                            $discountPrice =  $discount;
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
                            'signature' => 'debitprocess_v2:cron',
                            'input_data' => 'ToDate = ' . $end_date->format('Y-m-d') . ' - FromDate = ' . $start_date->format('Y-m-d') . ' - LTATV = ' . $_lastTimePay . ' - Service = ' . $apartmentServicePrice->bdc_service_id . ' - Price = ' . $apartmentServicePrice->price . ' - ChuKy = ' . $chuky . ' - Ngaysudung = ' . $dateUsing . ' - TongTien = ' . $sumeryPrice,
                            'output_data' => '',
                            'status' => 501
                        ]);

                        $apartmentServicePrice->customer_name = $customerInfo->full_name;
                        $apartmentServicePrice->customer_address = $customerInfo->address;
                        $apartmentServicePrice->provider_address = 'test';
                        $apartmentServicePrice->deadline = $cronJob->deadline;
                        $apartmentServicePrice->from_date = $start_date_new->format('Y-m-d');
                        $apartmentServicePrice->to_date = $end_date_new->format('Y-m-d');
                        $apartmentServicePrice->free = 0;
                        $apartmentServicePrice->dateUsing = $dateUsing;
                        $apartmentServicePrice->price_current = $apartmentServicePrice->price;
                        $apartmentServicePrice->price = round($sumeryPrice);
                        $apartmentServicePrice->discountPrice = round($discountPrice);
                        $apartmentServicePrice->service_name = $_service->name;
                        $apartmentServicePrice->apartment_name = $apartment->name;
                        $apartmentServicePrice->quantity = $quantity;
                        $apartmentServicePrice->one_price = $onePrice;
                        $apartmentServicePrice->bdc_price_type_id = 1;
                        $apartmentServicePrice->isNextCycle = $isNextCycle;
                        $apartmentServicePrice->discount_check = $discount_check;
                        $apartmentServicePrice->discount = $discount;
                        $apartmentServicePrice->discount_note = $discount_note;
                        $apartmentServicePrice->code_receipt = $_service->code_receipt;

                        $apartmentServicePrice->cycle_name = $cycleName;

                        if (empty($arrApartmentSericePrice)) {
                            array_push($arrApartmentSericePrice, $apartmentServicePrice->toArray());
                            if (count($serviceIds) == 1) {
                                echo 'end_1:';
                                QueueRedis::setItemForQueue("add_queue_apartment_service_price_z_v2_{$apartmentServicePrice->bdc_building_id}", $arrApartmentSericePrice);
                                $arrApartmentSericePrice = array();
                            }
                        } else {
                            $isExistsKey = array_search($apartmentServicePrice->bdc_apartment_id, array_column($arrApartmentSericePrice, 'bdc_apartment_id'), false);
                            if ($isExistsKey === false) {
                                echo "queue 1 : $apartmentServicePrice->bdc_apartment_id \n";
                                echo 'end_2:';
                                QueueRedis::setItemForQueue("add_queue_apartment_service_price_z_v2_{$apartmentServicePrice->bdc_building_id}", $arrApartmentSericePrice);
                                $arrApartmentSericePrice = array();
                                array_push($arrApartmentSericePrice, $apartmentServicePrice->toArray());
                            } else {
                                echo "queue 2 : $apartmentServicePrice->bdc_apartment_id \n";
                                array_push($arrApartmentSericePrice, $apartmentServicePrice->toArray());
                                $lastKey = array_key_last($arrApartmentSericePriceList);
                                if ($keyApartmentServicePrice == $lastKey) {
                                    echo 'end_3:';
                                    QueueRedis::setItemForQueue("add_queue_apartment_service_price_z_v2_{$apartmentServicePrice->bdc_building_id}", $arrApartmentSericePrice);
                                    $arrApartmentSericePrice = array();
                                }
                            }
                        }

                        $rsDataJson = $arrApartmentSericePrice ? json_encode($arrApartmentSericePrice) : null;
                        $debitLogs->create([
                            'bdc_building_id' => $cronJob->building_id,
                            'bdc_apartment_id' => $apartmentServicePrice->bdc_apartment_id,
                            'bdc_service_id' => $apartmentServicePrice->bdc_service_id,
                            'cycle_name' => $cycleName,
                            'key' => "debitprocess_v2:cron",
                            'input' => $resultDataJson,
                            'data' => $rsDataJson,
                            'message' => "Khởi tạo công nợ thành công.",
                            'status' => 200
                        ]);
                    } catch (\Exception $e) {
                        QueueRedis::forgetFlagCronjob();
                        $debitLogs->create([
                            'bdc_building_id' => $cronJob->building_id,
                            'bdc_apartment_id' => $apartmentServicePrice->bdc_apartment_id,
                            'bdc_service_id' => $apartmentServicePrice->bdc_service_id,
                            'key' => "debitprocess_v2:cron",
                            'input' => $resultDataJson,
                            'data' => $e->getMessage(),
                            'message' => "Lỗi hệ thống." . $log_input_data,
                            'status' => 501
                        ]);
                    }
                }
                if ($arrApartmentSericePrice) {
                    echo 'end_4:' . json_encode($arrApartmentSericePrice);
                    QueueRedis::setItemForQueue("add_queue_apartment_service_price_z_v2_{$cronJob->building_id}", $arrApartmentSericePrice);
                    $arrApartmentSericePrice = array();
                }
                // đóng cron job hiện tại
                $cronJobManager->update(['status' => 1], $cronJob->id);
                // tạo cron job create_debit_process:cron
                $cronJobManager->create([
                    'building_id' => $cronJob->building_id,
                    'user_id' => $cronJob->user_id,
                    'signature' => 'create_debit_process_v3:cron',
                    'status' => 0
                ]);
            }
            QueueRedis::forgetFlagCronjob();
        }
    }
}
