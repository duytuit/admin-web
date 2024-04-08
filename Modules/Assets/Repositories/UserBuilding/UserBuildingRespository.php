<?php

namespace Modules\Assets\Repositories\UserBuilding;

use App\Helpers\RedisHelper;
use App\Repositories\BaseRepository;
use Carbon\Carbon;
use Modules\Assets\Entities\UserBuilding;

class UserBuildingRespository extends BaseRepository implements UserBuildingInterface
{
    public function getModel()
    {
        return UserBuilding::class;
    }

    const REDIS_USER_BUILDING = "user_building:%s";
    const REDIS_USER_BUILDING_FIRST = "user_building_first:%s";

    public function filterByBuildingId($buildingId)
    {
        return $this->model->where('building_id', $buildingId)->get();
    }

    public function filterByUserId($userId)
    {
        $keyUserBuilding = RedisHelper::createKey(self::REDIS_USER_BUILDING, $userId);
        // $userBuildings = RedisHelper::get($keyUserBuilding);
        // if($userBuildings) {
        //     return $userBuildings;
        // }
        $userBuildings = $this->model->join('bdc_building', 'v3_user_has_building.building_id', '=', 'bdc_building.id')
            ->select('v3_user_has_building.user_id', 'v3_user_has_building.building_id', 'bdc_building.name as building_name')
            ->where('v3_user_has_building.user_id', $userId)->get();
        if($userBuildings) {
            // RedisHelper::set($keyUserBuilding, $userBuildings);
        }
        return $userBuildings;
    }

    public function firstByUserId($userId)
    {
        $keyUserBuilding = RedisHelper::createKey(self::REDIS_USER_BUILDING_FIRST, $userId);
        // $userBuildings = RedisHelper::get($keyUserBuilding);
        // if($userBuildings) {
        //     return $userBuildings;
        // }
        $userBuildings = $this->model->join('bdc_building', 'v3_user_has_building.building_id', '=', 'bdc_building.id')
            ->select(
                'v3_user_has_building.user_id',
                'v3_user_has_building.building_id',
                'bdc_building.name as building_name',
                'v3_user_has_building.last_login')
            ->where('v3_user_has_building.user_id', $userId)
            ->orderBy('v3_user_has_building.last_login', 'desc')
            ->first();
        if($userBuildings) {
            // RedisHelper::set($keyUserBuilding, $userBuildings);
        }
        return $userBuildings;
    }

    public function filterUserBuldingId($userId, $buildingId)
    {
        // $keyUserBuilding = RedisHelper::createKey(self::REDIS_USER_BUILDING_FIRST, $userId);
        // $userBuildings = RedisHelper::get($keyUserBuilding);
        // if($userBuildings) {
        //     return $userBuildings;
        // }
        $userBuildings = $this->model->join('bdc_building', 'v3_user_has_building.building_id', '=', 'bdc_building.id')
            ->select(
                'v3_user_has_building.user_id',
                'v3_user_has_building.building_id',
                'bdc_building.name as building_name',
                'v3_user_has_building.last_login')
            ->where('v3_user_has_building.user_id', $userId)
            ->where('bdc_building.id', $buildingId)
            ->first();
        if($userBuildings) {
            // RedisHelper::set($keyUserBuilding, $userBuildings);
        }
        return $userBuildings;
    }
}
