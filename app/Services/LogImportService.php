<?php

namespace App\Services;

use Illuminate\Support\Facades\Redis;

class LogImportService
{
    const IMPORT_APARTMENT   = 0;
    const IMPORT_RESIDENT    = 1;
    const IMPORT_VEHICLE     = 2;
    const IMPORT_VEHICLECARD = 3;
    const IMPORT_DEBIT       = 4;
    const IMPORT_TASK        = 5;

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
          $data = json_encode(array_merge(["files" => $data], ["error_logs" => $errors], ["bdc_building_id" => $building_id], ["type" => self::IMPORT_APARTMENT]));
          return self::queueSet(env('REDIS_QUEUE_PREFIX').'REDIS_LOG_IMPORT_QUEUE',$data);
    }

    public static function setItemForQueueResident( $data=[], $building_id, $errors = '')
    {
          $data = json_encode(array_merge(["files" => $data], ["error_logs" => $errors], ["bdc_building_id" => $building_id], ["type" => self::IMPORT_RESIDENT]));
          return self::queueSet(env('REDIS_QUEUE_PREFIX').'REDIS_LOG_IMPORT_QUEUE',$data);
    }

    public static function setItemForQueueVehicle( $data=[], $building_id, $errors = '')
    {
          $data = json_encode(array_merge(["files" => $data], ["error_logs" => $errors], ["bdc_building_id" => $building_id], ["type" => self::IMPORT_VEHICLE]));
          return self::queueSet(env('REDIS_QUEUE_PREFIX').'REDIS_LOG_IMPORT_QUEUE',$data);
    }

    public static function setItemForQueueVehicleCard( $data=[], $building_id, $errors = '')
    {
          $data = json_encode(array_merge(["files" => $data], ["error_logs" => $errors], ["bdc_building_id" => $building_id], ["type" => self::IMPORT_VEHICLECARD]));
          return self::queueSet(env('REDIS_QUEUE_PREFIX').'REDIS_LOG_IMPORT_QUEUE',$data);
    }

    public static function setItemForQueueDebit( $data=[], $building_id, $errors = '')
    {
          $data = json_encode(array_merge(["files" => $data], ["error_logs" => $errors], ["bdc_building_id" => $building_id], ["type" => self::IMPORT_DEBIT]));
          return self::queueSet(env('REDIS_QUEUE_PREFIX').'REDIS_LOG_IMPORT_QUEUE',$data);
    }

    public static function setItemForQueueTask( $data=[], $building_id, $errors = '')
    {
          $data = json_encode(array_merge(["files" => $data], ["error_logs" => $errors], ["bdc_building_id" => $building_id], ["type" => self::IMPORT_TASK]));
          return self::queueSet(env('REDIS_QUEUE_PREFIX').'REDIS_LOG_IMPORT_QUEUE',$data);
    }

    public static function getItemForQueue()
    {
        return json_decode( self::queuePop([env('REDIS_QUEUE_PREFIX').'REDIS_LOG_IMPORT_QUEUE']), true); 
    }

    public static function setItemBackQueue($data)
    {
      return self::queueSet(env('REDIS_QUEUE_PREFIX').'REDIS_LOG_IMPORT_QUEUE',$data);
    }
}