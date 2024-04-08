<?php

namespace Modules\Assets\Http\Controllers;

use App\Http\Controllers\Controller;
//use App\Http\Requests\AssetCategory\Add;
//use App\Http\Requests\AssetCategory\Show;
//use App\Http\Requests\AssetCategory\Update;
//use App\Repositories\AssetCategory\AssetCategoryRespository;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Modules\Assets\Http\Requests\AssetCategory\Add;
use Modules\Assets\Http\Requests\AssetCategory\Show;
use Modules\Assets\Http\Requests\AssetCategory\Update;
use Modules\Assets\Repositories\AssetCategory\AssetCategoryRespository;

class AssetCategoriesController extends Controller
{
    protected $_assetCategoryRespository;

    public function __construct(AssetCategoryRespository $assetCategoryRespository)
    {
        $this->_assetCategoryRespository = $assetCategoryRespository;
    }

    /**
     * @OA\GET(
     *     path="/api/v1/asset-category",
     *     tags={"Asset Category"},
     *     summary="Asset Category List",
     *     description="Asset Category List",
     *     operationId="asset_category",
     *     @OA\Parameter(
     *         description="Building Id",
     *         in="path",
     *         name="building_id",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *   @OA\Response(response=200, description="successful operation"),
     *   @OA\Response(response=406, description="not acceptable"),
     *   @OA\Response(response=500, description="internal server error")
     * )
     */
    public function index(Request $request)
    {
        try {
            $assetCategories = $this->_assetCategoryRespository->filterByBuildingId($request->building_id);
            return $this->sendResponse($assetCategories, 200, 'Lấy thông tin thành công.');
        } catch (Exception $e) {
            Log::channel('asset_category')->error($e->getMessage());
            return $this->sendError($e->getMessage(), [], 500);
        }
    }

    /**
     * @OA\POST(
     *     path="/api/v1/asset-category/add",
     *     tags={"Asset Category"},
     *     summary="Add Asset Category",
     *     description="Add Asset Category",
     *     operationId="asset_category_add",
     *     @OA\Parameter(
     *         description="Building Id",
     *         in="path",
     *         name="building_id",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         description="Title",
     *         in="path",
     *         name="title",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *   @OA\Response(response=200, description="successful operation"),
     *   @OA\Response(response=406, description="not acceptable"),
     *   @OA\Response(response=500, description="internal server error")
     * )
     */
    public function add(Add $request)
    {
        try {
            $attributes = [
                'building_id' => $request->building_id,
                'title' => $request->title,
            ];
            $assetCategory = $this->_assetCategoryRespository->create($attributes);
            if($assetCategory) {
                $this->_assetCategoryRespository->reloadById($assetCategory->id);
                $this->_assetCategoryRespository->reloadByBuildingId($request->building_id);
                return $this->sendResponse([], 200, 'Add Asset Category successfully.');
            } else {
                return $this->sendResponse([], 200, 'Add Asset Category failure.');
            }
        } catch (Exception $e) {
            Log::channel('asset_category')->error($e->getMessage());
            return $this->sendError($e->getMessage(), [], 500);
        }
    }

    /**
     * @OA\POST(
     *     path="/api/v1/asset-category/show",
     *     tags={"Asset Category"},
     *     summary="Show Asset Category",
     *     description="Show Asset Category",
     *     operationId="asset_category_show",
     *     @OA\Parameter(
     *         description="Asset Category Id",
     *         in="path",
     *         name="id",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *   @OA\Response(response=200, description="successful operation"),
     *   @OA\Response(response=406, description="not acceptable"),
     *   @OA\Response(response=500, description="internal server error")
     * )
     */
    public function show(Show $request)
    {
        try {
            $assetCategory = $this->_assetCategoryRespository->filterById($request->id);
            return $this->sendResponse($assetCategory, 200, 'Get Asset Category successfully.');
        } catch (Exception $e) {
            Log::channel('asset_category')->error($e->getMessage());
            return $this->sendError($e->getMessage(), [], 500);
        }
    }

    /**
     * @OA\PUT(
     *     path="/api/v1/asset-category/update",
     *     tags={"Asset Category"},
     *     summary="Update Asset Category",
     *     description="Update Asset Category",
     *     operationId="asset_category_update",
     *     @OA\Parameter(
     *         description="Id",
     *         in="path",
     *         name="id",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         description="Title",
     *         in="path",
     *         name="title",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *   @OA\Response(response=200, description="successful operation"),
     *   @OA\Response(response=406, description="not acceptable"),
     *   @OA\Response(response=500, description="internal server error")
     * )
     */
    public function update(Update $request)
    {
        try {
            $id = $request->id;
            $attributes = [
                'title' => $request->title,
            ];
            $assetCategory = $this->_assetCategoryRespository->update($id, $attributes);
            if($assetCategory) {
                $this->_assetCategoryRespository->reloadById($id);
                $this->_assetCategoryRespository->reloadByBuildingId($assetCategory->building_id);
                return $this->sendResponse([], 200, 'Update Asset Category successfully.');
            } else {
                return $this->sendResponse([], 200, 'Update Asset Category failure.');
            }
        } catch (Exception $e) {
            Log::channel('asset_category')->error($e->getMessage());
            return $this->sendError($e->getMessage(), [], 500);
        }
    }

    /**
     * @OA\DELETE(
     *     path="/api/v1/asset-category/delete",
     *     tags={"Asset Category"},
     *     summary="Delete Asset Category",
     *     description="Delete Asset Category",
     *     operationId="asset_category_delete",
     *     @OA\Parameter(
     *         description="Asset Category Id",
     *         in="path",
     *         name="id",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *   @OA\Response(response=200, description="successful operation"),
     *   @OA\Response(response=406, description="not acceptable"),
     *   @OA\Response(response=500, description="internal server error")
     * )
     */
    public function delete(Show $request)
    {
        try {
            $assetCategory = $this->_assetCategoryRespository->find($request->id);
            if($assetCategory) {
                $this->_assetCategoryRespository->delete($assetCategory->id);
                $this->_assetCategoryRespository->deleteRedisCache($assetCategory);
                return $this->sendResponse([], 200, 'Delete asset category successfully.');
            } else {
                return $this->sendResponse([], 501, 'Delete asset category failure.');
            }
        } catch (Exception $e) {
            Log::channel('asset_category')->error($e->getMessage());
            return $this->sendError($e->getMessage(), [], 500);
        }
    }
}
