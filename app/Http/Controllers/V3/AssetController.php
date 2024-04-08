<?php

namespace App\Http\Controllers\V3;

use App\Commons\Api;
use App\Commons\ApiResponse;
use App\Commons\Helper;
use App\Helpers\dBug;
use App\Helpers\Files;
use App\Http\Controllers\BuildingController;
use App\Models\Asset\AssetArea;
use App\Models\Asset\AssetCategory;
use App\Models\Department\Department;
use App\Models\V3\MaintenanceAsset;
use App\Repositories\Period\PeriodRepository;
use App\Repositories\V3\AreaRepository\AreaRepository;
use App\Repositories\V3\AssetCategoryRepository\AssetCategoryRepository;
use App\Repositories\V3\AssetRepository\AssetRepository;
use App\Repositories\V3\DepartmentRepository\DepartmentRepository;
use App\Repositories\V3\MaintenanceAssetRepository\MaintenanceAssetRepository;
use App\Repositories\V3\User\UserRepository;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Exception;

class AssetController extends BuildingController
{

    protected $areaRepository;
    protected $assetCategoryRepository;
    protected $departmentRepository;
    protected $repository;
    protected $userRepository;
    protected $periodRepository;
    protected $maintenanceAssetRepository;
    public function __construct(
        AreaRepository $areaRepository,
        AssetCategoryRepository $assetCategoryRepository,
        AssetRepository $repository,
        DepartmentRepository $departmentRepository,
        UserRepository $userRepository,
        PeriodRepository $periodRepository,
        MaintenanceAssetRepository $maintenanceAssetRepository,
        Request $request,
        $is_api = null
    )
    {
        $this->repository = $repository;
        $this->departmentRepository = $departmentRepository;
        $this->areaRepository = $areaRepository;
        $this->assetCategoryRepository = $assetCategoryRepository;
        $this->userRepository = $userRepository;
        $this->periodRepository = $periodRepository;
        $this->maintenanceAssetRepository = $maintenanceAssetRepository;
        parent::__construct($request, $is_api);
    }

    public function index(Request $request)
    {
        $data['meta_title'] = 'Thông tin tài sản';
        $data['filter'] = $request->all();
        $request->request->add(['building_id' => $this->building_active_id]);
        if(isset($data['filter']['asset_category_id'])){
            $data['_asset_category'] = AssetCategory::find($data['filter']['asset_category_id']);
        }
        if(isset($data['filter']['office_id'])){
            $data['_office_asset'] = AssetArea::find($data['filter']['office_id']);
        }
        if(isset($data['filter']['department_id'])){
            $data['_department_asset'] = Department::find($data['filter']['department_id']);
        }
        $array_search='';
        $i=0;
        foreach ($request->all() as $key => $value) {
            if($i == 0){
                $param='?'.$key.'='.(string)$value;
            }else{
                $param='&'.$key.'='.(string)$value;
            }
            $i++;
            $array_search.=$param;
        }

        $data['array_search'] = $array_search;
        $data['building_id'] = $this->building_active_id;
        return view('v3.assets.index',$data);
    }

