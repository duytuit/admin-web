<?php

namespace App\Http\Controllers\Comments;

use App\Commons\Helper;
use App\Helpers\dBug;
use App\Http\Controllers\BuildingController;
use App\Http\Requests\Comments\CommentsRequest;
use App\Models\Comment;
use App\Models\Comments\Comments;
use App\Repositories\Comments\CommentsRespository;
use App\Repositories\Feedback\FeedbackRespository;
use App\Repositories\Posts\PostsRespository;
use App\Repositories\PublicUsers\PublicUsersProfileRespository;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Campain;
use App\Models\Fcm\Fcm;
use App\Models\Feedback\Feedback;
use App\Models\PublicUser\V2\TokenUserPush;
use App\Models\PublicUser\V2\User;
use App\Models\PublicUser\V2\UserInfo;
use App\Models\SentStatus;
use App\Services\FCM\SendNotifyFCMService;
use App\Util\Debug\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CommentsController extends BuildingController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    private $model;
    private $modelPosts;
    private $modelFeedback;
    private $modelUserInfo;


    public function __construct(CommentsRespository $model,PostsRespository $modelPosts, FeedbackRespository $modelFeedback,PublicUsersProfileRespository $modelUserInfo,Request $request)
    {
        //$this->middleware('route_permision');
        $this->model = $model;
        $this->modelPosts = $modelPosts;
        $this->modelFeedback = $modelFeedback;
        $this->modelUserInfo = $modelUserInfo;
        parent::__construct($request);
    }

    public function index(Request $request)
    {
        $data['per_page'] = Cookie::get('per_page', 20);
        $data['type']     = $request->input('type', 'article');

        // Tìm kiếm nâng cao
        $advance = 0;

        $data['keyword'] = $request->input('keyword', '');
        $data['title']   = $request->input('title', '');
        $data['rating']  = $request->input('rating', '');
        $data['name']    = $request->input('name', '');
        $data['status']  = $request->input('status', '');

        $where = [];

        if ($data['keyword']) {
            $where[] = ['content', 'like', '%' . $data['keyword'] . '%'];
        }

        if ($data['rating']) {
            $where[] = ['rating', '=', $data['rating']];
            $advance = 1;
        }

        if ($data['type']) {
            $where[] = ['type', '=', $data['type']];
            $advance = 1;
        }

        if ($data['status'] != '') {
            $where[] = ['status', '=', (int) $data['status']];
            $advance = 1;
        }

        $comments = Comments::where($where);

        if ($data['title']) {
            $comments->whereHas('post', function ($query) use ($data) {
                $query->where('title', 'like', '%' . $data['title'] . '%');
            });
        }

        if ($data['name']) {
            $comments->whereHas('user', function ($query) use ($data) {
                $table = $query->getModel()->getTable();
                if ($table == 'bo_users') {
                    $query->where('ub_title', 'like', '%' . $data['name'] . '%');
                } else {
                    $query->where('cb_name', 'like', '%' . $data['name'] . '%');
                }
            });
        }

        $comments = $comments->orderByRaw('id DESC')->paginate($data['per_page']);

        $comments->load('user','user.BDCprofile');
        $comments->load('post');

        $data['comments'] = $comments;

        $heading = [
            'article' => 'Thông tin',
            'event'   => 'Sự kiện',
            'voucher' => 'Khuyến mại',
        ];
        $data['heading']    = $heading[$data['type']];
        $data['meta_title'] = "QL {$data['heading']} > Danh mục";

        $data['advance'] = $advance;
//        dd($comments);

        return view('backend.comments.index', $data);
    }
    public function indexEvent(Request $request)
    {
        $data['per_page'] = Cookie::get('per_page', 20);
        $data['type']     = $request->input('type', 'event');

        // Tìm kiếm nâng cao
        $advance = 0;

        $data['keyword'] = $request->input('keyword', '');
        $data['title']   = $request->input('title', '');
        $data['rating']  = $request->input('rating', '');
        $data['name']    = $request->input('name', '');
        $data['status']  = $request->input('status', '');

        $where = [];

        if ($data['keyword']) {
            $where[] = ['content', 'like', '%' . $data['keyword'] . '%'];
        }

        if ($data['rating']) {
            $where[] = ['rating', '=', $data['rating']];
            $advance = 1;
        }

        if ($data['type']) {
            $where[] = ['type', '=', $data['type']];
            $advance = 1;
        }

        if ($data['status'] != '') {
            $where[] = ['status', '=', (int) $data['status']];
            $advance = 1;
        }

        $comments = Comments::where($where);

        if ($data['title']) {
            $comments->whereHas('post', function ($query) use ($data) {
                $query->where('title', 'like', '%' . $data['title'] . '%');
            });
        }

        if ($data['name']) {
            $comments->whereHas('user', function ($query) use ($data) {
                $table = $query->getModel()->getTable();
                if ($table == 'bo_users') {
                    $query->where('ub_title', 'like', '%' . $data['name'] . '%');
                } else {
                    $query->where('cb_name', 'like', '%' . $data['name'] . '%');
                }
            });
        }

        $comments = $comments->orderByRaw('id DESC')->paginate($data['per_page']);

        $comments->load('user');
        $comments->load('post');

        $data['comments'] = $comments;

        $heading = [
            'article' => 'Thông tin',
            'event'   => 'Sự kiện',
            'voucher' => 'Khuyến mại',
        ];
        $data['heading']    = $heading[$data['type']];
        $data['meta_title'] = "QL {$data['heading']} > Danh mục";

        $data['advance'] = $advance;
//        dd($comments);

        return view('backend.comments.index', $data);
    }

    public function detail(Request $request, $id = 0)
    {
        $comment = $this->model->findFail($id);

        $post         = $this->modelPosts->findFail($comment->post_id);
        $data         = [];
        $data['type'] = $request->input('type', 'article');

        $comments = $post->comments;

        $comments->load('user','user.profileAll', 'comments', 'comments.user', 'comments.user.profileAll');
//        $comments->load('user', 'comments', 'comments.user');
//        dd($comments);
        $data['post']     = $post;
        $data['comments'] = $comments;
        $data['id'] = $id;
        $data['now']      = Carbon::now();
        $data['colors']   = ['#008a00', '#0050ef', '#6a00ff', '#a20025', '#fa6800', '#825a2c', '#6d8764'];

        $heading = [
            'article' => 'Thông tin',
            'event'   => 'Sự kiện',
            'voucher' => 'Khuyến mại',
        ];
        $data['heading']    = $heading[$data['type']];
        $data['meta_title'] = "QL {$data['heading']} > Danh mục";

        return view('backend.comments.detail', $data);
    }
    public function detailPost(Request $request, $id = 0)
    {
        $post         = $this->modelPosts->findFail($id);
        $data         = [];
        $data['type'] = $request->input('type', 'article');

        $comments = $post->comments;

        $comments->load('user','userInfo', 'comments', 'comments.user', 'comments.userInfo');
//        $comments->load('user', 'comments', 'comments.user');
//        dd($comments);
        $data['post']     = $post;
        $data['comments'] = $comments;
        $data['id'] = $id;
        $data['now']      = Carbon::now();
        $data['colors']   = ['#008a00', '#0050ef', '#6a00ff', '#a20025', '#fa6800', '#825a2c', '#6d8764'];

        $heading = [
            'article' => 'Thông tin',
            'event'   => 'Sự kiện',
            'voucher' => 'Khuyến mại',
        ];
        $data['heading']    = $heading[$data['type']];
        $data['meta_title'] = "QL {$data['heading']} > Danh mục";

        return view('backend.comments.detail', $data);
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {

    }

    public function save(CommentsRequest $request, $id = 0)
    {
        Log::info('check_command_feekback','0_'.json_encode($request->all()));
        $user = $this->modelUserInfo->getInfoByPubuserId(Auth::user()->id, $this->building_active_id);
        if(!$user){
            $message = [
                'error'  => 1,
                'status' =>  false,
                'msg'    => 'Bạn chưa cập nhật tài khoản vào tòa nhà này.',
            ];
            return response()->json($message);
        }
        $type = $request->input('type', 'article');
        $input = $request->except(['_token']);
        $files_name = $request->input('name_fileupload');
        $attached = null;
        if ($files_name) {

            $directory = 'media/feedback';

            $file_doc = $_SERVER['DOCUMENT_ROOT'] . '/' . $directory . '/' . $request->input('name_fileupload');

            $rs_file = Helper::doUpload($input["fileBase64"],$request->input('name_fileupload'),$directory);

            if(Helper::check_file_type_is_image($file_doc)){
                $attached['images']=@$rs_file->origin ? [@$rs_file->origin] : [];
                $attached['files']= [];
            }else{
                $attached['images']= [];
                $attached['files']=@$rs_file->origin ? [@$rs_file->origin] : [];
            }

            $input['url_fileupload'] =@$rs_file->origin;
            unset($input["fileBase64"]);
        }

        $input['status']  = $request->input('status', 0);
        $input['content'] = strip_tags($request->input('content'));
        $input['user_id'] = $user->id;
        $input['files'] = $attached ?json_encode($attached) : null;

        $input['user_type'] = 'user';

        $comment = $this->model->findNew($id);
        $comment->forceFill($input)->save();

        $post = $this->getPost($request);
        //        dd($post);
        if ($request->type != 'feedback') {


            $this->model->savePostResponse($post);
        }
        $list_user_comment = $this->model->listUser(['post_id'=> $post->id,'type'=>$type], ['user_id','new']);

        $total = ['email' => 0, 'app' => $list_user_comment->count() ?? 1, 'sms' => 0];
        $type_campain = 4;
        if($post->category_id == 1){ // thông báo
            $type_campain =config('typeCampain.BAN_TIN');
        }
        if($post->category_id == 2){ // tin hay
            $type_campain =config('typeCampain.TIN_HAY') ;
        }
        if($post->category_id == 3){ // sự kiện
            $type_campain = config('typeCampain.SU_KIEN');
        }
        if($post->category_id == 425){ // tài chính
            $type_campain =config('typeCampain.TAI_CHINH') ;
        }
        $campain = Campain::updateOrCreateCampain($post->title . ' có bình luận mới', $type_campain, $post->id, $total, $post->bdc_building_id, 0, 0);
        foreach ($list_user_comment as $lu) {
            if ($post->type == 'event') {
                $data_noti = [
                    "message" => $request['content'],
                    "title" => $post->title . ' có bình luận mới',
                    'type' => SendNotifyFCMService::NEW_POST_EVENT,
                    'screen' => "EventSingle",
                    "id" => $post->id
                ];
            } elseif ($post->type == 'voucher') {
                $data_noti = [
                    "message" => $request['content'],
                    "title" => $post->title . ' có bình luận mới',
                    'type' => SendNotifyFCMService::NEW_POST_VOUCHER,
                    'screen' => "VoucherSingle",
                    "id" => $post->id
                ];
            } elseif ($post->type == 'article') {
                $data_noti = [
                    "message" => $request['content'],
                    "title" => $post->title . ' có bình luận mới',
                    'type' => SendNotifyFCMService::NEW_POST_ARTICLE,
                    'screen' => "PostSingle",
                    "id" => $post->id
                ];
            } else {
                $data_noti = [
                    "message" => $request['content'],
                    "title" => $post->title . 'bạn có phản hồi mới',
                    'type' => SendNotifyFCMService::NEW_POST_FEEDBACK,
                    'screen' => "PetitionSingle",
                    "id" => $post->id
                ];
            }

            if($lu->new == 1){
                $data_noti['user_id'] =  $lu->user_id;
                SendNotifyFCMService::setItemForQueueNotify(array_merge($data_noti, ['building_id' =>$post->bdc_building_id,'campain_id' => $campain->id,'app'=>'v2']));
            }else{
                $user_id = $this->modelUserInfo->findAllBy_v1($lu->user_id);
                $data_noti['user_id'] =  $user_id->pub_user_id;
                SendNotifyFCMService::setItemForQueueNotify(array_merge($data_noti, ['building_id' =>$post->bdc_building_id,'campain_id' => $campain->id,'app'=>'v1']));

            }

        }
        if($request->type == 'feedback' && $post->user_id){
            $data_noti['user_id'] =  $post->user_id;
            SendNotifyFCMService::setItemForQueueNotify(array_merge($data_noti, ['building_id' =>$post->bdc_building_id,'campain_id' => $campain->id,'app'=>'v2']));
        }
        $message = [
            'error'  => 0,
            'status' => 'success',
            'msg'    => 'Đã cập nhập thông tin',
        ];

        if ($request->ajax()) {
            $comment->load('userInfo');
            $words = explode(' ', isset($comment->userInfo->display_name) ? @$comment->userInfo->display_name : (@$comment->userInfo->email ?? ''));
            $name  = end($words);
            $char  = substr($name, 0, 1);

            $comment->username = isset($comment->userInfo->display_name) ? @$comment->userInfo->display_name : (@$comment->userInfo->email ?? '');
            $comment->char     = strtoupper($char)??'';
            $comment->avatar     = isset($comment->userInfo->avatar) ? @$comment->userInfo->avatar : '';
            $comment->created  = $comment->created_at->diffForHumans(Carbon::now());

            $message['data'] = $comment;
            //dBug::trackingPhpErrorV2($message);
            Log::info('check_command_feekback','1_'.json_encode($message));
            return response()->json($message);
        } else {
            return redirect()
                ->route('admin.comments.index', ['type' => $type])
                ->with('message', $message);
        }
    }
    public function getPost($request)
    {
        $post_id = $request->post_id;
        if ($request->type == 'feedback') {
            $post = $this->modelFeedback->whereFindFail(['*'], 'id', $post_id);
            if($post){
                $post->update(['status'=>2]); // chờ cư dân phản hồi
            }
        } else {
            $post = $this->modelPosts->whereFindFail(['id', 'type', 'response', 'title','category_id'], 'id', $post_id);
        }
        return $post;
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

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {

    }
    public function action(Request $request)
    {
        return $this->model->action($request);
    }
    public function downloadfile(Request $request)
    {
            //file path in server
        $file_path = $_SERVER['DOCUMENT_ROOT'].$request->downloadfile;
        // check if file exist in server
        if(file_exists($file_path)) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="'.basename($file_path).'"');
            header('Expires: 0');
            header('Pragma: public');
            header('Content-Length: ' . filesize($file_path));
            // Clear output buffer
            flush();
            readfile($file_path);
            exit();
        }else{
            echo "File not found.";
        }
    }
}
