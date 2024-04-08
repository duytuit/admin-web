<?php
namespace Modules\Assets\Repositories\UserBuilding;

interface UserBuildingInterface
{
    public function filterByBuildingId($buildingId);

    public function filterByUserId($userId);

    public function firstByUserId($userId);

    public function filterUserBuldingId($userId, $buildingId);
}
