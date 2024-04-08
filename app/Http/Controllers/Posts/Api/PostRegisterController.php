<?php

namespace App\Http\Controllers\Posts\Api;

use App\Http\Resources\PostResource;
use App\Models\Comment;
use App\Models\Post;
use App\Repositories\Apartments\ApartmentsRespository;
use App\Repositories\Comments\CommentsRespository;
use App\Repositories\PostEmotion\PostEmotionRepository;
use App\Repositories\Posts\PostRegisterRespository;
use App\Repositories\Posts\PostsRespository;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class PostRegisterController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    use ApiResponse;
    /**
     * Constructor.
     */
    const DUPLICATE     = 19999;
    const LOGIN_FAIL    = 10000;
    private $model;
    private $modelPostRegister;
    private $modelComments;
    private $modelApartments;
    private $_postEmotionRepo;

    public function __construct(CommentsRespository $modelComments,PostsRespository $model,ApartmentsRespository $modelApartments, PostEmotionRepository $_postEmotionRepo, PostRegisterRespository $modelPostRegister)
    {
        $this->model    = $model;
        $this->modelComment    = new Comment();
        $this->modelComments    = $modelComments;
        $this->modelApartments    = $modelApartments;
        $this->modelPostRegister    = $modelPostRegister;
        $this->resource = new PostResource(null);
        $this->_postEmotionRepo = $_postEmotionRepo;
        //$this->middleware('jwt.auth');
        Carbon::setLocale('vi');
    }

    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
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
    public function destroy($id)
    {
        //
    }

    public function add(Request $request)
    {
        return $this->register($request);
    }

    public function remove(Request $request)
    {
        return $this->register($request);
    }

    protected function register(Request $request)
    {
        $rating = $request->rating ?? 0;

        $post = $this->getPost($request);
        $info = Auth::guard('public_user_v2')->user()->infoApp;
        $type = $post->type;

        // Nếu sự kiện hay voucher thuộc loại nội bộ
        // if ($post->private == 1) {
        //     if ($info->type == '1') {
        //         $check = $this->model->checkRegisters($info->id,json_decode($post->notify,true),$request->building_id);
        //         if (!$check) {
        //             return $this->responseError(['Bạn không thuộc diện đăng ký tham gia sự kiện này.'], 500 );
        //         }
        //     } else {
        //         return $this->responseError(['Bạn không thuộc diện đăng ký tham gia sự kiện này.'], 500 );
        //     }
        // }

        if ($type == 'voucher') {
            // Kiểm tra xem còn voucher còn không
            $count = $post->usedVoucher();
            if ($count <= 0) {
                return $this->responseError(['Hiện tại voucher đã hết.'], 422 );
            }
        }

        // delete register if exist
        $del = $this->modelPostRegister->delCheckExit($post, $info);

        // insert register
        if ($del==0) {
            $param         = [
                'post_id'   => $post->id,
                'code'      => $this->randomString(),
                'post_type' => $post->type,
                'user_id'   => $info->id,
                'user_type' => 'user',
                'user_name' => $info->full_name,
                'new' => 1,
            ];
            $this->modelPostRegister->saveRegister($param);
        }

        $this->savePostResponse($post);
        $this->savePostResponseCheckin($post);
        if($del > 0){
            return $this->responseSuccess([],'Hủy đăng ký thành công',200);
        }
        return $this->responseSuccess(['code' => $param['code']],'Đăng ký thành công',200);
    }


    protected function getPost($request)
    {
        $post_id = $request->post_id;
        $post = $this->model->getPostRegister(['id', 'type', 'response', 'private', 'notify', 'number'],$post_id,$request->type);
        return $post;
    }

    protected function savePostResponse(&$post)
    {
        $total= $this->modelPostRegister->countRegister($post);
        $response = json_decode($post->response,true);
        $response['register'] = $total;
        $post->response = json_encode($response);
        $update = $this->model->update($post->toArray(),$post['id'],'id');
        return $update;
    }
    protected function savePostResponseCheckin(&$post)
    {
        $total= $this->modelPostRegister->countCheckin($post);
        $response = json_decode($post->response,true);
        $response['check_in'] = $total;
        $post->response = json_encode($response);
        $update = $this->model->update($post->toArray(),$post['id'],'id');
        return $update;
    }

    protected function randomString($length = 6)
    {
        $str        = "";
        $characters = array_merge(range('A', 'Z'), range('0', '9'));
        $max        = count($characters) - 1;
        for ($i = 0; $i < $length; $i++) {
            $rand = mt_rand(0, $max);
            $str .= $characters[$rand];
        }
        return $str;
    }

    public function checkIn(Request $request)
    {
        $info = \Auth::guard('public_user')->user()->profileAll()->where('bdc_building_id',$request->building_id)->first();

        $post_id = $request->post_id;
        $posts = $this->model->getPostById($post_id);
        $time_checkin = time();
        $start_post = strtotime($posts['start_at']);
        $end_post = strtotime($posts['end_at']);
        if($start_post <= $time_checkin && $time_checkin <= $end_post){
            $register = $this->modelPostRegister->getItem($post_id,$info);
            if ($register) {
                if ($register->check_in) {
                    return $this->responseError(['Mã đã được sử dụng.'], 200 );
                }else{
                    $register->check_in = Carbon::now();
                    $register->save();
                    $this->savePostResponseCheckin($posts);
                    return $this->responseSuccess([],'Check in thành công',200);
                }
            }else{

                return $this->responseError(['Không thành công. Bạn vui lòng kiểm tra lại.'], 200 );
            }
        }else{
            return $this->responseError(['Đã quá thời gian checkin'], 200 );
        }
    }
}
