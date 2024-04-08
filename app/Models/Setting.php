<?php

namespace App\Models;

use App\Models\Model;

class Setting extends Model
{
    protected $guarded = [];
    protected $casts   = [
        'config_value' => 'array',
    ];

    public static function config_get($config_key = '')
    {
        if ($config_key) {
            $config = self::where('config_key', $config_key)->first();
        } else {
            $config = self::all();
        }

        return $config;
    }
}
