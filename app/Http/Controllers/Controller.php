<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * success response method.
     *
     * @return \Illuminate\Http\Response
     */
    public function sendResponse($result, $status, $message)
    {
        $response = [
            'success' => true,
            'status' => $status,
            'data'    => $result,
            'message' => $message,
        ];

        return response()->json($response, 200);
    }

    /**
     * success response method.
     *
     * @return \Illuminate\Http\Response
     */
    public function sendResponsePaging($result, $paging, $status, $message)
    {
        $response = [
            'success' => true,
            'status' => $status,
            'data'    => $result,
            'page'    => $paging,
            'message' => $message,
        ];

        return response()->json($response, 200);
    }

    /**
     * return error response.
     *
     * @return \Illuminate\Http\Response
     */
    public function sendError($errorMessage, $errorData = [], $code = 404)
    {
        $response = [
            'success' => false,
            'status' => $code,
            'message' => $errorMessage,
        ];

        if (!empty($errorData)) {
            $response['data'] = $errorData;
        }

        return response()->json($response, 200);
    }
    public function object_to_array($data)
    {
        if (is_array($data) || is_object($data))
        {
            $result = array();
            foreach ($data as $key => $value)
            {
                $result[$key] = $this->object_to_array($value);
            }
            return $result;
        }
        return $data;
    }
    public function sendSuccess_Api($result, $message = "Lấy dữ liệu thành công", $href = null, $status = 200)
    {

        $response = [
            'success' => true,
            'status' => $status,
            'data'    => $result,
            'message' => $message,
            'href' => $href
        ];

        return response()->json($response, $status);
    }
    public function sendError_Api($errorMessage = "Lấy dữ liệu không thành công", $errorData = [], $code = 404)
    {
        $response = [
            'success' => false,
            'status' => $code,
            'message' => $errorMessage,
        ];

        if (!empty($errorData)) {
            $response['data'] = $errorData;
        }

        return response()->json($response, $code);
    }
}
