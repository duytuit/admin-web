<?php

namespace App\Models\V3;

use Illuminate\Database\Eloquent\Model;
use App\Traits\ActionByUser;
use Jenssegers\Mongodb\Eloquent\SoftDeletes;

class TaskCategory extends Model
{
    use SoftDeletes,ActionByUser;
    protected $table = 'bdc_v2_task_category';

    protected  $guarded=[];

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
