<?php

namespace App\Models;

use App\Models\Model;
use App\Traits\ActionByUser;

class Fcm extends Model
{
    use ActionByUser;
    protected $table = 'fcms';
    protected $guarded = [];
    protected $casts   = [
        'token' => 'array',
    ];

    public static function getCountTokenbyUserId($users =[])
    {
        $rs = self::whereIn('user_id', $users)->where('token', '!=', null)->count();
        return $rs;
    }
}
