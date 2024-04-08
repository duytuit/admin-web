<?php

namespace App\Console\Commands\V2;

use App\Commons\Util\Debug\Log;
use App\Exceptions\QueueRedis;
use App\Models\BdcBills\Bills;
use App\Models\BdcV2DebitDetail\DebitDetail;
use App\Repositories\BdcApartmentServicePrice\ApartmentServicePriceRepository;
use App\Repositories\BdcBills\BillRepository;
use App\Repositories\BdcV2DebitDetail\DebitDetailRepository;
use App\Repositories\BdcDebitLogs\DebitLogsRepository;
use App\Repositories\BdcV2PaymentDetail\PaymentDetailRepository;
use App\Repositories\Config\ConfigRepository;
use App\Repositories\CronJobManager\CronJobManagerRepository;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PhiDauKyDebitProcess extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'phidaukydebitprocess_v2:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Thêm phí dịch vụ';

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
        ApartmentServicePriceRepository $apartmentServicePriceRepository,
        BillRepository $bill, 
        ConfigRepository $config,
        DebitLogsRepository $debitLogs)
    {
        Log::info('phidaukydebitprocess_v2','start');
        $cronJobs = $cronJobManager->findSignature($this->signature)->get();
        $apartmentServicePrice = null;
        $isSuccess = false;
        $flag_cronjob = QueueRedis::getFlagCronjob();
        if (!$flag_cronjob) {
            QueueRedis::setFlagCronjob(1);
            foreach ($cronJobs as $cronJob) {
                do {
                 
                    // Lấy queue thông tin dịch vụ của tòa nhà
                    $apartmentServicePrice = QueueRedis::getItemForQueue('add_queue_apartment_service_phi_dau_ky_v2_' . $cronJob->building_id);
                    $resultDataJson = json_encode($apartmentServicePrice);
                    try {
//                        DB::beginTransaction();
                        $flag_update_debit = false;
                        if($apartmentServicePrice){
                            echo "start\n";
                            echo "ok\n";
                            $apartmentServicePrice = (object)$apartmentServicePrice;
                            // if (Carbon::parse($apartmentServicePrice->last_time_pay) > Carbon::parse($apartmentServicePrice->from_date)) {
                            //     DB::rollBack();
                            //     $debitLogs->create([
                            //         'bdc_building_id' => $cronJob->building_id,
                            //         'bdc_apartment_id' => $apartmentServicePrice->bdc_apartment_id,
                            //         'bdc_service_id' => $apartmentServicePrice->bdc_service_id,
                            //         'key' => "debitprocess_v2:cron",
                            //         'cycle_name' => $apartmentServicePrice->cycle_name,
                            //         'input' => json_encode($apartmentServicePrice),
                            //         'data' => "",
                            //         'message' => "Last time pay" . $apartmentServicePrice->last_time_pay . " >= ngày tính đầu" . $apartmentServicePrice->from_date,
                            //         'status' => 110
                            //     ]);
                            //     continue;
                            // }
                            // kiểm tra xem có sử dụng mã billId đã có hay tạo mới
                            $sumery = @$apartmentServicePrice->price - round($apartmentServicePrice->discountPrice); // số tiền cần phải trả
                            if ($apartmentServicePrice->price <= 0) {
                                $debitLogs->create([
                                    'bdc_building_id' => $cronJob->building_id,
                                    'bdc_apartment_id' => $apartmentServicePrice != null ? $apartmentServicePrice->bdc_apartment_id : "",
                                    'bdc_service_id' => $apartmentServicePrice != null ? $apartmentServicePrice->bdc_service_id : "",
                                    'key' => "phidaukydebitprocess_v2:cron",
                                    'cycle_name' => $apartmentServicePrice->cycle_name,
                                    'input' => $resultDataJson,
                                    'data' => $isSuccess,
                                    'message' => "Phát sinh  = 0",
                                    'status' => 110
                                ]);
                                continue;
                            }
                            $code_bill =  $bill->autoIncrementBillCode($config, $cronJob->building_id);
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
                        
                            if ($debit) {
                                // $current_cycle_name = Carbon::now()->subMonth(1)->format('Ym');
                                $_bill = $debit->bdc_bill_id ? $bill->find($debit->bdc_bill_id) : false;
                                if ($_bill && $_bill->status > -3) {
                                    $debitLogs->create([
                                        'bdc_building_id' => $cronJob->building_id,
                                        'bdc_apartment_id' => $apartmentServicePrice != null ? $apartmentServicePrice->bdc_apartment_id : "",
                                        'bdc_service_id' => $apartmentServicePrice != null ? $apartmentServicePrice->bdc_service_id : "",
                                        'key' => "phidaukydebitprocess_v2:cron",
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
                                         'key' => "phidaukydebitprocess_v2:cron",
                                         'cycle_name' => $apartmentServicePrice->cycle_name,
                                         'input' => $resultDataJson,
                                         'data' => $isSuccess,
                                         'message' =>  "Dịch vụ $apartmentServicePrice->name kỳ $apartmentServicePrice->cycle_name đã có khoản thanh toán",
                                         'status' => 110
                                     ]);
                                     continue;
                                 }
                                if ($debit->deleted_at) {
                                    $debitDetail->restoreDebitByApartmentAndServiceAndCyclename($debit);
                                }
                                $debitDetail->updateDebitRestore(
                                    $debit->id,
                                    $billId,
                                    $apartmentServicePrice->from_date,
                                    $apartmentServicePrice->to_date,
                                    '[]',
                                    false,
                                    false,
                                    0,
                                    0,
                                    $sumery,
                                    round($apartmentServicePrice->discountPrice),
                                    $apartmentServicePrice->discount_check == 'phan_tram' ? 1 : 0,
                                    false,
                                    $paidByCyleName
                                );
                                $flag_update_debit = true;
                            } else {
                                $debitDetail->createDebit(
                                    $apartmentServicePrice->bdc_building_id,
                                    $apartmentServicePrice->bdc_apartment_id,
                                    $billId,
                                    $apartmentServicePrice->id,
                                    $apartmentServicePrice->cycle_name,
                                    $apartmentServicePrice->from_date,
                                    $apartmentServicePrice->to_date,
                                    '[]',
                                    0,
                                    0,
                                    $sumery,
                                    0,
                                    round($apartmentServicePrice->discountPrice),
                                    $apartmentServicePrice->discount_check == 'phan_tram' ? 1 : 0,
                                    $apartmentServicePrice->discountPrice ? "lên công nợ" : "",
                                    0,
                                    $paidByCyleName
                                );
                            }
                            $isSuccess = true;
    
                            if ($billId > 0) {
                                $debitDetailByBillId = $debitDetail->findByBillId($billId)->toArray();
                                $sumary = array_sum(array_column($debitDetailByBillId, 'sumery'));
                                $_bill = $bill->find($billId);
                                $_bill->cost = $sumary;
                                $_bill->save();
                            }
                            $debitLogs->create([
                                'bdc_building_id' => $cronJob->building_id,
                                'bdc_apartment_id' => $apartmentServicePrice != null ? $apartmentServicePrice->bdc_apartment_id : "",
                                'bdc_service_id' => $apartmentServicePrice != null ? $apartmentServicePrice->bdc_service_id : "",
                                'key' => "phidaukydebitprocess_v2:cron",
                                'cycle_name' => $apartmentServicePrice->cycle_name,
                                'input' => $resultDataJson,
                                'data' => $isSuccess,
                                'message' => "Thêm phí dịch vụ thành công",
                                'status' => 200
                            ]);
                        }
                        $apartmentServicePriceRepository->update(['last_time_pay' => $apartmentServicePrice->to_date], $apartmentServicePrice->id);
                        if($flag_update_debit){
                            QueueRedis::setItemForQueue('add_queue_stat_payment_', [
                                "apartmentId" => $apartmentServicePrice->bdc_apartment_id,
                                "service_price_id" => $apartmentServicePrice->id,
                                "cycle_name" => $apartmentServicePrice->cycle_name,
                            ]);
                        }
                    } catch (\Exception $e) {
                        QueueRedis::forgetFlagCronjob();
                        echo "error\n";
                        $debitLogs->create([
                            'bdc_building_id' => $cronJob->building_id,
                            'bdc_apartment_id' => $apartmentServicePrice != null ? @$apartmentServicePrice->bdc_apartment_id : "",
                            'bdc_service_id' => $apartmentServicePrice != null ? @$apartmentServicePrice->bdc_service_id : "",
                            'key' => "phidaukydebitprocess_v2:cron",
                            'cycle_name' =>  $apartmentServicePrice != null ? @$apartmentServicePrice->cycle_name : "",
                            'input' => $resultDataJson,
                            'data' => "",
                            'message' => $e->getMessage().'||'.$e->getLine(),
                            'status' => 500
                        ]);
                        continue;
                    }
                } while ($apartmentServicePrice != null);
                // Cập nhật trạng thái cron job

                echo "end ok\n";
                $cronJobManager->update(['status' => 1], $cronJob->id);
            }
            QueueRedis::forgetFlagCronjob();
        }
    }
}
