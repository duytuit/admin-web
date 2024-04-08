<?php

namespace App\Http\Controllers\PostEmotion\Api;

use App\Http\Controllers\BuildingController;
use App\Http\Controllers\Posts\Api\PostsController;
use App\Http\Resources\PostResource;
use App\Models\BoCustomer;
use App\Models\Comment;
use App\Models\CustomerGroup;
use App\Models\Fcm;
use App\Models\Post;
use App\Models\PostEmotion;
use App\Models\PostFollow;
use App\Models\PostPoll;
use App\Models\PostRegister;
use App\Models\PostVote;
use App\Models\PublicUser\UserInfo;
use App\Repositories\Apartments\ApartmentsRespository;
use App\Repositories\Comments\CommentsRespository;
use App\Repositories\PostEmotion\PostEmotionRepository;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;

class PostEmotionController extends BuildingController
{
    use ApiResponse;
    /**
     * Constructor.
     */
    const DUPLICATE = 19999;
    const LOGIN_FAIL = 10000;
    private $model;

    public function __construct(
        Request $request,
        PostEmotionRepository $model
    )
    {
        $this->model = $model;
        //$this->middleware('jwt.auth');
        parent::__construct($request);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'post_id' => 'required',
            'emotion' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->validateFail($validator->errors());
        }

        if( !in_array($request->input('emotion'), $this->model::EMOTION) ) {
            return $this->responseError(['Biểu cảm không hợp lệ'], 204);
        }

        $info = \Auth::guard('public_user')->user()->info;


        if( !$info ) {
            return $this->responseError(['Không lấy được thông tin User'], self::LOGIN_FAIL);
        }
        $check_emo = $this->model->findBy('user_id',$info->id);

        if(!$check_emo){
            $data['post_id']   = $request->input('post_id');
            $data['user_id']   = $info->id;
            $data['emotion']   = $request->input('emotion');
            $data['user_type'] = $info->type;
            $data['user_name'] = $info->display_name;
            $data['post_type'] = 'article';
            $post_emotion      = $this->model->create($data);

            if( !$post_emotion ) {
                return $this->responseError(['Thêm mới cảm xúc không thành công'], 204);
            }

            app('App\Http\Controllers\Posts\Api\PostsController')->updateResponse($request->post_id, $request->emotion);
            return $this->responseSuccess([], 'Thêm cảm xúc thành công', 200);

        }
        $this->model->deleteEmo($request->input('post_id'),$info->id);

        app('App\Http\Controllers\Posts\Api\PostsController')->updateResponse($request->post_id,'', $request->emotion);

