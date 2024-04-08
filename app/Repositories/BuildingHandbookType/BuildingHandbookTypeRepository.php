<?php

namespace App\Repositories\BuildingHandbookType;

use App\Repositories\Contracts\RepositoryInterface;
use App\Repositories\Eloquent\Repository;

class BuildingHandbookTypeRepository extends Repository {


    /**
     * Specify Model class name
     *
     * @return mixed
     */
    function model()
    {
      return \App\Models\BuildingHandbookType\BuildingHandbookType::class;
    }

    
    public function myPaginate($keyword)
    {
        return $this->model
            ->filter($keyword)
            ->orderBy('updated_at', 'DESC')
            ->paginate(15);
    }

    public function findByBuildingId($building_id)
    {
      return $this->model->where('bdc_building_id', $building_id)->get();
    }

}
