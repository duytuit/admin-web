<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Config;

class SocialComment extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    public function post()
    {
        return $this->belongsTo(SocialPost::class, 'post_id', 'id');
    }

    public function comments()
    {
        return $this->hasMany(SocialComment::class, 'parent_id', 'id')->orderBy('created_at', 'ASC');
    }

    public function user()
    {
        return $this->morphTo();
    }

    public function getUserTypeAttribute($value)
    {
        $types = Config::get('auth.types');

        return $types[$value] ?? $value;
    }
}
