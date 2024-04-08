<?php
namespace Modules\Assets\Repositories\UserHasDepartment;

interface UserHasDepartmentInterface
{
    public function getDepartments($userId, $buildingId);

    public function getDepartment($userId, $buildingId, $departmentId);

    public function assignUser($userId, $buildingId, $departmentId, $roleType);

    public function listAdmin($buildingId, $departmentId);
}
