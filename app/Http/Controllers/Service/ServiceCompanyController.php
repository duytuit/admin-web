<?php

namespace App\Http\Controllers\Service;

use App\Http\Controllers\BuildingController;
use App\Http\Requests\Service\ServiceRequest;
use App\Repositories\BdcPriceType\PriceTypeRepository;
use App\Repositories\BdcProgressive\ProgressiveRepository;
use App\Repositories\Service\ServiceRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Cookie;

class ServiceCompanyController extends BuildingController
{
    public $serviceRepo;
    public $priceTypeRepo;
    public $progressRepo;

    public function __construct(
        Request $request,
        ServiceRepository $serviceRepo,
        PriceTypeRepository $priceTypeRepo,
        ProgressiveRepository $progressRepo
    ) {
        parent::__construct($request);
        //$this->middleware('route_permision');
        $this->serviceRepo = $serviceRepo;
        $this->priceTypeRepo = $priceTypeRepo;
        $this->progressRepo = $progressRepo;
    }

    public function index(Request $request)
    {
        $data['meta_title'] = 'Quản lý dịch vụ';
        $data['per_page'] = Cookie::get('per_page', 10);
        $data['services'] = $this->serviceRepo->getAllServiceCompany($data['per_page']);
        $data['filter'] = $request->all();
        if ($request->name) {
            $data['services'] = $this->serviceRepo->filterCompany($request->name, $data['per_page']);
        }
        return view('service.company.index', $data);
    }

    public function create()
    {
        $meta_title = 'Thêm dịch vụ';
        $priceTypes = $this->priceTypeRepo->get_all();
        $progressives = $this->progressRepo->chooseManyPrice($this->building_active_id);
        return view('service.company.create', compact(['meta_title', 'priceTypes', 'progressives']));
    }

    public function store(ServiceRequest $request)
    {
       // $this->serviceRepo->createServiceCompany($request->all());
        $dataResponse = [
            'success' => true,
            'message' => 'Thêm dịch vụ mới thành công',
            'href' => route('admin.service.company.index')
        ];
        return response()->json($dataResponse);
    }

    public function edit($id)
    {
        $meta_title = 'Sửa dịch vụ';
        $priceTypes = $this->priceTypeRepo->get_all();
        $progressives = $this->progressRepo->chooseManyPrice($this->building_active_id);
        $service = $this->serviceRepo->findServiceCompany($id);
        return view('service.company.edit', compact(['service', 'meta_title', 'priceTypes', 'progressives']));
    }

    public function update(ServiceRequest $request, $id)
    {
       // $this->serviceRepo->updateServiceCompany($request->all(), $id);
        Cache::store('redis')->forget( env('REDIS_PREFIX') . 'get_detail_bdc_serviceById_'.$id);
        return redirect()->route('admin.service.company.index')->with('success', 'Sửa dịch vụ thành công.');
    }

    public function choose()
    {
        $data['per_page'] = Cookie::get('per_page', 10);
        $data = $this->serviceRepo->getAllChoose($data['per_page']);
        $data['per_page'] = Cookie::get('per_page', 10);
        $data['meta_title'] = 'Chọn dịch vụ';

        return view('service.company.choose', $data);
    }

    // Thay đổi trạng thái trong index
    public function changeStatus(Request $request)
    {
        $this->serviceRepo->changeStatusCompany($request->id);
        $dataResponse = [
            'success' => true,
            'message' => 'Cập nhật trạng thái thành công!'
        ];
        return response()->json($dataResponse);
    }

    public function postChoose(Request $request)
    {
        $ids = $request->has('ids') ? $request->ids : [];
        $this->serviceRepo->postChooseCompany($ids);
        return redirect()->route('admin.service.company.index')->with('success', 'Thêm dịch vụ mới thành công.');
    }

    public function getProgressive()
    {
        $manyPrice = $this->progressRepo->getManyPrice();
        return response()->json($manyPrice);
    }

    public function action(Request $request)
    {
        return $this->serviceRepo->action($request);
    }
}
