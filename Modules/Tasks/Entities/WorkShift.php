<?php

namespace Modules\Tasks\Entities;

use Illuminate\Database\Eloquent\Model;
use App\Traits\ActionByUser;

class WorkShift extends Model
{
    use ActionByUser;
    protected $table = 'work_shifts';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'building_id',
        'work_shift_name',
        'start_time',
        'end_time',
        'status',
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

    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';

    public function tasks()
    {
        return $this->hasMany(Task::class, 'work_shift_id');
    }
}
