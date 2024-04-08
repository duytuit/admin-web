<?php

namespace App\Repositories\ApartmentGroup;

use App\Models\ApartmentGroups\ApartmentGroup;
use App\Repositories\BaseRepository;

class ApartmentGroupRepository extends BaseRepository implements ApartmentGroupInterface
{

    public function getModel()
    {
        return ApartmentGroup::class;
    }
}
