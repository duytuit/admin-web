<?php
/*
 * create by tandc
 * */

namespace App\Repositories\BdcCoin;

use App\Exceptions\QueueRedis;
use App\Helpers\dBug;
use App\Repositories\BdcV2DebitDetail\DebitDetailRepository;
use App\Repositories\BdcV2LogCoinDetail\LogCoinDetailRepository;
use App\Repositories\CronJobManager\CronJobManagerRepository;
use App\Repositories\Eloquent\Repository;
use Illuminate\Support\Facades\Cache;
use App\Models\BdcCoin\Coin;
use Illuminate\Support\Facades\DB;

use function Clue\StreamFilter\fun;

class BdcCoinRepository extends Repository
{
    function model()
    {
        return Coin::class;
    }

    /**
     * clearCache : xóa cache
     * @param $apartmentId integer
     * @param $service_price_id integer
     * @return boolean
     * */
    public static function clearCache(int $apartmentId, int $service_price_id): bool
    {
        $keyCache = "getCoinBy_" . $apartmentId . "_" . $service_price_id;
        $keyCache2 = "getCoinTotal_" . $apartmentId;
        Cache::forget($keyCache);
        Cache::forget($keyCache2);
        return true;
    }

      /**
     * getCoin : lấy thông tin coin hiện tại
     * @param $apartmentId integer
     * @param $service_price_id integer
     * @return mixed
     * */

    public static function getCoin(int $apartmentId, int $service_price_id)
    {
        $rs = Coin::where([
            'bdc_apartment_id' => $apartmentId,
            'bdc_apartment_service_price_id' => $service_price_id,
        ])->first();
        if (!$rs) return null;
        return $rs;
        // $keyCache = "getCoinBy_" . $apartmentId . "_" . $service_price_id;
        // return Cache::remember($keyCache, 24 * 60 * 60, function () use ($apartmentId, $service_price_id) {
        //     $rs = Coin::where([
        //         'bdc_apartment_id' => $apartmentId,
        //         'bdc_apartment_service_price_id' => $service_price_id,
        //     ])->first();
        //     if (!$rs) return null;
        //     return (object)$rs->toArray();
        // });
    }

    /**
     * getCoinByBuilding : lấy thông tin coin theo tòa
     * @param $buildingId integer
     * @return mixed
     * */

    public static function getCoinByBuilding($request,int $buildingId)
    {
        return Coin::where(['bdc_building_id' => $buildingId])->where('coin','>',0)->where(function($query) use ($request){
            if ($request->bdc_apartment_id) { 
                $query->where('bdc_apartment_id', $request->bdc_apartment_id);
            }
            if (isset($request->ip_place_id) && $request->ip_place_id) {
                $query->whereHas('apartment', function ($query) use ($request) {
                    $query->where('building_place_id', $request->ip_place_id);
                });
            }
            if ($request->bdc_service_id) { 
                $query->whereHas('apartmentServicePrice', function ($query) use ($request) {
                    if ($request->bdc_service_id) { 
                        $query->where('bdc_service_id', $request->bdc_service_id);
                    }
                });
            }
        })->orderBy('bdc_apartment_id');
    }

    /**
     * getCoinByBuilding : lấy thông tin coin theo tòa
     * @param $buildingId integer
     * @return mixed
     * */

    public static function getCoinByApartmentId_v2(int $apartmentId)
    {
        return Coin::where('coin','>',0)->where(function($query) use ($apartmentId){
            if ($apartmentId) { 
                $query->where('bdc_apartment_id', $apartmentId);
            }
        })->get();
    }
    const DIEN = 5;
    const NUOC = 3;
    const DICHVU = 2;
    const PHUONG_TIEN = 4;

    /**
     * getCoinByBuilding : lấy ra tiền thừa theo loại dịch vụ
     * @param $buildingId integer
     * @return mixed
     * */

