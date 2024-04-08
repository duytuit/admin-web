<?php

namespace App\Repositories\BdcReceipts;

use App\Models\Apartments\Apartments;
use App\Repositories\Apartments\ApartmentsRespository;
use App\Repositories\BdcDebitDetail\DebitDetailRepository;
use App\Repositories\Building\BuildingPlaceRepository;
use App\Repositories\Building\BuildingRepository;
use App\Repositories\Customers\CustomersRespository;
use App\Repositories\Eloquent\Repository;
use App\Repositories\PublicUsers\PublicUsersProfileRespository;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Exception;
use Illuminate\Support\Facades\DB;
use App\Repositories\Service\ServiceRepository;
use App\Models\Building\Building;
use App\Models\PaymentInfo\PaymentInfo;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Building\BuildingPlace;
use App\Models\Service\Service;

const BUILDING_USER = 1;
use PHPExcel_Cell_DataType;

class ReceiptRepository extends Repository
{
    const PHIEUTHU = 'phieu_thu';
    const PHIEUBAOCO = 'phieu_bao_co';
    const PHIEUKETOAN = 'phieu_ke_toan';
    const PHIEUTHU_TRUOC = 'phieu_thu_truoc';
    const PHIEUCHI = 'phieu_chi';
    const PHIEUCHIKHAC = 'phieu_chi_khac';
    const TIENMAT = 'tien_mat';
    const COMPLETED = 1;
    const NOTCOMPLETED = 0;

    const PHIEUTHU_KYQUY = 'phieu_thu_ky_quy';
    const PHIEUHOAN_KYQUY = 'phieu_hoan_ky_quy';
    const DEPOSIT = 'deposit';
    /**
     * Specify Model class name
     *
     * @return mixed
     */
    function model()
    {
        return \App\Models\BdcReceipts\Receipts::class;
    }

    public function getAllReceiptBuilding($perPage, $building)
    {
        if ($perPage != null) {
            return $this->model->withTrashed()->where(['bdc_building_id' => $building])->whereNull('feature')->orderBy('create_date', 'desc')->paginate($perPage);
        } else {
            return $this->model->withTrashed()->where(['bdc_building_id' => $building])->whereNull('feature')->orderBy('create_date', 'desc')->get();
        }
    }

    public function getAllReceiptBuildingKyQuy($perPage, $building)
    {
        if ($perPage != null) {
            return $this->model->withTrashed()->where(function($query) {
                return $query->where('type', self::PHIEUTHU_KYQUY)->orWhere('type', self::PHIEUHOAN_KYQUY);
            })->where(['bdc_building_id' => $building])->where('feature',self::DEPOSIT)->orderBy('create_date', 'desc')->paginate($perPage);
        } else {
            return $this->model->withTrashed()->where(function($query) {
                return $query->where('type', self::PHIEUTHU_KYQUY)->orWhere('type', self::PHIEUHOAN_KYQUY);
            })->where(['bdc_building_id' => $building])->where('feature',self::DEPOSIT)->orderBy('create_date', 'desc')->get();
        }
    }

    public function filterApartmentId($apartmentId)
    {
        return $this->model->where(['bdc_apartment_id' => $apartmentId, 'type' => self::PHIEUTHU_TRUOC, 'status' => self::NOTCOMPLETED])->orderBy('id', 'DESC')->get();
    }

    public function findBuildingApartmentId($buildingId, $apartmentId)
    {
        $startDate = Carbon::now()->startOfMonth();
        $endDate = Carbon::now()->endOfMonth();
        return $this->model
            ->where(['bdc_building_id' => $buildingId, 'bdc_apartment_id' => $apartmentId])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();
    }

    public function findReceiptById($id)
    {
        return $this->model->where('id', $id)->first();
    }

    public function findByIdIsNotComplete($id)
    {
        return $this->model->where(['id' => $id, 'type' => self::PHIEUTHU_TRUOC, 'status' => self::NOTCOMPLETED])->first();
    }

    public function filterReceiptDeposit($request,$building)
    {
        $response = $this->model->where(['bdc_building_id' => $building])
            ->where(function ($query) use ($request) {
                if (isset($request['receipt_code_type']) && $request['receipt_code_type'] != null) {
                    $query->where('type', $request['receipt_code_type']);
                }
            })
            ->where(function ($query) use ($request) {
                if (isset($request['type_payment']) && $request['type_payment'] != null) {
                    $query->where('type_payment', $request['type_payment']);
                }
            })
            ->Where(function ($query) use ($request) {
                if (isset($request['bdc_apartment_id']) && $request['bdc_apartment_id'] != null) {
                    $query->where('bdc_apartment_id', $request['bdc_apartment_id']);
                }
            })
            ->WhereHas('pubUser', function (Builder $query) use ($request) {
                if (isset($request['user_id_receipt_code'])  && $request['user_id_receipt_code'] != null) {
                    $query->where('email', 'like', '%' . $request['user_id_receipt_code'] . '%')
                        ->orWhere('receipt_code', 'like', '%' . $request['user_id_receipt_code'] . '%')
                        ->orWhere('customer_name', 'like', '%' . $request['user_id_receipt_code'] . '%');
                }
            })
            ->whereHas('apartment', function (Builder $query) use ($request) {
                if(isset($request['ip_place_id'])){
                    $query->where('building_place_id', $request['ip_place_id']);
                }
            })
            ->Where(function ($query) use ($request) {
                if (isset($request['from_date'])  && $request['from_date'] != null) {
                    $from_date = Carbon::parse($request['from_date'])->format('Y-m-d');
                    $query->whereDate('create_date', '>=', $from_date);
                }
            })
            ->Where(function ($query) use ($request) {
                if (isset($request['to_date'])  && $request['to_date'] != null) {
                    $to_date = Carbon::parse($request['to_date'])->format('Y-m-d');
                    $query->whereDate('create_date', '<=',  $to_date . " 23:59:59");
                }
            })
            ->Where(function ($query) use ($request) {
                if (isset($request['created_at_from_date'])  && $request['created_at_from_date'] != null) {
                    $created_at_from_date = Carbon::parse($request['created_at_from_date'])->format('Y-m-d');
                    $query->whereDate('created_at', '>=', $created_at_from_date);
                }
            })
            ->Where(function ($query) use ($request) {
                if (isset($request['created_at_to_date'])  && $request['created_at_to_date'] != null) {
                    $created_at_to_date = Carbon::parse($request['created_at_to_date'])->format('Y-m-d');
                    $query->whereDate('created_at', '<=', $created_at_to_date . " 23:59:59");
                }
            })
            ->Where(function ($query) use ($request) {
                if (isset($request['config_id'])  && $request['config_id'] != null) {
                    $query->where('config_id', $request['config_id']);
                }
            })
            ->where('feature',self::DEPOSIT);
            if($building == 17 || $building == 77 || $building == 111){
                $response->whereNull('config_type_payment'); // lây phiếu thu v1 cho tòa imperial plaza và phương đông
            }
            $response->orderBy('create_date', 'desc');
       
        return $response;
    }
    public function filterReceipt($request,  $building)
    {
        $response = $this->model->where(['bdc_building_id' => $building])
            ->where(function ($query) use ($request) {
                if (isset($request['receipt_code_type']) && $request['receipt_code_type'] != null) {
                    $query->where('type', $request['receipt_code_type']);
                }
            })
            ->where(function ($query) use ($request) {
                if (isset($request['type_payment']) && $request['type_payment'] != null) {
                    $query->where('type_payment', $request['type_payment']);
                }
            })
            ->Where(function ($query) use ($request) {
                if (isset($request['bdc_apartment_id']) && $request['bdc_apartment_id'] != null) {
                    $query->where('bdc_apartment_id', $request['bdc_apartment_id']);
                }
            })
            ->WhereHas('pubUser', function (Builder $query) use ($request) {
                if (isset($request['user_id_receipt_code'])  && $request['user_id_receipt_code'] != null) {
                    $query->where('email', 'like', '%' . $request['user_id_receipt_code'] . '%')
                        ->orWhere('receipt_code', 'like', '%' . $request['user_id_receipt_code'] . '%')
                        ->orWhere('customer_name', 'like', '%' . $request['user_id_receipt_code'] . '%');
                }
            })
            ->whereHas('apartment', function (Builder $query) use ($request) {
                if(isset($request['ip_place_id'])){
                    $query->where('building_place_id', $request['ip_place_id']);
                }
            })
            ->Where(function ($query) use ($request) {
                if (isset($request['from_date'])  && $request['from_date'] != null) {
                    $from_date = Carbon::parse($request['from_date'])->format('Y-m-d');
                    $query->whereDate('create_date', '>=', $from_date);
                }
            })
            ->Where(function ($query) use ($request) {
                if (isset($request['to_date'])  && $request['to_date'] != null) {
                    $to_date = Carbon::parse($request['to_date'])->format('Y-m-d');
                    $query->whereDate('create_date', '<=', $to_date . " 23:59:59");
                }
            })
            ->Where(function ($query) use ($request) {
                if (isset($request['created_at_from_date'])  && $request['created_at_from_date'] != null) {
                    $created_at_from_date = Carbon::parse($request['created_at_from_date'])->format('Y-m-d');
                    $query->whereDate('created_at', '>=', $created_at_from_date);
                }
            })
            ->Where(function ($query) use ($request) {
                if (isset($request['created_at_to_date'])  && $request['created_at_to_date'] != null) {
                    $created_at_to_date = Carbon::parse($request['created_at_to_date'])->format('Y-m-d');
                    $query->whereDate('created_at', '<=', $created_at_to_date . " 23:59:59");
                }
            })
            ->Where(function ($query) use ($request) {
                if (isset($request['config_id'])  && $request['config_id'] != null) {
                    $query->where('config_id', $request['config_id']);
                }
            })
            ->whereNull('feature');
            if($building == 17 || $building == 77 || $building == 111){
                $response->whereNull('config_type_payment'); // lây phiếu thu v1 cho tòa imperial plaza và phương đông
            }
            $response->orderBy('create_date', 'desc');
        return $response;
    }

    public function filterReceiptKyQuy($request, $perPage,  $building)
    {
        // DB::enableQueryLog();
        $response = $this->model->where(['bdc_building_id' => $building])
            ->where(function($query) {
                return $query->where('type', self::PHIEUTHU_TRUOC)->orWhere('type', self::PHIEUCHIKHAC);
            })
            ->where(function ($query) use ($request) {
                if (isset($request['receipt_code_type']) && $request['receipt_code_type'] != null) {
                    $query->where('type', $request['receipt_code_type']);
                }
            })
            ->where(function ($query) use ($request) {
                if (isset($request['type_payment']) && $request['type_payment'] != null) {
                    $query->where('type_payment', $request['type_payment']);
                }
            })
            ->Where(function ($query) use ($request) {
                if (isset($request['bdc_apartment_id']) && $request['bdc_apartment_id'] != null) {
                    $query->where('bdc_apartment_id', $request['bdc_apartment_id']);
                }
            })
            ->WhereHas('pubUser', function (Builder $query) use ($request) {
                if (isset($request['user_id_receipt_code'])  && $request['user_id_receipt_code'] != null) {
                    $query->where('email', 'like', '%' . $request['user_id_receipt_code'] . '%')
                        ->orWhere('receipt_code', 'like', '%' . $request['user_id_receipt_code'] . '%')
                        ->orWhere('customer_name', 'like', '%' . $request['user_id_receipt_code'] . '%');
                }
            })
            ->whereHas('apartment', function (Builder $query) use ($request) {
                if(isset($request['ip_place_id'])){
                    $query->where('building_place_id', $request['ip_place_id']);
                }
            })
            ->Where(function ($query) use ($request) {
                if (isset($request['from_date'])  && $request['from_date'] != null) {
                    $from_date = Carbon::parse($request['from_date'])->format('Y-m-d');
                    $query->whereDate('create_date', '>=', $from_date);
                }
            })
            ->Where(function ($query) use ($request) {
                if (isset($request['to_date'])  && $request['to_date'] != null) {
                    $to_date = Carbon::parse($request['to_date'])->format('Y-m-d');
                    $query->whereDate('create_date', '<=', $to_date);
                }
            })
            ->Where(function ($query) use ($request) {
                if (isset($request['created_at_from_date'])  && $request['created_at_from_date'] != null) {
                    $created_at_from_date = Carbon::parse($request['created_at_from_date'])->format('Y-m-d');
                    $query->whereDate('created_at', '>=', $created_at_from_date);
                }
            })
            ->Where(function ($query) use ($request) {
                if (isset($request['created_at_to_date'])  && $request['created_at_to_date'] != null) {
                    $created_at_to_date = Carbon::parse($request['created_at_to_date'])->format('Y-m-d');
                    $query->whereDate('created_at', '<=', $created_at_to_date);
                }
            })
            ->Where(function ($query) use ($request) {
                if (isset($request['config_id'])  && $request['config_id'] != null) {
                    $query->where('config_id', $request['config_id']);
                }
            })
            ->orderBy('create_date', 'desc')
            ->paginate($perPage);
        // dd(DB::getQueryLog());
        return $response;
    }

