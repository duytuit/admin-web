<?php

namespace App\Http\Controllers\Apartments\V2;

use App\Commons\Api;
use App\Http\Controllers\BuildingController;
use App\Http\Requests\Apartments\ApartmentsRequest;
use App\Http\Requests\Customers\CustomersRequest;
use App\Models\BdcV2UserInfo\UserInfo;
use App\Models\Building\Building;
use App\Models\Building\V2\Company;
use App\Models\PublicUser\V2\TokenUser;
use App\Repositories\Apartments\ApartmentGroupRepository;
use App\Repositories\Apartments\ApartmentsRespository;

use App\Repositories\Building\BuildingPlaceRepository;
use App\Repositories\Building\BuildingRepository;
use App\Repositories\Feedback\FeedbackRespository;
use App\Repositories\PublicUsers\PublicUsersProfileRespository;
use App\Repositories\PublicUsers\PublicUsersRespository;
use App\Repositories\Customers\CustomersRespository;
use App\Repositories\Service\ServiceRepository;
use App\Repositories\SystemFiles\SystemFilesRespository;
use App\Repositories\VehicleCategory\VehicleCategoryRespository;
use App\Repositories\Vehicles\VehiclesRespository;
use App\Services\SendTelegram;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Apartments\ApartmentGroup;
use App\Models\Apartments\Apartments;
use App\Models\Apartments\V2\UserApartments;
use App\Models\BdcApartmentServicePrice\ApartmentServicePrice;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Media\Repositories\DocumentRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Repositories\BdcApartmentServicePrice\ApartmentServicePriceRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\Mime\Message;

class ApartmentsController extends BuildingController
{
    use ApiResponse;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    private $model;
    private $modelCustomers;
    private $modelUsers;
    private $modelUserProfile;
    private $modelVehicles;
    private $modelVehicleCategory;
    private $modelSystemFiles;
    private $modelFeedback;
    private $modelService;
    private $modelBuilding;
    private $modelBuildingPlace;
    private $modelApartmentGroup;
    protected $_documentRepository;
    protected $_buildingRepository;
    protected $_apartmentServicePriceRepository;

    public function __construct(
        ApartmentsRespository           $model,
        CustomersRespository            $modelCustomers,
        PublicUsersProfileRespository   $modelUserProfile,
        PublicUsersRespository          $modelUsers,
        VehiclesRespository             $modelVehicles,
        VehicleCategoryRespository      $modelVehicleCategory,
        SystemFilesRespository          $modelSystemFiles,
        FeedbackRespository             $modelFeedback,
        ServiceRepository               $modelService,
        BuildingRepository              $modelBuilding,
        BuildingPlaceRepository         $modelBuildingPlace,
        ApartmentGroupRepository        $modelApartmentGroup,
        DocumentRepository              $documentRepository,
        BuildingRepository              $buildingRepository,
        ApartmentServicePriceRepository $apartmentServicePriceRepository,
        Request                         $request
    )
    {
        $this->model = $model;
        $this->modelCustomers = $modelCustomers;
        $this->modelUsers = $modelUsers;
        $this->modelUserProfile = $modelUserProfile;
        $this->modelVehicles = $modelVehicles;
        $this->modelVehicleCategory = $modelVehicleCategory;
        $this->modelSystemFiles = $modelSystemFiles;
        $this->modelFeedback = $modelFeedback;
        $this->modelService = $modelService;
        $this->modelBuilding = $modelBuilding;
        $this->modelBuildingPlace = $modelBuildingPlace;
        $this->modelApartmentGroup = $modelApartmentGroup;
        $this->_documentRepository = $documentRepository;
        $this->_buildingRepository = $buildingRepository;
        $this->_apartmentServicePriceRepository = $apartmentServicePriceRepository;
        try {
            parent::__construct($request);
        } catch (\Exception $e) {
        }
    }

    public function getListdocument(Request $request)
    {
        $building_id = $request->get('building_id');
        $document_type = $request->get('document_type', 1);
        $apartment_id = $request->get('apartment_id');
        $apartment_group_id = $request->get('apartment_group_id');

        $filter = [
            'building_id' => $building_id,
            'apartment_id' => $apartment_id,
            'apartment_group_id' => $apartment_group_id
        ];

        $limit = isset($request->limit) ? $request->limit : 10;
        $page = isset($request->page) ? $request->page : 1;

        $documents = null;

        if ($document_type == 1) {
            $documents = $this->_documentRepository->filterByBuildingId($building_id);
        } else if ($document_type == 2) {
            $documents = $this->_documentRepository->filterDocument($filter);
        } else if ($document_type == 3 && !empty($apartment_id)) {
            $documents = $this->_documentRepository->filterDocumentByApartmentId($apartment_id);
        }

        $offSet = ($page * $limit) - $limit;
        $itemsForCurrentPage = array_slice($documents->toArray(), $offSet, $limit, true);
        $_documents = new LengthAwarePaginator($itemsForCurrentPage, count($documents), $limit, $page, []);
        $paging = [
            'total' => $_documents->total(),
            'currentPage' => $_documents->count(),
            'lastPage' => $_documents->lastPage(),
        ];

        $_documentArr = [];

        foreach ($_documents as $_document) {
            $_document = (object)$_document;
            $_document->attach_file = json_decode($_document->attach_file);
            array_push($_documentArr, $_document);
        }
        $_documentsList = $_documents->values()->toArray();
        if ($_documentsList) {
            $result = ['data' => $_documentArr, 'page' => $paging];
        } else {
            $result = ['data' => [], 'page' => $paging];
        }
        return $result;
    }

    public function index(Request $request)
    {

        $data['meta_title'] = 'Apartment';
        $data['per_page'] = Cookie::get('per_page', 20);
        $where = [];
        $apartment = $this->model->searchBy($this->building_active_id, $request, $where, $data['per_page']);
        $data_search = [
            'ap_name' => '',
            'ap_floor' => '',
            'ap_role' => '',
            'building_place_id' => '',
            'status' => '',
        ];

        $data['data_search'] = $request->data_search ?: $data_search;
        $data['data_search']['name'] = $request->name;
        $data['data_search']['name_group'] = $request->name_group;
        $data['data_search']['floor'] = $request->floor;

        if ($request->re_name != null) {
            $name = $this->modelUserProfile->findBy('id', $request->re_name, 'display_name');
            $data['data_search']['re_name'] = $name->re_name;
            $data['data_search']['name_profile'] = $name->display_name;
        }

        if ($request->place != null) {
            $name = $this->modelBuildingPlace->findById($request->place);
            $data['data_search']['building_place_id'] = $request->place;
            $data['name_place'] = $name->name . ' - ' . $name->code;
        }

        if ($request->status != null) {
            $data['data_search']['status'] = $request->status;
        }
        if ($request->re_name != null) {
            $data['data_search']['re_name'] = $request->re_name;
        }
        if (isset($request->search_key) && $request->search_key != null) {
            $apartment = Apartments::withTrashed()->where('name', $request->search_key)->paginate($data['per_page']);
        }
        $data['building_id'] = $this->building_active_id;
        $apartmentGroup = $this->modelApartmentGroup->searchBy($this->building_active_id, $request);
        //$data['residents'] = $this->modelCustomers->all(['id', 'pub_user_profile_id']);
        $data['apartments'] = $apartment;
        $data['apartment_groups'] = $apartmentGroup;
        return view('apartments.v2.index', $data);
    }

    public function reportApartmentbyCompany(Request $request)
    {
        $data['meta_title'] = 'Apartment';
        $data['filter'] = $request->all();
        $data['company'] = Company::get();
        
        if(isset($data['filter']['buildings_id'])){
            $data['buildings'] = Building::where('company_id',$data['filter']['company_id'] )->get();
        }
        return view('apartments.v2.report_apartment', $data);
    }

