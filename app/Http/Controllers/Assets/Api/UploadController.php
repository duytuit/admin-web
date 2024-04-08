<?php

namespace App\Http\Controllers\Assets\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Storage;
use App\Commons\Helper;
use Illuminate\Support\Facades\Validator;

class UploadController extends Controller
{
    
    public function upload(Request $request)
    {
        try {

            Validator::make($request->all(),[
                'attach_files'=>'required'
            ])->validate();;

            if (!$request->hasFile('attach_files')) {
                $responseData = [
                    'success' => false,
                    'message' => 'không có file'
                ];
                return response()->json($responseData);
            }

            $files = $request->file('attach_files');
            $pathFiles = [];

            foreach ($files as $file) {
                $extension = $file->getClientOriginalExtension();
                $check = in_array(strtolower($extension),Helper::FILE_MIME_TYPES);
                if($check) {
                    $fileName = strtolower($file->getClientOriginalName());
                    $urlFile = 'assets/' . $fileName;
                    $file->move(storage_path('assets/'), $fileName);
                    $localFile = \File::get($urlFile);
                    Storage::disk('ftp')->put('assets/'.$fileName, $localFile);
                    $tempPath = storage_path($urlFile);

                    $pathFile = env('DOMAIN_MEDIA_URL')."images/building_care/assets/".$fileName;
                    array_push($pathFiles, $pathFile);
                    \File::delete($tempPath);
                } 
            }
            if($pathFiles){
                $responseData = [
                    'success' => true,
                    'data' => $pathFiles,
                    'message' => 'Upload file thành công'
                ];
                return response()->json($responseData);
            }else{
                $responseData = [
                    'success' => false,
                    'data' => [],
                    'message' => 'không thành công'
                ];
                return response()->json($responseData);
            }
            
        }
        catch (Exception $exception) {
            $responseData = [
                'success' => false,
                'data' => [],
                'message' => $exception->getMessage()
            ];
            return response()->json($responseData);
        }

    }
}
