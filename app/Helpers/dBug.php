<?php

namespace App\Helpers;


use App\Models\V3\User\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;

class dBug
{
    static function show($obj, $label = '', $color = '#ffcebb')
    {
        echo "<pre style='border: 1px solid red;margin:3px;padding:3px;background-color:$color !important;max-height: 800px;overflow: auto'>";
        $debug = debug_backtrace();
        if ($label) {
            echo "<h2>$label</h2>";
        }
        echo ($debug[0]['file'] . ':' . $debug[0]['line']) . '<br/>';
        print_r($obj);
        echo "</pre>";
    }

    static function getDbInfo()
    {
        return DB::getQueryLog();
    }

    static $timeDebug = ['label' => '', 'starttime' => false, 'endtime' => false];

    static function startDebugTime($string)
    {
        if (\config('debugbar.enabled')) {
            start_measure($string);
        }
    }

    static function endDebugTime($string)
    {
        if (\config('debugbar.enabled')) {
            stop_measure($string);
        }
    }

    static function setDebugInfo($object, $label = 'youtube_apikey')
    {
        if (\config('debugbar.enabled')) {
            //stop_measure($string);
            \Debugbar::addMessage($object, $label);

        }
    }

    static function sendNotification($msg)
    {
        //gửi tin nhắn telegram
    }

    static function pushNotification($msg = '',$link='')
    {

        if($link==''){
            $link = \config('app.telegram_debug_log_channel');
        }
        //notif_one_id
        $ch = curl_init();
        // Set the URL
        curl_setopt($ch, CURLOPT_URL, $link . urlencode($msg));
        // Removes the headers from the output
        curl_setopt($ch, CURLOPT_HEADER, 0);
        // Return the output instead of displaying it directly
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        // Execute the curl session
        curl_exec($ch);
        // Close the curl session
        curl_close($ch);
        // Return the output as a variable
        return true;
    }

    static function trackingJsError()
    {

        $msg = \request('c');
        if ($msg) {
            $msg = base64_decode($msg);
        }
        $content = base64_decode('R0lGODlhAQABAJAAAP8AAAAAACH5BAUQAAAALAAAAAABAAEAAAICBAEAOw==');
        $response = Response::make($content, 200);
        $response->header('Content-Type', 'image/gif');

        if (!$msg) {
            return $response;
        }

        $msg .= "\n";
        $msg .= self::_getDebugInfo();
        env('APP_ENV') !== 'local' && self::pushNotification($msg,\config('app.telegram_jsdebug_log_channel'));
        return $response;

    }

    static function _getDebugInfo()
    {
        $msg = '';
        $msg .= "\nREMOTE_ADDR: " . @$_SERVER['REMOTE_ADDR'];
        $msg .= "\nHTTP_USER_AGENT: " . @$_SERVER['HTTP_USER_AGENT'];
        $msg .= "\nHTTP_REFERER: " . @$_SERVER['HTTP_REFERER'];
        $msg .= "\nFULL_URL: " . request()->fullUrl();
        $msg .= "\nCURRENT_URL: " . url()->current();
        $msg .= "\nREQUEST_METHOD: " . @$_SERVER['REQUEST_METHOD'];
        $msg .= "\nSERVER_NAME: " . @$_SERVER['SERVER_NAME'];
        $msg .= "\nHTTP_HOST: " . @$_SERVER['HTTP_HOST'];
        $msg .= "\nREQUEST_URI: " . @$_SERVER['REQUEST_URI'];
        $msg .= "\n---";
        $msg .= "\nUser Email: " . @Auth::user()->email;
        $msg .= "\nUser Id: " . @Auth::user()->id;
        return $msg;
    }

    static function trackingPhpError($exception, $link_issue = '', $statusCode = '')
    {

        if (!$statusCode) {
            if (method_exists($exception, 'getStatusCode')) {
                $statusCode = $exception->getStatusCode();
            }
        }
        if (in_array($statusCode, [404, 405,401]) || in_array(@$_SERVER['HTTP_USER_AGENT'],['TelegramBot (like TwitterBot)'])) {
            return;
        }
        if(in_array($exception->getMessage(),[
            'Unauthenticated.','The given data was invalid.'
        ])){
            return;
        }
        if($exception->getLine()==1){
            return;
        }
        if (app()->bound('sentry')) {
            $sentry_id = app('sentry')->captureException($exception);
            $link_issue = config('app.sentry_debug_query_url') . $sentry_id;
        }
        $msg = "Link issues: " . $link_issue;
        $msg .= "\nMessage: " . $exception->getMessage();
        $msg .= "\nStatusCode: " . $statusCode;
        $msg .= "\nFile: " . $exception->getFile() . ':' . $exception->getLine();
        $msg .= self::_getDebugInfo();
        self::pushNotification($msg);
    }
    static function trackingPhpErrorV2($msg = null)
    {
        $msg = "\nMessage: " . json_encode($msg);
        $msg .= self::_getDebugInfo();
        self::pushNotification($msg);
    }
    static function trackingPhpErrorV3($msg = null)
    {
        $msg = "\nMessage: " . json_encode($msg);
        $msg .= self::_getDebugInfo();
        self::pushNotification($msg);
    }


    static function showLineSingle($string)
    {
        echo $string . "\n<br/>";
    }

}
//v3