<?php

namespace Modules\Tasks\Repositories\Department;

use App\Helpers\RedisHelper;
use App\Repositories\BaseRepository;
use Carbon\Carbon;
use Modules\Tasks\Entities\Department;

class DepartmentRespository extends BaseRepository implements DepartmentInterface
{
    public function getModel()
    {
        return Department::class;
    }

    const REDIS_DEPARTMENT_BUILDING_ID = "department_building_id:%s";
    const REDIS_DEPARTMENT_ID = "department:%s";

    public function filterByBuildingId($buildingId)
    {
        $keyWorkShiftBuildingId = RedisHelper::createKey(self::REDIS_DEPARTMENT_BUILDING_ID, $buildingId);
        $workShifts = RedisHelper::get($keyWorkShiftBuildingId);
        if($workShifts) {
            return $workShifts;
        }
        $workShifts = $this->model->where(['building_id' => $buildingId])->get();
        if($workShifts) {
            RedisHelper::set($keyWorkShiftBuildingId, $workShifts);
        }
        return $workShifts;
    }

    public function reloadByBuildingId($buildingId)
    {
        $keyWorkShiftBuildingId = RedisHelper::createKey(self::REDIS_DEPARTMENT_BUILDING_ID, $buildingId);
        $workShifts = $this->model->where(['building_id' => $buildingId])->get();
        if($workShifts) {
            RedisHelper::set($keyWorkShiftBuildingId, $workShifts);
        }
        return $workShifts;
    }
}
