<?php

namespace App\Models;

use App\Models\Model;
use App\Models\Article;

class Vote extends Model
{
    public function article()
    {
        return $this->belongsTo(Article::class, 'article_id');
    }
}
