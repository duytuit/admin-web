<?php

namespace Modules\Tasks\Entities;

use Illuminate\Database\Eloquent\Model;
use App\Traits\ActionByUser;

class TaskComment extends Model
{
    use ActionByUser;
    protected $table = 'task_comments';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'task_id',
        'user_id',
        'comment',
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

    public function task()
    {
        return $this->belongsTo(Task::class, 'task_id');
    }
}