<?php

namespace App\Models\LogSendSMS;

use Jenssegers\Mongodb\Eloquent\Model as Model;

class LogSendSMS extends Model
{
    protected $connection = 'mongodb';

    protected $collection = 'log_send_sms';

    protected $guarded  = [];
}
