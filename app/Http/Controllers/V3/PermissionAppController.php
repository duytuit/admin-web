<?php

namespace App\Http\Controllers\V3;

use App\Http\Controllers\BuildingController;
use App\Http\Controllers\Controller;
use App\Http\Requests\V3\PermissionRequest;
use App\Models\V3\Role;
use App\Repositories\V3\PermissionRepository\PermissionRepository;
use App\Repositories\V3\RoleRepository\RoleRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PermissionAppController extends BuildingController
{

    /** @var PermissionRepository $repository */
    protected $repository;
    /** @var RoleRepository $roleRepository */
    protected $roleRepository;

    /**
     * UserController constructor.
     * @param PermissionRepository $permissionRepository
     * @param RoleRepository $roleRepository
     * @param Request $request
     * @throws \Exception
     */
    public function __construct(PermissionRepository $permissionRepository,
                                RoleRepository $roleRepository,
                                Request $request)
    {
        $this->repository = $permissionRepository;
        $this->roleRepository = $roleRepository;
        parent::__construct($request);
    }

    public function index()
    {
        $permissions = $this->repository->getAllPermisisonApp();

        $data = [
            'permissions'=>$permissions,
            'active_module'=>347,
            'meta_title'=>"QL Quyền app",
            'menu_app'=>true
        ];

        return view('v3.permission.index',$data);

    }

    public function create()
    {
        $modules = $this->repository->getMenuAppParentPermission();

        $data = [
            'meta_title' => "Thêm quyền mới",
            'modules'=>$modules,
            'menu_app'=>true
        ];

        return view('v3.permission.create',$data);
    }

    public function store(PermissionRequest $request)
    {

        $data = [
            'display_name'=>$request->get('display_name'),
            'name'=>Str::slug($request->get('name')),
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

        $permission = $this->repository->create($data);
        
        $consoles = [
            'edit'=>"Sửa",
            'add'=>"Thêm",
            'delete'=>"Xóa",
            'view'=>"Xem"
        ];
        
        foreach ($consoles as $key=>$console) {
            $this->repository->create([
                'name'=> $data['name'].".".$key,
                'display_name'=>$console,
                'guard_name'=>'v3_web',
                'parent_id'=>$permission->getKey(),
                'status'=>1,
                'order'=>0
            ]);
        }


        return redirect()->route('admin.v3.permission-app.index');

    }

    public function edit($permission_id)
    {
        $modules = $this->repository->getMenuAppParentPermission();
        $permisison = $this->repository->findById($permission_id);

        $roles = $this->roleRepository->getRolesByBuildingId($this->building_active_id);

        $permissionNames = $this->repository->getPermissionChildren($permission_id);

        foreach ($roles as $key => $role) {
            foreach ($permissionNames as $permissionName) {
                $roles[$key][$permissionName['name']] = array_search($permissionName['name'],$role['permission_names'])!==false;
            }
        }

        $data = [
            'meta_title' => "Thêm quyền mới",
            'modules'=>$modules,
            'permission'=>$permisison,
            'menu_app'=>true,
            'roles'=>$roles,
            'permissionNames'=>$permissionNames,
        ];

        return view('v3.permission-app.edit',$data);
    }

    public function update(PermissionRequest $request, $permission_id)
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

        return redirect()->route('admin.v3.permission-app.index');

    }

    public function destroy($permission_id)
    {
        try {
            $this->repository->forceDelete($permission_id);
        } catch (Exception $e) {
        }

        return redirect()->route('admin.v3.permission-app.index');
    }

    public function assignRole(Request $request)
    {
        $rolePermission = $request->get('rolePermission');

        $rolePermissions = \GuzzleHttp\json_decode($rolePermission);

        $roleIds = [];
        $listRole = [];

        foreach ($rolePermissions as $role) {
            if (array_search($role->roleId, $roleIds)===false) {
                array_push($roleIds, $role->roleId);
            }
        }

        foreach ($roleIds as $role) {
            $perGive = [];
            $perRemo = [];
            foreach ($rolePermissions as $ro) {
                if ($ro->roleId == $role) {
                    if ($ro->value) {
                        array_push($perGive,$ro->name);
                    }
                    else {
                        array_push($perRemo,$ro->name);
                    }
                }
            }
            array_push($listRole,[
                $role => [
                    'perGive'=>$perGive,
                    'perRemo'=>$perRemo
                ]
            ]);

            $roleObject = $this->roleRepository->query()
                ->where('id',$role)
                ->first();

            $roleObject->givePermissionTo($perGive);
            $roleObject->revokePermissionTo($perRemo);
        }

        return [
            "sucess"=>"Data"
        ];

    }

}