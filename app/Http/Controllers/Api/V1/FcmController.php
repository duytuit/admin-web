<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Controller;
use App\Models\Fcm;
use Illuminate\Http\Request;

class FcmController extends Controller
{
    public function update(Request $request)
    {
        $user  = $this->getApiUser();
        $token = $request->token;

        $fcm = Fcm::where('user_id', $user->id)
            ->where('device_id', $request->device_id)
            ->first();

        if (!$fcm) {
            $fcm = new Fcm();
        }

        $param = [
            'user_id'   => $user->id,
            'token'     => $token,
            'user_type' => $user->type,
            'device_id' => $request->device_id,
        ];

        $fcm->fill($param);
        $fcm->save();

        return response()->json([
            'data' => $fcm,
        ]);
    }

}
