<?php

namespace App\Models\RequestLog;
use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class RequestLog extends Eloquent {

    protected $connection = 'mongodb';

    protected $collection = 'request_log';

    protected $guarded  = [];

}