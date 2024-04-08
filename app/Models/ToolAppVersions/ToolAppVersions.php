<?php

namespace App\Models\ToolAppVersions;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\ActionByUser;

class ToolAppVersions extends Model
{
    use SoftDeletes;
    use ActionByUser;
    protected $table = 'tool_app_versions';
    protected $fillable = ['app_id', 'versions', 'public_time', 'status'];

}