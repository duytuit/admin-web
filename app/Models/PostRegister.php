<?php

namespace App\Models;

use App\Models\PublicUser\UserInfo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use App\Models\Post;

class PostRegister extends Model
{
    protected $guarded = [];

    protected $dates = ['check_in', 'used_at'];

    public function post()
    {
        return $this->belongsTo(Post::class, 'post_id', 'id');
    }

    public function user()
    {
        return $this->morphTo();
    }
    public function pubUserinfo()
    {
        return $this->belongsTo(UserInfo::class, 'user_id', 'id');
    }

    public function getUserTypeAttribute($value)
    {
        $types = [
            'user'     => 'App\\Models\\BoUser',
            'customer' => 'App\\Models\\BoCustomer',
            'partner' => 'App\\Models\\UserPartner',
        ];

        return $types[$value] ?? $value;
    }

    public function users()
    {
        return $this->belongsTo(BoUser::class, 'user_id')->where('user_type', 'user');
    }

    public function customers()
    {
        return $this->belongsTo(BoCustomer::class, 'user_id')->where('user_type', 'customer');
    }

    public function partners()
    {
        return $this->belongsTo(UserPartner::class, 'user_id')->where('user_type', 'partner');
    }

    public function getGroupAttribute()
    {
        $types = Config::get('auth.types');

        $user_type = str_replace('App\\Models\\', '', $this->user_type);

        return $types[$user_type] ?? $user_type;
    }
}
