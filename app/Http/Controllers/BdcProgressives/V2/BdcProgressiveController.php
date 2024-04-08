<?php

namespace App\Http\Controllers\BdcProgressives\V2;

use App\Http\Controllers\BuildingController;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Progressives\ImportExcelRequest;
use App\Http\Requests\Progressives\ProgressiveRequest;
use App\Models\BdcPriceType\PriceType;
use App\Models\BdcProgressivePrice\ProgressivePrice;
use App\Models\BdcProgressives\Progressives;
use App\Models\Building\Building;
use App\Repositories\Apartments\ApartmentsRespository;
use App\Repositories\BdcBills\BillRepository;
use App\Repositories\BdcApartmentServicePrice\ApartmentServicePriceRepository;
use App\Repositories\BdcDebitDetail\V2\DebitDetailRepository;
use App\Repositories\BdcDebitLogs\DebitLogsRepository;
use App\Repositories\BdcProgressive\V2\ProgressiveRepository;
use App\Repositories\Building\BuildingRepository;
use App\Repositories\CronJobManager\CronJobManagerRepository;
use App\Repositories\Customers\CustomersRespository;
use App\Repositories\Service\ServiceRepository;
use App\Traits\ApiResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cookie;

class BdcProgressiveController extends BuildingController
{
    use ApiResponse;

    protected $model;

    const CREATE_PRICE_FAILURE = 203;

    public function __construct(Request $request, ProgressiveRepository $model, DebitDetailRepository $debitDetail)
    {
        //$this->middleware('auth', ['except'=>[]]);
        //$this->middleware('route_permision');
        $this->model = $model;
        $this->debitDetail = $debitDetail;
        Carbon::setLocale('vi');
        parent::__construct($request);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        $data['meta_title'] = 'Bảng giá';
        $data['per_page'] = Cookie::get('per_page', 20);

        $progressive = $this->model->findByBuildingId($this->building_active_id)->paginate($data['per_page']);
        $data['progressive'] = $progressive;
        $data['count_display'] = count($progressive);
        return view('progressive.v2.index', $data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(PriceType $modelPriceType)
    {
        $progressives = $modelPriceType::pluck('name', 'id');
        return view('progressive.v2.create', ['meta_title' => 'Tạo bảng giá', 'progressives' => $progressives]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(ProgressiveRequest $request, ProgressivePrice $progressivePrice, BuildingRepository $buildingRepository)
    {
        $input = $request->all();

        \DB::beginTransaction();
        try {
            $building = Building::find($this->building_active_id);
            $this->model->addProgressive($building->id, $building->company_id, $input, $progressivePrice);
        } catch (\Exception $e) {
            \DB::rollBack();
            throw new \Exception("register ERROR: " . $e->getMessage(), 1);
        }
        \DB::commit();

        return redirect('admin/v2/progressive')->with('success', 'Thêm bảng giá thành công.');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit(PriceType $modelPriceType, $id)
    {
        $progressive = Progressives::find($id);
        $progressivePrice = $progressive->progressivePrice()->get();
        $progressives = $modelPriceType::pluck('name', 'id');
        $selectedRole = $progressive->bdc_price_type_id;
        return view('progressive.v2.edit', [
            'meta_title' => 'Sửa bảng giá',
            'item' => $progressive,
            'progressivePrices' => $progressivePrice,
            'progressives' => $progressives,
            'selectedRole' => $selectedRole
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update($id, ProgressiveRequest $request, ProgressivePrice $progressivePrice, BuildingRepository $buildingRepository)
    {
        $input = $request->all();

        \DB::beginTransaction();
        try {
            $building = Building::find($this->building_active_id);
            //$companyId = $buildingRepository->getCompanyOfBuildingId($building->urban_id);
            $this->model->updateProgressive($id, $building->id, $building->company_id, $input, $progressivePrice);
        } catch (\Exception $e) {
            \DB::rollBack();
            throw new \Exception("register ERROR: " . $e->getMessage(), 1);
        }
        \DB::commit();

        return redirect('admin/v2/progressive')->with('success', 'Sửa bảng giá thành công.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function delete($id)
    {
        $rs = $this->model->delete(['id' => $id]);
        if ($rs) {
            return $this->responseSuccess([], ['Xóa bảng giá thành công!']);
        } else {
            return $this->responseError(['Xóa bảng giá thất bại!'], self::CREATE_PRICE_FAILURE);
        }
    }

    public function importExcel()
    {
        //dd($this->building_active_id);
        $data['meta_title'] = 'Import Excel Dịch Vụ Lũy Tiến';
        return view('progressive.v2.import_excel', $data);
    }

    public function importExcelPhiDauKy()
    {
        //dd($this->building_active_id);
        $data['meta_title'] = 'Import Excel phí dịch vụ';
        return view('progressive.v2.import_phi_dau_ky', $data);
    }

    public function importFileExcelPost(
        ImportExcelRequest              $request,
        CronJobManagerRepository        $cronJobManager,
        ApartmentServicePriceRepository $apartmentServicePrice,
        CustomersRespository            $customer,
        ServiceRepository               $service,
        ApartmentsRespository           $apartmentRepository,
        DebitLogsRepository             $debitLogs)
    {
        if (!$request->file('file_import')) {
            return redirect('admin/v2/progressive/import-excel')->with('warning', 'Không có file tải lên.');
        }
        $importDienNuoc = $this->debitDetail->importFileDienNuoc(
            $request,
            $cronJobManager,
            $this->building_active_id,
            $apartmentServicePrice,
            $customer,
            $service,
            $apartmentRepository,
            $debitLogs);
        if (!is_bool($importDienNuoc)) {
            return redirect('admin/v2/progressive/import-excel')->with('warning', "Kỳ $importDienNuoc đã bị khóa");
        }
        if ($importDienNuoc) {
            return redirect('admin/v2/progressive/import-excel')->with('success', 'Import file thành công.');
        }
        return redirect('admin/v2/progressive/import-excel')->with('warning', 'Import file không thành công.');
    }

    public function importFileExcelPhiDauKyPost(
        ImportExcelRequest              $request,
        CronJobManagerRepository        $cronJobManager,
        ApartmentServicePriceRepository $apartmentServicePrice,
        CustomersRespository            $customer,
        ServiceRepository               $service,
        ApartmentsRespository           $apartmentRepository,
        DebitLogsRepository             $debitLogs)
    {
        if (!$request->file('file_import')) {
            return redirect('admin/v2/progressive/import-phi-dau-ky')->with('danger', 'Không có file tải lên.');
        }
        $importPhiDauKy = $this->debitDetail->importFileDauKy(
            $request,
            $cronJobManager,
            $this->building_active_id,
            $apartmentServicePrice,
            $customer,
            $service,
            $apartmentRepository,
            $debitLogs);
        if (!is_bool($importPhiDauKy)) {
            return redirect('admin/v2/progressive/import-excel')->with('warning', "Kỳ $importPhiDauKy đã bị khóa");
        }
        if ($importPhiDauKy) {
            return redirect('admin/v2/progressive/import-phi-dau-ky')->with('success', 'Import file thành công.');
        }
        return redirect('admin/v2/progressive/import-phi-dau-ky')->with('danger', 'Import file không thành công.');
    }

    public function download()
    {
        $file = public_path() . '/downloads/dien_nuoc_template.xlsx';
        return response()->download($file);
    }

    public function downloadphidauky()
    {
        $file = public_path() . '/downloads/phi_dich_vu_template.xlsx';
        return response()->download($file);
    }
}
