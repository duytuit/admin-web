<?php

namespace App\Models\Permissions;

use Illuminate\Database\Eloquent\Model;
use App\Traits\ActionByUser;

class GroupPermissions extends Model
{
    use ActionByUser;
    protected $table = 'pub_groups_user';

    protected $fillable = ['name', 'description', 'admin_id', 'parent_id', 'pub_user_ids', 'permission_ids', 'created_by', 'updated_by','status'];
}
