<?php

namespace App\Http\Controllers\SystemFiles;

use App\Http\Controllers\BuildingController;
use App\Http\Requests\SystemFiles\SystemFilesRequest;
use App\Repositories\Apartments\ApartmentsRespository;
use App\Repositories\SystemFiles\SystemFilesRespository;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cookie;

class SystemFilesController extends BuildingController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    private $model;
    private $modelApartment;


    public function __construct(SystemFilesRespository $model,ApartmentsRespository $modelApartment,Request $request)
    {
        //$this->middleware('route_permision');
        $this->model = $model;
        $this->modelApartment = $modelApartment;
        parent::__construct($request);
    }

    public function index(Request $request)
    {
        $data['meta_title'] = 'System files';
        $data['per_page'] = Cookie::get('per_page', 20);
        $files = $this->model->searchFiles($request, [],$data['per_page']);
        $data['files'] = $files;
        return view('systemfiles.index',$data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(SystemFilesRequest $request)
    {
        $checkFile = $this->model->checkFile($request,'file_apartment');
        if($checkFile['status'] == 'NOT_OK'){
            return redirect()->route('admin.systemfiles')->with('error', $checkFile['error']);
        }
        $data=[
            'building_id'=>0,
            'name'=>$request->name,
            'description'=>$request->description?$request->description:'',
            'type'=>$checkFile['data']['type'],
            'url'=>$checkFile['data']['url'],
            'model_type'=>'apartment',
            'model_id'=>$request->bdc_apartment_id,
            'status'=>0
        ];
        $insertFile = $this->model->create($data);
        if(!$insertFile){
            return redirect()->route('admin.systemfiles.index')->with('error', 'Thêm file không thành công!');
        }
        return redirect()->route('admin.systemfiles.index')->with('success', 'Cập nhật file thành công!');
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
        $data['meta_title'] = 'edit Vehicles';
        $file = $this->model->getOne('id',$id);
        $data['file'] = $file;
        $data['apartment'] = $this->modelApartment->findById($file->model_id);
        return view('systemfiles.edit',$data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(SystemFilesRequest $request, $id)
    {
        if($request->file_apartment){
            $checkFile = $this->model->checkFile($request,'file_apartment');
            if($checkFile['status'] == 'NOT_OK'){
                return redirect()->route('admin.systemfiles')->with('error', $checkFile['error']);
            }
        }else{
            $checkFile = $this->model->getOne('id',$id);
        }
        $data=[
            'building_id'=>0,
            'name'=>$request->name,
            'description'=>$request->description?$request->description:'',
            'type'=>$checkFile['data']['type']??$checkFile->type??'',
            'url'=>$checkFile['data']['url']??$checkFile->url??'',
            'model_type'=>'apartment',
            'model_id'=>$request->bdc_apartment_id,
            'status'=>0
        ];
        $update = $this->model->update($data,$id);
        if(!$update){
            return redirect()->route('admin.systemfiles.index')->with('error', 'Cập nhật file không thành công!');
        }
        return redirect()->route('admin.systemfiles.index')->with('success', 'Cập nhật file thành công!');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $this->model->delete(['id'=>$id]);
        return back()->with('success', 'Xóa file thành công!');
    }

    public function ajaxChangeStatus(Request $request)
    {
        if($request->status == 0){
            $this->model->update(['status'=>1],$request->id,'id');
           return response()->json(['status'=>1]);
        }
        $this->model->update(['status'=>0],$request->id,'id');
        return response()->json(['status'=>0]);
    }
    public function download(Request $request)
    {
        $file     = storage_path().'/'. $request->path;
        return response()->download($file);
    }
}