    public static function getCoinByTypeService($apartmentId)
    {
        $service_manager = Coin::select(DB::raw('sum(coin) as coin'))->where(['bdc_apartment_id' => $apartmentId])->whereHas('apartmentServicePrice',function($query){
            $query->whereHas('service' ,function($query){
                $query->where('type', 2);
            });
         })->groupBy('bdc_apartment_id')->first();
         $service_vehicle = Coin::select(DB::raw('sum(coin) as coin'))->where(['bdc_apartment_id' => $apartmentId])->whereHas('apartmentServicePrice',function($query){
            $query->whereHas('service' ,function($query){
                $query->where('type', 4);
            });
         })->groupBy('bdc_apartment_id')->first();
         $service_electric = Coin::select(DB::raw('sum(coin) as coin'))->where(['bdc_apartment_id' => $apartmentId])->whereHas('apartmentServicePrice',function($query){
            $query->whereHas('service' ,function($query){
                $query->where('type', 5);
            });
         })->groupBy('bdc_apartment_id')->first();
         $service_water = Coin::select(DB::raw('sum(coin) as coin'))->where(['bdc_apartment_id' => $apartmentId])->whereHas('apartmentServicePrice',function($query){
            $query->whereHas('service' ,function($query){
                $query->where('type', 3);
            });
         })->groupBy('bdc_apartment_id')->first();
        return [
            'Phí quản lý' => @$service_manager['coin'],
            'Phí phương tiện' => @$service_vehicle['coin'],
            'Phí nước' => @$service_water['coin'],
            'Phí điện' => @$service_electric['coin'],
        ];
    }
    /**
     * getCoinByApartment : lấy thông tin coin theo căn hộ
     * @param $apartmentId integer
     * @return mixed
     * */

    public static function getCoinByApartment(int $apartmentId)
    {
        return Coin::select('bdc_building_id','bdc_apartment_id','bdc_apartment_service_price_id',  DB::raw('sum(coin) as coin'))->where(['bdc_apartment_id' => $apartmentId])->groupBy('bdc_apartment_id','bdc_apartment_service_price_id')->get();
    }

    /**
     * getCoin : lấy thông tin coin hiện tại tất cả ví
     * @param $apartmentId integer
     * @return mixed
     * */

    public static function getCoinTotal(int $apartmentId, $useCache = true)
    {
        // $keyCache = "getCoinTotal_" . $apartmentId;
        // if(!$useCache) Cache::forget($keyCache);
       // return Cache::remember($keyCache, 24 * 60 * 60, function () use ($apartmentId) {
            $rs = Coin::where([
                'bdc_apartment_id' => $apartmentId,
            ])->select(DB::raw('SUM(coin) as tong'))->groupBy('bdc_apartment_id')->first();
            if (!$rs || !isset($rs["tong"])) return 0;
            return $rs["tong"];
       /// });

    }

     /**
     * getCoin : lấy thông tin coin hiện tại tất cả ví
     * @param $apartmentId integer
     * @return mixed
     * */

    public static function getCoinTotalByBuilding(int $buildingId,$request)
    {
        return  Coin::where('bdc_building_id', $buildingId )->where(function($query) use ($request){
            if ($request->bdc_apartment_id) { 
                $query->where('bdc_apartment_id', $request->bdc_apartment_id);
            }
            if ($request->bdc_service_id) { 
                $query->whereHas('apartmentServicePrice', function ($query) use ($request) {
                  
                    if ($request->bdc_service_id) { 
                        $query->where('bdc_service_id', $request->bdc_service_id);
                    }
                });
            }
        })->sum('coin');
    }

    /**
     * getCoin : lấy thông tin coin hiện tại tất cả ví
     * @param $apartmentId integer
     * @return mixed
     * */

