<?php

namespace Modules\Tasks\Entities;

use Illuminate\Database\Eloquent\Model;
use App\Traits\ActionByUser;

class Task extends Model
{
    use ActionByUser;
    protected $table = 'tasks';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'building_id',
        'bdc_department_id',
        'work_shift_id',
        'task_name',
        'description',
        'priority',
        'created_by',
        'completed_on',
        'supervisor',
        'status',
        'task_category_id',
        'due_date',
        'start_date',
        'type',
        'related',
        'maintenance_asset_id',
        'feedback',
        'attach_file',
        'apartment_id',
        'feedback_id',
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

    const STATUS = [
        'not_yet_started' => 'not_yet_started',
        'processing' => 'processing',
        'started' => 'started',
        'pending' => 'pending',
        'return' => 'return',
        'switch_request' => 'switch_request',
        'deny_request' => 'deny_request',
        'done' => 'done',
    ];

    const TYPE_PHATSINH = 'phat_sinh';
    const TYPE_LAPLAI = 'lap_lai';

    public function showSubTasks()
    {
        return $this->hasMany(SubTask::class, 'task_id')->select('id', 'task_id', 'title', 'description', 'feedback', 'attach_file', 'status');
    }

    public function subTasks()
    {
        return $this->hasMany(SubTask::class, 'task_id')->select('id', 'task_id', 'status');
    }

    public function taskUsers()
    {
        return $this->hasMany(TaskUser::class, 'task_id')->select('id', 'task_id', 'user_id', 'user_name', 'user_avatar');
    }

    public function taskComments()
    {
        return $this->hasMany(TaskComment::class, 'task_id')->select('id', 'task_id', 'user_id', 'comment', 'attach_file');
    }

    public function taskFiles()
    {
        return $this->hasMany(TaskFile::class, 'task_id')->select('id', 'task_id', 'building_id', 'file_name', 'hash_name', 'type');
    }

    public function apartment()
    {
        return $this->belongsTo(Apartment::class, 'apartment_id')->select('id', 'name', 'code', 'building_id', 'status');
    }

    public function feedbackUser()
    {
        return $this->belongsTo(Feedback::class, 'feedback_id')->select('id', 'title');
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'bdc_department_id')->select('id', 'name');
    }

    public function taskCategory()
    {
        return $this->belongsTo(TaskCategory::class, 'task_category_id')->select('id', 'category_name');
    }

    public function workShift()
    {
        return $this->belongsTo(WorkShift::class, 'work_shift_id')->select('id', 'work_shift_name');
    }
}
