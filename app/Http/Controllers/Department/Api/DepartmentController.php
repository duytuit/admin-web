<?php

namespace App\Http\Controllers\Department\Api;

use App\Repositories\Department\DepartmentRepository;
use App\Repositories\Department\DepartmentStaffRepository;
use App\Http\Controllers\Controller;
use App\Repositories\PublicUsers\PublicUsersProfileRespository;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    protected $departmentRepository;
    protected $departmentStaffRepository;
    protected $user_profile;
    use ApiResponse;

    public function __construct(Request $request,
        DepartmentRepository $departmentRepository,
        PublicUsersProfileRespository $user_profile,
        DepartmentStaffRepository $departmentStaffRepository
    )
    {
        $this->departmentRepository = $departmentRepository;
        $this->departmentStaffRepository = $departmentStaffRepository;
        $this->user_profile = $user_profile;
        //$this->middleware('jwt.auth');
    }

    public function index()
    {
        $departments = $this->departmentRepository->findByBuildingId(1);
        $data = [];
        foreach ($departments as $key => $department)
        {
            $data[$key]['id'] = $department->id;
            $data[$key]['name'] = $department->name;
        }
        return $this->responseSuccess($data, 'Success', 200);
    }

    public function getListByBuilding(Request $request)
    {
        $departments = $this->departmentRepository->listDepartmentsNew($request)->select('id','name')->get()->toArray();
        return $this->responseSuccess($departments, 'Success', 200);
    }

    public function detail($id)
    {
        if ($id > 0) {
            $staffs = $this->departmentStaffRepository->staffByDepartment($id);
            if ($staffs) {
                $list_id = $staffs->pluck('pub_user_id');

                $userprofiles = $this->user_profile->findByPubUserId($list_id);
                $data = [];
                foreach ($userprofiles as $key => $userprofile)
                {
                    $data[$key]['id'] = $userprofile->id;
                    $data[$key]['name'] = $userprofile->display_name;
                }
                return $this->responseSuccess($data, 'Success', 200);
            }
            return $this->responseError(['Không có staff trong id này'], 204);
        }
        return $this->responseError(['Không có id department'], 204);
    }
}
