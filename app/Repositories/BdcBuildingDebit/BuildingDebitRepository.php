<?php

namespace App\Repositories\BdcBuildingDebit;

use App\Repositories\Eloquent\Repository;

const BUILDING_USER = 1;

class BuildingDebitRepository extends Repository {
    /**
     * Specify Model class name
     *
     * @return mixed
     */
    function model()
    {
        return \App\Models\BdcBuildingDebit\BuildingDebit::class;
    }

    public function findDebitPeriodCode($debitPeriodCode)
    {
        return $this->model->where(['debit_period_code' => $debitPeriodCode])->first();
    }

    public function getAllApartmentOfUser($perPage)
    {
        return $this->model->where('bdc_building_id',BUILDING_USER)->paginate($perPage);
    }
}
