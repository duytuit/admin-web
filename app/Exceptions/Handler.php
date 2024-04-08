<?php

namespace App\Exceptions;

use App\Helpers\dBug;
use App\Util\Debug\Log;
use App\Util\Debug\LogAction;
use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Facades\Request;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * @param  \Exception  $exception
     * @return void
     */
    public function report(Exception $exception)
    {
       // dd($exception);
        parent::report($exception);
        LogAction::logToolActionFail('Message: ' .$exception->getMessage(). '|| File: ' .$exception->getFile() . ':' . $exception->getLine());
        dBug::trackingPhpError($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $exception)
    {
        // if it's API, hack to return JSON
        $isApiCall = (strpos($request->getUri(), '/api') !== false);
        if ($isApiCall) {
            $request->headers->set('X-Requested-With', 'XMLHttpRequest');
        }

        return parent::render($request, $exception);
    }

    /**
     * Redirect Unauthenticated User to login
     *
     * @param Request $request
     * @param AuthenticationException $exception
     * @return void
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        if ($request->expectsJson()) {
            return response()->json(['error' => 'Unauthenticated.'], 401);
        }
        $guard = array_get($exception->guards(), 0);

        switch ($guard) {
            case 'admin-user':
            case 'admin-partner':
                $login = 'admin.auth.login';
                break;
            case 'api-user':
            case 'api-partner':
                return response()->json(['error' => 'UnAuthorised'], 401);
                break;
            default:
                $login = 'admin.auth.login';
                break;
        }

        return redirect()->guest(route($login));
    }
}
