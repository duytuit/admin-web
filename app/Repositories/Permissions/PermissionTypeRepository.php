<?php

namespace App\Repositories\Permissions;

use App\Models\Permissions\Module;
use App\Models\PublicUser\UserPermission;
use App\Repositories\Eloquent\Repository;

class PermissionTypeRepository extends Repository {

    /**
     * Specify Model class name
     *
     * @return mixed
     */
    function model()
    {
        return \App\Models\Permissions\PubPermissionType::class;
    }
    public function searchBy($request='',$where=[],$perpage = 20)
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
        $list_search = $model->orderByRaw($options['order_by'])->with('pubUser')->paginate($options['per_page']);
        return $list_search;
    }

    public function getOne($id)
    {
        return $this->model->where('id', $id)->first();
    }

    public function getByIds($ids)
    {
        return $this->model->whereIn('id',explode(',',$ids))->get();
    }

}
