<?php

namespace App\Repositories\V3\PeriodRepository;

use App\Models\V3\Period;
use App\Repositories\V3\BaseRepository\BaseRepository;

class PeriodRepository extends BaseRepository
{
    /**
     * RoleRepository constructor.
     * @param Period $model
     */
    public function __construct(Period $model)
    {
        $this->model = $model;
    }
}
