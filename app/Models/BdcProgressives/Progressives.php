<?php

namespace App\Models\BdcProgressives;

use App\Models\BdcApartmentServicePrice\ApartmentServicePrice;
use App\Models\BdcPriceType\PriceType;
use App\Models\BdcProgressivePrice\ProgressivePrice;
use App\Models\Service\ServicePriceDefault;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\ActionByUser;

class Progressives extends Model
{
    use SoftDeletes;

    use ActionByUser;
    protected $table = 'bdc_progressives';

    protected $fillable = [
        'name', 'description', 'building_id', 'company_id', 'bdc_price_type_id','bdc_service_id','applicable_date'
    ];

    public function progressivePrice()
    {
        return $this->hasMany(ProgressivePrice::class, 'progressive_id','id');
    }

    public function servicePriceDefault()
    {
        return $this->hasOne(ServicePriceDefault::class, 'progressive_id', 'id');
    }

    public function priceType()
    {
        return $this->belongsTo(PriceType::class,'bdc_price_type_id', 'id');
    }

    public function apartmentServicePrices()
    {
        return $this->hasMany(ApartmentServicePrice::class,'bdc_progressive_id','id');
    }
}
