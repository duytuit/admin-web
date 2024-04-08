<?php

namespace App\Traits;

trait ApiResponse
{

    /**
     * @param $message
     * @param int $error_code
     * @param array $data
     * @param int $status_code
     * @return \Illuminate\Http\JsonResponse
     */
    public function responseError($message, $error_code = 200, array $data = [], $status_code = 200)
    {
        $result = [
            'status' => "fail",
            'error_code' => $error_code,
            'message' => $message,
            'data' => $data
        ];
        return $this->responseJson($result, [], $status_code);
    }

    /**
     * @param $message
     * @param array $data
     * @param int $error_code
     * @param int $status_code
     */
    public function validateFail($message, array $data = [], $error_code = 422, $status_code = 200)
    {
        $result = [
            'status' => "fail",
            'error_code' => $error_code,
            'message' => $message,
            'data' => $data
        ];
        return $this->responseJson($result, [], $status_code);
    }

    /**
     * @param array $data
     * @param string $message
     * @param int $error_code
     * @param array $extra_data
     * @param $status_code
     * @return \Illuminate\Http\JsonResponse
     */
    public function responseSuccess(array $data = [], $message = '', $error_code = 200, array $extra_data = [], $status_code = 200)
    {
        $result = [
            'status' => "true",
            'error_code' => $error_code,
            'message' => $message,
            'data' => $data
        ];
        return $this->responseJson($result, $extra_data, $status_code);
    }

    /**
     * @param array $data
     * @param array $extra_data
     * @param int $status_code
     * @return \Illuminate\Http\JsonResponse
     */
    public function responseJson(array $data = [], array $extra_data = [], $status_code = 200)
    {
        $result = [
            'status' => $data['status'] ?? false,
            'error_code' => $data['error_code'] ?? 200,
            'message' => $data['message'] ?? '',
            'data' => $data['data'] ?? [],
        ];
        $result = array_merge($result, $extra_data);
        return response()->json($result, $status_code);
    }
    // ===========================================================================================================================
      /**
     * @param string $message
     * @param mixed  $data
     *
     * @return array
     */
    public function makeResponse($message, $data, $href = null)
    {
        $result =  [
            'success' => true,
            'code' => 200,
            'message' => $message,
            'data'    => $data,
            'href'   => $href
        ];
        return $result;
    }

    /**
     * @param string $message
     * @param array  $data
     *
     * @return array
     */
    public function makeError($message, array $data = [])
    {
        $res = [
            'success' => false,
            'message' => $message,
        ];

        if (!empty($data)) {
            $res['data'] = $data;
        }

        return $res;
    }
    public function sendResponse($result, $message, $href = null)
    {
        return response()->json($this->makeResponse($message, $result, $href));
    }

    public function sendErrorResponse($error, $code = 404)
    {
        return response()->json($this->makeError($error), $code);
    }
    public function sendSuccessApi($result, $status = 200, $message = "Lấy dữ liệu thành công")
    {

        $response = [
            'success' => true,
            'status' => $status,
            'data'    => $result,
            'message' => $message,
        ];

        return response()->json($response, $status);
    }

    public function sendSuccessApiPage($result, $total, $page, $perPage, $status = 200, $message = "Lấy dữ liệu thành công")
    {

        $response = [
            'success' => true,
            'status' => $status,
            'data'    => $result,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'message' => $message,
        ];

        return response()->json($response,  $status);
    }
    public function sendErrorApi($errorMessage = "Lấy dữ liệu không thành công", $errorData = [], $code = 404)
    {
        $response = [
            'success' => false,
            'status' => $code,
            'message' => $errorMessage,
        ];

        if (!empty($errorData)) {
            $response['data'] = $errorData;
        }

        return response()->json($response,  $code);
    }
}
