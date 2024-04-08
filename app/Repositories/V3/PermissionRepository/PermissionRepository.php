<?php

namespace App\Repositories\V3\PermissionRepository;

use App\Http\Controllers\BuildingController;
use App\Models\V3\Permission;
use App\Models\V3\Role;
use App\Repositories\V3\BaseRepository\BaseRepository;
use App\Repositories\V3\RoleRepository\RoleRepository;
use App\Repositories\V3\UserRepository\UserRepository;
use Illuminate\Database\Eloquent\Builder;

class PermissionRepository extends BaseRepository implements PermissionRepositoryInterface
{

    protected $building_active_id;
    protected $userRepository;
    protected $roleRepositoy;

    /**
     * PermissionRepository constructor.
     * @param Permission $model
     * @param BuildingController $buildingController
     * @param UserRepository $userRepository
     * @param RoleRepository $roleRepository
     */
    public function __construct(
        Permission $model,
        BuildingController $buildingController,
        UserRepository $userRepository,
        RoleRepository $roleRepository
    )
    {
        $this->model = $model;
        $this->building_active_id = $buildingController->building_active_id;
        $this->userRepository = $userRepository;
        $this->roleRepositoy = $roleRepository;
    }

    public function getAllPermission(): array
    {
        return $this->query()
            ->whereNull('parent_id')
            ->with('children')
            ->get()
            ->toArray();
    }

    public function getAllPermissionWeb(): array
    {
        return $this->query()
            ->whereNull('parent_id')
            ->where('name','!=',Permission::ADMIN_MENU_APP)
            ->with('children')
            ->get()
            ->toArray();
    }

    public function getAllPermisisonApp(): array
    {
        return $this->query()
            ->where('name',Permission::ADMIN_MENU_APP)
            ->whereNull('parent_id')
            ->with(['children'=>function($query){
                $query->with(['children'=>function($q){
                    $q->orderBy('name','DESC');
                }]);
            }])
            ->get()
            ->toArray();
    }

    public function getPermisisonByRoleId($role_id): array
    {

        /**
         * @var Role $role
        */
        $role = $this->roleRepositoy->findById($role_id);

        $rolePermisison = $role->getAllPermissions()->pluck('name')->toArray();;

        $permisisons = $this->getAllPermission();

        foreach ($permisisons as $key=>$permisison) {
            $permisisons[$key]["checked"] = in_array($permisison["name"], $rolePermisison);
            if ($permisisons[$key]["children"]) {
                foreach ($permisisons[$key]["children"] as $ke => $child) {
                    if (!empty($child["name"])) {
                        $permisisons[$key]["children"][$ke]["checked"] = in_array($child["name"], $rolePermisison);
                    }
                    else {
                        $permisisons[$key]["children"][$ke]["checked"] = false;
                    }
                }
            }
        }

        return $permisisons;

    }

    public function getMenuPermisisonUser(): array
    {

        $userPermisison = $this->userRepository->getAllPermissionName($this->building_active_id);

        return $this->query()
            ->whereHas('children',function (Builder $query) use ($userPermisison){
                $query->whereIn('name',$userPermisison);
            })
            ->where('has_menu',1)
            ->whereNull('parent_id')
            ->with(['children'=>function($query) use ($userPermisison) {
                $query->whereIn('name',$userPermisison)
                    ->where('has_menu',1);
            }])
            ->get()
            ->toArray();
    }

    public function getMenuParentPermission(): array
    {
        return $this->query()
            ->whereNull('parent_id')
            ->get()
            ->toArray();
    }

    public function getMenuAppParentPermission(): array
    {
        return $this->query()
            ->where('name',Permission::ADMIN_MENU_APP)
            ->whereNull('parent_id')
            ->get()
            ->toArray();
    }

    public function getPermissionChildren($permisison_id): array
    {
        $permission = $this->query()
            ->where('id',$permisison_id)
            ->with(['children'=>function($query) {
                $query->orderBy('name','DESC');
            }])
            ->first()
            ->toArray();

        $permissionChild = [];

        array_push($permissionChild,[
            'id'=>$permission['id'],
            'name'=>$permission['name']
        ]);

        foreach ($permission['children'] as $child) {
            array_push($permissionChild,[
                'id'=>$child['id'],
                'name'=>$child['name']
            ]);
        }

        return $permissionChild;
    }

    public function getPermissionRoleApp($role_id)
    {
        /** @var Role $role */
        $role = $this->roleRepositoy->findById($role_id);

        $permissionRole = $role->getAllPermissions()
            ->pluck('name')
            ->toArray();

        $permissionApp = $this->getAllPermisisonApp();

        $permissions = $permissionApp[0]['children'];

        foreach ($permissions as $key => $permission) {
            $permissions[$key]['checked'] = in_array($permission['name'],$permissionRole);
            foreach ($permissions[$key]['children'] as $ke => $child) {
                $permissions[$key]['children'][$ke]['checked'] = in_array($child['name'],$permissionRole);
            }
        }

        return $permissions;

    }

}