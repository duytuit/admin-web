<?php

namespace App\Repositories\BuildingHandbook;

use App\Repositories\Contracts\RepositoryInterface;
use App\Repositories\Eloquent\Repository;

class BuildingHandbookRepository extends Repository {




    /**
     * Specify Model class name
     *
     * @return mixed
     */
    function model()
    {
      return \App\Models\BuildingHandbook\BuildingHandbook::class;
    }

    public function myPaginate($keyword, $per_page, $active_building)
    {
        return $this->model
          ->with('handbook_category', 'pub_profile')
          ->where('bdc_building_id', $active_building)
          ->filter($keyword)
          ->orderBy('updated_at', 'DESC')
          ->paginate($per_page);
    }
    public function myPaginateApi($keyword, $per_page, $active_building,$category_id)
    {
        $hb = $this->model->with('handbook_category', 'pub_profile')->where('bdc_building_id', $active_building)->where('status', 1);
        if($category_id){
            $hb = $hb->where('bdc_handbook_category_id',$category_id);
        }
        $hb = $hb->filter($keyword)->orderBy('updated_at', 'DESC')->paginate($per_page);
        return $hb;
    }
    public function myPaginateApiWithID($request,$keyword, $per_page, $active_building,$category_id)
    {
        $hb = $this->model->with('handbook_category')->where('bdc_building_id', $active_building)->where('status', 1);
        if($category_id){
            $hb = $hb->where('bdc_handbook_category_id',$category_id);
        }else{
            $hb = $hb->whereNull('bdc_handbook_category_id');
        }
        $hb = $hb->filter($request)->orderBy('updated_at', 'DESC')->paginate($per_page);
        return $hb;
    }
    public function myPaginateInternalApi($keyword, $per_page, $active_building,$type_id)
    {
        $hb = $this->model->with('handbook_category', 'pub_profile')->where('bdc_building_id', $active_building)->where('status', 1);
        if($type_id){
            $hb = $hb->where('bdc_handbook_type_id',$type_id);
        }
        $hb = $hb->filter($keyword)->orderBy('updated_at', 'DESC')->paginate($per_page);
        return $hb;
    }

    public function deleteMulti($ids)
    {
        return $this->model->whereIn('id', $ids)->delete();
    }

    public function getMenuHandbook($building_id)
    {
        return $this->model
            ->select('title', 'updated_at', 'id')
            ->where('bdc_building_id', $building_id)
            ->where('bdc_handbook_type_id', 3)
            ->orderBy('updated_at', 'DESC')
            ->limit(10)
            ->get();
    }
    public function getOne($building,$id)
    {
        return $this->model->where('bdc_building_id',$building)->with('handbook_category', 'pub_profile')->findOrFail($id);
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
