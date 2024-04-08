<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\SocialPost;
use App\Models\SocialReaction;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;

class SocialPostController extends Controller
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->model = new SocialPost();

        Carbon::setLocale('vi');
    }

    /**
     * Danh sách các bản ghi nội bộ
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $per_page = $request->input('per_page', 10);
        $per_page = $per_page > 0 ? $per_page : 10;

        $user = $this->getApiUser();
        $columns = $this->model->getTableColumns();

        $excludes = [
            'deleted_at',
        ];

        $allowFields = [];
        foreach ($columns as $column) {
            if (!in_array($column, $excludes)) {
                $allowFields[] = $column;
            }
        }

        $where[] = ['status', '=', 1];
        $select = $this->_select($request, $allowFields);
        $condition = $this->_filters($request, $columns);
        $order_by = $this->_sort($request, $columns);

        $items = $this->model
            ->select($select)
            ->where($where)
            ->where($condition)
            ->orderByRaw($order_by)
            ->paginate($per_page);

        foreach ($items as $item) {
            $post_id = $item->id;

            $post_reaction = SocialReaction::select('emotion')
                ->where('post_id', $post_id)
                ->where('user_id', $user->id)
                ->first();

            $item->reaction = $post_reaction ? $post_reaction->emotion : '';

            $result[] = $this->createStandardPostData($item);
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
        $user = $this->getApiUser();

        $item = $this->model->findOrFail($id);

        $post_reaction = SocialReaction::select('emotion')
            ->where('post_id', $item->id)
            ->where('user_id', $user->id)
            ->first();

        $item->reaction = $post_reaction ? $post_reaction->emotion : '';

        $result = $this->createStandardPostData($item);

        return $this->responseSuccess($result);
    }

    public function save(Request $request, $id = null)
    {
        //validate
        $validator = $this->validatePostData($request);

        if ($validator->fails()) {
            $error = $validator->errors();
            return $this->validateFail($error->first(), $error->toArray());
        }

        $user = $this->getApiUser();

        $columns = $this->model->getTableColumns();
        $input = $request->only($columns);
        $input['status'] = $request->input('status', 1);
        $input['user_id'] = $user->id;
        $images = $request->input('images', []);

        if ($id) {
            $owner = $this->isOwner($id);
            if ($owner['owner'] === false) {
                return $this->responseError($owner['message'], $owner['error_code']);
            }
            if ($request->has('images')) {
                $input['images'] = $images;
            }
        } else {
            $input['images'] = $images;
        }

        $item = $this->model::findOrNew($id);
        $item->fill($input)->save();

        if ($id) {
            $post_reaction = SocialReaction::select('emotion')
                ->where('post_id', $id)
                ->where('user_id', $user->id)
                ->first();

            $item->reaction = $post_reaction ? $post_reaction->emotion : '';
        }

        $result = $this->createStandardPostData($item);

        return $this->responseSuccess($result);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $id = (int)$request->id;

        $owner = $this->isOwner($id);
        if ($owner['owner'] === false) {
            return $this->responseError($owner['message'], $owner['error_code']);
        }

        $this->model->destroy($id);

        return $this->responseSuccess([], 'Delete success');
    }

    protected function validatePostData($request)
    {
        $rules = [
            'content' => 'required',
        ];
        $messages = [];
        $attributes = [];

        $validator = Validator::make($request->all(), $rules, $messages, $attributes);

        return $validator;
    }

    protected function createStandardPostData($record)
    {
        return [
            'id' => $record->id,
            'content' => $record->content,
            'images' => $record->images,
            'response' => $record->response,
            'reaction' => $record->reaction ?? '',
            'created_at' => $record->created_at,
            'updated_at' => $record->updated_at,
            'user' => [
                'user_id' => $record->user_id,
//                'user_type' => $this->user_type,
                'char' => $record->user->char,
                'name' => $record->user->name,
                'email' => $record->user->email ?? '',
                'phone' => $record->user->phone,
                'avatar' => $record->user->avatar ?? '',
            ],
        ];
    }

}
