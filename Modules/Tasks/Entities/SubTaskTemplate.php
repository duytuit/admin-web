<?php

namespace Modules\Tasks\Entities;

use Illuminate\Database\Eloquent\Model;
use App\Traits\ActionByUser;

class SubTaskTemplate extends Model
{
    use ActionByUser;
    protected $table = 'sub_task_templates';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'building_id',
        'bdc_department_id',
        'title',
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

    public function department()
    {
        return $this->belongsTo(Department::class, 'bdc_department_id')->select('id', 'name', 'description');
    }

    public function sub_task_template_infos()
    {
        return $this->hasMany(SubTaskTemplateInfo::class, 'sub_task_template_id');
    }
}
