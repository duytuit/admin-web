<?php

namespace App\Models\Building;

use App\Models\BuildingInfo\BuildingInfo;
use App\Models\Department\Department;
use App\Models\Apartments\Apartments;
use App\Models\BdcApartmentDebit\ApartmentDebit;
use App\Models\PaymentInfo\PaymentInfo;
use App\Models\PublicUser\UserInfo;
use App\Models\BdcApartmentServicePrice\ApartmentServicePrice;
use App\Models\BdcBills\Bills;
use App\Models\BdcBuildingDebit\BuildingDebit;
use App\Models\BdcDebitDetail\DebitDetail;
use App\Models\Building\V2\Company;
use App\Models\PublicUser\Users;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Traits\ActionByUser;

class Building extends Model
{
    use ActionByUser;
    protected $table = 'bdc_building';

    protected $fillable = [
        'name', 'description', 'address', 'phone', 'email', 'vnp_merchant_id', 'vnp_secret', 'merchant_9p_id',
        'partnert_9p_id', '9pay_card_check_sum', '9pay_card_merchant_secret_key', 'company_id', 'debit_date', 'debit_active','day_lock_cycle_name',
        'bdc_department_id', 'manager_id', 'building_code','bank','template_mail','status','limit_audit','config_menu','building_code_manage','chanel_payment','urban_id'
    ];

    public function apartments()
    {
        return $this->hasMany(Apartments::class, 'building_id', 'id');
    }

    public function apartmentServicePrices()
    {
        return $this->hasMany(ApartmentServicePrice::class, 'building_id', 'id');
    }

    public function debits()
    {
        return $this->belongsTo(DebitDetail::class, 'bdc_building_id', 'id');
    }

    public function bills()
    {
        return $this->hasMany(Bills::class, 'bdc_building_id', 'id');
    }

    public function apartmentDebits()
    {
        return $this->hasMany(ApartmentDebit::class, 'bdc_building_id', 'id');
    }

    public function buildingDebits()
    {
        return $this->hasMany(BuildingDebit::class, 'bdc_building_id', 'id');
    }
    
    public function department()
    {
        return $this->hasOne(Department::class, 'id', 'bdc_department_id');
    }

    public function manager()
    {
        return $this->belongsTo(Users::class, 'manager_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id','id');
    }
    public function urban()
    {
        return $this->belongsTo(Urn::class, 'urban_id','id');
    }
    public function buildingInfo()
    {
        return $this->hasMany(BuildingInfo::class, 'bdc_building_id','id');
    }
    public function paymentInfo()
    {
        return $this->hasMany(PaymentInfo::class, 'bdc_building_id','id');
    }

    public function buildingPlace()
    {
        return $this->hasMany(BuildingPlace::class, 'bdc_building_id','id');
    }
    public static function get_detail_building_by_building_id($id){

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
