<?php

namespace App\Models\Banks;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\ActionByUser;

class Banks extends Model
{
    use SoftDeletes;
    //
    use ActionByUser;
    protected $table = 'banks';


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title','alias', 'url', 'logo', 'app_name', 'bdc_building_id', 'status'
    ];

    protected $hidden = [];

    protected $dates = ['deleted_at'];
}
