<?php

namespace App\Repositories\VehicleCategory;

//use App\Repositories\Contracts\RepositoryInterface;

use App\Helpers\dBug;
use App\Repositories\Eloquent\Repository;

class VehicleCategoryRespository extends Repository {




    /**
     * Specify Model class name
     *
     * @return mixed
     */
    function model()
    {
        return \App\Models\VehicleCategory\VehicleCategory::class;
    }
    public function findAllBy($colums = 'id',$id)
    {
        return $this->model->where($colums, $id)->get();
    }
    public function searchByAll(array $options = [])
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

        return $model->orderByRaw($options['order_by'])->whereNull('bdc_service_id')->paginate($options['per_page']);
    }
    public function searchByAll_v2(array $options = [])
    {
        $default = [
            'select'   => '*',
            'where'    => [],
            'order_by' => 'id DESC',
            'per_page' => 20,
        ];
        $options = array_merge($default, $options);

        extract($options);

        $model = $this->model;

        if ($options['where']) {
            $model = $model->where($options['where']);
        }

        return $model->orderByRaw($options['order_by'])->whereNotNull('bdc_service_id')->paginate($options['per_page']);
    }
    public function getOne($colums = 'id',$id)
    {
        $row = $this->model->where($colums, $id)->first();
        return $row;
    }
}
