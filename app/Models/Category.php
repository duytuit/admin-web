<?php

namespace App\Models;

use App\Models\Article;
use App\Models\BoUser;
use App\Models\Model;
use App\Models\PublicUser\Users;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Building\Building;
use App\Traits\ActionByUser;

class Category extends Model
{
    use SoftDeletes;
    use ActionByUser;
    protected $table = 'categories';
    protected $guarded = [];


    const types = [
                    'article' => 'Bài viết',
                    'event' => 'Sự kiện',
                    'voucher' => 'Voucher',
                    'asset' => 'Tài sản',
                    'service' => 'dịch vụ',
                    'receipt' => 'phiếu thu',
                  ];

    public function articles()
    {
        return $this->hasMany(Article::class, 'category_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(Users::class, 'user_id', 'id');
    }

    public function building()
    {
        return $this->belongsTo(Building::class, 'bdc_building_id', 'id');
    }
    public static function getConfigTypePost($type)
    {
        $where[] = ['type', '=', $type];
        return self::where($where)->orderBy('id')->get();
    }
   
}
