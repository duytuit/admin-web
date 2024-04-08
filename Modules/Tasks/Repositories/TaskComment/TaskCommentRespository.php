<?php

namespace Modules\Tasks\Repositories\TaskComment;

use App\Helpers\RedisHelper;
use App\Repositories\BaseRepository;
use App\Repositories\RepositoryInterface;
use Carbon\Carbon;
use Modules\Tasks\Entities\TaskComment;

class TaskCommentRespository extends BaseRepository implements TaskCommentInterface
{
    public function getModel()
    {
        return TaskComment::class;
    }
}
