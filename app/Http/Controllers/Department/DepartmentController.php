<?php

namespace App\Http\Controllers\Department;

use App\Http\Controllers\BuildingController;
use App\Http\Requests\Department\CreateDepartmentRequest;
use App\Http\Requests\Department\EditDepartmentRequest;
use App\Models\V3\Task;
use App\Repositories\Permissions\GroupsPermissionRepository;
use App\Repositories\Permissions\ModuleRepository;
use App\Repositories\PublicUsers\PublicUsersProfileRespository;
use App\Repositories\Department\DepartmentRepository;
use App\Repositories\Department\DepartmentStaffRepository;
use App\Repositories\Permissions\GroupPermissionRepository;
use App\Repositories\Permissions\PermissionsRepository;
use App\Repositories\Permissions\UserPermissionRepository;
use Illuminate\Support\FacadesAuth;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Log;
use App\Commons\Helper;
use App\Helpers\dBug;
use App\Models\Department\Department;
use Illuminate\Support\Facades\Auth;
use ZipArchive;

class DepartmentController extends BuildingController
{
    protected $departmentRepository;
    protected $departmentStaffRepository;
    protected $groupPermissionRepository;

    public function __construct(
        DepartmentRepository $departmentRepository,
        Request $request,
        DepartmentStaffRepository $departmentStaffRepository,
        GroupPermissionRepository $groupPermissionRepository
    )
    {
        //$this->middleware('auth', ['except' => []]);
        //$this->middleware('route_permision');
        $this->departmentRepository = $departmentRepository;
        $this->departmentStaffRepository = $departmentStaffRepository;
        $this->groupPermissionRepository = $groupPermissionRepository;
        Carbon::setLocale('vi');
        parent::__construct($request);
    }

    public function index(Request $request)
    {
        $data['meta_title'] = 'Quản lý bộ phận';
        $data['filter'] = $request->all();
        $data['departments'] = $this->departmentRepository->myPaginate($data['filter'], $this->building_active_id);
        $data['active_building'] = $this->building_active_id;
        return view('department.index', $data);
    }

    public function store(CreateDepartmentRequest $request)
    {
        $this->departmentRepository->create($request->except(['_token']));
        $responseData = [
            'success' => true,
            'message' => 'Thêm bộ phận thành công!'
        ];

        return response()->json($responseData);
    }

    public function edit(Request $request)
    {
        $department = $this->departmentRepository->findDepartment($request->id);
        return view('department.modal.edit_department', compact('department'));
    }

    public function update(EditDepartmentRequest $request)
    {
        $request->except(['_token', 'id']);
        $input  = $request->all();
        $input['type_manager'] = $request->get('type_manager',0);
        $this->departmentRepository->update($input, $request->id);
        $responseData = [
            'success' => true,
            'message' => 'Cập nhật bộ phận thành công!'
        ];

        return response()->json($responseData);
    }

    public function show(
        $id,
        PublicUsersProfileRespository $userInforRepository,
        UserPermissionRepository $permissionRepository,
        GroupsPermissionRepository $groupsPermission,
        ModuleRepository $moduleRepository
    )
    {
        $data['meta_title'] = 'Chi tiết bộ phận';
        $data['groups_ids'] = [];
        $department = $this->departmentRepository->findDepartment($id);
        $data['department'] = $department;
        $data['staff'] = $this->departmentStaffRepository->staffByDepartment($id);
        // $staff = $this->departmentStaffRepository->getAllPublicUserStaff();
        $data['employee'] = $userInforRepository->getStaffActive($data['staff'], $this->building_active_id);
        $data['data'] = $moduleRepository->all();
        $data['id'] = $id;
        $data['active_module'] = Cookie::get('active_module_permission', $moduleRepository->first()->id);
        return view('department.show', $data);
    }
    public function show1(
        $id,
        PublicUsersProfileRespository $userInforRepository,
        UserPermissionRepository $permissionRepository,
        GroupsPermissionRepository $groupsPermission,
        ModuleRepository $moduleRepository
    )
    {
        $data['meta_title'] = 'Chi tiết bộ phận';
        $data['groups_ids'] = [];
        $department = $this->departmentRepository->findDepartment($id);
        $data['department'] = $department;
        $data['staff'] = $this->departmentStaffRepository->staffByDepartment($id);
        // $staff = $this->departmentStaffRepository->getAllPublicUserStaff();
        $data['employee'] = $userInforRepository->getStaffActive1($data['staff'], $this->building_active_id);
        $data['data'] = $moduleRepository->all();
        $data['id'] = $id;
        $data['active_module'] = Cookie::get('active_module_permission', $moduleRepository->first()->id);
        return view('department.show', $data);
    }

