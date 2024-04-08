<?php

namespace App\Models\BdcCoin;

use App\Models\Apartments\Apartments;
use App\Models\BdcApartmentServicePrice\ApartmentServicePrice;
use App\Models\Building\Building;
use App\Models\Service\Service;
use Illuminate\Database\Eloquent\Model;
use App\Traits\ActionByUser;

class Coin extends Model
{
    use ActionByUser;
    protected $table = 'bdc_coin';

    /*protected $fillable = [
        'bdc_building_id',
        'bdc_apartment_id',
        'bdc_apartment_service_price_id',
        'coin',
    ];*/

    protected $guarded = [];

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
}
