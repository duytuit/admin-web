<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Controller;
use App\Http\Resources\PostResource;
use App\Models\BoCustomer;
use App\Models\CustomerGroup;
use App\Models\Fcm;
use App\Models\Post;
use App\Models\PostEmotion;
use App\Models\PostFollow;
use App\Models\PostPoll;
use App\Models\PostRegister;
use App\Models\PostVote;
use App\Models\PublicUser\UserInfo;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

class PostController extends Controller
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->model    = new Post();
        $this->resource = new PostResource(null);

        Carbon::setLocale('vi');
    }

    /**
     * Danh sách các bản ghi nội bộ
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, $scope = 'all')
    {
        $expire    = 15;
        $cache_key = 'api_posts_' . $scope . '_' . md5(serialize($request->all()));

        // if (Cache::has($cache_key)) {
        //     return Cache::get($cache_key);
        // }\

        $user = $this->getApiUser();

        $type = $request->input('type', '');
        $keyword = $request->input('keyword', '');
//        dd($keyword);
        // kind =  normal/pin/slide => News/voucher/event is nomal/pin (only last ONE)/slide(show)
        $kind     = $request->input('kind', '');
        $per_page = $request->input('per_page', 10);
        $per_page = $per_page > 0 ? $per_page : 10;

        $columns    = $this->model->getTableColumns();
        $registered = !!$request->input('registered', false);

        $excludes = [
            'user_id',
            'customer_ids',
            'customer_group_ids',
            'notify',
            'deleted_at',
        ];

        $allowFields = [];
        foreach ($columns as $column) {
            if (!in_array($column, $excludes)) {
                $allowFields[] = $column;
            }
        }
        $where = [];

        $where[] = ['status', '=', 1];
        $where[] = ['publish_at', '<', Carbon::now()];

        if (in_array($type, ['article', 'event', 'voucher'])) {
            $where[] = ['type', '=', $type];
        }

        // Nếu lấy tin công khai
        if ($scope == 'public') {
            $where[] = ['private', '=', 0];
            if($keyword != ''){
                $where[] = [function ($query) use ($keyword) {
                    $query->where('title','like',$keyword.'%');
                    $query->whereIn('type',['article','event']);
                }];
            }
        }

        if ($scope == 'private') {
            // danh sach post public
            $list_post_public = Post::where('status',1)->where('type',$type)->where('private','0')->get();

            $list_post = [];
            foreach ($list_post_public as $lp){
                array_push($list_post,$lp['id']);
            }

            $list_post_private = Post::where('status',1)->where('type',$type)->where('private','1')->get();
            $list_post_p = [];$data_list=[];
            foreach ($list_post_private as $lp){
                if(isset($lp['notify']['customer_ids'])){
                    foreach ($lp['notify']['customer_ids'] as $ci){
                        if($ci == $user->cb_id){
                            array_push($list_post_p,$lp['id']);
                        }
                    }
                }
                if(isset($lp['notify']['group_ids'])){
                    $cus_group = new CustomerGroup();
                    $list_cus= $cus_group->getCustomerByid($lp['notify']['group_ids']);
                    $data_list = array_map(function($item){ return $item['id']; }, $list_cus);
                    foreach ($data_list as $ci){
                        if($ci == $user->id){
                            array_push($list_post_p,$lp['id']);
                        }
                    }
                }
                if(isset($lp['notify']['all_selected'])?$lp['notify']['all_selected']:0 == 1){
                    $list_cus= UserInfo::select('id')->get()->toArray();
                    $data_list = array_map(function($item){ return $item['id']; }, $list_cus);
                    $list_login_app = Fcm::whereIn('user_id',$data_list)->select('user_id')->distinct()->get()->toArray();
                    $list_user = array_map(function($item){ return $item['user_id']; }, $list_login_app);
                    foreach ($list_user as $ci){
                        if($ci == $user->id){
                            array_push($list_post_p,$lp['id']);
                        }
                    }
                }
            }
            $list_post_all = array_unique(array_merge($list_post,$list_post_p));
            if ($registered) {
                $register = PostRegister::where('user_id', $user->id)
                    ->where('user_type', $user->type)
                    ->where('post_type', $type)
                    ->pluck('post_id')
                    ->toArray();

                $list_post_all =  array_unique(array_merge($list_post_all,$register));
            }
            $where[] = [function ($query) use ($list_post_all) {
                $query->where('private', 1);
                $query->whereIn('id', $list_post_all);
            }];
        }
        if ($scope == 'all') {
            // danh sach post public
            if($keyword != ''){
                $list_post_public = Post::where('status',1)->whereIn('type',['article','event'])->where('title','like',$keyword.'%')->where('private','0')->get();
                $list_post_private = Post::where('status',1)->whereIn('type',['article','event'])->where('title','like',$keyword.'%')->where('private','1')->get();
            }else{
                $list_post_public = Post::where('status',1)->where('type',$type)->where('private','0')->get();
                $list_post_private = Post::where('status',1)->where('type',$type)->where('private','1')->get();
            }


            $list_post = [];
            foreach ($list_post_public as $lp){
                array_push($list_post,$lp['id']);
            }

//            $list_post_private = Post::where('status',1)->where('type',$type)->where('private','1')->get();
            $list_post_p = [];
            foreach ($list_post_private as $lp){
                if(isset($lp['notify']['customer_ids'])){
                    foreach ($lp['notify']['customer_ids'] as $ci){
                        if($ci == $user->cb_id){
                            array_push($list_post_p,$lp['id']);
                        }
                    }
                }
                if(isset($lp['notify']['group_ids'])){
                    $cus_group = new CustomerGroup();
                    $list_cus= $cus_group->getCustomerByid($lp['notify']['group_ids']);
                    $data_list = array_map(function($item){ return $item['id']; }, $list_cus);
                    foreach ($data_list as $ci){
                        if($ci == $user->id){
                            array_push($list_post_p,$lp['id']);
                        }
                    }
                }
                if(isset($lp['notify']['all_selected'])?$lp['notify']['all_selected']:0 == 1){
                    $list_cus= UserInfo::select('id')->get()->toArray();
                    $data_list = array_map(function($item){ return $item['id']; }, $list_cus);
                    $list_login_app = Fcm::whereIn('user_id',$data_list)->select('user_id')->distinct()->get()->toArray();
                    $list_user = array_map(function($item){ return $item['user_id']; }, $list_login_app);
                    foreach ($list_user as $ci){
                        if($ci == $user->id){
                            array_push($list_post_p,$lp['id']);
                        }
                    }
                }
            }
            $list_post_all = array_unique(array_merge($list_post,$list_post_p));
            if ($registered) {
                $register = PostRegister::where('user_id', $user->id)
                    ->where('user_type', $user->type)
                    ->where('post_type', $type)
                    ->pluck('post_id')
                    ->toArray();

                $list_post_all =  array_unique(array_merge($list_post_all,$register));
            }
            $where[] = [function ($query) use ($list_post_all) {
//                $query->orwhere('private', 1);
                $query->whereIn('id', $list_post_all);
            }];
        }

        $select    = $this->_select($request, $allowFields);
        $condition = $this->_filters($request, $columns);
        $order_by  = $this->_sort($request, $columns);

        $items = $this->model->select($select)
            ->where($where)
            ->where($condition)
            ->orderByRaw($order_by)
            ->paginate($per_page);

        $items->load('category');

        $items = $this->isRegistered($items, $type);
        $items = $this->isCheckIn($items);

        $resource = $this->resource->many($items);
//        dd($resource);
        return $resource;
    }

    public function indexbug(Request $request, $scope = 'all')
    {
        $expire    = 15;
        $cache_key = 'api_posts_' . $scope . '_' . md5(serialize($request->all()));

        // if (Cache::has($cache_key)) {
        //     return Cache::get($cache_key);
        // }\

        $user = $this->getApiUser();

        $type = $request->input('type', '');
        // kind =  normal/pin/slide => News/voucher/event is nomal/pin (only last ONE)/slide(show)
        $kind     = $request->input('kind', '');
        $per_page = $request->input('per_page', 10);
        $per_page = $per_page > 0 ? $per_page : 10;

        $columns    = $this->model->getTableColumns();
        $registered = !!$request->input('registered', false);

        $excludes = [
            'user_id',
            'customer_ids',
            'customer_group_ids',
            'notify',
            'deleted_at',
        ];

        $allowFields = [];
        foreach ($columns as $column) {
            if (!in_array($column, $excludes)) {
                $allowFields[] = $column;
            }
        }

        $where = [];

        $where[] = ['status', '=', 1];
        $where[] = ['publish_at', '<', Carbon::now()];

        if (in_array($type, ['article', 'event', 'voucher'])) {
            $where[] = ['type', '=', $type];
        }

        // Nếu lấy tin công khai
        if ($scope == 'public') {
            $where[] = ['private', '=', 0];
        }

        // Nếu lấy tin nội bộ
        if ($scope == 'private') {
            $where[] = [
                function ($query) use ($user, $type) {
                    $query->where('private', 1);

                    if ($user->type == 'customer') {
                        $query->orWhereRaw('JSON_CONTAINS(notify,"$.customer_ids", ' . (int) $user->id . ')');
                        if (is_array($user->group_id)) {
                            foreach ($user->group_id as $group_id) {
                                $query->orWhereRaw('JSON_CONTAINS(notify,"$.customer_ids", '. (int) $group_id . ')');
                            }
                        }
                    }
                    // if ($user->type == 'customer') {
                    //     $query->orWhereRaw("JSON_CONTAINS(`notify`, " . (int) $user->id . ", '$.customer_ids')");
                    //     if (is_array($user->group_id)) {
                    //         foreach ($user->group_id as $group_id) {
                    //             $query->orWhereRaw("JSON_CONTAINS(`notify`, " . (int) $group_id . ", '$.customer_ids')");
                    //         }
                    //     }
                    // }
                },
            ];
        }

        // Lấy tất cả
        if ($scope == 'all') {
            $where[] = [
                function ($query) use ($user, $type) {

                    if ($type == 'event') {
                        $query->orWhere('private', 0);
                    }

                    if ($user->type == 'customer' && $type != 'voucher') {
                        $query->orWhereRaw("JSON_CONTAINS(`notify`, " . (int) $user->id . ", '$.customer_ids')");
                        if (is_array($user->group_id)) {
                            foreach ($user->group_id as $group_id) {
                                $query->orWhereRaw("JSON_CONTAINS(`notify`, " . (int) $group_id . ", '$.customer_ids')");
                            }
                        }
                    }
                },
            ];
        }

        if (in_array($kind, ['normal', 'pin', 'slide'])) {
            $where[] = ['kind', '=', $kind];
        }

        $select    = $this->_select($request, $allowFields);
        $condition = $this->_filters($request, $columns);
        $order_by  = $this->_sort($request, $columns);

        if ($registered) {
            $register = PostRegister::where('user_id', $user->id)
                ->where('user_type', $user->type)
                ->where('post_type', $type)
                ->pluck('post_id')
                ->toArray();

            $where[] = [function ($query) use ($register) {
                $query->whereIn('id', $register);
            }];
        }

        $items = $this->model->select($select)
            ->where($where)
            ->where($condition)
            ->orderByRaw($order_by)
            ->paginate($per_page);

        $items->load('category');

        $items = $this->isRegistered($items, $type);
        $items = $this->isCheckIn($items);

        $resource = $this->resource->many($items);

        // Cache::put($cache_key, $resource, $expire);

        return $resource;
    }

    protected function isRegistered(&$posts, $type)
    {
        $user = $this->getApiUser();

        if ($user) {
            // $post_ids = $posts->pluck('id')->toArray();

            $register = PostRegister::where('user_id', $user->id)
                ->where('user_type', $user->type)
                ->where('post_type', $type)
                ->pluck('post_id')
                ->toArray();

        } else {
            $register = [];
        }

        foreach ($posts as $post) {
            $post->registered = in_array($post->id, $register);
        }

        return $posts;
    }

    protected function isCheckIn(&$posts)
    {
        $post_ids = $posts->pluck('id')->toArray();

        $register = PostRegister::select('post_id')
            ->whereIn('post_id', $post_ids)
            ->whereNull('check_in')
            ->pluck('post_id')
            ->toArray();

        $items = collect([]);

        foreach ($posts as $post) {
            if ($post->end_at > Carbon::now()) {
                if (in_array($post->id, $register)) {
                    $post->check_in = 0;
                } else {
                    $post->check_in = 1;
                }
            } else {
                $post->check_in = -1;
            }

            $items->push($post);
        }

        return $items;
    }

    /**
     * Danh sách các bản ghi công khai
     *
     * @return \Illuminate\Http\Response
     */
    public function getPublic(Request $request)
    {
        return $this->index($request, 'public');
    }

    /**
     * Danh sách các bản ghi nội bộ
     *
     * @return \Illuminate\Http\Response
     */
    public function getPrivate(Request $request)
    {
        return $this->index($request, 'private');
    }

    /**
     * Danh sách các bản ghi công khai + nội bộ
     *
     * @return \Illuminate\Http\Response
     */
    public function getAll(Request $request)
    {
        return $this->index($request, 'all');
    }

    /**
     * Display the specified resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
        $id = (int) $request->id;

        try {
            $item               = $this->model->findOrFail($id);
            $option             = $item->pollOptions();
            $item->poll_options = $option;

            $item->my_response = [
                'emotion'       => $this->getEmotion($id),
                'follow'        => $this->getFollow($id),
                'register'      => $this->getRegister($id),
                'code_register' => $this->getCode($id),
                'check_in'      => $this->getCheckIn($id),
                'vote'          => $this->getVote($id),
                'poll'          => $this->getPoll($id),
            ];

            if ($item->type == 'voucher') {
                $item->load('partner', 'partner.branches');
            }

            return response()->json(['data' => $item]);

        } catch (ModelNotFoundException $exception) {
            $json = [
                'errors' => [
                    [
                        'code'   => 11001,
                        'title'  => 'Record not found',
                        'detail' => 'Record ID ' . $id . ' for ' . str_replace('App\\', '', $exception->getModel()) . ' not found',
                    ],
                ],
            ];

            return response()->json($json, 404);
        }
    }

    protected function getEmotion($post_id)
    {
        $user = $this->getApiUser();

        $item = PostEmotion::select('emotion')
            ->where('post_id', $post_id)
            ->where('user_id', $user->id)
            ->where('user_type', $user->type)
            ->first();

        if ($item) {
            return $item->emotion;
        }

        return null;
    }

    protected function getFollow($post_id)
    {
        $user = $this->getApiUser();

        $item = PostFollow::select('id')
            ->where('post_id', $post_id)
            ->where('user_id', $user->id)
            ->where('user_type', $user->type)
            ->first();

        if ($item) {
            return true;
        }

        return false;
    }
    protected function getRegister($post_id)
    {
        $user = $this->getApiUser();

        $item = PostRegister::select('id')
            ->where('post_id', $post_id)
            ->where('user_id', $user->id)
            ->where('user_type', $user->type)
            ->first();

        if ($item) {
            return true;
        }

        return false;
    }

    protected function getCode($post_id)
    {
        $user = $this->getApiUser();
        $item = PostRegister::select('id', 'code')
            ->where('post_id', $post_id)
            ->where('user_id', $user->id)
            ->where('user_type', $user->type)
            ->first();

        if ($item) {
            return $item->code;
        }

        return null;
    }

    protected function getCheckIn($post_id)
    {
        $user = $this->getApiUser();

        $item = PostRegister::select('id')
            ->where('post_id', $post_id)
            ->where('user_id', $user->id)
            ->where('user_type', $user->type)
            ->whereNotNull('check_in')
            ->first();

        if ($item) {
            return true;
        }

        return false;
    }

    protected function getVote($post_id)
    {
        $user = $this->getApiUser();

        $item = PostVote::select('rating')
            ->where('post_id', $post_id)
            ->where('user_id', $user->id)
            ->where('user_type', $user->type)
            ->first();

        if ($item) {
            return $item->rating;
        }

        return null;
    }

    protected function getPoll($post_id)
    {
        $user = $this->getApiUser();

        $post_polls = PostPoll::select('poll_key', 'poll_id')
            ->where('post_id', $post_id)
            ->where('user_id', $user->id)
            ->where('user_type', $user->type)
            ->get()
            ->groupBy('poll_id');

        if ($post_polls) {
            $key = [];
            foreach ($post_polls as $poll_id => $pollKey) {
                $poll_id = (string) $poll_id;
                foreach ($pollKey as $value) {
                    $key[$poll_id][] = $value['poll_key'];
                }
            }
            $key = collect($key);
            if ($key->count() > 0) {
                return $key;
            }
        }

        return null;
    }

    /**
     * Save a resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function save(PostRequest $request)
    {
        $id    = (int) $request->id;
        $type  = $request->input('type', 'article');
        $input = $request->all();

        $input['id']      = $id;
        $input['status']  = $request->input('status', 0);
        $input['user_id'] = 1;

        $item = Post::findOrNew($id);
        $item->fill($input)->save();

        // url alias
        if (empty($item->alias)) {
            $slug = str_slug($item->title);
        } else {
            $slug = $request->alias;
        }

        if ($slug) {
            if ($item->type == 'event') {
                $uri = 'events/' . $item->id;
            } elseif ($item->type == 'voucher') {
                $uri = 'vouchers/' . $item->id;
            } else {
                $uri = 'articles/' . $item->id;
            }

            $url = UrlAlias::saveAlias($uri, $slug);

            $item->url_id = $url->id;
            $item->alias  = $url->alias;
            $item->save();
        }

        return $this->resource->one($item);
    }
}
