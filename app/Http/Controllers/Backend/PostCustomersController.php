<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Backend\Controller;
use App\Http\Controllers\BuildingController;
use App\Http\Requests\PostRequest;
use App\Models\Apartments\Apartments;
use App\Models\BoCustomer;
use App\Models\Building\BuildingPlace;
use App\Models\Category;
use App\Models\Comment;
use App\Models\CustomerGroup;
use App\Models\Customers\Customers;
use App\Repositories\Customers\CustomersRespository;
use App\Models\Partner;
use App\Models\PollOption;
use App\Models\Post;
use App\Models\PublicUser\UserInfo;
use App\Models\PublicUser\Users;
use App\Models\UrlAlias;
use App\Repositories\Comments\CommentsRespository;
use App\Repositories\NotifyLog\NotifyLogRespository;
use App\Repositories\PostsCustomers\PostsRespository;
use App\Repositories\PublicUsers\PublicUsersProfileRespository;
use App\Services\FCM\SendNotifyFCMService;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Validator;
use App\Services\SendSMSSoap;
use App\Services\ServiceSendMail;
use App\Services\ServiceSendMailV2;
use App\Services\SendSMSSoapV2;
use App\Commons\Helper;
use App\Models\Asset\AssetHandOver;
use App\Models\Building\Building;
use App\Models\Campain;
use App\Models\Fcm\Fcm;
use App\Models\SentStatus;

class PostCustomersController extends BuildingController
{
       /**
     * Khởi tạo
     */
    const POST_NEW = "POST";
    const BILL_NEW = "BILL";

    private $modelPost;
    private $modelCustomers;
    private $modelUserInfo;
    private $log;

    public function __construct(PostsRespository $modelPost,CustomersRespository $modelCustomers,NotifyLogRespository $log,PublicUsersProfileRespository $modelUserInfo,Request $request)
    {
        $this->model = new Post();
        $this->modelPost = $modelPost;
        $this->modelCustomers = $modelCustomers;
        $this->modelUserInfo = $modelUserInfo;
        $this->log = $log;
        Carbon::setLocale('vi');
        parent::__construct($request);
    }

    /**
     * Danh sách bản ghi
     *
     * @param  Request  $request
     * @return Response
     */
    public function index(Request $request)
    {
        // Phân trang
        $data['per_page'] = Cookie::get('per_page', 20);
        $list_cus_v2 = $this->modelCustomers->listProfileCustomerv2($this->building_active_id);
        // Tìm kiếm nâng cao
        $advance = 0;

        $data['keyword']     = $request->input('keyword', '');
        $data['hashtag']     = $request->input('hashtag', '');
        $data['category_id'] = $request->input('category_id', '');
        $data['customer'] = $request->input('customer', '');

        $data['type']        = 'article';
        $data['status']      = $request->input('status', '');
        $data['private']     = $request->input('private', '');

        $where = [];

//        if ($data['keyword']) {
//            $where[] = ['title', 'like', '%' . $data['keyword'] . '%'];
//        }
//
//        if ($data['hashtag']) {
//            $where[] = ['hashtag', 'like', '%' . $data['hashtag'] . '%'];
//            $advance = 1;
//        }

        if ($data['category_id']) {
            $where[] = ['category_id', '=', (int) $data['category_id']];
            $advance = 1;
        }

        if ($data['type']) {
            $where[] = ['type', '=', $data['type']];
        }
        if ($data['customer']) {
            $where[] = ['user_id', '=', $data['customer']];
            $advance = 1;
        }

        if ($data['status'] != '') {
            $where[] = ['status', '=', $data['status']];
            $advance = 1;
        }

        if ($data['private'] != '') {
            $where[] = ['private', '=', $data['private']];
            $advance = 1;
        }

        $posts = Post::where($where);
        if ($data['keyword']) {
            $posts->Where(function ($query) use ($request) {
                $query->orWhere('title', 'like', '%' . $request->keyword . '%');
                $query->orWhere('hashtag', 'like', '%' . $request->keyword . '%');
            });
            $advance = 1;
        }
        if($request->customer){
            $data['customer'] = $this->modelUserInfo->getOne('id',$request->customer);
        }
        $posts->where('bdc_building_id',$this->building_active_id);
        $posts = $posts->where('status_is_customer',1)->orderByRaw('id DESC')->paginate($data['per_page']);// status_is_customer == 1 là thông báo bàn giao căn hộ

        $posts->load('user','user.BDCprofile', 'category');

        $data['posts'] = $posts;
        $data['customers_v2'] = $list_cus_v2;
        // Danh mục
        $where   = [];
        $where[] = ['type', '=', $data['type']];

        $data['categories'] = Category::where($where)->get();

        $heading = [
            'article' => 'Thông báo',
            'event'   => 'Sự kiện',
            'voucher' => 'Khuyến mại',
        ];

        $data['heading']    = $heading[$data['type']];
        $data['meta_title'] = "QL {$data['heading']} > Bài viết";
        $data['now']        = Carbon::now();

        $data['advance'] = $advance;

        return view('backend.posts-customers.index', $data);
    }
     /**
     * Danh sách bản ghi
     *
     * @param  Request  $request
     * @return Response
     */
    public function indexAssetHandover(Request $request)
    {
        // Phân trang
        $data['per_page'] = Cookie::get('per_page', 20);
        $list_cus_v2 = $this->modelCustomers->listProfileCustomerv2($this->building_active_id);
        // Tìm kiếm nâng cao
        $advance = 0;

        $data['keyword']     = $request->input('keyword', '');
        $data['hashtag']     = $request->input('hashtag', '');
        $data['category_id'] = $request->input('category_id', '');
        $data['customer'] = $request->input('customer', '');

        $data['type']        = 'article';
        $data['status']      = $request->input('status', '');
        $data['private']     = $request->input('private', '');

        $where = [];

        if ($data['category_id']) {
            $where[] = ['category_id', '=', (int) $data['category_id']];
            $advance = 1;
        }

        if ($data['type']) {
            $where[] = ['type', '=', $data['type']];
        }
        if ($data['customer']) {
            $where[] = ['user_id', '=', $data['customer']];
            $advance = 1;
        }

        if ($data['status'] != '') {
            $where[] = ['status', '=', $data['status']];
            $advance = 1;
        }

        if ($data['private'] != '') {
            $where[] = ['private', '=', $data['private']];
            $advance = 1;
        }

        $posts = Post::where($where);
        if ($data['keyword']) {
            $posts->Where(function ($query) use ($request) {
                $query->orWhere('title', 'like', '%' . $request->keyword . '%');
                $query->orWhere('hashtag', 'like', '%' . $request->keyword . '%');
            });
            $advance = 1;
        }
        if($request->customer){
            $data['customer'] = $this->modelUserInfo->getOne('id',$request->customer);
        }
        $posts->where('bdc_building_id',$this->building_active_id);
        $posts = $posts->where('status_is_customer',2)->orderByRaw('id DESC')->paginate($data['per_page']);//status_is_customer ==2 thông báo bàn giao tài sản căn hộ

        $posts->load('user','user.BDCprofile', 'category');

        $data['posts'] = $posts;
        $data['customers_v2'] = $list_cus_v2; // hiển thi emai và số điện thoại thông báo
        // Danh mục
        $where   = [];
        $where[] = ['type', '=', $data['type']];

        $data['categories'] = Category::where($where)->get();

        $heading = [
            'article' => 'Thông báo',
            'event'   => 'Sự kiện',
            'voucher' => 'Khuyến mại',
        ];

        $data['heading']    = $heading[$data['type']];
        $data['meta_title'] = "QL {$data['heading']} > Bài viết";
        $data['now']        = Carbon::now();

        $data['advance'] = $advance;

        return view('backend.posts-customers.index_asset_handover', $data);
    }

    public function indexEvent(Request $request)
    {
        // Phân trang
        $data['per_page'] = Cookie::get('per_page', 20);

        // Tìm kiếm nâng cao
        $advance = 0;

        $data['keyword']     = $request->input('keyword', '');
        $data['hashtag']     = $request->input('hashtag', '');
        $data['category_id'] = $request->input('category_id', '');
        $data['customer'] = $request->input('customer', '');
        $data['type']        = 'event';
        $data['status']      = $request->input('status', '');
        $data['private']     = $request->input('private', '');

        $where = [];

//        if ($data['keyword']) {
//            $where[] = ['title', 'like', '%' . $data['keyword'] . '%'];
//        }
//
//        if ($data['hashtag']) {
//            $where[] = ['hashtag', 'like', '%' . $data['hashtag'] . '%'];
//            $advance = 1;
//        }

        if ($data['category_id']) {
            $where[] = ['category_id', '=', (int) $data['category_id']];
            $advance = 1;
        }

        if ($data['type']) {
            $where[] = ['type', '=', $data['type']];
        }

        if ($data['status'] != '') {
            $where[] = ['status', '=', $data['status']];
            $advance = 1;
        }

        if ($data['private'] != '') {
            $where[] = ['private', '=', $data['private']];
            $advance = 1;
        }

        $posts = Post::where($where);
        if ($data['keyword']) {
            $posts->Where(function ($query) use ($request) {
                $query->orWhere('title', 'like', '%' . $request->keyword . '%');
                $query->orWhere('hashtag', 'like', '%' . $request->keyword . '%');
            });
            $advance = 1;
        }
        if($request->customer){
            $data['customer'] = $this->modelUserInfo->getOne('id',$request->customer);
        }
        $posts->where('bdc_building_id',$this->building_active_id);
        $posts = $posts->orderByRaw('id DESC')->paginate($data['per_page']);

        $posts->load('user', 'category');

        $data['posts'] = $posts;

        // Danh mục
        $where   = [];
        $where[] = ['type', '=', $data['type']];

        $data['categories'] = Category::where($where)->get();
        $heading = [
            'article' => 'Thông báo',
            'event'   => 'Sự kiện',
            'voucher' => 'Khuyến mại',
        ];

        $data['heading']    = $heading[$data['type']];
        $data['meta_title'] = "QL {$data['heading']} > Bài viết";
        $data['now']        = Carbon::now();

        $data['advance'] = $advance;

        return view('backend.posts-customers.index', $data);
    }

