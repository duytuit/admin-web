<?php

namespace App\Models\VehicleCategory;

use App\Models\BdcProgressives\Progressives;
use App\Models\Service\Service;
use App\Models\VehicleCards\VehicleCards;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Traits\ActionByUser;

class VehicleCategory extends Model
{
    use SoftDeletes;
    //
    use ActionByUser;
    protected $table = 'bdc_vehicles_category';


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'description',
        'bdc_building_id',
        'status',
        'first_time_active',
        'ngay_chuyen_doi',
        'bdc_price_type_id',
        'bdc_progressive_id',
        'payment_deadline',
        'bill_date',
        'service_group',
        'code_receipt',
        'bdc_service_id',
        'type'
    ];

    protected $hidden = [];
    protected $dates = ['deleted_at'];

    public function progressive()
    {
        return $this->belongsTo(Progressives::class, 'bdc_progressive_id', 'id');
    }

    public function service()
    {
        return $this->belongsTo(Service::class, 'bdc_service_id', 'id');
    }

    public static function get_detail_vehicles_category_by_id($id){

        $rs = Cache::store('redis')->get(env('REDIS_PREFIX') . 'get_detail_vehicles_categoryById_'.$id);
 
        if($rs){
             return $rs;
        }
        $rs = DB::table('bdc_vehicles_category')->find($id); // lấy ra thông tin dự án
        if(!$rs){
             return false;
        }
         Cache::store('redis')->put(env('REDIS_PREFIX') . 'get_detail_vehicles_categoryById_' . $id, $rs,60*60*24);
         return $rs;
    }


}
