<?php

namespace App\Http\Controllers\Fcm;

use App\Http\Controllers\BuildingController;
use App\Repositories\Fcm\FcmRespository;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class FcmController extends BuildingController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    private $model;
    public function __construct(FcmRespository $model,Request $request)
    {
        $this->model = $model;
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
    public function create()
    {

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
    public function ajaxAddDevice(Request $request)
    {
        if(!Auth::user()){
            return redirect()->route('admin.auth.form');
        }
        $check = $this->model->checkFcmUser(Auth::user()->id,$request->id,'banquanly');
        if($check){
            $action = $this->model->updateToken(Auth::user()->id,$request->id,$request->token,'banquanly');
        }else{
            $action = $this->model->newToken(Auth::user()->id,$request->id,$request->token,'banquanly');
        }
        if($action){
            return response()->json(['message'=>'update token thành công']);
        }
        return response()->json(['message'=>'update token không thành công']);
    }
}
