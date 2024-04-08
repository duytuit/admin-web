<?php

namespace Modules\Tasks\Http\Controllers;

use App\Filter\TaskCategoryFilter;
use App\Http\Controllers\Controller;
//use App\Http\Requests\TaskCategory\TaskCategoryAdd;
//use App\Http\Requests\TaskCategory\TaskCategoryShow;
//use App\Http\Requests\TaskCategory\TaskCategoryUpdate;
//use App\Repositories\TaskCategory\TaskCategoryRespository;
//use App\Http\Requests\TaskCategory\TaskCategoryAdd;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Modules\Tasks\Http\Requests\TaskCategory\TaskCategoryAdd;
use Modules\Tasks\Http\Requests\TaskCategory\TaskCategoryShow;
use Modules\Tasks\Http\Requests\TaskCategory\TaskCategoryUpdate;
use Modules\Tasks\Repositories\TaskCategory\TaskCategoryRespository;

class TaskCategoriesController extends Controller
{
    protected $_taskCategoryRepository;

    public function __construct(TaskCategoryRespository $taskCategoryRespository)
    {
        $this->_taskCategoryRepository = $taskCategoryRespository;
    }

    /**
     * @OA\GET(
     *     path="/api/v1/task-category",
     *     tags={"Task Category"},
     *     summary="Task category list",
     *     description="Task category list",
     *     operationId="task_category_list",
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
     *         description="Category Name",
     *         in="path",
     *         name="category_name",
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
            $taskCategories = $this->_taskCategoryRepository->filterByBuildingId($request->building_id);
            $taskCategories = TaskCategoryFilter::index($taskCategories, $request);
            return $this->sendResponse($taskCategories, 200, 'Lấy thông tin thành công.');
        } catch (Exception $e) {
            Log::channel('task_category')->error($e->getMessage());
            return $this->sendError($e->getMessage(), [], 500);
        }
    }

    /**
     * @OA\POST(
     *     path="/api/v1/task-category/add",
     *     tags={"Task Category"},
     *     summary="Add task category",
     *     description="Add task category",
     *     operationId="task_category_add",
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
     *         description="Category Name",
     *         in="path",
     *         name="category_name",
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
    public function add(TaskCategoryAdd $request)
    {
        try {
            $user = $request->get('user');
            $attributes = [
                'building_id' => $request->building_id,
                'category_name' => $request->category_name,
            ];
            $taskCategory = $this->_taskCategoryRepository->create($attributes);
            if($taskCategory) {
                $this->_taskCategoryRepository->reloadById($taskCategory->id);
                $this->_taskCategoryRepository->reloadByBuildingId($request->building_id);
                return $this->sendResponse([], 200, 'Add task catetory successfully.');
            } else {
                return $this->sendResponse([], 200, 'Add task catetory failure.');
            }
        } catch (Exception $e) {
            Log::channel('task_category')->error($e->getMessage());
            return $this->sendError($e->getMessage(), [], 500);
        }
    }

    /**
     * @OA\POST(
     *     path="/api/v1/task-category/show",
     *     tags={"Task Category"},
     *     summary="Add task category",
     *     description="Add task category",
     *     operationId="task_category_add",
     *     @OA\Parameter(
     *         description="Category Id",
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
    public function show(TaskCategoryShow $request)
    {
        try {
            $taskCategory = $this->_taskCategoryRepository->filterById($request->id);
            return $this->sendResponse($taskCategory, 200, 'Get task catetory successfully.');
        } catch (Exception $e) {
            Log::channel('task_category')->error($e->getMessage());
            return $this->sendError($e->getMessage(), [], 500);
        }
    }

    /**
     * @OA\PUT(
     *     path="/api/v1/task-category/update",
     *     tags={"Task Category"},
     *     summary="Add task category",
     *     description="Add task category",
     *     operationId="task_category_add",
     *     @OA\Parameter(
     *         description="Category Id",
     *         in="path",
     *         name="category_id",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         description="Category Name",
     *         in="path",
     *         name="category_name",
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
    public function update(TaskCategoryUpdate $request)
    {
        try {
            $id = $request->category_id;
            $attributes = [
                'category_name' => $request->category_name,
            ];
            $taskCategory = $this->_taskCategoryRepository->update($id, $attributes);
            if($taskCategory) {
                $this->_taskCategoryRepository->reloadById($id);
                $this->_taskCategoryRepository->reloadByBuildingId($taskCategory->building_id);
                return $this->sendResponse([], 200, 'Update task catetory successfully.');
            } else {
                return $this->sendResponse([], 200, 'Update task catetory failure.');
            }
        } catch (Exception $e) {
            Log::channel('task_category')->error($e->getMessage());
            return $this->sendError($e->getMessage(), [], 500);
        }
    }

    /**
     * @OA\DELETE(
     *     path="/api/v1/task-category/delete",
     *     tags={"Task Category"},
     *     summary="Delete task category",
     *     description="Delete task category",
     *     operationId="task_category_delete",
     *     @OA\Parameter(
     *         description="Category Id",
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
    public function delete(TaskCategoryShow $request)
    {
        try {
            $taskCategory = $this->_taskCategoryRepository->find($request->id);
            if($taskCategory) {
                $this->_taskCategoryRepository->delete($taskCategory->id);
                $this->_taskCategoryRepository->deleteRedisCache($taskCategory);
                return $this->sendResponse([], 200, 'Delete task catetory successfully.');
            } else {
                return $this->sendResponse([], 501, 'Delete task catetory failure.');
            }
        } catch (Exception $e) {
            Log::channel('task_category')->error($e->getMessage());
            return $this->sendError($e->getMessage(), [], 500);
        }
    }
}
