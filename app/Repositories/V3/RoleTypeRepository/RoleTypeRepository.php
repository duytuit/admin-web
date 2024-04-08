<?php

namespace App\Repositories\V3\RoleTypeRepository;

use App\Models\V3\Role;
use App\Models\V3\RoleType;
use App\Repositories\V3\BaseRepository\BaseRepository;

class RoleTypeRepository extends BaseRepository
{
    /**
     * RoleTypeRepository constructor.
     * @param RoleType $model
     */
    public function __construct(RoleType $model)
    {
        $this->model = $model;
    }

    public function getRoleTypeCommon($building_id): array
    {
        return $this->query()
            ->whereIn('name', RoleType::ROLE_COMMON)
            ->orWhere('building_id',$building_id)
            ->get()
            ->pluck('display_name', 'id')
            ->toArray();
    }
}