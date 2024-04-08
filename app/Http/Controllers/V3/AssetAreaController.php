<?php

namespace App\Http\Controllers\V3;

use App\Commons\Api;
use App\Commons\ApiResponse;
use App\Http\Controllers\BuildingController;
use App\Http\Requests\V3\Area\AreaUpdate;
use App\Http\Requests\V3\Area\AreaAdd;
use App\Repositories\V3\AreaRepository\AreaRepository;
use Illuminate\Http\Request;
use Exception;

class AssetAreaController extends BuildingController
{

    /** @var AreaRepository $repository */
    protected $repository;

    /**
     * @param AreaRepository $repository
     * @param Request $request
     * @throws Exception
    */
    public function __construct(
        AreaRepository $repository,
        Request $request
    )
    {
        $this->repository = $repository;
        parent::__construct($request);
    }


    public function index()
    {

        $data = [
            'meta_title'=>"QL khu vực"
        ];

        return view('v3.assets.index',$data)->with("#asset_area");

    }

    public function store(AreaAdd $request)
    {

        $data = [
            'building_id'=>$this->building_active_id,
            'title'=>$request->get('title'),
            'code'=>$request->get('code'),
            'note'=>$request->get('note')
        ];

        $area = $this->repository->create($data);

        if ($area) {
            return ApiResponse::responseSuccess([]);
        }
        else {
            return ApiResponse::responseError([]);
        }

    }

    public function edit()
    {

    }

    public function update(AreaUpdate $request, $asset_area_id)
    {
        $data = [
            'title'=>$request->get('title'),
            'code'=>$request->get('code'),
            'note'=>$request->get('note')
        ];

//        $response = Api::PUT('/api/v2/area/update',$data,true);

        $area = $this->repository->updateOrCreate([
            'id' => $asset_area_id
        ],
            $data
        );

        if ($area) {
            return ApiResponse::responseSuccess([]);
        }
        else {
            return ApiResponse::responseError([]);
        }
    }

    public function destroy($asset_id)
    {

//        $response = Api::DELETE('/api/v2/area/delete',['id'=>$asset_id]);

        $this->repository->forceDelete($asset_id);

        return ApiResponse::responseSuccess([]);
    }

    public function delete(Request $request)
    {
        $ids = $request->get('ids');

        $ids = \GuzzleHttp\json_decode($ids);

        $this->repository->forceDelete($ids);

        return ApiResponse::responseSuccess([]);

    }

}
