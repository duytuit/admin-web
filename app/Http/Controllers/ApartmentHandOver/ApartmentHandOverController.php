<?php

namespace App\Http\Controllers\ApartmentHandOver;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\BuildingController;

use App\Http\Requests\Customers\CustomersRequest;
use App\Models\PublicUser\Users;
use App\Repositories\ApartmentHandOver\ApartmentHandOverRespository;
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
use DB;
use App\Services\SendSMSSoapV2;
use App\Repositories\Building\CompanyRepository;
use App\Commons\Helper;
use Validator;

class ApartmentHandOverController extends BuildingController
{
     /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    private $model;
    private $modelApartment;
    private $modelApartmentHandOver;
    private $modelUsers;
    private $modelUserProfile;
    private $modelFcm;
    private $modelBuildingPlace;
    private $company;

    public function __construct(CustomersRespository $model, ApartmentsRespository $modelApartment, ApartmentHandOverRespository $modelApartmentHandOver, PublicUsersRespository $modelUsers, PublicUsersProfileRespository $modelUserProfile, FcmRespository $modelFcm, BuildingPlaceRepository $modelBuildingPlace, Request $request, CompanyRepository $company)
    {
        //$this->middleware('route_permision');
        $this->model = $model;
        $this->modelApartment = $modelApartment;
        $this->modelApartmentHandOver = $modelApartmentHandOver;
        $this->modelUsers = $modelUsers;
        $this->modelUserProfile = $modelUserProfile;
        $this->modelFcm = $modelFcm;
        $this->modelBuildingPlace = $modelBuildingPlace;
        $this->company = $company;
        parent::__construct($request);
    }
     public function index(Request $request)
    {

        $data['meta_title'] = 'Apartment';
        $data['per_page'] = Cookie::get('per_page',10);
        $data['filter_apartments'] = $request->all();
        $list_cus = $this->model->listProfileCustomerNew($this->building_active_id,$request->all());
        $list_cus_v2 = $this->model->listProfileCustomerv2($this->building_active_id);
        $data['customers'] = $list_cus;
        $data['customers_v2'] = $list_cus_v2;
        $data['data_cus'] = Session::get('data_cus');
        $data['data_error'] = Session::get('error');
        $data['data_success'] = Session::get('success');
        $data['data_search']['type'] = $request->get('type');
        $data['list_apartment_handover'] = Helper::list_apartment_handover();
        if(isset($data['filter_apartments']['bdc_apartment_id'])){
           $data['get_apartment'] = $this->modelApartment->findById($data['filter_apartments']['bdc_apartment_id']);
        }
        if(isset($data['filter_apartments']['ip_place_id'])){
            $data['get_place_building'] = $this->modelBuildingPlace->findById($data['filter_apartments']['ip_place_id']);
        }
        
        return view('apartment-handover.index', $data);
    }
    public function export(Request $request)
    {
        return $this->model->ExportCustomers($this->building_active_id,$request->all());
    }
    public function indexImport()
    {

        $data['meta_title'] = 'import file apartment';
        $data['messages'] = json_decode(Session::get('messages'), true);
        $data['error_data'] = Session::get('error_data');
        return view('apartment-handover.import', $data);
    }
    public function importFile(Request $request)
    {
        $data_success = [];
        if (!$request->file('file_import')) {
            return redirect()->route('admin.apartment.handover.index')->with('error', 'Không có file tải lên');
        }
        $data['data_import'] = $this->modelApartmentHandOver->getDataFile($request->file('file_import'), $this->building_active_id, $this->app_id);
        $messages_title='Kết quả được đối chiếu với file import';
        if ($data['data_import']['data']['data_cus'] || $data['data_import']['data']['has_cus']) {
            $data_cus = [];
            $data_error = [];
            $data_success = [];
            $data_has_error = [];
            $data_has_success = [];
            foreach ($data['data_import']['data']['data_cus'] as $key => $user) {
                $password = $this->getToken(6);
                $place = $this->modelBuildingPlace->findByCode($user['place'], $this->building_active_id);
                if ($place) {
                    $apt_id = $this->modelApartment->findByNameFloorPlace($user['apartment_name'], $user['floor'], $place->id, $this->building_active_id);
                    if ($apt_id) {

                        if ($user['phone'] || $user['email']) {
                            if(isset($user['phone']) && $user['phone']){
                                if(Helper::detect_number($user['phone']) == false) { // false : là không phải số điện thoại
                                    $data_error[] = $user;
                                    continue;
                                }
                            }
                            if(isset($user['email']) && $user['email'] && !filter_var($user['email'], FILTER_VALIDATE_EMAIL) ) {
                                //Display valid date message
                                $data_error[] = $user;
                               continue;
                            }
                            $check = $this->modelUsers->checkPhoneEmail($user['phone'], $user['email']);

                            if (!$check) {
                                 $isert_user = $this->modelUsers->create(['email' => $user['email'] ?? null, 'mobile' => $user['phone'] ?? '', 'password' => Hash::make($user['password'] ?? $password)]);
                                 $this->modelUsers->sendMail($user['email'], $user['password'] ?? $password, $user, $this->building_active_id);
                            }
                           
                            $check_profile = $this->modelUserProfile->findByPubUserIdResident( $check->id ?? $isert_user->id, $this->building_active_id);
                            
                           
                            if (!$check_profile) {

                                $data_cus = $this->modelUserProfile->insertProfileNew(array_merge($data['data_import']['data']['customers'][$key], ['pub_user_id' => $check->id ?? $isert_user->id], $this->getCustomerCode($check->id ?? $isert_user->id)), $apt_id->id ?? 0, 0);
                                $check_customer_profile = $this->model->getPurchaser($apt_id->id);
                                if(isset($check_customer_profile->type) && $check_customer_profile->type == 0){ // là chủ hộ
                                     $data_cus['type'] = 5;
                                     $this->model->create($data_cus);
                                }else{
                                     $this->model->create($data_cus);
                                }
                                $isert_user = null;

                            } else {
                                $time = Carbon::now();
                                $check_customer_profile = $this->model->getPurchaser($apt_id->id);
                                if(isset($check_customer_profile->type) && $check_customer_profile->type == 0){ // là chủ hộ
                                     
                                     $data_cus = ['bdc_apartment_id' => $apt_id->id, 'pub_user_profile_id' => $check_profile->id ?? 0, 'type' => 5, 'created_at' => $time, 'updated_at' => $time, 'status_confirm'=>$user['status_confirm'],'handover_date'=> $user['handover_date'],'note_confirm'=> $user['note_confirm'],'is_resident'=> 1];
                                     $this->model->create($data_cus);
                                }else{
                                     $data_cus = ['bdc_apartment_id' => $apt_id->id, 'pub_user_profile_id' => $check_profile->id ?? 0, 'type' => 0, 'created_at' => $time, 'updated_at' => $time, 'status_confirm'=>$user['status_confirm'],'handover_date'=> $user['handover_date'],'note_confirm'=> $user['note_confirm'],'is_resident'=> 1];
                                     $this->model->create($data_cus);
                                }

                            }
                        }
                        $data_success[] = $user;
                    }

                    if (!$apt_id) {
                        $data_error[] = $user;
                    }
                } else {
                    $data_error[] = $user;
                }
            }
            if (!empty($data_error)) {
                $data['data_import']['messages'][] = ['messages' => 'Có ' . count($data_error) . ' không có căn hộ trên hệ thống, Không được thêm dữ liệu hoặc số điện thoại, email không đúng định dạng', 'data' => $data_error, 'color' => 'red'];
            }
            if (!empty($data_success)) {
                $data['data_import']['messages'][] = ['messages' => 'Có ' . count($data_success) . ' dữ liệu cư dân hoàn chỉnh được cập nhật trên hệ thống', 'data' => $data_success, 'color' => 'green'];
            }

            if (!empty($data_has_error)) {
                $data['data_import']['messages'][] = ['messages' => 'Có ' . count($data_has_error) . ' không có căn hộ trên hệ thống, Không được thêm dữ liệu', 'data' => $data_has_error, 'color' => 'red'];
            }
            if (!empty($data_has_success)) {
                $data['data_import']['messages'][] = ['messages' => 'Có ' . count($data_has_success) . ' Dữ liệu cư dân hoàn chỉnh và đã có trên hệ thống, được cập nhật vào căn hộ ', 'data' => $data_has_success, 'color' => 'orange'];
            }
        }
        return redirect()->route('admin.apartment.handover.index_import')->with(['success' => 'Import file thành công' ,'title'=>$messages_title,'messages' => json_encode($data['data_import']['messages'])]);
    }
    public function save(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'     => 'required',
            'email'     => 'required',
            'phone'     => 'required',
            'address'     => 'required',
            'password'     => 'required',
            'create_bdc_apartment_id'     => 'required',
        ]);
        if ($validator->fails()) {
             $message = [
                'success' => false,
                'message' => 'Thêm bản ghi cần điền đầy đủ thông tin!'
            ];
            return response()->json($message);
        }
        DB::beginTransaction();

        try {

            $user = $this->createUser($request);
            $result = $this->addCustomerToApartment($user, $request);
            if($result){
                return response()->json($result);
            }
            DB::commit();
            $message = [
                'success' => true,
                'message' => 'Thêm bản ghi thành công!'
            ];
            return response()->json($message);

        } catch (\Exception $e) {
            DB::rollBack();
            //throw new \Exception("Đã có lỗi xẩy ra khi tạo mới cư dân.( 1402 ) " . $e->getMessage(), 1);
            $message = [
                'success' => false,
                'message' => 'Thêm bản ghi thất bại!'
            ];
            return response()->json($message);
        }

       
        
    }
    private function createUser($request)
    {
        $email = $this->modelUsers->checkExit($request->email);
        $phone = $this->modelUsers->checkPhone($request->phone);
        // neu user ko ton tai se tao moi
       
        if ($email || $phone) {

            $user = $this->modelUsers->create([
                'email' => $request->email,
                'mobile'  => $request->phone,
                'password' => Hash::make($request->password),
                'status' => 1 // mac dinh active user
            ]);

            // gui mai thong bao tai khoan duoc tao
            $this->modelUsers->sendMail($request->email, $request->password, $request->name, $this->building_active_id);
            // gui sms thong bao tai khoan duoc tao
            $content = [
                'otp'=> $request->password,
             ];
            // gui sms thong bao tai khoan duoc tao
            if($phone){
                SendSMSSoapV2::setItemForQueue([
                    'content' => $content,
                    'target' => $request->phone,
                    'building_id'=>$this->building_active_id,
                    'type'=>SendSMSSoapV2::NEW_USER,
                ]);
            }
            return $user;
        }else{
            return $email ?? $phone;
        }

       
    }
    private function addCustomerToApartment($user, $request)
    {
        $time = Carbon::now();
        $profile = $this->findOrCreateProfile($user, $request);

        //kiem tra xem cu dan da co trong can ho chua
        $apartment = $this->model->checkCusExit($profile->id, $request->create_bdc_apartment_id, $this->building_active_id);
        if ($apartment) {
            $message = [
                'success' => false,
                'message' => 'Cư dân đã tồn tại trong căn hộ với tên là: ' . $profile->display_name
            ];
            return $message;
        }
        // add moi cu dan vao can ho
        //  $data_no_cus[] = [
        //      'bdc_apartment_id' => $request->create_bdc_apartment_id, 
        //      'pub_user_profile_id' => $profile->id,
        //      'type' => 0,
        //      'created_at' => $time,
        //      'updated_at' => $time,
        //      'status_confirm'=>2,
        //      'handover_date'=> $request->from_date
        // ];
        $customer =  $this->model->create([
            'pub_user_profile_id' => $profile->id,
            'bdc_apartment_id' => $request->create_bdc_apartment_id,
            'type' => 0,
            'status_confirm'=> $request->create_status_confirm,
            'handover_date'=> $request->from_date,
            'is_resident'=> 1
        ]);
        // gui mai thong bao duoc them vao can ho
        $this->model->sendNotifyNewCustomer($request->email, $request->name, $this->building_active_id, @$customer->bdcApartment->name);
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
                'address' => $request->address ?? null,
                'pub_user_id' => $user ? $user->id : 0,
                'type' => Users::USER_APP,
                'bdc_building_id' => $this->building_active_id,
                'app_id' => $this->app_id,
                'customer_code_prefix' => $user ? $rs['customer_code_prefix'] : null,
                'customer_code' => $user ? $rs['customer_code'] : null,

            ]);
        }
        return $profile;
    }
    public function download()
    {
        $file     = public_path() . '/media/files/hand_over_apartment_file_import_new.xlsx';
        return response()->download($file);
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
    private function getCustomerCode($pub_user_id)
    {
        return Helper::getCustomerCode($pub_user_id, $this->building_active_id);
    }
    public function change_status_confirm(Request $request)
    {
        try {
            $result = $this->model->change_status_confirm($request->id, $request->status_confirm);
            if($result){
                $message = [
                    'success' => true,
                    'message' => 'Thay đổi trạng thái thành công!'
                ];
                return response()->json($message);
            }
            $message = [
                'success' => false,
                'message' => 'Thay đổi trạng thái thất bại!'
            ];
            return response()->json($message);
        } catch (\Exception $e) {
            $message = [
                'success' => false,
                'message' => 'Thay đổi trạng thái thất bại!'
            ];
            return response()->json($message);
        }
        
    }
    public function change_note_confirm(Request $request)
    {
       
        try {
            $result = $this->model->change_note_confirm($request->id, $request->note_confirm);
            if($result){
                $message = [
                    'success' => true,
                    'message' => 'Cập nhật ghi chú thành công!'
                ];
                return response()->json($message);
            }
            $message = [
                'success' => false,
                'message' => 'Cập nhật ghi chú thất bại!'
            ];
            return response()->json($message);
        } catch (\Exception $e) {
            $message = [
                'success' => false,
                'message' => 'Cập nhật ghi chú thất bại!'
            ];
            return response()->json($message);
        }
    }
    public function change_success_handover(Request $request)
    {
        try {
            if($request->success_handover == 1){ //Đã bàn giao
               $result = $this->model->change_success_handover($request->id, $request->success_handover,null);
            }else{  //Chưa bàn giao
               $result = $this->model->change_success_handover($request->id, $request->success_handover,1);
            }
            if($result){
                $message = [
                    'success' => true,
                    'message' => 'Cập nhật thành công!'
                ];
                return response()->json($message);
            }
            $message = [
                'success' => false,
                'message' => 'Cập nhật thất bại!'
            ];
            return response()->json($message);
            
        } catch (\Exception $e) {
            $message = [
                'success' => false,
                'message' => 'Cập nhật thất bại!'
            ];
            return response()->json($message);
        }
    }
    public function action(Request $request)
    {
        return $this->modelUserProfile->action_customer_ids($request);
    }
    public function change_date_handover(Request $request)
    {
        try {
            $result = $this->model->change_date_handover($request->id, $request->date_handover);
            if($result){
                $message = [
                    'success' => true,
                    'message' => 'Cập nhật bàn giao thành công!'
                ];
                return response()->json($message);
            }
            $message = [
                'success' => false,
                'message' => 'Cập nhật bàn giao thất bại!'
            ];
            return response()->json($message);
        } catch (\Exception $e) {
            $message = [
                'success' => false,
                'message' => 'Cập nhật bàn giao thất bại!'
            ];
            return response()->json($message);
        }
    }
}
