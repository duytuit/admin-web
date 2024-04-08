<?php

namespace App\Http\Controllers\Auth\Admin;

use App\Http\Controllers\Api\V1\Controller;
use App\Models\BoCustomer;
use App\Models\BoUser;
use App\Models\PasswordReset;
use App\Models\UserPartner;
use App\Notifications\ResetPasswordRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Repositories\PublicUsers\PublicUsersRespository;
use App\Repositories\BdcverifycodeOTP\VerifyCodeOTPRepository;
use App\Services\ServiceSendMailV2;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use GuzzleHttp\Client;
use App\Models\PublicUser\Users;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Traits\ApiResponse;
use JWTAuth;
use App\Http\Requests\LoginWithOTP\LoginWithOTPRequest;
use App\Services\SendSMSSoapV2;
use App\Commons\Helper;
use App\Models\Building\Building;
use App\Models\Campain;
use App\Models\PaymentInfo\PaymentInfo;
use Illuminate\Support\Facades\Validator;

class ResetPasswordController extends Controller
{
    use ApiResponse;

    protected $_client;
    public $timeout = 0;
    use SendsPasswordResetEmails;

    const FORGOT = 3;
    const VERIFY_CODE = 33;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    protected $model;
    protected $verifycodeOTPrepository;

    public function __construct(PublicUsersRespository $model, Request $request, Client $client, VerifyCodeOTPRepository $verifycodeOTPrepository)
    {
        $this->model = $model;
        $this->verifycodeOTPrepository = $verifycodeOTPrepository;
        $this->_client = $client;
    }

    public function attributes()
    {
        return [
            'password' => 'Mật khẩu',
            'password_confirmation' => 'Mật khẩu xác nhận',
            'email' => 'Địa chỉ email',
        ];
    }

