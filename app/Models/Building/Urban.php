<?php

namespace App\Models\Building;

use App\Models\Building\V2\Company;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Traits\ActionByUser;
use Illuminate\Database\Eloquent\SoftDeletes;

class Urban extends Model
{
    use SoftDeletes,ActionByUser;
    protected $table = 'urban';

    protected $guarded  = [];

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id','id');
    }
    
    public static function get_detail_urban_by_urban_id($id){

        $rs = Cache::store('redis')->get(env('REDIS_PREFIX') . 'get_detail_buildingById_'.$id);
 
        if($rs){
             return $rs;
        }
        $rs = DB::table('bdc_building')->find($id); // lấy ra thông tin dự án
        if(!$rs){
             return false;
        }
         Cache::store('redis')->put(env('REDIS_PREFIX') . 'get_detail_buildingById_' . $id, $rs,60*60*24);
         return $rs;
    }
}
