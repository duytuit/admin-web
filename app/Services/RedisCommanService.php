<?php

namespace App\Services;

use Illuminate\Support\Facades\Redis;

class RedisCommanService
{
    public static function queueSet($key, $data)
    {
        return Redis::command('rpush', [$key, [$data]]);
    }

    public static function queuePop($key = [])
    {
        return Redis::command('lpop', $key);
    }

    public static function setKey($key, $data)
    {
        return Redis::command('set', [$key, $data]);
    }

    public static function getKey($key)
    {
        return Redis::command('get', [$key]);
    }

    public static function delKey($key)
    {
        return Redis::command('del', [$key]);
    }

    public static function exitsKey($key)
    {
        return Redis::command('exists', $key);
    }

    public static function queueRange($key, $start, $end)
    {
        return Redis::command('lrange', [$key, $start, $end]);
    }
}