    /**
     * Create token password reset.
     *
     * @param ResetPasswordRequest $request
     * @return JsonResponse
     */
    public function sendMail(Request $request)
    {
        $rules = [
            'email' => 'required|email',
        ];

        $validator = Validator::make($request->all(), $rules, [], $this->attributes());
        $errors = $validator->messages();

        $user = BoUser::where('ub_email', $request->email)->first();

        if (!$user) {
            $bo_customer = BoCustomer::where('cb_email', $request->email)->first();
            $user = $bo_customer;
        }

        if (!$user) {
            $user_partner = UserPartner::where('email', $request->email)->first();
            $user = $user_partner;
        }

        if (!$user) {
            $errors->add('email', 'Địa chỉ email không chính xác hoặc chưa đăng ký.');
        }

        if ($errors->toArray()) {
            return response()->json(['error' => $errors])->setStatusCode(401);
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
                return response()->json([
                    'msg' => 'Có lỗi phát sinh, bạn vui lòng thử lại họa liên hệ với chúng tôi để được hỗ trợ.',
                ]);
            }

            return response()->json([
                'msg' => 'Chúng tôi vừa gửi hướng dẫn cùng đường link để reset mật khẩu. Vui lòng kiểm tra email ' . $user->email,
            ]);
        }
    }

    public function LoginWithOTPApi(LoginWithOTPRequest $request)
    {
        $gettime = Carbon::now();
        $verifycodeOTP = $this->verifycodeOTPrepository->CheckOTPWithAccountNew($request->account, $request->verifycode);
        if (is_null($verifycodeOTP)) {
            $responseData = [
                'status' => false,
                'message' => 'Xác thực mã OTP thất bại!',
            ];
            return response()->json($responseData);
        }
        $diff = $gettime->getTimestamp() - strtotime($verifycodeOTP->created_at);
        if ($diff > 500) {
            $responseData = [
                'status' => false,
                'message' => 'Mã xác thực hết hạn!'
            ];
            return response()->json($responseData);
        } else {
            $user = Users::where('id', $verifycodeOTP->pub_users_id)->first();
            if ($user) {
                $access_token = JWTAuth::fromUser($user);
                $responseData = [
                    'status' => true,
                    'message' => 'Đăng nhập thành công!',
                    'access_token' => $access_token,
                    'token_type' => 'bearer',
                ];

                return response()->json($responseData);
            }
            $responseData = [
                'status' => false,
                'message' => 'Xác thực tài khoản thất bại!',
            ];
            return response()->json($responseData);
        }
        $responseData = [
            'status' => false,
            'message' => 'thất bại!'
        ];
        return response()->json($responseData);
    }

    public function sendOTPApi(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'mobile' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()], 404);
        }
        $CodeOTP = $this->getTokenOTP(6);
        if (filter_var($request->mobile, FILTER_VALIDATE_EMAIL)) {
            try {
                $user = $this->model->checkExitWeb($request->mobile);
                if (!$user) {
                    // gửi thông báo tài khoản không tồn tại
                    $responseData = [
                        'status' => false,
                        'message' => 'Tài khoản không tồn tại !',
                    ];
                    return response()->json($responseData);
                } else {
                    $this->verifycodeOTPrepository->create([
                        'pub_users_id' => $user->id,
                        'mobile' => $request->mobile,
                        'otp_code' => $CodeOTP,
                        'otp_timeout' => 500,
                        'status' => 1
                    ]);
                    // gui mai thong bao tai khoan duoc tao
                    $this->sendVerifyCodeOTP($request->mobile, $CodeOTP);
                    if ($user->mobile) {

                        $content = [
                            'customer' => Helper::convert_vi_to_en(@$user->BDCprofile->display_name),
                            'otp' => $CodeOTP,
                            'timeout' => 500
                        ];
                        if ($request->type_send_otp == 'login_sms') {
                            $result_sms = SendSMSSoapV2::sendSMS($content, $request->mobile, @$user->BDCprofile->bdc_building_id, SendSMSSoapV2::FORGOT, 'bdc');
                        }
                        if ($request->type_send_otp == 'login_forgot') {
                            $result_sms = SendSMSSoapV2::sendSMS($content, $request->mobile, @$user->BDCprofile->bdc_building_id, SendSMSSoapV2::FORGOT, 'bdc');
                        }

                        if ($result_sms == true) {
                            $responseData = [
                                'status' => true,
                                'message' => 'Mời bạn nhập nhập mã OTP!'
                            ];
                            return response()->json($responseData);
                        } else {
                            $responseData = [
                                'status' => false,
                                'message' => 'Tài khoản không tồn tại !',
                            ];
                            return response()->json($responseData);
                        }
                    }
                    $responseData = [
                        'status' => true,
                        'message' => 'Mời bạn nhập nhập mã OTP!'
                    ];
                    return response()->json($responseData);
                }
            } catch (\Exception $e) {
                return $e->getMessage();
            }
        } else {
            try {
                if (!$request->mobile) {
                    $responseData = [
                        'status' => false,
                        'message' => 'Tài khoản không tồn tại !',
                    ];
                    return response()->json($responseData);
                }
                $user = $this->model->checkPhoneWeb($request->mobile);
                if ($user) {
                    // gửi thông báo tài khoản không tồn tại
                    $this->verifycodeOTPrepository->create([
                        'pub_users_id' => $user->id,
                        'mobile' => $request->mobile,
                        'otp_code' => $CodeOTP,
                        'otp_timeout' => 500,
                        'status' => 1
                    ]);

                    if ($user->email) {
                        $this->sendVerifyCodeOTP($user->email, $CodeOTP);
                    }

                    $content = [
                        'customer' => Helper::convert_vi_to_en(@$user->BDCprofile->display_name),
                        'otp' => $CodeOTP,
                        'timeout' => 500
                    ];
                    if ($request->type_send_otp == 'login_sms') {
                        $result_sms = SendSMSSoapV2::sendSMS($content, $request->mobile, @$user->BDCprofile->bdc_building_id, SendSMSSoapV2::FORGOT, 'bdc');
                    }
                    if ($request->type_send_otp == 'login_forgot') {
                        $result_sms = SendSMSSoapV2::sendSMS($content, $request->mobile, @$user->BDCprofile->bdc_building_id, SendSMSSoapV2::FORGOT, 'bdc');
                    }

                    if ($result_sms == true) {
                        $responseData = [
                            'status' => true,
                            'message' => 'Mời bạn nhập nhập mã OTP!'
                        ];
                        return response()->json($responseData);
                    } else {
                        $responseData = [
                            'status' => false,
                            'message' => 'Tài khoản không tồn tại !',
                        ];
                        return response()->json($responseData);
                    }
                } else {
                    $responseData = [
                        'status' => false,
                        'message' => 'Tài khoản không tồn tại !',
                    ];
                    return response()->json($responseData);
                }
            } catch (\Exception $e) {
                return $e->getMessage();
            }
        }
    }

    function getTokenOTP($length)
    {
        $token = "";
        $codeAlphabet = "0123456789";
        $max = strlen($codeAlphabet); // edited

        for ($i = 0; $i < $length; $i++) {
            $token .= $codeAlphabet[random_int(0, $max - 1)];
        }

        return $token;
    }

    public function sendVerifyCodeOTP($email, $otp)
    {
        $user = Users::checkEmailPhone($email);

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
            'ten_khach_hang' => @$user->BDCprofile->display_name ?? '',
            'otp' => $otp,
            'sdt_bql' => @$building->phone,
            'ten_toa' => @$building->name,
            'email_bql' => $building->email,
            'thong_tin_thanh_toan' => $html ? implode(',', $html) : null,
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
            'attachFile' => null
        ];
        $requestClient = $client->request('POST', 'https://authv2.dxmb.vn/api/v2/notification/sendMail', [
            'headers' => $headers,
            'json' => $array_send_mail,
        ]);
    }

    public function CheckOTP(Request $request)
    {
        // if($this->timeout == 0){
        //     $responseData = [
        //         'status' => false,
        //         'message' => 'Mã xác thực hết hạn!'
        //     ]; 
        //     return response()->json($responseData);
        // }
        $validator = Validator::make($request->all(), [
            'account' => 'required',
            'verifycode' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()], 404);
        }
        $gettime = Carbon::now();
        $verifycodeOTP = $this->verifycodeOTPrepository->CheckOTPWithAccountNew($request->account, $request->verifycode);
        if (is_null($verifycodeOTP)) {
            $responseData = [
                'status' => false,
                'message' => 'Xác thực mã OTP thất bại!',
            ];
            return response()->json($responseData);
        }
        $diff = $gettime->getTimestamp() - strtotime($verifycodeOTP->created_at);
        if ($diff > 500) {
            $responseData = [
                'status' => false,
                'message' => 'Mã xác thực hết hạn!'
            ];
            return response()->json($responseData);
        } else {
            $user = Users::where('id', $verifycodeOTP->pub_users_id)->first();
            if ($user) {
                $access_token = JWTAuth::fromUser($user);
                $responseData = [
                    'status' => true,
                    'message' => 'Xác thực tài khoản thành công!',
                    'access_token' => $access_token,
                    'token_type' => 'bearer'
                ];

                return response()->json($responseData);
            }
            $responseData = [
                'status' => false,
                'message' => 'Xác thực tài khoản thất bại!',
            ];
            return response()->json($responseData);
        }
        $responseData = [
            'status' => false,
            'message' => 'thất bại!'
        ];
        return response()->json($responseData);
    }

    public function newpass(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'new_password' => 'required',
            'new_password_confirmation' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()], 404);
        }
        if (!$request->new_password || !$request->new_password_confirmation) {
            $responseData = [
                'status' => false,
                'message' => 'Đổi mật khẩu thất bại!',
            ];
            return response()->json($responseData);
        }
        if ($request->new_password !== $request->new_password_confirmation) {
            $responseData = [
                'status' => false,
                'message' => 'mật khẩu không khớp',
            ];
            return response()->json($responseData);
        }

        // $user = Users::where('email', $request->account)->orWhere('mobile', $request->account)->update([
        //     'password' => Hash::make($request->new_password),
        // ]);
        $user = Users::where('id', Auth::guard('public_user')->user()->id)->update([
            'password' => Hash::make($request->new_password),
        ]);

        if ($user) {
            $responseData = [
                'status' => true,
                'message' => 'Đổi mật khẩu thành công!',
            ];

            return response()->json($responseData);
        }

        $responseData = [
            'status' => false,
            'message' => 'Đổi mật khẩu thất bại!',
        ];
        return response()->json($responseData);
    }

    public function reset(Request $request)
    {
        $rules = [
            'password' => 'required',
            'password_confirmation' => 'required',
        ];

        $validator = Validator::make($request->all(), $rules, [], $this->attributes());
        $errors = $validator->messages();

        if ($request->password !== $request->password_confirmation) {
            $errors->add('password_confirmation', 'Mật khẩu xác nhân không chính xác.');
        }

        if ($errors->toArray()) {
            return response()->json(['error' => $errors])->setStatusCode(401);
        }

        if (!$request->has('_validate')) {
            $user = $this->getApiUser();
            $uid = $user->id;
            $password = Hash::make($request->password);
            try {
                switch ($user->type) {
                    case "user":
                        BoUser::where("id", $uid)->update([
                            "password" => trim($password),
                            'remember_token' => Str::random(60),
                        ]);
                        break;
                    case "customer":
                        BoCustomer::where("id", $uid)->update([
                            "cb_password" => trim($password),
                        ]);
                        break;
                    case "partner":
                        UserPartner::where("id", $uid)->update([
                            "password" => trim($password),
                            'remember_token' => Str::random(60),
                        ]);
                        break;
                    default:
                        break;
                }

                return response()->json([
                    'msg' => 'Thay đổi mật khẩu thành công.',
                ]);
            } catch (Exception $e) {
                return response()->json([
                    'error' => $e
                ])->setStatusCode(500);
            }
        }
    }
}
