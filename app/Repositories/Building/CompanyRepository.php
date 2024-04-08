<?php

namespace App\Repositories\Building;

use App\Helpers\dBug;
use App\Models\Building\Urban;
use App\Models\Building\V2\Company;
use App\Repositories\Eloquent\Repository;

class CompanyRepository extends Repository {
    /**
     * Specify Model class name
     *
     * @return mixed
     */
    function model()
    {
        return Company::class;
    }

    public function checkCompanyExist($code)
    {
        return $this->model->where('code', $code)->first();
    }

    public function getPrefixCompanyCode($building_id)
    {
        return $this->model->whereHas('building', function($query) use ($building_id){
            return $query->where('id', $building_id);
        })->first();
    }

    public function getAll($request)
    {
        return $this->model->where(function($query) use ($request){
            if(isset($request->name) && $request->name !=null){
                $query->where('name', '%'.$request->name.'%');
            }
        });
    }

    public function getAll1($request)
    {
        return $this->model->where(function($query) use ($request){
            if(isset($request->name) && $request->name !=null){
                $query->where('name', '%'.$request->name.'%');
                $query->where('id',2);
            }
        })->where('id',2);
    }
   
    public function getUrbanByCompany(array $options = [],$company_id)
    {
        $default = [
            'select'   => '*',
            'where'    => [],
            'order_by' => 'id DESC',
            'per_page' => 20,
        ];

        $options = array_merge($default, $options);

        extract($options);

        $model = Urban::select($options['select']);

        if ($options['where']) {
            $model = $model->where($options['where']);
        }
        $model = $model->where('company_id',$company_id);
        return $model->orderByRaw($options['order_by'])->paginate($options['per_page']);
    }
}
