<?php

namespace App\Http\Views;

use App\Http\Controllers\BuildingController;
use App\Models\Apartments\Apartments;
use App\Models\BdcApartmentServicePrice\ApartmentServicePrice;
use App\Models\Building\BuildingPlace;
use App\Models\Department\Department;
use App\Models\Service\Service;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

use App\Repositories\Permissions\ModuleRepository;
use App\Services\AppUserPermissions;
use Illuminate\Support\Facades\Auth;

class MenusComposer extends BuildingController
{
    /**
     * The user repository implementation.
     *
     * @var MenuRepository
     */
    protected $user_permissions;
    protected $permissions;
    protected $module;
    protected $user_permission;
    /**
     * Create a new profile composer.
     *
     * @param  UserPermissions  $user_permissions
     * @return void
     */
    public function __construct( ModuleRepository $group_menu, AppUserPermissions $user_permission, Request $request)
    {
        // Dependencies automatically resolved by service container...
      
        $this->group_menu = $group_menu;
        $this->user_permission = $user_permission;
        Carbon::setLocale('vi');
        parent::__construct($request);
    }

    /**
     * Bind data to the view.
     *
     * @param  View  $view
     * @return void
     */
    public function compose(View $view)
    {
        $view->with('group_menu', $this->group_menu->myMenuWithPermission($this->building_active_id));
        $view->with('route_current', \Route::current()->getName());
        $view->with('building_active', $this->building_active_id);
        $view->with('building_users', $this->buildings);
        $view->with('users_profile_active',\Auth::user() ? \Auth::user()->getUserInfoId($this->building_active_id) : null);
        $view->with('getCateByBuilding',$this->getCateByBuilding($this->building_active_id));
    }
    public function getCateByBuilding($buildingId)
    {
        $rs = Cache::store('redis')->get(env('REDIS_PREFIX') . 'get_getCateByBuilding_'.$buildingId);
        if($rs){
            return $rs;
        }
        $data =[
            'apartment' => json_encode(Apartments::select(['id', 'name'])->where('building_id', $buildingId)->get()->toArray()),
            'user_info' => json_encode(\App\Models\PublicUser\V2\UserInfo::select(['id', 'full_name', 'user_id', 'phone_contact', 'email_contact'])->whereHas('building', function ($query) use ($buildingId) {
                $query->where('building_id', $buildingId);
            })->get()->toArray()),
            'service' => json_encode(Service::select(['id', 'name','type'])->where('bdc_building_id', $buildingId)->get()->toArray()),
            'department' => json_encode(Department::select(['id', 'name'])->where('bdc_building_id', $buildingId)->get()->toArray()),
            'buildingPlace' => json_encode(BuildingPlace::select(['id', 'name'])->where('bdc_building_id', $buildingId)->get()->toArray()),
            'service_apartment' => json_encode(ApartmentServicePrice::select(['id', 'bdc_service_id'])->where('bdc_building_id', $buildingId)->get()->toArray()),
        ];
        $rs = json_encode($data);
        if(!$rs){
            return false;
        }
        Cache::store('redis')->put(env('REDIS_PREFIX') . 'get_getCateByBuilding_' . $buildingId, $rs,60*30); //cache 30 phút
        return $rs;
    }

}