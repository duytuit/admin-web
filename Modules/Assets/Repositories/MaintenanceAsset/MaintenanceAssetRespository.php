<?php

namespace Modules\Assets\Repositories\MaintenanceAsset;

use App\Helpers\RedisHelper;
//use App\Models\MaintenanceAsset;
use App\Repositories\BaseRepository;
use App\Repositories\RepositoryInterface;
use Carbon\Carbon;
use Modules\Assets\Entities\MaintenanceAsset;

class MaintenanceAssetRespository extends BaseRepository implements MaintenanceAssetInterface, RepositoryInterface
{
    public function getModel()
    {
        return MaintenanceAsset::class;
    }

    const REDIS_MAINTENANCE_ASSET_BUILDING_ID = "maintenance_asset_building_id:%s";
    const REDIS_MAINTENANCE_ASSET_ID = "maintenance_asset:%s";

    public function filterByBuildingId($buildingId)
    {
        $keyMaintenanceAssetBuildingId = RedisHelper::createKey(self::REDIS_MAINTENANCE_ASSET_BUILDING_ID, $buildingId);
        // $maintenanceAssets = RedisHelper::get($keyMaintenanceAssetBuildingId);
        // if($maintenanceAssets) {
        //     return $maintenanceAssets;
        // }
        $maintenanceAssets = $this->model->with('asset.area')
            // ->whereHas('asset', function($query) use ($buildingId) {
            //     $query->where('bdc_building_id', $buildingId);
            // })
            ->where('bdc_maintenance_assets.building_id', $buildingId)
            // ->where('bdc_maintenance_assets.status', MaintenanceAsset::STATUS_PEDDING)
            ->orderBy('updated_at', 'desc')
            ->get();
        if($maintenanceAssets) {
            RedisHelper::set($keyMaintenanceAssetBuildingId, $maintenanceAssets);
        }
        return $maintenanceAssets;
    }

    public function reloadByBuildingId($buildingId)
    {
        $keyMaintenanceAssetBuildingId = RedisHelper::createKey(self::REDIS_MAINTENANCE_ASSET_BUILDING_ID, $buildingId);
        $maintenanceAssets = $this->model->with('asset.area')
            ->where('bdc_maintenance_assets.building_id', $buildingId)
            // ->where('bdc_maintenance_assets.status', MaintenanceAsset::STATUS_PEDDING)
            ->get();
        if($maintenanceAssets) {
            RedisHelper::set($keyMaintenanceAssetBuildingId, $maintenanceAssets);
        }
        return $maintenanceAssets;
    }

    public function filterById($id)
    {
        $keyMaintenanceAssetId = RedisHelper::createKey(self::REDIS_MAINTENANCE_ASSET_ID, $id);
        // $maintenanceAsset = RedisHelper::get($keyMaintenanceAssetId);
        // if($maintenanceAsset) {
        //     return $maintenanceAsset;
        // }
        $maintenanceAsset = $this->model->with('asset.area')->find($id);
        if($maintenanceAsset) {
            RedisHelper::set($keyMaintenanceAssetId, $maintenanceAsset);
        }
        return $maintenanceAsset;
    }
    public function filterByIdWeb($id)
    {
        
        $maintenanceAsset = $this->model->with('asset.area')->find($id)->toArray();
        $result['data'] = $maintenanceAsset;
        return $result;
    }

    public function reloadById($id)
    {
        $keyMaintenanceAssetId = RedisHelper::createKey(self::REDIS_MAINTENANCE_ASSET_ID, $id);
        $maintenanceAsset = $this->model->with('asset.area')->find($id);
        if($maintenanceAsset) {
            RedisHelper::set($keyMaintenanceAssetId, $maintenanceAsset);
        }
        return $maintenanceAsset;
    }

    public function deleteRedisCache($model = null)
    {
        if($model != null) {
            $this->reloadByBuildingId($model->building_id);

            $keyMaintenanceAssetId = RedisHelper::createKey(self::REDIS_MAINTENANCE_ASSET_ID, $model->id);
            $maintenanceAsset = RedisHelper::get($keyMaintenanceAssetId);
            if($maintenanceAsset) {
                RedisHelper::delete($keyMaintenanceAssetId);
            }
        }
    }
}