    /**
     * Sửa bản ghi
     *
     * @param  Request  $request
     * @param  int  $id
     * @return Response
     */
    public function edit(Request $request, $id = 0)
    {
        if ($id > 0) {
            $post         = Post::findOrFail($id);
            $data['type'] = $post->type;
        } else {
            $post         = new Post;
            $data['type'] = $request->input('type', 'article');
        }

        $poll_options = $post->pollOptions();

        // Bài viết
        $data['id']           = $id;
        $data['post']         = $post;
        $data['poll_options'] = $poll_options;
        $data['now']          = Carbon::now();

        // Danh mục
        $data['categories'] = Category::searchBy([
            'order_by' => 'title ASC',
            'where'    => [
                ['type', '=', $data['type']],
            ],
        ]);

        // Đối tác
        $data['partners'] = Partner::searchBy([
            'order_by' => 'name ASC',
        ]);

        // Nhóm KH
//        $data['groups'] = CustomerGroup::searchBy([
//            'order_by' => 'name ASC',
//        ]);
        $data['floors'] = Apartments::select('floor')->where('building_id',$this->building_active_id)->distinct()->orderBy('floor', 'asc')->get()->toArray();

        // Các KH
        $notify       = old('notify', $post->notify);
        $customer_ids = $notify['customer_ids'] ?? [];
        $place_ids = $notify['place_ids'] ?? [];
        $group_ids = $notify['group_ids'] ?? [];
        $floors_ids = $notify['floor_ids'] ?? [];

        $data['customers'] = UserInfo::whereIn('id', $customer_ids)->where('bdc_building_id',$this->building_active_id)->get();
        $data['places'] = BuildingPlace::whereIn('id', $place_ids)->where('bdc_building_id',$this->building_active_id)->get();
        $data['groups'] = Apartments::whereIn('id', $group_ids)->where('building_id',$this->building_active_id)->get();
        $data['floors_ids'] = $floors_ids;

        $heading = [
            'article' => 'Thông báo',
            'event'   => 'Sự kiện',
            'voucher' => 'Khuyến mại',
        ];
        $data['heading']    = $heading[$data['type']];
        $data['building_id']    = $this->building_active_id;
        $data['meta_title'] = "QL {$data['heading']} > Bài viết";

        return view('backend.posts-customers.edit', $data);
    }
    public function save_apartment(Request $request)
    {
        $building = Building::get_detail_building_by_building_id($this->building_active_id);
        try {
            $customers_ids=json_decode($request->customers_id,true);
            $khach_hang = $this->modelCustomers->findCusIds($customers_ids);
            $notify_khach_hang = array_column($khach_hang->toArray(), 'pub_user_profile_id');
            $myArray = array_map('strval',$notify_khach_hang);

            $notify = [
            'send_mail'      => $request->notify['send_mail']??0,
            'send_sms'       => $request->notify['send_sms']??0,
            'send_app'       => $request->notify['send_app']??0,
            'all_selected'   => 1,
            'floor_selected'   =>0,
            'place_selected'   =>0,
            'customer_selected'   =>0,
            'customer_ids' => $myArray ?? [],
            'is_sent'   =>0,
            'is_sent_sms'   =>0,
            'group_selected' => [],
            'place_ids'   => [],
            ];

            $post_apartment =  $this->modelPost->create([
                'user_id'=>Auth::user()->id,
                'type' => 'article',
                'category_id'=>1,
                'title' => $request->title,
                'content' =>  $request->content,
                'publish_at' =>  Carbon::now(),
                'notify' => json_encode($notify),
                'status'  => $request->status??0,
                'content_sms' => $request->description,
                'bdc_building_id'=>$this->building_active_id,
                'status_is_customer' => 1, // khách hàng
                'lists_notify_apartment'=>$request->customers_id,
            ]);
            $warning_sms=null;
            if($khach_hang){
                $total = ['email'=>0, 'app'=> 0, 'sms'=> 0];
                if(isset($request->notify['send_mail']) && (int)$request->notify['send_mail'] == 1){
                    $total['email'] = sizeof($khach_hang);
                }
                if(isset($request->notify['send_app']) && (int)$request->notify['send_app']== 1){     
                    $userIdList = [];
                    foreach ($khach_hang as $key => $value) {
                        array_push($userIdList, $value->pubUserProfile->pubusers->id);
                    }
                    $countTokent = (int)Fcm::getCountTokenbyUserId($userIdList);     
                    $total['app'] = $countTokent;
                }
                if(isset($request->notify['send_sms']) && (int)$request->notify['send_sms'] == 1){
                    $total['sms'] = sizeof($khach_hang);
                }

                if($total['sms']> 0 || $total['app']> 0 || $total['email'] > 0){
                    $campain = Campain::updateOrCreateCampain($post_apartment->title, config('typeCampain.POST_NEWS'), $post_apartment->id, $total, $post_apartment->bdc_building_id, 0, 0);
                }
                foreach ($khach_hang as $key => $value) {
                    if(isset($request->notify['send_mail']) && (int)$request->notify['send_mail'] == 1){
                        ServiceSendMailV2::setItemForQueue( [
                            'params' => [
                            '@ten' => @$value->pubUserProfile->display_name,
                            '@url'=> ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == "on") ? "https" : "http"). "://". @$_SERVER['HTTP_HOST']
                            ],
                            'cc' => @$value->pubUserProfile->email,
                            'building_id' => $this->building_active_id,
                            'type' => ServiceSendMailV2::POST_NEWS,
                            'status' => 'prepare',
                            'subject' => '[BuildingCare] thông báo mới đến khách hàng',
                            'content'=> $request->content,
                            'campain_id' => $campain->id,
                        ]);
                    }
                    
                    if(isset($request->notify['send_sms']) && (int)$request->notify['send_sms'] == 1){
                    if(@$value->pubUserProfile->phone){
                        $number=@$value->pubUserProfile->phone;

                        $description =[
                        'khach_hang'     => Helper::convert_vi_to_en(@$value->pubUserProfile->display_name) ?? null,
                        'cab_ho'         => Helper::convert_vi_to_en(@$value->bdcApartment->name) ?? null,
                        'ngay_ban_giao'  => date('d-m-Y', strtotime($value->handover_date)) ?? null
                        ];
                        if($request->custom_template_email == 'mac_dinh'){ // gửi theo mẫu sms mặc định

                           $content = $description;
                           $type    = SendSMSSoapV2::APARTMENT_HANDOVER;

                        $result_sms =  SendSMSSoapV2::sendSMS($content,@$value->pubUserProfile->phone,@$value->pubUserProfile->bdc_building_id, $type);
                        }else{
                        if(isset($request->description) && strlen($request->description) < 160){
                            $content = [
                                    'params' => [
                                                '@khachhang'     => Helper::convert_vi_to_en(@$value->pubUserProfile->display_name) ?? null,
                                                '@canho'         => Helper::convert_vi_to_en(@$value->bdcApartment->name) ?? null,
                                                '@ngaybangiao'  => date('d-m-Y', strtotime($value->handover_date)) ?? null
                                                ],
                                    'content' => Helper::convert_vi_to_en($request->description)    // tự soạn nội dung sms
                               ];
                               $type    = SendSMSSoapV2::APARTMENT_HANDOVER_CUSTOM;

                            $result_sms =  SendSMSSoapV2::sendSMS($content,@$value->pubUserProfile->phone,@$value->pubUserProfile->bdc_building_id, $type);
                        }else{
                            $warning_sms = 'Nội dung gửi sms phải dưới 160 ký tự';
                        }
                        }
                    }
                    }
                    if(isset($request->notify['send_app']) && (int)$request->notify['send_app']== 1){
                        $data_noti = [
                                "message" => $request->title,
                                "building_id"=> $this->building_active_id,
                                "title"=> $request->title,
                                "action_name"=> self::POST_NEW,
                                "image"=> $request->image,
                                "user_id" => $value->pubUserProfile->pubusers->id,
                                "app_config" => @$building->template_mail == 'asahi' ? 'asahi' : 'cudan',
                                'type'=>SendNotifyFCMService::NEW_POST_EVENT,
                                'campain_id' => $campain->id,
                                'app'=>'v1'
                        ];
                        SendNotifyFCMService::setItemForQueueNotify($data_noti);
                    }
                    $value->status_confirm = CustomersRespository::NOTICE_SENT;
                    $value->save();
                }
            }
            
