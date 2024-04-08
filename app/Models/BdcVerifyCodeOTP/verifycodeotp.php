<?php

namespace App\Models\BdcVerifyCodeOTP;


use App\Models\PublicUser\Users;
use Illuminate\Database\Eloquent\Model;
use App\Traits\ActionByUser;

class verifycodeotp extends Model
{
    use ActionByUser;
    protected $table = 'verify_code_otp';

    protected $fillable = [
        'pub_users_id', 'otp_code', 'mobile', 'otp_timeout', 'status'
    ];

    public function pubUser()
    {
        return $this->belongsTo(Users::class, 'pub_users_id');
    }
}