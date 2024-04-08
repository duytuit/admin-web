<?php

namespace Modules\Assets\Http\Controllers;

use App\Http\Controllers\Controller;
//use App\Http\Requests\Area\AreaAdd;
//use App\Http\Requests\Area\AreaShow;
//use App\Http\Requests\Area\AreaUpdate;
//use App\Repositories\Area\AreaRespository;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Modules\Assets\Http\Requests\Area\AreaAdd;
use Modules\Assets\Http\Requests\Area\AreaShow;
use Modules\Assets\Http\Requests\Area\AreaUpdate;
use Modules\Assets\Repositories\Area\AreaRespository;

class AreasController extends Controller
{
    protected $_areaRespository;

    public function __construct(AreaRespository $areaRespository)
    {
        $this->_areaRespository = $areaRespository;
    }

    /**
     * @OA\GET(
     *     path="/api/v1/area",
     *     tags={"Area"},
     *     summary="Area List",
     *     description="Area List",
     *     operationId="area",
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
            $areas = $this->_areaRespository->filterByBuildingId($request->building_id);
            return $this->sendResponse($areas, 200, 'Lấy thông tin thành công.');
        } catch (Exception $e) {
            Log::channel('area')->error($e->getMessage());
            return $this->sendError($e->getMessage(), [], 500);
        }
    }

    /**
     * @OA\POST(
     *     path="/api/v1/area/add",
     *     tags={"Area"},
     *     summary="Add Area",
     *     description="Add Area",
     *     operationId="area_add",
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
     *         description="Code",
     *         in="path",
     *         name="code",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         description="Note",
     *         in="path",
     *         name="note",
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
    public function add(AreaAdd $request)
    {
        try {
            $attributes = [
                'building_id' => $request->building_id,
                'title' => $request->title,
                'code' => $request->code,
                'note' => $request->note,
            ];
            $area = $this->_areaRespository->create($attributes);
            if($area) {
                $this->_areaRespository->reloadById($area->id);
                $this->_areaRespository->reloadByBuildingId($request->building_id);
                return $this->sendResponse([], 200, 'Add Area successfully.');
            } else {
                return $this->sendResponse([], 200, 'Add Area failure.');
            }
        } catch (Exception $e) {
            Log::channel('area')->error($e->getMessage());
            return $this->sendError($e->getMessage(), [], 500);
        }
    }

    /**
     * @OA\POST(
     *     path="/api/v1/area/show",
     *     tags={"Area"},
     *     summary="Show Area",
     *     description="Show Area",
     *     operationId="area_show",
     *     @OA\Parameter(
     *         description="Area Id",
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
    public function show(AreaShow $request)
    {
        try {
            $area = $this->_areaRespository->filterById($request->id);
            return $this->sendResponse($area, 200, 'Get Area successfully.');
        } catch (Exception $e) {
            Log::channel('area')->error($e->getMessage());
            return $this->sendError($e->getMessage(), [], 500);
        }
    }

    /**
     * @OA\PUT(
     *     path="/api/v1/area/update",
     *     tags={"Area"},
     *     summary="Update Area",
     *     description="Update Area",
     *     operationId="area_update",
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
     *         description="Code",
     *         in="path",
     *         name="code",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         description="Note",
     *         in="path",
     *         name="note",
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
    public function update(AreaUpdate $request)
    {
        try {
            $id = $request->id;
            $attributes = [
                'title' => $request->title,
                'code' => $request->code,
                'note' => $request->note,
            ];
            $area = $this->_areaRespository->update($id, $attributes);
            if($area) {
                $this->_areaRespository->reloadById($id);
                $this->_areaRespository->reloadByBuildingId($area->building_id);
                return $this->sendResponse([], 200, 'Update Area successfully.');
            } else {
                return $this->sendResponse([], 200, 'Update Area failure.');
            }
        } catch (Exception $e) {
            Log::channel('area')->error($e->getMessage());
            return $this->sendError($e->getMessage(), [], 500);
        }
    }

    /**
     * @OA\DELETE(
     *     path="/api/v1/area/delete",
     *     tags={"Area"},
     *     summary="Delete Area",
     *     description="Delete Area",
     *     operationId="area_delete",
     *     @OA\Parameter(
     *         description="Area Id",
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
    public function delete(AreaShow $request)
    {
        try {
            $area = $this->_areaRespository->find($request->id);
            if($area) {
                $this->_areaRespository->delete($area->id);
                //Clear cache Area
                $this->_areaRespository->deleteRedisCache($area);
                return $this->sendResponse([], 200, 'Delete area successfully.');
            } else {
                return $this->sendResponse([], 501, 'Delete area failure.');
            }
        } catch (Exception $e) {
            Log::channel('area')->error($e->getMessage());
            return $this->sendError($e->getMessage(), [], 500);
        }
    }
}
