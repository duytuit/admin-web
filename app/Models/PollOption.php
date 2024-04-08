<?php

namespace App\Models;

use App\Models\BoUser;
use App\Models\Model;
use App\Models\Post;
use App\Models\PublicUser\UserInfo;
use App\Models\UserPartner;

class PollOption extends Model
{
    protected $guarded = [];
    protected $casts   = [
        'options' => 'array',
    ];

    public function user()
    {
        if ($this->user_type == 'user') {
            return $this->belongsTo(UserInfo::class, 'user_id', 'id');
        } else {
            return $this->belongsTo(UserPartner::class, 'user_id', 'id');
        }
    }

    public function post()
    {
        return $this->belongsTo(Post::class, 'post_id', 'id');
    }

}
