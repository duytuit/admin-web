<?php

namespace App\Http\Controllers\Frontend;

use App\Commons\Api;
use App\Http\Controllers\BuildingController;
use App\Notifications\ResetPasswordRequest;
use App\Repositories\PublicUsers\PublicUsersRespository;
use App\Repositories\BdcverifycodeOTP\VerifyCodeOTPRepository;
use App\Services\ServiceSendMailV2;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use App\Models\PublicUser\Users;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use App\Traits\ApiResponse;
use App\Services\SendSMSSoapV2;
use App\Commons\Helper;
use App\Helpers\dBug;
use App\Models\Apartments\V2\UserApartments;
use App\Models\Building\Building;
use App\Models\Campain;
use App\Models\PaymentInfo\PaymentInfo;

class ForgotPasswordController extends BuildingController
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset emails and
    | includes a trait which assists in sending these notifications from
    | your application to your users. Feel free to explore this trait.
    |
     */
    use ApiResponse;
    protected $_client;
    public $timeout=0;
    use SendsPasswordResetEmails;
    const FORGOT = 3;
    const VERIFY_CODE = 33;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    private $model;
    private $verifycodeOTPrepository;
    public function __construct(PublicUsersRespository $model,Request $request,Client $client,VerifyCodeOTPRepository $verifycodeOTPrepository)
    {
        $this->middleware('guest');
        $this->model = $model;
        $this->verifycodeOTPrepository = $verifycodeOTPrepository;
        $this->_client = $client;
        //
        parent::__construct($request);
    }

    public function attributes()
    {
        return [
            'email' => 'Địa chỉ email',
        ];
    }

    function getToken($length)
    {
        $token = "";
        $codeAlphabet= "0123456789";
        $max = strlen($codeAlphabet); // edited

        for ($i=0; $i < $length; $i++) {
            $token .= $codeAlphabet[random_int(0, $max-1)];
        }

        return $token;
    }
     function getTokenOTP($length)
    {
        $token = "";
        $codeAlphabet= "0123456789";
        $max = strlen($codeAlphabet); // edited

        for ($i=0; $i < $length; $i++) {
            $token .= $codeAlphabet[random_int(0, $max-1)];
        }

        return $token;
    }

    public function showLinkRequestForm()
    {
        return view('auth.admin.passwords.email');
    }
    public function showLinkRequestFormVerify(Request $request)
    {
        $data['meta_title'] = 'Verify Code';
        $data['hascode']  =$request->code;
        $data['account'] = $request->account;
        $data['timer'] = $request->timer;

        return view('auth.admin.verify',$data);
    }
    public function showLinkRequestFormResetPass()
    {
        return view('auth.admin.reset-pass');

    }
    public function showLinkRequestFormtest()
    {
        return view('auth.admin.test');

    }
    public function CheckOTP(Request $request)
    {

        $request->request->add(['account' => $request->account]);
        $request->request->add(['token' => $request->iduser]);
        $request->request->add(['code' => $request->verifycode]);
        $result_api = Api::POST('admin/auth/verifyAccount',$request->all());
        if($result_api->status == true){
            $responseData = [
                'success' => true,
                'message' => 'Xác thực tài khoản '.$request->account.' thành công!',
                'href' => route('password.resetpass')
            ];
            return response()->json($responseData);
        }else{
            $responseData = [
                'success' => false,
                'message' => 'Xác thực mã OTP thất bại!',
            ];
            return response()->json($responseData);
        }
       
    }
    public function newpass(Request $request)
    {
        if (!$request->new_password || !$request->new_password_confirmation) {
            $responseData = [
                'success' => false,
                'message' => 'mật khẩu không khớp',
                'href' => route('password.verify')
            ];
            return response()->json($responseData);
        }
        if ($request->new_password !== $request->new_password_confirmation) {
            $responseData = [
                'success' => false,
                'message' => 'mật khẩu không khớp',
                'href' => route('password.verify')
            ];
            return response()->json($responseData);
        }
        $request->request->add(['account' =>urldecode($request->account)]);
        $request->request->add(['pword' => $request->new_password]);
        $request->request->add(['token' => urldecode($request->id)]);

        $result_api = Api::POST('admin/auth/setPword',$request->all());
        dBug::trackingPhpErrorV2($request->all());
        dBug::trackingPhpErrorV2($result_api);
        if($result_api->status == true){
            $responseData = [
                'success' => true,
                'message' => 'Đổi mật khẩu thành công!',
                'href' => '/admin/login'
            ];
            return response()->json($responseData);
        }else{
            $responseData = [
                'success' => false,
                'message' => $result_api->mess,
                'href' => route('password.verify')
            ];
            return response()->json($responseData);
        }
    }
    public function sendOTP(Request $request)
    {
        $request->request->add(['account' => $request->mobile]);
        $result_api = Api::POST('admin/auth/forgetAccount',$request->all());
        if($result_api->status == true){
            return redirect()->route('password.verify',[ 'code' => $result_api->data->token ,'account' => $request->mobile,'timer'=> 600])->with(['success' => 'Mời bạn nhập nhập mã OTP..!']);
        }else{
            return redirect()->route('password.verify')->with(['error' => $result_api->mess]);
        }
    }

    /**
     * Create token password reset.
     *
     * @param  ResetPasswordRequest $request
     * @return JsonResponse
     */



    public function sendMail(Request $request)
    {
        $password = $this->getToken(6);
        if ($request->email == '') {
            return redirect()->route('password.request')->with('warning', 'Có lỗi phát sinh, bạn vui lòng thử lại họa liên hệ với chúng tôi để được hỗ trợ.');
        }
        if (!filter_var($request->email, FILTER_VALIDATE_EMAIL)) {
            if ($this->model->checkPhone($request->email)){
                $update = $this->model->resetPassByPhone($request->email,'123456');
                if($update){
//                    $this->sendMail($request->email,$password);
//                    return $this->responseSuccess(['password'=>'123456'],'Số điện thoại của bạn chính xác, mật khẩu đã được reset về mặc định mời đăng nhập lại');
                    return redirect()->route('password.request')->with('success', 'Chúng tôi vừa gửi hướng dẫn cùng đường link để reset mật khẩu. Vui lòng kiểm tra tin nhắn ' . $request->email);
                }
            };
            return redirect()->route('password.request')->with('warning', 'Có lỗi phát sinh, bạn vui lòng thử lại họa liên hệ với chúng tôi để được hỗ trợ.');
        }

        if($this->model->checkExit($request->email)){
            $update = $this->model->resetPass($request->email,$password);
            if($update){
                $this->sendEmail($request->email,$password);
//                return $this->responseSuccess(['password'=>$password],'Email của bạn chính xác, xin vui lòng kiểm tra email và đăng nhập lại');
                return redirect()->route('password.request')->with('success', 'Chúng tôi vừa gửi hướng dẫn cùng đường link để reset mật khẩu. Vui lòng kiểm tra email ' . $request->email);
            }else{
                return redirect()->route('password.request')->with('warning', 'Có lỗi phát sinh, bạn vui lòng thử lại họa liên hệ với chúng tôi để được hỗ trợ.');
            }
        }
        return redirect()->route('password.request')->with('warning', 'Có lỗi phát sinh, bạn vui lòng thử lại họa liên hệ với chúng tôi để được hỗ trợ.');


       /* $rules = [
            'email' => 'required|email',
        ];

        $validator = Validator::make($request->all(), $rules, [], $this->attributes());
        $errors    = $validator->messages();

        $user = BoUser::where('ub_email', $request->email)->first();

        if (!$user) {
            $bo_customer = BoCustomer::where('cb_email', $request->email)->first();
            $user        = $bo_customer;
        }

        if (!$user) {
            $user_partner = UserPartner::where('email', $request->email)->first();
            $user         = $user_partner;
        }

        if (!$user) {
            $errors->add('email', 'Địa chỉ email không chính xác.');
        }

        if ($errors->toArray()) {
            return back()->with(['errors' => $errors])->withInput();
        }

        if (!$request->has('_validate')) {

            $passwordReset = PasswordReset::updateOrCreate([
                'email' => $user->email,
            ], [
                'token' => Str::random(60),
            ]);

            if ($passwordReset) {
                $user->notify(new ResetPasswordRequest($passwordReset->token));
            } else {
                return redirect()->route('password.request')->with('warning', 'Có lỗi phát sinh, bạn vui lòng thử lại họa liên hệ với chúng tôi để được hỗ trợ.');
            }

            return redirect()->route('password.request')->with('success', 'Chúng tôi vừa gửi hướng dẫn cùng đường link để reset mật khẩu. Vui lòng kiểm tra email ' . $user->email);
        }*/
    }
    public function sendEmail($email,$pass)
    {
        $user = $this->model->getUserProfile($email);
        $total = ['email'=>1, 'app'=> 0, 'sms'=> 0];
        $campain = Campain::updateOrCreateCampain("Forgot password cho: ".$email, config('typeCampain.FORGOT'), null, $total, $this->building_active_id, 0, 0);

         
        $data = [
            'params' => [
                '@ten' => @$user->profileAll->display_name,
                '@pass' => $pass,
                '@ngay' => date('d/m/Y',time()),
                '@urlLogin' => url('/login'),
            ],
            'cc' => $email,
            'building_id' => @$user->profileAll->bdc_building_id,
            'type' => self::FORGOT,
            'status' => 'success',
            'campain_id' => $campain->id
        ];
        try {
            ServiceSendMailV2::setItemForQueue($data);
            return ;
        } catch (\Exception $e)
        {
            return $e->getMessage();
        }
    }
    public function sendVerifyCodeOTP($email,$otp)
    {
        $user = Users::checkEmailPhone($email);

       // dBug::trackingPhpErrorV2($user);

        $building = Building::get_detail_building_by_building_id(@$user->BDCprofile->bdc_building_id);

        $building_payment_info = PaymentInfo::get_detail_payment_info_by_building_id(@$building->id);

        $html = null;
        if ($building_payment_info) {
            foreach ($building_payment_info as $key => $value) {
                $html[] = '<div><p><strong>Thông tin thanh toán ' . ($key + 1) . ':</strong></p>' .
                    '<p>Số tài khoản: ' . $value->bank_account . ' </p>' .
                    '<p>Ngân hàng: ' . $value->bank_name . ' </p>' .
                    '<p>Chủ tài khoản: ' . $value->holder_name . ' </p>' .
                    '<p>Chi nhánh: ' . $value->branch . ' </p></div>';
            }
        }
        $template = json_encode([
            'ten_khach_hang' =>  @$user->BDCprofile->display_name ?? '',
            'otp' => $otp,
            'sdt_bql' => @$building->phone,
            'ten_toa' => @$building->name,
            'email_bql' => $building->email,
            'thong_tin_thanh_toan' =>$html ? implode(',',$html) : null,
        ]);
        $type = 'bdc_send_otp_change_password';
        $client = new \GuzzleHttp\Client();
        $headers = [
            'ClientSecret' => env('ClientSecret_bdc'),
            'ClientId' => env('ClientId_bdc'),
        ];

        $array_send_mail = [
            'code' => $type,
            'email' => $email,
            'message' => $template,
            'building_id' => @$building->id,
            'attachFile' =>  null
        ];
        $requestClient = $client->request('POST', 'https://authv2.dxmb.vn/api/v2/notification/sendMail', [
            'headers' => $headers,
            'json' => $array_send_mail,
        ]);
    }
}
