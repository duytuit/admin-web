<?php

namespace App\Http\Controllers\Backend;

use App\Commons\Api;
use App\Helpers\dBug;
use App\Http\Controllers\BuildingController;
use App\Http\Requests\UsersRequest\UserProfileRequest;
use App\Models\Building\Building;
use App\Models\Building\CompanyStaff;
use App\Models\Building\Urban;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\PublicUser\UserInfo;
use App\Models\PublicUser\V2\User;
use Validator;

class UserController extends BuildingController
{
    /**
     * Khởi tạo
     */
    public function __construct(Request $request)
    {
        Carbon::setLocale('vi');
        parent::__construct($request);
    }

    public function attributes()
    {
        return [
            'password'         => 'Mật khẩu',
            'password_confirm' => 'Mật khẩu xác nhận',
        ];
    }

    /**
     * Danh sách bản ghi
     *
     * @param  Request  $request
     * @return Response
     */
    public function index(Request $request)
    {
        return view('backend.users.index');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function profile(Request $request)
    {
        $data['meta_title'] = 'Thông tin user';
        $data['user'] = \Auth::user();
        if(!$data['user']){
            return redirect()->route('admin.auth.form');
        }
        return view('backend.users.profile', $data);
    }

    public function validator_pass(Request $request)
    {
        $rules = [
            'password'         => 'required',
            'password_confirm' => 'required',
        ];

        $validator = Validator::make($request->all(), $rules, [], $this->attributes());
        $errors    = $validator->messages();

        if ($request->password !== $request->password_confirm) {
            $errors->add('password_confirm', 'Mật khẩu xác nhân không chính xác.');
        }

        if ($errors->toArray()) {
            return response()->json(['error_resset' => $errors]);
        }
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function resetPass(Request $request)
    {
        //dd(435345);
        $request->request->add(['user_id' =>  \Auth::user()->id]);
        $request->request->add(['password' => $request->password]);
       // $user = User::checkEmailPhone($request->mobile);
        $result_api = Api::POST('admin/auth/changePassword?building_id='.$this->building_active_id,$request->all());
        if($result_api->status == true){
            return redirect(route('admin.users.profile'))->with('success',$result_api->mess);
        }else{
            return redirect(route('admin.users.profile'))->with('error', $result_api->mess);
        }
    }

    public function upload_avatar(Request $request)
    {
        $avatar = $request->image ?: '';
        $user = \Auth::user();
        UserInfo::where('pub_user_id',$user->id)->update(['avatar'=>$avatar]);

        return redirect()->route('admin.users.profile')->with('success', "Cập nhật avatar thành công.");
    }

    public function processProfile(UserProfileRequest $request) {


        $inputData = $request->except('_token');
        $user = \Auth::user();
        $building = Building::find($this->building_active_id);
        $urBan =  Urban::find($building->urban_id);
        $request->request->add(['urban' =>  json_encode($urBan)]);
        if($urBan){
            $staff_company = CompanyStaff::where('pub_user_id', $user->id)->where('bdc_company_id', $urBan->company_id)->first();
            if($staff_company){
                $staff_company->pub_user_id =  $user->id;
                $staff_company->bdc_company_id =  $urBan->company_id;
                $staff_company->save();
            }else
            {
                $staff_company =  CompanyStaff::create([
                    'pub_user_id' =>$user->id,
                    'bdc_company_id'=>$urBan->company_id,
                    'type'=>0,
                    'name'=>$inputData['display_name'],
                    'email'=>$inputData['email'],
                    'phone'=>$request->phone,
                    'active' =>1
                ]);
                
            }
        }
    
        UserInfo::where('pub_user_id', \Auth::user()->id)->update([
            'display_name' =>$inputData['display_name'],
            'email' =>$inputData['email'],
            'staff_code' =>$inputData['staff_code'],
            'phone' =>$inputData['phone'],
        ]);
        $user->email = $inputData['email'];
        $user->mobile = $inputData['phone'];
        $user->save();

        $request->request->add(['email' =>  $user->email]);
        $request->request->add(['phone' => $user->mobile]);
        $request->request->add(['customer_code' => $inputData['staff_code']]);
        $request->request->add(['full_name' => $inputData['display_name']]);
        $request->request->add(['building_id' => $this->building_active_id]);
        $result_api = Api::POST('admin/auth/updateUserAdmin',$request->all());
        dBug::trackingPhpErrorV2($result_api);
        if($result_api->status == true){
            return response()->json([
                'message' =>  $result_api->mess,
                'code' => 201,
                'data' => $user
            ],201);
        }else{
            return response()->json([
                'message' =>  $result_api->mess,
                'code' => 201,
                'data' => $user
            ],201);
        }
      
    }
}
