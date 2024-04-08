<?php

namespace App\Repositories\Permissions;

use App\Models\Permissions\Module;
use App\Models\PublicUser\UserPermission;
use App\Repositories\Eloquent\Repository;

class GroupsPermissionRepository extends Repository {

    /**
     * Specify Model class name
     *
     * @return mixed
     */
    function model()
    {
        return \App\Models\Permissions\GroupsPermissions::class;
    }
    public function searchBy($request='',$where=[],$perpage = 20,$building_id)
    {
        if (!empty($request->name)) {
            $where[] = ['name', 'Like', "%{$request->name}%"];
        }

        if (!empty($request->create_by)) {
            $where[] = ['create_by', '=', $request->create_by];
        }
        if (!empty($request->update_by)) {
            $where[] = ['update_by', '=', $request->update_by];
        }

        $default = [
            'select'   => '*',
            'where'    => $where,
            'order_by' => 'id DESC',
            'per_page' => $perpage,
        ];

        $options = array_merge($default, $where);

        extract($options);

        $model = $this->model->select($options['select']);

        if ($options['where']) {
            $model = $model->where($options['where']);
        }
        $model = $model->where('bdc_building_id',$building_id);
        $model = $model->orWhere('status',1);
        $list_search = $model->orderByRaw($options['order_by'])->with('pubUser')->paginate($options['per_page']);
        return $list_search;
    }


    public function findPermission($id)
    {
        $permissiongroups = $this->model->where('id', $id)->first();
//        dd($permissiongroups->permission_ids);
        if (!$permissiongroups->permission_ids) {
            return [];
        }
        $permission_ids = unserialize($permissiongroups->permission_ids);
        return $permission_ids;
    }
    public function getOne($id)
    {
        return $this->model->where('id', $id)->first();
    }

    public function createGroupPermission($department, $ids)
    {
        return $this->model->create([
           'admin_id' => $department->head_department->pub_user_id ?? 0,
            'parent_id' => $department->id,
            'permission_ids' => serialize($ids),
            'created_by' => auth()->user()->id
        ])->id;
    }

    public function updateGroupPermisson($idGroup, $ids)
    {
        return $this->model->find($idGroup)->update([
            'permission_ids' => serialize($ids),
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
    public function updatePermission($id, $permissions, $moduleId)
    {
        $permissions = $permissions ? $permissions : [];
        $permissionGroups = $this->model->where('id', $id)->first();
        if ($permissionGroups) {
            if($permissionGroups->permission_ids != null){
                $permission = unserialize($permissionGroups->permission_ids);
            }else{
                $permission=[];
            }
            $module = Module::with('permissions')->find($moduleId);

            $permissionModules = $module->permissions->pluck('id')->toArray();

            $permissionNotInModule = array_diff($permission, $permissionModules);

            $permissionGroups = array_merge($permissionNotInModule, $permissions);
            $this->model->where('id',$id)->update(['permission_ids'=>serialize($permissionGroups)]);
        } else {
            if (count($permissions) != 0) {
                $permissionGroups = $permissions;
                $this->model->where('id',$id)->update(['permission_ids'=>serialize($permissionGroups)]);
            }
        }
    }

    public function searchByAll(array $options = [],$request = null)
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
        if($request){
            if($request->keyword){
                $model = $model->where('name', 'like', '%' . $request->keyword . '%');
            }
        }
        if ($options['where']) {
            $model = $model->where($options['where']);
        }
        return $model->orderByRaw($options['order_by'])->paginate($options['per_page']);
    }
    public function getByIds($ids)
    {
        return $this->model->whereIn('id',explode(',',$ids))->get();
    }
    public function getByIdsa($ids)
    {
        return $this->model->whereIn('id',$ids)->get();
    }
    public function getAll($building_id)
    {
        return $this->model->where('bdc_building_id',$building_id)->orWhere('status',1)->get();
    }

}
