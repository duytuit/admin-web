<?php

namespace App\Http\Controllers\Home\Api;

use App\Http\Controllers\BuildingController;
use App\Models\Department\Department;
use App\Repositories\Apartments\ApartmentsRespository;
use App\Repositories\Building\BuildingRepository;
use App\Repositories\BuildingHandbook\BuildingHandbookRepository;
use App\Repositories\BuildingInfo\BuildingInfoRepository;
use App\Repositories\Customers\CustomersRespository;
use App\Repositories\Department\DepartmentRepository;
use App\Repositories\Feedback\FeedbackRespository;
use App\Repositories\MaintenanceAsset\MaintenanceAssetRepository;
use App\Repositories\PaymentInfo\PaymentInfoRepository;
use App\Repositories\Posts\PostsRespository;
use App\Repositories\Vehicles\VehiclesRespository;
use App\Repositories\WorkDiary\WorkDiaryRepository;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Carbon;
use App\Models\DepartmentStaff\DepartmentStaff;
use App\Repositories\PublicUsers\PublicUsersProfileRespository;
use App\Commons\Helper;
use App\Helpers\dBug;
use App\Models\Building\Building;
use App\Models\BuildingInfo\BuildingInfo;
use App\Models\PaymentInfo\PaymentInfo;
use App\Models\PublicUser\UserInfo;

class HomeController extends Controller
{
    use ApiResponse;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    const DUPLICATE     = 19999;
    const LOGIN_FAIL    = 10000;
    private $model;
    private $modelCustomers;
    private $modelVehicles;
    private $modelPost;
    private $workDiary;
    private $maintenanceAssetRepository;
    private $buildingHandbookRepository;
    private $modelFeedback;
    private $modelDepartment;
    private $modelBuilding;
    private $buildingInfo;
    private $payment;
    private $modelUserinfo;

    public function __construct(
        ApartmentsRespository $model,
        CustomersRespository $modelCustomers,
        VehiclesRespository $modelVehicles,
        WorkDiaryRepository $workDiary,
        MaintenanceAssetRepository $maintenanceAssetRepository,
        BuildingHandbookRepository $buildingHandbookRepository,
        PostsRespository $modelPost,
        FeedbackRespository $modelFeedback,
        DepartmentRepository $modelDepartment,
        PaymentInfoRepository $payment,
        BuildingInfoRepository $buildingInfo,
        BuildingRepository $modelBuilding,
        PublicUsersProfileRespository $modelUserinfo
    )
    {
        // $this->middleware('auth', ['except'=>[]]);
        $this->model = $model;
        $this->modelCustomers = $modelCustomers;
        $this->modelVehicles = $modelVehicles;
        $this->workDiary = $workDiary;
        $this->maintenanceAssetRepository = $maintenanceAssetRepository;
        $this->buildingHandbookRepository = $buildingHandbookRepository;
        $this->modelPost = $modelPost;
        $this->modelFeedback = $modelFeedback;
        $this->modelDepartment = $modelDepartment;
        $this->modelBuilding = $modelBuilding;
        $this->payment = $payment;
        $this->buildingInfo = $buildingInfo;
        $this->modelUserinfo = $modelUserinfo;
        $this->middleware('jwt.auth');
        Carbon::setLocale('vi');
    }

    public function index(Request $request)
    {
        $data['count_apartment']              = number_format($this->model->countItem($request->building_id),0,",",".");
        $data['count_resident']              = number_format($this->modelCustomers->listProfileCustomerCount($request->building_id),0,",",".");
        $data['count_vehicle']              = number_format($this->modelVehicles->countItem($request->building_id),0,",",".");
        $data['count_post_article']              = number_format($this->modelPost->countItem($request->building_id,'article'),0,",",".");
        $data['count_post_event']              = number_format($this->modelPost->countItem($request->building_id,'event'),0,",",".");
        $data['count_feedback_fback']              = number_format($this->modelFeedback->countItem($request->building_id,'fback'),0,",",".");
        $data['count_feedback_request']              = number_format($this->modelFeedback->countItem($request->building_id,'request'),0,",",".");
        $data['count_diary']              = number_format($this->workDiary->countItem($request->building_id),0,",",".");
        $data['count_department']              = number_format($this->modelDepartment->countItem($request->building_id),0,",",".");
        if($data){
            return $this->responseSuccess($data);
        }
        return $this->responseError(['Không có dữ liệu.'], self::LOGIN_FAIL );
    }

    public function listBuilding(Request $request)
    {
       
        $info = \Auth::guard('public_user')->user()->infoWeb->toArray();
        //dBug::trackingPhpErrorV2(\Auth::guard('public_user')->user());
        $building_ids =[];
        $building=[];
        foreach ($info as $i){
            $building_ids[]=$i['bdc_building_id'];
        }
        $building = $this->modelBuilding->getInfo($building_ids)->toArray(); 
        if($building){
            return $this->responseSuccess($building);
        }
        return $this->responseError(['Không có dữ liệu.'], self::LOGIN_FAIL );
    }

