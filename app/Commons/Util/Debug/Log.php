<?php
/*
 * create by tandc
 * */

namespace App\Commons\Util\Debug;
//use DebugBar;
use Barryvdh\Debugbar\Facade as DebugBar;
use App\Commons\Util\Redis;

class Log
{
    public static $prefix = "log_debug_";
    public static $timeExpire = 2 * 24 * 60 * 60;

    public static function info($table, $mess): bool
    {
        DebugBar::info($mess);
        $table && self::log($table, $mess);
        return true;
    }

    public static function error($table, $mess): bool
    {
        DebugBar::error($mess);
        $table && self::log($table, $mess);
        return true;
    }

    public static function warning($table, $mess): bool
    {
        DebugBar::warning($mess);
        $table && self::log($table, $mess);
        return true;
    }

    public static function log($table, $mess): bool
    {
        if (!$table) return false;
        $time = date('H:i:s Y-m-d');
        $isString = is_string($mess);
        $data = [
            "time" => $time,
            "mess" => $mess,
            "isString" => $isString,
        ];
        $milliseconds = (int)floor(microtime(true) * 1000);
        $keyRedis = self::$prefix . $table . '_' . $milliseconds;
        Redis::zAdd(self::$prefix . $table, $milliseconds, $keyRedis); // add key to list
        Redis::setAndExpire($keyRedis, $data, self::$timeExpire); // set value log
        $count = Redis::zCOUNT(self::$prefix . $table, $milliseconds - self::$timeExpire * 1000 * 1000, $milliseconds);
        if ($count > 5000) {
            $dataClear = Redis::zRANGEBYSCORE(self::$prefix . $table, $milliseconds - self::$timeExpire * 1000 * 1000, $milliseconds);
            if($dataClear) {
                $key = $dataClear[0];
                Redis::del($key);
                Redis::zRem(self::$prefix . $table, $key);
            }
        }
        return true;
    }

    public static function getAllKeyLog($table)
    {
        $milliseconds = (int) floor(microtime(true) * 1000);
        $data = Redis::zRANGEBYSCORE(self::$prefix . $table, $milliseconds - self::$timeExpire * 1000, $milliseconds);
        if (!$data) return false;
        return $data;
    }

    public static function getLog($table, $key)
    {
        $getData = Redis::get($key);
        if (!$getData) {
            Redis::zRem(self::$prefix . $table, $key);
        }
        return $getData;
    }

    public static function clearLog($table): bool
    {
        $allKey = Redis::zRANGE(self::$prefix . $table, 0, -1);
        if (!$allKey) return false;
        foreach ($allKey as $v) {
            Redis::del($v);
            Redis::zRem(self::$prefix . $table, $v);
        }
        Redis::del(self::$prefix . $table);
        return true;
    }

    public static function dump($data)
    {
        echo '<pre>';
        print_r(\GuzzleHttp\json_encode($data));
        echo '</pre>';
    }
}


