<?php

namespace Modules\Tasks\Http\Controllers;

use App\Commons\Helper;
use App\Filter\TaskFilter;
use App\Helpers\Files;
use App\Http\Controllers\Controller;
use App\Models\Building\Building;
use App\Models\Campain;
use App\Models\DepartmentStaff\DepartmentStaff;
use App\Models\Fcm\Fcm;
use App\Models\SentStatus;
use App\Services\FCM\SendNotifyFCMService;
//use App\Http\Requests\Task\TaskAdd;
//use App\Http\Requests\Task\TaskFeedBack;
//use App\Http\Requests\Task\TaskShow;
//use App\Http\Requests\Task\TaskUpdate;
//use App\Models\SubTask;
//use App\Models\Task;
//use App\Models\TaskFile;
//use App\Repositories\Role\RoleRespository;
//use App\Repositories\SubTask\SubTaskRespository;
//use App\Repositories\Task\TaskRespository;
//use App\Repositories\TaskFile\TaskFileRespository;
//use App\Repositories\TaskUser\TaskUserRespository;
use App\Services\SendTelegram;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;
use Modules\Tasks\Entities\SubTask;
use Modules\Tasks\Entities\Task;
use Modules\Tasks\Entities\TaskFile;
use Modules\Tasks\Http\Requests\Task\TaskAdd;
use Modules\Tasks\Http\Requests\Task\TaskFeedBack;
use Modules\Tasks\Http\Requests\Task\TaskShow;
use Modules\Tasks\Http\Requests\Task\TaskUpdate;
use Modules\Tasks\Repositories\Role\RoleRespository;
use Modules\Tasks\Repositories\SubTask\SubTaskRespository;
use Modules\Tasks\Repositories\Task\TaskRespository;
use Modules\Tasks\Repositories\TaskFile\TaskFileRespository;
use Modules\Tasks\Repositories\TaskUser\TaskUserRespository;

class TasksController extends Controller
{
    protected $_taskRespository;
    protected $_taskUserRespository;
    protected $_subTaskRespository;
    protected $_taskFileRespository;
    protected $_roleRespository;

    public function __construct(TaskRespository $taskRespository,
        TaskUserRespository $taskUserRespository,
        SubTaskRespository $subTaskRespository,
        TaskFileRespository $taskFileRespository,
        RoleRespository $roleRespository
        )
    {
        $this->_taskRespository = $taskRespository;
        $this->_taskUserRespository = $taskUserRespository;
        $this->_subTaskRespository = $subTaskRespository;
        $this->_taskFileRespository = $taskFileRespository;
        $this->_roleRespository = $roleRespository;
    }

