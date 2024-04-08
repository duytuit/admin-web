<?php

namespace App\Models\BdcApartmentReceipts;

use Illuminate\Database\Eloquent\Model;
use App\Traits\ActionByUser;

class ApartmentReceipts extends Model
{
    use ActionByUser;
    protected $table = 'bdc_apartment_receipts';

    protected $fillable = [
        'bdc_building_id', 'bdc_apartment_id', 'name', 'old_total', 'new_total', 'cost'
    ];
}
