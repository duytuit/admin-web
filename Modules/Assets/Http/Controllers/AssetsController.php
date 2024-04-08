<?php

namespace Modules\Assets\Http\Controllers;

use App\Filter\AssetFilter;
use App\Helpers\Files;
use App\Http\Controllers\Controller;
//use App\Http\Requests\Asset\Add;
//use App\Http\Requests\Asset\Show;
//use App\Http\Requests\Asset\Update;
//use App\Models\MaintenanceAsset;
//use App\Repositories\Asset\AssetRespository;
//use App\Repositories\MaintenanceAsset\MaintenanceAssetRespository;
//use App\Repositories\Period\PeriodRespository;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;
use Modules\Assets\Entities\MaintenanceAsset;
use Modules\Assets\Http\Requests\Asset\Add;
use Modules\Assets\Http\Requests\Asset\Show;
use Modules\Assets\Http\Requests\Asset\Update;
use Modules\Assets\Repositories\Asset\AssetRespository;
use Modules\Assets\Repositories\MaintenanceAsset\MaintenanceAssetRespository;
use Modules\Assets\Repositories\Period\PeriodRespository;

class AssetsController extends Controller
{
    protected $_assetRespository;
    protected $_periodRespository;
    protected $_maintenanceAssetRespository;

    public function __construct(AssetRespository $assetRespository, PeriodRespository $periodRespository, MaintenanceAssetRespository $maintenanceAssetRespository)
    {
        $this->_assetRespository = $assetRespository;
        $this->_periodRespository = $periodRespository;
        $this->_maintenanceAssetRespository = $maintenanceAssetRespository;
    }

