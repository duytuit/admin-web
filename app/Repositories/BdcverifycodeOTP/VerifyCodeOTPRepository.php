<?php

namespace App\Repositories\BdcverifycodeOTP;

use App\Models\BdcVerifyCodeOTP\verifycodeotp;
use App\Repositories\Eloquent\Repository;


class VerifyCodeOTPRepository extends Repository {
    /**
     * Specify Model class name
     *
     * @return mixed
     */
    function model()
    {
        return verifycodeotp::class;
    }
    public function CheckOTPWithAccount($accountid, $verifycode)
    {
        
        return $this->model->where(['pub_users_id' => $accountid ,'otp_code' => $verifycode])->orderBy('id', 'desc')->first();
        
    }
    public function CheckOTPWithAccountNew($account, $verifycode)
    {
        return $this->model->where(['mobile' => $account,'otp_code' => $verifycode])->orderBy('id', 'desc')->first();
    }
}
