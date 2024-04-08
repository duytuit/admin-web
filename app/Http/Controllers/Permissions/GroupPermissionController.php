<?php

namespace App\Http\Controllers\Permissions;

use App\Http\Controllers\BuildingController;
use App\Repositories\Permissions\GroupsPermissionRepository;
use App\Repositories\Permissions\ModuleRepository;
use App\Repositories\Permissions\PermissionsRepository;
use App\Repositories\Permissions\PermissionTypeRepository;
use App\Repositories\Permissions\UserPermissionRepository;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cookie;
// use Redis;
use Illuminate\Support\Facades\Redis;


class GroupPermissionController extends BuildingController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    private $modelGroup;

    public function __construct(PermissionsRepository $model,GroupsPermissionRepository $modelGroup,Request $request)
    {
       // $this->middleware('auth', ['except'=>[]]);
        //$this->middleware('route_permision');

        $this->model = $model;
        $this->modelGroup = $modelGroup;
        parent::__construct($request);
    }

    public function index(Request $request)
    {
        $data['meta_title'] = 'Group Permission';
        $data['per_page'] = Cookie::get('per_page', 20);
        $data['groups'] = $this->modelGroup->searchBy($request,[],$data['per_page'],$this->building_active_id);
        $data_search = [
            'name'        => '',
            'create_by'         => '',
            'update_by'          => ''
        ];
        $data['data_search'] = $request->data_search ?: $data_search;
        $data['data_search']['name'] = $request->name;
        $data['data_search']['create_by'] = $request->create_by;
        $data['data_search']['update_by'] = $request->update_by;
//        dd($data);
        return view('system.group-permission.index', $data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $data['meta_title'] = "Thêm mới nhóm quyền";
        $data['create_by'] = \Auth::User()->id;
        return view('system.group-permission.create', $data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = $request->validate( [
            'name'     => 'required|max:254'
        ]);
        $input = $request->only(['name', 'description','create_by','update_by']);
        $input = array_merge($input,['bdc_building_id'=>$this->building_active_id]);
        if($request->id){
            $store = $this->modelGroup->update($input,$request->id,'id');
            if(!$store){
                return redirect()->route('admin.system.group_permission.index')->with('error', 'thay đổi nhóm quyền không thành công!');
            }
            return redirect()->route('admin.system.group_permission.edit',['id'=>$request->id])->with('success', "Thay đổi nhóm quyền thành công.");
        }
        $store = $this->modelGroup->create($input);
        if(!$store){
            return redirect()->route('admin.system.group_permission.index')->with('error', 'Tạo nhóm quyền không thành công!');
        }
        return redirect()->route('admin.system.group_permission.edit',['id'=>$store->id])->with('success', "Tạo mới nhóm quyền thành công.");
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
    public function edit($id,ModuleRepository $moduleRepository, PermissionTypeRepository $type)
    {
        $data['meta_title'] = "Thay đổi nhóm quyền";
        $data['active_module'] = Cookie::get('active_module_permission_groups', $moduleRepository->first()->id);
        $data['data'] = $moduleRepository->all();
        //dd($data['data']);
        $data['id'] = $id;
        $data['permissionGroups'] = $this->modelGroup->findPermission($id);
        $data['group'] = $this->modelGroup->getOne($id);
        $data['types'] = $type->all(['id','name','description']);
        $data['update_by'] = \Auth::User()->id;
//        dd($data['permissionGroups']);
        return view('system.group-permission.create', $data);
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
        $this->modelGroup->delete(['id'=>$id]);
        return back()->with('success','Xóa nhóm quyền thành công');
    }
    public function updatePermission($id, Request $request,  UserPermissionRepository $permissionRepository)
    {

        $this->modelGroup->updatePermission($id, $request->ids, $request->module_id);
        Cookie::queue('active_module_permission_groups', $request->module_id , 60 * 24 * 30);

        $keys = Redis::keys('*_DXMB_USER_PERMISION*');
        foreach ($keys as $key) {
            Redis::del($key);
        }
        $keys = Redis::keys('*_DXMB_GROUP_PERMISION*');
        foreach ($keys as $key) {
            Redis::del($key);
        }
        
        \Cache::store('redis')->set( env('REDIS_PREFIX') .$id.'_DXMB_GROUP_PERMISION', null );

        $dataResponse = [
            'success' => true,
            'message' => 'Cập nhật quyền cho nhóm thành công!'
        ];
        return response()->json($dataResponse);
    }
}
