<?php
/*
 * create by tandc
 * */

namespace App\Util\Debug;

use App\Exceptions\QueueRedis;
use App\Repositories\BdcLockCyclename\BdcLockCyclenameRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use League\Flysystem\Exception;

class LogAction
{
    private static $instances = [];
    private static $requestId = false;
    private static $userId = 0;
    private static $buildingId = 0;

    protected function __generateRandomString($length = 10)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    protected function __construct()
    {
        $milliseconds = (int)floor(microtime(true) * 1000);
        $random = self::__generateRandomString(4);
        self::$requestId = $milliseconds . $random;
        try {
            $user = Auth::user();
            $userId = $user ? $user->id : 0;
        } catch (Exception $e) {
            $userId = 0;
        }
        if ($userId) {
            $building_id = Cache::store('redis')->get(env('REDIS_PREFIX') . '_DXMB_BUILDING_ACTIVE' . $userId);
            self::$buildingId = $building_id ? $building_id : 0;
        }
        self::$userId = $userId;
    }

    public static function getInstance(): LogAction
    {
        $cls = static::class;
        if (!isset(self::$instances[$cls])) {
            self::$instances[$cls] = new static();
        }

        return self::$instances[$cls];
    }

    /**
     * logToolAction : ghi lại log những thao tác trên tool admin
     * @param int $toolId
     * @param string $action view, insert, update, delete, import, export
     * @param string $url
     * @param mixed $param
     * @param mixed $buildingId
     * @param mixed $cycleName
     * @return array
     */
    public static function logToolAction(int $toolId, string $action, string $url, $param, $buildingId = false, $cycleName = false): array
    {
        $Log = LogAction::getInstance();
        $checkLock = $cycleName && BdcLockCyclenameRepository::checkLock($buildingId ? $buildingId : $Log::$buildingId, $cycleName, $action);
        QueueRedis::setItemForQueue('add_log_action', [
            "type" => 1,
            "toolId" => $toolId,
            "action" => $action,
            "by" => $Log::$userId,
            "time" => time(),
            "url" => $url,
            "param" => $param,
            "buildingId" => $buildingId ? $buildingId : $Log::$buildingId,
            "status" => 1,
            "requestId" => $Log::$requestId,
        ]);
        if($checkLock){
            self::logToolActionFail("Kỳ này đã bị khóa sổ, không được thao tác!");
            return [
              "status" => 1,
              "mess" => "Kỳ này đã bị khóa sổ, không được thao tác!"
            ];
        }
        return [
            "status" => 0,
            "mess" => "Thành công!"
        ];
    }

    /**
     * logToolActionFail : nếu lỗi thì log lại
     * @param string $mess
     */
    public static function logToolActionFail(string $mess)
    {
        $Log = LogAction::getInstance();
        QueueRedis::setItemForQueue('add_log_action', [
            "type" => 2,
            "mess" => $mess,
            "requestId" => $Log::$requestId,
        ]);
    }

    /**
     * logToolAction : ghi lại log những thao tác trên tool admin
     * @param string $table
     * @param string $action insert, delete, update
     * @param $rowId
     * @param $dataOld
     * @param $dataNew
     * @param string $sql
     * @param mixed $buildingId
     * @param mixed $cycleName
     * @return array
     */
    public static function logDatabaseAction(string $table, string $action, $rowId, $dataOld, $dataNew, string $sql, $buildingId = false, $cycleName = false): array
    {
        $Log = LogAction::getInstance();
        $checkLock = $cycleName && BdcLockCyclenameRepository::checkLock($buildingId ? $buildingId : $Log::$buildingId, $cycleName, $action);
        if($checkLock) {
            self::logToolActionFail("Kỳ này đã bị khóa sổ, không được thao tác!");
            return [
                "status" => 1,
                "mess" => "Kỳ này đã bị khóa sổ, không được thao tác!"
            ];
        }
        QueueRedis::setItemForQueue('add_log_action', [
            "type" => 3,
            "table" => $table,
            "action" => $action,
            "rowId" => $rowId,
            "dataOld" => $dataOld,
            "dataNew" => $dataNew,
            "sql" => $sql,
            "by" => $Log::$userId,
            "time" => time(),
            "buildingId" => $buildingId ? $buildingId : $Log::$buildingId,
            "requestId" => $Log::$requestId,
        ]);
        return [
            "status" => 0,
            "mess" => "Thành công!"
        ];
    }
}


