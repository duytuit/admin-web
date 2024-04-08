<?php

namespace App\Http\Controllers\BuildingHandbook\Api;

use App\Http\Controllers\BuildingController;
use App\Repositories\BuildingHandbook\BuildingHandbookRepository;
use App\Repositories\BuildingHandbookCategory\BuildingHandbookCategoryRepository;
use App\Repositories\BuildingHandbookType\BuildingHandbookTypeRepository;
use App\Traits\ApiResponse;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class BuildingHandbookController extends BuildingController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    use ApiResponse;

    const DUPLICATE     = 19999;
    const LOGIN_FAIL    = 10000;
    protected $model;
    protected $modelCategory;
    protected $modelTypeRepository;

    public function __construct(Request $request, BuildingHandbookRepository $model, BuildingHandbookCategoryRepository $modelCategory ,BuildingHandbookTypeRepository $modelTypeRepository)
    {
//        $this->middleware('auth', ['except'=>[]]);
//        //$this->middleware('route_permision');
        //$this->middleware('jwt.auth');
        $this->model = $model;
        $this->modelCategory = $modelCategory;
        $this->modelTypeRepository = $modelTypeRepository;
        Carbon::setLocale('vi');
        parent::__construct($request);
    }

    public function index(Request $request)
    {
        $data=[];
        $per_page = $request->input('per_page', 30);
        // $info = \Auth::guard('public_user')->user()->info()->where('bdc_building_id',$request->building_id)->first();
        $handbooks = $this->model->myPaginateApiWithID($request,$request->keyword??'', $per_page, $request->building_id, $request->category_id);
        if($request->feature == 0){
           foreach ($handbooks as $c){
            $data[]=[
                'id'=>$c->id,
                'title'=>$c->title,
                'content'=>$c->content,
                'category'=>@$c->handbook_category->name,
                'department'=>@$c->handbook_department->name ?? '',
                'partners_id'=>$c->bdc_business_partners_id ?? '',
                'url_video'=> isset($c->url_video) ? str_contains($c->url_video, 'www.youtube.com/watch') ?"<iframe width='320' height='200' src='https://www.youtube.com/embed/".explode('/watch?v=',$c->url_video)[1]."' frameborder='0' allow='accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture' allowfullscreen></iframe>"??"<iframe width='320' height='200' src='https://www.youtube.com/embed/".explode('/',$c->url_video)[3]."' frameborder='0' allow='accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture' allowfullscreen></iframe>"??null : null : null,
                'user'=>[
                    'id'=>@$c->pub_profile->id,
                    'name'=>@$c->pub_profile->display_name,
                    'phone'=>@$c->pub_profile->phone,
                    'email'=>@$c->pub_profile->email,
                    'avatar'=>@$c->pub_profile->avatar,
                ],
            ];
           }
        }else{
            foreach ($handbooks as $c){
            $data[]=[
                'id'=>$c->id,
                'title'=>$c->title,
                'avatar'=>json_decode($c->avatar,true),
                'category'=>@$c->handbook_category->name,
                'department'=>@$c->handbook_department->name ?? '',
                'partners_id'=>$c->bdc_business_partners_id ?? '',
                'user'=>[
                    'id'=>@$c->pub_profile->id,
                    'name'=>@$c->pub_profile->display_name,
                    'phone'=>@$c->pub_profile->phone,
                    'email'=>@$c->pub_profile->email,
                    'avatar'=>@$c->pub_profile->avatar,
                ],
            ];
           }
        }
        if($data){
            return $this->responseSuccess($data);
        }
        return $this->responseError(['Không có dữ liệu.'], self::LOGIN_FAIL );
    }

    public function internal(Request $request)
    {
        $data=[];
        $per_page = $request->input('per_page', 10);
        // $info = \Auth::guard('public_user')->user()->info()->where('bdc_building_id',$request->building_id)->first();
        $handbooks = $this->model->myPaginateInternalApi($request->keyword??'', $per_page, $request->building_id, 3);
        foreach ($handbooks as $c){
            $data[]=[
                'id'=>$c->id,
                'title'=>$c->title,
                'content'=>$c->content,
                'category'=>@$c->handbook_category->name,
                'department'=>@$c->handbook_department->name ?? '',
                'user'=>[
                    'id'=>@$c->pub_profile->id,
                    'name'=>@$c->pub_profile->display_name,
                    'phone'=>@$c->pub_profile->phone,
                    'email'=>@$c->pub_profile->email,
                    'avatar'=>@$c->pub_profile->avatar,
                ],
            ];
        }
        if($data){
            return $this->responseSuccess($data);
        }
        return $this->responseError(['Không có dữ liệu.'], self::LOGIN_FAIL );
    }
    public function detail(Request $request,$id)
    {
        $data=[];
        // $info = \Auth::guard('public_user')->user()->info()->where('bdc_building_id',$request->building_id)->first();
        $info_internal = $this->model->getOne($request->building_id,$id);
        if($info_internal){
            $data=[
                'id'=>$info_internal->id,
                'title'=>$info_internal->title,
                'content'=>$info_internal->content,
                'partners_id'=>$info_internal->bdc_business_partners_id ?? '',
                'url_video'=> isset($info_internal->url_video) ? str_contains($info_internal->url_video, 'www.youtube.com/watch') ?"<iframe width='320' height='200' src='https://www.youtube.com/embed/".explode('/watch?v=',$info_internal->url_video)[1]."' frameborder='0' allow='accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture' allowfullscreen></iframe>"??"<iframe width='320' height='200' src='https://www.youtube.com/embed/".explode('/',$info_internal->url_video)[3]."' frameborder='0' allow='accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture' allowfullscreen></iframe>"??null : null : null,
                'status'=>$info_internal->status,
                'created_at'=>date('d-m-Y h:i:s',strtotime($info_internal->created_at)),
                'updated_at'=>date('d-m-Y h:i:s',strtotime($info_internal->updated_at)),
                'user'=>[
                    'id'=>@$info_internal->pub_profile->id,
                    'name'=>@$info_internal->pub_profile->display_name,
                    'phone'=>@$info_internal->pub_profile->phone,
                    'email'=>@$info_internal->pub_profile->email,
                    'avatar'=>@$info_internal->pub_profile->avatar,
                ],
            ];
            if($data){
                return $this->responseSuccess($data);
            }
            return $this->responseError(['Không có dữ liệu.'], self::LOGIN_FAIL );
        }
       return $this->responseError(['Không có dữ liệu.'], self::LOGIN_FAIL );
    }


    public function category(Request $request)
    {
        $data=[];
        $per_page = $request->input('per_page', 30);
        // $info = \Auth::guard('public_user')->user()->info()->where('bdc_building_id',$request->building_id)->first();
        $categories = $this->modelCategory->myPaginateApi($request->keyword??'', $per_page,  $request->building_id,$request->type,$request->parent_id);
        foreach ($categories as $c){
            $data[]=[
                'id'=>$c->id,
                'name'=>$c->name,
                'status'=>$c->status,
                'avatar'=>json_decode($c->avatar,true),
                'parent_id'=>$c->parent_id,
                'type'=>$c->bdc_handbook_type_id,
                'phone'=>$c->phone,
            ];
        }
        if($data){
            return $this->responseSuccess($data);
        }
        return $this->responseError(['Không có dữ liệu.'], self::LOGIN_FAIL );
    }
     public function getPhoneInCategory(Request $request)
        {
            $data=[];
            $per_page = $request->input('per_page', 10);
            $categories = $this->modelCategory->getPhonePaginateApi($per_page, $request->building_id);
            foreach ($categories as $c){
                if(isset($c->phone)){
                     $data[]=[
                        'id'=>$c->id,
                        'name'=>$c->name,
                        'phone'=>$c->phone,
                    ];
                }
            }
            if($data){
                return $this->responseSuccess($data);
            }
            return $this->responseError(['Không có dữ liệu.'], self::LOGIN_FAIL );
        }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getHandbookType(Request $request)
    {
        $data=[];
        $HandbookType = $this->modelTypeRepository->myPaginate($request);
        foreach ($HandbookType as $c){
            $data[]=[
                'id'=>$c->id,
                'name'=>$c->name,
                'bdc_building_id'=>$c->bdc_building_id,
            ];
        }
        if($data){
            return $this->responseSuccess($data);
        }
        return $this->responseError(['Không có dữ liệu.'], self::LOGIN_FAIL );
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
}