    public function destroy($id)
    {
        $task = Task::where('department_id','like','%'.$id.'%')->where(function ($query){
            $query->where('status','<>',0)->where('status','<>',1);
        })->first();
        if($task){
            $dataResponse = [
                'success' => false,
                'message' => 'Bộ phận đang liên quan tới phần giao việc.Vui lòng bàn giao công việc trước khi xóa bộ phận'
            ];
            return response()->json($dataResponse);
        }
        $this->departmentRepository->findDepartment($id)->delete();
        $dataResponse = [
            'success' => true,
            'message' => 'Xóa bộ phận thành công!'
        ];
        return response()->json($dataResponse);
    }

    public function changeStatus(Request $request)
    {
        if($request->status == 0){
            $task = Task::where('department_id','like','%'.$request->id.'%')->where(function ($query){
                $query->where('status','<>',0)->where('status','<>',1);
            })->first();
            if($task){
                $dataResponse = [
                    'success' => false,
                    'message' => 'Bộ phận đang liên quan tới phần giao việc.Vui lòng bàn giao công việc trước khi thay đổi trạng thái'
                ];
                return response()->json($dataResponse);
            }
        }

        $this->departmentRepository->update($request->except('id'), $request->id);
        $dataResponse = [
            'success' => true,
            'message' => 'Cập nhật trạng thái thành công!'
        ];
        return response()->json($dataResponse);
    }
    public function changeStatusApp(Request $request)
    {
        $this->departmentRepository->update($request->except('id'), $request->id);
        $dataResponse = [
            'success' => true,
            'message' => 'Cập nhật trạng thái đánh giá thành công!'
        ];
        return response()->json($dataResponse);
    }

    public function addStaff(Request $request, $id)
    {
        $request->validate([
            'pub_user_ids' => 'required'
        ]);
        $data = $request->except(['_token']);
        try {
            $this->departmentStaffRepository->addStaffDepartment($data, $id);
            return response()->json([
                'success' => true,
                'message' => 'Thêm nhân viên vào bộ phận thành công'
            ]);
        } catch (\Exception $e) {
            Log::info($e->getMessage());
            return response()->json([
                'success' => false,
            ]);
        }

    }

    public function destroyStaff($id)
    {
        $this->departmentStaffRepository->findStaff($id)->delete();
        $dataResponse = [
            'success' => true,
            'message' => 'Xóa nhân viên khỏi phòng ban thành công!'
        ];
        return response()->json($dataResponse);
    }

    public function headStaff(Request $request)
    {
        $this->departmentStaffRepository->updateHeadDepartment($request);
        $staff = $this->departmentStaffRepository->findStaff($request->id);
        $this->groupPermissionRepository->updateHeadGroup($staff);
        return response()->json([
            'success' => true,
            'message' => 'Đặt trưởng phòng thành công'
        ]);
    }
    public function headBuilding(Request $request)
    {
        $this->departmentStaffRepository->updateHeadBuilding($request);
        return response()->json([
            'success' => true,
            'message' => 'Đặt trưởng ban quản lý thành công'
        ]);
    }
    public function changeStaff(Request $request)
    {
        $this->departmentStaffRepository->updateChangeStaff($request);
        return response()->json([
            'success' => true,
            'message' => 'Đặt nhân viên thành công'
        ]);
    }

