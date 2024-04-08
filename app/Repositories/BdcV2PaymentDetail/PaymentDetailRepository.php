<?php
/*
 * create by tandc
 * */

namespace App\Repositories\BdcV2PaymentDetail;

use App\Helpers\dBug;
use App\Models\BdcV2LogCoinDetail\LogCoinDetail;
use App\Repositories\BdcV2LogCoinDetail\LogCoinDetailRepository;
use App\Repositories\Eloquent\Repository;
use App\Util\Debug\Log;
use Illuminate\Support\Facades\Cache;
use App\Models\BdcV2PaymentDetail\PaymentDetail;
use Illuminate\Support\Facades\DB;

class PaymentDetailRepository extends Repository
{
    function model()
    {
        return PaymentDetail::class;
    }

    /**
     * clearCache : xóa cache
     * @param $apartmentId integer
     * @param $service_price_id integer
     * @return boolean
     * */
    public static function clearCache(int $apartmentId, int $service_price_id): bool
    {
//        $keyCache = "getCoinBy_" . $apartmentId . "_" . $service_price_id;
//        Cache::forget($keyCache);
        return true;
    }

    /**
     * createPayment : thêm công nợ mới
     * @param int $bdc_building_id
     * @param int $bdc_apartment_id
     * @param int $bdc_apartment_service_price_id
     * @param int $cycle_name
     * @param int $bdc_debit_detail_id
     * @param int $paid
     * @param $paid_date
     * @param int $bdc_receipt_id
     * @param int $bdc_log_coin_id
     * @return mixed
     */

    public static function createPayment(int $bdc_building_id, int $bdc_apartment_id, int $bdc_apartment_service_price_id, int $cycle_name, int $bdc_debit_detail_id, int $paid, $paid_date, int $bdc_receipt_id, $bdc_log_coin_id = null)
    {
//        self::clearCache($apartmentId, $service_price_id);
        return PaymentDetail::create([
            'bdc_building_id' => $bdc_building_id, // mã tòa
            'bdc_apartment_id' => $bdc_apartment_id, // mã căn hộ
            'bdc_apartment_service_price_id' => $bdc_apartment_service_price_id, // mã dịch vụ
            'cycle_name' => $cycle_name, // kỳ theo ngày thu tiền
            'bdc_receipt_id' => $bdc_receipt_id, // mã phiếu thu
            'bdc_log_coin_id' => $bdc_log_coin_id, // mã log ví
            'bdc_debit_detail_id' => $bdc_debit_detail_id, // mã debit
            'paid' => $paid, // số tiền thanh toán
            'paid_date' => $paid_date, // ngày thanh toán
        ]);
    }

    /**
     * getDataByReceiptId : lấy tất cả theo phiếu thu
     * @param int $bdc_receipt_id
     * @return mixed
     */

    public static function getDataByReceiptId(int $bdc_receipt_id)
    {
        return PaymentDetail::where([
            'bdc_receipt_id' => $bdc_receipt_id
        ])->get();
    }

    /**
     * getSumPaidByCycleName : lấy tổng tiền đã đóng theo kỳ
     * @param int $apartmentId
     * @param mixed $service_price_id
     * @param mixed $cycle_name
     * @param bool $fromReceipt
     * @return mixed
     */

    public static function getSumPaidByCycleName(int $apartmentId, $service_price_id = false, $cycle_name = false, bool $fromReceipt = false, $useCache = true)
    {
        $keyCache = "getSumPaidByCycleName_" . $apartmentId.'_'.$service_price_id.'_'.$cycle_name.'_'.$fromReceipt;
        if(!$useCache) Cache::forget($keyCache);
        return Cache::remember($keyCache, 5, function () use ($apartmentId, $service_price_id, $cycle_name, $fromReceipt) {
            $arrWhere = [
                'bdc_apartment_id' => $apartmentId,
            ];
            $service_price_id!==false&&$arrWhere["bdc_apartment_service_price_id"] = $service_price_id;
            $cycle_name!=false&&$arrWhere["cycle_name"] = $cycle_name;
            $rs = PaymentDetail::where($arrWhere)->select(DB::raw('SUM(paid) as tong'));
            $fromReceipt&&$rs->where('bdc_receipt_id','!=',0);
            $service_price_id!==false&&$rs->groupBy('bdc_apartment_id','bdc_apartment_service_price_id');
            $service_price_id==false&&$rs->groupBy('bdc_apartment_id');
            $rs = $rs->first();
            if (!$rs || !isset($rs["tong"])) return 0;
            return $rs["tong"];
        });
    }

