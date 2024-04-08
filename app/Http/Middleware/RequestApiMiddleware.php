<?php

namespace App\Http\Middleware;

use App\Helpers\dBug;
use Closure;
use App\Http\Controllers\BuildingController;
use App\Models\Permissions\Permission;
use App\Util\Debug\Log;
use App\Util\Debug\LogAction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class RequestApiMiddleware extends BuildingController
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

        dBug::trackingPhpErrorV2();
        return $next($request);
    }


}
