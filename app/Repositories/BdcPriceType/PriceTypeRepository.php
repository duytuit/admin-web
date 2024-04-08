<?php

namespace App\Repositories\BdcPriceType;

use App\Repositories\Eloquent\Repository;

class PriceTypeRepository extends Repository {
    /**
     * Specify Model class name
     *
     * @return mixed
     */
    function model()
    {
        return \App\Models\BdcPriceType\PriceType::class;
    }

    function get_all(){
        return $this->model->orderBy('id')->get();
    }

}
