<?php

namespace App\Models\Vnpay;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;
use Illuminate\Database\Eloquent\Model;

class VnpayLog extends Eloquent
{
    protected $connection = 'mongodb';

    protected $collection = 'vnpay_log';

    protected $guarded  = [];
}