    public static function getCoinTotalByTypeService(int $buildingId,$request)
    {
        return  Coin::where('bdc_building_id', $buildingId )->where(function($query) use ($request){
            if ($request->bdc_apartment_id || $request->bdc_service_id) { 
                $query->whereHas('apartmentServicePrice', function ($query) use ($request) {
                    if ($request->bdc_apartment_id) { 
                        $query->where('bdc_apartment_id', $request->bdc_apartment_id);
                    }
                    if ($request->bdc_service_id) { 
                        $query->where('bdc_service_id', $request->bdc_service_id);
                    }
                });
            }
        })->sum('coin');
    }

    /**
     * updateCoin : cập nhật lại số coin
     * @param $buildingId integer
     * @param $apartmentId integer
     * @param $service_price_id integer
     * @param $coin integer
     * @return boolean
     * */

    public static function updateCoin(int $buildingId, int $apartmentId, int $service_price_id, int $coin): bool
    {
        Coin::updateOrInsert(['bdc_apartment_id' => $apartmentId, 'bdc_apartment_service_price_id' => $service_price_id], [
            'bdc_apartment_id' => $apartmentId,
            'bdc_apartment_service_price_id' => $service_price_id,
            'bdc_building_id' => $buildingId,
            'coin' => intval($coin),
        ]);
        self::clearCache($apartmentId, $service_price_id);
        return true;
    }

    /**
     * addCoin : Thêm coin cho 1 ngăn ví nào đó
     * @param $buildingId integer // mã tòa
     * @param int $bdc_apartment_id // mã căn hộ
     * @param $service_price_id integer // mã dịch vụ
     * @param int $cycle_name // mã kỳ lúc thực hiện giao dịch này
     * @param int $user_id // user_id chủ căn hộ
     * @param $coin integer // số tiền công thêm. ví dụ muốn cộng thêm 10.000 thì truyền 10000 vào
     * @param $by // đẩy userid nếu bởi admin nào, hoặc auto: hạch toán tự động
     * @param int $from_type //từ nguồn nào, 1: nộp tiền thừa từ bảng reciept, 2: từ hạch toán tự động, 3: phân bổ từ ví A sang ví B, 4 hạch toán từ ví khác, 5 hủy phiếu thu, 6 điều chỉnh,7: convert tiền thừa v1->v2, 8: back coin auto payment, 9 trả lại tiền cho khách từ ví
     * @param int $from_id //id từ nguồn đấy. ví dụ từ reciept thì đẩy reciept_id vào từ ngăn ví khác thì đẩy id bảng coin vào
     * @param string $note // lý do thêm coin nếu có
     * @return array
     */

    public static function addCoin(int $buildingId, int $bdc_apartment_id, int $service_price_id, int $cycle_name, int $user_id, int $coin, $by, int $from_type, int $from_id, string $note = ""): array
    {

        $infoCoin = self::getCoin($bdc_apartment_id, $service_price_id);
        $coinUpdate = intval($coin);
        if ($infoCoin && $infoCoin->coin) {
            $coinUpdate = $infoCoin->coin + intval($coin);
        }

        $rs = self::updateCoin($buildingId, $bdc_apartment_id, $service_price_id, $coinUpdate);

        $dataLog = [
            "coinBefore" => $infoCoin,
            "coinUpdate" => $coinUpdate,
            "rs" => $rs,
        ];

        $log = LogCoinDetailRepository::createLogCoin($buildingId, $bdc_apartment_id, $service_price_id, $cycle_name, $user_id, $coin, 1, $by, $from_type, $from_id, \GuzzleHttp\json_encode($dataLog), $note);

        QueueRedis::setItemForQueue('add_queue_stat_payment_', [
            "apartmentId" => $bdc_apartment_id,
            "service_price_id" => $service_price_id,
            "cycle_name" => $cycle_name,
        ]);

        if(!$rs){
            return [
                "status" => 1, // 0 là thành công, còn lại là thất bại
                "mess" => "Thêm coin thất bại!"
            ];
        }

        return [
            "status" => 0, // 0 là thành công, còn lại là thất bại
            "mess" => "Thành công!",
            "log" => $log&&isset($log->id) ? $log->id : 0,
        ];
    }

