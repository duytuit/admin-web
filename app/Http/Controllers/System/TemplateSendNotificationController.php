<?php

namespace App\Http\Controllers\System;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\ServiceSendMailV2;
use App\Http\Controllers\BuildingController;
use App\Http\Requests\SendEmailRequest;
use App\Models\Campain;
use App\Models\SentStatus;
use Validator;

class TemplateSendNotificationController extends BuildingController
{
    protected $repository;

    public function __construct(Request $request)
    {
        //$this->middleware('route_permision');
        parent::__construct($request);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $data['meta_title'] = 'test mẫu gửi email';

        return view('template-send-notification.create', $data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function send(SendEmailRequest $request)
    {
        $total = ['email'=>1, 'app'=> 0, 'sms'=> 0];
        $campain = Campain::updateOrCreateCampain("Gửi mail cho: ".$request->nguoi_nhan, config('typeCampain.RESIDENT'), null, $total, $this->building_active_id, 0, 0);

         
        try {
            ServiceSendMailV2::setItemForQueue([
                'params' => [
                  '@ten' => $request->ten_khach_hang,
                  '@toanha' => $request->toa_nha,
                  '@pass'  => $request->password,
                  '@tenkhachhang' => $request->ten_khach_hang,
                  '@cyclename' => $request->ky_hoa_don,
                  '@tongtien' => $request->tong_tien,
                  '@dunocuoiky' => $request->du_no_cuoi_ky,
                  '@linkpdf' => $request->duong_dan_pdf,
                  '@ngay' => $request->ngay_thanh_toan,
                  '@billcode' => $request->ma_hoa_don,
                  '@kyhoadon' => $request->ky_hoa_don,
                  '@message' => $request->noi_dung_nhac_no,
                  '@otp' => $request->otp,
                  '@khach' => $request->ten_khach_hang,
                  '@phone' => $request->phone,
                  '@tentoanha' => $request->toa_nha,
                  '@dichvu' => $request->dich_vu_doi_tac,
                  '@timeorder' => $request->time_order,
                  '@mota' => $request->mo_ta,
                ],
                'cc' => $request->nguoi_nhan,
                'building_id' => $this->building_active_id,
                'type' => $request->name_template_send_email,
                'status' => 'prepare',
                'subject' => '[BuildingCare] thông báo mới đến cư dân.',
                'content'=> $request->content,
                'campain_id' => $campain->id
            ]);
            return redirect()->action('System\TemplateSendNotificationController@create')->with(['success' => 'Gửi Email Thành Công!']);
        } catch (\Exception $e)
        {
            return $e->getMessage();
        }
    }
}
