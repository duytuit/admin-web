<?php

namespace App\Repositories\BdcBuildingReceipts;

use App\Repositories\Eloquent\Repository;

class BuildingReceiptRepository extends Repository {
    /**
     * Specify Model class name
     *
     * @return mixed
     */
    function model()
    {
        return \App\Models\BdcBuildingReceipts\BuildingReceipts::class;
    }

}
