<?php

namespace App\Commons;

use Illuminate\Http\JsonResponse;

/**
 * Class ApiResponse
 * @package App\Helpers
 */
class ApiResponse
{
    /**
     * @param array $payload
     * @param int $statusCode
     * @return JsonResponse
     */
    public static function response(array $payload, $statusCode = 200)
    {
        return response()->json($payload, $statusCode);
    }

    /**
     * @param $res
     * @return JsonResponse
     */
    public static function responseSuccess($res)
    {
        $payload = [
            'code' => ErrorCode::SUCCESS,
            'message' => $res['message'] ?? ErrorCode::getMsg(ErrorCode::SUCCESS),
            'data' => $res['data'] ?? [],
        ];

        return self::response($payload);
    }

    /**
     * @param $res
     * @param int $statusCode
     * @return JsonResponse
     */
    public static function responseError($res, $statusCode = 200)
    {
        $payload = [
            'code' => ErrorCode::ERROR,
            'message' => $res['message'] ?? ErrorCode::getMsg(ErrorCode::ERROR),
            'data' => $res['data'] ?? [],
            'errors' => $res['errors'] ?? [],
        ];

        return self::response($payload, $statusCode);
    }
}
