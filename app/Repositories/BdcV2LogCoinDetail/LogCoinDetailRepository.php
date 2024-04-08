<?php
/*
 * create by tandc
 * */

namespace App\Repositories\BdcV2LogCoinDetail;

use App\Helpers\dBug;
use App\Models\BdcReceipts\Receipts;
use App\Repositories\Eloquent\Repository;
use App\Util\Debug\Log;
use Illuminate\Support\Facades\Cache;
use App\Models\BdcV2LogCoinDetail\LogCoinDetail;
use App\Models\BdcV2PaymentDetail\PaymentDetail;
use Illuminate\Support\Facades\DB;

class LogCoinDetailRepository extends Repository
{
    function model()
    {
        return LogCoinDetail::class;
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
     * @param int $user_id
     * @param int $coin
     * @param int $type
     * @param $by
     * @param int $from_type
     * @param int $from_id
     * @param string $note
     * @return mixed
     */

    public static function createLogCoin(int $bdc_building_id,int $bdc_apartment_id, int $bdc_apartment_service_price_id, int $cycle_name, int $user_id, int $coin, int $type, $by, int $from_type, int $from_id,string $data = "", string $note = "")
    {
//        self::clearCache($apartmentId, $service_price_id);
        return LogCoinDetail::create([
            'bdc_building_id' => $bdc_building_id, // mã căn hộ
            'bdc_apartment_id' => $bdc_apartment_id, // mã căn hộ
            'bdc_apartment_service_price_id' => $bdc_apartment_service_price_id, // mã dịch vụ
            'cycle_name' => $cycle_name, // kỳ
            'user_id' => $user_id, // user thanh toán
            'coin' => $coin, // số tiền thanh toán
            'type' => $type, // 0: trừ coin, 1: cộng coin
            'by' => $by, // bởi user id admin nào, hoặc hạch toán tự động
            'note' => $note, // ghi chú
            'from_type' => $from_type, // từ nguồn nào, 1: nộp tiền thừa từ bảng reciept, 2: từ hạch toán tự động, 3: phân bổ từ ví A sang ví B, 4 hạch toán từ ví khác,  5 hủy phiếu thu, 6 điều chỉnh, 7: convert tiền thừa v1->v2, 8: back coin auto payment
            'from_id' => $from_id, // id từ nguồn đấy
            'data' => $data, // dữ liệu trước khi thêm hoặc sau khi thêm để kiểm tra
        ]);
    }

    /**
     * getLogCoinByBuilding : lấy thông tin chi tiết phân bổ coin
     * @param $buildingId integer
     * @return mixed
     * */

    public static function getLogCoinByBuilding($request,int $buildingId)
    {
        return LogCoinDetail::withTrashed()->where(function($query) use($request){
            if ($request->cycle_name) { 
                $query->where('cycle_name',$request->cycle_name);
            }
            })->where(function($query) use($request,$buildingId){
                $query->where('bdc_building_id',$buildingId);
            if ($request->bdc_service_id > 0) { 
                $query->whereHas('apartmentServicePrice', function ($query) use ($request,$buildingId) {
                        $query->where('bdc_service_id', $request->bdc_service_id);
                });
            }
            if (isset($request->bdc_service_id) && $request->bdc_service_id == 0) { 
                $query->where('bdc_apartment_service_price_id',$request->bdc_service_id);
            }
            if ($request->bdc_apartment_id) { 
                $query->where('bdc_apartment_id', $request->bdc_apartment_id);
            }
            if (isset($request->ip_place_id) && $request->ip_place_id) {
                $query->whereHas('apartment', function ($query) use ($request) {
                    $query->where('building_place_id', $request->ip_place_id);
                });
            }
          
        })->orderBy('created_at','desc');
    }

    /**
     * getSumPaidByCycleName : lấy tổng tiền đã đóng theo kỳ
     * @param int $apartmentId
     * @param mixed $service_price_id
     * @param mixed $cycle_name
     * @param bool $fromReceipt
     * @param bool $useCache
     * @return mixed
     */

    public static function getSumPaidLogCoinByCycleName(int $apartmentId, $service_price_id = false, $cycle_name = false, bool $fromReceipt = false, bool $useCache = true)
    {
        $keyCache = "getSumPaidLogCoinByCycleName_" . $apartmentId.'_'.$service_price_id.'_'.$cycle_name.'_'.$fromReceipt;
        if(!$useCache) Cache::forget($keyCache);
        return Cache::remember($keyCache, 5, function () use ($apartmentId, $service_price_id, $cycle_name, $fromReceipt) {
            $arrWhere = [
                'bdc_apartment_id' => $apartmentId,
            ];
            $service_price_id!==false&&$arrWhere["bdc_apartment_service_price_id"] = $service_price_id;
            $cycle_name!==false&&$arrWhere["cycle_name"] = $cycle_name;
            $rs = LogCoinDetail::where($arrWhere)->select(DB::raw('SUM(coin) as tong, type'));
            $fromReceipt&&$rs->whereIn("from_type", [1,5,6,9]); // 1 nộp tiền thừa từ bảng reciept, 5 hủy phiếu thu, 6 điều chỉnh, 9 chi trả cư dân
            $service_price_id!==false&&$rs->groupBy('bdc_apartment_id','bdc_apartment_service_price_id','type');
            $service_price_id===false&&$rs->groupBy('bdc_apartment_id','type');
            $rs = $rs->get();
            if (!$rs) return 0;
            $sum = 0;
            foreach ($rs as $item){
                if($item['type'] === 1)  $sum += $item["tong"];
                else $sum -= $item["tong"];
            }
            return $sum;
        });
    }

    public static function getPaidLogCoinByCycleName(int $apartmentId, $service_price_id = false, $cycle_name = false, bool $fromReceipt = false)
    {
        $arrWhere = [
            'bdc_apartment_id' => $apartmentId,
        ];
        $service_price_id!==false&&$arrWhere["bdc_apartment_service_price_id"] = $service_price_id;
        $cycle_name!==false&&$arrWhere["cycle_name"] = $cycle_name;
        $rs = LogCoinDetail::where($arrWhere)->select(DB::raw('*'));
        $fromReceipt&&$rs->whereIn("from_type", [1,5,6,9]); // 1 nộp tiền thừa từ bảng reciept, 5 hủy phiếu thu, 6 điều chỉnh, 9 chi trả cư dân
        $service_price_id!==false&&$rs->groupBy('bdc_apartment_id','bdc_apartment_service_price_id','type');
        $service_price_id===false&&$rs->groupBy('bdc_apartment_id','type');
        $rs = $rs->get();
        return $rs;
    }

    public static function getSumPaidLogCoinByCycleNameCus(int $apartmentId, $service_price_id = false, $cycle_name = false, bool $fromReceipt = false, $oper = "=", $useCache = true)
    {
        $keyCache = "getSumPaidLogCoinByCycleName_" . $apartmentId.'_'.$service_price_id.'_'.$cycle_name.'_'.$fromReceipt.'_'.$oper;
        if(!$useCache) Cache::forget($keyCache);
        return Cache::remember($keyCache, 5, function () use ($apartmentId, $service_price_id, $cycle_name, $fromReceipt, $oper) {
            $arrWhere = [
                'bdc_apartment_id' => $apartmentId,
            ];
            $service_price_id!==false&&$arrWhere["bdc_apartment_service_price_id"] = $service_price_id;
//            $fromReceipt&&$arrWhere["from_type"] = 1;
            $rs = LogCoinDetail::where($arrWhere)->select(DB::raw('SUM(coin) as tong, type'));
            if ($cycle_name) $rs->where("cycle_name", $oper, $cycle_name);
            $fromReceipt&&$rs->whereIn("from_type", [1,5,6,9]); // 1 nộp tiền thừa từ bảng reciept, 5 hủy phiếu thu, 6 điều chỉnh, 9 chi trả cư dân
            $service_price_id!==false&&$rs->groupBy('bdc_apartment_id','bdc_apartment_service_price_id','type');
            $service_price_id===false&&$rs->groupBy('bdc_apartment_id','type');
            $rs = $rs->get();
            if (!$rs) return 0;
            $sum = 0;
            foreach ($rs as $item){
                if($item['type'] === 1)  $sum += $item["tong"];
                else $sum -= $item["tong"];
            }
            return $sum;
        });
    }

    /**
     * getSumPaidLogCoinByCycleNameFormType : lấy tổng tiền đã đóng theo kỳ và from type
     * @param int $apartmentId
     * @param mixed $service_price_id
     * @param mixed $cycle_name
     * @param int $from_type
     * @param int $type
     * @param bool $useCache
     * @return mixed
     */

    public static function getSumPaidLogCoinByCycleNameFormType(int $apartmentId,int $service_price_id, $cycle_name,int $from_type,int $type, $useCache = true)
    {
        $keyCache = "getSumPaidLogCoinByCycleNameFormType_" . $apartmentId.'_'.$service_price_id.'_'.$cycle_name.'_'.$from_type.'_'.$type;
        if(!$useCache) Cache::forget($keyCache);
        return Cache::remember($keyCache, 5, function () use ($apartmentId, $service_price_id, $cycle_name, $from_type, $type) {
            $arrWhere = [
                'bdc_apartment_id' => $apartmentId,
                'type' => $type,
            ];
            $service_price_id!==false&&$arrWhere["bdc_apartment_service_price_id"] = $service_price_id;
            $from_type&&$arrWhere["from_type"] = $from_type;
            $cycle_name&&$arrWhere["cycle_name"] = $cycle_name;
            $rs = LogCoinDetail::where($arrWhere)->select(DB::raw('SUM(coin) as tong'));
            $service_price_id!==false&&$rs->groupBy('bdc_apartment_id','bdc_apartment_service_price_id');
            $service_price_id===false&&$rs->groupBy('bdc_apartment_id');
            $rs = $rs->first();
            if (!$rs || !isset($rs["tong"])) return 0;
            return $rs["tong"];
        });
    }
    public static function getPaidLogCoinByCycleNameFormType(int $apartmentId,int $service_price_id, $cycle_name,int $from_type,int $type)
    {
        $arrWhere = [
            'bdc_apartment_id' => $apartmentId,
            'type' => $type,
        ];
        $service_price_id!==false&&$arrWhere["bdc_apartment_service_price_id"] = $service_price_id;
        $from_type&&$arrWhere["from_type"] = $from_type;
        $cycle_name&&$arrWhere["cycle_name"] = $cycle_name;
        $rs = LogCoinDetail::where($arrWhere)->select(DB::raw('*'));
//        $service_price_id!==false&&$rs->groupBy('bdc_apartment_id','bdc_apartment_service_price_id');
//        $service_price_id===false&&$rs->groupBy('bdc_apartment_id');
        $rs = $rs->get();
        return $rs;
    }

    public static function getSumPaidLogCoinByCycleNameFormTypeCus(int $apartmentId,int $service_price_id, $cycle_name,int $from_type,int $type, $oper = "=", $useCache = true)
    {
        $keyCache = "getSumPaidLogCoinByCycleNameFormType_" . $apartmentId.'_'.$service_price_id.'_'.$cycle_name.'_'.$from_type.'_'.$type.'_'.$oper;
        if(!$useCache) Cache::forget($keyCache);
        return Cache::remember($keyCache, 5, function () use ($apartmentId, $service_price_id, $cycle_name, $from_type, $type, $oper) {
            $arrWhere = [
                'bdc_apartment_id' => $apartmentId,
                'type' => $type,
            ];
            $service_price_id!==false&&$arrWhere["bdc_apartment_service_price_id"] = $service_price_id;
            $from_type&&$arrWhere["from_type"] = $from_type;
            $rs = LogCoinDetail::where($arrWhere)->select(DB::raw('SUM(coin) as tong'));
            if ($cycle_name) $rs->where("cycle_name", $oper, $cycle_name);
            $service_price_id!==false&&$rs->groupBy('bdc_apartment_id','bdc_apartment_service_price_id');
            $service_price_id===false&&$rs->groupBy('bdc_apartment_id');
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

    public static function getSumPaidLogCoin(int $apartmentId,int $service_price_id, $cycle_name, $useCache = true)
    {
        $keyCache = "getSumPaidLogCoin_" . $apartmentId.'_'.$service_price_id.'_'.$cycle_name;
        if(!$useCache) Cache::forget($keyCache);
        return Cache::remember($keyCache, 5, function () use ($apartmentId, $service_price_id, $cycle_name, $useCache) {
            $sumCoin = self::getSumPaidLogCoinByCycleName($apartmentId, $service_price_id, $cycle_name, true); // tiền từ phiếu thu log coin
            // tiền phân bổ from_type = 3
            $addSumCoin = self::getSumPaidLogCoinByCycleNameFormType($apartmentId, $service_price_id,$cycle_name, 3, 1); // số coin từ phân bổ được cộng
            $SubSumCoin = self::getSumPaidLogCoinByCycleNameFormType($apartmentId, $service_price_id,$cycle_name, 3, 0); // số coin từ phân bổ bị trừ
            // tiền hạch toán từ ví A from_type = 4
//            $SumCoin_tuvi = LogCoinDetailRepository::getSumPaidLogCoinByCycleNameFormTypeIs4($apartmentId, false ,$cycle_name, $service_price_id); // số coin được hach toán từ ví A
            $SubSumCoin_hachtoan = self::getSumPaidLogCoinByCycleNameFormTypeIs4($apartmentId, $service_price_id,$cycle_name); // số coin bị trừ cho hạch toán dịch vụ
            return $sumCoin+($addSumCoin-$SubSumCoin)-$SubSumCoin_hachtoan;
        });
    }

    /**
     * getSumPaidLogCoinByCycleNameFormType : lấy tổng tiền đã đóng theo kỳ và from type
     * @param int $apartmentId
     * @param mixed $service_price_id
     * @param mixed $cycle_name
     * @param bool $from_id
     * @param bool $useCache
     * @return mixed
     */

    public static function getSumPaidLogCoinByCycleNameFormTypeIs4(int $apartmentId,$service_price_id, $cycle_name, $from_id = false, $useCache = true)
    {
        $keyCache = "getSumPaidLogCoinByCycleNameFormTypeIs4_" . $apartmentId.'_'.$service_price_id.'_'.$cycle_name.'_'.$from_id;
        if(!$useCache) Cache::forget($keyCache);
        return Cache::remember($keyCache, 5, function () use ($apartmentId, $service_price_id, $cycle_name, $from_id) {
            $arrWhere = [
                'bdc_apartment_id' => $apartmentId,
                'from_type' => 4,
                'type' => 0,
            ];
            $service_price_id!==false&&$arrWhere["bdc_apartment_service_price_id"] = $service_price_id;
            $from_id!==false&&$arrWhere["from_id"] = $from_id;
            $cycle_name&&$arrWhere["cycle_name"] = $cycle_name;
            $rs = LogCoinDetail::where($arrWhere)->select(DB::raw('SUM(coin) as tong'));
            $service_price_id!==false&&$rs->groupBy('bdc_apartment_id','bdc_apartment_service_price_id');
            $service_price_id===false&&$rs->groupBy('bdc_apartment_id');
            $rs = $rs->first();
            if (!$rs || !isset($rs["tong"])) return 0;
            return $rs["tong"];
        });
    }

    public static function getPaidLogCoinByCycleNameFormTypeIs4(int $apartmentId,$service_price_id, $cycle_name, $from_id = false)
    {
        $arrWhere = [
            'bdc_apartment_id' => $apartmentId,
            'from_type' => 4,
            'type' => 0,
        ];
        $service_price_id!==false&&$arrWhere["bdc_apartment_service_price_id"] = $service_price_id;
        $from_id!==false&&$arrWhere["from_id"] = $from_id;
        $cycle_name&&$arrWhere["cycle_name"] = $cycle_name;
        $rs = LogCoinDetail::where($arrWhere)->select(DB::raw('*'));
//        $service_price_id!==false&&$rs->groupBy('bdc_apartment_id','bdc_apartment_service_price_id');
//        $service_price_id===false&&$rs->groupBy('bdc_apartment_id');
        $rs = $rs->get();
        return $rs;
    }

    public static function getSumPaidLogCoinByCycleNameFormTypeIs4Cus(int $apartmentId,$service_price_id, $cycle_name, $from_id = false, $oper = "=", $useCache = true)
    {
        $keyCache = "getSumPaidLogCoinByCycleNameFormTypeIs4Cus_" . $apartmentId.'_'.$service_price_id.'_'.$cycle_name.'_'.$from_id.'_'.$oper;
        if(!$useCache) Cache::forget($keyCache);
        return Cache::remember($keyCache, 5, function () use ($apartmentId, $service_price_id, $cycle_name, $from_id, $oper) {
            $arrWhere = [
                'bdc_apartment_id' => $apartmentId,
                'from_type' => 4,
                'type' => 0,
            ];
            $service_price_id!==false&&$arrWhere["bdc_apartment_service_price_id"] = $service_price_id;
            $from_id!==false&&$arrWhere["from_id"] = $from_id;
            $rs = LogCoinDetail::where($arrWhere)->select(DB::raw('SUM(coin) as tong'));
            if ($cycle_name) $rs->where("cycle_name", $oper, $cycle_name);
            $service_price_id!==false&&$rs->groupBy('bdc_apartment_id','bdc_apartment_service_price_id');
            $service_price_id===false&&$rs->groupBy('bdc_apartment_id');
            $rs = $rs->first();
            if (!$rs || !isset($rs["tong"])) return 0;
            return $rs["tong"];
        });
    }

    /**
     * getDataByFromtype : lấy dữ liệu bởi form type
     * @param int $bdc_building_id
     * @param int $from_type
     * @param bool $type
     * @param bool $from_date
     * @param bool $to_date
     * @return mixed
     */

    public static function getDataByFromtype(int $bdc_building_id,int $from_type, $type = false, $from_date = false, $to_date = false,$bdc_apartment_id = null)
    {
        $arrWhere = [
            'bdc_building_id' => $bdc_building_id,
            'from_type' => $from_type,
        ];
        $type!==false&&$arrWhere["type"] = $type;
        $bdc_apartment_id!==null&&$arrWhere["bdc_apartment_id"] = $bdc_apartment_id;
        $rs = LogCoinDetail::where($arrWhere);
        if($from_date && $to_date){
            $rs->whereDate('created_at', '>=', $from_date);
            $rs->whereDate('created_at', '<=', $to_date);
        }
        return $rs->get();
    }

     /**
     * getDataByFromtype : lấy dữ liệu bởi form type
     * @param int $bdc_building_id
     * @param int $from_type
     * @param bool $type
     * @param bool $from_date
     * @param bool $to_date
     * @return mixed
     */

    public static function getDataByFromtypeFlowAllocation(int $bdc_building_id, $from_date = false, $to_date = false,$bdc_apartment_id = null)
    {
        $arrWhere = [
            'bdc_building_id' => $bdc_building_id
        ];
        $bdc_apartment_id!==null&&$arrWhere["bdc_apartment_id"] = $bdc_apartment_id;
        $rs = LogCoinDetail::where($arrWhere)->where(function($query){
            $query->where(['from_type' => 3, 'type' => 1]); // phân bổ
        });
        if($from_date && $to_date){
            $rs->whereDate('created_at', '>=', $from_date);
            $rs->whereDate('created_at', '<=', $to_date);
        }
        return $rs->get();
      
    }
      /**
     * getDataByFromtype : lấy dữ liệu bởi form type
     * @param int $bdc_building_id
     * @param int $from_type
     * @param bool $type
     * @param bool $from_date
     * @param bool $to_date
     * @return mixed
     */

    public static function getDataByFromtypeAutoAccounting(int $bdc_building_id, $from_date = false, $to_date = false,$bdc_apartment_id = null)
    {
        $arrWhere = [
            'bdc_building_id' => $bdc_building_id
        ];
        $bdc_apartment_id!==null&&$arrWhere["bdc_apartment_id"] = $bdc_apartment_id;
        $rs = LogCoinDetail::where($arrWhere)->where(function($query){
            $query->where(['from_type' => 2, 'type' => 0]); // hạch toán tự động
        });
        if($from_date && $to_date){
            $rs->whereDate('created_at', '>=', $from_date);
            $rs->whereDate('created_at', '<=', $to_date);
        }
        return $rs->get();
    }

    /**
     * getDataByFromtype : lấy dữ liệu theo thời gian
     * @param int $bdc_building_id
     * @param bool $from_date
     * @param bool $to_date
     * @return mixed
     */

    public static function getDataByFromDate(int $bdc_building_id, $from_date = false, $to_date = false,$bdc_apartment_id = null)
    {
        $arrWhere = [
            'bdc_building_id' => $bdc_building_id,
        ];
        $bdc_apartment_id!==null&&$arrWhere["bdc_apartment_id"] = $bdc_apartment_id;
        $rs = LogCoinDetail::where($arrWhere)->where('from_type','<>',7);
        if($from_date && $to_date){
            $rs->whereDate('created_at', '>=', $from_date);
            $rs->whereDate('created_at', '<=', $to_date);
        }
        return $rs->get();
    }

    public static function getCountByFromtypeFromId(int $bdc_apartment_id,int $service_price_id,int $from_type, $from_id)
    {
        $arrWhere = [
            'bdc_apartment_id' => $bdc_apartment_id,
            'from_type' => $from_type,
            'from_id' => $from_id,
            'bdc_apartment_service_price_id' => $service_price_id,
        ];
        $rs = LogCoinDetail::where($arrWhere)->select(DB::raw('SUM(coin) as tong'))->first();
        if (!$rs || !isset($rs["tong"])) return 0;
        return $rs["tong"];
    }

    public static function getDataByFromtypeFromId(int $bdc_apartment_id,int $service_price_id,int $from_type, $from_id)
    {
        $arrWhere = [
            'bdc_apartment_id' => $bdc_apartment_id,
            'from_type' => $from_type,
            'from_id' => $from_id,
            'bdc_apartment_service_price_id' => $service_price_id,
        ];
       // dbug::trackingPhpErrorV2($arrWhere);
        return LogCoinDetail::where($arrWhere)->get();
    }

    public static function getDataByFromId(int $bdc_apartment_id,int $from_type, $from_id)
    {
        $arrWhere = [
            'bdc_apartment_id' => $bdc_apartment_id,
            'from_type' => $from_type,
            'from_id' => $from_id,
        ];
        return LogCoinDetail::where($arrWhere)->get();
    }
    public static function sum_coin($from_id)
    {
        $arrWhere = [
            'from_type' => 1,
            'from_id' => $from_id,
        ];
        return LogCoinDetail::where($arrWhere)->sum('coin');
    }
    public static function sum_coin_by_accounting($from_id)
    {
        $arrWhere = [
            'from_type' => 4,
            'note' => 'v1->v2-'.$from_id,
        ];
        return LogCoinDetail::where($arrWhere)->sum('coin');
    }

    public static function get_by_from_id_accounting($from_id)
    {
        $arrWhere = [
            'from_type' => 4,
            'note' => 'v1->v2-'.$from_id,
        ];
        return LogCoinDetail::where($arrWhere)->get();
    }

    public static function getCountTienthuaByRecieptid(int $reciept_id, int $service_price_id)
    {
        $arrWhere = [
            'from_id' => $reciept_id,
            'from_type' => 1,
            'bdc_apartment_service_price_id' => $service_price_id,
        ];
        $rs = LogCoinDetail::where($arrWhere)->select(DB::raw('SUM(coin) as tong'));;
        $rs = $rs->first();
        if (!$rs || !isset($rs["tong"])) return 0;
        return $rs["tong"];
    }

    public static function getDataById(int $id)
    {
        $arrWhere = [
            'id' => $id,
        ];
        return LogCoinDetail::where($arrWhere)->first();
    }

    public static function getDataByIdAndFromType(int $id)
    {
        $arrWhere = [
            'id' => $id,
            'from_type' => 1,
        ];
        return LogCoinDetail::where($arrWhere)->first();
    }
    public static function getDataByIdAndFromTypeV2(int $id)
    {
        $arrWhere = [
            'id' => $id,
            'from_type' => 1,
        ];
        return LogCoinDetail::where($arrWhere)->sum('coin');
    }
    public static function check_accounting_log_coin(int $reciept_id)
    {
        return LogCoinDetail::where(['note'=>$reciept_id,'from_type'=>4])->first();
    }
    public static function get_accounting_source(int $reciept_id,$payment_detail)
    {
        $log_coin =  LogCoinDetail::find($payment_detail->bdc_log_coin_id);
        if(!$log_coin){
            $log_coin = LogCoinDetail::where(['note'=>$reciept_id, 'from_id'=>$payment_detail->bdc_apartment_service_price_id,'from_type'=>4])->first();
        }
        return $log_coin;
    }
    public static function get_accounting_source_service_apartment_id(int $reciept_id, int $service_price_id)
    {
        return LogCoinDetail::where(['note'=>$reciept_id, 'bdc_apartment_service_price_id'=>$service_price_id,'from_type'=>6])->first();
    }
    public static function get_accounting_source_service_apartment_id_by_payment_slip(int $reciept_id, int $service_price_id)
    {
        return LogCoinDetail::where(['note'=>$reciept_id, 'bdc_apartment_service_price_id'=>$service_price_id,'from_type'=>9])->first();
    }

}
