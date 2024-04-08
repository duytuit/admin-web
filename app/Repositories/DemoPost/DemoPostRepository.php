<?php

namespace App\Repositories\DemoPost;

use App\Repositories\Contracts\RepositoryInterface;
use App\Repositories\Eloquent\Repository;

class DemoPostRepository extends Repository {




    /**
     * Specify Model class name
     *
     * @return mixed
     */
    function model()
    {
        return \App\Models\DemoPost\DemoPost::class;
    }

    // public function find()
    // {
    //     return $this->model->find($id, $columns);
    // }


}
