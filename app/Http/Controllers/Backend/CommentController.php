<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Backend\Controller;
use App\Http\Requests\CommentRequest;
use App\Models\Comment;
use App\Models\Campain;
use App\Models\Fcm;
use App\Models\Post;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use App\Models\Feedback;
use App\Models\SentStatus;
use App\Services\FCM\SendNotifyFCMService;

class CommentController extends Controller
{
    // không dùng
    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->model = new Comment();
        Carbon::setLocale('vi');
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
        $data['type']     = $request->input('type', 'article');

        switch ($data['type']) {
            case 'article':
                $this->authorize('article.index', app(Comment::class));
                break;

            case 'event':
                $this->authorize('event.index', app(Comment::class));
                break;

            case 'voucher':
                $this->authorize('voucher.index', app(Comment::class));
                break;

            default:
                break;
        }

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

        $comments = Comment::where($where);

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

    /**
     * Danh sách bình luận
     *
     * @param  Request  $request
     * @param  int  $id
     * @return Response
     */
    public function detail(Request $request, $id = 0)
    {
        $post         = Post::findOrFail($id);
        $data         = [];
        $data['type'] = $request->input('type', 'article');

        switch ($data['type']) {
            case 'article':
                $this->authorize('article.view', app(Comment::class));
                break;

            case 'event':
                $this->authorize('event.view', app(Comment::class));
                break;

            case 'voucher':
                $this->authorize('voucher.view', app(Comment::class));
                break;

            default:
                break;
        }
        $comments = $post->comments;

        $comments->load('user', 'comments', 'comments.user');

        $data['post']     = $post;
        $data['comments'] = $comments;
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
     * Lưu bản ghi
     *
     * @param  CommentRequest  $request
     * @param  int  $id
     * @return Response
     */
    public function save(CommentRequest $request, $id = 0)
    {
        $type = $request->input('type', 'article');
        switch ($type) {
            case 'article':
                $this->authorize('article.reply', app(Comment::class));
                break;

            case 'event':
                $this->authorize('event.reply', app(Comment::class));
                break;

            case 'voucher':
                $this->authorize('voucher.reply', app(Comment::class));
                break;

            default:
                break;
        }

        $input = $request->except(['_token']);

        $input['status']  = $request->input('status', 0);
        $input['content'] = strip_tags($request->input('content'));
        $input['user_id'] = Auth::user()->id;
        switch (Auth::user()->getTable()) {
            case 'user_partners':
                $input['user_type'] = 'partner';
                break;

            case 'b_o_customers':
                $input['user_type'] = 'customer';
                break;

            default:
                $input['user_type'] = 'user';
                break;
        }

        $comment = Comment::findOrNew($id);
        $comment->forceFill($input)->save();

        $post = $this->getPost($request);

        if ($request->type != 'feedback') {


            $this->savePostResponse($post);
        }
        $list_user_comment = Comment::where('post_id',$post->id)->select('user_id')->distinct()->get();
        $data_list = array();
        foreach ($list_user_comment as $lu){ $data_list[]= $lu['user_id'];}

        if($post->type == 'event'){
            $data_noti = [
                "message" => $request['content'],
                "user_id"=> $data_list,
                "title"=>$post->title.' có bình luận mới',
                'type'=>SendNotifyFCMService::NEW_POST_EVENT,
                'screen' => "EventSingle",
                "id" => $post->id
            ];
        }elseif ($post->type == 'voucher'){
            $data_noti = [
                "message" => $request['content'],
                "user_id"=> $data_list,
                "title"=>$post->title.' có bình luận mới',
                'type'=>SendNotifyFCMService::NEW_POST_VOUCHER,
                'screen' =>"VoucherSingle",
                "id" => $post->id
            ];
        }elseif ($post->type == 'article'){
            $data_noti = [
                "message" => $request['content'],
                "user_id"=> $data_list,
                "title"=>$post->title.' có bình luận mới',
                'type'=>SendNotifyFCMService::NEW_POST_ARTICLE,
                'screen' =>"PostSingle",
                "id" => $post->id
            ];
        }else{
            $data_noti = [
                "message" => $request['content'],
                "user_id"=> $data_list,
                "title"=>$post->title.'bạn có phản hồi mới',
                'type'=>SendNotifyFCMService::NEW_POST_FEEDBACK,
                'screen' =>"PetitionSingle",
                "id" => $post->id
            ];
        }   
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
        $countTokent = Fcm::getCountTokenbyUserId($data_list);     
        $total = ['email'=>0, 'app'=> $countTokent ?? 1, 'sms'=> 0];
        $campain = Campain::updateOrCreateCampain($data_noti['title'], $type_campain, $post->id, $total, $post->bdc_building_id, 0, 0);

        SendNotifyFCMService::setItemForQueueNotify(array_merge($data_noti,['building_id' =>$post->bdc_building_id,'campain_id' => $campain->id,'app'=>'v1']));
        $message = [
            'error'  => 0,
            'status' => 'success',
            'msg'    => 'Đã cập nhập thông tin',
        ];

        if ($request->ajax()) {
            $comment->load('user');

            $words = explode(' ', $comment->user->name);
            $name  = end($words);
            $char  = substr($name, 0, 1);

            $comment->username = $comment->user->name;
            $comment->char     = strtoupper($char);
            $comment->created  = $comment->created_at->diffForHumans(Carbon::now());

            $message['data'] = $comment;

            return response()->json($message);
        } else {
            return redirect()
                ->route('admin.comments.index', ['type' => $type])
                ->with('message', $message);
        }
    }

    protected function getPost($request)
    {
        $post_id = $request->post_id;

        if ($request->type == 'feedback') {
            $post = Feedback::where('id', $post_id)
                ->firstOrFail();
        } else {
            $post = Post::select(['id', 'type', 'response', 'title'])
                ->where('id', $post_id)
                ->firstOrFail();
        }
        return $post;
    }

    protected function savePostResponse(&$post)
    {
        $total = Comment::where([
            ['post_id', $post->id],
            ['type', $post->type],
        ])->count();

        $response = $post->response;

        $response['comment'] = $total;

        $post->response = $response;

        $post->save();

        return $post;
    }
}
