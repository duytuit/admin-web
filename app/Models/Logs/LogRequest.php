<?php

namespace App\Models\Logs;
use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class LogRequest extends Eloquent {

    protected $connection = 'mongodb';

    protected $collection = 'log_requests';

    protected $guarded  = [];

}