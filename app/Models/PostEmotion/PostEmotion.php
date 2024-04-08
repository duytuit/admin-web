<?php

namespace App\Models\PostEmotion;

use App\Models\PostCategory\PostCategory;
use App\Models\PublicUser\Users;
use Illuminate\Database\Eloquent\Model;
//use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\ActionByUser;

class PostEmotion extends Model
{
//    use SoftDeletes;
    //
    use ActionByUser;
    protected $table = 'post_emotions';


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'post_id', 'post_type', 'user_type', 'user_name', 'emotion','new'
    ];

    protected $hidden = [];

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
        'start_at',
        'end_at'
    ];

    public function user()
    {
        return $this->belongsTo(Users::class, 'user_id', 'id');
    }

}
