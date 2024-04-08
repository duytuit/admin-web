<?php

namespace App\Models\LogImport;
use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class LogImport extends Eloquent {

    protected $connection = 'mongodb';

    protected $collection = 'log_imports';

    protected $guarded  = [];

}