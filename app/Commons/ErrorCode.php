<?php

namespace App\Commons;

class ErrorCode
{
    const ERROR = -1;
    const SUCCESS = 0;

    static $message = [
        self::SUCCESS => 'Thành công',
        self::ERROR => 'Thất bại',
    ];

    public static function getMsg($code)
    {
        $msgs = self::$message;
        return $msgs[$code] ?? 'Xảy ra lỗi!!!';
    }
}
