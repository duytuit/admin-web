<?php

namespace App\Http\Controllers\WorkDiary\Api;

use App\Models\WorkDiary\WorkDiary;
use App\Repositories\Building\BuildingRepository;
use App\Repositories\Department\DepartmentRepository;
use App\Repositories\Department\DepartmentStaffRepository;
use App\Repositories\PublicUsers\PublicUsersProfileRespository;
use App\Repositories\SystemFiles\SystemFilesRespository;
use App\Repositories\WorkDiary\WorkDiaryRepository;
use App\Services\AppUserPermissions;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Validator;

class WorkDiaryController extends Controller
{
    use ApiResponse;

    const DUPLICATE = 19999;
    const LOGIN_FAIL = 10000;
    private $workDiaryRepository;
    private $departmentRepository;
    private $userProfile;
    private $buildingRepository;
    private $departmentStaffRepository;

    /**
     * Constructor.
     */
    public function __construct(
        WorkDiaryRepository $workDiaryRepository,
        DepartmentRepository $departmentRepository,
        PublicUsersProfileRespository $userProfile,
        BuildingRepository $buildingRepository,
        DepartmentStaffRepository $departmentStaffRepository
    )
    {
        //$this->middleware('jwt.auth');
        $this->workDiaryRepository = $workDiaryRepository;
        $this->departmentRepository = $departmentRepository;
        $this->buildingRepository = $buildingRepository;
        $this->departmentStaffRepository = $departmentStaffRepository;
        $this->userProfile = $userProfile;
    }

    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'building_id' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->validateFail($validator->errors());
        }
        $user = \Auth::guard('public_user')->user();
        $userInfo = $user->infoWeb->where('bdc_building_id', $request->building_id)->first();
        if ($userInfo) {
            $routePermissions = AppUserPermissions::getAccessRouter($user);
            $per_page = isset($request['per_page']) ? ($request['per_page'] > 0 ? $request['per_page'] : 10) : 10;
            $page = isset($request['page']) ? ($request['page'] > 0 ? $request['page'] : 1) : 1;
            $filter = $request->except(['per_page', 'page']);
            $workDiary = $this->workDiaryRepository->getWorkDiary($page, $per_page, $filter, $userInfo, $routePermissions);
            $extraData['info'] = $workDiary['info'];
            return $this->responseSuccess($workDiary['data'], 'Success', 200, $extraData);
        }
        return $this->responseError(['Không lấy được thông tin User'], self::LOGIN_FAIL);
    }

    public function detail($id, SystemFilesRespository $filesRespository, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'building_id' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->validateFail($validator->errors());
        }
        $userInfo = \Auth::guard('public_user')->user()->infoWeb->where('bdc_building_id', $request->building_id)->first();
        if ($userInfo) {
            if ($id > 0) {
                $work = $this->workDiaryRepository->findWorkDiary($id);
                if ($work) {
                    $permission = $this->checkPermission($userInfo, $work);
                    $files = $filesRespository->getModulFile('App\Models\WorkDiary', $work->id);
                    $workDiary = $this->workDiaryRepository->findWork($work, $this->userProfile, $permission);
                    $extraData['info'] = $workDiary['info'];
                    $workDiary['work']['files'] = [];
                    foreach ($files as $file) {
                        $workDiary['work']['files'][] = $file->url;
                    }
                    return $this->responseSuccess($workDiary['work'], 'Success', 200, $extraData);
                }
                return $this->responseError(['Id công việc không đúng'], 204);
            }
            return $this->responseError(['Không có id công việc'], 204);
        }
        return $this->responseError(['Không lấy được thông tin User'], self::LOGIN_FAIL);
    }

    public function checkPermission($user, $task)
    {
        $building = $this->buildingRepository->getActiveBuilding($user->bdc_building_id);
        $bdc_department_id = $building->bdc_department_id;
        $department = $this->departmentRepository->findByBuildingIdAndDepartmentId($user->bdc_building_id, $bdc_department_id);

        $supervisor_ids = [];
        if ($department) {
            $list_id =  $department->department_staffs ;
            foreach ($list_id as $staff) {
                $supervisor_ids[] = $staff->publicUser->getUserInfoId($user->bdc_building_id)->id;
            }
        }


        if ($user->id == $building->manager_id) {
            return WorkDiary::P_MANAGER;
        } elseif (in_array($user->id, $supervisor_ids)) {
            return WorkDiary::P_SUPERVISOR;
        } elseif ($user->id == $task->assign_to) {
            return WorkDiary::P_ASSIGN_TO;
        } elseif ($user->id == $task->created_by) {
            return WorkDiary::P_CREATED_BY;
        } else {
            return WorkDiary::P_OTHER;
        }
    }

    public function updateStatus($id, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'building_id' => 'required',
            'new_status' => 'required|max:5',
            'content' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->validateFail($validator->errors());
        }
        $userInfo = \Auth::guard('public_user')->user()->infoWeb->where('bdc_building_id', $request->building_id)->first();
        if ($userInfo) {
            if ($id > 0) {
                $work = $this->workDiaryRepository->findWorkDiary($id);
                if ($work) {
                    $new_review_note = [
                        'user_id' => $userInfo->id,
                        'previous_status' => $work->status,
                        'current_status' => $request->new_status,
                        'note' => $request->content,
                        'date' => date("H:i d/m/Y"),
                    ];
                    $data['review_note'] = json_decode($work->review_note, true);
                    if ($data['review_note'] == null) {
                        $data['review_note'] = [];
                    }
                    array_push($data['review_note'], $new_review_note);

                    $data['review_note'] = json_encode($data['review_note']);
                    $data['status'] = $request->new_status;

                    $checkUpdate = $this->workDiaryRepository->update($data, $id);
                    if ($checkUpdate) {
                        return $this->responseSuccess([], 'Cập nhật trạng thái thành công');
                    } else {
                        return $this->responseError(['Không có dữ liệu.'], 201);
                    }

                }
                return $this->responseError(['Id công việc không đúng'], 204);
            }
            return $this->responseError(['Không có id công việc'], 204);
        }
        return $this->responseError(['Không lấy được thông tin User'], self::LOGIN_FAIL);
    }

    public function store(Request $request, SystemFilesRespository $filesRespository)
    {
//        dd($request->all());
        $validator = Validator::make($request->all(), [
            'title'       => 'required|max:191',
            'description' => 'required',
//            'deadline'    => 'required|date|date_format:d/m/Y|after:today',
            'deadline'    => 'required|date|date_format:d/m/Y',
            'building_id' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->validateFail($validator->errors());
        }
        $userInfo = \Auth::guard('public_user')->user()->infoWeb->where('bdc_building_id', $request->building_id)->first();
        if ($userInfo) {
            $data = $request->except(['attachments', 'building_id', 'deadline']);
            $data['created_by'] = $userInfo->id;
            if( $request->staff && $request->department ) {
                $assign_to         = $request->staff;
                $bdc_department_id = $request->department;
            } else {
                $assign_to         = $userInfo->id;
                $bdc_department_id = @$userInfo->pubusers->departmentUser->bdc_department_id;
            }

            $data['assign_to']         = $assign_to;
            $data['bdc_building_id']   = $request->building_id;
            $data['bdc_department_id'] = $bdc_department_id;
            $data['start_at']          = date("Y-m-d");
            $data['end_at']            = date("Y-m-d", strtotime($request->deadline));
            // save log json
            $new_log = [
                'user_id' => \Auth::user()->id,
                'action'  => 'add',
                'time'    => date("H:i d/m/Y"),
            ];

            $data['logs'] = [];

            array_push($data['logs'], $new_log);
            $data['logs'] = json_encode($data['logs']);
            $new_task     = $this->workDiaryRepository->create($data);
            if ($new_task) {
                if ($request->attachments && count($request->attachments) > 0) {
                    foreach ($request->attachments as $file) {
                        $this->filePathParts($file, $new_task, $request->building_id, $filesRespository);
                    }
                }
                return $this->responseSuccess([], 'Thêm mới thành công');
            }
            return $this->responseError(['Không thể thêm mới'], 204);
        }
        return $this->responseError(['Không lấy được thông tin User'], self::LOGIN_FAIL);
    }

    private function filePathParts($file, $new_task, $building_id, $filesRespository)
    {
        $readFile = pathinfo($file);
        $file_data = [
            'building_id' => $building_id,
            'name' => $readFile['filename'],
            'description' => '',
            'type' => $readFile['extension'],
            'url' => $file,
            'model_type' => 'App\Models\WorkDiary',
            'model_id' => $new_task->id,
            'status' => 1
        ];

        $insertFile = $filesRespository->create($file_data);
        if (!$insertFile) {
            return $this->responseError(['Không thể thêm mới'], 204);
        }
    }

    public function delete($id, SystemFilesRespository $filesRespository)
    {
        if ($id > 0) {
            $work = $this->workDiaryRepository->findWorkDiary($id);
            if ($work) {
                $filesRespository->deleteModulFile('App\Models\WorkDiary', $work->id);
                $work->delete();
                return $this->responseSuccess([], 'Xóa thành công', 200);
            }
            return $this->responseError(['Id công việc không đúng'], 204);
        }
        return $this->responseError(['Không có id công việc'], 204);
    }

    public function update(Request $request, SystemFilesRespository $filesRespository, $id)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|max:191',
            'description' => 'required',
            'deadline' => 'required|date|after:today',
            'building_id' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->validateFail($validator->errors());
        }

        $userInfo = \Auth::guard('public_user')->user()->infoWeb->where('bdc_building_id', $request->building_id)->first();
        if ($userInfo) {
            if ($id > 0) {
                $work = $this->workDiaryRepository->findWorkDiary($id);
                if ($work) {
                    $data = $request->except(['attachments', 'building_id', 'deadline']);
                    $data['updated_by'] = $userInfo->id;
                    $data['end_at'] = date("Y-m-d", strtotime($request->deadline));

                    // update log json
                    $new_log = [
                        'user_id' => $userInfo->id,
                        'action' => 'edit',
                        'time' => date("H:i d/m/Y"),
                    ];

                    // merge with old logs of model
                    $data['logs'] = json_decode($this->workDiaryRepository->find($id)->logs, true);
                    if ($data['logs'] == null) {
                        $data['logs'] = [];
                    }
                    array_push($data['logs'], $new_log);
                    $data['logs'] = json_encode($data['logs']);

                    $this->workDiaryRepository->update($data, $id);
                    if (count($request->attachments) > 0) {
                        $filesRespository->deleteModulFile('App\Models\WorkDiary', $work->id);
                        foreach ($request->attachments as $file) {
                            $this->filePathParts($file, $work, $request->building_id, $filesRespository);
                        }
                    }
                    return $this->responseSuccess([], 'Chỉnh sửa công việc thành công');
                }
                return $this->responseError(['Id công việc không đúng'], 204);
            }
            return $this->responseError(['Id công việc không đúng'], 204);
        }
        return $this->responseError(['Không lấy được thông tin User với building truyền vào'], self::LOGIN_FAIL);
    }
}
