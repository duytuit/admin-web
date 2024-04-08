<?php

namespace App\Http\Controllers\Demo;

use App\Http\Controllers\Controller;
use App\Repositories\MailTemplates\MailTemplateRepository;
use App\Repositories\SettingSendMails\SettingSendMailRepository;
use App\Services\ServiceSendMail;
use Illuminate\Http\Request;
use App\Http\Requests\DemoPost\DemoPostRequest;
use App\Models\BdcDebitDetail\DebitDetail;
use App\Repositories\Building\BuildingRepository;
use App\Repositories\DemoPost\DemoPostRepository;
use App\Repositories\Service\ServiceRepository;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class DemoPostController extends Controller
{

    private $model;
    private $building;
    private $service;
    const INVOICE = 1;
    const EVENT = 2;

    public function __construct(DemoPostRepository $model, BuildingRepository $buildingRepository, ServiceRepository $serviceRepository)
    {
        // $this->middleware('auth', ['except'=>[]]);
        //$this->middleware('route_permision');
        $this->model = $model;
        $this->building = $buildingRepository;
        $this->service = $serviceRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return view('demo.index', [
            'meta_title'=> 'Demo Post'
        ]);
    }

    public function insertIntoBdcDebitV3(Request $request){
        if(empty($request->buildingId))
        {
            dd('chưa chuyền param query: buildingId');
        }
        $buildingId = $request->buildingId;
        $sql = "select * from (SELECT  bdc_apartment_service_price_id, count(bdc_apartment_service_price_id) as test,cycle_name,bdc_price_type_id,id
        FROM    bdc_debit_detail
        WHERE  EXISTS (SELECT id 
        FROM bdc_bills 
        WHERE bdc_debit_detail.bdc_bill_id = bdc_bills.id and bdc_bills.deleted_at is null) and bdc_debit_detail.bdc_building_id = $buildingId and version= 0 and bdc_price_type_id <> 1 and deleted_at is null group by bdc_apartment_id,bdc_apartment_service_price_id,cycle_name) as tb where test > 1";
        $rs = DB::select(DB::raw($sql));
        $sql_1 = "select * from (SELECT  bdc_apartment_service_price_id, count(bdc_apartment_service_price_id) as test,cycle_name,bdc_price_type_id,id
        FROM    bdc_debit_detail
        WHERE EXISTS (SELECT id 
        FROM bdc_bills 
        WHERE bdc_debit_detail.bdc_bill_id = bdc_bills.id and bdc_bills.deleted_at is null) and bdc_debit_detail.bdc_building_id = $buildingId and version= 0 and bdc_price_type_id <> 1 and deleted_at is null group by bdc_apartment_id,bdc_apartment_service_price_id,cycle_name) as tb where test = 1";
        $rs_2 = DB::select(DB::raw($sql_1));
        $result = array_merge($rs, $rs_2);
        if(count($result) == 0)
        {
            dd('không tìm thấy dữ liệu');
        }
       
        $result = Excel::create('Danh sách ghi số điện nước', function ($excel) use ($result) {
            $excel->setTitle('Danh sách căn hộ');
            $excel->sheet('Danh sách', function ($sheet) use ($result) {
                $row = 1;
                $sheet->row($row, [
                    'id',
                    'bdc_building_id',
                    'bdc_bill_id',
                    'bdc_apartment_id',
                    'bdc_service_id',
                    'bdc_apartment_service_price_id',
                    'title',
                    'sumery',
                    'from_date',
                    'to_date',
                    'detail',
                    'version',
                    'new_sumery',
                    'previous_owed',
                    'paid',
                    'created_at',
                    'updated_at',
                    'deleted_at',
                    'is_free',
                    'cycle_name',
                    'quantity',
                    'price',
                    'bdc_price_type_id',
                    'create_date',
                    'accounting_date',
                    'price_current',
                    'image',
                    'paid_v3',
                    'price_after_discount',
                    'type_discount',
                    'discount',
                    'code_receipt',
                    'old'
                ]);
                foreach ($result as $key => $value) {
                    $rs_debit = DebitDetail::where(['bdc_apartment_service_price_id' => $value->bdc_apartment_service_price_id, 'cycle_name' => $value->cycle_name, 'version' => 0])->get();
                   
                    foreach ($rs_debit as $key_1 => $value_1) {
                        $row++;
                        $sheet->row($row, [
                            $value_1->id,
                            $value_1->bdc_building_id,
                            $value_1->bdc_bill_id,
                            $value_1->bdc_apartment_id,
                            $value_1->bdc_service_id,
                            $value_1->bdc_apartment_service_price_id,
                            $value_1->title,
                            $value_1->sumery,
                            $value_1->from_date,
                            $value_1->to_date,
                            $value_1->detail,
                            $value_1->version,
                            $value_1->new_sumery,
                            $value_1->previous_owed,
                            $value_1->paid,
                            $value_1->created_at,
                            $value_1->updated_at,
                            $value_1->deleted_at,
                            $value_1->is_free,
                            $value_1->cycle_name,
                            $value_1->quantity,
                            $value_1->price,
                            $value_1->bdc_price_type_id,
                            $value_1->create_date,
                            $value_1->accounting_date,
                            $value_1->price_current,
                            $value_1->image,
                            $value_1->paid_v3,
                            $value_1->price_after_discount,
                            $value_1->type_discount,
                            $value_1->discount,
                            $value_1->code_receipt,
                            $value_1->old,
                        ]);
                    }
                   
                }
            });
        })->store('xlsx',storage_path('exports/'));
        $file     = storage_path('exports/'.$result->filename.'.'.$result->ext);
        return response()->download($file)->deleteFileAfterSend(true);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
      return view('demo.create',['meta_title'=> 'Demo Post Create']);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store( DemoPostRequest $request)
    {
      $input = $request->only(['title', 'description']);

      $this->model->create($input);

      return redirect('admin/demo/post/index');
    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
        // dd($request->input('filename'));
        return Redirect::to('/admin/demo/index' );
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    // public function update(Request $request, FileRepository $file, $id)
    public function update($id)
    {
        //
    }



    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
        return redirect()->back();
    }

    public function sendMail(Request $request)
    {
        $data = [
            'params' => [
              '@tenkhachhang' => 'Đàm Thanh Tuấn',
              '@tongtien' => '1,000000 VND',
              '@chucanho' => '1111',
              '@ngay' => '01/12/2019',
            ],
            'cc' => $request->get('email'),
            'building_id' => 1,
            'type' => self::EVENT,
            'status' => 'prepare'
        ];
        try {
            ServiceSendMail::setItemForQueue($data);
            var_dump($data);
            return ;
        } catch (\Exception $e)
        {
            return $e->getMessage();
        }
    }

    public function sendDemoMail(SettingSendMailRepository $sendMailRepository, MailTemplateRepository $mailTemplateRepository)
    {
        ServiceSendMail::sendMail($sendMailRepository, $mailTemplateRepository);
    }

    public function SyncService()
    {
        $services = DB::connection('mysql2')->select("select * from Services");
       // dd($services);
    }

    public function SyncBuilding()
    {

    }

}
