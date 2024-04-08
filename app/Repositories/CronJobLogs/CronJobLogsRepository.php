<?php

namespace App\Repositories\CronJobLogs;

use App\Repositories\Eloquent\Repository;

class CronJobLogsRepository extends Repository {
    /**
     * Specify Model class name
     *
     * @return mixed
     */
    function model()
    {
        return \App\Models\CronJobLogs\CronJobLogs::class;
    }
}
