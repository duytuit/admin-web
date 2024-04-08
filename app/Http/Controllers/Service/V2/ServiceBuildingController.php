<?php

namespace App\Http\Controllers\Service\V2;

use App\Commons\Helper;
use App\Helpers\dBug;
use App\Http\Controllers\BuildingController;
use App\Http\Requests\Progressives\ImportExcelRequest;
use App\Http\Requests\Service\ServiceRequest;
use App\Models\Building\Building;
use App\Repositories\Apartments\ApartmentsRespository;
use App\Repositories\BdcApartmentServicePrice\ApartmentServicePriceRepository;
use App\Repositories\BdcPriceType\PriceTypeRepository;
use App\Repositories\BdcProgressive\ProgressiveRepository;
use App\Repositories\Building\BuildingRepository;
use App\Repositories\Service\ServiceRepository;
use App\Services\SendTelegram;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;
use App\Models\Service\Service;
use Carbon\Carbon;
use App\Models\BdcApartmentServicePrice\ApartmentServicePrice;
use App\Models\BusinessPartners\BusinessPartners;
use App\Models\Category;
use App\Repositories\BusinessPartners\BusinessPartnerRepository;
use Illuminate\Support\Facades\Cache;

class ServiceBuildingController extends BuildingController
{
    public $serviceRepo;
    public $priceTypeRepo;
    public $buildingRepo;
    public $progressRepo;
    public $_apartmentServicePriceRepository;
    private $businessPartnerRepository;

    public function __construct(
        Request $request,
        ServiceRepository $serviceRepo,
        PriceTypeRepository $priceTypeRepo,
        ProgressiveRepository $progressRepo,
        ApartmentServicePriceRepository $apartmentServicePriceRepository,
        BusinessPartnerRepository $businessPartnerRepository,
        BuildingRepository $buildingRepo
    ) {
        parent::__construct($request);
        //$this->middleware('route_permision');
        $this->serviceRepo = $serviceRepo;
        $this->priceTypeRepo = $priceTypeRepo;
        $this->progressRepo = $progressRepo;
        $this->businessPartnerRepository = $businessPartnerRepository;
        $this->_apartmentServicePriceRepository = $apartmentServicePriceRepository;
    }

    public function index(Request $request)
    {
        $data['meta_title'] = 'Quản lý dịch vụ tòa nhà';
        $data['per_page'] = Cookie::get('per_page', 10);
        $data['per_page_maintenance'] = Cookie::get('per_page_maintenance', 10);
        $data['services'] = $this->serviceRepo->getAllServiceBuilding($data['per_page'], $this->building_active_id);
        $check_type_service_default = Category::where(['bdc_building_id' => $this->building_active_id,'type'=>'service', 'default' => 1])->first();
        if(!$check_type_service_default){
                $list_type_services = Helper::loai_phi_dich_vu;
                foreach ($list_type_services as $key => $value) {
                    Category::create([
                        'type' => 'service',
                        'alias' =>  str_slug($value),
                        'bdc_building_id' => $this->building_active_id,
                        'url_id' => Auth::user()->id,
                        'user_id' => Auth::user()->id,
                        'default' => 1,
                        'content' => '<p>loại dịch vụ mặc định</p>',
                        'status' => 1,
                        'category' => $key
                    ]);
                }
        }
        $data['filter'] = $request->all();
        if ($request->name) {
            $data['services'] = $this->serviceRepo->filterBuilding($request->name, $data['per_page'], $this->building_active_id);
        }
        $data['type_tinh_cong_no'] = ServiceRepository::getTinhCongNo($this->building_active_id);
        return view('service.v2.building.index', $data);
    }

