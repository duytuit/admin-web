<?php

namespace App\Services;

use Illuminate\Support\Facades\Redis;

class AppConfig
{

    public static function setAppIdOn( $app_id )
    {
       $app_ids = \Cache::store('redis')->get(  env('REDIS_PREFIX') . '_DXMB_APP_ID_ON' );
       if (!is_array($app_ids)) {
          $app_ids = [];
       }

       if (!in_array($app_id, $app_ids)){
            array_push($app_ids, $app_id);
        }

       return \Cache::store('redis')->forever( env('REDIS_PREFIX') . '_DXMB_APP_ID_ON', $app_ids );
    }

    // kiem tra app_id thuoc he thong va duoc public hay ko
    public static function hasAppId($app_id)
    {
        return true;
    }

    public static function setAppIdForDomain( $app_id, $domain )
    {
       return \Cache::store('redis')->forever( env('REDIS_PREFIX') . $domain, $app_id );
    }

    public static function getAppIdOfDomain($domain)
    {
        return "buildingcare";
    }



}