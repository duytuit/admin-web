<?php

namespace App\Http\Controllers\Permissions;

use App\Http\Controllers\Controller;
use App\Models\Permissions\Permission;
use App\Repositories\PaymentInfo\PaymentInfoRepository;
use App\Repositories\Permissions\PermissionTypeRepository;
use App\Repositories\Permissions\UserPermissionRepository;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cookie;
use Validator;
use App\Services\AppConfig;
use App\Repositories\Permissions\PermissionsRepository;
use App\Repositories\Permissions\ModuleRepository;
use App\Util\Debug\Log;

class PermissionController extends Controller
{

    public function __construct(PermissionsRepository $model)
    {
       // $this->middleware('auth', ['except'=>[]]);
        //$this->middleware('route_permision');

        $this->model = $model;
    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(ModuleRepository $moduleRepository, PermissionTypeRepository $type)
    {
        $data['active_module'] = Cookie::get('active_module', $moduleRepository->first()->id);
        $data['meta_title'] = 'Permission';
        $data['types'] = $type->all(['id','name','description']);
        $data['data'] = $moduleRepository->all();
        return view('system.permission.index', $data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(ModuleRepository $menu, PermissionTypeRepository $type)
    {
        $rs = [];
        foreach ($menu->all() as $key => $value) {
            $rs[$value->id] = $value->name;
        }
        $types = [];
        foreach ($type->all(['id','name','description']) as $key => $value) {
            $types[$value->id] = $value->name;
        }
        return view('system.permission.create')->with('meta_title', 'Add new Permission')->with('menus', $rs)->with('types', $types);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, UserPermissionRepository $permissionRepository)
    {
        $validatedData = $request->validate([
            'title' => 'required',
            'route_name' => 'required',
            'icon_web' => 'required',
        ]);

        $data = $request->only('title', 'route_name', 'link', 'module_id', 'has_menu', 'icon_web','type');
        Cookie::queue('active_module', $request->module_id , 60 * 24 * 30);
        $this->model->create($data);
        return redirect()->route('admin.system.permission.index')->with('success', 'Thêm mới quyền thành công');
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

    public function check_index_position(Request $request)
    {
        foreach ($request->order as $order) {
            $order = (object)$order;
            $permission = Permission::find($order->id);
            if($permission && @$order->position){
                $permission->position = $order->position;
                $permission->save();
            }
         }
         return response('Update Successfully.', 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id, ModuleRepository $menu, PermissionTypeRepository $type)
    {
        $rs = [];
        foreach ($menu->all() as $key => $value) {
            $rs[$value->id] = $value->name;
        }
        $data['menus'] = $rs;
        $types = [];
        foreach ($type->all(['id','name','description']) as $key => $value) {
            $types[$value->id] = $value->name;
        }
        $data['types'] = $types;
        $data['item'] = $this->model->findPermission($id);
        $data['meta_title'] = 'Sửa quyền';
        return view('system.permission.edit', $data);
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
        $validatedData = $request->validate([
            'title' => 'required',
            'route_name' => 'required',
            'icon_web' => 'required',
        ]);
        Cookie::queue('active_module', $request->module_id , 60 * 24 * 30);
        $data = $request->only('title', 'route_name', 'link', 'module_id', 'has_menu', 'icon_web','type');
        $this->model->update($data, $id);
        return redirect()->route('admin.system.permission.index')->with('success', 'Chỉnh sửa quyền thành công');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id, UserPermissionRepository $permissionRepository)
    {
        $this->model->findPermission($id)->delete();
        return response()->json([
            'success'=> true,
            'message' => 'Xóa thành công'
        ]);
    }
}