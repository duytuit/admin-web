<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\dBug;
use App\Http\Resources\CommentResource;
use App\Models\Comment;
use App\Models\Campain;
use App\Models\Fcm;
use App\Models\Post;
use App\Models\Feedback;
use App\Models\SentStatus;
use App\Services\FCM\SendNotifyFCMService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

class CommentController extends Controller
{

    /**
     * Construct
     */
    public function __construct()
    {
        $this->model    = new Comment();
        $this->resource = new CommentResource(null);

        Carbon::setLocale('vi');
    }

    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $post_id  = $request->post_id;
        $type     = $request->input('type', 'article');
        $per_page = $request->input('per_page', 10);
        $per_page = $per_page > 0 ? $per_page : 10;

        $columns = $this->model->getTableColumns();

        $excludes = ['deleted_at'];

        foreach ($columns as $column) {
            if (!in_array($column, $excludes)) {
                $allowFields[] = $column;
            }
        }

        $where   = [['status', 1]];
        $where[] = ['post_id', '=', $post_id];
        $where[] = ['parent_id', '=', 0];

        $select    = $this->_select($request, $allowFields);
        $condition = $this->_filters($request, $columns);
        $order_by  = $this->_sort($request, $columns);

        $items = $this->model->select($select)
            ->where($where)
            ->where($condition)
            ->orderByRaw($order_by)
            ->paginate($per_page);

        $items->load('user', 'comments', 'comments.user');

        return $this->resource->many($items);
    }

    /**
     * Save a resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function save(Request $request)
    {
        $user = $this->getApiUser();

        $id    = (int) $request->id;
        $type  = $request->input('type', 'article');
        $input = $request->except(['_token']);
        $aa = $request;
        $input['status']    = $request->input('status', 0);
        $input['content']   = strip_tags($request->input('content'));
        $input['user_id']   = $user->id;
        $input['user_type'] = $user->type;
        $input['post_id']   = $request->post_id;
        $post = $this->getPost($request);
        if($request->type != 'feedback'){
            $input['type']   = $post->type;
        }


        $item = Comment::findOrNew($id);
        $item->forceFill($input)->save();

        $item->load('user');

        $item->char     = $item->user->char;
        $item->username = $item->user->name;
        $item->created  = $item->created_at->diffForHumans(Carbon::now());


        if ($request->type != 'feedback') {
            $this->savePostResponse($post);
        }
//        Cache::store('redis')->put('trung', $post);
        $list_user_comment = Comment::where('post_id',$post->id)->select('user_id')->distinct()->get();
        $data_list = array();
        foreach ($list_user_comment as $lu){ 
            $data_list[]= $lu['user_id'];
        }
        dBug::trackingPhpErrorV2('commnent__'.json_encode($request->all()));
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
                'type'=>SendNotifyFCMService::NEW_POST_ARTICLE,
                'screen' =>"PetitionSingle",
                "id" => $post->id
            ];
        }  
        // ????
        $countTokent = Fcm::getCountTokenbyUserId($data_list);
        $total = ['email'=>0, 'app'=> $countTokent ?? 1, 'sms'=> 0];
        $campain = Campain::updateOrCreateCampain($data_noti['title'], config('typeCampain.POST_NEWS'), $post->id, $total, $post->bdc_building_id, 0, 0);

        SendNotifyFCMService::setItemForQueueNotify(array_merge($data_noti,['building_id' =>$post->bdc_building_id,'campain_id' => $campain->id,'app'=>'v1']));

        return $this->resource->one($item);
    }

    protected function getPost($request)
    {
        $post_id = $request->post_id;

        if ($request->type == 'feedback') {
            $post = Feedback::where('id', $post_id)
                ->firstOrFail();
        } else {
            $post = Post::select(['id', 'type', 'response','title'])
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
//        Cache::store('redis')->put('trung', $total);
        $post->response = $response;

        $post->save();

        return $post;
    }
}
