<?php

namespace App\Models\Posts;

use App\Models\Category;
use App\Models\Comments\Comments;
use App\Models\PostCategory\PostCategory;
use App\Models\PublicUser\Users;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Department\Department;
use App\Traits\ActionByUser;

class Posts extends Model
{
    use SoftDeletes;
    //
    use ActionByUser;
    protected $table = 'posts';
    protected $casts = ['attaches' => 'array'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'type', 'category_id', 'customer_ids', 'customer_group_ids', 'notify', 'url_id', 'alias', 'title', 'summary', 'content', 'image', 'hashtag', 'num_views', 'status', 'private', 'publish_at', 'images', 'attaches', 'poll_options', 'response', 'address', 'qr_code', 'start_at', 'end_at', 'number', 'partner_id', 'voucher_code', 'kind', 'app_id','department_id', 'bdc_building_id', 'content_sms','status_is_customer','lists_notify_apartment'
    ];

    protected $hidden = ['user_id'];

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
    public function comments()
    {
        return $this->hasMany(Comments::class, 'post_id', 'id')->orderBy('created_at', 'ASC');
    }
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id', 'id');
    }
     public function department()
    {
        return $this->belongsTo(Department::class, 'department_id', 'id');
    }
    public function register()
    {
        return $this->belongsTo(PostRegister::class, 'id', 'post_id');
    }

}
