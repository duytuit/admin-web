<?php

namespace Modules\Assets\Repositories\Area;

use App\Helpers\RedisHelper;
use App\Repositories\BaseRepository;
use App\Repositories\RepositoryInterface;
use Carbon\Carbon;
use Modules\Assets\Entities\Area;

class AreaRespository extends BaseRepository implements AreaInterface, RepositoryInterface
{
    public function getModel()
    {
        return Area::class;
    }

    const REDIS_AREA_BUILDING_ID = "area_building_id:%s";
    const REDIS_AREA_ID = "area:%s";

    public function filterByBuildingId($buildingId)
    {
        $keyAreaBuildingId = RedisHelper::createKey(self::REDIS_AREA_BUILDING_ID, $buildingId);
        $areas = RedisHelper::get($keyAreaBuildingId);
        if($areas) {
            return $areas;
        }
        $areas = $this->model->where(['building_id' => $buildingId])->get();
        if($areas) {
            RedisHelper::set($keyAreaBuildingId, $areas);
        }
        return $areas;
    }

    public function reloadByBuildingId($buildingId)
    {
        $keyAreaBuildingId = RedisHelper::createKey(self::REDIS_AREA_BUILDING_ID, $buildingId);
        $areas = $this->model->where(['building_id' => $buildingId])->get();
        if($areas) {
            RedisHelper::set($keyAreaBuildingId, $areas);
        }
        return $areas;
    }

    public function filterById($id)
    {
        $keyAreaId = RedisHelper::createKey(self::REDIS_AREA_ID, $id);
        $area = RedisHelper::get($keyAreaId);
        if($area) {
            return $area;
        }
        $area = $this->model->find($id);
        if($area) {
            RedisHelper::set($keyAreaId, $area);
        }
        return $area;
    }

    public function reloadById($id)
    {
        $keyAreaId = RedisHelper::createKey(self::REDIS_AREA_ID, $id);
        $area = $this->model->find($id);
        if($area) {
            RedisHelper::set($keyAreaId, $area);
        }
        return $area;
    }

    public function deleteRedisCache($model = null)
    {
        if($model != null) {
            $this->reloadByBuildingId($model->building_id);

            $keyAreaId = RedisHelper::createKey(self::REDIS_AREA_ID, $model->id);
            $area = RedisHelper::get($keyAreaId);
            if($area) {
                RedisHelper::delete($keyAreaId);
            }
        }
    }
}
