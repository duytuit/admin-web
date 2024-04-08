<?php

namespace App\Models\NotifyLog;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;
use Illuminate\Database\Eloquent\Model;

class NotifyLog extends Eloquent
{
    protected $connection = 'mongodb';

    protected $collection = 'notify_log';

    protected $guarded  = [];
}
