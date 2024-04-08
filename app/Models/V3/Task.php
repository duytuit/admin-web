<?php

namespace App\Models\V3;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Traits\ActionByUser;

class Task extends Model
{
    use SoftDeletes;
    protected $table = 'bdc_v2_task';
    protected $guarded = [];

}
