<?php

namespace App\Http\Controllers\BuildingHandbookCategory;

use App\Http\Controllers\BuildingController;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Validator;
use App\Http\Requests\BuildingHandbookCategory\BuildingHandbookCategoryRequest;
use App\Repositories\BuildingHandbookCategory\BuildingHandbookCategoryRepository;

class BuildingHandbookCategoryController extends BuildingController
{
    private $model;
    /**
     * Constructor.
     */
    public function __construct( BuildingHandbookCategoryRepository $model, Request $request )
    {
        //$this->middleware('route_permision');
        $this->model = $model;
        parent::__construct($request);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(BuildingHandbookCategoryRequest $request)
    {
        $data = $request->except('_token');
        // set default parent_id == 0
        $data['parent_id'] = isset($data['parent_id']) ? $data['parent_id'] : 0;
        $data['bdc_building_id'] = $this->building_active_id;
        $files_name = $request->input('name_fileupload');
        $url_image;$name_image;
        if($files_name){
            $name_image=$files_name;
            $directory = 'media/image/avatar';
                if (!is_dir($directory)) {
                    mkdir($directory);
                }
            $file_doc =$_SERVER['DOCUMENT_ROOT'].'/' . $directory . '/' . $request->input('name_fileupload');
            $url_image='/' . $directory . '/'  . $request->input('name_fileupload');
            $file = fopen($file_doc, "wb");
            $input = explode(',',  $request->input('fileBase64'));
            fwrite($file, base64_decode($input[1]));
            fclose($file);
            $data['avatar'] =  json_encode(['name_image' => $name_image, 'url_image' => $url_image]);
            unset($data["fileBase64"]); 
            unset($data["name_fileupload"]); 
        }
        $this->model->create($data);

        $dataResponse = [
            'success' => true,
            'message' => 'Thêm mới danh mục thành công!'
        ];
        return response()->json($dataResponse);
    }

    public function update(BuildingHandbookCategoryRequest $request, $id = 0)
    {
        $data = $request->except('_token');
        // set default parent_id == 0
        $data['parent_id'] = ($data['parent_id'] != null) ? $data['parent_id'] : 0;
         $files_name = $request->input('name_fileupload');
        $url_image;$name_image;
        if($files_name){
            $name_image=$files_name;
            $directory = 'media/image/avatar';
                if (!is_dir($directory)) {
                    mkdir($directory);
                }
            $file_doc =$_SERVER['DOCUMENT_ROOT'].'/' . $directory . '/' . $request->input('name_fileupload');
            $url_image='/' . $directory . '/'  . $request->input('name_fileupload');
            $file = fopen($file_doc, "wb");
            $input = explode(',',  $request->input('fileBase64'));
            fwrite($file, base64_decode($input[1]));
            fclose($file);
            $data['avatar'] =  json_encode(['name_image' => $name_image, 'url_image' => $url_image]);
            unset($data["fileBase64"]); 
            unset($data["name_fileupload"]); 
        }
        $this->model->update($data, $id);

        $dataResponse = [
            'success' => true,
            'message' => 'Cập nhật danh mục thành công!'
        ];
        return response()->json($dataResponse);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\BuildingHandbookCategory  $buildingHandbook
     * @return \Illuminate\Http\Response
     */
    public function edit($id = 0)
    {
        $data['id'] = $id;

        $handbook_cat = $this->model->find($id);

        if( $id == 0 ) {
            // create
            $data['parent_id'] = '';

        }

        if( $handbook_cat != null ) {
            // edit
            $data['parent_categories']    = $this->model->findByTypeAndBuildingId($handbook_cat->bdc_handbook_type_id, $this->building_active_id)->except($id);
            $data['category_name']        = $handbook_cat->name;
            $data['category_phone']        = $handbook_cat->phone;
            $data['parent_id']            = $handbook_cat->parent_id;
            $data['bdc_handbook_type_id'] = $handbook_cat->bdc_handbook_type_id;
            $data['avatar'] = json_decode($handbook_cat->avatar,true) ?? null;
        }

        return \response()->json($data);


    }

    public function delete(Request $request)
    {
        $id = $request->input('ids');
        $this->model->delete(['id' => $id]);

        $request->session()->flash('success', 'Xóa danh mục thành công');
    }

    public function ajaxChangeStatus($id)
    {
        $category = $this->model->find($id);

        // if inactive
        if( $category->status == 0 ) {
            $data['status'] = 1;
        }

        // if active
        if( $category->status == 1 ) {
            $data['status'] = 0;
        }

        $this->model->update($data, $id);

        return \response()->json(['status' => $data['status']]);
    }

    public function ajaxDeleteMulti(Request $request)
    {
        $this->model->deleteMulti($request->ids);
        $dataResponse = [
            'success' => true,
            'message' => 'Xóa danh mục thành công!'
        ];

        return response()->json($dataResponse);
    }
}
