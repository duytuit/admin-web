<?php

namespace App\Models\Network;

use App\Models\PublicUser\UserInfo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\ActionByUser;

class SocialPost extends Model
{
    //
    use SoftDeletes;

    use ActionByUser;
    protected $table = 'social_posts';

    protected $guarded = [];
    // /**
    //  * The attributes that are mass assignable.
    //  *
    //  * @var array
    //  */
    // protected $fillable = [
    //     'user_id','content', 'status', 'images', 'response', 'visible','bdc_building_id'
    // ];

    protected $hidden = [];

    protected $dates = ['deleted_at'];
    public function pubProfile()
    {
        return $this->belongsTo(UserInfo::class, 'user_id', 'id');
    }
}