    public static function getPaidByCycleName(int $apartmentId, $service_price_id = false, $cycle_name = false, bool $fromReceipt = false)
    {
        $arrWhere = [
            'bdc_apartment_id' => $apartmentId,
        ];
        $service_price_id!==false&&$arrWhere["bdc_apartment_service_price_id"] = $service_price_id;
        $cycle_name!=false&&$arrWhere["cycle_name"] = $cycle_name;
        $rs = PaymentDetail::where($arrWhere)->select(DB::raw('*'));
        $fromReceipt&&$rs->where('bdc_receipt_id','!=',0);
//        $service_price_id!==false&&$rs->groupBy('bdc_apartment_id','bdc_apartment_service_price_id');
//        $service_price_id==false&&$rs->groupBy('bdc_apartment_id');
        $rs = $rs->get();
        return $rs;
    }

    public static function getSumPaidByCycleNameCus(int $apartmentId, $service_price_id = false, $cycle_name = false, bool $fromReceipt = false, $oper = "=", $useCache = true)
    {
        $keyCache = "getSumPaidByCycleName_" . $apartmentId.'_'.$service_price_id.'_'.$cycle_name.'_'.$fromReceipt."_".$oper;
        if(!$useCache) Cache::forget($keyCache);
        return Cache::remember($keyCache, 5, function () use ($apartmentId, $service_price_id, $cycle_name, $fromReceipt, $oper) {
            $arrWhere = [
                'bdc_apartment_id' => $apartmentId,
            ];
            $service_price_id!==false&&$arrWhere["bdc_apartment_service_price_id"] = $service_price_id;
            $rs = PaymentDetail::where($arrWhere)->select(DB::raw('SUM(paid) as tong'));
            if ($cycle_name) $rs->where("cycle_name", $oper, $cycle_name);
            $fromReceipt&&$rs->where('bdc_receipt_id','!=',0);
            $service_price_id!==false&&$rs->groupBy('bdc_apartment_id','bdc_apartment_service_price_id');
            $service_price_id==false&&$rs->groupBy('bdc_apartment_id');
            $rs = $rs->first();
            if (!$rs || !isset($rs["tong"])) return 0;
            return $rs["tong"];
        });
    }
    /**
     * getSumPaidByCycleName : lấy tổng tiền đã đóng theo mã hóa đơn
     * @param int $apartmentId
     * @param mixed $service_price_id
     * @param mixed $cycle_name
     * @param bool $fromReceipt
     * @return mixed
     */

    public static function getSumPaidByDebitId(int $bdc_debit_detail_id)
    {
        $keyCache = "getSumPaidByDebitId_" . $bdc_debit_detail_id;
//        Cache::forget($keyCache);
        return Cache::remember($keyCache, 5, function () use ($bdc_debit_detail_id) {
            $rs = PaymentDetail::where([
                'bdc_debit_detail_id' => $bdc_debit_detail_id,
            ])->select(DB::raw('SUM(paid) as tong'));
            $rs = $rs->first();
            if (!$rs || !isset($rs["tong"])) return 0;
            return $rs["tong"];
        });
    }
    /**
     * getSumPaidByCycleName : lấy tổng tiền đã đóng trong phiếu thu theo kỳ
     * @param int $apartmentId
     * @param mixed $service_price_id
     * @param mixed $cycle_name
     * @return mixed
     */

