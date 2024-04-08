<?php

namespace Modules\Assets\Repositories\AssetCategory;

use App\Helpers\RedisHelper;
use App\Repositories\BaseRepository;
use App\Repositories\RepositoryInterface;
use Carbon\Carbon;
use Modules\Assets\Entities\AssetCategory;

class AssetCategoryRespository extends BaseRepository implements AssetCategoryInterface, RepositoryInterface
{
    public function getModel()
    {
        return AssetCategory::class;
    }

    const REDIS_ASSET_CATEGORY_BUILDING_ID = "asset_category_building_id:%s";
    const REDIS_ASSET_CATEGORY_ID = "asset_category:%s";

    public function filterByBuildingId($buildingId)
    {
        $keyAssetCategoryBuildingId = RedisHelper::createKey(self::REDIS_ASSET_CATEGORY_BUILDING_ID, $buildingId);
        // $assets = RedisHelper::get($keyAssetCategoryBuildingId);
        // if($assets) {
        //     return $assets;
        // }
        $assets = $this->model->where(['building_id' => $buildingId])->orderBy('updated_at', 'desc')->get();
        if($assets) {
            RedisHelper::set($keyAssetCategoryBuildingId, $assets);
        }
        return $assets;
    }

    public function reloadByBuildingId($buildingId)
    {
        $keyAssetCategoryBuildingId = RedisHelper::createKey(self::REDIS_ASSET_CATEGORY_BUILDING_ID, $buildingId);
        $assets = $this->model->where(['building_id' => $buildingId])->orderBy('updated_at', 'desc')->get();
        if($assets) {
            RedisHelper::set($keyAssetCategoryBuildingId, $assets);
        }
        return $assets;
    }

    public function filterById($id)
    {
        $keyAssetCategoryId = RedisHelper::createKey(self::REDIS_ASSET_CATEGORY_ID, $id);
        // $assetCategory = RedisHelper::get($keyAssetCategoryId);
        // if($assetCategory) {
        //     return $assetCategory;
        // }
        $assetCategory = $this->model->find($id);
        if($assetCategory) {
            RedisHelper::set($keyAssetCategoryId, $assetCategory);
        }
        return $assetCategory;
    }

    public function reloadById($id)
    {
        $keyAssetCategoryId = RedisHelper::createKey(self::REDIS_ASSET_CATEGORY_ID, $id);
        $assetCategory = $this->model->find($id);
        if($assetCategory) {
            RedisHelper::set($keyAssetCategoryId, $assetCategory);
        }
        return $assetCategory;
    }

    public function deleteRedisCache($model = null)
    {
        if($model != null) {
            $this->reloadByBuildingId($model->building_id);

            $keyAssetCategoryId = RedisHelper::createKey(self::REDIS_ASSET_CATEGORY_ID, $model->id);
            $assetCategory = RedisHelper::get($keyAssetCategoryId);
            if($assetCategory) {
                RedisHelper::delete($keyAssetCategoryId);
            }
        }
    }
}
