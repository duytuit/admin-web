<?php

namespace App\Http\Middleware;

use Closure;
use App\Http\Controllers\BuildingController;
use App\Models\Permissions\Permission;
use App\Util\Debug\Log;
use App\Util\Debug\LogAction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class RequestMiddleware extends BuildingController
{

      public function __construct(Request $request)
      {
          parent::__construct($request);
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

        $curent_route = Route::current();

        $action = @$curent_route->action['permission'];
        if ($action) {
            $menu = Permission::where('status', Permission::STATUS_INACTIVE)->where('route_name',  $curent_route->getName() )->first();
            LogAction::logToolAction($menu ? $menu->id : 0, $action, $request->fullUrl(), $request->all());
        }
        return $next($request);
    }


}
