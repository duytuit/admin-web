<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\SocialComment;
use App\Models\SocialPost;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;

class SocialCommentController extends Controller
{

    /**
     * Construct
     */
    public function __construct()
    {
        $this->model = new SocialComment();

        Carbon::setLocale('vi');
    }

    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $post_id = $request->post_id;
        $per_page = $request->input('per_page', 10);
        $per_page = $per_page > 0 ? $per_page : 10;

        $columns = $this->model->getTableColumns();

        $excludes = ['deleted_at'];

        foreach ($columns as $column) {
            if (!in_array($column, $excludes)) {
                $allowFields[] = $column;
            }
        }

        $where = [['post_id', '=', $post_id]];
        $where[] = ['parent_id', '=', 0];

        $select = $this->_select($request, $allowFields);
        $condition = $this->_filters($request, $columns);
        $order_by = $this->_sort($request, $columns);

        $items = $this->model->select($select)
            ->where($where)
            ->where($condition)
            ->orderByRaw($order_by)
            ->paginate($per_page);

//        $items->load('user', 'comments', 'comments.user');

        $result = [];

        foreach ($items as $key => $value) {
            $result[] = $this->createStandardCommentData($value);
        }

        $extra_data = [
            'pagination' => [
                'total' => $items->total(),
                'last_page' => $items->lastPage(),
                'per_page' => $items->perPage(),
                'current_page' => $items->currentPage(),
            ]
        ];

        return $this->responseSuccess($result, '', 200, $extra_data);
    }

    public function show(Request $request)
    {
        $id = (int)$request->id;

        $item = $this->model->findOrFail($id);

        $result = $this->createStandardCommentData($item);

        return $this->responseSuccess($result);
    }

    /**
     * Save a resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function save(Request $request)
    {
        //validate
        $validator = $this->validateCommentData($request);

        if ($validator->fails()) {
            $error = $validator->errors();
            return $this->validateFail($error->first(), $error->toArray());
        }

        $user = $this->getApiUser();

        $id = (int)$request->id;
        $columns = $this->model->getTableColumns();
        $input = $request->only($columns);

        $input['content'] = strip_tags($request->input('content'));
        $input['user_id'] = $user->id;
        $input['user_type'] = $user->type;
        $input['post_id'] = $request->post_id;

        if ($id) {
            $owner = $this->isOwner($id);
            if ($owner['owner'] === false) {
                return $this->responseError($owner['message'], $owner['error_code']);
            }
        } else {
            $input['parent_id'] = $request->input('reply_to', '0');
            unset($input['reply_to']);
        }

        //TODO : thêm chức năng gửi ảnh cho comment
        $item = $this->model::findOrNew($id);
        $item->fill($input)->save();

        $this->savePostResponse($request->post_id);

        $result = $this->createStandardCommentData($item);

        return $this->responseSuccess($result);
    }

    public function delete(Request $request)
    {
        $id = (int)$request->id;
        $owner = $this->isOwner($id);
        if ($owner['owner'] === false) {
            return $this->responseError($owner['message'], $owner['error_code']);
        }

        $this->model->destroy($id);

        return $this->responseSuccess([], 'Delete success');
    }

    protected function validateCommentData($request)
    {
        $rules = [
            'content' => 'required',
        ];
        $messages = [];
        $attributes = [];

        $validator = Validator::make($request->all(), $rules, $messages, $attributes);

        return $validator;
    }

    protected function savePostResponse($post_id)
    {
        $post = SocialPost::select(['id', 'response'])
            ->where('id', $post_id)
            ->firstOrFail();

        $total = SocialComment::where([
            ['post_id', $post->id],
        ])->count();

        $response = $post->response;

        $response['comment'] = $total;

        $post->response = $response;

        $post->save();

        return $post;
    }

    protected function createStandardCommentData($record)
    {
        $replies = [];
        if (!$record->comments->isEmpty()) {
            foreach ($record->comments as $sub_comment) {
                $replies[] = $this->createStandardCommentData($sub_comment);
            }
        }

        $comment = [
            'id' => $record->id,
            'content' => $record->content,
//            'files' => $record->files,
            'created_at' => $record->created_at,
            'updated_at' => $record->updated_at,
            'user' => [
                'user_id' => $record->user_id,
//                'user_type' => $comment->user_type,
                'char' => $record->user->char,
                'name' => $record->user->name,
                'email' => $record->user->email ?? '',
                'phone' => $record->user->phone,
                'avatar' => $record->user->avatar ?? '',
            ],
        ];
        $comment['replies'] = $replies;

        return $comment;
    }
}
