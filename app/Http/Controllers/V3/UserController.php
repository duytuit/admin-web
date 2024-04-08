<?php

namespace App\Http\Controllers\V3;

use App\Commons\Api;
use App\Commons\Helper;
use App\Http\Controllers\BuildingController;
use App\Http\Requests\V3\UserRequest;
use App\Models\V3\Role;
use App\Models\V3\User;
use App\Repositories\V3\RoleRepository\RoleRepository;
use App\Repositories\V3\UserRepository\UserRepository;
use App\Traits\ApiResponse;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Exception;

class UserController extends BuildingController
{

    /**
     * @var UserRepository $repository
    */
    protected $repository;

    /**
     * @var RoleRepository $roleRepository
     */
    protected $roleRepository;

    /**
     * UserController constructor.
     * @param UserRepository $repository
     * @param RoleRepository $roleRepository
     * @param Request $request
     * @throws Exception
     */
    public function __construct(
        UserRepository $repository,
        RoleRepository $roleRepository,
        Request $request
    )
    {
        $this->repository = $repository;
        $this->roleRepository = $roleRepository;
        parent::__construct($request);
    }

    public function index(Request $request)
    {

        $filter['display_name'] = $request->get('name');
        $filter['email'] = $request->get('email');
        $filter['phone'] = $request->get('phone');
        $filter['role'] = $request->get('role');
        $roles = $this->roleRepository->getRolesByBuildingId($this->building_active_id);

        $data['per_page'] = Cookie::get('per_page', 20);

        $building_id = $this->building_active_id;

        $query = $this->repository->query()->whereHas('buildings',function (Builder $query) use ($building_id){
                $query->where('id',$building_id);
            })
            ->with(['roles'=>function($query) use ($building_id) {
                $query->where('building_id',$building_id);
            }]);

        if (!empty($filter['display_name'])) {
            $query->where('display_name', 'like', "%" . $filter['display_name'] . "%");
        }
        if (!empty($filter['email'])) {
            $query->where('email', 'like', "%" . $filter['email'] . "%");
        }
        if (!empty($filter['phone'])) {
            $query->where('phone', 'like', "%" . $filter['phone'] . "%");
        }

        if (!empty($filter['role'])) {
            $query->whereHas('roles',function (Builder $q) use ($filter){
               $q->where('name',$filter['role']);
            });
        }

        $responseUser = Api::GET("/api/v1/admin/list",[
            'building_id'=>$this->building_active_id
        ], true);

        if ($responseUser->success === false) {
            return redirect()->route('admin.auth.login');
        }

        $users = $responseUser->data;

        if (!empty($filter['display_name'])) {
            $users = array_filter($users,function ($user) use ($filter) {
                return stripos($user->name, $filter['display_name']);
            });
        }
        if (!empty($filter['email'])) {
            $users = array_filter($users,function ($user) use ($filter) {
                return stripos($user->email, $filter['email']);
            });
        }
        if (!empty($filter['phone'])) {
            $users = array_filter($users,function ($user) use ($filter) {
                return stripos($user->phone, $filter['phone']);
            });
        }

        foreach ($users as $key => $user) {
            $us = new User();
            $us->uuid = $user->user_id;
            $user->roles = $us->roles()->where(function (Builder $query){
                $query->where('building_id', $this->building_active_id);
            })->get();
        }

        $roleType = DB::table('v3_role_types')->get()->pluck('display_name','id')->toArray();

//        $users = $query->paginate(15);

        if (!empty($filter['role'])) {
            $role_id = $this->roleRepository->query()->where('name',$filter['role'])->first()->getKey();
            if (!empty($role_id)) {
                $uuids = DB::table('v3_user_has_roles')
                    ->where('role_id',$role_id)
                    ->pluck('user_id')
                    ->toArray();
                if (!empty($uuids)) {
                    $users = array_filter($users,function ($user) use ($uuids) {
                        return in_array($user->user_id,$uuids);
                    });
                }
            }
        }

        $data = [
            'meta_title'=>"QL Nhân viên",
            'users'=>$users,
            'filter'=>$filter,
            'roles'=>$roles,
            'roleType'=>$roleType
        ];

        return view('v3.users.index',$data);
    }

    public function create()
    {
        $roles = $this->roleRepository->getRolesByBuildingId($this->building_active_id);

        $data = [
            "meta_title"=>"Thêm nhân viên mới",
            "roles"=>$roles
        ];

        return view("v3.users.create", $data);

    }

