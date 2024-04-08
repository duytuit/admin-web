<?php

namespace App\Repositories\BusinessPartners;


use App\Repositories\Eloquent\Repository;
use App\Models\BusinessPartners\BusinessPartners;

class BusinessPartnerRepository extends Repository {
    /**
     * Specify Model class name
     *
     * @return mixed
     */
    function model()
    {
        return BusinessPartners::class;
    }
    public function myPaginate($keyword, $per_page, $active_building)
    {
        return $this->model
          ->where('bdc_building_id', $active_building)
          ->filter($keyword)
          ->orderBy('updated_at', 'DESC')
          ->paginate($per_page);
    }
    public function getPartnersWithStatus($active_building)
    {
        return $this->model
          ->where(['bdc_building_id'=> $active_building, 'status'=>1])
          ->orderBy('updated_at', 'DESC')->get();
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