    public function getReportApartmentbyCompany(Request $request)
    {
        $fillter =  $request->fillter;
        if ($fillter == 'All') {
            $building =  Building::where('company_id', $request->company_id)->where('status', 1)->where('status_apartment',1)->get();
        }
        else
        {
            $building =  Building::where('id', $request->building_id)->where('status', 1)->where('status_apartment',1)->get();
        }
        //$building =  Building::where('id', $request->building_id)->get(); 
        //$building = Building::where('status', 1)->get();
        try {
            $result = Excel::create('Danh_sach_bao_cao' . date('d-m-Y-H-i-s', time()), function ($excel) use ($building, $request) {
                $excel->setTitle('Danh sách báo cáo');
                $excel->sheet('Danh sách báo cáo', function ($sheet) use ($building, $request) {
                    $row = 12;
                    $sheet->mergeCells('A1:F2');
                    $sheet->getStyle('A1')->getAlignment()->setWrapText(true);
                    $sheet->cells('A1', function ($cells) {
                        $cells->setFontSize(18);
                        $cells->setFontWeight('bold');
                        $cells->setValue('CÔNG TY TNHH CÔNG NGHỆ S-TECH');
                        $cells->setValignment('center');
                        $cells->setAlignment('center');
                    });
                    $date_time = Carbon::now();
                    $sheet->mergeCells('A3:F3');
                    $sheet->getStyle('A3')->getAlignment()->setWrapText(true);
                    $sheet->cells('A3', function ($cells) use ($date_time) {
                        $cells->setFontSize(11);
                        $cells->setValue('Địa chỉ: Tầng 18, tòa Center Building, số 01 Nguyễn Huy Tưởng, P. Thanh Xuân Trung, Q.Thanh Xuân, TP HN, VN');
                        $cells->setValignment('center');
                        $cells->setAlignment('center');
                    });
                    $sheet->mergeCells('A4:F4');
                    $sheet->getStyle('A4')->getAlignment()->setWrapText(true);
                    $sheet->cells('A4', function ($cells) use ($date_time) {
                        $cells->setFontSize(11);
                        $cells->setValue("Ngày " . $date_time->format('d') . " Tháng " . $date_time->format('m') . " năm " . $date_time->format('Y'));
                        $cells->setValignment('right');
                        $cells->setAlignment('right');
                    });
                    $sheet->mergeCells('A6:F6');
                    $sheet->getStyle('A6')->getAlignment()->setWrapText(true);
                    $sheet->cells('A6', function ($cells) use ($request) {
                        $cells->setFontSize(18);
                        $cells->setFontWeight('bold');
                        $cells->setValue("BIÊN BẢN ĐỐI SOÁT DỊCH VỤ");
                        $cells->setValignment('center');
                        $cells->setAlignment('center');
                    });

                    $sheet->mergeCells('A8:F8');
                    $sheet->getStyle('A8')->getAlignment()->setWrapText(true);
                    $sheet->cells('A8', function ($cells) use ($request) {
                        $cells->setFontSize(11);
                        $cells->setFontWeight('bold');
                        if ($request->company_id === 1)
                        {
                            $cells->setValue("Kính gửi: CÔNG TY CP DỊCH VỤ QUẢN LÝ TÀI SẢN VÀ ĐẦU TƯ ASAHI JAPAN"); 
                        }
                        else
                        {
                            $cells->setValue("Kính gửi: Công ty TNHH Công nghệ S-TECH"); 
                        }
                        $cells->setValignment('left');
                        $cells->setAlignment('left');
                    });

                    $sheet->mergeCells('A9:F9');
                    $sheet->getStyle('A9')->getAlignment()->setWrapText(true);
                    $sheet->cells('A9', function ($cells) {
                        $cells->setFontSize(11);
                        $cells->setValue('Công ty TNHH Công nghệ S-TECH xin gửi tới quý khách hàng bản đối soát dịch vụ phần mềm Building Care cụ thể như sau:');
                        $cells->setValignment('left');
                        $cells->setAlignment('left');
                    });
                    $sheet->mergeCells('A10:F10');
                    $sheet->getStyle('A10')->getAlignment()->setWrapText(true);
                    $sheet->cells('A10', function ($cells) use ($request) {
                        if (isset($request->from_date) && $request->from_date != null && isset($request->to_date) && $request->to_date != null) {
                            $cells->setFontSize(13);
                            $cells->setFontWeight('Iatalic');
                            $cells->setValue('Từ: ' . $request->from_date . ' đến: ' . $request->to_date);
                            $cells->setAlignment('center');
                            $cells->setValignment('center');
                        }
                    });

                    $sheet->mergeCells('A11:A12');
                    $sheet->getStyle('A11')->getAlignment()->setWrapText(true);
                    $sheet->cells('A11', function ($cells) {
                        $cells->setFontSize(11);
                        $cells->setFontWeight('bold');
                        $cells->setValue('STT');
                        $cells->setValignment('center');
                        $cells->setAlignment('center');
                        $cells->setBorder('thin', 'thin', 'thin', 'thin');
                    });

                    $sheet->mergeCells('B11:B12');
                    $sheet->getStyle('B11')->getAlignment()->setWrapText(true);
                    $sheet->cells('B11', function ($cells) {
                        $cells->setFontSize(11);
                        $cells->setFontWeight('bold');
                        $cells->setValue('Dự Án');
                        $cells->setValignment('center');
                        $cells->setAlignment('center');
                        $cells->setBorder('thin', 'thin', 'thin', 'thin');
                    });
                    $sheet->mergeCells('C11:C12');
                    $sheet->getStyle('C11')->getAlignment()->setWrapText(true);
                    $sheet->cells('C11', function ($cells) {
                        $cells->setFontSize(11);
                        $cells->setFontWeight('bold');
                        $cells->setValue('Ngày bắt đầu đầu tính phí');
                        $cells->setValignment('center');
                        $cells->setAlignment('center');
                        $cells->setBorder('thin', 'thin', 'thin', 'thin');
                    });
                    $sheet->mergeCells('D11:D12');
                    $sheet->getStyle('D11')->getAlignment()->setWrapText(true);
                    $sheet->cells('D11', function ($cells) {
                        $cells->setFontSize(11);
                        $cells->setFontWeight('bold');
                        $cells->setValue('Số lượng căn hộ trên PM');
                        $cells->setValignment('center');
                        $cells->setAlignment('center');
                        $cells->setBorder('thin', 'thin', 'thin', 'thin');
                    });
                    $sheet->mergeCells('E11:E12');
                    $sheet->getStyle('E11')->getAlignment()->setWrapText(true);
                    $sheet->cells('E11', function ($cells) {
                        $cells->setFontSize(11);
                        $cells->setFontWeight('bold');
                        $cells->setValue('Số lượng căn hộ thực tính phí');
                        $cells->setValignment('center');
                        $cells->setAlignment('center');
                        $cells->setBorder('thin', 'thin', 'thin', 'thin');
                    });
                    $sheet->mergeCells('F11:F12');
                    $sheet->getStyle('F11')->getAlignment()->setWrapText(true);
                    $sheet->cells('F11', function ($cells) {
                        $cells->setFontSize(11);
                        $cells->setFontWeight('bold');
                        $cells->setValue('Căn hộ có chủ hộ');
                        $cells->setValignment('center');
                        $cells->setAlignment('center');
                        $cells->setBorder('thin', 'thin', 'thin', 'thin');
                    });
                    $sheet->mergeCells('G11:G12');
                    $sheet->getStyle('G11')->getAlignment()->setWrapText(true);
                    $sheet->cells('G11', function ($cells) {
                        $cells->setFontSize(11);
                        $cells->setFontWeight('bold');
                        $cells->setValue('Ghi chú');
                        $cells->setValignment('center');
                        $cells->setAlignment('center');
                        $cells->setBorder('thin', 'thin', 'thin', 'thin');
                    });
                    $sheet->mergeCells('H11:H12');
                    $sheet->getStyle('H11')->getAlignment()->setWrapText(true);
                    $sheet->cells('H11', function ($cells) {
                        $cells->setFontSize(11);
                        $cells->setFontWeight('bold');
                        $cells->setValue('Căn hộ tải APP');
                        $cells->setValignment('center');
                        $cells->setAlignment('center');
                        $cells->setBorder('thin', 'thin', 'thin', 'thin');
                    });
                    $sheet->mergeCells('I11:I12');
                    $sheet->getStyle('I11')->getAlignment()->setWrapText(true);
                    $sheet->cells('I11', function ($cells) {
                        $cells->setFontSize(11);
                        $cells->setFontWeight('bold');
                        $cells->setValue('Căn hộ chưa tải APP');
                        $cells->setValignment('center');
                        $cells->setAlignment('center');
                        $cells->setBorder('thin', 'thin', 'thin', 'thin');
                    });
                   
                        foreach ($building as $key => $item) {

                            $row++;
                            $count_apartment = Apartments::where(function ($query) use ($request, $item) {
                                if (isset($request->from_date) && $request->from_date != null && isset($request->to_date) && $request->to_date != null) {
                                    //$from_date = Carbon::parse($request->from_date)->format('Y-m-d');
                                    $to_date = Carbon::parse($request->to_date)->format('Y-m-d');
                                    //$query->whereDate('created_at', '>=', $from_date);
                                    $query->whereDate('created_at', '<=', $to_date);
                                }
                                $query->where('building_id', $item->id);
                            })->count();
                            if (($count_apartment === 0) || (!$count_apartment))
                            {
                                $count_apartment_debit= 0;
                                $count_apartment_purchaser= 0;
                                $countResult= 0;
                                $dontuse= 0;
                            }
                            else
                            {
                            /*$count_apartment_debit = Apartments::where(function ($query) use ($request, $item) {
                                if (isset($request->from_date) && $request->from_date != null && isset($request->to_date) && $request->to_date != null) {
                                    $from_date = Carbon::parse($request->from_date)->format('Y-m-d');
                                    $to_date = Carbon::parse($request->to_date)->format('Y-m-d');
                                    $query->whereDate('created_at', '>=', $from_date);
                                    $query->whereDate('created_at', '<=', $to_date);
                                }
                                $query->where('building_id', $item->id);
                            })->whereHas('debit_v2')->count();*/
                            if (isset($request->from_date) && $request->from_date != null && isset($request->to_date) && $request->to_date != null){
                            $from_date = Carbon::parse($request->from_date)->format('Y-m-d');
                            $to_date = Carbon::parse($request->to_date)->format('Y-m-d');
                            $sql1= "select
                            count(*) as aggregate
                            from
                            bdc_apartments
                            where
                            ( building_id = " . $item->id . ")
                            and exists (
                            select
                                *
                            from
                                bdc_v2_debit_detail
                            where
                                bdc_apartments.id = bdc_v2_debit_detail.bdc_apartment_id
                                and bdc_v2_debit_detail.deleted_at is null and date(created_at) >= '".$from_date."'
                                and date(created_at) <='".$to_date."')
                            and bdc_apartments.deleted_at is null ";
                            }
                            else 
                            {
                                $sql1= "select
                            count(*) as aggregate
                            from
                            bdc_apartments
                            where
                            ( building_id = " . $item->id . ")
                            and exists (
                            select
                                *
                            from
                                bdc_v2_debit_detail
                            where
                                bdc_apartments.id = bdc_v2_debit_detail.bdc_apartment_id
                                and bdc_v2_debit_detail.deleted_at is null 
                               )
                            and bdc_apartments.deleted_at is null ";
                            }
                            //SendTelegram::SupersendTelegramMessage('sql check'. $sql1);
                            $count = DB::select(DB::raw($sql1));
                            $count_apartment_debit = $count[0]->aggregate;
                           // SendTelegram::SupersendTelegramMessage('sql check debit'. $count_apartment_debit);
                                //Duong remove old logic.

        //remove for net function                    $count_apartment_purchaser = Apartments::where(function ($query) use ($request, $item) {
                            /*    if (isset($request->from_date) && $request->from_date != null && isset($request->to_date) && $request->to_date != null) {
                                    $from_date = Carbon::parse($request->from_date)->format('Y-m-d');
                                    $to_date = Carbon::parse($request->to_date)->format('Y-m-d');
                                    $query->whereDate('created_at', '>=', $from_date);
                                    $query->whereDate('created_at', '<=', $to_date);
                                }
                                $query->where('building_id', $item->id);
                            })->whereHas('bdcResident', function ($query) {
                                $query->where('type', 0);
                            })->count(); */
                            if (isset($request->from_date) && $request->from_date != null && isset($request->to_date) && $request->to_date != null) {
                                $from_date = Carbon::parse($request->from_date)->format('Y-m-d');
                                $to_date = Carbon::parse($request->to_date)->format('Y-m-d');
                            $sql0= "select
                            count(*) as count
                        from
                            bdc_apartments
                        where
                                ( building_id = " . $item->id . " )
                            and exists (
                            select
                                *
                            from
                                bdc_v2_user_apartment
                            where
                                bdc_apartments.id = bdc_v2_user_apartment.apartment_id
                                and type = 0
                                and bdc_v2_user_apartment.deleted_at is null)
                            and bdc_apartments.deleted_at is null"; // and (date(created_at) >=  '".$from_date."' and date(created_at) <= '".$to_date."'     )
                            }
                            else
                            {
                                $sql0= "select
                            count(*) as count
                        from
                            bdc_apartments
                        where
                                ( building_id =  " . $item->id . ")
                            and exists (
                            select
                                *
                            from
                                bdc_v2_user_apartment
                            where
                                bdc_apartments.id = bdc_v2_user_apartment.apartment_id
                                and type = 0
                                and bdc_v2_user_apartment.deleted_at is null)
                            and bdc_apartments.deleted_at is null";
                            }
                            $count0 = DB::select(DB::raw($sql0));
                            $count_apartment_purchaser = $count0[0]->count;

                          //  $cudan2 = UserApartments::where('building_id', $item->id)->select('user_info_id')->count()
                            //$cudan1 = UserApartments::where('building_id', $item->id)->select('user_info_id')->get();
                           // $cudantrongtoa1 = UserInfo::whereIn('id', $cudan1->pluck('user_info_id'))->select('user_id')->distinct()->get();
                          //  SendTelegram::SupersendTelegramMessage('cu dan trong toa: '. $cudantrongtoa1);
                           // $useruseapp1 = TokenUser::whereIn('user_id', $cudantrongtoa1->pluck('user_id'))->distinct()->count();
                           // SendTelegram::SupersendTelegramMessage('cu dan dung app: '. $useruseapp1);

                            $sql3 = "select  count(*) as count  From bdc_apartments   where building_id =" . $item->id . " and deleted_at is null  and id in (   SELECT bdc_apartments.id 
                            FROM bdc_apartments
                            INNER JOIN bdc_v2_user_apartment on bdc_v2_user_apartment.apartment_id = bdc_apartments.id
                            INNER JOIN bdc_v2_user_info on bdc_v2_user_info.id = bdc_v2_user_apartment.user_info_id
                            INNER JOIN bdc_v2_user on bdc_v2_user.id = bdc_v2_user_info.user_id
                            INNER JOIN bdc_v2_token on bdc_v2_user.id = bdc_v2_token.user_id
                            where bdc_v2_user_apartment.building_id = " . $item->id . "
                            and bdc_v2_user.deleted_at is null
                            and bdc_v2_user_info.deleted_at is null
                            and bdc_v2_user_apartment.deleted_at is null
                            and bdc_apartments.deleted_at is null
                            and bdc_v2_token.deleted_at is null
                            and bdc_v2_token.last_login > DATE_SUB(NOW(), INTERVAL 3 MONTH)  group by  id)";
                               $count1 = DB::select(DB::raw($sql3));
                               $countResult = $count1[0]->count; // 
                                if(!$countResult){
                                    $countResult = 0;
                                }
                            $dontuse= $count_apartment - $countResult;
                            }

                            $data = [
                                'stt' => ($key + 1),
                                'name' => $item->name,
                                'ngay_bat_dau' => $item->created_at,
                                'so_can_ho' => $count_apartment,
                                'so_can_ho_tinh_phi' => $count_apartment_debit,
                                'can_ho_co_chu_ho' => $count_apartment_purchaser,
                                'ghi_chu' => '',
                                'use_app' =>  $countResult,
                                'dont_use_app' => $dontuse,

                            ];
                            $sheet->row($row, $data);

                    }
                 
                    
                    $sheet->setWidth(array(
                        'B' => 50,
                        'C' => 20,
                        'D' => 15,
                        'E' => 15,
                        'F' => 15,
                        'H' => 20,
                        'I' => 20,
                    ));
                });

            })->store('xlsx', storage_path('exports/'));
            $file = storage_path('exports/' . $result->filename . '.' . $result->ext);
            return response()->download($file)->deleteFileAfterSend(true);

        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }

