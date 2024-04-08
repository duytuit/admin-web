<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Controller;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use kcfinder\file;

class UploadController extends Controller
{

    public function upload(Request $request)
    {
        $user = $this->getApiUser();
        $type = $request->input('type', 'other');

        if ($user) {
            $files = $request->file('files');
            $url   = [];
            switch ($type) {
                case 'user':
                    if ($user->type == 'user') {
                        $file_name = 'user-' . $user->id . '-' . $user->ub_account_tvc;
                    } elseif ($user->type == 'customer') {
                        $file_name = 'user-' . $user->id . '-' . $user->cb_id_passport;
                    } else {
                        $file_name = 'user-' . $user->id . '-' . $user->email;
                    }
                    $part_upload = 'media/upload/user';
                    break;
                default:
                    $file_name   = $type . '-' . $user->id . '-' . time();
                    $part_upload = 'media/upload/' . $type;
                    break;
            }

            foreach ($files as $key => $file) {
                $fileName = $file_name . $key . '.' . $file->getClientOriginalExtension();
                $file->move($part_upload, $fileName);

                $url[] = '/' . $part_upload . '/' . $fileName;
            }

            return response()->json(['data' => $url]);
        }
    }

    public function upload_avatar(Request $request)
    {
        $user = $this->getApiUser();

        if ($user) {
            $file = $request->file('file');

            $part_upload = 'media/upload/avatar';

            if ($user->type == 'user') {
                $file_name = 'avatar-user' . $user->id . '-' . $user->ub_account_tvc;
            } elseif ($user->type == 'partner') {
                $file_name = 'avatar-partner' . $user->id . '-' . $user->email;
            } else {
                $file_name = 'avatar-customer' . $user->id . '-' . $user->cb_id_passport;
            }

            $fileName = $file_name . '.' . $file->getClientOriginalExtension();
            $file->move($part_upload, $fileName);

            $url = '/' . $part_upload . '/' . $fileName;

            if ($user->type == 'user') {
                $param['ub_avatar'] = $url;
                unset($user->group_id);
            } elseif ($user->type == 'partner') {
                $param['avatar'] = $url;
            } else {
                $param['cb_avatar'] = $url;
            }
            unset($user->type);
            try {
                $user->fill($param);
                $user->save();

                return response()->json([
                    'msg' => 'Cập nhật avatar thành công.',
                    'url' => $url,
                ]);
            } catch (ModelNotFoundException $e) {
                return response()->json(['error' => $e->getMessage()]);
            }

        }
    }
}
