<?php

namespace App\Models\AssetType;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\ActionByUser;

class AssetType extends Model
{
    use SoftDeletes;
    use ActionByUser;
    protected $table = 'bdc_assets_type';

    protected $fillable = ['name'];
}
