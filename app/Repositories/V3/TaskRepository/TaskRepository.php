<?php

namespace App\Repositories\V3\TaskRepository;

use App\Models\V3\Task;
use App\Repositories\V3\BaseRepository\BaseRepository;

class TaskRepository extends BaseRepository
{
    /**
     * RoleRepository constructor.
     * @param Task $model
     */
    public function __construct(Task $model)
    {
        $this->model = $model;
    }

    public function filterByBuildingId($buildingId){
        return $this->query()
            ->with('subTasks', 'taskUsers', 'department', 'taskCategory', 'workShift')
            ->select('id', 'building_id', 'bdc_department_id', 'task_category_id', 'work_shift_id', 'description', 'priority', 'task_name',
                'created_by', 'due_date', 'start_date', 'completed_on', 'maintenance_asset_id', 'related', 'type', 'status', 'supervisor')
            ->where(['building_id' => $buildingId])
            ->orderBy('updated_at', 'desc')
            ->get();
    }

    public function filter($model, $request) {
        $model = collect($model);

        $request = (object)$request;

        if(isset($request->maintenance_asset_id) && $request->maintenance_asset_id != null) {
            $model = $model->filter(function ($item) use ($request) {
                return $item->maintenance_asset_id == $request->maintenance_asset_id;
            })->values();
        }

        return $model;
    }

}
