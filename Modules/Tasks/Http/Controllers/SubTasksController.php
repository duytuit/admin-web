<?php

namespace Modules\Tasks\Http\Controllers;

use App\Helpers\Files;
use App\Http\Controllers\Controller;
//use App\Http\Requests\SubTask\SubTaskAdd;
//use App\Http\Requests\SubTask\SubTaskFeedBack;
//use App\Http\Requests\SubTask\SubTaskShow;
//use App\Http\Requests\SubTask\SubTaskUpdateStatus;
//use App\Models\SubTask;
//use App\Repositories\SubTask\SubTaskRespository;
//use App\Repositories\Task\TaskRespository;
//use App\Repositories\WorkShift\WorkShiftRespository;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Modules\Tasks\Entities\SubTask;
use Modules\Tasks\Http\Requests\SubTask\SubTaskAdd;
use Modules\Tasks\Http\Requests\SubTask\SubTaskFeedBack;
use Modules\Tasks\Http\Requests\SubTask\SubTaskShow;
use Modules\Tasks\Http\Requests\SubTask\SubTaskUpdateStatus;
use Modules\Tasks\Repositories\SubTask\SubTaskRespository;
use Modules\Tasks\Repositories\Task\TaskRespository;
use Modules\Tasks\Repositories\WorkShift\WorkShiftRespository;

class SubTasksController extends Controller
{
    protected $_subTaskRespository;
    protected $_taskRespository;
    protected $_workShiftRespository;

    public function __construct(SubTaskRespository $subTaskRespository, TaskRespository $taskRespository, WorkShiftRespository $workShiftRespository)
    {
        $this->_subTaskRespository = $subTaskRespository;
        $this->_taskRespository = $taskRespository;
        $this->_workShiftRespository = $workShiftRespository;
    }

