<?php

namespace App\Http\Controllers\BusinessPartner\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\BuildingController;
use App\Traits\ApiResponse;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Auth;
use App\Repositories\BusinessPartners\BusinessPartnerRepository;
use Validator;
use App\Repositories\ServicePartners\ServicePartnersRepository;

class BusinessPartnerController extends BuildingController
{
    use ApiResponse;
     private $businessPartnerRepository;
    private $servicePartnersRepository;

    public function __construct(
        Request $request,
        BusinessPartnerRepository $businessPartnerRepository,
        ServicePartnersRepository $servicePartnersRepository
        )
    {
        //$this->middleware('jwt.auth');
         $this->businessPartnerRepository = $businessPartnerRepository;
         $this->servicePartnersRepository = $servicePartnersRepository;
        parent::__construct($request);
    }
     public function create(Request $request)
    {
            $validator = Validator::make($request->all(), [
            'bdc_business_partners_id'         => 'required|numeric',
            'bdc_building_id'  =>'required|numeric',
            'timeorder'   => 'required',
            'description'  => 'required',
			]);
			if ($validator->fails()) {
				return response()->json(['error' => $validator->errors()], 404);
			}
        try{
             $Partner = $this->servicePartnersRepository->create([
                'pub_users_id' => Auth::id(),
                'bdc_business_partners_id'  => $request->bdc_business_partners_id,
                'timeorder' => $request->timeorder,
                'description' =>  $request->description,
                'bdc_building_id' =>  $request->bdc_building_id,
                'status' => 0
            ]);
            if($Partner){
                 return $this->responseSuccess(['đăng ký dịch vụ thành công'], 'Success', 200);
            }
             return $this->responseError('đăng ký dịch vụ thất bại', 400);
        } catch(\Exception $e) {
            return $this->responseError($e->getMessage(), 500);
        }

    }
}
