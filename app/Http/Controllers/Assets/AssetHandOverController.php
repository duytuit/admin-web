<?php

namespace App\Http\Controllers\Assets;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\BuildingController;
use Illuminate\Support\Facades\Cookie;
use App\Repositories\Assets\AssetApartmentRepository;
use App\Repositories\Assets\AssetHandOverRepository;
use App\Commons\Helper;
use DB;
use App\Models\Asset\AssetApartment;
use App\Models\Apartments\Apartments;
use App\Http\Requests\Asset\CreateAssetHandOverRequest;
use App\Http\Requests\Asset\UpdateAssetHandOverRequest;
use App\Repositories\Apartments\ApartmentsRespository;
use App\Repositories\Building\BuildingPlaceRepository;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use App\Models\Category;
use App\Repositories\Customers\CustomersRespository;

class AssetHandOverController extends BuildingController
{

    /** 
     * @var AssetApartmentRepository
     */
    protected $_assetApartmentRepository;

    /**
     * @var AssetHandOverRepository
     */
    protected $_assetHandOverRepository;

    /**
     * @var ApartmentsRespository
     */
    protected $_modelApartment;

     /**
     * @var BuildingPlaceRepository
     */
    protected $_modelBuildingPlace;

     /**
     * @var CustomersRespository
     */
    protected $_customersRespository;
    
    public function __construct(
        Request $request,
        AssetApartmentRepository $assetApartmentRepository,
        AssetHandOverRepository $assetHandOverRepository,
        ApartmentsRespository $modelApartment, 
        BuildingPlaceRepository $modelBuildingPlace,
        CustomersRespository $customersRespository
    )

    {
        //$this->middleware('route_permision');
        $this->_assetApartmentRepository = $assetApartmentRepository;
        $this->_assetHandOverRepository = $assetHandOverRepository;
        $this->_modelApartment = $modelApartment;
        $this->_modelBuildingPlace = $modelBuildingPlace;
        $this->_customersRespository = $customersRespository;
        parent::__construct($request);
    }
    public function index(Request $request)
    {
        $data['meta_title'] = 'Bàn giao tài sản';
        $data['per_page'] = Cookie::get('per_page_asset', 10);
        $data['filter'] = $request->all();
        $data['list_asset_handover'] = Helper::status_asset_apartment();
        $data['list_apartments'] = $this->_modelApartment->getApartmentOfBuildingV3($this->building_active_id);

        if(isset($data['filter']['bdc_apartment_id'])){
            $data['get_apartment'] = $this->_modelApartment->findById($data['filter']['bdc_apartment_id']);
        }
        if(isset($data['filter']['ip_place_id'])){
            $data['get_place_building'] = $this->_modelBuildingPlace->findById($data['filter']['ip_place_id']);
        }
        if(isset($data['filter']['asset_id'])){
            $data['get_asset_apartment'] = $this->_assetApartmentRepository->findById($data['filter']['asset_id']);
        }

        $data['asset_handovers'] = $this->_assetHandOverRepository->myPaginate($request,$this->building_active_id,$data['per_page']);
        return view('asset-apartments.tabs.asset-handover.list', $data);
    }
    public function create()
    {
        $data['meta_title'] = 'Thêm bàn giao tài sản';
        $data['list_asset_handover'] = Helper::status_asset_apartment();
        $data['assetCategory'] =  Category::where(['bdc_building_id'=>$this->building_active_id,'type'=> 'asset'])->get();
        return view('asset-apartments.tabs.asset-handover.create-edit', $data);
    }

