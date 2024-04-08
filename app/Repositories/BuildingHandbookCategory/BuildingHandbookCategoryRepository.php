<?php

namespace App\Repositories\BuildingHandbookCategory;

use App\Repositories\Contracts\RepositoryInterface;
use App\Repositories\Eloquent\Repository;


class BuildingHandbookCategoryRepository extends Repository {


    /**
     * Specify Model class name
     *
     * @return mixed
     */
    function model()
    {
      return \App\Models\BuildingHandbookCategory\BuildingHandbookCategory::class;
    }

    public function myPaginate($keyword, $per_page, $active_building)
    {
        return $this->model
            ->with('handbook_type', 'parent')
            ->where('bdc_building_id', $active_building)
            ->filter($keyword)
            ->orderBy('updated_at', 'DESC')
            ->paginate($per_page);
    }
    public function myPaginateApi($keyword, $per_page, $active_building,$type,$parent_id)
    {
        $hb = $this->model->with('handbook_type', 'parent')->where('bdc_building_id', $active_building);
        if($type){
            $hb = $hb->where('bdc_handbook_type_id', $type);
        }
        if($parent_id!=''){
            $hb = $hb->where('parent_id', $parent_id);
        }
        $hb = $hb->filter($keyword)->orderBy('updated_at', 'DESC')->paginate($per_page);
        return $hb;
    }
    public function getPhonePaginateApi($per_page, $active_building)
    {
        $hb = $this->model->where('bdc_building_id', $active_building)->orderBy('updated_at', 'DESC')->paginate($per_page);
        return $hb;
    }

    public function findByTypeAndBuildingId($type_id, $building_id)
    {
      return $this->model->where([
        ['bdc_handbook_type_id', $type_id],
        ['status', 1],
        ['bdc_building_id', $building_id],
      ])->get();
    }

    public function deleteMulti($ids)
    {
      return $this->model->whereIn('id', $ids)->delete();
    }

    public function findByBuildingId($building_id)
    {
      return $this->model->where('bdc_building_id', $building_id)->get();
    }

}