    public function store(UserRequest $request): RedirectResponse
    {
        $email = $request->get('email');
        $display_name = $request->get('display_name');
        $phone = $request->get('phone');
        $password = $request->get('password',123456);
        $role_name = $request->get('role_name');

        $checkEmail = $this->repository->findByEmail($email, $this->building_active_id);

        $roles = \GuzzleHttp\json_decode($request->get('roles'));
        $roleIds = \GuzzleHttp\json_decode($request->get('roleIds'));

        if ($checkEmail > 0) {
            return redirect()->route('admin.v3.user.create')->withErrors(['email'=>"Email đã tồn tại trong tòa nhà"]);
        }
        else {
            $data = [
                'uuid'=>Helper::genUuid("##BDC",28),
                'email'=>$email,
                'phone'=>$phone,
                'display_name'=>$display_name,
                'password' => Hash::make($password),
            ];

            $response = Api::POST('/api/v1/admin/register',[
                'email'=>$email,
                'name'=>$display_name,
                'phone'=>$phone,
                'password'=>$password
            ], false);

            if ($response->success) {
                $user = $response->data;

                $us = new User();

                $us->uuid = $user->id;

                var_dump($us->uuid);

                $data =$us->assignRole($roles);
                foreach ($roleIds as $roleId) {
                    DB::table('v3_user_has_roles')->insert([
                        'user_id'=>$us->uuid,
                        'role_id'=>$roleId,
                        'model_type'=>"App\Models\V3\User"
                    ]);
                }


                DB::table('v3_user_has_building')->insert([
                    'user_id'=>$us->uuid,
                    'building_id'=>$this->building_active_id
                ]);

            }
            else {
                $user = $response->data;

                $us = new User();

                $us->uuid = $user->id;

                $data =$us->assignRole($roles);

                foreach ($roleIds as $roleId) {
                    DB::table('v3_user_has_roles')->insert([
                        'user_id'=>$us->uuid,
                        'role_id'=>$roleId,
                        'model_type'=>"App\Models\V3\User"
                    ]);
                }

                DB::table('v3_user_has_building')->insert([
                    'user_id'=>$us->uuid,
                    'building_id'=>$this->building_active_id
                ]);
            }

            return redirect()->route('admin.v3.user.index');
        }

    }

    public function edit($user_id)
    {
        $roles = $this->roleRepository->getRolesByBuildingId($this->building_active_id);
//        /**
//         * @var User $user
//         */
//        $user = $this->repository->findById($user_id);

        $response = Api::GET('/api/v1/admin/show',[
            'user_id'=>$user_id
        ], true);

        $user = $response->data;


        $us = new User();
        $us->uuid = $user->user_id;

//        $role_name = $this->repository->getRoleNameByBuilding($user_id,$this->building_active_id);
        /** @var Role $role */
        $roles_user = $us->roles()->where(function (Builder $query){
            $query->where('building_id', $this->building_active_id);
        })->get()->pluck("name")->toArray();

        foreach ($roles as $key=>$value) {
            $roles[$key]['checked'] = in_array($value['name'], $roles_user);
        }

        $data = [
            'roles'=>$roles,
            "meta_title"=>"Cập nhật vai trò",
            "user"=>$user,
//            "role_name"=>$role_name
        ];

        return view('v3.users.edit',$data);
    }

    public function update(Request $request,$user_id): RedirectResponse
    {
        /**
         * @var User $user
         */
//        $user = $this->repository->findById($user_id);

        $role_name = $request->get('role_name');

        $roleGive = \GuzzleHttp\json_decode($request->get('roleGive'));
        $roleRove = \GuzzleHttp\json_decode($request->get('roleRove'));
        $roleIdsGive = \GuzzleHttp\json_decode($request->get('roleIdsGive'));
        $roleIdsRove = \GuzzleHttp\json_decode($request->get('roleIdsRove'));

        var_dump($roleIdsGive);
        var_dump($roleIdsRove);
//        die();

        foreach ($roleIdsRove as $roleIdRove) {
            DB::table('v3_user_has_roles')
                ->where([
                    'user_id'=>$user_id,
                    'role_id'=>$roleIdRove
                ])->delete();
        }

        foreach ($roleIdsGive as $roleIdGive) {
            DB::table('v3_user_has_roles')->insertOrIgnore([
                'user_id'=>$user_id,
                'role_id'=>$roleIdGive,
                'model_type'=>"App\Models\V3\User"
            ]);
        }

//        $user->syncRoles($roleGive);

        return redirect()->route('admin.v3.user.index');
    }

    public function destroy($user_id): RedirectResponse
    {
        try {
            $this->repository->forceDelete($user_id);
        } catch (Exception $e) {
        }

        return redirect()->route('admin.v3.user.index');
    }

    public function resetPassword($uuid)
    {

        $password = Helper::genUuid("##BDC",6);

        Api::POST('/api/v1/admin/reset-password',[
            'password'=>$password,
            'user_id'=>$uuid
        ], true);

//        $this->repository->updateOrCreate(['uuid'=>$uuid],['password'=>Hash::make($password)]);

        return [
            'message'=>' Mật khẩu reset là '.$password.' Xin vui lòng đăng nhập lại'
        ];

    }

}