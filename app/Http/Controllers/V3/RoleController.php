<?php

namespace App\Http\Controllers\V3;

use App\Commons\Api;
use App\Commons\Helper;
use App\Http\Controllers\BuildingController;
use App\Http\Requests\BdcUser\RoleRequest;
use App\Models\V3\Role;
use App\Models\V3\User;
use App\Repositories\V3\PermissionRepository\PermissionRepository;
use App\Repositories\V3\RoleRepository\RoleRepository;
use App\Repositories\V3\RoleTypeRepository\RoleTypeRepository;
use App\Repositories\V3\UserRepository\UserRepository;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Exception;

class RoleController extends BuildingController
{
    /** @var RoleRepository $repository */
    protected $repository;
    /** @var UserRepository $userRepository */
    protected $userRepository;
    /** @var PermissionRepository $permissionRepository */
    protected $permissionRepository;
    /** @var RoleTypeRepository $roleTypeRepository */
    protected $roleTypeRepository;

    /**
     * RoleController constructor.
     * @param RoleRepository $repository
     * @param UserRepository $userRepository
     * @param PermissionRepository $permissionRepository
     * @param RoleTypeRepository $roleTypeRepository
     * @param Request $request
     * @throws Exception
     */
    public function __construct(RoleRepository $repository,
                                UserRepository $userRepository,
                                PermissionRepository $permissionRepository,
                                RoleTypeRepository $roleTypeRepository,
                                Request $request)
    {
        $this->repository = $repository;
        $this->userRepository = $userRepository;
        $this->permissionRepository = $permissionRepository;
        $this->roleTypeRepository = $roleTypeRepository;
        parent::__construct($request);
    }

    public function index()
    {

        $roles = $this->repository->getRolesByBuildingId($this->building_active_id);

        $data = [
            'roles'=>$roles,
            'meta_title'=>"QL vai trò"
        ];

        return view('v3.role.index',$data);
    }

    public function create()
    {
        $data['meta_title'] = "Thêm mới vai trò";

        $roleTypes = $this->roleTypeRepository->getRoleTypeCommon($this->building_active_id);

        $data = [
            'meta_title'=> "Thêm mới vai trò",
            'role_types'=>$roleTypes
        ];

        return view('v3.role.create',$data);

    }

    /**
     * @param RoleRequest $request
     * @return RedirectResponse
     */
    public function store(RoleRequest $request): RedirectResponse
    {
        $name = $request->get('name');
        $description = $request->get('description');
        $role_type_id = $request->get('role_type_id');

        $data = [
            'name'=>Helper::genUuid("##BDC",32),
            'display_name'=>$name,
            'description'=>$description,
            'guard_name' => "v3_web",
            'building_id'=>$this->building_active_id,
            'role_type_id'=>$role_type_id
        ];

        $role = $this->repository->create($data);

        return redirect()->route('admin.v3.role.edit',$role->getKey());
    }

    public function edit($role_id)
    {
        $permisisonRepo = app(PermissionRepository::class);

        $permisisons = $permisisonRepo->getPermisisonByRoleId($role_id);

        /** @var Role $role */
        $role = $this->repository->findById($role_id)->with('role_type');

        $role = $this->repository->query()
            ->where('id', $role_id)
            ->with(['roleType'=>function($q){
                $q->first();
            }])
            ->first();

//        echo '<pre>',var_dump($role->roleType->display_name),'</pre>';
//        die();

//        $users = $this->userRepository->getUsersNotHaveRole($this->building_active_id);

        $permissionApp = $permisisonRepo->getPermissionRoleApp($role_id);

        $responseUser = Api::GET("/api/v1/admin/list",[
            'building_id'=>$this->building_active_id
        ], true);

        if ($responseUser->success === false) {
            return redirect()->route('admin.auth.login');
        }

        $users = $responseUser->data;
//        var_dump($role->name);

        $roleTypes = $this->roleTypeRepository->getRoleTypeCommon($this->building_active_id);

        foreach ($users as $user) {
            $us = new User();
            $us->uuid = $user->user_id;
            $user->checked = $us->hasRole($role->name);
        }

        $data = [
            'permissions'=>$permisisons,
            'role_id'=>$role_id,
            'role'=>$role,
            'meta_title'=>"Cập nhật vai trò",
            'active_module'=>-1,
            'users'=>$users,
            'permissionApp'=>$permissionApp,
            'role_types'=>$roleTypes
        ];

        return view('v3.role.edit', $data);
    }

    public function update(Request $request,$role_id)
    {
        $permissions = $request->get('permisisons');
        $permissions = json_decode($permissions);

        $display_name = $request->get('display_name');
        $description = $request->get('description');
        $role_type_id = $request->get('role_type_id');

        /**
         * @var Role $role
         */
        $role = $this->repository->findById($role_id);

        if (!empty($display_name) && !empty($role_type_id)) {
            $role->update([
                'display_name'=>$display_name,
                'description'=>$description,
                'role_type_id'=>$role_type_id
            ]);

            return redirect()->route('admin.v3.role.index');
        }
        else {
            $permissionGive = [];
            $permissionRevoke = [];

            if (!empty($permissions)) {
                foreach ($permissions as $key => $value) {
                    if (!empty($value->value)) {
                        array_push($permissionGive,$value->name);
                    }
                    else {
                        array_push($permissionRevoke,$value->name);
                    }
                }
            }

            $role->givePermissionTo($permissionGive);
            $role->revokePermissionTo($permissionRevoke);

            return [
                "sucess"=>"Data"
            ];
        }

    }

    public function destroy($role_id): RedirectResponse
    {
        try {
            $this->repository->forceDelete($role_id);
        } catch (Exception $e) {
        }

        return redirect()->route('admin.v3.role.index');
    }

    public function addUser(Request $request, $role_id)
    {
        $uuIds = $request->get('uuids');
        $uuIdsRove = $request->get('uuIdsRove');

        $uuIds = \GuzzleHttp\json_decode($uuIds);
        $uuIdsRove = \GuzzleHttp\json_decode($uuIdsRove);

        $role_name = $this->repository->findById($role_id)->toArray();

        $role_name = $role_name['name'];

        $users = $this->userRepository->query()
            ->whereIn('uuid',$uuIds)
            ->get();
        foreach ($users as $user) {
            $user->assignRole($role_name);
        }

        $usersRevo = $this->userRepository->query()
            ->whereIn('uuid', $uuIdsRove)
            ->get();
        foreach ($usersRevo as $user) {
            $user->removeRole($role_name);
        }

        return [];

    }

}