    public function store(CreateAssetHandOverRequest $request)
    {
        try {
               if(isset($request->asset_ids)){
                 $asset_ids = \json_decode($request->asset_ids,true);
                 foreach ($asset_ids as $key => $value) {
                    DB::beginTransaction();
                        $this->_assetHandOverRepository->create([
                            'bdc_building_id' => $this->building_active_id,
                            'asset_apartment_id'=>  $value,
                            'apartment_id'=> $request->apartment_id,
                            'handover_person_id'=> auth()->user()->id,
                            'date_expected'=> $request->date_expected ? date('Y-m-d', strtotime($request->date_expected )) : null,
                            'warranty_period'=> $request->warranty_period,
                            'customer'=> $request->customer,
                            'email'=> $request->email,
                            'phone'=> $request->phone,
                            'description'=> $request->description,
                            'documents' => $request->attach_link_files,
                            'status'=> $request->status,
                        ]);
                        $assetApartment_1 = AssetApartment::find($value);
                        if($assetApartment_1){
                            $assetApartment_1->update([
                                'bdc_apartment_id' => $request->apartment_id
                            ]);
                        }
                    DB::commit();
                 }
                 return $this->sendSuccess_Api([],'Thêm mới thành công', route('admin.asset-apartment.asset-handover.index'));
               } 
             return $this->sendError_Api('chưa có bàn giao nào được thêm');
            
         } catch (\Exception $e) {
             DB::rollBack();
             return $this->sendError_Api($e->getMessage());
         }
       
    }
    public function change_date_of_delivery(Request $request)
    {
        try {
            $asset_handover =  $this->_assetHandOverRepository->findById($request->id);
            if($asset_handover){
                $asset_handover->update([
                    'date_of_delivery' => $request->date_of_delivery ? date('Y-m-d', strtotime($request->date_of_delivery )) : null,
                ]);
                $message = [
                    'success' => true,
                    'message' => 'Cập nhật ngày bàn giao thành công!'
                ];
                return response()->json($message);
            }
           
        } catch (\Exception $e) {
            $message = [
                'success' => false,
                'message' => 'Cập nhật ngày bàn giao thất bại!'
            ];
            return response()->json($message);
        }
    }
    public function change_status(Request $request)
    {
        try {
            $asset_handover =  $this->_assetHandOverRepository->findById($request->id);
            if($asset_handover){
                $asset_handover->update([
                    'status' => $request->status,
                    'date_of_delivery' => $request->date_of_delivery  ? date('Y-m-d', strtotime($request->date_of_delivery)) : null,
                ]);
                $message = [
                    'success' => true,
                    'message' => 'Cập nhật trạng thái thành công!'
                ];
                return response()->json($message);
            }
           
        } catch (\Exception $e) {
            $message = [
                'success' => false,
                'message' => 'Cập nhật trạng thái thất bại!'
            ];
            return response()->json($message);
        }
    }
    public function edit($id)
    {
        $data['meta_title'] = 'Sửa bàn giao tài sản';
        $asset_handover =  $this->_assetHandOverRepository->findById($id);
        if($asset_handover){
           $data['id'] = $id;
           $data['asset_handover'] = $asset_handover;
           $data['list_asset_handover'] = Helper::status_asset_apartment();
           $data['assetCategory'] =  Category::where(['bdc_building_id'=>$this->building_active_id,'type'=> 'asset'])->get();
           $apartment = Apartments::find($asset_handover->apartment_id);
           $asset = AssetApartment::find($asset_handover->asset_apartment_id);
           $data['apartment_select'] = [
             'id'=>$apartment->id,
             'text'=>$apartment->name,
           ];
           $data['asset_select'] = [
             'id'=>$asset->id,
             'text'=>$asset->code,
           ];
           return view('asset-apartments.tabs.asset-handover.create-edit', $data);
        }
        
    }

