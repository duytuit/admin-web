<?php

namespace App\Http\Controllers\Upload;

use App\Commons\Helper;
use App\Repositories\PublicUsers\PublicUsersProfileRespository;
use App\Repositories\SystemFiles\SystemFilesRespository;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Validator;

class UploadController extends Controller
{
    use ApiResponse;

    const DUPLICATE = 19999;
    const LOGIN_FAIL = 10000;

    private $model;

    public function __construct( PublicUsersProfileRespository $model)
    {
        $this->model = $model;
        Carbon::setLocale('vi');
    }

    public function upload(Request $request, SystemFilesRespository $filesRespository)
    {
        $info = Auth::guard('public_user_v2')->user()->infoApp;

        $validator = Validator::make($request->all(), [
            'files.*' => 'file|max:4096|mimes:jpeg,jpg,png,gif,pdf,docx,doc,xlsx,csv,xls',
            'type' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->validateFail($validator->errors() );
        }
        $urls = [];
        switch ($request->type) {
            case 'diary': {
                if ($request->hasFile('files')) {
                    $files = $request->file('files');
                    foreach ($files as $file) {
                        $checkFile = $filesRespository->checkMultiFile($file,$request->type,$request->building_id,$info);
                        if( $checkFile['status'] == 'OK' ) {
                            $urls[] = $checkFile['data']['url'];
                        }
                    }
                }
                break;
            }
            case 'avatar': {
                if ($request->hasFile('files')) {
                    $files = $request->file('files');
                    foreach ($files as $file) {
                        $checkFile = $filesRespository->checkMultiFile($file,$request->type,$request->building_id,$info);
                        if( $checkFile['status'] == 'OK' ) {
                            $urls[] = $checkFile['data']['url'];
                        }
                    }
                    if($urls){
                        $this->model->update(['avatar'=>$urls[0]],$info->id,'id');
                    }
                }
                break;
            }
            default : {
                if ($request->hasFile('files')) {
                    $files = $request->file('files');
                    foreach ($files as $file) {
                        $checkFile = $filesRespository->checkMultiFile($file,$request->type,$request->building_id,$info);
                        if( $checkFile['status'] == 'OK' ) {
                            $urls[] = $checkFile['data']['url'];
                        }
                    }
                }
                break;
            }
        }

        return $this->responseSuccess($urls, 'upload file thành công');
    }
    function uploadV2(Request $request)
    {
        $return  = Helper::doUploadSingle($request);
        return response()->json($return, 200);
    }
    public function upload_v2(Request $request)
    {
        return $this->uploadV2($request);
    }
    public function upload_ckeditor(Request $request)
    {
        $file = $request->file('upload');
        $rs_file = Helper::doUpload($file, $file->getClientOriginalName(),@$request->folder);
        if ($rs_file->success == true) {
            $url = $rs_file->origin;
            return response()->json(['fileName' => $file->getClientOriginalName(), 'uploaded' => 1, 'url' => $url]);
        } else {
            $url = '';
            return response()->json(['fileName' => null, 'uploaded' => 0, "error" => [
                "message" => "Tải ảnh thất bại."
            ]]);
        }
      
    }
}
