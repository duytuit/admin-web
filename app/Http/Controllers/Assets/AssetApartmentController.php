<?php

namespace App\Http\Controllers\Assets;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\BuildingController;
use Illuminate\Support\Facades\Cookie;
use App\Repositories\Assets\AssetApartmentRepository;
use App\Repositories\Assets\AssetHandOverRepository;
use Illuminate\Support\Facades\Storage;
use App\Commons\Helper;
use App\Models\Asset\AssetApartment;
use App\Models\Building\BuildingPlace;
use App\Http\Requests\Asset\CreateAssetApartmentRequest;
use App\Http\Requests\Asset\UpdateAssetApartmentRequest;
use App\Models\Category;
use App\Repositories\Building\BuildingPlaceRepository;
use Maatwebsite\Excel\Facades\Excel;
use DB;

class AssetApartmentController extends BuildingController
{

    public const asset = 'asset';

    /**
     * @var AssetApartmentRepository
     */
    protected $_assetApartmentRepository;

    /**
     * @var AssetHandOverRepository
     */
    protected $_assetHandOverRepository;

    /**
     * @var BuildingPlaceRepository
     */
    protected $_buildingPlaceRepository;

    public function __construct(
        Request $request,
        AssetApartmentRepository $assetApartmentRepository,
        AssetHandOverRepository $assetHandOverRepository,
        BuildingPlaceRepository $buildingPlaceRepository
    )

    {
        ////$this->middleware('route_permision');
        $this->_assetApartmentRepository = $assetApartmentRepository;
        $this->_assetHandOverRepository = $assetHandOverRepository;
        $this->_buildingPlaceRepository = $buildingPlaceRepository;
        parent::__construct($request);
    }
    public function index(Request $request)
    {
        $data['meta_title'] = 'Bàn giao tài sản';
        $data['per_page'] = Cookie::get('per_page_asset', 10);
        $data['filter'] = $request->all();
        $data['assets'] = $this->_assetApartmentRepository->myPaginate($request,$this->building_active_id,$data['per_page']);
        return view('asset-apartments.tabs.asset.list', $data);
    }
    public function indexCategory(Request $request)
    {
         return redirect()->route('admin.categories.index', ['type' => 'asset']);
    }
    public function create()
    {
        $data['meta_title'] = 'Thêm tài sản';
        $data['assetCategory'] =  Category::where(['bdc_building_id'=>$this->building_active_id,'type'=> self::asset])->get();
        return view('asset-apartments.tabs.asset.create-edit', $data);
    }

    public function store(CreateAssetApartmentRequest $request)
    {
        $repeat_asset = $request->number > 0 ? $request->number : 1;
        try {
            if($repeat_asset > 0){
               for ($i=0; $i < $repeat_asset; $i++) { 
                    $this->_assetApartmentRepository->create([
                        'bdc_building_id' => $this->building_active_id,
                        'building_place_id' => $request->building_place_id,
                        'asset_category_id' => $request->asset_category_id,
                        'code'=> preg_replace('/\s+/', '_',Helper::convert_vi_to_en($request->code)).'_'.$this->building_active_id.'_'.AssetApartment::count(),
                        'name'=> $request->name,
                        'description'=> $request->description,
                        'documents' => $request->attach_link_files,
                        'created_by'=> auth()->user()->id,
                    ]);
               }
               return $this->sendSuccess_Api([],'Thêm mới thành công', route('admin.asset-apartment.asset.index'));
            }
            
         } catch (\Exception $e) {
             return $this->sendError_Api($e->getMessage());
         }
       
    }

    public function edit($id)
    {
        $data['meta_title'] = 'Sửa tài sản';
        $asset =  $this->_assetApartmentRepository->findById($id);
        if($asset){
           $data['id'] = $id;
           $code_asset = explode('_'.$this->building_active_id, $asset->code)[0]; // lấy mã tài sản nhà cung cấp
           $asset->code = $code_asset;
           $data['asset'] = $asset;
           $data['assetCategory'] =  Category::where(['bdc_building_id'=>$this->building_active_id,'type'=> self::asset])->get();
           $buildingPlace = BuildingPlace::find($asset->building_place_id);
           $data['building_place_select'] = [
             'id'=>$buildingPlace->id,
             'text'=>$buildingPlace->name,
           ];
           return view('asset-apartments.tabs.asset.create-edit', $data);
        }
        
    }

