<?php

namespace Modules\Tasks\Entities;

use Illuminate\Database\Eloquent\Model;
use App\Traits\ActionByUser;

class SubTaskTemplateInfo extends Model
{
    use ActionByUser;
    protected $table = 'sub_task_template_infos';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'sub_task_template_id',
        'title',
        'description'
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
