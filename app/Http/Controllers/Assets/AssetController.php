<?php

namespace App\Http\Controllers\Assets;

use App\Http\Controllers\BuildingController;
use App\Repositories\MaintenanceAsset\MaintenanceAssetRepository;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Repositories\Assets\AssetRepository;
use App\Repositories\AssetType\AssetTypeRepository;
use App\Repositories\Period\PeriodRepository;
use App\Http\Requests\Asset\CreateAssetRequest;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\URL;

class AssetController extends BuildingController
{
    protected $assetRepository;
    protected $assetTypeRepository;
    protected $periodRepository;
    protected $maintenanceAssetRepository;

    public function __construct(
        Request $request,
        AssetRepository $assetRepository,
        AssetTypeRepository $assetTypeRepository,
        PeriodRepository $periodRepository,
        MaintenanceAssetRepository $maintenanceAssetRepository
    )
    {
        //$this->middleware('auth', ['except'=>[]]);
        //$this->middleware('route_permision');
        $this->assetRepository = $assetRepository;
        $this->periodRepository = $periodRepository;
        $this->assetTypeRepository = $assetTypeRepository;
        $this->maintenanceAssetRepository = $maintenanceAssetRepository;
        Carbon::setLocale('vi');
        parent::__construct($request);
    }

    public function index(Request $request)
    {
        $data = $this->getAttribute();
        if ($request->has('keyword_maintain')) {
            $data['tab'] = 'maintenance_asset';
        } elseif($request->has('keyword')) {
            $data['tab'] = 'asset';
        } else {
            $data['tab'] ='asset';
        }
        $data['meta_title'] = 'Quản lý tài sản';
        $data['per_page'] = Cookie::get('per_page_asset', 10);
        $data['per_page_maintenance'] = Cookie::get('per_page_maintenance', 10);

        $data['filter'] = $request->all();
        $data['assets'] = $this->assetRepository->myPaginate($data['filter'], $this->building_active_id, $data['per_page']);
        $data['maintenance_assets'] = $this->maintenanceAssetRepository->myPaginate($data['filter'], $this->building_active_id, $data['per_page_maintenance']);
        return view('assets.index', $data);
    }

    public function create()
    {
        $data = $this->getAttribute();
        $data['meta_title'] = 'Thêm tài sản/Công cụ';
        return view('assets.create', $data);
    }

    public function store(CreateAssetRequest $request)
    {
        $request->merge([
            'price' => preg_replace("/([^0-9\\.])/i", "", $request->price)
        ]);
        $dataCreate = $request->except(['_token']);
        $dataCreate['bdc_building_id'] = $this->building_active_id;
        $this->assetRepository->create($dataCreate);
        return redirect()->route('admin.assets.index')->with('success', 'Thêm tài sản mới thành công.');
    }

    public function edit($id)
    {
        $data = $this->getAttribute();
        $data['meta_title'] = 'Chỉnh sửa tài sản/Công cụ';
        $asset = $this->assetRepository->findAsset($id);
        if (!$asset) {
            return abort(404);
        }
        $data['asset'] = $asset;
        return view('assets.edit', $data);
    }

    public function update(CreateAssetRequest $request, $id)
    {
        $request->merge([
            'price' => preg_replace("/([^0-9\\.])/i", "", $request->price)
        ]);
        $dataCreate = $request->except(['_token', '_method']);
        $dataCreate['bdc_building_id'] = $this->building_active_id;
        $this->assetRepository->update($dataCreate, $id);
        return redirect()->route('admin.assets.index')->with('success', 'Sửa tài sản mới thành công.');
    }

    public function destroy($id)
    {
        $this->assetRepository->findAsset($id)->delete();
        $dataResponse = [
            'success' => true,
            'message' => 'Xóa tài sản thành công!'
        ];
        return response()->json($dataResponse);
    }

    private function getAttribute()
    {
        return [
            'types' => $this->assetTypeRepository->all(),
            'periods' => $this->periodRepository->all(),
        ];
    }

    public function show($id)
    {
        $data['meta_title'] = 'Chi tiết tài sản - lịch bảo trì';
        $asset = $this->assetRepository->findAsset($id);
        if (!$asset) {
            return abort(404);
        }
        $data['asset'] = $asset;

        // dd($data['asset']);
        return view('assets.show', $data);
    }

    public function deleteMulti(Request $request)
    {
        $this->assetRepository->deleteMulti($request->ids);
        $dataResponse = [
            'success' => true,
            'message' => 'Xóa tài sản thành công!'
        ];
        return response()->json($dataResponse);
    }

    public function action(Request $request)
    {
        if ($request->has('per_page')) {
            $per_page = $request->input('per_page', 10);
            Cookie::queue('per_page_asset', $per_page, 60 * 24 * 30);
            Cookie::queue('tab_asset', $request->tab);
        }

        if ($request->has('per_page_maintenance')) {
            $per_page_maintenance = $request->input('per_page_maintenance', 10);
            Cookie::queue('per_page_maintenance', $per_page_maintenance, 60 * 24 * 30);
            Cookie::queue('tab_asset', $request->tab);
        }

        return redirect()->back()->with('tab', $request->tab);
    }

    public function checkDoneMaintain($id)
    {
        $this->maintenanceAssetRepository->checkDone($id, auth()->user()->id);
        return redirect()->to(URL::previous() . "#maintenance_asset")->with('success', 'Cập nhật thông tin bảo trì thành công!');
    }

    public function cancelCheck($id)
    {
        $this->maintenanceAssetRepository->cancelMaintain($id, auth()->user()->id);
        return redirect()->to(URL::previous() . "#maintenance_asset")->with('success', 'Cập nhật thông tin bảo trì thành công!');
    }
}
