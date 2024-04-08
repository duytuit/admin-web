<?php

namespace App\Http\Controllers\V3;

use App\Commons\Api;
use App\Commons\Helper;
use App\Http\Controllers\BuildingController;
use App\Models\Asset\Asset;
use App\Models\Asset\AssetDetail;
use App\Repositories\Period\PeriodRepository;
use App\Repositories\V3\AreaRepository\AreaRepository;
use App\Repositories\V3\AssetCategoryRepository\AssetCategoryRepository;
use App\Repositories\V3\AssetRepository\AssetRepository;
use App\Repositories\V3\DepartmentRepository\DepartmentRepository;
use App\Repositories\V3\MaintenanceAssetRepository\MaintenanceAssetRepository;
use App\Repositories\V3\TaskRepository\TaskRepository;
//use Modules\Tasks\Repositories\Task\TaskRespository;
use App\Repositories\V3\User\UserRepository;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Log;

class MaintenanceAssetController extends BuildingController
{

    protected $areaRepository;
    protected $assetCategoryRepository;
    protected $departmentRepository;
    protected $repository;
    protected $userRepository;
    protected $periodRepository;
    protected $maintenanceAssetRepository;
    protected $taskRepository;
    public function __construct(
        AreaRepository $areaRepository,
        AssetCategoryRepository $assetCategoryRepository,
        AssetRepository $repository,
        DepartmentRepository $departmentRepository,
        UserRepository $userRepository,
        PeriodRepository $periodRepository,
        MaintenanceAssetRepository $maintenanceAssetRepository,
        TaskRepository $taskRepository,
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
        $this->taskRepository = $taskRepository;
        parent::__construct($request, $is_api);
    }

    public function index(Request $request)
    {
        $data = [
            'meta_title'=>"QL Lịch bảo trì"
        ];
        $data['per_page'] = Cookie::get('per_page', 10);
        $data['filter'] = $request->all();
        $data['filter']['year'] = $year = $request->cycle_year ?? Carbon::now()->format('Y');
        $data['asset_detail'] = AssetDetail::where('building_id',$this->building_active_id)->where(function ($query) use($request){
             $query->where('status',1);
        })->paginate($data['per_page']);
        return view('v3.assets.maintenance',$data);
    }
    public function detail(Request $request,$asset_id)
    {

        $data['meta_title'] = 'Thông tin tài sản';
        $data['filter'] = $request->all();
        $request->request->add(['building_id' => $this->building_active_id]);
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
        $data['id'] = $asset_id;
        $data['status_task_html'] = json_encode(Helper::status_task_html);

        return view('v3.assets.maintenance-detail',$data);

    }

    public function action(Request $request)
    {
        $method = $request->input('method', '');
        if ($method == 'per_page') {
            $this->per_page($request);
            return back();
        }
    }
}
