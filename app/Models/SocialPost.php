<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class SocialPost extends Model
{
    use SoftDeletes;

    protected $guarded = ['response'];

    protected $casts = [
        'images' => 'array',
        'response' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(BoCustomer::class, 'user_id', 'id');
    }

//    public function comments()
//    {
//        return $this->hasMany(Comment::class, 'post_id', 'id')->orderBy('created_at', 'ASC');
//    }

//    public function emotions()
//    {
//        return $this->hasMany(PostEmotion::class, 'post_id', 'id');
//    }


}
