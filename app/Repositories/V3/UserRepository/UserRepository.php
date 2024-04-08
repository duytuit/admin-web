<?php

namespace App\Repositories\V3\UserRepository;

use App\Commons\Helper;
use App\Models\V3\Permission;
use App\Models\V3\Role;
use App\Models\V3\User;
use App\Repositories\BaseRepository\BaseRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class UserRepository extends BaseRepository implements UserRepositoryInterface
{

    /**
     * UserRepository constructor.
     * @param User $model
     */
    public function __construct(User $model)
    {
        $this->model = $model;
    }

    public function getAllPermissionName($building_active_id = null): array
    {
        /**
         * @var User $user
         */
        $user = Auth::user();

        if (empty($user)) {
            return [];
        }

        if ($user->hasRole(Role::ROLE_SUPER_ADMIN)) {
            return Permission::query()->pluck('name')->toArray();
        }
        else {
            /** @var Role $role */
            $roles = $user->roles()->where(function (Builder $query) use ($building_active_id){
                $query->where('building_id', $building_active_id);
            })->get();

            $permissions = [];
            foreach ($roles as $role) {
                $permissions = array_merge($permissions,$role->getAllPermissions()->pluck("name")->toArray());
            }

            return array_unique($permissions);

        }

    }

    public function getUserByBuildingId($building_id)
    {
        return $this->query()
            ->whereHas('buildings',function (Builder $query) use ($building_id){
                $query->where('id',$building_id);
            })
            ->with(['roles'=>function($query) use ($building_id) {
                $query->where('building_id',$building_id);
            }])
            ->paginate(15);
    }

    public function getRoleNameByBuilding($user_id,$building_active_id = null)
    {
        /**
         * @var User $user
         */
        $user = $this->findById($user_id);

        return $user->roles()->where(function (Builder $query) use ($building_active_id){
                $query->where('building_id', $building_active_id);
            })->pluck('name')->first();
    }

    public function getUsersNotHaveRole($building_active_id): array
    {
        return $this->query()
            ->whereHas('buildings', function (Builder $query) use ($building_active_id) {
                $query->where('id', $building_active_id);
            })
            ->has('roles', '==', 0)
            ->get()
            ->toArray();
    }

    public function updateUuidAllUser()
    {

        $users = $this->query()->get()->toArray();

        foreach ($users as $user) {
            $this->query()
                ->where('id',$user['id'])
                ->update([
                    'uuid'=>Helper::genUuid("##BDC",28)
                ]);
        }

    }

    public function findByEmail($email, $building_id)
    {
        return $this->query()
            ->where('email', $email)
            ->whereHas('buildings', function ($query) use ($building_id) {
                $query->where('id', $building_id);
            })
            ->count();
    }

}