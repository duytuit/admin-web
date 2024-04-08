<?php

namespace App\Services;

use App\Models\Permissions\GroupsPermissions;
use App\Repositories\Permissions\GroupsPermissionRepository;
use Illuminate\Support\Facades\Redis;

use App\Models\Permissions\UserPermissions;
use App\Models\Permissions\Permission;
use App\Commons\Helper;

class AppUserPermissions
{
    // public function __construct()
    // {

    // }

    public static function getPermission($user)
    {
        if (!$user) {
            return [];
        }
        $permision_ids = AppUserPermissions::getPermissionOfUser($user);
        $premission_of_group = [];
        if ($user->departmentUser) {
           $premission_of_group = @$user->departmentUser->department ? AppUserPermissions::getPermissionOfGroup(@$user->departmentUser->department) : [];
        }
        return array_unique(array_merge( $premission_of_group, $permision_ids));
    }

    public static function getAccessRouter($user)
    {
        if (!$user) {
            return [];
        }
        if (\Auth::user()->isadmin == 1 || Helper::checkAdmin($user->id)) {
            $routes = collect(\Route::getRoutes())->map(function ($route) { return $route->getName(); });
            return (array)$routes->toArray();
        }

       $permision_ids = AppUserPermissions::getPermission($user);
       return Permission::whereIn('id',$permision_ids)->get()->pluck('route_name')->toArray();
    }

    /**
    * Tra ra danh sach route user co quyen truy cap den
    * Ex: [1,2,3]
    * @return mixed
    */
    private static function getPermissionOfUser($user)
    {
        // dd($user);

        //lay ra danh sach menu user co quyen dung.
        //danh sach la mot array cac ten route user co the su dung
        if (\Auth::user()->isadmin == 1 || Helper::checkAdmin($user->id)) {
            $permision_ids = unserialize(\Cache::store('redis')->get( env('REDIS_PREFIX') .'1_DXMB_USER_PERMISION' ));
        }else {
            $permision_ids = unserialize(\Cache::store('redis')->get( env('REDIS_PREFIX') . $user->id.'_DXMB_USER_PERMISION' ));
        }

        if (!is_array($permision_ids)) {
            $permision_ids = [26];
            $rs = UserPermissions::where('pub_user_id', $user->id)->first();
            if (!$rs || !isset($rs->group_permission_ids)) {
               return [26];
            }
            $permissionsGroup = $rs->group_permission_ids;
            if($permissionsGroup) {
                foreach (GroupsPermissions::whereIn('id', explode(',', $permissionsGroup))->get() as $item) {
                    if ($item->permission_ids) {
                        $list_per = unserialize($item->permission_ids);
                    } else {
                        $list_per = [];
                    }
                    $permision_ids = array_unique(array_merge($permision_ids, $list_per));
                }
               //UserPermissions::where('pub_user_id', '=', $user->id)->update(['permissions' => serialize($permision_ids)]);
            }
                \Cache::store('redis')->forever( env('REDIS_PREFIX') . $user->id.'_DXMB_USER_PERMISION', serialize($permision_ids));

        }
        return $permision_ids;
    }

    private static function getPermissionOfGroup($department)
    {
        //danh sach la mot array cac ten route group co the su dung
        $permision_ids = unserialize(\Cache::store('redis')->get( env('REDIS_PREFIX') .$department->id.'_DXMB_GROUP_PERMISION' ));

        if (!is_array($permision_ids)) {
            $rs =$department->permissions;
            // // menu chua duoc add vao cho group
            if (!$rs || !$rs->permission_ids) {
                return [];
            }
            // luu vao cache de lan sau lay lai.
            \Cache::store('redis')->forever( env('REDIS_PREFIX') . $department->id.'_DXMB_GROUP_PERMISION' ,$rs->permission_ids );
            $permision_ids = unserialize($rs->permission_ids);
        }
        return $permision_ids;
    }

}