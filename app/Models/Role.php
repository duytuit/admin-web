<?php

namespace App\Models;

use App\Models\BoUser;
use App\Models\Model;

class Role extends Model
{
    protected $guarded = [];

    protected $casts = [
        'permissions' => 'array',
    ];

    public function users()
    {
        return $this->belongsToMany(BoUser::class, 'role_users', 'role_id', 'user_id')->where('user_type', 'user');
    }

    public function partners()
    {
        return $this->belongsToMany(UserPartner::class, 'role_users', 'role_id', 'user_id')->where('user_type', 'partner');
    }

    public function hasAccess(array $permissions)
    {
        foreach ($permissions as $permission) {
            if ($this->hasPermission($permission)) {
                return true;
            }
        }
        return false;
    }

    private function hasPermission(string $permission)
    {
        return $this->permissions[$permission] ?? false;
    }
}