    public function excelReceiptIndex($building)
    {
        $receipt = $this->model->where(['bdc_building_id' => $building, "type_payment" => self::TIENMAT])->get();
        $result = Excel::create('Phiếu thu', function ($excel) use ($receipt) {
            $excel->setTitle('Phiếu thu');
            $excel->sheet('Phiếu thu', function ($sheet) use ($receipt) {
                $receipts = [];
                $row = 1;
                $sheet->row($row, [
                    'STT',
                    'Mã chứng từ',
                    'Căn hộ',
                    'Số tiền',
                    'Thời gian',
                    'Hình thức',
                    'Người nộp',
                ]);
                foreach ($receipt as $key => $value) {
                    $row++;
                    if ($value->type_payment == 'tien_mat') {
                        $status = 'Tiền mặt';
                    } elseif ($value->type_payment == 'chuyen_khoan') {
                        $status = 'Chuyển khoản';
                    } elseif ($value->type_payment == 'vi') {
                        $status = 'Ví';
                    } else {
                        $status = 'VNPay';
                    }
                    $sheet->row($row, [
                        ($key + 1),
                        @$value->receipt_code,
                        @$value->apartment->name,
                        @$value->cost,
                        date('d/m/Y', strtotime(@$value->created_at)),
                        @$status,
                        @$value->customer_name,
                    ]);
                }
            });
        })->store('xlsx',storage_path('exports/'));
$file     = storage_path('exports/'.$result->filename.'.'.$result->ext);
return response()->download($file)->deleteFileAfterSend(true);
             
    }
    public function filterReceiptDepositExcel($buildingId, $request)
    {
        $response = $this->filterReceiptDeposit($request,$buildingId)->get();

        $result = Excel::create('Ký quỹ', function ($excel) use ($response) {
            $excel->setTitle('Ký quỹ');
            $excel->sheet('Ký quỹ', function ($sheet) use ($response) {
                $receipts = [];
                $row = 1;
                $sheet->row($row, [
                    'STT',
                    'Mã chứng từ',
                    'Loại chứng từ',
                    'Danh mục',
                    'Căn hộ',
                    'Số tiền',
                    'Ngày lập',
                    'Ngày hạch toán',
                    'Hình thức',
                    'Người nộp',
                    'Nội dung',
                    'Người tạo',
                ]);
                foreach ($response as $key => $value) {
                    $row++;
                    if ($value->type_payment == 'tien_mat') {
                        $status = 'Tiền mặt';
                    } elseif ($value->type_payment == 'chuyen_khoan') {
                        $status = 'Chuyển khoản';
                    } elseif ($value->type_payment == 'vi') {
                        $status = 'Ví';
                    } else {
                        $status = 'VNPay';
                    }
                    if ($value->apartment) {
                        $aprtment_name = $value->apartment->name;
                    } else {
                        $aprtment_name = '';
                    }

                    // loại chứng từ

                    switch ($value->type){
                        
                    case 'phieu_thu_ky_quy':
                        $TypeReceipt = 'Phiếu thu ký quỹ';
                        break;
                    case 'phieu_hoan_ky_quy':
                        $TypeReceipt = 'Phiếu hoàn ký quỹ';
                        break;
                    default:
                         $TypeReceipt = 'Phiếu kế toán';
                    };
                    $data = [
                        ($key + 1)."",
                        @$value->receipt_code,
                        $TypeReceipt,
                        @$value->pubConfig->title == null ? "" : @$value->pubConfig->title,
                        $aprtment_name,
                        @$value->cost."",
                        date('d/m/Y', strtotime(@$value->created_at)),
                        @$value->create_date ? date('d/m/Y', strtotime(@$value->create_date)) : '--/--/----',
                        $status,
                        @$value->customer_name,
                        @$value->description,
                        @$value->pubUser->email
                    ];

                    $sheet->row($row, $data);
                    
                }
            });
        })->store('xlsx',storage_path('exports/'));
        $file     = storage_path('exports/'.$result->filename.'.'.$result->ext);
        return response()->download($file)->deleteFileAfterSend(true);
             
    }

    public function filterReceiptExcel($buildingId, $request)
    {
        $response = $this->filterReceipt($request,  $buildingId)->get();
        $result = Excel::create('Phiếu thu', function ($excel) use ($response) {
            $excel->setTitle('Phiếu thu');
            $excel->sheet('Phiếu thu', function ($sheet) use ($response) {
                $receipts = [];
                $row = 1;
                $sheet->row($row, [
                    'STT',
                    'Mã chứng từ',
                    'Loại chứng từ',
                    'Danh mục',
                    'Căn hộ',
                    'Số tiền',
                    'Ngày lập',
                    'Ngày hạch toán',
                    'Hình thức',
                    'Người nộp',
                    'Nội dung',
                    'Người tạo',
                ]);
                foreach ($response as $key => $value) {
                    $row++;
                    if ($value->type_payment == 'tien_mat') {
                        $status = 'Tiền mặt';
                    } elseif ($value->type_payment == 'chuyen_khoan') {
                        $status = 'Chuyển khoản';
                    } elseif ($value->type_payment == 'vi') {
                        $status = 'Ví';
                    } else {
                        $status = 'VNPay';
                    }
                    if ($value->apartment) {
                        $aprtment_name = $value->apartment->name;
                    } else {
                        $aprtment_name = '';
                    }

                    // loại chứng từ

                    switch ($value->type){
                        
                    case 'phieu_thu':
                        $TypeReceipt = 'Phiếu thu';
                        break;
                    case 'phieu_thu_truoc':
                        $TypeReceipt = 'Phiếu thu khác';
                        break;
                    case 'phieu_chi':
                        $TypeReceipt = 'Phiếu chi';
                        break;
                    case 'phieu_bao_co':
                        $TypeReceipt = 'Phiếu báo có';
                        break;
                    default:
                         $TypeReceipt = 'Phiếu kế toán';
                    };
                    $data = [
                        ($key + 1)."",
                        @$value->receipt_code,
                        $TypeReceipt,
                        @$value->pubConfig->title == null ? "" : @$value->pubConfig->title,
                        $aprtment_name,
                        @$value->cost."",
                        date('d/m/Y', strtotime(@$value->created_at)),
                        @$value->create_date ? date('d/m/Y', strtotime(@$value->create_date)) : '--/--/----',
                        $status,
                        @$value->customer_name,
                        @$value->description,
                        @$value->pubUser->email ?? @$value->pubUserInfo->email
                    ];

                    $sheet->row($row, $data);
                    
                }
            });
        })->store('xlsx',storage_path('exports/'));
$file     = storage_path('exports/'.$result->filename.'.'.$result->ext);
return response()->download($file)->deleteFileAfterSend(true);
             
    }
    public function filterReceiptExcelNew($buildingId, $request)
    {
        $rs = $this->cashBookMoneyNew($buildingId, $request)->get();
        $response = $this->cashBookMoneyNew($buildingId, $request)->where(function ($query) use ($request) {
            if (isset($request['from_date']) && $request['from_date'] != null && isset($request['to_date']) && $request['to_date'] != null) {
                $from_date = Carbon::parse($request['from_date'])->format('Y-m-d');
                $to_date = Carbon::parse($request['to_date'])->format('Y-m-d');
                $query->whereDate('create_date', '>=', $from_date);
                $query->whereDate('create_date', '<=', $to_date);
            }
        })->orderBy('create_date', 'DESC')->get();
        // Khởi tạo session PHP nếu chưa khởi tạo
        if (session_id() === '') {
            session_start();
        }
        $_SESSION['Building_name'] = $response[0]->building->name;
        $_SESSION['Building_address'] = $response[0]->building->address;
        $_SESSION['sumDauKy']=0;
        // $array = unserialize($strBillIds);
        $result = Excel::create('Phiếu thu', function ($excel) use ($response, $rs, $request) {
            $excel->setTitle('Phiếu thu');
            $excel->sheet('Phiếu thu', function ($sheet) use ($response, $rs, $request) {
                $receipts = [];
                $row = 14;
                // $sheet->row($row, [
                //     'Mã chứng từ',
                //     'THu',
                //     'chi',
                //     'dien giải',
                //     'thu',
                //     'chi',
                //     'ton',
                //     'Người tạo',
                // ]);
                $ton = 0;
                for ($i = count($rs) - 1; $i >= 0; $i--) {
                    if (($rs[$i]['type'] != "phieu_chi" && $rs[$i]['type'] != "phieu_chi_khac" && $rs[$i]['type'] != "phieu_hoan_ky_quy") && $rs[$i]['type_payment'] == ReceiptRepository::TIENMAT) {
                        $ton += $rs[$i]['cost'];
                        $rs[$i]['ton'] = $ton;
                    }
                    if (($rs[$i]['type'] == "phieu_chi" || $rs[$i]['type'] == "phieu_chi_khac" || $rs[$i]['type'] == "phieu_hoan_ky_quy") && $rs[$i]['type_payment'] == ReceiptRepository::TIENMAT) {
                        $ton -= $rs[$i]['cost'];
                        $rs[$i]['ton'] = $ton;
                    }
                    if (isset($request['from_date'])) {
                        $create_date1 = Carbon::parse($rs[$i]['create_date']);
                        $from_date1 = Carbon::parse($request['from_date']);
                        if ($create_date1->lt($from_date1)) {
                            $_SESSION['sumDauKy'] = $rs[$i]['ton'];
                        }
                    }
                    for ($j = 0; $j < count($response); $j++) {
                        if ($rs[$i]['receipt_code'] == $response[$j]['receipt_code']) {
                            $row++;
                            $response[$j]['ton'] = $rs[$i]['ton'];
                            $sheet->row($row, [
                                empty($response[$j]->create_date) ? date('d/m/Y', strtotime($response[$j]->created_at)) : date('d/m/Y', strtotime($response[$j]->create_date)),
                                ($response[$j]->type != "phieu_chi" && $response[$j]->type != "phieu_chi_khac" && $response[$j]->type != "phieu_hoan_ky_quy") && $response[$j]->type_payment == ReceiptRepository::TIENMAT ? $response[$j]->receipt_code : '',
                                ($response[$j]->type == "phieu_chi" || $response[$j]->type == "phieu_chi_khac" || $response[$j]->type == "phieu_hoan_ky_quy") && $response[$j]->type_payment == ReceiptRepository::TIENMAT ? $response[$j]->receipt_code : '',
                                $response[$j]->description,
                                ($response[$j]->type != "phieu_chi" && $response[$j]->type != "phieu_chi_khac" && $response[$j]->type != "phieu_hoan_ky_quy") && $response[$j]->type_payment == ReceiptRepository::TIENMAT ? $response[$j]->cost : '',
                                ($response[$j]->type == "phieu_chi" || $response[$j]->type == "phieu_chi_khac" || $response[$j]->type == "phieu_hoan_ky_quy") && $response[$j]->type_payment == ReceiptRepository::TIENMAT ? $response[$j]->cost : '',
                                $response[$j]['ton'],
                                @$response[$j]->apartment->name,
                                @$response[$j]->pubUser->email ?? null,
                            ]);
                            break;
                        }
                    }
                }
                $sheet->mergeCells('B12:C13');
                $sheet->getStyle('b12')->getAlignment()->setWrapText(true);
                $sheet->cells('b12', function ($cells) {
                    $cells->setFontSize(11);
                    $cells->setFontWeight('bold');
                    $cells->setValue('Số hiệu chứng từ');
                    $cells->setValignment('center');
                    $cells->setAlignment('center');
                    $cells->setBorder('thin', 'thin', 'thin', 'thin');
                });


                $sheet->cells('b14', function ($cells) {
                    $cells->setFontSize(11);
                    $cells->setFontWeight('bold');
                    $cells->setValue('Thu');
                    $cells->setAlignment('center');
                    $cells->setBorder('thin', 'thin', 'thin', 'thin');
                });


                $sheet->cells('c14', function ($cells) {
                    $cells->setFontSize(11);
                    $cells->setFontWeight('bold');
                    $cells->setValue('Chi');
                    $cells->setAlignment('center');
                    $cells->setBorder('thin', 'thin', 'thin', 'thin');
                });

                $sheet->mergeCells('D12:D14');
                $sheet->getStyle('D12')->getAlignment()->setWrapText(true);
                $sheet->cells('D12', function ($cells) {
                    $cells->setFontSize(11);
                    $cells->setFontWeight('bold');
                    $cells->setValue('Diễn giải');
                    $cells->setValignment('center');
                    $cells->setAlignment('center');
                    $cells->setBorder('thin', 'thin', 'thin', 'thin');
                });


                $sheet->mergeCells('E12:G13');
                $sheet->getStyle('E12')->getAlignment()->setWrapText(true);
                $sheet->cells('E12', function ($cells) {
                    $cells->setFontSize(11);
                    $cells->setFontWeight('bold');
                    $cells->setValue('Số tiền');
                    $cells->setValignment('center');
                    $cells->setAlignment('center');
                    $cells->setBorder('thin', 'thin', 'thin', 'thin');
                });

                $sheet->cells('E14', function ($cells) {
                    $cells->setFontSize(11);
                    $cells->setFontWeight('bold');
                    $cells->setValue('Thu');
                    $cells->setAlignment('center');
                    $cells->setBorder('thin', 'thin', 'thin', 'thin');
                });

                $sheet->cells('F14', function ($cells) {
                    $cells->setFontSize(11);
                    $cells->setFontWeight('bold');
                    $cells->setValue('Chi');
                    $cells->setAlignment('center');
                    $cells->setBorder('thin', 'thin', 'thin', 'thin');
                });

                $sheet->cells('G14', function ($cells) {
                    $cells->setFontSize(11);
                    $cells->setFontWeight('bold');
                    $cells->setValue('Tồn');
                    $cells->setAlignment('center');
                    $cells->setBorder('thin', 'thin', 'thin', 'thin');
                });


                $sheet->mergeCells('A12:A14');
                $sheet->getStyle('A12')->getAlignment()->setWrapText(true);
                $sheet->cells('A12', function ($cells) {
                    $cells->setFontSize(11);
                    $cells->setFontWeight('bold');
                    $cells->setValue('Ngày, tháng chứng từ');
                    $cells->setAlignment('center');
                    $cells->setBorder('thin', 'thin', 'thin', 'thin');
                });

                $sheet->mergeCells('A5:G5');

                $sheet->cells('A5', function ($cells) {
                    $cells->setFontSize(22);
                    $cells->setFontWeight('bold');
                    $cells->setValue('SỔ QUỸ TIỀN MẶT');
                    $cells->setAlignment('center');
                });

                $sheet->mergeCells('A7:G7');

                $sheet->cells('A7', function ($cells) {
                    $cells->setFontSize(11);
                    $cells->setFontWeight('Iatalic');
                    $cells->setValue('Ngày: ' . date('d/m/Y', strtotime(Carbon::now())));
                    $cells->setAlignment('center');
                });

                $sheet->mergeCells('A9:G9');

                $sheet->cells('A9', function ($cells) {
                    $cells->setFontSize(11);
                    $cells->setFontWeight('bold');
                    $cells->setValue('Loại quỹ: Tiền Việt Nam ');
                    $cells->setAlignment('center');
                });

                $sheet->cells('F10', function ($cells) {
                    $cells->setFontSize(11);
                    $cells->setFontWeight('Iatalic');
                    $cells->setValue('Số dư đầu kỳ ');
                });

                $sheet->cells('A2', function ($cells) {
                    $cells->setFontSize(11);
                    $cells->setFontWeight('bold');
                    $cells->setValue('Đơn vị:');
                });

                $sheet->cells('A3', function ($cells) {
                    $cells->setFontSize(11);
                    $cells->setFontWeight('bold');
                    $cells->setValue('Địa chỉ:');
                });

                $sheet->cells('B2', function ($cells) {
                    $cells->setFontSize(11);
                    $cells->setValue($_SESSION['Building_name']);
                });

                $sheet->cells('B3', function ($cells) {
                    $cells->setFontSize(11);
                    $cells->setValue($_SESSION['Building_address']);
                });
                $sheet->cells('G10', function ($cells) {
                    $cells->setValue($_SESSION['sumDauKy']);
                });
                unset($_SESSION['sumDauKy']);
                unset($_SESSION['Building_name']);
                unset($_SESSION['Building_address']);
                $sheet->setWidth(array(
                    'A'     =>  20,
                    'B'     =>  20,
                    'C'     =>  20,
                    'D'     =>  60,
                    'E'     =>  20,
                    'F'     =>  20,
                    'G'     =>  20,
                ));
            });
        })->store('xlsx',storage_path('exports/'));
$file     = storage_path('exports/'.$result->filename.'.'.$result->ext);
return response()->download($file)->deleteFileAfterSend(true);
             
    }

