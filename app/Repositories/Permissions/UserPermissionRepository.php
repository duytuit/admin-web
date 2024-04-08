<?php

namespace App\Repositories\Permissions;

use App\Models\PublicUser\UserPermission;
use App\Repositories\Eloquent\Repository;
use App\Models\Permissions\Module;

class UserPermissionRepository extends Repository {

    /**
     * Specify Model class name
     *
     * @return mixed
     */
    function model()
    {
        return UserPermission::class;
    }

    public function getOne($id)
    {
        return $this->model->where('pub_user_id',$id)->first();
    }

    public function checkExit($id)
    {
        return $this->model->where('pub_user_id',$id)->count();
    }

    public function findPermission($id)
    {
        $permissionUser = $this->model->where('pub_user_id', $id)->first();
        if (!$permissionUser) {
            return [];
        }
        $permission_ids = unserialize($permissionUser->permissions);
        return $permission_ids;
    }

    public function updatePermission($id, $permissions, $moduleId)
    {

        $permissions = $permissions ? $permissions : [];
        $permissionUser = $this->model->where('pub_user_id', $id)->first();
        if ($permissionUser) {
            $permission = unserialize($permissionUser->permissions);
            $module = Module::with('permissions')->find($moduleId);
            $permissionModules = $module->permissions->pluck('id')->toArray();
            $permissionNotInModule = array_diff($permission, $permissionModules);
            $permissionsUsers = array_merge($permissionNotInModule, $permissions);
            $this->model->where('pub_user_id', $id)->delete();
            UserPermission::create([
                'pub_user_id' => $id,
                'permissions' => serialize($permissionsUsers)
            ]);
        } else {
            if (count($permissions) != 0) {
                $permissionsUsers = $permissions;
                UserPermission::create([
                    'pub_user_id' => $id,
                    'permissions' => serialize($permissionsUsers)
                ]);
            }
        }
    }

    public function updatePermissionApi($id, $permissions)
    {
        $permissions = $permissions ? $permissions : [];

        UserPermission::create([
            'pub_user_id' => $id,
            'permissions' => serialize($permissions)
        ]);
    }
}
