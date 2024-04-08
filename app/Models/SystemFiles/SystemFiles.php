<?php

namespace App\Models\SystemFiles;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\ActionByUser;

class SystemFiles extends Model
{
    use SoftDeletes;
    //
    use ActionByUser;
    protected $table = 'system_files';


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'building_id', 'name', 'type', 'url', 'description', 'model_type', 'model_id', 'status'
    ];

    protected $hidden = [];

    protected $dates = ['deleted_at'];
}
