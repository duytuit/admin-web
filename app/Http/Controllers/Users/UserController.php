<?php

namespace App\Http\Controllers\Users;

// use App\Http\Controllers\Backend\Controller;

use App\Commons\Api;
use App\Helpers\dBug;
use App\Http\Controllers\Controller;
use App\Http\Requests\PublicUser\CreateUserRequest;
use App\Models\PublicUser\UserCategory;
use App\Models\PublicUser\UserInfo;
use App\Repositories\Permissions\GroupsPermissionRepository;
use App\Repositories\Permissions\ModuleRepository;
use App\Repositories\Permissions\PermissionsRepository;
use App\Repositories\Permissions\PermissionTypeRepository;
use App\Repositories\PublicUsers\PublicUsersProfileRespository;
use App\Repositories\PublicUsers\PublicUsersRespository;
use App\Services\ServiceSendMail;
use App\Services\ServiceSendMailV2;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Validator;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\DB;
use App\Models\PublicUser\Users;
use App\Repositories\Permissions\UserPermissionRepository;
use App\Http\Controllers\BuildingController;
use App\Models\Building\Building as BuildingBuilding;
use App\Models\Campain;
use App\Models\Department\Department;
use App\Models\DepartmentStaff\DepartmentStaff;
use App\Models\SentStatus;
use App\Models\V3\Building;
use App\Repositories\Building\BuildingRepository;
use App\Repositories\Building\CompanyRepository;
use App\Repositories\Building\CompanyStaffRepository;
use App\Util\Debug\Log;
use GuzzleHttp\Promise\Promise;
use Http\Promise\Promise as PromisePromise;
use Illuminate\Support\Facades\File;
use ZipArchive;

use function GuzzleHttp\json_encode;

class UserController extends BuildingController
{
    use ApiResponse;

    private $model;
    private $modelUsers;
    private $category;
    protected $companyStaffRepository;
    protected $companyRepository;
    protected $buildingRepository;
    const FORGOT = 3;
    const NEW_USER = 100;
    const NEW_PROFILE = 99;

    // private $building_active_id;
    // private $buildings;
    // private $app_id;

    /**
     * Khởi tạo
     */
    public function __construct(UserInfo $model, UserCategory $category, PublicUsersRespository $modelUsers, Request $request, CompanyStaffRepository $companyStaffRepository, CompanyRepository $companyRepository, BuildingRepository $buildingRepository)
    {
        $this->model = $model;
        $this->modelUsers = $modelUsers;
        $this->category = $category;
        $this->companyStaffRepository = $companyStaffRepository;
        $this->companyRepository = $companyRepository;
        $this->buildingRepository = $buildingRepository;
        Carbon::setLocale('vi');
        parent::__construct($request);
    }


    public function attributes()
    {
        return [
            'password' => 'Mật khẩu',
            'password_confirm' => 'Mật khẩu xác nhận',
        ];
    }

    /**
     * Danh sách bản ghi
     *
     * @param Request $request
     * @return Response
     */
    public function index(Request $request)
    {
        $data['meta_title'] = "QL User";
        // Phân trang
        $data['per_page'] = Cookie::get('per_page', 20);
        //lay ra app id tuong ung voi domain

        // Tìm kiếm nâng cao
        $data['keyword'] = $request->input('keyword', '');
        $data['group_ids'] = $request->input('group_ids', '');
        $data['status'] = $request->input('status', null);

        $query = $this->model
            ->where('type', \App\Models\PublicUser\Users::USER_WEB)
            ->where(function ($query) use ($data) {
                $query->where('display_name', 'like', '%' . $data['keyword'] . '%')
                    ->orWhere('email', 'like', '%' . $data['keyword'] . '%')
                    ->orWhere('address', 'like', '%' . $data['keyword'] . '%')
                    ->orWhere('cmt', 'like', '%' . $data['keyword'] . '%')
                    ->orWhere('phone', 'like', '%' . $data['keyword'] . '%')
                    ->orWhere('staff_code', $data['keyword']);
            })
            ->where('bdc_building_id', $this->building_active_id)
            ->where('app_id', $this->app_id);

        if ($data['status'] != null) {
            $query = $query->where('status', $data['status']);
        }
        if ($data['group_ids']) {
            $department_staff = DepartmentStaff::where('bdc_department_id', $data['group_ids'])->pluck('pub_user_id')->toArray();
            if ($department_staff) {
                $query = $query->whereIn('pub_user_id', $department_staff);
            }

        }
        $data['users'] = $query->orderBy('status','desc')->paginate($data['per_page']);
        // Phòng ban
        $data['groups'] = Department::whereHas('department_staffs')->where('bdc_building_id', $this->building_active_id)->get();
        return view('users.index', $data);
    }