    public function buildinglock(Request $request)
    {
        $data['meta_title'] = 'Tòa nhà Lock';
        $data['per_page'] = Cookie::get('per_page', 10);
        $data['filter'] = $request->all();
        if(Auth::user()->isadmin != 1){
            return redirect()->away('/admin')->with(['warning' => 'Vui lòng quản trị để sử dụng tính năng này!']);
        }
        try{
        //$buildings = Building::where('status', 1)->get();
        $buildings = Building::select('bdc_building.*', 'b.user_id', 'c.display_name')
        ->leftJoin('logs as b', function ($join) {
            $join->on(DB::raw("b.content"), 'like', DB::raw("CONCAT('%', bdc_building.id, '%')") );
            $join->where(DB::raw("DATE(b.updated_at)"), ">=", '2023-08-07');
        })
        ->leftJoin('pub_user_profile as c', 'b.user_id', '=', 'c.pub_user_id')
        ->where('bdc_building.status', 1)
        ->groupBy('bdc_building.id')
        ->orderBy('b.updated_at', 'DESC')
        ->get();
        $results = Building::selectRaw('
            SUM(CASE WHEN status_apartment = 0 AND company_id = 2 THEN 1 ELSE 0 END) AS stech_off,
            SUM(CASE WHEN status_apartment = 1 AND company_id = 2 THEN 1 ELSE 0 END) AS stech_on,
            SUM(CASE WHEN status_apartment = 0 AND company_id = 1 THEN 1 ELSE 0 END) AS asa_off,
            SUM(CASE WHEN status_apartment = 1 AND company_id = 1 THEN 1 ELSE 0 END) AS asa_on
        ')
        ->where('status', 1)
        ->first();

        $data['steoff'] = $results->stech_off;
        $data['steon']  = $results->stech_on;
        $data['asaoff']  = $results->asa_off;
        $data['asaon']  = $results->asa_on;
        }
        catch (\Exception $e)
        {
            SendTelegram::SupersendTelegramMessage('Fail data test '.json_encode($e->getMessage()));
        }
        if(!$buildings){
            return redirect()->back()->with(['warning' => 'Không tìm thấy tòa nhà nào.']);
        }
        SendTelegram::SupersendTelegramMessage('data test '.json_encode($buildings));
        $data['buildings'] = $buildings;
        return view('LockBuilding.v2.index', $data);
    }

    public function set_type_tinh_cong_no(Request $request)
    {
        ServiceRepository::setTinhCongNo($this->building_active_id,$request->type_tinh_cong_no??null);
        $dataResponse = [
            'success' => true,
            'message' => 'Set kiểu tính công nợ thành công',
            'href' => route('admin.v2.service.building.index')
        ];
        return response()->json($dataResponse);
    }

    public function create()
    {
        $meta_title = 'Thêm dịch vụ tòa nhà';
        $priceTypes = $this->priceTypeRepo->get_all();
        $typeService = Category::where(['type'=>'service','bdc_building_id'=>$this->building_active_id,'status'=>1])->get();
        $progressives = $this->progressRepo->chooseManyPrice($this->building_active_id);
        $partners =  $this->businessPartnerRepository->getPartnersWithStatus($this->building_active_id);
        $service_types =  Helper::service_type;
        return view('service.v2.building.create', compact(['meta_title', 'priceTypes', 'progressives', 'typeService', 'partners','service_types']));
    }

    public function store(ServiceRequest $request)
    {
        $this->serviceRepo->createServiceBuilding($request->all(), $this->building_active_id);
        $dataResponse = [
            'success' => true,
            'message' => 'Thêm dịch vụ mới thành công',
            'href' => route('admin.v2.service.building.index')
        ];
        return response()->json($dataResponse);
    }

    public function check_index_accounting(Request $request)
    {
        $get_max_index_accounting = Service::where('bdc_building_id',$this->building_active_id)->max('index_accounting');
        $exit_index_accounting = $request->index_accounting ? Service::where(['bdc_building_id'=>$this->building_active_id,'index_accounting'=>$request->index_accounting])->first() : null;
        
        $dataResponse = [
            'success' => true,
            'message' => 'Thêm dịch vụ mới thành công',
            'data' => $get_max_index_accounting ?? 0,
            'exit_index_accounting' => $exit_index_accounting ? $exit_index_accounting->index_accounting : null,
            'href' => route('admin.v2.service.building.index')
        ];

        return response()->json($dataResponse);
    }

    public function update_index_accounting(Request $request)
    {
        foreach ($request->order as $order) {
           $service = Service::find($order['id']);
           if($service){
              $service->index_accounting = $order['position'];
              $service->save();
           }
        }
        return response('Update Successfully.', 200);
    }

    public function edit($id)
    {
        $meta_title = 'Sửa dịch vụ';
        $priceTypes = $this->priceTypeRepo->get_all();
        $progressives = $this->progressRepo->chooseManyPrice($this->building_active_id);
        $service = $this->serviceRepo->findServiceBuilding($id);
        $typeService = Category::where(['type'=>'service','bdc_building_id'=>$this->building_active_id,'status'=>1])->get();
        $partners =  $this->businessPartnerRepository->getPartnersWithStatus($this->building_active_id);
        $service_types =  Helper::service_type;
        return view('service.v2.building.edit', compact(['service', 'meta_title', 'priceTypes', 'progressives','typeService','partners','service_types']));
    }

    public function ajaxSelectTypeService(Request $request)
    {

        try {
            $typeService = Category::where(['category'=>$request->category,'bdc_building_id'=>$this->building_active_id])->first();
            $responseData = [
                'success' => true,
                'message' => $typeService ? $typeService->title : null
            ];
            return response()->json($responseData);
        } catch (\Exception $e) {
            $responseData = [
                'success' => false,
                'message' => $e->getMessage()
            ];
            return response()->json($responseData);
        }
      
    }

    public function update(ServiceRequest $request, $id)
    {
        $this->serviceRepo->updateServiceBuilding($request->all(), $id, $this->building_active_id);
        Cache::store('redis')->forget( env('REDIS_PREFIX') . 'get_detail_service_by_service_id'.$id);
        return redirect()->route('admin.v2.service.building.index')->with('success', 'Sửa dịch vụ thành công.');
    }
    public function destroy($id)
    {
        try {
            DB::beginTransaction();
            $service = Service::find($id);
            $service->user_id = auth()->user()->id;
            $service->save();
            $service->delete();
            Cache::store('redis')->forget( env('REDIS_PREFIX') . 'get_detail_bdc_serviceById_'.$id);
            ApartmentServicePrice::where('bdc_service_id',$id)->delete();
            Cache::store('redis')->forget( env('REDIS_PREFIX') . 'get_detail_service_by_service_id'.$id);
            DB::commit();
        } catch (\Exception $th) {
            DB::rollBack();
            return redirect()->route('admin.v2.service.building.index')->with('error', 'Xóa thất bại.');
        }
        return redirect()->route('admin.v2.service.building.index')->with('success', 'Xóa dịch vụ thành công.');
    }
    public function action(Request $request)
    {
        $method = $request->input('method','');
        if ($method == 'per_page') {
            $this->per_page($request);
            return back();
        }
    }
    public function choose()
    {
        $meta_title = 'Chọn dịch vụ';
        $data['per_page'] = Cookie::get('per_page', 10);
        $data = $this->serviceRepo->getAllChooseBuilding($data['per_page'], $this->building_active_id);
        $data['per_page'] = Cookie::get('per_page', 10);
        return view('service.v2.building.choose', compact('meta_title'), $data);
    }

    public function changeStatus(Request $request)
    {
        $this->serviceRepo->changeStatusBuilding($request);
        $dataResponse = [
            'success' => true,
            'message' => 'Thay đổi trạng thái thành công!'
        ];
        return response()->json($dataResponse);
    }

    public function postChoose(Request $request)
    {
        $ids = $request->has('ids') ? $request->ids : [];
        $this->serviceRepo->postChooseBuilding($ids, $this->building_active_id);
        return redirect()->route('admin.v2.service.building.index')->with('success', 'Thêm dịch vụ mới thành công.');
    }

    public function importExcel()
    {
        //dd($this->building_active_id);
        $data['meta_title'] = 'Import Excel Dịch Vụ Căn Hộ';
        return view('service.v2.building.import_excel', $data);
    }

    public function importApartmentService(
        ImportExcelRequest $request,
        ApartmentsRespository $apartmentsRespository,
        ServiceRepository $serviceRepository
    ) {
        $file = $request->file('file_import');

        if(!$file) return redirect()->route('admin.v2.service.building.importexcel')->with('warning', 'Chưa có file upload!');

        $path = $file->getRealPath();

        $excel_data = Excel::load($path)->get();

        storage_path('upload', $file->getClientOriginalName());

        $url = [
            'name' => $file->getClientOriginalName(),
            'uuid' => (string) \Webpatser\Uuid\Uuid::generate(),
        ];

        $buildingId = $this->building_active_id;

        $data_list_error = array();

        if ($excel_data->count()) {

            foreach ($excel_data as $content) {
                if (empty($content->ma_dich_vu) ||
                    empty($content->ma_can_ho)
                ) {
                    $new_content = $content->toArray();
                    $new_content['Message'] = 'hãy kiểm tra lại các trường yêu cầu bắt buộc';
                    array_push($data_list_error,$new_content);
                    continue;
                }
                $service = $serviceRepository->filterServiceBuildingId(trim($content->ma_dich_vu), $buildingId);
                if (!$service) {
                    $new_content = $content->toArray();
                    $new_content['Message'] = $content->ma_dich_vu.'| mã dịch vụ này không tồn tại';
                    array_push($data_list_error,$new_content);
                    continue;
                }
                $apartment = $apartmentsRespository->findByCode($buildingId, trim($content->ma_can_ho));
                if (!$apartment) {
                    $new_content = $content->toArray();
                    $new_content['Message'] = $content->ma_can_ho.'| mã căn hộ này không tồn tại';
                    array_push($data_list_error,$new_content);
                    continue;
                }

                $apartmentServicePrice = $this->_apartmentServicePriceRepository->findBuildingApartmentServiceId($buildingId,$apartment->id,$service->id);

                if ($apartmentServicePrice) { // căn hộ đăng ký dịch vụ này đã tồn tại
                    $new_content = $content->toArray();
                    $new_content['Message'] = 'căn hộ đăng ký dịch vụ này đã tồn tại';
                    $apartmentServicePrice->status = 1;
                    $apartmentServicePrice->save();
                    array_push($data_list_error,$new_content);
                    continue;
                }

                $servicePriceDefault = null;
                if (@$service->servicePriceDefault->bdc_price_type_id == ServiceRepository::getManyPrice()) {
                    $servicePriceDefault = $service->servicePriceDefault;
                }

                if (empty($content->ngay_bat_dau_tinh_phi) || !strtotime($content->ngay_bat_dau_tinh_phi)) { // ngày dự kiến không đúng định dạng dd/mm/yyyy';
                    $new_content = $content->toArray();
                    $new_content['Message'] =$content->ngay_bat_dau_tinh_phi.'| ngày không đúng định dạng dd/mm/yyyy';
                    array_push($data_list_error,$new_content);
                    continue;
                }

                if ($service->type == 2) { // giá dịch vụ sàn nhà

                    $tong_gia_tien = @$service->servicePriceDefault->price * $apartment->area;
                }
                if($service->bdc_period_id == 6){ // tính theo chu kỳ 1 năm
                    $current = Carbon::now();
                    $get_cycle_year = Carbon::parse($service->first_time_active);
                    $ngay_bat_dau_tinh_phi =  Carbon::parse($content->ngay_bat_dau_tinh_phi);
                    if($ngay_bat_dau_tinh_phi < $get_cycle_year){
                        $new_content = $content->toArray();
                        $new_content['Message'] =$content->ngay_bat_dau_tinh_phi.'| Phải lớn hơn hoặc bằng ngày áp dụng phí dịch vụ:'.$service->first_time_active;
                        array_push($data_list_error,$new_content);
                        continue;
                    }
                    $getDate = "{$current->year}-{$get_cycle_year->month}-{$get_cycle_year->day}";
                    $last_time_pay = Carbon::parse($getDate)->addYear();
                }

                $data = [
                    'bdc_service_id' => $content->ma_dich_vu,
                    'code' => $content->ma_can_ho,
                    'bdc_price_type_id' => @$service->servicePriceDefault->bdc_price_type_id,
                    'bdc_apartment_id' => $apartment->id,
                    'name' => $service->name,
                    'price' => $service->type == 2 ? $tong_gia_tien : @$service->servicePriceDefault->price,
                    'first_time_active' => Carbon::parse($content->ngay_bat_dau_tinh_phi),
                    'finish' => $content->ngay_ket_thuc,
                    'last_time_pay' => $service->bdc_period_id == 6 ? $last_time_pay : Carbon::parse($content->ngay_bat_dau_tinh_phi),
                    'bdc_vehicle_id' => 0,
                    'bdc_building_id' => $buildingId,
                    'bdc_progressive_id' => $servicePriceDefault != null ? $servicePriceDefault->progressive_id : 0,
                    'description' => "",
                    'floor_price' => @$service->servicePriceDefault->price,
                    'status' => 1,
                    'user_id' => Auth::id(),
                ];

                try {
                    $this->_apartmentServicePriceRepository->create($data);
                    $new_content = $content->toArray();
                    $new_content['Message'] = 'cập nhật thành công';
                    array_push($data_list_error,$new_content);
                } catch (\Exception $e) {
                    $new_content = $content->toArray();
                    $new_content['Message'] = $e->getMessage();
                    array_push($data_list_error,$new_content);
                    continue;
                }
            }
        }

        if (count($data_list_error) > 0) {
            $result = Excel::create('Kết quả Import', function ($excel) use ($data_list_error) {

                $excel->setTitle('Kết quả Import');
                $excel->sheet('Kết quả Import', function ($sheet) use ($data_list_error) {
                    $row = 1;
                    $sheet->row($row, [
                        'ma_can_ho',
                        'ma_dich_vu',
                        'ngay_bat_dau_tinh_phi',
                        'Error'
                    ]);
                    foreach ($data_list_error as $key => $value) {
                        $row++;
                        $sheet->row($row, [
                            isset($value['ma_can_ho']) ? $value['ma_can_ho']: null,
                            isset($value['ma_dich_vu']) ? $value['ma_dich_vu']: null,
                            isset($value['ngay_bat_dau_tinh_phi']) ? date("d/m/Y", strtotime($value['ngay_bat_dau_tinh_phi'])) : '',
                            isset($value['ngay_ket_thuc']) ? date("d/m/Y", strtotime($value['ngay_ket_thuc'])) : '',
                            $value['Message'],
                        ]);
                        if (isset($value['message']) && $value['message'] == 'cập nhật thành công') {
                            $sheet->cells('F' . $row, function ($cells) {
                                $cells->setBackground('#23fa43');
                            });
                        }
                        if (isset($value['message']) && $value['message'] != 'cập nhật thành công') {
                            $sheet->cells('F' . $row, function ($cells) {
                                $cells->setBackground('#FC2F03');
                            });
                        }
                    }
                });
            })->store('xlsx',storage_path('exports/'));
            ob_end_clean();
            $file     = storage_path('exports/'.$result->filename.'.'.$result->ext);
            return response()->download($file)->deleteFileAfterSend(true);
             
        } 
    }

    public function download()
    {
        ob_end_clean();
        $file = public_path() . '/downloads/import_dich_vu_cho_can_ho_template.xlsx';
        return response()->download($file);
    }
    public function indexCategory(Request $request)
    {
        $check_type_service_default = Category::where(['bdc_building_id' => $this->building_active_id,'type'=>'service', 'default' => 1])->first();
        if(!$check_type_service_default){
                $list_type_services = Helper::loai_phi_dich_vu;
                foreach ($list_type_services as $key => $value) {
                    Category::create([
                        'type' => 'service',
                        'alias' =>  str_slug($value),
                        'bdc_building_id' => $this->building_active_id,
                        'url_id' => Auth::user()->id,
                        'user_id' => Auth::user()->id,
                        'default' => 1,
                        'content' => '<p>loại dịch vụ mặc định</p>',
                        'status' => 1,
                        'category' => $key
                    ]);
                }
        }
         return redirect()->route('admin.categories.index', ['type' => 'service']);
    }
}
