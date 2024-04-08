<?php

namespace App\Services;

use Illuminate\Support\Facades\Redis;
use App\Models\Building\Building;
use App\Models\Campain;
use App\Models\CampainDetail;
use App\Models\PaymentInfo\PaymentInfo;
use App\Util\Debug\Log;
use Carbon\Carbon;

class ServiceSendMailV2
{
    const FORGOT = 3;
    const NEW_USER = 100;
    const NEW_PROFILE = 99;
    const RESIDENT = 4;
    const BILL = 69;
    const NHAC_NO = 70;
    const POST_NEWS = 20;
    const VERIFY_CODE = 33;
    const SERVICE_PARTNER = 25;
    const NOTIFY_TRANSACTION_PAYMENT = 26;
    public static function sendMail($settingSendMail, $mailTemplateRepository, $campains)
    {
        $time_start = microtime(true);
        do {
            $data = self::getItemForQueueV2($campains->id);
            // Log::info('check_send_mail','_3_'.json_encode($data));
            // Duong check send mail V2 
            if ($data == null) {
                //Campain::updateStatus($campains->id, 'email');
                RedisCommanService::delKey(env('REDIS_QUEUE_PREFIX') . 'REDIS_SEND_MAIL_Campain_running');
                break;
            }
            $log['data'] = $data;
            if ($data && count($data) > 0 && isset($data['cc']) && isset($data['building_id'])) { //kiểm tra có nội dung trong data lấy ra hay không
                $building = Building::get_detail_building_by_building_id($data['building_id']);
    
                $building_payment_info = PaymentInfo::get_detail_payment_info_by_building_id($data['building_id']);
    
                $html = null;
                if ($building_payment_info) {
                    foreach ($building_payment_info as $key => $value) {
                        $html[] = '<div><p><strong>Thông tin thanh toán ' . ($key + 1) . ':</strong></p>' .
                            '<p>Số tài khoản: ' . $value->bank_account . ' </p>' .
                            '<p>Ngân hàng: ' . $value->bank_name . ' </p>' .
                            '<p>Chủ tài khoản: ' . $value->holder_name . ' </p>' .
                            '<p>Chi nhánh: ' . $value->branch . ' </p></div>';
                    }
                }
    
                if (isset($data['type']) && isset($data['status']) && isset($building->template_mail)) { // kiểm tra xem nó có type và status không để lấy template
                    $template = $settingSendMail->getTemplate($data['building_id'], $data['type'], $data['status']);
                    if ($data['type'] == self::NEW_USER) {
                        $template = json_encode([
                            'ten_khach_hang' => $data['params']['@ten'],
                            'tai_khoan' => $data['cc'],
                            'ten_toa' => $building->name,
                            'mat_khau' => $data['params']['@pass'],
                            'sdt_bql' => $building->phone,
                            'email_bql' => $building->email,
                            'thong_tin_thanh_toan' => $html ? implode(',', $html) : null,
                        ]);
                        $type = $building->template_mail . '_create_account_first';
                    }
    
                    if ($data['type'] == self::BILL) {
                        $template = json_encode([
                            'ten_khach_hang' => $data['params']['@tenkhachhang'],
                            'ten_toa' => $building->name,
                            'ten_can_ho' => $data['params']['@canho'],
                            'ky_hoa_don' => $data['params']['@cyclename'],
                            'tong_tien' => $data['params']['@tongtien'],
                            'noi_dung' => $data['params']['@noidung'],
                            'du_no_cuoi_ky' => $data['params']['@dunocuoiky'],
                            'link_pdf' => $data['params']['@linkpdf'],
                            'thoi_han_thanh_toan' => $data['params']['@ngay'],
                            'ma_bang_ke' => $data['params']['@billcode'],
                            'sdt_bql' => $building->phone,
                            'email_bql' => $building->email,
                            'thong_tin_thanh_toan' => $html ? implode(',', $html) : null,
                        ]);
                        $type = $building->template_mail . '_bill';
                    }
                    if ($data['type'] == self::NHAC_NO) {
                        $template = json_encode([
                            'ten_khach_hang' => $data['params']['@tenkhachhang'],
                            'ten_toa' => $building->name,
                            'ten_can_ho' => $data['params']['@canho'],
                            'ky_hoa_don' => $data['params']['@kyhoadon'],
                            'noi_dung' =>  isset($data['params']['@message']) ? base64_encode($data['params']['@message']) : null,
                            'chi_tiet' =>  $data['params']['@noidung'],
                            'du_no_cuoi_ky' => $data['params']['@dunocuoiky'],
                            'sdt_bql' => $building->phone,
                            'email_bql' => $building->email,
                            'thong_tin_thanh_toan' => $html ? implode(',', $html) : null,
                        ]);
                        $type = $building->template_mail . '_bill_recall';
                    }
    
                    if ($data['type'] == self::RESIDENT) {
                        $template = json_encode([
                            'ten_khach_hang' => $data['params']['@ten'],
                            'ten_toa' => $building->name,
                            'ten_can_ho' => $data['params']['@canho'] ?? '',
                            'sdt_bql' => $building->phone,
                            'email_bql' => $building->email,
                            'thong_tin_thanh_toan' => $html ? implode(',', $html) : null,
                        ]);
                        $type = $building->template_mail . '_create_apartment';
                    }
    
                    if ($data['type'] == self::NEW_PROFILE) {
                        // $template =[
                        //     'content' => '<p>Chào @ten,</p><p>Bạn vừa được thêm vào tòa nhà @ten tòa nhà của hệ thống BuildingCare.</p><p>Thanks,</p>',
                        //     'subject' => 'BuildingCare Thông báo'
                        // ];
                        // $type='bdc_create_account_first';
                    }
    
                    if ($data['type'] == self::POST_NEWS) {
                        $template = json_encode([
                            'tieu_de' => $data['subject'],
                            'ten_khach_hang' => $data['params']['@ten'],
                            'tieu_de' => $data['subject'],
                            'noi_dung' => isset($data['content']) ?  base64_encode($data['content']) : null,
                            'sdt_bql' => $building->phone,
                            'ten_toa' => $building->name,
                            'email_bql' => $building->email,
                            'thong_tin_thanh_toan' => $html ? implode(',', $html) : null,
                        ]);
                        $type = $building->template_mail . '_notification';
                    }
                    //Code OTP
                    if ($data['type'] == self::VERIFY_CODE) {
                        $template = json_encode([
                                    'ten_khach_hang' => $data['params']['@ten'],
                                    'otp' => $data['params']['@otp'],
                                    'sdt_bql' => $building->phone,
                                    'ten_toa' => $building->name,
                                    'email_bql' => $building->email,
                                    'thong_tin_thanh_toan' =>$html ? implode(',',$html) : null,
                        ]);
                        $type = $building->template_mail . '_send_otp_change_password';
                    }
                    //quen mat khau
                    if ($data['type'] == self::FORGOT) {
                        $template = json_encode([
                                    'ten_khach_hang' => $data['params']['@ten'],
                                    'mat_khau' => $data['params']['@pass'],
                                    'ten_toa' => $building->name,
                                    'sdt_bql' => $building->phone,
                                    'email_bql' => $building->email,
                                    'thong_tin_thanh_toan' =>$html ? implode(',',$html) : null,
                        ]);
                        $type = $building->template_mail . '_change_password';
                    }
    
                    // đăng ký dịch vụ đối tác
                    // đăng ký dịch vụ đối tác
    
                    if ($data['type'] == self::SERVICE_PARTNER) {
                        $template = json_encode([
                            'ten_khach_hang' => $data['params']['@khach'],
                            'so_dien_thoai' => $data['params']['@phone'],
                            'ten_toa' => $building->name,
                            'dich_vu_khach_dang_ky' => $data['params']['@dichvu'],
                            'thoi_gian' => $data['params']['@timeorder'],
                            'noi_dung_app' => $data['params']['@mota'],
                            'sdt_bql' => $building->phone,
                            'email_bql' => $building->email,
                            'thong_tin_thanh_toan' => $html ? implode(',', $html) : null,
                        ]);
                        $type = $building->template_mail . '_booking';
                    }
    
                    // thông báo xác nhận giao dịch
    
                    if ($data['type'] == self::NOTIFY_TRANSACTION_PAYMENT) {
                        $template = json_encode([
                            'ten_khach_hang' => $data['params']['@ten_khach_hang'],
                            'ten_toa' => $building->name,
                            'ten_can_ho' => $data['params']['@ten_can_ho'],
                            'tienchuyenkhoan' => number_format($data['params']['@tienchuyenkhoan']),
                            'sdt_bql' => $building->phone,
                            'email_bql' => $building->email
                        ]);
                        $type = $building->template_mail . '_notify_transaction_payment';
                    }
                    if ($template) { //kiểm tra template có tồn tại
                        self::sendMailAction($template, $data, $log, $type, $campains);
                    }
                } else {
                    $template = $mailTemplateRepository->getDefaultTemplate($data['building_id']);
                    if ($template) {
                        self::sendMailAction($template, $data, $log, null, $campains);
                    }
                }
            }
            $time_end = microtime(true);
            $time = $time_end - $time_start;
        } while ($data != null || $time < 25);
      
    }

