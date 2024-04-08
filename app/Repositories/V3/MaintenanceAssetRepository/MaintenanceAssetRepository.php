<?php

namespace App\Repositories\V3\MaintenanceAssetRepository;

use App\Models\V3\MaintenanceAsset;
use App\Repositories\V3\BaseRepository\BaseRepository;
use Carbon\Carbon;

class MaintenanceAssetRepository extends BaseRepository
{

    /**
     * RoleRepository constructor.
     * @param MaintenanceAsset $model
     */
    public function __construct(MaintenanceAsset $model)
    {
        $this->model = $model;
    }

    public function filterByBuildingId($buildingId)
    {
        return $this->query()
            ->with('asset.area')
            ->where("bdc_maintenance_assets.building_id", $buildingId)
            ->get();

    }

    public function filter($model, $data = [])
    {
        $model = collect($model);
        if(isset($data['title']) && $data['title'] != null) {
            $model = $model->filter(function ($item) use ($data) {
                return stristr($item->title, $data['title']);
            })->values();
        }
        if(isset($data['department_id']) && $data['department_id'] != null) {
            $model = $model->filter(function ($item) use ($data) {
                return $item->asset->department_id == $data['department_id'];
            })->values();
        }
        if(isset($data['asset_id']) && $data['asset_id'] != null) {
            $model = $model->filter(function ($item) use ($data) {
                return $item->asset_id == $data['asset_id'];
            })->values();
        }
        if(isset($data['start_date']) && $data['start_date'] != null) {
            $model = $model->filter(function ($item) use ($data) {
                return Carbon::parse($item->maintenance_time) >= Carbon::parse($data['start_date']);
            })->values();
        }
        if(isset($data['end_date']) && $data['end_date'] != null) {
            $model = $model->filter(function ($item) use ($data) {
                return Carbon::parse($item->maintenance_time) <= Carbon::parse( $data['end_date']);
            })->values();
        }
        if(isset($data['area_id']) && $data['area_id'] != null) {
            $model = $model->filter(function ($item) use ($data) {
                return isset($item->asset->area_id) && $item->asset->area_id == $data['area_id'];
            })->values();
        }
        if(isset($data['asset_category_id']) && $data['asset_category_id'] != null) {
            $model = $model->filter(function ($item) use ($data) {
                return isset($item->asset->asset_category_id) && $item->asset->asset_category_id == $data['asset_category_id'];
            })->values();
        }
        if(isset($data['asset_name']) && $data['asset_category_id'] != null) {
            $model = $model->filter(function ($item) use ($data) {
                return (isset($item->asset->name) && stripos($item->asset->name,$data['asset_name'])!==false) || (stripos($item->title,$data['asset_name'])!==false);
            })->values();
        }

        return $model;

    }

    public function deleteByAssetId($assetIds)
    {
        if (is_array($assetIds)) {
            return $this->query()
                ->whereIn('asset_id', $assetIds)
                ->forceDelete();
        }
        else {
            return $this->query()
                ->where('asset_id',$assetIds)
                ->forceDelete();
        }

    }

    public function filterById($id)
    {
        return $this->query()
            ->with('asset.area')
            ->find($id);
    }

}
