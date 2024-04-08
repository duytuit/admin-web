<?php

namespace Modules\Assets\Http\Controllers;

use App\Filter\MaintenaceAssetFilter;
use App\Helpers\Files;
use App\Http\Controllers\Controller;
use App\Http\Requests\AssetCategory\Add;
use App\Http\Requests\AssetCategory\Show;
use App\Http\Requests\AssetCategory\Update;
//use App\Models\MaintenanceAsset;
use App\Repositories\AssetCategory\AssetCategoryRespository;
//use App\Repositories\MaintenanceAsset\MaintenanceAssetRespository;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;
use Modules\Assets\Entities\MaintenanceAsset;
use Modules\Assets\Repositories\MaintenanceAsset\MaintenanceAssetRespository;

class MaintenanceAssetsController extends Controller
{
    protected $_maintenanceAssetRespository;

    public function __construct(MaintenanceAssetRespository $maintenanceAssetRespository)
    {
        $this->_maintenanceAssetRespository = $maintenanceAssetRespository;
    }

    /**
     * @OA\GET(
     *     path="/api/v1/maintenance-asset",
     *     tags={"Maintenance Asset"},
     *     summary="Maintenance Asset List",
     *     description="Maintenance Asset List",
     *     operationId="maintenance_asset_list",
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
     *         description="Limit",
     *         in="path",
     *         name="limit",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         description="Page",
     *         in="path",
     *         name="page",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         description="Department Id",
     *         in="path",
     *         name="department_id",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         description="Asset Id",
     *         in="path",
     *         name="asset_id",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         description="Start Date",
     *         in="path",
     *         name="start_date",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         description="End Date",
     *         in="path",
     *         name="end_date",
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
            $limit = isset($request->limit) ? $request->limit : 10;
            $page = isset($request->page) ? $request->page : 1;

            $maintenanceAssets = $this->_maintenanceAssetRespository->filterByBuildingId($request->building_id);
            $maintenanceAssets = MaintenaceAssetFilter::index($maintenanceAssets, $request);
            $offSet = ($page * $limit) - $limit;
            $itemsForCurrentPage = array_slice($maintenanceAssets->toArray(), $offSet, $limit, true);
            $_maintenanceAssets = new LengthAwarePaginator($itemsForCurrentPage, count($maintenanceAssets), $limit, $page, []);
            $paging = [
                'total' => $_maintenanceAssets->total(),
                'currentPage' => $_maintenanceAssets->count(),
                'lastPage' => $_maintenanceAssets->lastPage(),
            ];

            $_maintenanceAssetArr = [];
            foreach($_maintenanceAssets as $_maintenanceAsset) {
                $_maintenanceAsset = (object)$_maintenanceAsset;
                @$_maintenanceAsset->attach_file = json_decode(@$_maintenanceAsset->attach_file);
                $asset = (object)@$_maintenanceAsset->asset;
                @$asset->images = json_decode(@$asset->images);
                $_maintenanceAsset->asset = $asset;
                array_push($_maintenanceAssetArr, $_maintenanceAsset);
            }

            return $this->sendResponsePaging($_maintenanceAssetArr, $paging, 200, 'Lấy thông tin thành công.');
        } catch (Exception $e) {
            Log::channel('maintenance_asset')->error($e->getMessage());
            return $this->sendError($e->getMessage(), [], 500);
        }
    }

    /**
     * @OA\POST(
     *     path="/api/v1/maintenance-asset/add",
     *     tags={"Maintenance Asset"},
     *     summary="Add Maintenance Asset",
     *     description="Add Maintenance Asset",
     *     operationId="maintenance_asset_add",
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
     *     @OA\Parameter(
     *         description="AssetId",
     *         in="path",
     *         name="asset_id",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         description="Maintenance Time",
     *         in="path",
     *         name="maintenance_time",
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
    public function add(Request $request)
    {
        try {
            $user = (object)$request->get('user');
            $attributes = [
                'building_id' => $request->building_id,
                'title' => $request->title,
                'asset_id' => $request->asset_id,
                'maintenance_time' => $request->maintenance_time,
                'user_id' => $user->user_id,
                'status' => MaintenanceAsset::STATUS_PEDDING,
            ];
            $maintenanceAsset = $this->_maintenanceAssetRespository->create($attributes);
            if($maintenanceAsset) {
                $this->_maintenanceAssetRespository->reloadByBuildingId($request->building_id);
                $this->_maintenanceAssetRespository->reloadById($maintenanceAsset->id);
                return $this->sendResponse([], 200, 'Add maintenance asset successfully.');
            } else {
                return $this->sendResponse([], 501, 'Add maintenance asset failure.');
            }
        } catch (Exception $e) {
            \DB::rollBack();
            Log::channel('maintenance_asset')->error($e->getMessage());
            return $this->sendError($e->getMessage(), [], 500);
        }
    }

    /**
     * @OA\POST(
     *     path="/api/v1/maintenance-asset/show",
     *     tags={"Maintenance Asset"},
     *     summary="Show Maintenance Asset",
     *     description="Show Maintenance Asset",
     *     operationId="maintenance_asset_show",
     *     @OA\Parameter(
     *         description="Maintenance Asset Id",
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
    public function show(Request $request)
    {
        try {
            $maintenanceAsset = $this->_maintenanceAssetRespository->filterById($request->id);
            if($maintenanceAsset) {
                @$maintenanceAsset->attach_file = json_decode(@$maintenanceAsset->attach_file);
                @$maintenanceAsset->asset->images = json_decode(@$maintenanceAsset->asset->images);
            }
            return $this->sendResponse($maintenanceAsset, 200, 'Get Asset successfully.');
        } catch (Exception $e) {
            Log::channel('maintenance_asset')->error($e->getMessage());
            return $this->sendError($e->getMessage(), [], 500);
        }
    }

    /**
     * @OA\PUT(
     *     path="/api/v1/maintenance-asset/update",
     *     tags={"Maintenance Asset"},
     *     summary="Update Maintenance Asset",
     *     description="Update Maintenance Asset",
     *     operationId="maintenance_asset_update",
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
     *     @OA\Parameter(
     *         description="AssetId",
     *         in="path",
     *         name="asset_id",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         description="Maintenance Time",
     *         in="path",
     *         name="maintenance_time",
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
    public function update(Request $request)
    {
        try {
            $id = $request->id;
            $attributes = [
                'title' => $request->title,
                'asset_id' => $request->asset_id,
                'maintenance_time' => $request->maintenance_time,
            ];
            $maintenanceAsset = $this->_maintenanceAssetRespository->update($id, $attributes);
            if($maintenanceAsset) {
                $this->_maintenanceAssetRespository->reloadByBuildingId($maintenanceAsset->building_id);
                $this->_maintenanceAssetRespository->reloadById($maintenanceAsset->id);
                return $this->sendResponse([], 200, 'Update maintenance asset successfully.');
            } else {
                return $this->sendResponse([], 501, 'Update maintenance asset failure.');
            }
        } catch (Exception $e) {
            Log::channel('maintenance_asset')->error($e->getMessage());
            return $this->sendError($e->getMessage(), [], 500);
        }
    }

    /**
     * @OA\PUT(
     *     path="/api/v1/maintenance-asset/update-status",
     *     tags={"Maintenance Asset"},
     *     summary="Update Status Maintenance Asset",
     *     description="Update Status Maintenance Asset",
     *     operationId="maintenance_asset_update_status",
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
     *         description="Description",
     *         in="path",
     *         name="description",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         description="Price",
     *         in="path",
     *         name="price",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         description="Images",
     *         in="path",
     *         name="attach_file",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         description="Status",
     *         in="path",
     *         name="status",
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
    public function updateStatus(Request $request)
    {
        try {
            $id = $request->id;
            $attributes = [
                'description' => $request->description,
                'price' => $request->price,
                'status' => $request->status,
            ];

            if(isset($request->attach_file) && $request->attach_file != null) {
                $files = json_decode($request->attach_file);
                $fileArr = [];
                foreach($files as $_file) {
                    $file = Files::uploadBase64Version2($_file, 'maintenance_asset');
                    if(!$file) {
                        return $this->sendError("Định dạng file không chính xác", [], 500);
                    }
                    array_push($fileArr, $file["hash_file"]);
                }
                $attributes["domain"] = env("DOMAIN_MEDIA_URL");
                $attributes["attach_file"] = json_encode($fileArr);
            } else {
                $attributes["attach_file"] = "[]";
            }

            $maintenanceAsset = $this->_maintenanceAssetRespository->update($id, $attributes);
            if($maintenanceAsset) {
                $this->_maintenanceAssetRespository->reloadByBuildingId($maintenanceAsset->building_id);
                $this->_maintenanceAssetRespository->reloadById($maintenanceAsset->id);
                return $this->sendResponse([], 200, 'Update status maintenance asset successfully.');
            } else {
                return $this->sendResponse([], 501, 'Update status maintenance asset failure.');
            }
        } catch (Exception $e) {
            Log::channel('maintenance_asset')->error($e->getMessage());
            return $this->sendError($e->getMessage(), [], 500);
        }
    }

    /**
     * @OA\DELETE(
     *     path="/api/v1/maintenance-asset/delete",
     *     tags={"Maintenance Asset"},
     *     summary="Delete Maintenance Asset",
     *     description="Delete Maintenance Asset",
     *     operationId="maintenance_asset_delete",
     *     @OA\Parameter(
     *         description="Maintenance Asset Id",
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
    public function delete(Request $request)
    {
        try {
            $maintenanceAsset = $this->_maintenanceAssetRespository->find($request->id);
            if($maintenanceAsset) {
                $this->_maintenanceAssetRespository->delete($maintenanceAsset->id);
                $this->_maintenanceAssetRespository->deleteRedisCache($maintenanceAsset);
                return $this->sendResponse([], 200, 'Delete maintenance asset successfully.');
            } else {
                return $this->sendResponse([], 501, 'Delete maintenance asset failure.');
            }
        } catch (Exception $e) {
            Log::channel('maintenance_asset')->error($e->getMessage());
            return $this->sendError($e->getMessage(), [], 500);
        }
    }
}