    public function filterReceiptExcelDetail($buildingId, $request, $debitDetailRepository)
    {
        $receipts = $this->filterReceipt($request,  $buildingId)->get();
        $result = Excel::create('Phiếu thu', function ($excel) use ($receipts, $debitDetailRepository) {
            $excel->setTitle('Phiếu thu');
            $excel->sheet('Phiếu thu', function ($sheet) use ($receipts, $debitDetailRepository) {
                $data = [];
                $content = [];
               
                foreach ($receipts as $key => $receipt) {
                    if ($receipt->type_payment == 'tien_mat') {
                        $status = 'PT';
                    } elseif ($receipt->type_payment == 'chuyen_khoan' || $receipt->type_payment == 'vi') {
                        $status = 'BC';
                    } else {
                        $status = 'VNPay';
                    }

                    $apartment = ApartmentsRespository::getInfoApartmentsById($receipt->bdc_apartment_id);
                    $aprtment_name = $apartment->name;
                    $buildingPlace = BuildingPlaceRepository::getInfoBuildingPlaceById($apartment->building_place_id);
                    $building = BuildingRepository::getInfoBuildingById($receipt->bdc_building_id);

                    $data = unserialize($receipt->data);
                    $customer = null;
                    $customerName = null;
                    $_maKhachHangNNC = null;
                    $apartment_id = null;
                    if ($data) {
                        foreach ($data as $_data) {
                            try {
                                $debitDetail = isset($_data['new_debit_id']) ? $debitDetailRepository->findDebitById($_data['new_debit_id']) :  $debitDetailRepository->filterServiceBillIdWithVersion($receipt->bdc_building_id, $_data["bill_id"], $_data["service_id"], $_data["version"]);
                            } catch (Exception $e) {
                                continue;
                            }
                            
                            if (!$debitDetail) {
                                continue;
                            }
                            if($apartment_id != $debitDetail->bdc_apartment_id) {
                                $customer = CustomersRespository::findApartmentIdV2($debitDetail->bdc_apartment_id,0);
                                $pubUserProfile =$customer ? PublicUsersProfileRespository::getInfoUserById($customer->user_info_id) : null;
                                $customerName = $customer ? @$pubUserProfile->full_name : "";
                                $_maKhachHangNNC = null;
                             }
                            $service =  Service::get_detail_bdc_service_by_bdc_service_id($debitDetail->bdc_service_id);
                            $serviceGroup = "";

                            if ($service) {
                                switch ($service->service_group) {
                                    case 2:
                                        $serviceGroup = "Phí thu hộ";
                                        break;
                                    case 3:
                                        $serviceGroup = "Phí chủ đầu tư";
                                        break;
                                    case 4:
                                        $serviceGroup = "Phí ban quản trị";
                                        break;
                                    default:
                                        $serviceGroup = "Phí công ty";
                                        break;
                                }
                            }
                            $cycleArr = explode('/', $debitDetail->cycle_name);
                            $cycleName = implode("0", array_reverse($cycleArr));
                            // Data
                            $loaiNK = $status;
                            $soChungTu =  $receipt->receipt_code;
                            $kyKeToan = $cycleName ?? $debitDetail->cycle_name;
                            $ngayLapPhieu = $receipt->created_at->format('d/m/Y');
                            $ngayNgayHachToan = @$receipt->create_date ? date('d/m/Y', strtotime(@$receipt->create_date)) : '--/--/----';
                            $dienGiai = $service->name . " " . $debitDetail->cycle_name;
                            $maKhachHangNNC = $_maKhachHangNNC;
                            $maNganHang = "";
                            $ctyCon = "";
                            $maPhongBan = "";
                            $maNhanVien = "";
                            $maPhi = $service->name;
                            $hopDong = "";
                            $sanPham = $apartment->code ?? null;
                            $block = $buildingPlace->code ?? null;
                            $duAn = $building->name;
                            $maThu = "";
                            $kheUoc = "";
                            $cpKhongHopLe = "";
                            $maTaiKhoan = "";
                            $tkDu = "";
                            $soTien = $debitDetail->paid;
                            $noCo = "";
                            $kyHieuHoaDon = "";
                            $ngayHoaDon = "";
                            $loaiThue = "";
                            $thueSuat = "";
                            $tienTruocThue = "";
                            $maSoHoaDon = "";
                            $nguoiNop_NhanTien = "";
                            $nguoiBanHang = "";
                            $phieuCanTru = "";
                            $maPhieuEApprove = "";
                            $ghiChu = $receipt->receipt_code;
                            $content[] = [
                                'Loại NK' => $loaiNK,
                                'Tên căn hộ' => $aprtment_name,
                                'Chủ hộ' => $customerName,
                                'Số chứng từ' => $soChungTu,
                                'Kỳ kế toán' => $kyKeToan,
                                'Ngày lập phiếu' => $ngayLapPhieu,
                                'Ngày hạch toán' => $ngayNgayHachToan,
                                'Diễn giải' => $dienGiai,
                                'Nội dung' => $receipt->description,
                                'Mã khách hàng-NCC' => $maKhachHangNNC,
                                'Mã ngân hàng' => $maNganHang,
                                'Cty Con - NH cho DXMB vay' => $ctyCon,
                                'Mã phòng ban' => $maPhongBan,
                                'Mã nhân viên' => $maNhanVien,
                                'Mã phí' => $maPhi,
                                'Hợp đồng' => $hopDong,
                                'Sản phẩm' => $sanPham,
                                'Block' => $block,
                                'Dự án' => $duAn,
                                'Mã thu' => $maThu,
                                'Khế ước' => $kheUoc,
                                'CP không hợp lệ' => $cpKhongHopLe,
                                'Mã tài khoản' => $maTaiKhoan,
                                'TK Dư' => $tkDu,
                                'Số tiền' => $soTien,
                                'Nợ có' => $noCo,
                                'Ký hiệu hóa đơn' => $kyHieuHoaDon,
                                'Ngày hóa đơn' => $ngayHoaDon,
                                'Loại thuế' => $loaiThue,
                                'Thuế suất' => $thueSuat,
                                'Tiền trước thuế' => $tienTruocThue,
                                'Mã số hóa đơn' => $maSoHoaDon,
                                'Người nộp/Nhận tiền' => $nguoiNop_NhanTien,
                                'Người bán hàng' => $nguoiBanHang,
                                'Phiếu cấn trừ' => $phieuCanTru,
                                'Mã chứng từ eApprove' => $maPhieuEApprove,
                                'Ghi chú' => $ghiChu,
                                'Nhóm dịch vụ' => $serviceGroup,
                                'receipt_type' => $receipt->type,
                                'Mô tả' => $debitDetail->title,
                            ];
                        }
                    }else{
                        if($apartment_id != $receipt->bdc_apartment_id) {
                            $customer = CustomersRespository::findApartmentIdV2($receipt->bdc_apartment_id,0);
                            $pubUserProfile =$customer ? PublicUsersProfileRespository::getInfoUserById($customer->user_info_id) : null;
                            $customerName = $customer ? @$pubUserProfile->full_name : "";
                            $_maKhachHangNNC = null;
                        }
                        $serviceGroup = "";

                        // Data
                        $loaiNK = $status;
                        $soChungTu =  $receipt->receipt_code;
                        $kyKeToan = date('Ym', strtotime(@$receipt->create_date));
                        $ngayLapPhieu = $receipt->created_at->format('d/m/Y');
                        $ngayNgayHachToan = @$receipt->create_date ? date('d/m/Y', strtotime(@$receipt->create_date)) : '--/--/----';
                        $dienGiai = $receipt->description;
                        $maKhachHangNNC = $_maKhachHangNNC;
                        $maNganHang = "";
                        $ctyCon = "";
                        $maPhongBan = "";
                        $maNhanVien = "";
                        $maPhi = '';
                        $hopDong = "";
                        $sanPham = $apartment->code ?? null;
                        $block = $buildingPlace->code ?? null;
                        $duAn = $building->name;
                        $maThu = "";
                        $kheUoc = "";
                        $cpKhongHopLe = "";
                        $maTaiKhoan = "";
                        $tkDu = "";
                        $soTien = $receipt->cost;
                        $noCo = "";
                        $kyHieuHoaDon = "";
                        $ngayHoaDon = "";
                        $loaiThue = "";
                        $thueSuat = "";
                        $tienTruocThue = "";
                        $maSoHoaDon = "";
                        $nguoiNop_NhanTien = "";
                        $nguoiBanHang = "";
                        $phieuCanTru = "";
                        $maPhieuEApprove = "";
                        $ghiChu = $receipt->receipt_code;
                        $content[] = [
                            'Loại NK' => $loaiNK,
                            'Tên căn hộ' => $aprtment_name,
                            'Chủ hộ' => $customerName,
                            'Số chứng từ' => $soChungTu,
                            'Kỳ kế toán' => $kyKeToan,
                            'Ngày lập phiếu' => $ngayLapPhieu,
                            'Ngày hạch toán' => $ngayNgayHachToan,
                            'Diễn giải' => $dienGiai,
                            'Nội dung' => $receipt->description,
                            'Mã khách hàng-NCC' => $maKhachHangNNC,
                            'Mã ngân hàng' => $maNganHang,
                            'Cty Con - NH cho DXMB vay' => $ctyCon,
                            'Mã phòng ban' => $maPhongBan,
                            'Mã nhân viên' => $maNhanVien,
                            'Mã phí' => $maPhi,
                            'Hợp đồng' => $hopDong,
                            'Sản phẩm' => $sanPham,
                            'Block' => $block,
                            'Dự án' => $duAn,
                            'Mã thu' => $maThu,
                            'Khế ước' => $kheUoc,
                            'CP không hợp lệ' => $cpKhongHopLe,
                            'Mã tài khoản' => $maTaiKhoan,
                            'TK Dư' => $tkDu,
                            'Số tiền' => $soTien,
                            'Nợ có' => $noCo,
                            'Ký hiệu hóa đơn' => $kyHieuHoaDon,
                            'Ngày hóa đơn' => $ngayHoaDon,
                            'Loại thuế' => $loaiThue,
                            'Thuế suất' => $thueSuat,
                            'Tiền trước thuế' => $tienTruocThue,
                            'Mã số hóa đơn' => $maSoHoaDon,
                            'Người nộp/Nhận tiền' => $nguoiNop_NhanTien,
                            'Người bán hàng' => $nguoiBanHang,
                            'Phiếu cấn trừ' => $phieuCanTru,
                            'Mã chứng từ eApprove' => $maPhieuEApprove,
                            'Ghi chú' => $ghiChu,
                            'Nhóm dịch vụ' => '',
                            'receipt_type' => $receipt->type,
                            'Mô tả' => $receipt->config_type_payment !=null ? 'v2':'',
                        ];
                    }
                }
                if ($content) {
                    // $sheet->fromArray($content);
                       $sheet->loadView('receipt._excelDetail', ["content" => $content]);
                }
           });
         })->store('xlsx',storage_path('exports/'));
        $file     = storage_path('exports/'.$result->filename.'.'.$result->ext);
        return response()->download($file)->deleteFileAfterSend(true);
             

    }

