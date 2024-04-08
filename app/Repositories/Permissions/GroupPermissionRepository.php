<?php

namespace App\Repositories\Permissions;

use App\Repositories\Eloquent\Repository;

class GroupPermissionRepository extends Repository {

    /**
     * Specify Model class name
     *
     * @return mixed
     */
    function model()
    {
        return \App\Models\Permissions\GroupPermissions::class;
    }

    public function createGroupPermission($department, $ids)
    {
        return $this->model->create([
           'admin_id' => $department->head_department->pub_user_id ?? 0,
            'parent_id' => $department->id,
            'permission_ids' => !empty($ids)?serialize($ids):serialize([26]),
            'created_by' => auth()->user()->id
        ])->id;
    }

    public function updateGroupPermisson($idGroup, $ids)
    {
        return $this->model->find($idGroup)->update([
            'permission_ids' => !empty($ids)?serialize($ids):serialize([26]),
            'updated_by' => auth()->user()->id
        ]);
    }

    public function updateHeadGroup($staff)
    {
        if ($staff) {
            $this->model->where('parent_id', $staff->bdc_department_id)->update([
               'admin_id' => $staff->pub_user_id
            ]);
        }
    }

}
