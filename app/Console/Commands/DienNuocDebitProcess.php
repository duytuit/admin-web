<?php

namespace App\Console\Commands;

use App\Commons\Util\Debug\Log;
use App\Exceptions\QueueRedis;
use App\Models\BdcDebitLogs\DebitLogs;
use App\Repositories\BdcApartmentServicePrice\ApartmentServicePriceRepository;
use App\Repositories\BdcBills\BillRepository;
use App\Repositories\BdcDebitDetail\DebitDetailRepository;
use App\Repositories\BdcDebitLogs\DebitLogsRepository;
use App\Repositories\Config\ConfigRepository;
use App\Repositories\CronJobLogs\CronJobLogsRepository;
use App\Repositories\CronJobManager\CronJobManagerRepository;
use App\Repositories\Customers\CustomersRespository;
use App\Repositories\Service\ServiceRepository;
use Webpatser\Uuid\Uuid;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DienNuocDebitProcess extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dienuocdebitprocess:cron';

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
        CronJobManagerRepository $cronJobManager, 
        DebitDetailRepository $debitDetail, 
        BillRepository $bill, 
        ServiceRepository $service,
        CronJobLogsRepository $cronJobLogsRepository,
        ConfigRepository $config,
        DebitLogsRepository $debitLogs
        )
    {
//        sleep(1);
        Log::info('tandc2','dienuocdebitprocess:cron');
        $cronJobs = $cronJobManager->findSignature($this->signature)->get();
        $apartmentServicePrice = null;
        $isFlag = false;
        $isSuccess = false;
        $flag_cronjob = QueueRedis::getFlagCronjob();
        if (!$flag_cronjob) {
            QueueRedis::setFlagCronjob(1);
            foreach ($cronJobs as $cronJob) {
                $count = 0;
                do {
                    DB::beginTransaction();
                    try {
                        // Lấy queue thông tin dịch vụ của tòa nhà
                        $apartmentServicePrice = QueueRedis::getItemForQueue('add_queue_apartment_service_dien_nuoc_price_' . $cronJob->building_id);
                        $resultDataJson = json_encode($apartmentServicePrice);
                        echo "start\n";
                        if (!empty($apartmentServicePrice)) {
                            echo "ok\n";
                            $apartmentServicePrice = (object)$apartmentServicePrice;
                            $findServiceCheckFromDate = DebitDetailRepository::findServiceCheckFromDate($apartmentServicePrice->bdc_service_id, $apartmentServicePrice->bdc_apartment_id, $apartmentServicePrice->id, $apartmentServicePrice->from_date);
                            if ($findServiceCheckFromDate) {
                                echo "check\n";
                                $debitLogs->create([
                                    'bdc_building_id' =>  $cronJob->building_id,
                                    'bdc_apartment_id' => $apartmentServicePrice ? $apartmentServicePrice->bdc_apartment_id : "",
                                    'bdc_service_id' => $apartmentServicePrice ? $apartmentServicePrice->bdc_service_id : "",
                                    'key' => "dienuocdebitprocess:cron",
                                    'input' => '',
                                    'data' => "",
                                    'message' => "Phí " . $apartmentServicePrice->name . " đã trùng trong khoảng thời gian" . $apartmentServicePrice->from_date . ' - ' . $apartmentServicePrice->to_date,
                                    'status' => 101
                                ]);
                                DB::commit();
                                continue;
                            }
                            // kiểm tra xem có sử dụng mã billId đã có hay tạo mới
                            if ($apartmentServicePrice->use_bill == 0) {
                                // tạo hóa đơn
                                $billResult = $bill->create([
                                    'bdc_apartment_id' => $apartmentServicePrice->bdc_apartment_id,
                                    'bdc_building_id' => $apartmentServicePrice->bdc_building_id,
                                    'bill_code' => $bill->autoIncrementBillCode($config, $cronJob->building_id),
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
                                $billWaitForConfirm = $bill->findBuildingApartmentIdWaitForConfirm($apartmentServicePrice->bdc_building_id, $apartmentServicePrice->bdc_apartment_id,$apartmentServicePrice->cycle_name);
                                //$billWaitForConfirm = $bill->findBuildingApartmentIdV2($apartmentServicePrice->bdc_building_id, $apartmentServicePrice->bdc_apartment_id,$apartmentServicePrice->cycle_name);
                                if ($billWaitForConfirm != null) {
                                    $billId = $billWaitForConfirm->id;
                                } else {
                                    $billResult = $bill->create([
                                        'bdc_apartment_id' => $apartmentServicePrice->bdc_apartment_id,
                                        'bdc_building_id' => $apartmentServicePrice->bdc_building_id,
                                        'bill_code' => $bill->autoIncrementBillCode($config, $cronJob->building_id),
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

                            // Tìm công nợ dịch vụ tháng trước
                            $debitDetailMaxVersion = $debitDetail->findByBuildingApartmentServiceId(
                                $apartmentServicePrice->bdc_building_id,
                                $apartmentServicePrice->bdc_apartment_id,
                                $apartmentServicePrice->bdc_service_id
                            );
                            $previousOwed = 0;
                            if ($debitDetailMaxVersion) {
                                $previousOwed = $debitDetailMaxVersion->previous_owed;
                                //$debitDetail->update(['previous_owed' => 0], $debitDetailMaxVersion->id);
                            }
                            // Tạo công nợ
                            $debitDetail->create([
                                'bdc_building_id' => $apartmentServicePrice->bdc_building_id,
                                'bdc_bill_id' => $billId,
                                'bdc_apartment_id' => $apartmentServicePrice->bdc_apartment_id,
                                'bdc_service_id' => $apartmentServicePrice->bdc_service_id,
                                'bdc_apartment_service_price_id' => $apartmentServicePrice->id,
                                'title' => $apartmentServicePrice->name,
                                'from_date' => $apartmentServicePrice->from_date,
                                'to_date' => $apartmentServicePrice->to_date,
                                'detail' => $apartmentServicePrice->detail,
                                'version' => 0,
                                'sumery' => $apartmentServicePrice->price,
                                'new_sumery' => $apartmentServicePrice->price,
                                'previous_owed' => $previousOwed,
                                'paid' => 0,
                                'is_free' => $apartmentServicePrice->free,
                                'cycle_name' => $apartmentServicePrice->cycle_name,
                                'bdc_price_type_id' => $apartmentServicePrice->bdc_price_type_id,
                                'image' => $apartmentServicePrice->url_image
                            ]);
                            $isSuccess = true;

                            if ($billId > 0) {
                                $debitDetailByBillId = $debitDetail->findMaxVersionByBillId($billId);
                                $sumary = array_sum(array_column($debitDetailByBillId, 'sumery'));
                                $_bill = $bill->find($billId);
                                $_bill->cost = $sumary;
                                $_bill->save();
                            }
                            $debitLogs->create([
                                'bdc_building_id' => $cronJob->building_id,
                                'bdc_apartment_id' => $apartmentServicePrice ? $apartmentServicePrice->bdc_apartment_id : "",
                                'bdc_service_id' => $apartmentServicePrice ? $apartmentServicePrice->bdc_service_id : "",
                                'key' => "dienuocdebitprocess:cron",
                                'input' => $resultDataJson,
                                'data' => $isSuccess,
                                'message' => "Thêm công nợ điện nước thành công",
                                'status' => 200
                            ]);
                        }
                    } catch (\Exception $e) {
                        echo "error\n";
                        DB::rollBack();
                        $debitLogs->create([
                            'bdc_building_id' => $cronJob->building_id,
                            'bdc_apartment_id' => $apartmentServicePrice ? $apartmentServicePrice->bdc_apartment_id : "",
                            'bdc_service_id' => $apartmentServicePrice ? $apartmentServicePrice->bdc_service_id : "",
                            'key' => "dienuocdebitprocess:cron",
                            'input' => '',
                            'data' => "",
                            'message' => $e->getMessage(),
                            'status' => 500
                        ]);
                    }
                    DB::commit();
                } while ($apartmentServicePrice != null);
                // Cập nhật trạng thái cron job
                $cronJobManager->update(['status' => 1], $cronJob->id);
            }
            QueueRedis::forgetFlagCronjob();
        }
    }
}
