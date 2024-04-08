<?php

namespace App\Models\ActivityLog;

use Jenssegers\Mongodb\Eloquent\Model;
use App\Models\PublicUser\UserInfo;
use App\Models\PublicUser\Users;
use App\Models\Building\Building;
use App\Models\Customers\Customers;
use App\Traits\ActionByUser;

class LogActionDB extends Model
{
    protected $connection = 'mongodb';

    protected $table = "database_log";

    protected $guarded = [];

    
    public function updated_by()
    {
        return $this->belongsTo(Users::class, 'by', 'id');
    }
    
}
