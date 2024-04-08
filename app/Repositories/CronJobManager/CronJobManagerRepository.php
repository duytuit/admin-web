<?php

namespace App\Repositories\CronJobManager;

use App\Commons\Util\Debug\Log;
use App\Repositories\Eloquent\Repository;

class CronJobManagerRepository extends Repository {

    const DEBIT_PROCESS = 'debitprocess:cron';
    const DEBIT_PROCESS_YEAR = 'debitprocessyear:cron';

    /**
     * Specify Model class name
     *
     * @return mixed
     */
    function model()
    {
        return \App\Models\CronJobManager\CronJobManager::class;
    }

    public function findSignature($signature)
    {
        return $this->model->where(['signature' => $signature, 'status' => 0]);
    }

    public function findSignatureBuildingId($signature, $buildingId)
    {
        return $this->model->where(['signature' => $signature, 'building_id' => $buildingId, 'status' => 0]);
    }

    public function findSignatureBuildingIdV2($signature, $buildingId, $status)
    {
        return $this->model->where(['signature' => $signature, 'building_id' => $buildingId, 'status' => $status]);
    }
}
