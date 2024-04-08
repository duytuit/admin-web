<?php

namespace App\Repositories\LogImport;

use App\Repositories\Eloquent\Repository;
use App\Models\LogImport\LogImport;

class LogImportRepository extends Repository {


    /**
     * Specify Model class name
     *
     * @return mixed
     */
    function model()
    {
        return LogImport::class;
    }

    function getLogImports($building_id)
    {
        return $this->model->where('bdc_building_id', $building_id)->get();
    }

    function getLogImportById($id)
    {
        return $this->model->find($id)->first();
    }
}
