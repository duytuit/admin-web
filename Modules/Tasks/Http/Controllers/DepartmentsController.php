<?php

namespace Modules\Tasks\Http\Controllers;

use App\Http\Controllers\Controller;
//use App\Repositories\Department\DepartmentRespository;
//use App\Repositories\UserHasDepartment\UserHasDepartmentRespository;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;
use Modules\Assets\Repositories\UserHasDepartment\UserHasDepartmentRespository;
use Modules\Tasks\Repositories\Department\DepartmentRespository;

class DepartmentsController extends Controller
{
    protected $_departmentRespository;
    protected $_userHasDepartmentRespository;
    protected $_userHasDepartment;

    public function __construct(DepartmentRespository $departmentRespository, UserHasDepartmentRespository $userHasDepartmentRespository, UserHasDepartmentRespository $userHasDepartment)
    {
        $this->_departmentRespository = $departmentRespository;
        $this->_userHasDepartmentRespository = $userHasDepartmentRespository;
        $this->_userHasDepartment = $userHasDepartment;
    }

    /**
     * @OA\GET(
     *     path="/api/v1/department",
     *     tags={"Department"},
     *     summary="Department List",
     *     description="Department List",
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
     *     @OA\Parameter(
     *         description="Department Name",
     *         in="path",
     *         name="name",
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
            $departments = $this->_departmentRespository->findColumns(['bdc_building_id' => $request->building_id]);
            if(isset($request->name) && $request->name != null) {
                $departments = $departments->where('name', 'like', '%' . $request->name . '%');
            }
            $departments = $departments->get();
            return $this->sendResponse($departments, 200, 'Lấy thông tin thành công.');
        } catch (Exception $e) {
            Log::channel('department')->error($e->getMessage());
            return $this->sendError($e->getMessage(), [], 500);
        }
    }

    /**
     * @OA\GET(
     *     path="/api/v1/department/list-admin",
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
     *   @OA\Response(response=200, description="successful operation"),
     *   @OA\Response(response=406, description="not acceptable"),
     *   @OA\Response(response=500, description="internal server error")
     * )
     */
    public function listAdmin(Request $request)
    {
        try {
            $limit = isset($request->limit) ? $request->limit : 10;
            $page = isset($request->page) ? $request->page : 1;
            $listUser = $this->_userHasDepartmentRespository->listAdmin(
                $request->building_id,
                $request->department_id);

            $userIds = array_column($listUser->toArray(), 'user_id');
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

            if($users['status'] == 200) {
                $arrUser = [];
                if($users['data'] != null) {
                    foreach($users['data'] as $_user) {
                        $userHasDepartment = $this->_userHasDepartment->getDepartment($_user['user_id'], $request->building_id, $request->department_id);
                        $_user['department'] = $userHasDepartment;
                        array_push($arrUser, $_user);
                    }
                    $offSet = ($page * $limit) - $limit;
                    $itemsForCurrentPage = array_slice($arrUser, $offSet, $limit, true);
                    $_users = new LengthAwarePaginator($itemsForCurrentPage, count($arrUser), $limit, $page, []);
                    $paging = [
                        'total' => $_users->total(),
                        'currentPage' => $_users->count(),
                        'lastPage' => $_users->lastPage(),
                    ];

                    $_usersList = $_users->values()->toArray();
                    return $this->sendResponsePaging($_usersList, $paging, 200, 'Get info successfully.');
                } else {
                    $this->sendError('Data not found...', [], 500);
                }
            }
        } catch (Exception $e) {
            Log::channel('admin')->error($e->getMessage());
            return $this->sendError($e->getMessage(), [], 500);
        }
    }
}
