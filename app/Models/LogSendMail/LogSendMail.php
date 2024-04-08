<?php

namespace App\Models\LogSendMail;

use Jenssegers\Mongodb\Eloquent\Model as Model;

class LogSendMail extends Model
{
    protected $connection = 'mongodb';

    protected $collection = 'log_send_mail';

    protected $guarded  = [];
    
    protected $dates = array('created_at');
}
