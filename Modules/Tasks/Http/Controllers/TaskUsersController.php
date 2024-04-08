<?php

namespace Modules\Tasks\Http\Controllers;

use App\Http\Controllers\Controller;
//use App\Http\Requests\Task\TaskUserShow;
//use App\Http\Requests\TaskUser\TaskUserAdd;
//use App\Repositories\TaskUser\TaskUserRespository;
//use App\Repositories\WorkShift\WorkShiftRespository;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Modules\Tasks\Http\Requests\Task\TaskUserShow;
use Modules\Tasks\Http\Requests\TaskUser\TaskUserAdd;
use Modules\Tasks\Repositories\TaskUser\TaskUserRespository;
use Modules\Tasks\Repositories\WorkShift\WorkShiftRespository;

class TaskUsersController extends Controller
{
    protected $_taskUserRespository;
    protected $_workShiftRespository;

    public function __construct(TaskUserRespository $taskUserRespository, WorkShiftRespository $workShiftRespository)
    {
        $this->_taskUserRespository = $taskUserRespository;
        $this->_workShiftRespository = $workShiftRespository;
    }

    /**
     * @OA\POST(
     *     path="/api/v1/task-user/add",
     *     tags={"Task Users"},
     *     summary="Task User Add",
     *     description="Task User Add",
     *     operationId="task_user_add",
     *     @OA\Parameter(
     *         description="Task Id",
     *         in="path",
     *         name="task_id",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         description="UserId",
     *         in="path",
     *         name="user_id",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         description="FullName",
     *         in="path",
     *         name="name",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         description="User Avatar",
     *         in="path",
     *         name="avatar",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *   @OA\Response(response=200, description="successful operation"),
     *   @OA\Response(response=406, description="not acceptable"),
     *   @OA\Response(response=500, description="internal server error")
     * )
     */
    public function add(TaskUserAdd $request)
    {
        try {
            $attributes = [
                'task_id' => $request->task_id,
                'user_id' => $request->user_id,
                'user_name' => $request->name,
                'user_avatar' => $request->avatar,
            ];
            $taskUser = $this->_taskUserRespository->create($attributes);
            if($taskUser) {
                //Reload cache WorkShift show tasks
                $this->_workShiftRespository->reloadShowTasks(@$taskUser->task->building_id);
            }
            return $this->sendResponse([], 200, 'Add Task User successfully.');
        } catch (Exception $e) {
            Log::channel('task_user')->error($e->getMessage());
            return $this->sendError($e->getMessage(), [], 500);
        }
    }

    /**
     * @OA\DELETE(
     *     path="/api/v1/task-user/delete",
     *     tags={"Task Users"},
     *     summary="Delete Task User",
     *     description="Delete Task User",
     *     operationId="task_user_delete",
     *     @OA\Parameter(
     *         description="SubTask Id",
     *         in="path",
     *         name="id",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *   @OA\Response(response=200, description="successful operation"),
     *   @OA\Response(response=406, description="not acceptable"),
     *   @OA\Response(response=500, description="internal server error")
     * )
     */
    public function delete(TaskUserShow $request)
    {
        try {
            $taskUser = $this->_subTaskRespository->find($request->id);
            if($taskUser) {
                $buildingId = @$taskUser->task->building_id;
                $taskUser->delete($request->id);
                //Reload cache WorkShift show tasks
                $this->_workShiftRespository->reloadShowTasks($buildingId);
            }
            return $this->sendResponse([], 200, 'Delete Task User successfully.');
        } catch (Exception $e) {
            Log::channel('task_user')->error($e->getMessage());
            return $this->sendError($e->getMessage(), [], 500);
        }
    }

}
