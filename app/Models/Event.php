<?php

namespace App\Models;

use App\Models\Article;
use App\Models\BoCustomer;
use App\Models\BoUser;
use App\Models\Model;

class Event extends Model
{
    protected $guarded = [];

    protected $dates = [
        'check_in',
    ];

    public function article()
    {
        return $this->belongsTo(Article::class, 'article_id', 'id');
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
