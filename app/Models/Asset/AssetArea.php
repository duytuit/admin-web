<?php

namespace App\Models\Asset;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AssetArea extends Model
{
    use SoftDeletes;
    protected $table = 'asset_area_office';
    protected $guarded  = [];
}
