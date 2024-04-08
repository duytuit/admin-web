<?php

namespace Modules\Assets\Http\Controllers;

use App\Http\Controllers\Controller;
//use App\Http\Requests\Admin\AdminDepartment;
//use App\Repositories\UserBuilding\UserBuildingRespository;
//use App\Repositories\UserHasDepartment\UserHasDepartmentRespository;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Modules\Assets\Http\Requests\Admin\AdminDepartment;
use Modules\Assets\Repositories\UserBuilding\UserBuildingRespository;
use Modules\Assets\Repositories\UserHasDepartment\UserHasDepartmentRespository;

class AdminsController extends Controller
{
    protected $_userBuildingRespository;
    protected $_userHasDepartmentRespository;

    public function __construct(UserBuildingRespository $userBuildingRespository, UserHasDepartmentRespository $userHasDepartmentRespository)
    {
        $this->_userBuildingRespository = $userBuildingRespository;
        $this->_userHasDepartmentRespository = $userHasDepartmentRespository;
    }

    /**
     * @OA\GET(
     *     path="/api/v1/admin/list",
     *     tags={"Admin"},
     *     summary="Admin list",
     *     description="Admin list",
     *     operationId="admin_list",
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
    public function getList(Request $request)
    {
        try {
            $user = (object)$request->get('user');
            $userBuildings = $this->_userBuildingRespository->filterByBuildingId($request->building_id);
            $userIds = array_column($userBuildings->toArray(), 'user_id');
            $userIdString = implode(",", $userIds);

            $client = new \GuzzleHttp\Client();
            $headers = [
                'Content-Type' => 'application/json',
                'Authorization' => $request->header('Authorization')
            ];
            $data = [
                "user_ids" => $userIdString,
            ];
            $requestClient = $client->request('GET', env('AUTH_SERVICE_URL') . 'api/v1/admin/list', ['headers' => $headers, 'body' => json_encode($data)]);
            $users = json_decode((string) $requestClient->getBody(), true);
            return $users['status'] == 200
                ? $this->sendResponse($users['data'], 200, 'Get info successfully.')
                : $this->sendError('Data not found...', [], 500);
        } catch (Exception $e) {
            Log::channel('admin')->error($e->getMessage());
            return $this->sendError($e->getMessage(), [], 500);
        }
    }

    /**
     * @OA\GET(
     *     path="/api/v1/admin/filter-phone",
     *     tags={"Admin"},
     *     summary="Tìm theo số điện thoại",
     *     description="Tìm theo số điện thoại",
     *     operationId="admin_list",
     *     @OA\Parameter(
     *         description="Phone",
     *         in="path",
     *         name="phone",
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
    public function filterPhone(Request $request)
    {
        try {
            $client = new \GuzzleHttp\Client();
            $headers = [
                'Content-Type' => 'application/json',
                'Authorization' => $request->header('Authorization')
            ];
            $data = [
                "phone" => $request->phone,
            ];
            $requestClient = $client->request('GET', env('AUTH_SERVICE_URL') . 'api/v1/admin/filter-phone', ['headers' => $headers, 'body' => json_encode($data)]);
            $users = json_decode((string) $requestClient->getBody(), true);
            return $users['status'] == 200
                ? $this->sendResponse($users['data'], 200, 'Get info successfully.')
                : $this->sendError('Data not found...', [], 500);
        } catch (Exception $e) {
            Log::channel('admin')->error($e->getMessage());
            return $this->sendError($e->getMessage(), [], 500);
        }
    }

    /**
     * @OA\GET(
     *     path="/api/v1/admin/department",
     *     tags={"Admin"},
     *     summary="Admin Has Department",
     *     description="Admin Has Department",
     *     operationId="admin_has_department",
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
     *         description="Role Type",
     *         in="path",
     *         name="role_type",
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
    public function department(AdminDepartment $request)
    {
        try {
            $user = (object)$request->get('user');
            $assignUser = $this->_userHasDepartmentRespository->assignUser(
                $user->user_id,
                $request->building_id,
                $request->department_id,
                $request->role_type);
            $userIds = array_column($assignUser->toArray(), 'user_id');
            $userIdString = implode(",", $userIds);
            $client = new \GuzzleHttp\Client();
            $headers = [
                'Content-Type' => 'application/json',
                'Authorization' => $request->header('Authorization')
            ];
            $data = [
                "user_ids" => $userIdString,
            ];
            $requestClient = $client->request('GET', env('AUTH_SERVICE_URL') . 'api/v1/admin/list', ['headers' => $headers, 'body' => json_encode($data)]);
            $users = json_decode((string) $requestClient->getBody(), true);
            return $users['status'] == 200
                ? $this->sendResponse($users['data'], 200, 'Get info successfully.')
                : $this->sendError('Data not found...', [], 500);
        } catch (Exception $e) {
            Log::channel('admin')->error($e->getMessage());
            return $this->sendError($e->getMessage(), [], 500);
        }
    }
}
