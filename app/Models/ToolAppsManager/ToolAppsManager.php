<?php

namespace App\Models\ToolAppsManager;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\ActionByUser;

class ToolAppsManager extends Model
{
    use SoftDeletes;
    use ActionByUser;
    protected $table = 'tool_apps_manager';
    protected $fillable = ['name', 'type', 'link', 'status', 'description'];

}