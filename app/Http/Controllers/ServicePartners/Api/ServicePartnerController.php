<?php

namespace App\Http\Controllers\ServicePartners\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\BuildingController;
use App\Models\Campain;
use App\Models\SentStatus;
use App\Traits\ApiResponse;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Auth;
use App\Services\ServiceSendMail;
use App\Services\ServiceSendMailV2;
use App\Repositories\BusinessPartners\BusinessPartnerRepository;
use Validator;
use App\Repositories\ServicePartners\ServicePartnersRepository;

class ServicePartnerController extends BuildingController
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
            'bdc_handbook_id'  =>'required|numeric',
            'customer'  =>'required',
            'phone' =>'required|regex:/^([0-9\s\-\+\(\)]*)$/|min:10',
            'email' =>'required|regex:/(.+)@(.+)\.(.+)/i',
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
                'bdc_handbook_id'  => $request->bdc_handbook_id,
                'customer'  => $request->customer,
                'phone' =>  $request->phone,
                'email' =>  $request->email,
                'timeorder' => $request->timeorder,
                'description' =>  $request->description,
                'bdc_building_id' =>  $request->bdc_building_id,
                'status' => 0
            ]);
            if($Partner){
                 $this->sendMail($request->bdc_business_partners_id,$request->bdc_handbook_id, $request->customer,$request->phone, $request->email,$request->timeorder,$request->description);
                 return $this->responseSuccess(['đăng ký dịch vụ thành công'], 'Success', 200);
            }
             return $this->responseError('đăng ký dịch vụ thất bại', 400);
        } catch(\Exception $e) {
            return $this->responseError($e->getMessage(), 500);
        }

    }
     public function sendMail($bdc_business_partners_id, $bdc_handbook_id, $customer, $phone, $email, $timeorder, $description, $type=25)
    {
        $service_partner = $this->servicePartnersRepository->getServicePartnersbyHandbookId($bdc_handbook_id);
 
        $total = ['email'=>1, 'app'=> 0, 'sms'=> 0];
        $campain = Campain::updateOrCreateCampain("Gửi mail đối tác: ".$email, config('typeCampain.SERVICE_PARTNER'), null, $total, $this->building_active_id, 0, 0);

         
        $data = [
            'params' => [
                '@dichvu' => @$service_partner->building_handbooks->title,
                '@doitac' => @$service_partner->businesspartners->name,
                '@khach' => $customer,
                '@phone' => $phone,
                '@timeorder' => $timeorder,
                '@mota' => $description,
                '@tentoanha'=> @$service_partner->building->name,
                '@url'=> ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == "on") ? "https" : "http"). "://". @$_SERVER['HTTP_HOST']
            ],
            'cc' => @$service_partner->businesspartners->email,
            'building_id' => $service_partner->bdc_building_id,
            'type' => $type,
            'status' => 'dang ky dich vu doi tac',
            'campain_id' => $campain->id
        ];
        try {
            ServiceSendMailV2::setItemForQueue($data);
            return;
        } catch (\Exception $e)
        {
            return $e->getMessage();
        }
    }
}
