<?php

namespace App\Http\Controllers\Posts\Api;

//use App\Http\Controllers\Api\V1\Controller;

use App\Commons\Helper;
use App\Helpers\dBug;
use App\Http\Controllers\BuildingController;
use App\Http\Resources\PostResource;
use App\Models\BoCustomer;
use App\Models\Comment;
use App\Models\Comments\Comments;
use App\Models\CustomerGroup;
use App\Models\PostEmotion;
use App\Models\PostFollow;
use App\Models\PostPoll;
use App\Models\PostRegister;
use App\Models\Posts\Posts;
use App\Models\PostVote;
use App\Models\PublicUser\UserInfo;
use App\Models\PublicUser\Users;
use App\Models\PublicUser\V2\User;
use App\Models\PublicUser\V2\UserInfo as V2UserInfo;
use App\Repositories\Apartments\ApartmentsRespository;
use App\Repositories\Comments\CommentsRespository;
use App\Repositories\Posts\PostsRespository;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use App\Repositories\PostEmotion\PostEmotionRepository;
use Exception;
use Validator;
use App\Repositories\Customers\CustomersRespository;
use App\Util\Debug\Log;
use Illuminate\Support\Facades\Auth;

class PostsController extends BuildingController
{
    use ApiResponse;
    /**
     * Constructor.
     */
    const DUPLICATE = 19999;
    const LOGIN_FAIL = 10000;
    const CONFIRMED = 4; // đã xác nhận
    const REFUSE = 5; // từ chối
    private $model;
    private $modelComments;
    private $modelApartments;
    private $_postEmotionRepo;
    private $modelCustomer;

    public function __construct(CustomersRespository $modelCustomer,CommentsRespository $modelComments, PostsRespository $model, ApartmentsRespository $modelApartments, PostEmotionRepository $_postEmotionRepo)
    {
        $this->model = $model;
        $this->modelComment = new Comment();
        $this->modelComments = $modelComments;
        $this->modelApartments = $modelApartments;
        $this->modelCustomer = $modelCustomer;
        $this->resource = new PostResource(null);
        $this->_postEmotionRepo = $_postEmotionRepo;
        //$this->middleware('jwt.auth');
        Carbon::setLocale('vi');
    }

