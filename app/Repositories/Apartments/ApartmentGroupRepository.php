<?php

namespace App\Repositories\Apartments;

use App\Repositories\Eloquent\Repository;

class ApartmentGroupRepository extends Repository
{

    const STATUS_INACTIVE = 0;
    const STATUS_ACTIVE = 1;

    function model()
    {
        return \App\Models\Apartments\ApartmentGroup::class;
    }

    public function searchBy($building_id, $request, $where = [], $perpage = 20)
    {
        if ($request->status != null && $request->status != 'false') {
            $where[] = ['status', '=', $request->status];
        }

        $default = [
            'select'   => '*',
            'where'    => $where,
            'order_by' => 'id DESC',
            'per_page' => $perpage,
        ];

        $options = array_merge($default, $where);

        extract($options);

        $model = $this->model;

        if ($options['where']) {
            $model = $model->where($options['where']);
        }

        $model = $model->where('bdc_building_id',$building_id);

        if (!empty($request->name_group)) {
            $model->Where(function ($query) use ($request) {
                $query->orWhere('name', 'like', '%' . $request->name_group . '%');
            });
        }

        return $model->orderByRaw($options['order_by'])->paginate($options['per_page']);
    }
    public function searchByV2(array $options = [],$building_id)
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
        $model = $model->where('bdc_building_id',$building_id);

        return $model->orderByRaw($options['order_by'])->paginate($options['per_page']);
    }
}