    /**
     * @OA\POST(
     *     path="/api/v1/sub-task/add",
     *     tags={"Sub Tasks"},
     *     summary="Sub Task Add",
     *     description="Sub Task Add",
     *     operationId="sub_task_add",
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
     *         description="Title",
     *         in="path",
     *         name="title",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         description="Description",
     *         in="path",
     *         name="description",
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
    public function add(SubTaskAdd $request)
    {
        try {
            $attributes = [
                'task_id' => $request->task_id,
                'title' => $request->title,
                'description' => $request->description,
                'status' => SubTask::STATUS['pending'],
            ];
            $subTask = $this->_subTaskRespository->create($attributes);
            if($subTask) {
                $this->_taskRespository->reloadByBuildingId(@$subTask->task->building_id);
                $this->_taskRespository->reloadById(@$subTask->task_id);
                //Reload redis cache subtask
                $this->_subTaskRespository->reloadById($subTask->id);
            }
            return $this->sendResponse([], 200, 'Add sub task successfully.');
        } catch (Exception $e) {
            Log::channel('sub_task')->error($e->getMessage());
            return $this->sendError($e->getMessage(), [], 500);
        }
    }

    /**
     * @OA\PUT(
     *     path="/api/v1/sub-task/update-status",
     *     tags={"Sub Tasks"},
     *     summary="Sub Task Add",
     *     description="Sub Task Add",
     *     operationId="sub_task_add",
     *     @OA\Parameter(
     *         description="SubTask Id",
     *         in="path",
     *         name="id",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         description="Status",
     *         in="path",
     *         name="status",
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
    public function updateStatus(SubTaskUpdateStatus $request)
    {
        try {
            $attributes = [
                'status' => $request->status,
            ];
            $subTask = $this->_subTaskRespository->update($request->id, $attributes);
            if($subTask) {
                //Reload cache Tasks
                $this->_taskRespository->reloadByBuildingId(@$subTask->task->building_id);
                $this->_taskRespository->reloadById(@$subTask->task_id);
                //Reload cache WorkShift show tasks
                $this->_workShiftRespository->reloadShowTasks(@$subTask->task->building_id);
                $this->_subTaskRespository->reloadById($subTask->id);
                return $this->sendResponse([], 200, 'Update status sub task successfully.');
            } else {
                return $this->sendError('Update status sub task failure.', [], 500);
            }
        } catch (Exception $e) {
            Log::channel('sub_task')->error($e->getMessage());
            return $this->sendError($e->getMessage(), [], 500);
        }
    }

    /**
     * @OA\PUT(
     *     path="/api/v1/sub-task/feedback",
     *     tags={"Tasks"},
     *     summary="Task Feedback",
     *     description="Task Feedback",
     *     operationId="task_feedback",
     *     @OA\Parameter(
     *         description="Task Id",
     *         in="path",
     *         name="id",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         description="FeedBack",
     *         in="path",
     *         name="feedback",
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
    public function feedback(SubTaskFeedBack $request)
    {
        try {
            $attributes = [
                'feedback' => $request->feedback,
            ];
            if(isset($request->attach_file) && $request->attach_file != null) {
                $files = json_decode($request->attach_file);
                $fileArr = [];
                foreach($files as $_file) {
                    $file = Files::uploadBase64Version2($_file, 'sub_tasks');
                    if(!$file) {
                        return $this->sendError("Định dạng file không chính xác", [], 500);
                    }
                    array_push($fileArr, $file["hash_file"]);
                }
                // $attributes["domain"] = env("DOMAIN_MEDIA_URL");
                $attributes["attach_file"] = json_encode($fileArr);
            }
            $subTask = $this->_subTaskRespository->update($request->id, $attributes);
            if($subTask) {
                //Reload cache Tasks
                $this->_taskRespository->reloadByBuildingId(@$subTask->task->building_id);
                $this->_taskRespository->reloadById(@$subTask->task_id);
                //Reload cache WorkShift show tasks
                $this->_workShiftRespository->reloadShowTasks(@$subTask->task->building_id);
                $this->_subTaskRespository->reloadById($subTask->id);
                return $this->sendResponse([], 200, 'Update sub task feedback successfully.');
            } else {
                return $this->sendError('Update sub task feedback failure.', [], 500);
            }
        } catch (Exception $e) {
            Log::channel('sub_task')->error($e->getMessage());
            return $this->sendError($e->getMessage(), [], 500);
        }
    }

    /**
     * @OA\DELETE(
     *     path="/api/v1/sub-task/delete",
     *     tags={"Sub Tasks"},
     *     summary="Delete Sub Task",
     *     description="Delete Sub Tasks",
     *     operationId="sub_task_delete",
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
    public function delete(SubTaskShow $request)
    {
        try {
            $subTask = $this->_subTaskRespository->find($request->id);
            if($subTask) {
                $buildingId = @$subTask->task->building_id;
                $taskId = @$subTask->task_id;
                $this->_subTaskRespository->delete($subTask->id);
                // Reload cache task
                $this->_taskRespository->reloadByBuildingId($buildingId);
                $this->_taskRespository->reloadById($taskId);
                //Reload cache WorkShift show tasks
                $this->_workShiftRespository->reloadShowTasks($buildingId);
                $this->_subTaskRespository->reloadById($subTask->id);
                return $this->sendResponse([], 200, 'Delete sub task successfully.');
            } else {
                return $this->sendError('Delete sub task failure.', [], 500);
            }
        } catch (Exception $e) {
            Log::channel('sub_task')->error($e->getMessage());
            return $this->sendError($e->getMessage(), [], 500);
        }
    }

    /**
     * @OA\POST(
     *     path="/api/v1/sub-task/show",
     *     tags={"Task"},
     *     summary="Show Task",
     *     description="Show Task",
     *     operationId="task_show",
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
    public function show(Request $request)
    {
        try {
            $subTask = $this->_subTaskRespository->filterById($request->id);
            $subTask->attach_file = $subTask->attach_file != null ? json_decode($subTask->attach_file) : [];
            return $this->sendResponse($subTask, 200, 'Get sub task successfully.');
        } catch (Exception $e) {
            Log::channel('sub_task')->error($e->getMessage());
            return $this->sendError($e->getMessage(), [], 500);
        }
    }
}
