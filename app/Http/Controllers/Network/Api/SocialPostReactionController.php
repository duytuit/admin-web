<?php

namespace App\Http\Controllers\Network\Api;

use App\Helpers\dBug;
use App\Http\Controllers\BuildingController;
use App\Models\Network\SocialPost;
use App\Models\SocialReaction;
use App\Repositories\Network\SocialPostsRepository;
use App\Repositories\Network\SocialReactionsRepository;
use App\Repositories\PublicUsers\PublicUsersProfileRespository;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class SocialPostReactionController extends BuildingController
{
    use ApiResponse;
    const DUPLICATE = 19999;
    const LOGIN_FAIL = 10000;
    private $model;
    private $modelPostReaction;
    private $userProfile;

    public function __construct(Request $request,SocialPostsRepository $socialPost,SocialReactionsRepository $modelPostReaction,PublicUsersProfileRespository $userProfile)
    {
        $this->model = $socialPost;
        $this->modelPostReaction = $modelPostReaction;
        $this->userProfile = $userProfile;
        Carbon::setLocale('vi');
    }

    public function add(Request $request)
    {
        //validate
        $validator = $this->validateReactionData($request);
        if ($validator->fails()) {
            $error = $validator->errors();
            return $this->validateFail($error->first(), $error->toArray());
        }
        return $this->reaction($request, true);
    }
    public function add_admin(Request $request)
    {
        //validate
        $validator = $this->validateReactionData($request);
        if ($validator->fails()) {
            $error = $validator->errors();
            return $this->validateFail($error->first(), $error->toArray());
        }
        return $this->reaction_admin($request, true);
    }
    protected function reaction_admin(Request $request, $insert = true)
    {
        $emotion = $request->emotion;
        $post_id = $request->post_id;
        $info = Auth::guard('public_user')->user()->BDCprofile;
        $social_post = $this->getSocialPost($post_id);
        $post_reaction = $this->modelPostReaction->getEmo($info['id'],$social_post->id);

        // delete emotion if exist
        $this->delete_admin($social_post->id, @$info->id);
        // insert emotion

        if ($insert) {
            $data = [
                'post_id' => $social_post->id,
                'user_id' => $info['id'],
                'emotion' => $emotion,
                'new' => 1,
            ];
            $this->modelPostReaction->insertRow($data);
        }
        
        $social_post = $this->saveSocialPostResponse($social_post);

        $item = $social_post->toArray();

        $item['updated_at'] = $social_post->updated_at;
        if ($item) {
            return $this->responseSuccess($item,'Lấy dữ liệu thành công');
        }
        return $this->responseError(['Không có dữ liệu.'], self::LOGIN_FAIL);
    }
    public function remove(Request $request)
    {
        return $this->reaction($request, false);
    }

    protected function reaction(Request $request, $insert = true)
    {
        $emotion = $request->emotion;
        $post_id = $request->post_id;
        $info = Auth::guard('public_user_v2')->user()->BDCprofileApp;
        $social_post = $this->getSocialPost($post_id);

        $post_reaction = $this->modelPostReaction->getEmo($info['id'],$social_post->id);

        // delete emotion if exist
        $this->delete($social_post->id, $info['id']);
        // insert emotion

        if ($insert) {
            $data = [
                'post_id' => $social_post->id,
                'user_id' => $info['id'],
                'emotion' => $emotion,
                'new' => 1,
            ];
            $this->modelPostReaction->insertRow($data);
        }
        
        $social_post = $this->saveSocialPostResponse($social_post);

        $item = $social_post->toArray();

        $item['updated_at'] = $social_post->updated_at;
        if ($item) {
            return $this->responseSuccess($item,'Lấy dữ liệu thành công');
        }
        return $this->responseError(['Không có dữ liệu.'], self::LOGIN_FAIL);
    }
    protected function delete_admin($post_id)
    {
        $info = Auth::guard('public_user')->user()->BDCprofile;
        $del = SocialReaction::where([
            ['post_id', $post_id],
            ['user_id', $info['id']],
        ])->delete();
        $social_post = $this->getSocialPost($post_id);
        $social_post = $this->saveSocialPostResponse($social_post);
        if ($del>0) {
            return $this->responseSuccess([],'Xóa dữ liệu thành công');
        }
        return response()->json(['status'=>true,'message'=>'không có dữ liệu' ,'data'=>[]], 200);
    }
    protected function delete($post_id)
    {
        $info = Auth::guard('public_user_v2')->user()->BDCprofileApp;
        $del = SocialReaction::where([
            ['post_id', $post_id],
            ['user_id', $info['id']],
        ])->delete();
        $social_post = $this->getSocialPost($post_id);
        $social_post = $this->saveSocialPostResponse($social_post);
        if ($del>0) {
            return $this->responseSuccess([],'Xóa dữ liệu thành công');
        }
        return $this->responseError(['Không có dữ liệu.'], self::LOGIN_FAIL);
    }

    protected function getSocialPost($post_id)
    {
        
        $social_post = SocialPost::where('id', $post_id)->first();

        return $social_post;
    }

    protected function saveSocialPostResponse(&$social_post)
    {
        $rows = SocialReaction::select(DB::raw('emotion, COUNT(*) AS total'))
        ->where('post_id', $social_post->id)->groupBy('emotion')->get();

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

        $social_post->response = json_encode( $response);

        $social_post->save();

        return $social_post;
    }

    protected function validateReactionData($request)
    {
        $rules = [
            'emotion' => [
                'required',
                Rule::in(['like', 'love', 'haha', 'wow', 'sad', 'angry']),
            ],
        ];
        $messages = [];
        $attributes = [];

        $validator = Validator::make($request->all(), $rules, $messages, $attributes);

        return $validator;
    }
}
