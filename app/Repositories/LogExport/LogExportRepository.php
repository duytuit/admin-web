<?php

namespace App\Repositories\LogExport;

use App\Repositories\Eloquent\Repository;
use App\Models\LogExport\LogExport;

class LogExportRepository extends Repository {


    /**
     * Specify Model class name
     *
     * @return mixed
     */
    function model()
    {
        return LogExport::class;
    }

    function getLogExports($building_id)
    {
        return $this->model->where('bdc_building_id', $building_id)->get();
    }

    function getLogExportById($id)
    {
        return $this->model->find($id)->first();
    }
}
