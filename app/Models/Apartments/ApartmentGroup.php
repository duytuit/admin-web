<?php

namespace App\Models\Apartments;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Jenssegers\Mongodb\Eloquent\SoftDeletes;
use App\Traits\ActionByUser;

class ApartmentGroup extends Model
{
    use SoftDeletes;

    use ActionByUser;
    protected $table = "bdc_apartment_groups";

    protected $fillable = [
        'name', 'description', 'status', 'bdc_building_id'
    ];

    public function apartments()
    {
        return $this->hasMany(Apartments::class, 'bdc_apartment_group_id', 'id');
    }
    public static function get_detail_apartment_group_by_apartment_group_id($id){

        $rs = Cache::store('redis')->get(env('REDIS_PREFIX') . 'get_detail_apartment_groupById_'.$id);
        if($rs){
             return $rs;
        }
        $rs = DB::table('bdc_apartment_groups')->find($id); // lấy ra thông tin dự án
        if(!$rs){
             return false;
        }
         Cache::store('redis')->put(env('REDIS_PREFIX') . 'get_detail_apartment_groupById_' . $id, $rs,60*60*24);
         return $rs;
    }
}
