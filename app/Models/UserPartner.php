<?php

namespace App\Models;

use App\Models\Model;
use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\MustVerifyEmail;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\Access\Authorizable;
use App\Traits\MyActivityTraits;
use Illuminate\Notifications\Notifiable;

class UserPartner extends Model implements AuthenticatableContract, AuthorizableContract, CanResetPasswordContract
{
    use SoftDeletes, 
        Authenticatable, 
        Authorizable,
        CanResetPassword, 
        MustVerifyEmail, 
        MyActivityTraits,
        Notifiable;

    protected $guarded = [];
    protected static $logAttributes = ['*'];
    protected static $logOnlyDirty = true;

    public function getNameAttribute()
    {
        return $this->full_name;
    }

    /** PHÂN QUYỀN */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_users', 'user_id', 'role_id')->where('role_users.user_type', 'partner');
    }

    public function getRoles()
    {
        if ($this->roles) {
            return $this->roles->implode('title', ', ');
        }
        return '';
    }

    public function getPermissions()
    {
        static $permissions = null;

        return $permissions;
    }

    public function hasRoleID($role_id)
    {
        $list = [];
        if ($this->roles) {
            $list = $this->roles->pluck('id')->toArray();
        }

        return in_array($role_id, $list);
    }

    public function hasRole($role)
    {
        return true;
    }

    public function hasAccess(array $permissions): bool
    {
        foreach ($this->roles as $role) {
            if ($role->hasAccess($permissions)) {
                return true;
            }
        }
        return false;
    }

    public function isRoot(string $roleSlug)
    {
        return $this->roles()->where('slug', $roleSlug)->count() == 1;
        // return $this->hasAccess(['admin.root']);
    }

    public function isSuperAdmin()
    {
        return $this->hasAccess(['admin.root']);
    }
}
