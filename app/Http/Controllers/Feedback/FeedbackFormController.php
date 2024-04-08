<?php

namespace App\Http\Controllers\Feedback;

use App\Commons\Helper;
use App\Http\Controllers\BuildingController;
use App\Repositories\Apartments\ApartmentsRespository;
use App\Repositories\Comments\CommentsRespository;
use App\Repositories\Feedback\FeedbackFormRespository;
use App\Repositories\Feedback\FeedbackRespository;
use App\Repositories\PublicUsers\PublicUsersProfileRespository;
use App\Repositories\SystemFiles\SystemFilesRespository;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Feedback\FeedbackForm;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;

class FeedbackFormController extends BuildingController
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


    public function __construct(FeedbackFormRespository $model, PublicUsersProfileRespository $modelUserProfile, ApartmentsRespository $modelApartment, CommentsRespository $modelComments,Request $request)
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
        $feedback_form = $this->model->searchBy($this->building_active_id,$request,[],$data['per_page']);
        $data_search = [
            'keyword'        => '',
            'status'          => ''
        ];
        $data['data_search'] = $request->data_search ?: $data_search;
        $data['data_search']['keyword'] = $request->keyword;
        $data['data_search']['status'] = $request->status;
        $data['lists'] = $feedback_form;
        $data['heading']    = 'Mẫu form yêu cầu';
        $data['meta_title'] = "QL mẫu form yêu cầu";
        $data['advance'] = $advance;
        return view('feedbackform.index', $data);
//        dd($feedback_form);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $data['building_id'] = $this->building_active_id;
        $data['meta_title'] = "Thêm mới form mẫu";
        $data['filter'] = $request->all();
        $data['form_registers'] = Helper::form_register_service;
        $data['param_registers'] = $request->type ? Helper::form_register_service[$request->type] : null;
        $data['form_type'] = $request->type ? $request->type : null;
        return view('feedbackform.create', $data);
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
    public function edit(Request $request, $id = 0)
    {

        $data['meta_title'] = "QL form đăng ký";

        $form_register = FeedbackForm::findOrFail($id);

        $data['filter']['type'] = $request->type ?? $form_register->type;

        $data['form_register'] = $form_register;

        $data['form_registers'] = Helper::form_register_service;

        $data['param_registers'] =@$request->type || @$form_register->type  ? Helper::form_register_service[$request->type ??$form_register->type] : null;

        $data['form_type'] =  @$request->type ?? @$form_register->type;

        return view('feedbackform.create', $data);
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

    public function save(Request $request,SystemFilesRespository $systemfile,$id = 0)
    {
        $data = $request->only(['bdc_building_id', 'title', 'hint','status','type','content']);
        $data['title'] = @$request->type ? Helper::form_register_service[$request->type]['title'] : null;
        if($request->url){
            $form_template =  $systemfile->checkMultiFile($request->url,'fb_form',$this->building_active_id,Auth::user());
            $data = array_merge($data,['url'=>$form_template['data']['url']]);
        }
        if ($id > 0) {
            $data['id'] = $id;
            $insert = FeedbackForm::findOrFail($id);
            $insert->fill($data)->save();
        } else {
            $insert = $this->model->create($data);
        }
        if(!$insert){
            return redirect()->route('admin.feedbackform.create')->with('error', 'Cập nhập form mẫu không thành công!');
        }
        return redirect()->route('admin.feedbackform.index')->with('success', 'Cập nhập form mẫu thành công!');
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
