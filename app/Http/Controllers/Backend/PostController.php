<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Backend\Controller;
use App\Http\Controllers\BuildingController;
use App\Http\Requests\PostRequest;
use App\Models\Apartments\Apartments;
use App\Models\Apartments\V2\UserApartments;
use App\Models\BoCustomer;
use App\Models\Building\Building;
use App\Models\Building\BuildingPlace;
use App\Models\Category;
use App\Models\Comment;
use App\Models\CustomerGroup;
use App\Models\Customers\Customers;
use App\Models\MailsStatus;
use App\Models\Partner;
use App\Models\PollOption;
use App\Models\Post;
use App\Models\PublicUser\UserInfo;
use App\Models\PublicUser\Users;
use App\Models\Campain;
use App\Models\Fcm\Fcm;
use App\Models\PublicUser\V2\User;
use App\Models\PublicUser\V2\UserInfo as V2UserInfo;
use App\Models\UrlAlias;
use App\Repositories\Comments\CommentsRespository;
use App\Repositories\Customers\CustomersRespository;
use App\Repositories\NotifyLog\NotifyLogRespository;
use App\Repositories\Posts\PostsRespository;
use App\Repositories\PublicUsers\PublicUsersProfileRespository;
use App\Services\FCM\SendNotifyFCMService;
use App\Services\FCM\V2\SendNotifyFCMService as V2SendNotifyFCMService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Validator;
use App\Services\SendSMSSoap;
use App\Services\ServiceSendMail;
use App\Services\ServiceSendMailV2;
use App\Util\Debug\Log;

class PostController extends BuildingController
{
    /**
     * Khởi tạo
     */
    const POST_NEW = "POST";
    const BILL_NEW = "BILL";

    private $modelPost;
    private $modelUserInfo;
    private $log;
    private $typeCampain;

    public function __construct(PostsRespository $modelPost,NotifyLogRespository $log,PublicUsersProfileRespository $modelUserInfo,Request $request)
    {
        $this->model = new Post();
        $this->modelPost = $modelPost;
        $this->modelUserInfo = $modelUserInfo;
        $this->log = $log;
        Carbon::setLocale('vi');
        parent::__construct($request);
        $this->typeCampain = config('typeCampain');
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
        $posts = $posts->whereNull('status_is_customer')->orderByRaw('id DESC')->paginate($data['per_page']);

        $posts->load('user','user.BDCprofile', 'category');

        $data['posts'] = $posts;

        // Danh mục
        $where   = [];
        $where[] = ['type', '=', $data['type']];

        $data['categories'] = Category::where($where)->get();

        $heading = [
            'article' => 'Thông tin',
            'event'   => 'Sự kiện',
            'voucher' => 'Khuyến mại',
        ];

        $data['heading']    = $heading[$data['type']];
        $data['meta_title'] = "QL {$data['heading']} > Bài viết";
        $data['now']        = Carbon::now();

        $data['advance'] = $advance;

        return view('backend.posts.index', $data);
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
            'article' => 'Thông tin',
            'event'   => 'Sự kiện',
            'voucher' => 'Khuyến mại',
        ];

        $data['heading']    = $heading[$data['type']];
        $data['meta_title'] = "QL {$data['heading']} > Bài viết";
        $data['now']        = Carbon::now();

        $data['advance'] = $advance;

