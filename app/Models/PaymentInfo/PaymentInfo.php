<?php

namespace App\Models\PaymentInfo;

use App\Models\Building\Building;
use App\Models\PublicUser\Users;
use App\Models\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Traits\ActionByUser;

class PaymentInfo extends Model
{
    use SoftDeletes;
    use ActionByUser;
    protected $table = 'bdc_payment_info';

    protected $fillable = ['code', 'bank_account', 'bdc_building_id', 'bank_name', 'holder_name', 'branch', 'app_status', 'web_status', 'type_payment', 'short_url', 'status_payment_info','active_payment'];

    public function building()
    {
        return $this->belongsTo(Building::class, 'bdc_building_id');
    }
    public function user()
    {
        return $this->belongsTo(Users::class, 'user_id');
    }
    public static function lists($buildingId){
        return self::where('bdc_building_id',$buildingId)->orderBy('web_status','desc')->get();
    }
    public static function get_detail_payment_info_by_building_id($id){

        $rs = Cache::store('redis')->get(env('REDIS_PREFIX') . 'get_detail_payment_infoById_'.$id);
 
        if($rs){
             return $rs;
        }
        $rs = DB::table('bdc_payment_info')->whereNull('deleted_at')->where('bdc_building_id',$id)->get(); // lấy ra thông tin dự án
        if(!$rs){
             return false;
        }
        $rs = $rs->toArray();
         Cache::store('redis')->put(env('REDIS_PREFIX') . 'get_detail_payment_infoById_' . $id, $rs,60*60*24);
         return $rs;
    }
}
