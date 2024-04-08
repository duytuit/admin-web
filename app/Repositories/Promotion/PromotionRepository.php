<?php
/*
 * create by tandc
 * */

namespace App\Repositories\Promotion;

use App\Repositories\Eloquent\Repository;
use App\Util\Debug\Log;
use App\Models\Promotion\Promotion;
use Illuminate\Support\Facades\Cache;

class PromotionRepository extends Repository
{
    function model()
    {
        return Promotion::class;
    }

    /**
     * clearCache : xóa cache
     * @return boolean
     * */
    public static function clearCache(): bool
    {
//        $keyCache = "getPromotionById_" . $apartmentId . "_" . $service_price_id;
//        Cache::forget($keyCache);
        return true;
    }


    public static function getPromotionById($id)
    {
        $keyCache = "getPromotionById_" . $id;
//        Cache::forget($keyCache);
        return Cache::remember($keyCache, 5, function () use ($id) {
            return Promotion::where([
                'id' => $id,
            ])->first();
        });
    }
}
