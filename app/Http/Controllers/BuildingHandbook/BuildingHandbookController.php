<?php

namespace App\Http\Controllers\BuildingHandbook;

use App\Http\Controllers\BuildingController;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Validator;
use App\Http\Requests\BuildingHandbook\BuildingHandbookRequest;
use App\Repositories\BuildingHandbook\BuildingHandbookRepository;
use App\Repositories\Department\DepartmentRepository;
use App\Repositories\BusinessPartners\BusinessPartnerRepository;
use App\Repositories\BuildingHandbookCategory\BuildingHandbookCategoryRepository;
use App\Repositories\BuildingHandbookType\BuildingHandbookTypeRepository;
use App\Repositories\PublicUsers\PublicUsersProfileRespository;

class BuildingHandbookController extends BuildingController
{
    private $model;
    private $categoryRepository;
    private $departmentRepository;
    private $typeRepository;
    private $userRepository;
    private $businessPartnerRepository;
    // private $auth_id;

    /**
     * Constructor.
     */
    public function __construct(
        Request $request,
        BuildingHandbookRepository $model,
        BuildingHandbookCategoryRepository $categoryRepository,
        BusinessPartnerRepository $businessPartnerRepository,
        DepartmentRepository $departmentRepository,
        BuildingHandbookTypeRepository $typeRepository,
        PublicUsersProfileRespository $userRepository
    )
    {
        //$this->middleware('route_permision');
        $this->model = $model;
        $this->categoryRepository = $categoryRepository;
        $this->departmentRepository = $departmentRepository;
        $this->businessPartnerRepository = $businessPartnerRepository;
        $this->typeRepository = $typeRepository;
        $this->userRepository = $userRepository;
        parent::__construct($request);
        // $this->auth_id          = \Auth::user()->getUserInfoId($this->building_active_id)->id;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($id = 0, Request $request)
    {
        // handbooks
        $data = $this->getAttribute();
        $data['meta_title'] = 'Cẩm nang tòa nhà';
        $data['per_page_handbook'] = Cookie::get('per_page_handbook', 10);
        $data['per_page_handbook_category'] = Cookie::get('per_page_handbook', 10);

        $data['filter_handbook'] = $request->all();
        $data['handbook_keyword'] = $request->input('handbook_keyword', '');

        if ($id == 0 ) {
            $data['handbooks'] = $this->model->myPaginate($data['filter_handbook'], $data['per_page_handbook'], $this->building_active_id);
        } else {
            $data['handbooks'] = $this->categoryRepository->find($id)->bdhs()->get();
        }

        // handbook-category
        $data['filter_handbook_category'] = $request->all();
        $data['handbook_categories'] = $this->categoryRepository->myPaginate($data['filter_handbook_category'], $data['per_page_handbook_category'],  $this->building_active_id);
        $data['handbook_categories_keyword'] = $request->input('handbook_categories_keyword', '');
        $data['handbook_types'] =$this->typeRepository->myPaginate($data['filter_handbook_category']);
        $data['keyword'] = $request->input('keyword', '');

        return view('building-handbook.index', $data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(BuildingHandbookRequest $request)
    {
        $data                    = $request->except('_token');
        $data['pub_profile_id']  = \Auth::user()->getUserInfoId($this->building_active_id)->id;
        // $data['pub_profile_id']  = $this->auth_id;
        $data['status']          = $request->input('status', 0);
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

        $responseData = [
            'success' => true,
            'message' => 'Thêm mới cẩm nang thành công!',
            'href' => route('admin.building-handbook.index')
        ];

        return response()->json($responseData);
    }

    public function update(BuildingHandbookRequest $request, $id = 0)
    {
        $data = $request->except('_token');
        $data['status'] = $request->input('status', 0);
        $data['feature'] = $request->input('feature', 1);

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


        $responseData = [
            'success' => true,
            'message' => 'Cập nhật cẩm nang thành công!',
            'href' => route('admin.building-handbook.index')
        ];

        return response()->json($responseData);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\BuildingHandbook  $buildingHandbook
     * @return \Illuminate\Http\Response
     */
    public function edit($id = 0)
    {
        $data = $this->getAttribute();

        $handbook = $this->model->find($id);

        $data['meta_title'] = 'Cẩm nang tòa nhà';

        $data['id'] = $id;

        if($handbook) {
            // edit
            $data['bdh_category'] = ($id == 0) ? '' : $handbook->bdc_handbook_category_id;
            $data['bdh_type'] = ($id == 0) ? '' : $handbook->bdc_handbook_type_id;
            $data['bdh_status'] = ($id == 0) ? '' : $handbook->status;
            $data['bdh_feature'] = ($id == 0) ? '' : $handbook->feature;
            $data['bdh_department'] = ($id == 0) ? '' : $handbook->department_id;
            $data['bdh_department'] = ($id == 0) ? '' : $handbook->avatar;
            $data['bdh_partners'] = ($id == 0) ? '' : $handbook->bdc_business_partners_id;
            $data['bdh_avatar']= ($id == 0) ? '' : json_decode($handbook->avatar,true);
            $data['bdh'] = $handbook;
            $data['type_categories'] = $this->categoryRepository->findByTypeAndBuildingId($handbook->bdc_handbook_type_id, $this->building_active_id);

            return view('building-handbook.edit', $data);
        }

        if( $id == 0 ) {
            // create
            return view('building-handbook.edit', $data);
        }

        return redirect()->back()->with('error', 'không tìm thấy cẩm nang');

    }

    private function getAttribute()
    {
        return [
            'categories' => $this->categoryRepository->findByBuildingId($this->building_active_id),
            'users'      => $this->userRepository->findByBuildingId($this->building_active_id),
            'types'      => $this->typeRepository->all(),
            'partners' =>  $this->businessPartnerRepository->getPartnersWithStatus($this->building_active_id),
            'departments'=> $this->departmentRepository->listDepartmentsNew($this->building_active_id)->get(),
        ];
    }

    public function delete(Request $request)
    {
        $id = $request->input('ids');
        $this->model->delete(['id' => $id]);

        $request->session()->flash('success', 'Xóa cẩm nang thành công');
    }

    public function ajaxGetCategory(Request $request)
    {
        $handbook_type_id = $request->input('bdc_handbook_type_id');
        $categories       = $this->categoryRepository->findByTypeAndBuildingId($handbook_type_id, $this->building_active_id);
        $all_categories   = $this->categoryRepository->findByBuildingId($this->building_active_id);

        return \response()->json(['categories' => $categories->toArray(), 'all_categories' => $all_categories]);
    }

    public function ajaxDeleteMultiHandbook(Request $request)
    {
        $this->model->deleteMulti($request->ids);
        $dataResponse = [
            'success' => true,
            'message' => 'Xóa cẩm nang thành công!'
        ];
        return response()->json($dataResponse);
    }

    public function ajaxChangeStatus($id)
    {
        $data['status'] = 1;
        $this->model->update($data, $id);

        return \response()->json(['status' => $data['status']]);
    }

    public function action(Request $request)
    {
        if ($request->has('per_page_handbook')) {
            $per_page = $request->input('per_page_handbook', 10);
            Cookie::queue('per_page_handbook', $per_page, 60 * 24 * 30);
            Cookie::queue('tab_handbook', $request->tab);
        }

        if ($request->has('per_page_handbook_category')) {
            $per_page_maintenance = $request->input('per_page_handbook_category', 10);
            Cookie::queue('per_page_handbook_category', $per_page_maintenance, 60 * 24 * 30);
            Cookie::queue('tab_handbook_category', $request->tab);
        }

        return redirect()->back()->with('tab', $request->tab);
    }
}
