<?php

namespace App\Models\PromotionApartment;

use App\Models\BdcApartmentServicePrice\ApartmentServicePrice;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\ActionByUser;
use Illuminate\Support\Facades\Cache;

class PromotionApartment extends Model
{
    use SoftDeletes;
    use ActionByUser;

    protected $table = 'promotion_apartment';
    protected $primaryKey = 'id';
    /*protected $fillable = [
        'id',
        'apartment_id', // tòa nhà
        'service_price_id',
        'promotion_id',
        'cycle_name',
        'by',
        'receipt_id',
        'updated_at',
        'created_at', //tạo lúc
        'deleted_at',  //xóa lúc
    ];*/

    protected $guarded = [];

    public function serviceApartment()
    {
        return $this->belongsTo(ApartmentServicePrice::class, 'service_price_id', 'id');
    }
}
