<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\BoCustomer;
use App\Models\BoUser;
use App\Models\PasswordReset;
use App\Models\UserPartner;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Validator;

class ResetPasswordController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    public function attributes()
    {
        return [
            'password'              => 'Mật khẩu',
            'password_confirmation' => 'Mật khẩu xác nhận',
        ];
    }

    /**
     * Display the password reset view for the given token.
     *
     * If no token is present, display the link request form.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string|null  $token
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function showResetForm(Request $request, $token = null)
    {
        $passwordReset = PasswordReset::where('token', $token)->firstOrFail();
        if ($passwordReset) {
            return view('auth.admin.passwords.reset')->with(
                ['token' => $token, 'email' => $request->email]
            );
        }
    }

    public function reset(Request $request)
    {
        $rules = [
            'password'              => 'required|min:6|confirmed'
        ];

        $validator = Validator::make($request->all(), $rules, [], $this->attributes());
        $errors    = $validator->messages();

        if ($request->password !== $request->password_confirmation) {
            $errors->add('password_confirmation', 'Mật khẩu xác nhân không chính xác.');
        }
        if ($errors->toArray()) {
            return back()->with(['errors' => $errors])->withInput();
        }

        if (!$request->has('_validate')) {

            $token         = $request->input('token', '');
            $passwordReset = PasswordReset::where('token', $token)->firstOrFail();

            if (Carbon::parse($passwordReset->updated_at)->addMinutes(720)->isPast()) {
                $passwordReset->delete();

                return response()->json([
                    'message' => 'Phiên làm việc đã hết hạn.',
                ], 422);
            }

            $user = BoUser::where('ub_email', $passwordReset->email)->first();
            $data  = ['password'=>Hash::make($request->password)];
            $url = 'admin.auth.login';

            if (!$user) {
                $user = BoCustomer::where('cb_email', $passwordReset->email)->first();
                $data  = ['cb_password'=>Hash::make($request->password)];
                $url = 'customer.forgot';
            }

            if (!$user) {
                $user = UserPartner::where('email', $passwordReset->email)->first();
                $data  = ['password'=>Hash::make($request->password)];
                $url = 'admin.auth.login';;
            }

            if ($user) {
                $user->update($data);
                $passwordReset->delete();
            }


            return redirect()->route($url)->with('success', 'Reset mật khẩu thành công, vui lòng đăng nhập.');
        }

    }
    public function success()
    {
        return view('auth.mobile-forgot');
    }
}
