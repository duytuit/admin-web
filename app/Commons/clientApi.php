<?php

namespace App\Commons;

use App\Commons\Helper;
use App\Helpers\dBug;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;

class clientApi
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
      
        $res = $client->request('GET', env('APP_URL').$url, [
            'headers' => $headers,
            'query'=> $query
        ]);
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
        $token = Helper::getToken(Auth::user()->id);
        if (!empty($token)) {
            $token = JWTAuth::fromUser(Auth::user());
        }
        $headers['Authorization'] = "Bearer ".$token;
        $client = new Client(['headers' => $headers]);
        $response = $client->post(env('APP_URL').$url, [
            'form_params' =>$data
        ]);
        return  json_decode($response->getBody()->getContents());

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

        $res = $client->request('POST', env('APP_URL').$url,$options);

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

        $client = new \GuzzleHttp\Client(['headers' => $headers]);

        $res = $client->request('PUT',  env('APP_URL').$url, $data);

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

        $res = $client->request('DELETE', env('APP_URL').$url, [
            'headers' => $headers,
            'form_params'=> $data
        ]);

        return json_decode($res->getBody()->getContents());
    }

}
