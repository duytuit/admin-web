<?php

namespace App\Models;

use App\Models\Model;
use App\Models\AppProject;
class GroupMenu extends Model
{
    protected $guarded = [];
    protected $casts   = [
        'menu_ids' => 'array',
    ];

    public function app_project()
    {
        return $this->belongsTo(AppProject::class, 'app_id', 'id');
    }
}
