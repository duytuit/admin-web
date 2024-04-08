<?php

namespace App\Models\Permissions;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\ActionByUser;

class PubPermissionType extends Model
{
    //
    use SoftDeletes;
    //
    use ActionByUser;
    protected $table = 'pub_permission_type';


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'description', 'parent', 'status'
    ];

    protected $hidden = ['user_id'];

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];
}
