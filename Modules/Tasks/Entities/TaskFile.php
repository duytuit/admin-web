<?php

namespace Modules\Tasks\Entities;

use Illuminate\Database\Eloquent\Model;
use App\Traits\ActionByUser;

class TaskFile extends Model
{
    use ActionByUser;
    protected $table = 'task_files';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'building_id',
        'task_id',
        'file_name',
        'hash_name',
        'size',
        'type',
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

    const TYPE_IMAGE = "image";
    const TYPE_FILE = "file";

    const TYPE_DELETE = "delete";
    const TYPE_ADD = "add";
}
