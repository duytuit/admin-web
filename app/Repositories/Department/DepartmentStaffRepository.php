<?php

namespace App\Repositories\Department;

use App\Repositories\Eloquent\Repository;
use App\Models\DepartmentStaff\DepartmentStaff;

class DepartmentStaffRepository extends Repository {


    /**
     * Specify Model class name
     *
     * @return mixed
     */
    function model()
    {
        return DepartmentStaff::class;
    }

    public function findStaff($id)
    {
        return $this->model->findOrFail($id);
    }

    public function getAllPublicUserStaff()
    {
        $staff = $this->model->all();
        return $staff->pluck('pub_user_id')->toArray();
    }

    public function addStaffDepartment($data, $departmentId)
    {
        foreach ($data['pub_user_ids'] as $item)
        {
            $this->model->create([
               'bdc_department_id' => $departmentId,
                'pub_user_id' => $item,
                'type' => $this->model::STAFF_DEPRATMENT
            ]);
        }
    }

    public function staffByDepartment($departmentId)
    {
        return $this->model->where('bdc_department_id', $departmentId)->whereHas('publicUser.BDCprofile')->get();
    }

    public function updateHeadDepartment($request)
    {
        $staff = $this->findStaff($request->id);
        if ($staff && $staff->type != $this->model::HEAD_DEPRATMENT) {
            $this->model->where('bdc_department_id', $request->departmentID)
                        ->where('type', $this->model::HEAD_DEPRATMENT)
                        ->update(['type' => $this->model::STAFF_DEPRATMENT]);

            $staff->update(['type' => $this->model::HEAD_DEPRATMENT]);
        }
    }
    public function updateHeadBuilding($request)
    {
        $staff = $this->findStaff($request->id);
        if ($staff && $staff->type != $this->model::HEAD_BUILDING) {
            $this->model->where('bdc_department_id', $request->departmentID)
                        ->where('type', $this->model::HEAD_BUILDING)
                        ->update(['type' => $this->model::STAFF_DEPRATMENT]);

            $staff->update(['type' => $this->model::HEAD_BUILDING]);
        }
    }
    public function updateChangeStaff($request)
    {
        $staff = $this->findStaff($request->id)->update(['type' => $this->model::STAFF_DEPRATMENT]);
    }
    public function getStaffByPubUser($user_id,$building_id)
    {
        return $this->model->where('pub_user_id',$user_id)->whereHas('department', function ($query) use ($building_id) { $query->where('bdc_building_id', '=', $building_id); })->with('department','department.permissions')->first();
    }
}