    public function update(UpdateAssetHandOverRequest $request, $id)
    {
        try {
            if(isset($request->asset_ids)){
                $asset_id = explode('"', $request->asset_ids)[1];
                DB::beginTransaction();
                    $asset_handover = $this->_assetHandOverRepository->findById($id);
                    $code = @$asset_handover->asset->code;
                    $assetApartment = AssetApartment::where('code',$code)->first();
                    if($assetApartment){
                            $assetApartment->update([
                                'bdc_apartment_id' => null
                            ]);
                    }
                    $asset_handover->update([
                        'bdc_building_id' => $this->building_active_id,
                        'asset_apartment_id'=>  $asset_id,
                        'apartment_id'=> $request->apartment_id,
                        'date_expected'=> $request->date_expected ? date('Y-m-d', strtotime($request->date_expected )) : null,
                        'warranty_period'=> $request->warranty_period,
                        'customer'=> $request->customer,
                        'email'=> $request->email,
                        'phone'=> $request->phone,
                        'description'=> $request->description,
                        'documents' => $request->attach_link_files,
                        'status'=> $request->status,
                        'updated_by'=> auth()->user()->id,
                    ]);
                    $assetApartment_1 = AssetApartment::find($asset_id);
                    if($assetApartment_1){
                        $assetApartment_1->update([
                            'bdc_apartment_id' => $request->apartment_id
                        ]);
                    }

                DB::commit();
                if($asset_handover){
                    return $this->sendSuccess_Api([],'Sửa thành công', route('admin.asset-apartment.asset-handover.index'));
                }
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError_Api($e->getMessage());
        }
        
    }

    public function action(Request $request)
    {
        return $this->_assetHandOverRepository->action($request,$this->building_active_id);
    }
    public function export(Request $request)
    {
        return $this->_assetHandOverRepository->export($request,$this->building_active_id);
    }

    public function indexImport()
    {
        $data['meta_title'] = 'import file tài sản căn hộ';
        return view('asset-apartments.tabs.asset-handover.import',$data);
    }
    public function dowload_file_import()
    {
        $file     = public_path() . '/downloads/import_ban_giao_tai_san_can_ho.xlsx';
        return response()->download($file);
    }
    public function import_store(Request $request) 
    {
        $file = $request->file('file_import');

        if(!$file) return redirect()->route('admin.asset-apartment.asset-handover.import')->with('warning', 'Chưa có file upload!');

        $path = $file->getRealPath();

        $excel_data = Excel::load($path)->get();
        storage_path('upload', $file->getClientOriginalName());

        $buildingId = $this->building_active_id;

        $data_list_error = array();


        if ($excel_data->count()) {

            foreach ($excel_data as $content) {
                if (empty($content->ma_tai_san) ||
                    empty($content->ma_can_ho) ||
                    empty($content->ngay_du_kien) ||
                    empty($content->thoi_gian_bao_hanh) ||
                    empty($content->khach_hang) ||
                    empty($content->email) ||
                    empty($content->phone) ||
                    empty($content->trang_thai)
                ) {
                    $new_content = $content->toArray();
                    $new_content['error'] = 'hãy kiểm tra lại các trường yêu cầu bắt buộc';
                    array_push($data_list_error,$new_content);
                    continue;
                }

                $assetApartment = $this->_assetApartmentRepository->findByCode($buildingId,$content->ma_tai_san); // $assetApartment->bdc_apartment_id is null: là chưa có căn hộ sử dụng , not null: đã có căn hộ sử dụng
                if (!$assetApartment) {
                    $new_content = $content->toArray();
                    $new_content['error'] =$content->ma_tai_san.'| mã tài sản không có trong hệ thống';
                    array_push($data_list_error,$new_content);
                    continue;
                }
                if($assetApartment && $assetApartment->bdc_apartment_id != null) {
                    $new_content = $content->toArray();
                    $new_content['error'] ='| đã có căn hộ '.@$assetApartment->apartment->name.' sử dụng mã tài sản này';
                    array_push($data_list_error,$new_content);
                    continue;
                }
                $apartment = $this->_modelApartment->findByCode($buildingId, $content->ma_can_ho); //is null: là không tồn tại căn hộ , not null: căn hộ đã tồn tại trên hệ thống
                if ($apartment == null) {
                    $new_content = $content->toArray();
                    $new_content['error'] ='| mã căn hộ không tồn tại trên hệ thống';
                    array_push($data_list_error,$new_content);
                    continue;
                }

                if (!strtotime($content->ngay_du_kien)) {
                    // Display valid date message
                    $new_content = $content->toArray();
                    $new_content['error'] =$content->ngay_du_kien.'| ngày dự kiến không đúng định dạng dd/mm/yyyy';
                    array_push($data_list_error,$new_content);
                    continue;
                }

                // check is number
                
                if(preg_match('/\d/', $content->thoi_gian_bao_hanh) !== 1) { // không phải là kiểu số hoặc nhỏ hơn 0
                    $new_content = $content->toArray();
                    $new_content['error'] =$content->thoi_gian_bao_hanh.'| không phải là kiểu số nguyên';
                    array_push($data_list_error,$new_content);
                    continue;
                }

                if( !filter_var($content->email, FILTER_VALIDATE_EMAIL) ) {
                     //Display valid date message
                    $new_content = $content->toArray();
                    $new_content['error'] =$content->email.'| email không đúng định dạng';
                    array_push($data_list_error,$new_content);
                    continue;
                }

                if(Helper::detect_number($content->phone) == false) { // false : là không phải số điện thoại
                     //Display valid date message
                    $new_content = $content->toArray();
                    $new_content['error'] =$content->phone.'| số điện thoại không đúng định dạng';
                    array_push($data_list_error,$new_content);
                    continue;
                }

                if(!in_array($content->trang_thai,[1,2,3])) { // false : là không phải số điện thoại
                    //Display valid date message
                   $new_content = $content->toArray();
                   $new_content['error'] = $content->trang_thai.'| trạng thái phải là số trong khoảng 1->3';
                   array_push($data_list_error,$new_content);
                   continue;
                }
                try {
                    DB::beginTransaction();
                    $this->_assetHandOverRepository->create([
                        'bdc_building_id' => $this->building_active_id,
                        'asset_apartment_id'=>  $assetApartment->id,
                        'apartment_id'=> $apartment->id,
                        'handover_person_id'=> auth()->user()->id,
                        'date_expected'=> $content->ngay_du_kien ? date('Y-m-d', strtotime($content->ngay_du_kien )) : null,
                        'date_of_delivery'=> $content->ngay_ban_giao ? date('Y-m-d', strtotime($content->ngay_ban_giao )) : null,
                        'warranty_period'=> $content->thoi_gian_bao_hanh,
                        'customer'=> $content->khach_hang,
                        'email'=> $content->email,
                        'phone'=> $content->phone,
                        'description'=> $content->mo_ta,
                        'status'=> $content->trang_thai,
                    ]);
                    $assetApartment_1 = AssetApartment::find($assetApartment->id);
                    if($assetApartment_1){
                        $assetApartment_1->update([
                            'bdc_apartment_id' => $apartment->id
                        ]);
                    }
                    DB::commit();
                } catch (\Exception $e) {
                    DB::rollBack();
                    $new_content = $content->toArray();
                    $new_content['error'] = $e->getMessage();
                    array_push($data_list_error,$new_content);
                    continue;
                }
            }
        }

        if ($data_list_error) {
            $result = Excel::create('Kết quả Import', function ($excel) use ($data_list_error) {

                $excel->setTitle('Kết quả Import');
                $excel->sheet('Kết quả Import', function ($sheet) use ($data_list_error) {
                    $row = 1;
                    $sheet->row($row, [
                        'Index',
                        'Ma_tai_san(*)',
                        'Ma_can_ho(*)',
                        'Ngay_du_kien(*)',
                        'Ngay_ban_giao',
                        'Thoi_gian_bao_hanh(*)',
                        'Khach_hang(*)',
                        'Email(*)',
                        'Phone(*)',
                        'Mo_ta',
                        'Trang_thai(*)',
                        'Error'
                    ]);

                    foreach ($data_list_error as $key => $value) {
                        $row++;
                        $sheet->row($row, [
                            ($key + 1),
                            $value['ma_tai_san'],
                            $value['ma_can_ho'],
                            $value['ngay_du_kien'],
                            $value['ngay_ban_giao'],
                            $value['thoi_gian_bao_hanh'],
                            $value['khach_hang'],
                            $value['email'],
                            $value['phone'],
                            $value['mo_ta'],
                            $value['trang_thai'],
                            $value['error'],
                        ]);
                    }
                });
            })->store('xlsx',storage_path('exports/'));
            ob_end_clean();
$file     = storage_path('exports/'.$result->filename.'.'.$result->ext);
return response()->download($file)->deleteFileAfterSend(true);
             
        } else {
            return redirect()->route('admin.asset-apartment.asset-handover.index')->with('success', 'Import file thành công!');
        }
    }
}
