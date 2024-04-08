<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\BuildingController;
use App\Models\BdcBills\Bills;
use App\Models\Building\Building;
use App\Models\Campain;
use App\Models\CampainDetail;
use App\Models\Service\Service;
use App\Models\Vehicles\Vehicles;
use App\Repositories\BdcApartmentServicePrice\ApartmentServicePriceRepository;
use App\Repositories\BdcBills\V2\BillRepository;
use App\Repositories\BdcV2DebitDetail\DebitDetailRepository as BdcV2DebitDetailDebitDetailRepository;
use App\Repositories\Customers\CustomersRespository;
use App\Repositories\PublicUsers\PublicUsersProfileRespository;
use App\Services\FCM\SendNotifyFCMService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Cookie;

class CampainController extends BuildingController
{
    public function index(Request $request)
    {
        // Phân trang
        $data['per_page'] = Cookie::get('per_page', 10);

        // Tìm kiếm nâng cao
        $advance = 0;

        $data['keyword']     = $request->input('keyword', '');
        $data['type'] = $request->input('type', '');
        $data['filter'] = $request->all();

        $where = [];
        $where[] = ['bdc_building_id', $this->building_active_id];

        if (empty($data['type']) && $data['type'] != null) {
            $where[] = ['type', '=', $request->type];
            $advance = 1;
        }
        $campains = Campain::where($where);
        if ($data['keyword']) {
            $campains->Where(function ($query) use ($request) {
                $query->orWhere('title', 'like', '%' . $request->keyword . '%');
            });
            $advance = 1;
        }
        if (isset($request->from_date) && $request->from_date !=null) {
            $from_date = Carbon::parse($request->from_date)->format('Y-m-d');
            $campains->whereDate('updated_at','=',$from_date);
        }
        $campains = $campains->orderBy('id', 'desc')->paginate($data['per_page']);
        $data['campains'] = $campains;

        $data['advance'] = $advance;
        $data['heading'] = 'Quản lý trạng thái gửi thông báo';
        $data['meta_title'] = 'Quản lý trạng thái gửi thông báo';
        return view('backend.campain.index', $data);
    }

