<?php

namespace App\Http\Controllers\V3;

use App\Commons\Api;
use App\Commons\ApiResponse;
use App\Http\Controllers\BuildingController;
use App\Repositories\V3\AreaRepository\AreaRepository;
use App\Repositories\V3\AssetCategoryRepository\AssetCategoryRepository;
use Illuminate\Http\Request;
use Exception;
use Modules\Assets\Http\Requests\AssetCategory\Add;

class AssetCategoryController extends BuildingController
{

    /** @var AssetCategoryRepository $repository */
    protected $repository;

    /**
     * @param AssetCategoryRepository $repository
     * @param Request $request
     * @throws Exception
     */
    public function __construct(
        AssetCategoryRepository $repository,
        Request $request
    )
    {
        $this->repository = $repository;
        parent::__construct($request);
    }

    public function index()
    {

        $data = [
            'meta_title'=>"QL danh mục Tài sản"
        ];

        return view('v3.assets.index',$data);

    }

    public function store(Add $request)
    {

        $data = [
            'building_id'=>$this->building_active_id,
            'title'=>$request->get('title'),
            'note'=>$request->get('note')
        ];

//        $response = Api::POST('/api/v2/asset-category/add',$data,true);

        $assetCategory = $this->repository->create($data);

        if ($assetCategory) {
            return ApiResponse::responseSuccess([]);
        }
        else {
            return ApiResponse::responseError([]);
        }

    }

    public function edit()
    {

    }

    public function update(Request $request,$asset_id)
    {
        $data = [
            'title'=>$request->get('title'),
            'note'=>$request->get('note')
        ];

//        $response = Api::PUT('/api/v2/asset-category/update',$data,true);

        $assetCategory = $this->repository->updateOrCreate([
            'id' => $asset_id,
        ], $data);


        if ($assetCategory) {
            return ApiResponse::responseSuccess([]);
        }
        else {
            return ApiResponse::responseError([]);
        }

    }

    public function destroy($asset_id)
    {
//        $response = Api::DELETE('/api/v2/asset-category/delete',['id'=>$asset_id]);

        $this->repository->forceDelete($asset_id);

        return ApiResponse::responseSuccess([]);

//        if ($response->success) {
//            return ApiResponse::responseSuccess([]);
//        }
//        else {
//            return ApiResponse::responseError([]);
//        }
    }

    public function delete(Request $request)
    {
        $ids = $request->get('ids');

        $ids = \GuzzleHttp\json_decode($ids);

        $this->repository->forceDelete($ids);

        return ApiResponse::responseSuccess([]);

//        $response = null;
//        foreach ($ids as $id) {
//            $response = Api::DELETE('/api/v2/asset-category/delete',['id'=>$id]);
//        }
//        if ($response->success) {
//            return ApiResponse::responseSuccess([]);
//        }
//        else {
//            return ApiResponse::responseError([]);
//        }

    }

}
