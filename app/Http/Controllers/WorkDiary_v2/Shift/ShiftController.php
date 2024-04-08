<?php

namespace App\Http\Controllers\WorkDiary_v2\Shift;

use Illuminate\Http\Request;
use App\Http\Controllers\BuildingController;
use Illuminate\Support\Facades\Cookie;
use Validator;
use Carbon\Carbon;
use GuzzleHttp\Client;
use App\Http\Requests\WorkDiaryV2\Shift\ShiftRequest;
use App\Commons\Helper;

class ShiftController extends BuildingController
{

    public function __construct(
        Request $request
    )
    {
          ////$this->middleware('route_permision');
          parent::__construct($request);
    }
    public function store(ShiftRequest $request)
    {
        $shift= $request->all();
        $shift['building_id']=$this->building_active_id;
        $_headers = [
              'Authorization' =>'Bearer '.Helper::getToken(\Auth::user()->id)
           ];
        $_client = new \GuzzleHttp\Client(['headers' => $_headers]);

        $responseShift = $_client->request('POST',env('APP_URL').'/api/admin/v1/work-shift/add',[
            'json' => $shift
        ]);
        $responseData = [
            'success' => true,
            'message' => 'Thêm mới thành công!'
        ];

        return response()->json($responseData);
    }
    public function update(ShiftRequest $request, $id = 0)
    {
        $data = $request->except('_token');
        $_headers = [
              'Authorization' =>'Bearer '.Helper::getToken(\Auth::user()->id)
           ];
        $_client = new \GuzzleHttp\Client(['headers' => $_headers]);
        $responseShift = $_client->request('PUT',env('APP_URL').'/api/admin/v1/work-shift/update',[
            'json' => $data
        ]);
        $responseData = [
            'success' => true,
            'message' => 'Cập nhập thành công!'
        ];

        return response()->json($responseData);
    }
    public function delete(Request $request)
    {
        $id = (int)$request->input('ids')[0];
        $_headers = [
              'Authorization' =>'Bearer '.Helper::getToken(\Auth::user()->id)
           ];
        $_client = new \GuzzleHttp\Client(['headers' => $_headers]);
        
        $responseShift = $_client->request('delete',env('APP_URL').'/api/admin/v1/work-shift/delete?id='.$id);

        $result_Shift = json_decode((string) $responseShift->getBody(), true);
        $request->session()->flash('success', 'Xóa thành công!');
    }
}
