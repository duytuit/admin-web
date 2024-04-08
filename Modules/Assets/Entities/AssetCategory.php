<?php

namespace Modules\Assets\Entities;

use Illuminate\Database\Eloquent\Model;
use App\Traits\ActionByUser;

class AssetCategory extends Model
{
    use ActionByUser;
    protected $table = 'bdc_asset_categories';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'building_id',
        'title'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [

    ];
}
