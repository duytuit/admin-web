<?php

namespace App\Services;

use Illuminate\Support\Facades\Redis;

class MaintenanceQueueService
{
    public static function setItemForQueue( $data=[])
    {
        return self::queueSet(env('REDIS_QUEUE_PREFIX').'REDIS_MAINTENANCE_QUEUE',json_encode($data));
    }

    public static function getItemForQueue()
    {
        // dump(env('REDIS_QUEUE_PREFIX').'REDIS_MAINTENANCE_QUEUE');
        return json_decode( self::queuePop([env('REDIS_QUEUE_PREFIX').'REDIS_MAINTENANCE_QUEUE']), true);
    }

    public static function queueSet($key, $data)
    {
        return Redis::command('rpush', [$key, [$data]]);
    }

    public static function queuePop($key=[])
    {
        return Redis::command('lpop', $key);
    }

}