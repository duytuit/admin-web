<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SocialReaction extends Model
{
    protected $guarded = [];

    public function socialPost()
    {
        return $this->belongsTo(SocialPost::class, 'post_id', 'id');
    }

}
