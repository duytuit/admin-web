<?php

namespace App\Repositories\V3\RoleRepository;

use App\Models\V3\Role;
use App\Repositories\V3\BaseRepository\BaseRepository;

class RoleRepository extends BaseRepository implements RoleRepositoryInterface
{

    /**
     * RoleRepository constructor.
     * @param Role $model
     */
    public function __construct(Role $model)
    {
        $this->model = $model;
    }

    public function getRolesByBuildingId($building_id): array
    {
        return $this->query()
            ->where('building_id', $building_id)
            ->with('roleType')
            ->get()
            ->toArray();
    }

}