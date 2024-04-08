<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Api\V1\Controller;
use App\Http\Resources\CommentResource;
use App\Models\Comment;
use App\Models\Feedback;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use JWTAuth;

class CommentController extends Controller
{

    /**
     * Construct
     */
    protected $user;

    public function __construct()
    {
        $this->model    = new Comment();
        $this->resource = new CommentResource(null);
        $this->user     = Auth::user();

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

        $where = [
            ['status', 1],
            ['post_id', '=', $post_id],
            ['parent_id', '=', 0],
        ];

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
        $token = JWTAuth::getToken();
        $user  = JWTAuth::toUser($token);

        $id    = (int) $request->id;
        $type  = $request->input('type', 'article');
        $input = $request->only('content', 'parent_id', 'status');

        $input['status']    = $request->input('status', 0);
        $input['content']   = strip_tags($request->input('content'));
        $input['user_id']   = $user->id;
        $input['user_type'] = 'customer';
        $input['post_id']   = $request->post_id;

        $item = Comment::findOrNew($id);
        $item->forceFill($input)->save();

        $item->load('user');

        $item->char     = $item->user->char;
        $item->username = $item->user->name;
        $item->created  = $item->created_at->diffForHumans(Carbon::now());

        $post = $this->getPost($request);
        if ($request->type != 'feedback') {
            $this->savePostResponse($post);
        }

        return $this->resource->one($item);
    }

    protected function getPost($request)
    {
        $post_id = $request->post_id;

        if ($request->type == 'feedback') {
            $post = Feedback::where('id', $post_id)
                ->firstOrFail();
        } else {
            $post = Post::select(['id', 'type', 'response'])
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