    public function createOrUpdatePermission(
        Request $request,
        UserPermissionRepository $permissionRepository,
        ModuleRepository $moduleRepository,GroupsPermissionRepository $groupsPermission
    )
    {
//        try {
//        dd($request->all());
            $department = $this->departmentRepository->findDepartment($request->departmentID);

            $data['permissionUser'] = $permissionRepository->findPermission(auth()->user()->id);
            $permissionsGroup = $department->permissions;
            if ($permissionsGroup) {
                $permission_ids = unserialize($permissionsGroup->permission_ids);
            } else {
                $permission_ids = [];
            }
          /*  $module = $moduleRepository->findMenu($request->module_id);
            $permissionModules = $module->permissions->pluck('id')->toArray();
            $permissionNotInModule = array_diff($permission_ids, $permissionModules);
            $permissionsGroup = array_merge($permissionNotInModule, $request->ids);*/
        $permissionsGroup =  $data['permissiongroups']=[];
            if($request->permission){
                foreach ($groupsPermission->getByIdsa($request->permission) as $item){
                    if($item->permission_ids){
                        $list_per= unserialize($item->permission_ids);
                    }else{
                        $list_per=[];
                    }

                    $data['permissiongroups'] = array_unique(array_merge($data['permissiongroups'],$list_per));
                }
            }
            $permissionsGroup = array_merge($permissionsGroup, $data['permissiongroups']);
//            dd($request->all(),$data['permissiongroups']);
            if (is_null($request->pub_group_id)) {
                $pub_group_id = $this->groupPermissionRepository->createGroupPermission($department, $permissionsGroup);
                $department->update([
                    'pub_group_id' => $pub_group_id
                ]);
            } else {
                $pub_group_id = $department->pub_group_id;
                $this->groupPermissionRepository->updateGroupPermisson($pub_group_id, $permissionsGroup);
            }
            $this->departmentRepository->update(['group_permission_ids'=>$request->permission?implode(',',$request->permission):null],$request->id);
            \Cache::store('redis')->put(env('REDIS_PREFIX') . $request->departmentID . '_DXMB_GROUP_PERMISION', null);
            return response()->json([
                'success' => true,
                'message' => 'Cập nhật quyền bộ phận thành công',
                'pub_group_id' => $pub_group_id
            ]);
//        } catch (\Exception $e) {
//            return response()->json([
//                'success' => false,
//                'message' => 'Có lỗi xảy ra!!! Không thể cập nhật quyền cho bộ phận này!'
//            ]);
//        }
    }

    public function updatePermissionUser($staffId, ModuleRepository $moduleRepository,GroupsPermissionRepository $groupsPermission,UserPermissionRepository $permissionRepository)
    {
        $data['data'] = $moduleRepository->all();
        $data['meta_title'] = 'Cập nhật quyền user trong bộ phận';
        $data['staff'] = $this->departmentStaffRepository->findStaff($staffId);
        if ($data['staff']->permission_deny) {
            $data['permission_deny'] = unserialize($data['staff']->permission_deny);
        } else {
            $data['permission_deny'] = [];
        }
        $data['info'] = $data['staff']->publicUser->infoWeb()->where('bdc_building_id', $data['staff']->department->bdc_building_id)->first();
        $permissionsGroup = $data['staff']->department->group_permission_ids;
        $permission_ids = [];
        foreach ($groupsPermission->getByIds($permissionsGroup) as $item){
            if ($item->permission_ids){
                $list_per= unserialize($item->permission_ids);
            }else{
                $list_per= [];
            }

            $permission_ids = array_unique(array_merge($permission_ids,$list_per));
        }
        $data['permission_ids'] = $permission_ids;
        $data['permission_check'] = array_diff($permission_ids, $data['permission_deny']);
        $Userinfo = $permissionRepository->getOne($data['staff']->pub_user_id);
        if($Userinfo->permissions){
            $data['permissionUser'] = unserialize($Userinfo->permissions);
        }else{
            $data['permissionUser'] = [26];
        }
        $data['active_module'] = Cookie::get('active_module_permission_user', $moduleRepository->first()->id);

        return view('department.user_permission', $data);
    }

