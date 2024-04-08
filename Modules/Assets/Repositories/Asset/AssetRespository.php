<?php

namespace Modules\Assets\Repositories\Asset;

use App\Helpers\RedisHelper;
use App\Repositories\BaseRepository;
use App\Repositories\RepositoryInterface;
use Carbon\Carbon;
use Modules\Assets\Entities\Asset;

class AssetRespository extends BaseRepository implements AssetInterface, RepositoryInterface
{
    public function getModel()
    {
        return Asset::class;
    }

    const REDIS_ASSET_BUILDING_ID = "asset_building_id:%s";
    const REDIS_ASSET_ID = "asset:%s";

    public function filterByBuildingId($buildingId)
    {
        $keyAssetBuildingId = RedisHelper::createKey(self::REDIS_ASSET_BUILDING_ID, $buildingId);
        // $assets = RedisHelper::get($keyAssetBuildingId);
        // if ($assets) {
        //     return $assets;
        // }
        $assets = $this->model->where(['bdc_building_id' => $buildingId])
        ->orderBy('updated_at', 'desc')
            ->get();
        if ($assets) {
            RedisHelper::set($keyAssetBuildingId, $assets);
        }
        return $assets;
    }

    public function reloadByBuildingId($buildingId)
    {
        $keyAssetBuildingId = RedisHelper::createKey(self::REDIS_ASSET_BUILDING_ID, $buildingId);
        $assets = $this->model->where(['bdc_building_id' => $buildingId])
        ->orderBy('updated_at', 'desc')
            ->get();
        if ($assets) {
            RedisHelper::set($keyAssetBuildingId, $assets);
        }
        return $assets;
    }

    public function filterById($id)
    {
        $keyAssetId = RedisHelper::createKey(self::REDIS_ASSET_ID, $id);
        // $asset = RedisHelper::get($keyAssetId);
        // if ($asset) {
        //     return $asset;
        // }
        $asset = $this->model->find($id);
        if ($asset) {
            RedisHelper::set($keyAssetId, $asset);
        }
        return $asset;
    }

    public function reloadById($id)
    {
        $keyAssetId = RedisHelper::createKey(self::REDIS_ASSET_ID, $id);
        $asset = $this->model->find($id);
        if ($asset) {
            RedisHelper::set($keyAssetId, $asset);
        }
        return $asset;
    }

    public function deleteRedisCache($model = null)
    {
        if ($model != null) {
            $this->reloadByBuildingId($model->bdc_building_id);

            $keyAssetId = RedisHelper::createKey(self::REDIS_ASSET_ID, $model->id);
            $asset = RedisHelper::get($keyAssetId);
            if ($asset) {
                RedisHelper::delete($keyAssetId);
            }
        }
    }
}
