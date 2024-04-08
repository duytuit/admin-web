<?php

namespace Modules\Tasks\Entities;

use Illuminate\Database\Eloquent\Model;
use App\Traits\ActionByUser;

class Feedback extends Model
{
    use ActionByUser;
    protected $table = 'feedback';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
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

    const STATUS_PEDDING = 0;
    const STATUS_PROCESSING = 1;
    const STATUS_DONE = 2;
}
