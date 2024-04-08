<?php

namespace App\Http\Controllers\Users\Api;

use App\Commons\Helper;
use App\Helpers\dBug;
use App\Http\Controllers\BuildingController;
use App\Http\Controllers\Controller;
use App\Models\Permissions\Module;
use App\Models\PublicUser\Users;
use App\Repositories\Department\DepartmentRepository;
use App\Repositories\Department\DepartmentStaffRepository;
use App\Repositories\Fcm\FcmRespository;
use App\Repositories\Permissions\ModuleRepository;
use App\Repositories\Permissions\UserPermissionRepository;
use App\Repositories\PublicUsers\PublicUsersProfileRespository;
use App\Repositories\PublicUsers\PublicUsersRespository;
use Hamcrest\Thingy;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use JWTAuth;
use phpDocumentor\Reflection\Types\This;
use Tymon\JWTAuth\Exceptions\JWTException;
use Validator;
use App\Models\PublicUser\UserInfo;
use App\Traits\ApiResponse;
use App\Services\AppConfig;
use App\Models\DepartmentStaff\DepartmentStaff;
use App\Models\PublicUser\V2\User;
use Carbon\Carbon;

class UserController extends BuildingController
{
    use ApiResponse;

    // const VALIDATE_FAIL = 10002;
    const DUPLICATE     = 19999;
    const LOGIN_FAIL    = 10000;
    private $model;
    private $modelFcm;
    private $modelUserinfo;
    private $modelModule;
    private $modelUsesPerm;
    private $modelDepartment;
    private $modelDepartmentStaff;
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct(PublicUsersRespository $model,PublicUsersProfileRespository $modelUserinfo,ModuleRepository $modelModule,UserPermissionRepository $modelUsesPerm,DepartmentRepository $modelDepartment,DepartmentStaffRepository $modelDepartmentStaff,FcmRespository $modelFcm,Request $request)
    {
        $this->model = $model;
        $this->modelFcm = $modelFcm;
        $this->modelModule = $modelModule;
        $this->modelUserinfo = $modelUserinfo;
        $this->modelUsesPerm = $modelUsesPerm;
        $this->modelDepartment = $modelDepartment;
        $this->modelDepartmentStaff = $modelDepartmentStaff;
        $this->middleware('auth:public_user', ['except' => ['login', 'register']]);
        parent::__construct($request);
    }

    public function register(Request $request, UserInfo $info)
    {

    }

    private function createUserInfo($info, $new_user, $app_id)
    {
        return $info->create([
            'pub_user_id'=>$new_user->id,
            'display_name'=>$new_user->email,
            'email'=>$new_user->email,
            'app_id'=>$app_id,
            'type'=>Users::USER_WEB
        ]);
    }