    public function getReportApartmentbyCompanyUpgrade(Request $request)
    {
        //$building = Building::where('status', 1)->get();
        $building= Building::where('status', 1)
        ->whereIn('id',$request->idfillter)
        ->get();
        try {
            $result = Excel::create('Danh_sach_bao_cao' . date('d-m-Y-H-i-s', time()), function ($excel) use ($building, $request) {
                $excel->setTitle('Danh sách báo cáo');
                $excel->sheet('Danh sách báo cáo', function ($sheet) use ($building, $request) {
                    $row = 12;
                    $sheet->mergeCells('A1:F2');
                    $sheet->getStyle('A1')->getAlignment()->setWrapText(true);
                    $sheet->cells('A1', function ($cells) {
                        $cells->setFontSize(18);
                        $cells->setFontWeight('bold');
                        $cells->setValue('CÔNG TY TNHH CÔNG NGHỆ S-TECH');
                        $cells->setValignment('center');
                        $cells->setAlignment('center');
                    });
                    $date_time = Carbon::now();
                    $sheet->mergeCells('A3:F3');
                    $sheet->getStyle('A3')->getAlignment()->setWrapText(true);
                    $sheet->cells('A3', function ($cells) use ($date_time) {
                        $cells->setFontSize(11);
                        $cells->setValue('Địa chỉ: Tầng 18, tòa Center Building, số 01 Nguyễn Huy Tưởng, P. Thanh Xuân Trung, Q.Thanh Xuân, TP HN, VN');
                        $cells->setValignment('center');
                        $cells->setAlignment('center');
                    });
                    $sheet->mergeCells('A4:F4');
                    $sheet->getStyle('A4')->getAlignment()->setWrapText(true);
                    $sheet->cells('A4', function ($cells) use ($date_time) {
                        $cells->setFontSize(11);
                        $cells->setValue("Ngày " . $date_time->format('d') . " Tháng " . $date_time->format('m') . " năm " . $date_time->format('Y'));
                        $cells->setValignment('right');
                        $cells->setAlignment('right');
                    });
                    $sheet->mergeCells('A6:F6');
                    $sheet->getStyle('A6')->getAlignment()->setWrapText(true);
                    $sheet->cells('A6', function ($cells) use ($request) {
                        $cells->setFontSize(18);
                        $cells->setFontWeight('bold');
                        $cells->setValue("BIÊN BẢN ĐỐI SOÁT DỊCH VỤ");
                        $cells->setValignment('center');
                        $cells->setAlignment('center');
                    });

                    $sheet->mergeCells('A8:F8');
                    $sheet->getStyle('A8')->getAlignment()->setWrapText(true);
                    $sheet->cells('A8', function ($cells) use ($request) {
                        $cells->setFontSize(11);
                        $cells->setFontWeight('bold');
                        $cells->setValue("Kính gửi: CÔNG TY CP DỊCH VỤ QUẢN LÝ TÀI SẢN VÀ ĐẦU TƯ ASAHI JAPAN");
                        $cells->setValignment('left');
                        $cells->setAlignment('left');
                    });

                    $sheet->mergeCells('A9:F9');
                    $sheet->getStyle('A9')->getAlignment()->setWrapText(true);
                    $sheet->cells('A9', function ($cells) {
                        $cells->setFontSize(11);
                        $cells->setValue('Công ty TNHH Công nghệ S-TECH xin gửi tới quý khách hàng bản đối soát dịch vụ phần mềm Building Care cụ thể như sau:');
                        $cells->setValignment('left');
                        $cells->setAlignment('left');
                    });
                    $sheet->mergeCells('A10:F10');
                    $sheet->getStyle('A10')->getAlignment()->setWrapText(true);
                    $sheet->cells('A10', function ($cells) use ($request) {
                        if (isset($request->from_date) && $request->from_date != null && isset($request->to_date) && $request->to_date != null) {
                            $cells->setFontSize(13);
                            $cells->setFontWeight('Iatalic');
                            $cells->setValue('Từ: ' . $request->from_date . ' đến: ' . $request->to_date);
                            $cells->setAlignment('center');
                            $cells->setValignment('center');
                        }
                    });

                    $sheet->mergeCells('A11:A12');
                    $sheet->getStyle('A11')->getAlignment()->setWrapText(true);
                    $sheet->cells('A11', function ($cells) {
                        $cells->setFontSize(11);
                        $cells->setFontWeight('bold');
                        $cells->setValue('STT');
                        $cells->setValignment('center');
                        $cells->setAlignment('center');
                        $cells->setBorder('thin', 'thin', 'thin', 'thin');
                    });

                    $sheet->mergeCells('B11:B12');
                    $sheet->getStyle('B11')->getAlignment()->setWrapText(true);
                    $sheet->cells('B11', function ($cells) {
                        $cells->setFontSize(11);
                        $cells->setFontWeight('bold');
                        $cells->setValue('Dự Án');
                        $cells->setValignment('center');
                        $cells->setAlignment('center');
                        $cells->setBorder('thin', 'thin', 'thin', 'thin');
                    });
                    $sheet->mergeCells('C11:C12');
                    $sheet->getStyle('C11')->getAlignment()->setWrapText(true);
                    $sheet->cells('C11', function ($cells) {
                        $cells->setFontSize(11);
                        $cells->setFontWeight('bold');
                        $cells->setValue('Ngày bắt đầu đầu tính phí');
                        $cells->setValignment('center');
                        $cells->setAlignment('center');
                        $cells->setBorder('thin', 'thin', 'thin', 'thin');
                    });
                    $sheet->mergeCells('D11:D12');
                    $sheet->getStyle('D11')->getAlignment()->setWrapText(true);
                    $sheet->cells('D11', function ($cells) {
                        $cells->setFontSize(11);
                        $cells->setFontWeight('bold');
                        $cells->setValue('Số lượng căn hộ trên PM');
                        $cells->setValignment('center');
                        $cells->setAlignment('center');
                        $cells->setBorder('thin', 'thin', 'thin', 'thin');
                    });
                    $sheet->mergeCells('E11:E12');
                    $sheet->getStyle('E11')->getAlignment()->setWrapText(true);
                    $sheet->cells('E11', function ($cells) {
                        $cells->setFontSize(11);
                        $cells->setFontWeight('bold');
                        $cells->setValue('Số lượng căn hộ thực tính phí');
                        $cells->setValignment('center');
                        $cells->setAlignment('center');
                        $cells->setBorder('thin', 'thin', 'thin', 'thin');
                    });
                    $sheet->mergeCells('F11:F12');
                    $sheet->getStyle('F11')->getAlignment()->setWrapText(true);
                    $sheet->cells('F11', function ($cells) {
                        $cells->setFontSize(11);
                        $cells->setFontWeight('bold');
                        $cells->setValue('Căn hộ có chủ hộ');
                        $cells->setValignment('center');
                        $cells->setAlignment('center');
                        $cells->setBorder('thin', 'thin', 'thin', 'thin');
                    });
                    $sheet->mergeCells('G11:G12');
                    $sheet->getStyle('G11')->getAlignment()->setWrapText(true);
                    $sheet->cells('G11', function ($cells) {
                        $cells->setFontSize(11);
                        $cells->setFontWeight('bold');
                        $cells->setValue('Ghi chú');
                        $cells->setValignment('center');
                        $cells->setAlignment('center');
                        $cells->setBorder('thin', 'thin', 'thin', 'thin');
                    });
                    $sheet->mergeCells('H11:H12');
                    $sheet->getStyle('H11')->getAlignment()->setWrapText(true);
                    $sheet->cells('H11', function ($cells) {
                        $cells->setFontSize(11);
                        $cells->setFontWeight('bold');
                        $cells->setValue('Cư dân tải APP');
                        $cells->setValignment('center');
                        $cells->setAlignment('center');
                        $cells->setBorder('thin', 'thin', 'thin', 'thin');
                    });
                    $sheet->mergeCells('I11:I12');
                    $sheet->getStyle('I11')->getAlignment()->setWrapText(true);
                    $sheet->cells('I11', function ($cells) {
                        $cells->setFontSize(11);
                        $cells->setFontWeight('bold');
                        $cells->setValue('Cư dân chưa tải APP');
                        $cells->setValignment('center');
                        $cells->setAlignment('center');
                        $cells->setBorder('thin', 'thin', 'thin', 'thin');
                    });
                    foreach ($building as $key => $item) {
                        $row++;
                        $count_apartment = Apartments::where(function ($query) use ($request, $item) {
                            if (isset($request->from_date) && $request->from_date != null && isset($request->to_date) && $request->to_date != null) {
                                //$from_date = Carbon::parse($request->from_date)->format('Y-m-d');
                                $to_date = Carbon::parse($request->to_date)->format('Y-m-d');
                                //$query->whereDate('created_at', '>=', $from_date);
                                $query->whereDate('created_at', '<=', $to_date);
                            }
                            $query->where('building_id', $item->id);
                        })->count();
                        $count_apartment_debit = Apartments::where(function ($query) use ($request, $item) {
                            if (isset($request->from_date) && $request->from_date != null && isset($request->to_date) && $request->to_date != null) {
                                $from_date = Carbon::parse($request->from_date)->format('Y-m-d');
                                $to_date = Carbon::parse($request->to_date)->format('Y-m-d');
                                $query->whereDate('created_at', '>=', $from_date);
                                $query->whereDate('created_at', '<=', $to_date);
                            }
                            $query->where('building_id', $item->id);
                        })->whereHas('debit_v2')->count();
                        $count_apartment_purchaser = Apartments::where(function ($query) use ($request, $item) {
                            if (isset($request->from_date) && $request->from_date != null && isset($request->to_date) && $request->to_date != null) {
                                $from_date = Carbon::parse($request->from_date)->format('Y-m-d');
                                $to_date = Carbon::parse($request->to_date)->format('Y-m-d');
                                $query->whereDate('created_at', '>=', $from_date);
                                $query->whereDate('created_at', '<=', $to_date);
                            }
                            $query->where('building_id', $item->id);
                        })->whereHas('bdcResident', function ($query) {
                            $query->where('type', 0);
                        })->count();
                        $data = [
                            'stt' => ($key + 1),
                            'name' => $item->name,
                            'ngay_bat_dau' => $item->created_at,
                            'so_can_ho' => $count_apartment,
                            'so_can_ho_tinh_phi' => $count_apartment_debit,
                            'can_ho_co_chu_ho' => $count_apartment_purchaser,
                            'ghi_chu' => '',
                        ];
                        $sheet->row($row, $data);
                    }
                    $sheet->setWidth(array(
                        'B' => 50,
                        'C' => 20,
                        'D' => 15,
                        'E' => 15,
                        'F' => 15,
                        'H' => 20,
                        'I' => 20,
                    ));
                });

            })->store('xlsx', storage_path('exports/'));
            $file = storage_path('exports/' . $result->filename . '.' . $result->ext);
            return response()->download($file)->deleteFileAfterSend(true);

        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $data['building_id'] = $this->building_active_id;
        $data['meta_title'] = "Thêm mới căn hộ";
        return view('apartments.v2.create', $data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, $id)
    {
        $data['meta_title'] = "Thay đổi căn hộ";
        $apatment = $this->model->findById($id);
        $vehicles = @$apatment->bdcVehicles;
        $data['apatment'] = $apatment;
        $data['building_place'] = $this->modelBuildingPlace->getDataExport(['*'], $this->building_active_id);
        $data['residents'] = UserApartments::where('apartment_id', $apatment->id)->get();
        $data['vehicle_cate'] = $this->modelVehicleCategory->all(['name', 'id']);

        $data['vehicles'] = isset($vehicles) ? $vehicles : [];

        $data['files'] = $this->modelSystemFiles->checkModulFile('apartment', $id, 10);
        $data['feedbacks'] = $this->modelFeedback->logFeedbackApartment($apatment->id);
        $data['services'] = $this->modelService->getAllServiceCompanySelect1('*', $apatment->id, $this->building_active_id);
        $data['status'] = isset($apatment->status) ? $apatment->status : '';
        $data['id'] = $id;
        $data['count_apt'] = number_format($this->model->countItem(), 0, ",", ".");
        $data['count_cus'] = number_format($this->modelCustomers->countItem(), 0, ",", ".");
        $data['count_vh'] = number_format($this->modelVehicles->countItem(), 0, ",", ".");
        $data['data_cus'] = Session::get('data_cus');
        $data['data_vhc'] = Session::get('data_vhc');
        $data['data_error'] = Session::get('error');
        $data['data_success'] = Session::get('success');

        $request->request->add([
            'building_id' => $this->building_active_id,
            'document_type' => 2,
            'apartment_id' => $id,
            'page' => 1,
            'limit' => 10000
        ]);

        $response_apartments = (object)$this->getListdocument($request);

        $documents = $response_apartments->data;

        $data['documents'] = $documents;

        $vehicleCateActive = DB::table('bdc_vehicles_category')
            ->where('bdc_building_id', $this->building_active_id)
            ->where('status', 1)
            ->whereNull('deleted_at')
            ->get();

        $data['vehicleCateActive'] = $vehicleCateActive;

        //dd($data);
        return view('apartments.v2.edit', $data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function save(ApartmentsRequest $request)
    {
        $building_place_id = $this->modelBuildingPlace->getId($this->building_active_id, $request->building_place_id);
        if (!$building_place_id) {
            return redirect()->route('admin.v2.apartments.index')->with('error', 'Mã tòa nhà ở khu tòa nhà hiện tại không đúng');
        }
        $input = $request->only(['building_id', 'name', 'description', 'floor', 'status', 'area', 'code', 'code_customer', 'name_customer', 'code_electric', 'code_water']);
        $input['building_place_id'] = $building_place_id;
        $input['created_by'] = auth()->user()->id;
        $check_name_duplicate = $this->model->findByName_v3($this->building_active_id, $building_place_id, $input['name']);
        if ($check_name_duplicate) {
            return redirect()->route('admin.v2.apartments.index')->with('error', 'Tên căn hộ này đã tồn tại');
        }
        $check_code_duplicate = $this->model->findByCode($this->building_active_id, $input['code']);
        if ($check_code_duplicate) {
            return redirect()->route('admin.v2.apartments.index')->with('error', 'Mã căn hộ này đã tồn tại');
        }

        if (isset($input['code_customer'])) {
            $check_code_duplicate = $this->model->checkExitByCode($this->building_active_id, $input['code_customer']);
            if ($check_code_duplicate) {
                return redirect()->route('admin.v2.apartments.index')->with('error', 'Mã khách hàng này đã tồn tại');
            }
        }

        if (isset($input['code_electric'])) {
            $check_code_duplicate = $this->model->checkExitByCode($this->building_active_id, $input['code_electric']);
            if ($check_code_duplicate) {
                return redirect()->route('admin.v2.apartments.index')->with('error', 'Mã công tơ điện này đã tồn tại');
            }
        }

        if (isset($input['code_water'])) {
            $check_code_duplicate = $this->model->checkExitByCode($this->building_active_id, $input['code_water']);
            if ($check_code_duplicate) {
                return redirect()->route('admin.v2.apartments.index')->with('error', 'Mã công tơ nước này đã tồn tại');
            }
        }

        $insert = $this->model->create($input);
        if (!$insert) {
            return redirect()->route('admin.v2.apartments.insert')->with('error', 'Thêm căn hộ không thành công!');
        }
        return redirect()->route('admin.v2.apartments.index', ['id' => $insert->id])->with('success', 'Thêm căn hộ thành công!');
    }

    public function update(ApartmentsRequest $request, $id)
    {
        $check_apartment = $this->model->checkApartment($id, $this->building_active_id);
        if (!$check_apartment) {
            return redirect()->route('admin.v2.apartments.edit')->with('error', 'Khu tòa nhà không có căn hộ hiện tại, căn hộ có thể thuộc khu tòa nhà khác');
        }
        $building_code = $this->modelBuildingPlace->getCode($this->building_active_id, $request->building_place_id);
        $input = $request->only(['building_id', 'name', 'description', 'floor', 'status', 'area', 'building_place_id', 'code', 'code_customer', 'name_customer', 'code_electric', 'code_water']);
        $input['updated_by'] = auth()->user()->id;
        if (!$building_code) {
            return redirect()->route('admin.v2.apartments.edit')->with('error', 'Mã tòa nhà ở khu tòa nhà hiện tại không đúng');
        }
        $check_duplicate = $this->model->find_check_duplicate_ByName_v3($this->building_active_id, $request->building_place_id, $input['name'], $id);
        if ($check_duplicate && $check_duplicate->id != $id) {
            return redirect()->route('admin.v2.apartments.index')->with('error', 'Tên căn hộ này đã tồn tại');
        }
        $check_code_duplicate = $this->model->findCheckDuplicateByCode($this->building_active_id, $input['code'], $id);
        if ($check_code_duplicate && $check_code_duplicate->id != $id) {
            return redirect()->route('admin.v2.apartments.index')->with('error', 'Mã căn hộ này đã tồn tại');
        }

        if (isset($input['code_customer'])) {
            $check_code_duplicate = $this->model->checkExitByCode($this->building_active_id, $input['code_customer'], $id);
            if ($check_code_duplicate) {
                return redirect()->route('admin.v2.apartments.index')->with('error', 'Mã khách hàng này đã tồn tại');
            }
        }

        if (isset($input['code_electric'])) {
            $check_code_duplicate = $this->model->checkExitByCode($this->building_active_id, $input['code_electric'], $id);
            if ($check_code_duplicate) {
                return redirect()->route('admin.v2.apartments.index')->with('error', 'Mã công tơ điện này đã tồn tại');
            }
        }

        if (isset($input['code_water'])) {
            $check_code_duplicate = $this->model->checkExitByCode($this->building_active_id, $input['code_water'], $id);
            if ($check_code_duplicate) {
                return redirect()->route('admin.v2.apartments.index')->with('error', 'Mã công tơ nước này đã tồn tại');
            }
        }

        if (isset($input['area'])) {
            $apartment_service = ApartmentServicePrice::whereHas('service', function ($query) {
                $query->where('type', 2);
            })->where('bdc_apartment_id', $id)->get();
            foreach ($apartment_service as $key => $value) {
                $value->price = @$value->service->servicePriceDefault->price * $input['area'];
                $value->save();
            }
        }

        $update = $this->model->update($input, $id);
        Cache::store('redis')->forget(env('REDIS_PREFIX') . 'get_detail_apartmentById_' . $id);
        if (!$update) {
            return redirect()->route('admin.v2.apartments.edit')->with('error', 'Cập nhật căn hộ không thành công!');
        }
        Cache::store('redis')->forget(env('REDIS_PREFIX') . 'get_detail_apartmentById_' . $id);
        return redirect()->route('admin.v2.apartments.index')->with('success', 'Cập nhật căn hộ thành công!');
    }

    public function ajaxGetSelectApartment(Request $request)
    {

        if ($request->search) {
            $where[] = ['name', 'like', '%' . $request->search . '%'];
            return response()->json($this->model->searchByAll(['where' => $where], $this->building_active_id));
        }
        return response()->json($this->model->searchByAll(['select' => ['id', 'name']], $this->building_active_id));
    }

    public function ajaxGetSelectApartmentv2(Request $request)
    {
        if ($request->search) {
            $where[] = ['name', 'like', '%' . $request->search . '%'];
            return response()->json($this->model->searchByAll(['where' => $where], $this->building_active_id, $request->place_id));
        }
        return response()->json($this->model->searchByAll(['select' => ['id', 'name']], $this->building_active_id, $request->place_id));
    }

    public function ajaxGetSelectBuildingPlace(Request $request)
    {

        if ($request->search) {
            $where[] = ['name', 'like', '%' . $request->search . '%'];
            return response()->json($this->modelBuildingPlace->searchByAll(['where' => $where], $this->building_active_id));
        }
        return response()->json($this->modelBuildingPlace->searchByAll(['select' => ['id', 'name', 'code']], $this->building_active_id));
    }

    public function ajaxGetApartmentInGroup(Request $request)
    {
        $apartment_group_id = $request->apartment_group_id;
        if ($request->search) {
            $where[] = ['name', 'like', '%' . $request->search . '%'];
            return response()->json($this->model->searchByApartmentInGroup(['where' => $where], $this->building_active_id, $apartment_group_id));
        }
        return response()->json($this->model->searchByApartmentInGroup(['select' => ['id', 'name',]], $this->building_active_id, $apartment_group_id));
    }

    public function ajaxGetSelectResident(Request $request)
    {

        if ($request->search) {
            return response()->json($this->modelUserProfile->searchByRelationshipV2($request->search, $this->building_active_id));
        }
        return response()->json($this->modelUserProfile->searchByRelationshipV2('', $this->building_active_id));
    }

    public function ajaxGetCustomer(Request $request)
    {
        $input = $request->all();
        $customer = CustomersRespository::findApartmentIdV2($input['apartment_id'], 0);
        if (!$customer) {
            return $this->responseSuccess([
                'customer_name' => ''
            ]);
        }
        $customer_name = @$customer->user_info_first->full_name;
        return $this->responseSuccess([
            'customer_name' => $customer_name,
            'email' => @$customer->user_info_first->email_contact,
            'phone' => @$customer->user_info_first->phone_contact,
            'ma_khach_hang' => @$customer->bdcApartment->code_customer,
            'ten_khach_hang' => @$customer->bdcApartment->name_customer,
        ]);
    }

    public function destroy($id)
    {
        $this->model->findById($id)->update(['deleted_by' => auth()->user()->id]);
        $this->model->delete(['id' => $id]);
        UserApartments::whereIn('apartment_id', [$id])->doesnthave('debit_v2')->delete();
        Cache::store('redis')->forget(env('REDIS_PREFIX') . 'get_detail_apartmentById_' . $id);
        return redirect()->route('admin.v2.apartments.index')->with('success', 'Xóa căn hộ thành công!');
    }

    public function action(Request $request)
    {
        $method = $request->input('method', '');
        if ($method == 'per_page') {
            $this->per_page($request);
            return back();
        } else if ($method == 'restore_apartment') {
            $this->model->restoreSelects($request);
            $this->modelCustomers->restoreCusNew($request->ids);
            return back()->with('success', 'Khôi phục căn hộ thành công!');
        } else {
            $this->model->deleteSelects($request);
            //chỉnh sửa bởi duytuit ngày 07/07/2020
            if (Auth::user()->isadmin == 1) {
                UserApartments::whereIn('apartment_id', $request->ids)->delete();
                return back()->with('success', 'Xóa căn hộ thành công!');
            } else {
                UserApartments::whereIn('apartment_id', $request->ids)->doesnthave('debit_v2')->delete();
                return back()->with('success', 'Xóa căn hộ thành công!');
            }
        }

    }

    public function per_page($request)
    {
        $per_page = $request->input('per_page', 20);

        Cookie::queue('per_page', $per_page, 60 * 24 * 30);

        return true;
    }

    public function createFile(Request $request, $id)
    {
        $checkFile = $this->modelSystemFiles->checkFile($request, 'file_apartment');
        if ($checkFile['status'] == 'NOT_OK') {
            return redirect()->route('admin.v2.apartments.edit', ['id' => $id])->with('error', $checkFile['error']);
        }
        $data = [
            'building_id' => $this->building_active_id,
            'name' => $request->name,
            'description' => $request->description ? $request->description : '',
            'type' => $checkFile['data']['type'],
            'url' => $checkFile['data']['url'],
            'model_type' => 'apartment',
            'model_id' => $id,
            'status' => 0
        ];
        $insertFile = $this->modelSystemFiles->create($data);
        if (!$insertFile) {
            return redirect()->route('admin.v2.apartments.edit', ['id' => $id])->with('error', 'Thêm file không thành công!');
        }
        return redirect()->route('admin.v2.apartments.edit', ['id' => $id])->with('success', 'Cập nhật file thành công!');
    }

    public function indexImport()
    {

        $data['meta_title'] = 'import file apartment';
        $data['messages'] = json_decode(Session::get('messages'), true);
        //        dd($data['messages']);
        $data['error_data'] = Session::get('error_data');
        return view('apartments.v2.import', $data);
    }

    public function importFileApartment(Request $request)
    {
        set_time_limit(0);
        $file = $request->file('file_import');

        if (!$file) return redirect()->route('admin.customers.index_import')->with('warning', 'Chưa có file upload!');

        $path = $file->getRealPath();

        $excel_data = Excel::load($path)->get();

        storage_path('upload', $file->getClientOriginalName());

        $buildingId = $this->building_active_id;

        $data_list_error = array();

        if ($excel_data->count()) {

            foreach ($excel_data as $content) {
                if (empty($content->index) ||
                    empty($content->place) ||
                    empty($content->floor) ||
                    empty($content->name) ||
                    empty($content->area) ||
                    empty($content->code)
                ) {
                    //     $new_content = $content->toArray();
                    //     $new_content['message'] = 'hãy kiểm tra lại các trường yêu cầu bắt buộc';
                    // array_push($data_list_error,$new_content);
                    continue;
                }

                // check is number

                if (preg_match('/\d/', $content->index) !== 1) { // không phải là kiểu số hoặc nhỏ hơn 0
                    $new_content = $content->toArray();
                    $new_content['message'] = $content->index . '| không phải là kiểu số nguyên';
                    array_push($data_list_error, $new_content);
                    continue;
                }

                $check_place = $this->modelBuildingPlace->findByCode($content->place, $buildingId); // is null : là mã tòa nhà không có trên hệ thống

                if (!$check_place) {
                    $new_content = $content->toArray();
                    $new_content['message'] = $content->place . '| mã tòa nhà không có trên hệ thống';
                    array_push($data_list_error, $new_content);
                    continue;
                }

                // check is number floor

                //  if(preg_match('/\d/', $content->floor) !== 1) { // không phải là kiểu số hoặc nhỏ hơn 0
                //     $new_content = $content->toArray();
                //     $new_content['message'] = $content->floor.'| không phải là kiểu số nguyên';
                //     array_push($data_list_error,$new_content);
                //     continue;
                // }

                $check_duplicate = $this->model->findByName_v3($buildingId, $check_place->id, $content->name);// is not null : là tên căn hộ này đã có trên hệ thống

                if ($check_duplicate) {
                    $new_content = $content->toArray();
                    $new_content['message'] = $content->floor . '| tên căn hộ này đã tồn tại trên hệ thống';
                    array_push($data_list_error, $new_content);
                    continue;
                }

                // check is decimal area

                if (preg_match('/\d/', $content->area) !== 1) { // không phải là kiểu số thập phân hoặc nhỏ hơn 0
                    $new_content = $content->toArray();
                    $new_content['message'] = $content->area . '| diện tích không đúng định dạng 0.00';
                    array_push($data_list_error, $new_content);
                    continue;
                }

                $check_code_duplicate = $this->model->findByCode($buildingId, $content->code);// is not null : là mã căn hộ này đã có trên hệ thống

                if ($check_code_duplicate) {
                    $new_content = $content->toArray();
                    $new_content['message'] = $content->code . '| mã căn hộ này đã tồn tại trên hệ thống';
                    array_push($data_list_error, $new_content);
                    continue;
                }

                // check is number

                if (preg_match('/\d/', $content->status) !== 1 && !in_array($content->status, [0, 1, 2, 3 ,4,5])) { // phải là kiểu số nằm trong khoảng [0,1,2,3]
                    $new_content = $content->toArray();
                    $new_content['message'] = $content->status . '| không phải là kiểu số nguyên';
                    array_push($data_list_error, $new_content);
                    continue;
                }

                // check code customer

                if (!empty($content->code_customer)) {

                    $check_code_customer = $this->model->findByCodeCustomer($buildingId, $content->code_customer); // is not null : là mã khách hàng này đã có trên hệ thống
                    if ($check_code_customer) {
                        $new_content = $content->toArray();
                        $new_content['message'] = $content->code_customer . '| Mã khách hàng đã tồn tại trên hệ thống';
                        array_push($data_list_error, $new_content);
                        continue;
                    }

                }


                try {
                    DB::beginTransaction();
                    $this->model->create([
                        'building_id' => $buildingId,
                        'name' => $content->name,
                        'description' => $content->description,
                        'floor' => $content->floor,
                        'status' => $content->status,
                        'area' => $content->area,
                        'building_place_id' => $check_place->id,
                        'code' => $content->code,
                        'code_customer' => @$content->code_customer,
                        'name_customer' => @$content->name_customer,
                        'created_by' => auth()->user()->id,
                    ]);
                    $new_content = $content->toArray();
                    $new_content['message'] = 'thêm mới thành công';
                    array_push($data_list_error, $new_content);
                    DB::commit();
                } catch (\Exception $e) {
                    DB::rollBack();
                    $new_content = $content->toArray();
                    $new_content['message'] = $e->getMessage();
                    array_push($data_list_error, $new_content);
                    continue;
                }
            }
        }

        if ($data_list_error) {
            $result = Excel::create('Kết quả Import', function ($excel) use ($data_list_error) {

                $excel->setTitle('Kết quả Import');
                $excel->sheet('Kết quả Import', function ($sheet) use ($data_list_error) {
                    $row = 1;
                    $sheet->row($row, [
                        'Index',
                        'Place',
                        'Floor',
                        'Name',
                        'Area',
                        'Description',
                        'Status',
                        'code',
                        'Message'
                    ]);

                    foreach ($data_list_error as $key => $value) {
                        $row++;
                        $sheet->row($row, [
                            isset($value['index']) ? $value['index'] : '',
                            isset($value['place']) ? $value['place'] : '',
                            isset($value['floor']) ? $value['floor'] : '',
                            isset($value['name']) ? $value['name'] : '',
                            isset($value['area']) ? $value['area'] : '',
                            isset($value['description']) ? $value['description'] : '',
                            isset($value['status']) ? $value['status'] : '',
                            isset($value['code']) ? $value['code'] : '',
                            $value['message'],
                        ]);
                        if (isset($value['message']) && $value['message'] == 'thêm mới thành công') {
                            $sheet->cells('I' . $row, function ($cells) {
                                $cells->setBackground('#23fa43');
                            });
                        }
                        if (isset($value['message']) && $value['message'] != 'thêm mới thành công') {
                            $sheet->cells('I' . $row, function ($cells) {
                                $cells->setBackground('#FC2F03');
                            });
                        }
                    }
                });
            })->store('xlsx', storage_path('exports/'));
            ob_end_clean();
            $file = storage_path('exports/' . $result->filename . '.' . $result->ext);
            return response()->download($file)->deleteFileAfterSend(true);

        }
    }

    public function importFileUpdateApartment(Request $request)
    {
        set_time_limit(0);
        $file = $request->file('file_import');

        if (!$file) return redirect()->route('admin.customers.index_import')->with('warning', 'Chưa có file upload!');

        $path = $file->getRealPath();

        $excel_data = Excel::load($path)->get();

        storage_path('upload', $file->getClientOriginalName());

        $buildingId = $this->building_active_id;

        $data_list_error = array();

        if ($excel_data->count()) {

            foreach ($excel_data as $content) {

                // check is number floor

                if (!empty($content->ma_dinh_danh) && preg_match('/\d/', $content->ma_dinh_danh) !== 1) { // không phải là kiểu số hoặc nhỏ hơn 0
                    $new_content = $content->toArray();
                    $new_content['message'] = $content->ma_dinh_danh . '| mã định danh không phải là kiểu số nguyên';
                    array_push($data_list_error, $new_content);
                    continue;
                }


                if (!empty($content->ma_toa)) {
                    $check_place = $this->modelBuildingPlace->findByCode($content->ma_toa, $buildingId); // is null : là mã tòa nhà không có trên hệ thống
                    if (!$check_place) {
                        $new_content = $content->toArray();
                        $new_content['message'] = $content->ma_toa . '| mã tòa nhà không có trên hệ thống';
                        array_push($data_list_error, $new_content);
                        continue;
                    }
                }

                //  // check is number floor

                //  if(!empty($content->tang) && preg_match('/\d/', $content->tang) !== 1) { // không phải là kiểu số hoặc nhỏ hơn 0
                //     $new_content = $content->toArray();
                //     $new_content['message'] = $content->tang.'| không phải là kiểu số nguyên';
                //     array_push($data_list_error,$new_content);
                //     continue;
                // }

                if (isset($check_place) && !empty($content->ten_can_ho)) {
                    $check_duplicate = $this->model->find_check_duplicate_ByName_v3($buildingId, $check_place->id, $content->ten_can_ho, $content->ma_dinh_danh);// is not null : là tên căn hộ này đã có trên hệ thống

                    if ($check_duplicate) {
                        $new_content = $content->toArray();
                        $new_content['message'] = $content->ten_can_ho . '| tên căn hộ này đã tồn tại trên hệ thống';
                        array_push($data_list_error, $new_content);
                        continue;
                    }
                }

                // check is decimal area

                if (!empty($content->dien_tichm2) && preg_match('/\d/', $content->dien_tichm2) !== 1) { // không phải là kiểu số thập phân hoặc nhỏ hơn 0
                    $new_content = $content->toArray();
                    $new_content['message'] = $content->dien_tichm2 . '| diện tích không đúng định dạng 0.00';
                    array_push($data_list_error, $new_content);
                    continue;
                }

                if (!empty($content->ma_ho)) {
                    $check_code_duplicate = $this->model->findCheckDuplicateByCode($buildingId, $content->ma_ho, $content->ma_dinh_danh);// is not null : là mã căn hộ này đã có trên hệ thống

                    if ($check_code_duplicate) {
                        $new_content = $content->toArray();
                        $new_content['message'] = $content->ma_ho . '| mã căn hộ này đã tồn tại trên hệ thống';
                        array_push($data_list_error, $new_content);
                        continue;
                    }
                }

                // check status

                if (!empty($content->trang_thai)) {

                    if (!in_array($content->trang_thai, [0, 1, 2, 3,4,5])) {
                        $new_content = $content->toArray();
                        $new_content['message'] = $content->trang_thai . '| không phải là kiểu số nguyên';
                        array_push($data_list_error, $new_content);
                        continue;
                    }

                }

                // check code customer

                if (!empty($content->ma_khach_hang)) {

                    $check_code_customer = $this->model->checkExitByCode($buildingId, $content->ma_khach_hang, $content->ma_dinh_danh ?? null); // is not null : là mã khách hàng này đã có trên hệ thống
                    if ($check_code_customer) {
                        $new_content = $content->toArray();
                        $new_content['message'] = $content->ma_khach_hang . '| Mã khách hàng đã tồn tại trên hệ thống';
                        array_push($data_list_error, $new_content);
                        continue;
                    }
                }

                // check code electric

                if (!empty($content->ma_cong_to_dien)) {

                    $check_code_customer = $this->model->checkExitByCode($buildingId, $content->ma_cong_to_dien, $content->ma_dinh_danh ?? null); // is not null : là công tơ điện này đã có trên hệ thống
                    if ($check_code_customer) {
                        $new_content = $content->toArray();
                        $new_content['message'] = $content->ma_cong_to_dien . '| Mã công tơ điện đã tồn tại trên hệ thống';
                        array_push($data_list_error, $new_content);
                        continue;
                    }
                }

                // check code water

                if (!empty($content->ma_cong_to_nuoc)) {

                    $check_code_customer = $this->model->checkExitByCode($buildingId, $content->ma_cong_to_nuoc, $content->ma_dinh_danh ?? null); // is not null : là công tơ nước này đã có trên hệ thống
                    if ($check_code_customer) {
                        $new_content = $content->toArray();
                        $new_content['message'] = $content->ma_cong_to_nuoc . '| Mã công tơ nước đã tồn tại trên hệ thống';
                        array_push($data_list_error, $new_content);
                        continue;
                    }
                }

                try {
                    DB::beginTransaction();
                    $apartment = null;
                    if ($content->ma_dinh_danh) {  // cập nhật
                        $apartment = $this->model->checkApartment($content->ma_dinh_danh, $buildingId);
                        if ($apartment) {
                            $apartment_service = ApartmentServicePrice::whereHas('service', function ($query) {
                                $query->where('type', 2);
                            })->where('bdc_apartment_id', $apartment->id)->get();
                            if ($apartment_service) {
                                foreach ($apartment_service as $key => $value) {
                                    $value->price = @$value->service->servicePriceDefault->price * $content->dien_tichm2;
                                    $value->save();
                                }
                            }
                            $apartment->name = !empty($content->ten_can_ho) ? $content->ten_can_ho : $apartment->name;
                            $apartment->description = !empty($content->mo_ta) ? $content->mo_ta : $apartment->description;
                            $apartment->floor = !empty($content->tang) ? $content->tang : $apartment->floor;
                            $apartment->status = !empty($content->trang_thai) ? $content->trang_thai : $apartment->status;
                            $apartment->area = !empty($content->dien_tichm2) ? $content->dien_tichm2 : $apartment->area;
                            $apartment->building_place_id = !empty($check_place) ? $check_place->id : $apartment->building_place_id;
                            $apartment->code = !empty($content->ma_ho) ? $content->ma_ho : $apartment->code;
                            $apartment->code_customer = !empty($content->ma_khach_hang) ? $content->ma_khach_hang : $apartment->code_customer;
                            $apartment->name_customer = !empty($content->ten_khach_hang) ? $content->ten_khach_hang : $apartment->name_customer;
                            $apartment->code_electric = !empty($content->ma_cong_to_dien) ? $content->ma_cong_to_dien : $apartment->code_electric;
                            $apartment->code_water = !empty($content->ma_cong_to_nuoc) ? $content->ma_cong_to_nuoc : $apartment->code_water;
                            $apartment->updated_by = auth()->user()->id;
                            $apartment->save();
                        }
                    } else { // thêm mới

                        // check is number tang

                        // if (preg_match('/\d/', $content->tang) !== 1) { // không phải là kiểu số hoặc nhỏ hơn 0
                        //     $new_content = $content->toArray();
                        //     $new_content['message'] = $content->tang . '| không phải là kiểu số nguyên';
                        //     array_push($data_list_error, $new_content);
                        //     continue;
                        // }

                        $check_duplicate = $this->model->findByName_v3($buildingId, $check_place->id, $content->ten_can_ho); // is not null : là tên căn hộ này đã có trên hệ thống

                        if ($check_duplicate) {
                            $new_content = $content->toArray();
                            $new_content['message'] = $content->ten_can_ho . '| tên căn hộ này đã tồn tại trên hệ thống';
                            array_push($data_list_error, $new_content);
                            continue;
                        }

                        // check is decimal area

                        if (preg_match('/\d/', $content->dien_tichm2) !== 1) { // không phải là kiểu số thập phân hoặc nhỏ hơn 0
                            $new_content = $content->toArray();
                            $new_content['message'] = $content->dien_tichm2 . '| diện tích không đúng định dạng 0.00';
                            array_push($data_list_error, $new_content);
                            continue;
                        }

                        $check_code_duplicate = $this->model->findByCode($buildingId, $content->ma_ho);// is not null : là mã căn hộ này đã có trên hệ thống

                        if ($check_code_duplicate) {
                            $new_content = $content->toArray();
                            $new_content['message'] = $content->ma_ho . '| mã căn hộ này đã tồn tại trên hệ thống';
                            array_push($data_list_error, $new_content);
                            continue;
                        }

                        $apartment = $this->model->create([
                            'building_id' => $buildingId,
                            'name' => $content->ten_can_ho,
                            'description' => $content->mo_ta,
                            'floor' => $content->tang,
                            'status' => $content->trang_thai,
                            'area' => $content->dien_tichm2,
                            'building_place_id' => $check_place->id,
                            'code' => $content->ma_ho,
                            'code_customer' => @$content->ma_khach_hang,
                            'name_customer' => @$content->ten_khach_hang,
                            'code_electric' => @$content->ma_cong_to_dien,
                            'code_water' => @$content->ma_cong_to_nuoc,
                            'created_by' => auth()->user()->id,
                        ]);

                    }
                    if ($apartment) {
                        $new_content = $content->toArray();
                        $new_content['message'] = 'cập nhật thành công';
                        array_push($data_list_error, $new_content);
                    } else {
                        $new_content = $content->toArray();
                        $new_content['message'] = 'cập nhật thất bại';
                        array_push($data_list_error, $new_content);
                    }

                    DB::commit();
                } catch (\Exception $e) {
                    DB::rollBack();
                    $new_content = $content->toArray();
                    $new_content['message'] = $e->getMessage();
                    array_push($data_list_error, $new_content);
                    continue;
                }
            }
        }

        if ($data_list_error) {
            $result = Excel::create('Kết quả Import', function ($excel) use ($data_list_error) {

                $excel->setTitle('Kết quả Import');
                $excel->sheet('Kết quả Import', function ($sheet) use ($data_list_error) {
                    $row = 1;
                    $sheet->row($row, [
                        'Mã định danh',
                        'Tên căn hộ',
                        'Mã hộ',
                        'Chủ hộ',
                        'Tầng',
                        'Diện tích(/m2)',
                        'Mô tả',
                        'Trạng thái',
                        'Mã tòa',
                        'Mã Khách hàng',
                        'Tên Khách hàng',
                        'Mã công tơ điện',
                        'Mã công tơ nước',
                        'Message'
                    ]);

                    foreach ($data_list_error as $key => $value) {
                        $row++;
                        $sheet->row($row, [
                            isset($value['ma_dinh_danh']) ? $value['ma_dinh_danh'] : '',
                            isset($value['ten_can_ho']) ? $value['ten_can_ho'] : '',
                            isset($value['ma_ho']) ? $value['ma_ho'] : '',
                            isset($value['chu_ho']) ? $value['chu_ho'] : '',
                            isset($value['tang']) ? $value['tang'] : '',
                            isset($value['dien_tichm2']) ? $value['dien_tichm2'] : '',
                            isset($value['mo_ta']) ? $value['mo_ta'] : '',
                            isset($value['trang_thai']) ? $value['trang_thai'] : '',
                            isset($value['ma_toa']) ? $value['ma_toa'] : '',
                            isset($value['ma_khach_hang']) ? $value['ma_khach_hang'] : '',
                            isset($value['ten_khach_hang']) ? $value['ten_khach_hang'] : '',
                            isset($value['ma_cong_to_dien']) ? $value['ma_cong_to_dien'] : '',
                            isset($value['ma_cong_to_nuoc']) ? $value['ma_cong_to_nuoc'] : '',
                            $value['message'],
                        ]);
                        if (isset($value['message']) && $value['message'] == 'cập nhật thành công') {
                            $sheet->cells('N' . $row, function ($cells) {
                                $cells->setBackground('#23fa43');
                            });
                        }
                        if (isset($value['message']) && $value['message'] != 'cập nhật thành công') {
                            $sheet->cells('N' . $row, function ($cells) {
                                $cells->setBackground('#FC2F03');
                            });
                        }
                    }
                });
            })->store('xlsx', storage_path('exports/'));
            $file = storage_path('exports/' . $result->filename . '.' . $result->ext);
            return response()->download($file)->deleteFileAfterSend(true);

        }
    }

    public function download()
    {
        $file = public_path() . '/downloads/apartment_file_inport.xlsx';
        return response()->download($file);
    }

    public function downloadFileUpdate()
    {
        $file = public_path() . '/downloads/file_import_update_aparments.xlsx';
        return response()->download($file);
    }

    public function export(Request $request)
    {
        $apartments = $this->model->getDataExport($this->building_active_id, $request);

        $building_places = $this->modelBuildingPlace->getDataExport(['id', 'name', 'email', 'mobile', 'address', 'description', 'status', 'code'], $this->building_active_id);

        try {
            $result = Excel::create('Danh_sach_can_ho' . date('d-m-Y-H-i-s', time()), function ($excel) use ($apartments, $building_places) {

                $excel->setTitle('Danh sách tòa nhà');
                $excel->sheet('Danh sách tòa nhà', function ($sheet) use ($building_places) {
                    $new_place = [];
                    foreach ($building_places as $key => $apt) {
                        $new_place[] = [
                            'STT' => $key + 1,
                            'Tên tòa nhà' => $apt->name ?? '',
                            'Mã tòa' => $apt->code ?? '',
                            'Email' => $apt->email ?? '',
                            'Mobile' => $apt->mobile ?? '',
                            'Trạng thái' => $apt->status ? 'Mở' : 'Đóng',
                            'Địa chỉ' => $apt->address ?? '',
                            'Mô tả' => $apt->description ?? '',
                        ];
                    }
                    $sheet->setAutoSize(true);

                    // data of excel
                    if ($new_place) {
                        $sheet->fromArray($new_place);
                    }
                    // add header
                    $sheet->cell('A1:H1', function ($cell) {
                        // change header color
                        $cell->setFontColor('#000000')
                            ->setBackground('#cecece')
                            ->setFontWeight('bold')
                            ->setFontSize(10)
                            ->setAlignment('center')
                            ->setValignment('center');
                    });
                });


                $excel->setTitle('Danh sách căn hộ');
                $excel->sheet('Danh sách căn hộ', function ($sheet) use ($apartments) {
                    $new_apartments = [];
                    // $status = [1 => 'Để không', 2 => 'Đang ở', 3 => 'Đang cho thuê', 4 => 'Muốn cho thuê'];
                    $status = [
                        0 => 'Để không',
                        1 => 'Cho thuê',
                        2 => 'Muốn cho thuê',
                        3 => 'Đang ở',
                        4 => 'Mới bàn giao',
                        5 => 'Đang cải tạo',
                    ];
                    foreach ($apartments as $key => $apt) {
                        $user = @$apt->bdcResident;
                        $building = @$apt->building;
                        $name = '';
                        if ($user) {
                            foreach ($user as $u) {
                                if ($u->type == 0) {
                                    if (@$u->user_info_first) {
                                        $name .= ', ' . @$u->user_info_first->full_name;
                                    } else {
                                        $name .= '';
                                    }
                                }
                            }
                        } else {
                            $name = '';
                        }

                        $new_apartments[] = [
                            'Mã định danh' => $apt->id,
                            'Tên căn hộ' => $apt->name ?? '',
                            'Mã hộ' => $apt->code ?? '',
                            'Chủ hộ' => trim($name, ', ') ?? '',
                            'Tầng' => (int)$apt->floor ?? '',
                            'Diện tích(/m2)' => $apt->area ?? '',
                            'Mô tả' => $apt->description ?? '',
                            'Trạng thái' => $status[$apt->status] ?? 'Để không',
                            'Tòa nhà' => @$apt->buildingPlace->name ?? '',
                            'Mã tòa' => @$apt->buildingPlace->code ?? '',
                            'Mã khách hàng' => @$apt->code_customer ?? '',
                            'Tên khách hàng' => @$apt->name_customer ?? '',
                            'Mã công tơ điện' => @$apt->code_electric ?? '',
                            'Mã công tơ nước' => @$apt->code_water ?? '',
                            'Ngày tạo' => @$apt->created_at,
                            'Người tạo' => @$apt->user_created_by->email,
                            'Ngày cập nhật gần nhất' => @$apt->updated_at,
                            'Người cập nhật gần nhất' => @$apt->user_updated_by->email
                        ];
                    }
                    $sheet->setAutoSize(true);

                    // data of excel
                    if ($new_apartments) {
                        $sheet->fromArray($new_apartments);
                    }
                    // add header
                    $sheet->cell('A1:R1', function ($cell) {
                        // change header color
                        $cell->setFontColor('#000000')
                            ->setBackground('#cecece')
                            ->setFontWeight('bold')
                            ->setFontSize(10)
                            ->setAlignment('center')
                            ->setValignment('center');
                    });
                });

            })->store('xlsx', storage_path('exports/'));
            ob_end_clean();
            $file = storage_path('exports/' . $result->filename . '.' . $result->ext);
            return response()->download($file)->deleteFileAfterSend(true);

        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }

    public function export_apartment_group(Request $request)
    {

        $apartments = Apartments::where('building_id', $this->building_active_id)->get();
        try {
            $result = Excel::create('Danh_sach_nhom_can_ho' . date('d-m-Y-H-i-s', time()), function ($excel) use ($apartments) {

                $excel->setTitle('Danh sách nhóm căn hộ');
                $excel->sheet('Danh sách nhóm căn hộ', function ($sheet) use ($apartments) {
                    $new_place = [];
                    $group_apartments = ApartmentGroup::where('bdc_building_id', $this->building_active_id)->get();
                    foreach ($apartments as $key => $apt) {
                        if ($group_apartments) {
                            foreach ($group_apartments as $key_1 => $value) {
                                if ($value->id == $apt->bdc_apartment_group_id) {
                                    $new_place[] = [
                                        'STT' => $key + 1,
                                        'Mã nhóm căn hộ' => $value->id ?? '',
                                        'Nhóm căn hộ' => $value->name ?? '',
                                        'Tên căn hộ' => $apt->name ?? '',
                                        'Mô tả' => $value->description ?? '',
                                    ];
                                } else {
                                    $new_place[] = [
                                        'STT' => $key + 1,
                                        'Mã nhóm căn hộ' => '',
                                        'Nhóm căn hộ' => '',
                                        'Tên căn hộ' => $apt->name ?? '',
                                        'Mô tả' => '',
                                    ];
                                }
                            }

                        }

                    }
                    $sheet->setAutoSize(true);

                    // data of excel
                    if ($new_place) {
                        $sheet->fromArray($new_place);
                    }
                    // add header
                    $sheet->cell('A1:E1', function ($cell) {
                        // change header color
                        $cell->setFontColor('#000000')
                            ->setBackground('#cecece')
                            ->setFontWeight('bold')
                            ->setFontSize(10)
                            ->setAlignment('center')
                            ->setValignment('center');
                    });
                });

            })->store('xlsx', storage_path('exports/'));
            $file = storage_path('exports/' . $result->filename . '.' . $result->ext);
            return response()->download($file)->deleteFileAfterSend(true);

        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }

    public function filterByName(Request $request)
    {
        $input = $request->all();
        $building_code = $this->modelBuilding->getCode($this->building_active_id);
        $name = $input["name"];
        $apartment = $this->model->findByName($name);
    }
}
