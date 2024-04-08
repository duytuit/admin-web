<?php

namespace App\Exceptions;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;

class QueueRedis
{
    public static function setItemForQueue($key, $data=[])
    {
       return self::queueSet($key, json_encode($data));
    }

    public static function getItemForQueue($key)
    {
        return json_decode(self::queuePop([$key]), true);
    }

    public static function queueSet($key, $data)
    {
        return Redis::command('rpush', [$key, [$data]]);
    }

    public static function queuePop($key=[])
    {
        return Redis::command('lpop', $key);
    }

    public static function setFlagCronjob($value)
    {
        return Cache::store('redis')->put(env('REDIS_PREFIX') . 'flag_cronjob', $value,5*60);
    }

    public static function getFlagCronjob()
    {
        return Cache::store('redis')->get(env('REDIS_PREFIX') . 'flag_cronjob');
    }
    public static function forgetFlagCronjob()
    {
        return Cache::store('redis')->forget(env('REDIS_PREFIX') . 'flag_cronjob');
    }
    public static function setFlagQueue($value)
    {
        return Cache::store('redis')->put(env('REDIS_PREFIX') . 'flag_queue', $value);
    }
    public static function getFlagQueue()
    {
        return Cache::store('redis')->get(env('REDIS_PREFIX') . 'flag_queue');
    }
    public static function forgetFlagQueue()
    {
        return Cache::store('redis')->forget(env('REDIS_PREFIX') . 'flag_queue');
    }


}