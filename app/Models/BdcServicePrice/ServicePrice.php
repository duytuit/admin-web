<?php

namespace App\Models\BdcServicePrice;

use App\Models\BdcPriceType\PriceType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\ActionByUser;

class ServicePrice extends Model
{
    use SoftDeletes;
    use ActionByUser;
    protected $table = 'bdc_service_price';

    protected $fillable = [
        'name', 'bdc_service_id', 'bdc_price_type_id', 'from', 'to', 'unit_price'
    ];

    public function maintenances()
    {
        return $this->belongsTo(PriceType::class, 'bdc_price_type_id','id');
    }
}
