<?php

namespace App\Models\V3;

use Illuminate\Database\Eloquent\Model;
use App\Traits\ActionByUser;

class SubTask extends Model
{
    use ActionByUser;
    protected $table = 'sub_tasks';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'task_id',
        'title',
        'description',
        'status',
        'feedback',
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

    const STATUS = [
        'pending' => 'pending',
        'normal' => 'normal',
        'not_normal' => 'not_normal',
    ];

    public function task()
    {
        return $this->belongsTo(Task::class, 'task_id');
    }
}
