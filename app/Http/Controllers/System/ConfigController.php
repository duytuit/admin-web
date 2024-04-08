<?php

namespace App\Http\Controllers\System;
use App\Http\Controllers\BuildingController;
use App\Repositories\Building\BuildingRepository;
use App\Repositories\Department\DepartmentRepository;
use App\Repositories\PaymentInfo\PaymentInfoRepository;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Validator;
use App\Repositories\System\ConfigRepository;

class ConfigController extends BuildingController
{
    protected $repository;
    protected $departmentRepository;
    protected $buildingRepository;

    public function __construct(Request $request, ConfigRepository $configRepository, DepartmentRepository $departmentRepository, BuildingRepository $buildingRepository)
    {
        //$this->middleware('auth', ['except'=>[]]);
        //$this->middleware('route_permision');
        Carbon::setLocale('vi');

        $this->repository = $configRepository;
        $this->departmentRepository = $departmentRepository;
        $this->buildingRepository = $buildingRepository;
        parent::__construct($request);
    }

    public function index()
    {
        $config = $this->repository->findByConfigKey('vnp');
        $configValue = null;
        if ($config) {
            $configValue = json_decode($config->config_value, true);
        }
        $departments = $this->departmentRepository->findByBuildingId($this->building_active_id);
        $building = $this->buildingRepository->getActiveBuilding($this->building_active_id);

        return view('system.config.index')->with(['meta_title' => 'Cài đặt', 'data' => $configValue, 'departments' => $departments, 'building' => $building]);
    }

    public function store(Request $request)
    {
        $data = $request->except('_token');
        $config = $this->repository->findByConfigKey($data['config_key']);
        $data['id'] = null;
        if ($config) {
            $data['id'] = $config->id;
        }

        $config = $this->repository->save($data);
        $building_id = $this->buildingRepository->getActiveBuilding($this->building_active_id)->id;

        $this->buildingRepository->updateDepartmentIdAndDebitDate($building_id, $request->bdc_department_id, $request->debit_date);

        return redirect()->action('System\ConfigController@index');
    }
}
