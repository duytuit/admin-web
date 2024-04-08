<?php

namespace App\Repositories\BdcReceipts\V2;

use App\Repositories\BdcV2LogCoinDetail\LogCoinDetailRepository;
use App\Repositories\Eloquent\Repository;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Exception;
use Illuminate\Support\Facades\DB;
use App\Repositories\Service\ServiceRepository;
use App\Models\Building\Building;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\BdcV2PaymentDetail\PaymentDetail;
use App\Repositories\Apartments\ApartmentsRespository;
use App\Repositories\Building\BuildingPlaceRepository;
use App\Repositories\Building\BuildingRepository;
use App\Repositories\Customers\CustomersRespository;
use App\Repositories\PublicUsers\PublicUsersProfileRespository;
use App\Models\Service\Service;
use App\Repositories\BdcApartmentServicePrice\ApartmentServicePriceRepository;
use App\Models\PublicUser\Users;
use App\Commons\Helper;
use App\Exceptions\QueueRedis;
use App\Helpers\dBug;
use App\Models\Apartments\Apartments;
use App\Models\BdcApartmentServicePrice\ApartmentServicePrice;
use App\Models\BdcReceipts\Receipts;
use App\Models\BdcV2LogCoinDetail\LogCoinDetail;
use App\Models\PublicUser\UserInfo;
use App\Models\Vehicles\Vehicles;
use App\Repositories\BdcV2DebitDetail\DebitDetailRepository;
use Illuminate\Support\Facades\Cache;
use \PHPExcel;
use \PHPExcel_IOFactory; 


const BUILDING_USER = 1;

use PHPExcel_Cell_DataType;
use PHPExcel_Style_Border;
use App\Services\SendTelegram;
use App\Models\Configs\Configs;

class ReceiptRepository extends Repository
{
    const PHIEUTHU = 'phieu_thu';
    const PHIEUBAOCO = 'phieu_bao_co';
    const PHIEUKETOAN = 'phieu_ke_toan';
    const PHIEUTHU_TRUOC = 'phieu_thu_truoc';
    const PHIEUCHI = 'phieu_chi';
    const PHIEUCHIKHAC = 'phieu_chi_khac';
    const TIENMAT = 'tien_mat';
    const CHUYENKHOAN = 'chuyen_khoan';
    const COMPLETED = 1;
    const NOTCOMPLETED = 0;

