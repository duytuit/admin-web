<?php

namespace App\Http\Controllers\CRMTool\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Validator;
use App\Traits\ApiResponse;
use App\Services\AppConfig;
use App\Models\Permissions\Module;

class ListmoduleController extends Controller
{
    use ApiResponse;

    private $model;
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct(Module $model)
    {
        $this->model = $model;
    }

    public function list_module(Request $request)
    {
      $validator = Validator::make($request->all(), [
        'app'     => 'required|in:CRM_tool',
        'code'    => 'required|in:^_^dxmb^_^',
      ]);

      if ($validator->fails()) {
        return $this->validateFail($validator->errors(), [], 404 );
      } else {
        // success
        return $this->responseSuccess($this->model->with('permissions')->get()->toArray());
      }
      
      
    }

    public function update_time(Request $request)
    {
      $validator = Validator::make($request->all(), [
        'app'     => 'required|in:CRM_tool',
        'code'    => 'required|in:^_^dxmb^_^',
      ]);

      if ($validator->fails()) {
        return $this->validateFail($validator->errors(), [], 404 );
      } else {
        // success
        return $this->responseSuccess();
      }
    }

}