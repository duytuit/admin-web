<?php
/*
 * create by tandc
 * */

namespace App\Repositories\BdcLockCyclename;

use App\Repositories\Eloquent\Repository;
use App\Util\Debug\Log;
use App\Models\BdcV2PaymentDetail\PaymentDetail;
use App\Models\LockCycleName\LockCycleName;

class BdcLockCyclenameRepository extends Repository
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
     * checkLock : kiểm tra đã khóa sổ action này chưa
     * @param int $buildingId
     * @param int $cycleName
     * @param string $action view, insert, update, delete, import, export
     * @return boolean true là đã khóa
     */

    public static function checkLock(int $buildingId, int $cycleName, string $action): bool
    {
        $lock_cycle = LockCycleName::where(['bdc_building_id' => $buildingId, 'cycle_name' => $cycleName, 'status' => 1])->first();
        if (!$lock_cycle) {
            return false;
        }
        $get_action = json_decode($lock_cycle->lock);
        if (in_array($action, $get_action)) {
            return true;
        }
        return false;
    }

}
