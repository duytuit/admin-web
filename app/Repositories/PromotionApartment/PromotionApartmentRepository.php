<?php
/*
 * create by tandc
 * */

namespace App\Repositories\PromotionApartment;

use App\Models\BdcV2PaymentDetail\PaymentDetail;
use App\Repositories\Eloquent\Repository;
use App\Util\Debug\Log;
use App\Models\PromotionApartment\PromotionApartment;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class PromotionApartmentRepository extends Repository
{
    function model()
    {
        return PromotionApartment::class;
    }

    /**
     * clearCache : xóa cache
     * @return boolean
     * */
    public static function clearCache(): bool
    {
//        $keyCache = "getPromotionApartment_" . $apartmentId . "_" . $service_price_id;
//        Cache::forget($keyCache);
        return true;
    }


    public static function getPromotionApartment($apartmentId, $bdc_apartment_service_price_id, $cycle_name)
    {
        $keyCache = "getPromotionApartment_" . $apartmentId . "_" . $bdc_apartment_service_price_id . "_" . $cycle_name;
//        Cache::forget($keyCache);
        return Cache::remember($keyCache, 5, function () use ($apartmentId, $bdc_apartment_service_price_id, $cycle_name) {
            $rs = PromotionApartment::where([
                'apartment_id' => $apartmentId,
                'service_price_id' => $bdc_apartment_service_price_id,
                'cycle_name' => $cycle_name,
            ])->first();
            return $rs;
        });
    }
}
