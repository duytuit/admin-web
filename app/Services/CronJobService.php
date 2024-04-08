<?php

namespace App\Services;

class CronJobService {
    public static function put($key, $value, $minutes) {
        return \Cache::store('redis')->put($key, $value, $minutes);
    }

    public static function get($key) {
        return \Cache::store('redis')->get($key);
    }
}