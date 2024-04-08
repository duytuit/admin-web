<?php

namespace App\Models\DatabaseLog;
use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class DatabaseLog extends Eloquent {

    protected $connection = 'mongodb';

    protected $collection = 'database_log';

    protected $guarded  = [];

}