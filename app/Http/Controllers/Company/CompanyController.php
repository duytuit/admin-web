<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\BuildingController;
use App\Http\Requests\Building\CreateBuildingRequest;
use App\Http\Requests\Building\UpdateBuildingCompanyRequest;
use App\Http\Requests\Company\CreateStaffRequest;
use App\Models\Department\Department;
use App\Models\DepartmentStaff\DepartmentStaff;
use App\Repositories\Building\BuildingRepository;
use App\Repositories\Building\CompanyRepository;
use App\Repositories\Building\CompanyStaffRepository;
use App\Repositories\Department\DepartmentRepository;
use App\Repositories\PublicUsers\PublicUsersProfileRespository;
use App\Repositories\PublicUsers\PublicUsersRespository;
use Carbon\Carbon;
use Illuminate\Support\FacadesAuth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Models\PublicUser\Users;
use App\Models\PublicUser\UserInfo;
use App\Models\Building\CompanyStaff;
use App\Commons\Helper;
use App\Http\Requests\Company\CreateCompanyRequest;
use App\Http\Requests\Company\CreateUrbanRequest;
use App\Models\Building\Building;
use App\Models\Building\Urban;
use App\Models\Building\V2\Company;
use App\Models\Configs\Configs;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\FacadesCache;
use Illuminate\Support\Facades\Cookie;

class CompanyController extends BuildingController
{
    protected $companyRepository;
    protected $companyStaffRepository;
    protected $buildingRepository;
    protected $userRepository;
    protected $user_profile;
    protected $departmentrepository;

    public function __construct(
        Request $request,
        CompanyRepository $companyRepository,
        CompanyStaffRepository $companyStaffRepository,
        BuildingRepository $buildingRepository,
        PublicUsersRespository $userRepository,
        PublicUsersProfileRespository $user_profile,
        DepartmentRepository $departmentrepository
    )
    {
        //$this->middleware('auth', ['except'=>[]]);
        //$this->middleware('route_permision');
        $this->departmentrepository = $departmentrepository;
        $this->companyRepository = $companyRepository;
        $this->companyStaffRepository = $companyStaffRepository;
        $this->buildingRepository = $buildingRepository;
        $this->userRepository = $userRepository;
        $this->user_profile = $user_profile;
        Carbon::setLocale('vi');
        parent::__construct($request);
    }