        return view('backend.posts.index', $data);
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
        $data['categories'] = Category::getConfigTypePost($data['type']);

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
            'article' => 'Thông tin',
            'event'   => 'Sự kiện',
            'voucher' => 'Khuyến mại',
        ];
        $data['heading']    = $heading[$data['type']];
        $data['building_id']    = $this->building_active_id;
        $data['meta_title'] = "QL {$data['heading']} > Bài viết";

        return view('backend.posts.edit', $data);
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

        $_building = Building::find($this->building_active_id); // lấy thông tin của dự án

        $building_id = $this->building_active_id;

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
            'publish_at'   => Carbon::now(),
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

        $data_list_user = array();$data_list_search = array();$data_list_manager = array();
        $email_cus = array(); $email_manager =array(); $data_list_email =array();
         // chỉ gửi email,sms ,app notify với status=1, is_sent = 0,private = 1 là tin nội bộ, 0 là công khai
        if(((isset($request['notify']['send_sms']) && $request['notify']['send_sms'] == 1) || (isset($request['notify']['send_app']) && $request['notify']['send_app'] == 1) || (isset($request['notify']['send_mail']) && $request['notify']['send_mail'] == 1)) && $request['notify']['is_sent'] == 0 && $request['status'] == 1){

            if($request['private'] == 1){
                if(isset($request['notify']['place_selected']) && $request['notify']['place_selected'] == 1){
                    if(!empty($request['notify']['place_ids'])){
                      
                        $list_apt = Apartments::whereIn('building_place_id',$request['notify']['place_ids'])->where('building_id',$building_id)->pluck('id')->toArray();

                        $list_cus = count($list_apt)> 0 ? CustomersRespository::findResidentApartmentV2($list_apt, $request['notify']['send_to'] == 0 ? 0 : null)->pluck('user_info_id')->toArray() : [];

                        $list_user = count($list_cus) > 0 ? DB::table('bdc_v2_user_info')->whereRaw("id IN ('".implode("','", $list_cus)."')")->select(['id', 'user_id', 'email_contact', 'phone_contact', 'full_name'])->get()->toArray() : [];

                       if(count($list_user) > 0) $data_list_user = array_merge($data_list_user,$list_user);
                    }

                }
                if(isset($request['notify']['customer_selected']) && $request['notify']['customer_selected'] == 1){
                    if(!empty($request['notify']['customer_ids'])){

                        $list_user = DB::table('bdc_v2_user_info')->whereIn('id', $request['notify']['customer_ids'])->select(['id', 'user_id', 'email_contact', 'phone_contact', 'full_name'])->get()->toArray();
                        
                        if(count($list_user) > 0) $data_list_user = array_merge($data_list_user,$list_user);
                    }

                }
                if(isset($request['notify']['group_selected']) && $request['notify']['group_selected'] == 1){
                    if(!empty($request['notify']['group_ids'])){

                        $list_cus = CustomersRespository::findResidentApartmentV2($request['notify']['group_ids'], $request['notify']['send_to'] == 0 ? 0 : null)->pluck('user_info_id')->toArray();

                        $list_user = count($list_cus) > 0 ? DB::table('bdc_v2_user_info')->whereRaw("id IN ('".implode("','", $list_cus)."')")->select(['id', 'user_id', 'email_contact', 'phone_contact', 'full_name'])->get()->toArray() : [];

                       if(count($list_user) > 0) $data_list_user = array_merge($data_list_user,$list_user);
                    }

                }
                if(isset($request['notify']['floor_selected']) && $request['notify']['floor_selected'] == 1){
                    if(!empty($request['notify']['floor_ids'])){

                        $list_apt = Apartments::whereIn('floor',$request['notify']['floor_ids'])->where('building_id',$building_id)->pluck('id')->toArray();

                        $list_cus = count($list_apt)> 0 ? CustomersRespository::findResidentApartmentV2($list_apt, $request['notify']['send_to'] == 0 ? 0 : null)->pluck('user_info_id')->toArray() : [];

                        $list_user = count($list_cus) > 0 ? DB::table('bdc_v2_user_info')->whereRaw("id IN ('".implode("','", $list_cus)."')")->select(['id', 'user_id', 'email_contact', 'phone_contact', 'full_name'])->get()->toArray() : [];

                       if(count($list_user) > 0) $data_list_user = array_merge($data_list_user,$list_user);
                    }

                }
                if(isset($request['notify']['all_selected']) && $request['notify']['all_selected'] == 1){

                    $list_cus = CustomersRespository::findResidentApartmentV2(null, $request['notify']['send_to'] == 0 ? 0 : null,$building_id )->pluck('user_info_id')->toArray();

                    $list_user = count($list_cus) > 0 ? DB::table('bdc_v2_user_info')->whereRaw("id IN ('".implode("','", $list_cus)."')")->select(['id', 'user_id', 'email_contact', 'phone_contact', 'full_name'])->get()->toArray() : [];

                    if(count($list_user) > 0) $data_list_user = array_merge($data_list_user,$list_user);

                }

            }else{
                $list_cus = CustomersRespository::findResidentApartmentV2(null, $request['notify']['send_to'] == 0 ? 0 : null,$building_id )->pluck('user_info_id')->toArray();

                $list_user = count($list_cus) > 0 ? DB::table('bdc_v2_user_info')->whereRaw("id IN ('".implode("','", $list_cus)."')")->select(['id', 'user_id', 'email_contact', 'phone_contact', 'full_name'])->get()->toArray() : [];

                if(count($list_user) > 0) $data_list_user = array_merge($data_list_user,$list_user);
            }

        }
        if ($slug) {
            $data_noti = [
                "message" => $request->title,
                "building_id"=> $this->building_active_id,
                "action_name"=> self::POST_NEW,
                "image"=> $request->image,
                "from_by"=> $post->user_id,
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
            
            $url          = UrlAlias::saveAlias($uri, $slug);
            $post->url_id = $url->id;
            $post->alias  = $url->alias;
            $update_noti = $post->notify;
            $unique_arr = array_unique(array_column($data_list_user,'id'));
            $data_list_user = array_intersect_key( $data_list_user,$unique_arr);
            $total = ['email'=>0, 'app'=> 0, 'sms'=> 0];
            $type_campain = 4;
            if($post->category_id == 1){ // thông báo
                $type_campain = $this->typeCampain['BAN_TIN'];
            }
            if($post->category_id == 2){ // tin hay
                $type_campain = $this->typeCampain['TIN_HAY'];
            }
            if($post->category_id == 3){ // sự kiện
                $type_campain = $this->typeCampain['SU_KIEN'];
            }
            if($post->category_id == 425){ // tài chính
                $type_campain = $this->typeCampain['TAI_CHINH'];
            }
            if(isset($request['notify']['send_app']) && $request['notify']['send_app'] == 1  && $request['status'] == 1 && $request['notify']['is_sent'] == 0) {

                $total['app'] = count($data_list_user);
                if ($total['app']>0){
                    $campain = Campain::updateOrCreateCampain($post->title, $type_campain, $post->id, $total, $this->building_active_id, 0, 0);
                    $update_noti['is_sent'] = 1;
                }

                foreach ($data_list_user as $key_1 => $value_1) {
                    $value_1 = (object)$value_1;
                    SendNotifyFCMService::setItemForQueueNotify(array_merge($data_noti, ['user_id' => $value_1->user_id, 'campain_id' => $campain->id,'app'=>'v2']));
                }

            }
            
            if(isset($request['notify']['send_mail']) && $request['notify']['send_mail'] == 1  && $request['status'] == 1 && $request['notify']['is_sent'] == 0) {
                $list_attaches=[];
                if($request->attaches){
                    foreach($request->attaches as $value){
                        if($value['src'] && file_get_contents($value['src'])){
                            array_push($list_attaches,$value['src']);
                        }
                    }
                }
                $total['email'] = count($data_list_user);
                if($total['email']>0){
                    $campain = Campain::updateOrCreateCampain($post->title, $type_campain, $post->id, $total, $this->building_active_id, 0, 0, @$campain->id);
                    $update_noti['is_sent'] = 1;
                }
                foreach ($data_list_user as $cudan) {
                    $cudan = (object)$cudan;
                    if(!@$cudan->email_contact || !filter_var($cudan->email_contact, FILTER_VALIDATE_EMAIL)){
                        continue;
                    }
                    ServiceSendMailV2::setItemForQueue( [
                        'params' => [
                          '@ten' => $cudan->full_name ?? 'Cư dân',
                          '@url'=> ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == "on") ? "https" : "http"). "://". @$_SERVER['HTTP_HOST'],
                          '@attachFile' => json_encode($list_attaches),
                        ],
                        'cc' =>$cudan->email_contact,
                        'building_id' => $this->building_active_id,
                        'type' => ServiceSendMailV2::POST_NEWS,
                        'feature' => $request->category_id == 1 ? 'higth' : 'medium', // higth, medium , low
                        'status' => 'prepare',
                        'subject' => $request->title,
                        'content'=> $request->content,
                        'campain_id' => $campain->id,
                    ]);
                }
               
            }
            
            if(isset($request['notify']['send_sms']) && isset($request['notify']['is_sent_sms']) && $request['notify']['send_sms'] == 1  && $request['notify']['is_sent_sms'] == 0 && $request['status'] == 1){
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
            $where[] = ['full_name', 'like', '%' . $keyword . '%'];
        }
        $customers = V2UserInfo::where($where)->paginate(10);
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

        $building_id = $this->building_active_id;

        $notify = $service->notify;
        $data_list_user = [];
        $data_noti = [];

        if (((isset($notify['send_sms']) && $notify['send_sms'] == 1) || (isset($notify['send_app']) && $notify['send_app'] == 1) || (isset($notify['send_mail']) && $notify['send_mail'] == 1)) && $notify['is_sent'] == 0) {

            if ($notify['private'] == 1) {

                if (isset($notify['place_selected']) && $notify['place_selected'] == 1) {
                    if (!empty($notify['place_ids'])) {

                        $list_apt = Apartments::whereIn('building_place_id', $notify['place_ids'])->where('building_id', $building_id)->pluck('id')->toArray();

                        $list_cus = count($list_apt) > 0 ? UserApartments::whereIn('apartment_id', $list_apt)->where('building_id', $building_id)->pluck('user_info_id')->toArray() : [];

                        $list_user = count($list_cus) > 0 ? V2UserInfo::whereIn('id', $list_cus)->select(['id', 'user_id', 'email_contact', 'phone_contact', 'full_name'])->get()->toArray() : [];

                        if (count($list_user) > 0) $data_list_user = array_merge($data_list_user, $list_user);
                    }
                }
                if (isset($notify['customer_selected']) && $notify['customer_selected'] == 1) {
                    if (!empty($notify['customer_ids'])) {

                        $list_user = V2UserInfo::whereIn('id', $notify['customer_ids'])->select(['id', 'user_id', 'email_contact', 'phone_contact', 'full_name'])->get()->toArray();

                        if (count($list_user) > 0) $data_list_user = array_merge($data_list_user, $list_user);
                    }
                }
                if (isset($notify['group_selected']) && $notify['group_selected'] == 1) {
                    if (!empty($notify['group_ids'])) {

                        $list_cus = count($list_apt) > 0 ? UserApartments::whereIn('apartment_id', $notify['group_ids'])->where('building_id', $building_id)->pluck('user_info_id')->toArray() : [];

                        $list_user = count($list_cus) > 0 ? V2UserInfo::whereIn('id', $list_cus)->select(['id', 'user_id', 'email_contact', 'phone_contact', 'full_name'])->get()->toArray() : [];

                        if (count($list_user) > 0) $data_list_user = array_merge($data_list_user, $list_user);
                    }
                }
                if (isset($notify['floor_selected']) && $notify['floor_selected'] == 1) {
                    if (!empty($notify['floor_ids'])) {

                        $list_apt = Apartments::whereIn('floor', $notify['floor_ids'])->where('building_id', $building_id)->pluck('id')->toArray();

                        $list_cus = count($list_apt) > 0 ? UserApartments::whereIn('apartment_id', $list_apt)->where('building_id', $building_id)->pluck('user_info_id')->toArray() : [];

                        $list_user = count($list_cus) > 0 ? V2UserInfo::whereIn('id', $list_cus)->select(['id', 'user_id', 'email_contact', 'phone_contact', 'full_name'])->get()->toArray() : [];

                        if (count($list_user) > 0) $data_list_user = array_merge($data_list_user, $list_user);
                    }
                }
                if (isset($notify['all_selected']) && $notify['all_selected'] == 1) {

                    $list_cus = UserApartments::where('building_id', $building_id)->pluck('user_info_id')->toArray();

                    $list_user = count($list_cus) > 0 ? V2UserInfo::whereIn('id', $list_cus)->select(['id', 'user_id', 'email_contact', 'phone_contact', 'full_name'])->get()->toArray() : [];

                    if (count($list_user) > 0) $data_list_user = array_merge($data_list_user, $list_user);
                }
            } else {
                $list_cus = UserApartments::where('building_id', $building_id)->pluck('user_info_id')->toArray();

                $list_user = count($list_cus) > 0 ? V2UserInfo::whereIn('id', $list_cus)->select(['id', 'user_id', 'email_contact', 'phone_contact', 'full_name'])->get()->toArray() : [];

                if (count($list_user) > 0) $data_list_user = array_merge($data_list_user, $list_user);
            }
        }

        $data_noti = [
            "message" => $request->title,
            "building_id" => $this->building_active_id,
            "title" => $request->title,
            "action_name" => self::POST_NEW,
            "image" => $request->image,
        ];
        if ($service->type == 'event') {
            $uri = 'events/' . $service->id;
            $data_noti += [
                'type' => SendNotifyFCMService::NEW_POST_EVENT,
                'screen' => "EventSingle",
                "id" => $service->id
            ];
        } elseif ($service->type == 'voucher') {
            $uri = 'vouchers/' . $service->id;
            $data_noti += [
                'type' => SendNotifyFCMService::NEW_POST_VOUCHER,
                'screen' => "VoucherSingle",
                "id" => $service->id
            ];
        } else {
            $uri = 'posts/' . $service->id;
            $data_noti += [
                'type' => SendNotifyFCMService::NEW_POST_ARTICLE,
                'screen' => "PostSingle",
                "id" => $service->id
            ];
        }

        $unique_arr = array_unique(array_column($data_list_user, 'id'));
        $data_list_user = array_intersect_key($data_list_user, $unique_arr);
        $total = ['email' => 0, 'app' => 0, 'sms' => 0];
        $type_campain = 4;
        if($service->category_id == 1){ // thông báo
            $type_campain = $this->typeCampain['BAN_TIN'];
        }
        if($service->category_id == 2){ // tin hay
            $type_campain = $this->typeCampain['TIN_HAY'];
        }
        if($service->category_id == 3){ // sự kiện
            $type_campain = $this->typeCampain['SU_KIEN'];
        }
        if($service->category_id == 425){ // tài chính
            $type_campain = $this->typeCampain['TAI_CHINH'];
        }
        if (isset($notify['send_app']) && $notify['send_app'] == 1 && $notify['is_sent'] == 0) {

            $total['app'] = count($data_list_user);
            if ($total['app'] > 0) {
                $campain = Campain::updateOrCreateCampain($service->title, $type_campain, $service->id, $total, $this->building_active_id, 0, 0);
                $notify['is_sent'] = 1;
            }

            foreach ($data_list_user as $key_1 => $value_1) {
                $value_1 = (object)$value_1;
                SendNotifyFCMService::setItemForQueueNotify(array_merge($data_noti, ['user_id' => $value_1->user_id, 'campain_id' => $campain->id, 'app' => 'v2']));
            }
           
        }

        if (isset($notify['send_mail']) && $notify['send_mail'] == 1 && $notify['is_sent'] == 0) {
            $list_attaches = [];
            if ($service->attaches) {
                foreach ($service->attaches as $value) {
                    if ($value['src'] && file_get_contents($value['src'])) {
                        array_push($list_attaches, $value['src']);
                    }
                }
            }
            $total['email'] = count($data_list_user);
            if ($total['email'] > 0) {
                $campain = Campain::updateOrCreateCampain($service->title, $type_campain, $service->id, $total, $this->building_active_id, 0, 0, @$campain->id);
                $notify['is_sent'] = 1;
            }
            foreach ($data_list_user as $cudan) {
                $cudan = (object)$cudan;
                if (!@$cudan->email_contact) {
                    continue;
                }
                ServiceSendMailV2::setItemForQueue([
                    'params' => [
                        '@ten' => $cudan->full_name ?? 'Cư dân',
                        '@url' => ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == "on") ? "https" : "http") . "://" . @$_SERVER['HTTP_HOST'],
                        '@attachFile' => json_encode($list_attaches),
                    ],
                    'cc' => $cudan->email_contact,
                    'building_id' => $this->building_active_id,
                    'type' => ServiceSendMailV2::POST_NEWS,
                    'feature' => $service->category_id == 1 ? 'higth' : 'medium', // higth, medium , low
                    'status' => 'prepare',
                    'subject' => $service->title,
                    'content' => $service->content,
                    'campain_id' => $campain->id,
                ]);
            }
          
        }
        $service->notify = $notify;
        $service->save();
        $this->modelPost->changeStatusPost($request);

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