    // Tạo hàng đợi campain cho mail 
    public static function setItemForQueue($data = [])
    {
        // return self::queueSet(env('REDIS_QUEUE_PREFIX').'REDIS_SEND_MAIL_QUEUE',json_encode($data));

        return self::queueSet(env('REDIS_QUEUE_PREFIX') . 'REDIS_SEND_MAIL_Campain_' . $data['campain_id'], json_encode($data));
    }
    public static function getItemForQueue()
    {
        return json_decode( self::queuePop([env('REDIS_QUEUE_PREFIX').'REDIS_SEND_MAIL_QUEUE']), true);
    }
    // Lấy/pop email từ hàng đợi campain
    public static function getItemForQueueV2($id)
    {
        $rs = json_decode( self::queuePop([env('REDIS_QUEUE_PREFIX').'REDIS_SEND_MAIL_Campain_'. $id]), true);
        if($rs){
            self::setKey(env('REDIS_QUEUE_PREFIX') . 'REDIS_SEND_MAIL_Campain_running', json_encode(['id'=>$id, 'started_at'=>strtotime(now())]));
        }
        return $rs;
    }
    // Tạo hàng đợi cho campain
    public static function setQueueMailCampain($id)
    {   
    
        return self::queueSet(env('REDIS_QUEUE_PREFIX') . 'REDIS_MAIL_QUEUE', $id);
    }
    // pop campain trong hàng đợi 
    public static function popQueueMailCampain()
    {   
        return self::queuePop(env('REDIS_QUEUE_PREFIX') . 'REDIS_MAIL_QUEUE');
    }
    // Lây campain id trong hàng đợi 
    public static function getQueueMailCampain()
    {   
        return json_decode( self::queuePop([env('REDIS_QUEUE_PREFIX').'REDIS_MAIL_QUEUE']), true);
    }    
    

