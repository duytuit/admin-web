<?php

namespace App\Repositories\AssetType;

use App\Models\AssetType\AssetType;
use App\Repositories\Eloquent\Repository;

class AssetTypeRepository extends Repository {
    /**
     * Specify Model class name
     *
     * @return mixed
     */
    function model()
    {
        return AssetType::class;
    }
}
