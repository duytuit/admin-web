<?php

namespace App\Models\Comments;

use App\Models\Post;
use App\Models\PublicUser\UserInfo;
use App\Models\PublicUser\Users;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\ActionByUser;

class Comments extends Model
{
    use SoftDeletes;
    //
    use ActionByUser;
    protected $table = 'comments';
    protected $guarded = [];

    protected $dates = ['deleted_at'];

    public function comments()
    {
        return $this->hasMany(Comments::class, 'parent_id', 'id')->orderBy('created_at', 'ASC');
    }
    public function userInfo()
    {
        return $this->belongsTo(UserInfo::class, 'user_id', 'id')->orderBy('created_at', 'ASC');
    }
    public function user()
    {
        return $this->belongsTo(Users::class, 'user_id', 'id')->orderBy('created_at', 'ASC');
    }
    public function post()
    {
        return $this->belongsTo(Post::class, 'post_id', 'id');
    }
    

}
