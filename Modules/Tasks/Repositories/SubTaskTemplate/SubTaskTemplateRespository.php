<?php

namespace Modules\Tasks\Repositories\SubTaskTemplate;

use App\Helpers\RedisHelper;
use App\Repositories\BaseRepository;
use App\Repositories\RepositoryInterface;
use Carbon\Carbon;
use Modules\Tasks\Entities\SubTaskTemplate;
use Modules\Tasks\Repositories\SubTaskTemplate\SubTaskTemplateInterface;
//
class SubTaskTemplateRespository extends BaseRepository implements SubTaskTemplateInterface, RepositoryInterface
{
    public function getModel()
    {
        return SubTaskTemplate::class;
    }

    const REDIS_SUB_TASK_TEMP_BUILDING_ID = "sub_task_template_building_id:%s";
    const REDIS_SUB_TASK_TEMP_ID = "sub_task_template:%s";

    public function filterByBuildingId($buildingId)
    {
        $keySubTaskTempBuildingId = RedisHelper::createKey(self::REDIS_SUB_TASK_TEMP_BUILDING_ID, $buildingId);
        $subTaskTemps = $this->model->with('department')->where(['building_id' => $buildingId])->orderBy('updated_at', 'desc')->get();
        if($subTaskTemps) {
            RedisHelper::set($keySubTaskTempBuildingId, $subTaskTemps);
        }
        return $subTaskTemps;
    }

    public function reloadByBuildingId($buildingId)
    {
        $keySubTaskTempBuildingId = RedisHelper::createKey(self::REDIS_SUB_TASK_TEMP_BUILDING_ID, $buildingId);
        $subTaskTemps = $this->model->with('department')->where(['building_id' => $buildingId])->get();
        if($subTaskTemps) {
            RedisHelper::set($keySubTaskTempBuildingId, $subTaskTemps);
        }
        return $subTaskTemps;
    }

    public function filterById($id)
    {
        $keySubTaskTempId = RedisHelper::createKey(self::REDIS_SUB_TASK_TEMP_ID, $id);
        $subTaskTemp = RedisHelper::get($keySubTaskTempId);
        if($subTaskTemp) {
            return $subTaskTemp;
        }
        // Cần reload lại cache ở Department & SubTaskTemplateInfo khi thêm mới or update
        $subTaskTemp = $this->model->with('department', 'sub_task_template_infos')->find($id);
        if($subTaskTemp) {
            RedisHelper::set($keySubTaskTempId, $subTaskTemp);
        }
        return $subTaskTemp;
    }

    public function reloadById($id)
    {
        $keySubTaskTempId = RedisHelper::createKey(self::REDIS_SUB_TASK_TEMP_ID, $id);
        // Cần reload lại cache ở Department & SubTaskTemplateInfo khi thêm mới or update
        $subTaskTemp = $this->model->with('department', 'sub_task_template_infos')->find($id);
        if($subTaskTemp) {
            RedisHelper::set($keySubTaskTempId, $subTaskTemp);
        }
        return $subTaskTemp;
    }

    public function deleteRedisCache($model = null)
    {
        if($model != null) {
            $this->reloadByBuildingId($model->building_id);

            $keySubTaskTempId = RedisHelper::createKey(self::REDIS_SUB_TASK_TEMP_ID, $model->id);
            $subTaskTemp = RedisHelper::get($keySubTaskTempId);
            if($subTaskTemp) {
                RedisHelper::delete($keySubTaskTempId);
            }
        }
    }
}