    public static function getSumPaidByCycleNameFromReceipt(int $apartmentId, $service_price_id = false, $cycle_name = false, $useCache = true)
    {
        $keyCache = "getSumPaidByCycleNameFromReceipt_" . $apartmentId.'_'.$service_price_id.'_'.$cycle_name;
        if(!$useCache) Cache::forget($keyCache);
        return Cache::remember($keyCache, 5, function () use ($apartmentId, $service_price_id, $cycle_name, $useCache) {

            $sumCoin = LogCoinDetailRepository::getSumPaidLogCoinByCycleName($apartmentId, $service_price_id,$cycle_name, true, $useCache); // tiền từ phiếu thu log coin
            $sumPayment = self::getSumPaidByCycleName($apartmentId, $service_price_id,$cycle_name,true, $useCache); // tiền từ phiếu thu bảng payment
            // tiền phân bổ from_type = 3
            $addSumCoin = LogCoinDetailRepository::getSumPaidLogCoinByCycleNameFormType($apartmentId, $service_price_id,$cycle_name, 3, 1,$useCache); // số coin từ phân bổ được cộng
            $SubSumCoin = LogCoinDetailRepository::getSumPaidLogCoinByCycleNameFormType($apartmentId, $service_price_id,$cycle_name, 3, 0,$useCache); // số coin từ phân bổ bị trừ

            // tiền hạch toán từ ví A from_type = 4
//            $SumCoin_tuvi = LogCoinDetailRepository::getSumPaidLogCoinByCycleNameFormTypeIs4($apartmentId, false ,$cycle_name, $service_price_id); // số coin được hach toán từ ví A
            $SubSumCoin_hachtoan = LogCoinDetailRepository::getSumPaidLogCoinByCycleNameFormTypeIs4($apartmentId, $service_price_id,$cycle_name,false,$useCache); // số coin bị trừ cho hạch toán dịch vụ
            //dBug::trackingPhpErrorV2($sumCoin.'|'.$sumPayment.'|'.$addSumCoin.'|'.$SubSumCoin.'|'.$SubSumCoin_hachtoan);
            return $sumCoin+$sumPayment+($addSumCoin-$SubSumCoin)-$SubSumCoin_hachtoan;
        });
    }

    public static function getSumPaidByCycleNameFromReceiptCus(int $apartmentId, $service_price_id = false, $cycle_name = false, $oper = "=", $useCache = true)
    {
        $keyCache = "getSumPaidByCycleNameFromReceipt_" . $apartmentId . '_' . $service_price_id . '_' . $cycle_name.'_'.$oper;
        if (!$useCache) Cache::forget($keyCache);
        return Cache::remember($keyCache, 5, function () use ($apartmentId, $service_price_id, $cycle_name, $useCache, $oper) {
            $sumCoin = LogCoinDetailRepository::getSumPaidLogCoinByCycleNameCus($apartmentId, $service_price_id, $cycle_name, true, $oper, $useCache); // tiền từ phiếu thu log coin
            $sumPayment = self::getSumPaidByCycleNameCus($apartmentId, $service_price_id, $cycle_name, true, $oper, $useCache); // tiền từ phiếu thu bảng payment
            // tiền phân bổ from_type = 3
            $addSumCoin = LogCoinDetailRepository::getSumPaidLogCoinByCycleNameFormTypeCus($apartmentId, $service_price_id, $cycle_name, 3, 1, $oper); // số coin từ phân bổ được cộng
            $SubSumCoin = LogCoinDetailRepository::getSumPaidLogCoinByCycleNameFormTypeCus($apartmentId, $service_price_id, $cycle_name, 3, 0, $oper); // số coin từ phân bổ bị trừ
            // tiền hạch toán từ ví A from_type = 4
//            $SumCoin_tuvi = LogCoinDetailRepository::getSumPaidLogCoinByCycleNameFormTypeIs4($apartmentId, false ,$cycle_name, $service_price_id); // số coin được hach toán từ ví A
            $SubSumCoin_hachtoan = LogCoinDetailRepository::getSumPaidLogCoinByCycleNameFormTypeIs4Cus($apartmentId, $service_price_id, $cycle_name, false, $oper); // số coin bị trừ cho hạch toán dịch vụ
            // Log::dump($sumCoin.'|'.$sumPayment.'|'.$addSumCoin.'|'.$SubSumCoin.'|'.$SubSumCoin_hachtoan);
            return $sumCoin + $sumPayment + ($addSumCoin - $SubSumCoin) - $SubSumCoin_hachtoan;
        });
    }

    public static function getSumPaidReceipt($receiptId,$apartmentId,$bdc_apartment_service_price_id)
    {
        $payment_detail = PaymentDetail::where(['bdc_receipt_id'=>$receiptId, 'bdc_apartment_id'=>$apartmentId, 'bdc_apartment_service_price_id'=>$bdc_apartment_service_price_id])->sum('paid');
        $logCoinDetail = LogCoinDetail::where(['from_id'=>$receiptId, 'bdc_apartment_id'=>$apartmentId, 'bdc_apartment_service_price_id'=>$bdc_apartment_service_price_id, 'type'=>1])->sum('coin');
        return $payment_detail + $logCoinDetail;
    }
}
