<?php

namespace App\Http\Controllers\BuildingPlace;

use App\Http\Controllers\BuildingController;
use App\Http\Requests\BuildingPlace\BuildingPlaceRequest;
use App\Repositories\Building\BuildingPlaceRepository;
use Illuminate\Http\Request;
use App\Models\Apartments\Apartments;
use App\Repositories\Building\BuildingRepository;
use App\Repositories\Department\DepartmentRepository;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Cookie;

class BuildingPlaceController extends BuildingController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    private $model;
    private $modelBuilding;
    private $_departmentRepository;
    public function __construct(BuildingPlaceRepository $model,BuildingRepository $buildingRepository, DepartmentRepository $departmentRepository,Request $request)
    {
        $this->model = $model;
        $this->modelBuilding = $buildingRepository;
        $this->_departmentRepository = $departmentRepository;
        parent::__construct($request);
    }

    public function index(Request $request)
    {
        //
        $data['meta_title'] = 'Building Place';
        $data['per_page'] = Cookie::get('per_page', 20);
        $where = [];
        $buildingPlace = $this->model->searchBy($this->building_active_id,$request,$where,$data['per_page']);
        $data_search = [
            'name'        => '',
            'mobile'         => '',
            'email'          => '',
            'status'          => '',
        ];
        $data['data_search'] = $request->data_search ?: $data_search;
        $data['data_search']['name'] = $request->name;
        $data['data_search']['mobile'] = $request->mobile;
        $data['data_search']['email'] = $request->email;
        $data['data_search']['status'] = $request->status;

        $data['buildingPlaces'] = $buildingPlace;

        $data['buildingPlacesAll'] = $this->model->getDataExport(['id','name'],$this->building_active_id);

        $data['count_display'] = count($buildingPlace);
        $data['building_id'] = $this->building_active_id;

        $request->request->add(['building_id' => $this->building_active_id]);
        $array_search='';
        $i=0;
        foreach ($request->all() as $key => $value) {
            if($i == 0){
                $param='?'.$key.'='.(string)$value;
            }else{
                $param='&'.$key.'='.(string)$value;
            }
            $i++;
            $array_search.=$param;
        }
        $data['array_search'] = $array_search;

        return view('building-place.index',$data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $data['building_id'] = $this->building_active_id;
        $data['meta_title'] = "Thêm mới căn hộ";
        return view('building-place.create', $data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    public function save(BuildingPlaceRequest $request)
    {
        $input = $request->only(['bdc_building_id', 'name', 'code','email','mobile','address','description','status']);
        $insert = $this->model->create($input);
        if(!$insert){
            return redirect()->route('admin.buildingplace.create')->with('error', 'Thêm tòa nhà không thành công!');
        }
        return redirect()->route('admin.buildingplace.edit',['id'=>$insert->id])->with('success', 'Cập nhật tòa nhà thành công!');
    }

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
        $data['meta_title'] = "Thay đổi tòa nhà";
        $data['building_id'] = $this->building_active_id;
        $buildingPlace = $this->model->findById($id);
        $data['buildingPlace']     = $buildingPlace;
        $data['id']     = $id;
        return view('building-place.create', $data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(BuildingPlaceRequest $request, $id)
    {
        $input = $request->only(['name', 'code','email','mobile','address','description','status']);
        $update = $this->model->update($input,$id);

        if(!$update){
            return redirect()->route('admin.buildingplace.edit',['id'=>$id])->with('error', 'Cập nhật tòa nhà không thành công!');
        }
        Cache::store('redis')->forget( env('REDIS_PREFIX') . 'get_detail_bulding_placeById_'.$id);
        return redirect()->route('admin.buildingplace.edit',['id'=>$id])->with('success', 'Cập nhật tòa nhà thành công!');
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
        Cache::store('redis')->forget( env('REDIS_PREFIX') . 'get_detail_bulding_placeById_'.$id);
        return redirect()->route('admin.buildingplace.index')->with('success', 'Xóa tòa nhà thành công!');
    }
    public function action(Request $request)
    {
        $check = Apartments::where('building_place_id',$request->id)->first();
        if($check){
            return redirect()->route('admin.buildingplace.index')->with('warning', 'Xóa không thành công, Tòa nhà đã có căn hộ sử dụng!');
        }
        $this->model->deleteSelects($request);
        return back()->with('success', 'Xóa tòa nhà thành công!');
    }

    public function ajaxGetSelectEmail(Request $request)
    {
        if ($request->search) {
            return response()->json($this->model->searchByEmail($request->search,$this->building_active_id));
        }
        return response()->json($this->model->searchByEmail('',$this->building_active_id));
    }
    public function ajaxGetAsset(Request $request)
    {
        if ($request->search) {
            $where[] = ['name', 'like', '%' . $request->search . '%'];
            return response()->json($this->modelBuilding->getAsset(['where' => $where],$this->building_active_id));
        }
        return response()->json($this->modelBuilding->getAsset(['select' => ['id', 'name']],$this->building_active_id));
    }
    public function ajaxGetAssetDetail(Request $request)
    {
        if ($request->search) {
            $where[] = ['asset_detail_id', 'like', '%' . $request->search . '%'];
            return response()->json($this->modelBuilding->getAssetDetail(['where' => $where],$this->building_active_id));
        }
        return response()->json($this->modelBuilding->getAssetDetail([],$this->building_active_id));
    }
    public function ajaxGetAssetDetailByName(Request $request)
    {
        return response()->json($this->modelBuilding->getAssetDetail(['where' => []],$this->building_active_id,'asset_name',$request));
    }
    public function ajaxGetAssetCategory(Request $request)
    {
        if ($request->search) {
            $where[] = ['title', 'like', '%' . $request->search . '%'];
            return response()->json($this->modelBuilding->getAssetCategory(['where' => $where],$this->building_active_id));
        }
        return response()->json($this->modelBuilding->getAssetCategory(['select' => ['id', 'title']],$this->building_active_id));
    }
    public function ajaxGetAssetArea(Request $request)
    {
        if ($request->search) {
            $where[] = ['name', 'like', '%' . $request->search . '%'];
            return response()->json($this->modelBuilding->getAssetArea(['where' => $where],$this->building_active_id));
        }
        return response()->json($this->modelBuilding->getAssetArea(['select' => ['id', 'name']],$this->building_active_id));
    }
    public function ajaxGetDepartment(Request $request)
    {
        if ($request->search) {
            $where[] = ['name', 'like', '%' . $request->search . '%'];
            return response()->json($this->_departmentRepository->getDepartment(['where' => $where],$this->building_active_id));
        }
        return response()->json($this->_departmentRepository->getDepartment(['select' => ['id', 'name']],$this->building_active_id));
    }
    public function ajaxGetFloor(Request $request)
    {
        if ($request->search || $request->place_id) {
            $where[] = ['name', 'like', '%' . $request->search . '%'];
            if ($request->place_id) {
                $where[] = ['place_id', '=',$request->place_id];
            }
            return response()->json($this->modelBuilding->getFloor(['where' => $where],$this->building_active_id));
        }
        return response()->json($this->modelBuilding->getFloor(['select' => ['id', 'name']],$this->building_active_id));
    }
    public function ajaxGetCheckList(Request $request)
    {
        if ($request->search) {
            $where[] = ['title', 'like', '%' . $request->search . '%'];
            return response()->json($this->modelBuilding->getCheckList(['where' => $where],$this->building_active_id));
        }
        return response()->json($this->modelBuilding->getCheckList(['select' => ['id', 'title']],$this->building_active_id));
    }
    public function ajaxGetPromotion(Request $request)
    {
        if ($request->search || $request->service_id) {
            $where[] = ['name', 'like', '%' . $request->search . '%']; 
            if ($request->service_id) {
                $where[] = ['service_id', '=',$request->service_id];
            }
            return response()->json($this->modelBuilding->getPromotion(['where' => $where],$this->building_active_id));
        }
        return response()->json($this->modelBuilding->getPromotion(['select' => ['id', 'name']],$this->building_active_id));
    }
    public function ajaxGetUrban(Request $request)
    {
        if ($request->search) {
            $where[] = ['name', 'like', '%' . $request->search . '%'];
            return response()->json($this->modelBuilding->getUrban(['where' => $where],$this->building_active_id));
        }
        return response()->json($this->modelBuilding->getUrban(['select' => ['id', 'name']],$this->building_active_id));
    }
    public function ajaxGetCateTask(Request $request)
    {
        if ($request->search) {
            $where[] = ['name', 'like', '%' . $request->search . '%'];
            return response()->json($this->modelBuilding->getCateTask(['where' => $where],$this->building_active_id));
        }
        return response()->json($this->modelBuilding->getCateTask(['select' => ['id', 'name']],$this->building_active_id));
    }

}
