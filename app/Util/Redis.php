<?php
/*
 * create by tandc
 * */
namespace App\Util;

use Illuminate\Support\Facades\Redis as RedisLaravel;

class Redis
{
    public static function sAdd($key, $value)
    {
        return RedisLaravel::command('SADD', [$key, $value]);
    }

    public static function sRem($key, $value)
    {
        return RedisLaravel::command('SREM', [$key, $value]);
    }

    public static function sMembers($key)
    {
        return RedisLaravel::command('SMEMBERS', [$key]);
    }

    public static function sCard($key)
    {
        return RedisLaravel::command('SCARD', [$key]);
    }

    public static function sIsMember($key, $value)
    {
        return RedisLaravel::command('SISMEMBER', [$key, $value]);
    }

    public static function get($key)
    {
        $rs = RedisLaravel::command('GET', [$key]);
        return unserialize($rs);
    }

    public static function setAndExpire($key, $value, $expire)
    {
        return RedisLaravel::command('SETEX', [$key, $expire, serialize($value)]);
    }

    public static function set($key, $value)
    {
        return RedisLaravel::command('SET', [$key, serialize($value)]);
    }

    public static function del($key)
    {
        return RedisLaravel::command('DEL', [$key]);
    }

    public static function getLenList($key)
    {
        return RedisLaravel::command('LLEN', [$key]);
    }

    public static function getDataList($key, $start, $end)
    {
        return RedisLaravel::command('LRANGE', [$key, $start, $end]);
    }

    public static function zAdd($key, $score, $value)
    {
        return RedisLaravel::command('ZADD', [$key, $score, $value]);
    }

    public static function zRANGE($key, $min, $max)
    {
        return RedisLaravel::command('ZRANGE', [$key, $min, $max]);
    }

    public static function zRANGEBYSCORE($key, $min, $max)
    {
        return RedisLaravel::command('ZRANGEBYSCORE', [$key, $min, $max]);
    }

    public static function zRem($key, $value)
    {
        return RedisLaravel::command('ZREM', [$key, $value]);
    }

    public static function zCOUNT($key, $min, $max)
    {
        return RedisLaravel::command('ZCOUNT', [$key, $min, $max]);
    }
}
