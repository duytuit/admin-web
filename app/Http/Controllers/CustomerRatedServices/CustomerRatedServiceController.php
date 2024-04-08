<?php

namespace App\Http\Controllers\CustomerRatedServices;

use App\Helpers\dBug;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\CustomerRatedServices\CreateRequest;
use App\Models\Apartments\Apartments;
use App\Models\Apartments\V2\UserApartments;
use App\Models\Building\Building;
use App\Models\Building\Urban;
use App\Models\Building\V2\Company;
use App\Models\CustomerRatedServices\CustomerRatedServices;
use App\Models\Department\Department;
use App\Models\DepartmentStaff\DepartmentStaff;
use App\Models\PublicUser\UserInfo;
use App\Models\PublicUser\V2\TokenUser;
use App\Models\PublicUser\V2\UserInfo as V2UserInfo;
use App\Repositories\CustomerRatedServices\CustomerRatedServicesRepository;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;

class CustomerRatedServiceController extends Controller
{

    
    private $_customerRatedServicesRepository;

    private $token;

    private $new_ver;

    public function __construct(Request $request,
            CustomerRatedServicesRepository $customerRatedServicesRepository
        )
    {
        $this->token = $request->get('token');
        $this->new_ver = $request->get('new_ver');
        if($this->token && $this->new_ver == null){
            //$this->middleware('jwt.auth');
        }
        $this->_customerRatedServicesRepository = $customerRatedServicesRepository;
    }

    public function index(Request $request)
    {
      
        if($this->new_ver == null){
            $user = Auth::guard('public_user_v2')->user();
            $user = @$user->id;
        }
        if($this->new_ver == 1){
            $token_user = TokenUser::where('token',$this->token)->first();
            if(!$token_user){
                return response()->json("Phien dang nhap het han", 401);
            }
            $expire_date = $token_user->time_expired ;
            $current_date = time();

            if (!empty($token_user) &&  $expire_date >= $current_date) {
                $user = $token_user->user_id;
            }
            else{
                return response()->json("Phien dang nhap het han", 401);
            }
        }
        if($request->building_id){
            $data['departments'] = Department::where('bdc_building_id',$request->building_id)->where('status_app',1)->get();
            $data['building_id'] = $request->building_id;
        }
        $data['meta_title'] = 'Đánh giá dịch vụ';
        $data['per_page'] = Cookie::get('per_page', 10);
        $data['user_id'] =$user ? $user : null;
        return view('customer-rated-services.index', $data);
    }
    public function del(Request $request)
    {
        CustomerRatedServices::whereHas('UserStaff.department',function($query) use ($request){
            if($request->bdc_department_id){
                $query->where('bdc_building_id',$request->bdc_building_id);
            }
            // tìm bộ phận
            if($request->bdc_department_id){
                $query->where('id',$request->bdc_department_id);
            }
        })->delete();
        echo 'Thành công.';
    }
    public function store(CreateRequest $request)
    {
        try{
            $building = Building::find($request->building_id);
            if($request->user_id){
                $user_info = V2UserInfo::where('user_id',$request->user_id)->first();
            }
            if($request->apartment_id){
                $apartment = Apartments::where(['id'=>$request->apartment_id,'building_id'=>$request->building_id])->first();
            }
            
            if(isset($request->department_ids) && count($request->department_ids) > 0){
                  
                    $building->limit_audit = json_decode( $building->limit_audit );
                    $department_ids = $request->department_ids;
                    foreach ($department_ids as $key => $id_department) {
                       
                        if(is_object($building->limit_audit)){
                            if(@$building->limit_audit->type =='thang'){
                                $count_employee_month = CustomerRatedServices::where('customer_name',isset($user_info) ? $user_info->full_name : $request->customer)
                                ->where('department_id',$id_department)
                                ->where('created_at','like',Carbon::now()->format('Y-m').'%')->count();
                                $limit_building = (int)@$building->limit_audit->limit;
                                if($limit_building <= $count_employee_month){
                                    return $this->sendError_Api('Vượt quá số lần đánh giá trên tháng',null, 200);
                                }
                            }
                            if(@$building->limit_audit->type =='ngay'){
                                $count_employee_month = CustomerRatedServices::where('customer_name',isset($user_info) ? $user_info->full_name : $request->customer)
                                ->where('department_id',$id_department)
                                ->where('created_at','like',Carbon::now()->format('Y-m-d').'%')->count();
                                $limit_building = (int)@$building->limit_audit->limit;
                                if($limit_building <= $count_employee_month){
                                    return $this->sendError_Api('Vượt quá số lần đánh giá trên ngày',null, 200);
                                }
                            }
        
                        }
                        $user_apartment = UserApartments::where(['user_info_id'=>@$user_info->id ?? 0,'apartment_id'=>@$request->apartment_id])->first();
                        $building_curent = Building::find(@$user_apartment->building_id??0);
                        $urban = Urban::find(@$building_curent->urban_id??0);
                        $from_where  = isset($user_info) ? (@$urban->company_id) : 0;
                        // dBug::trackingPhpErrorV2(@$urban);
                        $CustomerRatedService = CustomerRatedServices::create([
                            'customer_name' =>  isset($user_info) ? @$user_info->full_name : $request->customer,
                            'phone' => isset($user_info) ? @$user_info->phone_contact : $request->phone,
                            'apartment_name' =>isset($apartment) ? $apartment->name : 'Vãng lai', // tên căn hộ
                            'department_id' =>  $id_department, // tên căn hộ
                            'point' =>  $request->danh_gia,
                            'description' => $request->y_kien_khac,
                            'employee_id' =>  @$request->employee_id,
                            'bdc_building_id' =>  @$building->id,
                            'user_id' =>  $request->user_id,
                            'from_where' => $from_where ?? 0 // 1 : đánh giá từ ứng dụng asahi 2 : ứng dụng khác
                        ]);
                    }   
                    return $this->sendSuccess_Api([], 'Thành công', 200);
            }else{

                $building->limit_audit = json_decode( $building->limit_audit );
                if(is_object($building->limit_audit)){
                    if(@$building->limit_audit->type =='thang'){
                        $count_employee_month = CustomerRatedServices::where('customer_name',isset($user_info) ? $user_info->full_name : $request->customer)
                        ->where('department_id',$request->department_id)
                        ->where('created_at','like',Carbon::now()->format('Y-m').'%')->count();
                        $limit_building = (int)@$building->limit_audit->limit;
                        if($limit_building <= $count_employee_month){
                            return $this->sendError_Api('Vượt quá số lần đánh giá trên tháng',null, 200);
                        }
                    }
                    if(@$building->limit_audit->type =='ngay'){
                        $count_employee_month = CustomerRatedServices::where('customer_name',isset($user_info) ? $user_info->full_name : $request->customer)
                        ->where('department_id',$request->department_id)
                        ->where('created_at','like',Carbon::now()->format('Y-m-d').'%')->count();
                        $limit_building = (int)@$building->limit_audit->limit;
                        if($limit_building <= $count_employee_month){
                            return $this->sendError_Api('Vượt quá số lần đánh giá trên ngày',null, 200);
                        }
                    }

                }
               
                $user_apartment = UserApartments::where(['user_info_id'=>@$user_info->id ?? 0,'apartment_id'=>@$request->apartment_id])->first();
                $building_curent = Building::find(@$user_apartment->building_id??0);
                $urban = Urban::find(@$building_curent->urban_id??0);
                $from_where  = isset($user_info) ? (@$urban->company_id) : 0;
                // dBug::trackingPhpErrorV2(@$urban);
                $CustomerRatedService = $this->_customerRatedServicesRepository->create([
                    'customer_name' =>  isset($user_info) ? @$user_info->full_name : $request->customer,
                    'phone' => isset($user_info) ? @$user_info->phone_contact : $request->phone,
                    'apartment_name' =>isset($apartment) ? $apartment->name :'Vãng lai', // tên căn hộ
                    'department_id' =>  $request->department_id, // tên căn hộ
                    'point' =>  $request->danh_gia,
                    'description' => $request->y_kien_khac,
                    'employee_id' =>  $request->employee_id,
                    'bdc_building_id' =>  @$request->building_id,
                    'user_id' =>  $request->user_id,
                    'from_where' =>$from_where ?? 0  // 1 : đánh giá từ ứng dụng asahi 2 : ứng dụng khác
                ]);
                if($CustomerRatedService){
                    return $this->sendSuccess_Api([], 'Thành công', 200);
                }
                
            }

           return $this->sendError_Api('Thất bại',null, 400);
       } catch(\Exception $e) {
           return $this->sendError_Api($e->getMessage(), 500);
       }
    }

