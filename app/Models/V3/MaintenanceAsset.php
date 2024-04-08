<?php

namespace App\Models\V3;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\ActionByUser;

class MaintenanceAsset extends Model
{
    use SoftDeletes;
    use ActionByUser;
    protected $table = 'bdc_maintenance_assets';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'building_id',
        'title',
        'asset_id',
        'maintenance_time',
        'user_id',
        'description',
        'price',
        'status',
        'domain',
        'attach_file',
        'provider'
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

    const STATUS_SUCCESS = 1;
    const STATUS_PEDDING = 0;

    public function asset()
    {
        return $this->belongsTo(Asset::class, 'asset_id');
    }
}
