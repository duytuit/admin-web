<?php

namespace App\Repositories\V3\AreaRepository;

use App\Models\V3\Area;
use App\Repositories\V3\BaseRepository\BaseRepository;

class AreaRepository extends BaseRepository implements AreaRepositoryInterface
{

    /**
     * RoleRepository constructor.
     * @param Area $model
     */
    public function __construct(Area $model)
    {
        $this->model = $model;
    }

    public function filterByBuildingId($buildingId)
    {
        return $this->query()->where([
            'building_id' => $buildingId
        ])->get();
    }

    public function filter($model, $request)
    {
        $model = collect($model);

        $request = (object)$request;

        if(isset($request->keyword) && $request->keyword != null) {
            $model = $model->filter(function ($item) use ($request) {
                return stripos($item->title,$request->keyword)!==false
                    ||stripos($item->id,$request->keyword)!==false
                    || stripos($item->code, $request->keyword)!==false;
            })->values();
        }

        return $model;

    }

}