    public function getRegency(Request $request)
    {
        $info = \Auth::guard('public_user')->user();
        $user = $this->modelUserinfo->getInfoByPubuserId($info->id,$request->building_id);

        $get_employee =  DepartmentStaff::where('pub_user_id',$user->pub_user_id)->whereHas('department', function ($query) use ($request) {
            if (isset($request->building_id)) {
                $query->where('bdc_building_id', '=', $request->building_id);
            }
        })->first();
        $info_user = [
            'user_id' => $user->pub_user_id,
            'display_name' => $user->display_name,
            'email' => $user->email,
            'regency' => DepartmentStaff::REGENCY[isset($get_employee) ? $get_employee->type :DepartmentStaff::NOT_REGENCY]
        ];
        return response()->json([
            'success' => true,
            'data' => $info_user
        ], 200);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email'     => 'required|max:254',
            'password' => 'required|min:6',
            'app_id'=>'required'
        ]);
      
        if ($validator->fails()) {
            return $this->validateFail($validator->errors() );
        }
       
        $email =  $request->email;
        // che thong chi cho phep dang nhap bang sdt hoac email.
        if( !filter_var($email, FILTER_VALIDATE_EMAIL) ) {
             
             if (!($token = \Auth::guard('public_user')->attempt(['mobile' => $email, 'password' => $request->password]))) {
                return $this->responseError(['Số điện thoại hoặc mật khẩu không đúng.'], self::LOGIN_FAIL );
            }
            return $this->responseLoginSuccess($token, $request );
        }

        if (!($token = \Auth::guard('public_user')->attempt(['email' => $email, 'password' => $request->password]))) {
         
            return $this->responseError(['Email hoặc mật khẩu không đúng.'], self::LOGIN_FAIL );
        }
        return $this->responseLoginSuccess($token, $request );
    }
    public function login_v2(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email'     => 'required|max:254',
            'password' => 'required|min:6',
            'app_id'=>'required'
        ]);

        if ($validator->fails()) {
            return $this->sendErrorResponse($validator->errors() );
        }
 
        $email =  $request->email;
        // che thong chi cho phep dang nhap bang sdt hoac email.
        if( !filter_var($email, FILTER_VALIDATE_EMAIL) ) {

             if (!($token = \Auth::guard('public_user')->attempt(['mobile' => $email, 'password' => $request->password]))) {
                return $this->sendErrorResponse(['Số điện thoại hoặc mật khẩu không đúng.'], self::LOGIN_FAIL );
            }
            return $this->responseLoginSuccess_v2($token, $request );
        }

        if (!($token = \Auth::guard('public_user')->attempt(['email' => $email, 'password' => $request->password]))) {
            return $this->sendErrorResponse(['Email hoặc mật khẩu không đúng.'], self::LOGIN_FAIL );
        }

        return $this->responseLoginSuccess_v2($token, $request );
    }

    private function responseLoginSuccess($token, $request ){
       
        $userinfo = UserInfo::where([
            'pub_user_id'=> \Auth::guard('public_user')->user()->id,
            'type' => Users::USER_WEB,
            'app_id'=> $request->app_id,
            'status'=> 1
        ])->get();
        $buildings = null;
        foreach ($userinfo as $key => $value) {
            $buildings[] = [
                'id'=> @$value->building->id,
                'name'=>@$value->building->name
            ];
        }
        return $this->responseSuccess([
                    'access_token' => $token,
                    'token_type' => 'bearer',
                    'expires_in' => \Auth::guard('public_user')->factory()->getTTL() * 60000,
                    'profiled'=> $userinfo->count(),
                    'building'=> $buildings,
                    'user' => \Auth::guard('public_user')->user()

                ] );
    }
    private function responseLoginSuccess_v2($token, $request ){
      
        $userinfo = UserInfo::where([
            'pub_user_id'=> \Auth::guard('public_user')->user()->id,
            'type' => Users::USER_WEB,
            'app_id'=> $request->app_id,
            'status'=> 1
        ])->get();
        $buildings = null;
        foreach ($userinfo as $key => $value) {
            $buildings[] = [
                'id'=> @$value->building->id,
                'name'=>@$value->building->name
            ];
        }
        return $this->sendSuccessApi([
                    'access_token' => $token,
                    'token_type' => 'bearer',
                    'expires_in' => \Auth::guard('public_user')->factory()->getTTL() * 60000,
                    'profiled'=> $userinfo->count(),
                    'building'=> $buildings,
                    'user' => \Auth::guard('public_user')->user()

                ] );
    }

    public function logout(Request $request)
    {
    }

    public function getAuthUser(Request $request)
    {
        $info = \Auth::guard('public_user')->user()->BDCprofile()->where('bdc_building_id',$request->building_id)->first();
        if($info){
//            $info_sub = $this->modelUserinfo->checkChangeProfile($info->pub_user_id,$request->building_id ?? $info->bdc_building_id,$info->app_id,$info->type);

            $staffvsdepartment  = $this->modelDepartmentStaff->getStaffByPubUser($info->pub_user_id,$request->building_id ?? $info->bdc_building_id);
            $permission_deny = [];$permission_ids = [];$permission_check = [];
            if($staffvsdepartment){
                if ($staffvsdepartment->permission_deny) {
                    $permission_deny = unserialize($staffvsdepartment->permission_deny);
                }
                $permissionsGroup = $staffvsdepartment->department->permissions;
                if ($permissionsGroup) {
                    $permission_ids = unserialize($permissionsGroup->permission_ids);
                }
                $permission_check= array_diff($permission_ids, $permission_deny);
            }
            $apps_creen = $this->modelModule->getIdTypeApp();
            $permis = $this->modelUsesPerm->findPermission($info->pub_user_id);
            $permission_check= array_unique(array_merge($permis, $permission_check));
            $screens = [];
            foreach ($apps_creen->permissions as $item){
                if(in_array($item->id,$permission_check)){
                    $screens[]=$item['route_name'];
                }
            }
            $info->roles = $screens;
//        $info->info_sub = $info_sub;
            if (!$info) {
                return $this->responseError('Đã có lỗi xẩy ra.', 3006);
            }
            return $this->responseSuccess( $info->toArray() );
        }
        return $this->responseError('Kiểm tra lại building_id', 204);
    }

    public function createProfile(Request $request, UserInfo $info)
    {
         $validator = Validator::make($request->all(), [
            'app_id'=>'required'
        ]);

        if ($validator->fails()) {
            return $this->validateFail($validator->errors() );
        }
        // kiêm tra app id đúng hay không?
        if (!$this->checkAppId($request->app_id)) {
            return $this->validateFail( ['Bạn không thể thao tác với app id này.'] );
        }
        // kiem tra xem profile da ton tai chua
        $hasProfile = $info->where(['pub_user_id'=> \Auth::guard('public_user')->user()->id, 'app_id'=> $request->app_id])->count();
         if ( $hasProfile) {
            return $this->validateFail(['Hồ sơ đã tồn tại'] );
        }

        return $this->responseSuccess(  $this->createUserInfo($info,  \Auth::guard('public_user')->user(), $request->app_id)->toArray() );
    }

    public function resetPassword(Request $request)
    {
        $info = \Auth::guard('public_user')->user();
        if(!Hash::check($request->old,$info->password)) {
            return $this->responseError('Mật khẩu cũ không đúng', 400);
        }
        if($request->old == $request->new) {
            return $this->responseError('Mật khẩu mới không được trùng với mật khẩu cũ', 400);
        }
        $update = $this->model->updatePass($info->email,$request->new);
        if($update){
            return $this->responseSuccess([],'Thay đổi mật khẩu thành công');
        }
        return $this->responseError('Lỗi đường truyền', 404);

    }
    public function resetPassword_v2(Request $request)
    {
        $info = Auth::guard('public_user_v2')->user();
        if(!Hash::check($request->old,$info->pword)) {
            return $this->responseError('Mật khẩu cũ không đúng', 400);
        }
        if($request->old == $request->new) {
            return $this->responseError('Mật khẩu mới không được trùng với mật khẩu cũ', 400);
        }
        $update = User::changePass($info->email,$request->new);
        if($update){
            return $this->responseSuccess([],'Thay đổi mật khẩu thành công');
        }
        return $this->responseError('Lỗi đường truyền', 404);

    }
    public function resetPasswordCustomer(Request $request)
    {
        $info = \Auth::guard('public_user')->user();

        if(!Hash::check($request->password,$info->password)) {
            return $this->responseError('Mật khẩu cũ không đúng', 400);
        }
        if($request->password == $request->password_new) {
            return $this->responseError('Mật khẩu mới không được trùng với mật khẩu cũ', 400);
        }
        if($request->password_new != $request->password_confirmation){
            return $this->responseError('Mật khẩu mới và mật khẩu xác nhận không trùng nhau', 400);
        }

        $update = $this->model->updatePass($info->email,$request->password_new);
        if($update){
            return $this->responseSuccess([],'Thay đổi mật khẩu thành công');
        }
        return $this->responseError('Lỗi đường truyền', 404);

    }

    private function checkAppId($app_id)
    {
        return AppConfig::hasAppId($app_id);
    }

    public function changeProfile(Request $request)
    {
        $id = \Auth::guard('public_user')->user()->id;

        $user_info =  UserInfo::where('pub_user_id',$id)->get();
        
        if($user_info){
            $files = $request->file('avatar');
            $directory = 'media/avatar';
            $avatar=null;
            if ($request->hasFile('avatar')) {
                    $ext = strtolower($files->getClientOriginalExtension());
                    $rs_file = Helper::doUpload($files,$files->getClientOriginalName(),$directory);
                    if(in_array($ext,['jpeg', 'jpg','png', 'gif' ])){
                        $avatar=$rs_file->origin;
                    }
            }
            $rs_info_id = null;
            foreach ($user_info as $key => $value) {
                $value->update([
                    'display_name'=>$request->display_name ?? $value->display_name,
                    'phone'=>$request->phone ?? $value->phone,
                    'email'=>$request->email ?? $value->email,
                    'address'=>$request->address ?? $value->address,
                    'gender'=>$request->gender ?? $value->gender,
                    'birthday'=>$request->birthday ? Carbon::parse($request->birthday) : $value->birthday,
                    'cmt'=>$request->cmt  ?? $value->cmt,
                    'cmt_nc'=>$request->cmt_nc ? Carbon::parse($request->cmt_nc) : $value->cmt_nc,
                    'cmt_address'=>$request->cmt_address ?? $value->cmt_address,
                    'avatar'=>$avatar??$value->avatar,
                ]);
                $value->save();
                $rs_info_id=$value->id;
            }
            $rs_info =  UserInfo::find($rs_info_id);
            return $this->responseSuccess($rs_info->toArray(),'Thêm Profile thành công');
        }
        return $this->responseError('Thay đổi thông tin thất bại', 401);
    }
    public function logoutApp(Request $request)
    {
        try {
            $user_id = \Auth::guard('public_user')->user()->id;
            $this->modelFcm->deletefcm($user_id,'cudan');
            //$this->modelFcm->deleteByDeviceId($request->device_id);
            Auth::guard('public_user')->logout();
            return response()->json([
                'success' => true,
                'message' => 'Bạn đã logout thành công.',
            ]);
        } catch (JWTException $exception) {
            return response()->json([
                'success' => false,
                'message' => 'Xin lỗi, yêu cầu logout thất bại.',
            ], 500);
        }
    }
}
