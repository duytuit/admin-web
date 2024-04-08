<?php

namespace App\Http\Controllers\CustomerRatedServices\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\BuildingController;
use App\Traits\ApiResponse;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Auth;
use App\Repositories\CustomerRatedServices\CustomerRatedServicesRepository;
use App\Http\Requests\CustomerRatedServices\CustomerRatedServiceRequest;

class CustomerRatedServiceController extends BuildingController
{
    use ApiResponse;
    private $_customerRatedServicesRepository;

    public function __construct(
            Request $request,
            CustomerRatedServicesRepository $customerRatedServicesRepository
        )
    {
        //$this->middleware('jwt.auth');
        $this->_customerRatedServicesRepository = $customerRatedServicesRepository;
        parent::__construct($request);
    }
    public function index(Request $request)
    {
        try{
            $keyword = $request->all();
            $per_page = $request->input('per_page', 10);
            $per_page = $per_page > 0 ? $per_page : 10;
            $CustomerRatedService = $this->_customerRatedServicesRepository->myPaginate($keyword, $per_page, $request->building_id)->toArray();
            if($CustomerRatedService){
                 return $this->responseSuccess($CustomerRatedService['data'],'lấy danh sách thành công');
            }
             return $this->responseError('lấy danh sách thất bại', 400);
        } catch(\Exception $e) {
            return $this->responseError($e->getMessage(), 500);
        }

    }
    public function add(CustomerRatedServiceRequest $request)
    {
        try{
             $CustomerRatedService = $this->_customerRatedServicesRepository->create([
                'customer_name' => $request->customer_name,
                'email'  => $request->email,
                'phone' => $request->phone,
                'apartment_name' =>  $request->apartment_name,
                'rated' =>  $request->rated,
                'description' => $request->description,
                'employee_id' =>  $request->employee_id,
                'department_id' => $request->department_id,
                'bdc_building_id' => $request->bdc_building_id
            ]);
            if($CustomerRatedService){
                 return $this->responseSuccess(['tạo đánh giá thành công'], 'Success', 200);
            }
             return $this->responseError('tạo đánh giá thất bại', 400);
        } catch(\Exception $e) {
            return $this->responseError($e->getMessage(), 500);
        }

    }
    
}
