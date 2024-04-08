<?php

namespace App\Repositories\Permissions;

use App\Repositories\Contracts\RepositoryInterface;
use App\Repositories\Eloquent\Repository;

class PermissionsRepository extends Repository {




    /**
     * Specify Model class name
     *
     * @return mixed
     */
    function model()
    {
        return \App\Models\Permissions\Permission::class;
    }

    public function findPermission($id)
    {
        return $this->model->findOrFail($id);
    }

    public function myPaginate($page)
    {
        return $this->model
            ->with('module')
            ->orderBy('updated_at', 'DESC')
            ->paginate($page);
    }

    public function disablePermission($array)
    {
        return $this->model->whereNotIn('id', array_values($array))->pluck('id')->toArray();
    }

    public function paginateWithId($arrayId, $page)
    {
        return $this->model
            ->with('module')
            ->whereIn('id', $arrayId)
            ->orderBy('updated_at', 'DESC')
            ->paginate($page);
    }
}
