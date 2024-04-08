<?php

namespace Modules\Tasks\Repositories\Task;

use App\Helpers\RedisHelper;
//use App\Models\Task;
use App\Repositories\BaseRepository;
use App\Repositories\RepositoryInterface;
use Carbon\Carbon;
use Modules\Tasks\Entities\Task;

class TaskRespository extends BaseRepository implements TaskInterface, RepositoryInterface
{
    const NEW_TASK = 'task';

    public function getModel()
    {
        return Task::class;
    }

    const REDIS_TASK_BUILDING_ID = "task_building_id:%s";
    const REDIS_TASK_ID = "task:%s";

    public function filterByBuildingId($buildingId)
    {
        $keyTaskBuildingId = RedisHelper::createKey(self::REDIS_TASK_BUILDING_ID, $buildingId);
        $tasks = RedisHelper::get($keyTaskBuildingId);
        if($tasks) {
            return $tasks;
        }
        $tasks = $this->model->with('subTasks', 'taskUsers', 'department', 'taskCategory', 'workShift')
            ->select('id', 'building_id', 'bdc_department_id', 'task_category_id', 'work_shift_id', 'description', 'priority', 'task_name',
                'created_by', 'due_date', 'start_date', 'completed_on', 'maintenance_asset_id', 'related', 'type', 'status', 'supervisor')
            ->where(['building_id' => $buildingId])
            ->orderBy('updated_at', 'desc')
            ->get();
        if($tasks) {
            RedisHelper::set($keyTaskBuildingId, $tasks);
        }
        return $tasks;
    }
    public function filterByBuildingIdWeb($request,$buildingId,$per_page)
    {
        $tasks = $this->model->with('subTasks', 'taskUsers', 'department', 'taskCategory', 'workShift')
            ->select('id', 'building_id', 'bdc_department_id', 'task_category_id', 'work_shift_id', 'description', 'priority', 'task_name',
                'created_by', 'due_date', 'start_date', 'completed_on', 'maintenance_asset_id', 'related', 'type', 'status', 'supervisor')
            ->where(['building_id' => $buildingId])
            ->where(function ($query) use ($request) {
                if (isset($request->task_category_id) && $request->task_category_id != null) {
                    $query->where('task_category_id', $request->task_category_id);
                }
                if (isset($request->bdc_department_id) && $request->bdc_department_id != null) {
                    $query->where('bdc_department_id', $request->bdc_department_id);
                }
                if (isset($request->start_date) && $request->start_date != null) {
                    $query->whereDate('start_date', '>=', $request->start_date);
                }
                if (isset($request->end_date) && $request->end_date != null) {
                    $query->whereDate('start_date', '<=', $request->end_date);
                }
                if (isset($request->task_name) && $request->task_name != null) {
                    $query->whereDate('task_name', $request->task_name);
                }
                if (isset($request->status) && $request->status != null) {
                    $query->whereDate('status', $request->status);
                }
                if (isset($request->type) && $request->type != null) {
                    $query->whereDate('type', $request->type);
                }
            })
            ->whereHas('taskUsers', function ($query) use ($request){
                if(isset($request->user_id) && $request->user_id != null){
                    $query->where('user_id',$request->user_id);
                }
             })
            ->orderBy('updated_at', 'desc')
            ->paginate($per_page);
        return $tasks;
    }

    public function reloadByBuildingId($buildingId)
    {
        $keyTaskBuildingId = RedisHelper::createKey(self::REDIS_TASK_BUILDING_ID, $buildingId);
        $tasks = $this->model->with('subTasks', 'taskUsers', 'department', 'taskCategory', 'workShift')
            ->select('id', 'building_id', 'bdc_department_id', 'task_category_id', 'work_shift_id', 'description', 'priority', 'task_name',
                'created_by', 'due_date', 'start_date', 'completed_on', 'maintenance_asset_id', 'related', 'type', 'status', 'supervisor')
            ->where(['building_id' => $buildingId])
            ->orderBy('updated_at', 'desc')
            ->get();
        if($tasks) {
            RedisHelper::set($keyTaskBuildingId, $tasks);
        }
        return $tasks;
    }

    public function filterById($id)
    {
        $keyTaskId = RedisHelper::createKey(self::REDIS_TASK_ID, $id);
        $task = RedisHelper::get($keyTaskId);
        if($task) {
            return $task;
        }
        $task = $this->model->with('showSubTasks', 'taskUsers', 'taskFiles', 'apartment', 'feedbackUser')->find($id);
        if($task) {
            RedisHelper::set($keyTaskId, $task);
        }
        return $task;
    }

    public function filterByIdWeb($id)
    {
        $task = $this->model->with('showSubTasks', 'taskUsers', 'taskFiles', 'apartment', 'feedbackUser')->find($id);
        $task->attach_file = $task->attach_file != null ? json_decode($task->attach_file) : [];
            $subTasks = @$task->sub_tasks != null ? @$task->sub_tasks : @$task->subTasks;
            if($subTasks != null) {
                $subTaskArr = [];
                foreach(@$subTasks as $_subTask) {
                    $_subTask->attach_file = $_subTask->attach_file != null ? json_decode($_subTask->attach_file) : [];
                    array_push($subTaskArr, $_subTask);
                }
                $task->sub_tasks = $subTaskArr;
            }
        $result['data'] = $task->toArray();
        return $result;
    }

    public function reloadById($id)
    {
        $keyTaskId = RedisHelper::createKey(self::REDIS_TASK_ID, $id);
        $task = $this->model->with('showSubTasks', 'taskUsers', 'taskFiles', 'apartment', 'feedbackUser')->find($id);
        if($task) {
            RedisHelper::set($keyTaskId, $task);
        }
        return $task;
    }

    public function deleteRedisCache($id = null)
    {
        if($id != null) {
            $keyTaskId = RedisHelper::createKey(self::REDIS_TASK_ID, $id);
            $task = RedisHelper::get($keyTaskId);
            if($task) {
                $buildingId = $task->building_id;
                $this->reloadByBuildingId($buildingId);
                RedisHelper::delete($keyTaskId);
            }
        }
    }

    public function add($user, $attributes)
    {
        return $this->model->create($attributes);
    }
}
