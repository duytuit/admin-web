<?php

namespace App\Models\Permissions;

use Illuminate\Database\Eloquent\Model;
use App\Models\Menu\MenuOfStaff;
use App\Traits\ActionByUser;

class UserPermissions extends Model
{
    const UPDATED_AT = null;
    const CREATED_AT = null;
    use ActionByUser;
    protected $table = 'pub_user_permissions';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'pub_user_id', 'permissions'
    ];
    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        // 'user_id',
    ];

}