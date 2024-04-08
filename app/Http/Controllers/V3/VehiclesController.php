<?php

namespace App\Http\Controllers\V3;

use App\Commons\ApiResponse;
use App\Http\Controllers\BuildingController;
use App\Http\Requests\Vehicles\VehiclesRequest;
use App\Models\Apartments\Apartments;
use App\Models\Vehicles\Vehicles;
use App\Repositories\Apartments\ApartmentsRespository;
use App\Repositories\BdcApartmentServicePrice\ApartmentServicePriceRepository;
use App\Repositories\BdcProgressivePrice\ProgressivePriceRepository;
use App\Repositories\Service\ServiceRepository;
use App\Repositories\VehicleCards\VehicleCardsRespository;
use App\Repositories\VehicleCategory\VehicleCategoryRespository;
use App\Repositories\Vehicles\VehiclesRespository;
use App\Services\SendTelegram;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\BdcApartmentServicePrice\ApartmentServicePrice;
use App\Models\PublicUser\Users;
use App\Models\UserRequest\UserRequest;
use App\Models\VehicleCards\VehicleCards;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Facades\Excel;
use App\Repositories\Building\BuildingPlaceRepository;
use App\Models\VehicleCategory\VehicleCategory;
use PHPExcel_Style_Border;

class VehiclesController extends BuildingController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    private $model;
    private $modelApartment;
    private $modelCategory;
    private $modelBuildingPlace;
    private $modelService;
    private $modelProgressivePrice;
    private $modelVehicleCard;
    private $modelApartmentServicePrice;


    public function __construct(
        VehiclesRespository $model,
        ApartmentsRespository $modelApartment,
        VehicleCategoryRespository $modelCategory,
        Request $request,
        BuildingPlaceRepository $modelBuildingPlace,
        ServiceRepository $modelService,
        ProgressivePriceRepository $modelProgressivePrice,
        VehicleCardsRespository $modelVehicleCard,
        ApartmentServicePriceRepository $modelApartmentServicePrice
    ) {
        //$this->middleware('route_permision');
        $this->model = $model;
        $this->modelApartment = $modelApartment;
        $this->modelCategory = $modelCategory;
        $this->modelBuildingPlace = $modelBuildingPlace;
        $this->modelService = $modelService;
        $this->modelProgressivePrice = $modelProgressivePrice;
        $this->modelVehicleCard = $modelVehicleCard;
        $this->modelApartmentServicePrice = $modelApartmentServicePrice;
        parent::__construct($request);
    }

    public function totalinout (Request $request)
    {
        $data['meta_title'] = 'Vehicles';
        $data['per_page'] = Cookie::get('per_page', 20);

        $data['tab'] = $request->get('tab', '');
        $data['data_search'] = $request->all();
        $data['data_vhc'] = Session::get('data_vhc');
        $data['data_error'] = Session::get('error');
        $data['data_success'] = Session::get('success');

        $building_ac= $this->building_active_id;
         //check Vehicledaily 
         $sql= "select * From Transfer_countvehicle";
         $rs= DB::select(DB::raw($sql));
         $data['VehicleOut_Daily'] = $rs[0]->VehicleOut_Daily;
         $data['VehicleIn_Daily'] = $rs[0]->VehicleIn_Daily;
         $data['VehicleOut_Monthly'] = $rs[0]->VehicleOut_Monthly;
         $data['VehicleIn_Monthly'] = $rs[0]->VehicleIn_Monthly;
         $data['totalVehicleInPark_car'] = $rs[0]->totalVehicleInPark_car;
         $data['totalVehicleInPark_motor'] = $rs[0]->totalVehicleInPark_motor;
         $data['totalVehicleInPark_motor_Daily'] = $rs[0]->totalVehicleInPark_motor_Daily;
         $data['totalVehicleInPark_bicycle'] = $rs[0]->totalVehicleInPark_bicycle;
         $data['totalVehicleInPark_electricBike'] = $rs[0]->totalVehicleInPark_electricBike;
         $data['totalVehicleInPark'] = $rs[0]->totalVehicleInPark;
         $sql1= "select * from Transfer_event where building_id = $building_ac ";
         if (isset($data['data_search']['type_dir'])) {
            $sql1.= " and LaneDirection='".$data['data_search']['type_dir']."'";
        }
        if (isset($data['data_search']['type_vehi'])) {
            $sql1.= " and VehicleType='".$data['data_search']['type_vehi']."'";
        }
        $sql1.= " order by EventDateTime desc LIMIT ". $data['per_page'];
         $rs1= DB::select(DB::raw($sql1));
         $data['Vehicle_event'] = $rs1;
        return view('vehicles.v3.totalinout', $data);
    }
}
