<?php

namespace App\Models\BdcDebitDetail;

use App\Models\Apartments\Apartments;
use App\Models\BdcApartmentServicePrice\ApartmentServicePrice;
use App\Models\BdcBills\Bills;
use App\Models\Building\Building;
use App\Models\Service\Service;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;
use App\Traits\ActionByUser;

class DebitDetail extends Model
{
    use SoftDeletes;
    use ActionByUser;
    protected $table = 'bdc_debit_detail';

    protected $fillable = [
        'bdc_building_id', 
        'bdc_bill_id', 
        'bdc_apartment_id', 
        'bdc_service_id', 
        'bdc_apartment_service_price_id', 
        'title', 
        'sumery', 
        'from_date', 
        'to_date', 
        'detail', 
        'version', 
        'new_sumery', 
        'previous_owed', 
        'paid',
        'paid_v3',
        'is_free',
        'cycle_name',
        'quantity',
        'price',
        'bdc_price_type_id',
        'created_at',
        'updated_at',
        'create_date',
        'price_current',
        'image',
        'price_after_discount',
        'type_discount',
        'discount',
        'code_receipt',
        'old',
    ];

    protected $dates = ['from_date', 'to_date'];

    public function apartment()
    {
        return $this->belongsTo(Apartments::class, 'bdc_apartment_id','id');
    }

    public function building()
    {
        return $this->belongsTo(Building::class, 'bdc_building_id','id');
    }

    public function service()
    {
        return $this->belongsTo(Service::class, 'bdc_service_id','id');
    }

    public function apartmentServicePrice()
    {
        return $this->belongsTo(ApartmentServicePrice::class, 'bdc_apartment_service_price_id','id');
    }

    public function bill()
    {
        return $this->belongsTo(Bills::class, 'bdc_bill_id','id');
    }
    public static function bdcCountDebit($id)
    {
        $count_debit = Cache::store('redis')->get(env('REDIS_PREFIX') . 'count_debit_by_apartment_id_'.$id);
 
        if($count_debit){
             return $count_debit;
        }

        $count_debit = self::where('bdc_apartment_id',$id)->count();
        
        Cache::store('redis')->put(env('REDIS_PREFIX') . 'count_debit_by_apartment_id_' . $id, $count_debit,60*60*24);

        return $count_debit;
    }
}
