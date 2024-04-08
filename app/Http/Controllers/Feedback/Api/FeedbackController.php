<?php

namespace App\Http\Controllers\Feedback\Api;

use App\Commons\Helper;
use App\Helpers\dBug;
use App\Http\Controllers\BuildingController;
use App\Models\PublicUser\UserInfo;
use App\Repositories\Comments\CommentsRespository;
use App\Repositories\Feedback\FeedbackRespository;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Models\Building\Building;
use App\Models\Campain;
use App\Models\Comments\Comments;
use App\Models\Fcm\Fcm;
use App\Models\Feedback\Feedback;
use App\Models\PublicUser\V2\UserInfo as V2UserInfo;
use App\Services\FCM\SendNotifyFCMService;
use App\Util\Debug\Log;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class FeedbackController extends BuildingController
{
    use ApiResponse;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    const DUPLICATE     = 19999;
    const LOGIN_FAIL    = 10000;
    private $model;
    private $modelComments;
    public function __construct(FeedbackRespository $model,CommentsRespository $modelComments,Request $request)
    {
        // $this->middleware('auth', ['except'=>[]]);
        $this->model = $model;
        $this->modelComments = $modelComments;
        //$this->middleware('jwt.auth');
        parent::__construct($request);
    }

    public function index(Request $request)
    {
        $info = \Auth::guard('public_user')->user()->infoWeb->toArray();
        if ($info) {
            $per_page = $request->input('per_page', 10);
            $list_search = $this->model->searchByApi($request->building_id, $request, [], $per_page);
            $data = [];
            foreach ($list_search as $item) {
                $user = null;
                if($item->new == 1){
                    $user = V2UserInfo::where('user_id',$item->user_id)->first();
                }else{
                    $user = UserInfo::find($item->user_id??$item->pub_user_profile_id);
                }
                $data[] = [
                    'id' => $item->id,
                    'title' => $item->title,
                    'content' => $item->content,
                    'user' => [
                        'user_id'=>@$user->id,
                        'display_name'=>@$user->display_name ?? @$user->full_name,
                        'avatar'=>url('/').'/'.@$user->avatar,
                    ],
                    'apartment' => ['id' => $item->bdcApartment->id ?? null, 'name' => $item->bdcApartment->name ?? null],
                    'department' => ['id' => $item->pubUserProfile->bdcDepartmentStaff->department->id ?? null, 'name' => $item->pubUserProfile->bdcDepartmentStaff->department->name ?? null],
                    'type' => $item->type,
                    'comment_count' => count($item->allComments),
                    'status' => $item->status,
                    'attached' => json_decode($item->attached, true),
                    'created_at' => (string)date('Y-m-d H:i:s', strtotime($item->created_at)),
                ];
            }
            if ($data) {
                return $this->responseSuccess($data);
            }
            return response()->json(['status'=>true,'message'=>'không có dữ liệu'], 200);
        }
        return response()->json(['status'=>true,'message'=>'không có dữ liệu' ,'data'=>[]], 200);

    }
    public function indexCus(Request $request)
    {
        $info = Auth::guard('public_user_v2')->user()->infoApp;
        if($info){
            $per_page = $request->input('per_page', 10);
            $list_search = $this->model->searchByApi($request->building_id, $request, [], $per_page, $info['id']);
            $data = [];
            foreach ($list_search as $item) {
                $user = null;
                if($item->new == 1){
                    $user = V2UserInfo::where('user_id',$item->user_id)->first();
                }else{
                    $user = UserInfo::find($item->user_id);
                }
                $data[] = [
                    'id' => $item->id,
                    'title' => $item->title,
                    'content' => $item->content,
                    'user' => [
                        'user_id'=>@$user->id,
                        'display_name'=>@$user->display_name ?? @$user->full_name,
                        'avatar'=>@$user->avatar,
                    ],
                    'apartment' => ['id' => $item->bdcApartment->id ?? null, 'name' => $item->bdcApartment->name ?? null],
                    'type' => $item->type,
                    'comment_count' => count($item->allComments),
                    'status' => $item->status,
                    'attached' => json_decode($item->attached, true),
                    'created_at' => date('Y-m-d h:m:i', strtotime($item->created_at)),
                ];
            }
            if ($data) {
                return $this->responseSuccess($data);
            }
            return $this->responseError(['Không có dữ liệu.'], 204 );
        }
        return $this->responseError(['Không lấy được thông tin User'], self::LOGIN_FAIL );

    }
    public function indexCus_v2(Request $request)
    {
        $info = Auth::guard('public_user_v2')->user()->infoApp;
        if ($info) {
            $per_page = $request->input('per_page', 10);
            $list_search = $this->model->searchByApi($request->building_id, $request, [], $per_page, $info['id']);
            $data = [];
            foreach ($list_search as $item) {
                $data[] = [
                    'id' => $item->id,
                    'title' => $item->title,
                    'content' => $item->content,
                    'user' => ['display_name' => $item->pubUserProfile->display_name ?? null, 'user_id' => $item->pubUserProfile->id ?? null, 'avatar' => $item->pubUserProfile->avatar ?? null],
                    'apartment' => ['id' => $item->bdcApartment->id ?? null, 'name' => $item->bdcApartment->name ?? null],
                    'type' => $item->type,
                    'comment_count' => count($item->allComments),
                    'status' => $item->status,
                    'attached' => json_decode($item->attached, true),
                    'created_at' => date('Y-m-d h:m:i', strtotime($item->created_at)),
                ];
            }
            if ($data) {
                return $this->responseSuccess($data);
            }
            return $this->responseError(['Không có dữ liệu.'], 204);
        }
        return $this->responseError(['Không lấy được thông tin User'], self::LOGIN_FAIL);

    }
    public function detail(Request $request, $id = 0)
    {
        $info = \Auth::guard('public_user')->user()->infoWeb->toArray();

        $feedback = $this->model->findIdFB($id);
        // $check_reply =  Comments::where(['post_id'=>$id,'content'=>'Ban quản lý đã tiếp nhận ý kiến của cư dân.'])->first();
        // if(!$check_reply){
        //     // $reply=[
        //     //     'type'=>'feedback',
        //     //     'post_id'=>$id,
        //     //     'parent_id'=>0,
        //     //     'user_id'=>$info[0]['id']??1,
        //     //     'content'=>'Ban quản lý đã tiếp nhận ý kiến của cư dân.',
        //     // ];
        //     // $comment = $this->modelComments->create($reply);
        //     $feedback = Feedback::find($id);
        //     if($feedback){
        //         $feedback->update(['status'=>3]); // ban quản lý đã tiếp nhận ý kiến cư dân
        //     }
        // }
        $user = null;
        if($feedback->new == 1){
            $user = V2UserInfo::where('user_id',$feedback->user_id)->first();
        }else{
            $user = UserInfo::find($feedback->user_id??$feedback->pub_user_profile_id);
        }
        $data=[
            'id'=>$feedback->id,
            'title'=>$feedback->title,
            'content'=>$feedback->content,
            'user' => [
                'user_id'=>@$user->id,
                'display_name'=>@$user->display_name ?? @$user->full_name,
                'avatar'=>@$user->avatar,
            ],
            'apartment'=>['id'=>@$feedback->bdcApartment->id??null,'name'=>@$feedback->bdcApartment->name??null],
            'type'=>$feedback->type,
            'comment_count'=>count($feedback->allComments),
            'status'=>$feedback->status,
            'attached'=>json_decode($feedback->attached,true),
            'created_at'=>(string)$feedback->created_at,
        ];
       
      
        if($data){
            return $this->responseSuccess($data);
        }
        return $this->responseError(['Không có dữ liệu.'], 204 );
        
    }
    public function reply(Request $request,$id=0)
    {
        $info = \Auth::guard('public_user')->user()->infoWeb->toArray();
        if($info){
            if($id>0){
                $check = $this->model->getOne('id',$id);
                if(!empty($check)){
                    $data=[
                        'type'=>'feedback',
                        'post_id'=>$id,
                        'parent_id'=>$request->reply_to,
                        'user_id'=>$info[0]['id']??1,
                        'content'=>$request->content,
                        'rating'=>$request->rating,
                    ];
                    $files = $request->file('attached');
                    $directory = 'media/post';
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
                    $data['files'] =  $attached ? json_encode($attached) : json_encode(['images' => [], 'files' => []]);
                    $comment = $this->modelComments->create($data);
                    $feedback = Feedback::find($id);
                    if($feedback){
                        $feedback->update(['status'=>2]); // chờ cư dân phản hồi
                    }
                    return $this->responseSuccess($comment ? $comment->toArray() : [],'Bình luận thành công');
                }
                return $this->responseError(['Id feedback không đúng'], 204 );
            }
            return $this->responseError(['Không có id feedback'], 204 );
        }
        return $this->responseError(['Không lấy được thông tin User'], self::LOGIN_FAIL );
    }
    public function replyCustomer(Request $request,$id=0)
    {
        $info = \Auth::guard('public_user')->user()->info->toArray();
        if($info){
            if($id>0){
                $check = $this->model->getOne('id',$id);
                if(!empty($check)){
                    $url = [];
                    $file_doc = [];
                    if ($request->hasFile('attached')) {
                        $files = $request->file('attached');
                        $forder = date('d-m-Y');
                        $directory = 'media/image/feedback';
                        if (!is_dir($directory)) {
                            mkdir($directory);
                            if (!is_dir($directory . '/' . $forder)) {
                                mkdir($directory . '/' . $forder);
                            }
                        }
                        $expensions_doc = ['csv', 'doc', 'docx', 'djvu', 'odp', 'ods', 'odt', 'pps', 'ppsx', 'ppt', 'pptx', 'pdf', 'ps', 'eps', 'rtf', 'txt', 'wks', 'wps', 'xls', 'xlsx', 'xps', 'tif', 'tiff'];
                        $expensions_image = ['gif', 'jpeg', 'jpg', 'jif', 'jfif', 'jp2', 'jpx', 'j2k', 'j2c', 'png'];
                        foreach ($files as $file) {
                            $ext = strtolower($file->getClientOriginalExtension());
                            if (in_array($ext, $expensions_doc)) {
                                $name = str_replace('.' . $ext, '', $file->getClientOriginalName());
                                iconv(mb_detect_encoding($name, mb_detect_order(), true), "UTF-8", $name);
                                $mainFilename = iconv(mb_detect_encoding($name, mb_detect_order(), true), "UTF-8", $name) . '-' . date('d-m-Y-h-i-s');

                                $file->move($directory . '/' . $forder, $mainFilename . "." . $ext);
                                $url[] = '/' . $directory . '/' . $forder . '/' . $mainFilename . "." . $ext;
                            }
                            if (in_array($ext, $expensions_image)) {
                                $name = str_replace('.' . $ext, '', $file->getClientOriginalName());
                                iconv(mb_detect_encoding($name, mb_detect_order(), true), "UTF-8", $name);
                                $mainFilename = iconv(mb_detect_encoding($name, mb_detect_order(), true), "UTF-8", $name) . '-' . date('d-m-Y-h-i-s');

                                $file->move($directory . '/' . $forder, $mainFilename . "." . $ext);
                                $url[] = '/' . $directory . '/' . $forder . '/' . $mainFilename . "." . $ext;
                            }
                        }
                    }

                    $data=[
                        'type'=>'feedback',
                        'post_id'=>$id,
                        'parent_id'=>$request->reply_to,
                        'user_id'=>$info['id'] ?? 1,
                        'content'=>$request->content,
                        'rating'=>$request->rating,
                        'bdc_building_id'=>$request->building_id ?? 1,
                        'app_id'=>$info['app_id']??'',
                        'bdc_apartment_id'=>$request->apartment_id ?? 0,
                        'url_fileupload' => json_encode($url)
                    ];
                    $comment = $this->modelComments->create($data);
                    
                    $comment->title = 'Phản hồi kiến nghị';

                    $_building = Building::get_detail_building_by_building_id($request->building_id);
        
                    $user_info = UserInfo::where(['bdc_building_id'=>$request->building_id,'status'=>1,'type'=>2])->get();
                    $userIdList = [];
                    foreach ($user_info as  $value) {
                        array_push($userIdList, [$value->pub_user_id]);
                    }
                    $countTokent = (int)Fcm::getCountTokenbyUserId($userIdList);  
                    $total = ['email'=>0, 'app'=> $countTokent, 'sms'=> 0];

                    $campain = Campain::updateOrCreateCampain('Comment cho: '.$check->title, config('typeCampain.FEEDBACK_COMMENT'), $comment->id, $total, $request->building_id, 0, 0);
    
                    foreach ($user_info as $key => $value) {
                        $this->sendNotifyTask($comment,$_building,$value->pub_user_id, $campain->id);
                    }

                    return $this->responseSuccess([],'Bình luận thành công');
                }
                return $this->responseError(['Id feedback không đúng'], 204 );
            }
            return $this->responseError(['Không có id feedback'], 204 );
        }
        return $this->responseError(['Không lấy được thông tin User'], self::LOGIN_FAIL);
    }
    public function replyCustomer_v2(Request $request,$id=0)
    {
        $info = Auth::guard('public_user_v2')->user()->infoApp;
        if($info){
            if($id>0){
                $check = $this->model->getOne('id',$id);
                if(!empty($check)){
                    $directory = 'media/feedback';
                    $attached=null;
                    $files = $request->file('attached');
                    if ($request->hasFile('attached')) {
        
                        foreach ($files as $file) {
                            $ext = strtolower($file->getClientOriginalExtension());
                            $rs_file = Helper::doUpload($file,$file->getClientOriginalName(),$directory);
                            if(Helper::check_file_type_is_image($ext)){
                                $attached['images']=@$rs_file->origin ? [@$rs_file->origin] : [];
                                $attached['files']= [];
                            }else{
                                $attached['images']= [];
                                $attached['files']=@$rs_file->origin ? [@$rs_file->origin] : [];
                            }
        
                        }
                    }

                    $data = [
                        'type' => 'feedback',
                        'post_id' => $id,
                        'parent_id' => $request->reply_to,
                        'user_id' => $info['id'] ?? 1,
                        'content' => $request->content,
                        'rating' => $request->rating,
                        'bdc_building_id' => $request->building_id ?? 1,
                        'app_id' => 'buildingcare',
                        'bdc_apartment_id' => $request->apartment_id ?? 0,
                        'files' => $attached ? json_encode($attached) : json_encode(['images' => [], 'files' => []]),
                        'new' => 1
                    ];
                    $comment = $this->modelComments->create($data);
                    
                    $comment->title = 'Phản hồi kiến nghị';

                    $_building = Building::get_detail_building_by_building_id($request->building_id);
        
                    $user_info = UserInfo::where(['bdc_building_id'=>$request->building_id,'status'=>1,'type'=>2])->get();
                    $userIdList = [];
                    foreach ($user_info as  $value) {
                        array_push($userIdList, [$value->pub_user_id]);
                    }
                    $countTokent = (int)Fcm::getCountTokenbyUserId($userIdList);  
                    $total = ['email'=>0, 'app'=> $countTokent, 'sms'=> 0];

                    $campain = Campain::updateOrCreateCampain('Comment cho: '.$check->title, config('typeCampain.FEEDBACK_COMMENT'), $comment->id, $total, $this->building_active_id, 0, 0);
    
                    foreach ($user_info as $key => $value) {
                        $this->sendNotifyTask($comment,$_building,$value->pub_user_id, $campain->id);
                    }

                    return $this->responseSuccess([],'Bình luận thành công');
                }
                return $this->responseError(['Id feedback không đúng'], 204 );
            }
            return $this->responseError(['Không có id feedback'], 204 );
        }
        return $this->responseError(['Không lấy được thông tin User'], self::LOGIN_FAIL);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $info = \Auth::guard('public_user')->user()->info->toArray();
        if($info) {
            $files = $request->file('attached');

            $forder = date('d-m-Y');
            $directory = 'media/image/feedback';
            if (!is_dir($directory)) {
                mkdir($directory);
                if (!is_dir($directory . '/' . $forder)) {
                    mkdir($directory . '/' . $forder);
                }
            }
            $url = [];$file_doc = [];
            if ($request->hasFile('attached')) {
                $expensions_doc= ['csv','doc','docx','djvu','odp','ods','odt','pps','ppsx','ppt','pptx','pdf','ps','eps','rtf','txt','wks','wps','xls','xlsx','xps','tif','tiff'];
                $expensions_image= ['gif','jpeg','jpg','jif','jfif','jp2','jpx','j2k','j2c','png'];
                foreach ($files as $file) {
                    $ext = strtolower($file->getClientOriginalExtension());
                    if(in_array($ext,$expensions_doc)){
                        $name = str_replace('.' . $ext, '', $file->getClientOriginalName());
                        iconv(mb_detect_encoding($name, mb_detect_order(), true), "UTF-8", $name);
                        $mainFilename = iconv(mb_detect_encoding($name, mb_detect_order(), true), "UTF-8", $name) . '-' . date('d-m-Y-h-i-s');

                        $file->move($directory.'/'.$forder, $mainFilename.".".$ext);
                        $file_doc[] = '/' . $directory . '/' . $forder . '/' . $mainFilename . "." . $ext;
                    }
                    if(in_array($ext,$expensions_image)){
                        $name = str_replace('.' . $ext, '', $file->getClientOriginalName());
                        iconv(mb_detect_encoding($name, mb_detect_order(), true), "UTF-8", $name);
                        $mainFilename = iconv(mb_detect_encoding($name, mb_detect_order(), true), "UTF-8", $name) . '-' . date('d-m-Y-h-i-s');

                        $file->move($directory.'/'.$forder, $mainFilename.".".$ext);
                        $url[] = '/' . $directory . '/' . $forder . '/' . $mainFilename . "." . $ext;
                    }

                }
            }
            $create = $this->model->create( [
                'pub_user_profile_id' => $info['id']??1,
                'title' => $request->title,
                'content' => $request->content,
                'rating' => $request->rating ?? 0,
                'attached' => json_encode(['images' => $url, 'files' => $file_doc]),
                'type' => $request->type ?? '',
                'status' => 0,
                'bdc_building_id' => $request->building_id ?? $this->building_id ?? null,
                'app_id' => $request->app_id ?? $this->app_id ?? null,
                'bdc_apartment_id' => $request->bdc_apartment_id??0,
                'bdc_department_id' => $request->type == 'fback'?$request->department_id:0
            ]);

            $_building = Building::get_detail_building_by_building_id($request->building_id);
        
            $user_info = UserInfo::where(['bdc_building_id'=>$request->building_id,'status'=>1,'type'=>2])->get();
            $userIdList = [];
            foreach ($user_info as  $value) {
                array_push($userIdList, [$value->pub_user_id]);
            }
            $countTokent = (int)Fcm::getCountTokenbyUserId($userIdList);  
            $total = ['email'=>0, 'app'=> $countTokent, 'sms'=> 0];

            $campain = Campain::updateOrCreateCampain($request->title, config('typeCampain.FEEDBACK'), $create->id, $total,$request->building_id, 0, 0);

            foreach ($user_info as $key => $value) {
                $this->sendNotifyTask($create,$_building,$value->pub_user_id, $campain->id);
            }

            if ($create) {
                return $this->responseSuccess([], 'Thêm phản hồi thành công');
            }
            return $this->responseError(['Không có dữ liệu.'], 204);
        }
        return $this->responseError(['Không lấy được thông tin User'], self::LOGIN_FAIL );
    }

      /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create_v2(Request $request)
    {
        $info = Auth::guard('public_user_v2')->user()->infoApp;
        if($info) {
            $files = $request->file('attached');
            $directory = 'media/feedback';
            $attached=null;
            if ($request->hasFile('attached')) {

                foreach ($files as $file) {
                    $ext = strtolower($file->getClientOriginalExtension());
                    $rs_file = Helper::doUpload($file,$file->getClientOriginalName(),$directory);
                    if(Helper::check_file_type_is_image($ext)){
                        $attached['images']=@$rs_file->origin ? [@$rs_file->origin] : [];
                        $attached['files']= [];
                    }else{
                        $attached['images']= [];
                        $attached['files']=@$rs_file->origin ? [@$rs_file->origin] : [];
                    }

                }
            }
            $create = $this->model->create( [
                'pub_user_profile_id' => $info['id']??1,
                'title' => $request->title,
                'content' => $request->content,
                'rating' => $request->rating ?? 0,
                'attached' => $attached ? json_encode($attached) : json_encode(['images' => [], 'files' => []]),
                'type' => $request->type ?? '',
                'status' => 0,
                'bdc_building_id' => $request->building_id ?? $this->building_id ?? null,
                'app_id' => $request->app_id ?? $this->app_id ?? null,
                'bdc_apartment_id' => $request->bdc_apartment_id??0,
                'bdc_department_id' => $request->type == 'fback'?$request->department_id:0,
                'new' => 1
            ]);

            $_building = Building::get_detail_building_by_building_id($request->building_id);
        
            $user_info = UserInfo::where(['bdc_building_id'=>$request->building_id,'status'=>1,'type'=>2])->get();
            $userIdList = [];
            foreach ($user_info as  $value) {
                array_push($userIdList, [$value->pub_user_id]);
            }
            $countTokent = (int)Fcm::getCountTokenbyUserId($userIdList);  
            $total = ['email'=>0, 'app'=> $countTokent, 'sms'=> 0];

            $campain = Campain::updateOrCreateCampain($request->title, config('typeCampain.FEEDBACK'), $create->id, $total, $this->building_active_id, 0, 0);

            foreach ($user_info as $key => $value) {
                $this->sendNotifyTask($create,$_building,$value->pub_user_id, $campain->id);
            }

            if ($create) {
                return $this->responseSuccess([], 'Thêm phản hồi thành công');
            }
            return $this->responseError(['Không có dữ liệu.'], 204);
        }
        return $this->responseError(['Không lấy được thông tin User'], self::LOGIN_FAIL );
    }

    public function sendNotifyTask($object, $building , $user_id ,$campainId, $status = null)
    {
        $data_noti=[
            'message' => $status == null ? $object->content : $status, 
            'building_id' => $building->id,
            'title' => '['.$building->name."]_" .$object->title,
            'action_name' => 'fback',
            'image' => null,
            'type' => 'fback',
            'screen' => null,
            'id' => $object->id,
            'user_id' => $user_id,
            'app_config' => "banquanly",
            'avatar' => "avatar/system/01.png",
            'campain_id' => $campainId,
            'app'=>'v1'
        ];

        SendNotifyFCMService::setItemForQueueNotify($data_noti);
    } 

    public function comments(Request $request,$id=0)
    {

        $per_page = $request->input('per_page', 10);
        $per_page = $per_page > 0 ? $per_page : 10;
        $order_by = 'id ASC';
       
        if($id>0){
            $data=[];
            $comment_main = $this->modelComments->listCommentsFeedbackOrderBy($id,$per_page);
         
            foreach ($comment_main as $i) {
                $user = null;
                if($i->new == 1){
                    $user = V2UserInfo::where('user_id',$i->user_id)->first();
                }else{
                    $user = UserInfo::find($i->user_id);
                }
                $reply = $this->modelComments->listCommentsFeedbackOrderBy($id, $per_page, $i->id);
                $data_reply = null;
                foreach ($reply as $r) {
                    $user_1 = null;
                    if($r->new == 1){
                        $user_1 = V2UserInfo::where('user_id',$r->user_id)->first();
                    }else{
                        $user_1 = UserInfo::find($r->user_id);
                    }
                    $data_reply[] = [
                        'id' => $r->id,
                        'type' => $r->type,
                        'post_id' => $r->post_id,
                        'user' => [
                            'user_id'=>@$user_1->id,
                            'display_name'=>@$user_1->display_name ?? @$user_1->full_name,
                            'avatar'=>@$user_1->avatar,
                        ],
                        'content' => $r->content,
                        'files' => $r->files,
                        'rating' => $r->rating,
                        'status' => $r->status,
                        'created_at' => (string)$r->created_at,
                        'updated_at' => (string)$r->updated_at,
                    ];
                }
                $data[] = [
                    'id' => $i->id,
                    'type' => $i->type,
                    'post_id' => $i->post_id,
                    'user' => [
                        'user_id'=>@$user->id,
                        'display_name'=>@$user->display_name ?? @$user->full_name,
                        'avatar'=>@$user->avatar,
                    ],
                    'content' => $i->content,
                    'files' => $i->files,
                    'rating' => $i->rating,
                    'status' => $i->status,
                    'reply' => !empty($data_reply) ? $data_reply : [],
                    'created_at' => (string)$i->created_at,
                    'updated_at' => (string)$i->updated_at,
                ];
            }
            return $this->responseSuccess($data);
        }
        return response()->json(['status'=>true,'message'=>'không có dữ liệu' ,'data'=>[]], 200);
    }
  
    public function commentsCustomer(Request $request,$id=0)
    {

        $per_page = $request->input('per_page', 10);
        $per_page = $per_page > 0 ? $per_page : 10;
        $order_by = 'id ASC';
        if($id>0){
            $data=[];
            $comment_main = $this->modelComments->listCommentsFeedback($id,$per_page);
            foreach ($comment_main as $i){
                $reply = $this->modelComments->listCommentsFeedback($id,$per_page,$i['id']);
                foreach ($reply as $r){

                    $data_reply[]=[
                        'id'=>$r->id,
                        'type'=>$r->type,
                        'post_id'=>$r->post_id,
                        'user' => ['user_id' => $r->user_id ?? 0, 'display_name' => @$r->userInfo->display_name ?? (@$r->userInfo->type == 2 ? 'Ban quản lý' : 'Cư dân'), 'avatar' => @$r->userInfo->avatar ? @$r->userInfo->avatar : null],
                        'content'=>$r->content,
                        'files'=>@$r->files,
                        'rating'=>$r->rating,
                        'status'=>$r->status,
                        'url_fileupload' => @$r->url_fileupload ? (array)[$r->url_fileupload] : null,
                        'created_at'=>$r->created_at,
                        'updated_at'=>$r->updated_at,
                    ];
                }
                $data[]=[
                    'id'=>$i->id,
                    'type'=>$i->type,
                    'post_id'=>$i->post_id,
                    'user' => ['user_id' => $i->user_id ?? 0, 'display_name' => @$i->userInfo->display_name ?? (@$i->userInfo->type == 2 ? 'Ban quản lý' : 'Cư dân'), 'avatar' => @$i->userInfo->avatar ? @$i->userInfo->avatar : null],
                    'content'=>$i->content,
                    'files'=>@$i->files,
                    'rating'=>$i->rating,
                    'status'=>$i->status,
                    'reply'=>!empty($data_reply)?$data_reply:[],
                    'url_fileupload' => @$i->url_fileupload ?  (array)[$i->url_fileupload] : null,
                    'created_at'=>$i->created_at,
                    'updated_at'=>$i->updated_at,
                    'domain'=> env('APP_URL'),
                ];
            }
            return $this->responseSuccess($data);
        }
        return $this->responseError(['Không có dữ liệu.'], 204 );
    }

    public function getComment(Request $request,$id=0)
    {
        $per_page = $request->input('per_page', 10);
        $per_page = $per_page > 0 ? $per_page : 10;
        if($id>0){
            $data=[];
            $comment_main = $this->modelComments->listCommentsById('id',$id);
          
            //dd($comment_main);
            foreach ($comment_main as $i){
                $reply = $this->modelComments->listCommentsByIdPerpage('parent_id',$id,$per_page);

                foreach (isset($reply['data'])?$reply['data']:[] as $r){
                    $data_reply[]=[
                        'id'=>$r['id'],
                        'type'=>$r['type'],
                        'post_id'=>$r['post_id'],
                        'user'=>['user_id'=>$r['user_id']??0,'display_name'=>$r['user']['display_name'],'avatar'=>$r['user']['avatar']],
                        'content'=>$r['content'],
                        'files'=>$r['files'],
                        'rating'=>$r['rating'],
                        'status'=>$r['status'],
                        'created_at'=>$r['created_at'],
                        'updated_at'=>$r['updated_at'],
                    ];
                }
                $data[]=[
                    'id'=>$i['id'],
                    'type'=>$i['type'],
                    'post_id'=>$i['post_id'],
                    'user'=>['user_id'=>$i['user_id']??0,'display_name'=>$i['user']['display_name'],'avatar'=>$i['user']['avatar']],
                    'content'=>$i['content'],
                    'files'=>$i['files'],
                    'rating'=>$i['rating'],
                    'status'=>$i['status'],
                    'reply'=>$reply,
                    'created_at'=>$i['created_at'],
                    'updated_at'=>$i['updated_at'],
                ];
            }
//            dd($data);
            return $this->responseSuccess($data);
        }
        return response()->json(['status'=>true,'message'=>'không có dữ liệu' ,'data'=>[]], 200);
    }
    public function getCommentCustomer(Request $request,$id=0)
    {
        $per_page = $request->input('per_page', 10);
        $per_page = $per_page > 0 ? $per_page : 10;
        if($id>0){
            $data=[];
            $comment_main = $this->modelComments->listCommentsById('id',$id);
            foreach ($comment_main as $i){
                $reply = $this->modelComments->listCommentsByIdPerpage('parent_id',$id,$per_page);

                foreach (isset($reply['data'])?$reply['data']:[] as $r){
                    $data_reply[]=[
                        'id'=>$r['id'],
                        'type'=>$r['type'],
                        'post_id'=>$r['post_id'],
                        'user'=>['user_id'=>$r['user_id']??0,'display_name'=>$r['user']['display_name'],'avatar'=>$r['user']['avatar']?$r['user']['avatar']:null],
                        'content'=>$r['content'],
                        'files'=>$r['files'],
                        'rating'=>$r['rating'],
                        'status'=>$r['status'],
                        'created_at'=>$r['created_at'],
                        'updated_at'=>$r['updated_at'],
                    ];
                }
                $data[]=[
                    'id'=>$i['id'],
                    'type'=>$i['type'],
                    'post_id'=>$i['post_id'],
                    'user'=>['user_id'=>$i['user_id']??0,'display_name'=>$i['user']['display_name'],'avatar'=>$i['user']['avatar']?$i['user']['avatar']:null],
                    'content'=>$i['content'],
                    'files'=>$i['files'],
                    'rating'=>$i['rating'],
                    'status'=>$i['status'],
                    'reply'=>$reply,
                    'created_at'=>$i['created_at'],
                    'updated_at'=>$i['updated_at'],
                ];
            }
//            dd($data);
            return $this->responseSuccess($data);
        }
        return $this->responseError(['Không có dữ liệu.'], 204 );
    }
    public function updateStatus(Request $request,$id=0)
    {
        $info = \Auth::guard('public_user')->user()->BDCprofile;
        if($id>0){
            $this->model->updateStatus($request->status,$request->building_id,$id);
            if($request->status == 1){
                $reply=[
                    'type'=>'feedback',
                    'post_id'=>$id,
                    'parent_id'=>0,
                    'user_id'=>@$info->id,
                    'content'=>'Cảm ơn bạn đã đóng góp ý kiến.',
                ];
                $comment = $this->modelComments->create($reply);
            }
            if($request->status == 3){
                $check_reply =  Comments::where(['post_id'=>$id,'content'=>'Ban quản lý đã tiếp nhận ý kiến của cư dân.'])->first();
                if(!$check_reply){
                    $reply=[
                        'type'=>'feedback',
                        'post_id'=>$id,
                        'parent_id'=>0,
                        'user_id'=>@$info->id,
                        'content'=>'Ban quản lý đã tiếp nhận ý kiến của cư dân.',
                    ];
                    $comment = $this->modelComments->create($reply);
                }
            }
            return $this->responseSuccess([],'Status đã được update');
        }
        return $this->responseError(['Không có dữ liệu.'], 204 );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $id = (int)$request->id;

        $delete = Feedback::find($id)->delete();
        if ($delete) {
            return $this->responseSuccess([],'Xóa dữ liệu thành công');
        }
        return $this->responseError('Không xóa được dữ liệu.', self::LOGIN_FAIL);
       
    }

     /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function delete_comment(Request $request)
    {
        $id = (int)$request->id;
        $feedback_id = (int)$request->feedback_id;

        $delete = Comments::where(['post_id'=>$feedback_id,'id'=>$id])->delete();
        if ($delete) {
            return $this->responseSuccess([],'Xóa dữ liệu thành công');
        }
        return $this->responseError('Không xóa được dữ liệu.', self::LOGIN_FAIL);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function createFormRepairApartment(Request $request)
    {
        $info = \Auth::guard('public_user')->user()->info->toArray();
        if($info) {
            $files = $request->file('attached');

            $forder = date('d-m-Y');
            $directory = 'media/image/repair_apartment';
            if (!is_dir($directory)) {
                mkdir($directory);
                if (!is_dir($directory . '/' . $forder)) {
                    mkdir($directory . '/' . $forder);
                }
            }
            $url = [];$file_doc = [];
            if ($request->hasFile('attached')) {
                $expensions_doc= ['csv','doc','docx','djvu','odp','ods','odt','pps','ppsx','ppt','pptx','pdf','ps','eps','rtf','txt','wks','wps','xls','xlsx','xps','tif','tiff'];
                $expensions_image= ['gif','jpeg','jpg','jif','jfif','jp2','jpx','j2k','j2c','png'];
                foreach ($files as $file) {
                    $ext = strtolower($file->getClientOriginalExtension());
                    if(in_array($ext,$expensions_doc)){
                        $name = str_replace('.' . $ext, '', $file->getClientOriginalName());
                        iconv(mb_detect_encoding($name, mb_detect_order(), true), "UTF-8", $name);
                        $mainFilename = iconv(mb_detect_encoding($name, mb_detect_order(), true), "UTF-8", $name) . '-' . date('d-m-Y-h-i-s');

                        $file->move($directory.'/'.$forder, $mainFilename.".".$ext);
                        $file_doc[] = '/' . $directory . '/' . $forder . '/' . $mainFilename . "." . $ext;
                    }
                    if(in_array($ext,$expensions_image)){
                        $name = str_replace('.' . $ext, '', $file->getClientOriginalName());
                        iconv(mb_detect_encoding($name, mb_detect_order(), true), "UTF-8", $name);
                        $mainFilename = iconv(mb_detect_encoding($name, mb_detect_order(), true), "UTF-8", $name) . '-' . date('d-m-Y-h-i-s');

                        $file->move($directory.'/'.$forder, $mainFilename.".".$ext);
                        $url[] = '/' . $directory . '/' . $forder . '/' . $mainFilename . "." . $ext;
                    }

                }
            }
            $create = $this->model->create([
                'pub_user_profile_id' => $info['id']??1,
                'title' => $request->title,
                'content' => $request->content,
                'rating' => $request->rating ?? 0,
                'attached' => json_encode(['images' => $url, 'files' => $file_doc]),
                'type' => 'repair_apartment',
                'status' => 0,
                'bdc_building_id' => $request->building_id ?? $this->building_id ?? null,
                'app_id' => $request->app_id ?? $this->app_id ?? null,
                'bdc_apartment_id' => $request->bdc_apartment_id??0,
                'bdc_department_id' => 0,
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
                'full_name' => $request->full_name,
                'email' => $request->email,
                'phone' => $request->phone,
                'unit_name' => $request->unit_name,
                'repair_status' => FeedbackRespository::STATUS_CHUA_XY_LY
            ]);

            if ($create) {
                return $this->responseSuccess([], 'Thêm phản hồi thành công');
            }
            return $this->responseError(['Không có dữ liệu.'], 204);
        }
        return $this->responseError(['Không lấy được thông tin User'], self::LOGIN_FAIL );
    }

    public function createFormRepairApartment_v2(Request $request)
    {
        $info = Auth::guard('public_user_v2')->user()->infoApp;
        if($info) {
            $files = $request->file('attached');

            $forder = date('d-m-Y');
            $directory = 'media/image/repair_apartment';
            if (!is_dir($directory)) {
                mkdir($directory);
                if (!is_dir($directory . '/' . $forder)) {
                    mkdir($directory . '/' . $forder);
                }
            }
            $url = [];$file_doc = [];
            if ($request->hasFile('attached')) {
                $expensions_doc= ['csv','doc','docx','djvu','odp','ods','odt','pps','ppsx','ppt','pptx','pdf','ps','eps','rtf','txt','wks','wps','xls','xlsx','xps','tif','tiff'];
                $expensions_image= ['gif','jpeg','jpg','jif','jfif','jp2','jpx','j2k','j2c','png'];
                foreach ($files as $file) {
                    $ext = strtolower($file->getClientOriginalExtension());
                    if(in_array($ext,$expensions_doc)){
                        $name = str_replace('.' . $ext, '', $file->getClientOriginalName());
                        iconv(mb_detect_encoding($name, mb_detect_order(), true), "UTF-8", $name);
                        $mainFilename = iconv(mb_detect_encoding($name, mb_detect_order(), true), "UTF-8", $name) . '-' . date('d-m-Y-h-i-s');

                        $file->move($directory.'/'.$forder, $mainFilename.".".$ext);
                        $file_doc[] = '/' . $directory . '/' . $forder . '/' . $mainFilename . "." . $ext;
                    }
                    if(in_array($ext,$expensions_image)){
                        $name = str_replace('.' . $ext, '', $file->getClientOriginalName());
                        iconv(mb_detect_encoding($name, mb_detect_order(), true), "UTF-8", $name);
                        $mainFilename = iconv(mb_detect_encoding($name, mb_detect_order(), true), "UTF-8", $name) . '-' . date('d-m-Y-h-i-s');

                        $file->move($directory.'/'.$forder, $mainFilename.".".$ext);
                        $url[] = '/' . $directory . '/' . $forder . '/' . $mainFilename . "." . $ext;
                    }

                }
            }
            $create = $this->model->create([
                'pub_user_profile_id' => $info['id']??1,
                'title' => $request->title,
                'content' => $request->content,
                'rating' => $request->rating ?? 0,
                'attached' => json_encode(['images' => $url, 'files' => $file_doc]),
                'type' => 'repair_apartment',
                'status' => 0,
                'bdc_building_id' => $request->building_id ?? $this->building_id ?? null,
                'app_id' => $request->app_id ?? $this->app_id ?? null,
                'bdc_apartment_id' => $request->bdc_apartment_id??0,
                'bdc_department_id' => 0,
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
                'full_name' => $request->full_name,
                'email' => $request->email,
                'phone' => $request->phone,
                'unit_name' => $request->unit_name,
                'repair_status' => FeedbackRespository::STATUS_CHUA_XY_LY
            ]);

            if ($create) {
                return $this->responseSuccess([], 'Thêm phản hồi thành công');
            }
            return $this->responseError(['Không có dữ liệu.'], 204);
        }
        return $this->responseError(['Không lấy được thông tin User'], self::LOGIN_FAIL );
    }

    public function repairApartmentList(Request $request)
    {
        $info = Auth::guard('public_user_v2')->user()->infoApp;
        if($info){
            $per_page = $request->input('per_page', 10);
            $list_search = $this->model->searchRepairApartment($request->building_id, $request, $per_page);
            $data=[];

            foreach ($list_search as $item){
                $data[]=[
                    'id'=>$item->id,
                    'title'=>$item->title,
                    'content'=>$item->content,
                    'user'=>['display_name'=>$item->pubUserProfile->display_name??null,'user_id'=>$item->pubUserProfile->id??null,'avatar'=>$item->pubUserProfile->avatar??null],
                    'apartment'=>['id'=>$item->bdcApartment->id??null,'name'=>$item->bdcApartment->name??null],
                    'department'=>['id'=>$item->pubUserProfile->bdcDepartmentStaff->department->id??null,'name'=>$item->pubUserProfile->bdcDepartmentStaff->department->name??null],
                    'type'=>$item->type,
                    'comment_count'=>count($item->allComments),
                    'status'=>$item->status,
                    'attached'=>json_decode($item->attached,true),
                    'created_at'=>date('Y-m-d h:m:i',strtotime($item->created_at)),
                    'start_time'=>date('Y-m-d h:m:i',strtotime($item->start_time)),
                    'end_time'=>date('Y-m-d h:m:i',strtotime($item->end_time)),
                    'full_name'=>$item->full_name,
                    'email'=>$item->email,
                    'phone'=>$item->phone,
                    'unit_name'=>$item->unit_name,
                    'repair_status'=>$item->repair_status,
                ];
            }
            if($data){
                return $this->responseSuccess($data);
            }
            return $this->responseError(['Không có dữ liệu.'], 204 );
        }
        return $this->responseError(['Không lấy được thông tin User'], self::LOGIN_FAIL );

    }
}
