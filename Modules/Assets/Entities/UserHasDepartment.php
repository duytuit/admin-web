<?php

namespace Modules\Assets\Entities;

use Illuminate\Database\Eloquent\Model;
use App\Traits\ActionByUser;

class UserHasDepartment extends Model
{
    use ActionByUser;
    protected $table = 'v3_user_has_departments';

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
}
