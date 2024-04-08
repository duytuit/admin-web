<?php

namespace App\Http\Middleware;

use Closure;
use App\Http\Controllers\BuildingController;
use App\Models\Building\Building;
use Illuminate\Http\Request;

class RouteAccountant extends BuildingController
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
        // $building = Building::get_detail_building_by_building_id($this->building_active_id);
        // $user = auth()->user();
        // if ($user->isadmin == 0 && $building && @$building->config_menu == 2 ) {
        //     # khong co quyen vao route nay
        //     return redirect()->away('/admin')->with(['warning' => 'Vui lòng liên hệ quản trị để sử dụng tính năng này!']);

        // }

        return $next($request);
    }


}