    public function update(UpdateAssetApartmentRequest $request, $id)
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

    public function action(Request $request)
    {
        return $this->_assetApartmentRepository->action($request,$this->building_active_id);
    }
    public function export(Request $request)
    {
        return $this->_assetApartmentRepository->export($request,$this->building_active_id);
    }
    public function indexImport()
    {
        $data['meta_title'] = 'import file tài sản căn hộ';
        return view('asset-apartments.tabs.asset.import',$data);
    }
    public function dowload_file_import()
    {
        $file     = public_path() . '/downloads/import_them_moi_tai_san_ban_giao.xlsx';
        return response()->download($file);
    }
    public function dowload_file_update_import()
    {
        $file     = public_path() . '/downloads/import_cap_nhat_tai_san_ban_giao.xlsx';
        return response()->download($file);
    }
    public function import_store(Request $request) 
    {
        $file = $request->file('file_import');

        if(!$file) return redirect()->route('admin.asset-apartment.asset.import')->with('warning', 'Chưa có file upload!');

        $path = $file->getRealPath();

        $excel_data = Excel::load($path)->get();

        storage_path('upload', $file->getClientOriginalName());

        $buildingId = $this->building_active_id;

        $data_list_error = array();


        if ($excel_data->count()) {

            foreach ($excel_data as $content) {
                if (empty($content->ma_tai_san) ||
                    empty($content->ten_tai_san) ||
                    empty($content->loai_tai_san) ||
                    empty($content->ma_toa_nha) ||
                    empty($content->so_luong)
                ) {
                    $new_content = $content->toArray();
                    $new_content['error'] = 'hãy kiểm tra lại các trường yêu cầu bắt buộc';
                    array_push($data_list_error,$new_content);
                    continue;
                }
                

                $category = Category::where(['bdc_building_id'=>$this->building_active_id,'type'=> self::asset,'id'=>$content->loai_tai_san])->first(); // is null : là không có mã này trên hệ thống

                if (!$category) {
                    $new_content = $content->toArray();
                    $new_content['error'] = $content->loai_tai_san.'| mã loại tài sản không có trong hệ thống';
                    array_push($data_list_error,$new_content);
                    continue;
                }

                $buildingPlace = $this->_buildingPlaceRepository->findByCode($content->ma_toa_nha,$buildingId);  // is null : là không có mã này trên hệ thống

                if (!$buildingPlace) {
                    $new_content = $content->toArray();
                    $new_content['error'] =$content->ma_toa_nha.'| mã căn hộ không tồn tại trên hệ thống';
                    array_push($data_list_error,$new_content);
                    continue;
                }

                // check is number
                
                if(preg_match('/\d/', $content->so_luong) !== 1) { // không phải là kiểu số hoặc nhỏ hơn 0
                    $new_content = $content->toArray();
                    $new_content['error'] =$content->so_luong.'| không phải là kiểu số nguyên';
                    array_push($data_list_error,$new_content);
                    continue;
                }

                
                try {
                    DB::beginTransaction();
                    $so_luong  = (int)$content->so_luong;
                    if($so_luong > 0){
                        for ($i=0; $i < $so_luong; $i++) { 
                             $this->_assetApartmentRepository->create([
                                 'bdc_building_id' => $this->building_active_id,
                                 'building_place_id' => $buildingPlace->id,
                                 'asset_category_id' => $content->loai_tai_san,
                                 'code'=> preg_replace('/\s+/', '_',Helper::convert_vi_to_en($content->ma_tai_san)).'_'.$this->building_active_id.'_'.AssetApartment::count(),
                                 'name'=> $content->ten_tai_san,
                                 'description'=> $content->mo_ta,
                                 'created_by'=> auth()->user()->id,
                             ]);
                        }
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
                        'Ten_tai_san(*)',
                        'Loai_tai_san(*)',
                        'Ma_toa_nha(*)',
                        'So_luong(*)',
                        'Mo_ta',
                        'Error'
                    ]);

                    foreach ($data_list_error as $key => $value) {
                        $row++;
                        $sheet->row($row, [
                            ($key + 1),
                            $value['ma_tai_san'],
                            $value['ten_tai_san'],
                            $value['loai_tai_san'],
                            $value['ma_toa_nha'],
                            $value['so_luong'],
                            $value['mo_ta'],
                            $value['error'],
                        ]);
                    }
                });
            })->store('xlsx',storage_path('exports/'));
            ob_end_clean();
