<?php

namespace App\Http\Controllers\Auth\Admin;

use App\Http\Controllers\Controller;
use Illuminate\FoundationAuthAuthenticatesUsers;
use Illuminate\Http\Request;
use App\Commons\Helper;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\FacadesAuth;
use Tymon\JWTAuth\Facades\JWTAuth;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
     */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/admin';

    public function __construct()
    {
        $this->middleware('guest', ['except'=>['logout']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function showLoginForm()
    {
        $data['meta_title'] = 'Đăng nhập hệ thống';

        return view('auth.admin.login', $data);
    }

    public function login(Request $request)
    {

        // Validate the form data
        $this->validate($request, [
            'email' => 'required',
            'password' => 'required|min:6',
        ]);

         $email =  $request->email;
        // che thong chi cho phep dang nhap bang sdt hoac email.
        if( !filter_var($email, FILTER_VALIDATE_EMAIL) ) {
            // $email = $email . "@phone.dxmb";
            if (Auth::guard('backend_public')->attempt([
                'mobile' => $email,
                'password'       =>$request->password
            ],  $request->remember)) {
                $token = JWTAuth::fromUser(Auth::user());
                Helper::setToken(Auth::user()->id,$token);
                return redirect()->route('admin.home');
            }

            // if unsuccessful, then redirect back to the login with the form data
            return redirect()->route('admin.auth.form')->withInput($request->all())->withErrors( ['Đăng nhập thất bại! Số điện thoại hoặc mật khẩu không đúng.']);
        }

        if (Auth::guard('backend_public')->attempt([
            'email' => $email,
            'password'       =>$request->password
        ],  $request->remember)) {
            $token = JWTAuth::fromUser(Auth::user());
            Helper::setToken(Auth::user()->id,$token);
            return redirect()->route('admin.home');
        }
        // if unsuccessful, then redirect back to the login with the form data
        return redirect()->route('admin.auth.form')->withInput($request->all())->withErrors( ['Đăng nhập thất bại! Tài khoản đăng nhập (Email/Số điện thoại) hoặc mật khẩu không đúng.']);
    }

    public function logout()
    {
        if(Auth::user() && Helper::getToken(Auth::user()->id)){
            Helper::delToken(Auth::user()->id);
        }
        Auth::guard('backend_public')->logout();
        return redirect()->route('admin.auth.login');
    }
}
