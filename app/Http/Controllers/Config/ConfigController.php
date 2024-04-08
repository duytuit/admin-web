<?php

namespace App\Http\Controllers\Config;

use App\Commons\Helper;
use App\Http\Controllers\BuildingController;
use App\Models\Configs\Configs;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Validator;
use Carbon\Carbon;
use App\Http\Requests\Config\ConfigRequest;
use App\Models\Feedback\FeedbackForm;
use App\Models\UserRequest\UserRequest;
use App\Repositories\Config\ConfigRepository;
use App\Services\SendNotifyFCMService;
use App\Services\LogImportService;
use Illuminate\Support\Facades\Auth;

class ConfigController extends BuildingController
{
    const POST_NEW = "NTASK";
    private $model;

    private $auth_id;

    /**
     * Constructor.
     */
    public function __construct(
        Request $request,
        ConfigRepository $model
    )
    {
        //$this->middleware('route_permision');
        $this->model            = $model;
        parent::__construct($request);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // $data               = $this->getAttribute();
        $data['filter']     = $request->all();
        $data['meta_title'] = 'Quản lý cấu hình';
        $data['per_page']   = Cookie::get('per_page', 20);
        $data['configs']    = $this->model->myPaginate($data['filter'], $data['per_page'], $this->building_active_id);
        $data['keyword']    = $request->input('keyword', '');
        // dd($data);
        // $check_config_default = Configs::where(['bdc_building_id' => $this->building_active_id, 'default' => 1])->first();
        // if(!$check_config_default){
        //     $list_config_default = Helper::config_receipt;
           
        //     foreach ($list_config_default as $key => $value) {
        //         $value['bdc_building_id'] = $this->building_active_id;
        //         $value['publish'] = 1;
        //         $value['status'] = 1;
        //         $value['default'] = 1;
        //         $value['value'] = $value['value'].'_'.$this->building_active_id;
        //         Configs::create($value);
        //     }
        // }
        return view('configs.index', $data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(ConfigRequest $request)
    {
        $check = $this->model->CheckDuplicateValue($request->value);
        if($check){
            return redirect( route('admin.configs.create') )->with('error', 'Giá trị trùng lặp!');
        }
        $data = $request->except('_token');
        $data['status'] = 1;
        $data['publish'] = 1;
        $data['bdc_building_id'] = $this->building_active_id;

        $this->model->create($data);

        return redirect( route('admin.configs.index') )->with('success', 'Thêm mới cấu hình thành công!');
    }

    public function update(ConfigRequest $request, $id = 0)
    {
        $check = $this->model->CheckDuplicateValue($request->value, $id);
        if($check){
            return redirect()->back()->with('error', 'Giá trị trùng lặp!');
        }
        $data = $request->except('_token');
        $config = $this->model->update($data, $id);

        return redirect( route('admin.configs.index') )->with('success', 'Cập nhật cấu hình thành công!');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\WorkDiary  $buildingHandbook
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $data['meta_title']        = 'Quản lý cấu hình';
        $data['config']           = $this->model->find($id);
      
        return view('configs.edit', $data);
    }

    public function create()
    {
        // $data = $this->getAttribute();
        $data['meta_title'] = 'Quản lý cấu hình';
        return view('configs.create', $data);
    }
    public function view(Request $request)
    {
        $data_content['meta_title'] = 'Quản lý cấu hình';
        if($request->user_request_id){
           $user_request =  UserRequest::find($request->user_request_id);
           if($user_request){
               $form_register = FeedbackForm::where('type',$user_request->type)->first();
               if($form_register){
                    if($user_request->type  == 4){ // đăng ký chuyển đồ
                        $data = @$user_request->data ? json_decode(@$user_request->data) : null;
                        $products = $data->products;
                        $times =@$data->times;
                        $detail_times = '';
                        if(@$times){
                            foreach ($times as $key => $value) {
                                $detail_times .= $value ? str_replace(' ',' đến ',@$value) : '';
                            }
                        }
                        $form_data = '';
                        foreach ($products as $key => $value) {
                            $form_data .='<tr>
                                                <td style="border-bottom:1px solid black; border-left:1px solid black; border-right:1px solid black; border-top:1px solid black;">
                                                    <p style="text-align:center"><span style="font-size:11pt"><span style="font-family:Calibri,sans-serif"><strong>'.($key+1).'</strong></span></span></p>
                                                </td>
                                                <td style="border-bottom:1px solid black; border-left:none; border-right:1px solid black; border-top:1px solid black;">
                                                    <p style="margin-right:5px; text-align:center"><span style="font-size:11pt"><span style="font-family:Calibri,sans-serif"><strong>'.$detail_times.'</strong></span></span></p>
                                                </td>
                                                <td style="border-bottom:1px solid black; border-left:none; border-right:1px solid black; border-top:1px solid black;">
                                                    <p style="text-align:center"><span style="font-size:11pt"><span style="font-family:Calibri,sans-serif"><strong><span style="font-size:10.0pt"><span style="font-family:&quot;Arial&quot;,sans-serif">'.$value->title.'</span></span></strong></span></span></p>
                                                </td>
                                                <td style="border-bottom:1px solid black; border-left:none; border-right:1px solid black; border-top:1px solid black;">
                                                    <p style="margin-right:5px; text-align:center"><span style="font-size:11pt"><span style="font-family:Calibri,sans-serif"><strong></strong></span></span></p>
                                                </td>
                                                <td style="border-bottom:1px solid black; border-left:none; border-right:1px solid black; border-top:1px solid black;">
                                                    <p style="text-align:center"><span style="font-size:11pt"><span style="font-family:Calibri,sans-serif"><strong>'.number_format($value->amount).'</strong></span></span></p>
                                                </td>
                                                <td style="border-bottom:1px solid black; border-left:none; border-right:1px solid black; border-top:1px solid black;">
                                                    <p style="text-align:center"><span style="font-size:11pt"><span style="font-family:Calibri,sans-serif"><strong>'.$value->desc.'</strong></span></span></p>
                                                </td>
                                          </tr>';
                        }
                        $param = [
                            '@ten_khach_hang' => @$user_request->user_created_by->full_name,
                            '@email' => @$user_request->user_created_by->email_contact,
                            '@can_ho' => @$user_request->apartment->name,
                            '@dien_thoai_lien_he' =>  @$data->phone,
                            '@ngay_van_chuyen' => @$data->date,
                            '@du_lieu' => $form_data
                        ];
                        foreach($param as $key => $_data) {
                            $form_register->content  = str_ireplace($key, $_data, $form_register->content);
                        }
                    }
                    if($user_request->type  == 5){ // đăng ký sửa chữa
                        $data = @$user_request->data ? json_decode(@$user_request->data) : null;
                        $form_data = '';
                        $param = [
                            '@ten_khach_hang'  => @$user_request->user_created_by->full_name,
                            '@sdt_kh'  => @$data->phone,
                            '@can_ho'  => @$user_request->apartment->name,
                            '@ten_nha_thau'  => @$data->construction,
                            '@nguoi_chiu_trach_nhiem'  => '',
                            '@sdt_nha_thau'  => @$user_request->user_created_by->full_name,
                            '@start_time'  => @$data->from,
                            '@end_time'  => @$data->to,
                            '@ngay_bat_dau'  => @$data->from,
                            '@ngay_ket_thuc'  => @$data->to,
                        ];
                        foreach($param as $key => $_data) {
                            $form_register->content  = str_ireplace($key, $_data, $form_register->content);
                        }
                    }
                    if($user_request->type  == 6){ // đăng ký tiện ích
                        $data = json_decode($user_request->data);
                        $times =@$data->time;
                        $detail_times = '';
                        if(@$times){
                            foreach ($times as $key => $value) {
                                if($key != 0){
                                    $detail_times .= $value ? ' | '.str_replace(' ',' đến ',@$value) : '';
                                }else{
                                    $detail_times .= $value ? str_replace(' ',' đến ',@$value) : '';
                                }
                            }
                        }
                        $form_data ='<tr>
                                            <td style="border-bottom:1px solid black; border-left:1px solid black; border-right:1px solid black; border-top:1px solid black;">
                                                <p style="text-align:center"><span style="font-size:11pt"><span style="font-family:Calibri,sans-serif"><strong>1</strong></span></span></p>
                                            </td>
                                            <td style="border-bottom:1px solid black; border-left:none; border-right:1px solid black; border-top:1px solid black;">
                                                <p style="margin-right:5px; text-align:center"><span style="font-size:11pt"><span style="font-family:Calibri,sans-serif"><strong>'.@$user_request->user_created_by->full_name.'</strong></span></span></p>
                                            </td>
                                            <td colspan="2" rowspan="1" style="border-bottom:1px solid black; border-left:none; border-right:1px solid black; border-top:1px solid black;">
                                                <p style="text-align:center"><span style="font-size:11pt"><span style="font-family:Calibri,sans-serif"><strong><span style="font-size:10.0pt"><span style="font-family:&quot;Arial&quot;,sans-serif">'.$detail_times.'</span></span></strong></span></span></p>
                                            </td>
                                            <td style="border-bottom:1px solid black; border-left:none; border-right:1px solid black; border-top:1px solid black;">
                                                <p style="margin-right:5px; text-align:center"><span style="font-size:11pt"><span style="font-family:Calibri,sans-serif"><strong>'.Helper::type_utilities[$data->service_type].'</strong></span></span></p>
                                            </td>
                                            <td style="border-bottom:1px solid black; border-left:none; border-right:1px solid black; border-top:1px solid black;">
                                                <p style="text-align:center"><span style="font-size:11pt"><span style="font-family:Calibri,sans-serif"><strong></strong></span></span></p>
                                            </td>
                                    </tr>';
                        $param = [
                            '@ten_khach_hang'=> @$user_request->user_created_by->full_name,
                            '@can_ho'=>@$user_request->apartment->name,
                            '@sdt'=> @$data->phone,
                            '@ngay_de_nghi'=> @$data->date,
                            '@du_lieu'=> $form_data
                        ];
                        foreach($param as $key => $_data) {
                            $form_register->content  = str_ireplace($key, $_data, $form_register->content);
                        }
                    }
               }
           }
        }
        $data_content['view'] = @$form_register->content;
        return view('configs.view', $data_content);
    }
    public function create_view(Request $request)
    {
        $data['meta_title'] = 'Quản lý cấu hình';
        return view('configs.create_view', $data);
    }

    public function delete(Request $request)
    {
        $id = $request->input('id');
        $rs = $this->model->find($id);
        if($rs){
            $rs->delete();
        }
        $request->session()->flash('success', 'Xóa cấu hình thành công');
    }

    public function billPdf()
    {
        $data['meta_title'] = 'Quản lý cấu hình';
        $configBangkePdf = $this->model->findByKeyFirst($this->building_active_id, ConfigRepository::BANGKE_PDF);
        $data['configBangkePdf'] = $configBangkePdf;
        return view('configs.billPdf', $data);
    }
    public function receipt()
    {
        $data['meta_title'] = 'Quản lý cấu hình';
        $configReceipt = $this->model->findByKeyFirst($this->building_active_id, ConfigRepository::RECEIPT_VIEW);
        $data['configReceipt'] = $configReceipt;
        return view('configs.receipt', $data);
    }

    public function billPdfPost(Request $request)
    {
        $data=null;
        $configBangkePdf = $this->model->find($request->id);
        if(@$request->type == ConfigRepository::RECEIPT_VIEW){
          
            $data['title'] = "Mẫu phiếu thu";
            $data['value'] = $request->receipt_style;
            $data['key'] = ConfigRepository::RECEIPT_VIEW;
            $data['bdc_building_id'] = $this->building_active_id;
            $data['created_by'] = Auth::user()->id;
            $data['status'] = ConfigRepository::ACTIVE;
            $data['publish'] = ConfigRepository::INACTIVE;
        }

        if(@$request->type == ConfigRepository::BANGKE_PDF){
            $data['title'] = "Mẫu PDF bảng kê";
            $data['value'] = $request->billpdf;
            $data['key'] = ConfigRepository::BANGKE_PDF;
            $data['bdc_building_id'] = $this->building_active_id;
            $data['created_by'] = Auth::user()->id;
            $data['status'] = ConfigRepository::ACTIVE;
            $data['publish'] = ConfigRepository::INACTIVE;
        }
        if(!$configBangkePdf) {
            $this->model->create($data);    
        } else {
            $this->model->update($data, $configBangkePdf->id);
        }
        if(@$request->type == ConfigRepository::RECEIPT_VIEW){
            return redirect( route('admin.configs.receipt_style') )->with('success', 'Cập nhật mẫu phiếu thu thành công!');
        }
        if(@$request->type == ConfigRepository::BANGKE_PDF){
            return redirect( route('admin.configs.billPdf') )->with('success', 'Cập nhật mẫu bảng kê thành công!');
        }
      
    }
}
