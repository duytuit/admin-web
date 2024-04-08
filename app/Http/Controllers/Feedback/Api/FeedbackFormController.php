<?php

namespace App\Http\Controllers\Feedback\Api;

use App\Http\Controllers\BuildingController;
use App\Models\Comment;
use App\Models\Comments\Comments;
use App\Models\Post;
use App\Models\PublicUser\UserInfo;
use App\Repositories\Comments\CommentsRespository;
use App\Repositories\Feedback\FeedbackFormRespository;
use App\Repositories\Feedback\FeedbackRespository;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Apartments\V2\UserApartments;
use Illuminate\Support\Facades\Auth;

class FeedbackFormController extends BuildingController
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
    public function __construct(FeedbackFormRespository $model,CommentsRespository $modelComments,Request $request)
    {
        // $this->middleware('auth', ['except'=>[]]);
        $this->model = $model;
        $this->modelComments = $modelComments;
        //$this->middleware('jwt.auth');
        parent::__construct($request);
    }

    public function index(Request $request)
    {
        $info = Auth::guard('public_user_v2')->user()->infoApp;
        $user_apartment = UserApartments::where('user_info_id',$info->id)->first();
        if($info){
            $per_page = $request->input('per_page', 10);
            $lists = $this->model->searchByApi($request->building_id??@$user_apartment->building_id,$request,[],$per_page);
            $data=[];
            foreach ($lists as $item){
                $data[]=[
                    'id'=>$item->id,
                    'title'=>$item->title,
                    'url'=>url('/').'/'.$item->url,
                    'hint'=>$item->hint,
                    'building_id'=>$item->bdc_building_id,
                    'status'=>$item->status
                ];
            }
            if($data){
                return $this->responseSuccess($data);
            }
            return $this->responseError(['Không có dữ liệu.'], 204 );
        }
        return $this->responseError(['Không lấy được thông tin User'], self::LOGIN_FAIL );
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */


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
}
