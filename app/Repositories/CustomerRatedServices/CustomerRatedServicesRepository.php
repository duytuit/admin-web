<?php

namespace App\Repositories\CustomerRatedServices;


use App\Repositories\Eloquent\Repository;
use App\Models\CustomerRatedServices\CustomerRatedServices;

class CustomerRatedServicesRepository extends Repository {
    /**
     * Specify Model class name
     *
     * @return mixed
     */
    function model()
    {
        return CustomerRatedServices::class;
    }
    public function myPaginate($keyword, $per_page, $building_id)
    {
        return $this->model->where(function($query) use ($building_id){
            if(isset($building_id) && $building_id !=null){
                $query->where('bdc_building_id',$building_id);
            }
        })
        ->filter($keyword)
        ->orderBy('updated_at', 'DESC')
        ->paginate($per_page);
    }
    public function searchByAll(array $options = [],$building_id)
    {
        $default = [
            'select'   => '*',
            'where'    => [],
            'order_by' => 'id DESC',
            'per_page' => 10,
        ];

        $options = array_merge($default, $options);
        extract($options);

        $model = $this->model->select($options['select']);

        if ($options['where']) {
            $model = $model->where($options['where']);
        }
        $model = $model->where('bdc_building_id',$building_id);
        return $model->orderByRaw($options['order_by'])->paginate($options['per_page']);
    }
}
