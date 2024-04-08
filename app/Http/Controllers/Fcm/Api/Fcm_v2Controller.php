<?php

namespace App\Http\Controllers\Fcm\Api;

//use App\Http\Controllers\Api\V1\Controller;

use App\Helpers\dBug;
use App\Http\Controllers\BuildingController;
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
use App\Models\PublicUser\Users;
use App\Models\PublicUser\V2\TokenUserPush;
use App\Models\PublicUser\V2\User;
use App\Repositories\Comments\CommentsRespository;
use App\Repositories\Fcm\FcmRespository;
use App\Repositories\NotifyLog\NotifyLogRespository;
use App\Repositories\Posts\PostsRespository;
use App\Repositories\PublicUsers\PublicUsersProfileRespository;
use App\Services\FCM\V2\SendNotifyFCMService as V2SendNotifyFCMService;
use App\Services\FCM\SendNotifyFCMService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

class Fcm_v2Controller extends BuildingController
{
    use ApiResponse;
    /**
     * Constructor.
     */
    const DUPLICATE     = 19999;
    const LOGIN_FAIL    = 10000;
    private $model;
    private $modelUserinfo;
    private $modelComments;
    private $modelLog;
    private $modelFcm;

    public function __construct(CommentsRespository $modelComments,PostsRespository $model,NotifyLogRespository $modelLog,FcmRespository $modelFcm,PublicUsersProfileRespository $modelUserinfo,Request $request)
    {
        $this->model    = $model;
        $this->modelUserinfo    = $modelUserinfo;
        $this->modelComment    = new Comment();
        $this->modelComments    = $modelComments;
        $this->modelLog    = $modelLog;
        $this->modelFcm    = $modelFcm;
        $this->resource = new PostResource(null);
        Carbon::setLocale('vi');
        parent::__construct($request);
    }

