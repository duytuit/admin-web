<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Services\FCM\SendNotifyFCMService;
use App\Services\ServiceSendMailV2;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\ActionByUser;

class Campain extends Model
{
    use SoftDeletes;
    use ActionByUser;
    protected $table = "bdc_campain";

    protected $guarded = [];

    protected $seachable = ['email', 'app', 'sms'];

    public static function updateOrCreateCampain($title = null, $type, $typeId = null, $total, $buildingId, $status = 0, $sort = 0, $id = null)
    {
        $rs = null;
        $status = ["email" => 0, "app" => 0, "sms" => 0];
        if ($id != null) {
            $rs = self::where('id', $id)->first();
            $rs->title = $title;
            $rs->type = $type;
            $rs->type_id = $typeId;
            $rs->total = json_encode($total);
            $rs->bdc_building_id = $buildingId;
            $rs->save();
        } else {
            $rs = self::create([
                'title' => $title,
                'type' => $type,
                'type_id' => $typeId,
                'total' => json_encode($total),
                'bdc_building_id' => $buildingId,
                'status' => json_encode($status)
            ]);
        }
     
        return $rs;
    }

    public static function getCampainIdFormQueueMail()
    {
        $campainId = ServiceSendMailV2::getQueueMailCampain();
        if (!$campainId) {
            $rs = self::where('status->email', 0)->orderBy('sort', 'asc')->get();
            foreach ($rs as $value) {
                ServiceSendMailV2::setQueueMailCampain($value->id);
            }
         
        }
        return $campainId;
    }
    public static function getCampainIdFormQueueNotify()
    {
        $campainId = SendNotifyFCMService::getQueueAppCampain();
        if (!$campainId) {
            $rs = self::where('status->app', 0)->where('send',0)->orderBy('sort', 'asc')->get();
            foreach ($rs as $value) {
                SendNotifyFCMService::setQueueAppCampain($value->id);
            }
            $campainId = isset($rs[0]) ? $rs[0]->id : null;
        }

        return $campainId;
    }
    public static function updateStatus($id, $type ,$value = null)
    {
        $campains = Campain::find($id);
        if($campains){
            $status  = json_decode($campains->status);
            foreach ($status as $key => $value) {
               if($key == $type && $value == null){
                  $status->$key = 1;
               }
            }
            $campains->status = json_encode($status);
            $campains->save();
            return json_encode($status);
        }
        return false;
    }
    public static function updateTotal($id, $type ,$value = null)
    {
        $campains = Campain::find($id);
        if($campains){
            $total  = json_decode($campains->total);
            foreach ($total as $key => $value) {
               if($key == $type && $value == null){
                $total->$key = 1;
               }else{
                $total->$key = 0;
               }
            }
            $campains->total = json_encode($total);
            $campains->save();
            return true;
        }
        return false;
    }
    public static function findByType($type)
    {
        return Campain::where(function ($q) use ($type) {
                                $q->where('status->'.$type, 0);
                                $q->where('total->'.$type,'<>',0);
                        })
                      ->where('send', 0)
                    //   ->where('id', 1467)
                      ->orderBy('sort', 'asc')->get();
    }
    public static function findByTypeFirst($type)
    {
        return Campain::where(function ($q) use ($type) {
                                $q->where('status->'.$type, 0);
                                $q->where('total->'.$type,'<>',0);
                        })
                      ->where('send', 0)
                      ->orderBy('sort', 'asc')->first();
    }
}
