<?php

namespace App\Http\Controllers\Feedback;

use App\Http\Controllers\BuildingController;
use App\Repositories\Apartments\ApartmentsRespository;
use App\Repositories\Comments\CommentsRespository;
use App\Repositories\Feedback\FeedbackRespository;
use App\Repositories\PublicUsers\PublicUsersProfileRespository;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cookie;
use App\Models\Asset\AssetApartment;
use App\Models\Apartments\Apartments;
use App\Models\PublicUser\UserInfo;
use App\Http\Requests\Feedback\CreateWarrantyClaimRequest;
use App\Models\Feedback\Feedback;

class FeedbackController extends BuildingController
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


    public function __construct(FeedbackRespository $model, PublicUsersProfileRespository $modelUserProfile, ApartmentsRespository $modelApartment, CommentsRespository $modelComments,Request $request)
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
        if($request->keyword || $request->type || $request->rating || $request->status|| $request->name|| $request->apartment_id|| $request->floor){
            $advance = 1;
        }
        $feedback = $this->model->searchBy($this->building_active_id,$request,[],'fback',$data['per_page']);
        $data_search = [
            'keyword'        => '',
            'type'         => '',
            'rating'          => '',
            'status'          => '',
            'name'          => '',
        ];
        $data['data_search'] = $request->data_search ?: $data_search;
        $data['data_search']['keyword'] = $request->keyword;
        $data['data_search']['type'] = $request->type;
        $data['data_search']['rating'] = $request->rating;
        $data['data_search']['status'] = $request->status;
        if($request->name){
            $name = $this->modelUserProfile->findBy('id',$request->name,'display_name');
            $data['data_search']['name'] = $request->name;
            $data['data_search']['name_profile'] = $name->display_name;
        }
        if($request->apartment_id){
            $data['data_search']['apartment'] = $this->modelApartment->findById($request->apartment_id);
        }
        $data['data_search']['floor'] = $request->floor;
        $data['floors'] = $this->modelApartment->getApartmentFloor();
        $data['feedback'] = $feedback;

        $data['heading']    = 'Ý kiến phản hồi';
        $data['meta_title'] = "QL Ý kiến phản hồi";

        $data['types'] = [
            'user'    => 'Nhân viên',
            'product' => 'Sản phẩm',
            'service' => 'Dịch vụ',
            'other'   => 'Khác',
        ];
        $data['advance'] = $advance;
        return view('feedback.index', $data);
    }

    public function indexRequest(Request $request)
    {
        $advance = 0;
        $data['per_page'] = Cookie::get('per_page', 20);
        $data['type']        = 'request';

        if($request->keyword || $request->type || $request->rating || $request->status|| $request->name|| $request->apartment_id|| $request->floor){
            $advance = 1;
        }
        $feedback = $this->model->searchBy($this->building_active_id,$request,[],'request',$data['per_page']);
        $data_search = [
            'keyword'        => '',
            'type'         => '',
            'rating'          => '',
            'status'          => '',
            'name'          => '',
        ];
        $data['data_search'] = $request->data_search ?: $data_search;
        $data['data_search']['keyword'] = $request->keyword;
        $data['data_search']['type'] = $request->type;
        $data['data_search']['rating'] = $request->rating;
        $data['data_search']['status'] = $request->status;
        if($request->name){
            $name = $this->modelUserProfile->findBy('id',$request->name,'display_name');
            $data['data_search']['name'] = $request->name;
            $data['data_search']['name_profile'] = $name->display_name;
        }
        if($request->apartment_id){
            $data['data_search']['apartment'] = $this->modelApartment->findById($request->apartment_id);
        }
        $data['data_search']['floor'] = $request->floor;
        $data['floors'] = $this->modelApartment->getApartmentFloor();
        $data['feedback'] = $feedback;

        $data['heading']    = 'Ý kiến phản hồi';
        $data['meta_title'] = "QL Ý kiến phản hồi";

        $data['types'] = [
            'user'    => 'Nhân viên',
            'product' => 'Sản phẩm',
            'service' => 'Dịch vụ',
            'other'   => 'Khác',
        ];

        $data['advance'] = $advance;
        return view('feedback.index', $data);
    }

    public function detail(Request $request, $id = 0)
    {
        $feedback = $this->model->findIdFB($id);

        $data['id']       = $id;
        $data['now']      = Carbon::now();
        $data['feedback'] = $feedback;
        $data['colors']   = ['#008a00', '#0050ef', '#6a00ff', '#a20025', '#fa6800', '#825a2c', '#6d8764'];
        $data['types'] = [
            'user'    => 'Nhân viên',
            'userinfo'    => 'Nhân viên profile',
            'product' => 'Sản phẩm',
            'service' => 'Dịch vụ',
            'other'   => 'Khác',
            'fback'   => 'Ý kiến cư dân',
            'request'   => 'Form yêu cầu',
            'repair_apartment'   => 'Đăng ký sửa chữa',
        ];
        $data['listComments'] = $this->modelComments->listComments($id,'feedback');
        $data['meta_title'] = "QL Ý kiến phản hồi";
        $data['feedbackRespository'] = $this->model;
        // if($feedback->status == 0){
        //     $feedback->update(['status'=>3]); // // ban quản lý đã tiếp nhận ý kiến cư dân
        // }
       
        return view('feedback.detail', $data);
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        
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

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {

    }
    public function action(Request $request)
    {
        return $this->model->action($request);
    }
    public function ajaxGetSelectUserProfile(Request $request)
    {
        if ($request->search) {
            return response()->json($this->modelUserProfile->searchByNomal($request->search));
        }
        return response()->json($this->modelUserProfile->searchByNomal(''));
    }
    public function ajaxSearch(Request $request)
    {
        $data['searchs'] = $this->model->searchBy($this->building_active_id,$request);
        $data['searchs']->load("pubUserProfile");
        return view('feedback.sub-views.table', $data);
    }

    public function repairApartment(Request $request)
    {
        $advance = 0;
        $data['per_page'] = Cookie::get('per_page', 20);
        $data['type']        = FeedbackRespository::TYPE_REPAIR_APARTMENT;

        if($request->keyword || $request->type || $request->rating || $request->status|| $request->name|| $request->apartment_id|| $request->floor){
            $advance = 1;
        }
        $feedback = $this->model->searchBy($this->building_active_id, $request, [], FeedbackRespository::TYPE_REPAIR_APARTMENT, $data['per_page']);
        $data_search = [
            'keyword'        => '',
            'type'           => '',
            'rating'         => '',
            'status'         => '',
            'name'           => '',
        ];
        $data['data_search'] = $request->data_search ?: $data_search;
        $data['data_search']['keyword'] = $request->keyword;
        $data['data_search']['type'] = $request->type;
        $data['data_search']['rating'] = $request->rating;
        $data['data_search']['repair_status'] = $request->repair_status;
        if($request->name){
            $name = $this->modelUserProfile->findBy('id',$request->name,'display_name');
            $data['data_search']['name'] = $request->name;
            $data['data_search']['name_profile'] = $name->display_name;
        }
        if($request->apartment_id){
            $data['data_search']['apartment'] = $this->modelApartment->findById($request->apartment_id);
        }
        $data['data_search']['floor'] = $request->floor;
        $data['floors'] = $this->modelApartment->getApartmentFloor();
        $data['feedback'] = $feedback;

        $data['heading']    = 'Đăng ký sửa chữa';
        $data['meta_title'] = "Đăng ký sửa chữa";

        $data['types'] = [
            'user'    => 'Nhân viên',
            'product' => 'Sản phẩm',
            'service' => 'Dịch vụ',
            'other'   => 'Khác',
        ];

        $data['advance'] = $advance;
        return view('feedback.repair-apartment', $data);
    }

    public function repairApartmentCreate(Request $request)
    {
        $data['building_id'] = $this->building_active_id;
        $data['meta_title'] = "Thêm mới đăng ký sửa chữa";

        $data['apartments'] = $this->modelApartment->getApartmentOfBuildingDebit($this->building_active_id);

        return view('feedback.repair-apartment-create', $data);
    }

    public function repairApartmentStore(Request $request)
    {
        $files = $request->file('attached');

        $forder = date('d-m-Y');
        $directory = 'media/image/repair_apartment';
        if (!is_dir($directory)) {
            mkdir($directory);
            if (!is_dir($directory . '/' . $forder)) {
                mkdir($directory . '/' . $forder);
            }
        }
        $url = [];
        $file_doc = [];
        if ($request->hasFile('attached')) {
            $expensions_doc = ['csv', 'doc', 'docx', 'djvu', 'odp', 'ods', 'odt', 'pps', 'ppsx', 'ppt', 'pptx', 'pdf', 'ps', 'eps', 'rtf', 'txt', 'wks', 'wps', 'xls', 'xlsx', 'xps', 'tif', 'tiff'];
            $expensions_image = ['gif', 'jpeg', 'jpg', 'jif', 'jfif', 'jp2', 'jpx', 'j2k', 'j2c', 'png'];
            foreach ($files as $file) {
                $ext = $file->getClientOriginalExtension();
                if (in_array($ext, $expensions_doc)) {
                    $name = str_replace('.' . $ext, '', $file->getClientOriginalName());
                    iconv(mb_detect_encoding($name, mb_detect_order(), true), "UTF-8", $name);
                    $mainFilename = iconv(mb_detect_encoding($name, mb_detect_order(), true), "UTF-8", $name) . '-' . date('d-m-Y-h-i-s');

                    $file->move($directory . '/' . $forder, $mainFilename . "." . $ext);
                    $file_doc[] = '/' . $directory . '/' . $forder . '/' . $mainFilename . "." . $ext;
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
        $create = $this->model->create([
            'pub_user_profile_id' => $info['id'] ?? 1,
            'title' => $request->title,
            'content' => $request->content,
            'rating' => $request->rating ?? 0,
            'attached' => json_encode(['images' => $url, 'files' => $file_doc]),
            'type' => 'repair_apartment',
            'status' => 0,
            'bdc_building_id' => $this->building_active_id,
            'app_id' => $request->app_id ?? $this->app_id ?? null,
            'bdc_apartment_id' => $request->bdc_apartment_id ?? 0,
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
            return redirect('admin/feedback/repair-apartment-create')->with('success', 'Thêm đăng ký sửa chữa thành công.');
        }
        return redirect('admin/feedback/repair-apartment-create')->with('error', 'Thêm đăng ký sửa chữa thất bại.');
    }

    public function repairChangeStatus(Request $request)
    {
        return $this->model->repairChangeStatus($request->ids);
    }

    public function repairChangeStatusV2(Request $request)
    {
        return $this->model->repairChangeStatus($request->ids, $request->repair_status);
    }
    public function repairChangeStatusV3(Request $request)
    {
        return $this->model->repairChangeStatusV2($request->ids, $request->repair_status);
    }

    // ========================== Yêu cầu bảo hành tài sản căn hộ ===============================
    public function warranty_claim(Request $request)
    {
        $advance = 0;
        $data['per_page'] = Cookie::get('per_page', 20);
        $data['type']        = FeedbackRespository::TYPE_WARRANTY_CLAIM;

        if($request->keyword || $request->type || $request->rating || $request->status|| $request->name|| $request->apartment_id|| $request->floor){
            $advance = 1;
        }
        $feedback = $this->model->searchBy($this->building_active_id, $request, [], FeedbackRespository::TYPE_WARRANTY_CLAIM, $data['per_page']);
        $data_search = [
            'keyword'        => '',
            'type'           => '',
            'rating'         => '',
            'status'         => '',
            'name'           => '',
        ];
        $data['data_search'] = $request->data_search ?: $data_search;
        $data['data_search']['keyword'] = $request->keyword;
        $data['data_search']['type'] = $request->type;
        $data['data_search']['rating'] = $request->rating;
        $data['data_search']['repair_status'] = $request->repair_status;
        if($request->name){
            $name = $this->modelUserProfile->findBy('id',$request->name,'display_name');
            $data['data_search']['name'] = $request->name;
            $data['data_search']['name_profile'] = $name->display_name;
        }
        if($request->apartment_id){
            $data['data_search']['apartment'] = $this->modelApartment->findById($request->apartment_id);
        }
        $data['data_search']['floor'] = $request->floor;
        $data['floors'] = $this->modelApartment->getApartmentFloor();
        $data['feedback'] = $feedback;

        $data['heading']    = 'Đăng ký bảo hành tài sản căn hộ';
        $data['meta_title'] = "Đăng ký bảo hành tài sản căn hộ";

        $data['types'] = [
            'user'    => 'Nhân viên',
            'product' => 'Sản phẩm',
            'service' => 'Dịch vụ',
            'other'   => 'Khác',
            'warranty_claim'   => 'Bảo hành tài sản',
        ];

        $data['advance'] = $advance;
        return view('feedback.warranty_claim', $data);
    }

    public function warrantyClaimEdit($id)
    {
        $data['meta_title'] = 'Sửa đăng ký bảo hành';
        $feedback_warrantyClaim =  $this->model->findIdFB($id);
        if($feedback_warrantyClaim){
           $data['id'] = $id;
           $data['warranty_claim'] = $feedback_warrantyClaim;
           $apartment = Apartments::find($feedback_warrantyClaim->bdc_apartment_id);
           $asset = AssetApartment::find($feedback_warrantyClaim->bdc_asset_apartment_id);
           $data['apartment_select'] = [
             'id'=>$apartment->id,
             'text'=>$apartment->name,
           ];
           $data['asset_select'] = [
             'id'=>$asset->id,
             'text'=>$asset->code,
           ];
           return view('feedback.warranty_claim_create', $data);
        }
        
    }

    public function warrantyClaimUpdate(Request $request, $id)
    {
        try {
            $asset =  $this->_assetApartmentRepository->findById($id)->update([
                'building_place_id' => $request->building_place_id,
                'asset_category_id' => $request->asset_category_id,
                'code' => preg_replace('/\s+/', '_',Helper::convert_vi_to_en($request->code)).'_'.$this->building_active_id.'_'.AssetApartment::count(),
                'name' => $request->name,
                'description'=> $request->description,
                'documents' => $request->attach_link_files,
                'updated_by'=> auth()->user()->id,
            ]);
            if($asset){
                return $this->sendSuccess_Api([],'Sửa thành công', route('admin.asset-apartment.asset.index'));
            }
           
        } catch (\Exception $e) {
            return $this->sendError_Api($e->getMessage());
        }
        
    }

    public function warrantyClaimCreate(Request $request)
    {
        $data['building_id'] = $this->building_active_id;
        $data['meta_title'] = "Thêm mới đăng ký bảo hành tài sản căn hộ";

        $data['apartments'] = $this->modelApartment->getApartmentOfBuildingDebit($this->building_active_id);

        return view('feedback.warranty_claim_create', $data);
    }

    public function warrantyClaimStore(CreateWarrantyClaimRequest $request)
    {
        $file_doc=[];
        $url=[];
        if($request->attach_link_files){
            $expensions_doc = ['csv', 'doc', 'docx', 'djvu', 'odp', 'ods', 'odt', 'pps', 'ppsx', 'ppt', 'pptx', 'pdf', 'ps', 'eps', 'rtf', 'txt', 'wks', 'wps', 'xls', 'xlsx', 'xps', 'tif', 'tiff'];
            $expensions_image = ['gif', 'jpeg', 'jpg', 'jif', 'jfif', 'jp2', 'jpx', 'j2k', 'j2c', 'png'];
            $attach_link_files = json_decode($request->attach_link_files,true);
            foreach ($attach_link_files as $key => $value) {
                $path_parts = pathinfo($value);

                if(in_array($path_parts['extension'], $expensions_doc)){  // file doc
                    array_push($file_doc,$value);
                }

                if(in_array($path_parts['extension'], $expensions_image)){  // file image
                    array_push($url,$value);
                }

            }
        }
        $user_info = UserInfo::where(['bdc_building_id'=>$this->building_active_id,'pub_user_id'=>auth()->user()->id])->first();
        try {
            $create = $this->model->create([
                'pub_user_profile_id' => $user_info ? $user_info->id : 1,
                'title' => $request->title,
                'content' => $request->description,
                'rating' => $request->rating ?? 0,
                'attached' => json_encode(['images' => $url, 'files' => $file_doc]),
                'type' => FeedbackRespository::TYPE_WARRANTY_CLAIM,
                'status' => 0,
                'bdc_building_id' => $this->building_active_id,
                'app_id' => $request->app_id ?? $this->app_id ?? null,
                'bdc_apartment_id' => $request->bdc_apartment_id ?? 0,
                'bdc_department_id' => 0,
                'start_time' => $request->start_time ? date('Y-m-d', strtotime($request->start_time )) : null,
                'end_time' => $request->end_time ? date('Y-m-d', strtotime($request->end_time )) : null,
                'full_name' => $request->full_name,
                'email' => $request->email,
                'phone' => $request->phone,
                'repair_status' => FeedbackRespository::STATUS_CHUA_XY_LY,
                'bdc_asset_apartment_id' => $request->asset_id,
                'user_id' => auth()->user()->id
            ]);
            if ($create) {
                return $this->sendSuccess_Api([],'Thêm mới thành công', route('admin.feedback.warrantyClaim'));
            }
            return $this->sendError_Api('Thêm mới thất bại');
            
         } catch (\Exception $e) {
             return $this->sendError_Api($e->getMessage());
         }
       
    }
    public function ajaxGetSelectFeedback(Request $request)
    {

        if ($request->search) {
            $where[] = ['title', 'like', '%' . $request->search . '%'];
            return response()->json($this->model->searchAjaxByAll(['where' => $where], $this->building_active_id));
        }
        return response()->json($this->model->searchAjaxByAll(['select' => ['id', 'title']], $this->building_active_id));
    }
}
