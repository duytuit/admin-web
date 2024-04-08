<?php

namespace App\Commons;

use App\Commons\Helper;
use App\Commons\Util\Debug\Log as DebugLog;
use App\Helpers\dBug;
use App\Util\Debug\Log;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;

class Api
{

    /**
     * @param string $url
     * @param array $query
     * @param boolean $auth
     * @return mixed
     */
    public static function GET(string $url, array $query)
    {

        $token = Helper::getToken(Auth::user()->id);
        if (!empty($token)) {
            $token = JWTAuth::fromUser(Auth::user());
        }

        $headers['Authorization'] = "Bearer ".$token;
        
        $client = new \GuzzleHttp\Client();

        $data['headers'] = $headers;
        if (count($query) > 0) {
            $data['query'] = $query;
        }
        DebugLog::info("tu_check_token",json_encode(env('DOMAIN_API',"https://apibdc.dxmb.vn/").'|'.json_encode($headers)));
        $res = $client->request('GET', env('DOMAIN_API',"https://apibdc.dxmb.vn/").$url, $data);
        return  json_decode($res->getBody()->getContents());

    }

    /**
     * @param string $url
     * @param array $data
     * @param boolean $auth
     * @return mixed
     */
    public static function POST(string $url, array $data)
    {

        $token = Helper::getToken(@Auth::user()->id);

        if (!empty($token)) {
            $token = JWTAuth::fromUser(Auth::user());
        }
        $headers['Authorization'] = "Bearer ".$token;
        $client = new \GuzzleHttp\Client();
        DebugLog::info("tu_check_token",json_encode(env('DOMAIN_API',"https://apibdc.dxmb.vn/").'|'.json_encode($headers)));
        $res = $client->request('POST', env('DOMAIN_API',"https://apibdc.dxmb.vn/").$url, [
            'headers' => $headers,
            'form_params'=> $data
        ]);

        return json_decode($res->getBody()->getContents());

    }

      /**
     * @param string $url
     * @param array $data
     * @param boolean $auth
     * @return mixed
     */
    public static function POST_MULTIPART(string $url, array $options)
    {

        $token = Helper::getToken(Auth::user()->id);

        if (!empty($token)) {
            $token = JWTAuth::fromUser(Auth::user());
        }

        $headers['Authorization'] = "Bearer ".$token;

        $client = new \GuzzleHttp\Client(['headers' => $headers]);
        DebugLog::info("tu_check_token",json_encode(env('DOMAIN_API',"https://apibdc.dxmb.vn/").'|'.json_encode($headers)));
        $res = $client->request('POST', env('DOMAIN_API',"https://apibdc.dxmb.vn/").$url,$options);

        return json_decode($res->getBody()->getContents());

    }

    /**
     * @param string $url
     * @param array $data
     * @param boolean $auth
     * @return mixed
     */
    public static function PUT(string $url, array $data)
    {
        $token = Helper::getToken(Auth::user()->id);

        if (!empty($token)) {
            $token = JWTAuth::fromUser(Auth::user());
        }

        $headers['Authorization'] = "Bearer ".$token;

        $client = new \GuzzleHttp\Client();

        $res = $client->request('PUT',  env('DOMAIN_API',"https://apibdc.dxmb.vn/").$url, [
            'headers' => $headers,
            'form_params'=> $data
        ]);

        return json_decode($res->getBody()->getContents());
    }

    /**
     * @param string $url
     * @param array $data
     * @return mixed
     */
    public static function DELETE(string $url, array $data)
    {

        $token = Helper::getToken(Auth::user()->id);

        if (!empty($token)) {
            $token = JWTAuth::fromUser(Auth::user());
        }

        $headers['Authorization'] = "Bearer ".$token;

        $client = new \GuzzleHttp\Client();

        $res = $client->request('DELETE', env('DOMAIN_API',"https://apibdc.dxmb.vn/").$url, [
            'headers' => $headers,
            'form_params'=> $data
        ]);

        return json_decode($res->getBody()->getContents());
    }

}
