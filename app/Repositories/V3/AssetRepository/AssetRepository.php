<?php

namespace App\Repositories\V3\AssetRepository;

use App\Models\V3\Asset;
use App\Repositories\V3\BaseRepository\BaseRepository;
use Illuminate\Http\Request;

class AssetRepository extends BaseRepository implements AssetRepositoryInterface {

    /**
     * RoleRepository constructor.
     * @param Asset $model
     */
    public function __construct(Asset $model)
    {
        $this->model = $model;
    }

    public function filterByBuildingId($buildingId)
    {
        return $this->query()->where(['bdc_building_id' => $buildingId])
            ->orderBy('id', 'desc')
            ->get();
    }

    public function filterByRequest($model,Request $request)
    {

        $model = collect($model);

        if (isset($request->keyword_asset) && $request->keyword_asset !== null) {
            $model = $model->filter(function ($item) use ($request) {
                return stristr(strtolower($item->name), strtolower($request->keyword_asset));
            })->values();
        }
        if (isset($request->keyword_asset_category_id) && $request->keyword_asset_category_id !== null) {
            $model =  $model->filter(function ($item) use ($request) {
                return $item->asset_category_id == $request->keyword_asset_category_id;
            })->values();
        }
        if(isset($request->keyword_asset_area_id) && $request->keyword_asset_area_id != null) {
            $model = $model->filter(function ($item) use ($request) {
                return $item->area_id == $request->keyword_asset_area_id;
            })->values();
        }
        if(isset($request->keyword_asset_department_id) && $request->keyword_asset_department_id != null) {
            $model = $model->filter(function ($item) use ($request) {
                return $item->department_id == $request->keyword_asset_department_id;
            })->values();
        }

        return $model;

    }

    public function filter($model, $request)
    {
        $model = collect($model);

        $request = (object)$request;

        if(isset($request->asset_category_id) && $request->asset_category_id != null) {
            $model = $model->filter(function ($item) use ($request) {
                return $item->asset_category_id == $request->asset_category_id;
            })->values();
        }
        if(isset($request->area_id) && $request->area_id != null) {
            $model = $model->filter(function ($item) use ($request) {
                return $item->area_id == $request->area_id;
            })->values();
        }
        if(isset($request->department_id) && $request->department_id != null) {
            $model = $model->filter(function ($item) use ($request) {
                return $item->department_id == $request->department_id;
            })->values();
        }
        if(isset($request->name) && $request->name != null) {
            $model = $model->filter(function ($item) use ($request) {
                return stristr(strtolower($item->name), strtolower($request->name));
            })->values();
        }
        return $model;

    }

}