    public function filterReceiptExcelDetail_v2($buildingId, $request, $debitDetailRepository,$serviceRepository)
    {
        $receipts = $this->model
            ->where(['bdc_building_id' => $buildingId])
            ->where(function ($query) use ($request) {
                if (isset($request['receipt_code_type']) && $request['receipt_code_type'] != null) {
                    $query->where('type', $request['receipt_code_type']);
                }
            })
            ->where(function ($query) use ($request) {
                if (isset($request['type_payment']) && $request['type_payment'] != null) {
                    $query->where('type_payment', $request['type_payment']);
                }
            })
            ->Where(function ($query) use ($request) {
                if (isset($request['bdc_apartment_id']) && $request['bdc_apartment_id'] != null) {
                    $query->where('bdc_apartment_id', $request['bdc_apartment_id']);
                }
            })->WhereHas('pubUser', function (Builder $query) use ($request) {
                if (isset($request['user_id_receipt_code']) && $request['user_id_receipt_code'] != null) {
                    $query->where('email', 'like', '%' . $request['user_id_receipt_code'] . '%')
                        ->orWhere('receipt_code', 'like', '%' . $request['user_id_receipt_code'] . '%');
                }
            })->whereHas('apartment', function (Builder $query) use ($request) {
                if(isset($request['ip_place_id'])){
                    $query->where('building_place_id', $request['ip_place_id']);
                }
            })
            ->Where(function ($query) use ($request) {
                if (isset($request['from_date']) && $request['from_date'] != null) {
                    $from_date = Carbon::parse($request['from_date'])->format('Y-m-d');
                    $query->where('create_date', '>=', $from_date);
                }
            })->Where(function ($query) use ($request) {
                if (isset($request['to_date']) && $request['to_date'] != null) {
                    $to_date = Carbon::parse($request['to_date'])->format('Y-m-d');
                    $query->where('create_date', '<=', $to_date . " 23:59:59");
                }
            })
            ->Where(function ($query) use ($request) {
                if (isset($request['created_at_from_date'])  && $request['created_at_from_date'] != null) {
                    $created_at_from_date = Carbon::parse($request['created_at_from_date'])->format('Y-m-d');
                    $query->whereDate('created_at', '>=', $created_at_from_date);
                }
            })
            ->Where(function ($query) use ($request) {
                if (isset($request['created_at_to_date'])  && $request['created_at_to_date'] != null) {
                    $created_at_to_date = Carbon::parse($request['created_at_to_date'])->format('Y-m-d');
                    $query->whereDate('created_at', '<=', $created_at_to_date . " 23:59:59");
                }
            })
            ->orderBy('create_date', 'DESC')->get();

            $_SESSION['Building_name'] = $receipts[0]->building->name;
            $_SESSION['Building_address'] = $receipts[0]->building->address;
            $_SESSION['sumDauKy']=0;
            
        $result = Excel::create('Phiếu thu', function ($excel) use ($receipts, $debitDetailRepository, $serviceRepository) {
            $excel->setTitle('Phiếu thu');
            $excel->sheet('Phiếu thu', function ($sheet) use ($receipts, $debitDetailRepository, $serviceRepository) {
                $data_receipts = [];
                $row = 14;


                $sheet->cells('D6', function ($cells) {
                    $cells->setFontSize(22);
                    $cells->setFontWeight('bold');
                    $cells->setValue('BẢNG KÊ THU TIỀN');
                });

                $sheet->cells('D7', function ($cells) {
                    $cells->setFontSize(11);
                    $cells->setFontWeight('Iatalic');
                    $cells->setValue('Từ ngày..............Đến.............. ');
                    $cells->setAlignment('center');
                });

                $sheet->cells('A2', function ($cells) {
                    $cells->setFontSize(11);
                    $cells->setFontWeight('bold');
                    $cells->setValue('Đơn vị:');
                });

                $sheet->cells('A3', function ($cells) {
                    $cells->setFontSize(11);
                    $cells->setFontWeight('bold');
                    $cells->setValue('Địa chỉ:');
                });

                $sheet->cells('B2', function ($cells) {
                    $cells->setFontSize(11);
                    $cells->setValue($_SESSION['Building_name']);
                });

                $sheet->cells('B3', function ($cells) {
                    $cells->setFontSize(11);
                    $cells->setValue($_SESSION['Building_address']);
                });

                unset($_SESSION['Building_name']);
                unset($_SESSION['Building_address']);

                $sheet->mergeCells('A12:B13');
                $sheet->getStyle('A12')->getAlignment()->setWrapText(true);
                $sheet->cells('A12', function ($cells) {
                    $cells->setFontSize(11);
                    $cells->setFontWeight('bold');
                    $cells->setValue('Chứng từ');
                    $cells->setValignment('center');
                    $cells->setAlignment('center');
                    $cells->setBorder('thin', 'thin', 'thin', 'thin');
                });

                $sheet->cells('A14', function ($cells) {
                    $cells->setFontSize(11);
                    $cells->setFontWeight('bold');
                    $cells->setValue('Số phiếu');
                    $cells->setAlignment('center');
                    $cells->setBorder('thin', 'thin', 'thin', 'thin');
                });


                $sheet->cells('B14', function ($cells) {
                    $cells->setFontSize(11);
                    $cells->setFontWeight('bold');
                    $cells->setValue('Ngày');
                    $cells->setAlignment('center');
                    $cells->setBorder('thin', 'thin', 'thin', 'thin');
                });
                
                $sheet->mergeCells('C12:C14');
                $sheet->getStyle('C12')->getAlignment()->setWrapText(true);
                $sheet->cells('C12', function ($cells) {
                    $cells->setFontSize(11);
                    $cells->setFontWeight('bold');
                    $cells->setValue('Căn hộ');
                    $cells->setValignment('center');
                    $cells->setAlignment('center');
                    $cells->setBorder('thin', 'thin', 'thin', 'thin');
                });
                
                $sheet->mergeCells('D12:D14');
                $sheet->getStyle('D12:D14')->getAlignment()->setWrapText(true);
                $sheet->cells('D12', function ($cells) {
                    $cells->setFontSize(11);
                    $cells->setFontWeight('bold');
                    $cells->setValue('Diễn giải');
                    $cells->setValignment('center');
                    $cells->setAlignment('center');
                    $cells->setBorder('thin', 'thin', 'thin', 'thin');
                });

                $sheet->mergeCells('E12:E14');
                $sheet->getStyle('E12')->getAlignment()->setWrapText(true);
                $sheet->cells('E12', function ($cells) {
                    $cells->setFontSize(11);
                    $cells->setFontWeight('bold');
                    $cells->setValue('Người nộp tiền');
                    $cells->setValignment('center');
                    $cells->setAlignment('center');
                    $cells->setBorder('thin', 'thin', 'thin', 'thin');
                });

                $get_all_building = $serviceRepository->getAllServiceBuilding_v2($receipts[0]->building->id);
                $get_all_building_count =  $get_all_building->count();
                $lastColumn = $sheet->getHighestColumn();
                $lastColumn_new = null;
                foreach ($get_all_building as $key => $value) {
                    $lastColumn_new = $lastColumn++;
                    $column_range_new = $lastColumn_new.'14';
                    $name_service = $value->name;
                    $sheet->cells($column_range_new, function ($cells) use ($name_service) {
                        $cells->setFontSize(11);
                        $cells->setFontWeight('bold');
                        $cells->setValue($name_service);
                        $cells->setValignment('center');
                        $cells->setAlignment('center');
                        $cells->setBorder('thin', 'thin', 'thin', 'thin');
                    });
                    $sheet->setWidth(array($lastColumn_new => 30));
                 
                   
                }
                foreach ($receipts as $key => $receipt) {

                    $row++;
                   
                    $data = unserialize($receipt->data);
                    
                    if ($data) {
                        foreach ($data as $_data) {
                            try {
                                $debitDetail = isset($value['new_debit_id']) ? $debitDetailRepository->findDebitById($_data['new_debit_id']) :  $debitDetailRepository->filterServiceBillIdWithVersion($receipt->bdc_building_id, $_data["bill_id"], $_data["service_id"], $_data["version"]);
                            } catch (Exception $e) {
                                continue;
                            }
                            
                            if (!$debitDetail) {
                                continue;
                            }
                            $building = $debitDetail->building;
                            $apartment = $debitDetail->apartment;
                            $service = $debitDetail->service;
                            $serviceGroup = "";
                            if ($service) {
                                switch ($service->service_group) {
                                    case 2:
                                        $serviceGroup = "Phí thu hộ";
                                        break;
                                    case 3:
                                        $serviceGroup = "Phí chủ đầu tư";
                                        break;
                                    default:
                                        $serviceGroup = "Phí công ty";
                                        break;
                                }
                            }
                            $buildingPlace = @$apartment->buildingPlace;
                            $customer = @$apartment->bdcCustomers ? @$apartment->bdcCustomers->where('type', 0)->first() ?? @$apartment->bdcCustomers->first() : null;
                            $customerName = $customer ? @$customer->pubUserProfile->display_name : "";
                            $cycleArr = explode('/', $debitDetail->cycle_name);
                            $cycleName = implode("0", array_reverse($cycleArr));
                            $_maKhachHangNNC = $customer
                                ? @$customer->pubUserProfile
                                ? @$customer->pubUserProfile->customer_code_prefix . "" . str_pad(@$customer->pubUserProfile->customer_code, 9, "0", STR_PAD_LEFT)
                                : ""
                                : "";
                            // Data
                            //$loaiNK = $status;
                            $soChungTu =  $receipt->receipt_code;
                            $kyKeToan = $cycleName ?? $debitDetail->cycle_name;
                            $ngayLapPhieu = $receipt->created_at->format('d/m/Y');
                            $ngayNgayHachToan = @$receipt->create_date ? date('d/m/Y', strtotime(@$receipt->create_date)) : '--/--/----';
                            $dienGiai = $debitDetail->service->name . " " . $debitDetail->cycle_name;
                            $maKhachHangNNC = $_maKhachHangNNC;
                            $maNganHang = "";
                            $ctyCon = "";
                            $maPhongBan = "";
                            $maNhanVien = "";
                            $maPhi = $debitDetail->service->name;
                            $hopDong = "";
                            $sanPham = $apartment->code ?? null;
                            $block = $buildingPlace->code ?? null;
                            $duAn = $building->name;
                            $maThu = "";
                            $kheUoc = "";
                            $cpKhongHopLe = "";
                            $maTaiKhoan = "";
                            $tkDu = "";
                            $soTien = $debitDetail->paid;
                            $noCo = "";
                            $kyHieuHoaDon = "";
                            $ngayHoaDon = "";
                            $loaiThue = "";
                            $thueSuat = "";
                            $tienTruocThue = "";
                            $maSoHoaDon = "";
                            $nguoiNop_NhanTien = $receipt->customer_name;
                            $nguoiBanHang = "";
                            $phieuCanTru = "";
                            $maPhieuEApprove = "";
                            $ghiChu = $receipt->receipt_code;
                            $type_payment =  $receipt->type_payment;
                            $receipt_code_type =  $receipt->type;
                            $aprtment_name = $receipt->apartment ? $receipt->apartment->name : "";
                            $description = $receipt->description;
                            $nguoiThu = $receipt->pubUser->email;
                        }
                    }
                    //===========================================================================
                    $sheet->setCellValueByColumnAndRow(0, $row, $soChungTu);
                    $sheet->setCellValueByColumnAndRow(1, $row, $ngayLapPhieu);
                    $sheet->setCellValueByColumnAndRow(2, $row, $aprtment_name);
                    $sheet->setCellValueByColumnAndRow(3, $row, $description);
                    $sheet->setCellValueByColumnAndRow(4, $row, $nguoiNop_NhanTien);
                    //===========================================================================
                    foreach ($get_all_building as $key => $value) {
                        if($debitDetail->bdc_service_id == $value->id){
                            $sheet->setCellValueByColumnAndRow($key+5, $row, $soTien);
                        }
                    }
                    //===========================================================================
                    $sheet->setCellValueByColumnAndRow($get_all_building_count+4, $row, $soTien);
                    $sheet->setCellValueByColumnAndRow($get_all_building_count+5, $row, $receipt_code_type);
                    $sheet->setCellValueByColumnAndRow($get_all_building_count+6, $row, $nguoiThu);
                    $sheet->setCellValueByColumnAndRow($get_all_building_count+7, $row, $apartment->code);
                        
                }
                //$sheet->setBorder('A1:F10', 'thin');
                $range_new = 'F12:'.$lastColumn_new.'13';
                $sheet->mergeCells($range_new);
                $sheet->getStyle('F12')->getAlignment()->setWrapText(true);
                $sheet->cells('F12', function ($cells) {
                    $cells->setFontSize(11);
                    $cells->setFontWeight('bold');
                    $cells->setValue('Khoản thu');
                    $cells->setValignment('center');
                    $cells->setAlignment('center');
                    $cells->setBorder('thin', 'thin', 'thin', 'thin');
                });
                $lastColumn_new = $lastColumn++;
                $a = $lastColumn_new.'12:'.$lastColumn_new.'14';
                
                $sheet->mergeCells($a);
                $sheet->getStyle($lastColumn_new.'12')->getAlignment()->setWrapText(true);
                $sheet->cells($lastColumn_new.'12', function ($cells) {
                    $cells->setFontSize(11);
                    $cells->setFontWeight('bold');
                    $cells->setValue('Tổng cộng');
                    $cells->setValignment('center');
                    $cells->setAlignment('center');
                    $cells->setBorder('thin', 'thin', 'thin', 'thin');
                });
                $sheet->setWidth(array($lastColumn_new => 30));
                $lastColumn_new = $lastColumn++;
                $b = $lastColumn_new.'12:'.$lastColumn_new.'14';
                $sheet->mergeCells($b);
                $sheet->getStyle($lastColumn_new.'12')->getAlignment()->setWrapText(true);
                $sheet->cells($lastColumn_new.'12', function ($cells) {
                    $cells->setFontSize(11);
                    $cells->setFontWeight('bold');
                    $cells->setValue('Hình thức');
                    $cells->setValignment('center');
                    $cells->setAlignment('center');
                    $cells->setBorder('thin', 'thin', 'thin', 'thin');
                });
                $sheet->setWidth(array($lastColumn_new => 30));
                $lastColumn_new = $lastColumn++;
                $c = $lastColumn_new.'12:'.$lastColumn_new.'14';
                $sheet->mergeCells($c);
                $sheet->getStyle($lastColumn_new.'12')->getAlignment()->setWrapText(true);
                $sheet->cells($lastColumn_new.'12', function ($cells) {
                    $cells->setFontSize(11);
                    $cells->setFontWeight('bold');
                    $cells->setValue('NV thu');
                    $cells->setValignment('center');
                    $cells->setAlignment('center');
                    $cells->setBorder('thin', 'thin', 'thin', 'thin');
                });
                $sheet->setWidth(array($lastColumn_new => 30));
                $lastColumn_new = $lastColumn++;
                $d = $lastColumn_new.'12:'.$lastColumn_new.'14';
                $sheet->mergeCells($d);
                $sheet->getStyle($lastColumn_new.'12')->getAlignment()->setWrapText(true);
                $sheet->cells($lastColumn_new.'12', function ($cells) {
                    $cells->setFontSize(11);
                    $cells->setFontWeight('bold');
                    $cells->setValue('Mã SP');
                    $cells->setValignment('center');
                    $cells->setAlignment('center');
                    $cells->setBorder('thin', 'thin', 'thin', 'thin');
                });
                $sheet->setWidth(array($lastColumn_new => 30));
                // begin - footer 
                $total_row = $receipts->count() + 20;
                $b_footer = 'B'.$total_row;
                $sheet->cells($b_footer, function ($cells) {
                    $cells->setFontSize(11);
                    $cells->setFontWeight('bold');
                    $cells->setValue('Người Nộp Tiền');
                    $cells->setAlignment('center');
                });

                $e_footer = 'E'.$total_row;

                $sheet->cells($e_footer, function ($cells) {
                    $cells->setFontSize(11);
                    $cells->setFontWeight('bold');
                    $cells->setValue('Kế Toán');
                    $cells->setAlignment('center');
                });
                
                $total_row_new = $total_row -1;
                $h_footer_1 = 'H'.$total_row_new;

                $sheet->cells($h_footer_1, function ($cells) {
                    $cells->setFontSize(11);
                    $cells->setFontWeight('bold');
                    $cells->setValue('Ngày.....Tháng.....Năm.....');
                    $cells->setAlignment('center');
                });

                $h_footer_2 = 'H'.$total_row;

                $sheet->cells($h_footer_2, function ($cells) {
                    $cells->setFontSize(11);
                    $cells->setFontWeight('bold');
                    $cells->setValue('Giám đốc (Trưởng ban tòa nhà)');
                    $cells->setAlignment('center');
                });

                // end - footer
                $sheet->setWidth(array(
                    'A'     =>  20,
                    'B'     =>  20,
                    'C'     =>  20,
                    'D'     =>  20,
                    'E'     =>  20,
                    'F'     =>  20,
                    'G'     =>  20,
                ));
            });
        })->store('xlsx',storage_path('exports/'));
$file     = storage_path('exports/'.$result->filename.'.'.$result->ext);
return response()->download($file)->deleteFileAfterSend(true);
             
    }

