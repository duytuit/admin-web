<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Api\V1\Controller;
use App\Http\Resources\PostResource;
use App\Models\Post;
use App\Models\PostEmotion;
use App\Models\PostFollow;
use App\Models\PostPoll;
use App\Models\PostRegister;
use App\Models\PostVote;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class PostController extends Controller
{
    /**
     * Constructor.
     */
    protected $user;

    public function __construct()
    {
        $this->model    = new Post();
        $this->resource = new PostResource(null);
        $this->user     = Auth::user();

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
        // $expire    = 15;
        // $cache_key = 'api_posts_' . $scope . '_' . md5(serialize($request->all()));

        $user = $this->user;

        $type = $request->input('type', '');
        // kind =  normal/pin/slide => News/voucher/event is nomal/pin (only last ONE)/slide(show)
        $kind     = $request->input('kind', '');
        $per_page = $request->input('per_page', 10);
        $per_page = $per_page > 0 ? $per_page : 10;

        $columns = $this->model->getTableColumns();

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
        $where[] = ['app_id', '=', $user->app_id];
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
            $where[] = ['private', '=', 1];
        }

        // Lấy tất cả
        if ($scope == 'all') {
            $where[] = [
                function ($query) use ($user) {
                    $query->orWhereRaw("JSON_CONTAINS(`notify`, " . (int) $user->id . ", '$.customer_ids')")
                        ->orWhereRaw("JSON_CONTAINS(`notify`, '[]', '$.customer_ids')");
                    if (is_array($user->group_id)) {
                        foreach ($user->group_id as $group_id) {
                            $query->orWhereRaw("JSON_CONTAINS(`notify`, " . (int) $group_id . ", '$.customer_ids')");
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

        $items = $this->model->select($select)
            ->where($where)
            ->where($condition)
            ->orderByRaw($order_by)
            ->paginate($per_page);

        $items->load('category');

        $items     = $this->isRegistered($items);
        $items     = $this->isCheckIn($items);
        $new_items = collect([]);

        $registered = !!$request->input('registered', false);

        if ($registered) {
            foreach ($items as $value) {
                if ($value->registered === $registered) {
                    $new_items->push($value);
                }
            }
        } else {
            $new_items = $items;
        }

        // $resource = $this->resource->many($new_items);

        // Cache::put($cache_key, $resource, $expire);

        // return $resource;

        return response()->json(['data' => $new_items]);
    }

    protected function isRegistered($posts)
    {
        $user = $this->user;

        if ($user) {
            $post_ids = $posts->pluck('id')->toArray();

            $register = PostRegister::select('post_id')
                ->whereIn('post_id', $post_ids)
                ->where('user_id', $user->id)
                ->where('user_type', $user->type)
                ->pluck('post_id')
                ->toArray();

        } else {
            $register = [];
        }

        $items = collect([]);

        foreach ($posts as $post) {
            $post->registered = in_array($post->id, $register);
            $items->push($post);
        }

        return $items;
    }

    protected function isCheckIn($posts)
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
        $id    = (int) $request->id;
        $where = [
            ['status', '=', 1],
            ['id', '=', $id],
        ];

        try {
            $item   = $this->model->where($where);
            $option = $item->pollOptions();

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

            return $this->resource->one($item);
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
        $user = $this->user;

        $item = PostEmotion::select('emotion')
            ->where('post_id', $post_id)
            ->where('user_id', $user->id)
            ->first();

        if ($item) {
            return $item->emotion;
        }

        return null;
    }

    protected function getFollow($post_id)
    {
        $user = $this->user;

        $item = PostFollow::select('id')
            ->where('post_id', $post_id)
            ->where('user_id', $user->id)
            ->first();

        if ($item) {
            return true;
        }

        return false;
    }
    protected function getRegister($post_id)
    {
        $user = $this->user;

        $item = PostRegister::select('id')
            ->where('post_id', $post_id)
            ->where('user_id', $user->id)
            ->first();

        if ($item) {
            return true;
        }

        return false;
    }

    protected function getCode($post_id)
    {
        $user = $this->user;
        $item = PostRegister::select('id', 'code')
            ->where('post_id', $post_id)
            ->where('user_id', $user->id)
            ->first();

        if ($item) {
            return $item->code;
        }

        return null;
    }

    protected function getCheckIn($post_id)
    {
        $user = $this->user;

        $item = PostRegister::select('id')
            ->where('post_id', $post_id)
            ->where('user_id', $user->id)
            ->whereNotNull('check_in')
            ->first();

        if ($item) {
            return true;
        }

        return false;
    }

    protected function getVote($post_id)
    {
        $user = $this->user;

        $item = PostVote::select('rating')
            ->where('post_id', $post_id)
            ->where('user_id', $user->id)
            ->first();

        if ($item) {
            return $item->rating;
        }

        return null;
    }

    protected function getPoll($post_id)
    {
        $user = $this->user;

        $post_polls = PostPoll::select('poll_key')
            ->where('post_id', $post_id)
            ->where('user_id', $user->id)
            ->first();

        if ($post_polls) {
            return $post_polls->poll_key;
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

        $item    = Post::findOrNew($id);
        $updated = $item->fill($input)->save();

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
            $updated      = $item->save();
        }

        if ($updated) {
            return response()->json([
                'success' => true,
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Xin lỗi! Cập nhật thất bại.',
            ], 500);
        }
    }
}