    public function index(Request $request)
    {
        $data['meta_title'] = 'Công ty';
        $data['per_page'] = Cookie::get('per_page', 10);
        $data['filter'] = $request->all();
        if(Auth::user()->isadmin != 1){
            return redirect()->away('/admin')->with(['warning' => 'Vui lòng quản trị để sử dụng tính năng này!']);
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
        $company= $this->companyRepository->getAll($request)->get();
        if(!$company){
            return redirect()->away('/admin')->with(['warning' => 'Không tìm thấy công ty']);
        }
        $data['company'] = $company;
        $request->request->add(['company_id' => $request->company_id ?? $company[0]->id]);
        $buildings = $this->buildingRepository->getBuildingByCompany($request);
        if(!$buildings){
            return redirect()->back()->with(['warning' => 'Không tìm thấy tòa nhà nào.']);
        }
        $data['buildings'] = $buildings;
        $data['urbans'] = Urban::where(function($query) use($request){
              if(isset($request->company_id) && $request->company_id !=null){
                $query->where('company_id',$request->company_id);
              }
        })->get();
        return view('company.list-building-urban-company', $data);
    }

    public function listurban(Request $request)
    {
        $data['meta_title'] = 'Công ty';
        $data['per_page'] = Cookie::get('per_page', 10);
        $data['filter'] = $request->all();
        if((Auth::user()->isadmin != 1) && ((Auth::user()->id) != 36811 )) {
            return redirect()->away('/admin')->with(['warning' => 'Vui lòng quản trị để sử dụng tính năng này!']);
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
        if((Auth::user()->id) != 36811 )
        {
            $company = $this->companyRepository->getAll($request)->get();
        }
        else
        {
            $company= $this->companyRepository->getAll1($request)->get();
        }
        
        if(!$company){
            return redirect()->away('/admin')->with(['warning' => 'Không tìm thấy công ty']);
        }
        $data['company'] = $company;
        $request->request->add(['company_id' => $request->company_id ?? $company[0]->id]);
        $buildings = $this->buildingRepository->getBuildingByCompany($request);
        if(!$buildings){
            return redirect()->back()->with(['warning' => 'Không tìm thấy tòa nhà nào.']);
        }
        $data['buildings'] = $buildings;
        $data['urbans'] = Urban::where(function($query) use($request){
              if(isset($request->company_id) && $request->company_id !=null){
                $query->where('company_id',$request->company_id);
              }
        })->get();
        return view('company.listurban', $data);
    }

    public function listdepartment(Request $request)
    {
        $data['meta_title'] = 'Danh Sách Phòng Ban';
        $data['filter'] = $request->all();
        $data['departments'] = $this->departmentrepository->myPaginate1($data['filter'], $this->building_active_id);
        $data['active_building'] = $this->building_active_id;
        return view('company.listdepartment', $data);
    }

    public function listemp(Request $request)
    {
        $data['meta_title'] = "Danh Sách Nhân Viên";
        $data['per_page'] = Cookie::get('per_page', 20);
        $data['keyword'] = $request->input('keyword', '');
        $data['group_ids'] = $request->input('group_ids', '');
        $data['status'] = $request->input('status', null);
        $query = UserInfo::where('type', \App\Models\PublicUser\Users::USER_WEB)
            ->where(function ($query) use ($data) {
                $query->where('display_name', 'like', '%' . $data['keyword'] . '%')
                    ->orWhere('email', 'like', '%' . $data['keyword'] . '%')
                    ->orWhere('address', 'like', '%' . $data['keyword'] . '%')
                    ->orWhere('cmt', 'like', '%' . $data['keyword'] . '%')
                    ->orWhere('phone', 'like', '%' . $data['keyword'] . '%')
                    ->orWhere('staff_code', $data['keyword']);
            })
            ->where('bdc_building_id', $this->building_active_id)
            ->where('app_id', $this->app_id)
            ->where('data_type','V2');

        if ($data['status'] != null) {
            $query = $query->where('status', $data['status']);
        }
        if ($data['group_ids']) {
            $department_staff = DepartmentStaff::where('bdc_department_id', $data['group_ids'])->pluck('pub_user_id')->toArray();
            if ($department_staff) {
                $query = $query->whereIn('pub_user_id', $department_staff);
            }
        }
        $data['users'] = $query->orderBy('status','desc')->paginate($data['per_page']);
        $data['groups'] = Department::whereHas('department_staffs')->where('bdc_building_id', $this->building_active_id)->get();
        return view('company.listemp', $data);
    }

    public function listcompany(Request $request)
    {
        $data['meta_title'] = 'Công ty';
        $data['per_page'] = Cookie::get('per_page', 10);
        $data['filter'] = $request->all();
        if((Auth::user()->isadmin != 1)  && ((Auth::user()->id) != 36811 )){
            return redirect()->away('/admin')->with(['warning' => 'Vui lòng quản trị để sử dụng tính năng này!']);
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
        if((Auth::user()->id) != 36811 )
        {
            $companys = $this->companyRepository->getAll($request)->get();
        }
        else
        {
            $companys = $this->companyRepository->getAll1($request)->get();
        }
        if(!$companys){
            return redirect()->away('/admin')->with(['warning' => 'Không tìm thấy công ty']);
        }
        $data['companys'] = $companys;
      
        return view('company.listcompany', $data);
    }

    public function indexCompany(Request $request)
    {
        $data['meta_title'] = 'Công ty';
        $data['per_page'] = Cookie::get('per_page', 10);
        $data['filter'] = $request->all();
        if(Auth::user()->isadmin != 1){
            return redirect()->away('/admin')->with(['warning' => 'Vui lòng quản trị để sử dụng tính năng này!']);
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
        $companys = $this->companyRepository->getAll($request)->get();
        if(!$companys){
            return redirect()->away('/admin')->with(['warning' => 'Không tìm thấy công ty']);
        }
        $data['companys'] = $companys;
      
        return view('company.list-company', $data);
    }

    public function create(Request $request)
    {
        if((Auth::user()->isadmin != 1)&& ((Auth::user()->id) != 36811 )){
            return redirect()->away('/admin')->with(['warning' => 'Vui lòng liên hệ quản trị để sử dụng tính năng này!']);
        }
        $data['meta_title'] = 'Công ty';
        $data['banks'] = Helper::banks();
        $data['template_emails'] = Helper::template_emails();
        $data['company'] = $this->companyRepository->getAll($request)->get();
        $data['get_company'] = Company::find($request->company_id);
        return view('company.create', $data);
    }

    public function saveUrban(CreateUrbanRequest $request)
    {
        $urban = $request->id ? Urban::find($request->id) : new Urban();
        $urban->fill($request->all());
        $urban->save();
        return $this->sendSuccess_Api([],'Thành công', route('admin.company.urban-building.index'));
    }

    public function saveCompany(CreateCompanyRequest $request)
    {
        $company = $request->id ? Company::find($request->id) : new Company();
        $company->fill($request->all());
        $company->save();
        return $this->sendSuccess_Api([],'Thành công', route('admin.company.list.index'));
    }
    public function delCompany(Request $request ,$id)
    {
        $company = Company::find($id);
      
        if(!$company){
            return redirect()->back()->with(['error' => 'Xóa thất bại!']);
        }
        $building = Building::where('company_id',$id)->first();
        if($building){
            return redirect()->back()->with(['warning' => 'Xóa thất bại! Công ty đang có tòa nhà hoạt động']);
        }
        $company->delete();
        return redirect()->back()->with(['success' => 'Xóa thành công']);
    }
    public function delUrban(Request $request ,$id)
    {
        $urban = Urban::find($id);
        if(!$urban){
            return redirect()->back()->with(['error' => 'Xóa thất bại!']);
        }
        $urban->delete();
        return redirect()->back()->with(['success' => 'Xóa thành công']);
    }

    public function edit($id)
    {
        if((Auth::user()->isadmin != 1) && ((Auth::user()->id) != 36811 )){
            return redirect()->away('/admin')->with(['warning' => 'Vui lòng liên hệ quản trị để sử dụng tính năng này!']);
        }
        $data['meta_title'] = 'Công ty';
        $building = $this->buildingRepository->find($id);
        $data['building'] =$building;
        if (!$data['building']) {
             return redirect()->away('/admin')->with(['warning' => 'Vui lòng liên hệ với quản trị để sử dụng tính năng này!']);
        }
        $data['get_manager'] = $this->user_profile->getInfoByPubuserIdV2($building->manager_id);
        $data['get_company'] = Company::all();
        $urban = Urban::find($building->urban_id);
        $data['company'] = @$urban->company;
        $data['urban'] = @$urban;
        $data['banks'] = Helper::banks();
        $data['template_emails'] = Helper::template_emails();
        return view('company.edit', $data);
    }

    public function update($id, UpdateBuildingCompanyRequest $request, PublicUsersProfileRespository $userInfo) {
        if((Auth::user()->isadmin != 1) && ((Auth::user()->id) != 36811 )){
            return redirect()->away('/admin')->with(['warning' => 'Vui lòng liên hệ quản trị để sử dụng tính năng này!']);
        }
        DB::beginTransaction();
        try {
            $building = $this->buildingRepository->find($id);
            if(!$building){
                return redirect()->back()->with(['warning' => 'Không tìm thấy tòa nhà nào.']);
            }
            $building->update($request->except(['_token']));
            DB::commit();
            Cache::store('redis')->forget( env('REDIS_PREFIX') . 'get_detail_buildingById_'.$id);
            Cache::store('redis')->put( env('REDIS_PREFIX') . '_DXMB_BUILDING_'.Auth::user()->id , null );
            Cache::store('redis')->put( env('REDIS_PREFIX') . '_DXMB_BUILDING_'.@$building->manager_id , null );
            Cache::store('redis')->forget( env('REDIS_PREFIX') . 'get_detail_buildingById_'.$id);
            return redirect()->route('admin.company.urban-building.index',['company_id'=>$building->company_id])->with('success', 'Cập nhật tòa nhà thành công');
        } catch (\Exception $e)
        {
            DB::rollback();
            return redirect()->route('admin.company.urban-building.index')->with('error', $e->getMessage());
        }
    }

    public function store(CreateBuildingRequest $request, PublicUsersProfileRespository $userInfo)
    {
        $check_config_default = Configs::where(['bdc_building_id' => $this->building_active_id, 'default' => 1])->first();
       
        DB::beginTransaction();
        try {
            $building = $this->buildingRepository->create($request->except(['_token']));
            if(!$check_config_default){
                $list_config_default = Helper::config_receipt;
               
                foreach ($list_config_default as $key => $value) {
                    $value['bdc_building_id'] = @$building->id;
                    $value['publish'] = 1;
                    $value['status'] = 1;
                    $value['default'] = 1;
                    $value['value'] = $value['value'].'_'.@$building->id;
                    Configs::create($value);
                }
            }
            DB::commit();
            Cache::store('redis')->put( env('REDIS_PREFIX') . '_DXMB_BUILDING_'.Auth::user()->id , null );
            return redirect()->route('admin.company.urban-building.index',['company_id'=>@$building->company_id])->with('success', 'Thêm tòa nhà thành công');
        } catch (\Exception $e)
        {
            DB::rollback();
            return redirect()->route('admin.company.urban-building.index',['company_id'=>@$building->company_id])->with('error', $e->getMessage());
        }
    }

    public function createStaff(Request $request)
    {
        $messages = [
            'required' => 'Vui lòng điền email để kiểm tra',
            'unique' => 'Đã có thông tin nhân viên này trong công ty',
        ];
        Validator::make($request->all(), [
            'check_email' => [
                'required',
                'email',
                // Rule::unique('bdc_company_staff', 'email')->where(function ($query) use($request) {
                //     return $query->where('bdc_company_id', $request->company_id);
                // })
            ],
        ], $messages)->validate();

        $staff = $this->userRepository->checkExit($request->check_email);
        if (!$staff) {
            return response()->json([
                'email' => $request->check_email,
                'is_new' => true
            ]);
        } else {
            return response()->json([
                'email' => $request->check_email,
                'is_new' => false
            ]);
        }
    }

    public function createStaffEmail()
    {
        $hasCompany = auth()->user()->company_staff;
            if (!$hasCompany) {
                  if(Auth::user()->isadmin ==1){
                      $hasCompany = $this->companyRepository->find(1);
                  }else{
                    return redirect()->away('/admin')->with(['warning' => 'Vui lòng cập nhật bộ phận và nhân sự để sử dụng tính năng này!']);
                  }
            }
        $data['meta_title'] = 'Thêm nhân viên';
        $data['company'] = $hasCompany->company ?? $hasCompany;
        return view('company.staff.create', $data);
    }

    public function storeStaff(CreateStaffRequest $request,PublicUsersProfileRespository $userInfo)
    {
        $data = $request->except(['_token', 'is_new', 'password', 'password_confirmation']);
        DB::beginTransaction();
        try {
            if ($request->is_new == 'true') {
                $user = $this->userRepository->create(['email'=> $request->email ,'mobile'=>$request->phone, 'password'=>bcrypt($request->password)]);
            } else {
                $user =  $this->userRepository->checkExit($request->email);
            }
            $data['pub_user_id'] = $user->id;
            $data['type'] = false;
            $data['active'] = true;
            $hasProfile = UserInfo::where('pub_user_id', $user->id)->where('bdc_building_id',$this->building_active_id)->where('type', Users::USER_WEB)->first();
            if (!$hasProfile) {
                $userInfo->create([
                    'pub_user_id' => $user->id,
                    'display_name' => $request->name,
                    'email' => $request->email,
                    'staff_code' => $request->code,
                    'phone' => $request->phone,
                    'bdc_building_id' => $this->building_active_id,
                    'type' => Users::USER_WEB,
                    'status' => true,
                    'app_id' => $this->app_id
                ]);
            }
            $staff_company = $this->companyStaffRepository->getStaffByPublicId($user->id, 1);
            if(!$staff_company){
                $this->companyStaffRepository->create($data);
            }
            DB::commit();
            $dataResponse = [
                'success' => true,
                'message' => 'Thêm thông tin nhân viên thành!',
                'href' => route('admin.company.urban-building.index')
            ];
            return response()->json($dataResponse);
        } catch (\Exception $e){

            DB::rollback();

            $dataResponse = [
                'success' => false,
                'message' => $e->getMessage(),
                'href' => route('admin.company.urban-building.index')
            ];
            return response()->json($dataResponse);
        }
    }

    public function changeStatus(Request $request)
    {
        $this->companyStaffRepository->update($request->except('id'), $request->id);
        $dataResponse = [
            'success' => true,
            'message' => 'Cập nhật trạng thái thành công!'
        ];
        return response()->json($dataResponse);
    }
    public function changeStatusBuilding(Request $request)
    {
        $this->buildingRepository->update($request->except('id'), $request->id);
        $dataResponse = [
            'success' => true,
            'message' => 'Cập nhật trạng thái thành công!'
        ];
        return response()->json($dataResponse);
    }
    public function ajaxGetStaffByCompany(Request $request)
    {
        if ($request->search) {
            $where[] = ['display_name', 'like', '%' . $request->search . '%'];
            return response()->json($this->user_profile->getStaffByCompany(['where' => $where],  $request->company_id));
        }
        return response()->json($this->user_profile->getStaffByCompany([],  $request->company_id));
    }
    public function ajaxGetUrbanByCompany(Request $request)
    {
        if ($request->search) {
            $where[] = ['name', 'like', '%' . $request->search . '%'];
            return response()->json($this->companyRepository->getUrbanByCompany(['where' => $where], $request->company_id));
        }
        return response()->json($this->companyRepository->getUrbanByCompany([],$request->company_id));
    }
}
