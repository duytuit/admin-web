<?php

namespace App\Console\Commands\V2;

use App\Exceptions\QueueRedis;
use App\Models\BdcBills\Bills;
use App\Models\BdcV2DebitDetail\DebitDetail;
use App\Repositories\BdcApartmentServicePrice\ApartmentServicePriceRepository;
use App\Repositories\BdcBills\BillRepository;
use App\Repositories\BdcDebitLogs\DebitLogsRepository;
use App\Repositories\BdcV2DebitDetail\DebitDetailRepository;
use App\Repositories\BdcV2PaymentDetail\PaymentDetailRepository;
use App\Repositories\Config\ConfigRepository;
use App\Repositories\CronJobLogs\CronJobLogsRepository;
use App\Repositories\CronJobManager\CronJobManagerRepository;
use App\Repositories\Promotion\PromotionRepository;
use App\Repositories\PromotionApartment\PromotionApartmentRepository;
use App\Util\Debug\Log;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CreateDebitCronV2 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create_debit_process_v3:cron';

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
        DebitLogsRepository $debitLogs
    ) {
//        sleep(4);
        Log::info('tandc2','create_debit_process_v3:cron');
        Log::info('create_debit_process_v3','start');
        $cronJobs = $cronJobManager->findSignature($this->signature)->get();
        $apartmentServicePrice = null;
        $start_time = time();
        try {
            $flag_cronjob = QueueRedis::getFlagCronjob();
            // Log::info('check_cronjob','1_'.json_encode($flag_cronjob));
            if (!$flag_cronjob) {
                QueueRedis::setFlagCronjob(1);
            //     Log::info('check_cronjob','1_'.json_encode($cronJobs));
            
                foreach ($cronJobs as $cronJob) {
                    do {
                        $apartmentServicePrice = QueueRedis::getItemForQueue("add_queue_apartment_service_price_z_v2_{$cronJob->building_id}");
                        //Log::info('check_cronjob','2_'.json_encode($apartmentServicePrice));

                        if($apartmentServicePrice){
                            echo "Start....";
                            $apartmentServicePriceCollectConvert = collect($apartmentServicePrice);
                            $apartmentServicePrice = (object)collect($apartmentServicePrice)->toArray();
                            foreach ($apartmentServicePrice as $key => $_apartmentServicePrice) {
                                $_apartmentServicePrice = (object)$_apartmentServicePrice;
                                $sumery = $_apartmentServicePrice->price - round($_apartmentServicePrice->discountPrice); // số tiền cần phải trả

                                // xử lý khuyễn mãi --- start ---
                                $discount = round($_apartmentServicePrice->discountPrice);
                                $discount_type = $_apartmentServicePrice->discount_check == 'phan_tram' ? 1 : 0;
                                $discount_note = $_apartmentServicePrice->discountPrice ? (string)$_apartmentServicePrice->discount_note  : ' ';
                                $checkProApart = PromotionApartmentRepository::getPromotionApartment($_apartmentServicePrice->bdc_apartment_id,$_apartmentServicePrice->id,$_apartmentServicePrice->cycle_name);
                                $ProInfo = false;
                                if($checkProApart){
                                    $ProInfo = PromotionRepository::getPromotionById($checkProApart->promotion_id);
                                }

                                if($ProInfo){
                                    $discount = $ProInfo->type_discount === 0 ? round($ProInfo->discount) : round(($ProInfo->discount/100)*$_apartmentServicePrice->price);
                                    $sumery = $_apartmentServicePrice->price - $discount;
                                    $discount_type = $ProInfo->type_discount;
                                    $discount_note = 'auto|'.$ProInfo->name;
                                }
                                // xử lý khuyễn mãi --- end ---

                                if ($_apartmentServicePrice->price <= 0) {
                                    $debitLogs->create([
                                        'bdc_building_id' => $cronJob->building_id,
                                        'bdc_apartment_id' => $_apartmentServicePrice->bdc_apartment_id,
                                        'bdc_service_id' => $_apartmentServicePrice->bdc_service_id,
                                        'key' => "create_debit_process_v3:cron",
                                        'cycle_name' => $_apartmentServicePrice->cycle_name,
                                        'input' => json_encode($_apartmentServicePrice),
                                        'data' => "",
                                        'message' => "Phát sinh = 0",
                                        'status' => 110
                                    ]);
                                    continue;
                                }
                                if ($_apartmentServicePrice->dateUsing == 0) {
                                    $debitLogs->create([
                                        'bdc_building_id' => $cronJob->building_id,
                                        'bdc_apartment_id' => $_apartmentServicePrice->bdc_apartment_id,
                                        'bdc_service_id' => $_apartmentServicePrice->bdc_service_id,
                                        'key' => "create_debit_process_v3:cron",
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
                                $BillApartment = $bill->findBuildingApartmentIdV3($_apartmentServicePrice->bdc_building_id, $_apartmentServicePrice->bdc_apartment_id, $_apartmentServicePrice->cycle_name);
                                //Tao hóa đơn
                                if ($BillApartment) {
                                    try {
                                        // tính tổng tiền theo kỳ ở payment detail
                                        $paidByCyleName = PaymentDetailRepository::getSumPaidByCycleName($_apartmentServicePrice->bdc_apartment_id, $_apartmentServicePrice->id, $_apartmentServicePrice->cycle_name);
                                        // tự động hach toán nếu trong ví có tiền
    
                                        $debit = $debitDetail->getDebitByApartmentAndServiceAndCyclenameWithTrashed($_apartmentServicePrice->bdc_apartment_id, $_apartmentServicePrice->id, $_apartmentServicePrice->cycle_name);
                                        if ($debit) {
                                            // $current_cycle_name = Carbon::now()->subMonth(1)->format('Ym');
    
                                             if ($debit->paid > 0) {
                                                 $debitLogs->create([
                                                     'bdc_building_id' => $cronJob->building_id,
                                                     'bdc_apartment_id' => $_apartmentServicePrice->bdc_apartment_id,
                                                     'bdc_service_id' => $_apartmentServicePrice->bdc_service_id,
                                                     'key' => "create_debit_process_v3:cron",
                                                     'cycle_name' => $_apartmentServicePrice->cycle_name,
                                                     'input' => json_encode($_apartmentServicePrice),
                                                     'data' => "",
                                                     'message' => "Dịch vụ $_apartmentServicePrice->name kỳ $_apartmentServicePrice->cycle_name đã có khoản thanh toán",
                                                     'status' => 110
                                                 ]);
                                                 continue;
                                             }
                                            $_bill =  $debit->bdc_bill_id ? $bill->find($debit->bdc_bill_id) : false;
                                            if($_bill && $_bill->status > -3){
                                                $debitLogs->create([
                                                    'bdc_building_id' => $cronJob->building_id,
                                                    'bdc_apartment_id' => $_apartmentServicePrice->bdc_apartment_id,
                                                    'bdc_service_id' => $_apartmentServicePrice->bdc_service_id,
                                                    'key' => "create_debit_process_v3:cron",
                                                    'cycle_name' => $_apartmentServicePrice->cycle_name,
                                                    'input' => json_encode($_apartmentServicePrice),
                                                    'data' => "",
                                                    'message' => "Đã tồn tại phát sinh dịch vụ $_apartmentServicePrice->name kỳ $_apartmentServicePrice->cycle_name",
                                                    'status' => 110
                                                ]);
                                                continue;
                                            }
                                            if ($debit->deleted_at) {
                                                $debitDetail->restoreDebitByApartmentAndServiceAndCyclename($debit);
                                            }
                                            $debitDetail->updateDebitRestore(
                                                $debit->id,
                                                $BillApartment->id,
                                                $_apartmentServicePrice->from_date,
                                                $_apartmentServicePrice->to_date,
                                                '[]',
                                                false,
                                                false,
                                                $_apartmentServicePrice->quantity,
                                                (int)$_apartmentServicePrice->one_price,
                                                $sumery,
                                                $discount,
                                                $discount_type,
                                                false,
                                                $paidByCyleName,
                                                false,
                                                $discount_note,false,$checkProApart ? $checkProApart->promotion_id : 0, $checkProApart ? $checkProApart->id : 0
                                            );
                                            QueueRedis::setItemForQueue('add_queue_stat_payment_', [
                                                "apartmentId" => $_apartmentServicePrice->bdc_apartment_id,
                                                "service_price_id" => $_apartmentServicePrice->id,
                                                "cycle_name" => $_apartmentServicePrice->cycle_name,
                                            ]);
                                        } else {
                                            // Tạo công nợ
                                            $debitDetail::createDebit(
                                                $_apartmentServicePrice->bdc_building_id,
                                                $_apartmentServicePrice->bdc_apartment_id,
                                                $BillApartment->id,
                                                $_apartmentServicePrice->id,
                                                $_apartmentServicePrice->cycle_name,
                                                $_apartmentServicePrice->from_date,
                                                $_apartmentServicePrice->to_date,
                                                '[]',
                                                $_apartmentServicePrice->quantity,
                                                (int)$_apartmentServicePrice->one_price,
                                                $sumery,
                                                0,
                                                $discount,
                                                $discount_type,
                                                $discount_note,
                                                0,
                                                $paidByCyleName,"",$checkProApart ? $checkProApart->promotion_id : 0, $checkProApart ? $checkProApart->id : 0
                                            );
                                        }
    
                                        // Cập nhật last_time_pay
                                        $apartmentServicePriceRepository->update(['last_time_pay' => $_apartmentServicePrice->to_date], $_apartmentServicePrice->id);
                                    } catch (\Exception $e) {
                                        QueueRedis::forgetFlagCronjob();
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
                                } else
                                {
                                    $sum = $apartmentServicePriceCollectConvert->sum('price');
                                    $sumFree = $apartmentServicePriceCollectConvert->where('free', '=', '1')->sum('price');
                                    try {
                                        // DB::beginTransaction();
                                        $code_bill =  $bill->autoIncrementBillCode($config, $cronJob->building_id);
                                        $billResult = $bill->create([
                                            'bdc_apartment_id' => $_apartmentServicePrice->bdc_apartment_id,
                                            'bdc_building_id' => $_apartmentServicePrice->bdc_building_id,
                                            'bill_code' => $code_bill,
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
    
                                        // tính tổng tiền theo kỳ ở payment detail
                                        $paidByCyleName = PaymentDetailRepository::getSumPaidByCycleName($_apartmentServicePrice->bdc_apartment_id, $_apartmentServicePrice->id, $_apartmentServicePrice->cycle_name);
                                        // tự động hach toán nếu trong ví có tiền
    
                                        $debit = $debitDetail->getDebitByApartmentAndServiceAndCyclenameWithTrashed($_apartmentServicePrice->bdc_apartment_id, $_apartmentServicePrice->id, $_apartmentServicePrice->cycle_name);
                                        if ($debit) {
                                            // $current_cycle_name = Carbon::now()->subMonth(1)->format('Ym');
    
                                            // if ($debit->cycle_name < $current_cycle_name) {
                                            //     continue;
                                            // }
                                            if ($debit->paid > 0) {
                                                $debitLogs->create([
                                                    'bdc_building_id' => $cronJob->building_id,
                                                    'bdc_apartment_id' => $_apartmentServicePrice->bdc_apartment_id,
                                                    'bdc_service_id' => $_apartmentServicePrice->bdc_service_id,
                                                    'key' => "create_debit_process_v3:cron",
                                                    'cycle_name' => $_apartmentServicePrice->cycle_name,
                                                    'input' => json_encode($_apartmentServicePrice),
                                                    'data' => "",
                                                    'message' => "Dịch vụ $_apartmentServicePrice->name kỳ $_apartmentServicePrice->cycle_name đã có khoản thanh toán",
                                                    'status' => 110
                                                ]);
                                                continue;
                                            }
                                            $_bill =  $debit->bdc_bill_id ? $bill->find($debit->bdc_bill_id) : false;
                                            if($_bill && $_bill->status > -3){
                                                $debitLogs->create([
                                                    'bdc_building_id' => $cronJob->building_id,
                                                    'bdc_apartment_id' => $_apartmentServicePrice->bdc_apartment_id,
                                                    'bdc_service_id' => $_apartmentServicePrice->bdc_service_id,
                                                    'key' => "create_debit_process_v3:cron",
                                                    'cycle_name' => $_apartmentServicePrice->cycle_name,
                                                    'input' => json_encode($_apartmentServicePrice),
                                                    'data' => "",
                                                    'message' => "Đã tồn tại phát sinh dịch vụ $_apartmentServicePrice->name kỳ $_apartmentServicePrice->cycle_name",
                                                    'status' => 110
                                                ]);
                                                continue;
                                            }
                                            if ($debit->deleted_at) {
                                                $debitDetail->restoreDebitByApartmentAndServiceAndCyclename($debit);
                                            }
                                            $debitDetail->updateDebitRestore(
                                                $debit->id,
                                                $billResult->id,
                                                $_apartmentServicePrice->from_date,
                                                $_apartmentServicePrice->to_date,
                                                '[]',
                                                false,
                                                false,
                                                $_apartmentServicePrice->quantity,
                                                (int)$_apartmentServicePrice->one_price,
                                                $sumery,
                                                $discount,
                                                $discount_type,
                                                false,
                                                $paidByCyleName,
                                                false,
                                                $discount_note,false,$checkProApart ? $checkProApart->promotion_id : 0, $checkProApart ? $checkProApart->id : 0
                                            );
                                            QueueRedis::setItemForQueue('add_queue_stat_payment_', [
                                                "apartmentId" => $_apartmentServicePrice->bdc_apartment_id,
                                                "service_price_id" => $_apartmentServicePrice->id,
                                                "cycle_name" => $_apartmentServicePrice->cycle_name,
                                            ]);
                                        } else {
                                      
                                            // Tạo công nợ
                                            $debitDetail::createDebit(
                                                $_apartmentServicePrice->bdc_building_id,
                                                $_apartmentServicePrice->bdc_apartment_id,
                                                $billResult->id,
                                                $_apartmentServicePrice->id,
                                                $_apartmentServicePrice->cycle_name,
                                                $_apartmentServicePrice->from_date,
                                                $_apartmentServicePrice->to_date,
                                                '[]',
                                                $_apartmentServicePrice->quantity,
                                                (int)$_apartmentServicePrice->one_price,
                                                $sumery,
                                                0,
                                                $discount,
                                                $discount_type,
                                                $discount_note,
                                                0,
                                                $paidByCyleName,"",$checkProApart ? $checkProApart->promotion_id : 0, $checkProApart ? $checkProApart->id : 0
                                            );
                                        }
    
                                        // Cập nhật last_time_pay
                                        $apartmentServicePriceRepository->update(['last_time_pay' => $_apartmentServicePrice->to_date], $_apartmentServicePrice->id);

                                    } catch (\Exception $e) {
                                        Log::info('create_debit_process_v3',$e->getTraceAsString());
                                        QueueRedis::forgetFlagCronjob();
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
                                }
                                $debitLogs->create([
                                    'bdc_building_id' => $_apartmentServicePrice->bdc_building_id,
                                    'bdc_apartment_id' => $_apartmentServicePrice->bdc_apartment_id,
                                    'bdc_service_id' => $_apartmentServicePrice->bdc_service_id,
                                    'key' => "create_debit_process_v3:cron",
                                    'cycle_name' => $_apartmentServicePrice->cycle_name,
                                    'input' => json_encode($_apartmentServicePrice),
                                    'data' => "",
                                    'message' => "Xử lý thành công.",
                                    'status' => 200
                                ]);
                            }
                        }
                       
                        echo "\nIsSucces :";
                    } while ($apartmentServicePrice != null);

                    echo "\nStart update cron job : $cronJob->id\n";
                    $cronJobManager->update(['status' => 1], $cronJob->id);
                    echo "\nEnd update cron job : $cronJob->id\n";
                }
                QueueRedis::forgetFlagCronjob();
           }
        } catch (\Exception $e) {
            QueueRedis::forgetFlagCronjob();
            echo $e->getMessage();
            Log::info('check_cronjob',$e->getTraceAsString());
            $cronJobLogsRepository->create([
                'bdc_building_id' => 0,
                'signature' => 'create_debit_process_v3:cron',
                'input_data' => 'Input Error',
                'output_data' => $e->getMessage(),
                'status' => 501
            ]);
        }
    }
}