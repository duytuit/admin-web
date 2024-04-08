<?php

namespace App\Repositories\V3\User;

use App\Models\V3\User\User;
use App\Repositories\V3\BaseRepository\BaseRepository;

class UserRepository extends BaseRepository
{

    /**
     * UserRepository constructor.
     * @param User $model
     */
    public function __construct(User $model)
    {
        $this->model = $model;
    }

    public function filterByBuildingId($buildingId)
    {
        return $this->query()
            ->whereHas("infoWeb", function ($q) use ($buildingId) {
                $q->where('bdc_building_id', $buildingId);
            })
            ->with('info')
            ->get();
    }

}
