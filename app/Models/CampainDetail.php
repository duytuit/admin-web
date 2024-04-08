<?php

namespace App\Models;

// use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Facades\Cache;
use Jenssegers\Mongodb\Eloquent\Model as Eloquent;
class CampainDetail extends Eloquent
{
    //
    protected $connection = 'mongodb';
    protected $collection = 'campain_detail';
    protected $guarded = [];
}
