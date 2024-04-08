<?php

namespace App\Models\Permissions;

use App\Models\Customers\Customers;
use App\Models\PublicUser\UserInfo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\ActionByUser;

class GroupsPermissions extends Model
{
    //
    use SoftDeletes;
    //
    //use ActionByUser;
    protected $table = 'pub_group_permission';


    protected $guarded = [];

    protected $hidden = ['user_id'];

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];
    public function pubUser()
    {
        return $this->hasone(UserInfo::class, 'pub_user_id', 'create_by');
    }
}