    public function store(Request $request)
    {

        try {
            DB::beginTransaction();
            $periodId = isset($request->bdc_period_id) && $request->bdc_period_id != null ? $request->bdc_period_id : 0;

            $data = [
                'bdc_building_id'=>$this->building_active_id,
                'name'=>$request->get('name'),
                'asset_category_id'=>$request->get('asset_category_id'),
                'quantity'=>$request->get('quantity'),
                'bdc_period_id'=>$request->get('bdc_period_id',0),
                'maintainance_date'=>$request->get('maintainance_date'),
                'area_id'=>$request->get('area_id'),
                'department_id'=>$request->get('department_id'),
                'follower'=>$request->get('follower'),
                'warranty_period'=>$request->get('warranty_period'),
                'asset_note'=>$request->get('asset_note'),
            ];

            $asset = $this->repository->create($data);

            if ($asset) {

                if ($periodId) {
                    $period = $this->periodRepository->find($periodId);
                    if ($period) {
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
                                'building_id' => $this->building_active_id,
                                'title' => $title,
                                'asset_id' => $asset->id,
                                'maintenance_time' => $date,
                                'user_id' => '',
                                'description' => '',
                                'price' => 0,
                                'status' => MaintenanceAsset::STATUS_PEDDING,
                            ];
                            $this->maintenanceAssetRepository->create($attributePeriod);
                        }
                    }
                }

                if(isset($request->images) && $request->images != null) {
                    $asset->images = $request->images;
                    $asset->save();
                }

                DB::commit();
                return ApiResponse::responseSuccess([]);
            }
            else {
                DB::rollBack();
                return ApiResponse::responseError([]);
            }

        } catch (Exception $e) {
            DB::rollBack();
            Log::channel('asset')->error($e->getMessage());
            return ApiResponse::responseError([]);
        }

    }

    public function edit()
    {

    }

    public function show($asset_id,Request $request)
    {
        $id = $request->get('asset_id');
        $id = $asset_id;

        $asset = $this->repository->findById($id);
        return ApiResponse::responseSuccess([
            'data'=>$asset
        ]);

    }

    public function update(Request $request,$asset_id)
    {

        try {
            DB::beginTransaction();

            $id = $asset_id;
            $periodId = isset($request->bdc_period_id) && $request->bdc_period_id != null ? $request->bdc_period_id : 0;

            $attributes = [
                'title'=>$request->get('name'),
                'name'=>$request->get('name'),
                'asset_category_id'=>$request->get('asset_category_id'),
                'quantity'=>$request->get('quantity'),
                'bdc_period_id'=>$request->get('bdc_period_id',0),
                'maintainance_date'=>$request->get('maintainance_date'),
                'area_id'=>$request->get('area_id'),
                'department_id'=>$request->get('department_id'),
                'follower'=>$request->get('follower'),
                'warranty_period'=>$request->get('warranty_period'),
                'asset_note'=>$request->get('asset_note'),
            ];

            $asset = $this->repository->update($id, $attributes);

            if ($asset) {
                if($periodId) {
                    $period = $this->periodRepository->find($periodId);
                    if ($period) {
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
                        $this->maintenanceAssetRepository->findColumns([
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
                            $this->maintenanceAssetRepository->create($attributePeriod);
                        }
                    }
                }

                if(isset($request->images) && $request->images != null) {
                    $asset->images = $request->images;
                    $asset->save();
                }
                DB::commit();
                return ApiResponse::responseSuccess([]);
            }

            else {
                DB::rollBack();
                return ApiResponse::responseError([]);
            }

        }
        catch (Exception $e) {
            DB::rollBack();
            Log::channel('asset')->error($e->getMessage());
            return ApiResponse::responseError([]);
        }

    }

    public function destroy($asset_id)
    {

        $this->repository->forceDelete($asset_id);

        $this->maintenanceAssetRepository
            ->findColumns([
                'building_id' => $this->building_active_id,
                'asset_id' => $asset_id
            ])->forceDelete();

        return ApiResponse::responseSuccess([]);
    }

    public function delete(Request $request)
    {
        $ids = $request->get('ids');
        $ids = \GuzzleHttp\json_decode($ids);

        $this->repository->forceDelete($ids);

        $this->maintenanceAssetRepository->deleteByAssetId($ids);

        return ApiResponse::responseSuccess([]);
    }

    public function updateMaintain(Request $request)
    {
        $id = $request->get('id');

        $attributes = [
            'description' => $request->description,
            'price' => $request->price,
            'status' => 1,
            'provider'=>$request->provider
        ];

        if(isset($request->attach_file) && $request->attach_file != null) {
            $attributes["attach_file"] = $request->attach_file;
        } else {
            $attributes["attach_file"] = "[]";
        }

        $maintenanceAsset = $this->maintenanceAssetRepository->update($id, $attributes);

        if ($maintenanceAsset) {
            return ApiResponse::responseSuccess([]);
        }
        else {
            return ApiResponse::responseError([]);
        }

    }

    public function addMaintainDate(Request $request)
    {
        $title = $request->get('title');
        $asset_id = $request->get('asset_id');
        $maintainance_date = $request->get('maintainance_date');

        $user = auth()->user();

        $attributes = [
            'building_id' => $this->building_active_id,
            'title' => $request->title,
            'asset_id' => $request->asset_id,
            'maintenance_time' => $request->maintainance_date,
            'user_id' => $user->id,
            'status' => MaintenanceAsset::STATUS_PEDDING,
            'provider'=>$request->provider
        ];

        $maintenanceAsset = $this->maintenanceAssetRepository->create($attributes);

        if ($maintenanceAsset) {
            return ApiResponse::responseSuccess([]);
        }
        else {
            return ApiResponse::responseError([]);
        }

    }

    public function importexcel(Request $request)
    {
        $data['meta_title'] = 'import Tài sản';
        $array_search='';
        $i=0;
        $request->request->add(['building_id' => $this->building_active_id]);
        foreach ($request->all() as $key => $value) {
            if($i == 0){
                $param='?'.$key.'='.(string)$value;
            }else{
                $param='&'.$key.'='.(string)$value;
            }
            $i++;
            $array_search.=$param;
        }
        $data['array_search'] = $array_search;
        $file   =  '/downloads/import_tai_san.xlsx';
        $data['file'] = $file;
        return view('v3.assets.importexcel', $data);
    }

    public function importexcel_asset_detail(Request $request)
    {
        $data['meta_title'] = 'import Chi tiết tài sản';
        $array_search='';
        $i=0;
        $request->request->add(['building_id' => $this->building_active_id]);
        foreach ($request->all() as $key => $value) {
            if($i == 0){
                $param='?'.$key.'='.(string)$value;
            }else{
                $param='&'.$key.'='.(string)$value;
            }
            $i++;
            $array_search.=$param;
        }
        $data['array_search'] = $array_search;
        $file   =  '/downloads/import_chi_tiet_tai_san.xlsx';
        $data['file'] = $file;
        return view('v3.assets.importexcel_chitiettaisan', $data);
    }

    public function download()
    {
        $file = public_path() . '/downloads/import_tai_san_template.xlsx';
        return response()->download($file);
    }

    public function importAssets(Request $request)
    {
        $file = $request->file('file_import');
        $path = $file->getRealPath();

        $excel_data = Excel::load($path)->get();
        storage_path('upload', $file->getClientOriginalName());

        $data_list = array();

        if ($excel_data->count()) {
            foreach ($excel_data as $content) {
                $area = DB::table('areas')
                    ->where('id',(int)$content->ma_khu_vuc)
                    ->where('building_id',$this->building_active_id)
                    ->count();
                if ($area == 0) {
                    continue;
                }

                $phone = $content->so_dien_thoai;

                if (substr($phone, 0, 1) == "'") {
                    $phone = substr($phone,1,strlen($phone)-1);
                }

                if (substr($phone, 0, 1) != '0') {
                    $phone = "0".$phone;
                }

//                $user = Api::GET('/api/v2/admin/filter-phone',[
//                    'phone'=>$phone
//                ],true);

                $user = $this->userRepository->findColumns([
                    'mobile'=>$phone
                ])->first();

//                $user = $user->data ?? null;

                if (empty($user)) {
                    continue;
                }
                $uuid = $user->id;

//                $user = DB::table('v3_users')
//                    ->where('uuid', $content->ma_nguoi_giam_sat)
//                    ->count();
//                if ($user == 0) {
//                    continue;
//                }
                $department = DB::table('bdc_department')
                    ->where('id', (int)$content->ma_bo_phan)
                    ->where('bdc_building_id',$this->building_active_id)
                    ->count();
                if ($department == 0) {
                    continue;
                }
                $period = DB::table('bdc_period')
                    ->where('id', (int)$content->ma_ky_bao_tri)
                    ->count();
                if ($period == 0) {
                    continue;
                }
                $category = DB::table('bdc_asset_categories')
                    ->where('id', (int)$content->ma_danh_muc)
                    ->where('building_id',$this->building_active_id)
                    ->count();
                if ($category == 0) {
                    continue;
                }

                $data = [
                    'bdc_building_id'=>$this->building_active_id,
                    'name'=>$content->ten_tai_san,
                    'asset_category_id'=>(int)$content->ma_danh_muc,
                    'quantity'=>(int)$content->so_luong,
                    'bdc_period_id'=>(int)$content->ma_ky_bao_tri,
                    'maintainance_date'=>$content->bao_tri_tu->format('y-m-d'),
                    'area_id'=>(int)$content->ma_khu_vuc,
                    'department_id'=>(int)$content->ma_bo_phan,
                    'follower'=>$uuid,
                    'warranty_period'=>$content->han_bao_hanh->format('y-m-d'),
                    'asset_note'=>$content->thong_so_ky_thuat,
                ];
                array_push($data_list,$data);
            }
        }

        $response = null;

        foreach ($data_list as $data) {
//            $response = Api::POST('/api/v2/asset/add',$data,true);
            try {
                DB::beginTransaction();

                $asset = $this->repository->create($data);

                if ($asset) {
                    $periodId = $asset->bdc_period_id;

                    if ($periodId) {
                        $period = $this->periodRepository->find($periodId);
                        if ($period) {
                            $carbonFC = $period->carbon_fc;
                            $count = 0;
                            switch ($carbonFC) {
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
                            for ($i = 0; $i < $count; $i++) {
                                $date = $i == 0 ? $date : $date->addMonths($carbonFC);
                                if ($date->year > $currentDate->year) {
                                    continue;
                                }
                                $title = "Bảo trì " . strtolower($asset->name) . " tháng " . $date->month;
                                $attributePeriod = [
                                    'building_id' => $this->building_active_id,
                                    'title' => $title,
                                    'asset_id' => $asset->id,
                                    'maintenance_time' => $date,
                                    'user_id' => '',
                                    'description' => '',
                                    'price' => 0,
                                    'status' => MaintenanceAsset::STATUS_PEDDING,
                                ];
                                $this->maintenanceAssetRepository->create($attributePeriod);
                            }
                        }
                    }
                    DB::commit();
                } else {
                    DB::rollBack();
                }
            } catch (Exception $e) {
                DB::rollBack();
            }
        }

        return redirect()->route('admin.v3.assets.importexcel')->with('success', 'Import file thành công.');

//        if (!empty($response) && $response->success) {
//            return redirect()->route('admin.v3.assets.importexcel')->with('success', 'Import file thành công.');
//        }
//        else {
//            return redirect()->route('admin.v3.assets.importexcel')->withErrors(['Import Không thành công']);
//        }

    }

    public function detail(Request $request, $asset_id)
    {

        $asset = $this->repository->findById($asset_id);

        $maintenancesThis = $this->maintenanceAssetRepository->filterByBuildingId($this->building_active_id);

        $categories = $this->assetCategoryRepository->filterByBuildingId($this->building_active_id);


        $areas = $this->areaRepository->filterByBuildingId($this->building_active_id);

        $asset_cate = [];
        $asset_area = [];

        foreach ($categories as $category) {
            $asset_cate[$category->id] = $category->title;
        }

        foreach ($areas as $area) {
            $asset_area[$area->id] = $area->title;
        }

        $department = DB::table('bdc_department')->where('id', $asset->department_id)->first();

        $maintenance_times = $this->maintenanceAssetRepository->filter($maintenancesThis,[
            'asset_id'=>$asset_id,
            'limit'=>100000,
            'start_date'=>date('Y-01-01'),
            'end_date'=>date('Y-12-31')
        ]);

        $data = [
            'meta_title'=>"Thông tin lịch bảo trì của tài sản",
            'asset'=>$asset,
//            'maintenances'=>$maintenances,
            'asset_cate'=>$asset_cate,
            'asset_area'=>$asset_area,
            'department'=>$department,
            'maintenance_times'=>$maintenance_times
        ];

        return view('v3.assets.asset-detail',$data);

    }

}
