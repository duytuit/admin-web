<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Redis;
use OpenApi\StaticAnalyser;

class RedisHelper
{
    public static function createKey($key, $value)
    {
        return sprintf($key, $value);
    }

    public static function get($key)
    {
        if(Redis::exists($key)) {
            return json_decode(Redis::get($key));
        }
        return null;
    }

    public static function set($key, $data)
    {
        Redis::set($key, $data);
    }

    public static function search($key, $values = [])
    {
        if(Redis::exists($key)) {
            return collect(json_decode(Redis::get($key)));
        }
        return collect([]);
    }

    public static function delete($key, $multip = false)
    {
        if($multip) {
            Redis::del(Redis::keys("$key:*"));
        } else {
            Redis::del($key);
        }
    }
}
