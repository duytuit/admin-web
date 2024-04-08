<?php

namespace App\Http\Controllers\WorkDiary_v2\Category;

use Illuminate\Http\Request;
use App\Http\Controllers\BuildingController;
use Illuminate\Support\Facades\Cookie;
use Validator;
use Carbon\Carbon;
use GuzzleHttp\Client;
use App\Http\Requests\WorkDiaryV2\Category\CategoryRequest;
use App\Commons\Helper;

class CategoryController extends BuildingController
{

    public function __construct(
        Request $request
    )
    {
          ////$this->middleware('route_permision');
          parent::__construct($request);
    }
    public function store(CategoryRequest $request)
    {
        $category= $request->all();
        $category['building_id']=$this->building_active_id;

        $_headers = [
              'Authorization' =>'Bearer '.Helper::getToken(\Auth::user()->id)
           ];
        $_client = new \GuzzleHttp\Client(['headers' => $_headers]);

        $responseCategory = $_client->request('POST',env('APP_URL').'/api/admin/v1/task-category/add',[
            'json' => $category
        ]);
        $responseData = [
            'success' => true,
            'message' => 'Thêm mới thành công!'
        ];

        return response()->json($responseData);
    }
    public function update(CategoryRequest $request, $id = 0)
    {
        $data = $request->except('_token');
        $_headers = [
              'Authorization' =>'Bearer '.Helper::getToken(\Auth::user()->id)
           ];
        $_client = new \GuzzleHttp\Client(['headers' => $_headers]);
        $responseCategory = $_client->request('PUT',env('APP_URL').'/api/admin/v1/task-category/update',[
            'json' => $data
        ]);
        $result_Category = json_decode((string) $responseCategory->getBody(), true);
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
        $responseCategory = $_client->request('delete',env('APP_URL').'/api/admin/v1/task-category/delete?id='.$id);
        $result_Category = json_decode((string) $responseCategory->getBody(), true);
        $request->session()->flash('success', 'Xóa thành công!');
    }
}
