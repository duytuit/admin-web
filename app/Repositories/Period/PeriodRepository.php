<?php

namespace App\Repositories\Period;

use App\Repositories\Eloquent\Repository;
use App\Models\Period\Period;

class PeriodRepository extends Repository {
    /**
     * Specify Model class name
     *
     * @return mixed
     */
    function model()
    {
        return Period::class;
    }
}
