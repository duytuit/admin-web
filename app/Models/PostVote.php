<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostVote extends Model
{
    protected $guarded = [];

    public function post()
    {
        return $this->belongsTo(Article::class, 'post_id', 'id');
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
