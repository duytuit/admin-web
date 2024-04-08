<?php

namespace App\Http\Controllers\Users\Api;

use App\Http\Controllers\BuildingController;
use App\Repositories\PublicUsers\PublicUsersProfileRespository;
use App\Repositories\PublicUsers\PublicUsersRespository;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class RegisterController extends BuildingController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

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
    private $modelUserProfile;
    public function __construct(PublicUsersRespository $model,PublicUsersProfileRespository $modelUserProfile,Request $request)
    {
        $this->model = $model;
        $this->modelUserProfile = $modelUserProfile;
        //
        parent::__construct($request);
    }

    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $check = $this->model->checkExit($request->email);
        if($check){
            return $this->responseError('Email này đã có trên hệ thống', 200,[]);
        }
        $insert = $this->model->create(['email'=>$request->email,'password'=>bcrypt($request->password),'status'=>1]);
        if($insert){
            $insert_profile = $this->modelUserProfile->create(['pub_user_id'=>$insert->id,'bdc_building_id'=>1,'gender'=>3,'status'=>1,'type_profile'=>0,'email'=>$request->email,'type'=>2,'app_id'=>'buildingcare','display_name'=>$request->email]);
            if(!$insert_profile){
                return $this->responseError('Tạo profile không thành công', 200,[]);
            }
        }
        if ($insert){
            return $this->responseSuccess([],'Tạo tài khoản thành công');
        }
        return $this->responseError('Tạo tài khoản không thành công', 200,[]);
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
}
