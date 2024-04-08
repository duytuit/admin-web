<?php

namespace App\Http\Controllers\VehicleCards;

use App\Http\Controllers\BuildingController;
use App\Http\Requests\VehicleCards\VehicleCardsRequest;
use App\Models\Vehicles\Vehicles;
use App\Repositories\Apartments\ApartmentsRespository;
use App\Repositories\VehicleCards\VehicleCardsRespository;
use App\Repositories\VehicleCategory\VehicleCategoryRespository;
use App\Repositories\Vehicles\VehiclesRespository;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Apartments\ApartmentGroup;
use App\Models\Apartments\Apartments;
use App\Models\UserRequest\UserRequest;
use App\Models\VehicleCategory\VehicleCategory;
use App\Repositories\Building\BuildingPlaceRepository;
use App\Util\Debug\Log;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Facades\Excel;

class VehicleCardsController extends BuildingController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    private $model;
    private $modelVehicles;
    private $modelApartment;
    private $modelVehicleCate;
    private $modelBuildingPlace;

    public function __construct(VehicleCardsRespository $model, 
    VehiclesRespository $modelVehicles,
     ApartmentsRespository $modelApartment,
      VehicleCategoryRespository $modelVehicleCate,
      Request $request,
      BuildingPlaceRepository $modelBuildingPlace
      )
    {
        // $this->middleware('auth', ['except'=>[]]);
        //$this->middleware('route_permision');
        $this->model = $model;
        $this->modelVehicles = $modelVehicles;
        $this->modelApartment = $modelApartment;
        $this->modelVehicleCate = $modelVehicleCate;
        $this->modelBuildingPlace = $modelBuildingPlace;
        parent::__construct($request);
    }

    public function index(Request $request)
    {
        $data['meta_title'] = 'Vehicle Card';
        $data['per_page'] = Cookie::get('per_page', 20);
        $vehiclecard = $this->model->searchBy($this->building_active_id,$request,[])->paginate( $data['per_page']);

        $data['data_search'] = $request->all();
        $data['data_search']['keyword'] = $request->keyword;
        if(!empty($request->apartment)){
            $data['data_search']['apartment'] = $this->modelApartment->findById($request->apartment);
        }
        if(!empty($request->cate)){
            $data['data_search']['cate'] = $this->modelVehicleCate->getOne('id',$request->cate);
        }
        if (isset($request->place_id)) {
            $data['get_place_building'] = $this->modelBuildingPlace->findById($request->place_id);
        }
        $data['data_search']['status'] = $request->status;
        $data['vehiclecards'] = $vehiclecard;
        $data['display_count'] = count($vehiclecard);
        $data['data_cus'] = Session::get('data_cus');
        $data['data_error'] = Session::get('error');
        $data['data_success'] = Session::get('success');

        return view('vehiclecards.index',$data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(VehicleCardsRequest $request)
    {
        $vehicle = $this->modelVehicles->getByReqVcard($request);
        if($vehicle){
            $insert = $this->model->createCheck($request,$vehicle);
            if($request->user_request_push_card_vehicle){
                $_user_request =  UserRequest::find($request->user_request_push_card_vehicle);
                $_user_request->status = 3;
                $_user_request->save();
            }
            if ($insert){
                return redirect()->route('admin.vehiclecards.index')->with(['success'=>'Thêm vé thành công!','data_cus'=>'Thêm vé thành công!']);
            }
            return redirect()->route('admin.vehiclecards.index')->with(['error'=>'Thêm vé không thành công,vé biển số này đang hoạt động!','data_cus'=>'Thêm vé không thành công, vé biển số này đang hoạt động!']);
        }
        return redirect()->route('admin.vehiclecards.index')->with(['error'=>'Thêm vé không thành công, Không tìm thấy phương tiện','data_cus'=>'Thêm vé không thành công, Không tìm thấy phương tiện']);
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
    public function export(Request $request)
    {
        try {
            $vehiclecard = $this->model->searchBy($this->building_active_id,$request,[])->get();
            $result = Excel::create('Danh_sach_the_xe' . date('d-m-Y-H-i-s', time()), function ($excel) use ($vehiclecard) {
                $excel->setTitle('Danh sách');
                $excel->sheet('Danh sách', function ($sheet) use ($vehiclecard) {
                    $result = [];
                    foreach ($vehiclecard as $key => $vc) {
                        $Vehicles = Vehicles::get_detail_vehicle_by_id($vc->bdc_vehicle_id);
                        $category =$Vehicles ? VehicleCategory::get_detail_vehicles_category_by_id($Vehicles->vehicle_category_id) : null;
                        $apartment = $Vehicles ? Apartments::get_detail_apartment_by_apartment_id($Vehicles->bdc_apartment_id) : null;
                        $apartment_group = $Vehicles ? ApartmentGroup::get_detail_apartment_group_by_apartment_group_id($apartment->bdc_apartment_group_id) : null;
                        $result[] = [
                            'STT' => $key + 1,
                            'Mã thẻ' => $vc->code,
                            'Căn hộ' => @$apartment->name,
                            'Nhóm căn hộ' => @$apartment_group->name,
                            'Phương tiện' => $category->name,
                            'Biển số' => $Vehicles->number,
                            'Ghi chú' => @$vc->description,
                            'Trạng thái' => $vc->status == 1 ? 'đang hoạt động' : 'chưa hoạt động',
                        ];
                    }
                    $sheet->setAutoSize(true);
                    if ($result) {
                        $sheet->fromArray($result);
                    }
                    $sheet->cell('A1:H1', function ($cell) {
                        // change header color
                        $cell->setBackground('#C5D9F1')
                            ->setFontWeight('bold')
                            ->setAlignment('center')
                            ->setValignment('center');
                    });
                });
            })->store('xlsx',storage_path('exports/'));
            $file     = storage_path('exports/'.$result->filename.'.'.$result->ext);
            return response()->download($file)->deleteFileAfterSend(true);
             
        } catch (\Exception $e) {
            Log::info('Error 3:', $e->getMessage());
            echo $e->getMessage();
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $data['meta_title'] = 'edit Vehicle Card';
        $vehiclecard = $this->model->getOne('id',$id);

        $data['vehicle'] = $vehiclecard->bdcVehicle;
        $data['vehicle']->load('bdcVehiclesCategory');
        $data['vehicle']->load('bdcApartment');
        $data['vehiclecard'] = $vehiclecard;
        return view('vehiclecards.edit',$data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(VehicleCardsRequest $request, $id)
    {
        $vehicle = $this->modelVehicles->getByReqVcard($request);
        $data=[
            'bdc_vehicle_id'=>$vehicle->id,
            'code'=>strtoupper($request->code),
            'description' => $request->description,
            'status' => $request->status
        ];
        $this->model->update($data,$id,'id');
        return redirect()->route('admin.vehiclecards.index')->with(['success'=>'Cập nhật vé thành công!','data_cus'=>'Cập nhật vé thành công!']);
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
        return redirect()->route('admin.vehiclecards.index')->with(['success'=>'Xóa vé thành công!','data_cus'=>'Xóa vé thành công!']);
    }
    public function ajaxGetSelectVehicleNumber(Request $request)
    {
        if ($request->search) {
            $where[] = ['number', 'like', '%' . $request->search . '%'];
        }
        if ($request->apartment) {
            $where[] = ['bdc_apartment_id', '=', $request->apartment];
        }
        if ($request->cate) {
            $where[] = ['vehicle_category_id', '=',$request->cate];
        }
        if (!empty($where)) {
            return response()->json($this->modelVehicles->searchByAll(['where'=>$where]));
        }
        return response()->json($this->modelVehicles->paginate(['number']));
    }
    public function action(Request $request)
    {
        $method = $request->input('method', '');
        if ($method == 'delete') {
            $this->model->destroyIn($request->ids);
            return back()->with('success', 'Xóa thành công '.count($request->ids) . 'bản ghi');
        }
        return back()->with('success', 'Xóa không thành công');
    }
    public function ajaxChangeStatus(Request $request)
    {
        if($request->status == 2){
            $this->model->update(['status'=>1],$request->id,'id');
            return response()->json(['status'=>1]);
        }
        $this->model->update(['status'=>2],$request->id,'id');
        return response()->json(['status'=>2]);
    }

    public function indexImport()
    {

        $data['meta_title'] = 'import file vehicle card';

        $data['messages'] = json_decode(Session::get('messages'),true);
//        dd($data['messages']);
        $data['error_data'] = Session::get('error_data');

        return view('vehiclecards.import',$data);
    }
    public function importFileVehicleCard(Request $request)
    {
        $data_error=[];$data_success=[];
        if (!$request->file('file_import')) {
            return redirect()->route('admin.vehiclecards.index')->with('danger', 'Không có file tải lên');
        }
        $data['data_import'] = $this->model->getDataFile($request->file('file_import'));

        if($data['data_import']['data']['vehiclecard']){
            foreach ($data['data_import']['data']['data_vhc'] as $key => $vhc){
                $vh_id= $this->modelVehicles->findByNumber($vhc['number'])??'';
                if($vh_id){
                    $data['data_import']['data']['vehiclecard'][$key] = array_merge($data['data_import']['data']['vehiclecard'][$key],['bdc_vehicle_id'=>$vh_id->id??0,'status'=>1]);
                    $data_success[]=$vhc;
                }else{
                    $data_error[]=$vhc;
                    unset($data['data_import']['data']['vehiclecard'][$key]);
                }
                unset($data['data_import']['data']['vehiclecard'][$key]['number']);
            }
            if(!empty($data_error)){
                $data['data_import']['messages'][]=['messages'=>'Lỗi không có vé xe trên hệ thống','data'=>$data_error];
            }
            if(!empty($data_success)){
                $data['data_import']['messages'][]=['messages'=>'Có bản ghi được cập nhật trên hệ thống','data'=>$data_success];
            }
//            dd($data['data_import']);
            $check = $this->model->insert($data['data_import']['data']['vehiclecard']);
            if ((isset($check) && $check == false) || !isset($check)) {
                return redirect()->route('admin.vehiclecards.index_import')->with(['error'=>'Import file không thành công','messages'=>json_encode($data['data_import']['messages'])]);
            }
            return redirect()->route('admin.vehiclecards.index_import')->with(['success'=>'Import file thành công','messages'=>json_encode($data['data_import']['messages'])]);
        }
        return redirect()->route('admin.vehiclecards.index_import')->with(['success'=>'Import file thành công, không có dữ liệu được thêm','messages'=>json_encode($data['data_import']['messages'])]);
    }
    public function download()
    {
        $file     = public_path().'/downloads/vehicle_card_file_import.xlsx';
        return response()->download($file);
    }
    public function changeStatus(Request $request)
    {
        $service = $this->model->getVcById($request->id);
        $checkStatusvehicleCard=$this->model->getVcCheckStatusByVehicle_id($service->bdc_vehicle_id);
        $data = $request->except('id');
        if($data['status'] == 0)
        {
             $this->model->find($request->id)->update($data);
                $dataResponse = [
                    'success' => true,
                    'message' => 'Cập nhật trạng thái thành công!'
                ];
             return response()->json($dataResponse);
        }else{
             if( $checkStatusvehicleCard->count() > 0){
             $dataResponse = [
                    'error' => true,
                    'message' => 'Không thể thay đổi trang thái thẻ xe này!'
                    ];
                   return response()->json($dataResponse);  
            }
             $this->model->find($request->id)->update($data);
                $dataResponse = [
                    'success' => true,
                    'message' => 'Cập nhật trạng thái thành công!'
                ];
             return response()->json($dataResponse);
        }     
    }
}
