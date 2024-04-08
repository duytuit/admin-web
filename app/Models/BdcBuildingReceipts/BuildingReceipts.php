<?php

namespace App\Models\BdcBuildingReceipts;

use Illuminate\Database\Eloquent\Model;
use App\Traits\ActionByUser;

class BuildingReceipts extends Model
{
    use ActionByUser;
    protected $table = 'bdc_building_receipts';

    protected $fillable = [
        'bdc_building_id', 'name', 'old_total', 'new_total', 'cost'
    ];
}