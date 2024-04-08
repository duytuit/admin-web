<?php

namespace App\Http\Controllers\V3;

use App\Commons\Api;
use App\Http\Controllers\Controller;
use App\Models\V3\User;
use App\Repositories\V3\UserRepository\UserRepository;
use GuzzleHttp\Client;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use App\Commons\Helper;

class AuthController extends Controller
{

    use AuthenticatesUsers;

    protected $redirectTo = '/admin/user';

    public function showLoginForm()
    {

        $data = [
            'meta_title'=>"Đăng nhập hệ thống"
        ];

        return view('v3.auth.login',$data);
    }

    public function login(Request $request)
    {
        $this->validate($request,[
            'email'=>'required',
            'password' => 'required|min:6',
        ]);

        $email = $request->get('email');
        $password = $request->get('password');

//        $headers = [
//            'User-Agent' => 'testing/1.0',
//            'Accept'     => 'application/json',
//            'ClientId' => env('CLIENT_ID',""),
//            'ClientSecret'=> env('CLIENT_SECRET',"")
//        ];
//
//        $client = new \GuzzleHttp\Client();
//        $res = $client->request('POST', env('DOMAIN_API',"")."/api/v1/admin/login", [
//            'headers' => $headers,
//            'form_params'=>[
//                'account' => $email,
//                'password'=>$password,
//            ]
//        ]);
//
//        $response = \GuzzleHttp\json_decode($res->getBody()->getContents());

        $data = [
            'account' => $email,
            'password'=>$password,
        ];

        $response = Api::POST("/api/v1/admin/login", $data, false);

        if ($response->success === false) {
            return redirect()->route('admin.auth.login');
        }
        else {
            $user = $response->data;

            $userObj = new User();

            $userObj->uuid = $user->id;
            $userObj->display_name = $user->name;
            $userObj->email = $user->email;
//            session()->push("token",$user->token);
            session(['token'=>$user->token]);
            Cookie::queue('TOKEN',$user->token,60*12*356);
            Helper::setToken($user->id,$user->token);

//            $userObj = app(UserRepository::class)->firstOrCreate(['uuid'=>$user->id],[
//                'uuid'=>$user->id,
//                'display_name'=>$user->name,
//                'email'=>$user->email
//            ]);

            DB::table('v3_users')->updateOrInsert(['email'=>$user->email],[
                'uuid'=>$user->id,
                'display_name'=>$user->name,
                'email'=>$user->email
            ]);

            Auth::guard('v3_web')->login($userObj);
            return redirect()->route('admin.home');


//        echo '<pre>',var_dump($userObj->toArray()),'</pre>';
//
//        session()->push("token",$user->token);
//
//        var_dump(session()->get('token'));
//        die();

//        if(Auth::guard('v3_web')->attempt(['email'=>$email,'password'=>$password])) {
//            return redirect()->route('admin.home');
//        }
//        else {
//            return redirect()->route('admin.auth.login');
//        }

        }
//        die();

//        $user = $response->data;
//
//        $userObj = new User();
//
//        $userObj->uuid = $user->id;
//        $userObj->display_name = $user->name;
//        $userObj->email = $user->email;
//
////        echo '<pre>',var_dump($userObj->toArray()),'</pre>';
////
////        session()->push("token",$user->token);
////
////        var_dump(session()->get('token'));
////        die();

//        if(Auth::guard('v3_web')->attempt(['email'=>$email,'password'=>$password])) {
//            return redirect()->route('admin.home');
//        }
//        else {
//            return redirect()->route('admin.auth.login');
//        }
//        Auth::guard('v3_web')->login($userObj);
//        return redirect()->route('admin.home');
////        die();
////        if(Auth::guard('v3_web')->login($userObj)) {
////            return redirect()->route('admin.home');
////        }
////        else {
////            return redirect()->route('admin.auth.login');
////        }

    }

    public function logout()
    {

        if(Helper::getToken(\Auth::user()->uuid)){
            Helper::delToken(\Auth::user()->uuid);
        }
        session()->flush();
        Auth::logout();
        return redirect()->route('admin.auth.login');
    }

}
