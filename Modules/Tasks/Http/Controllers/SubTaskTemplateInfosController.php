<?php

namespace Modules\Tasks\Http\Controllers;

//use App\Repositories\SubTaskTemplateInfo\SubTaskTemplateInfoRespository;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Modules\Tasks\Repositories\SubTaskTemplateInfo\SubTaskTemplateInfoRespository;

class SubTaskTemplateInfosController extends Controller
{
    protected $_subTaskTemplateInfoRespository;

    public function __construct(SubTaskTemplateInfoRespository $subTaskTemplateInfoRespository)
    {
        $this->_subTaskTemplateInfoRespository = $subTaskTemplateInfoRespository;
    }

    /**
     * @OA\GET(
     *     path="/api/v1/sub-task-template-info",
     *     tags={"Sub Task Template Info"},
     *     summary="Sub Task Template Info List",
     *     description="Sub Task Template Info List",
     *     operationId="sub_task_template_info",
     *     @OA\Parameter(
     *         description="Sub Task Template Id",
     *         in="path",
     *         name="sub_task_template_id",
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
            $subTaskTemplateInfos = $this->_subTaskTemplateInfoRespository->findColumns(['sub_task_template_id' => $request->sub_task_template_id])->get();
            return $this->sendResponse($subTaskTemplateInfos, 200, 'Lấy thông tin thành công.');
        } catch (Exception $e) {
            Log::channel('sub_task_template_info')->error($e->getMessage());
            return $this->sendError($e->getMessage(), [], 500);
        }
    }

}
