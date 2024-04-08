<?php

namespace App\Models\Posts;

use App\Models\Category;
use App\Models\Comments\Comments;
use App\Models\PostCategory\PostCategory;
use App\Models\PublicUser\UserInfo;
use App\Models\PublicUser\Users;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\ActionByUser;

class PostRegister extends Model
{
    //
    use ActionByUser;
    protected $table = 'post_registers';


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'post_id', 'post_type', 'user_id', 'user_type', 'user_name', 'check_in', 'used_at', 'code'
    ];

    protected $hidden = ['user_id'];

    protected $dates = [
        'created_at',
        'updated_at',
        'start_at',
        'end_at'
    ];
    public function bdcCustomers()
    {
        return $this->belongsTo(UserInfo::class, 'user_id', 'id');
    }
    public function posts()
    {
        return $this->belongsTo(Posts::class, 'post_id', 'id');
    }

}
