<?php

namespace App\Models\BdcElectricMeter;

use App\Models\Apartments\Apartments;
use App\Models\PublicUser\Users;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\ActionByUser;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ElectricMeter extends Model
{
    use SoftDeletes;
    use ActionByUser;
    protected $table = 'bdc_electric_meter';

    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(Users::class, 'user_id');
    }

    public function apartment()
    {
        return $this->belongsTo(Apartments::class, 'bdc_apartment_id');
    }

    public static function get_detail_electric_meter_by_id($id){

        $rs = Cache::store('redis')->get(env('REDIS_PREFIX') . 'get_detail_electric_meterById_'.$id);
 
        if($rs){
             return $rs;
        }
        $rs = DB::table('bdc_electric_meter')->find($id); // lấy ra thông tin phiếu thu
        if(!$rs){
             return false;
        }
         Cache::store('redis')->put(env('REDIS_PREFIX') . 'get_detail_electric_meterById_' . $id, $rs,60*60*24);
         //Cache::store('redis')->forget(env('REDIS_PREFIX') . 'get_detail_receiptById_' . $id);
         return $rs;
    }

}
