<?php

namespace App\Http\Controllers\V3;

use App\Http\Controllers\Controller;
use App\Http\Requests\V3\PermissionRequest;
use App\Repositories\V3\PermissionRepository\PermissionRepository;
use Illuminate\Http\Request;

//use App\Repositories\PermissionRepository\PubModuleRepository;

class PermissionController extends Controller
{
    /** @var PermissionRepository $repository */
    protected $repository;

    /**
     * UserController constructor.
     * @param PermissionRepository $permissionRepository
     */
    public function __construct(PermissionRepository $permissionRepository)
    {
        $this->repository = $permissionRepository;
    }

    public function index()
    {
        $permissions = $this->repository->getAllPermissionWeb();

        $data = [
            'permissions'=>$permissions,
            'active_module'=>-1,
            'meta_title'=>"QL Quyền"
        ];

//        $pubModuleRepo = app(PubModuleRepository::class);
//
//        $modules = $pubModuleRepo->getAllModule();
//
//        foreach ($modules as $module) {
//            if (!empty($module['permissions'])) {
//                $data = [
//                    'name'=>'',
//                    'guard_name'=>'web',
//                    'display_name'=>$module['name'],
//                    'description'=>$module['description'],
//                    'icon'=>$module['icon_web'],
//                    'status'=>$module['type'],
//                    'url'=>'',
//                    'model'=>'',
//                    'order'=>0,
//                    'has_menu'=>1
//                ];
//                $dataPermission = $this->repository->create($data);
//
//                if (!empty($dataPermission) && $module['permissions']) {
//                    foreach ($module['permissions'] as $permission) {
//                        $dataM = [
//                            'name'=>$permission['route_name'],
//                            'guard_name'=>'web',
//                            'display_name'=>$permission['title'],
//                            'description'=>$permission['title'],
//                            'icon'=>$permission['icon_web'],
//                            'status'=>$permission['type'],
//                            'url'=>$permission['link'],
//                            'model'=>'',
//                            'order'=>$permission['position'],
//                            'parent_id'=>$dataPermission->getKey(),
//                            'has_menu'=>$permission['has_menu'],
//                        ];
//                        $this->repository->create($dataM);
//                    }
//                }
//
//            }
//        }

        return view('v3.permission.index',$data);
    }

    public function create()
    {

        $modules = $this->repository->getMenuParentPermission();

        $data = [
            'meta_title' => "Thêm quyền mới",
            'modules'=>$modules
        ];

        return view('v3.permission.create',$data);
    }

    public function store(PermissionRequest $request)
    {

        $data = [
            'display_name'=>$request->get('display_name'),
            'name'=>$request->get('name'),
            'description'=>$request->get('display_name'),
            'parent_id'=>$request->get('parent_id'),
            'icon' => $request->get('icon_web'),
            'has_menu'=>$request->get('has_menu'),
            'icon_app'=>$request->get('icon_app'),
            'display_app'=>$request->get('display_app'),
            'guard_name'=>'v3_web',
            'status'=>1,
            'order'=>0
        ];

        $this->repository->create($data);

        return redirect()->route('admin.v3.permission.index');

    }

    public function edit($permission_id)
    {
        $modules = $this->repository->getMenuParentPermission();
        $permisison = $this->repository->findById($permission_id);

        $data = [
            'meta_title' => "Thêm quyền mới",
            'modules'=>$modules,
            'permission'=>$permisison
        ];

        return view('v3.permission.edit',$data);
    }

    public function update(Request $request, $permission_id)
    {
        $data = [
            'display_name'=>$request->get('display_name'),
            'name'=>$request->get('name'),
            'description'=>$request->get('display_name'),
            'parent_id'=>$request->get('parent_id'),
            'icon' => $request->get('icon_web'),
            'has_menu'=>$request->get('has_menu'),
            'icon_app'=>$request->get('icon_app'),
            'display_app'=>$request->get('display_app'),
            'guard_name'=>'v3_web',
            'status'=>1,
            'order'=>0
        ];

        $this->repository->updateOrCreate(['id'=>$permission_id],$data);

        return redirect()->route('admin.v3.permission.index');

    }

    public function destroy($permission_id)
    {
        try {
            $this->repository->forceDelete($permission_id);
        } catch (Exception $e) {
        }

        return redirect()->route('admin.v3.permission.index');
    }
}