<?php

namespace App\Models\Apartments;

use App\Models\Apartments\V2\UserApartments;
use App\Models\BdcApartmentDebit\ApartmentDebit;
use App\Models\BdcApartmentServicePrice\ApartmentServicePrice;
use App\Models\BdcBills\Bills;
use App\Models\BdcDebitDetail\DebitDetail;
use App\Models\BdcV2DebitDetail\DebitDetail as BdcV2DebitDetailDebitDetail;
use App\Models\Building\Building;
use App\Models\Building\BuildingPlace;
use App\Models\Customers\Customers;
use App\Models\SystemFiles\SystemFiles;
use App\Models\Vehicles\Vehicles;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\PublicUser\Users;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Traits\ActionByUser;

class Apartments extends Model
{
    use SoftDeletes;
    //
    use ActionByUser;
    protected $table = 'bdc_apartments';


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'description', 'floor', 'status', 'building_id','area','code','building_place_id','created_by','updated_by','code_customer','name_customer','deleted_by','code_electric','code_water','bdc_apartment_group_id','img_viet_qr'
    ];

    protected $hidden = [];

    protected $dates = ['deleted_at'];

    public function bdcCustomers()
    {
        return $this->hasMany(Customers::class, 'bdc_apartment_id', 'id');
    }
    public function bdcCustomersV2()
    {
        return $this->hasMany(Customers::class, 'bdc_apartment_id', 'id')->whereHas('pubUserProfile')->whereNull('is_resident');
    }
    public function bdcResident()
    {
        return $this->hasMany(UserApartments::class, 'apartment_id', 'id');
    }

    public static function bdcCountResident($id)
    {
        $count_resident = Cache::store('redis')->get(env('REDIS_PREFIX') . 'count_resident_by_apartment_id_'.$id);
 
        if($count_resident){
             return $count_resident;
        }

        $count_resident = UserApartments::where('apartment_id',$id)->count();
        
        Cache::store('redis')->put(env('REDIS_PREFIX') . 'count_resident_by_apartment_id_' . $id, $count_resident,60*60*24);

        return $count_resident;
    }

    public function bdcVehicles()
    {
        return $this->hasMany(Vehicles::class, 'bdc_apartment_id', 'id');
    }

    public static function bdcCountVehicle($id)
    {
        $count_vehicle = Cache::store('redis')->get(env('REDIS_PREFIX') . 'count_vehicle_by_apartment_id_'.$id);
 
        if($count_vehicle){
             return $count_vehicle;
        }

        $count_vehicle = Vehicles::where('bdc_apartment_id',$id)->count();
        
        Cache::store('redis')->put(env('REDIS_PREFIX') . 'count_vehicle_by_apartment_id_' . $id, $count_vehicle,60*60*24);

        return $count_vehicle;
    }

    public function building()
    {
        return $this->belongsTo(Building::class, 'building_id', 'id');
    }
    public function buildingPlace()
    {
        return $this->belongsTo(BuildingPlace::class, 'building_place_id', 'id');
    }

    public function apartmentGroup()
    {
        return $this->belongsTo(ApartmentGroup::class, 'bdc_apartment_group_id', 'id');
    }

    public function apartmentServicePrices()
    {
        return $this->hasMany(ApartmentServicePrice::class, 'bdc_apartment_id', 'id');
    }

    public function debits()
    {
        return $this->hasMany(DebitDetail::class, 'bdc_apartment_id', 'id');
    }

    public function debit_v2()
    {
        return $this->belongsTo(BdcV2DebitDetailDebitDetail::class, 'id', 'bdc_apartment_id');
    }

    public function bills()
    {
        return $this->hasMany(Bills::class, 'bdc_apartment_id', 'id');
    }
    public function billsV2()
    {
        return $this->hasMany(Bills::class, 'bdc_apartment_id', 'id')->where('status', '>=',-2)->orderBy('updated_at', 'desc');
    }

    public function billsV3()
    {
        return $this->hasMany(Bills::class, 'bdc_apartment_id', 'id')->whereHas('debitDetailV2')->where('status', '>=',-2)->orderBy('updated_at', 'desc');
    }
    public function billsV4()
    {
        return $this->hasMany(Bills::class, 'bdc_apartment_id', 'id')->where('status', '>=',-2)->orderBy('updated_at', 'desc');
    }


    public function apartmentDebit()
    {
        return $this->hasMany(ApartmentDebit::class, 'bdc_apartment_id', 'id');
    }

    public function systemFile()
    {
        $where = [
            ['model_type', '=', 'apartment']
        ];
        return $this->hasMany(SystemFiles::class, 'model_id', 'id')->where($where);
    }

    public static function bdcCountSystemFile($id)
    {
        $count_systemfile = Cache::store('redis')->get(env('REDIS_PREFIX') . 'count_systemfile_by_apartment_id_'.$id);
 
        if($count_systemfile){
             return $count_systemfile;
        }

        $count_systemfile = SystemFiles::where('model_id',$id)->where('model_type','apartment')->count();
        
        Cache::store('redis')->put(env('REDIS_PREFIX') . 'count_systemfile_by_apartment_id_' . $id, $count_systemfile,60*60*24);

        return $count_systemfile;
    }

    public function user_created_by()
    {
        return $this->belongsTo(Users::class, 'created_by','id');
    }
    public function user_updated_by()
    {
        return $this->belongsTo(Users::class, 'updated_by', 'id');
    }
    public function user_deleted_by()
    {
        return $this->belongsTo(Users::class, 'deleted_by', 'id');
    }
    public static function get_detail_apartment_by_apartment_id($id){

        $rs = Cache::store('redis')->get(env('REDIS_PREFIX') . 'get_detail_apartmentById_'.$id);
 
        if($rs){
             return $rs;
        }
        $rs = DB::table('bdc_apartments')->find($id); // lấy ra thông tin dự án
        if(!$rs){
             return false;
        }
         Cache::store('redis')->put(env('REDIS_PREFIX') . 'get_detail_apartmentById_' . $id, $rs,60*60*24);
         return $rs;
    }
}
