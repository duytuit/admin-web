<?php

namespace App\Models\ActivityLog;

use Jenssegers\Mongodb\Eloquent\Model;
use App\Models\PublicUser\UserInfo;
use App\Models\PublicUser\Users;
use App\Models\Building\Building;
use App\Models\Customers\Customers;
use App\Traits\ActionByUser;

class LogActiveTool extends Model
{
    protected $connection = 'mongodb';
    protected $table = "request_log";
    protected $guarded = [];


}