    public function updateReceipt($id, $request)
    {
        $receipt = $this->model->where('id', $id)->first();
        // if ($request['type_payment'] == 'tien_mat') {
        //     $request['type_payment'] = 'tien_mat';
        // } elseif ($request['type_payment'] == 2) {
        //     $request['type_payment'] = 'chuyen_khoan';
        // } else {
        //     $request['type_payment'] = 'vnpay';
        // }
        $receipt->update([
            // 'cost' => $request['cost'],
            'type_payment' => $request['type_payment'],
            'customer_name' => $request['customer_name'],
            'description' => isset($request['description']) ? $request['description'] : $receipt->description,
            'create_date' => isset($request['create_date']) ? Carbon::parse($request['create_date'])  : $receipt->create_date,
            'type' => isset($request['type']) ? $request['type'] : $receipt->type,
            'updated_by'=>auth()->user()->id
        ]);
    }

    public function autoIncrementReceiptCode($config, $buildingId)
    {
        // $billCount = $this->model->count();
        $filterByKey = $config->getConfigbyKey($config::RECEIPT_CODE, $buildingId);
        $receipt = collect(DB::select(DB::raw("SELECT MAX(RIGHT(`receipt_code`, 7)) as receipt_code FROM `bdc_receipts` 
            WHERE `type`=:typeCode AND `bdc_building_id`=:buildingId AND `receipt_code` LIKE '%$filterByKey%'"), ['buildingId' => $buildingId, 'typeCode' => self::PHIEUTHU]))->first();
        if ($receipt->receipt_code == null) {
            $receiptCode = $filterByKey . "_0000001";
            return $receiptCode;
        }

        $numberReceiptCode = (int)$receipt->receipt_code;
        $numberReceiptCode = $numberReceiptCode + 1;
        $lengthNumberReceiptCode = strlen($numberReceiptCode);
        $idReceiptCode = substr('0000000',  0, 7 - $lengthNumberReceiptCode);
        $receiptCode = $filterByKey . "_" . $idReceiptCode . $numberReceiptCode;
        return $receiptCode;
    }
    public function autoIncrementCreditTransferReceiptCode($config, $buildingId)
    {
        // $billCount = $this->model->count();
        $filterByKey = $config->getConfigbyKey($config::CREDIT_TRANSFER_RECEIPT_CODE, $buildingId);
        $receipt = collect(DB::select(DB::raw("SELECT MAX(RIGHT(`receipt_code`, 7)) as receipt_code FROM `bdc_receipts` 
            WHERE `type`=:typeCode AND `bdc_building_id`=:buildingId AND `receipt_code` LIKE '%$filterByKey%'"), ['buildingId' => $buildingId, 'typeCode' => self::PHIEUBAOCO]))->first();
        if ($receipt->receipt_code == null) {
            $receiptCode = $filterByKey . "_0000001";
            return $receiptCode;
        }

        $numberReceiptCode = (int)$receipt->receipt_code;
        $numberReceiptCode = $numberReceiptCode + 1;
        $lengthNumberReceiptCode = strlen($numberReceiptCode);
        $idReceiptCode = substr('0000000',  0, 7 - $lengthNumberReceiptCode);
        $receiptCode = $filterByKey . "_" . $idReceiptCode . $numberReceiptCode;
        return $receiptCode;
    }

    public function autoIncrementAccountingReceiptCode($config, $buildingId)
    {
        // $billCount = $this->model->count();
        $filterByKey = $config->getConfigbyKey($config::ACCOUNTING_RECEIPT_CODE, $buildingId);
        $receipt = collect(DB::select(DB::raw("SELECT MAX(RIGHT(`receipt_code`, 7)) as receipt_code FROM `bdc_receipts` 
            WHERE `type`=:typeCode AND `bdc_building_id`=:buildingId AND `receipt_code` LIKE '%$filterByKey%'"), ['buildingId' => $buildingId, 'typeCode' => self::PHIEUKETOAN]))->first();
        if ($receipt->receipt_code == null) {
            $receiptCode = $filterByKey . "_0000001";
            return $receiptCode;
        }

        $numberReceiptCode = (int)$receipt->receipt_code;
        $numberReceiptCode = $numberReceiptCode + 1;
        $lengthNumberReceiptCode = strlen($numberReceiptCode);
        $idReceiptCode = substr('0000000',  0, 7 - $lengthNumberReceiptCode);
        $receiptCode = $filterByKey . "_" . $idReceiptCode . $numberReceiptCode;
        return $receiptCode;
    }

    public function autoIncrementReceiptDeposit($config, $buildingId)
    {
        // $billCount = $this->model->count();
        $filterByKey = $config->getConfigbyKey($config::RECEIPT_DEPOSIT, $buildingId);
      
        $receipt = collect(DB::select(DB::raw("SELECT MAX(RIGHT(`receipt_code`, 7)) as receipt_code FROM `bdc_receipts` 
            WHERE `type`=:typeCode AND `bdc_building_id`=:buildingId AND `receipt_code` LIKE '%$filterByKey%'"), ['buildingId' => $buildingId, 'typeCode' => self::PHIEUTHU_KYQUY]))->first();
        if ($receipt->receipt_code == null) {
            $receiptCode = $filterByKey . "_0000001";
            return $receiptCode;
        }

        $numberReceiptCode = (int)$receipt->receipt_code;
        $numberReceiptCode = $numberReceiptCode + 1;
        $lengthNumberReceiptCode = strlen($numberReceiptCode);
        $idReceiptCode = substr('0000000',  0, 7 - $lengthNumberReceiptCode);
        $receiptCode = $filterByKey . "_" . $idReceiptCode . $numberReceiptCode;
        return $receiptCode;
    }

    public function autoIncrementReceiptCodePrevious($config, $buildingId)
    {
        // $billCount = $this->model->count();
        $filterByKey = $config->getConfigbyKey($config::PROVISIONAL_RECEIPT_CODE, $buildingId);
        $receipt = collect(DB::select(DB::raw("SELECT MAX(RIGHT(`receipt_code`, 7)) as receipt_code FROM `bdc_receipts` 
            WHERE `type`=:typeCode AND `bdc_building_id`=:buildingId AND `receipt_code` LIKE '%$filterByKey%'"), ['buildingId' => $buildingId, 'typeCode' => self::PHIEUTHU_TRUOC]))->first();
        if ($receipt->receipt_code == null) {
            $receiptCode = $filterByKey . "_0000001";
            return $receiptCode;
        }

        $numberReceiptCode = (int)$receipt->receipt_code;
        $numberReceiptCode = $numberReceiptCode + 1;
        $lengthNumberReceiptCode = strlen($numberReceiptCode);
        $idReceiptCode = substr('0000000',  0, 7 - $lengthNumberReceiptCode);
        $receiptCode = $filterByKey . "_" . $idReceiptCode . $numberReceiptCode;
        return $receiptCode;
    }

    public function autoIncrementReceiptPaymentSlipCode($config, $buildingId)
    {
        // $billCount = $this->model->count();
        $filterByKey = $config->getConfigbyKey($config::RECEIPT_PAYMENT_SLIP_CODE, $buildingId);
        $receipt = collect(DB::select(DB::raw("SELECT MAX(RIGHT(`receipt_code`, 7)) as receipt_code FROM `bdc_receipts` 
            WHERE `type`=:typeCode AND `bdc_building_id`=:buildingId  AND `receipt_code` LIKE '%$filterByKey%'"), ['buildingId' => $buildingId, 'typeCode' => self::PHIEUCHI]))->first();
        if ($receipt->receipt_code == null) {
            $receiptCode = $filterByKey . "_0000001";
            return $receiptCode;
        }

        $numberReceiptCode = (int)$receipt->receipt_code;
        $numberReceiptCode = $numberReceiptCode + 1;
        $lengthNumberReceiptCode = strlen($numberReceiptCode);
        $idReceiptCode = substr('0000000',  0, 7 - $lengthNumberReceiptCode);
        $receiptCode = $filterByKey . "_" . $idReceiptCode . $numberReceiptCode;
        return $receiptCode;
    }

    public function autoIncrementReceiptPaymentSlipCodeOther($config, $buildingId)
    {
        // $billCount = $this->model->count();
        $filterByKey = $config->getConfigbyKey($config::RECEIPT_PAYMENT_SLIP_CODE_OTHER, $buildingId);
        $receipt = collect(DB::select(DB::raw("SELECT MAX(RIGHT(`receipt_code`, 7)) as receipt_code FROM `bdc_receipts` 
            WHERE `type`=:typeCode AND `bdc_building_id`=:buildingId  AND `receipt_code` LIKE '%$filterByKey%'"), ['buildingId' => $buildingId, 'typeCode' => self::PHIEUCHIKHAC]))->first();
        if ($receipt->receipt_code == null) {
            $receiptCode = $filterByKey . "_0000001";
            return $receiptCode;
        }

        $numberReceiptCode = (int)$receipt->receipt_code;
        $numberReceiptCode = $numberReceiptCode + 1;
        $lengthNumberReceiptCode = strlen($numberReceiptCode);
        $idReceiptCode = substr('0000000',  0, 7 - $lengthNumberReceiptCode);
        $receiptCode = $filterByKey . "_" . $idReceiptCode . $numberReceiptCode;
        return $receiptCode;
    }

    public function autoIncrementReceiptPaymentSlipDeposit($config, $buildingId)
    {
        // $billCount = $this->model->count();
        $filterByKey = $config->getConfigbyKey($config::RECEIPT_PAYMENT_DEPOSIT, $buildingId);
       
        $receipt = collect(DB::select(DB::raw("SELECT MAX(RIGHT(`receipt_code`, 7)) as receipt_code FROM `bdc_receipts` 
            WHERE `type`=:typeCode AND `bdc_building_id`=:buildingId  AND `receipt_code` LIKE '%$filterByKey%'"), ['buildingId' => $buildingId, 'typeCode' => self::PHIEUHOAN_KYQUY]))->first();
        if ($receipt->receipt_code == null) {
            $receiptCode = $filterByKey . "_0000001";
            return $receiptCode;
        }

        $numberReceiptCode = (int)$receipt->receipt_code;
        $numberReceiptCode = $numberReceiptCode + 1;
        $lengthNumberReceiptCode = strlen($numberReceiptCode);
        $idReceiptCode = substr('0000000',  0, 7 - $lengthNumberReceiptCode);
        $receiptCode = $filterByKey . "_" . $idReceiptCode . $numberReceiptCode;
        return $receiptCode;
    }

    public function searchByApi($building_id, $request = '', $where = [], $perpage = 20)
    {
        $where = [];
        if (!empty($request->keyword)) {
            $where[] = ['receipt_code', 'Like', '%' . $request->keyword . '%'];
        }

        //        if (!empty($request->hashtag)) {
        //            $where[] = ['hashtag', 'Like', '%'.$request->hashtag.'%'];
        //        }

        if (!empty($request->apartment_id)) {
            $where[] = ['bdc_apartment_id', $request->apartment_id];
        }
        if (!empty($request->date)) {
            $where[] = ['created_at', '<=', $request->date];
        }

        if (!empty($request->cost)) {
            $where[] = ['cost', '=', $request->cost];
        }

        if ($request->status != null) {
            $where[] = ['status', '=', $request->status];
        }

        if ($request->private != null) {
            $where[] = ['private', '=', $request->private];
        }

        if (!empty($request->name)) {
            $where[] = ['pub_user_profile_id', '=', (int)$request->name];
        }

        $default = [
            'select'   => ['id', 'bdc_building_id', 'bdc_apartment_id', 'receipt_code','url', 'cost', 'customer_name', 'customer_address', 'provider_address', 'bdc_receipt_total', 'type_payment', 'description', 'created_at'],
            'where'    => $where,
            'order_by' => 'id DESC',
            'per_page' => $perpage,
        ];

        $options = array_merge($default, $where);
        extract($options);

        $model = $this->model->select($options['select']);

        if ($options['where']) {
            $model = $model->where($options['where']);
        }
        $model = $model->where('bdc_building_id', $building_id);

        $list_search = $model->with('apartment', 'building')->orderByRaw($options['order_by'])->paginate($options['per_page']);
        return $list_search->toArray();
    }

    public function findReceiptByIdApi($id, $building_id)
    {
        return $this->model->where('bdc_building_id', $building_id)->where('id', $id)->with('apartment', 'building')->first()->toArray();
    }

    public function receiptTotal($buildingId, $input)
    {
        $sql = "SELECT * FROM (
            SELECT `bdc_debit_detail`.`bdc_building_id`, `bdc_debit_detail`.`bdc_apartment_id`, `bdc_apartments`.`name`, `bdc_bills`.`bill_code`,
                `bdc_debit_detail`.`sumery`, `bdc_debit_detail`.`created_at`, '0' as `cost`, '0' as receipt_code, 'type' as `type`
            FROM `bdc_debit_detail`
            INNER JOIN (
                SELECT bdc_bill_id, bdc_apartment_id, bdc_apartment_service_price_id, MAX(version) as version
                FROM `bdc_debit_detail`
                WHERE `bdc_debit_detail`.`bdc_building_id` = $buildingId
                GROUP BY bdc_bill_id, bdc_apartment_id, bdc_apartment_service_price_id) as tb1
            ON tb1.`bdc_bill_id`=`bdc_debit_detail`.`bdc_bill_id` 
                AND tb1.`bdc_apartment_service_price_id`=`bdc_debit_detail`.`bdc_apartment_service_price_id`
                AND tb1.`bdc_apartment_id`=`bdc_debit_detail`.`bdc_apartment_id`
                AND tb1.`version`=`bdc_debit_detail`.`version`
            INNER JOIN bdc_apartments ON bdc_apartments.id=bdc_debit_detail.bdc_apartment_id
            INNER JOIN bdc_bills ON bdc_bills.id=bdc_debit_detail.bdc_bill_id
            UNION ALL
            SELECT `bdc_receipts`.`bdc_building_id`, `bdc_receipts`.`bdc_apartment_id`, `bdc_apartments`.`name`, '0' as `bill_code`,
                '0' as `sumery`, `bdc_receipts`.`created_at`, `bdc_receipts`.`cost`, `bdc_receipts`.`receipt_code`, `bdc_receipts`.`type` 
            FROM bdc_receipts
            INNER JOIN bdc_apartments ON `bdc_apartments`.`id`=`bdc_receipts`.`bdc_apartment_id`
            WHERE `bdc_receipts`.`bdc_building_id` = $buildingId AND `bdc_receipts`.`status` = 1
        ) as tb1 WHERE 1=1 ";
        if ($input != null) {
            if (array_key_exists('from_date', $input) && $input["from_date"] != null) {
                $sql .= " AND `created_at` >= '" . $input["from_date"] . "'";
            }
            if (array_key_exists('to_date', $input) && $input["to_date"] != null) {
                $sql .= " AND `created_at` <= '" . $input["to_date"] . "'";
            }
            if (array_key_exists('bdc_apartment_id', $input) && $input['bdc_apartment_id'] != null) {
                $sql .= " AND `bdc_apartment_id` = " . $input["bdc_apartment_id"];
            }
            if (array_key_exists('kieu_chi_thu', $input) && $input["kieu_chi_thu"] > 0) {
                switch ($input["kieu_chi_thu"]) {
                    case '1':
                        $sql .= " AND `sumery` > 0";
                        break;
                    case '2':
                        $sql .= " AND `cost` > 0 AND `type` = '" . self::PHIEUTHU . "' AND `type` = '" . self::PHIEUTHU_TRUOC . "'";
                        break;
                    case '3':
                        $sql .= " AND `cost` > 0 AND `type` = '" . self::PHIEUCHI . "'";
                        break;
                }
            }
        }
        $sql .= ' ORDER BY `created_at` ASC';
        return DB::select(DB::raw($sql));
    }

    public function cashBookMoney($buildingId, $request)
    {
        \DB::enableQueryLog();
        $rs = $this->model->where(["bdc_building_id" => $buildingId, "type_payment" => self::TIENMAT])
            // ->orWhere('type_payment', self::PHIEUCHI)
            ->where(function ($query) use ($request) {
                if (isset($request['bdc_apartment_id']) && $request['bdc_apartment_id'] != null) {
                    $query->where('bdc_apartment_id', '=', $request['bdc_apartment_id']);
                }
                if (isset($request['receipt_code']) && $request['receipt_code'] != null) {
                    $query->where('receipt_code', 'LIKE', '%' . $request['receipt_code'] . '%');
                }
            })->where(function ($query) use ($request) {
                if (isset($request['from_date']) && $request['from_date'] != null && isset($request['to_date']) && $request['to_date'] != null) {
                    $from_date = Carbon::parse($request['from_date'])->format('Y-m-d');
                    $to_date   = Carbon::parse($request['to_date'])->format('Y-m-d');

                    $query->whereDate('create_date', '>=', $from_date);
                    $query->whereDate('create_date', '<=', $to_date);
                }
            })->orderBy('create_date', 'ASC');
        // dd(\DB::getQueryLog());
        return $rs;
    }
    
    public function cashBookMoneyNew($buildingId, $request)
    {
        $rs = $this->model->where(["bdc_building_id" => $buildingId, "type_payment" => self::TIENMAT])
            // ->orWhere('type_payment', self::PHIEUCHI)
            ->where(function ($query) use ($request) {
                if (isset($request['bdc_apartment_id']) && $request['bdc_apartment_id'] != null) {
                    $query->where('bdc_apartment_id', '=', $request['bdc_apartment_id']);
                }
                if (isset($request['receipt_code']) && $request['receipt_code'] != null) {
                    $query->where('receipt_code', 'LIKE', '%' . $request['receipt_code'] . '%');
                }
            })->where(function ($query) use ($request) {
                if (isset($request['from_date']) && $request['from_date'] != null && isset($request['to_date']) && $request['to_date'] != null) {
                    $from_date = Carbon::parse($request['from_date'])->format('Y-m-d');
                    $to_date   = Carbon::parse($request['to_date'])->format('Y-m-d');

                    $query->whereDate('create_date', '>=', $from_date);
                    $query->whereDate('create_date', '<=', $to_date);
                }
            })->orderBy('create_date', 'DESC')->orderBy('updated_at', 'DESC');

        return $rs;
    }

    public function receiptReportDeposit($buildingId, $request)
    {
        $sql = "SELECT 
                *
                FROM
               (SELECT 
                    *
                FROM
                    (SELECT DISTINCT
                        `a`.`bdc_apartment_id`,
                            (SELECT 
                                    SUM(`b`.`cost`)
                                FROM
                                    `bdc_receipts` AS `b`
                                WHERE
                                    `a`.`bdc_apartment_id` = `b`.`bdc_apartment_id`
                                        AND `b`.`type` = 'phieu_thu_ky_quy'";
                       if (isset($request['from_date']) && $request['from_date'] != null && isset($request['to_date']) && $request['to_date'] != null) {
                            $from_date = Carbon::parse($request['from_date'])->format('Y-m-d');
                            $to_date = Carbon::parse($request['to_date'])->format('Y-m-d');
                            $sql .=" AND `b`.`create_date` >= '$from_date' AND `b`.`create_date` <= '$to_date 23:59:59'";
                       }
                       $sql .="AND `b`.`deleted_at` IS NULL) AS 'thu_tien',
                            (SELECT 
                                    SUM(`c`.`cost`)
                                FROM
                                    `bdc_receipts` AS `c`
                                WHERE
                                    `a`.`bdc_apartment_id` = `c`.`bdc_apartment_id`
                                        AND `c`.`type` = 'phieu_hoan_ky_quy'";
                       if (isset($request['from_date']) && $request['from_date'] != null && isset($request['to_date']) && $request['to_date'] != null) {
                            $from_date = Carbon::parse($request['from_date'])->format('Y-m-d');
                            $to_date = Carbon::parse($request['to_date'])->format('Y-m-d');
                            $sql .=" AND `c`.`create_date` >= '$from_date' AND `c`.`create_date` <= '$to_date 23:59:59'";
                       }
                       $sql .="AND `c`.`deleted_at` IS NULL) AS 'chi_tien'
                    FROM
                        `bdc_receipts` AS `a`
                    WHERE
                        `a`.`feature` = 'deposit' 
                            AND `a`.`deleted_at` IS NULL) AS tb1
                        INNER JOIN
                    `bdc_apartments` ON `bdc_apartments`.`id` = `tb1`.`bdc_apartment_id`
                WHERE
                `bdc_apartments`.`deleted_at` IS NULL) AS tb2 WHERE `tb2`.`building_id` = $buildingId ";
                if(isset($request['bdc_apartment_id']) && $request['bdc_apartment_id'] != null){
                    $bdc_apartment_id    = $request['bdc_apartment_id'];
                    $sql .="AND `tb2`.`bdc_apartment_id` = $bdc_apartment_id";
                }
        return DB::select(DB::raw($sql));
    }

    public function exportReportDeposit($buildingId, $request)
    {
        $name_building = Building::where('id',$buildingId)->first();
        $_SESSION['Building_name'] = @$name_building->name;
        $_SESSION['Building_address'] = @$name_building->address;

        $result = $this->receiptReportDeposit($buildingId, $request);

        $result = Excel::create('Báo cáo ký quỹ', function ($excel) use ($result) {
            $excel->setTitle('Báo cáo ký quỹ');
            $excel->sheet('Báo cáo ký quỹ', function ($sheet) use ($result) {
                $row = 14;


                $sheet->cells('C6', function ($cells) {
                    $cells->setFontSize(22);
                    $cells->setFontWeight('bold');
                    $cells->setValue('BÁO CÁO KÝ QUỸ');
                });

                if (isset($request['from_date']) && $request['from_date'] != null && isset($request['to_date']) && $request['to_date'] != null) {
                    $from_date = $request['from_date'];
                    $to_date = $request['to_date'];
                    $sheet->cells('C7', function ($cells) use ($from_date,$to_date) {
                        $cells->setFontSize(11);
                        $cells->setFontWeight('Iatalic');
                        $cells->setValue('Từ ngày '.$from_date.' Đến '.$to_date);
                        $cells->setAlignment('center');
                    });
                }

                $sheet->cells('A2', function ($cells) {
                    $cells->setFontSize(11);
                    $cells->setFontWeight('bold');
                    $cells->setValue('Đơn vị:');
                });

                $sheet->cells('A3', function ($cells) {
                    $cells->setFontSize(11);
                    $cells->setFontWeight('bold');
                    $cells->setValue('Địa chỉ:');
                });

                $sheet->cells('B2', function ($cells) {
                    $cells->setFontSize(11);
                    $cells->setValue($_SESSION['Building_name']);
                });

                $sheet->cells('B3', function ($cells) {
                    $cells->setFontSize(11);
                    $cells->setValue($_SESSION['Building_address']);
                });

                unset($_SESSION['Building_name']);
                unset($_SESSION['Building_address']);

                $sheet->mergeCells('A12:A14');
                $sheet->getStyle('A12')->getAlignment()->setWrapText(true);
                $sheet->cells('A12', function ($cells) {
                    $cells->setFontSize(11);
                    $cells->setFontWeight('bold');
                    $cells->setValue('STT');
                    $cells->setValignment('center');
                    $cells->setAlignment('center');
                    $cells->setBorder('thin', 'thin', 'thin', 'thin');
                });

                $sheet->mergeCells('B12:B14');
                $sheet->getStyle('B12')->getAlignment()->setWrapText(true);
                $sheet->cells('B12', function ($cells) {
                    $cells->setFontSize(11);
                    $cells->setFontWeight('bold');
                    $cells->setValue('Căn hộ');
                    $cells->setValignment('center');
                    $cells->setAlignment('center');
                    $cells->setBorder('thin', 'thin', 'thin', 'thin');
                });
                
                $sheet->mergeCells('C12:C14');
                $sheet->getStyle('C12:C14')->getAlignment()->setWrapText(true);
                $sheet->cells('C12', function ($cells) {
                    $cells->setFontSize(11);
                    $cells->setFontWeight('bold');
                    $cells->setValue('Đã thu');
                    $cells->setValignment('center');
                    $cells->setAlignment('center');
                    $cells->setBorder('thin', 'thin', 'thin', 'thin');
                });

                $sheet->mergeCells('D12:D14');
                $sheet->getStyle('D12')->getAlignment()->setWrapText(true);
                $sheet->cells('D12', function ($cells) {
                    $cells->setFontSize(11);
                    $cells->setFontWeight('bold');
                    $cells->setValue('Đã hoàn trả');
                    $cells->setValignment('center');
                    $cells->setAlignment('center');
                    $cells->setBorder('thin', 'thin', 'thin', 'thin');
                });
                $sheet->mergeCells('E12:E14');
                $sheet->getStyle('E12')->getAlignment()->setWrapText(true);
                $sheet->cells('E12', function ($cells) {
                    $cells->setFontSize(11);
                    $cells->setFontWeight('bold');
                    $cells->setValue('Còn nợ');
                    $cells->setValignment('center');
                    $cells->setAlignment('center');
                    $cells->setBorder('thin', 'thin', 'thin', 'thin');
                });
                $sheet->mergeCells('F12:F14');
                $sheet->getStyle('F12')->getAlignment()->setWrapText(true);
                $sheet->cells('F12', function ($cells) {
                    $cells->setFontSize(11);
                    $cells->setFontWeight('bold');
                    $cells->setValue('Mã SP');
                    $cells->setValignment('center');
                    $cells->setAlignment('center');
                    $cells->setBorder('thin', 'thin', 'thin', 'thin');
                });

                foreach ($result as $key => $value) {
                    $row++;
                    $sheet->setCellValueByColumnAndRow(0, $row, ($key + 1));
                    $sheet->getCellByColumnAndRow(1, $row)->setValueExplicit($value->name, PHPExcel_Cell_DataType::TYPE_STRING);
                    $sheet->setCellValueByColumnAndRow(2, $row, $value->thu_tien);
                    $sheet->setCellValueByColumnAndRow(3, $row, $value->chi_tien);
                    $sheet->setCellValueByColumnAndRow(4, $row, ($value->thu_tien-$value->chi_tien));
                    $sheet->setCellValueByColumnAndRow(5, $row, $value->code);
                }

                // begin - footer 
                $total_row = count($result) + 20;
                
                $total_row_new = $total_row + 1;
                $h_footer_1 = 'E'.$total_row_new;

                $sheet->cells($h_footer_1, function ($cells) {
                    $cells->setFontSize(11);
                    $cells->setFontWeight('bold');
                    $cells->setValue('Người lập');
                    $cells->setAlignment('center');
                });

                $h_footer_2 = 'E'.$total_row;

                $sheet->cells($h_footer_2, function ($cells) {
                    $cells->setFontSize(11);
                    $cells->setFontWeight('bold');
                    $cells->setValue('Ngày.....Tháng.....Năm.....');
                    $cells->setAlignment('center');
                });

                // end - footer
                $sheet->setWidth(array(
                    'A'     =>  20,
                    'B'     =>  20,
                    'C'     =>  20,
                    'D'     =>  20,
                    'E'     =>  20,
                    'F'     =>  20,
                    'G'     =>  20,
                ));
            });
        })->store('xlsx',storage_path('exports/'));
$file     = storage_path('exports/'.$result->filename.'.'.$result->ext);
return response()->download($file)->deleteFileAfterSend(true);
             
    }

    public function cashBookMoneyWithTypePayment($typePayment, $buildingId, $request)
    {
        $rs = $this->model->where(["bdc_building_id" => $buildingId, "type_payment" => $typePayment])
            ->where(function ($query) use ($request) {
                if (isset($request['bdc_apartment_id']) && $request['bdc_apartment_id'] != null) {
                    $query->where('bdc_apartment_id', '=', $request['bdc_apartment_id']);
                }
                if (isset($request['receipt_code']) && $request['receipt_code'] != null) {
                    $query->where('receipt_code', 'LIKE', '%' . $request['receipt_code'] . '%');
                }
            })->where(function ($query) use ($request) {
                if (isset($request['from_date']) && $request['from_date'] != null && isset($request['to_date']) && $request['to_date'] != null) {
                    $from_date = Carbon::parse($request['from_date'])->format('Y-m-d');
                    $to_date   = Carbon::parse($request['to_date'])->format('Y-m-d');

                    $query->whereDate('created_at', '>=', $from_date);
                    $query->whereDate('created_at', '<=', $to_date);
                }
            })->orderBy('created_at', 'DESC');
        return $rs;
    }

    public function cashBookMoneyWithType($typePayment, $type, $buildingId, $request)
    {
        $rs = $this->model->where(["bdc_building_id" => $buildingId, "type_payment" => $typePayment, "type" => $type])
            ->where(function ($query) use ($request) {
                if (isset($request['bdc_apartment_id']) && $request['bdc_apartment_id'] != null) {
                    $query->where('bdc_apartment_id', '=', $request['bdc_apartment_id']);
                }
                if (isset($request['receipt_code']) && $request['receipt_code'] != null) {
                    $query->where('receipt_code', 'LIKE', '%' . $request['receipt_code'] . '%');
                }
            })->where(function ($query) use ($request) {
                if (isset($request['from_date']) && $request['from_date'] != null && isset($request['to_date']) && $request['to_date'] != null) {
                    $from_date = Carbon::parse($request['from_date'])->format('Y-m-d');
                    $to_date   = Carbon::parse($request['to_date'])->format('Y-m-d');
                    $query->whereDate('create_date', '>=', $from_date);
                    $query->whereDate('create_date', '<=', $to_date);
                }
            })->orderBy('create_date', 'DESC');
        return $rs;
    }

    public function cashBookMoneyWithTypeV2($typePayment, $buildingId, $request)
    {
        $rs = $this->model->where(["bdc_building_id" => $buildingId, "type_payment" => $typePayment])
            ->where(function($query) use ($request) {
                $query->where('type', self::PHIEUCHI)->orWhere('type', self::PHIEUCHIKHAC)->orWhere('type', self::PHIEUHOAN_KYQUY);
            })
            ->where(function ($query) use ($request) {
                if (isset($request['bdc_apartment_id']) && $request['bdc_apartment_id'] != null) {
                    $query->where('bdc_apartment_id', '=', $request['bdc_apartment_id']);
                }
                if (isset($request['receipt_code']) && $request['receipt_code'] != null) {
                    $query->where('receipt_code', 'LIKE', '%' . $request['receipt_code'] . '%');
                }
            })->where(function ($query) use ($request) {
                if (isset($request['from_date']) && $request['from_date'] != null && isset($request['to_date']) && $request['to_date'] != null) {
                    $from_date = Carbon::parse($request['from_date'])->format('Y-m-d');
                    $to_date   = Carbon::parse($request['to_date'])->format('Y-m-d');
                    $query->whereDate('create_date', '>=', $from_date);
                    $query->whereDate('create_date', '<=', $to_date);
                }
            })->orderBy('create_date', 'DESC');
        return $rs;
    }

    public function cashBookMoneyWithTypePhieThu($typePayment, $buildingId, $request)
    {
        $rs = $this->model->where(["bdc_building_id" => $buildingId, "type_payment" => $typePayment])
            ->where(function ($query) use ($request) {
                   $query->where('type', '<>', ReceiptRepository::PHIEUCHI)->where('type', '<>', ReceiptRepository::PHIEUCHIKHAC)->where('type', '<>', ReceiptRepository::PHIEUHOAN_KYQUY);
            })
            ->where(function ($query) use ($request) {
                if (isset($request['bdc_apartment_id']) && $request['bdc_apartment_id'] != null) {
                    $query->where('bdc_apartment_id', '=', $request['bdc_apartment_id']);
                }
                if (isset($request['receipt_code']) && $request['receipt_code'] != null) {
                    $query->where('receipt_code', 'LIKE', '%' . $request['receipt_code'] . '%');
                }
            })->where(function ($query) use ($request) {
                if (isset($request['from_date']) && $request['from_date'] != null && isset($request['to_date']) && $request['to_date'] != null) {
                    $from_date = Carbon::parse($request['from_date'])->format('Y-m-d');
                    $to_date   = Carbon::parse($request['to_date'])->format('Y-m-d');
                    $query->whereDate('create_date', '>=', $from_date);
                    $query->whereDate('create_date', '<=', $to_date);
                }
            })->orderBy('create_date', 'DESC');
        return $rs;
    }

    public function receiptDauKy($buildingId, $input)
    {
        $sql = "SELECT * FROM (
            SELECT `bdc_debit_detail`.`bdc_building_id`, `bdc_debit_detail`.`bdc_apartment_id`, `bdc_apartments`.`name`, `bdc_bills`.`bill_code`,
                `bdc_debit_detail`.`sumery`, `bdc_debit_detail`.`created_at`, '0' as `cost`, '0' as receipt_code, 'type' as `type`
            FROM `bdc_debit_detail`
            INNER JOIN (
                SELECT bdc_bill_id, bdc_apartment_id, bdc_apartment_service_price_id, MAX(version) as version
                FROM `bdc_debit_detail`
                WHERE `bdc_debit_detail`.`bdc_building_id` = $buildingId
                GROUP BY bdc_bill_id, bdc_apartment_id, bdc_apartment_service_price_id) as tb1
            ON tb1.`bdc_bill_id`=`bdc_debit_detail`.`bdc_bill_id` 
                AND tb1.`bdc_apartment_service_price_id`=`bdc_debit_detail`.`bdc_apartment_service_price_id`
                AND tb1.`bdc_apartment_id`=`bdc_debit_detail`.`bdc_apartment_id`
                AND tb1.`version`=`bdc_debit_detail`.`version`
            INNER JOIN bdc_apartments ON bdc_apartments.id=bdc_debit_detail.bdc_apartment_id
            INNER JOIN bdc_bills ON bdc_bills.id=bdc_debit_detail.bdc_bill_id
            UNION ALL
            SELECT `bdc_receipts`.`bdc_building_id`, `bdc_receipts`.`bdc_apartment_id`, `bdc_apartments`.`name`, '0' as `bill_code`,
                '0' as `sumery`, `bdc_receipts`.`created_at`, `bdc_receipts`.`cost`, `bdc_receipts`.`receipt_code`, `bdc_receipts`.`type` 
            FROM bdc_receipts
            INNER JOIN bdc_apartments ON `bdc_apartments`.`id`=`bdc_receipts`.`bdc_apartment_id`
            WHERE `bdc_receipts`.`bdc_building_id` = $buildingId AND `bdc_receipts`.`status` = 1
        ) as tb1 WHERE 1=1 ";

        if ($input != null) {
            $createdAt = Carbon::parse($input->created_at);
            $sql .= " AND `created_at` < '" . $createdAt->format('Y-m-d') . "'";
        }

        $sql .= ' ORDER BY `created_at` ASC';

        return DB::select(DB::raw($sql));
    }

    public function cashBookMoneyDauky($buildingId, $input)
    {
        return $this->model->where(["bdc_building_id" => $buildingId, "type_payment" => self::TIENMAT])
            ->where(function($query) use ($input) {
                $query->where('type', self::PHIEUCHI)->orWhere('type', self::PHIEUCHIKHAC)->orWhere('type', self::PHIEUHOAN_KYQUY);
            })
            ->where(function ($query) use ($input) {
                if (isset($input["from_date"])) {
                    $query->where('create_date', '<', Carbon::parse($input["from_date"]));
                }
            })->orderBy('create_date', 'ASC');
    }

    public function cashBookMoneyDaukyWithTypePayment($typePayment, $buildingId, $input)
    {
        return $this->model->where(["bdc_building_id" => $buildingId, "type_payment" => $typePayment])
            ->where(function ($query) use ($input) {
                if (isset($input)) {
                    $query->where('create_date', '<', Carbon::parse($input->create_date));
                }
            })->orderBy('created_at', 'ASC');
    }

    public function cashBookMoneyDaukyWithType($type, $buildingId, $input)
    {
        return $this->model->where(["bdc_building_id" => $buildingId, "type" => $type])
            ->where(function ($query) use ($input) {
                if (isset($input)) {
                    $query->where('create_date', '<', Carbon::parse($input["from_date"]));
                }
            })->orderBy('created_at', 'ASC');
    }

    public function cashBookMoneyDaukyWithTypePhieThu($typePayment, $buildingId, $input)
    {
        return $this->model->where(["bdc_building_id" => $buildingId, "type_payment" => $typePayment])
            ->where(function ($query) use ($input) {
                $query->where('type', '<>', ReceiptRepository::PHIEUCHI)->where('type', '<>', ReceiptRepository::PHIEUCHIKHAC)->where('type', '<>', ReceiptRepository::PHIEUHOAN_KYQUY);
            })
            ->where(function ($query) use ($input) {
                if (isset($input)) {
                    $query->where('create_date', '<', Carbon::parse($input["from_date"]));
                }
            })->orderBy('create_date', 'ASC');
    }

    public function findReceiptCodePay($code)
    {
        return $this->model->where('receipt_code', $code)->first();
    }
    public function findReceiptCodePayByBuilding($code,$buildingId)
    {
        return $this->model->where(['receipt_code'=> $code,'bdc_building_id'=>$buildingId])->first();
    }

    public function updateAttribute($id, $data = [])
    {
        return $this->model->where('id', $id)->update($data);
    }

    public function LatestRecordDatabaseByDatetime($cost,$apartmentId,$type_payment)
    {
        $end_time = Carbon::now();
        $start_time = Carbon::now()->subMinute(30);
        return $this->model->where(['cost'=>$cost,'bdc_apartment_id'=>$apartmentId,'type_payment'=>$type_payment])->whereBetween('created_at', [$start_time, $end_time])->orderBy('created_at', 'desc')->first();
    }
    public function filterByBillId($billId, $buildingId, $apartmentId, $typePayment)
    {
        $rs = $this->model
            ->where(['bdc_building_id' => $buildingId, 'bdc_apartment_id' => $apartmentId])
            ->where('bdc_bill_id', 'LIKE', '%' . $billId . '%');
        if ($typePayment != "all") {
            $rs->where(['type_payment' => $typePayment]);
        }
        $rs->where('cost', '>',0);
        return $rs->get();
    }

    public function dauKy($building, $type, $createdAt)
    {
        return $this->model->where(['type'=> $type, 'bdc_building_id' => $building])->where(function($query) use ($createdAt) {
            if (isset($createdAt) && $createdAt != null) {
                $query->whereDate('created_at', '<', $createdAt);
            }
        })->get();
    }

    public function totalCost($building, $type, $fromDate, $toDate)
    {
        return $this->model->where(['type'=> $type, 'bdc_building_id' => $building])->where(function($query) use ($fromDate, $toDate) {
            if ($fromDate != null && $toDate != null) {
                $query->whereDate('created_at', '>=', $fromDate)->whereDate('created_at', '<=', $toDate);
            }
        })->get();
    }
}