            $message = [
                'success' => true,
                'message' => 'Gửi thông báo thành công!'.$warning_sms
            ];
            return response()->json($message);
        } catch (\Exception $e) {
            $message = [
                'success' => false,
                'message' => $e->getMessage()
            ];
            return response()->json($message);
        }
    }
    public function save_asset_hand_over(Request $request)
    {
        try {
            $group_ids = json_decode($request->customers_id,true); // căn hộ

            $asset_ids = json_decode($request->asset_handover_ids,true); // id asset



            $notify = [
            'send_mail'      => $request->notify['send_mail']??0,
            'send_sms'       => $request->notify['send_sms']??0,
            'send_app'       => $request->notify['send_app']??0,
            'all_selected'   => 1,
            'floor_selected'   =>0,
            'place_selected'   =>0,
            'customer_selected'   =>0,
            'customer_ids' => [],
            'is_sent'   =>0,
            'is_sent_sms'   =>0,
            'group_selected' => [],
            'place_ids'   => [],
            'group_ids'  => $group_ids ?? [],
            ];
            $warning_sms=null;
            if($asset_ids){

                $post_apartment =  $this->modelPost->create([
                    'user_id'=>Auth::user()->id,
                    'type' => 'article',
                    'category_id'=>1,
                    'title' => $request->title,
                    'content' =>  $request->content,
                    'publish_at' =>  Carbon::now(),
                    'notify' => json_encode($notify),
                    'status'  => $request->status??0,
                    'content_sms' => $request->description,
                    'bdc_building_id'=>$this->building_active_id,
                    'status_is_customer' => 2, // khách hàng
                    'lists_notify_apartment'=>$request->customers_id,
                ]);

                $total = ['email'=>0, 'app'=> 0, 'sms'=> 0];
                if(isset($request->notify['send_mail']) && (int)$request->notify['send_mail'] == 1)
                    $total['email'] = sizeof($asset_ids);  
                if(isset($request->notify['send_sms']) && (int)$request->notify['send_sms'] == 1)
                    $total['sms'] = sizeof($asset_ids);
                
                if($total['sms']> 0 || $total['app']> 0 || $total['email'] > 0){
                    $campain = Campain::updateOrCreateCampain($post_apartment->title, config('typeCampain.POST_NEWS'), $post_apartment->id, $total, $post_apartment->bdc_building_id, 0, 0);
                }

                foreach ($asset_ids as $key => $value) {

                    $asset_handover = AssetHandOver::find($value);
                    $notify['asset_handover'][] =@$asset_handover->apartment->name .' | '. $asset_handover->customer.' | '.$asset_handover->email.' | '.$asset_handover->phone;
                    if($asset_handover && isset($request->notify['send_mail']) && (int)$request->notify['send_mail'] == 1){
                        ServiceSendMailV2::setItemForQueue( [
                            'params' => [
                            '@ten' => @$asset_handover->customer,
                            '@url'=> ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == "on") ? "https" : "http"). "://". @$_SERVER['HTTP_HOST']
                            ],
                            'cc' => @$asset_handover->email,
                            'building_id' => $this->building_active_id,
                            'type' => ServiceSendMailV2::POST_NEWS,
                            'status' => 'prepare',
                            'subject' => '[BuildingCare] thông báo mới đến khách hàng',
                            'content'=> $request->content,
                            'campain_id' => $campain->id,    
                        ]);
                    }
                    
                    if($asset_handover && isset($request->notify['send_sms']) && (int)$request->notify['send_sms'] == 1){
                    if($asset_handover->phone){

                            $description =[
                            'khach_hang'     => $asset_handover->customer ? Helper::convert_vi_to_en($asset_handover->customer) : null,
                            'cab_ho'         => Helper::convert_vi_to_en(@$asset_handover->apartment->name) ?? null,
                            'tai_san'         => Helper::convert_vi_to_en(@$asset_handover->asset->name) ?? null,
                            'ngay_ban_giao'  => date('d-m-Y', strtotime($asset_handover->date_expected)) ?? null
                            ];
                            if($request->custom_template_email == 'mac_dinh'){ // gửi theo mẫu sms mặc định

                                $content = $description;
                                $type    = SendSMSSoapV2::ASSET_HANDOVER;

                                $result_sms =  SendSMSSoapV2::sendSMS($content,$asset_handover->phone,$asset_handover->bdc_building_id, $type);
                            }else{
                                if(isset($request->description) && strlen($request->description) < 160){
                                    $content = [
                                            'params' => [
                                                        '@khach_hang'     => $asset_handover->customer ? Helper::convert_vi_to_en($asset_handover->customer) : null,
                                                        '@cab_ho'         => $asset_handover->apartment ? Helper::convert_vi_to_en(@$asset_handover->apartment->name) : null,
                                                        '@tai_san'       => @$asset_handover->asset ? Helper::convert_vi_to_en(@$asset_handover->asset->name) : null,
                                                        '@ngay_ban_giao'   => $asset_handover->date_expected ? date('d-m-Y', strtotime($asset_handover->date_expected)) : null
                                                        ],
                                            'content' => Helper::convert_vi_to_en($request->description)    // tự soạn nội dung sms
                                    ];
                                    $type    = SendSMSSoapV2::POST_CONTENT;

                                    $result_sms =  SendSMSSoapV2::sendSMS($content,$asset_handover->phone,$asset_handover->bdc_building_id, $type);
                                }else{
                                    $warning_sms = 'Nội dung gửi sms phải dưới 160 ký tự';
                                }
                            }
                    }
                    }
                }
            }

            $post_apartment =  $this->modelPost->updateOrCreate(['id'=>$post_apartment->id],[
                'user_id'=>Auth::user()->id,
                'type' => 'article',
                'category_id'=>1,
                'title' => $request->title,
                'content' =>  $request->content,
                'publish_at' =>  Carbon::now(),
                'notify' => json_encode($notify),
                'status'  => $request->status??0,
                'content_sms' => $request->description,
                'bdc_building_id'=>$this->building_active_id,
                'status_is_customer' => 2, // khách hàng
                'lists_notify_apartment'=>$request->customers_id,
            ]);
            $message = [
                'success' => true,
                'message' => 'Gửi thông báo thành công!'.$warning_sms
            ];
            return response()->json($message);
        } catch (\Exception $e) {
            $message = [
                'success' => false,
                'message' => $e->getMessage()
            ];
            return response()->json($message);
        }
    }
    /**
     * Lưu bản ghi
     *
     * @param  PostRequest $request
     * @param  int  $id
     * @return Response
     */
    public function save(PostRequest $request, $id = 0)
    {

        $type = $request->input('type', 'article');

        $post = Post::findOrNew($id);

        $poll_option_ids = $request->poll_option_ids ?: [];

        if ($poll_option_ids) {
            $poll_option_ids = explode(',', $poll_option_ids);
        }
        if ($post->poll_options) {
            $poll_option_ids = array_merge($post->poll_options, $poll_option_ids);
        }

        $pollOptions = PollOption::whereIn('id', $poll_option_ids)->whereNull('post_id')->get();

        $input = $request->all();

        $params = [
            'id'           => $id,
            'status'       => $request->input('status', 0),
            'user_id'      => Auth::user()->id,
            'title'        => $request->title,
            'alias'        => $request->alias,
            'summary'      => $request->summary,
            'content'      => $request->content,
            'start_at'     => $request->start_at,
            'end_at'       => $request->end_at,
            'address'      => $request->address,
            'hashtag'      => $request->hashtag,
            'url_video'    => $request->url_video,
            'category_id'  => $request->category_id??0,
            'publish_at'   => $request->publish_at,
            'private'      => $request->private,
            'type'         => $request->type,
            'poll_options' => $poll_option_ids,
            'image'        => $request->image,

        ];
        if($id==0){
            $params += [
            'bdc_building_id'        => $this->building_active_id,
            ];
        }

        $params = array_merge($input, $params);

        $notify = [
            'send_mail'      => 0,
            'send_sms'       => 0,
            'send_app'       => 0,
            'all_selected'   => 0,
            'floor_selected'   =>0,
            'place_selected'   =>0,
            'customer_selected'   =>0,
            'is_sent'   =>0,
            'is_sent_sms'   =>0,
            'group_selected' => [],
            'customer_ids'   => [],
            'place_ids'   => [],
        ];

        $params['notify'] = array_merge($notify, $request->input('notify', []));
        unset($params['poll_option_ids']);
        $post->fill($params);
        DB::transaction(function () use ($post, $pollOptions) {
            $post->save();
            foreach ($pollOptions as $pollOption) {
                $pollOption->post_id = $post->id;
                $pollOption->save();
            }
        });

        // url alias
        if (empty($post->alias)) {
            $slug = str_slug($post->title);
        } else {
            $slug = $request->alias;
        }

        $data_list = array();$data_list_search = array();$data_list_manager = array();
        $email_cus = array(); $email_manager =array(); $data_list_email =array();
        if(((isset($request['notify']['send_app']) && $request['notify']['send_app'] == 1) || (isset($request['notify']['send_mail']) && $request['notify']['send_mail'] == 1)) && $request['notify']['is_sent'] == 0 && $request['status'] == 1){

            
            if($request['private'] == 1){
                
                if(isset($request['notify']['place_selected']) && ['notify']['place_selected'] == 1){
                    if(!empty($request['notify']['place_ids'])){
                        $building_id = $this->building_active_id;
                        $list_apt = Apartments::whereIn('building_place_id',$request['notify']['place_ids'])->where('building_id',$this->building_active_id)->select('id')->distinct()->get()->toArray();
                        $data_apt = array_map(function($item){ return $item['id']; }, $list_apt);
                        $list_cus = Customers::whereIn('bdc_apartment_id',$data_apt)->whereHas('bdcApartment', function ($query) use ($building_id) {$query->where('building_id', '=', $building_id);})->whereHas('pubUserProfile', function ($query) use ($building_id) {$query->where('bdc_building_id', '=', $building_id);$query->where('type', 1);$query->where('type_profile',0);$query->whereNotIn('id',UserInfo::select('id')->where('bdc_building_id',$building_id)->where('type',1)->where('type_profile',0)->where('config_fcm','like','%POST%'));})->select('pub_user_profile_id')->distinct()->get()->toArray();
                        $list_pub = UserInfo::whereIn('id',$list_cus)->select(['pub_user_id', 'email','display_name'])->distinct()->get()->toArray();
                        $data_list_search = array_map(function($item){ return $item['pub_user_id']; }, $list_pub);

                        $data_list = array_merge($data_list,$data_list_search);

                        $data_list_email = array_map(function($item){ if ($item['email']) {
                           return $item;
                        } }, $list_pub);
                        $email_cus =  array_merge($email_cus,$data_list_email);
                    }

                }
                if(isset($request['notify']['customer_selected']) && $request['notify']['customer_selected'] == 1){
                    if(!empty($request['notify']['customer_ids'])){
                        $list_cus = UserInfo::whereIn('id',$request['notify']['customer_ids'])->where('bdc_building_id',$this->building_active_id)->where('type',1)->where('type_profile',0)->whereNotIn('id',UserInfo::select('id')->where('bdc_building_id',$this->building_active_id)->where('type',1)->where('type_profile',0)->where('config_fcm','like','%POST%'))->select(['pub_user_id', 'email','display_name'])->distinct()->get()->toArray();
                        $data_list_search = array_map(function($item){ return $item['pub_user_id']; }, $list_cus);
                        $data_list = array_merge($data_list,$data_list_search);
                        $data_list_email = array_map(function($item){ if ($item['email']) {
                           return $item;
                        } }, $list_cus);
                        $email_cus =  array_merge($email_cus,$data_list_email);
                    }

                }
                if(isset($request['notify']['group_selected']) && $request['notify']['group_selected'] == 1){
                    $building_id = $this->building_active_id;
                    if(!empty($request['notify']['group_ids'])){


                        $list_cus = Customers::whereIn('bdc_apartment_id',$request['notify']['group_ids'])->whereHas('bdcApartment', function ($query) use ($building_id) {$query->where('building_id', '=', $building_id);})->whereHas('pubUserProfile', function ($query) use ($building_id) {$query->where('bdc_building_id', '=', $building_id);$query->where('type', 1);$query->where('type_profile',0);$query->whereNotIn('id',UserInfo::select('id')->where('bdc_building_id',$building_id)->where('type',1)->where('type_profile',0)->where('config_fcm','like','%POST%'));})->select('pub_user_profile_id')->distinct()->get()->toArray();
                        $list_pub = UserInfo::whereIn('id',$list_cus)->select(['pub_user_id', 'email','display_name'])->distinct()->get()->toArray();
                        $data_list_search = array_map(function($item){ return $item['pub_user_id']; }, $list_pub);
                        $data_list = array_merge($data_list,$data_list_search);
                        $data_list_email = array_map(function($item){ if ($item['email']) {
                           return $item;
                        } }, $list_pub);
                        $email_cus =  array_merge($email_cus,$data_list_email);
                    }

                }
                if(isset($request['notify']['floor_selected']) && $request['notify']['floor_selected'] == 1){
                    if(!empty($request['notify']['floor_ids'])){
                        $building_id = $this->building_active_id;
                        $list_apt = Apartments::whereIn('floor',$request['notify']['floor_ids'])->where('building_id',$this->building_active_id)->select('id')->distinct()->get()->toArray();
                        $data_apt = array_map(function($item){ return $item['id']; }, $list_apt);

                        $list_cus = Customers::whereIn('bdc_apartment_id',$data_apt)->whereHas('bdcApartment', function ($query) use ($building_id) {$query->where('building_id', '=', $building_id);})->whereHas('pubUserProfile', function ($query) use ($building_id) {$query->where('bdc_building_id', '=', $building_id);$query->where('type', 1);$query->where('type_profile',0);$query->whereNotIn('id',UserInfo::select('id')->where('bdc_building_id',$building_id)->where('type',1)->where('type_profile',0)->where('config_fcm','like','%POST%'));})->select('pub_user_profile_id')->distinct()->get()->toArray();
                        $list_pub = UserInfo::whereIn('id',$list_cus)->select(['pub_user_id', 'email','display_name'])->distinct()->get()->toArray();
                        $data_list_search = array_map(function($item){ return $item['pub_user_id']; }, $list_pub);

                        $data_list = array_merge($data_list,$data_list_search);
                        $data_list_email = array_map(function($item){ if ($item['email']) {
                           return $item;
                        } }, $list_pub);
                        $email_cus =  array_merge($email_cus,$data_list_email);
                    }

                }
                if(isset($request['notify']['all_selected']) && $request['notify']['all_selected'] == 1){
                    $list_cus= UserInfo::select(['pub_user_id', 'email','display_name'])->where('bdc_building_id',$this->building_active_id)->where('type',1)->where('type_profile',0)->whereNotIn('pub_user_id',UserInfo::select('pub_user_id')->where('bdc_building_id',$this->building_active_id)->where('type',1)->where('type_profile',0)->where('config_fcm','like','%POST%'))->distinct()->get()->toArray();
                    $data_list_search = array_map(function($item){ return $item['pub_user_id']; }, $list_cus);
                    $data_list = array_merge($data_list,$data_list_search);

                    $data_list_email = array_map(function($item){ if ($item['email']) {
                           return $item;
                        } }, $list_cus);
                        $email_cus =  array_merge($email_cus,$data_list_email);

                }


            }else{
                $list_cus= UserInfo::select(['pub_user_id', 'email','display_name'])->where('bdc_building_id',$this->building_active_id)->where('type',1)->where('type_profile',0)->whereNotIn('pub_user_id',UserInfo::select('pub_user_id')->where('bdc_building_id',$this->building_active_id)->where('type',1)->where('type_profile',0)->where('config_fcm','like','%POST%'))->distinct()->get()->toArray();
                $data_list_search = array_map(function($item){ return $item['pub_user_id']; }, $list_cus);
                $data_list = array_merge($data_list,$data_list_search);
                $data_list_email = array_map(function($item){ if ($item['email']) {
                           return $item;
                        } }, $list_cus);
                        $email_cus =  array_merge($email_cus,$data_list_email);
            }
            $list_cus= UserInfo::select(['pub_user_id', 'email','display_name'])->where('bdc_building_id',$this->building_active_id)->where('type',2)->where('type_profile',0)->whereNotIn('pub_user_id',UserInfo::select('pub_user_id')->where('bdc_building_id',$this->building_active_id)->where('type',2)->where('type_profile',0)->where('config_fcm','like','%POST%'))->distinct()->get()->toArray();
            $data_list_manager = array_map(function($item){ return $item['pub_user_id']; }, $list_cus);

            $data_list_manager = array_map(function($item){ if ($item['email']) {
               return $item;
            } }, $list_cus);
        }

        $data_list = array_unique($data_list);
        if ($slug) {
            $data_noti = [
                "message" => $request->title,
                "building_id"=> $this->building_active_id,
                "title"=> $request->title,
                "action_name"=> self::POST_NEW,
                "image"=> $request->image,
            ];
            if ($post->type == 'event') {
                $uri = 'events/' . $post->id;
                $data_noti+=[
                    'type'=>SendNotifyFCMService::NEW_POST_EVENT,
                    'screen' => "EventSingle",
                    "id" => $post->id
                ];
            } elseif ($post->type == 'voucher') {
                $uri = 'vouchers/' . $post->id;
                $data_noti+=['type'=>SendNotifyFCMService::NEW_POST_VOUCHER,
                    'screen' =>"VoucherSingle",
                    "id" => $post->id
                ];
            } else {
                $uri = 'posts/' . $post->id;
                $data_noti+=['type'=>SendNotifyFCMService::NEW_POST_ARTICLE,
                    'screen' =>"PostSingle",
                    "id" => $post->id
                ];
            }

            $data_list_sms = [];$data_list_sms_manager = [];
            if(isset($request['notify']['send_sms']) && $request['notify']['send_sms'] == 1){
                if($request['private'] == 1){
                    //send place
                    if(isset($request['notify']['place_selected']) && $request['notify']['place_selected'] == 1){
                        if(!empty($request['notify']['place_ids'])){
                            $building_id = $this->building_active_id;
                            $list_apt = Apartments::whereIn('building_place_id',$request['notify']['place_ids'])->where('building_id',$this->building_active_id)->select('id')->distinct()->get()->toArray();
                            $data_apt = array_map(function($item){ return $item['id']; }, $list_apt);
                            $list_cus = Customers::whereIn('bdc_apartment_id',$data_apt)->whereHas('bdcApartment', function ($query) use ($building_id) {$query->where('building_id', '=', $building_id);})->whereHas('pubUserProfile', function ($query) use ($building_id) {$query->where('bdc_building_id', '=', $building_id);$query->where('type', 1);$query->where('type_profile',0);$query->whereNotIn('id',UserInfo::select('id')->where('bdc_building_id',$building_id)->where('type',1)->where('type_profile',0)->where('config_fcm','like','%POST%'));})->select('pub_user_profile_id')->distinct()->get()->toArray();
                            $list_pub = UserInfo::whereIn('id',$list_cus)->whereRaw('phone IS NOT NULL')->select('phone')->distinct()->get()->toArray();
                            $data_list_search = array_map(function($item){ return $item['phone']; }, $list_pub);
                            $data_list_sms = array_merge($data_list,$data_list_search);
                        }

                    }
                    //send customer
                    if(isset($request['notify']['customer_selected']) && $request['notify']['customer_selected'] == 1){
                        if(!empty($request['notify']['customer_ids'])){
                            $list_cus = UserInfo::whereIn('id',$request['notify']['customer_ids'])->where('bdc_building_id',$this->building_active_id)->where('type',1)->where('type_profile',0)->whereNotIn('id',UserInfo::select('id')->where('bdc_building_id',$this->building_active_id)->where('type',1)->where('type_profile',0)->where('config_fcm','like','%POST%'))->whereRaw('phone IS NOT NULL')->select('phone')->distinct()->get()->toArray();
                            $data_list_search = array_map(function($item){ return $item['phone']; }, $list_cus);
                            $data_list_sms = array_merge($data_list,$data_list_search);
                        }
                    }
                    //send apartment
                    if(isset($request['notify']['group_selected']) && $request['notify']['group_selected'] == 1){
                        $building_id = $this->building_active_id;
                        if(!empty($request['notify']['group_ids'])){
                            $list_cus = Customers::whereIn('bdc_apartment_id',$request['notify']['group_ids'])->whereHas('bdcApartment', function ($query) use ($building_id) {$query->where('building_id', '=', $building_id);})->whereHas('pubUserProfile', function ($query) use ($building_id) {$query->where('bdc_building_id', '=', $building_id);$query->where('type', 1);$query->where('type_profile',0);$query->whereNotIn('id',UserInfo::select('id')->where('bdc_building_id',$building_id)->where('type',1)->where('type_profile',0)->where('config_fcm','like','%POST%'));})->select('pub_user_profile_id')->distinct()->get()->toArray();
                            $list_pub = UserInfo::whereIn('id',$list_cus)->whereRaw('phone IS NOT NULL')->select('phone')->distinct()->get()->toArray();
                            $data_list_search = array_map(function($item){ return $item['phone']; }, $list_pub);
                            $data_list_sms = array_merge($data_list,$data_list_search);
                        }

                    }
                    //send floor
                    if(isset($request['notify']['floor_selected']) && $request['notify']['floor_selected'] == 1){
                        if(!empty($request['notify']['floor_ids'])){
                            $building_id = $this->building_active_id;
                            $list_apt = Apartments::whereIn('floor',$request['notify']['floor_ids'])->where('building_id',$this->building_active_id)->select('id')->distinct()->get()->toArray();
                            $data_apt = array_map(function($item){ return $item['id']; }, $list_apt);

                            $list_cus = Customers::whereIn('bdc_apartment_id',$data_apt)->whereHas('bdcApartment', function ($query) use ($building_id) {$query->where('building_id', '=', $building_id);})->whereHas('pubUserProfile', function ($query) use ($building_id) {$query->where('bdc_building_id', '=', $building_id);$query->where('type', 1);$query->where('type_profile',0);$query->whereNotIn('id',UserInfo::select('id')->where('bdc_building_id',$building_id)->where('type',1)->where('type_profile',0)->where('config_fcm','like','%POST%'));})->select('pub_user_profile_id')->distinct()->get()->toArray();
                            $list_pub = UserInfo::whereIn('id',$list_cus)->whereRaw('phone IS NOT NULL')->select('phone')->distinct()->get()->toArray();
                            $data_list_search = array_map(function($item){ return $item['phone']; }, $list_pub);

                            $data_list_sms = array_merge($data_list,$data_list_search);
                        }

                    }
                    //send all
                    if(isset($request['notify']['all_selected']) && $request['notify']['all_selected'] == 1){
                        $list_cus= UserInfo::select('phone')->where('bdc_building_id',$this->building_active_id)->where('type',1)->where('type_profile',0)->whereNotIn('pub_user_id',UserInfo::select('pub_user_id')->where('bdc_building_id',$this->building_active_id)->where('type',1)->where('type_profile',0)->where('config_fcm','like','%POST%'))->whereRaw('phone IS NOT NULL')->distinct()->get()->toArray();
                        $data_list_search = array_map(function($item){ return $item['phone']; }, $list_cus);
                        $data_list_sms = array_merge($data_list,$data_list_search);

                    }
                }
                $list_cus= UserInfo::select('phone')->where('bdc_building_id',$this->building_active_id)->where('type',2)->where('type_profile',0)->whereNotIn('pub_user_id',UserInfo::select('pub_user_id')->where('bdc_building_id',$this->building_active_id)->where('type',2)->where('type_profile',0)->where('config_fcm','like','%POST%'))->whereRaw('phone IS NOT NULL')->distinct()->get()->toArray();
                $data_list_search = array_map(function($item){ return $item['phone']; }, $list_cus);
                $data_list_sms_manager = $data_list_search;
            }
            
            $url          = UrlAlias::saveAlias($uri, $slug);
            $post->url_id = $url->id;
            $post->alias  = $url->alias;
            $update_noti = $post->notify;

            $total = ['email'=>0, 'app'=> 0, 'sms'=> 0];
            if(isset($request['notify']['send_app']) && $request['notify']['send_app'] == 1 && $request['status'] == 1 && $request['notify']['is_sent'] == 0) {
                $countTokent = (int)Fcm::getCountTokenbyUserId($data_list) + (int)Fcm::getCountTokenbyUserId($data_list_manager);     
                $total['app'] = $countTokent;
                if ($total['app']>0)
                    $campain = Campain::updateOrCreateCampain($post->title, config('typeCampain.POST_NEWS'), $post->id, $total, $this->building_active_id, 0, 0);

                foreach ($data_list as $cudan) {
                    SendNotifyFCMService::setItemForQueueNotify(array_merge($data_noti, ['user_id' => $cudan, 'app_config' => 'cudan', 'campain_id' => $campain->id]));
                }
                foreach ($data_list_manager as $banquanly) {
                    SendNotifyFCMService::setItemForQueueNotify(array_merge($data_noti, ['user_id' => $banquanly, 'app_config' => 'banquanly', 'campain_id' => $campain->id]));
                }
                $update_noti['is_sent'] = 1;
            }
            
            if(isset($request['notify']['send_mail']) && $request['notify']['send_mail'] == 1 && $request['status'] == 1 && $request['notify']['is_sent'] == 0) {
                $unique_email_cus = array_map("unserialize", array_unique(array_map("serialize", $email_cus)));
                $total['email'] = sizeof($unique_email_cus);
                if ($total['email']>0){
                    $campain = Campain::updateOrCreateCampain($post->title, config('typeCampain.POST_NEWS'), $post->id, $total, $this->building_active_id, 0, 0, @$campain->id);
                     
                }
                foreach ($unique_email_cus as $cudan) {
                    // ServiceSendMail::setItemForQueue( ['user_id' => $cudan, 'message' => 'Có một thông báo mới được gửi đến bạn vui lòng đăng nhập app BuildingCare để xem chi tiết']);
                    // echo "=======";
                    // print_r($cudan['email']);
                    ServiceSendMailV2::setItemForQueue( [
                        'params' => [
                          '@ten' => $cudan['display_name'],
                          '@url'=> ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == "on") ? "https" : "http"). "://". @$_SERVER['HTTP_HOST']
                        ],
                        'cc' =>$cudan['email'],
                        'building_id' => $this->building_active_id,
                        'type' => ServiceSendMailV2::POST_NEWS,
                        'status' => 'prepare',
                        'subject' => '[BuildingCare] thông báo mới đến cư dân.',
                        'content'=> $request->content,
                        'campain_id' => $campain->id,
                    ]);
                }
                // foreach ($data_list_email as $banquanly) {
                //     // ServiceSendMail::setItemForQueue( ['user_id' => $banquanly, 'message' => 'Có một thông báo mới được gửi đến bạn vui lòng đăng nhập app BuildingCare dành cho ban quản lý để xem chi tiết']);
                //     ServiceSendMail::setItemForQueue( [
                //         'params' => [
                //           '@ten' => $cudan['display_name']
                //         ],
                //         'cc' =>$cudan['email'],
                //         'building_id' => $this->building_active_id,
                //         'type' => ServiceSendMail::POST_NEWS,
                //         'status' => 'prepare',
                //         'subject' => '[BuildingCare] thông báo mới đến ban quản lý.',
                //         'content'=> $request->content
                //     ]);
                // }
                $update_noti['is_sent'] = 1;
            }
            
            if(isset($request['notify']['send_sms']) && isset($request['notify']['is_sent_sms']) && $request['notify']['send_sms'] == 1  && $request['notify']['is_sent_sms'] == 0 && $request['status'] == 1){
                // if(!empty($data_list_sms)){
                //     foreach ($data_list_sms as $cudan){
                //         SendSMSSoap::setItemForQueue([
                //             'content'=>'Ban co thong bao moi tu Ban Quan Ly toa nha',
                //             'building_id'=>$this->building_active_id,
                //             'type'=>'POST',
                //             'target'=>$cudan
                //         ]);
                //     }
                // }
                // if(!empty($data_list_sms_manager)){
                //     foreach ($data_list_sms_manager as $banquanly){
                //         SendSMSSoap::setItemForQueue([
                //             'content'=>'Ban co thong bao moi tu Ban Quan Ly toa nha',
                //             'building_id'=>$this->building_active_id,
                //             'type'=>'POST',
                //             'target'=>$banquanly
                //         ]);
                //     }
                // }
                $update_noti['is_sent_sms'] = 1;
            }
            $post->notify = $update_noti;
            $post->save();
        }

        $message = [
            'error'  => 0,
            'status' => 'success',
            'msg'    => 'Đã cập nhập thông tin',
        ];
        if($type == 'event'){
            return redirect()->route('admin.posts.index_event', ['type' => $type])->with('message', $message);
        }
        return redirect()->route('admin.posts.index', ['type' => $type])->with('message', $message);
    }

    /**
     * Get customers by name
     *
     * @return \Illuminate\Http\Response
     */
    public function ajaxCustomers(Request $request)
    {

        $keyword = $request->input('keyword', '');

        $where = [];

        if ($keyword) {
            $where[] = ['display_name', 'like', '%' . $keyword . '%'];
        }
        $where[] = ['type',1];
        $where[] = ['type_profile',0];
        $where[] = ['bdc_building_id',$this->building_active_id];
        $select = ['id', 'display_name'];

        $customers = UserInfo::where($where)->select($select)->paginate(10);
//        dd($customers->toArray());
        return response()->json($customers);
    }
    public function ajaxApartment(Request $request)
    {
        $keyword = $request->input('keyword', '');

        $where = [];

        if ($keyword) {
            $where[] = ['name', 'like', '%' . $keyword . '%'];
        }
        $where[] = ['building_id',$this->building_active_id];
        $select = ['id', 'name'];

        $customers = Apartments::where($where)->select($select)->paginate(10);

        return response()->json($customers);
    }

    /**
     * Get posts by title
     *
     * @return \Illuminate\Http\Response
     */
    public function ajaxPosts(Request $request)
    {
        $keyword = $request->input('keyword', '');

        $where = [];

        if ($keyword) {
            $where[] = ['title', 'like', '%' . $keyword . '%'];
        }

        $select = ['id', 'title'];

        $posts = Post::where($where)->select($select)->paginate(10);

        return response()->json($posts);
    }
    public function ajaxBuildingPlace(Request $request)
    {
        $keyword = $request->input('keyword', '');

        $where = [];

        if ($keyword) {
            $where[] = ['title', 'like', '%' . $keyword . '%'];
        }

        $select = ['id', 'name'];

        $posts = BuildingPlace::where($where)->where('bdc_building_id',$this->building_active_id)->select($select)->paginate(10);

        return response()->json($posts);
    }

    public function attributes()
    {
        return [
            'poll_options' => 'Câu hỏi bình chọn',
            'title'        => 'Nội dung câu hỏi bình chọn',
        ];
    }

    public function addPollOption(Request $request)
    {
        $rules = [
            'poll_options' => 'required',
        ];

        $validator = Validator::make($request->all(), $rules, [], $this->attributes());
        $errors    = $validator->messages();

        $option_id = $request->input('poll_options', 0);
        $post_id   = $request->input('post_id', 0);
        $post = Post::findOrFail($post_id);

        $poll_options    = $post->poll_options ?: [];
        $poll_option_ids = $request->poll_ids ?: [];
        if ($poll_option_ids) {
            $poll_option_ids = explode(',', $poll_option_ids);
        }

        $poll_options = array_merge($poll_options, $poll_option_ids);

        $option = PollOption::find($option_id);
        if (!$option && $option_id) {
            $errors->add('poll_options', 'Câu hỏi không tồn tại');
        }
        // Kiểm tra xem câu hỏi đã có chưa?
        if (in_array($option_id, $poll_options)) {

            $errors->add('poll_options', 'Câu hỏi đã có trong danh sách.');

        }
        if ($errors->toArray()) {
            return response()->json(['errors' => $errors]);
        }

        if (!$request->has('_validate')) {

            return view('backend.posts.sub-views.option', ['poll_option' => $option]);
        }
    }

    public function savePollOption(Request $request)
    {
        $poll_option = new PollOption();
        $rules = [
            'title' => 'required',
        ];

        $validator = Validator::make($request->all(), $rules, [], $this->attributes());
        $errors    = $validator->messages();

        $post_id = $request->post_id;

        if ($errors->toArray()) {
            return response()->json(['errors' => $errors]);
        }

        if (!$request->has('_validate')) {
            $input = $request->all();

            // Xử lý câu hỏi thăm dò (poll_options)
            if (isset($input['options'])) {
                foreach ($input['options'] as $index => $item) {
                    if (!$item) {
                        unset($input['options'][$index]);
                    }
                }
            }

            if (!isset($input['options']) || !$input['options']) {
                $errors->add('options', 'Cần ít nhất 1 câu trả lời cho câu hỏi');
                return response()->json(['errors' => $errors]);
            }

            $poll_options = $input['options'];
            $polls        = [];

            if ($poll_options) {
                foreach ($poll_options as $key => $value) {
                    $k         = "poll_" . $key;
                    $polls[$k] = $value;
                }
                $input['options'] = $polls;
            }
            $user  = \Auth::user();
            $param = [
                'user_id'   => $user->id,
                'user_type' => $user->getTable() == 'user_partners' ? 'partner' : 'user',
            ];

            $param = array_merge($input, $param);
            if (isset($param['post_id'])) {
                unset($param['post_id']);
            }

            $poll_option->fill($param)->save();

            $options = isset($poll_option->options) && is_array($poll_option->options) ? $poll_option->options : [];

            $html = '<div class="panel panel-primary"> <div class="panel-heading"> <h4 class="panel-title">';
            $html .= '<a data-toggle="collapse" href="#poll-option-' . $poll_option->id . '">' . $poll_option->updated_at->format("d-m-Y H:i:s") . '</a>';
            $html .= '<a href="' . route('admin.polloptions.edit', ['id' => $poll_option->id]) . '" class="pull-right" title="Sửa câu hỏi" style="font-size: 20px; margin-left: 7px;"><i class="fa      fa-edit"></i></a></h4></div>';
            $html .= '<div id="poll-option-' . $poll_option->id . '" class="panel-collapse collapse in">';
            $html .= '<div class="form-horizontal"> <div class="panel-body"> <div class="form-group"> <label class="col-sm-2 control-label" style="padding-top: 0px;">Câu hỏi</label>';
            $html .= '<div class="col-sm-10">' . $poll_option->title . '</div> </div>';

            $html .= '<div class="form-group"> <label class="col-sm-2 control-label" style="padding-top: 0px;">Câu trả lời</label> <div class="col-sm-10"> <ol>';
            foreach ($options as $item) {
                $html .= '<li>' . $item . '</li>';
            }
            $html .= '</ol> </div> </div> </div> </div> </div> </div>';

            return response()->json([
                'view' => $html,
                'id'   => $poll_option->id,
            ]);
        }
    }

    public function deletePollOption(Request $request)
    {
        $id   = $request->id ?: 0;
        $post = Post::findOrFail($id);

        $poll_id    = $request->poll_id ?: 0;
        $pollOption = PollOption::findOrFail($poll_id);

        $poll_options = $post->poll_options;
        foreach ($poll_options as $key => $poll_option) {
            if ($poll_option == $poll_id) {
                unset($poll_options[$key]);
            }
        }
        DB::transaction(function () use ($post, $pollOption, $poll_options) {
            $post->poll_options = $poll_options;
            $post->save();

            $pollOption->post_id = null;
            $pollOption->save();
        });

        return redirect(url('/admin/posts/' . $id . '/edit?type=event#poll_options'))->with('success', 'Xóa câu hỏi thành công.');
    }
    public function action(Request $request){
        return $this->modelPost->action($request);
    }
    public function changeStatus(Request $request)
    {
        $service = $this->modelPost->getPostById($request->id);
        $notify = json_decode($service->notify,true);
        $data_list = [];$data_noti = [];$data_list_manager = [];
        if(isset($notify['send_app']) && $notify['send_app'] == 1 && $notify['is_sent'] == 0){
            if($service->private == 1){
                if($notify['place_selected'] && $notify['place_selected'] == 1){
                    if(!empty($notify['place_ids'])){
                        $building_id = $this->building_active_id;
                        $list_apt = Apartments::whereIn('building_place_id',$notify['place_ids'])->where('building_id',$this->building_active_id)->select('id')->distinct()->get()->toArray();
                        $data_apt = array_map(function($item){ return $item['id']; }, $list_apt);
                        $list_cus = Customers::whereIn('bdc_apartment_id',$data_apt)->whereHas('bdcApartment', function ($query) use ($building_id) {$query->where('building_id', '=', $building_id);})->whereHas('pubUserProfile', function ($query) use ($building_id) {$query->where('bdc_building_id', '=', $building_id);$query->where('type', 1);$query->where('type_profile',0);$query->whereNotIn('id',UserInfo::select('id')->where('bdc_building_id',$building_id)->where('type',1)->where('type_profile',0)->where('config_fcm','like','%POST%'));})->select('pub_user_profile_id')->distinct()->get()->toArray();
                        $list_pub = UserInfo::whereIn('id',$list_cus)->select('pub_user_id')->distinct()->get()->toArray();
                        $data_list_search = array_map(function($item){ return $item['pub_user_id']; }, $list_pub);
                        $data_list = array_merge($data_list,$data_list_search);
                    }

                }
                if($notify['customer_selected'] && $notify['customer_selected'] == 1){
                    if(!empty($notify['customer_ids'])){
                        $list_cus = UserInfo::whereIn('id',$notify['customer_ids'])->where('bdc_building_id',$this->building_active_id)->where('type',1)->where('type_profile',0)->whereNotIn('id',UserInfo::select('id')->where('bdc_building_id',$this->building_active_id)->where('type',1)->where('type_profile',0)->where('config_fcm','like','%POST%'))->select('pub_user_id')->distinct()->get()->toArray();
                        $data_list_search = array_map(function($item){ return $item['pub_user_id']; }, $list_cus);
                        $data_list = array_merge($data_list,$data_list_search);
                    }

                }
                if($notify['group_selected'] && $notify['group_selected'] == 1){
                    $building_id = $this->building_active_id;
                    if(!empty($notify['group_ids'])){
                        $list_cus = Customers::whereIn('bdc_apartment_id',$notify['group_ids'])->whereHas('bdcApartment', function ($query) use ($building_id) {$query->where('building_id', '=', $building_id);})->whereHas('pubUserProfile', function ($query) use ($building_id) {$query->where('bdc_building_id', '=', $building_id);$query->where('type', 1);$query->where('type_profile',0);$query->whereNotIn('id',UserInfo::select('id')->where('bdc_building_id',$building_id)->where('type',1)->where('type_profile',0)->where('config_fcm','like','%POST%'));})->select('pub_user_profile_id')->distinct()->get()->toArray();
                        $list_pub = UserInfo::whereIn('id',$list_cus)->select('pub_user_id')->distinct()->get()->toArray();
                        $data_list_search = array_map(function($item){ return $item['pub_user_id']; }, $list_pub);
//                        $data_list_search = array_map(function($item){ return $item['pub_user_profile_id']; }, $list_cus);
                        $data_list = array_merge($data_list,$data_list_search);
//                        $data_list = array_map(function($item){ return $item['id']; }, $list_cus);
                    }

                }
                if($notify['floor_selected'] && $notify['floor_selected'] == 1){
                    if(!empty($notify['floor_ids'])){
                        $building_id = $this->building_active_id;
                        $list_apt = Apartments::whereIn('floor',$notify['floor_ids'])->where('building_id',$this->building_active_id)->select('id')->distinct()->get()->toArray();
                        $data_apt = array_map(function($item){ return $item['id']; }, $list_apt);

                        $list_cus = Customers::whereIn('bdc_apartment_id',$data_apt)->whereHas('bdcApartment', function ($query) use ($building_id) {$query->where('building_id', '=', $building_id);})->whereHas('pubUserProfile', function ($query) use ($building_id) {$query->where('bdc_building_id', '=', $building_id);$query->where('type', 1);$query->where('type_profile',0);$query->whereNotIn('id',UserInfo::select('id')->where('bdc_building_id',$building_id)->where('type',1)->where('type_profile',0)->where('config_fcm','like','%POST%'));})->select('pub_user_profile_id')->distinct()->get()->toArray();
                        $list_pub = UserInfo::whereIn('id',$list_cus)->select('pub_user_id')->distinct()->get()->toArray();
                        $data_list_search = array_map(function($item){ return $item['pub_user_id']; }, $list_pub);
                        // $data_list_search = array_map(function($item){ return $item['pub_user_profile_id']; }, $list_cus);

                        $data_list = array_merge($data_list,$data_list_search);
                        // $data_list = array_map(function($item){ return $item['id']; }, $list_cus);
                    }

                }
                if($notify['all_selected'] && $notify['all_selected'] == 1){
                    $list_cus= UserInfo::select('pub_user_id')->where('bdc_building_id',$this->building_active_id)->where('type',1)->where('type_profile',0)->whereNotIn('pub_user_id',UserInfo::select('pub_user_id')->where('bdc_building_id',$this->building_active_id)->where('type',1)->where('type_profile',0)->where('config_fcm','like','%POST%'))->distinct()->get()->toArray();
                    $data_list_search = array_map(function($item){ return $item['pub_user_id']; }, $list_cus);
                    $data_list = array_merge($data_list,$data_list_search);

                }

                // else{
                //     $list_cus= UserInfo::select('id')->get()->toArray();
                //     $data_list = array_map(function($item){ return $item['id']; }, $list_cus);
                // }

            }else{
                $list_cus= UserInfo::select('pub_user_id')->where('bdc_building_id',$this->building_active_id)->where('type',1)->where('type_profile',0)->whereNotIn('pub_user_id',UserInfo::select('pub_user_id')->where('bdc_building_id',$this->building_active_id)->where('type',1)->where('type_profile',0)->where('config_fcm','like','%POST%'))->distinct()->get()->toArray();
                $data_list_search = array_map(function($item){ return $item['pub_user_id']; }, $list_cus);
                $data_list = array_merge($data_list,$data_list_search);
            }
            $list_cus= UserInfo::select('pub_user_id')->where('bdc_building_id',$this->building_active_id)->where('type',2)->where('type_profile',0)->whereNotIn('pub_user_id',UserInfo::select('pub_user_id')->where('bdc_building_id',$this->building_active_id)->where('type',2)->where('type_profile',0)->where('config_fcm','like','%POST%'))->distinct()->get()->toArray();
            $data_list_search = array_map(function($item){ return $item['pub_user_id']; }, $list_cus);
            // $data_list_manager = array_merge($data_list,$data_list_search);
            $data_list_manager = $data_list_search;

        }
        $data_list = array_unique($data_list);

        $data_noti = [
            "message" => $request->title,
            // "user_id"=> $data_list,
            // "manager_id"=> $data_list_manager,
            "building_id"=> $this->building_active_id,
            "title"=> $request->title,
            "action_name"=> self::POST_NEW,
            "image"=> $request->image,
        ];
        if ($service->type == 'event') {
            $uri = 'events/' . $service->id;
            $data_noti+=[
                'type'=>SendNotifyFCMService::NEW_POST_EVENT,
                'screen' => "EventSingle",
                "id" => $service->id
            ];
        } elseif ($service->type == 'voucher') {
            $uri = 'vouchers/' . $service->id;
            $data_noti+=['type'=>SendNotifyFCMService::NEW_POST_VOUCHER,
                'screen' =>"VoucherSingle",
                "id" => $service->id
            ];
        } else {
            $uri = 'posts/' . $service->id;
            $data_noti+=['type'=>SendNotifyFCMService::NEW_POST_ARTICLE,
                'screen' =>"PostSingle",
                "id" => $service->id
            ];
        }



        $data_list_sms = [];$data_list_sms_manager = [];
        if(isset($notify['send_sms']) && $notify['send_sms'] == 1){
            if($service->private == 1){
                //send place
                if($notify['place_selected'] && $notify['place_selected'] == 1){
                    if(!empty($notify['place_ids'])){
                        $building_id = $this->building_active_id;
                        $list_apt = Apartments::whereIn('building_place_id',$notify['place_ids'])->where('building_id',$this->building_active_id)->select('id')->distinct()->get()->toArray();
                        $data_apt = array_map(function($item){ return $item['id']; }, $list_apt);
                        $list_cus = Customers::whereIn('bdc_apartment_id',$data_apt)->whereHas('bdcApartment', function ($query) use ($building_id) {$query->where('building_id', '=', $building_id);})->whereHas('pubUserProfile', function ($query) use ($building_id) {$query->where('bdc_building_id', '=', $building_id);$query->where('type', 1);$query->where('type_profile',0);$query->whereNotIn('id',UserInfo::select('id')->where('bdc_building_id',$building_id)->where('type',1)->where('type_profile',0)->where('config_fcm','like','%POST%'));})->select('pub_user_profile_id')->distinct()->get()->toArray();
                        $list_pub = UserInfo::whereIn('id',$list_cus)->whereRaw('phone IS NOT NULL')->select('phone')->distinct()->get()->toArray();
                        $data_list_search = array_map(function($item){ return $item['phone']; }, $list_pub);
                        $data_list_sms = array_merge($data_list,$data_list_search);
                    }

                }
                //send customer
                if($notify['customer_selected'] && $notify['customer_selected'] == 1){
                    if(!empty($notify['customer_ids'])){
                        $list_cus = UserInfo::whereIn('id',$notify['customer_ids'])->where('bdc_building_id',$this->building_active_id)->where('type',1)->where('type_profile',0)->whereNotIn('id',UserInfo::select('id')->where('bdc_building_id',$this->building_active_id)->where('type',1)->where('type_profile',0)->where('config_fcm','like','%POST%'))->whereRaw('phone IS NOT NULL')->select('phone')->distinct()->get()->toArray();
                        $data_list_search = array_map(function($item){ return $item['phone']; }, $list_cus);
                        $data_list_sms = array_merge($data_list,$data_list_search);
                    }
                }
                //send apartment
                if($notify['group_selected'] && $notify['group_selected'] == 1){
                    $building_id = $this->building_active_id;
                    if(!empty($notify['group_ids'])){
                        $list_cus = Customers::whereIn('bdc_apartment_id',$notify['group_ids'])->whereHas('bdcApartment', function ($query) use ($building_id) {$query->where('building_id', '=', $building_id);})->whereHas('pubUserProfile', function ($query) use ($building_id) {$query->where('bdc_building_id', '=', $building_id);$query->where('type', 1);$query->where('type_profile',0);$query->whereNotIn('id',UserInfo::select('id')->where('bdc_building_id',$building_id)->where('type',1)->where('type_profile',0)->where('config_fcm','like','%POST%'));})->select('pub_user_profile_id')->distinct()->get()->toArray();
                        $list_pub = UserInfo::whereIn('id',$list_cus)->whereRaw('phone IS NOT NULL')->select('phone')->distinct()->get()->toArray();
                        $data_list_search = array_map(function($item){ return $item['phone']; }, $list_pub);
                        // $data_list_search = array_map(function($item){ return $item['pub_user_profile_id']; }, $list_cus);
                        $data_list_sms = array_merge($data_list,$data_list_search);
                        // $data_list = array_map(function($item){ return $item['id']; }, $list_cus);
                    }

                }
                //send floor
                if($notify['floor_selected'] && $notify['floor_selected'] == 1){
                    if(!empty($notify['floor_ids'])){
                        $building_id = $this->building_active_id;
                        $list_apt = Apartments::whereIn('floor',$notify['floor_ids'])->where('building_id',$this->building_active_id)->select('id')->distinct()->get()->toArray();
                        $data_apt = array_map(function($item){ return $item['id']; }, $list_apt);

                        $list_cus = Customers::whereIn('bdc_apartment_id',$data_apt)->whereHas('bdcApartment', function ($query) use ($building_id) {$query->where('building_id', '=', $building_id);})->whereHas('pubUserProfile', function ($query) use ($building_id) {$query->where('bdc_building_id', '=', $building_id);$query->where('type', 1);$query->where('type_profile',0);$query->whereNotIn('id',UserInfo::select('id')->where('bdc_building_id',$building_id)->where('type',1)->where('type_profile',0)->where('config_fcm','like','%POST%'));})->select('pub_user_profile_id')->distinct()->get()->toArray();
                        $list_pub = UserInfo::whereIn('id',$list_cus)->whereRaw('phone IS NOT NULL')->select('phone')->distinct()->get()->toArray();
                        $data_list_search = array_map(function($item){ return $item['phone']; }, $list_pub);
                        // $data_list_search = array_map(function($item){ return $item['pub_user_profile_id']; }, $list_cus);

                        $data_list_sms = array_merge($data_list,$data_list_search);
                        // $data_list = array_map(function($item){ return $item['id']; }, $list_cus);
                    }

                }
                //send all
                if($notify['all_selected'] && $notify['all_selected'] == 1){
                    $list_cus= UserInfo::select('phone')->where('bdc_building_id',$this->building_active_id)->where('type',1)->where('type_profile',0)->whereNotIn('pub_user_id',UserInfo::select('pub_user_id')->where('bdc_building_id',$this->building_active_id)->where('type',1)->where('type_profile',0)->where('config_fcm','like','%POST%'))->whereRaw('phone IS NOT NULL')->distinct()->get()->toArray();
                    $data_list_search = array_map(function($item){ return $item['phone']; }, $list_cus);
                    $data_list_sms = array_merge($data_list,$data_list_search);

                }
            }
            $list_cus= UserInfo::select('phone')->where('bdc_building_id',$this->building_active_id)->where('type',2)->where('type_profile',0)->whereNotIn('pub_user_id',UserInfo::select('pub_user_id')->where('bdc_building_id',$this->building_active_id)->where('type',2)->where('type_profile',0)->where('config_fcm','like','%POST%'))->whereRaw('phone IS NOT NULL')->distinct()->get()->toArray();
            $data_list_search = array_map(function($item){ return $item['phone']; }, $list_cus);
            // $data_list_manager = array_merge($data_list,$data_list_search);
            $data_list_sms_manager = $data_list_search;
        }
        if(isset($notify['send_app']) && $notify['send_app'] == 1  && $notify['is_sent'] == 0){
            $countTokent = (int)Fcm::getCountTokenbyUserId($data_list) + (int)Fcm::getCountTokenbyUserId($data_list_manager);     
            $total = ['email'=>0, 'app'=> $countTokent, 'sms'=> 0];
            if($total["app"] > 0)
            $campain = Campain::updateOrCreateCampain($service->title, config('typeCampain.POST_NEWS'), $service->id, $total, $this->building_active_id, 0, 0);

            foreach ($data_list as $cudan){
                SendNotifyFCMService::setItemForQueueNotify(array_merge($data_noti,['user_id'=>$cudan,'app_config'=>'cudan', 'campain_id' => $campain->id]));
            }
            foreach ($data_list_manager as $banquanly){
                SendNotifyFCMService::setItemForQueueNotify(array_merge($data_noti,['user_id'=>$banquanly,'app_config'=>'banquanly', 'campain_id' => $campain->id]));
            }
            $this->modelPost->changeStatusPostNoti($request,$notify);
        }else if(isset($notify['send_sms']) && isset($notify['is_sent_sms']) && $notify['send_sms'] == 1  && $notify['is_sent_sms'] == 0){
//                SendNotifyFCMService::setItemForQueueNotify($data_noti);
            // if(!empty($data_list_sms)){
            //     foreach ($data_list_sms as $cudan){
            //         SendSMSSoap::setItemForQueue([
            //             'content'=>'Ban co thong bao moi tu Ban Quan Ly toa nha',
            //             'building_id'=>$this->building_active_id,
            //             'type'=>'POST',
            //             'target'=>$cudan
            //         ]);
            //     }
            // }
            // if(!empty($data_list_sms_manager)){
            //     foreach ($data_list_sms_manager as $banquanly){
            //         SendSMSSoap::setItemForQueue([
            //             'content'=>'Ban co thong bao moi tu Ban Quan Ly toa nha',
            //             'building_id'=>$this->building_active_id,
            //             'type'=>'POST',
            //             'target'=>$banquanly
            //         ]);
            //     }
            // }
            $this->modelPost->changeStatusPostSms($request,$notify);
        }else{
            $this->modelPost->changeStatusPost($request);
        }



      /*  if ($service->type == 2)
        {
            $dataResponse = [
                'error' => true,
                'message' => 'Không thể thay đổi trang thái bài viết này!'
            ];
            return response()->json($dataResponse);
        }*/

//        dd($data_list,$data_noti);
        $dataResponse = [
            'success' => true,
            'message' => 'Thay đổi trạng thái thành công!'
        ];
        return response()->json($dataResponse);
    }
    public function listComments(Request $request,CommentsRespository $comments)
    {
        $data['meta_title'] = 'Danh sách bình luận';
        $data['heading']    = 'Danh sách bình luận';
        $data['per_page'] = Cookie::get('per_page', 20);
        $data['lists'] = $comments->listCommentsByType(['event','article'],$this->building_active_id,$data['per_page']);
//        dd($data['lists']);
        return view('backend.posts.comments.index', $data);
    }
}
