<?php

namespace App\Services;

use Mail;
use Illuminate\Support\Facades\Redis;
use App\Models\LogSendMail\LogSendMail;

use App\Repositories\PublicUsers\PublicUsersProfileRespository;

class ServiceSendMail
{
    const FORGOT =3;
    const NEW_USER =100;
    const NEW_PROFILE =99;
    const RESIDENT = 4;
    const BILL = 69;
    const POST_NEWS = 20;
    const VERIFY_CODE = 33;
    const SERVICE_PARTNER = 25;
    public static function sendMail($settingSendMail, $mailTemplateRepository) {
        $data = self::getItemForQueue();
        $log['data'] = $data;
        print_r( $data );
        $base_url=((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == "on") ? "https" : "http"). "://". @$_SERVER['HTTP_HOST'];
        if ($data && count($data) > 0 && isset($data['cc']) && isset($data['building_id'])) { //kiểm tra có nội dung trong data lấy ra hay không

            if (isset($data['type']) && isset($data['status'])) { // kiểm tra xem nó có type và status không để lấy template
                $template = $settingSendMail->getTemplate($data['building_id'], $data['type'], $data['status']);
                if ($data['type'] == self::NEW_USER ) {
                    $template =[
                        'content' => '<p style="color: black;">Xin kính chào Quý cư dân/ khách hàng @ten!</p>
                                      <p style="color: black;">Ban quản lý tòa nhà <span style="font-weight: bold">@toanha</span> xin gửi lời chúc sức khỏe đến Quý cư dân/ khách hàng.</p>
                                      <p style="color: black;">Để phục vụ tốt nhất cho công tác quản lý vận hành tòa nhà, kính mời Quý cư dân/ khách hàng</p>
                                      <p style="color: black;">tải <span style="font-weight: bold">App Building Care</span> và đăng nhập mật khẩu :<span style="font-weight: bold">@pass</span></p>
                                      <p style="color: black;">Trân trọng cảm ơn !</p>',
                        'subject' => 'Chào mừng đến BuildingCare'
                    ];
                }

                if($data['type']== self::BILL)  {
                    $template = [
                        'content' => '<head>
                        <meta charset="utf-8">
                        <meta http-equiv="X-UA-Compatible" content="IE=edge">
                        <!-- Tell the browser to be responsive to screen width -->
                        <meta name="viewport" content="width=device-width, initial-scale=1">
                        <meta name="description" content="">
                        <meta name="author" content="">
                        <!-- Favicon icon -->
                        <link rel="icon" type="image/png" sizes="16x16" href="images/favi.png">
                        <title>Hoa don</title>
                        
                        <style type="text/css">
                            body{
                            background: #f5f5f5;
                            font-size: 15px;
                            color: #2a3036;
                            font-family: roboto;
                            }
                            .font20{
                            font-size: 20px;
                            }
                            .font22{
                            font-size: 22px;
                            }
                            .font30{
                            font-size: 30px;
                            }
                            .color-blue{
                            color: #2185c7;
                            }
                            .color-red{
                            color: #ed1c24;
                            }
                            .bg-blue{
                            background: #e7f5ff;
                            padding: 20px;
                            border-radius: 5px;
                            }
                            .bg-red{
                            background: #ffe7e7;
                            padding: 20px;
                            border-radius: 5px;
                            }
                            .font-weight-bold{
                            font-weight: bold;
                            }
                            .container-mail{
                            width: 50% !important;
                            margin: 0 auto;
                            display: grid;
                            }
                            .mt-2{
                            margin-top: .5rem !important;
                            }
                            .mt-3{
                            margin-top: 1rem !important;
                            }
                            .text-center{
                            text-align: center !important;
                            }
                            .justify-content-center {
                                -webkit-box-pack: center !important;
                                -ms-flex-pack: center !important;
                                justify-content: center !important;
                            }
                            .d-flex {
                                display: -webkit-box !important;
                                display: -ms-flexbox !important;
                                display: flex !important;
                            }
                            .logo{
                            margin: 0 auto;
                            padding: 15px 0px;
                            }
                            .content{
                            padding: 55px 50px; 
                            background: #fff;
                            box-shadow: 0px 0px 15px #cccccc45;
                            width: 100%;
                            overflow: hidden;
                            border-radius: 9px;
                            }
                            .social i{
                            height: 25px;
                                width: 25px;
                                border-radius: 50%;
                                color: #fff;
                                padding-top: 6px;
                                padding-left: 0px;
                                font-size: 14px;
                                margin-right: 10px;
                                text-align: center;
                            }
                            .social i:hover{
                            opacity: 0.5;
                            }
                            .fa-facebook{
                            background: #527abc;
                            }
                            .fa-link{
                            background: #52bb91;
                            }
                            .fa-envelope{
                            background:#51b9d4;
                            }
                            footer{
                            margin: 0 auto;
                                padding: 30px 0px;
                            }
                            .info-company p{
                            margin-bottom: 6px !important;
                            }
                            .contact{
                            display: flex;
                            }

                            @media only screen and (max-width: 768px){
                            .container-mail{
                                width: 70% !important;
                            }
                            }
                            @media only screen and (max-width: 576px){
                            
                            .container-mail{
                                width: 100% !important;
                            }
                            .content{
                                padding: 55px 20px;
                            }
                            .d-flex{
                                display: none;
                            }
                            .col1, .col2{
                                width: 100% !important;
                            }
                            .ct{
                                display: block !important;
                            }
                            }

                            /*CSS CONTENT*/
                            .dear{
                            font-size: 20px;
                            }
                            .img-themvaocanho{
                            max-width: 100%;
                            margin: 0 auto;
                                display: block;
                            }
                            .col-tk{
                            height: 170px;
                            }
                            .col1, .col2{
                            width: 49%;
                            border: 5px solid #fff;
                            }
                            .ct{
                            width: 100%;
                            display: inline-flex;
                            }

                        </style>

                        <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@700&display=swap" rel="stylesheet"> 
                        <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet"> 
                    </head>

                    <body>
                        <div class="container-mail">
                            <head>
                            <img class="logo" src="@url/adminLTE/img/logo-bdc.png">
                            </head>

                            <!-- START CONTENT -->
                            <div class="content">
                            <p class="dear">Kính gửi Quý cư dân <b>@tenkhachhang,</b></p>
                            <p>Ban quản lý tòa nhà trân trọng gửi Quý cư dân bảng kê dịch vụ kỳ tháng <b>@cyclename</b> của căn hộ <b>@canho .</b></p>
                            <div class="bg-blue mb-2">
                                <p class="font20 text-center">Tổng phí: <span class="font30 font-weight-bold">@tongtien</span></p>
                                <p class="text-center"><b>Thời hạn thanh toán:</b> <span class="font-weight-bold color-blue">@ngay</span></p>
                            </div>
                            <p><b>Chi tiết xem tại đây:</b> <a href="@linkpdf">@billcode</a></p>
                            <p>Quý cư dân vui lòng thanh toán đúng hạn để đảm bảo cung cấp dịch vụ liên tục.</p>
                            <div class="end mt-3">
                                <p>Hỗ trợ liên hệ: [Email] buildingcare@crm.dxmb.vn hoặc [Hotline] 0948.36.9191 </p>
                                <p class="font-weight-bold">Trân trọng thông báo,</p>
                            </div>
                            </div>
                            <!-- END CONTENT -->

                            <footer>
                            <div class="social d-flex justify-content-center text-center mb-2">
                                <a href=""><i class="fa fa-facebook" aria-hidden="true"></i></a>
                                <a href=""><i class="fa fa-link" aria-hidden="true"></i></a>
                                <a href=""><i class="fa fa-envelope" aria-hidden="true"></i></a>
                            </div>
                            <div class="info-company text-center">  
                                <p class="font-weight-bold">PHẦN MỀM TIỆN ÍCH CHUNG CƯ BUILDING CARE</p>
                                <p>Địa chỉ: Tầng 18, Center Building, 85 Vũ Trọng Phụng, Thanh Xuân, HN</p>
                                <div class="contact d-flex justify-content-center">
                                <p>Hotline: 0948.36.9191 &emsp;</p>
                                <p>Website: https://buildingcare.biz &emsp;</p>
                                <p>Mail: bdc@crm.dxmb.vn</p>
                                </div>
                            </div>
                            </footer>
                            
                        </div>
                    </body>',
                        'subject' => 'BuildingCare Thông báo'
                    ];
                }

                if ($data['type'] == self::RESIDENT ) {
                    $template =[
                        'content' => '<p>Chào @ten,</p><p>Căn hộ của bạn vừa được thêm vào hệ thống BuildingCare.</p><p>Thanks,</p>',
                        'subject' => 'BuildingCare Thông báo'
                    ];
                }

                if ($data['type'] == self::NEW_PROFILE ) {
                    $template =[
                        'content' => '<p>Chào @ten,</p><p>Bạn vừa được thêm vào tòa nhà @ten tòa nhà của hệ thống BuildingCare.</p><p>Thanks,</p>',
                        'subject' => 'BuildingCare Thông báo'
                    ];
                }

                if ($data['type'] == self::POST_NEWS ) {
                    $template =[
                        'content' => '<head>
                        <meta charset="utf-8">
                        <meta http-equiv="X-UA-Compatible" content="IE=edge">
                        <!-- Tell the browser to be responsive to screen width -->
                        <meta name="viewport" content="width=device-width, initial-scale=1">
                        <meta name="description" content="">
                        <meta name="author" content="">
                        <!-- Favicon icon -->
                        <link rel="icon" type="image/png" sizes="16x16" href="images/favi.png">
                        <title>Thong bao</title>
                        <style type="text/css">
                            body{
                            background: #f5f5f5;
                            font-size: 15px;
                            color: #2a3036;
                            font-family: roboto;
                            }
                            .font20{
                            font-size: 20px;
                            }
                            .font22{
                            font-size: 22px;
                            }
                            .font30{
                            font-size: 30px;
                            }
                            .color-blue{
                            color: #2185c7;
                            }
                            .color-red{
                            color: #ed1c24;
                            }
                            .bg-blue{
                            background: #e7f5ff;
                            padding: 20px;
                            border-radius: 5px;
                            }
                            .bg-red{
                            background: #ffe7e7;
                            padding: 20px;
                            border-radius: 5px;
                            }
                            .font-weight-bold{
                            font-weight: bold;
                            }
                            .container-mail{
                            width: 50% !important;
                            margin: 0 auto;
                            display: grid;
                            }
                            .mt-2{
                            margin-top: .5rem !important;
                            }
                            .mt-3{
                            margin-top: 1rem !important;
                            }
                            .text-center{
                            text-align: center !important;
                            }
                            .justify-content-center {
                                -webkit-box-pack: center !important;
                                -ms-flex-pack: center !important;
                                justify-content: center !important;
                            }
                            .d-flex {
                                display: -webkit-box !important;
                                display: -ms-flexbox !important;
                                display: flex !important;
                            }
                            .logo{
                            margin: 0 auto;
                            padding: 15px 0px;
                            }
                            .content{
                            padding: 55px 50px; 
                            background: #fff;
                            box-shadow: 0px 0px 15px #cccccc45;
                            width: 100%;
                            overflow: hidden;
                            border-radius: 9px;
                            }
                            .social i{
                            height: 25px;
                                width: 25px;
                                border-radius: 50%;
                                color: #fff;
                                padding-top: 6px;
                                padding-left: 0px;
                                font-size: 14px;
                                margin-right: 10px;
                                text-align: center;
                            }
                            .social i:hover{
                            opacity: 0.5;
                            }
                            .fa-facebook{
                            background: #527abc;
                            }
                            .fa-link{
                            background: #52bb91;
                            }
                            .fa-envelope{
                            background:#51b9d4;
                            }
                            footer{
                            margin: 0 auto;
                                padding: 30px 0px;
                            }
                            .info-company p{
                            margin-bottom: 6px !important;
                            }
                            .contact{
                            display: flex;
                            }

                            @media only screen and (max-width: 768px){
                            .container-mail{
                                width: 70% !important;
                            }
                            }
                            @media only screen and (max-width: 576px){
                            
                            .container-mail{
                                width: 100% !important;
                            }
                            .content{
                                padding: 55px 20px;
                            }
                            .d-flex{
                                display: none;
                            }
                            .col1, .col2{
                                width: 100% !important;
                            }
                            .ct{
                                display: block !important;
                            }
                            }

                            /*CSS CONTENT*/
                            .dear{
                            font-size: 20px;
                            }
                            .img-themvaocanho{
                            max-width: 100%;
                            margin: 0 auto;
                                display: block;
                            }
                            .col-tk{
                            height: 170px;
                            }
                            .col1, .col2{
                            width: 49%;
                            border: 5px solid #fff;
                            }
                            .ct{
                            width: 100%;
                            display: inline-flex;
                            }

                        </style>

                        <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@700&display=swap" rel="stylesheet"> 
                        <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet"> 
                    </head>

                    <body>
                        <div class="container-mail">
                            <head>
                                <img class="logo" src="@url/adminLTE/img/logo-bdc.png">
                            </head>

                            <!-- START CONTENT -->
                            <div class="content">
                            <p class="dear">Kính gửi Quý cư dân <b>@ten,</b></p>
                            <p>'.$data['content'].'</p>
                            </div>
                            <!-- END CONTENT -->

                            <footer>
                            <div class="social d-flex justify-content-center text-center mb-2">
                                <a href=""><i class="fa fa-facebook" aria-hidden="true"></i></a>
                                <a href=""><i class="fa fa-link" aria-hidden="true"></i></a>
                                <a href=""><i class="fa fa-envelope" aria-hidden="true"></i></a>
                            </div>
                            <div class="info-company text-center">  
                                <p class="font-weight-bold">PHẦN MỀM TIỆN ÍCH CHUNG CƯ BUILDING CARE</p>
                                <p>Địa chỉ: Tầng 18, Center Building, 85 Vũ Trọng Phụng, Thanh Xuân, HN</p>
                                <p>Hotline: 0948.36.9191 &emsp; Website: https://buildingcare.biz &emsp; Mail: bdc@crm.dxmb.vn</p>
                            </div>
                            </footer>
                            
                        </div>
                    </body>',
                        'subject' => $data['subject']
                    ];
                }

                if ($template) { //kiểm tra template có tồn tại
                    self::sendMailAction($template, $data, $log);
                    return $data;
                }
                $log = self::setLogFail($log, 'Không có template theo yêu cầu đẩy vào');
                LogSendMail::create($log);
                //không có template theo yêu cầu đẩy vào
                return [];
            } else {
                $template = $mailTemplateRepository->getDefaultTemplate($data['building_id']);
                if ($template) {
                    self::sendMailAction($template, $data, $log);
                    return $data;
                }
                $log = self::setLogFail($log, 'Không có template mặc định');
                LogSendMail::create($log);
                //khong co template mặc định
                return [];
            }
        }
        //quen mat khau
        if (!isset($data['building_id']) && $data['type'] == self::FORGOT ) {
            $template =[
                'content' => '<p style="color: black;">Xin kính chào Quý cư dân/ khách hàng <span style="font-weight: bold">@ten</span> !</p>
                             <p style="color: black;">Mật khẩu đăng nhập phần mềm BuildingCare mới của Quý cư dân/ khách hàng là <span style="font-weight: bold">@pass</span></p>
                             <p style="color: black;">Trân trọng cảm ơn !</p>',
                'subject' => 'Mật khẩu BuildingCare'
            ];

            self::sendMailAction($template, $data, $log);
        }
        // đăng ký dịch vụ đối tác
        if (!isset($data['building_id']) && $data['type'] == self::SERVICE_PARTNER ) {
            $template =[
                'content' => '<div class="footer" style="text-align : center;color: black;">
                                <div style="width:90%;margin: 0 auto;">
                                    <div style="text-align: left;width: 100%;margin: 0 auto;">
                                        <div style="margin-bottom: 5px;">
                                            <img src="@url/adminLTE/img/logo-bdc.png" alt="Building Care"/>
                                        </div>
                                        <h1 style="color: black;">Thông tin đặt chỗ</h1>
                                        <p style="color: black;">Xin chào đối tác: @doitac,</p>
                                        <p style="color: black;">BuildingCare xin gửi thông tin khách hàng đăng ký dịch vụ:</p>
                                        <table style="border-collapse: collapse;color: black;width:100%;">
                                            <tr style="border: 1px solid #858585;">
                                                <td style="border: 1px solid #858585;font-weight:bold;">Khách hàng:</td>
                                                <td style="border: 1px solid #858585;width:70%">@khach</td>
                                            </tr>
                                            <tr style="border: 1px solid #858585;">
                                                <td style="border: 1px solid #858585;font-weight:bold;">Số điện thoại:</td>
                                                <td style="border: 1px solid #858585;">@phone</td>
                                            </tr>
                                            <tr style="border: 1px solid #858585;">
                                                <td style="border: 1px solid #858585;font-weight:bold;">Tòa nhà:</td>
                                                <td style="border: 1px solid #858585;">@tentoanha</td>
                                            </tr>
                                            <tr style="border: 1px solid #858585;">
                                                <td style="border: 1px solid #858585;font-weight:bold;">Dịch vụ khách đăng ký:</td>
                                                <td style="border: 1px solid #858585;">@dichvu</td>
                                            </tr>
                                            <tr style="border: 1px solid #858585;">
                                                <td style="border: 1px solid #858585;font-weight:bold;">Thời gian đăng ký:</td>
                                                <td style="border: 1px solid #858585;">@timeorder</td>
                                            </tr>
                                            <tr style="border: 1px solid #858585;">
                                                <td style="border: 1px solid #858585;font-weight:bold;">Nội dung:</td>
                                                <td style="border: 1px solid #858585;">@mota</td>
                                            </tr>
                                        </table>
                                        <p>Trân trọng</p>
                                    </div>
                                    <div class="banner-bottom">
                                        <a href="https://www.facebook.com/BuildingCareVN">
                                            <img src="@url/adminLTE/img/baner-new.png" style="width:100%;"/>
                                        </a>
                                    </div>
                                    <div class="banner-link" style="display: inline-flex;margin: 10px;text-align: center;">
                                        <img src="@url/adminLTE/img/hotline.png" style="height: 23px;"/>
                                        <span style="margin: 3px 15px 2px;font-weight: bold;color: black;">Liên kết: </span>
                                        <div style="display: flex;">
                                            <a href="https://www.facebook.com/BuildingCareVN" class="link-facebook">
                                                <img src="@url/adminLTE/img/facebook.png"/>
                                            </a>
                                            <a href="https://youtu.be/kpWqvZCsczg" style="margin-left:15px;">
                                                <img src="@url/adminLTE/img/youtube.png" style="height:23px;width:23px;"/>
                                            </a>
                                        </div>
                                    </div>
                                    <div class="banner-contact">
                                        <h3 style="color: black;">CÔNG TY CỔ PHẦN DỊCH VỤ VÀ ĐỊA ỐC ĐẤT XANH MIỀN BẮC</h3>
                                        <p style="color: black;">Địa chỉ: Tầng 18, Center Building, 85 Vũ Trọng Phụng, Thanh Xuân, HN</p>
                                        <div style="color: black;">
                                            <div style=" display: inline-block;">Hotline: 0948.36.9191</div>
                                            <div style="margin: 0 30px;display: inline-block;">Website: https://buildingcare.biz</div>
                                            <div style=" display: inline-block;">Mail: buildingcare@crm.dxmb.vn</div>
                                        </div>
                                    </div>
                                </div>
                            </div>',
                'subject' => 'Thông báo đăng ký dịch vụ đến đối tác của BuildingCare'
            ];

            self::sendMailAction($template, $data, $log);
        }
         //Code OTP
        if ($data['type'] == self::VERIFY_CODE ) {
            $template =[
                'content' => '<p>Chào @ten,</p><p>Mã xác thực OTP phần mềm buildingcare mới của bạn là @otp</p><p>Thanks,</p>',
                'subject' => 'Mã OTP BuildingCare'
            ];

            self::sendMailAction($template, $data, $log);
        }



        //hết dữ liệu hoặc dữ liệu thiếu người nhận và tòa nhà

        return [];
    }

