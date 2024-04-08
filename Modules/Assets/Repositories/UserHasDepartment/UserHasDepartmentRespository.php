<?php

namespace Modules\Assets\Repositories\UserHasDepartment;

use App\Helpers\RedisHelper;
use App\Models\RoleType;
use App\Repositories\BaseRepository;
use Carbon\Carbon;

class UserHasDepartmentRespository extends BaseRepository implements UserHasDepartmentInterface
{
    public function getModel()
    {
        return \Modules\Assets\Entities\UserHasDepartment::class;
    }

    public function getDepartments($userId, $buildingId)
    {
        return $this->model->join('bdc_department', 'bdc_department.id', '=', 'v3_user_has_departments.department_id')
            ->select('bdc_department.id', 'bdc_department.name', 'bdc_department.code')
            ->where('v3_user_has_departments.user_id', $userId)
            ->where('bdc_department.bdc_building_id', $buildingId)
            ->get();
    }

    public function getDepartment($userId, $buildingId, $departmentId)
    {
        return $this->model->join('bdc_department', 'bdc_department.id', '=', 'v3_user_has_departments.department_id')
            ->select('bdc_department.id', 'bdc_department.name', 'bdc_department.code')
            ->where('v3_user_has_departments.user_id', $userId)
            ->where('bdc_department.bdc_building_id', $buildingId)
            ->where('bdc_department.id', $departmentId)
            ->first();
    }

    public function assignUser($userId, $buildingId, $departmentId, $roleType)
    {
        $users = $this->model->join('bdc_department', 'bdc_department.id', '=', 'v3_user_has_departments.department_id')
            ->select('v3_user_has_departments.user_id')
            ->join('bdc_building', 'bdc_building.id', '=', 'bdc_department.bdc_building_id')
            ->join('v3_roles', 'v3_roles.building_id', '=', 'bdc_building.id')
            ->join('v3_role_types', 'v3_role_types.id', '=', 'v3_roles.role_type_id')
            ->where(['bdc_building.id' => $buildingId]);
            // ->where('v3_user_has_departments.user_id', '!=', $userId)
            // ->where('v3_role_types.name', '!=', RoleType::TYPE_MANAGER);
        if($roleType == RoleType::TYPE_HEAD || $roleType == RoleType::TYPE_EMPLOYEE) {
            $users = $users->where('v3_role_types.name', '!=', RoleType::TYPE_MANAGER)
                ->where(['v3_role_types.name' => RoleType::TYPE_EMPLOYEE, 'v3_user_has_departments.department_id' => $departmentId]);
        }
        $users = $users->groupBy('v3_user_has_departments.user_id')->get();
        return $users;
    }

    public function listAdmin($buildingId, $departmentId)
    {
        $users = $this->model->join('bdc_department', 'bdc_department.id', '=', 'v3_user_has_departments.department_id')
            ->select('v3_user_has_departments.user_id')
            ->join('bdc_building', 'bdc_building.id', '=', 'bdc_department.bdc_building_id')
            ->join('v3_roles', 'v3_roles.building_id', '=', 'bdc_building.id')
            ->join('v3_role_types', 'v3_role_types.id', '=', 'v3_roles.role_type_id')
            ->where(['bdc_building.id' => $buildingId]);
        if($departmentId != null) {
            $users = $users->where(['v3_user_has_departments.department_id' => $departmentId]);
        }
        $users = $users->groupBy('v3_user_has_departments.user_id')->get();
        return $users;
    }
}
