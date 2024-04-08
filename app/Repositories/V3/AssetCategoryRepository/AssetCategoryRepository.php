<?php

namespace App\Repositories\V3\AssetCategoryRepository;

use App\Models\V3\AssetCategory;
use App\Repositories\V3\BaseRepository\BaseRepository;

class AssetCategoryRepository extends BaseRepository implements AssetCategoryRepositoryInterface
{

    /**
     * RoleRepository constructor.
     * @param AssetCategory $model
     */
    public function __construct(AssetCategory $model)
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
                return stripos($item->title,$request->keyword)!==false||stripos($item->id,$request->keyword)!==false;
            })->values();
        }

        return $model;

    }

}
