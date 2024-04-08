<?php

namespace App\Repositories\AppsActive;

use App\Repositories\Eloquent\Repository;
use App\Models\AppsActive\AppsActive;

class AppsActiveRepository extends Repository
{


    /**
     * Specify Model class name
     *
     * @return mixed
     */
    function model()
    {
        return AppsActive::class;
    }

    public function findAppIdActive($domain)
    {
        return $this->model->where([
            'status' => 1,
            'domain' => $domain
        ])->get();
    }
}