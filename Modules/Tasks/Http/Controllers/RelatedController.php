<?php

namespace Modules\Tasks\Http\Controllers;

use App\Http\Controllers\Controller;
//use App\Repositories\Department\DepartmentRespository;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RelatedController extends Controller
{
    /**
     * @OA\GET(
     *     path="/api/v1/related",
     *     tags={"Related"},
     *     summary="Related List",
     *     description="Related List",
     *     operationId="related_list",
     *   @OA\Response(response=200, description="successful operation"),
     *   @OA\Response(response=406, description="not acceptable"),
     *   @OA\Response(response=500, description="internal server error")
     * )
     */
    public function index(Request $request)
    {
        try {
            $data = [
                [
                    "id" => "lich_bao_tri",
                    "name" => "Lịch bảo trì"
                ],
                [
                    "id" => "phan_hoi_cu_dan",
                    "name" => "Phản hồi cư dân"
                ],
                [
                    "id" => "sua_chua_can_ho",
                    "name" => "Sửa chữa căn hộ"
                ],
            ];
            return $this->sendResponse($data, 200, 'Lấy thông tin thành công.');
        } catch (Exception $e) {
            Log::channel('related')->error($e->getMessage());
            return $this->sendError($e->getMessage(), [], 500);
        }
    }
}
