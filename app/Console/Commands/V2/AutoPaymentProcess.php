<?php

namespace App\Console\Commands\V2;

use App\Exceptions\QueueRedis;
use App\Repositories\BdcCoin\BdcCoinRepository;
use App\Repositories\BdcV2DebitDetail\DebitDetailRepository;
use App\Repositories\BdcV2PaymentDetail\PaymentDetailRepository;
use App\Repositories\Customers\CustomersRespository;
use App\Util\Debug\Log;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class AutoPaymentProcess extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create_auto_payment_process:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Tự động hạch toán';

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
            do {

                $billIds = QueueRedis::getItemForQueue("add_queue_auto_payment_from_coin_");

                if (!empty($billIds)) {

                    // get all debit by billid

                    $allDebit = DebitDetailRepository::getAllByBillId($billIds);
                    if ($allDebit) foreach ($allDebit as $item) {

                        // tự động hach toán nếu trong ví có tiền
                        $sumery = (int)$item->sumery - (int)$item->paid; // số tiền cần phải trả: là bằng số tiền cần phải thanh toán trừ đi số tiền đã thanh toán

                        if ($sumery <= 0) {
                            // đã thanh toán hết rồi thì bỏ qua
                            continue;
                        }

                        $total_so_du = BdcCoinRepository::getCoin($item->bdc_apartment_id, $item->bdc_apartment_service_price_id);

                        // bắt đầu luồng mới của tú
                        // $check_coin_v2 = $paymentDetail::check_total_cost($cronJob->building_id,$item->bdc_apartment_id,$item->bdc_apartment_service_price_id);

                        // if($check_coin_v2 > 0){
                        //     $so_tien = $check_coin_v2 > $sumery ? $sumery : $check_coin_v2;
                        //     PaymentDetail::sub($cronJob->building_id,$item->bdc_apartment_id,0,$item->bdc_apartment_service_price_id,$item->bdc_bill_id,$item->id,$so_tien,"tien_mat",Carbon::now()->format('Ym'),Carbon::now());
                        // }
                        // kết thúc luồng mới của tú

                        if ($item->bdc_apartment_service_price_id && $total_so_du && $total_so_du->coin && $total_so_du->coin > 0) { // nếu trong ví có tiền thì tự động hạch toán


                            $coin = $sumery;
                            if ($sumery > $total_so_du->coin) { // số tiền cần thanh toán nhỏ hơn hoặc bằng số tiền trong ví
                                $coin = $total_so_du->coin;
                            }

                            $_customer = CustomersRespository::findApartmentIdV2($item->bdc_apartment_id, 0);
                            try {
                                \DB::beginTransaction();
                                $rsSubCoin = BdcCoinRepository::subCoin(
                                    $item->bdc_building_id,
                                    $item->bdc_apartment_id,
                                    $item->bdc_apartment_service_price_id,
                                    $item->cycle_name,
                                    @$_customer->user_info_id,
                                    $coin, "auto", 2, $total_so_du->bdc_apartment_service_price_id
                                );
                                PaymentDetailRepository::createPayment(
                                    $item->bdc_building_id,
                                    $item->bdc_apartment_id,
                                    $item->bdc_apartment_service_price_id,
                                    $item->cycle_name,
                                    $item->id,
                                    $coin,
                                    Carbon::now(),
                                    0,
                                    $rsSubCoin && isset($rsSubCoin['log']) ? $rsSubCoin['log'] : 0
                                );
                                \DB::commit();
                                DebitDetailRepository::updatePaidByCycleNameFromReceipt($item->bdc_apartment_id, $item->bdc_apartment_service_price_id, $item->cycle_name, false);
                                QueueRedis::setItemForQueue('add_queue_stat_payment_', [
                                    "apartmentId" => $item->bdc_apartment_id,
                                    "service_price_id" => $item->bdc_apartment_service_price_id,
                                    "cycle_name" => $item->cycle_name,
                                ]);
                            } catch (\Exception $e) {
                                \DB::rollBack();
                            }

                        }
                    }
                }
            } while ($billIds != null);

            echo "ok";
        } catch (\Exception $e) {
            echo $e->getMessage();
            Log::log("tandc", "Error create_auto_payment_process || " . $e->getMessage());
        }
        return;
    }
}
