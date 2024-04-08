<?php

namespace App\Models\Period;

use App\Models\Service\Service;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\ActionByUser;

class Period extends Model
{
    use SoftDeletes;
    use ActionByUser;
    protected $table = 'bdc_period';

    protected $fillable = ['name', 'carbon_fc', 'bdc_building_id'];

    public function service()
    {
        return $this->hasOne(Service::class, 'bdc_period_id', 'id');
    }
}
