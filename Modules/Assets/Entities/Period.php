<?php

namespace Modules\Assets\Entities;

use Illuminate\Database\Eloquent\Model;
use App\Traits\ActionByUser;

class Period extends Model
{
    use ActionByUser;
    protected $table = 'bdc_period';

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
