<?php

namespace Modules\Tasks\Repositories\TaskUser;

use App\Helpers\RedisHelper;
use App\Repositories\BaseRepository;
use Carbon\Carbon;
use Modules\Tasks\Entities\TaskUser;

class TaskUserRespository extends BaseRepository implements TaskUserInterface
{
    public function getModel()
    {
        return TaskUser::class;
    }

    public function deleteByTaskId($taskId)
    {
        return $this->model->where(['task_id' => $taskId])->delete();
    }
}
