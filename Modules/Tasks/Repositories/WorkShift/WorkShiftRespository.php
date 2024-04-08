<?php

namespace Modules\Tasks\Repositories\WorkShift;

use App\Helpers\RedisHelper;
use App\Repositories\BaseRepository;
use App\Repositories\RepositoryInterface;
//use App\Repositories\WorkShift\WorkShiftInterface;
//use App\Repositories\WorkShift\WorkShiftInterface;
use Carbon\Carbon;
use Modules\Tasks\Entities\WorkShift;

class WorkShiftRespository extends BaseRepository implements WorkShiftInterface, RepositoryInterface
{
    public function getModel()
    {
        return WorkShift::class;
    }

    const REDIS_WORK_SHIFT_BUILDING_ID = "work_shift_building_id:%s";
    const REDIS_WORK_SHIFT_ID = "work_shift:%s";
    const REDIS_WORK_SHIFT_TASKS_ID = "work_shift_tasks:%s";

    public function filterByBuildingId($buildingId)
    {
        $keyWorkShiftBuildingId = RedisHelper::createKey(self::REDIS_WORK_SHIFT_BUILDING_ID, $buildingId);
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
        $keyWorkShiftBuildingId = RedisHelper::createKey(self::REDIS_WORK_SHIFT_BUILDING_ID, $buildingId);
        $workShifts = $this->model->where(['building_id' => $buildingId])->get();
        if($workShifts) {
            RedisHelper::set($keyWorkShiftBuildingId, $workShifts);
        }
        return $workShifts;
    }

    public function showTasks($buildingId)
    {
        $keyWorkShiftBuildingId = RedisHelper::createKey(self::REDIS_WORK_SHIFT_TASKS_ID, $buildingId);
        $workShifts = RedisHelper::get($keyWorkShiftBuildingId);
        if($workShifts) {
            return $workShifts;
        }
        // Reload redis cache SubTask & TaskUser khi add, update, delete
        $workShifts = $this->model->with('tasks.subTasks', 'tasks.taskUsers')->where(['building_id' => $buildingId])->get();
        if($workShifts) {
            RedisHelper::set($keyWorkShiftBuildingId, $workShifts);
        }
        return $workShifts;
    }

    public function reloadShowTasks($buildingId)
    {
        $keyWorkShiftBuildingId = RedisHelper::createKey(self::REDIS_WORK_SHIFT_TASKS_ID, $buildingId);
        // Reload redis cache SubTask & TaskUser khi add, update, delete
        $workShifts = $this->model->with('tasks.subTasks', 'tasks.taskUsers')->where(['building_id' => $buildingId])->get();
        if($workShifts) {
            RedisHelper::set($keyWorkShiftBuildingId, $workShifts);
        }
        return $workShifts;
    }

    public function filterById($id)
    {
        $keyWorkShiftId = RedisHelper::createKey(self::REDIS_WORK_SHIFT_ID, $id);
        $workShift = RedisHelper::get($keyWorkShiftId);
        if($workShift) {
            return $workShift;
        }
        $workShift = $this->model->find($id);
        if($workShift) {
            RedisHelper::set($keyWorkShiftId, $workShift);
        }
        return $workShift;
    }

    public function reloadById($id)
    {
        $keyWorkShiftId = RedisHelper::createKey(self::REDIS_WORK_SHIFT_ID, $id);
        $workShift = $this->model->find($id);
        if($workShift) {
            RedisHelper::set($keyWorkShiftId, $workShift);
        }
        return $workShift;
    }

    public function deleteRedisCache($model = null)
    {
        if($model != null) {
            $this->reloadByBuildingId($model->building_id);
            $this->reloadShowTasks($model->building_id);

            $keyWorkShiftId = RedisHelper::createKey(self::REDIS_WORK_SHIFT_ID, $model->id);
            $workShift = RedisHelper::get($keyWorkShiftId);
            if($workShift) {
                RedisHelper::delete($keyWorkShiftId);
            }
        }
    }

}