        return $this->responseSuccess([], 'Xóa cảm xúc thành công', 200);

    }
    public function store_v2(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'post_id' => 'required',
            'emotion' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->validateFail($validator->errors());
        }

        if( !in_array($request->input('emotion'), $this->model::EMOTION) ) {
            return $this->responseError(['Biểu cảm không hợp lệ'], 204);
        }

        $info = Auth::guard('public_user_v2')->user()->infoApp;


        if( !$info ) {
            return $this->responseError(['Không lấy được thông tin User'], self::LOGIN_FAIL);
        }
        $check_emo = $this->model->findBy('user_id',$info->id);

        if(!$check_emo){
            $data['post_id']   = $request->input('post_id');
            $data['user_id']   = $info->id;
            $data['new']       = 1;
            $data['emotion']   = $request->input('emotion');
            $data['user_type'] = $info->type;
            $data['user_name'] = $info->display_name;
            $data['post_type'] = 'article';
            $post_emotion      = $this->model->create($data);

            if( !$post_emotion ) {
                return $this->responseError(['Thêm mới cảm xúc không thành công'], 204);
            }

            app('App\Http\Controllers\Posts\Api\PostsController')->updateResponse($request->post_id, $request->emotion);
            return $this->responseSuccess([], 'Thêm cảm xúc thành công', 200);

        }
        $this->model->deleteEmo($request->input('post_id'),$info->id);

        app('App\Http\Controllers\Posts\Api\PostsController')->updateResponse($request->post_id,'', $request->emotion);

        return $this->responseSuccess([], 'Xóa cảm xúc thành công', 200);

    }
    public function delete_v2(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'post_id' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->validateFail($validator->errors());
        }

        $info = Auth::guard('public_user_v2')->user()->infoApp;

        if( !$info ) {
            return $this->responseError(['Không lấy được thông tin User'], self::LOGIN_FAIL);
        }
        $previous_emotion = $this->model->find($id)->emotion;
        $delete           = $this->model->delete(['id' => $id]);

        if( !$delete ) {
            return $this->responseError(['Xóa cảm xúc không thành công'], 204);
        }

        app('App\Http\Controllers\Posts\Api\PostsController')->updateResponse($request->post_id, '', $previous_emotion);
        return $this->responseSuccess(['Xóa cảm xúc thành công'], 'Success', 200);
    }
    public function delete(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'post_id' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->validateFail($validator->errors());
        }

        $info = \Auth::guard('public_user')->user()->info;

        if( !$info ) {
            return $this->responseError(['Không lấy được thông tin User'], self::LOGIN_FAIL);
        }
        $previous_emotion = $this->model->find($id)->emotion;
        $delete           = $this->model->delete(['id' => $id]);

        if( !$delete ) {
            return $this->responseError(['Xóa cảm xúc không thành công'], 204);
        }

        app('App\Http\Controllers\Posts\Api\PostsController')->updateResponse($request->post_id, '', $previous_emotion);
        return $this->responseSuccess(['Xóa cảm xúc thành công'], 'Success', 200);
    }

    public function update_v2(Request $request, $id = 0)
    {
        $validator = Validator::make($request->all(), [
            'post_id' => 'required',
            'emotion' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->validateFail($validator->errors());
        }

        if (!in_array($request->input('emotion'), $this->model::EMOTION)) {
            return $this->responseError(['Biểu cảm không hợp lệ'], 204);
        }

        $info = Auth::guard('public_user_v2')->user()->infoApp;
        if (!$info) {
            return $this->responseError(['Không lấy được thông tin User'], self::LOGIN_FAIL);
        }

        $data['emotion']  = $request->input('emotion');
        $previous_emotion = $this->model->find($id)->emotion;
        $update = $this->model->update($data, $id);
        if (!$update) {
            return $this->responseError(['Cập nhật cảm xúc không thành công'], 204);
        }

        app('App\Http\Controllers\Posts\Api\PostsController')->updateResponse($request->post_id, $request->emotion, $previous_emotion);
        return $this->responseSuccess(['Cập nhật cảm xúc thành công'], 'Success', 200);
    }

    public function update(Request $request, $id = 0)
    {
        $validator = Validator::make($request->all(), [
            'post_id' => 'required',
            'emotion' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->validateFail($validator->errors());
        }

        if( !in_array($request->input('emotion'), $this->model::EMOTION) ) {
            return $this->responseError(['Biểu cảm không hợp lệ'], 204);
    }

        $info = \Auth::guard('public_user')->user()->info;
        if( !$info ) {
            return $this->responseError(['Không lấy được thông tin User'], self::LOGIN_FAIL);
        }

        $data['emotion']  = $request->input('emotion');
        $previous_emotion = $this->model->find($id)->emotion;
        $update = $this->model->update($data, $id);
        if( !$update ) {
            return $this->responseError(['Cập nhật cảm xúc không thành công'], 204);
        }

        app('App\Http\Controllers\Posts\Api\PostsController')->updateResponse($request->post_id, $request->emotion, $previous_emotion);
        return $this->responseSuccess(['Cập nhật cảm xúc thành công'], 'Success', 200);
    }
    public function getPostEmotion_v2(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'post_id' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->validateFail($validator->errors());
        }

        $info = Auth::guard('public_user_v2')->user()->infoApp;
        if( !$info ) {
            return $this->responseError(['Không lấy được thông tin User'], self::LOGIN_FAIL);
        }

        $emotion = $this->model->findEmotion($request->post_id, $info->id);

        if( !$emotion ) {
            return $this->responseError('Người dùng chưa thể hiện cảm xúc cho bài viết này', 204);
        }

        $data['emotion'] = $emotion->emotion;
        return $this->responseSuccess($data, 'Success', 200);

    }
    public function getPostEmotion(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'post_id' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->validateFail($validator->errors());
        }

        $info = \Auth::guard('public_user')->user()->info;
        if( !$info ) {
            return $this->responseError(['Không lấy được thông tin User'], self::LOGIN_FAIL);
        }

        $emotion = $this->model->findEmotion($request->post_id, $info->id);

        if( !$emotion ) {
            return $this->responseError('Người dùng chưa thể hiện cảm xúc cho bài viết này', 204);
        }

        $data['emotion'] = $emotion->emotion;
        return $this->responseSuccess($data, 'Success', 200);

    }
}