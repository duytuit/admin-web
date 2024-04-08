<?php

namespace App\Repositories\BuildingInfo;

use App\Models\BuildingInfo\BuildingInfo;
use App\Repositories\Eloquent\Repository;

const DEFAULT_PAGE = 10;
class BuildingInfoRepository extends Repository {
    /**
     * Specify Model class name
     *
     * @return mixed
     */
    function model()
    {
        return BuildingInfo::class;
    }

    function findByBuilding($building_id)
    {
        return $this->model->where('bdc_building_id', $building_id)->get();
    }

    function findBuildingInfo($id)
    {
        return $this->model->with('building')->findOrFail($id);
    }

    public function myPaginate()
    {
        return $this->model
            ->with('building')
            ->orderBy('updated_at', 'DESC')
            ->paginate(DEFAULT_PAGE);
    }
}