    public static function setItemForQueue( $data=[])
    {
        return self::queueSet(env('REDIS_QUEUE_PREFIX').'REDIS_SEND_MAIL_QUEUE',json_encode($data));
    }

    public static function getItemForQueue()
    {
        return json_decode( self::queuePop([env('REDIS_QUEUE_PREFIX').'REDIS_SEND_MAIL_QUEUE']), true);
    }

    public static function queueSet($key, $data)
    {
        return Redis::command('rpush', [$key, [$data]]);
    }

    public static function queuePop($key=[])
    {
        return Redis::command('lpop', $key);
    }

    public static function setLogSuccess($log, $content, $cc, $subject)
    {
        $log['success'] = true;
        $log['message'] = 'Gửi thành công';
        $log['response'] = [
            'content' => $content,
            'cc' => $cc,
            'subject' => $subject,
        ];
        return $log;
    }

    public static function setLogFail($log, $message)
    {
        $log['success'] = false;
        $log['message'] = $message;
        $log['response'] = [];
        return $log;
    }

    public static function sendMailAction($template, $data, $log)
    {
        $content = $template['content'];
        $subject = $template['subject'] ? $template['subject'] : $template['name'];
        $cc = $data['cc'];
        if (isset($data['params'])) {
            foreach ($data['params'] as $key => $value) {
                $content = str_replace($key, $value, $content);
            }
        }
        try {
            Mail::send([], [], function ($message) use ($cc, $content, $subject) {
                $message->to($cc)
                    ->subject($subject)
                    ->setBody($content, 'text/html');
            });
            $log = self::setLogSuccess($log, $content, $cc, $subject);
        } catch (\Exception $e) {
            $log = self::setLogFail($log, $e->getMessage());
            //ServiceSendMail::setItemForQueue($data);
        }
        LogSendMail::create($log);
    }

    public static function setItemForQueue2( $data=[])
    {
        return self::queueSet(env('REDIS_QUEUE_PREFIX').'REDIS_SEND_MAIL_QUEUE_2',json_encode($data));
    }

    public static function getItemForQueue2()
    {
        return json_decode( self::queuePop([env('REDIS_QUEUE_PREFIX').'REDIS_SEND_MAIL_QUEUE_2']), true);
    }

}