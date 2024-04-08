<?php

namespace App\Repositories\Vnpay;

//use App\Repositories\Contracts\RepositoryInterface;
use App\Repositories\Eloquent\Repository;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cookie;

class VnpayRespository extends Repository {
    function model()
    {
        return \App\Models\Vnpay\VnpayLog::class;
    }
}