<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\V1\Controller;

class SettingController extends Controller
{
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($type)
    {
        try {
            $setting  = Setting::where('config_key', $type)->first();
            return response()->json($setting);

        } catch (ModelNotFoundException $exception) {
            return response()->json([
                'errors' => [
                    [
                        'code'   => 11001,
                        'title'  => 'Record not found',
                        'detail' => 'Record setting ' . $type . ' for ' . str_replace('App\\', '', $exception->getModel()) . ' not found',
                    ],
                ],
            ])->setStatusCode(400);
        }
    }
}
