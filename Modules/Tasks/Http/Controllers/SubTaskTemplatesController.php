<?php

namespace Modules\Tasks\Http\Controllers;

use App\Filter\SubTaskTemplateFilter;
use App\Http\Controllers\Controller;
//use App\Http\Requests\SubTaskTemplate\SubTaskTemplateAdd;
//use App\Http\Requests\SubTaskTemplate\SubTaskTemplateShow;
//use App\Http\Requests\SubTaskTemplate\SubTaskTemplateSttInfoAdd;
//use App\Http\Requests\SubTaskTemplate\SubTaskTemplateSttInfoUpdate;
//use App\Http\Requests\SubTaskTemplate\SubTaskTemplateUpdate;
//use App\Repositories\SubTaskTemplate\SubTaskTemplateRespository;
//use App\Repositories\SubTaskTemplateInfo\SubTaskTemplateInfoRespository;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;
use Modules\Tasks\Http\Requests\SubTaskTemplate\SubTaskTemplateAdd;
use Modules\Tasks\Http\Requests\SubTaskTemplate\SubTaskTemplateShow;
use Modules\Tasks\Http\Requests\SubTaskTemplate\SubTaskTemplateSttInfoAdd;
use Modules\Tasks\Http\Requests\SubTaskTemplate\SubTaskTemplateSttInfoUpdate;
use Modules\Tasks\Http\Requests\SubTaskTemplate\SubTaskTemplateUpdate;
use Modules\Tasks\Repositories\SubTaskTemplate\SubTaskTemplateRespository;
use Modules\Tasks\Repositories\SubTaskTemplateInfo\SubTaskTemplateInfoRespository;

class SubTaskTemplatesController extends Controller
{
    protected $_subTaskTemplateRespository;
    protected $_subTaskTemplateInfoRespository;

    public function __construct(SubTaskTemplateRespository $subTaskTemplateRespository, SubTaskTemplateInfoRespository $subTaskTemplateInfoRespository)
    {
        $this->_subTaskTemplateRespository = $subTaskTemplateRespository;
        $this->_subTaskTemplateInfoRespository = $subTaskTemplateInfoRespository;
    }

