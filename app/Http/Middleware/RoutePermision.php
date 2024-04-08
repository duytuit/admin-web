<?php

namespace App\Http\Middleware;

use App\Helpers\dBug;
use Closure;
use Illuminate\Support\Facades\Auth;
use App\Models\Permissions\Permission;
use App\Services\AppUserPermissions;
use Exception;
use App\Commons\Helper;
use Illuminate\Support\Facades\DB;
use App\Models\PublicUser\Users;
use App\Models\PublicUser\UserInfo;
use Illuminate\Support\Facades\Route;

class RoutePermision
{
    protected $menu;
    protected $user_permission;

      public function __construct(Permission $menu,  AppUserPermissions $user_permission)
      {
        $this->menu = $menu;
        $this->user_permission = $user_permission;
      }
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        if(Auth::user()){
            $checkToken = Helper::getToken(Auth::user()->id);
            if(!$checkToken){
                Auth::guard('backend_public')->logout();
            }
        }
        if ( Auth::user() && (Auth::user()->isadmin == 1 || Helper::checkAdmin(Auth::user()->id))) {
            return $next($request);
        }
        $menu = Permission::where('status', Permission::STATUS_INACTIVE)->where('route_name',  Route::current()->getName() )->first();

        $user =Auth::user();

        if ($menu && !in_array($menu->id, $this->user_permission->getPermission($user)) ) {
            # khong co quyen vao route nay
            return redirect()->route('admin.auth.form');

        }
     
        return $next($request);
    }


}
