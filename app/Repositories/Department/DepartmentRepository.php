<?php

namespace App\Repositories\Department;

use App\Repositories\Eloquent\Repository;
use App\Models\Department\Department;

const DEFAULT_PAGE = 10;

class DepartmentRepository extends Repository {


    /**
     * Specify Model class name
     *
     * @return mixed
     */
    function model()
    {
        return Department::class;
    }

    public function myPaginate($keyword, $active_building)
    {
        return $this->model
            ->with('department_staffs', 'building', 'head_department', 'permissions')
            ->where('bdc_building_id', $active_building)
            ->whereNull('data_type')
            ->filter($keyword)
            ->orderBy('updated_at', 'DESC')
            ->paginate(DEFAULT_PAGE);
    }

    public function myPaginate1($keyword, $active_building)
    {
        return $this->model
            ->with('department_staffs', 'building', 'head_department', 'permissions')
            ->where('bdc_building_id', $active_building)
            ->where('Data_Type', 'PHONGBAN')
            ->filter($keyword)
            ->orderBy('updated_at', 'DESC')
            ->paginate(DEFAULT_PAGE);
    }

    public function findDepartment($id)
    {
        return $this->model->with('department_staffs', 'building', 'head_department', 'permissions')->findOrFail($id);
    }

    public function findByBuildingId($id)
    {
        return $this->model->where('bdc_building_id', $id)->get();
    }

    public function findByBuildingIdAndDepartmentId($building_id, $department_id)
    {
        return $this->model->with('department_staffs.publicUser')->where(
            [
                ['bdc_building_id', $building_id],
                ['id', $department_id],
            ]
        )->first();
    }
    public function countItem($building_id)
    {
        return $this->model->where('bdc_building_id', $building_id)->count();
    }
    public function listDepartments($building_id,$per_page)
    {
        return $this->model->where('bdc_building_id', $building_id)->where('status',1)->paginate($per_page);
    }
    public function listDepartmentsNew($request)
    {
        return $this->model->where(function ($query) use ($request) {
            if(isset($request->building_id)){
                $query->Where('bdc_building_id',$request->building_id);
            }
            if(isset($request->name)){
                $query->Where('name', 'like', '%' . $request->name . '%');
            }
        })->where('status',1);
    }
    public function getDepartment(array $options = [],$building_id)
    {
        $default = [
            'select'   => '*',
            'where'    => [],
            'order_by' => 'id DESC',
            'per_page' => 20,
        ];

        $options = array_merge($default, $options);

        extract($options);

        $model = $this->model->select($options['select']);

        if ($options['where']) {
            $model = $model->where($options['where']);
        }
        $model = $model->where('bdc_building_id',$building_id);
        return $model->orderByRaw($options['order_by'])->paginate($options['per_page']);
    }
}
