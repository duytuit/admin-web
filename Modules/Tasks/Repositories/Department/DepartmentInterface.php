<?php
namespace Modules\Tasks\Repositories\Department;

interface DepartmentInterface
{
    public function filterByBuildingId($buildingId);

    public function reloadByBuildingId($buildingId);
}