$file     = storage_path('exports/'.$result->filename.'.'.$result->ext);
return response()->download($file)->deleteFileAfterSend(true);
             
        } else {
            return redirect()->route('admin.asset-apartment.asset.index')->with('success', 'Đã tài dữ liệu lên!');
        }
    }
    public function import_update(Request $request) 
    {
        $file = $request->file('file_import');

        if(!$file) return redirect()->route('admin.asset-apartment.asset.import')->with('warning', 'Chưa có file upload!');

        $path = $file->getRealPath();

        $excel_data = Excel::load($path)->get();
        storage_path('upload', $file->getClientOriginalName());

        $buildingId = $this->building_active_id;

        $data_list_error = array();


        if ($excel_data->count()) {

            foreach ($excel_data as $content) {
                if (
                    empty($content->ma_tai_san) ||
                    empty($content->ten_tai_san) ||
                    empty($content->loai_tai_san) ||
                    empty($content->ma_toa_nha) 
                ) {
                    continue;
                }

                $assetApartment =  $this->_assetApartmentRepository->findByIdApartmentIsNull($buildingId,$content->ma_tai_san);  // is null : là không có mã này trên hệ thống

                if (!$assetApartment) {
                    $new_content = $content->toArray();
                    $new_content['error'] = $content->ma_tai_san.'| mã tài sản không có trong hệ thống hoặc đã được sử dụng';
                    array_push($data_list_error,$new_content);
                    continue;
                }

                $category = Category::where(['bdc_building_id'=>$this->building_active_id,'type'=> self::asset,'id'=>$content->loai_tai_san])->first(); // is null : là không có mã này trên hệ thống

                if (!$category) {
                    $new_content = $content->toArray();
                    $new_content['error'] = $content->loai_tai_san.'| mã loại tài sản không có trong hệ thống';
                    array_push($data_list_error,$new_content);
                    continue;
                }

                $buildingPlace = $this->_buildingPlaceRepository->findByCode($content->ma_toa_nha,$buildingId);  // is null : là không có mã này trên hệ thống

                if (!$buildingPlace) {
                    $new_content = $content->toArray();
                    $new_content['error'] =$content->ma_toa_nha.'| mã căn hộ không tồn tại trên hệ thống';
                    array_push($data_list_error,$new_content);
                    continue;
                }

                
                try {
                    DB::beginTransaction();
                    $assetApartment->update([
                        'building_place_id' => $buildingPlace->id,
                        'asset_category_id' => $category->id,
                        //'code' => preg_replace('/\s+/', '_',Helper::convert_vi_to_en($content->ma_tai_san)).'_'.$this->building_active_id.'_'.AssetApartment::count(),
                        'name' => $content->ten_tai_san,
                        'description'=> $content->mo_ta,
                        'updated_by'=> auth()->user()->id,
                    ]);
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
                        'Ten_tai_san(*)',
                        'Loai_tai_san(*)',
                        'Ma_toa_nha(*)',
                        'Mo_ta',
                        'Error'
                    ]);

                    foreach ($data_list_error as $key => $value) {
                        $row++;
                        $sheet->row($row, [
                            ($key + 1),
                            $value['ma_tai_san'],
                            $value['ten_tai_san'],
                            $value['loai_tai_san'],
                            $value['ma_toa_nha'],
                            $value['mo_ta'],
                            $value['error'],
                        ]);
                    }
                });
            })->store('xlsx',storage_path('exports/'));
$file     = storage_path('exports/'.$result->filename.'.'.$result->ext);
return response()->download($file)->deleteFileAfterSend(true);
             
        } else {
            return redirect()->route('admin.asset-apartment.asset.index')->with('success', 'Import file thành công!');
        }
    }
    public function ajaxGetSelect(Request $request)
    {
        if($request->asset_category_id){
            if ($request->search || $request->bdc_apartment_id) {
                $where[] = ['code', 'like', '%' . $request->search . '%'];
                if($request->bdc_apartment_id){
                    $where[] = ['bdc_apartment_id', '=', $request->bdc_apartment_id];
                }
                return response()->json($this->_assetApartmentRepository->searchByAll(['where' => $where], $this->building_active_id,$request->asset_category_id));
            }
            return response()->json($this->_assetApartmentRepository->searchByAll(['select' => ['id', 'code']], $this->building_active_id,$request->asset_category_id));
        }
    }
}
