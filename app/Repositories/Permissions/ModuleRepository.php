<?php

namespace App\Repositories\Permissions;

use App\Commons\Helper;
use App\Models\Building\Building;
use App\Repositories\Contracts\RepositoryInterface;
use App\Repositories\Eloquent\Repository;
use  App\Services\AppUserPermissions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class ModuleRepository extends Repository {




    /**
     * Specify Model class name
     *
     * @return mixed
     */
    function model()
    {
        return \App\Models\Permissions\Module::class;
    }

    public function myMenuWithPermission($buildingId, $columns = array('*'))
    {
        $building = Building::get_detail_building_by_building_id($buildingId);

        $user = Auth::guard('backend_public')->user();

        $permissions = AppUserPermissions::getPermission($user);

        $menu = null;
        if($user->isadmin == 1){
            $menu = Cache::store('redis')->get(env('REDIS_PREFIX') . 'get_permission_by_userId_3_'.$user->id);
 
            if($menu){
                 return $menu;
            }

            $menu = $this->model->whereHas('permissions', function($q) use ($permissions){
                $q->whereIn('id', $permissions);
            })
            ->where('type',0)
            ->whereNotIn('id', Helper::config_menu_v1)
            ->with('menus')->orderBy('created_at', 'desc')->get( $columns);

            Cache::store('redis')->put(env('REDIS_PREFIX') . 'get_permission_by_userId_3_' . $user->id, $menu,60);
        }else if($building && @$building->config_menu == 2){
            $menu = Cache::store('redis')->get(env('REDIS_PREFIX') . 'get_permission_by_userId_1_'.$user->id);
 
            if($menu){
                 return $menu;
            }

            $menu = $this->model->whereHas('permissions', function($q) use ($permissions){
                $q->whereIn('id', $permissions);
            })
            ->where('type',0)
            ->whereNotIn('id', Helper::config_menu_v1)
            ->with('menus')->orderBy('created_at', 'desc')->get( $columns);

            Cache::store('redis')->put(env('REDIS_PREFIX') . 'get_permission_by_userId_1_' . $user->id, $menu,60);
        } else{
            $menu = Cache::store('redis')->get(env('REDIS_PREFIX') . 'get_permission_by_userId_2_'.$user->id);
 
            if($menu){
                 return $menu;
            }
            $menu = $this->model->whereHas('permissions', function($q) use ($permissions){
                $q->whereIn('id', $permissions);
            })
            ->where('type',0)
            ->whereNotIn('id', Helper::config_menu_v2)
            ->with('menus')->orderBy('created_at', 'desc')->get( $columns);
            
            Cache::store('redis')->put(env('REDIS_PREFIX') . 'get_permission_by_userId_2_' . $user->id, $menu,60);
        } 
       
        return $menu;
    }

    public function findMenu($id)
    {
        return $this->model->findOrFail($id);
    }

    public function first()
    {
        return $this->model->first();
    }
    public function countType()
    {
        return $this->model->where('type',1)->count();
    }
    public function getIdTypeApp()
    {
        return $this->model->select('id')->where('type',1)->with('permissions')->first();
    }

}