    public function getUserBuilding(Request $request)
    {
        $info = \Auth::guard('public_user')->user();
        $info_user=[];
        if (Helper::checkAdmin($info->id) || $info->isadmin == 1 ) {
            $info_user = [
                'user_id' => $info->id,
                'display_name' => null,
                'email' => $info->email,
                'role' => 'super_admin',    //'supper_admin',
                'building_id' => (int)$request->building_id,
                'departments' => Department::where('bdc_building_id', $request->building_id)->where('status',1)->select('id','name')->get()->toArray(),
            ];
        }else{
            $profile_user_first = UserInfo::where(['pub_user_id'=>$info->id,'bdc_building_id'=>$request->building_id,'type'=>2])->first();
            $get_employee =  DepartmentStaff::where('pub_user_id',$info->id)->join('bdc_department', 'bdc_department.id', '=', 'bdc_department_staff.bdc_department_id')->where('bdc_department.bdc_building_id',$request->building_id)->select('bdc_department.id', 'bdc_department.name','bdc_department_staff.type')->get()->toArray();
            if($get_employee && $profile_user_first){
                $parent_role = 0; //0:quyền nhân viên, 1:trưởng bộ phận , 2:ban quản lý
                foreach ($get_employee as $key => $value) {
                    // $get_employee[$key]['role_child'] = DepartmentStaff::REGENCY[isset($value) ? $value['type'] :DepartmentStaff::NOT_REGENCY];
                    if(isset($value) && (int)$value['type'] > $parent_role){
                        $parent_role = (int)$value['type'];
                    }
                }
                $info_user = [
                    'user_id' => $profile_user_first->pub_user_id,
                    'display_name' => $profile_user_first->display_name,
                    'email' => $profile_user_first->email,
                    'role' => DepartmentStaff::REGENCY[$info->id == @$profile_user_first->building->manager_id  ? 2 : $parent_role],
                    'building_id' => (int)$request->building_id,
                    'departments' => $get_employee,
                ];
            }
        }
        if($info_user){
            return $this->responseSuccess($info_user);
        }
        return $this->responseError(['Không có dữ liệu.'], self::LOGIN_FAIL );
    }
    public function getListUserByDepartment(Request $request)
    {
        $info = \Auth::guard('public_user')->user()->info()->where('bdc_building_id',$request->building_id)->first();
        $profile_user_first = UserInfo::where(['pub_user_id'=>$info->id,'bdc_building_id'=>$request->building_id,'type'=>2])->first();
        $info_user=[];
        if (Helper::checkAdmin($info->id) || $info->isadmin == 1 || $info->id == @$profile_user_first->building->manager_id) {

            $info_user = UserInfo::whereHas('bdcDepartmentStaff.department', function($query) use ($request) {
                if (isset($request->building_id)) {
                    $query->where('bdc_building_id', '=', $request->building_id);
                }
            })->where('bdc_building_id',$request->building_id)->get()->toArray();

        }else{
            $info_user = UserInfo::whereHas('bdcDepartmentStaff', function($query) use ($request) {
                if (isset($request->department_id)) {
                    $query->where('bdc_department_id', '=', $request->department_id);
                }
            })->where('bdc_building_id',$request->building_id)->get()->toArray();
        }

        if($info_user){
            return $this->responseSuccess($info_user);
        }
        return $this->responseError(['Không có dữ liệu.'], self::LOGIN_FAIL );
    }

    public function showUser(Request $request,$id)
    {
        $profile_user_first = UserInfo::where(['pub_user_id'=>$id,'type'=>2])->first()->toArray();

        if($profile_user_first){
            return $this->responseSuccess($profile_user_first);
        }
        return $this->responseError(['Không có dữ liệu.'], self::LOGIN_FAIL );
    }
    public function buildingInfo(Request $request)
    {
        $building = Building::get_detail_building_by_building_id($request->building_id);
        $buildingInfo =  BuildingInfo::where('bdc_building_id',$request->building_id)->first();
        $paymentInfo  =  PaymentInfo::where('bdc_building_id',$request->building_id)->get();
        if($building){
            if($buildingInfo){
                $building->building_info =(object)$buildingInfo->toArray();
                if($paymentInfo){
                 
                    $data_payment=null;
                    foreach ($paymentInfo as $key => $value) {
                        $data_payment[]=$value;
                    }
                    $building->payment_info = $data_payment;
                }
               
            }
            return $this->responseSuccess((array)$building);
        }
        return $this->responseError(['Không có dữ liệu.'], self::LOGIN_FAIL );
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
