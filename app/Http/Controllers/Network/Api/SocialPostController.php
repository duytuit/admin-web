<?php

namespace App\Http\Controllers\Network\Api;

use App\Commons\Helper;
use App\Helpers\dBug;
use App\Http\Controllers\BuildingController;
use App\Models\Network\SocialComments;
use App\Models\Network\SocialReactions;
use App\Models\PublicUser\UserInfo as PublicUserUserInfo;
use App\Models\PublicUser\V2\UserInfo;
use App\Models\SocialPost;
use App\Models\SocialReaction;
use App\Repositories\Apartments\ApartmentsRespository;
use App\Repositories\Customers\CustomersRespository;
use App\Repositories\Network\SocialCommentsRepository;
use App\Repositories\Network\SocialPostsRepository;
use App\Repositories\Network\SocialReactionsRepository;
use App\Repositories\PublicUsers\PublicUsersProfileRespository;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class SocialPostController extends BuildingController
{
    /**
     * Constructor.
     */

    use ApiResponse;

    const DUPLICATE = 19999;
    const LOGIN_FAIL = 10000;
    private $model;
    private $modelPostReaction;
    private $modelApartment;
    private $userProfile;
    private $modelCustomer;
    private $modelSocialComment;

    public function __construct(Request $request, SocialCommentsRepository $modelSocialComment, SocialPostsRepository $socialPost, SocialReactionsRepository $modelPostReaction, PublicUsersProfileRespository $userProfile, ApartmentsRespository $modelApartment, CustomersRespository $modelCustomer)
    {
        $this->middleware('jwt_auth');
        $this->model = $socialPost;
        $this->modelPostReaction = $modelPostReaction;
        $this->modelApartment = $modelApartment;
        $this->modelCustomer = $modelCustomer;
        $this->userProfile = $userProfile;
        $this->modelSocialComment = $modelSocialComment;
        Carbon::setLocale('vi');
        parent::__construct($request, 'app');
    }

    /**
     * Danh sách các bản ghi nội bộ
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $data = [];
        $data_comment = [];
        $per_page = $request->input('per_page', 10);
        $per_page = $per_page > 0 ? $per_page : 10;
        $list_cus = [];
        $request->request->add(['building_id' => @$request->building_id ?? $this->building_active_id]);// thêm building id vao de xac nhan toa nha hien tai
        $_user = Auth::guard('public_user')->user();
        $user_id = @$_user->BDCprofile->id;
        if (!$user_id) {
            $_user = Auth::guard('public_user_v2')->user();
            $user_id = @$_user->id;
        }
        $list = $this->model->searchByApi($request, [], $per_page);
        foreach ($list as $item) {
            $post_reaction = $this->modelPostReaction->getEmo($user_id, $item->id);
            $reaction = $post_reaction ? $post_reaction->emotion : null;
            $user = null;

            if ($item->new == 1) {
                $user = UserInfo::where('user_id', $item->user_id)->first();
            } else {
                $user = PublicUserUserInfo::find($item->user_id);
            }
            $post_comment = SocialComments::where(['post_id' => $item['id']])->count();

            $rows = SocialReaction::select(DB::raw('emotion, COUNT(*) AS total'))
                ->where('post_id', $item->id)->groupBy('emotion')->get();

            $response['emotion'] = [
                'like' => 0,
                'love' => 0,
                'haha' => 0,
                'wow' => 0,
                'sad' => 0,
                'angry' => 0,
            ];

            foreach ($rows as $row) {

                $response['emotion'][$row['emotion']] = $row['total'];
            }


            $data[] = [
                'id' => $item->id,
                'content' => $item->content,
                'images' => json_decode($item->images, true),
                'response' => $response,
                'data_comment' => $data_comment ? $data_comment : null,
                'count_comment' => $post_comment,
                'reaction' => $reaction,
                'count_reaction' => SocialReactions::where('post_id', $item->id)->count(),
                'created_at' => (string)$item->created_at,
                'updated_at' => (string)$item->updated_at,
                'user' => [
                    'id' => @$user->id,
                    'name' => @$user->display_name ?? @$user->full_name,
                    'avatar' => @$user->avatar,
                ],
                'isLiked' => $reaction == null ? false : true,
                'status' => $item->status,
                'visible' => $item->visible
            ];

        }

        if ($data) {
            return $this->responseSuccess($data, 'Lấy dữ liệu thành công');
        }
        return response()->json(['status' => true, 'message' => 'không có dữ liệu', 'data' => []], 200);

    }

    public function getFloor($profile = [])
    {
        $result = $this->modelApartment->getFloorCustomerIds(\Auth::guard('public_user')->user()->id, $this->building_active_id);
        if (count($result) == 0) {
            return $this->responseError(["Không có căn hộ.(Code 0903)"], self::LOGIN_FAIL);
        }

        return $result->pluck('floor');
    }

    public function show(Request $request, $id)
    {

        $info = Auth::guard('public_user_v2')->user()->infoApp;

        $item = $this->model->getOne($id);

        if ($item) {
            $post_reaction = $this->modelPostReaction->getEmo($info['id'], $item->id);
            $item->reaction = $post_reaction ? $post_reaction->emotion : null;
            $user = null;
            if ($item->new == 1) {
                $user = UserInfo::where('user_id', $item->user_id)->first();;
            } else {
                $user = PublicUserUserInfo::find($item->user_id);
            }
            $data = [
                'id' => $item->id,
                'content' => $item->content,
                'images' => json_decode($item->images, true),
                'response' => json_decode($item->response, true),
                'reaction' => $item->reaction,
                'created_at' => $item->created_at,
                'updated_at' => $item->updated_at,
                'user' => [
                    'id' => @$user->id,
                    'name' => @$user->display_name ?? @$user->full_name,
                    'avatar' => @$user->avatar,
                ],
                'status' => $item->status,
                'visible' => $item->visible,
            ];
            return $this->responseSuccess($data, 'Lấy dữ liệu thành công');
        }
        return $this->responseError(['Không có dữ liệu.'], self::LOGIN_FAIL);
    }

    public function show_admin(Request $request, $id)
    {
        $info = Auth::guard('public_user')->user()->BDCprofile;
        $item = $this->model->getOne($id);
        $data_comment = [];
        if ($item) {
            $post_reaction = $this->modelPostReaction->getEmo($info['id'], $item->id);
            $item->reaction = $post_reaction ? $post_reaction->emotion : null;
            $user = null;
            if ($item->new == 1) {
                $user = UserInfo::where('user_id', $item->user_id)->first();;
            } else {
                $user = PublicUserUserInfo::find($item->user_id);
            }
            $post_comment = SocialComments::where(['post_id' => $item['id'], 'parent_id' => 0])->get();
            foreach ($post_comment as $item_comment) {
                $replies = [];
                $replies_list = SocialComments::where(['parent_id' => $item_comment->id])->get();
                if ($item_comment->new == 1) {
                    $user_comment = UserInfo::where('user_id', $item_comment->user_id)->first();;
                } else {
                    $user_comment = PublicUserUserInfo::find($item_comment->user_id);
                }
                foreach ($replies_list as $r) {
                    $replies[] = [
                        'id' => $r->id,
                        'content' => $r->content,
                        'created_at' => $r->created_at,
                        'updated_at' => $r->updated_at,
                        'user' => [
                            'id' => @$user_comment->id,
                            'name' => @$user_comment->display_name ?? @$user_comment->full_name,
                            'avatar' => @$user_comment->avatar,
                        ],
                    ];
                }
                $data_comment[] = [
                    'id' => $item_comment->id,
                    'content' => $item_comment->content,
                    'created_at' => $item_comment->created_at,
                    'updated_at' => $item_comment->updated_at,
                    'user' => [
                        'id' => @$user_comment->id,
                        'name' => @$user_comment->display_name ?? @$user_comment->full_name,
                        'avatar' => @$user_comment->avatar,
                    ],
                    'replies' => $replies
                ];
            }
            $data = [
                'id' => $item->id,
                'content' => $item->content,
                'images' => json_decode($item->images, true),
                'response' => json_decode($item->response, true),
                'comment' => $data_comment,
                'reaction' => $item->reaction,
                'created_at' => $item->created_at,
                'updated_at' => $item->updated_at,
                'user' => [
                    'id' => @$user->id,
                    'name' => @$user->display_name ?? @$user->full_name,
                    'avatar' => @$user->avatar,
                ],
                'status' => $item->status,
                'visible' => $item->visible,
            ];
            return $this->responseSuccess($data, 'Lấy dữ liệu thành công');
        }
        return response()->json(['status' => true, 'message' => 'không có dữ liệu', 'data' => []], 200);
    }

    public function save(Request $request, $id = null)
    {
        $info = \Auth::guard('public_user')->user()->BDCprofile;
        $input = $request->only(['content', 'images', 'visible']);
        $input['status'] = $request->input('status', 1);
        $input['visible'] = $request->input('visible', 0);
        $input['user_id'] = $info['id'];
        $files = $request->file('attached');
        $directory = 'media/social';
        $attached = [
            'images' => [],
            'files' => [],
        ];
        if ($request->hasFile('attached')) {

            foreach ($files as $file) {
                $ext = strtolower($file->getClientOriginalExtension());
                $rs_file = Helper::doUpload($file, $file->getClientOriginalName(), $directory);
                if (in_array($ext, ['jpeg', 'jpg', 'png', 'gif'])) {
                    $attached['images'][] = @$rs_file->origin ? @$rs_file->origin : [];
                } else {
                    $attached['files'][] = @$rs_file->origin ? @$rs_file->origin : [];
                }

            }
        }
        $input['images'] = $attached ? json_encode($attached) : json_encode(['images' => [], 'files' => []]);
        $input['bdc_building_id'] = $request->building_id;
        $query = $this->model->createSocial($input, $id);
        if ($query) {
            return $this->responseSuccess($query->toArray(), 'Cập nhật dữ liệu thành công');
        }
        return $this->responseError('Không cập nhật được dữ liệu.', self::LOGIN_FAIL);
    }

    public function save_v2(Request $request, $id = null)
    {
        $info = Auth::guard('public_user_v2')->user()->infoApp;
        $input = $request->only(['content', 'images', 'visible']);
        $input['status'] = $request->input('status', 1);
        $input['visible'] = $request->input('visible', 0);
        $input['user_id'] = $info['id'];
        $input['new'] = 1;
        $images = $request->input('images', []);
        if (empty($images)) {
            $images = $request->input('images[]', []);
        }
        $input['images'] = json_encode($images);
        $input['bdc_building_id'] = $this->building_active_id;
        $query = $this->model->createSocial($input, $id);
        if ($query) {
            return $this->responseSuccess($query->toArray(), 'Cập nhật dữ liệu thành công');
        }
        return $this->responseError('Không cập nhật được dữ liệu.', self::LOGIN_FAIL);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $id = (int)$request->id;

        $delete = $this->model->delete(['id' => $id]);
        if ($delete) {
            return $this->responseSuccess([], 'Xóa dữ liệu thành công');
        }
        return $this->responseError('Không xóa được dữ liệu.', self::LOGIN_FAIL);
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
