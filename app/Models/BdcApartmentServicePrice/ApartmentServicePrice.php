<?php

namespace App\Models\BdcApartmentServicePrice;

use App\Models\Apartments\Apartments;
use App\Models\BdcDebitDetail\DebitDetail;
use App\Models\BdcPriceType\PriceType;
use App\Models\BdcProgressives\Progressives;
use App\Models\Building\Building;
use App\Models\Service\Service;
use App\Models\PublicUser\Users;
use App\Models\Vehicles\Vehicles;
use App\Traits\ActionByUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ApartmentServicePrice extends Model
{
    use SoftDeletes;
    use ActionByUser;
    protected $table = 'bdc_apartment_service_price';

    protected $fillable = [
        'bdc_service_id', 'bdc_price_type_id', 'bdc_apartment_id',
        'name', 'price', 'first_time_active', 'last_time_pay', 'bdc_progressive_id', 'bdc_vehicle_id',
        'bdc_building_id', 'description' , 'floor_price', 'status','user_id','finish','updated_by'
    ];

    public function building()
    {
        return $this->hasMany(Building::class, 'building_id', 'id');
    }

    public function apartment()
    {
        return $this->belongsTo(Apartments::class,'bdc_apartment_id','id');
    }

    public function service()
    {
        return $this->belongsTo(Service::class,'bdc_service_id','id');
    }

    public function priceType()
    {
        return $this->belongsTo(PriceType::class,'bdc_price_type_id','id');
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicles::class,'bdc_vehicle_id','id');
    }

    public function progressive()
    {
        return $this->belongsTo(Progressives::class,'bdc_progressive_id','id');
    }

    public function debits()
    {
        return $this->hasMany(DebitDetail::class,'bdc_apartment_service_price_id','id');
    }
    public function pubUser()
    {
        return $this->belongsTo(Users::class, 'user_id');
    }
    public static function get_detail_bdc_apartment_service_price_by_apartment_id($id){

        $rs = Cache::store('redis')->get(env('REDIS_PREFIX') . 'get_detail_bdc_apartment_service_priceById_'.$id);
 
        if($rs){
             return $rs;
        }
        $rs = DB::table('bdc_apartment_service_price')->find($id); // lấy ra thông tin dự án
        if(!$rs){
             return false;
        }
         Cache::store('redis')->put(env('REDIS_PREFIX') . 'get_detail_bdc_apartment_service_priceById_' . $id, $rs,60*60*24);
         return $rs;
    }
}
