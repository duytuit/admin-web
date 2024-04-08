<?php

namespace App\Http\Controllers\Banks\Config;

use App\Http\Controllers\BuildingController;
use App\Repositories\Apartments\ApartmentsRespository;
use App\Repositories\Banks\BanksRespository;
use App\Repositories\Comments\CommentsRespository;
use App\Repositories\Feedback\FeedbackFormRespository;
use App\Repositories\Feedback\FeedbackRespository;
use App\Repositories\PublicUsers\PublicUsersProfileRespository;
use App\Repositories\SystemFiles\SystemFilesRespository;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;

class BanksController extends BuildingController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    private $model;
    private $modelUserProfile;
    private $modelApartment;
    private $modelComments;


    public function __construct(BanksRespository $model, PublicUsersProfileRespository $modelUserProfile, ApartmentsRespository $modelApartment, CommentsRespository $modelComments,Request $request)
    {
        //$this->middleware('route_permision');
        $this->model = $model;
        $this->modelUserProfile = $modelUserProfile;
        $this->modelApartment = $modelApartment;
        $this->modelComments = $modelComments;
        parent::__construct($request);
    }

    public function index(Request $request)
    {
        $advance = 0;
        $data['per_page'] = Cookie::get('per_page', 20);
        $data['type']        = 'fback';

        if($request->keyword || $request->status){
            $advance = 1;
        }
        $banks = $this->model->searchBy($this->building_active_id,$request,[],$data['per_page']);
        $data_search = [
            'keyword'        => '',
            'status'          => ''
        ];
        $data['data_search'] = $request->data_search ?: $data_search;
        $data['data_search']['keyword'] = $request->keyword;
        $data['data_search']['status'] = $request->status;
        $data['lists'] = $banks;
        $data['heading']    = 'Danh sách ngân hàng';
        $data['meta_title'] = "QL danh sách ngân hàng";
        $data['advance'] = $advance;

        return view('banks.index', $data);
//        dd($feedback_form);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $data['building_id'] = $this->building_active_id;
        $data['meta_title'] = "Thêm mới Ngân hàng";
        return view('banks.create', $data);
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

    public function save(Request $request,SystemFilesRespository $systemfile)
    {
        if($request->url){
            $form_template =  $systemfile->checkMultiFile($request->logo,'banks',$this->building_active_id,Auth::user());
        }
        $input = $request->only(['bdc_building_id', 'title', 'url','status','alias']);
        $data = array_merge($input,['logo'=>$form_template['data']['url'],'app_name'=>$this->app_id]);
        $insert = $this->model->create($data);
        if(!$insert){
            return redirect()->route('admin.banks.create')->with('error', 'Thêm ngân hàng không thành công!');
        }
        return redirect()->route('admin.banks.index')->with('success', 'Thêm ngân hàng thành công!');
    }
    public function download(Request $request)
    {
//        dd($request->all());
        $file     = storage_path().'/'.$request->url;
        return response()->download($file);
    }
    public function action(Request $request)
    {
        return $this->model->action($request);
    }
}