    /**
     * @OA\GET(
     *     path="/api/v1/asset",
     *     tags={"Asset"},
     *     summary="Asset List",
     *     description="Asset List",
     *     operationId="asset",
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
            $limit = isset($request->limit) ? $request->limit : 10;
            $page = isset($request->page) ? $request->page : 1;

            $assets = $this->_assetRespository->filterByBuildingId($request->building_id);
            $assets = AssetFilter::index($assets, $request);

            $offSet = ($page * $limit) - $limit;
            $itemsForCurrentPage = array_slice($assets->toArray(), $offSet, $limit, true);
            $_assets = new LengthAwarePaginator($itemsForCurrentPage, count($assets), $limit, $page, []);
            $paging = [
                'total' => $_assets->total(),
                'currentPage' => $_assets->count(),
                'lastPage' => $_assets->lastPage(),
            ];

            $_assetArr = [];
            foreach($_assets as $_asset) {
                $_asset = (object)$_asset;
                $_asset->images = json_decode($_asset->images);
                array_push($_assetArr, $_asset);
            }

            return $this->sendResponsePaging($_assetArr, $paging, 200, 'Lấy thông tin thành công.');
        } catch (Exception $e) {
            Log::channel('asset')->error($e->getMessage());
            return $this->sendError($e->getMessage(), [], 500);
        }
    }

    /**
     * @OA\POST(
     *     path="/api/v1/asset/add",
     *     tags={"Asset"},
     *     summary="Add Asset",
     *     description="Add Asset",
     *     operationId="asset_add",
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
     *         description="Asset Name",
     *         in="path",
     *         name="name",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         description="Asset Category",
     *         in="path",
     *         name="asset_category_id",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         description="Quantity",
     *         in="path",
     *         name="quantity",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         description="Kỳ bảo trì",
     *         in="path",
     *         name="bdc_period_id",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         description="Ngày bắt đầu bảo trì",
     *         in="path",
     *         name="maintainance_date",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         description="Khu vực",
     *         in="path",
     *         name="area_id",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         description="Bộ phận",
     *         in="path",
     *         name="department_id",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         description="Người quản lý",
     *         in="path",
     *         name="follower",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         description="Ngày bảo hành",
     *         in="path",
     *         name="warranty_period",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         description="Hình ảnh",
     *         in="path",
     *         name="images",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         description="Ghi chú",
     *         in="path",
     *         name="asset_note",
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
            \DB::beginTransaction();
            $periodId = isset($request->bdc_period_id) && $request->bdc_period_id != null ? $request->bdc_period_id : 0;
            $attributes = [
                'bdc_building_id' => $request->building_id,
                'name' => $request->name,
                'asset_category_id' => $request->asset_category_id,
                'quantity' => $request->quantity,
                'bdc_period_id' => $periodId,
                'maintainance_date' => $request->maintainance_date,
                'area_id' => $request->area_id,
                'department_id' => $request->department_id,
                'follower' => $request->follower,
                'warranty_period' => $request->warranty_period,
                'asset_note' => $request->asset_note,
            ];
            $asset = $this->_assetRespository->create($attributes);
            if($asset) {
                if($periodId) {
                    $period = $this->_periodRespository->find($periodId);
                    if($period) {
                        $carbonFC = $period->carbon_fc;
                        $count = 0;
                        switch($carbonFC) {
                            case 1:
                                $count = 12;
                                break;
                            case 2:
                                $count = 6;
                                break;
                            case 3:
                                $count = 4;
                                break;
                            case 4:
                                $count = 3;
                                break;
                            case 6:
                                $count = 2;
                                break;
                            case 12:
                                $count = 1;
                                break;
                        }
                        $date = Carbon::parse($request->maintainance_date);
                        $currentDate = Carbon::now();
                        for($i = 0; $i < $count; $i++) {
                            $date = $i == 0 ? $date : $date->addMonths($carbonFC);
                            if($date->year > $currentDate->year) {
                                continue;
                            }
                            $title = "Bảo trì " . strtolower($asset->name) . " tháng " . $date->month;
                            $attributePeriod = [
                                'building_id' => $request->building_id,
                                'title' => $title,
                                'asset_id' => $asset->id,
                                'maintenance_time' => $date,
                                'user_id' => '',
                                'description' => '',
                                'price' => 0,
                                'status' => MaintenanceAsset::STATUS_PEDDING,
                            ];
                            $this->_maintenanceAssetRespository->create($attributePeriod);
                        }
                    }
                }
                if(isset($request->images) && $request->images != null) {
                    $images = json_decode($request->images);
                    $imageArr = [];
                    foreach($images as $_image) {
                        $image = Files::uploadBase64($_image, 'assets');
                        array_push($imageArr, $image);
                    }
                    $asset->domain = env("DOMAIN_MEDIA_URL");
                    $asset->images = json_encode($imageArr);
                    $asset->save();
                }

                $this->_assetRespository->reloadById($asset->id);
                $this->_assetRespository->reloadByBuildingId($request->building_id);

                \DB::commit();
                return $this->sendResponse([], 200, 'Add Asset successfully.');
            } else {
                \DB::rollBack();
                return $this->sendResponse([], 200, 'Add Asset failure.');
            }
        } catch (Exception $e) {
            \DB::rollBack();
            Log::channel('asset')->error($e->getMessage());
            return $this->sendError($e->getMessage(), [], 500);
        }
    }

    /**
     * @OA\POST(
     *     path="/api/v1/asset/show",
     *     tags={"Asset"},
     *     summary="Show Asset",
     *     description="Show Asset",
     *     operationId="asset_show",
     *     @OA\Parameter(
     *         description="Asset Id",
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
            $asset = $this->_assetRespository->filterById($request->id);
            $asset->images = json_decode($asset->images);
            return $this->sendResponse($asset, 200, 'Get Asset successfully.');
        } catch (Exception $e) {
            Log::channel('asset')->error($e->getMessage());
            return $this->sendError($e->getMessage(), [], 500);
        }
    }

    /**
     * @OA\PUT(
     *     path="/api/v1/asset/update",
     *     tags={"Asset"},
     *     summary="Update Asset",
     *     description="Update Asset",
     *     operationId="asset_update",
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
     *         description="Asset Name",
     *         in="path",
     *         name="name",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         description="Asset Category",
     *         in="path",
     *         name="asset_category_id",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         description="Quantity",
     *         in="path",
     *         name="quantity",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         description="Kỳ bảo trì",
     *         in="path",
     *         name="bdc_period_id",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         description="Ngày bắt đầu bảo trì",
     *         in="path",
     *         name="maintainance_date",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         description="Khu vực",
     *         in="path",
     *         name="area_id",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         description="Bộ phận",
     *         in="path",
     *         name="department_id",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         description="Người quản lý",
     *         in="path",
     *         name="follower",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         description="Ngày bảo hành",
     *         in="path",
     *         name="warranty_period",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         description="Hình ảnh",
     *         in="path",
     *         name="images",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         description="Ghi chú",
     *         in="path",
     *         name="asset_note",
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
            \DB::beginTransaction();
            $id = $request->id;
            $periodId = isset($request->bdc_period_id) && $request->bdc_period_id != null ? $request->bdc_period_id : 0;
            $attributes = [
                'name' => $request->name,
                'asset_category_id' => $request->asset_category_id,
                'quantity' => $request->quantity,
                'bdc_period_id' => $request->bdc_period_id,
                'maintainance_date' => $request->maintainance_date,
                'area_id' => $request->area_id,
                'department_id' => $request->department_id,
                'follower' => $request->follower,
                'warranty_period' => $request->warranty_period,
                'asset_note' => $request->asset_note,
            ];
            $asset = $this->_assetRespository->update($id, $attributes);

            if($asset) {
                if($periodId) {
                    $period = $this->_periodRespository->find($periodId);
                    if($period) {
                        $carbonFC = $period->carbon_fc;
                        $count = 0;
                        switch($carbonFC) {
                            case 1:
                                $count = 12;
                                break;
                            case 2:
                                $count = 6;
                                break;
                            case 3:
                                $count = 4;
                                break;
                            case 4:
                                $count = 3;
                                break;
                            case 6:
                                $count = 2;
                                break;
                            case 12:
                                $count = 1;
                                break;
                        }
                        $date = Carbon::parse($request->maintainance_date);
                        $currentDate = Carbon::now();
                        $this->_maintenanceAssetRespository->findColumns([
                            'building_id' => $asset->bdc_building_id,
                            'status' => MaintenanceAsset::STATUS_SUCCESS,
                            'asset_id' => $asset->id
                        ])->delete();
                        for($i = 0; $i < $count; $i++) {
                            $date = $i == 0 ? $date : $date->addMonths($carbonFC);
                            if($date->year > $currentDate->year) {
                                continue;
                            }
                            $title = "Bảo trì " . strtolower($asset->name) . " tháng " . $date->month;
                            $attributePeriod = [
                                'building_id' => $asset->bdc_building_id,
                                'title' => $title,
                                'asset_id' => $asset->id,
                                'maintenance_time' => $date,
                                'user_id' => '',
                                'description' => '',
                                'price' => 0,
                                'status' => MaintenanceAsset::STATUS_PEDDING,
                            ];
                            $this->_maintenanceAssetRespository->create($attributePeriod);
                        }
                    }
                }

                if(isset($request->images) && $request->images != null) {
                    $images = json_decode($request->images);
                    $imageArr = [];
                    foreach($images as $_image) {
                        $image = Files::uploadBase64($_image, 'assets');
                        array_push($imageArr, $image);
                    }
                    $asset->images = json_encode($imageArr);
                    $asset->save();
                }

                $this->_assetRespository->reloadById($id);
                $this->_assetRespository->reloadByBuildingId($asset->bdc_building_id);

                \DB::commit();
                return $this->sendResponse([], 200, 'Update Asset successfully.');
            } else {
                \DB::rollBack();
                return $this->sendResponse([], 200, 'Update Asset failure.');
            }
        } catch (Exception $e) {
            \DB::rollBack();
            Log::channel('asset')->error($e->getMessage());
            return $this->sendError($e->getMessage(), [], 500);
        }
    }

    /**
     * @OA\DELETE(
     *     path="/api/v1/asset/delete",
     *     tags={"Asset"},
     *     summary="Delete Asset",
     *     description="Delete Asset",
     *     operationId="asset_delete",
     *     @OA\Parameter(
     *         description="Asset Id",
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
            $asset = $this->_assetRespository->find($request->id);
            if($asset) {
                $this->_assetRespository->delete($asset->id);
                $this->_assetRespository->deleteRedisCache($asset);
                return $this->sendResponse([], 200, 'Delete asset successfully.');
            } else {
                return $this->sendResponse([], 501, 'Delete asset failure.');
            }
        } catch (Exception $e) {
            Log::channel('asset')->error($e->getMessage());
            return $this->sendError($e->getMessage(), [], 500);
        }
    }
}
