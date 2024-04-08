<?php

namespace App\Models\LogExport;
use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class LogExport extends Eloquent {

    protected $connection = 'mongodb';

    protected $collection = 'log_exports';

    protected $guarded  = [];

}