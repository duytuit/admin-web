<?php

namespace App\Http\Controllers\Department\Api;

use App\Http\Controllers\BuildingController;
use App\Repositories\Department\DepartmentRepository;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class DepartmentAppController extends BuildingController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    use ApiResponse;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    const DUPLICATE     = 19999;
    const LOGIN_FAIL    = 10000;
    private $model;
    public function __construct(DepartmentRepository $model,Request $request)
    {
        // $this->middleware('auth', ['except'=>[]]);
        $this->model = $model;
        //$this->middleware('jwt.auth');
        parent::__construct($request);
    }
    public function index(Request $request)
    {
        $per_page = $request->input('per_page', 10);
        $list = $this->model->listDepartments($request->building_id,$per_page)->toArray();
        if($list['data']){
            return $this->responseSuccess($list['data']);
        }
        return $this->responseError(['Không có dữ liệu.'], self::LOGIN_FAIL );
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
}
