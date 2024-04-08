<?php

namespace App\Models;

use App\Models\Comment;
use App\Models\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Feedback extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'attached' => 'array',
    ];

    public function customer()
    {
        return $this->belongsTo(BoCustomer::class, 'customer_id', 'id');
    }

    public function comments()
    {
        $where = [
            ['parent_id', '=', 0],
            ['type', '=', 'feedback'],
        ];
        return $this->hasMany(Comment::class, 'post_id', 'id')->where($where);
    }
}