    public function installApp(Request $request)
    {
        $listType = [
          'asahi'   => [
              "ios" => "https://apps.apple.com/us/app/asahi-care/id1521442178",
              "android" => "market://details?id=com.asahi.bdc",
              "desktop" => "https://play.google.com/store/apps/details?id=com.asahi.bdc",
          ],
          'buildingCare'   => [
              "ios" => "https://apps.apple.com/us/app/building-care/id1303331189",
              "android" => "market://details?id=com.portalbeanz.loaphuong",
              "desktop" => "https://play.google.com/store/apps/details?id=com.portalbeanz.loaphuong",
          ],
        ];

        $type = $request->get('type');

        if(!isset($listType[$type])) {
            dd("không hỗ trợ type này!");
        }
        $iPod = stripos($_SERVER['HTTP_USER_AGENT'], "iPod");
        $iPhone = stripos($_SERVER['HTTP_USER_AGENT'], "iPhone");
        $iPad = stripos($_SERVER['HTTP_USER_AGENT'], "iPad");
        $mac = stripos($_SERVER['HTTP_USER_AGENT'], "Mac");
        $Android = stripos($_SERVER['HTTP_USER_AGENT'], "Android");
        $url = $listType[$type]["desktop"];
        if ($Android) $url = $listType[$type]["android"];
        else if ($iPod || $iPhone || $iPad || $mac) $url = $listType[$type]["ios"];
        header('Location: '.$url);
        die;
    }
}
