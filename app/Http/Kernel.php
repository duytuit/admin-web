<?php

namespace App\Http;

use App\Commons\Helper;
use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    // public function __construct()
    // {
    //     echo '<html><head></head><body><main class="full-screen-main">
    //         <div class="content">
    //         <div class="content-description">
    //             <h2>Hệ thống BuildingCare</h2>
    //             <h3>Đang trong thời gian bảo trì. Vui lòng thử lại sau ít phút!</h3>
    //         </div>
    //         <div class="maintenace-image">
    //             <img src="/adminLTE/img/maintenance_page.png" class="image" alt="Maintenace Image">
    //         </div>
    //     </div>
    //     </main>
    //     <style>
    //     .full-screen-main{
    //     width: 100%;
    //     height: 100%;
    //     display: flex;
    //     justify-content: center;
    //     align-items: center;
    //     }
    //     .content{
    //     width: 500px;
    //     }
    //     .image{
    //     width: 100%;
    //     }
    //     .content-description{
    //     text-align: center;
    //     }
    //     </style></body></html>';
    //     return;
    // }




    /**
     * The application's global HTTP middleware stack.
     *
     * These middleware are run during every request to your application.
     *
     * @var array
     */
    protected $middleware = [
        \App\Http\Middleware\CheckForMaintenanceMode::class,
        \App\Http\Middleware\EncryptCookies::class,
        \Illuminate\Session\Middleware\StartSession::class,
        \Illuminate\Session\Middleware\AuthenticateSession::class,
        \App\Http\Middleware\TrimStrings::class,
        \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
        \App\Http\Middleware\TrustProxies::class,
        // \App\Http\Middleware\LogMiddleware::class,
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array
     */
    protected $middlewareGroups = [
        'web' => [
            \App\Http\Middleware\EncryptCookies::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],

        'api' => [
            'throttle:240,1',
            'bindings',
        ],
    ];

    /**
     * The application's route middleware.
     *
     * These middleware may be assigned to groups or used individually.
     *
     * @var array
     */
    protected $routeMiddleware = [
        'auth'          => \Illuminate\Auth\Middleware\Authenticate::class,
        'auth.basic'    => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'bindings'      => \Illuminate\Routing\Middleware\SubstituteBindings::class,
        'cache.headers' => \Illuminate\Http\Middleware\SetCacheHeaders::class,
        'can'           => \Illuminate\Auth\Middleware\Authorize::class,
        'guest'         => \App\Http\Middleware\RedirectIfAuthenticated::class,
        'signed'        => \Illuminate\Routing\Middleware\ValidateSignature::class,
        'throttle'      => \Illuminate\Routing\Middleware\ThrottleRequests::class,
        'minify'        => \App\Http\Middleware\MinifyHtml::class,
        'role'          => \App\Http\Middleware\Role::class,
        // 'scopes'        => \Laravel\Passport\Http\Middleware\CheckScopes::class,
        'jwt.auth'      => \Tymon\JWTAuth\Http\Middleware\Authenticate::class,
        //'jwt.refresh'   => \Tymon\JWTAuth\Middleware\RefreshToken::class,
        'route_permision' => \App\Http\Middleware\RoutePermision::class,
        'route_accountant' => \App\Http\Middleware\RouteAccountant::class,
        'route_accountant_v2' => \App\Http\Middleware\RouteAccountant_v2::class,
        'maintenance' => \App\Http\Middleware\Maintenance::class,
        'request_info' => \App\Http\Middleware\RequestMiddleware::class,
        'request_api' => \App\Http\Middleware\RequestApiMiddleware::class,
        'jwt_auth' => \App\Http\Middleware\VerifyJWTToken::class,
    ];
}
