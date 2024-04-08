<?php

namespace App\Models\Building;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Traits\ActionByUser;

class BuildingPlace extends Model
{
    //
    use SoftDeletes;
    use ActionByUser;
    protected $table = 'bdc_bulding_place';

    protected $fillable = [
        'name', 'description', 'address', 'mobile', 'email', 'status', 'bdc_building_id', 'code'
    ];
    public static function get_detail_bulding_place_by_bulding_place_id($id){

        $rs = Cache::store('redis')->get(env('REDIS_PREFIX') . 'get_detail_bulding_placeById_'.$id);
 
        if($rs){
             return $rs;
        }
        $rs = DB::table('bdc_bulding_place')->find($id); // lấy ra thông tin dự án
        if(!$rs){
             return false;
        }
         Cache::store('redis')->put(env('REDIS_PREFIX') . 'get_detail_bulding_placeById_' . $id, $rs,60*60*24);
         return $rs;
    }
}
