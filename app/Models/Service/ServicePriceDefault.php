<?php

namespace App\Models\Service;

use App\Models\BdcPriceType\PriceType;
use App\Models\BdcProgressives\Progressives;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\ActionByUser;

class ServicePriceDefault extends Model
{
    use SoftDeletes;
    use ActionByUser;
    protected $table = 'bdc_service_price_default';

    protected $fillable = [
        'bdc_building_id', 'bdc_service_id', 'bdc_price_type_id', 'name', 'progressive_id', 'created_at',
        'updated_at','price'
    ];

    public function service()
    {
        return $this->belongsTo(Service::class, 'bdc_service_id', 'id');
    }

    public function progressive()
    {
        return $this->belongsTo(Progressives::class, 'progressive_id', 'id');
    }

    public function priceType()
    {
        return $this->belongsTo(PriceType::class, 'bdc_price_type_id', 'id');
    }
}