    const PHIEUTHU_KYQUY = 'phieu_thu_ky_quy';
    const PHIEUHOAN_KYQUY = 'phieu_hoan_ky_quy';
    const PHIEU_DIEUCHINH = 'phieu_dieu_chinh';
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
            return $this->model->withTrashed()->where(['bdc_building_id' => $building])->orderBy('create_date', 'desc')->paginate($perPage);
        } else {
            return $this->model->withTrashed()->where(['bdc_building_id' => $building])->orderBy('create_date', 'desc')->get();
        }
    }

    public function getAllReceiptBuildingKyQuy($building)
    {
        $response = $this->model->where(function ($query) {
                $query->where('type', self::PHIEUTHU_KYQUY)->orWhere('type', self::PHIEUHOAN_KYQUY);
        })->where(['bdc_building_id' => $building])->where('feature', self::DEPOSIT)->orderBy('create_date', 'desc');
        if($building == 17 || $building == 77 || $building == 111){
            $response->whereNotNull('config_type_payment'); // lây phiếu thu v1 cho tòa imperial plaza và phương đông
        }
        return $response;
    }

    public function filterApartmentId($apartmentId)
    {
        return $this->model->where(['bdc_apartment_id' => $apartmentId, 'type' => self::PHIEUTHU_TRUOC, 'status' => self::NOTCOMPLETED])->orderBy('id', 'DESC')->get();
    }

    public function filterByApartmentId($apartmentId, $request)
    {
        return $this->model->where(['bdc_apartment_id' => $apartmentId, 'config_type_payment' => 1])
            ->where('type', '<>', ReceiptRepository::PHIEUKETOAN)
            ->where(function ($query) use ($request) {
                if ($request->type_payment) {
                    $query->where('type', $request->type_payment);
                }
            })
            ->whereNull('feature')
            ->orderBy('created_at', 'DESC')->get();
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

    public function findReceiptByIdWithTrashed($id)
    {
        return $this->model->withTrashed()->find($id);
    }

    public function distinct_user_by_building($buildingId)
    {
        return $this->model->select('user_id')->where('bdc_building_id', $buildingId)->distinct()->get();
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
                    $linksArray = array_filter($request['receipt_code_type']);
                    if (count($linksArray) > 0) {
                        $query->whereIn('type', $linksArray);
                    }
                }
                $query->where('type', self::PHIEUTHU_KYQUY)->orWhere('type', self::PHIEUHOAN_KYQUY);
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
                if (isset($request['ip_place_id'])) {
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
            ->where('feature', self::DEPOSIT);
            if($building == 17 || $building == 77 || $building == 111){
                $response->whereNotNull('config_type_payment'); // lây phiếu thu v1 cho tòa imperial plaza và phương đông
            }
            $response->orderBy('create_date', 'desc');
        return $response;
    }
    public function filterReceipt($request, $building)
    {
        $response = $this->model
            ->where(function ($query) use ($request) {
                if (isset($request['receipt_code_type']) && $request['receipt_code_type'] != null) {
                    $linksArray = array_filter($request['receipt_code_type']);
                    if (count($linksArray) > 0) {
                        $query->whereIn('type', $linksArray);
                    }
                }
                if (isset($request['user_id'])  && $request['user_id'] != null) {
                    $query->WhereHas('pubUser', function (Builder $query) use ($request) {
                        $query->where('id', $request['user_id']);
                    });
                }
                if (isset($request['user_id_receipt_code']) && $request['user_id_receipt_code'] != null) {
                    $query->where('receipt_code', 'like', '%' . $request['user_id_receipt_code'] . '%');
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
            });
            if (isset($request['ip_place_id'])) {
                $response->whereHas('apartment', function (Builder $query) use ($request) {
                    $query->where('building_place_id', $request['ip_place_id']);
                });
            }
            if(\Auth::user()->isadmin !=1){
                $response->where(['bdc_building_id' => $building]);
            }
            if($building == 17 || $building == 77 || $building == 111){
                $response->whereNotNull('config_type_payment');
            }
            $response->orderBy('create_date', 'desc')->orderBy('updated_at', 'desc');
        return $response;
    }


    public function countFilterReceipt($request, $building)
    {
        $response = $this->model
            ->where(['bdc_building_id' => $building])
            ->where(function ($query) use ($request) {
                if (isset($request['receipt_code_type']) && $request['receipt_code_type'] != null) {
                    $linksArray = array_filter($request['receipt_code_type']);
                    if (count($linksArray) > 0) {
                        $query->whereIn('type', $linksArray);
                    }
                }
                if (isset($request['user_id'])  && $request['user_id'] != null) {
                    $query->WhereHas('pubUser', function (Builder $query) use ($request) {
                        $query->where('id', $request['user_id']);
                    });
                }
                if (isset($request['user_id_receipt_code']) && $request['user_id_receipt_code'] != null) {
                    $query->where('receipt_code', 'like', '%' . $request['user_id_receipt_code'] . '%');
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
            ->whereHas('apartment', function (Builder $query) use ($request) {
                if (isset($request['ip_place_id'])) {
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
            });
            if($building == 17 || $building == 77 || $building == 111){
                $response->whereNotNull('config_type_payment');
            }
        return $response->select(DB::raw('COALESCE(SUM(cost),0) as tong'))->first()->toArray()["tong"];
    }

    public function filterReceiptKyQuy($request, $perPage,  $building)
    {
        // DB::enableQueryLog();
        $response = $this->model->where(['bdc_building_id' => $building])
            ->where(function ($query) {
                return $query->where('type', self::PHIEUTHU_TRUOC)->orWhere('type', self::PHIEUCHIKHAC);
            })
            ->where(function ($query) use ($request) {
                if (isset($request['receipt_code_type']) && $request['receipt_code_type'] != null) {
                    $linksArray = array_filter($request['receipt_code_type']);
                    if (count($linksArray) > 0) {
                        $query->whereIn('type', $linksArray);
                    }
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
                if (isset($request['ip_place_id'])) {
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

                    switch ($value->type) {

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
                        ($key + 1) . "",
                        @$value->receipt_code,
                        $TypeReceipt,
                        @$value->pubConfig->title == null ? "" : @$value->pubConfig->title,
                        $aprtment_name,
                        @$value->cost . "",
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
        $response = $this->filterReceipt($request,$buildingId)->get();
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

                    $status = Helper::loai_danh_muc[$value->type_payment] ?? $value->type_payment;

                    if ($value->apartment) {
                        $aprtment_name = $value->apartment->name;
                    } else {
                        $aprtment_name = '';
                    }

                    // loại chứng từ

                    $TypeReceipt = Helper::loai_danh_muc[$value->type] ?? $value->type;
                    
                    $data = [
                        ($key + 1) . "",
                        @$value->receipt_code,
                        $TypeReceipt,
                        @$value->pubConfig->title == null ? "" : @$value->pubConfig->title,
                        $aprtment_name,
                        @$value->cost . "",
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
        ob_end_clean();
        $file  = storage_path('exports/'.$result->filename.'.'.$result->ext);
        return response()->download($file)->deleteFileAfterSend(true);  
    }
    public function filterReceiptExcelNew($buildingId, $request)
    {
        $receiptTotals = $this->cashBookMoneyV2($buildingId, $request)->get();
        $sum_thu = 0;
        $sum_chi = 0;
        $sum_thu_dau_ky = 0;
        $sum_chi_dau_ky = 0;
        $sumDauKy = 0;
        
        if (isset($request['from_date'])) {
            $sumPT = $this->cashBookMoneyDaukyWithTypePhieThu(ReceiptRepository::TIENMAT, $buildingId, $request)->sum('cost');
            $sumPC = $this->cashBookMoneyDauky($buildingId, $request)->sum('cost');
            $sumDauKy = $sumPT - $sumPC;  
        }
        if($receiptTotals->count() > 0){
            $sum_thu = $this->sumThu($buildingId,$receiptTotals[0]->idNew);
            $sum_chi = $this->sumChi($buildingId,$receiptTotals[0]->idNew);
    
            // $sum_thu_dau_ky = $this->sumThuDauKy($buildingId,$last_item);
            // $sum_chi_dau_ky = $this->sumChiDauKy($buildingId,$last_item);
        }
        // Khởi tạo session PHP nếu chưa khởi tạo
        if (session_id() === '') {
            session_start();
        }
        $building = Building::get_detail_building_by_building_id($buildingId);
        $_SESSION['Building_name'] = $building->name;
        $_SESSION['Building_address'] = $building->address;
        $_SESSION['sumDauKy'] =$sumDauKy + $sum_thu_dau_ky - $sum_chi_dau_ky;

        $sum_cuoi_ky = $sum_thu-$sum_chi;

        $result = Excel::create('Phiếu thu', function ($excel) use ($receiptTotals,$sum_cuoi_ky,$buildingId) {
            $excel->setTitle('Phiếu thu');
            $excel->sheet('Phiếu thu', function ($sheet) use ($receiptTotals,$sum_cuoi_ky,$buildingId) {
                $sumcost = 0;
                $sumchi= 0;
                $sum_sum_end_cycle= 0;
                $row = 14;
                
                $temp_sum_cuoi_ky = @$sum_cuoi_ky;
                if($receiptTotals){
                    foreach($receiptTotals as $lock => $receiptTotal)
                    {
                        if($lock !=0){
                            if(@$receiptTotals[$lock-1]->type != "phieu_chi" && @$receiptTotals[$lock-1]->type != "phieu_chi_khac" && @$receiptTotals[$lock-1]->type != "phieu_hoan_ky_quy"){ // phiếu thu
                                $temp_sum_cuoi_ky -=$receiptTotals[$lock-1]->cost;
                            }
                            else if(@$receiptTotals[$lock-1]->type == "phieu_chi" || @$receiptTotals[$lock-1]->type == "phieu_chi_khac" || @$receiptTotals[$lock-1]->type == "phieu_hoan_ky_quy"){ // phiếu chi
                                $temp_sum_cuoi_ky +=$receiptTotals[$lock-1]->cost;
                            }
                        }
                        if($receiptTotals->count()==1){
                            $temp_sum_cuoi_ky = @$receiptTotal->cost;
                        }
                        $row++;
                        $apartment = Apartments::get_detail_apartment_by_apartment_id($receiptTotal->bdc_apartment_id);
                        $user = Users::get_detail_user_by_user_id($receiptTotal->user_id);
                        //
                        $PhiDV= 0;
                        $phiNuoc= 0;
                        $phiXe= 0;
                        $phiDien= 0;
                        $other= 0;
                        $coin = 0;
                        $query = "  
                        SELECT
                        SUM(CASE WHEN d.`type` = 2 THEN paid ELSE 0 END) AS PhiDV,
                        SUM(CASE WHEN d.`type` = 4 THEN paid ELSE 0 END) AS phiXe,
                        SUM(CASE WHEN d.`type` = 3 THEN paid ELSE 0 END) AS phiNuoc,
                        SUM(CASE WHEN d.`type` = 5 THEN paid ELSE 0 END) AS phiDien,
                        SUM(CASE WHEN d.`type` = 0 THEN paid ELSE 0 END) AS other
                      FROM
                        bdc_apartment_service_price a
                      JOIN bdc_v2_payment_detail b ON a.id = b.bdc_apartment_service_price_id
                      JOIN bdc_services d ON a.bdc_service_id = d.id
                      WHERE
                        b.bdc_receipt_id = ".$receiptTotal->id."
                        AND b.deleted_at IS NULL
                    ";
                    $result = DB::select(DB::raw($query));
                    if (count($result) > 0) {
                        $PhiDV = $result[0]->PhiDV;
                        $phiXe = $result[0]->phiXe;
                        $phiNuoc = $result[0]->phiNuoc;
                        $phiDien = $result[0]->phiDien;
                        $other = $result[0]->other;
                    } else {
                        $PhiDV = 0;
                        $phiXe = 0;
                        $phiNuoc = 0;
                        $phiDien = 0;
                        $other= 0;
                    }
                    $result1 = DB::select(DB::raw("select sum(coin) as summ from `bdc_v2_log_coin_detail` where (`bdc_apartment_id` = ".$receiptTotal->bdc_apartment_id."  and `from_id` = ".$receiptTotal->id.") and `bdc_v2_log_coin_detail`.`deleted_at` is null"));
                    if (count($result) > 0) {$coin= $result1[0]->summ;} else {$coin= 0;}
                        $sheet->row($row, [
                            $receiptTotal->receipt_code,
                            empty($receiptTotal->create_date) ? date('d/m/Y', strtotime($receiptTotal->created_at)) : date('d/m/Y', strtotime($receiptTotal->create_date)),
                            '',
                            $receiptTotal->description,
                            ($receiptTotal->type != "phieu_chi" && $receiptTotal->type != "phieu_chi_khac" && $receiptTotal->type != "phieu_hoan_ky_quy") && $receiptTotal->type_payment == ReceiptRepository::TIENMAT ? number_format( $receiptTotal->cost ): '',
                            ($receiptTotal->type == "phieu_chi" || $receiptTotal->type == "phieu_chi_khac" || $receiptTotal->type == "phieu_hoan_ky_quy") && $receiptTotal->type_payment == ReceiptRepository::TIENMAT ? number_format( $receiptTotal->cost ) : '',
                            number_format($temp_sum_cuoi_ky),
                            @$apartment->name,
                            @$user->email ?? null,
                             number_format($PhiDV),
                             number_format($phiNuoc),
                             number_format($phiXe),
                             number_format($phiDien),
                            number_format($coin),
                             number_format($other),
                           // $receiptTotal->idNew
                        ]);
                        $sum_sum_end_cycle += $temp_sum_cuoi_ky;
                        if (($receiptTotal->type != "phieu_chi" && $receiptTotal->type != "phieu_chi_khac" && $receiptTotal->type != "phieu_hoan_ky_quy") && $receiptTotal->type_payment == ReceiptRepository::TIENMAT)
                        {
                           $sumcost += $receiptTotal->cost;
                        }
                        if (($receiptTotal->type == "phieu_chi" || $receiptTotal->type == "phieu_chi_khac" || $receiptTotal->type == "phieu_hoan_ky_quy") && $receiptTotal->type_payment == ReceiptRepository::TIENMAT)
                        {
                            $sumchi += $receiptTotal->cost;
                        }
                    }
                }
                $sheet->mergeCells('J11:O12');
                $sheet->cells('J11', function ($cells) {
                    $cells->setFontSize(11);
                    $cells->setFontWeight('bold');
                    $cells->setValue('Loại phí');
                    $cells->setAlignment('center');
                    $cells->setValignment('center');
                });
                $sheet->mergeCells('B13:C13');
                $sheet->cells('b13', function ($cells) {
                    $cells->setFontSize(11);
                    $cells->setFontWeight('bold');
                    $cells->setValue('Ngày, Tháng');
                    $cells->setAlignment('center');
                    $cells->setBorder('thin', 'thin', 'thin', 'thin');
                });
                $sheet->mergeCells('I11:I13');
                $sheet->cells('I11', function ($cells) {
                    $cells->setFontSize(11);
                    $cells->setValue('Người lập phiếu');
                    $cells->setAlignment('center');
                });
                
                $sheet->cells('I11', function ($cells) {
                    $cells->setFontSize(11);
                    $cells->setValue('Người Thu phí');
                    $cells->setAlignment('center');
                    $cells->setFontWeight('bold');
                });
                $sheet->cells('J13', function ($cells) {
                    $cells->setFontSize(11);
                    $cells->setValue('PDV');
                    $cells->setAlignment('center');
                    $cells->setFontWeight('bold');
                });
                $sheet->cells('K13', function ($cells) {
                    $cells->setFontSize(11);
                    $cells->setValue('Nước');
                    $cells->setAlignment('center');
                    
                    $cells->setFontWeight('bold');
                });
                $sheet->cells('L13', function ($cells) {
                    $cells->setFontSize(11);
                    $cells->setValue('Xe');
                    $cells->setAlignment('center');
                    $cells->setFontWeight('bold');
                });
                $sheet->cells('M13', function ($cells) {
                    $cells->setFontSize(11);
                    $cells->setValue('Điện');
                    $cells->setFontWeight('bold');
                    $cells->setAlignment('center');
                });
                $sheet->cells('N13', function ($cells) {
                    $cells->setFontSize(11);
                    $cells->setFontWeight('bold');
                    $cells->setValue('Tiền thừa');
                    $cells->setAlignment('center');
                });
                $sheet->cells('O13', function ($cells) {
                    $cells->setFontSize(11);
                    $cells->setFontWeight('bold');
                    $cells->setValue('Khác');
                    $cells->setAlignment('center');
                });

                $sheet->mergeCells('D11:D13');
                $sheet->getStyle('D11')->getAlignment()->setWrapText(true);
                $sheet->cells('D11', function ($cells) {
                    $cells->setFontSize(11);
                    $cells->setFontWeight('bold');
                    $cells->setValue('Diễn giải');
                    $cells->setValignment('center');
                    $cells->setAlignment('center');
                    $cells->setBorder('thin', 'thin', 'thin', 'thin');
                });


                $sheet->mergeCells('E11:G12');
                $sheet->getStyle('E11')->getAlignment()->setWrapText(true);
                $sheet->cells('E11', function ($cells) {
                    $cells->setFontSize(11);
                    $cells->setFontWeight('bold');
                    $cells->setValue('Số tiền');
                    $cells->setValignment('center');
                    $cells->setAlignment('center');
                    $cells->setBorder('thin', 'thin', 'thin', 'thin');
                });

                $sheet->cells('E13', function ($cells) {
                    $cells->setFontSize(11);
                    $cells->setFontWeight('bold');
                    $cells->setValue('Thu (Gửi Vào)');
                    $cells->setAlignment('center');
                    $cells->setBorder('thin', 'thin', 'thin', 'thin');
                });

                $sheet->mergeCells('E2:G2');
                $sheet->cells('E2', function ($cells) {
                    $cells->setFontSize(11);
                    $cells->setFontWeight('bold');
                    $cells->setValue('Mẫu số S08-DN');
                    $cells->setAlignment('center');
                });

                $sheet->mergeCells('E3:G3');
                $sheet->mergeCells('E4:G4');
                $sheet->cells('E3', function ($cells) {
                    $cells->setFontSize(11);
                    //$cells->setFontWeight('bold');
                    $cells->setValue('(Ban hành theo Thông tư số 200/2014/TT-BTC');
                    $cells->setAlignment('center');
                });
                $sheet->cells('E4', function ($cells) {
                    $cells->setFontSize(11);
                    //$cells->setFontWeight('bold');
                    $cells->setValue('Ngày 22/12/2014 của Bộ Tài chính)');
                    $cells->setAlignment('center');
                });

                $sheet->cells('F13', function ($cells) {
                    $cells->setFontSize(11);
                    $cells->setFontWeight('bold');
                    $cells->setValue('Chi (rút ra)');
                    $cells->setAlignment('center');
                    $cells->setBorder('thin', 'thin', 'thin', 'thin');
                });

                $sheet->cells('G13', function ($cells) {
                    $cells->setFontSize(11);
                    $cells->setFontWeight('bold');
                    $cells->setValue('Còn lại');
                    $cells->setAlignment('center');
                    $cells->setBorder('thin', 'thin', 'thin', 'thin');
                });

                $sheet->mergeCells('A11:C12');
                $sheet->cells('A11', function ($cells) {
                    $cells->setFontSize(11);
                    $cells->setFontWeight('bold');
                    $cells->setValue('Chứng từ');
                    $cells->setValignment('center');
                    $cells->setAlignment('center');
                    $cells->setBorder('thin', 'thin', 'thin', 'thin');
                });

                $sheet->getStyle('A13')->getAlignment()->setWrapText(true);
                $sheet->cells('A13', function ($cells) {
                    $cells->setFontSize(11);
                    $cells->setFontWeight('bold');
                    $cells->setValue('Số chứng từ');
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
                $sheet->mergeCells('H11:H13');
                $sheet->cells('H11', function ($cells) {
                    $cells->setFontSize(11);
                    $cells->setFontWeight('bold');
                    $cells->setValue('Căn Hộ');
                    $cells->setAlignment('center');
                    $cells->setValignment('center');
                    $cells->setBorder('thin', 'thin', 'thin', 'thin');
                });

               /* $sheet->mergeCells('A9:G9');

                $sheet->cells('A9', function ($cells) {
                    $cells->setFontSize(11);
                    $cells->setFontWeight('bold');
                    $cells->setValue('Loại quỹ: Tiền Việt Nam ');
                    $cells->setAlignment('center');
                });*/

                $sheet->cells('D14', function ($cells) {
                    $cells->setFontSize(11);
                    $cells->setFontWeight('Iatalic');
                    $cells->setValignment('center');
                    $cells->setAlignment('center');
                    $cells->setFontWeight('bold');
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
                $sheet->cells('G14', function ($cells) {
                    $cells->setValue(number_format($_SESSION['sumDauKy']));
                });
                 $sheet->cells('H10', function ($cells) {
                    $cells->setValue('Đơn vị tính: VND');
                });
                $cusrow= $sheet->getHighestRow();
                $sheet->cells('A'.($cusrow + 3), function ($cells) {
                    $cells->setValue('- Sổ này có … trang, đánh số từ trang số 01 đến trang …');
                });
                $sheet->cells('D'.($cusrow + 1), function ($cells) {
                    $cells->setValue('- Cộng số phát sinh trong kỳ');
                    $cells->setFontWeight('bold');
                });
                $sheet->cells('E'.($cusrow + 1), function ($cells) use ($sumcost)  { //
                    $cells->setValue(number_format($sumcost));
                    $cells->setFontWeight('bold');
                    $cells->setAlignment('right');
                });
                $sheet->cells('F'.($cusrow + 1), function ($cells) use ($sumchi)  { //
                    $cells->setValue(number_format($sumchi));
                    $cells->setFontWeight('bold');
                    $cells->setAlignment('right');
                });
                $sheet->cells('D'.($cusrow + 2), function ($cells) {
                    $cells->setValue('- Số dư cuối kỳ');
                    $cells->setFontWeight('bold');
                });
                //Diamond
                try {
                    $daukyreal = $_SESSION['sumDauKy'];
                    $soducuoikyreal=  $daukyreal + $sumcost - $sumchi;
                $sheet->cells('G'.($cusrow + 2), function ($cells) use ($soducuoikyreal)  { //
                    $cells->setValue(number_format($soducuoikyreal));
                    $cells->setAlignment('right');
                    $cells->setFontWeight('bold');
                });
                $sheet->cells('A'.($cusrow + 4), function ($cells) {
                    $cells->setValue(' - Ngày mở sổ: …');
                });
                $sheet->cells('G'.($cusrow + 5), function ($cells) {
                    $cells->setValue('Ngày...tháng...năm');
                    
                });
                $sheet->cells('A'.($cusrow + 6), function ($cells) {
                    $cells->setValue('Kế Toán Ban');
                    $cells->setFontWeight('bold');
                    $cells->setAlignment('center');
                });
                $sheet->cells('G'.($cusrow + 6), function ($cells) {
                    $cells->setValue('Trưởng Ban');
                    $cells->setFontWeight('bold');
                    $cells->setAlignment('center');
                });
                $sheet->cells('A'.($cusrow + 7), function ($cells) {
                    $cells->setValue('(Ký tên)');
                    $cells->setAlignment('center');
                });
                $sheet->cells('G'.($cusrow + 7), function ($cells) {
                    $cells->setValue('(Ký, họ tên, đóng dấu)');
                    $cells->setAlignment('center');
                });
                $sheet->getStyle('G'.($cusrow + 7))->getFont()->setItalic(true);
                $sheet->getStyle('D20')->getAlignment()->setWrapText(true);
                
                for ($row = 14; $row <= ($cusrow + 2); $row++) {
                    $sheet->getStyle('D'.$row)->getAlignment()->setWrapText(true);
                    $sheet->cells('G'.($row), function ($cells) { $cells->setAlignment('right');$cells->setBorder('thin', 'thin', 'thin', 'thin');});
                    $sheet->cells('A'.($row), function ($cells) { $cells->setAlignment('left');$cells->setBorder('thin', 'thin', 'thin', 'thin');});
                    $sheet->cells('E'.($row), function ($cells) { $cells->setAlignment('right');$cells->setBorder('thin', 'thin', 'thin', 'thin');});
                    $sheet->mergeCells('B'.$row.':C'.$row);
                    $sheet->cells('B'.($row), function ($cells) { $cells->setAlignment('center');$cells->setBorder('thin', 'thin', 'thin', 'thin');});
                    $sheet->cells('F'.($row), function ($cells) { $cells->setAlignment('right');$cells->setBorder('thin', 'thin', 'thin', 'thin');});
                    $sheet->cells('D'.($row), function ($cells) { $cells->setBorder('thin', 'thin', 'thin', 'thin');});
                    $sheet->cells('H'.($row), function ($cells) { $cells->setBorder('thin', 'thin', 'thin', 'thin');});
                }
            }
            catch (Exception $e)
            {
                SendTelegram::SupersendTelegramMessage('Ex::'.$e->getMessage());
            }
                unset($_SESSION['sumDauKy']);
                unset($_SESSION['Building_name']);
                unset($_SESSION['Building_address']);
                $sheet->setWidth(array(
                    'A'     =>  25,
                    'B'     =>  10,
                    'C'     =>  10,
                    'D'     =>  60,
                    'E'     =>  16,
                    'F'     =>  16,
                    'G'     =>  16,
                    'H'     =>  20,
                    'I'     =>  25,
                ));
            });
        })->store('xlsx',storage_path('exports/'));
        ob_end_clean();
        $file     = storage_path('exports/'.$result->filename.'.'.$result->ext);
        return response()->download($file)->deleteFileAfterSend(true);
    }

    public function filterReceiptExcelNewVerBanking($buildingId, $request)
    {
        set_time_limit(0);
        $receiptTotals = $this->cashBookMoneyVerBanking($buildingId, $request)->get();
        $sum_thu = 0;
        $sum_chi = 0;
        $sum_thu_dau_ky = 0; 
        $sum_chi_dau_ky = 0;
        $sumDauKy = 0;
        
        if (isset($request['from_date'])) {
            $sumPT = $this->cashBookMoneyDaukyWithTypePhieThu(ReceiptRepository::CHUYENKHOAN, $buildingId, $request)->sum('cost');
            $sumPC = $this->cashBookMoneyDaukyVerBanking($buildingId, $request)->sum('cost');
            $sumDauKy = $sumPT - $sumPC;  
        }
        if($receiptTotals->count() > 0){
            $sum_thu = $this->sumThuVerBanking($buildingId,$receiptTotals[0]->idNew);
            $sum_chi = $this->sumChiVerBanking($buildingId,$receiptTotals[0]->idNew);
    
            // $sum_thu_dau_ky = $this->sumThuDauKy($buildingId,$last_item);
            // $sum_chi_dau_ky = $this->sumChiDauKy($buildingId,$last_item);
        }
        // Khởi tạo session PHP nếu chưa khởi tạo
        if (session_id() === '') {
            session_start();
        }
        $building = Building::get_detail_building_by_building_id($buildingId);
        $_SESSION['Building_name'] = $building->name;
        $_SESSION['Building_address'] = $building->address;
        $_SESSION['sumDauKy'] =$sumDauKy + $sum_thu_dau_ky - $sum_chi_dau_ky;

        $sum_cuoi_ky = $sum_thu-$sum_chi;

        $result = Excel::create('Phiếu thu', function ($excel) use ($receiptTotals,$sum_cuoi_ky,$buildingId) {
            $excel->setTitle('Phiếu thu');
            $excel->sheet('Phiếu thu', function ($sheet) use ($receiptTotals,$sum_cuoi_ky,$buildingId) {
                $sumcost = 0;
                $sumchi= 0;
                $sum_sum_end_cycle= 0;
                $row = 14;
                
                $temp_sum_cuoi_ky = @$sum_cuoi_ky;
                if($receiptTotals){
                    foreach($receiptTotals as $lock => $receiptTotal)
                    {
                        if($lock !=0){
                            if(@$receiptTotals[$lock-1]->type != "phieu_chi" && @$receiptTotals[$lock-1]->type != "phieu_chi_khac" && @$receiptTotals[$lock-1]->type != "phieu_hoan_ky_quy"){ // phiếu thu
                                $temp_sum_cuoi_ky -=$receiptTotals[$lock-1]->cost;
                            }
                            else if(@$receiptTotals[$lock-1]->type == "phieu_chi" || @$receiptTotals[$lock-1]->type == "phieu_chi_khac" || @$receiptTotals[$lock-1]->type == "phieu_hoan_ky_quy"){ // phiếu chi
                                $temp_sum_cuoi_ky +=$receiptTotals[$lock-1]->cost;
                            }
                        }
                        if($receiptTotals->count()==1){
                            $temp_sum_cuoi_ky = @$receiptTotal->cost;
                        }
                        $row++;
                        $apartment = Apartments::get_detail_apartment_by_apartment_id($receiptTotal->bdc_apartment_id);
                        $user = Users::get_detail_user_by_user_id($receiptTotal->user_id);
                        $queryResult = ApartmentServicePrice::join('bdc_v2_payment_detail as b', 'bdc_apartment_service_price.id', '=', 'b.bdc_apartment_service_price_id')
                                ->join('bdc_services as d', 'bdc_apartment_service_price.bdc_service_id', '=', 'd.id')
                                ->where('b.bdc_receipt_id', $receiptTotal->id)
                                ->whereNull('b.deleted_at')
                                ->selectRaw('
                                    SUM(CASE WHEN d.type = 2 THEN paid ELSE 0 END) AS PhiDV,
                                    SUM(CASE WHEN d.type = 4 THEN paid ELSE 0 END) AS phiXe,
                                    SUM(CASE WHEN d.type = 3 THEN paid ELSE 0 END) AS phiNuoc,
                                    SUM(CASE WHEN d.type = 5 THEN paid ELSE 0 END) AS phiDien,
                                    SUM(CASE WHEN d.type = 0 THEN paid ELSE 0 END) AS other'
                                )
                                ->first();
                            $defaultValues = [
                                'PhiDV' => 0,
                                'phiXe' => 0,
                                'phiNuoc' => 0,
                                'phiDien' => 0,
                                'other' => 0,
                            ];
                            if ($queryResult) {
                                $PhiDV = $queryResult->PhiDV;
                                $phiXe = $queryResult->phiXe;
                                $phiNuoc = $queryResult->phiNuoc;
                                $phiDien = $queryResult->phiDien;
                                $other = $queryResult->other;
                            } else {
                                extract($defaultValues);
                            }
                       /* $result1 = DB::select(DB::raw("select sum(coin) as summ from `bdc_v2_log_coin_detail` where (`bdc_apartment_id` = ".$receiptTotal->bdc_apartment_id."  and `from_id` = ".$receiptTotal->id.") and `bdc_v2_log_coin_detail`.`deleted_at` is null"));
                        if (count($result1) > 0) {$coin= $result1[0]->summ;} else {$coin= 0;} */
                        $result1 = LogCoinDetail::where('bdc_apartment_id', $receiptTotal->bdc_apartment_id)
                        ->where('from_id', $receiptTotal->id)
                        ->whereNull('deleted_at')
                        ->selectRaw('SUM(coin) as summ')
                        ->first();
                        $coin = $result1 ? $result1->summ : 0;
                        //end of Duong change section
                        $sheet->row($row, [
                            $receiptTotal->receipt_code,
                            empty($receiptTotal->create_date) ? date('d/m/Y', strtotime($receiptTotal->created_at)) : date('d/m/Y', strtotime($receiptTotal->create_date)),
                            '',
                            $receiptTotal->description,
                            ($receiptTotal->type != "phieu_chi" && $receiptTotal->type != "phieu_chi_khac" && $receiptTotal->type != "phieu_hoan_ky_quy") && $receiptTotal->type_payment == ReceiptRepository::CHUYENKHOAN ? number_format( $receiptTotal->cost ): '',
                            ($receiptTotal->type == "phieu_chi" || $receiptTotal->type == "phieu_chi_khac" || $receiptTotal->type == "phieu_hoan_ky_quy") && $receiptTotal->type_payment == ReceiptRepository::CHUYENKHOAN ? number_format( $receiptTotal->cost ) : '',
                            number_format($temp_sum_cuoi_ky),
                            @$apartment->name,
                            @$user->email ?? null,
                             number_format($PhiDV),
                             number_format($phiNuoc),
                             number_format($phiXe),
                             number_format($phiDien),
                            number_format($coin),
                             number_format($other),
                           // $receiptTotal->idNew
                        ]);
                        $sum_sum_end_cycle += $temp_sum_cuoi_ky;
                        if (($receiptTotal->type != "phieu_chi" && $receiptTotal->type != "phieu_chi_khac" && $receiptTotal->type != "phieu_hoan_ky_quy") && $receiptTotal->type_payment == ReceiptRepository::CHUYENKHOAN)
                        {
                           $sumcost += $receiptTotal->cost;
                        }
                        if (($receiptTotal->type == "phieu_chi" || $receiptTotal->type == "phieu_chi_khac" || $receiptTotal->type == "phieu_hoan_ky_quy") && $receiptTotal->type_payment == ReceiptRepository::TIENMAT)
                        {
                            $sumchi += $receiptTotal->cost;
                        }
                    }
                }
                $sheet->mergeCells('J11:O12');
                $sheet->cells('J11', function ($cells) {
                    $cells->setFontSize(11);
                    $cells->setFontWeight('bold');
                    $cells->setValue('Loại phí');
                    $cells->setAlignment('center');
                    $cells->setValignment('center');
                });
                $sheet->mergeCells('B13:C13');
                $sheet->cells('b13', function ($cells) {
                    $cells->setFontSize(11);
                    $cells->setFontWeight('bold');
                    $cells->setValue('Ngày, Tháng');
                    $cells->setAlignment('center');
                    $cells->setBorder('thin', 'thin', 'thin', 'thin');
                });
                $sheet->mergeCells('I11:I13');
                $sheet->cells('I11', function ($cells) {
                    $cells->setFontSize(11);
                    $cells->setValue('Người lập phiếu');
                    $cells->setAlignment('center');
                });
                
                $sheet->cells('I11', function ($cells) {
                    $cells->setFontSize(11);
                    $cells->setValue('Người Thu phí');
                    $cells->setAlignment('center');
                    $cells->setFontWeight('bold');
                });
                $sheet->cells('J13', function ($cells) {
                    $cells->setFontSize(11);
                    $cells->setValue('PDV');
                    $cells->setAlignment('center');
                    $cells->setFontWeight('bold');
                });
                $sheet->cells('K13', function ($cells) {
                    $cells->setFontSize(11);
                    $cells->setValue('Nước');
                    $cells->setAlignment('center');
                    
                    $cells->setFontWeight('bold');
                });
                $sheet->cells('L13', function ($cells) {
                    $cells->setFontSize(11);
                    $cells->setValue('Xe');
                    $cells->setAlignment('center');
                    $cells->setFontWeight('bold');
                });
                $sheet->cells('M13', function ($cells) {
                    $cells->setFontSize(11);
                    $cells->setValue('Điện');
                    $cells->setFontWeight('bold');
                    $cells->setAlignment('center');
                });
                $sheet->cells('N13', function ($cells) {
                    $cells->setFontSize(11);
                    $cells->setFontWeight('bold');
                    $cells->setValue('Tiền thừa');
                    $cells->setAlignment('center');
                });
                $sheet->cells('O13', function ($cells) {
                    $cells->setFontSize(11);
                    $cells->setFontWeight('bold');
                    $cells->setValue('Khác');
                    $cells->setAlignment('center');
                });

                $sheet->mergeCells('D11:D13');
                $sheet->getStyle('D11')->getAlignment()->setWrapText(true);
                $sheet->cells('D11', function ($cells) {
                    $cells->setFontSize(11);
                    $cells->setFontWeight('bold');
                    $cells->setValue('Diễn giải');
                    $cells->setValignment('center');
                    $cells->setAlignment('center');
                    $cells->setBorder('thin', 'thin', 'thin', 'thin');
                });


                $sheet->mergeCells('E11:G12');
                $sheet->getStyle('E11')->getAlignment()->setWrapText(true);
                $sheet->cells('E11', function ($cells) {
                    $cells->setFontSize(11);
                    $cells->setFontWeight('bold');
                    $cells->setValue('Số tiền');
                    $cells->setValignment('center');
                    $cells->setAlignment('center');
                    $cells->setBorder('thin', 'thin', 'thin', 'thin');
                });

                $sheet->cells('E13', function ($cells) {
                    $cells->setFontSize(11);
                    $cells->setFontWeight('bold');
                    $cells->setValue('Thu (Gửi Vào)');
                    $cells->setAlignment('center');
                    $cells->setBorder('thin', 'thin', 'thin', 'thin');
                });

                $sheet->mergeCells('A8:G8');
                $sheet->cells('A8', function ($cells) {
                    $cells->setFontSize(11);
                    $cells->setFontWeight('bold');
                    $cells->setValue('Nơi mở tài khoản giao dịch: ');
                    $cells->setAlignment('center');
                    $cells->setBorder('thin', 'thin', 'thin', 'thin');
                });

                $sheet->mergeCells('A9:G9');
                $sheet->cells('A9', function ($cells) {
                    $cells->setFontSize(11);
                    $cells->setFontWeight('bold');
                    $cells->setValue('Số hiệu tài khoản tại nơi gửi: ');
                    $cells->setAlignment('center');
                    $cells->setBorder('thin', 'thin', 'thin', 'thin');
                });

                $sheet->mergeCells('E2:G2');
                $sheet->cells('E2', function ($cells) {
                    $cells->setFontSize(11);
                    $cells->setFontWeight('bold');
                    $cells->setValue('Mẫu số S08-DN');
                    $cells->setAlignment('center');
                });

                $sheet->mergeCells('E3:G3');
                $sheet->mergeCells('E4:G4');
                $sheet->cells('E3', function ($cells) {
                    $cells->setFontSize(11);
                    //$cells->setFontWeight('bold');
                    $cells->setValue('(Ban hành theo Thông tư số 200/2014/TT-BTC');
                    $cells->setAlignment('center');
                });
                $sheet->cells('E4', function ($cells) {
                    $cells->setFontSize(11);
                    //$cells->setFontWeight('bold');
                    $cells->setValue('Ngày 22/12/2014 của Bộ Tài chính)');
                    $cells->setAlignment('center');
                });

                $sheet->cells('F13', function ($cells) {
                    $cells->setFontSize(11);
                    $cells->setFontWeight('bold');
                    $cells->setValue('Chi (rút ra)');
                    $cells->setAlignment('center');
                    $cells->setBorder('thin', 'thin', 'thin', 'thin');
                });

                $sheet->cells('G13', function ($cells) {
                    $cells->setFontSize(11);
                    $cells->setFontWeight('bold');
                    $cells->setValue('Còn lại');
                    $cells->setAlignment('center');
                    $cells->setBorder('thin', 'thin', 'thin', 'thin');
                });

                $sheet->mergeCells('A11:C12');
                $sheet->cells('A11', function ($cells) {
                    $cells->setFontSize(11);
                    $cells->setFontWeight('bold');
                    $cells->setValue('Chứng từ');
                    $cells->setValignment('center');
                    $cells->setAlignment('center');
                    $cells->setBorder('thin', 'thin', 'thin', 'thin');
                });

                $sheet->getStyle('A13')->getAlignment()->setWrapText(true);
                $sheet->cells('A13', function ($cells) {
                    $cells->setFontSize(11);
                    $cells->setFontWeight('bold');
                    $cells->setValue('Số chứng từ');
                    $cells->setAlignment('center');
                    $cells->setBorder('thin', 'thin', 'thin', 'thin');
                });

                $sheet->mergeCells('A5:G5');

                $sheet->cells('A5', function ($cells) {
                    $cells->setFontSize(22);
                    $cells->setFontWeight('bold');
                    $cells->setValue('SỔ TIỀN GỬI NGÂN HÀNG');
                    $cells->setAlignment('center');
                });

                $sheet->mergeCells('A7:G7');

                $sheet->cells('A7', function ($cells) {
                    $cells->setFontSize(11);
                    $cells->setFontWeight('Iatalic');
                    $cells->setValue('Ngày: ' . date('d/m/Y', strtotime(Carbon::now())));
                    $cells->setAlignment('center');
                });
                $sheet->mergeCells('H11:H13');
                $sheet->cells('H11', function ($cells) {
                    $cells->setFontSize(11);
                    $cells->setFontWeight('bold');
                    $cells->setValue('Căn Hộ');
                    $cells->setAlignment('center');
                    $cells->setValignment('center');
                    $cells->setBorder('thin', 'thin', 'thin', 'thin');
                });

               /* $sheet->mergeCells('A9:G9');

                $sheet->cells('A9', function ($cells) {
                    $cells->setFontSize(11);
                    $cells->setFontWeight('bold');
                    $cells->setValue('Loại quỹ: Tiền Việt Nam ');
                    $cells->setAlignment('center');
                });*/

                $sheet->cells('D14', function ($cells) {
                    $cells->setFontSize(11);
                    $cells->setFontWeight('Iatalic');
                    $cells->setValignment('center');
                    $cells->setAlignment('center');
                    $cells->setFontWeight('bold');
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
                $sheet->cells('G14', function ($cells) {
                    $cells->setValue(number_format($_SESSION['sumDauKy']));
                });
                 $sheet->cells('H10', function ($cells) {
                    $cells->setValue('Đơn vị tính: VND');
                });
                $cusrow= $sheet->getHighestRow();
                $sheet->cells('A'.($cusrow + 3), function ($cells) {
                    $cells->setValue('- Sổ này có … trang, đánh số từ trang số 01 đến trang …');
                });
                $sheet->cells('D'.($cusrow + 1), function ($cells) {
                    $cells->setValue('- Cộng số phát sinh trong kỳ');
                    $cells->setFontWeight('bold');
                });
                $sheet->cells('E'.($cusrow + 1), function ($cells) use ($sumcost)  { //
                    $cells->setValue(number_format($sumcost));
                    $cells->setFontWeight('bold');
                    $cells->setAlignment('right');
                });
                $sheet->cells('F'.($cusrow + 1), function ($cells) use ($sumchi)  { //
                    $cells->setValue(number_format($sumchi));
                    $cells->setFontWeight('bold');
                    $cells->setAlignment('right');
                });
                $sheet->cells('D'.($cusrow + 2), function ($cells) {
                    $cells->setValue('- Số dư cuối kỳ');
                    $cells->setFontWeight('bold');
                });
                //Diamond
                try {
                    $daukyreal = $_SESSION['sumDauKy'];
                    $soducuoikyreal=  $daukyreal + $sumcost - $sumchi;
                $sheet->cells('G'.($cusrow + 2), function ($cells) use ($soducuoikyreal)  { //
                    $cells->setValue(number_format($soducuoikyreal));
                    $cells->setAlignment('right');
                    $cells->setFontWeight('bold');
                });
                $sheet->cells('A'.($cusrow + 4), function ($cells) {
                    $cells->setValue(' - Ngày mở sổ: …');
                });
                $sheet->cells('G'.($cusrow + 5), function ($cells) {
                    $cells->setValue('Ngày...tháng...năm');
                    
                });
                $sheet->cells('A'.($cusrow + 6), function ($cells) {
                    $cells->setValue('Kế Toán Ban');
                    $cells->setFontWeight('bold');
                    $cells->setAlignment('center');
                });
                $sheet->cells('G'.($cusrow + 6), function ($cells) {
                    $cells->setValue('Trưởng Ban');
                    $cells->setFontWeight('bold');
                    $cells->setAlignment('center');
                });
                $sheet->cells('A'.($cusrow + 7), function ($cells) {
                    $cells->setValue('(Ký tên)');
                    $cells->setAlignment('center');
                });
                $sheet->cells('G'.($cusrow + 7), function ($cells) {
                    $cells->setValue('(Ký, họ tên, đóng dấu)');
                    $cells->setAlignment('center');
                });
                $sheet->getStyle('G'.($cusrow + 7))->getFont()->setItalic(true);
                $sheet->getStyle('D20')->getAlignment()->setWrapText(true);
                
                for ($row = 14; $row <= ($cusrow + 2); $row++) {
                    $sheet->getStyle('D'.$row)->getAlignment()->setWrapText(true);
                    $sheet->cells('G'.($row), function ($cells) { $cells->setAlignment('right');$cells->setBorder('thin', 'thin', 'thin', 'thin');});
                    $sheet->cells('A'.($row), function ($cells) { $cells->setAlignment('left');$cells->setBorder('thin', 'thin', 'thin', 'thin');});
                    $sheet->cells('E'.($row), function ($cells) { $cells->setAlignment('right');$cells->setBorder('thin', 'thin', 'thin', 'thin');});
                    $sheet->mergeCells('B'.$row.':C'.$row);
                    $sheet->cells('B'.($row), function ($cells) { $cells->setAlignment('center');$cells->setBorder('thin', 'thin', 'thin', 'thin');});
                    $sheet->cells('F'.($row), function ($cells) { $cells->setAlignment('right');$cells->setBorder('thin', 'thin', 'thin', 'thin');});
                    $sheet->cells('D'.($row), function ($cells) { $cells->setBorder('thin', 'thin', 'thin', 'thin');});
                    $sheet->cells('H'.($row), function ($cells) { $cells->setBorder('thin', 'thin', 'thin', 'thin');});
                }
            }
            catch (Exception $e)
            {
            }
                unset($_SESSION['sumDauKy']);
                unset($_SESSION['Building_name']);
                unset($_SESSION['Building_address']);
                $sheet->setWidth(array(
                    'A'     =>  25,
                    'B'     =>  10,
                    'C'     =>  10,
                    'D'     =>  60,
                    'E'     =>  16,
                    'F'     =>  16,
                    'G'     =>  16,
                    'H'     =>  20,
                    'I'     =>  25,
                ));
            });
        })->store('xlsx',storage_path('exports/'));
        ob_end_clean();
        $file     = storage_path('exports/'.$result->filename.'.'.$result->ext);
        return response()->download($file)->deleteFileAfterSend(true);
    }

    public function filterReceiptExcelDetail($buildingId, $request, $debitDetailRepository)
    {
        $receipts = $this->filterReceipt($request,$buildingId)->get();
        $result = Excel::create('Phiếu thu', function ($excel) use ($receipts, $request,$buildingId) {
            $excel->setTitle('Phiếu thu');
            $excel->sheet('Phiếu thu', function ($sheet) use ($receipts, $request,$buildingId) {
                $data = [];
                $content = [];
                foreach ($receipts as $key => $receipt) {
                    if ($receipt->type_payment == 'tien_mat') {
                        $status = 'PT';
                    } else {
                        $status = 'BC';
                    }

                    $apartment = ApartmentsRespository::getInfoApartmentsById($receipt->bdc_apartment_id);
                    $aprtment_name = $apartment->name;
                    $buildingPlace = BuildingPlaceRepository::getInfoBuildingPlaceById($apartment->building_place_id);
                    $building = BuildingRepository::getInfoBuildingById($receipt->bdc_building_id);

                    $customer = CustomersRespository::findApartmentIdV2($receipt->bdc_apartment_id, 0);
                    $pubUserProfile = $customer ? PublicUsersProfileRespository::getInfoUserById($customer->user_info_id) : null;
                    $customerName = $customer ? @$pubUserProfile->full_name : $receipt->customer_name;
                    $_maKhachHangNNC = null;
                    $PaymentDetail = $receipt->PaymentDetail;
                    $getTienThua =  LogCoinDetailRepository::getDataByFromId($receipt->bdc_apartment_id,1,$receipt->id);
                    if ($PaymentDetail->count() > 0) {
                        $check_paid_coin = 0;
                        foreach ($PaymentDetail as $_data) {
                            $service_apartment_id = @$_data->bdc_apartment_service_price_id;

                            $service_apart =  ApartmentServicePriceRepository::getInfoServiceApartmentById($service_apartment_id);
                            $service = $service_apart ? Service::get_detail_bdc_service_by_bdc_service_id($service_apart->bdc_service_id) : null;
                            $vehicle = @$service_apart && @$service_apart->bdc_vehicle_id > 0 ? Vehicles::get_detail_vehicle_by_id($service_apart->bdc_vehicle_id) : null;
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
                            $paid_coin = LogCoinDetailRepository::getCountTienthuaByRecieptid($receipt->id,$service_apartment_id);
                            $paid_coin = $paid_coin ? $paid_coin : LogCoinDetailRepository::getDataByIdAndFromTypeV2(@$_data->bdc_log_coin_id??0);
                            if($getTienThua){
                                foreach ($getTienThua as $key_1 => $value_1) {
                                    $value_1->type = 2; // chi tiêt tiền thừa
                                    if($_data->bdc_apartment_service_price_id == $value_1->bdc_apartment_service_price_id){
                                        $getTienThua->forget($key_1);
                                    }
                                }
                            }

                            $cycleArr = explode('/', @$_data->cycle_name);
                            $cycleName = implode("0", array_reverse($cycleArr));
                            // Data
                            $loaiNK = $status;
                            $soChungTu =  $receipt->receipt_code;
                            $kyKeToan = @$_data->debit->cycle_name;
                            $ngayLapPhieu = $receipt->created_at->format('d/m/Y');
                            $ngayNgayHachToan = @$receipt->create_date ? date('d/m/Y', strtotime(@$receipt->create_date)) : '--/--/----';
                            $dienGiai =$service ? @$service->name . " " . @$_data->debit->cycle_name : $receipt->description;
                            $maKhachHangNNC = $_maKhachHangNNC;
                            $maNganHang = "";
                            $ctyCon = "";
                            $maPhongBan = "";
                            $maNhanVien = "";
                            $maPhi = @$service_apart->name;
                            $hopDong = "";
                            $sanPham = $apartment->code ?? null;
                            $block = @$buildingPlace->code ?? null;
                            $duAn = $building->name;
                            $maThu = "";
                            $kheUoc = "";
                            $cpKhongHopLe = "";
                            $maTaiKhoan = "";
                            $tkDu = "";
                            $soTien = @$_data->paid + ($check_paid_coin == $_data->bdc_apartment_service_price_id  ? 0 : $paid_coin);
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
                            $ghiChu = $service_apartment_id == 0 ? 'tiền thừa chưa chỉ định' : $receipt->receipt_code;
                            if($receipt->type != 'phieu_ke_toan'){
                                $content[] = [
                                    'Loại NK' => $loaiNK,
                                    'Số chứng từ' => $soChungTu,
                                    'Kỳ bảng kê' => $kyKeToan,
                                    'Kỳ kế toán' => date('Ym', strtotime(@$receipt->create_date)),
                                    'Ngày lập phiếu' => $ngayLapPhieu,
                                    'Ngày hạch toán' => $ngayNgayHachToan,
                                    'Diễn giải' => $dienGiai,
                                    'Mã khách hàng-NCC' => $maKhachHangNNC,
                                    'Mã ngân hàng' =>'',
                                    'Ngân hàng' =>'',
                                    'Cty Con - NH cho DXMB vay' => $ctyCon,
                                    'Mã phòng ban' => $maPhongBan,
                                    'Mã nhân viên' => $maNhanVien,
                                    'Mã phí' => $maPhi,
                                    'Hợp đồng' => $hopDong,
                                    'Chi tiết sản phẩm' => $vehicle ? @$service->name . "-" .@$vehicle->number : @$service->name,
                                    'Sản phẩm' => $sanPham,
                                    'Block' => $block,
                                    'Dự án' => $duAn,
                                    'Mã thu' => $maThu,
                                    'Khế ước' => $kheUoc,
                                    'CP không hợp lệ' => $cpKhongHopLe,
                                    'Mã tài khoản' => $maTaiKhoan,
                                    'TK Dư' => $tkDu,
                                    'Số tiền' => $soTien,
                                    'Tài khoản nợ' => '',
                                    'Tài khoản có' =>  '',
                                    'Mã khách hàng' => @$receipt->ma_khach_hang,
                                    'Tên khách hàng' => @$receipt->ten_khach_hang,
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
                                    'Tên căn hộ' => $aprtment_name,
                                    'Chủ hộ' => $receipt->customer_name,
                                    'Hình thức' => Helper::loai_danh_muc[$receipt->type_payment] ?? $receipt->type_payment,
                                    'Biển số xe' =>@$service_apart && @$service_apart->bdc_vehicle_id > 0 ? @$vehicle->number : null
                                ];
                            }
                            if ($receipt->type == 'phieu_ke_toan') {
                                $apartment_service = ApartmentServicePriceRepository::getInfoServiceApartmentById($_data->bdc_apartment_service_price_id);
                                $vehicle_2 = $apartment_service && $apartment_service->bdc_vehicle_id > 0 ? Vehicles::get_detail_vehicle_by_id($apartment_service->bdc_vehicle_id) :null;
                                $dienGiai = $apartment_service && $apartment_service->bdc_vehicle_id > 0 ? $apartment_service->name. ' - ' . @$vehicle_2->number : @$apartment_service->name . " " . $kyKeToan;
                                $content[] = [
                                    'Loại NK' => $loaiNK,
                                    'Số chứng từ' => $soChungTu,
                                    'Kỳ bảng kê' => $kyKeToan,
                                    'Kỳ kế toán' => date('Ym', strtotime(@$receipt->create_date)),
                                    'Ngày lập phiếu' => $ngayLapPhieu,
                                    'Ngày hạch toán' => $ngayNgayHachToan,
                                    'Diễn giải' => $dienGiai,
                                    'Mã khách hàng-NCC' => $maKhachHangNNC,
                                    'Mã ngân hàng' =>'',
                                    'Ngân hàng' =>'',
                                    'Cty Con - NH cho DXMB vay' => $ctyCon,
                                    'Mã phòng ban' => $maPhongBan,
                                    'Mã nhân viên' => $maNhanVien,
                                    'Mã phí' =>  $apartment_service && $apartment_service->bdc_vehicle_id > 0 ? @$apartment_service->name . ' - ' . @$vehicle_2->number : @$apartment_service->name,
                                    'Hợp đồng' => $hopDong,
                                    'Chi tiết sản phẩm' => @$apartment_service->name,
                                    'Sản phẩm' => $sanPham,
                                    'Block' => $block,
                                    'Dự án' => $duAn,
                                    'Mã thu' => $maThu,
                                    'Khế ước' => $kheUoc,
                                    'CP không hợp lệ' => $cpKhongHopLe,
                                    'Mã tài khoản' => $maTaiKhoan,
                                    'TK Dư' => $tkDu,
                                    'Số tiền' => $soTien,
                                    'Tài khoản nợ' => '',
                                    'Tài khoản có' =>  '',
                                    'Mã khách hàng' => @$receipt->ma_khach_hang,
                                    'Tên khách hàng' => @$receipt->ten_khach_hang,
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
                                    'Tên căn hộ' => $aprtment_name,
                                    'Chủ hộ' => $receipt->customer_name,
                                    'Hình thức' => Helper::loai_danh_muc[$receipt->type_payment] ?? $receipt->type_payment,
                                    'Biển số xe' => @$vehicle_2->number
                                ];
                                $get_accounting_source = LogCoinDetailRepository::get_accounting_source($receipt->id,$_data);
                                if($get_accounting_source && $get_accounting_source->type=4)
                                {
                                   
                                    $apartment_service =  ApartmentServicePriceRepository::getInfoServiceApartmentById($get_accounting_source->bdc_apartment_service_price_id);
                                    $service = $apartment_service ? Service::get_detail_bdc_service_by_bdc_service_id($apartment_service->bdc_service_id) : null;
                                    $vehicle =@$apartment_service->bdc_vehicle_id > 0 ? Vehicles::get_detail_vehicle_by_id($apartment_service->bdc_vehicle_id) : null;
                                    $new_service = $apartment_service && $apartment_service->bdc_vehicle_id > 0 ? @$apartment_service->name . ' - ' . @$vehicle->number : @$apartment_service->name;
                                    $content[] = [
                                        'Loại NK' => $loaiNK,
                                        'Số chứng từ' => $soChungTu,
                                        'Kỳ bảng kê' => $kyKeToan,
                                        'Kỳ kế toán' => date('Ym', strtotime(@$receipt->create_date)),
                                        'Ngày lập phiếu' => $ngayLapPhieu,
                                        'Ngày hạch toán' => $ngayNgayHachToan,
                                        'Diễn giải' => $new_service,
                                        'Mã khách hàng-NCC' => $maKhachHangNNC,
                                        'Mã ngân hàng' =>'',
                                        'Ngân hàng' =>'',
                                        'Cty Con - NH cho DXMB vay' => $ctyCon,
                                        'Mã phòng ban' => $maPhongBan,
                                        'Mã nhân viên' => $maNhanVien,
                                        'Mã phí' =>  $new_service,
                                        'Hợp đồng' => $hopDong,
                                        'Chi tiết sản phẩm' => @$apartment_service->name,
                                        'Sản phẩm' => $sanPham,
                                        'Block' => $block,
                                        'Dự án' => $duAn,
                                        'Mã thu' => @$service->code_receipt,
                                        'Khế ước' => $kheUoc,
                                        'CP không hợp lệ' => $cpKhongHopLe,
                                        'Mã tài khoản' => $maTaiKhoan,
                                        'TK Dư' => $tkDu,
                                        'Số tiền' => 0 - $soTien,
                                        'Tài khoản nợ' => '',
                                        'Tài khoản có' =>  '',
                                        'Mã khách hàng' => @$receipt->ma_khach_hang,
                                        'Tên khách hàng' => @$receipt->ten_khach_hang,
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
                                        'Tên căn hộ' => $aprtment_name,
                                        'Chủ hộ' => $receipt->customer_name,
                                        'Hình thức' => Helper::loai_danh_muc[$receipt->type_payment] ?? $receipt->type_payment,
                                        'Biển số xe' => @$vehicle->number
                                    ];
                                }
                            }
                            if ($check_paid_coin != $_data->bdc_apartment_service_price_id) {
                                $check_paid_coin = $_data->bdc_apartment_service_price_id;
                            }
                        }
                    } else {
                        //Tiền thừa
                        $nguon_hach_toan_v1 = LogCoinDetailRepository::get_by_from_id_accounting($receipt->id);
                        if($nguon_hach_toan_v1){
                            foreach ($nguon_hach_toan_v1 as $value_tien_thua) {
                                $content[] = [
                                    'Loại NK' => $status,
                                    'Số chứng từ' => $receipt->receipt_code,
                                    'Kỳ bảng kê' => "",
                                    'Kỳ kế toán' => date('Ym', strtotime(@$receipt->create_date)),
                                    'Ngày lập phiếu' => $receipt->created_at->format('d/m/Y'),
                                    'Ngày hạch toán' => @$receipt->create_date ? date('d/m/Y', strtotime(@$receipt->create_date)) : '--/--/----',
                                    'Diễn giải' => "tiền thừa",
                                    'Mã khách hàng-NCC' => @$_maKhachHangNNC,
                                    'Mã ngân hàng' =>'',
                                    'Ngân hàng' =>'',
                                    'Cty Con - NH cho DXMB vay' => "",
                                    'Mã phòng ban' => "",
                                    'Mã nhân viên' => "",
                                    'Mã phí' => "",
                                    'Hợp đồng' => "",
                                    'Chi tiết sản phẩm' => "",
                                    'Sản phẩm' => $apartment->code ?? null,
                                    'Block' => @$buildingPlace->code ?? null,
                                    'Dự án' => $building->name,
                                    'Mã thu' => "",
                                    'Khế ước' => "",
                                    'CP không hợp lệ' => "",
                                    'Mã tài khoản' => "",
                                    'TK Dư' => "",
                                    'Số tiền' => 0 - (int)$value_tien_thua->coin,
                                    'Tài khoản nợ' => '',
                                    'Tài khoản có' =>  '',
                                    'Mã khách hàng' => @$receipt->ma_khach_hang,
                                    'Tên khách hàng' => @$receipt->ten_khach_hang,
                                    'Ký hiệu hóa đơn' => "",
                                    'Ngày hóa đơn' => "",
                                    'Loại thuế' => "",
                                    'Thuế suất' => "",
                                    'Tiền trước thuế' => "",
                                    'Mã số hóa đơn' => "",
                                    'Người nộp/Nhận tiền' => "",
                                    'Người bán hàng' => "",
                                    'Phiếu cấn trừ' => "",
                                    'Mã chứng từ eApprove' => "",
                                    'Ghi chú' => $receipt->receipt_code,
                                    'Nhóm dịch vụ' => "",
                                    'receipt_type' => $receipt->type,
                                    'Tên căn hộ' =>  @$apartment->name,
                                    'Chủ hộ' => $receipt->customer_name,
                                    'Hình thức' => isset($receipt->type_payment)&&$receipt->type_payment ? Helper::loai_danh_muc[$receipt->type_payment] ?? $receipt->type_payment : "",
                                    'Biển số xe' => ''
                                ];
                            }
                        }
                        if ($receipt->type == 'phieu_ke_toan') {
                            $content[] = [
                                'Loại NK' => $status,
                                'Số chứng từ' => $receipt->receipt_code,
                                'Kỳ bảng kê' => "",
                                'Kỳ kế toán' => date('Ym', strtotime(@$receipt->create_date)),
                                'Ngày lập phiếu' => $receipt->created_at->format('d/m/Y'),
                                'Ngày hạch toán' => @$receipt->create_date ? date('d/m/Y', strtotime(@$receipt->create_date)) : '--/--/----',
                                'Diễn giải' => $receipt->description,
                                'Mã khách hàng-NCC' => @$_maKhachHangNNC,
                                'Mã ngân hàng' => '',
                                'Ngân hàng' => '',
                                'Cty Con - NH cho DXMB vay' => "",
                                'Mã phòng ban' => "",
                                'Mã nhân viên' => "",
                                'Mã phí' => "",
                                'Hợp đồng' => "",
                                'Chi tiết sản phẩm' => "",
                                'Sản phẩm' => $apartment->code ?? null,
                                'Block' => @$buildingPlace->code ?? null,
                                'Dự án' => $building->name,
                                'Mã thu' => "",
                                'Khế ước' => "",
                                'CP không hợp lệ' => "",
                                'Mã tài khoản' => "",
                                'TK Dư' => "",
                                'Số tiền' => $receipt->cost,
                                'Tài khoản nợ' => '',
                                'Tài khoản có' =>  '',
                                'Mã khách hàng' => @$receipt->ma_khach_hang,
                                'Tên khách hàng' => @$receipt->ten_khach_hang,
                                'Ký hiệu hóa đơn' => "",
                                'Ngày hóa đơn' => "",
                                'Loại thuế' => "",
                                'Thuế suất' => "",
                                'Tiền trước thuế' => "",
                                'Mã số hóa đơn' => "",
                                'Người nộp/Nhận tiền' => "",
                                'Người bán hàng' => "",
                                'Phiếu cấn trừ' => "",
                                'Mã chứng từ eApprove' => "",
                                'Ghi chú' => $receipt->receipt_code,
                                'Nhóm dịch vụ' => "",
                                'receipt_type' => $receipt->type,
                                'Tên căn hộ' =>  @$apartment->name,
                                'Chủ hộ' => $receipt->customer_name,
                                'Hình thức' => isset($receipt->type_payment) && $receipt->type_payment ? Helper::loai_danh_muc[$receipt->type_payment] ?? $receipt->type_payment : "",
                                'Biển số xe' => ''
                            ];
                        } 
                    }
                    if ($getTienThua) {
                        foreach ($getTienThua as $key_1 => $value_1) {
                            $apartmentServicePrice = @$value_1->bdc_apartment_service_price_id != 0 ? ApartmentServicePrice::get_detail_bdc_apartment_service_price_by_apartment_id($value_1->bdc_apartment_service_price_id) : null;
                            $service = $apartmentServicePrice ? Service::get_detail_bdc_service_by_bdc_service_id($apartmentServicePrice->bdc_service_id) : null;
                            $vehicle = @$apartmentServicePrice->bdc_vehicle_id > 0 ? Vehicles::get_detail_vehicle_by_id($apartmentServicePrice->bdc_vehicle_id) : null;

                            $content[] = [
                                'Loại NK' => $status,
                                'Số chứng từ' => $receipt->receipt_code,
                                'Kỳ bảng kê' => "",
                                'Kỳ kế toán' => date('Ym', strtotime(@$receipt->create_date)),
                                'Ngày lập phiếu' => $receipt->created_at->format('d/m/Y'),
                                'Ngày hạch toán' => @$receipt->create_date ? date('d/m/Y', strtotime(@$receipt->create_date)) : '--/--/----',
                                'Diễn giải' => "tiền thừa",
                                'Mã khách hàng-NCC' => @$_maKhachHangNNC,
                                'Mã ngân hàng' => '',
                                'Ngân hàng' => '',
                                'Cty Con - NH cho DXMB vay' => "",
                                'Mã phòng ban' => "",
                                'Mã nhân viên' => "",
                                'Mã phí' =>  $value_1->bdc_apartment_service_price_id == 0 ? 'Tiền thừa chưa chỉ định' : @$service->name.'-'.@$vehicle->number.'(tiền thừa)',
                                'Hợp đồng' => "",
                                'Chi tiết sản phẩm' => "",
                                'Sản phẩm' => $apartment->code ?? null,
                                'Block' => @$buildingPlace->code ?? null,
                                'Dự án' => $building->name,
                                'Mã thu' => "",
                                'Khế ước' => "",
                                'CP không hợp lệ' => "",
                                'Mã tài khoản' => "",
                                'TK Dư' => "",
                                'Số tiền' => $value_1->coin,
                                'Tài khoản nợ' => '',
                                'Tài khoản có' =>  '',
                                'Mã khách hàng' => @$receipt->ma_khach_hang,
                                'Tên khách hàng' => @$receipt->ten_khach_hang,
                                'Ký hiệu hóa đơn' => "",
                                'Ngày hóa đơn' => "",
                                'Loại thuế' => "",
                                'Thuế suất' => "",
                                'Tiền trước thuế' => "",
                                'Mã số hóa đơn' => "",
                                'Người nộp/Nhận tiền' => "",
                                'Người bán hàng' => "",
                                'Phiếu cấn trừ' => "",
                                'Mã chứng từ eApprove' => "",
                                'Ghi chú' => $receipt->receipt_code,
                                'Nhóm dịch vụ' => "",
                                'receipt_type' => $receipt->type,
                                'Tên căn hộ' =>  @$apartment->name,
                                'Chủ hộ' => $receipt->customer_name,
                                'Hình thức' => isset($receipt->type_payment) && $receipt->type_payment ? Helper::loai_danh_muc[$receipt->type_payment] ?? $receipt->type_payment : "",
                                'Biển số xe' => ''
                            ];

                        }
                       
                    }
                    if($receipt->type == 'phieu_chi'){
                        $get_detail = isset($receipt->logs)? json_decode($receipt->logs) : null;
                        if($get_detail){
                            foreach ($get_detail as $key => $value) {
                                if(str_contains($value->service_apartment_id, 'tien_thua_') ){ 
                                    $service_apartment_id = explode('tien_thua_',$value->service_apartment_id)[1];
                                    $apartmentServicePrice = $service_apartment_id != 0 ? ApartmentServicePrice::get_detail_bdc_apartment_service_price_by_apartment_id($service_apartment_id) : null;
                                    $service = $apartmentServicePrice ? Service::get_detail_bdc_service_by_bdc_service_id($apartmentServicePrice->bdc_service_id) : null;
                                    $vehicle = @$apartmentServicePrice->bdc_vehicle_id > 0 ? Vehicles::get_detail_vehicle_by_id($apartmentServicePrice->bdc_vehicle_id) : null;
                                    $get_accounting_source = LogCoinDetailRepository::get_accounting_source_service_apartment_id_by_payment_slip($receipt->id,$service_apartment_id);
                                    if($get_accounting_source)
                                    {
                                        $content[] = [
                                            'Loại NK' => $status,
                                            'Số chứng từ' => $receipt->receipt_code,
                                            'Kỳ bảng kê' => "",
                                            'Kỳ kế toán' => date('Ym', strtotime(@$receipt->create_date)),
                                            'Ngày lập phiếu' => $receipt->created_at->format('d/m/Y'),
                                            'Ngày hạch toán' => @$receipt->create_date ? date('d/m/Y', strtotime(@$receipt->create_date)) : '--/--/----',
                                            'Diễn giải' => $receipt->description,
                                            'Mã khách hàng-NCC' => @$_maKhachHangNNC,
                                            'Mã ngân hàng' => '',
                                            'Ngân hàng' => '',
                                            'Cty Con - NH cho DXMB vay' => "",
                                            'Mã phòng ban' => "",
                                            'Mã nhân viên' => "",
                                            'Mã phí' => $service_apartment_id == 0 ? 'Chi tiền chưa chỉ định' : @$service->name.'-'.@$vehicle->number.'(chi tiền)',
                                            'Hợp đồng' => "",
                                            'Chi tiết sản phẩm' => "",
                                            'Sản phẩm' => $apartment->code ?? null,
                                            'Block' => @$buildingPlace->code ?? null,
                                            'Dự án' => $building->name,
                                            'Mã thu' => "",
                                            'Khế ước' => "",
                                            'CP không hợp lệ' => "",
                                            'Mã tài khoản' => "",
                                            'TK Dư' => "",
                                            'Số tiền' => 0 - $get_accounting_source->coin,
                                            'Tài khoản nợ' => '',
                                            'Tài khoản có' =>  '',
                                            'Mã khách hàng' => @$receipt->ma_khach_hang,
                                            'Tên khách hàng' => @$receipt->ten_khach_hang,
                                            'Ký hiệu hóa đơn' => "",
                                            'Ngày hóa đơn' => "",
                                            'Loại thuế' => "",
                                            'Thuế suất' => "",
                                            'Tiền trước thuế' => "",
                                            'Mã số hóa đơn' => "",
                                            'Người nộp/Nhận tiền' => "",
                                            'Người bán hàng' => "",
                                            'Phiếu cấn trừ' => "",
                                            'Mã chứng từ eApprove' => "",
                                            'Ghi chú' => $receipt->receipt_code,
                                            'Nhóm dịch vụ' => "",
                                            'receipt_type' => $receipt->type,
                                            'Tên căn hộ' =>  @$apartment->name,
                                            'Chủ hộ' => $receipt->customer_name,
                                            'Hình thức' => Helper::loai_danh_muc[$receipt->type_payment] ?? $receipt->type_payment,
                                            'Biển số xe' => @$vehicle->number
                                        ];
                                    }
                                }
                             }
                        }
        
                    }
                    if($receipt->type == 'phieu_chi_khac' || $receipt->type == 'phieu_hoan_ky_quy'){
                        $content[] = [
                            'Loại NK' => $status,
                            'Số chứng từ' => $receipt->receipt_code,
                            'Kỳ bảng kê' => "",
                            'Kỳ kế toán' => date('Ym', strtotime(@$receipt->create_date)),
                            'Ngày lập phiếu' => $receipt->created_at->format('d/m/Y'),
                            'Ngày hạch toán' => @$receipt->create_date ? date('d/m/Y', strtotime(@$receipt->create_date)) : '--/--/----',
                            'Diễn giải' => $receipt->description,
                            'Mã khách hàng-NCC' => @$_maKhachHangNNC,
                            'Mã ngân hàng' => '',
                            'Ngân hàng' => '',
                            'Cty Con - NH cho DXMB vay' => "",
                            'Mã phòng ban' => "",
                            'Mã nhân viên' => "",
                            'Mã phí' => "",
                            'Hợp đồng' => "",
                            'Chi tiết sản phẩm' => "",
                            'Sản phẩm' => $apartment->code ?? null,
                            'Block' => @$buildingPlace->code ?? null,
                            'Dự án' => $building->name,
                            'Mã thu' => "",
                            'Khế ước' => "",
                            'CP không hợp lệ' => "",
                            'Mã tài khoản' => "",
                            'TK Dư' => "",
                            'Số tiền' => 0 - $receipt->cost,
                            'Tài khoản nợ' => '',
                            'Tài khoản có' =>  '',
                            'Mã khách hàng' => @$receipt->ma_khach_hang,
                            'Tên khách hàng' => @$receipt->ten_khach_hang,
                            'Ký hiệu hóa đơn' => "",
                            'Ngày hóa đơn' => "",
                            'Loại thuế' => "",
                            'Thuế suất' => "",
                            'Tiền trước thuế' => "",
                            'Mã số hóa đơn' => "",
                            'Người nộp/Nhận tiền' => "",
                            'Người bán hàng' => "",
                            'Phiếu cấn trừ' => "",
                            'Mã chứng từ eApprove' => "",
                            'Ghi chú' => $receipt->receipt_code,
                            'Nhóm dịch vụ' => "",
                            'receipt_type' => $receipt->type,
                            'Tên căn hộ' =>  @$apartment->name,
                            'Chủ hộ' => $receipt->customer_name,
                            'Hình thức' => isset($receipt->type_payment) && $receipt->type_payment ? Helper::loai_danh_muc[$receipt->type_payment] ?? $receipt->type_payment : "",
                            'Biển số xe' => ''
                        ];
                    }
                    if($receipt->type == 'phieu_thu_truoc' || $receipt->type == 'phieu_thu_ky_quy'){
                        $content[] = [
                            'Loại NK' => $status,
                            'Số chứng từ' => $receipt->receipt_code,
                            'Kỳ bảng kê' => "",
                            'Kỳ kế toán' => date('Ym', strtotime(@$receipt->create_date)),
                            'Ngày lập phiếu' => $receipt->created_at->format('d/m/Y'),
                            'Ngày hạch toán' => @$receipt->create_date ? date('d/m/Y', strtotime(@$receipt->create_date)) : '--/--/----',
                            'Diễn giải' => $receipt->description,
                            'Mã khách hàng-NCC' => @$_maKhachHangNNC,
                            'Mã ngân hàng' => '',
                            'Ngân hàng' => '',
                            'Cty Con - NH cho DXMB vay' => "",
                            'Mã phòng ban' => "",
                            'Mã nhân viên' => "",
                            'Mã phí' => "",
                            'Hợp đồng' => "",
                            'Chi tiết sản phẩm' => "",
                            'Sản phẩm' => $apartment->code ?? null,
                            'Block' => @$buildingPlace->code ?? null,
                            'Dự án' => $building->name,
                            'Mã thu' => "",
                            'Khế ước' => "",
                            'CP không hợp lệ' => "",
                            'Mã tài khoản' => "",
                            'TK Dư' => "",
                            'Số tiền' => $receipt->cost,
                            'Tài khoản nợ' => '',
                            'Tài khoản có' =>  '',
                            'Mã khách hàng' => @$receipt->ma_khach_hang,
                            'Tên khách hàng' => @$receipt->ten_khach_hang,
                            'Ký hiệu hóa đơn' => "",
                            'Ngày hóa đơn' => "",
                            'Loại thuế' => "",
                            'Thuế suất' => "",
                            'Tiền trước thuế' => "",
                            'Mã số hóa đơn' => "",
                            'Người nộp/Nhận tiền' => "",
                            'Người bán hàng' => "",
                            'Phiếu cấn trừ' => "",
                            'Mã chứng từ eApprove' => "",
                            'Ghi chú' => $receipt->receipt_code,
                            'Nhóm dịch vụ' => "",
                            'receipt_type' => $receipt->type,
                            'Tên căn hộ' =>  @$apartment->name,
                            'Chủ hộ' => $receipt->customer_name,
                            'Hình thức' => isset($receipt->type_payment) && $receipt->type_payment ? Helper::loai_danh_muc[$receipt->type_payment] ?? $receipt->type_payment : "",
                            'Biển số xe' => ''
                        ];
                    }

                }
                $customer = CustomersRespository::findApartmentIdV2(@$request['bdc_apartment_id'], 0);
                $pubUserProfile = $customer ? PublicUsersProfileRespository::getInfoUserById($customer->user_info_id) : null;
                $customerName = $customer ? @$pubUserProfile->full_name : "";
                $fromDate = false;
                if (isset($request['from_date'])  && $request['from_date'] != null) {
                    $created_at_from_date = Carbon::parse($request['from_date'])->format('Y-m-d');
                    $fromDate = $created_at_from_date . " 00:00:00";
                }

                $toDate = false;
                if (isset($request['to_date'])  && $request['to_date'] != null) {
                    $created_at_to_date = Carbon::parse($request['to_date'])->format('Y-m-d');
                    $toDate = $created_at_to_date . " 23:59:59";
                }

                if($fromDate && $toDate){
                       // lấy ra danh sách phân bổ
                       $dataPhanbo = LogCoinDetailRepository::getDataByFromtypeFlowAllocation($buildingId, $fromDate, $toDate, @$request['bdc_apartment_id'] ?? null);
                        foreach ($dataPhanbo as $item) {
                            if(@$item->coin == 0){
                            continue;
                            }
                            $ghichu=null;
                            $so_chung_tu = null;
                            if (@$item->from_type == 1 || @$item->from_type == 4 || @$item->from_type == 6){
                                $rs_receipt = Receipts::get_detail_receipt_by_receipt_id($item->from_id);
                                $ghichu = @$rs_receipt->description;
                                $so_chung_tu = "PB_" . $item->id;
                            }
                            else if(@$item->from_type == 2){
                                $ghichu ='Hạch toán tự động';
                                $so_chung_tu = "AUTO_" . $item->id;
                            }
                            else if(@$item->from_type == 5){
                                $rs_receipt = Receipts::get_detail_receipt_by_receipt_id($item->from_id);
                                $ghichu ='[Huỷ phiếu thu]_'.@$rs_receipt->description;
                                $so_chung_tu = "PB_" . $item->id;
                            }
                            else{
                                $ghichu = @$item->note;
                                $so_chung_tu = "PB_" . $item->id;
                            }
                            if ($item->bdc_apartment_service_price_id != 0) {
                                $servicePrice = ApartmentServicePriceRepository::getInfoServiceApartmentById($item->bdc_apartment_service_price_id);
                                $service = $servicePrice ? Service::get_detail_bdc_service_by_bdc_service_id($servicePrice->bdc_service_id) : null;
                                $vehicle_3 =  @$servicePrice->bdc_vehicle_id > 0 ? Vehicles::get_detail_vehicle_by_id($servicePrice->bdc_vehicle_id) : null;
                                $ma_phi = @$servicePrice->bdc_vehicle_id > 0 ? @$service->name . ' - ' . @$vehicle_3->number : @$service->name;
                            }
                            if ($item->from_id != 0) {
                                $servicePrice = ApartmentServicePriceRepository::getInfoServiceApartmentById($item->from_id);
                                $service_b =$servicePrice ? Service::get_detail_bdc_service_by_bdc_service_id($servicePrice->bdc_service_id) : null;
                                $vehicle_3 =  @$servicePrice->bdc_vehicle_id > 0 ? Vehicles::get_detail_vehicle_by_id($servicePrice->bdc_vehicle_id) : null;
                                $ma_phi_b = @$servicePrice->bdc_vehicle_id > 0 ? @$service_b->name . ' - ' . @$vehicle_3->number : @$service_b->name;
                            }

                            $name_service_a = $item->bdc_apartment_service_price_id != 0 ? @$service->name : "chưa chỉ định";
                            $name_service_b = $item->from_id != 0 ? @$service_b->name : "chưa chỉ định";

                            $diendai = 'Phân bổ tiền thừa từ "' . $name_service_b . '" sang "' . $name_service_a . '"';
                            $apartment = ApartmentsRespository::getInfoApartmentsById($item->bdc_apartment_id);
                            $buildingPlace = BuildingPlaceRepository::getInfoBuildingPlaceById($apartment->building_place_id);
                            $content[] = [
                                'Loại NK' => 'PKT',
                                'Số chứng từ' => $so_chung_tu,
                                'Kỳ bảng kê' =>  @$item->cycle_name,
                                'Kỳ kế toán' =>  @$rs_receipt ? date('Ym', strtotime(@$rs_receipt->create_date)) : date('Ym', strtotime(@$item->created_at)),
                                'Ngày lập phiếu' => @$rs_receipt ? $rs_receipt->created_at->format('d/m/Y') : date('d/m/Y', strtotime(@$item->created_at)),
                                'Ngày hạch toán' =>  @$rs_receipt ? date('d/m/Y', strtotime(@$rs_receipt->create_date)) : date('d/m/Y', strtotime(@$item->created_at)),
                                'Diễn giải' => $diendai,
                                'Mã khách hàng-NCC' => $_maKhachHangNNC,
                                'Mã ngân hàng' =>'',
                                'Ngân hàng' =>'',
                                'Cty Con - NH cho DXMB vay' => '',
                                'Mã phòng ban' => '',
                                'Mã nhân viên' => '',
                                'Mã phí' =>   $item->bdc_apartment_service_price_id != 0 ? $ma_phi : null,
                                'Hợp đồng' => '',
                                'Chi tiết sản phẩm' => @$name_service_a->name,
                                'Sản phẩm' => $apartment->code,
                                'Block' => @$buildingPlace->code,
                                'Dự án' =>  $building->name,
                                'Mã thu' => $item->bdc_apartment_service_price_id != 0 ? @$service->code_receipt : null,
                                'Khế ước' => '',
                                'CP không hợp lệ' => '',
                                'Mã tài khoản' => '',
                                'TK Dư' => '',
                                'Số tiền' => (int)@$item->coin,
                                'Tài khoản nợ' => '',
                                'Tài khoản có' =>  '',
                                'Mã khách hàng' => @$receipt->ma_khach_hang,
                                'Tên khách hàng' => @$receipt->ten_khach_hang,
                                'Ký hiệu hóa đơn' => '',
                                'Ngày hóa đơn' => '',
                                'Loại thuế' => '',
                                'Thuế suất' => '',
                                'Tiền trước thuế' => '',
                                'Mã số hóa đơn' => '',
                                'Người nộp/Nhận tiền' => '',
                                'Người bán hàng' => '',
                                'Phiếu cấn trừ' => '',
                                'Mã chứng từ eApprove' => '',
                                'Ghi chú' =>  $ghichu,
                                'Nhóm dịch vụ' => '',
                                'receipt_type' => $receipt->type,
                                'Tên căn hộ' =>$apartment->name,
                                'Chủ hộ' => $customerName,
                                'Hình thức' => Helper::loai_danh_muc[$receipt->type_payment] ?? $receipt->type_payment,
                                'Biển số xe' => @$servicePrice->bdc_vehicle_id > 0 ? @$vehicle_3->number : null
                            ];
                            $content[] = [
                                'Loại NK' => 'PKT',
                                'Số chứng từ' => $so_chung_tu,
                                'Kỳ bảng kê' =>  @$item->cycle_name,
                                'Kỳ kế toán' =>  @$rs_receipt ? date('Ym', strtotime(@$rs_receipt->create_date)) : date('Ym', strtotime(@$item->created_at)),
                                'Ngày lập phiếu' => @$rs_receipt ? $rs_receipt->created_at->format('d/m/Y') : date('d/m/Y', strtotime(@$item->created_at)),
                                'Ngày hạch toán' =>  @$rs_receipt ? date('d/m/Y', strtotime(@$rs_receipt->create_date)) : date('d/m/Y', strtotime(@$item->created_at)),
                                'Diễn giải' => $diendai,
                                'Mã khách hàng-NCC' => $_maKhachHangNNC,
                                'Mã ngân hàng' =>'',
                                'Ngân hàng' =>'',
                                'Cty Con - NH cho DXMB vay' => '',
                                'Mã phòng ban' => '',
                                'Mã nhân viên' => '',
                                'Mã phí' =>   $item->from_id != 0 ? $ma_phi_b : null,
                                'Hợp đồng' => '',
                                'Chi tiết sản phẩm' => @$name_service_b->name,
                                'Sản phẩm' => $apartment->code,
                                'Block' => @$buildingPlace->code,
                                'Dự án' =>  $building->name,
                                'Mã thu' =>  $item->from_id != 0 ? @$service_b->code_receipt : null,
                                'Khế ước' => '',
                                'CP không hợp lệ' => '',
                                'Mã tài khoản' => '',
                                'TK Dư' => '',
                                'Số tiền' =>0 - (int)@$item->coin,
                                'Tài khoản nợ' => '',
                                'Tài khoản có' =>  '',
                                'Mã khách hàng' => @$receipt->ma_khach_hang,
                                'Tên khách hàng' => @$receipt->ten_khach_hang,
                                'Ký hiệu hóa đơn' => '',
                                'Ngày hóa đơn' => '',
                                'Loại thuế' => '',
                                'Thuế suất' => '',
                                'Tiền trước thuế' => '',
                                'Mã số hóa đơn' => '',
                                'Người nộp/Nhận tiền' => '',
                                'Người bán hàng' => '',
                                'Phiếu cấn trừ' => '',
                                'Mã chứng từ eApprove' => '',
                                'Ghi chú' =>  $ghichu,
                                'Nhóm dịch vụ' => '',
                                'receipt_type' => $receipt->type,
                                'Tên căn hộ' =>$apartment->name,
                                'Chủ hộ' =>$customerName,
                                'Hình thức' => Helper::loai_danh_muc[$receipt->type_payment] ?? $receipt->type_payment,
                                'Biển số xe' => @$servicePrice->bdc_vehicle_id > 0 ? @$vehicle_3->number : null
                            ];
                        }
                         // lấy ra danh sách phân bổ
                         $dataHachToan = LogCoinDetailRepository::getDataByFromtypeAutoAccounting($buildingId, $fromDate, $toDate, @$request['bdc_apartment_id'] ?? null);
                         foreach ($dataHachToan as $item) {
                             if(@$item->coin == 0){
                             continue;
                             }
                             $ghichu=null;
                             $so_chung_tu = null;
                             if (@$item->from_type == 1 || @$item->from_type == 4 || @$item->from_type == 6){
                                 $rs_receipt = Receipts::get_detail_receipt_by_receipt_id($item->from_id);
                                 $ghichu = @$rs_receipt->description;
                                 $so_chung_tu = "PB_" . $item->id;
                             }
                             else if(@$item->from_type == 2){
                                 $ghichu ='Hạch toán tự động';
                                 $so_chung_tu = "AUTO_" . $item->id;
                             }
                             else if(@$item->from_type == 5){
                                 $rs_receipt = Receipts::get_detail_receipt_by_receipt_id($item->from_id);
                                 $ghichu ='[Huỷ phiếu thu]_'.@$rs_receipt->description;
                                 $so_chung_tu = "PB_" . $item->id;
                             }
                             else{
                                 $ghichu = @$item->note;
                                 $so_chung_tu = "PB_" . $item->id;
                             }
                             if ($item->bdc_apartment_service_price_id != 0) {
                                 $servicePrice = ApartmentServicePriceRepository::getInfoServiceApartmentById($item->bdc_apartment_service_price_id);
                                 $service = $servicePrice ? Service::get_detail_bdc_service_by_bdc_service_id($servicePrice->bdc_service_id) : null;
                                 $vehicle_3 =  @$servicePrice->bdc_vehicle_id > 0 ? Vehicles::get_detail_vehicle_by_id($servicePrice->bdc_vehicle_id) : null;
                                 $ma_phi = @$servicePrice->bdc_vehicle_id > 0 ? @$service->name . ' - ' . @$vehicle_3->number : @$service->name;
                             }
                             if ($item->from_id != 0) {
                                 $servicePrice = ApartmentServicePriceRepository::getInfoServiceApartmentById($item->from_id);
                                 $service_b =$servicePrice ? Service::get_detail_bdc_service_by_bdc_service_id($servicePrice->bdc_service_id) : null;
                                 $vehicle_3 =  @$servicePrice->bdc_vehicle_id > 0 ? Vehicles::get_detail_vehicle_by_id($servicePrice->bdc_vehicle_id) : null;
                                 $ma_phi_b = @$servicePrice->bdc_vehicle_id > 0 ? @$service_b->name . ' - ' . @$vehicle_3->number : @$service_b->name;
                             }
 
                             $name_service_a = $item->bdc_apartment_service_price_id != 0 ? @$service->name : "chưa chỉ định";
                             $name_service_b = $item->from_id != 0 ? @$service_b->name : "chưa chỉ định";
 
                             $diendai = 'Hạch toán tiền thừa từ "' . $name_service_b . '" sang "' . $name_service_a . '"';
                             $apartment = ApartmentsRespository::getInfoApartmentsById($item->bdc_apartment_id);
                             $buildingPlace = BuildingPlaceRepository::getInfoBuildingPlaceById($apartment->building_place_id);
                             $content[] = [
                                 'Loại NK' => 'PKT',
                                 'Số chứng từ' => $so_chung_tu,
                                 'Kỳ bảng kê' =>  @$item->cycle_name,
                                 'Kỳ kế toán' =>  @$rs_receipt ? date('Ym', strtotime(@$rs_receipt->create_date)) : date('Ym', strtotime(@$item->created_at)),
                                 'Ngày lập phiếu' => @$rs_receipt ? $rs_receipt->created_at->format('d/m/Y') : date('d/m/Y', strtotime(@$item->created_at)),
                                 'Ngày hạch toán' =>  @$rs_receipt ? date('d/m/Y', strtotime(@$rs_receipt->create_date)) : date('d/m/Y', strtotime(@$item->created_at)),
                                 'Diễn giải' => $diendai,
                                 'Mã khách hàng-NCC' => @$_maKhachHangNNC,
                                 'Mã ngân hàng' =>'',
                                 'Ngân hàng' =>'',
                                 'Cty Con - NH cho DXMB vay' => '',
                                 'Mã phòng ban' => '',
                                 'Mã nhân viên' => '',
                                 'Mã phí' =>   $item->bdc_apartment_service_price_id != 0 ? $ma_phi : null,
                                 'Hợp đồng' => '',
                                 'Chi tiết sản phẩm' => @$name_service_a->name,
                                 'Sản phẩm' => $apartment->code,
                                 'Block' => @$buildingPlace->code,
                                 'Dự án' =>  $building->name,
                                 'Mã thu' => $item->bdc_apartment_service_price_id != 0 ? @$service->code_receipt : null,
                                 'Khế ước' => '',
                                 'CP không hợp lệ' => '',
                                 'Mã tài khoản' => '',
                                 'TK Dư' => '',
                                 'Số tiền' => (int)@$item->coin,
                                 'Tài khoản nợ' => '',
                                 'Tài khoản có' =>  '',
                                 'Mã khách hàng' => @$receipt->ma_khach_hang,
                                 'Tên khách hàng' => @$receipt->ten_khach_hang,
                                 'Ký hiệu hóa đơn' => '',
                                 'Ngày hóa đơn' => '',
                                 'Loại thuế' => '',
                                 'Thuế suất' => '',
                                 'Tiền trước thuế' => '',
                                 'Mã số hóa đơn' => '',
                                 'Người nộp/Nhận tiền' => '',
                                 'Người bán hàng' => '',
                                 'Phiếu cấn trừ' => '',
                                 'Mã chứng từ eApprove' => '',
                                 'Ghi chú' =>  $ghichu,
                                 'Nhóm dịch vụ' => '',
                                 'receipt_type' => $receipt->type,
                                 'Tên căn hộ' =>$apartment->name,
                                 'Chủ hộ' => $customerName,
                                 'Hình thức' => Helper::loai_danh_muc[$receipt->type_payment] ?? $receipt->type_payment,
                                 'Biển số xe' => @$servicePrice->bdc_vehicle_id > 0 ? @$vehicle_3->number : null
                             ];
                             $content[] = [
                                 'Loại NK' => 'PKT',
                                 'Số chứng từ' => $so_chung_tu,
                                 'Kỳ bảng kê' =>  @$item->cycle_name,
                                 'Kỳ kế toán' =>  @$rs_receipt ? date('Ym', strtotime(@$rs_receipt->create_date)) : date('Ym', strtotime(@$item->created_at)),
                                 'Ngày lập phiếu' => @$rs_receipt ? $rs_receipt->created_at->format('d/m/Y') : date('d/m/Y', strtotime(@$item->created_at)),
                                 'Ngày hạch toán' =>  @$rs_receipt ? date('d/m/Y', strtotime(@$rs_receipt->create_date)) : date('d/m/Y', strtotime(@$item->created_at)),
                                 'Diễn giải' => $diendai,
                                 'Mã khách hàng-NCC' => $_maKhachHangNNC,
                                 'Mã ngân hàng' =>'',
                                 'Ngân hàng' =>'',
                                 'Cty Con - NH cho DXMB vay' => '',
                                 'Mã phòng ban' => '',
                                 'Mã nhân viên' => '',
                                 'Mã phí' =>   $item->from_id != 0 ? $ma_phi_b : null,
                                 'Hợp đồng' => '',
                                 'Chi tiết sản phẩm' => @$name_service_b->name,
                                 'Sản phẩm' => $apartment->code,
                                 'Block' => @$buildingPlace->code,
                                 'Dự án' =>  $building->name,
                                 'Mã thu' =>  $item->from_id != 0 ? @$service_b->code_receipt : null,
                                 'Khế ước' => '',
                                 'CP không hợp lệ' => '',
                                 'Mã tài khoản' => '',
                                 'TK Dư' => '',
                                 'Số tiền' =>0 - (int)@$item->coin,
                                 'Tài khoản nợ' => '',
                                 'Tài khoản có' =>  '',
                                 'Mã khách hàng' => @$receipt->ma_khach_hang,
                                 'Tên khách hàng' => @$receipt->ten_khach_hang,
                                 'Ký hiệu hóa đơn' => '',
                                 'Ngày hóa đơn' => '',
                                 'Loại thuế' => '',
                                 'Thuế suất' => '',
                                 'Tiền trước thuế' => '',
                                 'Mã số hóa đơn' => '',
                                 'Người nộp/Nhận tiền' => '',
                                 'Người bán hàng' => '',
                                 'Phiếu cấn trừ' => '',
                                 'Mã chứng từ eApprove' => '',
                                 'Ghi chú' =>  $ghichu,
                                 'Nhóm dịch vụ' => '',
                                 'receipt_type' => $receipt->type,
                                 'Tên căn hộ' =>$apartment->name,
                                 'Chủ hộ' => $customerName,
                                 'Hình thức' => Helper::loai_danh_muc[$receipt->type_payment] ?? $receipt->type_payment,
                                 'Biển số xe' => @$servicePrice->bdc_vehicle_id > 0 ? @$vehicle_3->number : null
                             ];
                         }
                }
                if ($content) {
                    $sheet->loadView('receipt.v2._excelDetail', ["content" => $content]);
                }
            });
        })->store('xlsx',storage_path('exports/'));
        ob_end_clean();
        $file     = storage_path('exports/'.$result->filename.'.'.$result->ext);
        return response()->download($file)->deleteFileAfterSend(true);
             
    }

    //Duong remove old function for PHPExcel
    public function filterReceiptExcelDetail_v3($buildingId, $request, $debitDetailRepository, $serviceRepository)
    {
        $receipts = $this->filterReceipt($request,$buildingId)->get();
      
        $_SESSION['sumDauKy'] = 0;

        $result = Excel::create('Phiếu thu', function ($excel) use ($receipts, $buildingId, $request) {
            $excel->setTitle('Phiếu thu');
            $excel->sheet('Phiếu thu', function ($sheet) use ($receipts, $buildingId, $request) {
                $data_receipts = [];
                $row = 14;
                $building = Building::get_detail_building_by_building_id($buildingId);
                $sheet->cells('A6', function ($cells) {
                    $cells->setFontSize(22);
                    $cells->setFontWeight('bold');
                    $cells->setValue('BẢNG KÊ THU TIỀN');
                });
                $sheet->cells('A7', function ($cells) use ($request) {
                    $cells->setFontSize(11);
                    $cells->setFontWeight('Iatalic');
                    if (isset($request['from_date']) && isset($request['to_date'])) {
                        $cells->setValue('Từ ngày :..' . $request['from_date'] . '..Đến :..' . $request['to_date']);
                    } else {
                        $cells->setValue('Từ ngày..............Đến.............. ');
                    }
                });

                $sheet->cells('A2', function ($cells) {
                    $cells->setFontSize(16);
                    $cells->setFontWeight('bold');
                    $cells->setValue('Đơn vị:');
                });

                $sheet->cells('A3', function ($cells) {
                    $cells->setFontSize(16);
                    $cells->setFontWeight('bold');
                    $cells->setValue('Địa chỉ:');
                });

                $sheet->cells('B2', function ($cells) use($building){
                    $cells->setFontSize(16);
                    $cells->setValue($building->name);
                });

                $sheet->cells('B3', function ($cells) use($building) {
                    $cells->setFontSize(16);
                    $cells->setValue($building->address);
                });

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

                $lastColumn = $sheet->getHighestColumn();
                $lastColumn++;
                $lastColumn_new = $lastColumn++;
                $sheet->getStyle($lastColumn_new . '14')->getAlignment()->setWrapText(true);
                $sheet->cells($lastColumn_new . '14', function ($cells) {
                    $cells->setFontSize(11);
                    $cells->setFontWeight('bold');
                    $cells->setValue('Phí phương Tiện');
                    $cells->setValignment('center');
                    $cells->setAlignment('center');
                    $cells->setBorder('thin', 'thin', 'thin', 'thin');
                });

                // điện 
                $lastColumn_new = $lastColumn++;
                $sheet->getStyle($lastColumn_new . '14')->getAlignment()->setWrapText(true);
                $sheet->cells($lastColumn_new . '14', function ($cells) {
                    $cells->setFontSize(11);
                    $cells->setFontWeight('bold');
                    $cells->setValue('Phí điện');
                    $cells->setValignment('center');
                    $cells->setAlignment('center');
                    $cells->setBorder('thin', 'thin', 'thin', 'thin');
                });

                // Nước
                $lastColumn_new = $lastColumn++;
                $sheet->getStyle($lastColumn_new . '14')->getAlignment()->setWrapText(true);
                $sheet->cells($lastColumn_new . '14', function ($cells) {
                    $cells->setFontSize(11);
                    $cells->setFontWeight('bold');
                    $cells->setValue('Phí nước');
                    $cells->setValignment('center');
                    $cells->setAlignment('center');
                    $cells->setBorder('thin', 'thin', 'thin', 'thin');
                });

                // Sàn
                $lastColumn_new = $lastColumn++;
                $sheet->getStyle($lastColumn_new . '14')->getAlignment()->setWrapText(true);
                $sheet->cells($lastColumn_new . '14', function ($cells) {
                    $cells->setFontSize(11);
                    $cells->setFontWeight('bold');
                    $cells->setValue('Phí dịch vụ');
                    $cells->setValignment('center');
                    $cells->setAlignment('center');
                    $cells->setBorder('thin', 'thin', 'thin', 'thin');
                });

                // Dịch vụ khác
                $lastColumn_new = $lastColumn++;
                $sheet->getStyle($lastColumn_new . '14')->getAlignment()->setWrapText(true);
                $sheet->cells($lastColumn_new . '14', function ($cells) {
                    $cells->setFontSize(11);
                    $cells->setFontWeight('bold');
                    $cells->setValue('Dịch vụ khác');
                    $cells->setValignment('center');
                    $cells->setAlignment('center');
                    $cells->setBorder('thin', 'thin', 'thin', 'thin');
                });

                foreach ($receipts as $key => $receipt) {
                    if($receipt->type == 'phieu_dieu_chinh'){ // không lấy ra phiếu điều chỉnh
                        continue;
                    }
                    $PaymentDetail = $receipt->PaymentDetail;

                    $soChungTu =  $receipt->receipt_code;
                    $ngayNgayHachToan = @$receipt->create_date ? date('d/m/Y', strtotime(@$receipt->create_date)) : '--/--/----';
                    $receipt_code_type =  $receipt->type;
                    $description = $receipt->description;
                    $nguoiNop_NhanTien = $receipt->customer_name;
                    $type_payment =  $receipt->type_payment;

                    $nguoiThu = Users::get_detail_user_by_user_id($receipt->user_id);
                    $apartment = ApartmentsRespository::getInfoApartmentsById($receipt->bdc_apartment_id);
                    $check_accounting = LogCoinDetailRepository::check_accounting_log_coin(@$receipt->id);
                   
                    if($check_accounting){
                         continue;
                    }
                    $row++;

                    $sheet->setCellValueByColumnAndRow(0, $row, (string) $soChungTu);
                    $sheet->setCellValueByColumnAndRow(1, $row, (string) $ngayNgayHachToan);
                    $sheet->setCellValueByColumnAndRow(2, $row, (string) $apartment->name);
                    $sheet->setCellValueByColumnAndRow(3, $row, (string) $description);
                    $sheet->setCellValueByColumnAndRow(4, $row, (string) $nguoiNop_NhanTien);
                    $sheet->setCellValueByColumnAndRow(12, $row,(string)  Helper::loai_danh_muc[$type_payment] ?? $type_payment);
                    $sheet->setCellValueByColumnAndRow(13, $row, (string) Helper::loai_danh_muc[$receipt_code_type] ?? $receipt_code_type);
                    $sheet->setCellValueByColumnAndRow(14, $row, (string) @$nguoiThu->email);
                    $sheet->setCellValueByColumnAndRow(15, $row, (string) @$apartment->code);

                    $soTienPhuongTien = 0;
                    $soTienDien = 0;
                    $soTienNuoc = 0;
                    $soTienSan = 0;
                    $soTienDichVuKhac = 0;
                    $getTienThua =  LogCoinDetailRepository::getDataByFromId($receipt->bdc_apartment_id,1,$receipt->id);
                    if ($PaymentDetail->count()>0) {
                        $check_paid_coin = 0;
                        $tien_thua = LogCoinDetailRepository::sum_coin_by_accounting(@$receipt->id);
                        foreach ($PaymentDetail as $_data) {

                            $paid_coin = LogCoinDetailRepository::getCountTienthuaByRecieptid($receipt->id,$_data->bdc_apartment_service_price_id);
                            $paid_coin = $paid_coin ? $paid_coin : LogCoinDetailRepository::getDataByIdAndFromTypeV2(@$_data->bdc_log_coin_id??0);

                            if($getTienThua){
                                foreach ($getTienThua as $key_1 => $value_1) {
                                    $value_1->type = 2; // chi tiêt tiền thừa
                                    if($_data->bdc_apartment_service_price_id == $value_1->bdc_apartment_service_price_id){
                                        $getTienThua->forget($key_1);
                                    }
                                }
                            }

                            $soTien = @$_data->paid + ($check_paid_coin == $_data->bdc_apartment_service_price_id  ? 0 : $paid_coin);
                            $soTien_new = $soTien;
                            if($tien_thua > 0 && $tien_thua < $soTien){
                                $soTien_new =$soTien - $tien_thua;
                                $tien_thua -=$tien_thua;
                            }
                            if($tien_thua > 0 && $tien_thua > $soTien){
                                $tien_thua -=$soTien;
                                $soTien_new = 0;
                            }
                            if (isset($_data)) {

                                $service_apart =  ApartmentServicePriceRepository::getInfoServiceApartmentById($_data->bdc_apartment_service_price_id);
                                $service =  Service::get_detail_bdc_service_by_bdc_service_id($service_apart->bdc_service_id);

                                //===========================================================================
                                if ($service->type == ServiceRepository::PHUONG_TIEN) {
                                    $soTienPhuongTien = $soTienPhuongTien + (int)$soTien_new;
                                } else if ($service->type == ServiceRepository::DIEN) {
                                    $soTienDien = $soTienDien + (int)$soTien_new;
                                } else if ($service->type == ServiceRepository::NUOC) {
                                    $soTienNuoc = $soTienNuoc + (int)$soTien_new;
                                } else if ($service->type == ServiceRepository::DICHVU) {
                                    $soTienSan = $soTienSan + (int)$soTien_new;
                                } else {
                                    $soTienDichVuKhac = $soTienDichVuKhac + (int)$soTien_new;
                                }
                            }
                            if ($check_paid_coin != $_data->bdc_apartment_service_price_id) {
                                $check_paid_coin = $_data->bdc_apartment_service_price_id;
                            }
                        }
                    }
                    $cost_receipt = (int)$receipt->cost;
                    if ( $receipt->type == 'phieu_hoan_ky_quy' || $receipt->type == 'phieu_chi' || $receipt->type == 'phieu_chi_khac') {
                        $soTienDichVuKhac = 0 - (int)$receipt->cost;
                        $cost_receipt = 0 - (int)$receipt->cost;
                    }
                    // PHUONG_TIEN
                    $sheet->setCellValueByColumnAndRow(5, $row, (string) $soTienPhuongTien);
                    //DIEN
                    $sheet->setCellValueByColumnAndRow(6, $row, (string) $soTienDien);
                    //NUOC
                    $sheet->setCellValueByColumnAndRow(7, $row, (string) $soTienNuoc);
                    //SAN
                    $sheet->setCellValueByColumnAndRow(8, $row, (string) $soTienSan);
                    //DICH VU KHAC
                    $sheet->setCellValueByColumnAndRow(9, $row, (string) $soTienDichVuKhac);
                    //Tiền thừa
                     $tien_thua = 0;
                    foreach ($getTienThua as $key_1 => $value_1) {
                       $tien_thua+=$value_1->coin;
                    }

                    $sheet->setCellValueByColumnAndRow(10, $row, (string) @$tien_thua);
                    //TONG
                    $sheet->setCellValueByColumnAndRow(11, $row, (string) $cost_receipt);
                }
                //$sheet->setBorder('A1:F10', 'thin');
                $range_new = 'F12:' . $lastColumn_new . '13';
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
                $b = $lastColumn_new . '12:' . $lastColumn_new . '14';
                $sheet->setWidth(array($lastColumn_new => 15));
                $sheet->mergeCells($b);
                $sheet->getStyle($lastColumn_new . '12')->getAlignment()->setWrapText(true);
                $sheet->cells($lastColumn_new . '12', function ($cells) {
                    $cells->setFontSize(11);
                    $cells->setFontWeight('bold');
                    $cells->setValue('Tiền thừa');
                    $cells->setValignment('center');
                    $cells->setAlignment('center');
                    $cells->setBorder('thin', 'thin', 'thin', 'thin');
                });
                $sheet->setWidth(array($lastColumn_new => 15));
                $lastColumn_new = $lastColumn++;
                $b = $lastColumn_new . '12:' . $lastColumn_new . '14';
                $sheet->mergeCells($b);
                $sheet->getStyle($lastColumn_new . '12')->getAlignment()->setWrapText(true);
                $sheet->cells($lastColumn_new . '12', function ($cells) {
                    $cells->setFontSize(11);
                    $cells->setFontWeight('bold');
                    $cells->setValue('Tổng cộng');
                    $cells->setValignment('center');
                    $cells->setAlignment('center');
                    $cells->setBorder('thin', 'thin', 'thin', 'thin');
                });

                $lastColumn_new = $lastColumn++;
                $c = $lastColumn_new . '12:' . $lastColumn_new . '14';
                $sheet->setWidth(array($lastColumn_new => 15));
                $sheet->mergeCells($c);
                $sheet->getStyle($lastColumn_new . '12')->getAlignment()->setWrapText(true);
                $sheet->cells($lastColumn_new . '12', function ($cells) {
                    $cells->setFontSize(11);
                    $cells->setFontWeight('bold');
                    $cells->setValue('Hình thức');
                    $cells->setValignment('center');
                    $cells->setAlignment('center');
                    $cells->setBorder('thin', 'thin', 'thin', 'thin');
                });

                $lastColumn_new = $lastColumn++;
                $c = $lastColumn_new . '12:' . $lastColumn_new . '14';
                $sheet->setWidth(array($lastColumn_new => 15));
                $sheet->mergeCells($c);
                $sheet->getStyle($lastColumn_new . '12')->getAlignment()->setWrapText(true);
                $sheet->cells($lastColumn_new . '12', function ($cells) {
                    $cells->setFontSize(11);
                    $cells->setFontWeight('bold');
                    $cells->setValue('Kiểu phiếu');
                    $cells->setValignment('center');
                    $cells->setAlignment('center');
                    $cells->setBorder('thin', 'thin', 'thin', 'thin');
                });

                $lastColumn_new = $lastColumn++;
                $d = $lastColumn_new . '12:' . $lastColumn_new . '14';
                $sheet->setWidth(array($lastColumn_new => 15));
                $sheet->mergeCells($d);
                $sheet->getStyle($lastColumn_new . '12')->getAlignment()->setWrapText(true);
                $sheet->cells($lastColumn_new . '12', function ($cells) {
                    $cells->setFontSize(11);
                    $cells->setFontWeight('bold');
                    $cells->setValue('NV thu');
                    $cells->setValignment('center');
                    $cells->setAlignment('center');
                    $cells->setBorder('thin', 'thin', 'thin', 'thin');
                });
                $lastColumn_new = $lastColumn++;
                $d = $lastColumn_new . '12:' . $lastColumn_new . '14';
                $sheet->setWidth(array($lastColumn_new => 15));
                $sheet->mergeCells($d);
                $sheet->getStyle($lastColumn_new . '12')->getAlignment()->setWrapText(true);
                $sheet->cells($lastColumn_new . '12', function ($cells) {
                    $cells->setFontSize(11);
                    $cells->setFontWeight('bold');
                    $cells->setValue('Mã SP');
                    $cells->setValignment('center');
                    $cells->setAlignment('center');
                    $cells->setBorder('thin', 'thin', 'thin', 'thin');
                });
                // total - footer

                $sheet->cells('A' . ($row + 1), function ($cells) {
                    $cells->setFontSize(11);
                    $cells->setFontWeight('bold');
                    $cells->setValue('Tổng');
                });

                $attrs = [];
                $attrs['t'] = 'array';

                $arrSum = ["F","G","H","J","I","K","L"];

                foreach ($arrSum as $item){
                    $sheet->cells($item.($row + 1), function($cells) use ($sheet, $row) {
                        $cells->setFontSize(11);
                        $cells->setFontWeight('bold');
                    });
                    $sheet->getCell($item.($row + 1))->setFormulaAttributes($attrs);
                    $sheet->setCellValue($item.($row + 1),'=SUM('.$item.'15:'.$item.$row.')');
                }

                $sheet->setColumnFormat(array(
                    'F15:L'.($row + 1) => "#,##0",
                    'C15:C500' => \PHPExcel_Style_NumberFormat::FORMAT_TEXT,
                ));

                $sheet->cells('A12:P14', function ($cells) {
                    $cells->setBackground('#cfe2f3');
                    $cells->setValignment('center');
                    $cells->setAlignment('center');
                });


                // begin - footer 
                $total_row = $receipts->count() + 20;
                $b_footer = 'B' . $total_row;
                $sheet->cells($b_footer, function ($cells) {
                    $cells->setFontSize(11);
                    $cells->setFontWeight('bold');
                    $cells->setValue('Người Nộp Tiền');
                    $cells->setAlignment('center');
                });

                $e_footer = 'F' . $total_row;

                $sheet->cells($e_footer, function ($cells) {
                    $cells->setFontSize(11);
                    $cells->setFontWeight('bold');
                    $cells->setValue('Kế Toán');
                    $cells->setAlignment('center');
                });

                $total_row_new = $total_row - 1;
                $h_footer_1 = 'M' . $total_row_new;

                $sheet->cells($h_footer_1, function ($cells) {
                    $cells->setFontSize(11);
                    $cells->setFontWeight('bold');
                    $cells->setValue('Ngày.....Tháng.....Năm.....');
                    $cells->setAlignment('center');
                });

                $h_footer_2 = 'M' . $total_row;

                $sheet->cells($h_footer_2, function ($cells) {
                    $cells->setFontSize(11);
                    $cells->setFontWeight('bold');
                    $cells->setValue('Giám đốc (Trưởng ban tòa nhà)');
                    $cells->setAlignment('center');
                });

                $total_row_last = ($row + 1);
                $range_new_last = 'A15:' . $lastColumn_new . $total_row_last;
                $sheet->getStyle($range_new_last)->applyFromArray(
                    array(
                        'borders' => array(
                            'allborders' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THIN,
                                'color' => array('rgb' => 'FFFF0000'),
                            )
                        ),
                        'font' => [
                            'size' => 11
                        ]
                    )
                );

                // end - footer

                $sheet->setHeight(array(
                    6     =>  50,
                    7     =>  40,
                    8     =>  5,
                    9     =>  5,
                    10     =>  5,
                    11     =>  5,
                ));

                $sheet->mergeCells('A6:P6');
                $sheet->cells('A6', function ($cells) {
                    $cells->setValignment('center');
                    $cells->setAlignment('center');
                });
                $sheet->mergeCells('A7:P7');
                $sheet->cells('A7', function ($cells) {
                    $cells->setValignment('center');
                    $cells->setAlignment('center');
                });
                $sheet->setAutoSize(true);
                $sheet->setWidth(array(
                    'A'     =>  25,
                    'B'     =>  15,
                    'C'     =>  20,
                    'D'     =>  20,
                    'F'     =>  10,
                    'G'     =>  10,
                    'k'     =>  10,
                    'L'     =>  20,
                    'M'     =>  15,
                ));
            });
        })->store('xlsx',storage_path('exports/'));
        ob_end_clean();
          $file = storage_path('exports/'.$result->filename.'.'.$result->ext);
         return response()->download($file)->deleteFileAfterSend(true);
             
    }


    public function export_thu_tien_tavico($buildingId, $request, $debitDetailRepository)
    {
        $receipts = $this->filterReceipt($request,$buildingId)->get();

        $result = Excel::create('Phiếu thu', function ($excel) use ($receipts, $buildingId, $request) {
            $excel->setTitle('Phiếu thu');
            $excel->sheet('Phiếu thu', function ($sheet) use ($receipts, $buildingId, $request) {
                $data = [];
                $content = [];
                foreach ($receipts as $key => $receipt) {

                    if ($receipt->type_payment == 'tien_mat') {
                        $status = 'PT';
                    } elseif ($receipt->type_payment == 'chuyen_khoan' || $receipt->type_payment == 'vi') {
                        $status = 'BC';
                    } else {
                        $status = Helper::loai_danh_muc[$receipt->type_payment] ?? $receipt->type_payment;
                    }
                    $PaymentDetail = $receipt->PaymentDetail;

                    $apartment = ApartmentsRespository::getInfoApartmentsById($receipt->bdc_apartment_id);
                    $buildingPlace = BuildingPlaceRepository::getInfoBuildingPlaceById($apartment->building_place_id);
                    $building = BuildingRepository::getInfoBuildingById($receipt->bdc_building_id);
                    $_customer = CustomersRespository::findApartmentIdV2($receipt->bdc_apartment_id, 0);
                    $pubUserProfile = @$_customer ? PublicUsersProfileRespository::getInfoUserById($_customer->user_info_id) : null;
                    $user = Users::get_detail_user_by_user_id($receipt->user_id);
                    $getTienThua =  LogCoinDetailRepository::getDataByFromId($receipt->bdc_apartment_id,1,$receipt->id);
                    if ($PaymentDetail->count()>0) {
                        $check_paid_coin = 0;
                        foreach ($PaymentDetail as $_data) {
                            $service_apartment_id = @$_data->bdc_apartment_service_price_id;
                            $service_apart =  ApartmentServicePriceRepository::getInfoServiceApartmentById($service_apartment_id);
                            $service = $service_apart ? Service::get_detail_bdc_service_by_bdc_service_id($service_apart->bdc_service_id) : null;
                            $vehicle =$service_apart->bdc_vehicle_id > 0 ? Vehicles::get_detail_vehicle_by_id($service_apart->bdc_vehicle_id) : null;
                            $new_service = $service_apart ? @$service_apart->name . ' - ' . @$vehicle->number : 'từ tiền thừa chưa chỉ định';
                            $paid_coin = LogCoinDetailRepository::getCountTienthuaByRecieptid($receipt->id,$service_apartment_id);
                            $paid_coin = $paid_coin ? $paid_coin : LogCoinDetailRepository::getDataByIdAndFromTypeV2(@$_data->bdc_log_coin_id??0);
                            if($getTienThua){
                                foreach ($getTienThua as $key_1 => $value_1) {
                                    $value_1->type = 2; // chi tiêt tiền thừa
                                    if($_data->bdc_apartment_service_price_id == $value_1->bdc_apartment_service_price_id){
                                        $getTienThua->forget($key_1);
                                    }
                                }
                            }

                        
                            if ($receipt->type == 'phieu_ke_toan') {
                                $diendai = 'Hạch toán ' . @$new_service . ' tháng ' . date('Ym', strtotime(@$receipt->create_date)) . ' căn hộ ' . @$apartment->name . ' KH ' . @$receipt->customer_name . ' tại ' . @$building->name;
                              
                                $content[] = [
                                    'Loại NK' =>   $status,
                                    'Hình thức' =>  Helper::loai_danh_muc[$receipt->type_payment] ?? $receipt->type_payment,
                                    'Kiểu phiếu' =>  Helper::loai_danh_muc[$receipt->type] ?? $receipt->type,
                                    'Số chứng từ' =>  $receipt->receipt_code,
                                    'Kỳ kế toán' =>  date('Ym', strtotime(@$receipt->create_date)),
                                    'Ngày phát sinh' => @$receipt->create_date ? date('d/m/Y', strtotime(@$receipt->create_date)) : null,
                                    'Diễn giải' => $diendai,
                                    'Mã Khách hàng-NCC' => $status == 'BC' ? 'O00000000001' : @$apartment->code_customer,
                                    'Mã Ngân hàng' => '',
                                    'Cty Con - NH cho DXMB vay' =>  '',
                                    'Mã Phòng ban' =>  @$building->building_code_manage,
                                    'Mã Nhân viên' =>  '',
                                    'Mã phí' =>   $service_apart->bdc_vehicle_id > 0 ? $service->name . ' - ' . @$vehicle->number : $service->name,
                                    'Hợp đồng' => '',
                                    'Sản phẩm' =>  @$apartment->code,
                                    'Block' =>   @@$buildingPlace->code,
                                    'Dự án' =>  @$building->name,
                                    'Mã thu' =>   @$service->code_receipt,
                                    'Khế ước' =>  "",
                                    'CP không hợp lệ' =>  "",
                                    'Mã tài khoản' =>  '',
                                    'TKDƯ' =>   '',
                                    'Số tiền' =>  (int)$_data->paid + ($check_paid_coin == $_data->bdc_apartment_service_price_id  ? 0 : $paid_coin),
                                    'Nợ/Có' =>  '',
                                    'Ký hiệu hóa đơn' =>  "",
                                    'Số hóa đơn' =>  "",
                                    'Ngày hóa đơn' =>  "",
                                    'Loại thuế' => "",
                                    'Thuế suất' =>  "",
                                    'Tiền trước thuế' => "",
                                    'Mẫu số hóa đơn' => "",
                                    'Người nộp' =>  @$user->email,
                                    'Người bán hàng' =>  "",
                                    'Phiếu cấn trừ' =>  "",
                                    'Mã phiếu eApprove' =>  "",
                                    'Ghi chú' => $receipt->receipt_code,
                                ];
                                $get_accounting_source = LogCoinDetailRepository::get_accounting_source($receipt->id,$_data);
                                if($get_accounting_source && $get_accounting_source->type=4)
                                {
                                   
                                    $apartment_service =  ApartmentServicePriceRepository::getInfoServiceApartmentById($get_accounting_source->bdc_apartment_service_price_id);
                                    $service = $apartment_service ? Service::get_detail_bdc_service_by_bdc_service_id($apartment_service->bdc_service_id) : null;

                                    $vehicle =@$apartment_service->bdc_vehicle_id > 0 ? Vehicles::get_detail_vehicle_by_id($apartment_service->bdc_vehicle_id) : null;
                                    $new_service = $apartment_service && $apartment_service->bdc_vehicle_id > 0 ? @$apartment_service->name . ' - ' . @$vehicle->number : @$apartment_service->name;
                                    $diendai = 'Hạch toán ' . $new_service . ' tháng ' . date('Ym', strtotime(@$receipt->create_date)) . ' căn hộ ' . @$apartment->name . ' KH ' . @$receipt->customer_name . ' tại ' . @$building->name;
                                    $content[] = [
                                        'Loại NK' =>   $status,
                                        'Hình thức' =>  Helper::loai_danh_muc[$receipt->type_payment] ?? $receipt->type_payment,
                                        'Kiểu phiếu' =>  Helper::loai_danh_muc[$receipt->type] ?? $receipt->type,
                                        'Số chứng từ' =>  $receipt->receipt_code,
                                        'Kỳ kế toán' =>  date('Ym', strtotime(@$receipt->create_date)),
                                        'Ngày phát sinh' => @$receipt->create_date ? date('d/m/Y', strtotime(@$receipt->create_date)) : null,
                                        'Diễn giải' => $diendai,
                                        'Mã Khách hàng-NCC' => $status == 'BC' ? 'O00000000001' : @$apartment->code_customer,
                                        'Mã Ngân hàng' => '',
                                        'Cty Con - NH cho DXMB vay' =>  '',
                                        'Mã Phòng ban' =>  @$building->building_code_manage,
                                        'Mã Nhân viên' =>  '',
                                        'Mã phí' =>   $new_service,
                                        'Hợp đồng' => '',
                                        'Sản phẩm' =>  @$apartment->code,
                                        'Block' =>   @@$buildingPlace->code,
                                        'Dự án' =>  @$building->name,
                                        'Mã thu' =>  @$service->code_receipt,
                                        'Khế ước' =>  "",
                                        'CP không hợp lệ' =>  "",
                                        'Mã tài khoản' =>  '',
                                        'TKDƯ' =>   '',
                                        'Số tiền' =>0 - $get_accounting_source->coin,
                                        'Nợ/Có' =>  '',
                                        'Ký hiệu hóa đơn' =>  "",
                                        'Số hóa đơn' =>  "",
                                        'Ngày hóa đơn' =>  "",
                                        'Loại thuế' => "",
                                        'Thuế suất' =>  "",
                                        'Tiền trước thuế' => "",
                                        'Mẫu số hóa đơn' => "",
                                        'Người nộp' =>  @$user->email,
                                        'Người bán hàng' =>  "",
                                        'Phiếu cấn trừ' =>  "",
                                        'Mã phiếu eApprove' =>  "",
                                        'Ghi chú' => $receipt->receipt_code,
                                    ];

                                }
                            }
                            if ($receipt->type != 'phieu_ke_toan') {
                              
                                $diendai = 'Thu tiền ' . @$new_service . ' tháng ' . date('Ym', strtotime(@$receipt->create_date)) . ' căn hộ ' . @$apartment->name . ' KH ' . @$receipt->customer_name . ' tại ' . @$building->name;
                                $content[] = [
                                    'Loại NK' =>   $status,
                                    'Hình thức' =>  Helper::loai_danh_muc[$receipt->type_payment] ?? $receipt->type_payment,
                                    'Kiểu phiếu' =>  Helper::loai_danh_muc[$receipt->type] ?? $receipt->type,
                                    'Số chứng từ' =>  $receipt->receipt_code,
                                    'Kỳ kế toán' =>  date('Ym', strtotime(@$receipt->create_date)),
                                    'Ngày phát sinh' => @$receipt->create_date ? date('d/m/Y', strtotime(@$receipt->create_date)) : null,
                                    'Diễn giải' => $diendai,
                                    'Mã Khách hàng-NCC' => $status == 'BC' ? 'O00000000001' : @$apartment->code_customer,
                                    'Mã Ngân hàng' => '',
                                    'Cty Con - NH cho DXMB vay' =>  '',
                                    'Mã Phòng ban' =>  @$building->building_code_manage,
                                    'Mã Nhân viên' =>  '',
                                    'Mã phí' =>   @$service_apart->bdc_vehicle_id > 0 ? @$service->name . ' - ' . @$vehicle->number : @$service->name,
                                    'Hợp đồng' => '',
                                    'Sản phẩm' =>  @$apartment->code,
                                    'Block' =>   @@$buildingPlace->code,
                                    'Dự án' =>  @$building->name,
                                    'Mã thu' =>  @$service->code_receipt,
                                    'Khế ước' =>  "",
                                    'CP không hợp lệ' =>  "",
                                    'Mã tài khoản' =>  '',
                                    'TKDƯ' =>   '',
                                    'Số tiền' => (int)$_data->paid+ ($check_paid_coin == $_data->bdc_apartment_service_price_id  ? 0 : $paid_coin),
                                    'Nợ/Có' =>  '',
                                    'Ký hiệu hóa đơn' =>  "",
                                    'Số hóa đơn' =>  "",
                                    'Ngày hóa đơn' =>  "",
                                    'Loại thuế' => "",
                                    'Thuế suất' =>  "",
                                    'Tiền trước thuế' => "",
                                    'Mẫu số hóa đơn' => "",
                                    'Người nộp' =>  @$user->email,
                                    'Người bán hàng' =>  "",
                                    'Phiếu cấn trừ' =>  "",
                                    'Mã phiếu eApprove' =>  "",
                                    'Ghi chú' => $receipt->receipt_code,
                                ];
                            }
                            if ($check_paid_coin != $_data->bdc_apartment_service_price_id) {
                                $check_paid_coin = $_data->bdc_apartment_service_price_id;
                            }
                        }
                        $nguon_hach_toan_v1 = LogCoinDetailRepository::get_by_from_id_accounting($receipt->id);
                        $diendai = 'Hạch toán tiền thừa tháng ' . date('Ym', strtotime(@$receipt->create_date)) . ' căn hộ ' . @$apartment->name . ' KH ' . @$receipt->customer_name . ' tại ' . @$building->name;
                        if($nguon_hach_toan_v1){
                            foreach ($nguon_hach_toan_v1 as $value_tien_thua) {
                                $service_apart_tien_thua =  ApartmentServicePriceRepository::getInfoServiceApartmentById($value_tien_thua->bdc_apartment_service_price_id);
                                $service_tien_thua = $service_apart_tien_thua ? Service::get_detail_bdc_service_by_bdc_service_id($service_apart_tien_thua->bdc_service_id) : null;
                                $content[] = [
                                    'Loại NK' =>   $status,
                                    'Hình thức' =>  Helper::loai_danh_muc[$receipt->type_payment] ?? $receipt->type_payment,
                                    'Kiểu phiếu' =>  Helper::loai_danh_muc[$receipt->type] ?? $receipt->type,
                                    'Số chứng từ' =>  $receipt->receipt_code,
                                    'Kỳ kế toán' =>  date('Ym', strtotime(@$receipt->create_date)),
                                    'Ngày phát sinh' => @$receipt->create_date ? date('d/m/Y', strtotime(@$receipt->create_date)) : null,
                                    'Diễn giải' => $diendai,
                                    'Mã Khách hàng-NCC' => $status == 'BC' ? 'O00000000001' : @$apartment->code_customer,
                                    'Mã Ngân hàng' => '',
                                    'Cty Con - NH cho DXMB vay' =>  '',
                                    'Mã Phòng ban' =>  @$building->building_code_manage,
                                    'Mã Nhân viên' =>  '',
                                    'Mã phí' =>  'Tiền thừa',
                                    'Hợp đồng' => '',
                                    'Sản phẩm' =>  @$apartment->code,
                                    'Block' =>   @@$buildingPlace->code,
                                    'Dự án' =>  @$building->name,
                                    'Mã thu' =>   @$service_tien_thua->code_receipt,
                                    'Khế ước' =>  "",
                                    'CP không hợp lệ' =>  "",
                                    'Mã tài khoản' =>  '',
                                    'TKDƯ' =>   '',
                                    'Số tiền' =>  0 - (int) $value_tien_thua->coin,
                                    'Nợ/Có' =>  '',
                                    'Ký hiệu hóa đơn' =>  "",
                                    'Số hóa đơn' =>  "",
                                    'Ngày hóa đơn' =>  "",
                                    'Loại thuế' => "",
                                    'Thuế suất' =>  "",
                                    'Tiền trước thuế' => "",
                                    'Mẫu số hóa đơn' => "",
                                    'Người nộp' =>  @$user->email,
                                    'Người bán hàng' =>  "",
                                    'Phiếu cấn trừ' =>  "",
                                    'Mã phiếu eApprove' =>  "",
                                    'Ghi chú' => $receipt->receipt_code,
                                ];
                            }
                           
                        }
                     
                    
                    } else {
                        if ($receipt->type == 'phieu_ke_toan') {
                            $diendai = 'Thu tiền '. date('Ym', strtotime(@$receipt->create_date)) . ' ' . @$apartment->name . ' ' . @$receipt->customer_name . ' ' .  @$building->name;
                            $content[] = [
                                'Loại NK' =>   $status,
                                'Hình thức' =>  Helper::loai_danh_muc[$receipt->type_payment] ?? $receipt->type_payment,
                                'Kiểu phiếu' =>  Helper::loai_danh_muc[$receipt->type] ?? $receipt->type,
                                'Số chứng từ' =>  $receipt->receipt_code,
                                'Kỳ kế toán' =>  date('Ym', strtotime(@$receipt->create_date)),
                                'Ngày phát sinh' => @$receipt->create_date ? date('d/m/Y', strtotime(@$receipt->create_date)) : null,
                                'Diễn giải' => $diendai,
                                'Mã Khách hàng-NCC' => $status == 'BC' ? 'O00000000001' : @$apartment->code_customer,
                                'Mã Ngân hàng' => '',
                                'Cty Con - NH cho DXMB vay' =>  '',
                                'Mã Phòng ban' =>  @$building->building_code_manage,
                                'Mã Nhân viên' =>  '',
                                'Mã phí' =>  '',
                                'Hợp đồng' => '',
                                'Sản phẩm' =>  @$apartment->code,
                                'Block' =>   @@$buildingPlace->code,
                                'Dự án' =>  @$building->name,
                                'Mã thu' =>  "",
                                'Khế ước' =>  "",
                                'CP không hợp lệ' =>  "",
                                'Mã tài khoản' =>  '',
                                'TKDƯ' =>   '',
                                'Số tiền' =>   (int) $receipt->cost,
                                'Nợ/Có' =>  '',
                                'Ký hiệu hóa đơn' =>  "",
                                'Số hóa đơn' =>  "",
                                'Ngày hóa đơn' =>  "",
                                'Loại thuế' => "",
                                'Thuế suất' =>  "",
                                'Tiền trước thuế' => "",
                                'Mẫu số hóa đơn' => "",
                                'Người nộp' =>  @$user->email,
                                'Người bán hàng' =>  "",
                                'Phiếu cấn trừ' =>  "",
                                'Mã phiếu eApprove' =>  "",
                                'Ghi chú' => $receipt->receipt_code,
                            ];
                        }
                    }
                  
                    $diendai = 'Thu tiền thừa tháng ' . date('Ym', strtotime(@$receipt->create_date)) . ' căn hộ ' . @$apartment->name . ' KH ' . @$receipt->customer_name . ' tại ' . @$building->name;
                    if ($getTienThua) {
                        foreach ($getTienThua as $key_1 => $value_1) {
                            $apartmentServicePrice = @$value_1->bdc_apartment_service_price_id != 0 ? ApartmentServicePrice::get_detail_bdc_apartment_service_price_by_apartment_id($value_1->bdc_apartment_service_price_id) : null;
                            $service = $apartmentServicePrice ? Service::get_detail_bdc_service_by_bdc_service_id($apartmentServicePrice->bdc_service_id) : null;
                            $vehicle = @$apartmentServicePrice->bdc_vehicle_id > 0 ? Vehicles::get_detail_vehicle_by_id($apartmentServicePrice->bdc_vehicle_id) : null;

                            $content[] = [
                                'Loại NK' =>   $status,
                                'Hình thức' =>  Helper::loai_danh_muc[$receipt->type_payment] ?? $receipt->type_payment,
                                'Kiểu phiếu' =>  Helper::loai_danh_muc[$receipt->type] ?? $receipt->type,
                                'Số chứng từ' =>  $receipt->receipt_code,
                                'Kỳ kế toán' =>  date('Ym', strtotime(@$receipt->create_date)),
                                'Ngày phát sinh' => @$receipt->create_date ? date('d/m/Y', strtotime(@$receipt->create_date)) : null,
                                'Diễn giải' => $diendai,
                                'Mã Khách hàng-NCC' => $status == 'BC' ? 'O00000000001' : @$apartment->code_customer,
                                'Mã Ngân hàng' => '',
                                'Cty Con - NH cho DXMB vay' =>  '',
                                'Mã Phòng ban' =>  @$building->building_code_manage,
                                'Mã Nhân viên' =>  '',
                                'Mã phí' => $value_1->bdc_apartment_service_price_id == 0 ? 'Tiền thừa chưa chỉ định' : @$service->name.'-'.@$vehicle->number.'(tiền thừa)',
                                'Hợp đồng' => '',
                                'Sản phẩm' =>  @$apartment->code,
                                'Block' =>   @@$buildingPlace->code,
                                'Dự án' =>  @$building->name,
                                'Mã thu' =>   @$service->code_receipt,
                                'Khế ước' =>  "",
                                'CP không hợp lệ' =>  "",
                                'Mã tài khoản' =>  '',
                                'TKDƯ' =>   '',
                                'Số tiền' =>   (int) $value_1->coin,
                                'Nợ/Có' =>  '',
                                'Ký hiệu hóa đơn' =>  "",
                                'Số hóa đơn' =>  "",
                                'Ngày hóa đơn' =>  "",
                                'Loại thuế' => "",
                                'Thuế suất' =>  "",
                                'Tiền trước thuế' => "",
                                'Mẫu số hóa đơn' => "",
                                'Người nộp' =>  @$user->email,
                                'Người bán hàng' =>  "",
                                'Phiếu cấn trừ' =>  "",
                                'Mã phiếu eApprove' =>  "",
                                'Ghi chú' => $receipt->receipt_code,
                            ];

                        }
                       
                    }
                    if($receipt->type == 'phieu_chi'){
                        $get_detail = isset($receipt->logs)? json_decode($receipt->logs) : null;
                        if($get_detail){
                            foreach ($get_detail as $key => $value) {
                                if(str_contains($value->service_apartment_id, 'tien_thua_') ){ 
                                    $service_apartment_id = explode('tien_thua_',$value->service_apartment_id)[1];
                                    $apartmentServicePrice = $service_apartment_id != 0 ? ApartmentServicePrice::get_detail_bdc_apartment_service_price_by_apartment_id($service_apartment_id) : null;
                                    $service = $apartmentServicePrice ? Service::get_detail_bdc_service_by_bdc_service_id($apartmentServicePrice->bdc_service_id) : null;
                                    $vehicle = @$apartmentServicePrice->bdc_vehicle_id > 0 ? Vehicles::get_detail_vehicle_by_id($apartmentServicePrice->bdc_vehicle_id) : null;
                                    $get_accounting_source = LogCoinDetailRepository::get_accounting_source_service_apartment_id_by_payment_slip($receipt->id,$service_apartment_id);
                                    if($get_accounting_source)
                                    {
                                        $content[] = [
                                            'Loại NK' =>   $status,
                                            'Hình thức' =>  Helper::loai_danh_muc[$receipt->type_payment] ?? $receipt->type_payment,
                                            'Kiểu phiếu' =>  Helper::loai_danh_muc[$receipt->type] ?? $receipt->type,
                                            'Số chứng từ' =>  $receipt->receipt_code,
                                            'Kỳ kế toán' =>  date('Ym', strtotime(@$receipt->create_date)),
                                            'Ngày phát sinh' => @$receipt->create_date ? date('d/m/Y', strtotime(@$receipt->create_date)) : null,
                                            'Diễn giải' => $service_apartment_id == 0 ? 'Chi từ tiền thừa chưa chỉ định' : 'Chi tiền từ dịch vụ '. @$service->name.'-'.@$vehicle->number,
                                            'Mã Khách hàng-NCC' => $status == 'BC' ? 'O00000000001' : @$apartment->code_customer,
                                            'Mã Ngân hàng' => '',
                                            'Cty Con - NH cho DXMB vay' =>  '',
                                            'Mã Phòng ban' =>  @$building->building_code_manage,
                                            'Mã Nhân viên' =>  '',
                                            'Mã phí' => $service_apartment_id == 0 ? 'Chi tiền chưa chỉ định' : @$service->name.'-'.@$vehicle->number.'(chi tiền)',
                                            'Hợp đồng' => '',
                                            'Sản phẩm' =>  @$apartment->code,
                                            'Block' =>   @@$buildingPlace->code,
                                            'Dự án' =>  @$building->name,
                                            'Mã thu' =>   @$service->code_receipt,
                                            'Khế ước' =>  "",
                                            'CP không hợp lệ' =>  "",
                                            'Mã tài khoản' =>  '',
                                            'TKDƯ' =>   '',
                                            'Số tiền' =>  0 - $get_accounting_source->coin,
                                            'Nợ/Có' =>  '',
                                            'Ký hiệu hóa đơn' =>  "",
                                            'Số hóa đơn' =>  "",
                                            'Ngày hóa đơn' =>  "",
                                            'Loại thuế' => "",
                                            'Thuế suất' =>  "",
                                            'Tiền trước thuế' => "",
                                            'Mẫu số hóa đơn' => "",
                                            'Người nộp' =>  @$user->email,
                                            'Người bán hàng' =>  "",
                                            'Phiếu cấn trừ' =>  "",
                                            'Mã phiếu eApprove' =>  "",
                                            'Ghi chú' => $receipt->receipt_code,
                                        ];
                                    }
                                }
                             }
                        }
        
                    }
                    if($receipt->type == 'phieu_chi_khac' || $receipt->type == 'phieu_hoan_ky_quy'){
                        $content[] = [
                            'Loại NK' =>   $status,
                            'Hình thức' =>  Helper::loai_danh_muc[$receipt->type_payment] ?? $receipt->type_payment,
                            'Kiểu phiếu' =>  Helper::loai_danh_muc[$receipt->type] ?? $receipt->type,
                            'Số chứng từ' =>  $receipt->receipt_code,
                            'Kỳ kế toán' =>  date('Ym', strtotime(@$receipt->create_date)),
                            'Ngày phát sinh' => @$receipt->create_date ? date('d/m/Y', strtotime(@$receipt->create_date)) : null,
                            'Diễn giải' => 'Chi tiền' . ' ' . date('Ym', strtotime(@$receipt->create_date)) . ' ' . @$apartment->name . ' ' . @$receipt->customer_name . ' ' .  @$building->name,
                            'Mã Khách hàng-NCC' => $status == 'BC' ? 'O00000000001' : @$apartment->code_customer,
                            'Mã Ngân hàng' => '',
                            'Cty Con - NH cho DXMB vay' =>  '',
                            'Mã Phòng ban' =>  @$building->building_code_manage,
                            'Mã Nhân viên' =>  '',
                            'Mã phí' =>  '',
                            'Hợp đồng' => '',
                            'Sản phẩm' =>  @$apartment->code,
                            'Block' =>   @@$buildingPlace->code,
                            'Dự án' =>  @$building->name,
                            'Mã thu' =>  "",
                            'Khế ước' =>  "",
                            'CP không hợp lệ' =>  "",
                            'Mã tài khoản' =>  '',
                            'TKDƯ' =>    '',
                            'Số tiền' => 0-(int) $receipt->cost,
                            'Nợ/Có' =>  '',
                            'Ký hiệu hóa đơn' =>  "",
                            'Số hóa đơn' =>  "",
                            'Ngày hóa đơn' =>  "",
                            'Loại thuế' => "",
                            'Thuế suất' =>  "",
                            'Tiền trước thuế' => "",
                            'Mẫu số hóa đơn' => "",
                            'Người nộp' =>  @$user->email,
                            'Người bán hàng' =>  "",
                            'Phiếu cấn trừ' =>  "",
                            'Mã phiếu eApprove' =>  "",
                            'Ghi chú' => $receipt->receipt_code,
                        ];
                    }
                    if($receipt->type == 'phieu_thu_truoc' || $receipt->type == 'phieu_thu_ky_quy'){
                        $content[] = [
                            'Loại NK' =>   $status,
                            'Hình thức' =>  Helper::loai_danh_muc[$receipt->type_payment] ?? $receipt->type_payment,
                            'Kiểu phiếu' =>  Helper::loai_danh_muc[$receipt->type] ?? $receipt->type,
                            'Số chứng từ' =>  $receipt->receipt_code,
                            'Kỳ kế toán' =>  date('Ym', strtotime(@$receipt->create_date)),
                            'Ngày phát sinh' => @$receipt->create_date ? date('d/m/Y', strtotime(@$receipt->create_date)) : null,
                            'Diễn giải' => 'Thu tiền' . ' ' . date('Ym', strtotime(@$receipt->create_date)) . ' ' . @$apartment->name . ' ' . @$receipt->customer_name . ' ' .  @$building->name,
                            'Mã Khách hàng-NCC' => $status == 'BC' ? 'O00000000001' : @$apartment->code_customer,
                            'Mã Ngân hàng' => '',
                            'Cty Con - NH cho DXMB vay' =>  '',
                            'Mã Phòng ban' =>  @$building->building_code_manage,
                            'Mã Nhân viên' =>  '',
                            'Mã phí' =>  '',
                            'Hợp đồng' => '',
                            'Sản phẩm' =>  @$apartment->code,
                            'Block' =>   @@$buildingPlace->code,
                            'Dự án' =>  @$building->name,
                            'Mã thu' =>  "",
                            'Khế ước' =>  "",
                            'CP không hợp lệ' =>  "",
                            'Mã tài khoản' =>  '',
                            'TKDƯ' =>    '',
                            'Số tiền' => (int) $receipt->cost,
                            'Nợ/Có' =>  '',
                            'Ký hiệu hóa đơn' =>  "",
                            'Số hóa đơn' =>  "",
                            'Ngày hóa đơn' =>  "",
                            'Loại thuế' => "",
                            'Thuế suất' =>  "",
                            'Tiền trước thuế' => "",
                            'Mẫu số hóa đơn' => "",
                            'Người nộp' =>  @$user->email,
                            'Người bán hàng' =>  "",
                            'Phiếu cấn trừ' =>  "",
                            'Mã phiếu eApprove' =>  "",
                            'Ghi chú' => $receipt->receipt_code,
                        ];
                    }

                }
                $fromDate = false;
                if (isset($request['from_date'])  && $request['from_date'] != null) {
                    $created_at_from_date = Carbon::parse($request['from_date'])->format('Y-m-d');
                    $fromDate = $created_at_from_date . " 00:00:00";
                }

                $toDate = false;
                if (isset($request['to_date'])  && $request['to_date'] != null) {
                    $created_at_to_date = Carbon::parse($request['to_date'])->format('Y-m-d');
                    $toDate = $created_at_to_date . " 23:59:59";
                }
                if($fromDate && $toDate){
                    // lấy ra danh sách phân bổ
                    $dataPhanbo = LogCoinDetailRepository::getDataByFromtypeFlowAllocation($buildingId, $fromDate, $toDate, @$request['bdc_apartment_id'] ?? null);
                    foreach ($dataPhanbo as $item) {
                        if(@$item->coin == 0){
                            continue;
                        }
                        $ghichu=null;
                        $so_chung_tu = null;
                        if (@$item->from_type == 1 || @$item->from_type == 4 || @$item->from_type == 6){
                            $rs_receipt = Receipts::get_detail_receipt_by_receipt_id($item->from_id);
                            $ghichu = @$rs_receipt->description;
                            $so_chung_tu = "PB_" . $item->id;
                        }
                        else if(@$item->from_type == 2){
                            $ghichu ='Hạch toán tự động';
                            $so_chung_tu = "AUTO_" . $item->id;
                        }
                        else if(@$item->from_type == 5){
                            $rs_receipt = Receipts::get_detail_receipt_by_receipt_id($item->from_id);
                            $ghichu ='[Huỷ phiếu thu]_'.@$rs_receipt->description;
                            $so_chung_tu = "PB_" . $item->id;
                        }
                        else{
                            $ghichu = @$item->note;
                            $so_chung_tu = "PB_" . $item->id;
                        }
                    
                        if ($item->bdc_apartment_service_price_id != 0) {
                            $servicePrice = ApartmentServicePriceRepository::getInfoServiceApartmentById($item->bdc_apartment_service_price_id);
                            $service =$servicePrice ? Service::get_detail_bdc_service_by_bdc_service_id($servicePrice->bdc_service_id) : null;
                            $vehicle_2 =  @$servicePrice->bdc_vehicle_id > 0 ? Vehicles::get_detail_vehicle_by_id($servicePrice->bdc_vehicle_id) : null;
                            $ma_phi = @$servicePrice->bdc_vehicle_id > 0 ? @$service->name . ' - ' . @$vehicle_2->number : @$service->name;
                        }
                        if ($item->from_id != 0) {
                            $servicePrice = ApartmentServicePriceRepository::getInfoServiceApartmentById($item->from_id);
                            $service_b = $servicePrice ? Service::get_detail_bdc_service_by_bdc_service_id($servicePrice->bdc_service_id) : null;
                            $vehicle_2 =  @$servicePrice->bdc_vehicle_id > 0 ? Vehicles::get_detail_vehicle_by_id($servicePrice->bdc_vehicle_id) : null;
                            $ma_phi_b = @$servicePrice->bdc_vehicle_id > 0 ? @$service_b->name . ' - ' .  @$vehicle_2->number : @$service_b->name;
                        }

                        $name_service_a = $item->bdc_apartment_service_price_id != 0 ? @$service->name : "chưa chỉ định";
                        $name_service_b = $item->from_id != 0 ? @$service_b->name : "chưa chỉ định";

                        $diendai = 'Phân bổ tiền thừa từ ' . $name_service_b . ' sang ' . $name_service_a;
                        $apartment = ApartmentsRespository::getInfoApartmentsById($item->bdc_apartment_id);
                        $buildingPlace = BuildingPlaceRepository::getInfoBuildingPlaceById($apartment->building_place_id);
                        $content[] = [
                            'Loại NK' =>   "PKT",
                            'Hình thức' =>  '',
                            'Kiểu phiếu' =>  '',
                            'Số chứng từ' =>  $so_chung_tu,
                            'Kỳ kế toán' =>  @$item->cycle_name,
                            'Ngày phát sinh' =>  @$rs_receipt ? date('d/m/Y', strtotime(@$rs_receipt->created_at)) : date('d/m/Y', strtotime(@$item->created_at)),
                            'Diễn giải' => $diendai,
                            'Mã Khách hàng-NCC' => @$apartment->code_customer,
                            'Mã Ngân hàng' =>  "",
                            'Cty Con - NH cho DXMB vay' =>  '',
                            'Mã Phòng ban' =>  @$building->building_code_manage,
                            'Mã Nhân viên' =>  '',
                            'Mã phí' =>  $item->bdc_apartment_service_price_id != 0 ? $ma_phi : null,
                            'Hợp đồng' => '',
                            'Sản phẩm' =>  @$apartment->code,
                            'Block' =>   @@$buildingPlace->code,
                            'Dự án' =>  @$building->name,
                            'Mã thu' =>  $item->bdc_apartment_service_price_id != 0 ? @$service->code_receipt : null,
                            'Khế ước' =>  "",
                            'CP không hợp lệ' =>  "",
                            'Mã tài khoản' =>  "",
                            'TKDƯ' =>    "",
                            'Số tiền' =>   (int) (@$item->coin),
                            'Nợ/Có' =>  '',
                            'Ký hiệu hóa đơn' =>  "",
                            'Số hóa đơn' =>  "",
                            'Ngày hóa đơn' =>  "",
                            'Loại thuế' => "",
                            'Thuế suất' =>  "",
                            'Tiền trước thuế' => "",
                            'Mẫu số hóa đơn' => "",
                            'Người nộp' =>  "",
                            'Người bán hàng' =>  "",
                            'Phiếu cấn trừ' =>  "",
                            'Mã phiếu eApprove' =>  "",
                            'Ghi chú' => $ghichu,
                        ];
                        $content[] = [
                            'Loại NK' =>   "PKT",
                            'Hình thức' =>  '',
                            'Kiểu phiếu' =>  '',
                            'Số chứng từ' =>  $so_chung_tu,
                            'Kỳ kế toán' =>  @$item->cycle_name,
                            'Ngày phát sinh' => @$rs_receipt ? date('d/m/Y', strtotime(@$rs_receipt->created_at)) : date('d/m/Y', strtotime(@$item->created_at)),
                            'Diễn giải' => $diendai,
                            'Mã Khách hàng-NCC' => @$apartment->code_customer,
                            'Mã Ngân hàng' =>  "",
                            'Cty Con - NH cho DXMB vay' =>  '',
                            'Mã Phòng ban' =>  @$building->building_code_manage,
                            'Mã Nhân viên' =>  '',
                            'Mã phí' => $item->from_id != 0 ? $ma_phi_b : null,
                            'Hợp đồng' => '',
                            'Sản phẩm' =>  @$apartment->code,
                            'Block' =>   @@$buildingPlace->code,
                            'Dự án' =>  @$building->name,
                            'Mã thu' => $item->from_id != 0 ? @$service_b->code_receipt : null,
                            'Khế ước' =>  "",
                            'CP không hợp lệ' =>  "",
                            'Mã tài khoản' =>  "",
                            'TKDƯ' =>    "",
                            'Số tiền' =>   0 - (int)(@$item->coin),
                            'Nợ/Có' =>  '',
                            'Ký hiệu hóa đơn' =>  "",
                            'Số hóa đơn' =>  "",
                            'Ngày hóa đơn' =>  "",
                            'Loại thuế' => "",
                            'Thuế suất' =>  "",
                            'Tiền trước thuế' => "",
                            'Mẫu số hóa đơn' => "",
                            'Người nộp' =>  "",
                            'Người bán hàng' =>  "",
                            'Phiếu cấn trừ' =>  "",
                            'Mã phiếu eApprove' =>  "",
                            'Ghi chú' =>$ghichu,
                        ];
                    }
                     // lấy ra danh sách hạch toán tự động
                     $dataHachtoan = LogCoinDetailRepository::getDataByFromtypeAutoAccounting($buildingId, $fromDate, $toDate, @$request['bdc_apartment_id'] ?? null);
                     foreach ($dataHachtoan as $item) {
                         if(@$item->coin == 0){
                             continue;
                         }
                         $ghichu=null;
                         $so_chung_tu = null;
                         if (@$item->from_type == 1 || @$item->from_type == 4 || @$item->from_type == 6){
                             $rs_receipt = Receipts::get_detail_receipt_by_receipt_id($item->from_id);
                             $ghichu = @$rs_receipt->description;
                             $so_chung_tu = "PB_" . $item->id;
                         }
                         else if(@$item->from_type == 2){
                             $ghichu ='Hạch toán tự động';
                             $so_chung_tu = "AUTO_" . $item->id;
                         }
                         else if(@$item->from_type == 5){
                             $rs_receipt = Receipts::get_detail_receipt_by_receipt_id($item->from_id);
                             $ghichu ='[Huỷ phiếu thu]_'.@$rs_receipt->description;
                             $so_chung_tu = "PB_" . $item->id;
                         }
                         else{
                             $ghichu = @$item->note;
                             $so_chung_tu = "PB_" . $item->id;
                         }
                     
                         if ($item->bdc_apartment_service_price_id != 0) {
                             $servicePrice = ApartmentServicePriceRepository::getInfoServiceApartmentById($item->bdc_apartment_service_price_id);
                             $service =$servicePrice ? Service::get_detail_bdc_service_by_bdc_service_id($servicePrice->bdc_service_id) : null;
                             $vehicle_2 =  @$servicePrice->bdc_vehicle_id > 0 ? Vehicles::get_detail_vehicle_by_id($servicePrice->bdc_vehicle_id) : null;
                             $ma_phi = @$servicePrice->bdc_vehicle_id > 0 ? @$service->name . ' - ' . @$vehicle_2->number : @$service->name;
                         }
                         if ($item->from_id != 0) {
                             $servicePrice = ApartmentServicePriceRepository::getInfoServiceApartmentById($item->from_id);
                             $service_b = $servicePrice ? Service::get_detail_bdc_service_by_bdc_service_id($servicePrice->bdc_service_id) : null;
                             $vehicle_2 =  @$servicePrice->bdc_vehicle_id > 0 ? Vehicles::get_detail_vehicle_by_id($servicePrice->bdc_vehicle_id) : null;
                             $ma_phi_b = @$servicePrice->bdc_vehicle_id > 0 ? @$service_b->name . ' - ' .  @$vehicle_2->number : @$service_b->name;
                         }
 
                         $name_service_a = $item->bdc_apartment_service_price_id != 0 ? @$service->name : "chưa chỉ định";
                         $name_service_b = $item->from_id != 0 ? @$service_b->name : "chưa chỉ định";
 
                         $diendai = 'Hạch toán tiền thừa từ ' . $name_service_b . ' sang ' . $name_service_a;
                         $apartment = ApartmentsRespository::getInfoApartmentsById($item->bdc_apartment_id);
                         $buildingPlace = BuildingPlaceRepository::getInfoBuildingPlaceById($apartment->building_place_id);
                         $content[] = [
                             'Loại NK' =>   "PKT",
                             'Hình thức' =>  '',
                             'Kiểu phiếu' =>  '',
                             'Số chứng từ' =>  $so_chung_tu,
                             'Kỳ kế toán' =>  @$item->cycle_name,
                             'Ngày phát sinh' =>  @$rs_receipt ? date('d/m/Y', strtotime(@$rs_receipt->created_at)) : date('d/m/Y', strtotime(@$item->created_at)),
                             'Diễn giải' => $diendai,
                             'Mã Khách hàng-NCC' => @$apartment->code_customer,
                             'Mã Ngân hàng' =>  "",
                             'Cty Con - NH cho DXMB vay' =>  '',
                             'Mã Phòng ban' =>  @$building->building_code_manage,
                             'Mã Nhân viên' =>  '',
                             'Mã phí' =>  $item->bdc_apartment_service_price_id != 0 ? $ma_phi : null,
                             'Hợp đồng' => '',
                             'Sản phẩm' =>  @$apartment->code,
                             'Block' =>   @@$buildingPlace->code,
                             'Dự án' =>  @$building->name,
                             'Mã thu' =>  $item->bdc_apartment_service_price_id != 0 ? @$service->code_receipt : null,
                             'Khế ước' =>  "",
                             'CP không hợp lệ' =>  "",
                             'Mã tài khoản' =>  "",
                             'TKDƯ' =>    "",
                             'Số tiền' =>   (int) (@$item->coin),
                             'Nợ/Có' =>  '',
                             'Ký hiệu hóa đơn' =>  "",
                             'Số hóa đơn' =>  "",
                             'Ngày hóa đơn' =>  "",
                             'Loại thuế' => "",
                             'Thuế suất' =>  "",
                             'Tiền trước thuế' => "",
                             'Mẫu số hóa đơn' => "",
                             'Người nộp' =>  "",
                             'Người bán hàng' =>  "",
                             'Phiếu cấn trừ' =>  "",
                             'Mã phiếu eApprove' =>  "",
                             'Ghi chú' => $ghichu,
                         ];
                         $content[] = [
                             'Loại NK' =>   "PKT",
                             'Hình thức' =>  '',
                             'Kiểu phiếu' =>  '',
                             'Số chứng từ' =>  $so_chung_tu,
                             'Kỳ kế toán' =>  @$item->cycle_name,
                             'Ngày phát sinh' =>  @$rs_receipt ? date('d/m/Y', strtotime(@$rs_receipt->created_at)) : date('d/m/Y', strtotime(@$item->created_at)),
                             'Diễn giải' => $diendai,
                             'Mã Khách hàng-NCC' => @$apartment->code_customer,
                             'Mã Ngân hàng' =>  "",
                             'Cty Con - NH cho DXMB vay' =>  '',
                             'Mã Phòng ban' =>  @$building->building_code_manage,
                             'Mã Nhân viên' =>  '',
                             'Mã phí' => $item->from_id != 0 ? $ma_phi_b : null,
                             'Hợp đồng' => '',
                             'Sản phẩm' =>  @$apartment->code,
                             'Block' =>   @@$buildingPlace->code,
                             'Dự án' =>  @$building->name,
                             'Mã thu' => $item->from_id != 0 ? @$service_b->code_receipt : null,
                             'Khế ước' =>  "",
                             'CP không hợp lệ' =>  "",
                             'Mã tài khoản' =>  "",
                             'TKDƯ' =>    "",
                             'Số tiền' =>   0 - (int)(@$item->coin),
                             'Nợ/Có' =>  '',
                             'Ký hiệu hóa đơn' =>  "",
                             'Số hóa đơn' =>  "",
                             'Ngày hóa đơn' =>  "",
                             'Loại thuế' => "",
                             'Thuế suất' =>  "",
                             'Tiền trước thuế' => "",
                             'Mẫu số hóa đơn' => "",
                             'Người nộp' =>  "",
                             'Người bán hàng' =>  "",
                             'Phiếu cấn trừ' =>  "",
                             'Mã phiếu eApprove' =>  "",
                             'Ghi chú' =>$ghichu,
                         ];
                     }
                 }
             
               // dd($content);
                if ($content) {
                    $sheet->fromArray($content);
                }
            });
        })->store('xlsx',storage_path('exports/'));
        ob_end_clean();
        $file     = storage_path('exports/'.$result->filename.'.'.$result->ext);
        return response()->download($file)->deleteFileAfterSend(true);
             
    }

    public function filterReceiptExcelDetail_v2($buildingId, $request, $debitDetailRepository, $serviceRepository)
    {
        $receipts = $this->model
            ->where(['bdc_building_id' => $buildingId])
            ->where(function ($query) use ($request) {
                if (isset($request['receipt_code_type']) && $request['receipt_code_type'] != null) {
                    $linksArray = array_filter($request['receipt_code_type']);
                    if (count($linksArray) > 0) {
                        $query->whereIn('type', $linksArray);
                    }
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
                if (isset($request['ip_place_id'])) {
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
        $_SESSION['sumDauKy'] = 0;

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
                    $column_range_new = $lastColumn_new . '14';
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
                                $debitDetail = $debitDetailRepository->filterServiceBillIdWithVersion($receipt->bdc_building_id, $_data["bill_id"], $_data["service_id"], $_data["version"]);
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
                            $customer = CustomersRespository::findApartmentIdV2($receipt->bdc_apartment_id, 0);
                            $pubUserProfile = $customer ? PublicUsersProfileRespository::getInfoUserById($customer->user_info_id) : null;
                            $customerName = $customer ? @$pubUserProfile->full_name : "";
                            $_maKhachHangNNC = null;
                            // Data
                            //$loaiNK = $status;
                            $soChungTu =  $receipt->receipt_code;
                            $kyKeToan =@$_data->debit->cycle_name;
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
                            $block = @$buildingPlace->code ?? null;
                            $duAn = $building->name;
                            $maThu = "";
                            $kheUoc = "";
                            $cpKhongHopLe = "";
                            $maTaiKhoan = "";
                            $tkDu = "";
                            $soTien = $debitDetail->paid + $debitDetail->paid_v3;
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
                        if ($debitDetail->bdc_service_id == $value->id) {
                            $sheet->setCellValueByColumnAndRow($key + 5, $row, $soTien);
                        }
                    }
                    //===========================================================================
                    $sheet->setCellValueByColumnAndRow($get_all_building_count + 4, $row, $soTien);
                    $sheet->setCellValueByColumnAndRow($get_all_building_count + 5, $row, $receipt_code_type);
                    $sheet->setCellValueByColumnAndRow($get_all_building_count + 6, $row, $nguoiThu);
                    $sheet->setCellValueByColumnAndRow($get_all_building_count + 7, $row, $apartment->code);
                }
                //$sheet->setBorder('A1:F10', 'thin');
                $range_new = 'F12:' . $lastColumn_new . '13';
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
                $a = $lastColumn_new . '12:' . $lastColumn_new . '14';

                $sheet->mergeCells($a);
                $sheet->getStyle($lastColumn_new . '12')->getAlignment()->setWrapText(true);
                $sheet->cells($lastColumn_new . '12', function ($cells) {
                    $cells->setFontSize(11);
                    $cells->setFontWeight('bold');
                    $cells->setValue('Tổng cộng');
                    $cells->setValignment('center');
                    $cells->setAlignment('center');
                    $cells->setBorder('thin', 'thin', 'thin', 'thin');
                });
                $sheet->setWidth(array($lastColumn_new => 30));
                $lastColumn_new = $lastColumn++;
                $b = $lastColumn_new . '12:' . $lastColumn_new . '14';
                $sheet->mergeCells($b);
                $sheet->getStyle($lastColumn_new . '12')->getAlignment()->setWrapText(true);
                $sheet->cells($lastColumn_new . '12', function ($cells) {
                    $cells->setFontSize(11);
                    $cells->setFontWeight('bold');
                    $cells->setValue('Hình thức');
                    $cells->setValignment('center');
                    $cells->setAlignment('center');
                    $cells->setBorder('thin', 'thin', 'thin', 'thin');
                });
                $sheet->setWidth(array($lastColumn_new => 30));
                $lastColumn_new = $lastColumn++;
                $c = $lastColumn_new . '12:' . $lastColumn_new . '14';
                $sheet->mergeCells($c);
                $sheet->getStyle($lastColumn_new . '12')->getAlignment()->setWrapText(true);
                $sheet->cells($lastColumn_new . '12', function ($cells) {
                    $cells->setFontSize(11);
                    $cells->setFontWeight('bold');
                    $cells->setValue('NV thu');
                    $cells->setValignment('center');
                    $cells->setAlignment('center');
                    $cells->setBorder('thin', 'thin', 'thin', 'thin');
                });
                $sheet->setWidth(array($lastColumn_new => 30));
                $lastColumn_new = $lastColumn++;
                $d = $lastColumn_new . '12:' . $lastColumn_new . '14';
                $sheet->mergeCells($d);
                $sheet->getStyle($lastColumn_new . '12')->getAlignment()->setWrapText(true);
                $sheet->cells($lastColumn_new . '12', function ($cells) {
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
                $b_footer = 'B' . $total_row;
                $sheet->cells($b_footer, function ($cells) {
                    $cells->setFontSize(11);
                    $cells->setFontWeight('bold');
                    $cells->setValue('Người Nộp Tiền');
                    $cells->setAlignment('center');
                });

                $e_footer = 'E' . $total_row;

                $sheet->cells($e_footer, function ($cells) {
                    $cells->setFontSize(11);
                    $cells->setFontWeight('bold');
                    $cells->setValue('Kế Toán');
                    $cells->setAlignment('center');
                });

                $total_row_new = $total_row - 1;
                $h_footer_1 = 'H' . $total_row_new;

                $sheet->cells($h_footer_1, function ($cells) {
                    $cells->setFontSize(11);
                    $cells->setFontWeight('bold');
                    $cells->setValue('Ngày.....Tháng.....Năm.....');
                    $cells->setAlignment('center');
                });

                $h_footer_2 = 'H' . $total_row;

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
    public function updatePaymentAndLogCoin($receipt){
        $logCoinDetail = LogCoinDetail::where(['from_id' => $receipt->id, 'from_type' => 1])->get();
        if($logCoinDetail->count()==0){
            $logCoinDetail = LogCoinDetail::where(['note' => $receipt->id, 'from_type' => 4])->get();
            if($logCoinDetail->count()==0){
                $logCoinDetail = LogCoinDetail::where(['note' => $receipt->id, 'from_type' => 9])->get();
                if($logCoinDetail->count()==0){
                    $logCoinDetail = LogCoinDetail::where(['from_id' => $receipt->id, 'from_type' => 6])->get();
                    if($logCoinDetail->count()==0){
                        $logCoinDetail = LogCoinDetail::where(['note' => $receipt->id, 'from_type' => 3])->get();
                    }
                }
            }
        }
        $_add_queue_stat_payment = null;
        foreach ($logCoinDetail as $key_1 => $value_1) {
            $cycle_name_before = $value_1->cycle_name;
            $cycle_name_coin = Carbon::parse($receipt->create_date)->format('Ym');
            $value_1->cycle_name = $cycle_name_coin;
           // $value_1->note = $receipt->description;
            $value_1->save();
            $_add_queue_stat_payment[] = [
                "apartmentId" => $value_1->bdc_apartment_id,
                "service_price_id" => $value_1->bdc_apartment_service_price_id,
                "cycle_name" => $cycle_name_before,
            ];
            $_add_queue_stat_payment[] = [
                "apartmentId" => $value_1->bdc_apartment_id,
                "service_price_id" => $value_1->bdc_apartment_service_price_id,
                "cycle_name" => $cycle_name_coin,
            ]; 
        }

        $paymentDetail = PaymentDetail::where('bdc_receipt_id', $receipt->id)->get();
        foreach ($paymentDetail as $key_2 => $value_2) {
            $cycle_name_before = $value_2->cycle_name;
            $cycle_name_payment = Carbon::parse($receipt->create_date)->format('Ym');
            $value_2->cycle_name = $cycle_name_payment;
            $value_2->save();
            $_add_queue_stat_payment[] = [
                "apartmentId" => $value_2->bdc_apartment_id,
                "service_price_id" => $value_2->bdc_apartment_service_price_id,
                "cycle_name" => $cycle_name_before,
            ];
            $_add_queue_stat_payment[] = [
                "apartmentId" => $value_2->bdc_apartment_id,
                "service_price_id" => $value_2->bdc_apartment_service_price_id,
                "cycle_name" => $cycle_name_payment,
            ];  
        }
        if($_add_queue_stat_payment && count($_add_queue_stat_payment) > 0){
            foreach ($_add_queue_stat_payment as $key => $value) {
               QueueRedis::setItemForQueue('add_queue_stat_payment_', $value);
            }
        }
    }
    public function updateReceipt($id, $request)
    {
        $receipt = $this->model->find($id);

        $receipt->type_payment  =  $request['type_payment'];
        $receipt->customer_name  =  $request['customer_name'];
        $receipt->description  =  isset($request['description']) ? $request['description'] : $receipt->description;
        $receipt->create_date  =  isset($request['create_date']) ? Carbon::parse($request['create_date'])  : $receipt->create_date;
        $receipt->type  =  isset($request['type']) ? $request['type'] : $receipt->type;
        $receipt->updated_by = auth()->user()->id;
        $receipt->save();
        
        $this->updatePaymentAndLogCoin($receipt);
        Cache::store('redis')->forget(env('REDIS_PREFIX') . 'get_detail_receiptById_' . $id);
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
    public function autoIncrementReceiptCodeAdjustmentSlip($config, $buildingId)
    {
        // $billCount = $this->model->count();
        $filterByKey = $config->getConfigbyKey($config::ADJUSTMENT_SLIP, $buildingId);
        $receipt = collect(DB::select(DB::raw("SELECT MAX(RIGHT(`receipt_code`, 7)) as receipt_code FROM `bdc_receipts` 
            WHERE `type`=:typeCode AND `bdc_building_id`=:buildingId AND `receipt_code` LIKE '%$filterByKey%'"), ['buildingId' => $buildingId, 'typeCode' => self::PHIEU_DIEUCHINH]))->first();
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

    public function searchByApi($building_id, $request, $where = [], $perpage = 20)
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
            'select'   => ['id', 'bdc_building_id', 'bdc_apartment_id', 'receipt_code', 'url', 'cost', 'customer_name', 'customer_address', 'provider_address', 'bdc_receipt_total', 'type_payment', 'description', 'created_at'],
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
    public function sumThu($buildingId,$idNew)
    {
        $sql = "SELECT COALESCE(SUM(cost), 0) as tong_thu from (
            SELECT cost from (SELECT
                (@cnt := @cnt + 1) AS idNew,
                t.*
            FROM bdc_receipts AS t
            CROSS JOIN (SELECT @cnt := 0) AS dummy
            WHERE bdc_building_id = $buildingId";
            if($buildingId == 17 || $buildingId == 77 || $buildingId == 111){
                $sql .=" AND config_type_payment is not null";
            }
            $sql.=" AND type_payment = 'tien_mat' AND type != 'phieu_dieu_chinh' AND deleted_at is null ORDER BY create_date DESC, id DESC) as tb1
            WHERE type != 'phieu_chi' AND type != 'phieu_chi_khac' AND type != 'phieu_hoan_ky_quy' AND type != 'phieu_dieu_chinh' and idNew >= $idNew) as tb2";
        return DB::select(DB::raw($sql))[0]->tong_thu;
    }
    public function sumChi($buildingId,$idNew)
    {
        $sql = "SELECT COALESCE(SUM(cost), 0) as tong_chi from (
            SELECT cost from (SELECT
                (@cnt := @cnt + 1) AS idNew,
                t.*
            FROM bdc_receipts AS t
            CROSS JOIN (SELECT @cnt := 0) AS dummy
            WHERE bdc_building_id = $buildingId";
            if($buildingId == 17 || $buildingId == 77 || $buildingId == 111){
                $sql .=" AND config_type_payment is not null";
            }
            $sql.=" AND type_payment = 'tien_mat' AND type != 'phieu_dieu_chinh' AND deleted_at is null ORDER BY create_date DESC, id DESC) as tb1
            WHERE (type = 'phieu_chi' OR type = 'phieu_chi_khac' OR type = 'phieu_hoan_ky_quy') and idNew >= $idNew) as tb2";
            return DB::select(DB::raw($sql))[0]->tong_chi;
    }
    public function sumThuDauKy($buildingId,$idNew)
    {
        $sql = "SELECT COALESCE(SUM(cost), 0) as tong_thu from (
            SELECT cost from (SELECT
                (@cnt := @cnt + 1) AS idNew,
                t.*
            FROM bdc_receipts AS t
            CROSS JOIN (SELECT @cnt := 0) AS dummy
            WHERE bdc_building_id = $buildingId";
            if($buildingId == 17 || $buildingId == 77 || $buildingId == 111){
                $sql .=" AND config_type_payment is not null";
            }
            $sql.=" AND type_payment = 'tien_mat' AND deleted_at is null ORDER BY create_date DESC, id DESC) as tb1
            WHERE type != 'phieu_chi' AND type != 'phieu_chi_khac' AND type != 'phieu_hoan_ky_quy' and idNew > $idNew) as tb2";
        return DB::select(DB::raw($sql))[0]->tong_thu;
    }
    public function sumChiDauKy($buildingId,$idNew)
    {
        $sql = "SELECT COALESCE(SUM(cost), 0) as tong_chi from (
            SELECT cost from (SELECT
                (@cnt := @cnt + 1) AS idNew,
                t.*
            FROM bdc_receipts AS t
            CROSS JOIN (SELECT @cnt := 0) AS dummy
            WHERE bdc_building_id = $buildingId";
            if($buildingId == 17 || $buildingId == 77 || $buildingId == 111){
               $sql .=" AND config_type_payment is not null";
            }
            $sql.=" AND type_payment = 'tien_mat' AND deleted_at is null ORDER BY create_date DESC, id DESC) as tb1
            WHERE (type = 'phieu_chi' OR type = 'phieu_chi_khac' OR type = 'phieu_hoan_ky_quy') and idNew > $idNew) as tb2";
        return DB::select(DB::raw($sql))[0]->tong_chi;
    }
    public function sumChiTrongKy($buildingId,$idNewFrom,$idNewTo)
    {
        $sql = "SELECT COALESCE(SUM(cost), 0) as tong_chi from (
            SELECT cost from (SELECT
                (@cnt := @cnt + 1) AS idNew,
                t.*
            FROM bdc_receipts AS t
            CROSS JOIN (SELECT @cnt := 0) AS dummy
            WHERE bdc_building_id = $buildingId";
            if($buildingId == 17 || $buildingId == 77 || $buildingId == 111){
                $sql .=" AND config_type_payment is not null";
            }
            $sql.=" AND type_payment = 'tien_mat' AND type != 'phieu_dieu_chinh' AND deleted_at is null ORDER BY create_date DESC, id DESC) as tb1
            WHERE (type = 'phieu_chi' OR type = 'phieu_chi_khac' OR type = 'phieu_hoan_ky_quy') and idNew BETWEEN $idNewFrom and $idNewTo) as tb2";
        return DB::select(DB::raw($sql))[0]->tong_chi;
    }

    public function sumChiTrongKyVerBanking($buildingId,$idNewFrom,$idNewTo)
    {
        $sql = "SELECT COALESCE(SUM(cost), 0) as tong_chi from (
            SELECT cost from (SELECT
                (@cnt := @cnt + 1) AS idNew,
                t.*
            FROM bdc_receipts AS t
            CROSS JOIN (SELECT @cnt := 0) AS dummy
            WHERE bdc_building_id = $buildingId";
            if($buildingId == 17 || $buildingId == 77){
                $sql .=" AND config_type_payment is not null";
            }
            $sql.=" AND type_payment = 'chuyen_khoan' AND type != 'phieu_dieu_chinh' AND deleted_at is null ORDER BY create_date DESC, id DESC) as tb1
            WHERE (type = 'phieu_chi' OR type = 'phieu_chi_khac' OR type = 'phieu_hoan_ky_quy') and idNew BETWEEN $idNewFrom and $idNewTo) as tb2";
        return DB::select(DB::raw($sql))[0]->tong_chi;
    }

    public function sumChiVerBanking($buildingId,$idNew)
    {
        $sql = "SELECT COALESCE(SUM(cost), 0) as tong_chi from (
            SELECT cost from (SELECT
                (@cnt := @cnt + 1) AS idNew,
                t.*
            FROM bdc_receipts AS t
            CROSS JOIN (SELECT @cnt := 0) AS dummy
            WHERE bdc_building_id = $buildingId";
            if($buildingId == 17 || $buildingId == 77){
                $sql .=" AND config_type_payment is not null";
            }
            $sql.=" AND type_payment = 'chuyen_khoan' AND type != 'phieu_dieu_chinh' AND deleted_at is null ORDER BY create_date DESC, id DESC) as tb1
            WHERE (type = 'phieu_chi' OR type = 'phieu_chi_khac' OR type = 'phieu_hoan_ky_quy') and idNew >= $idNew) as tb2";
            return DB::select(DB::raw($sql))[0]->tong_chi;
    }
    
    public function sumThuTrongKy($buildingId,$idNewFrom,$idNewTo)
    {
        $sql = "SELECT COALESCE(SUM(cost), 0) as tong_thu from (
            SELECT cost from (SELECT
                (@cnt := @cnt + 1) AS idNew,
                t.*
            FROM bdc_receipts AS t
            CROSS JOIN (SELECT @cnt := 0) AS dummy
            WHERE bdc_building_id = $buildingId";
            if($buildingId == 17 || $buildingId == 77 || $buildingId == 111){
                $sql .=" AND config_type_payment is not null";
            }
            $sql.=" AND type_payment = 'tien_mat' AND type != 'phieu_dieu_chinh' AND deleted_at is null ORDER BY create_date DESC, id DESC) as tb1
            WHERE type != 'phieu_chi' AND type != 'phieu_chi_khac' AND type != 'phieu_hoan_ky_quy' AND type != 'phieu_dieu_chinh' and idNew BETWEEN $idNewFrom and $idNewTo) as tb2";
        return DB::select(DB::raw($sql))[0]->tong_thu;
    }
    public function sumThuTrongKy_Duongver($buildingId,$idNewFrom,$idNewTo, $request)
    { //Duong change for compare old logic with new logic
        $sql = "SELECT COALESCE(SUM(cost), 0) as tong_thu from (
            SELECT cost from (SELECT
                (@cnt := @cnt + 1) AS idNew,
                t.*
            FROM bdc_receipts AS t
            CROSS JOIN (SELECT @cnt := 0) AS dummy
            WHERE bdc_building_id = $buildingId";
            if($buildingId == 17 || $buildingId == 77 || $buildingId == 111){
                $sql .=" AND config_type_payment is not null";
            }
            $sql.=" AND type_payment = 'tien_mat' AND type != 'phieu_dieu_chinh' AND deleted_at is null ORDER BY create_date DESC, id DESC) as tb1
            WHERE type != 'phieu_chi' AND type != 'phieu_chi_khac' AND type != 'phieu_hoan_ky_quy' AND type != 'phieu_dieu_chinh' /*and idNew BETWEEN $idNewFrom and $idNewTo)*/";
            if (@$request['user_id']) {
                $us_is= $request['user_id'];
                $sql.= "AND `user_id` = $us_is";}
            if(isset($request['from_date']) && $request['from_date'] != null && isset($request['to_date']) && $request['to_date'] != null){
                    $from_date = Carbon::parse($request['from_date'])->format('Y-m-d');
                    $to_date   = Carbon::parse($request['to_date'])->format('Y-m-d');
                    $sql .=" AND create_date >= '$from_date 00:00:00' AND create_date <= '$to_date 23:59:59'";
                }
            $sql.= ") as tb2";
        return DB::select(DB::raw($sql))[0]->tong_thu;
    }
    public function sumThuVerBanking($buildingId,$idNew)
    {
        $sql = "SELECT COALESCE(SUM(cost), 0) as tong_thu from (
            SELECT cost from (SELECT
                (@cnt := @cnt + 1) AS idNew,
                t.*
            FROM bdc_receipts AS t
            CROSS JOIN (SELECT @cnt := 0) AS dummy
            WHERE bdc_building_id = $buildingId";
            if($buildingId == 17 || $buildingId == 77){
                $sql .=" AND config_type_payment is not null";
            }
            $sql.=" AND type_payment = 'chuyen_khoan' AND type != 'phieu_dieu_chinh' AND deleted_at is null ORDER BY create_date DESC, id DESC) as tb1
            WHERE type != 'phieu_chi' AND type != 'phieu_chi_khac' AND type != 'phieu_hoan_ky_quy' AND type != 'phieu_dieu_chinh' and idNew >= $idNew) as tb2";
        return DB::select(DB::raw($sql))[0]->tong_thu;
    }
    public function sumThuTrongKyVerBanking($buildingId,$idNewFrom,$idNewTo)
    {
        $sql = "SELECT COALESCE(SUM(cost), 0) as tong_thu from (
            SELECT cost from (SELECT
                (@cnt := @cnt + 1) AS idNew,
                t.*
            FROM bdc_receipts AS t
            CROSS JOIN (SELECT @cnt := 0) AS dummy
            WHERE bdc_building_id = $buildingId";
            if($buildingId == 17 || $buildingId == 77){
                $sql .=" AND config_type_payment is not null";
            }
            $sql.=" AND type_payment = 'chuyen_khoan' AND type != 'phieu_dieu_chinh' AND deleted_at is null ORDER BY create_date DESC, id DESC) as tb1
            WHERE type != 'phieu_chi' AND type != 'phieu_chi_khac' AND type != 'phieu_hoan_ky_quy' AND type != 'phieu_dieu_chinh' and idNew BETWEEN $idNewFrom and $idNewTo) as tb2";
        return DB::select(DB::raw($sql))[0]->tong_thu;
    }

    public function cashBookMoneyV2($buildingId, $request)
    {
        $sql = "SELECT * FROM(SELECT
                    (@cnt := @cnt + 1) AS idNew,
                    t.*
                FROM (SELECT  * from bdc_receipts WHERE bdc_building_id = $buildingId AND type_payment = 'tien_mat' AND type != 'phieu_dieu_chinh' AND deleted_at is null ORDER BY create_date DESC, id DESC) as t
                CROSS JOIN (SELECT @cnt := 0) AS dummy
                WHERE bdc_building_id = $buildingId AND type_payment = 'tien_mat' AND type != 'phieu_dieu_chinh' AND deleted_at is null ORDER BY create_date DESC, id DESC) as tb1 WHERE bdc_building_id = $buildingId";
                if(@$request['bdc_apartment_id']){
                    $bdc_apartment_id = $request['bdc_apartment_id'];
                    $sql .=" AND bdc_apartment_id = $bdc_apartment_id";
                }
                if(@$request['receipt_code']){
                $receipt_code = $request['receipt_code'];
                $sql .=" AND receipt_code = '$receipt_code'";
                }
                if(@$request['ip_place_id'])
                {
                    $receipt_place= $request['ip_place_id'];
                    $sql .= " AND bdc_apartment_id IN (select id From bdc_apartments where building_place_id = $receipt_place) ";
                }
                if(@$request['user_id']){
                    $receipt_userid = $request['user_id'];
                    $sql .=" AND `user_id` = $receipt_userid";
                }
                if(isset($request['from_date']) && $request['from_date'] != null && isset($request['to_date']) && $request['to_date'] != null){
                    $from_date = Carbon::parse($request['from_date'])->format('Y-m-d');
                    $to_date   = Carbon::parse($request['to_date'])->format('Y-m-d');
                    $sql .=" AND create_date >= '$from_date 00:00:00' AND create_date <= '$to_date 23:59:59'";
                }
                if($buildingId == 17 || $buildingId == 77 || $buildingId == 111){
                    $sql .=" AND config_type_payment is not null";
                }
          return  DB::table(DB::raw("($sql) as tb3 ORDER BY idNew"));
    }
    public function cashBookMoney($buildingId, $request)
    {
        $rs = $this->model->where(["bdc_building_id" => $buildingId, "type_payment" => self::TIENMAT])
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
            })->orderBy('create_date', 'ASC')->orderBy('updated_at', 'ASC');
        return $rs;
    }

    public function cashBookMoneyNew($buildingId, $request)
    {
        $rs = $this->model->where(["bdc_building_id" => $buildingId, "type_payment" => self::TIENMAT])
            // ->Where('type', '!=', self::PHIEUKETOAN)
            ->where(function ($query) use ($request) {
                if (isset($request['bdc_apartment_id']) && $request['bdc_apartment_id'] != null) {
                    $query->where('bdc_apartment_id', '=', $request['bdc_apartment_id']);
                }
                if (isset($request['receipt_code']) && $request['receipt_code'] != null) {
                    $query->where('receipt_code', 'LIKE', '%' . $request['receipt_code'] . '%');
                }
                if (isset($request['user_id']) && $request['user_id'] != null) {
                    $query->where('user_id', $request['user_id']);
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
            $sql .= " AND `b`.`create_date` >= '$from_date' AND `b`.`create_date` <= '$to_date 23:59:59'";
        }
        if ($buildingId == 17 || $buildingId ==77) {
            $sql .= " AND `b`.`config_type_payment` IS NOT NULL";
        }
        $sql .= " AND `b`.`deleted_at` IS NULL) AS 'thu_tien',
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
            $sql .= " AND `c`.`create_date` >= '$from_date' AND `c`.`create_date` <= '$to_date 23:59:59'";
        }
        if ($buildingId == 17 || $buildingId ==77) {
            $sql .= " AND `c`.`config_type_payment` IS NOT NULL";
        }
        $sql .= " AND `c`.`deleted_at` IS NULL) AS 'chi_tien'
                    FROM
                        `bdc_receipts` AS `a`
                    WHERE
                        `a`.`feature` = 'deposit' 
                            AND `a`.`deleted_at` IS NULL";
        if ($buildingId == 17 || $buildingId ==77) {
            $sql .= " AND `a`.`config_type_payment` IS NOT NULL";
        }                  
        $sql.=" ) AS tb1
                        INNER JOIN
                    `bdc_apartments` ON `bdc_apartments`.`id` = `tb1`.`bdc_apartment_id`
                WHERE
                `bdc_apartments`.`deleted_at` IS NULL) AS tb2 WHERE `tb2`.`building_id` = $buildingId ";
        if (isset($request['bdc_apartment_id']) && $request['bdc_apartment_id'] != null) {
            $bdc_apartment_id    = $request['bdc_apartment_id'];
            $sql .= "AND `tb2`.`bdc_apartment_id` = $bdc_apartment_id";
        }
        if (isset($request['ip_place_id']) && $request['ip_place_id'] != null) {
            $ip_place_id    = $request['ip_place_id'];
            $sql .= "AND `tb2`.`building_place_id` = $ip_place_id";
        }
        return DB::table( DB::raw("($sql) as sub") );
    }

    public function exportReportDeposit($buildingId, $request)
    {
        $name_building = Building::where('id', $buildingId)->first();
        $_SESSION['Building_name'] = @$name_building->name;
        $_SESSION['Building_address'] = @$name_building->address;

        $result = $this->receiptReportDeposit($buildingId, $request)->get();

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
                    $sheet->cells('C7', function ($cells) use ($from_date, $to_date) {
                        $cells->setFontSize(11);
                        $cells->setFontWeight('Iatalic');
                        $cells->setValue('Từ ngày ' . $from_date . ' Đến ' . $to_date);
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
                    $sheet->setCellValueByColumnAndRow(4, $row, ($value->thu_tien - $value->chi_tien));
                    $sheet->setCellValueByColumnAndRow(5, $row, $value->code);
                }

                // begin - footer 
                $total_row = count($result) + 20;

                $total_row_new = $total_row + 1;
                $h_footer_1 = 'E' . $total_row_new;

                $sheet->cells($h_footer_1, function ($cells) {
                    $cells->setFontSize(11);
                    $cells->setFontWeight('bold');
                    $cells->setValue('Người lập');
                    $cells->setAlignment('center');
                });

                $h_footer_2 = 'E' . $total_row;

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
            ->where(function ($query) use ($request) {
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
            // ->Where('type', '!=', self::PHIEUKETOAN)
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
        $response = $this->model->where(["bdc_building_id" => $buildingId, "type_payment" => self::TIENMAT])
            ->where(function ($query) use ($input) {
                $query->where('type', self::PHIEUCHI)->orWhere('type', self::PHIEUCHIKHAC)->orWhere('type', self::PHIEUHOAN_KYQUY);
            })
            ->where(function ($query) use ($input) {
                if (isset($input["from_date"])) {
                    $query->where('create_date', '<', Carbon::parse($input["from_date"]));
                }
                if (isset($request['bdc_apartment_id']) && $request['bdc_apartment_id'] != null) {
                    $query->where('bdc_apartment_id', $request['bdc_apartment_id']);
                }
            })->orderBy('create_date', 'ASC');
        if($buildingId == 17 || $buildingId == 77 || $buildingId == 111){
            $response->whereNotNull('config_type_payment');
        }
        return $response;
    }
    public function cashBookMoneyVerBanking($buildingId, $request)
    {
        $sql = "SELECT * FROM(SELECT
                    (@cnt := @cnt + 1) AS idNew,
                    t.*
                FROM (SELECT  * from bdc_receipts WHERE bdc_building_id = $buildingId AND type_payment = 'chuyen_khoan' AND type != 'phieu_dieu_chinh' AND deleted_at is null ORDER BY create_date DESC, id DESC) as t
                CROSS JOIN (SELECT @cnt := 0) AS dummy
                WHERE bdc_building_id = $buildingId AND type_payment = 'chuyen_khoan' AND type != 'phieu_dieu_chinh' AND deleted_at is null ORDER BY create_date DESC, id DESC) as tb1 WHERE bdc_building_id = $buildingId";
                if(@$request['bdc_apartment_id']){
                    $bdc_apartment_id = $request['bdc_apartment_id'];
                    $sql .=" AND bdc_apartment_id = $bdc_apartment_id";
                }
                if(@$request['ip_place_id'])
                {
                    $receipt_place= $request['ip_place_id'];
                    $sql .= " AND bdc_apartment_id IN (select id From bdc_apartments where building_place_id = $receipt_place) ";
                }
                if(@$request['receipt_code']){
                $receipt_code = $request['receipt_code'];
                $sql .=" AND receipt_code = '$receipt_code'";
                }
                if(isset($request['from_date']) && $request['from_date'] != null && isset($request['to_date']) && $request['to_date'] != null){
                    $from_date = Carbon::parse($request['from_date'])->format('Y-m-d');
                    $to_date   = Carbon::parse($request['to_date'])->format('Y-m-d');
                    $sql .=" AND create_date >= '$from_date 00:00:00' AND create_date <= '$to_date 23:59:59'";
                }
                if(@$request['user_id']){
                    $receipt_userid = $request['user_id'];
                    $sql .=" AND `user_id` = $receipt_userid";
                }
                
                if($buildingId == 17 || $buildingId == 77 || $buildingId == 111){
                    $sql .=" AND config_type_payment is not null";
                }
          return  DB::table(DB::raw("($sql) as tb3 ORDER BY idNew"));
    }
    public function cashBookMoneyDaukyVerBanking($buildingId, $input)
    {
        $response = $this->model->where(["bdc_building_id" => $buildingId, "type_payment" => self::CHUYENKHOAN])
            ->where(function ($query) use ($input) {
                $query->where('type', self::PHIEUCHI)->orWhere('type', self::PHIEUCHIKHAC)->orWhere('type', self::PHIEUHOAN_KYQUY);
            })
            ->where(function ($query) use ($input) {
                if (isset($input["from_date"])) {
                    $query->where('create_date', '<', Carbon::parse($input["from_date"]));
                }
                if (isset($request['bdc_apartment_id']) && $request['bdc_apartment_id'] != null) {
                    $query->where('bdc_apartment_id', $request['bdc_apartment_id']);
                }
            })->orderBy('create_date', 'ASC');
        if($buildingId == 17 || $buildingId == 77){
            $response->whereNotNull('config_type_payment');
        }
        return $response;
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
        $response = $this->model->where(["bdc_building_id" => $buildingId, "type_payment" => $typePayment])
            // ->Where('type', '!=', self::PHIEUKETOAN)
            ->where(function ($query) use ($input) {
                $query->where('type', '<>', ReceiptRepository::PHIEUCHI)->where('type', '<>', ReceiptRepository::PHIEUCHIKHAC)->where('type', '<>', ReceiptRepository::PHIEUHOAN_KYQUY)->where('type', '<>', ReceiptRepository::PHIEU_DIEUCHINH);
            })
            ->where(function ($query) use ($input) {
                if (isset($input)) {
                    $query->where('create_date', '<', Carbon::parse($input["from_date"]));
                }
                if (isset($request['bdc_apartment_id']) && $request['bdc_apartment_id'] != null) {
                    $query->where('bdc_apartment_id', $request['bdc_apartment_id']);
                }
            })->orderBy('create_date', 'ASC');
            if($buildingId == 17 || $buildingId == 77 || $buildingId == 111){
                $response->whereNotNull('config_type_payment');
            }
         return $response;
    }

    public function findReceiptCodePay($code, $building_id = null)
    {
        return DB::table('bdc_receipts')->where('receipt_code', $code)->where(function ($query) use ($building_id) {
            if ($building_id) {
                $query->where('bdc_building_id', $building_id);
            }
        })->first();
    }

    public function updateAttribute($id, $data = [])
    {
        return $this->model->where('id', $id)->update($data);
    }

    public function LatestRecordDatabaseByDatetime($cost,$apartmentId,$type_payment)
    {
        $end_time = Carbon::now();
        $start_time = Carbon::now()->subMinute(30);
        return $this->model->where(['cost' => $cost, 'bdc_apartment_id' => $apartmentId, 'type_payment' => $type_payment])->whereBetween('created_at', [$start_time, $end_time])->orderBy('created_at', 'desc')->first();
    }
    public function filterByBillId($billId, $buildingId, $apartmentId, $typePayment)
    {
        $rs = $this->model
            ->where(['bdc_building_id' => $buildingId, 'bdc_apartment_id' => $apartmentId])
            ->where('bdc_bill_id', 'LIKE', '%' . $billId . '%');
        if ($typePayment != "all") {
            $rs->where(['type_payment' => $typePayment]);
        }
        return $rs->get();
    }

    public function dauKy($building, $type, $createdAt)
    {
        $response = $this->model->where(['type' => $type, 'bdc_building_id' => $building])->where(function ($query) use ($createdAt) {
            if (isset($createdAt) && $createdAt != null) {
                $query->whereDate('created_at', '<', $createdAt);
            }
            $query->where('type', self::PHIEUTHU_KYQUY)->orWhere('type', self::PHIEUHOAN_KYQUY);
        })->where('feature', self::DEPOSIT);
        if($building == 17 || $building == 77 || $building == 111){
            $response->whereNotNull('config_type_payment'); // lây phiếu thu v1 cho tòa imperial plaza và phương đông
        }
        $response->get();
        return $response;
    }

    public function totalCost($building, $type, $fromDate, $toDate)
    {
        $response = $this->model->where(['type' => $type, 'bdc_building_id' => $building])->where(function ($query) use ($fromDate, $toDate) {
            if ($fromDate != null && $toDate != null) {
                $query->whereDate('created_at', '>=', $fromDate)->whereDate('created_at', '<=', $toDate);
            }
            $query->where('type', self::PHIEUTHU_KYQUY)->orWhere('type', self::PHIEUHOAN_KYQUY);
        })->where('feature', self::DEPOSIT);
        if($building == 17 || $building == 77 || $building == 111){
            $response->whereNotNull('config_type_payment'); // lây phiếu thu v1 cho tòa imperial plaza và phương đông
        }
        $response->get();
        return $response;
    }
}