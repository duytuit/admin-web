<?php
namespace Modules\Tasks\Repositories\TaskUser;

interface TaskUserInterface
{
    public function deleteByTaskId($taskId);
}