    /**
     * Danh sách bản ghi
     *
     * @param Request $request
     * @return Response
     */
    public function index_user(Request $request)
    {
        $data['meta_title'] = "QL User";
        // Phân trang
        $data['per_page'] = Cookie::get('per_page', 20);
        //lay ra app id tuong ung voi domain

        // Tìm kiếm nâng cao
        $data['keyword'] = $request->input('keyword', '');
        $data['group_ids'] = $request->input('group_ids', '');
        $data['status'] = $request->input('status', null);
        $data['keyword_user'] = $request->input('keyword_user', '');

        $query = $this->model
            ->where(function ($query) use ($data) {
                $query->where('display_name', 'like', '%' . $data['keyword'] . '%')
                    ->orWhere('email', 'like', '%' . $data['keyword'] . '%')
                    ->orWhere('address', 'like', '%' . $data['keyword'] . '%')
                    ->orWhere('cmt', 'like', '%' . $data['keyword'] . '%')
                    ->orWhere('phone', 'like', '%' . $data['keyword'] . '%')
                    ->orWhere('staff_code', $data['keyword']);
            })
            ->where('app_id', $this->app_id);

        if ($data['status']) {
            $query = $query->where('status', $data['status']);
        }
        $query_users = Users::where(function ($query) use ($data) {
            $query->where('email', 'like', '%' . $data['keyword_user'] . '%')
                ->orWhere('id', 'like', '%' . $data['keyword_user'] . '%')
                ->orWhere('mobile', 'like', '%' . $data['keyword_user'] . '%');
        });
        $data['users'] = $query_users->withTrashed()->orderBy('updated_at', 'desc')->paginate($data['per_page']);

        $data['profiles'] = $query->withTrashed()->orderBy('updated_at', 'desc')->paginate($data['per_page']);

        return view('users.index_info', $data);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function edit($id = 0,
                         UserPermissionRepository $permissionRepository,
                         PermissionsRepository $permissions,
                         Request $request,
                         Users $users,
                         GroupsPermissionRepository $groupsPermission,
                         ModuleRepository $moduleRepository,
                         PermissionTypeRepository $type)
    {
        $data['meta_title'] = 'Cập nhập tài khoản';
        $data['id'] = $id;
        $data['buildings'] = \App\Models\Building\Building::where('status',1)->select(['id','name','company_id'])->orderBy('company_id')->get();
        $data['active_module'] = Cookie::get('active_module_permission', $moduleRepository->first()->id);
        $data['data'] = $moduleRepository->all();
        $data['Userinfo'] = $permissionRepository->getOne($id);
        $data['permissionUser'] = [];
        if ($data['Userinfo']) {
            $data['listGroupsPermission'] = $groupsPermission->getByIds($data['Userinfo']->group_permission_ids);
            foreach ($groupsPermission->getByIds($data['Userinfo']->group_permission_ids) as $item) {
                if ($item->permission_ids) {
                    $list_per = unserialize($item->permission_ids);
                } else {
                    $list_per = [];
                }

                $data['permissionUser'] = array_unique(array_merge($data['permissionUser'], $list_per));
            }
        }
        $data['groupSelect'] = [];
        $data['BuildingSelect'] = [];

        if (isset($data['Userinfo']->group_permission_ids)) {
            $data['groupSelect'] = explode(',', $data['Userinfo']->group_permission_ids);
        }

        $data['listGroupsPermissions'] = $groupsPermission->getAll($this->building_active_id);
        $data['listBuilding'] = $this->getAllBuilding();

//        dd($data['listGroupsPermission']);
        $data['meta_title'] = 'Phân quyền người dùng';

        $data['user'] = $users->with('BDCprofile')->find($id);
        $data['active_bulding'] = $this->building_active_id;
        $data['id'] = $id;
        $data['types'] = $type->all(['id', 'name', 'description']);
        return view('users.edit', $data);
    }

    public function update(Request $request, $id)
    {
        $data['meta_title'] = 'Cập nhập tài khoản';
        try {
            $user = Users::find($id);

            $request->request->add(['email' =>  $request->email]);
            $request->request->add(['phone' => $request->mobile]);
            $request->request->add(['customer_code' => $request->staff_code]);
            $request->request->add(['full_name' => $request->display_name]);
            $request->request->add(['building_id' => $this->building_active_id]);

            $result_api = Api::POST('admin/auth/updateUserAdmin',$request->all());
            if(\Auth::user()->isadmin ==1){
                $building_ids = $request->building_ids;
                UserInfo::where('pub_user_id',$user->id)->where('type',2)->update([
                    'status' => 0,
                    'phone' =>  $request->mobile,
                    'email' => $request->email,
                    'cmt' => $request->cmt,
                    'display_name' => $request->display_name,
                    'cmt_address' => $request->cmt_address,
                    'staff_code' => $request->staff_code
                ]);
                foreach ($building_ids as $building_id) {
                    $user_info = UserInfo::where('pub_user_id',$user->id)->where('type',2)->where('bdc_building_id',$building_id)->first();
                    if($user_info){
                        $user_info->status =1;
                        $user_info->save();
                    }else{
                        UserInfo::create([
                            'pub_user_id' => $user->id,
                            'display_name' => $request->display_name ?? @$user->profileAll->display_name,
                            'phone' => $request->mobile,
                            'email' => $request->email,
                            'app_id' => @$app_id,
                            'bdc_building_id' => $building_id,
                            'type' => Users::USER_WEB,
                            'status' => 1
                        ]);
                    }
                }
                $list_user_info = UserInfo::where('pub_user_id',$user->id)->where('type',2)->where('status',0)->get();
                foreach ($list_user_info as $item) {
                    if ($this->getBuildingIdActive($item->pub_user_id)) {
                        $this->delBuildingIdActive($item->pub_user_id);
                    }
                }
            }else{
               UserInfo::where('pub_user_id',$user->id)->where('type',2)->update([
                   'phone' =>  $request->mobile,
                   'email' => $request->email,
                   'cmt' => $request->cmt,
                   'display_name' => $request->display_name,
                   'cmt_address' => $request->cmt_address,
                   'staff_code' => $request->staff_code
                ]);
            }
            return redirect()->route('admin.users.manageUser')->with('success',$result_api->mess);
        } catch (\Exception $e) {
            throw new \Exception("update error: " . $e->getMessage(), 1);
        }
    }

    public function restoreUser(Request $request, $id)
    {
        $data['meta_title'] = 'Cập nhập tài khoản';
        try {
            $user = Users::withTrashed()->find($id)->restore();
            return redirect()->route('admin.users.manageUserApp')->with('success', "cập nhập tài khoản thành công!");
        } catch (\Exception $e) {
            throw new \Exception("update error: " . $e->getMessage(), 1);
        }
    }

    public function create()
    {
        $data['meta_title'] = "Tạo mới người dùng";
        $data['buildings'] = \App\Models\Building\Building::where('status',1)->select(['id','name','company_id'])->orderBy('company_id')->get();
        return view('users.create', $data);
    }
    public function create1()
    {
        $data['meta_title'] = "Tạo mới người dùng";
        $data['buildings'] = \App\Models\Building\Building::where('status',1)->select(['id','name','company_id'])->orderBy('company_id')->get();
        return view('users.create1', $data);
    }

    public function action(Request $request)
    {
        $method = $request->input('method', '');
        if ($method == 'qrcode' && $request->ids) {
            $directory = storage_path() . '/qrcode';
            if (!is_dir($directory)) {
                mkdir(storage_path() . '/qrcode');
            }
            $zipname = storage_path() . '/qrcode/file_qrcode_nhan_vien.zip';
            $zip = new ZipArchive;
            $zip->open($zipname, ZipArchive::CREATE);
            $path_list = [];
            foreach ($request->ids as $key => $value) {
                $user_depart = DepartmentStaff::where('pub_user_id', explode('_', $value)[0])->get();
                if (count($user_depart) > 0) {
                    foreach ($user_depart as $key_1 => $value_1) {
                        $base_url = 'https://bdcadmin.dxmb.vn/audit-service?employee_id=' . $value_1->pub_user_id . '_' . $value_1->bdc_department_id;
                        $_client = new \GuzzleHttp\Client();
                        $responseTask = $_client->request('Get', 'https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=' . $base_url, ['stream' => true]);
                        $body = $responseTask->getBody()->getContents();
                        $base64 = base64_encode($body);
                        $mime = "image/png";
                        $img = ('data:' . $mime . ';base64,' . $base64);
                        $file = file_get_contents($img);
                        $path = storage_path('qrcode/' . $value . '_' . @$value_1->department->name . '.png');
                        $path_list[] = storage_path('qrcode/' . $value . '_' . @$value_1->department->name . '.png');
                        $file = file_put_contents($path, $file);
                        $zip->addFile(storage_path() . '/qrcode/' . $value . '_' . @$value_1->department->name . '.png', $value . '_' . @$value_1->department->name . '.png');
                    }
                } else {
                    $base_url = 'https://bdcadmin.dxmb.vn/audit-service?employee_id=' . explode('_', $value)[0];
                    $_client = new \GuzzleHttp\Client();
                    $responseTask = $_client->request('Get', 'https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=' . $base_url, ['stream' => true]);

                    $body = $responseTask->getBody()->getContents();
                    $base64 = base64_encode($body);
                    $mime = "image/png";
                    $img = ('data:' . $mime . ';base64,' . $base64);
                    $file = file_get_contents($img);
                    $path = storage_path('qrcode/' . $value . '.png');
                    $path_list[] = storage_path('qrcode/' . $value . '.png');
                    $file = file_put_contents($path, $file);
                    $zip->addFile(storage_path() . '/qrcode/' . $value . '.png', $value . '.png');
                }

            }
            $zip->close();
            foreach ($path_list as $key => $value) {
                unlink($value);
            }
            $file = storage_path('qrcode/file_qrcode_nhan_vien.zip');
            return response()->download($file)->deleteFileAfterSend(true);

        } else {
            return $this->modelUsers->action($request, $this->building_active_id);
        }

    }

    function getToken($length)
    {
        $token = "";
        $codeAlphabet = "0123456789";
        $max = strlen($codeAlphabet); // edited

        for ($i = 0; $i < $length; $i++) {
            $token .= $codeAlphabet[random_int(0, $max - 1)];
        }

        return $token;
    }

    public function store(Request $request, Users $user)
    {
        $password = $this->getToken(6);
        $validator = $request->validate([
            'email' => 'required|max:254'
        ]);
        $userupdatephone = Users::where('email', $request->email)->update([
            'mobile' => $request->phone,
        ]);
        try {
            $type = self::NEW_PROFILE;
            // kiem tra user dang nhap co email hoad sdt neu chua ton tai thi tao moi
            $hasUser = $user->where('email', $request->email)->first();
            if (!$hasUser) {
                $hasUser = $user->create(['email' => $request->email, 'mobile' => $request->phone, 'password' => bcrypt($password)]);
                $type = self::NEW_USER;
                $input = [
                    'phone' => $request->phone,
                    'pword' => $password,
                    'full_name' => $request->display_name,
                    'building_id' => $this->building_active_id,
                    'gender' => 1,
                    'email' => $request->email ? $request->email : $request->email_contact,
                    'cmt_number' => $request->cmt,
                    'cmt_address' => $request->cmt_address,
                ];
                $result_add_apartment = Api::POST('admin/addUserAdmin', $input);
                if ($result_add_apartment->status == true) {
                    Log::info("check_insert_user", '1_' . json_encode($result_add_apartment));
                } else {
                    return redirect()->back()->withErrors([$result_add_apartment->mess]);
                }
            }
            // lấy công ty theo tòa nhà
            $building = BuildingBuilding::find($this->building_active_id);

            $staff_company = $this->companyStaffRepository->getStaffByPublicId($hasUser->id, $building->company_id);
            if (!$staff_company) {
                $this->companyStaffRepository->create([
                    'pub_user_id' => $hasUser->id,
                    'bdc_company_id' => $building->company_id,
                    'type' => 0,
                    'name' => $request->display_name,
                    'email' => $request->email,
                    'phone' => $request->phone,
                    'active' => 1
                ]);
            }
            // kiem tra xem profile tren app nay neu chua co thi tao moi.
            $hasProfile = $this->model->where('pub_user_id', $hasUser->id)->where('bdc_building_id', $this->building_active_id)->where('type', Users::USER_WEB)->first();
            if ($hasProfile) {
                return redirect()->back()->with('warning','Người dùng "' . $request->email . '" đã tồn tại trên hệ thống.');
            }

            $this->createUserInfo($this->model, $hasUser, $this->app_id, $request);

            if ($type == self::NEW_PROFILE) {

                $message = 'Người dùng "' . $request->email . '" đã tồn tại trên hệ thống, đã được tạo mới profile';
                $this->sendMail($request->email, $password, $type, 'Đã có tài khoản trên hệ thống và tạo mới profile');
            } else {
                $message = "Tạo mới người dùng thành công.";
                $this->sendMail($hasUser->email, $password, $type, 'Tài khoản đã được tạo mới');
            }
        } catch (\Exception $e) {
            throw new \Exception("register ERROR: " . $e->getMessage(), 1);
        }
        if($request->data_type == 'V2')
        {
            return redirect()->route('admin.company.listemp.listemp')->with('success', $message);
        }
        return redirect()->route('admin.users.manageUser')->with('success', $message);
    }

    private function createUserInfo($info, $new_user, $app_id, $request)
    {
        if(\Auth::user()->isadmin ==1){
            $building_ids = $request->building_ids;
            if(count($building_ids) >0){
                foreach ($building_ids as $building_id) {
                    $info->create([
                        'pub_user_id' => $new_user->id,
                        'display_name' => $request->display_name ?? $new_user->email,
                        'phone' => $request->phone,
                        'cmt' => $request->cmt,
                        'cmt_address' => $request->cmt_address,
                        'email' => $new_user->email,
                        'app_id' => $app_id,
                        'bdc_building_id' => $building_id,
                        'type' => Users::USER_WEB,
                        'status' => 1,
                        'staff_code' => $request->code_name,
                        'data_type'=>$request->data_type,
                    ]);
                }
                return true;
            }else{
                return false;
            }
        }else{
            $info->create([
                'pub_user_id' => $new_user->id,
                'display_name' => $request->display_name ?? $new_user->email,
                'phone' => $request->phone,
                'cmt' => $request->cmt,
                'cmt_address' => $request->cmt_address,
                'email' => $new_user->email,
                'app_id' => $app_id,
                'bdc_building_id' => $this->building_active_id,
                'type' => Users::USER_WEB,
                'status' => 1,
                'staff_code' => $request->code_name,
                'data_type'=>$request->data_type,
            ]);
            return true;
        }


    }


    public function changeStatus(Request $request)
    {
        $data = $request->except('id');
        $userInfo = $this->model->find($request->id);
        if ($userInfo) {
            $userInfo->update($data);
            if ($this->getBuildingIdActive($userInfo->pub_user_id)) {
                $this->delBuildingIdActive($userInfo->pub_user_id);
            }
        }
        $dataResponse = [
            'success' => true,
            'message' => 'Cập nhật trạng thái thành công!'
        ];
        return response()->json($dataResponse);
    }

    public function destroyUser(Request $request)
    {
        //Users::where('id', $request->id)->delete();
        $user = $this->model->where(['bdc_building_id' => $this->building_active_id, 'pub_user_id' => $request->id, 'type' => Users::USER_WEB])->first();
        if ($user) {
            if ($this->getBuildingIdActive($user->pub_user_id)) {
                $this->delBuildingIdActive($user->pub_user_id);
            }
            $user->delete();
        }
        return redirect()->route('admin.users.manageUser')->with('success', "Xóa tài khoản thành công!");
    }

    public function destroyUserApp(Request $request)
    {
        $userProfile = $this->model->where('pub_user_id', $request->id)->first();
        if ($userProfile) {
            $userProfile->delete();
        }
        $user = Users::find($request->id);
        if ($user) $user->delete();
        return redirect()->route('admin.users.manageUserApp')->with('success', "Xóa tài khoản thành công!");
    }

    public function destroyProfile(Request $request)
    {
        $user_info = $this->model->find($request->id);
        if ($user_info) $user_info->delete();
        return redirect()->route('admin.users.manageUserApp')->with('success', "Xóa profile thành công!");
    }


    public function listPermission(
        $id,
        UserPermissionRepository $permissionRepository,
        PermissionsRepository $permissions,
        Request $request,
        Users $users,
        GroupsPermissionRepository $groupsPermission,
        ModuleRepository $moduleRepository,
        PermissionTypeRepository $type
    )
    {
        $data['active_module'] = Cookie::get('active_module_permission', $moduleRepository->first()->id);
        $data['data'] = $moduleRepository->all();
        $data['per_page'] = $request->per_page ?? 10;
//        $data['permissionUser'] = $permissionRepository->findPermission($id);
//        dd($data['permissionUser']);
        $data['Userinfo'] = $permissionRepository->getOne($id);
        $data['permissionUser'] = [];
        if ($data['Userinfo']) {
            $data['listGroupsPermission'] = $groupsPermission->getByIds($data['Userinfo']->group_permission_ids);
            foreach ($groupsPermission->getByIds($data['Userinfo']->group_permission_ids) as $item) {
                if ($item->permission_ids) {
                    $list_per = unserialize($item->permission_ids);
                } else {
                    $list_per = [];
                }

                $data['permissionUser'] = array_unique(array_merge($data['permissionUser'], $list_per));
            }
        }
        $data['groupSelect'] = [];
        $data['BuildingSelect'] = [];

        if (isset($data['Userinfo']->group_permission_ids)) {
            $data['groupSelect'] = explode(',', $data['Userinfo']->group_permission_ids);
        }

        $data['listGroupsPermissions'] = $groupsPermission->getAll($this->building_active_id);
        $data['listBuilding'] = $this->getAllBuilding();

//        dd($data['listGroupsPermission']);
        $data['meta_title'] = 'Phân quyền người dùng';

        $data['user'] = $users->with('BDCprofile')->find($id);
        $data['active_bulding'] = $this->building_active_id;
        $data['id'] = $id;
        $data['types'] = $type->all(['id', 'name', 'description']);
        if (!isset($data['user'])) {
            return redirect()->away('/admin')->with(['warning' => 'không tìm thấy quyền!']);
        }
        return view('users.permission.index', $data);
    }

    public function updatePermission($id, Request $request, UserPermissionRepository $permissionRepository)
    {
        $permissionRepository->updatePermission($id, $request->ids, $request->module_id);
        Cookie::queue('active_module_permission', $request->module_id, 60 * 24 * 30);
        \Cache::store('redis')->set(env('REDIS_PREFIX') . $id . '_DXMB_USER_PERMISION', null);
        $dataResponse = [
            'success' => true,
            'message' => 'Cập nhật quyền cho user thành công!'
        ];
        return response()->json($dataResponse);
    }

    public function sendMail($email, $pass, $type = 99, $description)
    {
        $user = $this->modelUsers->getUserProfile($email);
        $total = ['email' => 1, 'app' => 0, 'sms' => 0];
        $campain = Campain::updateOrCreateCampain("Gửi mail cho: " . $email, config('typeCampain.RESIDENT'), null, $total, $this->building_active_id, 0, 0);


        $data = [
            'params' => [
                '@ten' => $user->profileAll->display_name ?? $email,
                '@pass' => $pass,
                '@ngay' => date('d/m/Y', time()),
                '@urlLogin' => url('/login'),
                '@mota' => $description,
                '@toanha' => $this->buildings[$this->building_active_id]
            ],
            'cc' => $email,
            'building_id' => $this->building_active_id,
            'type' => $type,
            'status' => 'new_user',
            'campain_id' => $campain->id
        ];

        try {
            ServiceSendMailV2::setItemForQueue($data);
            return;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function sendMailBills()
    {
        $aa = $this->sendMailBill();
        // dd($aa);
    }

    public function sendMailBill()
    {
        $total = ['email' => 1, 'app' => 0, 'sms' => 0];
        $campain = Campain::updateOrCreateCampain("Gửi mail cho: trungpn@dxmb.vn", config('typeCampain.BILL'), null, $total, 61, 0, 0);


        $data = [
            'params' => [
                '@tenkhachhang ' => 'Pham trung',
                '@tongtien' => '310000',
                '@chucanho' => '1103',
                '@ngay' => date('d/m/Y', time()),
            ],
            'cc' => 'trungpn@dxmb.vn',
            'building_id' => 61,
            'type' => 69,
            'status' => 'paid',
            'campain_id' => $campain->id
        ];
        try {
            ServiceSendMailV2::setItemForQueue($data);
            return;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function updateGroupPermission(Request $request, GroupsPermissionRepository $groupsPermission, UserPermissionRepository $userPermission)
    {
        if ($request->id) {
            $check = $userPermission->checkExit($request->id);
            if ($check <= 0) {
                $userPermission->create(['pub_user_id' => $request->id]);
            }
            $data['permissionUser'] = [];
            if ($request->permission) {
                foreach ($groupsPermission->getByIdsa($request->permission) as $item) {
                    if ($item->permission_ids) {
                        $list_per = unserialize($item->permission_ids);
                    } else {
                        $list_per = [];
                    }

                    $data['permissionUser'] = array_unique(array_merge($data['permissionUser'], $list_per));
                }
            }
            DB::table('pub_user_permissions')->where('pub_user_id', $request->id)->update(['group_permission_ids' => $request->permission ? implode(',', $request->permission) : null, 'permissions' => !empty($data['permissionUser']) ? serialize($data['permissionUser']) : serialize([26])]);
            \Cache::store('redis')->set(env('REDIS_PREFIX') . $request->id . '_DXMB_USER_PERMISION', null);
        }

    }

    public function ajaxGetSelectGroup(Request $request, GroupsPermissionRepository $groupsPermission)
    {
        if ($request->keyword) {
            return response()->json($groupsPermission->searchByAll(['select' => ['id', 'name']], $request));
        }
        return response()->json($groupsPermission->searchByAll(['select' => ['id', 'name']]));
    }

    public function ajaxChangeIsAdmin(Request $request)
    {
        $userIsAdmin = Users::where('id', $request->id)->update([
            'isadmin' => $request->isadmin,
        ]);
    }

    public function ResetPassUser(Request $request)
    {
        // $password = $this->getToken(6);
        // if($this->modelUsers->checkExit($request->email)){
        //     $update = $this->modelUsers->resetPass($request->email,$password);
        //     if($update){
        //         return $this->responseSuccess(['password'=>$password],' Mật khẩu reset là '.$password.' Xin vui lòng đăng nhập lại');
        //     }else{
        //          return $this->responseError('Lỗi đường truyền', 404);
        //     }
        // }
        $request->request->add(['building_id' => $this->building_active_id]);
        $request->request->add(['type_admin' => 1]);
        $request->request->add(['user_id' => (int)$request->user_id]);
        $result_reset = Api::POST('admin/resetUserPass', $request->all());

        if ($result_reset->status == true) {
            Log::info("check_insert_user", '3_' . json_encode($result_reset));
            return $this->responseSuccess(['password' => (int)$result_reset->data], ' Mật khẩu reset là ' . (string)$result_reset->data . ' Xin vui lòng đăng nhập lại...');
        } else {
            Log::info("check_insert_user", '4_' . json_encode($result_reset));
            return $this->responseError($result_reset->mess, 404);
        }
    }
}
