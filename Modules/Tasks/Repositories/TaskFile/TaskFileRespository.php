<?php

namespace Modules\Tasks\Repositories\TaskFile;

use App\Helpers\RedisHelper;
use App\Repositories\BaseRepository;
use App\Repositories\RepositoryInterface;
use Carbon\Carbon;
use Modules\Tasks\Entities\TaskFile;

class TaskFileRespository extends BaseRepository implements TaskFileInterface
{
    public function getModel()
    {
        return TaskFile::class;
    }
}