    public function getCampainDetail(Request $request, $id)
    {
        $data['heading'] = 'Quản lý gửi thông báo';
        $data['meta_title'] = $data['heading'] ;
        $data['per_page'] = Cookie::get('per_page', 20);
        $data['filter'] = $request->all();
        $data['filter']['keyword'] = $request->input('keyword', '');
        $data['filter']['type'] = $request->input('type', 'email');

        $campain = Campain::find($id);
        if(!$campain){
            return redirect()->back()->with('warning','không tìm thấy dữ liệu');
        }
        $campainDetail = CampainDetail::where(function ($query) use ($data,$campain) {
            $query->where('campain_id',$campain->id);
            if ($data['filter']['keyword']) {
                $keyword = $data['filter']['keyword'];
                $query->where('contact', 'like', '%' . $keyword . '%');
            }
            if ($data['filter']['type']) {
                $type = $data['filter']['type'];
                $query->where('type', $type );
            }
        })->paginate((int)$data['per_page']);
        $data['posts'] = $campainDetail;
        $data['id'] = $id;
        $data['campain'] = $campain;
        return view('backend.campain.detail', $data);
    }
    public function action(Request $request,BillRepository $billRepo)
    {
        $method = $request->input('method', '');
        if ($method == 'per_page') {
            $this->per_page($request);
            return back();
        }
        if ($method == 'recall_email') {
            if(!$request->ids){
                return back()->with('warning', 'Không có bản ghi nào được chọn');
            }else{
                $ids = $request->ids;
                foreach ($ids as $index => $item) {
                    $campain = Campain::find($item);
                    if($campain && $campain->type == 0){
                        $campain->updated_at = Carbon::now();
                        $campain->save();
                        $bill = Bills::where('id', $campain->type_id)->first();
                        if ($bill) {
                            $apart = $bill->apartment;
                            $_customer = CustomersRespository::findResidentApartment($bill->bdc_apartment_id, 0);
                            if ($_customer->count() > 0) {
                                // lấy tổng dư nợ cuối kỳ
                                $getServiceApartments = BdcV2DebitDetailDebitDetailRepository::getAllApartmentDetailLastTime4($bill->bdc_building_id, $bill->cycle_name, null, $bill->bdc_apartment_id, false, false, 100, 1);
                                $sumbill      = BdcV2DebitDetailDebitDetailRepository::sumByBillId($bill->id);
                                $total = ['email' => 1, 'app' => 1, 'sms' => 0];
                                $campain = Campain::updateOrCreateCampain("Bill: " . $bill->bill_code.' '.$bill->cycle_name, config('typeCampain.HOA_DON'), $bill->id, $total, $bill->bdc_building_id, 0, 0);
                                foreach ($_customer as $key_cus => $value_cus) {
                                    $pubUserProfile = $value_cus ? PublicUsersProfileRespository::getInfoUserById($value_cus->user_info_id) : null;

                                    $building = Building::get_detail_building_by_building_id($bill->bdc_building_id);
                                    $data_noti_v2 = [
                                        "message" => 'Trạng thái: chờ thanh toán',
                                        "building_id" => $bill->bdc_building_id,
                                        "title" => '[' . $building->name . "]_" . @$apart->name . ' hóa đơn kỳ tháng ' . @$bill->cycle_name,
                                        "action_name" => $billRepo::BILL_NEW,
                                        'type' => $billRepo::BILL_NEW,
                                        'id' => $bill->id,
                                        'avatar' => "avatar/system/01.png",
                                        'app' => 'v2'
                                    ];

                                    $email = $pubUserProfile->email_contact;
                                    $name = $pubUserProfile->full_name;
                                    $pub_user_id = $pubUserProfile->user_id;
                                    SendNotifyFCMService::setItemForQueueNotify(array_merge($data_noti_v2, ['user_id' => $pub_user_id, 'app_config' => @$building->template_mail == 'asahi' ? 'asahi' : 'cudan', 'campain_id' => $campain->id]));
                                    // send mail
                                    if (filter_var($pubUserProfile->email_contact, FILTER_VALIDATE_EMAIL)) {
                                        $debitsTotal = null;
                                        $view = null;
                                        $sum_du_no_cuoi_ky = 0;
                                        if ($getServiceApartments) {
                                            foreach ($getServiceApartments as $key => $value) {
                                                $servicePrice = ApartmentServicePriceRepository::getInfoServiceApartmentById($value->bdc_apartment_service_price_id);
                                                $Vehicles = null;
                                                if ($value->bdc_apartment_service_price_id == 0) {
                                                    $service = (object) ["code_receipt" => "", "name" => "Tiền thừa"];
                                                } else {
                                                    $service = Service::get_detail_bdc_service_by_bdc_service_id($servicePrice->bdc_service_id);
                                                    if ($servicePrice->bdc_vehicle_id > 0) {
                                                        $Vehicles = Vehicles::get_detail_vehicle_by_id($servicePrice->bdc_vehicle_id);
                                                    }
                                                }
                                                $detail = [
                                                    'ten_khach_hang' => @$pubUserProfile->full_name,
                                                    'can_ho' =>  @$apart->name,
                                                    'ma_san_pham' => @$apart->code,
                                                    'ma_thu' =>  $service->code_receipt,
                                                    'dich_vu' => isset($Vehicles) ? $service->name . ' - ' . $Vehicles->number  : $service->name,
                                                    'dau_ky' =>  $value->cycle_name == $bill->cycle_name ? $value->before_cycle_name : $value->after_cycle_name,
                                                    'trong_ky' => $value->cycle_name == $bill->cycle_name ? $value->sumery : 0,
                                                    'thanh_toan' => $value->cycle_name == $bill->cycle_name ? $value->paid_by_cycle_name : 0,
                                                    'cuoi_ky' => $value->after_cycle_name,
                                                ];
                                                $sum_du_no_cuoi_ky += $value->after_cycle_name;
                                                $debitsTotal[] = collect($detail);
                                            }
                                        }
                                        $base_url=((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == "on") ? "https" : "http"). "://". @$_SERVER['HTTP_HOST'];
                                        if ($debitsTotal) {
                                            $view = view('bill.v2._send_mail', compact('debitsTotal'))->render();
                                            $view = base64_encode($view);
                                            $cost = $sumbill->thanh_tien - $sumbill->thanh_toan;
                                            $url = isset($bill->url) ? $base_url . '/' . $bill->url . '?version=2' : "không có URL";
                                            $billRepo->sendMailBill($email, @$name, @$cost, @$apart->name, @$bill->confirm_date, $url, $bill->bill_code, $bill->cycle_name, $bill->bdc_building_id, $sum_du_no_cuoi_ky, $bill->id, $campain, $view);
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
            return back()->with('success', 'Đang gửi lại '.count($ids).' email');
        }
    }
}
