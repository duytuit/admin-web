<?php

namespace App\Http\Controllers\Network\Api;

use App\Commons\Helper;
use App\Helpers\dBug;
use App\Http\Controllers\BuildingController;
use App\Models\Network\SocialReactions;
use App\Models\PublicUser\UserInfo as PublicUserUserInfo;
use App\Models\PublicUser\V2\UserInfo;
use App\Models\SocialComment;
use App\Models\SocialPost;
use App\Repositories\Network\SocialCommentsRepository;
use App\Repositories\Network\SocialPostsRepository;
use App\Repositories\Network\SocialReactionsRepository;
use App\Repositories\PublicUsers\PublicUsersProfileRespository;
use App\Traits\ApiResponse;
use App\Util\Debug\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class SocialCommentController extends BuildingController
{

    /**
     * Construct
     */
    use ApiResponse;
    const DUPLICATE = 19999;
    const LOGIN_FAIL = 10000;
    private $model;
    private $modelPostReaction;
    private $modelSocialComment;
    private $userProfile;

    public function __construct(Request $request,SocialPostsRepository $socialPost,SocialReactionsRepository $modelPostReaction,SocialCommentsRepository $modelSocialComment,PublicUsersProfileRespository $userProfile)
    {
        $this->model = $socialPost;
        $this->modelPostReaction = $modelPostReaction;
        $this->modelSocialComment = $modelSocialComment;
        $this->userProfile = $userProfile;
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
        $data=[];
        $per_page = $request->input('per_page', 10);
        $per_page = $per_page > 0 ? $per_page : 10;
        $list = $this->modelSocialComment->searchByApi( $request, [], $per_page);
        foreach ($list as $item){
            $replies = [];
            $replies_list = $this->modelSocialComment->getReply($request->post_id,$item->id);
            $user = null;
            if($item->new == 1){
                $user = UserInfo::where('user_id',$item->user_id)->first();
            }else{
                $user = PublicUserUserInfo::find($item->user_id);
            }
            foreach ($replies_list as $r){
                $user_1 = null;
                if($r->new == 1){
                    $user_1 =UserInfo::where('user_id',$r->user_id)->first();
                }else{
                    $user_1 = PublicUserUserInfo::find($r->user_id);
                }
                $replies[]=[
                    'id'=>$r->id,
                    'content'=>$r->content,
                    'files'=> $r->files,
                    'created_at'=>(string)$r->created_at,
                    'updated_at'=>(string)$r->updated_at,
                    'user' => [
                        'id'=>@$user_1->id,
                        'name'=>@$user_1->display_name ?? @$user_1->full_name,
                        'avatar'=>@$user_1->avatar,
                    ],
                ];
            }
            $data[]= [
                'id'=>$item->id,
                'content'=>$item->content,
                'files'=> $item->files,
                'created_at'=>(string)$item->created_at,
                'updated_at'=>(string)$item->updated_at,
                'user' => [
                    'id'=>@$user->id,
                    'name'=>@$user->display_name ?? @$user->full_name,
                    'avatar'=>@$user->avatar,
                ],
                'replies' =>$replies
            ];
        }
        if ($data) {
            return $this->responseSuccess($data,'Lấy dữ liệu thành công');
        }
        return response()->json(['status'=>true,'message'=>'không có dữ liệu' ,'data'=>[]], 200);
    }

    public function show(Request $request)
    {
        $data = [];
        $id = (int)$request->id;
        $item = $this->modelSocialComment->getOneByPost($id,(int)$request->post_id);
        if($item){
            $replies = [];
            $replies_list = $this->modelSocialComment->getReply($request->post_id,$item->id);
            foreach ($replies_list as $r){
                $user_1 = null;
                if($r->new == 1){
                    $user_1 =UserInfo::where('user_id',$r->user_id)->first();
                }else{
                    $user_1 = PublicUserUserInfo::find($r->user_id);
                }
                $replies[]=[
                    'id'=>$r->id,
                    'content'=>$r->content,
                    'created_at'=>$r->created_at,
                    'updated_at'=>$r->updated_at,
                    'user' => [
                        'id'=>@$user_1->id,
                        'name'=>@$user_1->display_name ?? @$user_1->full_name,
                        'avatar'=>@$user_1->avatar,
                    ],
                ];
            }
            $user = null;
            if($item->new == 1){
                $user = UserInfo::where('user_id',$item->user_id)->first();;
            }else{
                $user = PublicUserUserInfo::find($item->user_id);
            }
            $data=[
                'id'=>$item->id,
                'content'=>$item->content,
                'created_at'=>(string)$item->created_at,
                'updated_at'=>(string)$item->updated_at,
                'count_reaction'=> SocialReactions::where('post_id',$request->post_id)->count(),
                'user' => [
                    'id'=>@$user->id,
                    'name'=>@$user->display_name ?? @$user->full_name,
                    'avatar'=>@$user->avatar,
                ],
                'replies'=>$replies
            ];
            return $this->responseSuccess($data,'Lấy dữ liệu thành công');
        }
        return response()->json(['status'=>true,'message'=>'không có dữ liệu' ,'data'=>[]], 200);
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
        $info = Auth::guard('public_user_v2')->user()->BDCprofileApp;

        $id = (int)$request->id;

        $input['content'] = strip_tags($request->input('content'));
        $input['user_id'] = $info['id'];
        $input['new'] = 1;
        $input['user_type'] = 'user';
        $input['post_id'] = $request->post_id;
        $input['parent_id'] = $request->input('reply_to', '0');
        unset($input['reply_to']);

        //TODO : thêm chức năng gửi ảnh cho comment
        $item = $this->modelSocialComment->createSocialComments($input,$id);


        $this->savePostResponse($request->post_id);

        if ($item) {
            if($id){
                return $this->responseSuccess([],'Sửa bình luận thành công');
            }
            return $this->responseSuccess([],'Bình luận thành công');
        }
        if($id){
            return $this->responseError('Sửa bình luận không thành công', self::LOGIN_FAIL);
        }
        return $this->responseError('Bình luận không thành công', self::LOGIN_FAIL);
    }
    public function save_admin(Request $request)
    {
        //validate
        // $validator = $this->validateCommentData($request);

        // if ($validator->fails()) {
        //     $error = $validator->errors();
        //     return $this->validateFail($error->first(), $error->toArray());
        // }
        $info = Auth::guard('public_user')->user()->BDCprofile;

        $id = (int)$request->id;
        $input['content'] = strip_tags($request->input('content'));
        $input['user_id'] = $info['id'];
        $input['user_type'] = 'user';
        $input['post_id'] = $request->post_id;
        $input['parent_id'] = $request->input('reply_to', '0');
        unset($input['reply_to']);
        $files = $request->file('attached');
        $directory = 'media/social';
        $attached=[
            'images' => [],
            'files' => [],
        ];
        if ($request->hasFile('attached')) {

            foreach ($files as $file) {
                $ext = strtolower($file->getClientOriginalExtension());
                $rs_file = Helper::doUpload($file,$file->getClientOriginalName(),$directory);
                if(in_array($ext,['jpeg', 'jpg','png', 'gif' ])){
                    $attached['images'][]=@$rs_file->origin ? @$rs_file->origin : [];
                }else{
                    $attached['files'][]=@$rs_file->origin ? @$rs_file->origin : [];
                }

            }
        }
        $input['files'] =  $attached ? json_encode($attached) : json_encode(['images' => [], 'files' => []]);
        //TODO : thêm chức năng gửi ảnh cho comment
        $item = $this->modelSocialComment->createSocialComments($input,$id);


        $this->savePostResponse($request->post_id);

        if ($item) {
            if($id){
                return $this->responseSuccess([],'Sửa bình luận thành công');
            }
            return $this->responseSuccess([],'Bình luận thành công');
        }
        if($id){
            return $this->responseError('Sửa bình luận không thành công', self::LOGIN_FAIL);
        }
        return $this->responseError('Bình luận không thành công', self::LOGIN_FAIL);
    }

    public function delete(Request $request)
    {
        $id = (int)$request->id;

        $del = $this->modelSocialComment->delete(['id'=>$id]);
        if($del){
            return $this->responseSuccess([], 'Xóa bình luận thành công');
        }else{
            return $this->responseError('Xóa bình luận không thành công', self::LOGIN_FAIL);
        }

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
        $post = $this->model->getSelectbyId(['id', 'response'],$post_id);

        $total = $this->modelSocialComment->getCountPost($post->id);
        $response = json_decode($post->response,true);

        $response['comment'] = $total;

        //$post->response = json_encode($response);
        $this->model->update(['response'=>$post->response],$post->id);

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
