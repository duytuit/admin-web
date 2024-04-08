<?php

namespace App\Models\Apartments\V2;

use App\Models\Apartments\Apartments;
use App\Models\BdcV2DebitDetail\DebitDetail;
use App\Models\Building\Building;
use App\Models\PublicUser\V2\UserInfo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;
use App\Traits\ActionByUser;

class UserApartments extends Model
{
    use SoftDeletes;
    //
    use ActionByUser;
    protected $table = 'bdc_v2_user_apartment';


    protected $guarded = [];

    public static function getListUserPushNotify($apartmentId){
        $user_info_id = self::whereIn('apartment_id',$apartmentId)->pluck('user_info_id');
        if($user_info_id){
            return $user_info_id->toArray();
        }
        return [];
    }

    public static function getPurchaser($apartmentId){
        return self::where(['apartment_id'=>$apartmentId,'type'=>0])->first();
    }
    public function user_info_first()
    {
        return $this->belongsTo(UserInfo::class, 'user_info_id','id');
    }
    public static function bdcUserInfo($id)
    {
        $user_info = Cache::store('redis')->get(env('REDIS_PREFIX') . 'detail_user_info_by_id_'.$id);
 
        if($user_info){
             return $user_info;
        }

        $user_info = UserInfo::find($id);
        
        Cache::store('redis')->put(env('REDIS_PREFIX') . 'detail_user_info_by_id_' . $id, $user_info,60*60*24);

        return $user_info;
    }
    public function bdcApartment()
    {
        return $this->belongsTo(Apartments::class, 'apartment_id', 'id');
    }
    public function building()
    {
        return $this->belongsTo(Building::class, 'building_id', 'id')->where('status',1);
    }
    public function debit_v2()
    {
        return $this->belongsTo(DebitDetail::class, 'apartment_id', 'bdc_apartment_id');
    }
}
