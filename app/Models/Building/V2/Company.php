<?php

namespace App\Models\Building\V2;

use Illuminate\Database\Eloquent\Model;
use App\Traits\ActionByUser;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class Company extends Model
{
    use SoftDeletes,ActionByUser;
    protected $table = 'company';
    protected $guarded  = [];
    public static function get_detail_company_by_id($id){

        $rs = Cache::store('redis')->get(env('REDIS_PREFIX') . 'get_detail_company_by_id_'.$id);
 
        if($rs){
             return $rs;
        }
        $rs = self::find($id); // lấy ra thông tin dự án
        if(!$rs){
             return false;
        }
         Cache::store('redis')->put(env('REDIS_PREFIX') . 'get_detail_company_by_id_' . $id, $rs,60*60*24);
         return $rs;
    }
}
