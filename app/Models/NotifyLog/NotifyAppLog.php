<?php

namespace App\Models\NotifyLog;

// use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Facades\Cache;
use Jenssegers\Mongodb\Eloquent\Model as Eloquent;
class NotifyAppLog extends Eloquent
{
    protected $connection = 'mongodb';
    protected $collection = 'notify';
    protected $guarded = [];
}
