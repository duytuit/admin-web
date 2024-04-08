<?php

namespace App\Models\Network;

use App\Models\PublicUser\UserInfo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\ActionByUser;

class SocialComments extends Model
{
    //
    use SoftDeletes;

    use ActionByUser;
    protected $table = 'social_comments';


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'post_id','parent_id', 'user_id', 'user_type', 'content', 'files'
    ];

    protected $hidden = [];

    protected $dates = ['deleted_at'];
    public function pubProfile()
    {
        return $this->belongsTo(UserInfo::class, 'user_id', 'id');
    }
}
