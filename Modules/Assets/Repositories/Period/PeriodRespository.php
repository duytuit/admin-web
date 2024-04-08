<?php

namespace Modules\Assets\Repositories\Period;

use App\Helpers\RedisHelper;
use App\Repositories\BaseRepository;
//use App\Repositories\Period\PeriodInterface;
use Carbon\Carbon;
use Modules\Assets\Entities\Period;

class PeriodRespository extends BaseRepository implements PeriodInterface
{
    public function getModel()
    {
        return Period::class;
    }
}
