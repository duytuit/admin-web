<?php

namespace App\Console\Commands\V2;

use App\Commons\Helper;
use App\Exceptions\QueueRedis;
use App\Helpers\dBug;
use App\Models\Apartments\Apartments;
use App\Models\Apartments\V2\UserApartments;
use App\Models\BdcApartmentServicePrice\ApartmentServicePrice;
use App\Models\BdcBills\Bills;
use App\Models\BdcElectricMeter\ElectricMeter;
use App\Models\BdcProgressivePrice\ProgressivePrice;
use App\Models\BdcProgressives\Progressives;
use App\Models\Service\Service;
use App\Repositories\BdcApartmentServicePrice\ApartmentServicePriceRepository;
use App\Repositories\BdcBills\BillRepository;
use App\Repositories\BdcDebitLogs\DebitLogsRepository;
use App\Repositories\BdcV2DebitDetail\DebitDetailRepository;
use App\Repositories\BdcV2PaymentDetail\PaymentDetailRepository;
use App\Repositories\Config\ConfigRepository;
use App\Repositories\CronJobLogs\CronJobLogsRepository;
use App\Repositories\CronJobManager\CronJobManagerRepository;
use App\Repositories\Service\ServiceRepository;
use App\Util\Debug\Log;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DienNuocDebitProcess extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dienuocdebitprocess_v2:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Xử lý công nợ điện nước';

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
        CronJobManagerRepository        $cronJobManager,
        DebitDetailRepository           $debitDetail,
        BillRepository                  $bill,
        ServiceRepository               $service,
        CronJobLogsRepository           $cronJobLogsRepository,
        ApartmentServicePriceRepository $apartmentServicePriceRepository,
        ConfigRepository                $config,
        DebitLogsRepository             $debitLogs
    )
    {
        Log::info('tandc2', 'dienuocdebitprocess_v2:cron');
        $cronJobs = $cronJobManager->findSignature($this->signature)->get();
        Log::info('dienuocdebitprocess_v2', 'start');
        $apartmentServicePrice = null;
        $isSuccess = false;
        $flag_cronjob = QueueRedis::getFlagCronjob();
        if (!$flag_cronjob) {
            QueueRedis::setFlagCronjob(1);
            foreach ($cronJobs as $cronJob) {
                do {
                    // Lấy queue thông tin dịch vụ của tòa nhà
                    $apartment_service = QueueRedis::getItemForQueue('add_queue_apartment_service_dien_nuoc_price_v2_' . $cronJob->building_id);
                    $resultDataJson = json_encode($apartment_service);
                    $electric = (object)$apartment_service;
                    $flag_update_debit = false;
                    if ($apartment_service) {
                        if (@$cronJob->type == 1) { // tính điện nước từ ghi chỉ  số điện nước trên app
                            try {
                                $request = json_decode($cronJob->data);
                                $Ids = (object)$request->ids;
                                $cycleName = $request->cycle_year . $request->cycle_month;
                                $apartment = Apartments::get_detail_apartment_by_apartment_id($electric->bdc_apartment_id);
                                $_query_apartmentServicePrice = ApartmentServicePrice::where('bdc_building_id', $electric->bdc_building_id)
                                    ->where('bdc_apartment_id', $electric->bdc_apartment_id)
                                    ->whereHas('service', function ($query) use ($electric) {
                                        if ($electric->type == 0) { // điện
                                            $query->where('type', ServiceRepository::DIEN);
                                        }
                                        if ($electric->type == 1) { // nước

                                            $query->where('type', ServiceRepository::NUOC);
                                        }
                                        if ($electric->type == 2) { // nước nóng

                                            $query->where('type', ServiceRepository::NUOC_NONG);
                                        }
                                        $query->where('status', 1);
                                    })
                                    ->where(['bdc_price_type_id' => 2, 'status' => 1]);
                                $_check = $_query_apartmentServicePrice->count();

                                if ($_check == 0) {
                                    $debitLogs->create([
                                        'bdc_building_id' => $electric->bdc_building_id,
                                        'bdc_apartment_id' => $electric->bdc_apartment_id,
                                        'key' => "import_dien_nuoc",
                                        'cycle_name' => $cycleName,
                                        'input' => "",
                                        'data' => "",
                                        'message' => "Căn hộ $apartment->name chưa đăng ký dịch vụ" . Helper::electric_type[$electric->type],
                                        'status' => 101
                                    ]);
                                    continue;
                                }

                                if ($_check > 1) {
                                    $debitLogs->create([
                                        'bdc_building_id' => $electric->bdc_building_id,
                                        'bdc_apartment_id' => $electric->bdc_apartment_id,
                                        'key' => "import_dien_nuoc",
                                        'cycle_name' => $cycleName,
                                        'input' => "",
                                        'data' => "",
                                        'message' => "Căn hộ $apartment->name đang sử dụng nhiều dịch vụ" . Helper::electric_type[$electric->type],
                                        'status' => 101
                                    ]);

                                    continue;
                                }

                                $_apartmentServicePrice = $_query_apartmentServicePrice->first();

                                if ($_apartmentServicePrice == null) {
                                    $debitLogs->create([
                                        'bdc_building_id' => $electric->bdc_building_id,
                                        'bdc_apartment_id' => $electric->bdc_apartment_id,
                                        'key' => "import_dien_nuoc",
                                        'cycle_name' => $cycleName,
                                        'input' => "",
                                        'data' => "",
                                        'message' => "Căn hộ $apartment->name chưa đăng ký sử dụng dịch vụ" . Helper::electric_type[$electric->type],
                                        'status' => 101
                                    ]);
                                    continue;
                                }
                                $resultDataJson = json_encode($_apartmentServicePrice);

                                $electrics = null;
                                $meters = null;
                                if ($request->cycle_name_handle_electric && @$Ids->electric == $electric->type) {
                                    $_service_ = Service::find($_apartmentServicePrice->bdc_service_id);
                                    $check_service_apartment = $_service_ ? $debitDetail::getDebitTypeServiceCycleName($electric->bdc_building_id, $apartment->id, $cycleName, $_service_->type) : null;
                                    if ($check_service_apartment) {
                                        $apartment_service = ApartmentServicePrice::get_detail_bdc_apartment_service_price_by_apartment_id($_apartmentServicePrice->id);
                                        $debitLogs->create([
                                            'bdc_building_id' => $electric->bdc_building_id,
                                            'bdc_apartment_id' => $electric->bdc_apartment_id,
                                            'bdc_service_id' => $_apartmentServicePrice->id,
                                            'key' => "import_dien_nuoc",
                                            'cycle_name' => $cycleName,
                                            'input' => json_encode($_apartmentServicePrice),
                                            'data' => "",
                                            'message' => "có phát sinh công nợ $apartment_service->name kỳ $cycleName",
                                            'status' => 105
                                        ]);
                                        continue;
                                    }
                                    $electrics = ElectricMeter::where(['bdc_building_id' => $electric->bdc_building_id, 'month_create' => $request->cycle_name_handle_electric, 'type' => @$Ids->electric])->where('bdc_apartment_id', $electric->bdc_apartment_id)->orderBy('date_update')->get();
                                }
                                if ($request->cycle_name_handle_meter && @$Ids->meter == $electric->type) {
                                    $_service_ = Service::find($_apartmentServicePrice->bdc_service_id);
                                    $check_service_apartment = $_service_ ? $debitDetail::getDebitTypeServiceCycleName($electric->bdc_building_id, $apartment->id, $cycleName, $_service_->type) : null;
                                    if ($check_service_apartment) {
                                        $apartment_service = ApartmentServicePrice::get_detail_bdc_apartment_service_price_by_apartment_id($_apartmentServicePrice->id);
                                        $debitLogs->create([
                                            'bdc_building_id' => $electric->bdc_building_id,
                                            'bdc_apartment_id' => $electric->bdc_apartment_id,
                                            'bdc_service_id' => $_apartmentServicePrice->id,
                                            'key' => "import_dien_nuoc",
                                            'cycle_name' => $cycleName,
                                            'input' => json_encode($_apartmentServicePrice),
                                            'data' => "",
                                            'message' => "có phát sinh công nợ $apartment_service->name kỳ $cycleName",
                                            'status' => 105
                                        ]);
                                        continue;
                                    }
                                    $meters = ElectricMeter::where(['bdc_building_id' => $electric->bdc_building_id, 'month_create' => $request->cycle_name_handle_meter, 'type' => @$Ids->meter])->where('bdc_apartment_id', $electric->bdc_apartment_id)->orderBy('date_update')->get();
                                }
                                if ($request->cycle_name_handle_meter_hot && @$Ids->meter_hot == $electric->type) {
                                    $_service_ = Service::find($_apartmentServicePrice->bdc_service_id);
                                    $check_service_apartment = $_service_ ? $debitDetail::getDebitTypeServiceCycleName($electric->bdc_building_id, $apartment->id, $cycleName, $_service_->type) : null;
                                    if ($check_service_apartment) {
                                        $apartment_service = ApartmentServicePrice::get_detail_bdc_apartment_service_price_by_apartment_id($_apartmentServicePrice->id);
                                        $debitLogs->create([
                                            'bdc_building_id' => $electric->bdc_building_id,
                                            'bdc_apartment_id' => $electric->bdc_apartment_id,
                                            'bdc_service_id' => $_apartmentServicePrice->id,
                                            'key' => "import_dien_nuoc",
                                            'cycle_name' => $cycleName,
                                            'input' => json_encode($_apartmentServicePrice),
                                            'data' => "",
                                            'message' => "có phát sinh công nợ $apartment_service->name kỳ $cycleName",
                                            'status' => 105
                                        ]);
                                        continue;
                                    }
                                    $meter_hots = ElectricMeter::where(['bdc_building_id' => $electric->bdc_building_id, 'month_create' => $request->cycle_name_handle_meter_hot, 'type' => @$Ids->meter_hot])->where('bdc_apartment_id', $electric->bdc_apartment_id)->orderBy('date_update')->get();
                                }
                                $price = 0;
                                $totalPrice = 0;
                                $dataArray = array();
                                $_dataArray = array();
                                $data_detail = null;
                                $data_price = null;
                                $totalNumber = 0;
                                $totalNumberAll = 0;
                                $electric_meters_ids = [];
                                $toDate = $electric->date_update;
                                $check_two_price = null;
                                $totalNumber_two_price = 0;
                                $list_date_update = null;
                                $list_level=[50,50,100,100,100];
                                if (@$meter_hots && isset($meter_hots) && $meter_hots->count() > 0) {
                                    foreach ($meter_hots as $key_1 => $value_1) {
                                        $list_date_update[] = $value_1->date_update;
                                        if ($value_1->type_action == 1) {
                                            continue;
                                        }
                                        $_meter = ElectricMeter::where(['bdc_apartment_id' => $value_1->bdc_apartment_id, 'type' => $value_1->type])->where('id', '<>', $value_1->id)->whereDate('date_update', '<', $value_1->date_update)->orderBy('date_update', 'desc')->limit(1)->first();
                                        $_data_detail = [
                                            'id' => $value_1->id,
                                            'from_date' => Carbon::parse(@$_meter->date_update)->format('d/m/Y'),
                                            'to_date' => Carbon::parse($value_1->date_update)->format('d/m/Y'),
                                            'interval' => @$_meter ? Carbon::parse(@$_meter->date_update)->diffInDays(Carbon::parse($value_1->date_update)) : ''
                                        ];
                                        $data_detail[] = (object)$_data_detail;
                                        $tieu_thu = $value_1->after_number - $value_1->before_number;
                                        if ($value_1->type_action == 2) {
                                            $check_two_price = $value_1;
                                            $totalNumber_two_price = $tieu_thu;
                                        } else {
                                            $totalNumber += $tieu_thu;
                                        }
                                        $totalNumberAll += $tieu_thu;
                                        $toDate = $value_1->date_update;
                                    }
                                    $electric_meters_ids = array_merge($electric_meters_ids, $meter_hots->pluck('id')->toArray());
                                }
                                if (@$meters && isset($meters) && $meters->count() > 0) {
                                    foreach ($meters as $key_1 => $value_1) {
                                        $list_date_update[] = $value_1->date_update;
                                        if ($value_1->type_action == 1) {
                                            continue;
                                        }
                                        $_meter = ElectricMeter::where(['bdc_apartment_id' => $value_1->bdc_apartment_id, 'type' => $value_1->type])->where('id', '<>', $value_1->id)->whereDate('date_update', '<', $value_1->date_update)->orderBy('date_update', 'desc')->limit(1)->first();
                                        $_data_detail = [
                                            'id' => $value_1->id,
                                            'from_date' => Carbon::parse(@$_meter->date_update)->format('d/m/Y'),
                                            'to_date' => Carbon::parse($value_1->date_update)->format('d/m/Y'),
                                            'interval' => @$_meter ? Carbon::parse(@$_meter->date_update)->diffInDays(Carbon::parse($value_1->date_update)) : ''
                                        ];
                                        $data_detail[] = (object)$_data_detail;
                                        $tieu_thu = $value_1->after_number - $value_1->before_number;
                                        if ($value_1->type_action == 2) {
                                            $check_two_price = $value_1;
                                            $totalNumber_two_price = $tieu_thu;
                                        } else {
                                            $totalNumber += $tieu_thu;
                                        }
                                        $totalNumberAll += $tieu_thu;
                                        $toDate = $value_1->date_update;
                                    }
                                    $electric_meters_ids = array_merge($electric_meters_ids, $meters->pluck('id')->toArray());
                                }
                                if (@$electrics && isset($electrics) && $electrics->count() > 0) {
                                    foreach ($electrics as $key_1 => $value_1) {
                                        $list_date_update[] = $value_1->date_update;
                                        if ($value_1->type_action == 1) {
                                            continue;
                                        }
                                        $_meter = ElectricMeter::where(['bdc_apartment_id' => $value_1->bdc_apartment_id, 'type' => $value_1->type])->where('id', '<>', $value_1->id)->whereDate('date_update', '<', $value_1->date_update)->orderBy('date_update', 'desc')->limit(1)->first();
                                        $_data_detail = [
                                            'id' => $value_1->id,
                                            'from_date' => Carbon::parse(@$_meter->date_update)->format('d/m/Y'),
                                            'to_date' => Carbon::parse($value_1->date_update)->format('d/m/Y'),
                                            'interval' => @$_meter ? Carbon::parse(@$_meter->date_update)->diffInDays(Carbon::parse($value_1->date_update)) : ''
                                        ];
                                        $data_detail[] = (object)$_data_detail;
                                        $tieu_thu = $value_1->after_number - $value_1->before_number;
                                        if ($value_1->type_action == 2) {
                                            $check_two_price = $value_1;
                                            $totalNumber_two_price = $tieu_thu;
                                        } else {
                                            $totalNumber += $tieu_thu;
                                        }
                                        $totalNumberAll += $tieu_thu;
                                        $toDate = $value_1->date_update;
                                    }
                                    $electric_meters_ids = array_merge($electric_meters_ids, $electrics->pluck('id')->toArray());
                                }

                                if ($totalNumber == 0 && $totalNumber_two_price == 0) {
                                    $debitLogs->create([
                                        'bdc_building_id' => $electric->bdc_building_id,
                                        'bdc_apartment_id' => $electric->bdc_apartment_id,
                                        'bdc_service_id' => $_apartmentServicePrice->id,
                                        'key' => "import_dien_nuoc",
                                        'cycle_name' => $cycleName,
                                        'input' => json_encode($_apartmentServicePrice),
                                        'data' => "",
                                        'message' => "Căn hộ $apartment->name chỉ số tiêu thụ = 0.",
                                        'status' => 102
                                    ]);
                                    continue;
                                }
                                $_progressive = Progressives::where('bdc_service_id', $_apartmentServicePrice->bdc_service_id)->orderBy('applicable_date')->limit(2)->get();
                                if (!$_progressive) {
                                    $debitLogs->create([
                                        'bdc_building_id' => $electric->bdc_building_id,
                                        'bdc_apartment_id' => $electric->bdc_apartment_id,
                                        'bdc_service_id' => $_apartmentServicePrice->id,
                                        'key' => "import_dien_nuoc",
                                        'cycle_name' => $cycleName,
                                        'input' => json_encode($_apartmentServicePrice),
                                        'data' => "",
                                        'message' => "Căn hộ $apartment->name chưa có bảng giá lũy tiến dịch vụ " . Helper::electric_type[$electric->type],
                                        'status' => 101
                                    ]);
                                    continue;
                                }
                                $check_progressive_detail = 0;
                                foreach ($_progressive as $key => $item) {
                                    // echo json_encode($item)."\n";
                                    if ($key == 0) {
                                        $progressivePrices = ProgressivePrice::where('progressive_id', $item->id)->get();
                                        if (!$progressivePrices) {
                                            $check_progressive_detail = 1;
                                        } else {
                                            foreach ($progressivePrices as $progressivePrice) {
                                                // tính tổng tiền cho dich vụ điện nước
                                                if ($progressivePrice->to >= $totalNumber) {
                                                    $price = ($totalNumber - $progressivePrice->from + 1) * $progressivePrice->price;
                                                    $_dataArray["from"] = $progressivePrice->from;
                                                    $_dataArray["to"] = $totalNumber;
                                                    $_dataArray["price"] = $progressivePrice->price;
                                                    $_dataArray["total_price"] = $price;
                                                    $totalPrice += $price;
                                                    array_push($dataArray, $_dataArray);
                                                    $data_price[] = (object)array('from' => $progressivePrice->from, 'to' => $totalNumber, 'price' => $progressivePrice->price, 'total_price' => $price, 'meter' => null,'level'=>array_key_exists($key,$list_level)? @$list_level[$key]:100);
                                                    break;
                                                } else {
                                                    $price = ($progressivePrice->to - $progressivePrice->from + 1) * $progressivePrice->price;
                                                    $_dataArray["from"] = $progressivePrice->from;
                                                    $_dataArray["to"] = $progressivePrice->to;
                                                    $_dataArray["price"] = $progressivePrice->price;
                                                    $_dataArray["total_price"] = $price;
                                                    $totalPrice += $price;
                                                    array_push($dataArray, $_dataArray);
                                                    $data_price[] = (object)array('from' => $progressivePrice->from, 'to' => $progressivePrice->to, 'price' => $progressivePrice->price, 'total_price' => $price, 'meter' => null,'level'=>array_key_exists($key,$list_level)? @$list_level[$key]:100);
                                                }
                                            }
                                            continue;
                                        }
                                    }
                                    echo json_encode($check_two_price) . "\n";
                                    if ($check_two_price != null) { // thay đổi giá giữa kỳ
                                        $progressivePrices = ProgressivePrice::where('progressive_id', $item->id)->get();
                                        echo json_encode($progressivePrices) . "\n";
                                        foreach ($progressivePrices as $progressivePrice) {
                                            // tính tổng tiền cho dich vụ điện nước
                                            if ($progressivePrice->to >= $totalNumber_two_price) {
                                                $price = ($totalNumber_two_price - $progressivePrice->from + 1) * $progressivePrice->price;
                                                $_dataArray["from"] = $progressivePrice->from;
                                                $_dataArray["to"] = $totalNumber_two_price;
                                                $_dataArray["price"] = $progressivePrice->price;
                                                $_dataArray["total_price"] = $price;
                                                $totalPrice += $price;
                                                array_push($dataArray, $_dataArray);
                                                $data_price[] = (object)array('from' => $progressivePrice->from, 'to' => $totalNumber_two_price, 'price' => $progressivePrice->price, 'total_price' => $price, 'meter' => $check_two_price,'level'=>array_key_exists($key,$list_level)? @$list_level[$key]:100);
                                                break;
                                            } else {
                                                $price = ($progressivePrice->to - $progressivePrice->from + 1) * $progressivePrice->price;
                                                $_dataArray["from"] = $progressivePrice->from;
                                                $_dataArray["to"] = $progressivePrice->to;
                                                $_dataArray["price"] = $progressivePrice->price;
                                                $_dataArray["total_price"] = $price;
                                                $totalPrice += $price;
                                                array_push($dataArray, $_dataArray);
                                                $data_price[] = (object)array('from' => $progressivePrice->from, 'to' => $progressivePrice->to, 'price' => $progressivePrice->price, 'total_price' => $price, 'meter' => $check_two_price,'level'=>array_key_exists($key,$list_level)? @$list_level[$key]:100);
                                            }
                                        }
//                                        foreach ($progressivePrices as $progressivePrice) {
//                                            // tính tổng tiền cho dich vụ điện nước
//                                            echo $totalNumberAll . "\n";
//                                            $temp = 0;
//                                            echo "totalNumber: " . $totalNumber . "\n";
//                                            echo "progressive: $progressivePrice->from\n";
//                                            if ($progressivePrice->to >= $totalNumber+1) {
//                                                echo $progressivePrice->from . '|' . $totalNumber . '|' . $progressivePrice->to . "\n";
//                                                if (($progressivePrice->from < $totalNumber) && ($totalNumber < $progressivePrice->to)) {
//                                                    echo $progressivePrice->from . '_' . $totalNumber . '_' . $progressivePrice->to . "\n";
//                                                    $price = ((int)$progressivePrice->to - (int)$totalNumber) * $progressivePrice->price;
//                                                    echo $price;
//                                                    echo '----------';
//                                                    echo "$progressivePrice->to | $totalNumber | $progressivePrice->price";
//                                                    $_dataArray["from"] = $totalNumber+1;
//                                                    $_dataArray["to"] = $progressivePrice->to;
//                                                    $_dataArray["price"] = $progressivePrice->price;
//                                                    $_dataArray["total_price"] = $price;
//                                                    $totalPrice += $price;
//                                                    array_push($dataArray, $_dataArray);
//                                                    $data_price[] = (object)array('from' => $totalNumber+1, 'to' => $progressivePrice->to, 'price' => $progressivePrice->price, 'total_price' => $price, 'meter' => ((int)$progressivePrice->to - (int)$totalNumber));
//                                                } else {
//                                                    if ($progressivePrice->to >= $totalNumberAll+1) {
//                                                        if ($progressivePrice->from < $totalNumberAll+1) {
//                                                            $price = ($totalNumberAll - $progressivePrice->from + 1) * $progressivePrice->price;
//                                                            $_dataArray["from"] = $progressivePrice->from;
//                                                            $_dataArray["to"] = $totalNumberAll;
//                                                            $_dataArray["price"] = $progressivePrice->price;
//                                                            $_dataArray["total_price"] = $price;
//                                                            $totalPrice += $price;
//                                                            array_push($dataArray, $_dataArray);
//                                                            $data_price[] = (object)array('from' => $progressivePrice->from, 'to' => $totalNumberAll, 'price' => $progressivePrice->price, 'total_price' => $price, 'meter' => 0);
//                                                        }
//                                                        break;
//                                                    } else {
//                                                        $price = ($progressivePrice->to - $progressivePrice->from + 1) * $progressivePrice->price;
//                                                        $_dataArray["from"] = $progressivePrice->from;
//                                                        $_dataArray["to"] = $progressivePrice->to;
//                                                        $_dataArray["price"] = $progressivePrice->price;
//                                                        $_dataArray["total_price"] = $price;
//                                                        $totalPrice += $price;
//                                                        array_push($dataArray, $_dataArray);
//                                                        $data_price[] = (object)array('from' => $progressivePrice->from, 'to' => $progressivePrice->to, 'price' => $progressivePrice->price, 'total_price' => $price, 'meter' => 0);
//                                                    }
//
//                                                }
//                                            }
//                                        }
                                    }

                                }
                                if ($check_two_price != null && $_progressive->count() == 1) {
                                    $debitLogs->create([
                                        'bdc_building_id' => $electric->bdc_building_id,
                                        'bdc_apartment_id' => $electric->bdc_apartment_id,
                                        'bdc_service_id' => $_apartmentServicePrice->id,
                                        'key' => "import_dien_nuoc",
                                        'cycle_name' => $cycleName,
                                        'input' => json_encode($_apartmentServicePrice),
                                        'data' => "",
                                        'message' => "Căn hộ $apartment->name chưa có 2 bảng giá lũy tiến dịch vụ chi tiết " . Helper::electric_type[$electric->type],
                                        'status' => 101
                                    ]);
                                    continue;
                                }
                                if ($check_progressive_detail == 1) {
                                    $debitLogs->create([
                                        'bdc_building_id' => $electric->bdc_building_id,
                                        'bdc_apartment_id' => $electric->bdc_apartment_id,
                                        'bdc_service_id' => $_apartmentServicePrice->id,
                                        'key' => "import_dien_nuoc",
                                        'cycle_name' => $cycleName,
                                        'input' => json_encode($_apartmentServicePrice),
                                        'data' => "",
                                        'message' => "Căn hộ $apartment->name chưa có bảng giá lũy tiến dịch vụ chi tiết " . Helper::electric_type[$electric->type],
                                        'status' => 101
                                    ]);
                                    continue;
                                }

                                $dataString = null;
                                $dataString['data_detail'] = $data_detail;
                                $dataString['total'] = $totalPrice;
                                $dataString['data_price'] = $data_price;
                                $dataString['check_two_price'] = $check_two_price ? 1 : 0;
                                echo json_encode($dataString);
                                // lấy chủ hộ của căn hộ
                                $_customer = UserApartments::getPurchaser($electric->bdc_apartment_id, 0);
                                if ($_customer == null) {
                                    $debitLogs->create([
                                        'bdc_building_id' => $electric->bdc_building_id,
                                        'bdc_apartment_id' => $electric->bdc_apartment_id,
                                        'bdc_service_id' => $_apartmentServicePrice->id,
                                        'key' => "import_dien_nuoc",
                                        'cycle_name' => $cycleName,
                                        'input' => json_encode($_apartmentServicePrice),
                                        'data' => "",
                                        'message' => "Căn hộ $apartment->name chưa có chủ hộ.",
                                        'status' => 102
                                    ]);
                                    continue;
                                }
                                // Lấy thông tin căn hộ
                                $get_after_number = ElectricMeter::where(['bdc_building_id' => $electric->bdc_building_id, 'bdc_apartment_id' => $apartment->id, 'type' => $electric->type])
                                    ->whereDate('date_update', '<', Carbon::parse($toDate)->format('Y-m-d'))->orderBy('date_update', 'desc')
                                    ->first();
                                $_fromDate = $get_after_number && $check_two_price == null ? Carbon::parse($get_after_number->date_update)->format('Y-m-d') : Carbon::parse($toDate)->subMonth(1)->format('Y-m-d');

                                $toDate = Carbon::parse($toDate)->format('Y-m-d');

                                $customerInfo = @$_customer->user_info_first;
                                if ($customerInfo == null) {
                                    $debitLogs->create([
                                        'bdc_building_id' => $electric->bdc_building_id,
                                        'bdc_apartment_id' => $electric->bdc_apartment_id,
                                        'bdc_service_id' => $_apartmentServicePrice->id,
                                        'key' => "import_dien_nuoc",
                                        'cycle_name' => $cycleName,
                                        'input' => json_encode($_apartmentServicePrice),
                                        'data' => "",
                                        'message' => "Thông tin người dùng không tồn tại.",
                                        'status' => 104
                                    ]);
                                    continue;
                                }


                                $discountPrice = 0;
                                if (isset($request->discount_check) && @$Ids->electric == $electric->type) {
                                    if ($request->discount_check == 'phan_tram') {
                                        $discountPrice = ($totalPrice / 100) * (int)$request->discount[0];
                                    }

                                    if ($request->discount_check == 'gia_tien') {
                                        $discountPrice = (int)$request->discount[0];
                                    }
                                }
                                if (isset($request->discount_check) && @$Ids->meter == $electric->type) {
                                    if ($request->discount_check == 'phan_tram') {
                                        $discountPrice = ($totalPrice / 100) * (int)$request->discount[1];
                                    }

                                    if ($request->discount_check == 'gia_tien') {
                                        $discountPrice = (int)$request->discount[1];
                                    }
                                }

                                $_apartmentServicePrice->customer_name = $customerInfo->full_name;
                                $_apartmentServicePrice->customer_address = $customerInfo->address;
                                $_apartmentServicePrice->provider_address = 'test';
                                $_apartmentServicePrice->deadline = $request->payment_deadline ? Carbon::parse($request->payment_deadline)->format('Y-m-d') : Carbon::now()->format('Y-m-d');
                                $_apartmentServicePrice->from_date = $_fromDate;
                                $_apartmentServicePrice->to_date = $toDate;
                                $_apartmentServicePrice->price = $totalPrice;
                                $_apartmentServicePrice->free = 0;
                                $_apartmentServicePrice->service_name = @$_apartmentServicePrice->name;
                                $_apartmentServicePrice->apartment_name = $apartment->name;
                                $_apartmentServicePrice->detail = json_encode($dataString);
                                $_apartmentServicePrice->bdc_price_type_id = 2;
                                $_apartmentServicePrice->use_bill = 1;

                                $_apartmentServicePrice->cycle_name = $cycleName;
                                $_apartmentServicePrice->url_image = $electric->images;
                                $_apartmentServicePrice->discount_check = isset($request->discount_check) ? $request->discount_check : null;
                                $_apartmentServicePrice->discount = isset($request->discount) ? $request->discount[1] : null;
                                $_apartmentServicePrice->discountPrice = $discountPrice;
                                $_apartmentServicePrice->electric_meters = count($electric_meters_ids) > 0 ? json_encode($electric_meters_ids) : null;

                                $resultDataJson = json_encode($_apartmentServicePrice);

                                $debitLogs->create([
                                    'bdc_building_id' => $electric->bdc_building_id,
                                    'bdc_apartment_id' => $electric->bdc_apartment_id,
                                    'bdc_service_id' => $_apartmentServicePrice->id,
                                    'key' => "import_dien_nuoc",
                                    'cycle_name' => $cycleName,
                                    'input' => json_encode($electric),
                                    'data' => $resultDataJson,
                                    'message' => "Căn $apartment->name Lấy thông tin " . Helper::electric_type[$electric->type] . " thành công",
                                    'status' => 100
                                ]);

                                $apartmentServicePrice = $_apartmentServicePrice;

                                $sumery = $apartmentServicePrice->price - round($apartmentServicePrice->discountPrice); // số tiền cần phải trả
                                if ($apartmentServicePrice->price <= 0) {
                                    $debitLogs->create([
                                        'bdc_building_id' => $cronJob->building_id,
                                        'bdc_apartment_id' => $apartmentServicePrice != null ? $apartmentServicePrice->bdc_apartment_id : "",
                                        'bdc_service_id' => $apartmentServicePrice != null ? $apartmentServicePrice->bdc_service_id : "",
                                        'key' => "dienuocdebitprocess:cron",
                                        'cycle_name' => $apartmentServicePrice->cycle_name,
                                        'input' => $resultDataJson,
                                        'data' => $isSuccess,
                                        'message' => "Phát sinh  = 0",
                                        'status' => 110
                                    ]);
                                    continue;
                                }
                                $code_bill = $bill->autoIncrementBillCode($config, $cronJob->building_id);
                                if ($apartmentServicePrice->use_bill == 0) {
                                    // tạo hóa đơn
                                    $billResult = $bill->create([
                                        'bdc_apartment_id' => $apartmentServicePrice->bdc_apartment_id,
                                        'bdc_building_id' => $apartmentServicePrice->bdc_building_id,
                                        'bill_code' => $code_bill,
                                        'cost' => $apartmentServicePrice->price,
                                        'customer_name' => $apartmentServicePrice->customer_name,
                                        'customer_address' => $apartmentServicePrice->customer_address != null ? $apartmentServicePrice->customer_address : "",
                                        'deadline' => $apartmentServicePrice->deadline,
                                        'provider_address' => 'Banking',
                                        'is_vat' => 0,
                                        'status' => $bill::WAIT_FOR_CONFIRM,
                                        'notify' => 0,
                                        'cycle_name' => $apartmentServicePrice->cycle_name
                                    ]);
                                    $billId = $billResult->id;
                                } else {
                                    $billWaitForConfirm = $bill->findBuildingApartmentIdWaitForConfirm($apartmentServicePrice->bdc_building_id, $apartmentServicePrice->bdc_apartment_id, $apartmentServicePrice->cycle_name);
                                    if ($billWaitForConfirm != null) {
                                        $billId = $billWaitForConfirm->id;
                                    } else {
                                        $billResult = $bill->create([
                                            'bdc_apartment_id' => $apartmentServicePrice->bdc_apartment_id,
                                            'bdc_building_id' => $apartmentServicePrice->bdc_building_id,
                                            'bill_code' => $code_bill,
                                            'cost' => $apartmentServicePrice->price,
                                            'customer_name' => $apartmentServicePrice->customer_name,
                                            'customer_address' => $apartmentServicePrice->customer_address != null ? $apartmentServicePrice->customer_address : "",
                                            'deadline' => $apartmentServicePrice->deadline,
                                            'provider_address' => 'Banking',
                                            'is_vat' => 0,
                                            'status' => $bill::WAIT_FOR_CONFIRM,
                                            'notify' => 0,
                                            'cycle_name' => $apartmentServicePrice->cycle_name
                                        ]);
                                        $billId = $billResult->id;
                                    }
                                }
                                // Tạo công nợ
                                // tính tổng tiền theo kỳ ở payment detail
                                $paidByCyleName = PaymentDetailRepository::getSumPaidByCycleName($apartmentServicePrice->bdc_apartment_id, $apartmentServicePrice->id, $apartmentServicePrice->cycle_name);
                                // tự động hach toán nếu trong ví có tiền

                                // Tạo công nợ
                                $debit = $debitDetail->getDebitByApartmentAndServiceAndCyclenameWithTrashed($apartmentServicePrice->bdc_apartment_id, $apartmentServicePrice->id, $apartmentServicePrice->cycle_name);
                                // Log::info('tu_check_cronjob', '4_' . json_encode($debit).'||'.$sumery);
                                $rs_debit = null;

                                if ($debit) {

                                    $_bill = $debit->bdc_bill_id ? $bill->find($debit->bdc_bill_id) : false;
                                    if ($_bill && $_bill->status > -3) {
                                        $debitLogs->create([
                                            'bdc_building_id' => $cronJob->building_id,
                                            'bdc_apartment_id' => $apartmentServicePrice != null ? $apartmentServicePrice->bdc_apartment_id : "",
                                            'bdc_service_id' => $apartmentServicePrice != null ? $apartmentServicePrice->bdc_service_id : "",
                                            'key' => "dienuocdebitprocess:cron",
                                            'cycle_name' => $apartmentServicePrice->cycle_name,
                                            'input' => $resultDataJson,
                                            'data' => $isSuccess,
                                            'message' => "Đã tồn tại phát sinh dịch vụ $apartmentServicePrice->name kỳ $apartmentServicePrice->cycle_name",
                                            'status' => 110
                                        ]);
                                        continue;
                                    }
                                    if ($debit->paid > 0) {
                                        $debitLogs->create([
                                            'bdc_building_id' => $cronJob->building_id,
                                            'bdc_apartment_id' => $apartmentServicePrice != null ? $apartmentServicePrice->bdc_apartment_id : "",
                                            'bdc_service_id' => $apartmentServicePrice != null ? $apartmentServicePrice->bdc_service_id : "",
                                            'key' => "dienuocdebitprocess:cron",
                                            'cycle_name' => $apartmentServicePrice->cycle_name,
                                            'input' => $resultDataJson,
                                            'data' => $isSuccess,
                                            'message' => "Dịch vụ $apartmentServicePrice->name kỳ $apartmentServicePrice->cycle_name đã có khoản thanh toán",
                                            'status' => 110
                                        ]);
                                        continue;
                                    }
                                    if ($debit->deleted_at) {
                                        $debitDetail->restoreDebitByApartmentAndServiceAndCyclename($debit);
                                    }
                                    // Log::info('tu_check_cronjob', '5_' . json_encode($apartmentServicePrice));
                                    $rs_debit = $debitDetail->updateDebitRestore(
                                        $debit->id,
                                        $billId,
                                        $apartmentServicePrice->from_date,
                                        $apartmentServicePrice->to_date,
                                        $apartmentServicePrice->detail,
                                        false,
                                        false,
                                        0,
                                        0,
                                        round($sumery),
                                        round($apartmentServicePrice->discountPrice),
                                        $apartmentServicePrice->discount_check == 'phan_tram' ? 1 : 0,
                                        false,
                                        $paidByCyleName
                                    );
                                    $flag_update_debit = true;
                                    // Log::info('tu_check_cronjob', '6_' . json_encode($rs_debit));
                                } else {
                                    $rs_debit = $debitDetail->createDebit(
                                        $apartmentServicePrice->bdc_building_id,
                                        $apartmentServicePrice->bdc_apartment_id,
                                        $billId,
                                        $apartmentServicePrice->id,
                                        $apartmentServicePrice->cycle_name,
                                        $apartmentServicePrice->from_date,
                                        $apartmentServicePrice->to_date,
                                        $apartmentServicePrice->detail,
                                        0,
                                        0,
                                        round($sumery),
                                        0,
                                        round($apartmentServicePrice->discountPrice),
                                        $apartmentServicePrice->discount_check == 'phan_tram' ? 1 : 0,
                                        $apartmentServicePrice->discountPrice ? "lên công nợ" : "",
                                        0,
                                        $paidByCyleName
                                    );
                                    // Log::info('tu_check_cronjob', '6_' . json_encode($rs_debit));
                                }

                                $isSuccess = true;

                                if ($billId > 0) {
                                    $debitDetailByBillId = $debitDetail->findByBillId($billId)->toArray();
                                    $sumary = array_sum(array_column($debitDetailByBillId, 'sumery'));
                                    $_bill = $bill->find($billId);
                                    $_bill->cost = $sumary;
                                    $_bill->save();
                                }
                                if (@$apartmentServicePrice->electric_meters) {
                                    $electric_meters = json_decode($apartmentServicePrice->electric_meters);
                                    ElectricMeter::whereIn('id', $electric_meters)->update(['status' => 1]);
                                }
                                $debitLogs->create([
                                    'bdc_building_id' => $cronJob->building_id,
                                    'bdc_apartment_id' => $apartmentServicePrice != null ? $apartmentServicePrice->bdc_apartment_id : "",
                                    'bdc_service_id' => $apartmentServicePrice != null ? $apartmentServicePrice->bdc_service_id : "",
                                    'key' => "dienuocdebitprocess:cron",
                                    'cycle_name' => $apartmentServicePrice->cycle_name,
                                    'input' => $resultDataJson,
                                    'data' => $isSuccess,
                                    'message' => "Thêm công nợ điện nước thành công",
                                    'status' => 200
                                ]);
                                $apartmentServicePriceRepository->update(['last_time_pay' => $apartmentServicePrice->to_date], $apartmentServicePrice->id);
                                // Log::info('tu_check_cronjob', '7_' . json_encode($rs_debit));
                                // Log::info('tu_check_cronjob', '8_' . json_encode($rs_debit));
                                if ($flag_update_debit) {
                                    QueueRedis::setItemForQueue('add_queue_stat_payment_', [
                                        "apartmentId" => $apartmentServicePrice->bdc_apartment_id,
                                        "service_price_id" => $apartmentServicePrice->id,
                                        "cycle_name" => $apartmentServicePrice->cycle_name,
                                    ]);
                                }
                            } catch (\Exception $e) {
                                QueueRedis::forgetFlagCronjob();
                                echo "error\n";
                                echo json_encode($e->getTraceAsString() . '|' . $e->getLine());
                                dBug::trackingPhpErrorV2($e->getMessage(), $e->getLine());
                                Log::info('tu_check_cronjob', '3_' . json_encode($electric));
                                $debitLogs->create([
                                    'bdc_building_id' => $cronJob->building_id,
                                    'bdc_apartment_id' => @$electric != null ? @$electric->bdc_apartment_id : "",
                                    'key' => "dienuocdebitprocess:cron",
                                    'cycle_name' => @$electric->cycle_name,
                                    'input' => $resultDataJson,
                                    'data' => "",
                                    'message' => $e->getMessage(),
                                    'status' => 500
                                ]);
                                continue;
                            }
                        } else {   // nếu là import dịch vụ giá luy tiến
                            try {
                                $apartmentServicePrice = $electric;
                                // kiểm tra xem có sử dụng mã billId đã có hay tạo mới
                                $sumery = $apartmentServicePrice->price - round($apartmentServicePrice->discountPrice); // số tiền cần phải trả
                                if ($apartmentServicePrice->price <= 0) {
                                    // dBug::trackingPhpErrorV2('sumery'.$sumery);
                                    // Log::info('tu_check_cronjob', '1_' . json_encode($apartmentServicePrice));
                                    // DB::rollBack();
                                    $debitLogs->create([
                                        'bdc_building_id' => $cronJob->building_id,
                                        'bdc_apartment_id' => $apartmentServicePrice != null ? $apartmentServicePrice->bdc_apartment_id : "",
                                        'bdc_service_id' => $apartmentServicePrice != null ? $apartmentServicePrice->bdc_service_id : "",
                                        'key' => "dienuocdebitprocess:cron",
                                        'cycle_name' => $apartmentServicePrice->cycle_name,
                                        'input' => $resultDataJson,
                                        'data' => $isSuccess,
                                        'message' => "Phát sinh  = 0",
                                        'status' => 110
                                    ]);
                                    continue;
                                }
                                $code_bill = $bill->autoIncrementBillCode($config, $cronJob->building_id);
                                if ($apartmentServicePrice->use_bill == 0) {
                                    // tạo hóa đơn
                                    $billResult = $bill->create([
                                        'bdc_apartment_id' => $apartmentServicePrice->bdc_apartment_id,
                                        'bdc_building_id' => $apartmentServicePrice->bdc_building_id,
                                        'bill_code' => $code_bill,
                                        'cost' => $apartmentServicePrice->price,
                                        'customer_name' => $apartmentServicePrice->customer_name,
                                        'customer_address' => $apartmentServicePrice->customer_address != null ? $apartmentServicePrice->customer_address : "",
                                        'deadline' => $apartmentServicePrice->deadline,
                                        'provider_address' => 'Banking',
                                        'is_vat' => 0,
                                        'status' => $bill::WAIT_FOR_CONFIRM,
                                        'notify' => 0,
                                        'cycle_name' => $apartmentServicePrice->cycle_name
                                    ]);
                                    $billId = $billResult->id;
                                } else {
                                    $billWaitForConfirm = $bill->findBuildingApartmentIdWaitForConfirm($apartmentServicePrice->bdc_building_id, $apartmentServicePrice->bdc_apartment_id, $apartmentServicePrice->cycle_name);
                                    if ($billWaitForConfirm != null) {
                                        $billId = $billWaitForConfirm->id;
                                    } else {
                                        $billResult = $bill->create([
                                            'bdc_apartment_id' => $apartmentServicePrice->bdc_apartment_id,
                                            'bdc_building_id' => $apartmentServicePrice->bdc_building_id,
                                            'bill_code' => $code_bill,
                                            'cost' => $apartmentServicePrice->price,
                                            'customer_name' => $apartmentServicePrice->customer_name,
                                            'customer_address' => $apartmentServicePrice->customer_address != null ? $apartmentServicePrice->customer_address : "",
                                            'deadline' => $apartmentServicePrice->deadline,
                                            'provider_address' => 'Banking',
                                            'is_vat' => 0,
                                            'status' => $bill::WAIT_FOR_CONFIRM,
                                            'notify' => 0,
                                            'cycle_name' => $apartmentServicePrice->cycle_name
                                        ]);
                                        $billId = $billResult->id;
                                    }
                                }
                                // Tạo công nợ
                                // tính tổng tiền theo kỳ ở payment detail
                                $paidByCyleName = PaymentDetailRepository::getSumPaidByCycleName($apartmentServicePrice->bdc_apartment_id, $apartmentServicePrice->id, $apartmentServicePrice->cycle_name);
                                // tự động hach toán nếu trong ví có tiền

                                // Tạo công nợ
                                $debit = $debitDetail->getDebitByApartmentAndServiceAndCyclenameWithTrashed($apartmentServicePrice->bdc_apartment_id, $apartmentServicePrice->id, $apartmentServicePrice->cycle_name);
                                // Log::info('tu_check_cronjob', '4_' . json_encode($debit).'||'.$sumery);
                                $rs_debit = null;

                                if ($debit) {
                                    $_bill = $debit->bdc_bill_id ? $bill->find($debit->bdc_bill_id) : false;
                                    if ($_bill && $_bill->status > -3) {
                                        $debitLogs->create([
                                            'bdc_building_id' => $cronJob->building_id,
                                            'bdc_apartment_id' => $apartmentServicePrice != null ? $apartmentServicePrice->bdc_apartment_id : "",
                                            'bdc_service_id' => $apartmentServicePrice != null ? $apartmentServicePrice->bdc_service_id : "",
                                            'key' => "dienuocdebitprocess:cron",
                                            'cycle_name' => $apartmentServicePrice->cycle_name,
                                            'input' => $resultDataJson,
                                            'data' => $isSuccess,
                                            'message' => "Đã tồn tại phát sinh dịch vụ $apartmentServicePrice->name kỳ $apartmentServicePrice->cycle_name",
                                            'status' => 110
                                        ]);
                                        continue;
                                    }
                                    if ($debit->paid > 0) {
                                        $debitLogs->create([
                                            'bdc_building_id' => $cronJob->building_id,
                                            'bdc_apartment_id' => $apartmentServicePrice != null ? $apartmentServicePrice->bdc_apartment_id : "",
                                            'bdc_service_id' => $apartmentServicePrice != null ? $apartmentServicePrice->bdc_service_id : "",
                                            'key' => "dienuocdebitprocess:cron",
                                            'cycle_name' => $apartmentServicePrice->cycle_name,
                                            'input' => $resultDataJson,
                                            'data' => $isSuccess,
                                            'message' => "Dịch vụ $apartmentServicePrice->name kỳ $apartmentServicePrice->cycle_name đã có khoản thanh toán",
                                            'status' => 110
                                        ]);
                                        continue;
                                    }
                                    if ($debit->deleted_at) {
                                        $debitDetail->restoreDebitByApartmentAndServiceAndCyclename($debit);
                                    }
                                    // Log::info('tu_check_cronjob', '5_' . json_encode($apartmentServicePrice));
                                    $rs_debit = $debitDetail->updateDebitRestore(
                                        $debit->id,
                                        $billId,
                                        $apartmentServicePrice->from_date,
                                        $apartmentServicePrice->to_date,
                                        $apartmentServicePrice->detail,
                                        false,
                                        false,
                                        0,
                                        0,
                                        round($sumery),
                                        round($apartmentServicePrice->discountPrice),
                                        $apartmentServicePrice->discount_check == 'phan_tram' ? 1 : 0,
                                        false,
                                        $paidByCyleName
                                    );
                                    $flag_update_debit = true;
                                    // Log::info('tu_check_cronjob', '6_' . json_encode($rs_debit));
                                } else {
                                    $rs_debit = $debitDetail->createDebit(
                                        $apartmentServicePrice->bdc_building_id,
                                        $apartmentServicePrice->bdc_apartment_id,
                                        $billId,
                                        $apartmentServicePrice->id,
                                        $apartmentServicePrice->cycle_name,
                                        $apartmentServicePrice->from_date,
                                        $apartmentServicePrice->to_date,
                                        $apartmentServicePrice->detail,
                                        0,
                                        0,
                                        round($sumery),
                                        0,
                                        round($apartmentServicePrice->discountPrice),
                                        $apartmentServicePrice->discount_check == 'phan_tram' ? 1 : 0,
                                        $apartmentServicePrice->discountPrice ? "lên công nợ" : "",
                                        0,
                                        $paidByCyleName
                                    );
                                    // Log::info('tu_check_cronjob', '6_' . json_encode($rs_debit));
                                }

                                $isSuccess = true;

                                if ($billId > 0) {
                                    $debitDetailByBillId = $debitDetail->findByBillId($billId)->toArray();
                                    $sumary = array_sum(array_column($debitDetailByBillId, 'sumery'));
                                    $_bill = $bill->find($billId);
                                    $_bill->cost = $sumary;
                                    $_bill->save();
                                }
                                if (@$apartmentServicePrice->electric_meters) {
                                    $electric_meters = json_decode($apartmentServicePrice->electric_meters);
                                    ElectricMeter::whereIn('id', $electric_meters)->update(['status' => 1]);
                                }
                                $debitLogs->create([
                                    'bdc_building_id' => $cronJob->building_id,
                                    'bdc_apartment_id' => $apartmentServicePrice != null ? $apartmentServicePrice->bdc_apartment_id : "",
                                    'bdc_service_id' => $apartmentServicePrice != null ? $apartmentServicePrice->bdc_service_id : "",
                                    'key' => "dienuocdebitprocess:cron",
                                    'cycle_name' => $apartmentServicePrice->cycle_name,
                                    'input' => $resultDataJson,
                                    'data' => $isSuccess,
                                    'message' => "Thêm công nợ điện nước thành công",
                                    'status' => 200
                                ]);
                                $apartmentServicePriceRepository->update(['last_time_pay' => $apartmentServicePrice->to_date], $apartmentServicePrice->id);
                                // Log::info('tu_check_cronjob', '7_' . json_encode($rs_debit));
                                // Log::info('tu_check_cronjob', '8_' . json_encode($rs_debit));
                                if ($flag_update_debit) {
                                    QueueRedis::setItemForQueue('add_queue_stat_payment_', [
                                        "apartmentId" => $apartmentServicePrice->bdc_apartment_id,
                                        "service_price_id" => $apartmentServicePrice->id,
                                        "cycle_name" => $apartmentServicePrice->cycle_name,
                                    ]);
                                }


                            } catch (\Exception $e) {
                                QueueRedis::forgetFlagCronjob();
                                echo "error\n";
                                dBug::trackingPhpErrorV2($e->getMessage(), $e->getLine());
//                            echo json_encode($electric);
                                $debitLogs->create([
                                    'bdc_building_id' => $cronJob->building_id,
                                    'bdc_apartment_id' => $electric != null ? $electric->bdc_apartment_id : "",
                                    'bdc_service_id' => $electric != null ? $electric->bdc_service_id : "",
                                    'key' => "dienuocdebitprocess:cron",
                                    'cycle_name' => $electric->cycle_name,
                                    'input' => $resultDataJson,
                                    'data' => "",
                                    'message' => $e->getMessage(),
                                    'status' => 500
                                ]);
                                continue;
                            }
                        }
                    }
                } while ($apartment_service != null);
                // Cập nhật trạng thái cron job
                echo "end ok\n";
                $cronJobManager->update(['status' => 1], $cronJob->id);
            }
            QueueRedis::forgetFlagCronjob();
        }
    }
}
