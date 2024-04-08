<?php

namespace App\Http\Controllers\HistoryNotify;

use App\Models\Campain;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\BuildingController;
use App\Models\LogSendMail\LogSendMail;
use App\Models\LogSendSMS\LogSendSMS;
use App\Models\NotifyLog\NotifyLog;
use App\Models\Vnpay\VnpayReturnLog;
use Illuminate\Support\Facades\Cookie;
use Carbon\Carbon;
use App\Models\Fcm\Fcm;
use Illuminate\Support\Facades\Auth;

class HistoryNotifyController extends BuildingController
{

    /**
     * Khởi tạo
     */
    public function __construct(Request $request)
    {
        //$this->middleware('auth', ['except'=>[]]);
        //$this->middleware('route_permision');
        parent::__construct($request);
    }
    /**
     * Danh sách bản ghi
     *
     * @param  Request  $request
     * @return Response
     */
    public function index(Request $request)
    {
        $data['meta_title'] = "Lịch sử gửi email";
         // Phân trang
        $data['per_page_email'] = Cookie::get('per_page_email',20);
        $data['per_page_sms'] = Cookie::get('per_page_sms',20);
        $data['per_page_notify_app'] = Cookie::get('per_page_notify_app',20);
        $data['per_page_payment'] = Cookie::get('per_page_payment',20);
        $data['per_page_tokenfcm'] = Cookie::get('per_page_tokenfcm',20);
          // tokenfcm
        $data['tokenfcm_keyword']   = $request->input('tokenfcm_keyword') ?? null;
        $data['tokenfcm_date']   = $request->input('tokenfcm_date') ?? null;
        
        if(!empty($data['tokenfcm_keyword']) || !empty($request['tokenfcm_date']) ){
            $query_tokenfcm = Fcm::where('user_id',$request['tokenfcm_keyword'])->orderBy('updated_at', 'desc')->paginate((int)$data['per_page_tokenfcm']);
        }else{
           $query_tokenfcm = Fcm::orderBy('updated_at', 'desc')->paginate((int)$data['per_page_tokenfcm']);
        }
       
        $data['tokenfcms'] = $query_tokenfcm;

        return view('history-notify.index', $data);
    }

    public function entire(Request $request)
    {
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
        //
        $data['sended_mail'] = $campains->sum('sended_email');
        $data['sended_app'] = $campains->sum('sended_app');
        $data['sended_sms'] = $campains->sum('sended_sms');
        $mail_total=0; 
        $sms_total= 0;
        $app_total=0;
        foreach ($campains as $item) {
        $app_total  += json_decode($item->total)->app;
        $sms_total  += json_decode($item->total)->sms;
        $mail_total += json_decode($item->total)->email;
        }
        $data['app_total']= $app_total;
        $data['mail_total']= $mail_total;
        $data['sms_total']= $sms_total;
        $data['advance'] = $advance;
        $data['heading'] = 'Quản lý trạng thái gửi thông báo';
        $data['meta_title'] = 'Quản lý trạng thái gửi thông báo';
        return view('Mailandsms.index', $data);
    }
  
    public function action(Request $request)
    {
        if ($request->has('per_page_email')) {
            $page_email = $request->input('per_page_email', 20);
            Cookie::queue('per_page_email', $page_email, 60 * 24 * 30);
            Cookie::queue('per_page_email', $request->tab);
        }

        if ($request->has('per_page_sms')) {
            $page_sms = $request->input('per_page_sms', 20);
            Cookie::queue('per_page_sms', $page_sms, 60 * 24 * 30);
            Cookie::queue('tab_per_page_sms', $request->tab);
        }

        if ($request->has('per_page_notify_app')) {
            $page_notify_app = $request->input('per_page_notify_app', 20);
            Cookie::queue('per_page_notify_app', $page_notify_app, 60 * 24 * 30);
            Cookie::queue('tab_per_page_notify_app', $request->tab);
        }

        if ($request->has('per_page_payment')) {
            $page_payment = $request->input('per_page_payment', 20);
            Cookie::queue('per_page_payment', $page_payment, 60 * 24 * 30);
            Cookie::queue('tab_per_page_payment', $request->tab);
        }

        return redirect()->back()->with('tab', $request->tab);
    }
     public function delete(Request $request)
    {
        $id = $request->input('ids');
        Fcm::destroy(['id' => $id]);
        $request->session()->flash('success', 'Xóa FCM thành công');
    }
}