    public static function queueSet($key, $data)
    {
        return Redis::command('rpush', [$key, [$data]]);
    }

    public static function queuePop($key = [])
    {
        return Redis::command('lpop', $key);
    }

    public static function setKey($key, $data)
    {
        return Redis::command('set', [$key, $data]);
    }

    public static function getKey($key)
    {
        return Redis::command('get', [$key]);
    }

    public static function delKey($key)
    {
        return Redis::command('del', [$key]);
    }

    public static function queueRange($key, $start, $end)
    {
        return Redis::command('lrange', [$key, $start, $end]);
    }

    public static function setLogSuccess($log, $content, $cc, $subject)
    {
        $log['success'] = true;
        $log['message'] = 'Gửi thành công';
        $log['building_id'] = (int)$log['data']['building_id'];
        $log['email'] = $cc;
        $log['content'] = $content;
        $log['created_date'] = Carbon::now()->toDateTimeString();
        return $log;
    }

    public static function setLogFail($log, $message)
    {
        $log['success'] = false;
        $log['message'] = $message;
        $log['building_id'] = (int)$log['data']['building_id'];
        $log['email'] = $log['data']['cc'];
        $log['content'] = json_encode($log['data']['params']);
        $log['created_date'] = Carbon::now()->toDateTimeString();
        return $log;
    }

