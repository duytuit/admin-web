<?php

namespace Modules\Tasks\Repositories\SubTask;

use App\Helpers\RedisHelper;
use App\Repositories\BaseRepository;
use App\Repositories\RepositoryInterface;
use Carbon\Carbon;
use Modules\Tasks\Entities\SubTask;

class SubTaskRespository extends BaseRepository implements SubTaskInterface, RepositoryInterface
{
    public function getModel()
    {
        return SubTask::class;
    }

    const REDIS_SUB_TASK_BUILDING_ID = "sub_task_building_id:%s";
    const REDIS_SUB_TASK_ID = "sub_task:%s";

    public function filterByBuildingId($buildingId)
    {
    }

    public function reloadByBuildingId($buildingId)
    {
    }

    public function filterById($id)
    {
        $keySubTaskId = RedisHelper::createKey(self::REDIS_SUB_TASK_ID, $id);
        $subTask = RedisHelper::get($keySubTaskId);
        if($subTask) {
            return $subTask;
        }
        $subTask = $this->model->find($id);
        if($subTask) {
            RedisHelper::set($keySubTaskId, $subTask);
        }
        return $subTask;
    }

    public function reloadById($id)
    {
        $keySubTaskId = RedisHelper::createKey(self::REDIS_SUB_TASK_ID, $id);
        $subTask = $this->model->find($id);
        if($subTask) {
            RedisHelper::set($keySubTaskId, $subTask);
        }
        return $subTask;
    }

    public function deleteRedisCache($id = null)
    {
    }

    public function deleteByTaskId($taskId)
    {
        return $this->model->where(['task_id' => $taskId])->delete();
    }
}