    public function updatePermissionDeny($staffId, Request $request, ModuleRepository $moduleRepository)
    {
        try {
            $staff = $this->departmentStaffRepository->findStaff($staffId);
            $permissionsGroup = $staff->department->permissions;
            if ($permissionsGroup) {
                $permission_ids = unserialize($permissionsGroup->permission_ids);
            } else {
                $permission_ids = [];
            }
            $module = $moduleRepository->findMenu($request->module_id);
            $permissionModules = $module->permissions->pluck('id')->toArray();
            $permissionNotInModule = array_diff($permission_ids, $permissionModules);
            $permissionsGroup = array_merge($permissionNotInModule, $request->ids);
            $permissionDeny = array_diff($permission_ids, $permissionsGroup);
            $staff->update([
                'permission_deny' => serialize($permissionDeny)
            ]);
            Cookie::queue('active_module_permission_user', $request->module_id , 60 * 24 * 30);
            return response()->json([
                'success' => true,
                'message' => 'Cập nhật quyền cho nhân viên thành công',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Cập nhật quyền cho nhân viên thất bại',
            ]);
        }

    }
    public function action(Request $request)
    {
        $method = $request->input('method', '');
        if($method == 'qrcode' && $request->ids){
            $directory = storage_path('qrcode');
            if (!is_dir($directory)) {
                mkdir(storage_path().'/qrcode');
            }
            $zipname = storage_path().'/qrcode/file_qrcode_bo_phan.zip';
            $zip = new ZipArchive;
            $zip->open($zipname, ZipArchive::CREATE);
            $path_list = [];
            foreach ($request->ids as $key => $value) {
                $_department =  Department::where('id',$value)->where('status',1)->first();
                $base_url='https://bdcadmin.dxmb.vn/audit-service?department_id='.$_department->id.'_'.$_department->bdc_building_id;
                $_client = new \GuzzleHttp\Client();
                $responseTask = $_client->request('Get','https://api.qrserver.com/v1/create-qr-code/?size=250x250&data='.$base_url, ['stream' => true]);
        
                $body = $responseTask->getBody()->getContents();
                $base64 = base64_encode($body);
                $mime = "image/png";
                $img = ('data:' . $mime . ';base64,' . $base64);
                $file = file_get_contents($img);
                $path = storage_path('qrcode/'.$value.'_'.@$_department->name.'.png');
                $path_list[] = storage_path('qrcode/'.$value.'_'.@$_department->name.'.png');
                $file = file_put_contents($path,$file);
                $zip->addFile(storage_path().'/qrcode/'.$value.'_'.@$_department->name.'.png',$value.'_'.@$_department->name.'.png');
                
            }
            $zip->close();
            foreach ($path_list as $key => $value) {
                unlink($value);
            }
            $file     = storage_path().'/qrcode/file_qrcode_bo_phan.zip';
            return response()->download($file)->deleteFileAfterSend(true);
            
        }
        return redirect()->back();
       
    }
    public function updateGroupPermission(Request $request,GroupsPermissionRepository $groupsPermission)
    {
//        dd($request->all());
        if($request->id){
            $data['permissionUser']=[];
            if($request->permission){
                foreach ($groupsPermission->getByIdsa($request->permission) as $item){
                    if($item->permission_ids){
                        $list_per= unserialize($item->permission_ids);
                    }else{
                        $list_per=[];
                    }

                    $data['permissionUser'] = array_unique(array_merge($data['permissionUser'],$list_per));
                }
            }
            $this->departmentRepository->update(['group_permission_ids'=>$request->permission?implode(',',$request->permission):null],$request->id);
            \Cache::store('redis')->set( env('REDIS_PREFIX') .$request->id.'_DXMB_GROUP_PERMISION' , null );
        }

    }
    public function ajaxGetSelectGroup(Request $request,GroupsPermissionRepository $groupsPermission)
    {
        if ($request->keyword) {
            return response()->json($groupsPermission->searchByAll(['select'=>['id','name']],$request));
        }
        return response()->json($groupsPermission->searchByAll(['select'=>['id','name']]));
    }
}