    /**
     * @OA\GET(
     *     path="/api/v1/sub-task-template",
     *     tags={"Sub Task Template"},
     *     summary="Sub Task Template List",
     *     description="Sub Task Template List",
     *     operationId="sub_task_template",
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
     *         description="Title",
     *         in="path",
     *         name="title",
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
            $limit = isset($request->limit) ? $request->limit : 10;
            $page = isset($request->page) ? $request->page : 1;

            $subTaskTemplates = $this->_subTaskTemplateRespository->filterByBuildingId($request->building_id);
            $subTaskTemplates = SubTaskTemplateFilter::index($subTaskTemplates, $request);
            $offSet = ($page * $limit) - $limit;
            $itemsForCurrentPage = array_slice($subTaskTemplates->toArray(), $offSet, $limit, true);
            $_subTaskTemplates = new LengthAwarePaginator($itemsForCurrentPage, count($subTaskTemplates), $limit, $page, []);
            $paging = [
                'total' => $_subTaskTemplates->total(),
                'currentPage' => $_subTaskTemplates->count(),
                'lastPage' => $_subTaskTemplates->lastPage(),
            ];
            $_subTaskTemplatesList = $_subTaskTemplates->values()->toArray();
            return $this->sendResponsePaging($_subTaskTemplatesList, $paging, 200, 'Lấy thông tin thành công.');
        } catch (Exception $e) {
            Log::channel('sub_task_template')->error($e->getMessage());
            return $this->sendError($e->getMessage(), [], 500);
        }
    }

    /**
     * @OA\POST(
     *     path="/api/v1/sub-task-template/add",
     *     tags={"Sub Task Template"},
     *     summary="Add Sub Task Template",
     *     description="Add Sub Task Template",
     *     operationId="sub_task_template_add",
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
     *         description="Category Name",
     *         in="path",
     *         name="title",
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
    public function add(SubTaskTemplateAdd $request)
    {
        try {
            $attributes = [
                'building_id' => $request->building_id,
                'bdc_department_id' => $request->department_id,
                'title' => $request->title,
            ];
            $subTaskTemplate = $this->_subTaskTemplateRespository->create($attributes);
            if($subTaskTemplate) {
                $this->_subTaskTemplateRespository->reloadByBuildingId($request->building_id);
                $this->_subTaskTemplateRespository->reloadById($subTaskTemplate->id);
                return $this->sendResponse([], 200, 'Add sub task template successfully.');
            } else {
                return $this->sendResponse([], 200, 'Add sub task template failure.');
            }
        } catch (Exception $e) {
            Log::channel('sub_task_template')->error($e->getMessage());
            return $this->sendError($e->getMessage(), [], 500);
        }
    }

    /**
     * @OA\POST(
     *     path="/api/v1/sub-task-template/stt-infos/add",
     *     tags={"Sub Task Template"},
     *     summary="Add Sub Task Template",
     *     description="Add Sub Task Template",
     *     operationId="sub_task_template_add",
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
     *         name="department_ids",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         description="Category Name",
     *         in="path",
     *         name="title",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         description="Sub Task Template Info",
     *         in="path",
     *         name="sub_task_template_infos",
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
    public function addSubTaskTempInfo(SubTaskTemplateSttInfoAdd $request)
    {
        try {
            \DB::beginTransaction();
            $departmentIds = json_decode($request->department_ids);
            foreach($departmentIds as $_departmentId) {
                $attributes = [
                    'building_id' => $request->building_id,
                    'bdc_department_id' => $_departmentId,
                    'title' => $request->title,
                ];
                $subTaskTemplate = $this->_subTaskTemplateRespository->create($attributes);
                if($subTaskTemplate) {
                    $subTaskTempInfos = json_decode($request->sub_task_template_infos);
                    foreach($subTaskTempInfos as $_subTaskTempInfo) {
                        $attributeSttInfos = [
                            'sub_task_template_id' => $subTaskTemplate->id,
                            'title' => $_subTaskTempInfo->title,
                            'description' => $_subTaskTempInfo->description,
                        ];
                        $this->_subTaskTemplateInfoRespository->create($attributeSttInfos);
                    }
                    $this->_subTaskTemplateRespository->reloadByBuildingId($request->building_id);
                    $this->_subTaskTemplateRespository->reloadById($subTaskTemplate->id);
                } else {
                    \DB::rollBack();
                    return $this->sendResponse([], 200, 'Add sub task template failure.');
                }
            }
            \DB::commit();
            return $this->sendResponse([], 200, 'Add sub task template successfully.');
        } catch (Exception $e) {
            \DB::rollBack();
            Log::channel('sub_task_template')->error($e->getMessage());
            return $this->sendError($e->getMessage(), [], 500);
        }
    }

    /**
     * @OA\PUT(
     *     path="/api/v1/sub-task-template/stt-infos/update",
     *     tags={"Sub Task Template"},
     *     summary="Add Sub Task Template",
     *     description="Add Sub Task Template",
     *     operationId="sub_task_template_update",
     *     @OA\Parameter(
     *         description="Sub Task Template Id",
     *         in="path",
     *         name="id",
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
     *         description="Category Name",
     *         in="path",
     *         name="title",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         description="Sub Task Template Info",
     *         in="path",
     *         name="sub_task_template_infos",
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
    public function updateSubTaskTempInfo(SubTaskTemplateSttInfoUpdate $request)
    {
        try {
            \DB::beginTransaction();
            $attributes = [
                'bdc_department_id' => $request->department_id,
                'title' => $request->title,
            ];
            $subTaskTemplate = $this->_subTaskTemplateRespository->update($request->id, $attributes);
            if($subTaskTemplate) {
                $this->_subTaskTemplateInfoRespository->deleteBySubTaskTempateId($subTaskTemplate->id);

                $subTaskTempInfos = json_decode($request->sub_task_template_infos);
                foreach($subTaskTempInfos as $_subTaskTempInfo) {
                    $attributeSttInfos = [
                        'sub_task_template_id' => $subTaskTemplate->id,
                        'title' => $_subTaskTempInfo->title,
                        'description' => $_subTaskTempInfo->description,
                    ];
                    $this->_subTaskTemplateInfoRespository->create($attributeSttInfos);
                }

                //Clear cache SubTaskTemplate
                $this->_subTaskTemplateRespository->reloadByBuildingId($subTaskTemplate->building_id);
                $this->_subTaskTemplateRespository->reloadById($subTaskTemplate->id);
                \DB::commit();
                return $this->sendResponse([], 200, 'Update sub task template successfully.');
            } else {
                \DB::rollBack();
                return $this->sendResponse([], 200, 'Update sub task template failure.');
            }
        } catch (Exception $e) {
            \DB::rollBack();
            Log::channel('sub_task_template')->error($e->getMessage());
            return $this->sendError($e->getMessage(), [], 500);
        }
    }

    /**
     * @OA\POST(
     *     path="/api/v1/sub-task-template/show",
     *     tags={"Sub Task Template"},
     *     summary="Show Sub Task Template",
     *     description="Show Sub Task Template",
     *     operationId="sub_task_template_show",
     *     @OA\Parameter(
     *         description="Sub Task Template Id",
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
    public function show(SubTaskTemplateShow $request)
    {
        try {
            $taskCategory = $this->_subTaskTemplateRespository->filterById($request->id);
            return $this->sendResponse($taskCategory, 200, 'Get sub task template successfully.');
        } catch (Exception $e) {
            Log::channel('sub_task_template')->error($e->getMessage());
            return $this->sendError($e->getMessage(), [], 500);
        }
    }

    /**
     * @OA\PUT(
     *     path="/api/v1/sub-task-template/update",
     *     tags={"Sub Task Template"},
     *     summary="Update Sub Task Template",
     *     description="Update Sub Task Template",
     *     operationId="sub_task_template_update",
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
     *         description="Category Name",
     *         in="path",
     *         name="title",
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
    public function update(SubTaskTemplateUpdate $request)
    {
        try {
            $id = $request->id;
            $attributes = [
                'title' => $request->title,
            ];
            $subTaskTemp = $this->_subTaskTemplateRespository->update($id, $attributes);
            if($subTaskTemp) {
                //Clear cache SubTaskTemplate
                $this->_subTaskTemplateRespository->reloadById($id);
                $this->_subTaskTemplateRespository->reloadByBuildingId($subTaskTemp->building_id);
                return $this->sendResponse([], 200, 'Update sub task template successfully.');
            } else {
                return $this->sendResponse([], 200, 'Update sub task template failure.');
            }
        } catch (Exception $e) {
            Log::channel('sub_task_template')->error($e->getMessage());
            return $this->sendError($e->getMessage(), [], 500);
        }
    }

    /**
     * @OA\DELETE(
     *     path="/api/v1/sub-task-template/delete",
     *     tags={"Sub Task Template"},
     *     summary="Delete Sub Task Template",
     *     description="Delete Sub Task Template",
     *     operationId="sub_task_template_delete",
     *     @OA\Parameter(
     *         description="Sub Task Template Id",
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
    public function delete(SubTaskTemplateShow $request)
    {
        try {
            $subTaskTemplate = $this->_subTaskTemplateRespository->find($request->id);
            if($subTaskTemplate) {
                $this->_subTaskTemplateRespository->delete($subTaskTemplate->id);
                //Clear cache SubTaskTemplate
                $this->_subTaskTemplateRespository->deleteRedisCache($subTaskTemplate);
                return $this->sendResponse([], 200, 'Delete sub task template successfully.');
            } else {
                return $this->sendResponse([], 501, 'Delete sub task template failure.');
            }
        } catch (Exception $e) {
            Log::channel('sub_task_template')->error($e->getMessage());
            return $this->sendError($e->getMessage(), [], 500);
        }
    }
}
