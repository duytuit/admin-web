<?php

namespace Modules\Tasks\Http\Controllers;

use App\Filter\WorkShiftFilter;
use App\Http\Controllers\Controller;
//use App\Http\Requests\WorkShift\WorkShiftAdd;
//use App\Http\Requests\WorkShift\WorkShiftShow;
//use App\Http\Requests\WorkShift\WorkShiftUpdate;
//use App\Models\WorkShift;
//use App\Repositories\WorkShift\WorkShiftRespository;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;
use Modules\Tasks\Entities\WorkShift;
use Modules\Tasks\Http\Requests\WorkShift\WorkShiftAdd;
use Modules\Tasks\Http\Requests\WorkShift\WorkShiftShow;
use Modules\Tasks\Http\Requests\WorkShift\WorkShiftUpdate;
use Modules\Tasks\Repositories\WorkShift\WorkShiftRespository;

class WorkShiftsController extends Controller
{
    protected $_workShiftRespository;

    public function __construct(WorkShiftRespository $workShiftRespository)
    {
        $this->_workShiftRespository = $workShiftRespository;
    }

    /**
     * @OA\GET(
     *     path="/api/v1/work-shift",
     *     tags={"Work Shift"},
     *     summary="Work Shift List",
     *     description="Work Shift List",
     *     operationId="work_shift",
     *     @OA\Parameter(
     *         description="Building Id",
     *         in="path",
     *         name="building_id",
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
            $workShifts = $this->_workShiftRespository->filterByBuildingId($request->building_id);
            return $this->sendResponse($workShifts, 200, 'Lấy thông tin thành công.');
        } catch (Exception $e) {
            Log::channel('work_shift')->error($e->getMessage());
            return $this->sendError($e->getMessage(), [], 500);
        }
    }

    public function tasks(Request $request)
    {
        try {
            $limit = isset($request->limit) ? $request->limit : 10;
            $page = isset($request->page) ? $request->page : 1;

            $workShifts = $this->_workShiftRespository->showTasks($request->building_id);
            $workShifts = WorkShiftFilter::tasks($workShifts, $request);
            $offSet = ($page * $limit) - $limit;
            $itemsForCurrentPage = array_slice($workShifts->toArray(), $offSet, $limit, true);
            $_tasks = new LengthAwarePaginator($itemsForCurrentPage, count($workShifts), $limit, $page, []);
            $paging = [
                'total' => $_tasks->total(),
                'currentPage' => $_tasks->count(),
                'lastPage' => $_tasks->lastPage(),
            ];

            $_tasksList = $_tasks->values()->toArray();

            return $this->sendResponsePaging($_tasksList, $paging, 200, 'Lấy thông tin thành công.');
        } catch (Exception $e) {
            Log::channel('work_shift')->error($e->getMessage());
            return $this->sendError($e->getMessage(), [], 500);
        }
    }

    /**
     * @OA\POST(
     *     path="/api/v1/work-shift/add",
     *     tags={"Work Shift"},
     *     summary="Add Work Shift",
     *     description="Add Work Shift",
     *     operationId="work_shift_add",
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
     *         description="Work Shift Name",
     *         in="path",
     *         name="name",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         description="Start Time",
     *         in="path",
     *         name="start_time",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         description="End Time",
     *         in="path",
     *         name="end_time",
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
    public function add(WorkShiftAdd $request)
    {
        try {
            $attributes = [
                'building_id' => $request->building_id,
                'work_shift_name' => $request->name,
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
                'status' => WorkShift::STATUS_ACTIVE
            ];
            $workShift = $this->_workShiftRespository->create($attributes);
            if($workShift) {
                $this->_workShiftRespository->reloadById($workShift->id);
                $this->_workShiftRespository->reloadByBuildingId($request->building_id);
                return $this->sendResponse([], 200, 'Add Work Shift successfully.');
            } else {
                return $this->sendResponse([], 200, 'Add Work Shift failure.');
            }
        } catch (Exception $e) {
            Log::channel('work_shift')->error($e->getMessage());
            return $this->sendError($e->getMessage(), [], 500);
        }
    }

    /**
     * @OA\POST(
     *     path="/api/v1/work-shift/show",
     *     tags={"Work Shift"},
     *     summary="Show Work Shift",
     *     description="Show Work Shift",
     *     operationId="work_shift_show",
     *     @OA\Parameter(
     *         description="Work Shift Id",
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
    public function show(WorkShiftShow $request)
    {
        try {
            $workShift = $this->_workShiftRespository->filterById($request->id);
            return $this->sendResponse($workShift, 200, 'Get Work Shift successfully.');
        } catch (Exception $e) {
            Log::channel('work_shift')->error($e->getMessage());
            return $this->sendError($e->getMessage(), [], 500);
        }
    }

    /**
     * @OA\PUT(
     *     path="/api/v1/work-shift/update",
     *     tags={"Work Shift"},
     *     summary="Update Work Shift",
     *     description="Update Work Shift",
     *     operationId="work_shift_update",
     *     @OA\Parameter(
     *         description="Category Id",
     *         in="path",
     *         name="id",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         description="Work Shift Name",
     *         in="path",
     *         name="name",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         description="Start time",
     *         in="path",
     *         name="start_time",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         description="End time",
     *         in="path",
     *         name="end_time",
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
    public function update(WorkShiftUpdate $request)
    {
        try {
            $id = $request->id;
            $attributes = [
                'work_shift_name' => $request->name,
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
            ];
            $workShift = $this->_workShiftRespository->update($id, $attributes);
            if($workShift) {
                $this->_workShiftRespository->reloadById($id);
                $this->_workShiftRespository->reloadByBuildingId($workShift->building_id);
                return $this->sendResponse([], 200, 'Update Work Shift successfully.');
            } else {
                return $this->sendResponse([], 200, 'Update Work Shift failure.');
            }
        } catch (Exception $e) {
            Log::channel('work_shift')->error($e->getMessage());
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
     *         description="Work Shift Id",
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
    public function updateStatus(Request $request)
    {
        try {
            $attributes = [
                'status' => $request->status,
            ];
            $workShift = $this->_workShiftRespository->update($request->id, $attributes);
            if($workShift) {
                $this->_workShiftRespository->reloadByBuildingId($workShift->building_id);
                $this->_workShiftRespository->reloadById($workShift->id);
                return $this->sendResponse([], 200, 'Update status work shift successfully.');
            } else {
                return $this->sendError('Update status work shift failure.', [], 500);
            }
        } catch (Exception $e) {
            Log::channel('work_shift')->error($e->getMessage());
            return $this->sendError($e->getMessage(), [], 500);
        }
    }

    /**
     * @OA\DELETE(
     *     path="/api/v1/work-shift/delete",
     *     tags={"Work Shift"},
     *     summary="Delete Work Shift",
     *     description="Delete Work Shift",
     *     operationId="work_shift_delete",
     *     @OA\Parameter(
     *         description="Work Shift Id",
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
    public function delete(WorkShiftShow $request)
    {
        try {
            $workShift = $this->_workShiftRespository->find($request->id);
            if($workShift) {
                $this->_workShiftRespository->delete($workShift->id);
                $this->_workShiftRespository->deleteRedisCache($workShift);
                return $this->sendResponse([], 200, 'Delete work shift successfully.');
            } else {
                return $this->sendResponse([], 501, 'Delete work shift failure.');
            }
        } catch (Exception $e) {
            Log::channel('work_shift')->error($e->getMessage());
            return $this->sendError($e->getMessage(), [], 500);
        }
    }
}
