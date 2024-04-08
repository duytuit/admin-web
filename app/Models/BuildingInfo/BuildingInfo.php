<?php

namespace App\Models\BuildingInfo;

use App\Models\Building\Building;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\ActionByUser;

class BuildingInfo extends Model
{
    use SoftDeletes;
    use ActionByUser;
    protected $table = 'bdc_building_info';

    protected $fillable = ['bdc_building_id', 'content', 'quantity', 'note'];

    public function building()
    {
        return $this->belongsTo(Building::class, 'bdc_building_id');
    }
}
