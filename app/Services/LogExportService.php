<?php

namespace App\Services;

use Illuminate\Support\Facades\Redis;

class LogExportService
{
    const EXPORT_APARTMENT   = 0;
    const EXPORT_DEBIT       = 1;
    const EXPORT_RECEIPT     = 2;
    const EXPORT_BILL        = 3;

    public static function queueSet($key, $data)
    {
      return Redis::command('rpush', [$key, [$data]]);
    }

    public static function queuePop($key = [])
    {
      return Redis::command('lpop', $key);
    }

    public static function setItemForQueueApartment( $data=[], $building_id, $errors = '')
    {
          $data = json_encode(array_merge(["files" => $data], ["error_logs" => $errors], ["bdc_building_id" => $building_id], ["type" => self::EXPORT_APARTMENT]));
          return self::queueSet(env('REDIS_QUEUE_PREFIX').'REDIS_LOG_EXPORT_QUEUE',$data);
    }

    public static function setItemForQueueDebit( $data=[], $building_id, $errors = '')
    {
          $data = json_encode(array_merge(["files" => $data], ["error_logs" => $errors], ["bdc_building_id" => $building_id], ["type" => self::EXPORT_DEBIT]));
          return self::queueSet(env('REDIS_QUEUE_PREFIX').'REDIS_LOG_EXPORT_QUEUE',$data);
    }

    public static function setItemForQueueReceipt( $data=[], $building_id, $errors = '')
    {
          $data = json_encode(array_merge(["files" => $data], ["error_logs" => $errors], ["bdc_building_id" => $building_id], ["type" => self::EXPORT_RECEIPT]));
          return self::queueSet(env('REDIS_QUEUE_PREFIX').'REDIS_LOG_EXPORT_QUEUE',$data);
    }

    public static function setItemForQueueBill( $data=[], $building_id, $errors = '')
    {
          $data = json_encode(array_merge(["files" => $data], ["error_logs" => $errors], ["bdc_building_id" => $building_id], ["type" => self::EXPORT_BILL]));
          return self::queueSet(env('REDIS_QUEUE_PREFIX').'REDIS_LOG_EXPORT_QUEUE',$data);
    }

    public static function getItemForQueue()
    {
        return json_decode( self::queuePop([env('REDIS_QUEUE_PREFIX').'REDIS_LOG_EXPORT_QUEUE']), true); 
    }

    public static function setItemBackQueue($data)
    {
      return self::queueSet(env('REDIS_QUEUE_PREFIX').'REDIS_LOG_EXPORT_QUEUE',$data);
    }
}