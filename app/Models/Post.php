<?php

namespace App\Models;

use App\Models\BoCustomer;
use App\Models\BoUser;
use App\Models\Category;
use App\Models\Comment;
use App\Models\Model;
use App\Models\PollOption;
use App\Models\PublicUser\Users;
use App\Traits\ActionByUser;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class Post 
extends Model
{
    use SoftDeletes;
    use ActionByUser;
    protected $table = 'posts';
    protected $guarded = [];

    protected $casts = [
        'notify'             => 'array',
        'images'             => 'array',
        'attaches'           => 'array',
        'poll_options'       => 'array',
        'response'           => 'array',
        'customer_ids'       => 'array',
        'customer_group_ids' => 'array',
    ];

    protected $dates = ['publish_at', 'start_at', 'end_at'];

    public function user()
    {
        return $this->belongsTo(Users::class, 'user_id', 'id');
    }

    public function customers()
    {
        $customer_ids = explode(',', $this->customer_ids);
        $customers    = BoCustomer::whereIn('id', $customer_ids)->get();
        return $customers;
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id', 'id');
    }

    public function comments()
    {
        return $this->hasMany(Comment::class, 'post_id', 'id')->orderBy('created_at', 'ASC');
    }

    public function partner()
    {
        return $this->belongsTo(Partner::class, 'partner_id', 'id');
    }

    public function emotions()
    {
        return $this->hasMany(PostEmotion::class, 'post_id', 'id');
    }

    public function follows()
    {
        return $this->hasMany(PostFollow::class, 'post_id', 'id');
    }

    public function polls()
    {
        return $this->hasMany(PostPoll::class, 'post_id', 'id');
    }

    public function pollOptions()
    {
        if ($this->poll_options) {
            $poll_options = PollOption::whereIn('id', $this->poll_options)->get();
            return $poll_options;
        }

        return null;
    }

    public function shares()
    {
        return $this->hasMany(PostShare::class, 'post_id', 'id');
    }

    public function votes()
    {
        return $this->hasMany(PostVote::class, 'post_id', 'id');
    }

    public function register()
    {
        return $this->hasMany(PostRegister::class, 'post_id', 'id');
    }

    public function check_in()
    {
        return $this->hasMany(PostRegister::class, 'post_id', 'id')->whereNotNull('check_in');
    }

    public function checkRegisters($customer_id)
    {

        $customer_ids = !empty($this->notify['customer_ids']) ? $this->notify['customer_ids'] : [];
        $group_ids    = !empty($this->notify['group_ids']) ? $this->notify['group_ids'] : [];

        if ($customer_ids) {
            if (in_array($customer_id, $customer_ids)) {
                return true;
            }
        }

        if ($group_ids) {
            $this->isCustomerInGroup($customer_id, $group_ids);
        }

        return true;
    }

    public function isCustomerInGroup($customer_id, $group_ids)
    {
        $customer = BoCustomer::findById($customer_id);
        foreach ($group_ids as $group_id) {
            if (in_array($group_id, (array) $customer->group_ids)) {
                return true;
            }
        }

        return false;
    }

    public function usedVoucher()
    {
        $register = $this->register->count();
        $count    = $this->number - $register;
        return $count;
    }

    public static function get_detail_post_by_id($id)
    {
        
        $rs = Cache::store('redis')->get(env('REDIS_PREFIX') . 'post_detail_' . $id);

        if ($rs ) {
            return $rs;
        }
        $rs = DB::table('posts')->where('id', $id)->first(); // lấy ra thông tin bai viet
        if (!$rs) {
            return false;
        }
        Cache::store('redis')->put(env('REDIS_PREFIX') . 'post_detail_' . $id, $rs, 60 * 60 * 24);

        return $rs;
    }

    public function save(array $options = [])
    {
        parent::save($options);
        $rs = Cache::store('redis')->get(env('REDIS_PREFIX') . 'post_detail_' . $this->id);
        if ($rs) {
            $post  = DB::table('posts')->where('id', $this->id)->first();
            Cache::store('redis')->put(env('REDIS_PREFIX') . 'post_detail_' . $this->id, $post, 60 * 60 * 24);
        }
    }
}
