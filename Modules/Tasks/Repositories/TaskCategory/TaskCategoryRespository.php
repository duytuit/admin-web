<?php

namespace Modules\Tasks\Repositories\TaskCategory;

use App\Helpers\RedisHelper;
use App\Repositories\BaseRepository;
use App\Repositories\RepositoryInterface;
use Carbon\Carbon;
use Modules\Tasks\Entities\TaskCategory;

class TaskCategoryRespository extends BaseRepository implements TaskCategoryInterface, RepositoryInterface
{
    public function getModel()
    {
        return TaskCategory::class;
    }

    const REDIS_TASK_CATEGORY_BUILDING_ID = "task_category_building_id:%s";
    const REDIS_TASK_CATEGORY_ID = "task_category:%s";

    public function filterByBuildingId($buildingId)
    {
        $keyTaskCategoryBuildingId = RedisHelper::createKey(self::REDIS_TASK_CATEGORY_BUILDING_ID, $buildingId);
        $taskCategories = RedisHelper::get($keyTaskCategoryBuildingId);
        if($taskCategories) {
            return $taskCategories;
        }
        $taskCategories = $this->model->where(['building_id' => $buildingId])->get();
        if($taskCategories) {
            RedisHelper::set($keyTaskCategoryBuildingId, $taskCategories);
        }
        return $taskCategories;
    }

    public function reloadByBuildingId($buildingId)
    {
        $keyTaskCategoryBuildingId = RedisHelper::createKey(self::REDIS_TASK_CATEGORY_BUILDING_ID, $buildingId);
        $taskCategories = $this->model->where(['building_id' => $buildingId])->get();
        if($taskCategories) {
            RedisHelper::set($keyTaskCategoryBuildingId, $taskCategories);
        }
        return $taskCategories;
    }

    public function filterById($id)
    {
        $keyTaskCategoryId = RedisHelper::createKey(self::REDIS_TASK_CATEGORY_ID, $id);
        $taskCategory = RedisHelper::get($keyTaskCategoryId);
        if($taskCategory) {
            return $taskCategory;
        }
        $taskCategory = $this->model->find($id);
        if($taskCategory) {
            RedisHelper::set($keyTaskCategoryId, $taskCategory);
        }
        return $taskCategory;
    }

    public function reloadById($id)
    {
        $keyTaskCategoryId = RedisHelper::createKey(self::REDIS_TASK_CATEGORY_ID, $id);
        $taskCategory = $this->model->find($id);
        if($taskCategory) {
            RedisHelper::set($keyTaskCategoryId, $taskCategory);
        }
        return $taskCategory;
    }

    public function deleteRedisCache($model = null)
    {
        if($model != null) {
            $this->reloadByBuildingId($model->building_id);

            $keyTaskCategoryId = RedisHelper::createKey(self::REDIS_TASK_CATEGORY_ID, $model->id);
            $taskCategory = RedisHelper::get($keyTaskCategoryId);
            if($taskCategory) {
                RedisHelper::delete($keyTaskCategoryId);
            }
        }
    }
}
