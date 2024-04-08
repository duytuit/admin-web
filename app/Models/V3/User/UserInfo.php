<?php

namespace App\Models\V3\User;

use App\Models\V3\Building;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\ActionByUser;

class UserInfo extends Model
{
    use SoftDeletes;

    use ActionByUser;
    protected $table = 'pub_user_profile';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'pub_user_id',
        'type',
        'phone',
        'email',
        'address',
        'gender',
        'display_name',
        'birthday',
        'avatar' ,
        'created_by',
        'created_at',
        'cmt',
        'cmt_nc',
        'app_id',
        'status',
        'staff_code',
        'bdc_building_id',
        'type_profile',
        'config_fcm',
        'customer_code',
        'customer_code_prefix'
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
    public function building()
    {
        return $this->belongsTo(Building::class, 'bdc_building_id');
    }
}
