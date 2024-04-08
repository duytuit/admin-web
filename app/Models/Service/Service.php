<?php

namespace App\Models\Service;

use App\Models\BdcApartmentServicePrice\ApartmentServicePrice;
use App\Models\BdcDebitDetail\DebitDetail;
use App\Models\Period\Period;
use Illuminate\Database\Eloquent\Model;
use App\Models\PublicUser\Users;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Traits\ActionByUser;

class Service extends Model
{
    use SoftDeletes;

    use ActionByUser;
    protected $table = 'bdc_services';

    protected $fillable = [
        'bdc_building_id', 'bdc_period_id', 'name', 'unit', 'description', 'bill_date', 'payment_deadline',
        'company_id', 'service_code', 'first_time_active', 'type', 'service_group','user_id','status','ngay_chuyen_doi','code_receipt','index_accounting','price_free','partner_id','check_confirm','persion_register','service_type','progressive_id'
    ];

    public function period()
    {
        return $this->belongsTo(Period::class, 'bdc_period_id', 'id');
    }

    public function servicePriceDefault()
    {
        return $this->hasOne(ServicePriceDefault::class, 'bdc_service_id', 'id');
    }

    public function apartmentServicePrices()
    {
        return $this->hasMany(ApartmentServicePrice::class,'bdc_service_id','id');
    }
    public function children()
    {
        return $this->hasMany(Service::class, 'service_code','service_code');
    }

    public function apartmentUseService()
    {
        return $this->hasManyThrough(ApartmentServicePrice::class, Service::class, 'service_code', 'bdc_service_id', 'service_code', 'id');
    }

    public function debits()
    {
        return $this->hasMany(DebitDetail::class, 'bdc_service_id','id');
    }
    public function pubUser()
    {
        return $this->belongsTo(Users::class, 'user_id');
    }
    public static function get_detail_bdc_service_by_bdc_service_id($id){

        $rs = Cache::store('redis')->get(env('REDIS_PREFIX') . 'get_detail_bdc_serviceById_'.$id);
 
        if($rs){
             return $rs;
        }
        $rs = DB::table('bdc_services')->find($id); // lấy ra thông tin dự án
        if(!$rs){
             return false;
        }
         Cache::store('redis')->put(env('REDIS_PREFIX') . 'get_detail_bdc_serviceById_' . $id, $rs,60*60*24);
         return $rs;
    }
}
