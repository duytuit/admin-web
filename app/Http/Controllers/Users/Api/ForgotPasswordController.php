<?php

namespace App\Http\Controllers\Users\Api;

use App\Http\Controllers\BuildingController;
use App\Models\PublicUser\Users;
use App\Repositories\PublicUsers\PublicUsersRespository;
use App\Services\ServiceSendMail;
use App\Services\ServiceSendMailV2;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Campain;
use App\Models\PublicUser\V2\User;
use App\Models\SentStatus;
use Illuminate\Support\Facades\Hash;

class ForgotPasswordController extends BuildingController
{
    use ApiResponse;
    const FORGOT = 3;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    const DUPLICATE     = 19999;
    const LOGIN_FAIL    = 10000;
    private $model;
    public function __construct(PublicUsersRespository $model,Request $request)
    {
        $this->model = $model;
        //
        parent::__construct($request);
    }
    public function index()
    {

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
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
    public function forgotPassword(Request $request)
    {
        $password = $this->getToken(6);
        if ($request->email == '') {
            return $this->responseError('Không có dữ liệu', 204);
        }
        if (!filter_var($request->email, FILTER_VALIDATE_EMAIL)) {
            if ($this->model->checkPhone($request->email)){
                $update = $this->model->resetPassByPhone($request->email,$password);
                if($update){
                    return $this->responseSuccess(['password'=>'123456'],'Số điện thoại của bạn chính xác, mật khẩu reset là '.$password.' Mời đăng nhập lại');
                }
            };
            return $this->responseError('Không có dữ liệu', 204);
        }

        if($this->model->checkExit($request->email)){
            $update = $this->model->resetPass($request->email,$password);
            if($update){
                $this->sendMail($request->email,$password);
                return $this->responseSuccess(['password'=>$password],'Email của bạn chính xác, Mật khẩu reset là '.$password.' Xin vui lòng kiểm tra email và đăng nhập lại');
            }else{
                return $this->responseError('Lỗi đường truyền', 404);
            }
        }
        return $this->responseError('Email không có trên hệ thống', 400);
    }

    public function forgotPassword_v2(Request $request)
    {
        $password = $this->getToken(6);
        if ($request->email == '') {
            return $this->responseError('Không có dữ liệu', 204);
        }
        if (!filter_var($request->email, FILTER_VALIDATE_EMAIL)) {
            $user = User::checkEmailPhone($request->email);
            if ($user){
                $user->pword = Hash::make($password);
                $user->save();
                if($user){
                    return $this->responseSuccess(['password'=>$password],'Số điện thoại của bạn chính xác, mật khẩu reset là '.$password.' Mời đăng nhập lại');
                }
            };
            return $this->responseError('Không có dữ liệu', 204);
        }else{
            $user = User::checkEmailPhone($request->email);
            if ($user){
                $user->pword = Hash::make($password);
                $user->save();
                if($user){
                    return $this->responseSuccess(['password'=>$password],'Số điện thoại của bạn chính xác, mật khẩu reset là '.$password.' Mời đăng nhập lại');
                }
            };
            return $this->responseError('Không có dữ liệu', 204);
        }

        return $this->responseError('Email không có trên hệ thống', 400);
    }

    public function sendMail($email,$pass)
    {
        $user = $this->model->getUserProfile($email);
        $total = ['email'=>1, 'app'=> 0, 'sms'=> 0];
        $campain = Campain::updateOrCreateCampain('Gửi email forgot password cho '.$email, config('typeCampain.FORGOT'), null, $total, @$user->profileAll->bdc_building_id, 0, 0);

         
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

}