    /**
     * @OA\GET(
     *     path="/api/v1/task",
     *     tags={"Task"},
     *     summary="Task List",
     *     description="Task List",
     *     operationId="task",
     *     @OA\Parameter(
     *         description="Building Id",
     *         in="path",
     *         name="building_id",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         description="Task category id",
     *         in="path",
     *         name="task_category_id",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         description="Department Id",
     *         in="path",
     *         name="bdc_department_id",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         description="Maintenance Asset id",
     *         in="path",
     *         name="maintenance_asset_id",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         description="Task name",
     *         in="path",
     *         name="task_name",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         description="StartDate",
     *         in="path",
     *         name="start_date",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         description="EndDate",
     *         in="path",
     *         name="end_date",
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
     *     @OA\Parameter(
     *         description="Limit",
     *         in="path",
     *         name="limit",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         description="Page",
     *         in="path",
     *         name="page",
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
    public function index(Request $request)
    {
        try {
            $user = (object)$request->get('user');
            $limit = isset($request->limit) ? $request->limit : 10;
            $page = isset($request->page) ? $request->page : 1;
            // $roleType = $this->_roleRespository->getRoleType($user->user_id, $request->building_id);
            $tasks = $this->_taskRespository->filterByBuildingId($request->building_id);
            $tasks = TaskFilter::index($tasks, $request, $user);
            $offSet = ($page * $limit) - $limit;
            $itemsForCurrentPage = array_slice($tasks->toArray(), $offSet, $limit, true);
            $_tasks = new LengthAwarePaginator($itemsForCurrentPage, count($tasks), $limit, $page, []);
            $paging = [
                'total' => $_tasks->total(),
                'currentPage' => $_tasks->count(),
                'lastPage' => $_tasks->lastPage(),
            ];

            $_tasksList = $_tasks->values()->toArray();

            return $this->sendResponsePaging($_tasksList, $paging, 200, 'Lấy thông tin thành công.');
        } catch (Exception $e) {
            Log::channel('task')->error($e->getMessage());
            return $this->sendError($e->getMessage(), [], 500);
        }
    }

    /**
     * @OA\POST(
     *     path="/api/v1/task/add",
     *     tags={"Task"},
     *     summary="Add Task",
     *     description="Add Task",
     *     operationId="task_add",
     *     @OA\Parameter(
     *         description="Building Id",
     *         in="path",
     *         name="building_id",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         description="Department Id",
     *         in="path",
     *         name="department_id",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         description="Work Shift Id",
     *         in="path",
     *         name="work_shift_id",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         description="Task name",
     *         in="path",
     *         name="task_name",
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
     *     @OA\Parameter(
     *         description="Priority",
     *         in="path",
     *         name="priority",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         description="Completed On",
     *         in="path",
     *         name="completed_on",
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
     *     @OA\Parameter(
     *         description="Supervisor",
     *         in="path",
     *         name="supervisor",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         description="UserIds[]",
     *         in="path",
     *         name="user_ids",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         description="Sub Task[]",
     *         in="path",
     *         name="sub_tasks",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         description="Related",
     *         in="path",
     *         name="related",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         description="Maintenance Asset Id",
     *         in="path",
     *         name="maintenance_asset_id",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         description="Apartment Id",
     *         in="path",
     *         name="apartment_id",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         description="Feedback Id",
     *         in="path",
     *         name="feedback_id",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         description="Task Images",
     *         in="path",
     *         name="task_images",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         description="Task Files",
     *         in="path",
     *         name="task_files",
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
    public function add(TaskAdd $request)
    {
        try {
            \DB::beginTransaction();
            $user = null;

            $startDate = json_decode($request->start_date);
            if($startDate) {
                foreach($startDate as $_startDate) {
                    $task = $this->addTask($user, $request, $_startDate);
                }
            } else {
                $task = $this->addTask($user, $request);
            }

            if($task) {
                \DB::commit();
                $this->_taskRespository->reloadById($task->id);
                $this->_taskRespository->reloadByBuildingId($request->building_id);
                return $this->sendResponse([], 200, 'Add Task successfully.');
            } else {
                \DB::rollBack();
                return $this->sendResponse([], 501, 'Add Task failure.');
            }
        } catch (Exception $e) {
            \DB::rollBack();
            Log::channel('task')->error($e->getMessage());
            return $this->sendError($e->getMessage(), [], 500);
        }
    }

    /**
     * @OA\POST(
     *     path="/api/v1/task/show",
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
    public function show(TaskShow $request)
    {
        try {
            $task = $this->_taskRespository->filterById($request->id);
            $task->attach_file = $task->attach_file != null ? json_decode($task->attach_file) : [];
            $subTasks = @$task->sub_tasks != null ? @$task->sub_tasks : @$task->subTasks;
            if($subTasks != null) {
                $subTaskArr = [];
                foreach(@$subTasks as $_subTask) {
                    $_subTask->attach_file = $_subTask->attach_file != null ? json_decode($_subTask->attach_file) : [];
                    array_push($subTaskArr, $_subTask);
                }
                $task->sub_tasks = $subTaskArr;
            }
            return $this->sendResponse($task, 200, 'Get Task successfully.');
        } catch (Exception $e) {
            Log::channel('task')->error($e->getMessage());
            return $this->sendError($e->getMessage(), [], 500);
        }
    }

    /**
     * @OA\PUT(
     *     path="/api/v1/task/update",
     *     tags={"Task"},
     *     summary="Update Task",
     *     description="Update Task",
     *     operationId="task_update",
     *     @OA\Parameter(
     *         description="Department Id",
     *         in="path",
     *         name="department_id",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         description="Work Shift Id",
     *         in="path",
     *         name="work_shift_id",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         description="Task name",
     *         in="path",
     *         name="task_name",
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
     *     @OA\Parameter(
     *         description="Priority",
     *         in="path",
     *         name="priority",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         description="Completed On",
     *         in="path",
     *         name="completed_on",
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
     *     @OA\Parameter(
     *         description="Supervisor",
     *         in="path",
     *         name="supervisor",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         description="UserIds[]",
     *         in="path",
     *         name="user_ids",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         description="Sub Task[]",
     *         in="path",
     *         name="sub_tasks",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         description="Related",
     *         in="path",
     *         name="related",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         description="Maintenance Asset Id",
     *         in="path",
     *         name="maintenance_asset_id",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         description="Task Images",
     *         in="path",
     *         name="task_images",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         description="Task Files",
     *         in="path",
     *         name="task_files",
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
    public function update(TaskUpdate $request)
    {
        try {
            \DB::beginTransaction();
            $user = null;
            $id = $request->id;

            // $startDate = json_decode($request->start_date);
            // if($startDate) {
            //     foreach($startDate as $_startDate) {
            //         $task = $this->updateTask($id, $user, $request, $_startDate);
            //     }
            // } else {
            //     $task = $this->updateTask($id, $user, $request);
            // }
            $task = $this->updateTask($id, $user, $request);
            if($task) {
                \DB::commit();
                $this->_taskRespository->reloadByBuildingId($request->building_id);
                $this->_taskRespository->reloadById($id);
                return $this->sendResponse([], 200, 'Update Task successfully.');
            } else {
                \DB::rollBack();
                return $this->sendResponse([], 500, 'Update Task failure.');
            }
        } catch (Exception $e) {
            \DB::rollBack();
            Log::channel('task')->error($e->getMessage());
            return $this->sendError($e->getMessage(), [], 500);
        }
    }

    /**
     * @OA\PUT(
     *     path="/api/v1/task/update-status",
     *     tags={"Tasks"},
     *     summary="Task Update Status",
     *     description="Task Update Status",
     *     operationId="task_update_status",
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
    public function updateStatus(TaskShow $request)
    {
       
        try {
            $attributes = [
                'status' => $request->status,
            ];
            $task = $this->_taskRespository->update($request->id, $attributes);
            if($task) {
                // notify người nhận việc
                $_building = Building::get_detail_building_by_building_id($task->building_id);

                $userInfos =  @$task->taskUsers;
                $_departmentStaff = DepartmentStaff::where(['bdc_department_id'=>$task->bdc_department_id,'type'=>0])->whereHas('department', function ($query) use ($task) {
                    if (isset($task->building_id)) {
                        $query->where('bdc_building_id', $task->building_id);
                    }
                })->first();

                $userIdList = [];
                foreach ($userInfos as  $value) {
                    array_push($userIdList, [$value->user_id]);
                }
                array_push($userIdList, [@$_building->manager_id, @$_departmentStaff->pub_user_id, @$task->supervisor]);
                $countTokent = (int)Fcm::getCountTokenbyUserId($userIdList);  
                $total = ['email'=>0, 'app'=> $countTokent, 'sms'=> 0];
        
                $campain = Campain::updateOrCreateCampain("Thông báo công việc: ".$task->task_name, config('typeCampain.TASK'), $task->id, $total, $task->building_id, 0, 0);
        
                foreach($userInfos as $_userInfo) {
                    $this->sendNotifyTask($task, $_building,$_userInfo->user_id, null, $campain->id);
                }

                // notify trưởng ban quản lý
                if($_building->manager_id){  // nếu có trưởng ban quản lý
                    $this->sendNotifyTask($task, $_building,$_building->manager_id, null, $campain->id);
                }
                SendTelegram::SupersendTelegramMessage('671');
                // notify trưởng bộ phận
                $this->sendNotifyTask($task, $_building,$_departmentStaff->pub_user_id, null, $campain->id);

                // notify giám sát
                $this->sendNotifyTask($task, $_building,$task->supervisor, null, $campain->id);

                $this->_taskRespository->reloadByBuildingId($task->building_id);
                $this->_taskRespository->reloadById($task->id);
                return $this->sendResponse([], 200, 'Update status task successfully.');
            } else {
                return $this->sendError('Update status task failure.', [], 500);
            }
        } catch (Exception $e) {
            Log::channel('task')->error($e->getMessage());
            return $this->sendError($e->getMessage(), [], 500);
        }
    }

    /**
     * @OA\PUT(
     *     path="/api/v1/task/feedback",
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
     *         description="Status",
     *         in="path",
     *         name="status",
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
    public function feedback(TaskFeedBack $request)
    {
        try {
            $attributes = [
                'feedback' => $request->feedback,
                'status' => $request->status,
            ];
            if(isset($request->attach_file) && $request->attach_file != null) {
                $files = json_decode($request->attach_file);
                $fileArr = [];
                foreach($files as $_file) {
                    $file = Files::uploadBase64Version2($_file, 'tasks');
                    if(!$file) {
                        return $this->sendError("Định dạng file không chính xác", [], 500);
                    }
                    array_push($fileArr, $file["hash_file"]);
                }
                // $attributes["domain"] = env("DOMAIN_MEDIA_URL");
                $attributes["attach_file"] = json_encode($fileArr);
            }
            $task = $this->_taskRespository->update($request->id, $attributes);
            if($task) {
                $this->_taskRespository->reloadByBuildingId($task->building_id);
                $this->_taskRespository->reloadById($task->id);
                return $this->sendResponse([], 200, 'Update task feedback successfully.');
            } else {
                return $this->sendError('Update task feedback failure.', [], 500);
            }
        } catch (Exception $e) {
            Log::channel('task')->error($e->getMessage());
            return $this->sendError($e->getMessage(), [], 500);
        }
    }

    /**
     * @OA\DELETE(
     *     path="/api/v1/task/delete",
     *     tags={"Task"},
     *     summary="Delete Task",
     *     description="Delete Task",
     *     operationId="task_delete",
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
    public function delete(TaskShow $request)
    {
        try {
           
            $task = Task::find($request->id);
            $_building = Building::get_detail_building_by_building_id($task->building_id);
            // notify người nhận việc
           
            $userInfos =  @$task->taskUsers;
            $_departmentStaff = DepartmentStaff::where(['bdc_department_id'=>$task->bdc_department_id,'type'=>0])->whereHas('department', function ($query) use ($task) {
                if (isset($task->building_id)) {
                    $query->where('bdc_building_id', $task->building_id);
                }
            })->first();

            $userIdList = [];
            foreach ($userInfos as  $value) {
                array_push($userIdList, [$value->user_id]);
            }
            array_push($userIdList, [@$_building->manager_id, @$_departmentStaff->pub_user_id, @$task->supervisor]);
            $countTokent = (int)Fcm::getCountTokenbyUserId($userIdList);  
            $total = ['email'=>0, 'app'=> $countTokent, 'sms'=> 0];

            $campain = Campain::updateOrCreateCampain("Thông báo huỷ công việc: ".$task->task_name, config('typeCampain.TASK'), $task->id, $total, $task->building_id, 0, 0);

            foreach($userInfos as $_userInfo) {
                $this->sendNotifyTask($task, $_building,$_userInfo->user_id,'Đã hủy', $campain->id);
            }

            // notify trưởng ban quản lý
            if($_building->manager_id){  // nếu có trưởng ban quản lý
                $this->sendNotifyTask($task, $_building,$_building->manager_id,'Đã hủy', $campain->id);
            }
            // notify trưởng bộ phận
            $this->sendNotifyTask($task, $_building,$_departmentStaff->pub_user_id,'Đã hủy', $campain->id);
            // notify giám sát
            $this->sendNotifyTask($task, $_building,$task->supervisor,'Đã hủy', $campain->id);

            $this->_taskRespository->delete($request->id);
            //$this->_taskRespository->deleteRedisCache($request->id);
            $this->_taskRespository->reloadByBuildingId($request->building_id);
            $this->_taskUserRespository->deleteByTaskId($request->id);
            $this->_subTaskRespository->deleteByTaskId($request->id);
             

            return $this->sendResponse([], 200, 'Delete Task successfully.');
        } catch (Exception $e) {
            Log::channel('task')->error($e->getMessage());
            return $this->sendError($e->getMessage(), [], 500);
        }
    }

    public function addTask($user = null, $request, $startDate = null)
    {
        $_building = Building::get_detail_building_by_building_id($request->building_id);
       
        $attributes = [
            'building_id' => $request->building_id,
            'bdc_department_id' => $request->department_id,
            'task_category_id' => $request->task_category_id,
            'work_shift_id' => (isset($request->work_shift_id) && $request->work_shift_id != null) ? $request->work_shift_id : 0,
            'task_name' => $request->task_name,
            'description' => $request->description,
            'supervisor' => $request->supervisor,
            'created_by' => \Auth::user()->id,
            'type' => $request->type,
            'related' => $request->related,
            'maintenance_asset_id' => $request->maintenance_asset_id,
            'apartment_id' => $request->apartment_id,
            'feedback_id' => $request->feedback_id,
            'status' => Task::STATUS['not_yet_started'],
        ];
        if($request->type == Task::TYPE_LAPLAI) {
            $attributes["start_date"] = $startDate;
            $attributes["due_date"] = $startDate;
        } else {
            $attributes["start_date"] = $request->start_date;
            $attributes["due_date"] = $request->due_date;
        }
        $task = $this->_taskRespository->create($attributes);

        $userInfos = json_decode($request->user_infos);
        $_departmentStaff = DepartmentStaff::where(['bdc_department_id'=>$task->bdc_department_id,'type'=>0])->whereHas('department', function ($query) use ($request) {
            if (isset($request->building_id)) {
                $query->where('bdc_building_id', $request->building_id);
            }
        })->first();

        $userIdList = [];
        foreach ($userInfos as  $value) {
            array_push($userIdList, [$value->user_id]);
        }
        array_push($userIdList, [@$_building->manager_id, @$_departmentStaff->pub_user_id, @$task->supervisor]);
        $countTokent = (int)Fcm::getCountTokenbyUserId($userIdList);  
        $total = ['email'=>0, 'app'=> $countTokent, 'sms'=> 0];

        $campain = Campain::updateOrCreateCampain("Thông báo công việc: ".$task->task_name, config('typeCampain.TASK'), $task->id, $total, $task->building_id, 0, 0);

        foreach($userInfos as $_userInfo) {
            $taskUserAttributes = [
                'task_id' => $task->id,
                'user_id' => $_userInfo->user_id,
                'user_name' => $_userInfo->name,
                'user_avatar' => $_userInfo->avatar,
            ];
            $this->sendNotifyTask($task, $_building,$_userInfo->user_id, null, $campain->id);

            $this->_taskUserRespository->create($taskUserAttributes);
        }

        // notify trưởng ban quản lý
        if($_building->manager_id){  // nếu có trưởng ban quản lý
            $this->sendNotifyTask($task, $_building,$_building->manager_id, null, $campain->id);
        }
        // notify trưởng bộ phận
        $this->sendNotifyTask($task, $_building,$_departmentStaff->pub_user_id, null, $campain->id);
        // notify giám sát
        $this->sendNotifyTask($task, $_building,$task->supervisor, null, $campain->id);
        SendTelegram::SupersendTelegramMessage('911');
        $subTasks = json_decode($request->sub_tasks);
        foreach($subTasks as $_subTask) {
            $subTaskAttributes = [
                'task_id' => $task->id,
                'title' => $_subTask->title,
                'description' => $_subTask->description,
                'status' => SubTask::STATUS['pending'],
            ];
            $this->_subTaskRespository->create($subTaskAttributes);
        }

        if(isset($request->task_files) && $request->task_files != null) {
            $files = json_decode($request->task_files);
            foreach($files as $_file) {
                $file = Files::uploadBase64Version2($_file, 'tasks');
                if(!$file) {
                    return $this->sendError("Kích thước file không lớn hơn 5M hoặc định dạng file không chính xác", [], 500);
                }
                $taskFile = [
                    'building_id' => $request->building_id,
                    'task_id' => $task->id,
                    'file_name' => $file["file_name"],
                    'hash_name' => $file["hash_file"],
                    'size' => $file["file_size"],
                    'type' => TaskFile::TYPE_FILE,
                ];
                $this->_taskFileRespository->create($taskFile);
            }
        }

        if(isset($request->task_images) && $request->task_images != null) {
            $images = json_decode($request->task_images);
            foreach($images as $_image) {
                $image = Files::uploadBase64Version2($_image, 'tasks');
                if(!$image) {
                    return $this->sendError("Kích thước file không lớn hơn 5M hoặc định dạng file không chính xác", [], 500);
                }
                $taskFile = [
                    'building_id' => $request->building_id,
                    'task_id' => $task->id,
                    'file_name' => $image["file_name"],
                    'hash_name' => $image["hash_file"],
                    'size' => $image["file_size"],
                    'type' => TaskFile::TYPE_IMAGE,
                ];
                $this->_taskFileRespository->create($taskFile);
            }
        }

        return $task;
    }

    public function sendNotifyTask($task, $building , $user_id , $status = null, $campainId )
    {
        $data_noti=[
            'message' => $status == null ? 'trạng thái: '.Helper::status_worktask_v2[$task->status] : $status,
            'building_id' => $building->id,
            'title' => '['.$building->name."]_" .$task->task_name,
            'action_name' => TaskRespository::NEW_TASK,
            'image' => null,
            'type' => TaskRespository::NEW_TASK,
            'screen' => null,
            'id' => $task->id,
            'user_id' => $user_id,
            'app_config' => "banquanly",
            'avatar' => "avatar/system/01.png",
            'campain_id' => $campainId,
            'app'=>'v1'
        ];

        SendNotifyFCMService::setItemForQueueNotify($data_noti);
    } 

    public function updateTask($id, $user = null, $request, $startDate = null)
    {
      
        $attributes = [
            'bdc_department_id' => $request->department_id,
            'task_category_id' => $request->task_category_id,
            'work_shift_id' => $request->work_shift_id,
            'task_name' => $request->task_name,
            'description' => $request->description,
            'supervisor' => $request->supervisor,
            'created_by' => \Auth::user()->id,
            // 'type' => $request->type,
            'related' => $request->related,
            'maintenance_asset_id' => $request->maintenance_asset_id,
            'apartment_id' => $request->apartment_id,
            'feedback_id' => $request->feedback_id,
        ];
        if($request->type == Task::TYPE_LAPLAI) {
            $attributes["start_date"] = $startDate;
            $attributes["due_date"] = $startDate;
        } else {
            $attributes["start_date"] = $request->start_date;
            $attributes["due_date"] = $request->due_date;
        }
        $task = $this->_taskRespository->update($id, $attributes);

        $_building = Building::get_detail_building_by_building_id($task->building_id);

        $this->_taskUserRespository->deleteByTaskId($id);
        $this->_subTaskRespository->deleteByTaskId($id);

        $userInfos = json_decode($request->user_infos);  

        // truong bo phan
        $_departmentStaff = DepartmentStaff::where(['bdc_department_id'=>$task->bdc_department_id,'type'=>0])->whereHas('department', function ($query) use ($task) {
            if (isset($task->building_id)) {
                $query->where('bdc_building_id', $task->building_id);
            }
        })->first();

        $userIdList = [];
        foreach ($userInfos as  $value) {
            array_push($userIdList, [$value->user_id]);
        }
        array_push($userIdList, [@$_building->manager_id, @$_departmentStaff->pub_user_id, @$task->supervisor]);
        $countTokent = (int)Fcm::getCountTokenbyUserId($userIdList);  
        $total = ['email'=>0, 'app'=> $countTokent, 'sms'=> 0];

        $campain = Campain::updateOrCreateCampain("Thông báo cập nhật công việc: ".$task->task_name, config('typeCampain.TASK'), $task->id, $total, $task->building_id, 0, 0);
        foreach($userInfos as $_userInfo) {
            $taskUserAttributes = [
                'task_id' => $task->id,
                'user_id' => $_userInfo->user_id,
                'user_name' => $_userInfo->name,
                'user_avatar' => $_userInfo->avatar,
            ];
            $this->sendNotifyTask($task, $_building,$_userInfo->user_id, null, $campain->id);
            $this->_taskUserRespository->create($taskUserAttributes);
        }

         // notify trưởng ban quản lý
        if($_building->manager_id){  // nếu có trưởng ban quản lý
            $this->sendNotifyTask($task, $_building,$_building->manager_id, null, $campain->id);
        }

        // notify trưởng bộ phận
        $this->sendNotifyTask($task, $_building,$_departmentStaff->pub_user_id, null, $campain->id);

        // notify giám sát
        $this->sendNotifyTask($task, $_building,$task->supervisor, null, $campain->id);

        $subTasks = json_decode($request->sub_tasks);
        foreach($subTasks as $_subTask) {
            $subTaskAttributes = [
                'task_id' => $task->id,
                'title' => $_subTask->title,
                'description' => $_subTask->description,
                'status' => SubTask::STATUS['pending'],
            ];
            $this->_subTaskRespository->create($subTaskAttributes);
        }

        if(isset($request->task_files) && $request->task_files != null) {
            // $this->_taskFileRespository->findColumns(['task_id' => $task->id, 'type' => TaskFile::TYPE_FILE])->delete();
            $files = json_decode($request->task_files);
            foreach($files as $_file) {
                $rs = $this->_taskFileRespository->find($_file->id);
                if($rs) {
                    continue;
                }
                $file = Files::uploadBase64Version2($_file, 'tasks');
                if(!$file) {
                    return $this->sendError("Kích thước file không lớn hơn 5M hoặc định dạng file không chính xác", [], 500);
                }
                $taskFile = [
                    'building_id' => $task->building_id,
                    'task_id' => $task->id,
                    'file_name' => $file["file_name"],
                    'size' => $file["file_size"],
                    'type' => TaskFile::TYPE_FILE,
                ];
                $this->_taskFileRespository->create($taskFile);
            }
        }

        if(isset($request->task_images) && $request->task_images != null) {
            // $this->_taskFileRespository->findColumns(['task_id' => $task->id, 'type' => TaskFile::TYPE_IMAGE])->delete();
            $images = json_decode($request->task_images);
            foreach($images as $_image) {
                $rs = $this->_taskFileRespository->find($_image->id);
                if($_image->type == TaskFile::TYPE_DELETE) {
                    if($rs) {
                        $rs->delete();
                        continue;
                    }
                }
                if($rs) {
                    continue;
                }
                $image = Files::uploadBase64Version2($_image, 'tasks');
                if(!$image) {
                    return $this->sendError("Kích thước file không lớn hơn 5M hoặc định dạng file không chính xác", [], 500);
                }
                $taskFile = [
                    'building_id' => $task->building_id,
                    'task_id' => $task->id,
                    'file_name' => $image["file_name"],
                    'hash_name' => $image["hash_file"],
                    'size' => $image["file_size"],
                    'type' => TaskFile::TYPE_IMAGE,
                ];
                $this->_taskFileRespository->create($taskFile);
            }
        }

        return $task;
    }
}
