<?php

namespace App\Repositories\ToolAppVersions;

use App\Repositories\Eloquent\Repository;
use App\Models\ToolAppVersions\ToolAppVersions;

class ToolAppVersionsRepository extends Repository
{


    /**
     * Specify Model class name
     *
     * @return mixed
     */
    function model()
    {
        return ToolAppVersions::class;
    }
}