    public static function sendMailAction($template, $data, $log, $type = null, $campains)
    {
        if (isset($type)) {
            $client = new \GuzzleHttp\Client();
            $headers = [
                'ClientSecret' => env('ClientSecret_bdc'),
                'ClientId' => env('ClientId_bdc'),
            ];

            $array_send_mail = [
                'code' => $type,
                'email' => $data['cc'],
                'message' => $template,
                'building_id' => $campains->bdc_building_id,
                'attachFile' => isset($data['params']['@attachFile']) ? $data['params']['@attachFile'] : null
            ];
            try {
                $requestClient = $client->request('POST', 'https://authv2.dxmb.vn/api/v2/notification/sendMail', [
                    'headers' => $headers,
                    'json' => $array_send_mail,
                ]);
                $result_resource = json_decode((string) $requestClient->getBody(), true);
                CampainDetail::create([
                    'campain_id' => $data['campain_id'],
                    'building_id' => $campains->bdc_building_id,
                    'type_campain' =>$campains->type,
                    'view' => 0,
                    'type' => 'email',
                    'contact' => $data['cc'],
                    'status' => $result_resource['success'],
                    'reason' => $result_resource['message'],
                    'content' => $array_send_mail,
                ]);
                $rs_campain =  Campain::find($campains->id);
                if($rs_campain){
                    $rs_campain->sended_email = $rs_campain->sended_email+1;
                    $rs_campain->update(['sended_email'=>$rs_campain->sended_email]); 

                    $total = json_decode($rs_campain->total);
                    if ($rs_campain->sended_email >= $total->email) {
                        Campain::updateStatus($rs_campain->id, 'email');
                    }
                    
                }
            } catch (\Exception $e) {
                SendTelegram::SupersendTelegramMessage('PRO exp Send Mail: '.$e->getMessage());
                CampainDetail::create([
                    'campain_id' => $data['campain_id'],
                    'type' => 'email',
                    'building_id' => $campains->bdc_building_id,
                    'type_campain' =>$campains->type,
                    'view' => 0,
                    'contact' => $data['cc'],
                    'status' => false,
                    "reason" => $e->getMessage(),
                    'content' => $array_send_mail
                ]);
            }
        }
    }
    public static function sendMail_v2($settingSendMail, $mailTemplateRepository)
    {
        $time_start = microtime(true);
        do {
            $allKey = Redis::keys('*REDIS_SEND_MAIL_Campain_*');
            $data =  json_decode( self::queuePop([@$allKey[0]]), true);
            if ($data == null) {
                break;
            }
            $array = explode('_', $allKey[0]);
            $campain_id = $array[count($array)-1];
            $log['data'] = $data;
            if ($data && count($data) > 0 && isset($data['cc']) && isset($data['building_id'])) { //kiểm tra có nội dung trong data lấy ra hay không
                $building = Building::get_detail_building_by_building_id($data['building_id']);
    
                $building_payment_info = PaymentInfo::get_detail_payment_info_by_building_id($data['building_id']);
    
                $html = null;
                if ($building_payment_info) {
                    foreach ($building_payment_info as $key => $value) {
                        $html[] = '<div><p><strong>Thông tin thanh toán ' . ($key + 1) . ':</strong></p>' .
                            '<p>Số tài khoản: ' . $value->bank_account . ' </p>' .
                            '<p>Ngân hàng: ' . $value->bank_name . ' </p>' .
                            '<p>Chủ tài khoản: ' . $value->holder_name . ' </p>' .
                            '<p>Chi nhánh: ' . $value->branch . ' </p></div>';
                    }
                }
    
                if (isset($data['type']) && isset($data['status']) && isset($building->template_mail)) { // kiểm tra xem nó có type và status không để lấy template
                    $template = $settingSendMail->getTemplate($data['building_id'], $data['type'], $data['status']);
                    if ($data['type'] == self::NEW_USER) {
                        $template = json_encode([
                            'ten_khach_hang' => $data['params']['@ten'],
                            'tai_khoan' => $data['cc'],
                            'ten_toa' => $building->name,
                            'mat_khau' => $data['params']['@pass'],
                            'sdt_bql' => $building->phone,
                            'email_bql' => $building->email,
                            'thong_tin_thanh_toan' => $html ? implode(',', $html) : null,
                        ]);
                        $type = $building->template_mail . '_create_account_first';
                    }
                    //bill
                    if ($data['type'] == self::BILL) {
                        $template = json_encode([
                            'ten_khach_hang' => $data['params']['@tenkhachhang'],
                            'ten_toa' => $building->name,
                            'ten_can_ho' => $data['params']['@canho'],
                            'ky_hoa_don' => $data['params']['@cyclename'],
                            'tong_tien' => $data['params']['@tongtien'],
                            'noi_dung' => $data['params']['@noidung'],
                            'du_no_cuoi_ky' => $data['params']['@dunocuoiky'],
                            'link_pdf' => $data['params']['@linkpdf'],
                            'thoi_han_thanh_toan' => $data['params']['@ngay'],
                            'ma_bang_ke' => $data['params']['@billcode'],
                            'sdt_bql' => $building->phone,
                            'email_bql' => $building->email,
                            'thong_tin_thanh_toan' => $html ? implode(',', $html) : null,
                        ]);
                        $type = $building->template_mail . '_bill';
                    }
                    if ($data['type'] == self::NHAC_NO) {
                        $template = json_encode([
                            'ten_khach_hang' => $data['params']['@tenkhachhang'],
                            'ten_toa' => $building->name,
                            'ten_can_ho' => $data['params']['@canho'],
                            'ky_hoa_don' => $data['params']['@kyhoadon'],
                            'noi_dung' =>  isset($data['params']['@message']) ? base64_encode($data['params']['@message']) : null,
                            'chi_tiet' =>  $data['params']['@noidung'],
                            'du_no_cuoi_ky' => $data['params']['@dunocuoiky'],
                            'sdt_bql' => $building->phone,
                            'email_bql' => $building->email,
                            'thong_tin_thanh_toan' => $html ? implode(',', $html) : null,
                        ]);
                        $type = $building->template_mail . '_bill_recall';
                    }
    
                    if ($data['type'] == self::RESIDENT) {
                        $template = json_encode([
                            'ten_khach_hang' => $data['params']['@ten'],
                            'ten_toa' => $building->name,
                            'ten_can_ho' => $data['params']['@canho'] ?? '',
                            'sdt_bql' => $building->phone,
                            'email_bql' => $building->email,
                            'thong_tin_thanh_toan' => $html ? implode(',', $html) : null,
                        ]);
                        $type = $building->template_mail . '_create_apartment';
                    }
    
                    if ($data['type'] == self::NEW_PROFILE) {
                        // $template =[
                        //     'content' => '<p>Chào @ten,</p><p>Bạn vừa được thêm vào tòa nhà @ten tòa nhà của hệ thống BuildingCare.</p><p>Thanks,</p>',
                        //     'subject' => 'BuildingCare Thông báo'
                        // ];
                        // $type='bdc_create_account_first';
                    }
    
                    if ($data['type'] == self::POST_NEWS) {
                        $template = json_encode([
                            'tieu_de' => $data['subject'],
                            'ten_khach_hang' => $data['params']['@ten'],
                            'tieu_de' => $data['subject'],
                            'noi_dung' => isset($data['content']) ?  base64_encode($data['content']) : null,
                            'sdt_bql' => $building->phone,
                            'ten_toa' => $building->name,
                            'email_bql' => $building->email,
                            'thong_tin_thanh_toan' => $html ? implode(',', $html) : null,
                        ]);
                        $type = $building->template_mail . '_notification';
                    }
                    //Code OTP
                    if ($data['type'] == self::VERIFY_CODE) {
                        $template = json_encode([
                                    'ten_khach_hang' => $data['params']['@ten'],
                                    'otp' => $data['params']['@otp'],
                                    'sdt_bql' => $building->phone,
                                    'ten_toa' => $building->name,
                                    'email_bql' => $building->email,
                                    'thong_tin_thanh_toan' =>$html ? implode(',',$html) : null,
                        ]);
                        $type = $building->template_mail . '_send_otp_change_password';
                    }
                    //quen mat khau
                    if ($data['type'] == self::FORGOT) {
                        $template = json_encode([
                                    'ten_khach_hang' => $data['params']['@ten'],
                                    'mat_khau' => $data['params']['@pass'],
                                    'ten_toa' => $building->name,
                                    'sdt_bql' => $building->phone,
                                    'email_bql' => $building->email,
                                    'thong_tin_thanh_toan' =>$html ? implode(',',$html) : null,
                        ]);
                        $type = $building->template_mail . '_change_password';
                    }
    
                    // đăng ký dịch vụ đối tác
                    // đăng ký dịch vụ đối tác
    
                    if ($data['type'] == self::SERVICE_PARTNER) {
                        $template = json_encode([
                            'ten_khach_hang' => $data['params']['@khach'],
                            'so_dien_thoai' => $data['params']['@phone'],
                            'ten_toa' => $building->name,
                            'dich_vu_khach_dang_ky' => $data['params']['@dichvu'],
                            'thoi_gian' => $data['params']['@timeorder'],
                            'noi_dung_app' => $data['params']['@mota'],
                            'sdt_bql' => $building->phone,
                            'email_bql' => $building->email,
                            'thong_tin_thanh_toan' => $html ? implode(',', $html) : null,
                        ]);
                        $type = $building->template_mail . '_booking';
                    }
    
                    // thông báo xác nhận giao dịch
    
                    if ($data['type'] == self::NOTIFY_TRANSACTION_PAYMENT) {
                        $template = json_encode([
                            'ten_khach_hang' => $data['params']['@ten_khach_hang'],
                            'ten_toa' => $building->name,
                            'ten_can_ho' => $data['params']['@ten_can_ho'],
                            'tienchuyenkhoan' => number_format($data['params']['@tienchuyenkhoan']),
                            'sdt_bql' => $building->phone,
                            'email_bql' => $building->email
                        ]);
                        $type = $building->template_mail . '_notify_transaction_payment';
                    }
                    if ($template) { //kiểm tra template có tồn tại
                        self::sendMailAction_v2($template, $data, $log, $type,$campain_id);
                    }
                } else {
                    $template = $mailTemplateRepository->getDefaultTemplate($data['building_id']);
                    if ($template) {
                        self::sendMailAction_v2($template, $data, $log,null,$campain_id);
                    }
                }
            }
            $time_end = microtime(true);
            $time = $time_end - $time_start;
        } while ($data != null || $time < 25);
      
    }
    public static function sendMailAction_v2($template, $data, $log, $type = null,$campain_id)
    {
        if (isset($type)) {
            $client = new \GuzzleHttp\Client();
            $headers = [
                'ClientSecret' => env('ClientSecret_bdc'),
                'ClientId' => env('ClientId_bdc'),
            ];

            $array_send_mail = [
                'code' => $type,
                'email' => $data['cc'],
                'message' => $template,
                'building_id' => $data['building_id'],
                'attachFile' => isset($data['params']['@attachFile']) ? $data['params']['@attachFile'] : null
            ];
            $rs_campain =  Campain::find($campain_id);
            try {
                $requestClient = $client->request('POST', 'https://authv2.dxmb.vn/api/v2/notification/sendMail', [
                    'headers' => $headers,
                    'json' => $array_send_mail,
                ]);
                $result_resource = json_decode((string) $requestClient->getBody(), true);
                SendTelegram::SupersendTelegramMessage('check_send_mail_v2_result_error_end_:'.json_encode($result_resource));
                CampainDetail::create([
                    'campain_id' => $campain_id,
                    'building_id' => $data['building_id'],
                    'type_campain' =>$rs_campain->type,
                    'view' => 0,
                    'type' => 'email',
                    'contact' => $data['cc'],
                    'status' => @$result_resource['success'],
                    'reason' => $result_resource['message'],
                    'content' => $array_send_mail,
                ]);
                if($rs_campain){
                    $rs_campain->sended_email = $rs_campain->sended_email+1;
                    $rs_campain->update(['sended_email'=>$rs_campain->sended_email]);
                    $total = json_decode($rs_campain->total);
                    if ($rs_campain->sended_email >= $total->email) {
                        Campain::updateStatus($rs_campain->id, 'email');
                    }
                }
            } catch (\Exception $e) {
                SendTelegram::SupersendTelegramMessage('PRO exp Send Mail V2: '.json_encode($e->getMessage().$e->getLine()).'id campain:'.$campain_id.'building_id:'.$data['building_id']);
                 CampainDetail::create([
                    'campain_id' => $campain_id,
                    'type' => 'email',
                    'building_id' => $data['building_id'],
                    'type_campain' =>$rs_campain->type,
                    'view' => 0,
                    'contact' => $data['cc'],
                    'status' => false,
                    "reason" => $e->getMessage(),
                    'content' => $array_send_mail
                ]);
                Log::info('check_send_mail_v2','error_end_'.json_encode($e->getMessage()));
            }
        }
    }

    public static function setItemForQueue2($data = [])
    {
        return self::queueSet(env('REDIS_QUEUE_PREFIX') . 'REDIS_SEND_MAIL_QUEUE_2', json_encode($data));
    }

    public static function getItemForQueue2()
    {
        return json_decode(self::queuePop([env('REDIS_QUEUE_PREFIX') . 'REDIS_SEND_MAIL_QUEUE_2']), true);
    }
}
