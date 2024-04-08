<?php

namespace App\Models\BdcBuildingDebit;

use App\Models\Building\Building;
use Illuminate\Database\Eloquent\Model;
use App\Traits\ActionByUser;

class BuildingDebit extends Model
{
    use ActionByUser;
    protected $table = 'bdc_building_debit';

    protected $fillable = [
        'bdc_building_id', 'name', 'old_owed', 'new_owed', 'total', 'debit_period_code', 'total_free'
    ];

    public function building()
    {
        return $this->belongsTo(Building::class, 'bdc_building_id', 'id');
    }
}