    /**
     * Danh sách các bản ghi nội bộ
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $info = \Auth::guard('public_user')->user()->BDCprofile()->first();
        $per_page = $request->input('per_page', 10);
        $per_page = $per_page > 0 ? $per_page : 10;
        $listNotifies = $this->modelLog->findByStatus(0,(int)$per_page,$info);

        $data = [];
        foreach ($listNotifies['data'] as $key => $item){
//            $user_in_notify = in_array($info[0]['pub_user_id'],$item['user_id']);
//            if($user_in_notify){
                $data[]= [
                    'noti_id'=>$item['_id'],
                    'user_id'=>$info['pub_user_id'],
                    'action_name'=>$item['info']['action_name']??'',
                    'info'=>['id'=>$item['info']['id'],'title'=>$item['info']['title'],'type'=>$item['info']['type'],'image'=>$item['info']['image']],
                    'status'=>$item['status'],
                    'create_at'=>$item['created_at'],
                    'read_at'=>$item['read_at']
                ];
//            }
        }
        if($data){
            return $this->responseSuccess($data);
        }
        return $this->responseError(['Không có dữ liệu.'], self::LOGIN_FAIL );
    } 
    
    public function indexCus(Request $request)
    {
        $info = \Auth::guard('public_user')->user()->info()->first();
        $per_page = $request->input('per_page', 10);
        $per_page = $per_page > 0 ? $per_page : 10;
        $listNotifies = $this->modelLog->findByStatus(0,(int)$per_page,$info);

        $data = [];
        foreach ($listNotifies['data'] as $key => $item){
//            $user_in_notify = in_array($info[0]['pub_user_id'],$item['user_id']);
//            if($user_in_notify){
                $data[]= [
                    'noti_id'=>$item['_id'],
                    'user_id'=>$info['pub_user_id'],
                    'user'=>[
                        'id'=>$info['id'],
                        'name'=>$info['display_name'],
                        'avatar'=>$info['avatar']
                    ],
                    'action_name'=>$item['info']['action_name']??'',
                    'info'=>['id'=>$item['info']['id'],'title'=>$item['info']['title'],'type'=>$item['info']['type'],'image'=>$item['info']['image']],
                    'status'=>$item['status'],
                    'create_at'=>$item['created_at'],
                    'read_at'=>$item['read_at']
                ];
//            }
        }
        if($data){
            return $this->responseSuccess($data);
        }
        return $this->responseError(['Không có dữ liệu.'], self::LOGIN_FAIL );
    }
    public function seeAllNoti(Request $request,$id=0)
    {
        $info = \Auth::guard('public_user')->user()->BDCprofile()->first();

        $count = $this->modelLog->findByStatusVsSee($info);
        return $this->responseSuccess(['count'=>$count]);
    }
    public function seeAllNotiCus(Request $request,$id=0)
    {
        $info = \Auth::guard('public_user')->user()->info()->first();

        $count = $this->modelLog->findByStatusVsSee($info);
        return $this->responseSuccess(['count'=>$count]);
    }
    public function readNotify(Request $request)
    {
        $info = \Auth::guard('public_user')->user()->BDCprofile()->first();

        $id= $request->input('notify_id', 0);
        $last_seen= $request->input('last_seen', '');
        $read = $this->modelLog->readSaveCheck($id,$last_seen,$info);
        if($read>0){
            return $this->responseSuccess(['Cập nhật thành công']);
        }
        return $this->responseError(['Không có dữ liệu.'], 204 );
    }
    public function readNotifyCus(Request $request)
    {
        $info = \Auth::guard('public_user')->user()->info()->first();

        $id= $request->input('notify_id', 0);
        $last_seen= $request->input('last_seen', '');
        $read = $this->modelLog->readSaveCheck($id,$last_seen,$info);
        if($read>0){
            return $this->responseSuccess(['Cập nhật thành công']);
        }
        return $this->responseError(['Không có dữ liệu.'], 204 );
    }
    public function getComment(Request $request,$id=0)
    {
        if($id>0){
            $data=[];
            $comment_main = $this->modelComments->listCommentsById('id',$id);
            foreach ($comment_main as $i){
                $reply = $this->modelComments->listCommentsByIdPerpage('parent_id',$i['id']);
                $data[]=[
                    'id'=>$i['id'],
                    'type'=>$i['type'],
                    'post_id'=>$i['post_id'],
                    'user_id'=>$i['user_id'],
                    'content'=>$i['content'],
                    'files'=>$i['files'],
                    'rating'=>$i['rating'],
                    'status'=>$i['status'],
                    'reply'=>$reply,
                    'created_at'=>$i['created_at'],
                    'updated_at'=>$i['updated_at'],
                ];
            }
            return $this->responseSuccess($data);
        }
        return $this->responseError(['Không có dữ liệu.'], self::LOGIN_FAIL );
    }
    public function reply(Request $request,$id=0)
    {
        $info = \Auth::guard('public_user')->user()->BDCprofile()->where('bdc_building_id',$request->building_id)->first();
        if($info){
            if($id>0){
                $post = $this->model->getOneSelect(['id','type'],'id',$id);
                $data=[
                    'type'=>$post->type,
                    'post_id'=>$post->id,
                    'parent_id'=>$request->reply_to,
                    'user_id'=>$info['id']??1,
                    'content'=>$request->content,
                    'rating'=>$request->rating
                ];
                $this->modelComments->create($data);
                return $this->responseSuccess([],'Bình luận thành công');
            }
            return $this->responseError(['Không có id bài viết'], 204 );
        }
        return $this->responseError(['Không lấy được thông tin User'], self::LOGIN_FAIL );
    }
    public function check()
    {
        $data = $this->modelFcm->all(['*'])->toArray();
        if($data){
            return $this->responseSuccess($data,['Lấy dữ liệu thành công']);
        }
        return $this->responseError(['Không có dữ liệu.'], 204 );
    }

    public function logToken(Request $request)
    {
        $info = \Auth::guard('public_user')->user()->BDCprofile()->first();
        $bundle_id = $request->post("bundle_id");
        $platform = $request->post("platform");
        $token = $request->post("token");
        $device_id = $request->post("device_id");
        $type = $request->post("type");
        $user_id = $info['pub_user_id'];

        if(!$bundle_id || !$platform || !$token || !$device_id || !$type){
            return $this->responseError(['Tham số không hợp lệ'], 204 );
        }

        // $this->modelLog->create([
        //     'messages' => json_encode($request->all()),
        //     'userinfo' => json_encode([
        //                 'id' => $info->id,
        //                 'pub_user_id' => $info->pub_user_id,
        //                 'display_name' => $info->display_name,
        //                 'phone' => $info->phone,
        //                 'bdc_building_id' => $info->bdc_building_id,
        //             ]),
        //     'building_id'=> $info->bdc_building_id,
        //     'created_date' => Carbon::now()->toDateTimeString(),
        //     'hide_loads' => null
        //     ]);

        $check = $this->modelFcm->checkToken($token,$type,$platform);
        if (!$check) { // tạo mới
            $rs = $this->modelFcm->newToken2($user_id,$device_id,$token,$type,$platform,$bundle_id);
            if($rs) return $this->responseSuccess(['Thêm thành công']);
        } else if($user_id != $check['user_id']) { // cập nhật lại token cho user mới
            $rs = $this->modelFcm->updateTokenNewUser($check['id'],$user_id);
            if($rs) return $this->responseSuccess(['Cập nhật thành công']);
        }
        return $this->responseSuccess(['ok đã nhận']);
    }

    public function saveToken(Request $request)
    {
        $info = \Auth::guard('public_user')->user()->BDCprofile()->first();
        
        $this->modelFcm->deletefcm($info['pub_user_id'],'banquanly');

        $action = $this->modelFcm->newToken($info['pub_user_id'],$request->id,$request->token,'banquanly');
        
        if($action){
            return $this->responseSuccess(['Cập nhật thành công']);
        }
        return $this->responseError(['Không có dữ liệu.'], 204 );
    }
    public function saveTokenCus(Request $request)
    {
        $info = \Auth::guard('public_user')->user()->info()->first();
        // $this->modelLog->create([
        //     'messages' => json_encode($request->all()),
        //     'userinfo' => json_encode([
        //                 'id' => $info->id,
        //                 'pub_user_id' => $info->pub_user_id,
        //                 'display_name' => $info->display_name,
        //                 'phone' => $info->phone,
        //                 'bdc_building_id' => $info->bdc_building_id,
        //             ]),
        //     'building_id'=> $info->bdc_building_id,
        //     'created_date' => Carbon::now()->toDateTimeString(),
        //     'hide_loads' => null
        //     ]);
        $this->modelFcm->deletefcm($info['pub_user_id'],$request->type ?? 'cudan');
        $action = $this->modelFcm->newToken($info['pub_user_id'],$request->id,$request->token,$request->type ?? 'cudan');
        if($action){
            return $this->responseSuccess(['Cập nhật thành công']);
        }
        return $this->responseError(['Không có dữ liệu.'], 204 );

    }
    public function pushNotify(Request $request)
    {
        $token = $request->token;
        $data_payload = $request->data_payload;
        $data_payload = json_decode($data_payload);
        $user_token = TokenUserPush::where('token',$token)->first();
        if($user_token && $data_payload){
            $get_user_v2 = TokenUserPush::where('user_id',$user_token->user_id)->get();
            foreach ($get_user_v2 as $key_1 => $value_1) {
                V2SendNotifyFCMService::pushNotify($value_1->token,$data_payload,$value_1->bundle_id);
            }
            $user_v2 = User::find($user_token->user_id);
            $user_v1 = Users::where(function ($query) use ($user_v2) {
                if ($user_v2->email) {
                    $query->where('email', $user_v2->email);
                } else {
                    $query->where('mobile', $user_v2->phone);
                }
            })->first();
            if ($user_v1) {
                $fcms = Fcm::where('user_id', $user_v1->id)->get();
                foreach ($fcms as $key => $value) {
                    V2SendNotifyFCMService::pushNotify($value->token, $data_payload, $user_token->bundle_id);
                }
            }
            return $this->sendSuccessApi([],200,'Thành công');
        }
        return $this->sendErrorApi('Thất bại');
    }
    public function configUsers(Request $request)
    {
        $info = \Auth::guard('public_user')->user()->BDCprofile()->where('bdc_building_id',$request->building_id)->first();
        $config= $info->config_fcm;

        if($config){
            if($request->ban && !strpos($config,$request->ban)){
                $config .= '|'.$request->ban;
            }
            if($request->active){
                $config = trim(str_replace($request->active,'',$config),'|');
            }
            $this->modelUserinfo->update(['config_fcm'=>$config?$config:null],$info->id,'id');
            return $this->responseSuccess(['Cập nhật thành công']);
        }
        if(!$request->ban){
            return $this->responseError(['Dữ liệu nhập không đúng.'], 400 );
        }
        $this->modelUserinfo->update(['config_fcm'=>$request->ban],$info->id,'id');
        return $this->responseSuccess(['Cập nhật thành công']);
    }
    public function getConfigUsers(Request $request)
    {
        $info = \Auth::guard('public_user')->user()->BDCprofile()->where('bdc_building_id',$request->building_id)->first();
        $config= $info->config_fcm;
        $list_config = ['POST','CPOST','FBACK','REQUEST','CFBACK','CREQUEST','TASK','TASK_ASSIGNED','DEBT','RECORD','BILL_NOTICE','MAINTENANCE'];

        if($config){
            $list_ban = explode('|',$config);
            $list_config_action = [];
            foreach ($list_config as $item){
                if(in_array($item,$list_ban)){
                    $list_config_action[$item]= true;
                }else{
                    $list_config_action[$item]= false;
                }
            }
            return $this->responseSuccess($list_config_action,'Lấy dữ liệu thành công',200);
        }
        return $this->responseError('Không có dữ liệu config',400, [] );

    }

}
