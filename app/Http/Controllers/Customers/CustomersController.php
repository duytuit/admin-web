<?php

namespace App\Http\Controllers\Customers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\BuildingController;

use App\Http\Requests\Customers\CustomersRequest;
use App\Models\PublicUser\Users;
use App\Repositories\Apartments\ApartmentsRespository;
use App\Repositories\Building\BuildingPlaceRepository;
use App\Repositories\Customers\CustomersRespository;
use App\Repositories\Fcm\FcmRespository;
use App\Repositories\PublicUsers\PublicUsersProfileRespository;
use App\Repositories\PublicUsers\PublicUsersRespository;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Facades\Excel;
use Mail;
use App\Services\SendSMSSoap;
use App\Repositories\Building\CompanyRepository;
use App\Commons\Helper;
use App\Models\Apartments\V2\UserApartments;
use App\Models\Building\Building;
use App\Models\Campain;
use App\Models\PublicUser\UserInfo;
use App\Services\SendSMSSoapV2;
use App\Services\ServiceSendMailV2;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class CustomersController extends BuildingController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    private $model;
    private $modelApartment;
    private $modelUsers;
    private $modelUserProfile;
    private $modelFcm;
    private $modelBuildingPlace;
    private $company;

    public function __construct(CustomersRespository $model, ApartmentsRespository $modelApartment, PublicUsersRespository $modelUsers, PublicUsersProfileRespository $modelUserProfile, FcmRespository $modelFcm, BuildingPlaceRepository $modelBuildingPlace, Request $request, CompanyRepository $company)
    {
        //$this->middleware('route_permision');
        $this->model = $model;
        $this->modelUsers = $modelUsers;
        $this->modelUserProfile = $modelUserProfile;
        $this->modelFcm = $modelFcm;
        $this->modelApartment = $modelApartment;
        $this->modelBuildingPlace = $modelBuildingPlace;
        $this->company = $company;
        parent::__construct($request);
    }
    public function index(Request $request)
    {

        $data['meta_title'] = 'Customers';
       
        // dd($this->model->listProfileCustomerNew($this->building_active_id)->toArray());
        $data['per_page'] = Cookie::get('per_page',10);
        $list_cus = $this->model->listCustomerNew($this->building_active_id, $request, $data['per_page']);
        $list_cus_all_pluck_pub_profile = $this->model->listProfileCustomerV3($this->building_active_id)->pluck('pub_user_profile_id')->unique()->toArray();
        $list_user_ids = UserInfo::whereIn('id',$list_cus_all_pluck_pub_profile)->pluck('pub_user_id');
        $count_mobile_active = Users::whereIn('id',$list_user_ids)->where('mobile_active',1)->count();
        $data_search = [
            'keyword'        => '',
            'apartment'         => '',
            'email'         => '',
            'phone'         => '',
            'gender'         => '',
            'birthday'         => '',
            'place'         => '',
            'type'=>'',
            'birthday_day'         => '',
            'birthday_month'         => '',
            'birthday_from_year'         => '',
            'birthday_to_year'=>''
        ];

        $data['data_search'] = $request->data_search ?: $data_search;
        $data['data_search']['keyword'] = $request->keyword;
        $data['data_search']['apartment'] = $request->apartment;
        if ($request->apartment) {
            $data['data_search']['apartment'] = $this->modelApartment->findById($request->apartment);
        }
        if ($request->place) {
            $name = $this->modelBuildingPlace->findById($request->place);
            $data['data_search']['place'] = $request->place;
            $data['data_search']['name_place'] = $name->name . ' - ' . $name->code;
        }
        $data['data_search']['email'] = $request->email;
        $data['data_search']['phone'] = $request->phone;
        $data['data_search']['gender'] = $request->gender;
        $data['data_search']['birthday'] = $request->birthday;
        $data['data_search']['birthday_from_year'] = $request->birthday_from_year;
        $data['data_search']['birthday_to_year'] = $request->birthday_to_year;
        $data['data_search']['birthday_day'] = $request->birthday_day;
        $data['data_search']['birthday_month'] = $request->birthday_month;
        $data['building_active_id'] = $this->building_active_id;
        $data['customers'] = $list_cus;
        $data['count_mobile_active'] = $count_mobile_active;
        $data['display_count'] = count($list_cus);
        $data['data_cus'] = Session::get('data_cus');
        $data['data_error'] = Session::get('error');
        $data['data_success'] = Session::get('success');
        $data['data_search']['type'] = $request->get('type');
        return view('customers.index', $data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(CustomersRequest $request)
    {
        // dd('ddd');
        $data['meta_title'] = 'Add Customers';
        return view('customers.create', $data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'email'     => 'max:254',
            'bdc_apartment_id'     => 'required',
            'type'     => 'required'
        ]);

        $data['meta_title'] = 'Add Customers';

        if ($request->email == null && $request->phone == null) {
            return redirect()->route('admin.customers.index')->with(['error' => 'Thêm Cư dân cần điền email hoặc số điện thoại.']);
        }
        DB::beginTransaction();
        if ($request->email <> null && filter_var($request->email, FILTER_VALIDATE_EMAIL)) {
            // if($request->phone <> null) {
            //     $check_user = $this->modelUsers->checkPhone($request->phone);
            //     // check trùng số điện thoại
            //     if ($check_user) {
            //         return back()->with('error', 'Số điện thoại này đã tồn tại');
            //     }
            // }
            try {
                $user = $this->createUserWithEmail($request);
                $result = $this->addCustomerToApartment($user, $request);
            } catch (\Exception $e) {
                DB::rollBack();
                throw new \Exception("Đã có lỗi xẩy ra khi tạo mới cư dân.( 1402 ) " . $e->getMessage(), 1);
            }

            DB::commit();
            return $result;
        }
         if ($request->phone <> null && $request->email == null ) {
            try {
                $user = $this->createUserWithPhone($request);

                $result =  $this->addCustomerToApartment($user, $request);
            } catch (\Exception $e) {
                DB::rollBack();
                throw new \Exception("Đã có lỗi xẩy ra khi tạo mới cư dân.( 1402 ) ".$e->getMessage(), 1);
            }
            DB::commit();
            return $result;
        }

    }

    private function createUserWithEmail($request)
    {
        $user = $this->modelUsers->checkExit($request->email);
        // neu user ko ton tai se tao moi
        if (!$user) {

            $password = $this->getPasswort($request);

            $user = $this->modelUsers->create([
                'email' => $request->email,
                'mobile'  => $request->phone,
                'password' => Hash::make($password),
                'status' => 1 // mac dinh active user
            ]);

            // gui mai thong bao tai khoan duoc tao
            $this->modelUsers->sendMail($request->email, $password, $request->name, $this->building_active_id);
        }

        return $user;
    }
    private function createUserDontEmaiPhone($request)
    {
        $user = $this->modelUsers->create([
            'display_name' => $request->email,
            'status' => 1 // mac dinh active user
        ]);
        return $user;
    }

    private function createUserWithPhone($request)
    {
        $user = $this->modelUsers->checkPhone($request->phone);
        // neu user ko ton tai se tao moi
        if (!$user) {

            $password = $this->getPasswort($request);

            $user = $this->modelUsers->create([
                'email' => $request->email,
                'mobile' => $request->phone,
                'password' => Hash::make($password),
                'status' => 1 // mac dinh active user
            ]);
            $total = ['email'=> 0, 'app'=> 0, 'sms'=> 1];
            $campain = Campain::updateOrCreateCampain("Gửi sms cho: ".$request->phone, config('typeCampain.RESIDENT'), null, $total, $this->building_active_id, 0, 0);
            $content = [
                'otp'=> $password,
                'account'=> $request->phone,
             ];
            // gui sms thong bao tai khoan duoc tao
            SendSMSSoapV2::setItemForQueue([
                'content' => $content,
                'target' => $request->phone,
                'building_id'=>$this->building_active_id,
                'type'=>SendSMSSoapV2::NEW_USER,
                'campain_id'=>$campain->id,
            ]);
        }

        return $user;
    }


    private function getCustomerCode($pub_user_id)
    {
        // $company = $this->company->getPrefixCompanyCode($this->building_active_id);
        // if (!$company) {
        //     throw new Exception("Không tìm thấy Công ty thích hợp.(0309)", 0);

        // }
        // $lastId = $this->modelUserProfile->getLastIdWithPrefix($pub_user_id, $company->customer_code_prefix, $company->id);

        // return [
        //     'customer_code_prefix'=> $company->customer_code_prefix,
        //     'customer_code' =>$lastId
        // ];
        return Helper::getCustomerCode($pub_user_id, $this->building_active_id);
    }

    private  function findOrCreateProfile($user, $request)
    {
        if ($user) {
            $profile = $this->modelUserProfile->findByPubUserIdResident($user->id, $this->building_active_id);
        } else {
            $profile = '';
        }
        if (!$profile) {
            if ($user) {
                $rs = $this->getCustomerCode($user->id);
            }

            $profile = $this->modelUserProfile->create([
                'display_name' => $request->name,
                'email' => $request->email ?? null,
                'phone' => $request->phone ?? null,
                'pub_user_id' => $user ? $user->id : 0,
                'gender' => $request->sex,
                'birthday' => date('Y-m-d', strtotime($request->create_birthday)),
                'type' => Users::USER_APP,
                'bdc_building_id' => $this->building_active_id,
                'app_id' => $this->app_id,
                'customer_code_prefix' => $user ? $rs['customer_code_prefix'] : null,
                'customer_code' => $user ? $rs['customer_code'] : null,

            ]);
        }
        return $profile;
    }

    private function getPasswort($request)
    {
        $password = $request->pub_pass;
        //kiem tra có pass gui len hay ko
        // khong co thi tao password random
        if (!$password) {
            $password = $this->getToken(6);
        }
        return $password;
    }

    private function addCustomerToApartment($user, $request)
    {
        $profile = $this->findOrCreateProfile($user, $request);

        //kiem tra xem cu dan da co trong can ho chua
        $apartment = $this->model->checkCusExit($profile->id, $request->bdc_apartment_id, $this->building_active_id);
        if ($apartment) {
            return redirect()->route('admin.apartments.edit', ['id' => $request->bdc_apartment_id])->with(['error' => 'Cư dân đã tồn tại trong căn hộ với tên là: "' . $profile->display_name . '"', 'data_cus' => 'Cư dân đã tồn tại trong căn hộ với tên là: "' . $profile->display_name . '"']);
        }
        // Đổi thành viên mới thành chủ hộ
        if ($request->type == 0) {
            // Kiểm tra căn hộ đã có chủ hộ chưa
            $check = $this->model->checkUsersType($request->type, $request->bdc_apartment_id, $this->building_active_id);
            if (isset($check) && $check->type == 0) {
                $this->model->find($check->id)->update(['bdc_apartment_id' => $check->bdc_apartment_id, 'type' => 5, 'pub_user_profile_id' => $check->pub_user_profile_id]);
            }
        }

        // add moi cu dan vao can ho
        $customer =  $this->model->create([
            'pub_user_profile_id' => $profile->id,
            'bdc_apartment_id' => $request->bdc_apartment_id,
            'type' => $request->type
        ]);
        // gui mai thong bao duoc them vao can ho
        $this->model->sendNotifyNewCustomer($request->email, $request->name, $this->building_active_id, @$customer->bdcApartment->name);

        return redirect()->back()->with(['success' => 'Thêm Cư dân thành công!', 'data_cus' => 'Thêm Cư dân thành công!']);
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
    public function edit($id, Request $request)
    {
        $customer = $this->modelUserProfile->getOne('id', $id);
        // dd( $customer);
        $data['bdcCustomers'] = @$customer->bdcCustomers;

        $data['customer'] = $customer;
        $data['urlApartment'] = $request->headers->get('referer');
        $data['meta_title'] = 'edit Customers';
        return view('customers.edit', $data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(CustomersRequest $request, $id)
    {
        
        $birthday = Carbon::parse($request->birthday);
        $dataUser = [
            'display_name' => $request->name,
            'phone' => $request->phone,
            'email' => $request->email,
            'address' => $request->address,
            'cmt' => $request->cmt,
            'cmt_nc' => date('y-m-d', strtotime($request->cmt_nc)),
            'birthday' =>  $birthday,
            // 'birthday'=> $birthday->format('y-m-d'),
            'gender' => $request->gender,
            'avatar' => $request->avatar,
        ];
        if ($request->cus_id) {
            foreach ($request->cus_id as $key => $cus) {
                
                // Đổi thành viên mới thành chủ hộ
                if ($request->type[$key] == '0') {
                    // Kiểm tra căn hộ đã có chủ hộ chưa
                    $check = $this->model->checkUsersType($request->type[$key], $request->bdc_apartment_id[$key], $this->building_active_id);
                    if (isset($check) && $check->type == 0) {
                        $this->model->find($check->id)->update(['bdc_apartment_id' => $check->bdc_apartment_id, 'type' => 5, 'pub_user_profile_id' => $check->pub_user_profile_id]);
                    }
                }
                $this->model->find($cus)->update(['bdc_apartment_id' => $request->bdc_apartment_id[$key], 'type' => $request->type[$key]]);
                Cache::store('redis')->forget( env('REDIS_PREFIX') . 'get_detail_customerById_'.$request->bdc_apartment_id[$key]);
            }
           
        }
        $result =  $this->modelUserProfile->find($id)->update($dataUser);
        Cache::store('redis')->forget(env('REDIS_PREFIX') . 'get_detail_user_infoById_'.$id);
 
        if( $result == 1){
             $getPubUser =  $this->modelUserProfile->find($id);
             $DataPubUser = [
                'email' => $request->email,
                'mobile'  => $request->phone,
             ];
            $user = $this->modelUsers->find($getPubUser->pub_user_id);
            if($user){
                $user->update($DataPubUser);
            }
            
        }

        // quay lại 1 trang  
        $actual_link = $request->url_Apartment;
        $pieces = explode("/", $actual_link);
        if (count($pieces)>4 && $pieces[4] == 'customers') {
            return redirect()->route('admin.customers.index')->with(['success' => 'Cập nhật Cư dân thành công!', 'data_cus' => 'Cập nhật Cư dân thành công!']);
        } else {
            return redirect()->route('admin.apartments.edit', ['id' => $request->bdc_apartment_id[0]])->with(['success' => 'Cập nhật Cư dân thành công!', 'data_cus' => 'Cập nhật Cư dân thành công!']);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $this->model->find($id)->delete();
        return redirect()->route('admin.customers.index')->with(['success' => 'Xóa cư dân thành công!', 'data_cus' => 'Xóa cư dân thành công!']);
    }
    public function destroyCus($id)
    {
        $this->model->find($id)->delete();
        //$this->modelUserProfile->delete(['id' => $id]);
        return redirect()->route('admin.customers.index')->with(['success' => 'Xóa cư dân thành công!', 'data_cus' => 'Xóa cư dân thành công!']);
    }
    function getToken($length)
    {
        $token = "";
        $codeAlphabet= "0123456789";
        $max = strlen($codeAlphabet); // edited

        for ($i = 0; $i < $length; $i++) {
            $token .= $codeAlphabet[random_int(0, $max - 1)];
        }

        return $token;
    }
    public function saveUserApartment(CustomersRequest $request)
    {
        $type = [0,6,7]; // chủ hộ, khách thuê,chủ hộ cũ bắt buộc email và số điện thoại
        DB::beginTransaction();
        if (in_array($request->type, $type)) {
            if($request->email == null && $request->phone == null){
                return redirect()->route('admin.apartments.edit', ['id' => $request->bdc_apartment_id])->with(['error' => 'Thêm Cư dân cần điền email hoặc số điện thoại.']);
            }
            if ($request->email <> null && filter_var($request->email, FILTER_VALIDATE_EMAIL)) {
                try {
                    $user = $this->createUserWithEmail($request);
                    $result = $this->addCustomerToApartment($user, $request);
                } catch (\Exception $e) {
                    DB::rollBack();
                    throw new \Exception("Đã có lỗi xẩy ra khi tạo mới cư dân.( 1402 ) " . $e->getMessage(), 1);
                }
    
                DB::commit();
                return $result;
            }
             if ($request->phone <> null && $request->email == null ) {
                try {
                    $user = $this->createUserWithPhone($request);
    
                    $result =  $this->addCustomerToApartment($user, $request);
                } catch (\Exception $e) {
                    DB::rollBack();
                    throw new \Exception("Đã có lỗi xẩy ra khi tạo mới cư dân.( 1402 ) ".$e->getMessage(), 1);
                }
                DB::commit();
                return $result;
            }

        }else{
            try {
                if ($request->email <> null && filter_var($request->email, FILTER_VALIDATE_EMAIL)) {
                    try {
                        $user = $this->createUserWithEmail($request);
                        $result = $this->addCustomerToApartment($user, $request);
                    } catch (\Exception $e) {
                        DB::rollBack();
                        throw new \Exception("Đã có lỗi xẩy ra khi tạo mới cư dân.( 1402 ) " . $e->getMessage(), 1);
                    }
        
                    DB::commit();
                    return $result;
                }
                else if($request->phone <> null && $request->email == null ) {
                    try {
                        $user = $this->createUserWithPhone($request);
        
                        $result =  $this->addCustomerToApartment($user, $request);
                    } catch (\Exception $e) {
                        DB::rollBack();
                        throw new \Exception("Đã có lỗi xẩy ra khi tạo mới cư dân.( 1402 ) ".$e->getMessage(), 1);
                    }
                    DB::commit();
                    return $result;
                }else{
                    $result = $this->addCustomerToApartment(null, $request);
                    DB::commit();
                    return $result;
                }
               
            } catch (\Exception $e) {
                DB::rollBack();
                throw new \Exception("Đã có lỗi xẩy ra khi tạo mới cư dân.( 1402 ) " . $e->getMessage(), 1);
            }

           
        }

    }

    public function destroyCustomerApartment($id)
    {
        $del =  $this->model->find($id)->delete();

        if ($del) {
            return back()->with('success', 'Xóa cư dân thành công!');
        } else {
            return back()->with('error', 'Xóa cư dân không thành công!');
        }
    }
    public function indexImportNew(Request $request)
    {
        set_time_limit(0);
        $file = $request->file('file_import');

        if(!$file) return redirect()->route('admin.customers.index_import')->with('warning', 'Chưa có file upload!');

        $path = $file->getRealPath();

        $excel_data = Excel::load($path)->get();

        storage_path('upload', $file->getClientOriginalName());

        $buildingId = $this->building_active_id;

        $data_list_error = array();


        if ($excel_data->count()) {

            foreach ($excel_data as $content) {
                if (empty($content->id)
                ) {
                    $new_content = $content->toArray();
                    $new_content['message'] = 'hãy kiểm tra lại các trường yêu cầu bắt buộc';
                    array_push($data_list_error,$new_content);
                    continue;
                }
                if(!empty($content->email) && !empty($content->so_dien_thoai)) { $new_content = $content->toArray();
                    $new_content['message'] ='Không nhập cả mail lẫn sdt à ? ';
                    array_push($data_list_error,$new_content);
                    continue;}

                // check is number
                
                if(preg_match('/\d/', $content->id) !== 1) { // không phải là kiểu số hoặc nhỏ hơn 0
                    $new_content = $content->toArray();
                    $new_content['message'] =$content->id.'| không phải là kiểu số nguyên';
                    array_push($data_list_error,$new_content);
                    continue;
                }

                // check is number
                
                if(preg_match('/\d/', $content->quan_he_voi_chu_ho) !== 1) { // không phải là kiểu số hoặc nhỏ hơn 0
                    $new_content = $content->toArray();
                    $new_content['message'] =$content->quan_he_voi_chu_ho.'| trường quan hệ với chủ hộ không phải là kiểu số nguyên';
                    array_push($data_list_error,$new_content);
                    continue;
                }
                

                $user_info = $this->modelUserProfile->getInfoById($buildingId,$content->id); // is null : là tài khoản này không có trên hệ thống

                if (!$user_info) {
                    $new_content = $content->toArray();
                    $new_content['message'] = $content->id.'| tài khoản này không có trên hệ thống';
                    array_push($data_list_error,$new_content);
                    continue;
                }

                if (!empty($content->ngay_sinh) && !strtotime($content->ngay_sinh)) {
                    // Display valid date message
                    $new_content = $content->toArray();
                    $new_content['message'] =$content->ngay_sinh.'| ngày sinh không đúng định dạng dd/mm/yyyy';
                    array_push($data_list_error,$new_content);
                    continue;
                }

                /*if (!empty($content->ngay_cap) && !strtotime($content->ngay_cap)) {
                    // Display valid date message
                    $new_content = $content->toArray();
                    $new_content['message'] =$content->ngay_cap.'| ngày cấp không đúng định dạng dd/mm/yyyy';
                    array_push($data_list_error,$new_content);
                    continue;
                }*/

                // check is number
                
                if(!empty($content->gioi_tinh) && preg_match('/\d/', $content->gioi_tinh) !== 1) { // không phải là kiểu số hoặc nhỏ hơn 0
                    $new_content = $content->toArray();
                    $new_content['message'] =$content->gioi_tinh.'| không phải là kiểu số nguyên';
                    array_push($data_list_error,$new_content);
                    continue;
                }

                if(!empty($content->email)) {
                    if(!filter_var(trim($content->email), FILTER_VALIDATE_EMAIL)){
                        //Display valid date message
                        $new_content = $content->toArray();
                        $new_content['message'] =$content->email.'| email không đúng định dạng';
                        array_push($data_list_error,$new_content);
                        continue;
                    }
                    $checkEmail = $this->modelUsers->checkExitById($user_info->pub_user_id,$content->email); // is not null : là email này đã có trên hệ thống không thể cập nhật được
                    if($checkEmail){
                        $new_content = $content->toArray();
                        $new_content['message'] =$content->email.'| email này đã có trên hệ thống không thể cập nhật được';
                        array_push($data_list_error,$new_content);
                        continue;
                    }
                }

                if(!empty($content->so_dien_thoai)) { // false : là không phải số điện thoại
                   if(Helper::detect_number($content->so_dien_thoai) == false){
                        $new_content = $content->toArray();
                        $new_content['message'] =$content->so_dien_thoai.'| số điện thoại không đúng định dạng';
                        array_push($data_list_error,$new_content);
                        continue;
                   }
                    $checkPhone = $this->modelUsers->checkPhoneById($user_info->pub_user_id,$content->so_dien_thoai); // is not null : là phone này đã có trên hệ thống không thể cập nhật được
                    if($checkPhone){
                        $new_content = $content->toArray();
                        $new_content['message'] =$content->so_dien_thoai.'| phone này đã có trên hệ thống không thể cập nhật được';
                        array_push($data_list_error,$new_content);
                        continue;
                    }
                }

                
                try {
                    DB::beginTransaction();

                        $user_info->update([
                            'display_name' =>!empty($content->ten_khach_hang) ? $content->ten_khach_hang : $user_info->display_name,
                            'birthday' =>!empty($content->ngay_sinh) ? $content->ngay_sinh : $user_info->birthday,
                            'gender' => !empty($content->gioi_tinh) ? $content->gioi_tinh : $user_info->gender,
                            'email' => !empty($content->email) ? $content->email : $user_info->email,
                            'phone' => !empty($content->so_dien_thoai) ? $content->so_dien_thoai : $user_info->phone,
                            'cmt' => !empty($content->chung_minh_nhan_dan) ? $content->chung_minh_nhan_dan : $user_info->cmt,
                            'cmt_nc' => !empty($content->ngay_cap) ? $content->ngay_cap : $user_info->cmt_nc,
                            'address' => !empty($content->noi_cap) ? $content->noi_cap : $user_info->address,
                        ]);
                        // Đổi thành viên mới thành chủ hộ

                        $check = $this->model->checkUsersWithApartment($user_info->id,$content->ma_ho, $this->building_active_id);
                        if(!$check){
                            $new_content = $content->toArray();
                            $new_content['message'] =$user_info->display_name .' Không có trong căn hộ có mã'. $content->ma_ho;
                            array_push($data_list_error,$new_content);
                            continue;
                        }

                        if ($content->quan_he_voi_chu_ho == 0) {
                            // Kiểm tra căn hộ đã có chủ hộ chưa
                            $customer = $this->model->checkUsersType($content->quan_he_voi_chu_ho, $check->bdc_apartment_id, $this->building_active_id);
                            if (isset($customer) && $customer->type == 0) {
                                $this->model->find($customer->id)->update(['bdc_apartment_id' => $customer->bdc_apartment_id, 'type' => 5, 'pub_user_profile_id' => $customer->pub_user_profile_id]);
                            }
                        }
                           
                       $check->update(['type' => $content->quan_he_voi_chu_ho]); 

                       $user = Users::find($user_info->pub_user_id);

                       if($user) $user->update([
                          'email' => !empty($content->email) ? $content->email : $user->email,
                          'mobile' => !empty($content->so_dien_thoai) ? $content->so_dien_thoai : $user->mobile,
                       ]);
                       $new_content = $content->toArray();
                       $new_content['message'] = 'cập nhật thành công';
                       array_push($data_list_error,$new_content);
                    DB::commit();
                } catch (\Exception $e) {
                    DB::rollBack();
                    $new_content = $content->toArray();
                    $new_content['message'] = $e->getMessage();
                    array_push($data_list_error,$new_content);
                    continue;
                }
            }
        }

        if ($data_list_error) {
            $result = $result = Excel::create('Kết quả Import', function ($excel) use ($data_list_error) {

                $excel->setTitle('Kết quả Import');
                $excel->sheet('Kết quả Import', function ($sheet) use ($data_list_error) {
                    $row = 1;
                    $sheet->row($row, [
                        'ID',
                        'Tên khách hàng',
                        'Ngày sinh',
                        'Giới tính',
                        'Email',
                        'Số điện thoại',
                        'Chứng minh nhân dân',
                        'Ngày cấp',
                        'Nơi cấp',
                        'Quan hệ với chủ hộ',
                        'Message'
                    ]);

                    foreach ($data_list_error as $key => $value) {
                        $row++;
                        $sheet->row($row, [
                            isset($value['id']) ? $value['id'] : '',
                            isset($value['ten_khach_hang']) ? $value['ten_khach_hang'] : '',
                            isset($value['ngay_sinh']) ? date("d/m/Y", strtotime($value['ten_khach_hang'])) : '',
                            isset($value['gioi_tinh']) ? $value['ten_khach_hang'] : '',
                            isset($value['email']) ? $value['email'] : '',
                            isset($value['so_dien_thoai']) ? $value['so_dien_thoai'] : '',
                            isset($value['chung_minh_nhan_dan']) ? $value['chung_minh_nhan_dan'] : '',
                            isset($value['ngay_cap']) ? $value['ngay_cap'] : '',
                            isset($value['noi_cap']) ? $value['noi_cap'] : '',
                            isset($value['quan_he_voi_chu_ho']) ? $value['quan_he_voi_chu_ho'] : '',
                            $value['message'],
                        ]);
                        if (isset($value['message']) && $value['message'] == 'cập nhật thành công') {
                            $sheet->cells('K' . $row, function ($cells) {
                                $cells->setBackground('#23fa43');
                            });
                        }
                        if (isset($value['message']) && $value['message'] != 'cập nhật thành công') {
                            $sheet->cells('K' . $row, function ($cells) {
                                $cells->setBackground('#FC2F03');
                            });
                        }
                    }
                });
            })->store('xlsx', storage_path('exports/'));
            ob_end_clean();
            $file     = storage_path('exports/' . $result->filename . '.' . $result->ext);
            header('Content-disposition: attachment; filename=' . $result->filename . '.' . $result->ext);
            readfile($file);
            unlink(storage_path('exports/' . $result->filename . '.' . $result->ext));
        } 
       
    }
    public function ViewExcel(Request $request)
    {
        $file = $request->file('file_import');
        if($file){
          $path = $file->getRealPath();
          $excel_data = Excel::load($path)->get();

            storage_path('upload', $file->getClientOriginalName());

            $url = [
                'name' => $file->getClientOriginalName(),
                'uuid' => (string) \Webpatser\Uuid\Uuid::generate(),
            ];
        
        // $data['data_import']['messages'][] = ['messages' => 'Có ' . 1 . ' không có căn hộ trên hệ thống, Không được thêm dữ liệu', 'data' => 123, 'color' => 'red'];
        $messages_title='Xem trước file import';
            if ($excel_data->count()) {
                
                $data['viewexcel']['messages']='';
                foreach ($excel_data as $key => $value) {
                    $data['viewexcel']['data'][] = [
                            'index'=> $value['index'],
                            'display_name' => $value['name'],
                            'cmt' => $value['cmt'],
                            'phone' => $value['phone'],
                            'email' => $value['email'],
                            'password' => $value['password'],
                            'gender' => $value['sex'],
                            'type' => $value['type'],
                            'apartment_name' => $value['apartment_name'],
                            'floor' => $value['floor'],
                            'place' =>$value['place'], 
                    ];
                }
        
                return redirect()->route('admin.customers.index_import')->with(['success' => 'Xem danh sách import file','title'=>$messages_title, 'messages' => json_encode($data)]);;
            }
        }
        return redirect()->back();
    }
    public function indexImport()
    {
        $data['meta_title'] = 'import file resident';
        $data['messages'] = json_decode(Session::get('messages'), true);
        $data['error_data'] = Session::get('error_data');
        return view('customers.import', $data);
    }
    public function importFileApartment(Request $request)
    {
        set_time_limit(0);
        $file = $request->file('file_import');

        if(!$file) return redirect()->route('admin.customers.index_import')->with('warning', 'Chưa có file upload!');

        $path = $file->getRealPath();

        $excel_data = Excel::load($path)->get();

        storage_path('upload', $file->getClientOriginalName());

        $buildingId = $this->building_active_id;
        $name_building= Building::where('id',$buildingId)->first();
        $data_list_error = array();

        if ($excel_data->count()) {

            foreach ($excel_data as $content) {
                if (empty($content->name) ||
                    empty($content->apartment_name) ||
                    empty($content->floor) ||
                    empty($content->place) 
                ) {
                    $new_content = $content->toArray();
                    $new_content['message'] = 'hãy kiểm tra lại các trường yêu cầu bắt buộc';
                    array_push($data_list_error,$new_content);
                    continue;
                }

                if(!empty($content->email) && !empty($content->phone)) { $new_content = $content->toArray();
                    $new_content['message'] ='Không nhập cả mail lẫn sdt à ? ';
                    array_push($data_list_error,$new_content);
                    continue;}

                // check is number
                
                if(!empty($content->password) && preg_match('/\d/', $content->password) !== 1) { // không phải là kiểu số hoặc nhỏ hơn 0
                    $new_content = $content->toArray();
                    $new_content['message'] =$content->password.'|password không phải là kiểu số nguyên';
                    array_push($data_list_error,$new_content);
                    continue;
                }

                // check is number
                
                if(!empty($content->sex) && preg_match('/\d/', $content->sex) !== 1) { // không phải là kiểu số hoặc nhỏ hơn 0
                    $new_content = $content->toArray();
                    $new_content['message'] =$content->sex.'|giới tính không phải là kiểu số nguyên';
                    array_push($data_list_error,$new_content);
                    continue;
                }

                // check is number
                
                if(!empty($content->type) && preg_match('/\d/', $content->type) !== 1) { // không phải là kiểu số hoặc nhỏ hơn 0
                    $new_content = $content->toArray();
                    $new_content['message'] =$content->type.'|quan hệ với chủ hộ không phải là kiểu số nguyên';
                    array_push($data_list_error,$new_content);
                    continue;
                }

                if (!empty($content->birthday) && !strtotime($content->birthday)) {
                    // Display valid date message
                    $new_content = $content->toArray();
                    $new_content['message'] =$content->birthday.'| ngày sinh không đúng định dạng dd/mm/yyyy';
                    array_push($data_list_error,$new_content);
                    continue;
                }
                

                $checkPhone=null;
                $checkEmail=null;
                $check_profile=null;

                if(!empty($content->email)) {
                    if(!filter_var(trim($content->email), FILTER_VALIDATE_EMAIL)){
                        //Display valid date message
                        $new_content = $content->toArray();
                        $new_content['message'] =$content->email.'| email không đúng định dạng';
                        array_push($data_list_error,$new_content);
                        continue;
                    }
                    $checkEmail = $this->modelUsers->checkExit($content->email); // is not null : là email này đã có trên hệ thống không thể cập nhật được
                    // if($checkEmail){
                    //     $new_content = $content->toArray();
                    //     $new_content['message'] =$content->email.'| email này đã có trên hệ thống không thể cập nhật được';
                    //     array_push($data_list_error,$new_content);
                    //     continue;
                    // }
                }

                if(!empty($content->phone)) { // false : là không phải số điện thoại
                   if(Helper::detect_number($content->phone) == false){
                        $new_content = $content->toArray();
                        $new_content['message'] =$content->phone.'| số điện thoại không đúng định dạng';
                        array_push($data_list_error,$new_content);
                        continue;
                   }
                    $checkPhone = $this->modelUsers->checkPhone($content->phone); // is not null : là phone này đã có trên hệ thống không thể cập nhật được
                    // if($checkPhone){
                    //     $new_content = $content->toArray();
                    //     $new_content['message'] =$content->phone.'| phone này đã có trên hệ thống không thể cập nhật được';
                    //     array_push($data_list_error,$new_content);
                    //     continue;
                    // }
                }

                $place = $this->modelBuildingPlace->findByCode($content->place, $this->building_active_id);

                if(!$place){
                    // Display valid date message
                    $new_content = $content->toArray();
                    $new_content['message'] = $content->place.'| tòa nhà này không có trên hệ thống';
                    array_push($data_list_error,$new_content);
                    continue;
                }

                $apt_id = $this->modelApartment->findByNameFloorPlace(trim($content->apartment_name), $content->floor, $place->id, $this->building_active_id);

                if(!$apt_id){
                    // Display valid date message
                    $new_content = $content->toArray();
                    $new_content['message'] = $content->apartment_name.'| căn hộ này không có trên hệ thống(Kiểm tra lại mã tòa,tầng trong căn hộ)';
                    array_push($data_list_error,$new_content);
                    continue;
                }

                $check_customer = $this->model->checkProfileApartmentV2($apt_id->id);

                if ($check_customer) {
                    if($content->type == 0){
                        $this->model->updateAllType($check_customer->bdc_apartment_id, 0, 5);
                    }
                }
                
                try {
                    DB::beginTransaction();
                    $password = $this->getToken(6);
                    $isert_user=null;
                    $check_profile=null;
                    $id_user=null;
                    
                    if (!isset($checkPhone) && !isset($checkEmail) && (!empty($content->email) || !empty($content->phone))) {
                        $isert_user = $this->modelUsers->create(['email' => !empty($content->email) ? $content->email : null, 'mobile' => !empty($content->phone) ? $content->phone : null, 'password' => Hash::make($content->password ?? $password)]);
                        
                        $type = config('typeCampain.NEW_USER');
                        $total = ['email'=> 1, 'app'=> 0, 'sms'=> 1];

                        $campain = Campain::updateOrCreateCampain("Thêm mới cư dân tòa ".@$name_building->name, $type, null, $total, $this->building_active_id, 0, 0);

                        if(!empty($content->email)){
                            $data = [
                                'params' => [
                                    '@ten' => $content->name,
                                    '@pass' => $content->password ?? $password,
                                    '@ngay' => date('d/m/Y',time()),
                                    '@urlLogin' => url('/login'),
                                    '@urlApp' => url('/login')
                                ],
                                'cc' => $content->email,
                                'building_id' => $this->building_active_id,
                                'type' => ServiceSendMailV2::NEW_USER,
                                'status' => 'create',
                                'campain_id' => $campain->id
                            ];
                            ServiceSendMailV2::setItemForQueue($data);
                        }
    
                        if(!empty($content->phone)){
                            $content_1 = [
                                'otp'=> $content->password ?? $password,
                                'account'=> $content->phone,
                             ];
                            // gui sms thong bao tai khoan duoc tao
                            SendSMSSoapV2::setItemForQueue([
                                'content' => $content_1,
                                'target' => $content->phone,
                                'building_id'=>$this->building_active_id,
                                'type'=>SendSMSSoapV2::NEW_USER,
                                'campain_id' => $campain->id
                            ]);
                        }
                    
                    }
                    if(isset($isert_user)){
                        $check_profile = $this->modelUserProfile->findByPubUserIdResident($isert_user->id, $this->building_active_id);
                    }else{
                        if($checkEmail || $checkPhone){
                            $check_profile = $this->modelUserProfile->findByPubUserIdResident(isset($checkEmail) ? $checkEmail->id : $checkPhone->id, $this->building_active_id);
                        }
                    }

                    if (!isset($check_profile)) {
                        $data_Profile = [
                            'display_name'  => $content->name,
                            'cmt' => $content->cmt,
                            'phone' => isset($content->phone) ? str_replace(array('-', '.', ' '), '', $content->phone) : null,
                            'birthday' => !empty($content->birthday) ? date('Y-m-d', strtotime($content->birthday)) : null,
                            'email' => !empty($content->email) ? $content->email : null,
                            'gender' => !empty($content->sex) ? $content->sex : null,
                            'type' => Users::USER_APP,
                            'bdc_building_id' => $this->building_active_id,
                            'app_id' => 'buildingcare',
                        ];
                        $id_user = isset($isert_user) ? $isert_user->id : (isset($checkEmail) ? $checkEmail->id : (isset($checkPhone->id) ? $checkPhone->id : 0));
                        if($id_user != 0){
                            $data_cus = $this->modelUserProfile->insertProfile(array_merge($data_Profile, ['pub_user_id' => $id_user ],$this->getCustomerCode($id_user)), $apt_id->id , $content->type);
                        }else{
                            $data_cus = $this->modelUserProfile->insertProfile(array_merge($data_Profile, ['pub_user_id' => $id_user ]), $apt_id->id , $content->type == 0 ? 5 : $content->type );
                        }
                        $this->model->create($data_cus);
                    } else {
                        $time = Carbon::now();
                        $check_customer_profile = $this->model->checkProfileApartmentCheckType($check_profile->id,$apt_id->id);
                        if(isset($check_customer_profile->type) && $check_customer_profile->type !=  $content->type){
                             $check_customer_profile->type =  $content->type;
                             $check_customer_profile->save();
                        }else{
                             $data_cus = ['bdc_apartment_id' => $apt_id->id, 'pub_user_profile_id' => $check_profile->id , 'type' =>  $content->type, 'created_at' => $time, 'updated_at' => $time];
                             $this->model->create($data_cus);
                        }

                    }
                    $new_content = $content->toArray();
                    $new_content['message'] = 'thêm mới thành công';
                    array_push($data_list_error,$new_content);
                    DB::commit();
                } catch (\Exception $e) {
                    DB::rollBack();
                    $new_content = $content->toArray();
                    $new_content['message'] = $e->getMessage();
                    array_push($data_list_error,$new_content);
                    continue;
                }
            }
        }

        if ($data_list_error) {
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            $result = $result = Excel::create('Kết quả Import', function ($excel) use ($data_list_error) {

                $excel->setTitle('Kết quả Import');
                $excel->sheet('Kết quả Import', function ($sheet) use ($data_list_error) {
                    $row = 1;
                    $sheet->row($row, [
                        'ID',
                        'Name(*)',
                        'Cmt',
                        'Phone',
                        'Email',
                        'Birthday',
                        'Password(*)',
                        'Sex(*)',
                        'Type(*)',
                        'Apartment name(*)',
                        'Floor(*)',
                        'Place(*)',
                        'Message'
                    ]);

                    foreach ($data_list_error as $key => $value) {
                        $row++;
                        $sheet->row($row, [
                            isset($value['index']) ? $value['index'] : '',
                            isset($value['name']) ? $value['name'] : '',
                            isset($value['cmt']) ? $value['cmt'] : '',
                            isset($value['phone']) ? $value['phone'] : '',
                            isset($value['email']) ? $value['email'] : '',
                            isset($value['birthday']) ? date("d/m/Y", strtotime($value['birthday'])) : '',
                            isset($value['password']) ? $value['password'] : '',
                            isset($value['sex']) ? $value['sex'] : '',
                            isset($value['type']) ? $value['type'] : '',
                            isset($value['apartment_name']) ? $value['apartment_name'] : '',
                            isset($value['floor']) ? $value['floor'] : '',
                            isset($value['place']) ? $value['place'] : '',
                            $value['message'],
                        ]);
                        if (isset($value['message']) && $value['message'] == 'thêm mới thành công') {
                            $sheet->cells('M' . $row, function ($cells) {
                                $cells->setBackground('#23fa43');
                            });
                        }
                        if (isset($value['message']) && $value['message'] != 'thêm mới thành công') {
                            $sheet->cells('M' . $row, function ($cells) {
                                $cells->setBackground('#FC2F03');
                            });
                        }
                    }
                });
            })->store('xlsx',storage_path('exports/'));
$file     = storage_path('exports/'.$result->filename.'.'.$result->ext);
return response()->download($file)->deleteFileAfterSend(true);            
             
        } 
    }


    public function action(Request $request)
    {
        return $this->modelUserProfile->action($request,$this->building_active_id);
    }
    public function download()
    {
        $file     = public_path() . '/downloads/cudan_file_import.xlsx';
        return response()->download($file);
    }
    public function downloadUpdate()
    {
        $file     = public_path() . '/downloads/cap_nhat_cudan_file_import.xlsx';
        return response()->download($file);
    }
    public function ajaxCheckType(Request $request)
    {
        $check =  UserApartments::where(['type'=>$request->type,'apartment_id'=>$request->aparment,'building_id'=> $this->building_active_id])->first();
        if (isset($check) && $check->type == 0) {
            return response()->json(['message' => 'Căn hộ này đã có chủ hộ, bạn có muốn đổi?', 'status' => 1]);
        }
        return response()->json(['message' => 'Căn hộ chưa có chủ hộ, bạn có muốn thêm?', 'status' => 0]);
    }
    public function ajaxGetCus(Request $request)
    {

        if ($request->search) {
            return response()->json($this->modelUserProfile->searchByweb($request->search));
        }
        return response()->json($this->modelUserProfile->searchByweb(''));
    }
    public function export()
    {
        $data_out = ['id', 'display_name', 'phone', 'email', 'customer_code', 'customer_code_prefix','cmt','birthday','gender','pub_user_id'];
        $customer = $this->modelUserProfile->getDataExport($data_out, $this->building_active_id)->load('pubusers','building', 'bdcCustomers', 'bdcCustomers.bdcApartment');
        try {
           $result =  Excel::create("Danh_sach_cu_dan" . date('d-m-Y-H-i-s', time()), function ($excel) use ($customer) {
                $excel->setTitle('Danh sách cư dân');
                $excel->sheet('Danh sách cư dân', function ($sheet) use ($customer) {
                    foreach ($customer as $key => $cus) {
                        $apartmentName = '';
                        $bdcCus = !empty($cus->bdcCustomers) ? $cus->bdcCustomers : [];
                        foreach ($bdcCus as $k => $re) {
                            if ($re->bdcApartment) {
                                $apartmentName .= ', ' . empty($re->bdcApartment->name) ? ', ' . $re->bdcApartment->name : '';
                            }
                        }
                        $code = $cus->customer_code_prefix . str_pad((string)$cus->customer_code, 9, "0", STR_PAD_LEFT);
                        $new_apartments[] = [
                            'ID'               => $cus->id,
                            'Họ và tên'    => $cus->display_name ?? '',
                            'Ngày sinh'    => $cus->birthday ?? '',
                            'Cmt'    => $cus->cmt ?? '',
                            'Giới tính'    => $cus->gender == 2 ? 'Nữ' : ($cus->gender == 1 ? 'Nam' : 'Khác'),
                            'Active Mobile'    => @$cus->pubusers->mobile_active,
                            'Email'        => $cus->email ?? '',
                            'Phone'          => $cus->phone ?? '',
                            'Căn hộ'     => trim($apartmentName, ', ') ?? '',
                            'Mã KH'     => $code ?? ''
                        ];
                    }
                    $sheet->setAutoSize(true);

                    // data of excel
                    if ($new_apartments) {
                        $sheet->fromArray($new_apartments);
                    }
                    // add header
                    $sheet->cell('A1:G1', function ($cell) {
                        // change header color
                        $cell->setFontColor('#000000')
                            ->setBackground('#cecece')
                            ->setFontWeight('bold')
                            ->setFontSize(10)
                            ->setAlignment('center')
                            ->setValignment('center');
                    });
                });
             })->store('xlsx',storage_path('exports/'));
             $file     = storage_path('exports/'.$result->filename.'.'.$result->ext);
             return response()->download($file)->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }

    public function sendMailChecked(Request $request)
    {
        $user = auth()->user()->BDCprofile;
        $check_profile = $this->modelUserProfile->findByidsSelectEmail(explode(',', trim($request->list_customer, ',on')));
        foreach ($check_profile as $item) {
            Mail::send([], [], function ($message) use ($item, $request, $user) {
                $message->to($item['email'], 'Resident')->replyTo('noreply@dxmb.vn', $user->display_name ?? 'Administrator')->from('noreply@dxmb.vn', $user->display_name ?? 'Administrator')->subject($request->title_send_mail)->setBody($request->description_send_mail, 'text/html');
            });
        }
        $count = count($check_profile);
        if ($count > 0) {
            return back()->with('success', 'Có ' . $count . ' email được gửi tới cư dân đã chọn');
        }
        return back()->with('error', 'Không có email nào được gửi tới cư dân');
    }
    public function sendSmsChecked(Request $request)
    {
        $check_profile = $this->modelUserProfile->findByidsSelectSms(explode(',', trim($request->list_customer, ',on')));
        foreach ($check_profile as $item) {
            // SendSMSSoapV2::setItemForQueue([
            //     'content' => $request->description_send_sms ?? '',
            //     'target' => $item['phone'],
            //     'building_id'=>$this->building_active_id,
            //     'type'=>'POST',
            // ]);
        }
        $count = count($check_profile);
        if ($count > 0) {
            return back()->with('success', 'Có ' . $count . ' sms được gửi tới cư dân đã chọn');
        }
        return back()->with('error', 'Không có sms nào được gửi tới cư dân');
    }
}
