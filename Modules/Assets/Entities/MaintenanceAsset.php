<?php

namespace Modules\Assets\Entities;

use Illuminate\Database\Eloquent\Model;
use App\Traits\ActionByUser;

class MaintenanceAsset extends Model
{
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
