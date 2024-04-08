<?php

namespace App\Http\Controllers\Auth\Api;

use App\Commons\Api;
use App\Helpers\dBug;
use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterAuthRequest;
use App\Models\Apartments\Apartments;
use App\Models\BoCustomer;
use App\Models\Building\Building;
use App\Models\PublicUser\UserInfo;
use App\Models\PublicUser\Users;
use App\Models\PublicUser\V2\User;
use App\Models\PublicUser\V2\UserInfo as V2UserInfo;
use App\Models\PublicUser\V2\UserInfoApartment;
use App\Repositories\Apartments\ApartmentsRespository;
use App\Repositories\Customers\CustomersRespository;
use App\Repositories\Fcm\FcmRespository;
use App\Repositories\PublicUsers\PublicUsersProfileRespository;
use App\Traits\ApiResponse;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class ApiController extends Controller
{
    use ApiResponse;

    const DUPLICATE     = 19999;
    const LOGIN_FAIL    = 10000;
    private $model;
    private $modelfcm;
    private $modelUserinfo;
    public function __construct(Users $model,FcmRespository $modelfcm,PublicUsersProfileRespository $modelUserinfo)
    {
        $this->model = $model;
        $this->modelfcm = $modelfcm;
        $this->modelUserinfo = $modelUserinfo;
        $this->middleware('auth:public_user_v2', ['except' => ['login_v2', 'register']]);
    }
    public $loginAfterSignUp = true;

    public function register(RegisterAuthRequest $request)
    {
        $user  = new BoCustomer();
        $param = [
            'cb_id'       => time(),
            'cb_email'    => $request->cb_email,
            'cb_password' => bcrypt($request->cb_password),
            'cb_name'     => $request->cb_name,
            'app_id'      => $request->app_id,
        ];
        $user->fill($param)->save();

        // if ($this->loginAfterSignUp) {
        //     return $this->login($request);
        // }

        return response()->json([
            'success' => true,
            'message' => 'Đăng ký thành công',
            'data'    => $user,
        ], 200);
    }

    public function login(Request $request)
    {
       // dBug::trackingPhpErrorV2($request->all());
        $input = $request->only('email', 'password','app_id');

        if( filter_var($input['email'], FILTER_VALIDATE_EMAIL) ) {
            // $input['email'] = $email . "@phone.dxmb";
            $attempt = ['email'=>$request->email, 'password' => $request->password];
        }else{
            $attempt = ['mobile'=>$request->email, 'password' => $request->password];
        }

        if (!($token = Auth::guard('public_user')->attempt($attempt))) {
            return response()->json([
                'status' => 'error',
                'error'  => 'invalid.credentials',
                'msg'    => 'Invalid Credentials.',
            ], Response::HTTP_BAD_REQUEST);
        }

        return $this->responseSuccess([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => Auth::guard('public_user')->factory()->getTTL() * 60,
            'profiled'=> UserInfo::where([
                'pub_user_id'=> Auth::guard('public_user')->user()->id,
                'type' => Users::USER_APP,
                'app_id'=> $request->app_id
            ])->count()

        ] );
    }
    public function login_v2(Request $request,User $user)
    {
        //dBug::trackingPhpErrorV2($request->all());
        $input = $request->only('email', 'password','app_id');
        if( filter_var($input['email'], FILTER_VALIDATE_EMAIL) ) {
            $attempt = ['email'=>$request->email, 'password' => $request->password];
        }else{
            $attempt = ['phone'=>$request->email, 'password' => $request->password];
        }
        if (!($token = Auth::guard('public_user_v2')->attempt($attempt))) {
            return response()->json([
                'status' => 'error',
                'error'  => 'invalid.credentials',
                'msg'    => 'Invalid Credentials.',
            ], Response::HTTP_BAD_REQUEST);
        }
        return $this->responseSuccess([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => Auth::guard('public_user_v2')->factory()->getTTL() * 60,
            'profiled'=> V2UserInfo::where([
                'user_id'=> Auth::guard('public_user_v2')->user()->id
            ])->count()

        ] );
    }

    public function getUserInfo(Request $request,ApartmentsRespository $aptm,CustomersRespository $cusm)
    {
        $user = Auth::guard('public_user')->user();
        $info = Auth::guard('public_user')->user()->info();
      
        if($request->building_id){
            $info = $info->whereHas('bdcCustomers', function($q) use ($request){
                $q->where('bdc_building_id', $request->building_id);
            });
        }
        $info = $info->first();
        $list_profile = $this->modelUserinfo->getByPubUserId($user->id);
        $apartment_list_id = $cusm->getApartmentByIds($list_profile);
        $apartment_list = $aptm->getbyIdsV2($apartment_list_id);

        if (!$info) {
            return $this->responseError('Đã có lỗi xẩy ra(5555).', 3006);
        }
        $apartment = [];
        foreach ($apartment_list as $item){
            $apartment[]= [
                'id'=>$item->id,
                'name'=>$item->name,
                'building_id'=>$item->building_id,
                'place'=>$item->building_place_id??null,
                'type'=> !empty($item->building->template_mail) ? @$item->building->template_mail:null
            ];
        }

        $info->apartments = $apartment;
        unset($info->bdcCustomers);

        $user->mobile_active = 1;
        $user->save();
        return $this->responseSuccess( $info->toArray() );

    }
    public function getUserInfoV2(Request $request)
    {
        $user_info = Auth::guard('public_user_v2')->user()->infoApp;
        $user = Auth::guard('public_user_v2')->user();
        $user_apartment = UserInfoApartment::getApartmentByUserInfo($user_info->id);
        $apartment = [];
        if ($user_apartment) {
            foreach ($user_apartment as $key => $value) {
                $apart = Apartments::get_detail_apartment_by_apartment_id($value->apartment_id);
                $building = Building::get_detail_building_by_building_id($apart->building_id);
                if($key == 0){
                    $user_info->bdc_building_id =  $building->id;
                }
                $apartment[] = [
                    'id' => $value->apartment_id,
                    'name' => $apart->name,
                    'building_id' => $apart->building_id,
                    'place' => $apart->building_place_id ?? null,
                    'type' => !empty($building->template_mail) ? @$building->template_mail : null
                ];
            }
        }
        $user_info->pub_user_id = $user_info->user_id;
        $user_info->display_name =  $user_info->full_name;
        $user_info->phone =  $user->phone;
        $user_info->email =  $user->email;
        $user_info->address =  $user_info->address;
        $user_info->cmt =  $user_info->cmt_number;
        $user_info->cmt_nc =  $user_info->cmt_province;
        $user_info->cmt_address =  $user_info->cmt_address;
        $user_info->app_id =   "buildingcare";
        $user_info->staff_code = null;
        $user_info->customer_code_prefix =  null;
        $user_info->customer_code =  null;
        $user_info->apartments = $apartment;
        $user_info->birthday = Carbon::parse(@$user_info->birthday)->format('Y-m-d');
        return $this->responseSuccess($user_info->toArray());

    }
    public function logout(Request $request)
    {
        $this->validate($request, [
            'token' => 'required',
        ]);

        try {
            JWTAuth::invalidate($request->token);

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

    public function getAuthUser(Request $request)
    {
        $token      = JWTAuth::getToken();
        $user       = JWTAuth::toUser($token);
        $user->type = $this->getApiUserType($user);
        return response()->json(['data' => $user]);
    }

    protected function getApiUserType($user)
    {
        $class = get_class($user);
        $types = Config::get('auth.types');
        $type  = 'user';

        foreach ($types as $key => $value) {
            if ($class == $value) {
                $type = $key;
                break;
            }
        }

        return $type;
    }

    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required'
        ]);

        if( $validator->fails() ) {
            return $this->validateFail(($validator->errors()));
        }
        $email = $request->input('email');

        if( $email != $info->email ) {
            return $this->responseError(['Email không chính xác.'], 401);
        }

        $user = Users::where('id', $info->id)->update([
            'password' => Hash::make('bdc123456'),
        ]);

        if( !$user ) {
            return $this->responseError(['Thay đổi mật khẩu không thành công'], 401);
        }

        return $this->responseSuccess(['Thay đổi mật khẩu thành công'], 'Success', 200);

    }

    public function changePassword(Request $request)
    {

        $info = Auth::guard('public_user')->user();
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

        if ($password_new !== $password_confirmation) {
            return $this->responseError(['Mật khẩu xác nhận không chính xác.'], 401);
        }

        if( !Hash::check($password, $info->password) ) {
            return $this->responseError(['Mật khẩu không chính xác.'], 401);
        }

        $password_new = Hash::make($password_new);
        $user         = Users::where('id', $info->id)->update([
            'password' => trim($password_new),
        ]);

        if( !$user ) {
            return $this->responseError(['Thay đổi mật khẩu không thành công'], 401);
        }

        return $this->responseSuccess(['Thay đổi mật khẩu thành công'], 'Success', 200);

    }
    public function changeProfile(Request $request)
    {
        $user_info = Auth::guard('public_user_v2')->user()->infoApp;
        $apartment = UserInfoApartment::where('user_info_id',$user_info->id)->first();
         $options = [
            'multipart' => [
                [
                    'name' => 'user_id',
                    'contents' =>  $user_info->user_info_id
                ],
                [
                    'name' => 'user_info_id',
                    'contents' =>  $user_info->id
                ],
                [
                    'name' => 'full_name',
                    'contents' => $request->name
                ],
                [
                    'name' => 'address',
                    'contents' =>  $request->address
                ],
                [
                    'name' => 'cmt_number',
                    'contents' =>   $request->id_passport,
                ],
                [
                    'name' => 'cmt_date',
                    'contents' =>$request->issue_date ?  Carbon::parse($request->issue_date)->format('Y-m-d') : null
                ],
                [
                    'name' => 'gender',
                    'contents' =>   $request->gender ?? 1
                ],
                [
                    'name' => 'phone_contact',
                    'contents' =>   $request->phone
                ],
                [
                    'name' => 'email_contact',
                    'contents' =>  $request->email
                ],
                [
                    'name' => 'building_id',
                    'contents' =>   $apartment->building_id
                ]
            ]
        ];
        $residents = Api::POST_MULTIPART('admin/updateUser',$options);

        if($residents->status == true){
            return $this->responseSuccess([],'Thay đổi Profile thành công');
        }
        return $this->responseError($residents->mess, 404);
    }
}