    /**
     * Danh sách các bản ghi nội bộ
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        try{
            $per_page = $request->input('per_page', 10);
            $per_page = $per_page > 0 ? $per_page : 10;
            $info = Auth::guard('public_user')->user()->BDCprofile;
            $data = $this->model->searchByApi($request->building_id, $request, [], $per_page);
            foreach ($data['data'] as $key => $d) {
                $notify = json_decode($d['notify'], true);
                $emotion = $this->_postEmotionRepo->findEmotion($d['id'],$info->id);
                $data['data'][$key]['response'] = json_decode($d['response'], true);
                $data['data'][$key]['count_comment'] = $this->modelComments->countComments($d['id']);
                $data['data'][$key]['url_video'] =$d['url_video'];
                $data['data'][$key]['attaches'] = $d['attaches']?$d['attaches']:null;
                $data['data'][$key]['my_emotion'] = $emotion->emotion??null;
                $data['data'][$key]['check_comments'] = $notify['check_comments']??null;
                unset($data['data'][$key]['notify']);
            }
            if ($data) {
                return $this->responseSuccess($data['data']);
            }
            return response()->json(['status'=>true,'message'=>'không có dữ liệu'], 200);
        } catch (Exception $e) {
            return $this->responseError($e->getMessage(), self::LOGIN_FAIL);
        }
        
    }

    public function indexCustomer(Request $request)
    {
        try {
            $per_page = $request->input('per_page', 10);
            $per_page = $per_page > 0 ? $per_page : 10;
            $info = Auth::guard('public_user')->user()->info()->where('bdc_building_id',$request->building_id)->with('bdcCustomers', 'bdcCustomers.bdcApartment')->first();
            $data_cus = $this->modelCustomer->notifyCustomerConfirmed($info['bdc_building_id'], $info);
            if(isset($data_cus)){
               $data_cus = $data_cus->toArray();
            }
            $data = $this->model->searchByApiCustomer($info['bdc_building_id'], $info, $request, [], $per_page);
            foreach ($data['data'] as $key => $d) {
                $notify = json_decode($d['notify'], true);
                $emotion = $this->_postEmotionRepo->findEmotion($d['id'],$info->id);
                $data['data'][$key]['response'] = json_decode($d['response'], true);
                $data['data'][$key]['attaches'] =$d['attaches'] ? $d['attaches']->toArray() : null;
                $data['data'][$key]['url_video'] = isset($d['url_video']) ? str_contains($d['url_video'], 'www.youtube.com/watch') ?"<iframe width='320' height='200' src='https://www.youtube.com/embed/".explode('/watch?v=',$d['url_video'])[1]."' frameborder='0' allow='accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture' allowfullscreen></iframe>"??"<iframe width='320' height='200' src='https://www.youtube.com/embed/".explode('/',$d['url_video'])[3]."' frameborder='0' allow='accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture' allowfullscreen></iframe>"??null : null : null;
                $data['data'][$key]['my_emotion'] = $emotion->emotion??null;
                $data['data'][$key]['apartment_handover'] = null;
                $data['data'][$key]['apartment_handover_status'] = null;
                if($d['lists_notify_apartment'] && $data_cus){
                    $get_id_cus = json_decode($d['lists_notify_apartment'], true);
                    foreach ($get_id_cus as $key_1 => $value) {
                        $find_cus = $this->modelCustomer->findCusWithStatus_Refuse($value)->toArray();
                        // nếu khách hàng từ chối thì không hiển thị thông báo
                        if($find_cus){
                           $data['data'][$key]['apartment_handover_status'] = 'tu_choi';
                        }
                        if(isset($data_cus[0]) && $value == $data_cus[0]['id']){
                           $data['data'][$key]['apartment_handover'] = $data_cus[0];
                           break;
                        }
                    }
                }
                
                $data['data'][$key]['check_comments'] = $notify['check_comments']??null;
                unset($data['data'][$key]['notify']);
            }
            if ($data) {
                return $this->responseSuccess($data['data']);
            }
            return $this->responseError(['Không có dữ liệu.'], self::LOGIN_FAIL);
        } catch (Exception $e) {
            return $this->responseError($e->getMessage(), self::LOGIN_FAIL);
        }
    }

    public function indexCustomer_v2(Request $request)
    {
        try {
            $per_page = $request->input('per_page', 10);
            $per_page = $per_page > 0 ? $per_page : 10;
            $info = Auth::guard('public_user_v2')->user();
            $data_cus = $this->modelCustomer->notifyCustomerConfirmed($request->building_id, $info);
            if(isset($data_cus)){
               $data_cus = $data_cus->toArray();
            }
            $data = $this->model->searchByApiCustomer_v2($request->building_id, $info, $request, [], $per_page);
           
            foreach ($data as $key => $d) {
                $user_info = null;
                if(@$d->new == 1){
                    $user_info = V2UserInfo::where('user_id',$d->user_id)->first();
                }else{
                    $user_info =  UserInfo::where(['bdc_building_id' => $request->building_id, 'pub_user_id' => $d->user_id, 'type' => 2])->first();
                }
                $user= [
                    "id"=> $d->user_id,
                    "email"=> $user_info->email_contact ?? $user_info->email,
                ];
                $profile_all = [
                    "id"  => $user_info->id,
                    "pub_user_id"  => $d->user_id,
                    "display_name"  => $user_info->full_name ??  $user_info->display_name,
                    "phone"  => $user_info->phone_contact ?? $user_info->phone,
                    "email"  => $user_info->email_contact ?? $user_info->email,
                    "address"  => null,
                    "type"  => 2,
                    "cmt"  => null,
                    "cmt_nc"  => null,
                    "avatar"  => null,
                    "bdc_building_id"  => (int)$request->building_id,
                    "birthday"  => null,
                    "gender"  => 3,
                    "status"  => 1,
                    "app_id"  => "buildingcare",
                    "staff_code"  => null,
                    "deleted_by"  => null,
                    "deleted_at"  => null,
                    "type_profile"  => 0,
                    "config_fcm"  => null,
                    "cmt_address"  => null,
                    "customer_code_prefix"  => null,
                    "customer_code"  => null
                ];
                $user['profile_all']=$profile_all;
                $notify = json_decode($d['notify'], true);
                $emotion = $this->_postEmotionRepo->findEmotion($d['id'], $info->id);
                $data[$key]['response'] = json_decode($d['response'], true);
                $data[$key]['attaches'] = $d['attaches'];
                $data[$key]['url_video'] = isset($d['url_video']) ? str_contains($d['url_video'], 'www.youtube.com/watch') ? "<iframe width='320' height='200' src='https://www.youtube.com/embed/" . explode('/watch?v=', $d['url_video'])[1] . "' frameborder='0' allow='accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture' allowfullscreen></iframe>" ?? "<iframe width='320' height='200' src='https://www.youtube.com/embed/" . explode('/', $d['url_video'])[3] . "' frameborder='0' allow='accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture' allowfullscreen></iframe>" ?? null : null : null;
                $data[$key]['my_emotion'] = $emotion->emotion ?? null;
                $data[$key]['apartment_handover'] = null;
                $data[$key]['apartment_handover_status'] = null;
                $data[$key]['user'] = $user;
                if ($d['lists_notify_apartment'] && $data_cus) {
                    $get_id_cus = json_decode($d['lists_notify_apartment'], true);
                    foreach ($get_id_cus as $key_1 => $value) {
                        $find_cus = $this->modelCustomer->findCusWithStatus_Refuse($value)->toArray();
                        // nếu khách hàng từ chối thì không hiển thị thông báo
                        if ($find_cus) {
                            $data[$key]['apartment_handover_status'] = 'tu_choi';
                        }
                        if (isset($data_cus[0]) && $value == $data_cus[0]['id']) {
                            $data[$key]['apartment_handover'] = $data_cus[0];
                            break;
                        }
                    }
                }

                $data[$key]['check_comments'] = $notify['check_comments'] ?? null;
                unset($data[$key]['notify']);
            }
            if ($data) {
                return $this->responseSuccess($data->toArray()['data']);
            }
            return $this->responseError(['Không có dữ liệu.'], self::LOGIN_FAIL);
        } catch (Exception $e) {
            return $this->responseError($e->getMessage(), self::LOGIN_FAIL);
        }
    }

    public function notifyCustomer(Request $request)
    {
        try {

            // $info = Auth::guard('public_user')->user()->info()->where('bdc_building_id',$request->building_id)->with('bdcCustomers', 'bdcCustomers.bdcApartment')->first();
            // $data = $this->modelCustomer->notifyCustomer($info['bdc_building_id'], $info)->toArray();

            // if ($data) {
            //     return $this->responseSuccess($data);
            // }

            return $this->responseError(['Không có dữ liệu.'], self::LOGIN_FAIL);
        } catch (Exception $e) {
            return $this->responseError($e->getMessage(), self::LOGIN_FAIL);
        }
    }
    public function customerConfirm(Request $request)
    {
        try {

            if($request->status_confirm == 1){ //4 Đã xác nhận
              $data = $this->modelCustomer->change_success_handover_customer_confirm($request->id,$request->description, $request->status_confirm,self::CONFIRMED,null);
            }
            if($request->status_confirm == 0){  // 5 Từ chối
              $data = $this->modelCustomer->change_success_handover_customer_confirm($request->id,$request->description, $request->status_confirm,self::REFUSE,1);
            }
            if(isset($request->post_id) && $request->post_id != null){
               $get_post = $this->model->getPostById((int)$request->post_id);
               $array_lists_notify_apartment = json_decode($get_post->lists_notify_apartment, true);
               if (($key = array_search($request->id, $array_lists_notify_apartment)) !== false) {
                    unset($array_lists_notify_apartment[$key]);
                }
                $get_post->lists_notify_apartment = json_encode(array_values($array_lists_notify_apartment));
                $get_post->save();
            }
            if($data){
                return $this->responseSuccess([],'xác nhận thành công.');
            }
            return $this->responseError('xác nhận thất bại', self::LOGIN_FAIL);
        } catch (Exception $e) {
            return $this->responseError($e->getMessage(), self::LOGIN_FAIL);
        }
    }
    public function destroy(Request $request)
    {
        $id = (int)$request->id;

        $delete = Posts::find($id)->delete();
        if ($delete) {
            return $this->responseSuccess([],'Xóa dữ liệu thành công');
        }
        return $this->responseError('Không xóa được dữ liệu.', self::LOGIN_FAIL);
    }
    public function delete(Request $request)
    {
        $id = (int)$request->id;
        $post_id = (int)$request->post_id;

        $delete = Comments::where(['post_id'=>$post_id,'id'=>$id])->delete();
        if ($delete) {
            return $this->responseSuccess([],'Xóa dữ liệu thành công');
        }
        return $this->responseError('Không xóa được dữ liệu.', self::LOGIN_FAIL);
    }
    public function detail(Request $request, $id = 0)
    {
        $info = Auth::guard('public_user')->user()->BDCprofile()->where('bdc_building_id',$request->building_id)->first();

        if ($id > 0) {
            $data = $this->model->selectByApi(['*'], 'id', $id, $request->building_id);
            $emotion = $this->_postEmotionRepo->findEmotion($id,$info->id);
            $attaches = [];
            if(isset($data) && $data->attaches) {
                foreach ($data->attaches as $item) {
                    $attaches[] = $item['src'];
                }
            }
            if($data){
                $show = [
                    'id' => $data->id,
                    'title' => $data->title,
                    'type' => $data->type,
                    'start_at' => $data->start_at,
                    'end_at' => $data->end_at,
                    'response' => json_decode($data->response,true),
                    'my_emotion' => $emotion->emotion??null,
                    'publish_at' => $data->publish_at,
                    'image' => $data->image,
                    'images' => $data->images,
                    'url_video' => $data->url_video,
                    'attaches' => $attaches,
                    'address' => $data->address,
                    'qr_code' => $data->qr_code,
                    'summary' => $data->summary,
                    'content' => preg_replace('/\/media/','https://bdcapi.dxmb.vn/media',$data->content),
                    'category_id' => $data->category_id,
                    'user' => ['email' => @$data->user->email, 'display_name' => @$data->user->profileAll->display_name, 'phone' => @$data->user->profileAll->phone, 'avatar' => @$data->user->profileAll->avatar]
                ];
                return $this->responseSuccess($show);
            }
           
        }
        return $this->responseError(['Không có dữ liệu.'], self::LOGIN_FAIL);
    }

    public function detailCus(Request $request, $id = 0)
    {
        $info = Auth::guard('public_user_v2')->user();
        if ($id > 0) {
            $data = $this->model->selectByApi(['*'], 'id', $id, $request->building_id);
            if (!$data) {
                return $this->responseError(['Không có dữ liệu.'], 204);
            }
            $emotion = $this->_postEmotionRepo->findEmotion($id, $info->id);
            $attaches = [];
            if (isset($data->attaches)) {
                foreach ($data->attaches as $item) {
                    $attaches[] = url($item['src']);
                }
            }
            $show = [
                'id' => $data->id,
                'title' => $data->title,
                'type' => $data->type,
                'start_at' => $data->start_at,
                'end_at' => $data->end_at,
                'response' => json_decode($data->response, true),
                'my_emotion' => $emotion->emotion ?? null,
                'publish_at' => $data->publish_at,
                'image' => $data->image,
                'images' => $data->images,
                'attaches' => $attaches,
                'address' => $data->address,
                'url_video' => isset($data->url_video) ? str_contains($data->url_video, 'www.youtube.com/watch') ? "<iframe width='320' height='200' src='https://www.youtube.com/embed/" . explode('/watch?v=', $data->url_video)[1] . "' frameborder='0' allow='accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture' allowfullscreen></iframe>" ?? "<iframe width='320' height='200' src='https://www.youtube.com/embed/" . explode('/', $data->url_video)[3] . "' frameborder='0' allow='accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture' allowfullscreen></iframe>" ?? null : null : null,
                'qr_code' => $data->qr_code,
                'summary' => $data->summary,
                'content' => $data->content,
                'category_id' => $data->category_id,
                'user' => ['email' => @$data->user->email, 'display_name' => @$data->user->profileAll->display_name, 'phone' => @$data->user->profileAll->phone, 'avatar' => @$data->user->profileAll->avatar]
            ];
            $notify = json_decode($data->notify, true);
            $show['check_comment'] = $notify['check_comments'] ?? null;
            unset($data->notify);
            if ($data) {
                if ($data->type) {
                    $show['post_resister'] = $this->isRegistered($data, $data->type);
                }
                $show['post_checkin'] = $this->isCheckIn($data);
                $show['response'] = json_decode($data->response, true);
                $show['qr_url'] =  route('api.v1.checkIn', ['post_id' => $id, 'building_id' => $request->building_id]);
                return $this->responseSuccess($show);
            }
            return $this->responseError(['Không có dữ liệu.'], 204);
        }
        return $this->responseError(['Không có dữ liệu.'], 204);
    }

    public function comments(Request $request, $id = 0)
    {
        $per_page = $request->input('per_page', 10);
        $per_page = $per_page > 0 ? $per_page : 10;
        if ($id > 0) {
            $data = [];
            $comment_main = $this->modelComments->listCommentsPost($id, $request->type, $per_page);
            foreach ($comment_main as $i) {
                $reply = $this->modelComments->listCommentsPost($id, $request->type, $per_page, $i->id);
                $user = null;
                if($i->new == 1){
                    $user = V2UserInfo::where('user_id',$i->user_id)->first();
                }else{
                    $user = UserInfo::find($i->user_id);
                }
                $data_reply=null;
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

    public function commentsCustomer(Request $request, $id = 0)
    {
        $per_page = $request->input('per_page', 10);
        $per_page = $per_page > 0 ? $per_page : 10;
        $order_by = 'id ASC';
        if ($id > 0) {
            $data = [];
            $post = $this->model->getOneSelect('type', 'id', $id);
            $comment_main = $this->modelComments->listCommentsPost($id, $post->type, $per_page, 0);

            foreach ($comment_main as $i) {
                $data_reply = [];
                $reply = $this->modelComments->listCommentsPost($id, $post->type, $per_page, $i->id);

                
                foreach ($reply as $r) {

                    $data_reply[] = [
                        'id' => $r->id,
                        'type' => $r->type,
                        'post_id' => $r->post_id,
                        'user' => ['user_id' => $r->user_id ?? 0, 'display_name' => @$r->userInfo->display_name?? (@$r->userInfo->type == 2 ? 'Ban quản lý':'Cư dân'), 'avatar' => @$r->userInfo->avatar ? url("/").'/'. @$r->userInfo->avatar : null],
                        'content' => $r->content,
                        'files' => $r->files,
                        'rating' => $r->rating,
                        'status' => $r->status,
                        'created_at' => $r->created_at,
                        'updated_at' => $r->updated_at,
                    ];
                }

                $data[] = [
                    'id' => $i->id,
                    'type' => $i->type,
                    'post_id' => $i->post_id,
                    'user' => ['user_id' => $i->user_id ?? 0, 'display_name' => @$i->userInfo->display_name?? (@$i->userInfo->type == 2 ? 'Ban quản lý':'Cư dân'), 'avatar' => @$i->userInfo->avatar ? url("/") .'/'. @$i->userInfo->avatar : null],
                    'content' => $i->content,
                    'files' => $i->files,
                    'rating' => $i->rating,
                    'status' => $i->status,
                    'reply' => !empty($data_reply) ? $data_reply : [],
                    'created_at' => $i->created_at,
                    'updated_at' => $i->updated_at,
                ];
            }
            return $this->responseSuccess($data);
        }
        return $this->responseError(['Không có dữ liệu.'], self::LOGIN_FAIL);
    }

    public function getComment(Request $request, $id = 0)
    {
        if ($id > 0) {
            $data = [];
            $comment_main = $this->modelComments->listCommentsById('id', $id);
            foreach ($comment_main as $i) {
                $reply = $this->modelComments->listCommentsByIdPerpage('parent_id', $i['id']);
                $data[] = [
                    'id' => $i['id'],
                    'type' => $i['type'],
                    'post_id' => $i['post_id'],
                    'user_id' => $i['user_id'],
                    'content' => $i['content'],
                    'files' => $i['files'],
                    'rating' => $i['rating'],
                    'status' => $i['status'],
                    'reply' => $reply,
                    'created_at' => $i['created_at'],
                    'updated_at' => $i['updated_at'],
                ];
            }
            return $this->responseSuccess($data);
        }
        return response()->json(['status'=>true,'message'=>'không có dữ liệu' ,'data'=>[]], 200);
    }

    public function reply(Request $request, $id = 0)
    {
        $info = Auth::guard('public_user')->user()->infoWeb->toArray();
        if ($info) {
            if ($id > 0) {
                $post = $this->model->getOneSelect(['id', 'type','response'], 'id', $id);
                $data = [
                    'type' => $post->type,
                    'post_id' => $post->id,
                    'parent_id' => $request->reply_to,
                    'user_id' => $info[0]['id'] ?? 1,
                    'content' => $request->content,
                    'rating' => $request->rating
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
                $comment=  $this->modelComments->create($data);
                $this->modelComments->savePostResponse($post);
                return $this->responseSuccess($comment ? $comment->toArray() :[], 'Bình luận thành công');
            }
            return $this->responseError(['Không có id bài viết'], 204);
        }
        return $this->responseError(['Không lấy được thông tin User'], self::LOGIN_FAIL);
    }

    public function replyCustomer(Request $request, $id = 0)
    {
        $info = Auth::guard('public_user_v2')->user()->infoApp;
        if ($info) {
            if ($id > 0) {
                $post = $this->model->getOneSelect(['id', 'type', 'notify','response'], 'id', $id);
                $notify = json_decode($post->notify, true);
                if ($notify['check_comments'] == 1) {
                    $status = 0;
                } else {
                    $status = 1;
                }
                $data = [
                    'type' => $post->type,
                    'post_id' => $post->id,
                    'parent_id' => $request->reply_to,
                    'user_id' => $info['id'] ?? 1,
                    'content' => $request->content,
                    'rating' => $request->rating,
                    'status' => $status
                ];
                $this->modelComments->create($data);

                
                $this->modelComments->savePostResponse($post);
                return $this->responseSuccess([], 'Bình luận thành công');
            }
            return $this->responseError(['Không có id bài viết'], 204);
        }
        return $this->responseError(['Không lấy được thông tin User'], self::LOGIN_FAIL);
    }

    protected function isRegistered(&$posts, $type)
    {
       // $info = Auth::guard('public_user')->user()->info->toArray();
        $info = Auth::guard('public_user_v2')->user();
        if ($info) {
            $register = PostRegister::where('user_id', $info['id'])
                ->where('user_type', 'user')
                ->where('post_type', $type)
                ->pluck('post_id')
                ->toArray();

        } else {
            $register = [];
        }
        $registered = in_array($posts['id'], $register);
        return $registered;
    }

    protected function isCheckIn(&$posts)
    {
        $post_ids = $posts['id'];

        $register = PostRegister::select('post_id')
            ->where('post_id', $post_ids)
            ->whereNull('check_in')
            ->pluck('post_id')
            ->toArray();
        $items = [];

        if ($posts['end_at'] > Carbon::now()) {
            if (in_array($posts['id'], $register)) {
                $check_in = 0;
            } else {
                $check_in = 1;
            }
        } else {
            $check_in = -1;
        }
        return $check_in;
    }

    public function updateResponse($id, $emotion = '', $previous_emotion = '')
    {
        $post = $this->model->find($id);

        if (!$post->response) {
            $data['response']['emotion'] = [];
        } else {
            $data['response'] = json_decode($post->response, true);
        }
        if (isset($data['response']['emotion'])){
            // Khi người dùng thay đổi trạng thái
            if ($previous_emotion != '' && $emotion != '') {
                $data['response']['emotion'][$previous_emotion]--;

                if (array_key_exists($emotion, $data['response']['emotion'])) {
                    $data['response']['emotion'][$emotion]++;
                } else {
                    $data['response']['emotion'] = array_merge($data['response']['emotion'], [$emotion => 1]);
                }
            }

            // Khi người dùng bỏ trạng thái
            if ($emotion == '') {
                $data['response']['emotion'][$previous_emotion]--;
            }

            // Khi người dùng bày tỏ trạng thái lần đầu tiên
            if ($previous_emotion == '' && $emotion != '') {
                if (array_key_exists($emotion, $data['response']['emotion'])) {
                    $data['response']['emotion'][$emotion]++;
                } else {
                    $data['response']['emotion'] = array_merge($data['response']['emotion'], [$emotion => 1]);
                }
            }

            $data['response'] = json_encode($data['response']);
        }else{
            if($emotion){
                $data['response']['emotion'] = [$emotion=>1];
            }
            $data['response'] = json_encode($data['response']);
        }

//        dd($data);
     $this->model->update($data, $id);
//        dd($aaa);

    }
}
