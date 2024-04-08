<?php

namespace App\Http\Controllers\BusinessPartner;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\BuildingController;
use Illuminate\Support\Facades\Cookie;
use App\Repositories\BusinessPartners\BusinessPartnerRepository;
use Validator;
use App\Repositories\ServicePartners\ServicePartnersRepository;
use Excel;
use App\Models\ServicePartners\ServicePartners;

class BusinessPartnerController extends BuildingController
{
    private $businessPartnerRepository;
    private $servicePartnersRepository;
    // private $auth_id;

    /**
     * Constructor.
     */
    public function __construct(
        Request $request,
        BusinessPartnerRepository $businessPartnerRepository,
        ServicePartnersRepository $servicePartnersRepository
    )
    {
        //$this->middleware('route_permision');
        $this->businessPartnerRepository = $businessPartnerRepository;
         $this->servicePartnersRepository = $servicePartnersRepository;
        parent::__construct($request);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($id = 0,Request $request)
    {
        // business partners
        $data['meta_title'] = 'Quản lý đối tác';
        $data['per_page_business_partners'] = Cookie::get('per_page_business_partners', 10);
        $data['per_page_service_partners'] = Cookie::get('per_page_service_partners', 10);

        $data['filter_business_partners'] = $request->all();
        $data['business_partners_keyword'] = $request->input('business_partners_keyword', '');

        if ($id == 0 ) {
            $data['business_partners'] = $this->businessPartnerRepository->myPaginate($data['filter_business_partners'], $data['per_page_business_partners'],  $this->building_active_id);
           
        } else {
            $data['business_partners'] = $this->businessPartnerRepository->find($id)->get();
        }

        return view('businesspartners.index', $data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data                    = $request->except('_token');
        $data['pub_users_id']  = \Auth::id();
        $data['bdc_building_id']  = $this->building_active_id;

        $this->businessPartnerRepository->create($data);

        $responseData = [
            'success' => true,
            'message' => 'Thêm mới đối tác thành công!',
            'href' => route('admin.business-partners.index')
        ];

        return response()->json($responseData);
    }

    public function update(Request $request, $id = 0)
    {
        $data = $request->except('_token');

        $this->businessPartnerRepository->update($data, $id);


        $responseData = [
            'success' => true,
            'message' => 'Cập nhật đối tác thành công!',
            'href' => route('admin.business-partners.index')
        ];

        return response()->json($responseData);
    }
    public function exportExcel(Request $request)
    {
       $response = ServicePartners::
           where('bdc_building_id',  $this->building_active_id)
          ->filter($request)
          ->orderBy('updated_at', 'DESC')->get();

        // $array = unserialize($strBillIds);

        $result = Excel::create('dang ky dich vu', function ($excel) use ($response) {
            $excel->setTitle('dang ky dich vu');
            $excel->sheet('dang ky dich vu', function ($sheet) use ($response) {
                $service_partners = [];
                $row = 1;
                $sheet->row($row, [
                    'STT',
                    'Tên khách hàng',
                    'SĐT',
                    'Email',
                    'Thời gian đặt',
                    'Thời gian tạo',
                    'Ghi chú',
                    'Trạng thái',
                    'Người tạo',
                    'Ngày duyệt',
                    'Người duyệt',
                ]);
                foreach ($response as $key => $value) {
                    $row++;
                    // trạng thái
                    if ($value->status == 1) {
                        $status = 'Đã duyệt';
                    } else {
                        $status = 'Chưa duyệt';
                    }

                    $sheet->row($row, [
                        ($key + 1),
                        $value->customer,
                        $value->phone,
                        $value->email,
                        $value->timeorder,
                        $value->updated_at,
                        $value->description, 
                        $status,
                        @$value->PubUsers->infoWeb->display_name ?? '',
                        $value->confirm_date ?? '--/--/----',
                        @$value->Approved->infoWeb->email ?? '',
                    ]);
                }
            });
        })->store('xlsx',storage_path('exports/'));
$file     = storage_path('exports/'.$result->filename.'.'.$result->ext);
return response()->download($file)->deleteFileAfterSend(true);
             
    }
    /**
     * Show the form for editing the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function edit($id = 0)
    {
        $data['id'] = $id;

        $partners = $this->businessPartnerRepository->find($id);
      
        return \response()->json($partners);

    }
    public function delete(Request $request)
    {
        $id = $request->input('ids');
        $this->businessPartnerRepository->delete(['id' => $id]);

        $request->session()->flash('success', 'Xóa đối tác thành công!');
    }

    public function changeStatus(Request $request)
    {
        $data = $request->except('id');
        $this->businessPartnerRepository->find($request->id)->update($data);
        $dataResponse = [
            'success' => true,
            'message' => 'Cập nhật trạng thái thành công!'
        ];
        return response()->json($dataResponse);
    }

    public function action(Request $request)
    {
        if ($request->has('per_page_business_partners')) {
            $per_page = $request->input('per_page_business_partners', 10);
            Cookie::queue('per_page_business_partners', $per_page, 60 * 24 * 30);
            Cookie::queue('tab_business_partners', $request->tab);
        }

        if ($request->has('per_page_service_partners')) {
            $per_page_maintenance = $request->input('per_page_service_partners', 10);
            Cookie::queue('per_page_service_partners', $per_page_maintenance, 60 * 24 * 30);
            Cookie::queue('tab_service_partners', $request->tab);
        }

        return redirect()->back()->with('tab', $request->tab);
    }
}
