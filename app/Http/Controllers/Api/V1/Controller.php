<?php

namespace App\Http\Controllers\Api\V1;

use App\Traits\ApiRequest;
use App\Traits\ApiUser;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests, ApiRequest, ApiUser;

    /**
     * Model tương ứng Controller API
     *
     */
    protected $model;

    /**
     * Resource tương ứng Controller API
     *
     */
    protected $resource;

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        return $this->save($request);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $id = (int) $request->id;

        return $this->save($request, $id);
    }

    /**
     * Display the specified resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
        $id = (int) $request->id;

        try {
            $item = $this->model->findOrFail($id);
            return $this->resource->one($item);
        } catch (ModelNotFoundException $exception) {
            $json = [
                'errors' => [
                    [
                        'code'   => 11001,
                        'title'  => 'Record not found',
                        'detail' => 'Record ID ' . $id . ' for ' . str_replace('App\\', '', $exception->getModel()) . ' not found',
                    ],
                ],
            ];

            return response()->json($json, 404);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $id = (int) $request->id;

        if ($id) {
            $list = [$id];
        } else {
            $list = $request->input('id', []);
        }

        $this->model->whereIn('id', $list)->delete();

        return response()->json(null, 204);
    }

    /**
     * Format lại key
     *
     * @param array $data
     * @param string $prefix
     * @param array $exception_key
     * @return void
     */
    public function formatKey(array $data, string $prefix, array $exception_key = [])
    {
        if (!is_array($data) || !$data) {
            return null;
        }
        //Get keys
        $keys = array_keys($data);

        //Map keys to format function
        $keys = array_map(function ($key) use ($prefix, $exception_key) {
            if (in_array($key, $exception_key)) {
                return $key;
            }
            return str_replace($prefix, '', $key);
        }, $keys);

        //Use array_combine to map formatted keys to array values
        $array = array_combine($keys, $data);
        return $array;
    }
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
}
