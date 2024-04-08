<?php

namespace App\Http\Middleware;

use App\Commons\Helper;
use Closure;

class Maintenance
{
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
        $maintain = Helper::getMaintenance();
        $cookie = @$_COOKIE['bdc_test'];
        if($maintain == 'true' && $cookie == null ){
            return redirect()->route('admin.maintenance');
        }
        return $next($request);
    }


}
