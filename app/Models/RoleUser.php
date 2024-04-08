<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoleUser extends Model
{
    protected $guarded = [];

    public function user()
    {
        return $this->morphTo();
    }

    public function getUserTypeAttribute($value)
    {
        $types = Config::get('auth.types');

        return $types[$value] ?? $value;
    }
    
    public function users()
    {
        return $this->hasMany(BoUser::class, 'user_id', 'id');
    }

    public function partners()
    {
        return $this->hasMany(UserPartner::class, 'user_id', 'id');
    }

    public function roles()
    {
        return $this->hasMany(Role::class, 'role_id', 'id');
    }
}
