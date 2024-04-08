<?php

namespace App\Models\System;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\ActionByUser;

class Config extends Model
{
    use ActionByUser;
    protected $table = 'bdc_configs';

    public $fillable = ['config_key', 'config_value', 'bdc_building_id'];
}
