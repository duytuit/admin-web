<?php

namespace App\Repositories\V3\DepartmentRepository;

use App\Models\V3\Department;
use App\Repositories\V3\BaseRepository\BaseRepository;

class DepartmentRepository extends BaseRepository implements DepartmentRepositoryInterface
{

    /**
     * RoleRepository constructor.
     * @param Department $model
     */
    public function __construct(Department $model)
    {
        $this->model = $model;
    }

    public function filterByBuildingId($buildingId)
    {
        return $this->query()->where([
            'bdc_building_id' => $buildingId
        ])->get();

    }

}
