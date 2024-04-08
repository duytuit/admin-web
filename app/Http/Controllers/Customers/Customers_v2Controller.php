<?php

namespace App\Http\Controllers\Customers;

use App\Commons\Api;
use Illuminate\Http\Request;
use App\Http\Controllers\BuildingController;

use App\Http\Requests\Customers\CustomersRequest;
use App\Models\PublicUser\Users;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Facades\Excel;
use Mail;
use App\Commons\Helper;
use App\Helpers\dBug;
use App\Http\Requests\Customers\Imports_v2Request;
use App\Http\Requests\Customers\ImportsRequest;
use App\Models\Apartments\V2\UserApartments;
use App\Models\Building\Building;
use App\Models\Campain;
use App\Models\PublicUser\V2\UserInfo;
use App\Repositories\Apartments\ApartmentsRespository;
use App\Repositories\Building\BuildingPlaceRepository;
use App\Services\SendSMSSoapV2;
use App\Services\ServiceSendMailV2;
use App\Traits\ApiResponse;
use App\Util\Debug\Log;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;

class Customers_v2Controller extends BuildingController
{
    use ApiResponse;
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

    public function __construct( Request $request,ApartmentsRespository $modelApartment,BuildingPlaceRepository $modelBuildingPlace)
    {
        $this->modelApartment = $modelApartment;
        $this->modelBuildingPlace = $modelBuildingPlace;
        parent::__construct($request);
    }
    public function index(Request $request)
    {

        $data['meta_title'] = 'Customers';
        $data['per_page'] = Cookie::get('per_page',10);
        $limit =  $data['per_page'] ?  $data['per_page'] : 10;
        $page = isset($request->page) ? $request->page : 1;
        $data['per_page'] = $limit;
        $data['data_search'] = $request->all();

        $request->request->add(['building_id' => $this->building_active_id]);
        $request->request->add(['limit' => $limit]);
        $request->request->add(['page' => $page]);

        if ($request->apartment_id) {
            $data['data_search']['apartment'] = $this->modelApartment->findById($request->apartment_id);
        }
        if ($request->building_place_id) {
            $name = $this->modelBuildingPlace->findById($request->building_place_id);
            $data['data_search']['place'] = $request->building_place_id;
            $data['data_search']['name_place'] = $name->name . ' - ' . $name->code;
        }

        if ($request->birthday_day && $request->birthday_month && $request->birthday_from_year && $request->birthday_to_year) {
               $from_date = $request->birthday_from_year.'-' .$request->birthday_month.'-'.$request->birthday_day;
               $to_date = $request->birthday_to_year.'-' .$request->birthday_month.'-'.$request->birthday_day;
               $request->request->add(['from_date' => $from_date]);
               $request->request->add(['to_date' => $to_date]);
        }
        if ($request->birthday) {
            $birthday = Carbon::parse($request->birthday)->format('Y-m-d');
            $request->request->add(['birthday' => $birthday]);
        }
        $residents = Api::GET('admin/getListResidents',$request->all());

        Log::info('check_api_customer','1_'.json_encode($residents));
        if($residents->status == true){
            $_residents = new LengthAwarePaginator($residents->data->users, $residents->data->count, $limit, $page,  ['path' => route('admin.v2.customers.index')]);
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
        $data['residents'] = @$_residents;
        return view('customers.v2.index', $data);
    }
    public function resetPass(Request $request)
    {
        if ($request->user_id == '') {
            return $this->sendErrorApi('Không có dữ liệu',[], 204);
        }
        $request->request->add(['building_id' => $this->building_active_id]);
        $result_reset = Api::POST('admin/resetUserPass',$request->all());
        
        if($result_reset->status == true){
            return $this->sendSuccessApi(['password'=>(int)$result_reset->data],200,'Số điện thoại của bạn chính xác, mật khẩu reset là '.$result_reset->data.' Mời đăng nhập lại'); 
        }else{
            return $this->sendSuccessApi([],200,$result_reset->mess); 
        }
    }
    public function searchResident(Request $request)
    {
        if ($request->text_search == '') {
            return $this->sendErrorApi('Không có dữ liệu',[], 204);
        }
        $request->request->add(['building_id' => $this->building_active_id]);
        $result_resident = Api::GET('admin/filterUser',$request->all());
        if($result_resident->status == true){
            return $this->sendSuccessApi($result_resident->data,200,'thành công.'); 
        }else{
            return $this->sendSuccessApi([],200,$result_resident->mess); 
        }
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
        return view('customers.v2.create', $data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        //hashtag = 1 : cư dân không có email, phone, cmt
       if($request->user_info_id && $request->apartment_id){
            $request->request->add(['building_id' => $this->building_active_id]);
            $input =  $request->only(['user_info_id', 'apartment_id','building_id', 'type']);
            $result_add_apartment = Api::POST('admin/addApartment',$input);
            if($result_add_apartment->status == true){
                return $this->sendSuccessApi($result_add_apartment->data,200,'thành công.'); 
            }else{
                return $this->sendSuccessApi([],200,$result_add_apartment->mess); 
            }
       }
       if($request->name && $request->apartment_id){

            $input =  [
                'phone' => $request->phone ? $request->phone : $request->phone_contact,
                'pword' =>  $request->pword ? $request->pword : self::getToken(6),
                'full_name' =>$request->name,
                'apartment_id' => $request->apartment_id,
                'building_id' => $this->building_active_id,
                'type' => $request->type,
                'birthday' => Carbon::parse($request->create_birthday)->format('Y-m-d'),
                'gender' => $request->sex ,
                'email' => $request->email ? $request->email : $request->email_contact,
                'is_send' => $request->is_send,
            ];

            $result_add_apartment = Api::POST('admin/addUser',$input);
            if($result_add_apartment->status == true){
                return $this->sendSuccessApi($result_add_apartment->data,200,$result_add_apartment->mess); 
            }else{
                return $this->sendSuccessApi([],200,$result_add_apartment->mess); 
            }
       }
       return $this->sendSuccessApi([],200,'có lỗi xảy ra.'); 
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
        $user_info = UserInfo::find($id);
        if(!$user_info){
           return redirect()->back()->with(['error' => 'Không tìm thấy thông tin.']);
        }
        $data['bdcCustomers'] = UserApartments::where('user_info_id',$id)->get();

        $data['user_info'] = $user_info;
        $data['urlApartment'] = $request->headers->get('referer');
        $data['meta_title'] = 'edit Customers';
        return view('customers.v2.edit', $data);
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
        $options = [
            'multipart' => [
                [
                    'name' => 'user_id',
                    'contents' =>  $request->user_id
                ],
                [
                    'name' => 'user_info_id',
                    'contents' =>  $request->user_info_id
                ],
                [
                    'name' => 'phone',
                    'contents' => $request->phone
                ],
                [
                    'name' => 'full_name',
                    'contents' => $request->full_name
                ],
                [
                    'name' => 'email',
                    'contents' => $request->email
                ],
                [
                    'name' => 'address',
                    'contents' =>  $request->address
                ],
                [
                    'name' => 'cmt_number',
                    'contents' =>   $request->cmt_number
                ],
                [
                    'name' => 'cmt_date',
                    'contents' =>$request->cmt_date ?  Carbon::parse($request->cmt_date)->format('Y-m-d'):null
                ],
                [
                    'name' => 'cmt_address',
                    'contents' =>  $request->cmt_address
                ],
                [
                    'name' => 'cmt_province',
                    'contents' =>  $request->cmt_province
                ],
                [
                    'name' => 'birthday',
                    'contents' =>$request->birthday ? Carbon::parse($request->birthday)->format('Y-m-d'): null
                ],
                [
                    'name' => 'gender',
                    'contents' =>   $request->gender ?? 1
                ],
                [
                    'name' => 'phone_contact',
                    'contents' =>   $request->phone_contact
                ],
                [
                    'name' => 'email_contact',
                    'contents' =>  $request->email_contact
                ],
                [
                    'name' => 'building_id',
                    'contents' =>   $this->building_active_id
                ]
            ]
        ];
        if ($request->file('avatar')) {
            $avatar =[
                'name' => 'avatar',
                'contents' => fopen($request->file('avatar')->path(), 'r'),
                'filename' => $request->file('avatar')->hashName(),
            ];
            $options['multipart'][] = $avatar;
        }
        if ($request->file('cmt_img')) {
            $cmt_img =[
                'name' => 'cmt_img',
                'contents' => fopen($request->file('cmt_img')->path(), 'r'),
                'filename' => $request->file('cmt_img')->hashName(),
            ];
            $options['multipart'][] = $cmt_img;
        }
        $residents = Api::POST_MULTIPART('admin/updateUser',$options);
        Cache::store('redis')->forget(env('REDIS_PREFIX') . 'detail_user_info_by_id_'.$request->user_info_id);
        if($residents->status == true){
            return redirect()->route('admin.v2.customers.index')->with(['success' => $residents->mess]);
        }else{
            return redirect()->route('admin.v2.customers.index')->with(['warning' => $residents->mess]); 
        }
    }
    public function addInfoApartment(Request $request)
    {
        if (!$request->apartment_id && !$request->user_info_id && !$request->type) {
            return $this->sendErrorApi('Không có dữ liệu',[], 204);
        }
        $request->request->add(['building_id' => $this->building_active_id]);
        $result_del = Api::GET('admin/deleleUserApartment',$request->all());
        $result_add = Api::POST('admin/addApartment',$request->all());
        //dBug::trackingPhpErrorV2($result_add);
        if($result_add->status == true){
            return $this->sendSuccessApi([],200,'Cập nhật thành công.'); 
        }else{
            return $this->sendSuccessApi([],200,$result_add->mess); 
        }

    }
    public function deleteInfoApartment(Request $request)
    {
        if (!$request->apartment_id && !$request->user_info_id ) {
            return $this->sendErrorApi('Không có dữ liệu',[], 204);
        }
        $request->request->add(['building_id' => $this->building_active_id]);
        $result_del = Api::GET('admin/deleleUserApartment',$request->all());
        
        if($result_del->status == true){
            return $this->sendSuccessApi([],200,'Xoá thành công.'); 
        }else{
            return $this->sendSuccessApi([],200,$result_del->mess); 
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
        return redirect()->route('admin.v2.customers.index')->with(['success' => 'Xóa cư dân thành công!', 'data_cus' => 'Xóa cư dân thành công!']);
    }
    public function destroyCus(Request $request)
    {
        if (!$request->apartment_id && !$request->user_info_id ) {
            return $this->sendErrorApi('Không có dữ liệu',[], 204);
        }
        $request->request->add(['building_id' => $this->building_active_id]);
        $result_del = Api::GET('admin/deleleUserApartment',$request->all());
        
        if($result_del->status == true){
            return redirect()->route('admin.v2.customers.index')->with(['success' => 'Xóa cư dân thành công!']);
        }else{
            return redirect()->route('admin.v2.customers.index')->with(['warning' => $result_del->mess]);
        }
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

    public function destroyCustomerApartment(Request $request)
    {
        if (!$request->user_info_id ) {
            return $this->sendErrorApi('Không có dữ liệu',[], 204);
        }
        $request->request->add(['building_id' => $this->building_active_id]);
        $result_del = Api::GET('admin/deleleUserApartment',$request->all());
        
        if($result_del->status == true){
            return back()->with('success', 'Xóa cư dân thành công!');
        }else{
            return back()->with('error', $result_del->mess);
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
        
                return redirect()->route('admin.v2.customers.index_import')->with(['success' => 'Xem danh sách import file','title'=>$messages_title, 'messages' => json_encode($data)]);;
            }
        }
        return redirect()->back();
    }
    public function indexImport(Request $request)
    {
        $data['meta_title'] = 'import file resident';
        $data['messages'] = json_decode(Session::get('messages'), true);
        $data['error_data'] = Session::get('error_data');
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
        return view('customers.v2.import', $data);
    }
    public function importFileApartment(Imports_v2Request $request)
    {
        set_time_limit(0);
        $file = $request->file('file');
        if(!$file) return redirect()->route('admin.v2.customers.index_import')->with('warning', 'Chưa có file upload!');
        $options = [
            'multipart' => [
                [
                    'name' => 'building_id',
                    'contents' =>   $this->building_active_id
                ]
            ]
        ];
        if ($request->file('file')) {
            $files =[
                'name' => 'file',
                'contents' => fopen($request->file('file')->path(), 'r'),
                'filename' => $request->file('file')->hashName(),
            ];
            $options['multipart'][] = $files;
        }
        $residents = Api::POST_MULTIPART('admin/importUserExcel',$options);
        if($residents->status == true){
            return redirect()->route('admin.v2.customers.index')->with(['success' => 'Import cư dân đang được xử lý!']);
        }else{
            return redirect()->route('admin.v2.customers.index')->with(['warning' => $residents->mess]); 
        }
     
    }
    public function indexImportUpdate(Request $request)
    {
        set_time_limit(0);
        $file = $request->file('file');
        if(!$file) return redirect()->route('admin.v2.customers.index_import')->with('warning', 'Chưa có file upload!');
        $options = [
            'multipart' => [
                [
                    'name' => 'building_id',
                    'contents' =>   $this->building_active_id
                ]
            ]
        ];
        if ($request->file('file')) {
            $files =[
                'name' => 'file',
                'contents' => fopen($request->file('file')->path(), 'r'),
                'filename' => $request->file('file')->hashName(),
            ];
            $options['multipart'][] = $files;
        }
        $residents = Api::POST_MULTIPART('admin/importUpdateUserExcel',$options);
        if($residents->status == true){
            return redirect()->route('admin.v2.customers.index')->with(['success' => 'Import cư dân đang được xử lý!']);
        }else{
            return redirect()->route('admin.v2.customers.index')->with(['warning' => $residents->mess]); 
        }
    }

    public function action(Request $request)
    {
        $method = $request->input('method','');
        if ($method == 'per_page') {
            $this->per_page($request);
            return back();
        }
        if ($method == 'delete') {
            if(count( $request->ids) > 0){
                $ids =  $request->ids;
                foreach ($ids as $key => $value) {
                    $_userApartments = UserApartments::where(['user_info_id'=>$value,'building_id'=>$this->building_active_id])->delete();
                }
            }
            return back()->with('success','xóa thành công '.count( $request->ids).' bản ghi');
        } 
    }
    public function download()
    {
        $file     = public_path() . '/downloads/cudan_file_import_5.xlsx';
        return response()->download($file);
    }
    public function downloadUpdate()
    {
        $file     = public_path() . '/downloads/cap_nhat_cudan_file_import_v2.xlsx';
        return response()->download($file);
    }
    public function ajaxCheckType(Request $request)
    {

        $check = $this->model->checkUsersType($request->type, $request->aparment, $this->building_active_id);
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
    public function export(Request $request)
    {
        //dd($request->all());
        $residents = Api::GET('dev/exportResidents',$request->all());
        dd($residents);
        return response()->download($residents);
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
        }
        $count = count($check_profile);
        if ($count > 0) {
            return back()->with('success', 'Có ' . $count . ' sms được gửi tới cư dân đã chọn');
        }
        return back()->with('error', 'Không có sms nào được gửi tới cư dân');
    }
}
