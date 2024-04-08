<?php

namespace App\Http\Controllers\Auth\Api;

use App\Http\Controllers\Controller;
use App\Models\BoCustomer;
use App\Models\Fcm;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Validator;

class PassportController extends Controller
{
    public function attributes()
    {
        return [
            'name'     => 'Tên khách hàng',
            'phone'    => 'Số điện thoại',
            'password' => 'Mật khẩu',
        ];
    }
    /**
     * Handles Login Request
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        // Validate the form data
        $this->validate($request, [
            'username' => 'required',
            'password' => 'required',
        ]);

        $username = $request->username;
        $password = $request->password;
        $remember = $request->remember;

        $token = null;

        if (Auth::guard('customer')->attempt([
            'cb_account' => $username,
            'password'   => $password,
            'status'     => 1], $remember)) {
            $user  = Auth::guard('customer')->user();
            $token = $user->createToken('customer');

        } elseif (Auth::guard('customer')->attempt([
            'cb_phone' => $username,
            'password' => $password,
            'status'   => 1], $remember)) {
            $user  = Auth::guard('customer')->user();
            $token = $user->createToken('customer');

        } elseif (Auth::guard('customer')->attempt([
            'cb_email' => $username,
            'password' => $password,
            'status'   => 1], $remember)) {
            $user  = Auth::guard('customer')->user();
            $token = $user->createToken('customer');
        }

        if ($request->remember_me) {
            $token->expires_at = Carbon::now()->addWeeks(1);
        }

        if ($token) {
            $expires_in = $token->token->expires_at->diffInSeconds(Carbon::now());
            return response()->json([
                'access_token' => $token->accessToken,
                'expires_in'   => $expires_in,
                'user'         => [
                    'id'       => $user->id,
                    'uid'      => $user->uid,
                    'username' => $user->username,
                    'name'     => $user->name,
                    'email'    => $user->email,
                    'phone'    => $user->phone,
                    'avatar'   => $user->avatar,
                ],
            ]);
        } else {
            return response()->json(['error' => 'UnAuthorised'], 401);
        }
    }

    public function logout(Request $request)
    {
        $user  = $request->user();
        $token = $user->token();
        $token->revoke();

        $fcm = Fcm::where('user_id', $user->id)
            ->where('device_id', $request->device_id)
            ->delete();

        $response = 'You have been succesfully logged out!';
        return response($response, 200);

    }

    /**
     * Returns Authenticated User Details
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function user(Request $request)
    {
        $bearer = $request->bearerToken();
        $jti    = (new \Lcobucci\JWT\Parser())->parse($bearer)->getHeader('jti');

        $access = \Laravel\Passport\Token::where('id', $jti)->first();
        $guard  = $access->name;

        $provider = Config::get('auth.guards.' . $guard . '.provider');
        $model    = Config::get('auth.providers.' . $provider . '.model');

        $class = new $model();
        $user  = $class->find($access->user_id);

        return response()->json([
            'user' => [
                'id'       => $user->id,
                'uid'      => $user->uid,
                'username' => $user->username,
                'name'     => $user->name,
                'email'    => $user->email,
                'phone'    => $user->phone,
                'avatar'   => $user->avatar,
            ],
        ]);
    }

    /**
     * Đăng ký tài khoản của khách hàng
     *
     * @param Request $request
     * @return void
     */
    public function add_customer(Request $request)
    {
        $rules = [
            'name'     => 'required',
            'phone'    => 'required',
            'password' => 'required',
        ];

        $validator = Validator::make($request->all(), $rules, [], $this->attributes());
        $errors    = $validator->messages();

        // $customer = BoCustomer::is_exist(['cb_phone' => $request->cb_phone, 'project_id' => $request->project_id, 'cb_id' => '']);
        // if ($customer === true) {
        //     $errors->add('is_customer', 'Tài khoản đã tồn tại.');
        // }

        if ($errors->toArray()) {
            return response()->json(['error' => $errors])->setStatusCode(401);
        }

        if (!$request->has('_validate')) {
            $data['status'] = $request->input('status', 1);

            $params = [
                'cb_id'       => time(),
                'cb_name'     => $request->name,
                'cb_phone'    => $request->phone,
                'cb_password' => Hash::make($request->password),
                'cb_source'   => 'app',
            ];

            $customer = new BoCustomer();
            $customer->fill($params);
            $customer->save();

            return response()->json(['msg' => 'Bạn đã đăng ký thành công.']);
        }
    }
}
