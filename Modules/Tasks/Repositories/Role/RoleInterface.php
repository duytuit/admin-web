<?php
namespace Modules\Tasks\Repositories\Role;

interface RoleInterface
{
    public function isManager($userId, $buildingId);
    public function isHead($userId, $buildingId);
    public function isEmployee($userId, $buildingId);
    public function isSuper($userId);
    public function getRoleType($userId, $buildingId);
}
