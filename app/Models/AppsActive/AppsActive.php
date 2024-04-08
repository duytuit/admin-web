<?php

namespace App\Models\AppsActive;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\ActionByUser;

class AppsActive extends Model
{
    use SoftDeletes;
    use ActionByUser;
    protected $table = 'apps_active';
    protected $fillable = ['app_id', 'versions', 'public_time', 'status'];

}