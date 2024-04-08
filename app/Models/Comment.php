<?php

namespace App\Models;

use App\Models\Model;
use App\Models\Post;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Config;

class Comment extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    public function post()
    {
        return $this->belongsTo(Post::class, 'post_id', 'id');
    }

    public function comments()
    {
        return $this->hasMany(Comment::class, 'parent_id', 'id')->orderBy('created_at', 'ASC');
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

    public function getCharAttribute()
    {
        $words = explode(' ', $this->convert_vi_to_en(isset($this->name)?$this->name:'Unknow User'));
        $name  = end($words);
        $char  = substr($name, 0, 1);
        return $char;
    }
}
