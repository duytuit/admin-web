<?php

namespace Modules\Tasks\Repositories\Role;

use App\Helpers\RedisHelper;
//use App\Models\RoleType;
use App\Repositories\BaseRepository;
use App\Repositories\RepositoryInterface;
use Carbon\Carbon;

class RoleRespository extends BaseRepository implements RoleInterface
{
    public function getModel()
    {
        return \App\Models\Role::class;
    }

    public function isManager($userId, $buildingId)
    {
        return $this->model->join('v3_user_has_roles', 'v3_roles.id', '=', 'v3_user_has_roles.role_id')
            ->join('v3_role_types', 'v3_role_types.id', '=', 'v3_roles.role_type_id')
            ->select('v3_role_types.name')
            ->where('v3_roles.building_id', $buildingId)
            ->where('v3_user_has_roles.user_id', $userId)
            ->where('v3_role_types.name', RoleType::TYPE_MANAGER)
            ->first();
    }

    public function isHead($userId, $buildingId)
    {
        return $this->model->join('v3_user_has_roles', 'v3_roles.id', '=', 'v3_user_has_roles.role_id')
            ->join('v3_role_types', 'v3_role_types.id', '=', 'v3_roles.role_type_id')
            ->select('v3_role_types.name')
            ->where('v3_roles.building_id', $buildingId)
            ->where('v3_user_has_roles.user_id', $userId)
            ->where('v3_role_types.name', RoleType::TYPE_HEAD)
            ->first();
    }

    public function isEmployee($userId, $buildingId)
    {
        return $this->model->join('v3_user_has_roles', 'v3_roles.id', '=', 'v3_user_has_roles.role_id')
            ->join('v3_role_types', 'v3_role_types.id', '=', 'v3_roles.role_type_id')
            ->select('v3_role_types.name')
            ->where('v3_roles.building_id', $buildingId)
            ->where('v3_user_has_roles.user_id', $userId)
            ->where('v3_role_types.name', RoleType::TYPE_EMPLOYEE)
            ->first();
    }

    public function isSuper($userId)
    {
        return $this->model->join('v3_user_has_roles', 'v3_roles.id', '=', 'v3_user_has_roles.role_id')
            ->join('v3_role_types', 'v3_role_types.id', '=', 'v3_roles.role_type_id')
            ->select('v3_role_types.name')
            ->whereNull('v3_roles.building_id')
            ->where('v3_user_has_roles.user_id', $userId)
            ->first();
    }

    public function getRoleType($userId, $buildingId)
    {
        $roleType = $this->isSuper($userId);
        if(!$roleType) {
            $roleType = $this->isManager($userId, $buildingId);
            if(!$roleType) {
                $roleType = $this->isHead($userId, $buildingId);
                if(!$roleType) {
                    $roleType = $this->isEmployee($userId, $buildingId);
                }
            }
        }
        return isset($roleType->name) ? $roleType->name : "";
    }
}