    /**
     * subCoin : Trừ coin cho 1 ngăn ví nào đó
     * @param $buildingId integer // mã tòa
     * @param int $bdc_apartment_id // mã căn hộ
     * @param $service_price_id integer // mã dịch vụ
     * @param int $cycle_name // mã kỳ lúc thực hiện giao dịch này
     * @param int $user_id // user_id chủ căn hộ
     * @param $coin integer // số tiền trừ. ví dụ muốn trừ 10.000 thì truyền 10000 vào
     * @param $by // đẩy userid nếu bởi admin nào, hoặc auto: hạch toán tự động
     * @param int $from_type //từ nguồn nào, 1: nộp tiền thừa từ bảng reciept, 2: từ hạch toán tự động, 3: phân bổ từ ví A sang ví B, 4 hạch toán từ ví khác, 5 hủy phiếu thu, 6 điều chỉnh,7: convert tiền thừa v1->v2, 8: back coin auto payment, 9 trả lại tiền cho khách từ ví
     * @param int $from_id //id từ nguồn đấy. ví dụ từ reciept thì đẩy reciept_id vào từ ngăn ví khác thì đẩy bdc_apartment_service_price_id bảng coin vào
     * @param string $note // lý do trừ coin nếu có
     * @return array
     */

    public static function subCoin(int $buildingId, int $bdc_apartment_id, int $service_price_id, int $cycle_name, int $user_id, int $coin, $by, int $from_type, int $from_id, string $note = ""): array
    {

        $infoCoin = self::getCoin($bdc_apartment_id, $service_price_id);
     
        if (!$infoCoin || !isset($infoCoin->coin) || isset($infoCoin->coin) && $infoCoin->coin < intval($coin)) {
            //dBug::trackingPhpErrorV2($infoCoin->coin.'|'. $bdc_apartment_id.'|'.  $service_price_id.'|'.  intval($coin).'_________');
            return [
                "status" => 1, // 0 là thành công, còn lại là thất bại
                "mess" => "Không đủ coin để thực hiện giao dịch này!"
            ];
        }

        $coinUpdate = $infoCoin->coin - intval($coin);
        //dBug::trackingPhpErrorV2($infoCoin->coin.'|'. $bdc_apartment_id.'|'.  $service_price_id.'|'.  intval($coin));
        $rs = self::updateCoin($buildingId, $bdc_apartment_id, $service_price_id, $coinUpdate);
       
        $dataLog = [
            "coinBefore" => $infoCoin,
            "coinUpdate" => $coinUpdate,
            "rs" => $rs,
        ];
      
        $log = LogCoinDetailRepository::createLogCoin($buildingId, $bdc_apartment_id, $service_price_id, $cycle_name, $user_id, $coin, 0, $by, $from_type, $from_id, \GuzzleHttp\json_encode($dataLog), $note);

        QueueRedis::setItemForQueue('add_queue_stat_payment_', [
            "apartmentId" => $bdc_apartment_id,
            "service_price_id" => $service_price_id,
            "cycle_name" => $cycle_name,
        ]);

        if($from_type === 4){
            QueueRedis::setItemForQueue('add_queue_stat_payment_', [
                "apartmentId" => $bdc_apartment_id,
                "service_price_id" => $from_id,
                "cycle_name" => $cycle_name,
            ]);
        }

        if(!$rs){
         
            return [
                "status" => 2, // 0 là thành công, còn lại là thất bại
                "mess" => "Cập nhật coin thất bại!"
            ];
        }

        return [
            "status" => 0, // 0 là thành công, còn lại là thất bại
            "mess" => "Thành công!",
            "log" => $log&&isset($log->id) ? $log->id : 0,
        ];
    }
}
