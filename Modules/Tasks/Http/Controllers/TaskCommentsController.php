<?php

namespace Modules\Tasks\Http\Controllers;

use App\Helpers\Files;
use App\Http\Controllers\Controller;
//use App\Http\Requests\Task\TaskCommentShow;
//use App\Http\Requests\Task\TaskUserShow;
//use App\Http\Requests\TaskUser\TaskCommentAdd;
//use App\Http\Requests\TaskUser\TaskUserAdd;
//use App\Repositories\Task\TaskRespository;
//use App\Repositories\TaskComment\TaskCommentRespository;
//use App\Repositories\TaskUser\TaskUserRespository;
//use App\Repositories\WorkShift\WorkShiftRespository;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Modules\Tasks\Http\Requests\Task\TaskCommentShow;
use Modules\Tasks\Http\Requests\TaskUser\TaskCommentAdd;
use Modules\Tasks\Repositories\Task\TaskRespository;
use Modules\Tasks\Repositories\TaskComment\TaskCommentRespository;

class TaskCommentsController extends Controller
{
    protected $_taskCommentRespository;
    protected $_taskRespository;

    public function __construct(TaskCommentRespository $taskCommentRespository, TaskRespository $taskRespository)
    {
        $this->_taskCommentRespository = $taskCommentRespository;
        $this->_taskRespository = $taskRespository;
    }

    /**
     * @OA\POST(
     *     path="/api/v1/task-comment/add",
     *     tags={"Task Comments"},
     *     summary="Task Comment Add",
     *     description="Task Comment Add",
     *     operationId="task_comment_add",
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
     *         description="Comment",
     *         in="path",
     *         name="comment",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         description="Attach File",
     *         in="path",
     *         name="attach_file",
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
    public function add(TaskCommentAdd $request)
    {
        try {
            $attributes = [
                'task_id' => $request->task_id,
                'user_id' => $request->user_id,
                'comment' => $request->comment,
            ];

            if(isset($request->attach_file) && $request->attach_file != null) {
                $files = json_decode($request->attach_file);
                $fileArr = [];
                foreach($files as $_file) {
                    $file = Files::uploadBase64Version2($_file, 'task_comments');
                    if(!$file) {
                        return $this->sendError("Định dạng file không chính xác", [], 500);
                    }
                    array_push($fileArr, $file["hash_file"]);
                }
                // $attributes["domain"] = env("DOMAIN_MEDIA_URL");
                $attributes["attach_file"] = json_encode($fileArr);
            }

            $taskUser = $this->_taskCommentRespository->create($attributes);
            if($taskUser) {
                //Reload cache task
                $this->_taskRespository->reloadByBuildingId(@$taskUser->task->building_id);
                $this->_taskRespository->reloadById($request->task_id);
                return $this->sendResponse([], 200, 'Add task comment successfully.');
            } else {
                return $this->sendResponse([], 501, 'Add task comment failure.');
            }
        } catch (Exception $e) {
            Log::channel('task_comment')->error($e->getMessage());
            return $this->sendError($e->getMessage(), [], 500);
        }
    }

    /**
     * @OA\DELETE(
     *     path="/api/v1/task-comment/delete",
     *     tags={"Task Comment"},
     *     summary="Delete Task Comment User",
     *     description="Delete Task Comment User",
     *     operationId="task_comment_delete",
     *     @OA\Parameter(
     *         description="Task Id",
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
    public function delete(TaskCommentShow $request)
    {
        try {
            $taskUser = $this->_taskCommentRespository->find($request->id);
            if($taskUser) {
                $buildingId = @$taskUser->task->building_id;
                $taskUser->delete($request->id);
                //Reload cache task
                $this->_taskRespository->reloadByBuildingId($buildingId);
                $this->_taskRespository->reloadById($taskUser->task_id);
            }
            return $this->sendResponse([], 200, 'Delete task comment successfully.');
        } catch (Exception $e) {
            Log::channel('task_comment')->error($e->getMessage());
            return $this->sendError($e->getMessage(), [], 500);
        }
    }

}
