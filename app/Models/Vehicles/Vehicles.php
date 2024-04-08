<?php

namespace App\Models\Vehicles;

use App\Models\Apartments\Apartments;
use App\Models\BdcApartmentServicePrice\ApartmentServicePrice;
use App\Models\PublicUser\Users;
use App\Models\VehicleCards\VehicleCards;
use App\Models\VehicleCategory\VehicleCategory;
use App\Traits\ActionByUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class Vehicles extends Model
{
    use SoftDeletes;
    //
    use ActionByUser;
    protected $table = 'bdc_vehicles';


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'bdc_apartment_id',
        'name',
        'number',
        'description',
        'vehicle_category_id',
        'bdc_progressive_price_id',
        'first_time_active',
        'status',
        'priority_level',
        'price',
        'updated_by',
        'user_id',
        'deleted_at',
        'finish'
    ];

    protected $hidden = [];
    // protected $dates = ['deleted_at'];
    public function bdcVehiclesCategory()
    {
        return $this->belongsTo(VehicleCategory::class, 'vehicle_category_id', 'id');
    }
    public function bdcApartment()
    {
        return $this->belongsTo(Apartments::class, 'bdc_apartment_id', 'id');
    }

    public function apartmentServicePrices()
    {
        return $this->hasMany(ApartmentServicePrice::class,'bdc_vehicle_id','id');
    }

    public function apartmentServicePrices_v2()
    {
        return $this->hasOne(ApartmentServicePrice::class,'bdc_vehicle_id','id');
    }

    public function bdcVehicleCard()
    {
        return $this->belongsTo(VehicleCards::class, 'id', 'bdc_vehicle_id');
    }

    public function vehicleCard()
    {
        return $this->hasOne(VehicleCards::class, 'bdc_vehicle_id', 'id');
    }
    public function updated_user()
    {
        return $this->belongsTo(Users::class,'updated_by','id');
    }

    public static function get_detail_vehicle_by_id($id){

        $rs = Cache::store('redis')->get(env('REDIS_PREFIX') . 'get_detail_vehicleById_'.$id);
 
        if($rs){
             return $rs;
        }
        $rs = Vehicles::find($id); // lấy ra thông tin dự án
        if(!$rs){
             return false;
        }
         Cache::store('redis')->put(env('REDIS_PREFIX') . 'get_detail_vehicleById_' . $id, $rs,60*60*24);
         return $rs;
    }

}
