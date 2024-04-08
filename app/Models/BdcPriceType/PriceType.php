<?php

namespace App\Models\BdcPriceType;

use App\Models\BdcApartmentServicePrice\ApartmentServicePrice;
use App\Models\BdcProgressives\Progressives;
use App\Models\BdcServicePrice\ServicePrice;
use App\Models\Service\ServicePriceDefault;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\ActionByUser;

class PriceType extends Model
{
    use SoftDeletes;
    use ActionByUser;
    protected $table = 'bdc_price_type';

    protected $fillable = [
        'name'
    ];

    public function maintenances()
    {
        return $this->hasMany(ServicePrice::class, 'bdc_price_type_id');
    }

    public function servicePriceDefault()
    {
        return $this->hasOne(ServicePriceDefault::class, 'bdc_price_type_id','id');
    }

    public function progressive()
    {
        return $this->hasOne(Progressives::class,'bdc_price_type_id','id');
    }

    public function apartmentServicePrice()
    {
        return $this->hasOne(ApartmentServicePrice::class,'bdc_price_type_id','id');
    }

}
