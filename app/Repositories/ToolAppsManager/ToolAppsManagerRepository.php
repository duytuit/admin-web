<?php

namespace App\Repositories\ToolAppsManager;

use App\Repositories\Eloquent\Repository;
use App\Models\ToolAppsManager\ToolAppsManager;

class ToolAppsManagerRepository extends Repository
{


    /**
     * Specify Model class name
     *
     * @return mixed
     */
    function model()
    {
        return ToolAppsManager::class;
    }
}