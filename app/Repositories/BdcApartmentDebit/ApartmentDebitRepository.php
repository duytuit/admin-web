<?php

namespace App\Repositories\BdcApartmentDebit;

use App\Repositories\Eloquent\Repository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Excel;

const OUT_OF_DATE = 2;
const TYPE = 1;

class ApartmentDebitRepository extends Repository {
    /**
     * Specify Model class name
     *
     * @return mixed
     */
    function model()
    {
        return \App\Models\BdcApartmentDebit\ApartmentDebit::class;
    }

    public function findDebitPeriodCode($debitPeriodCode)
    {
        $startDate = Carbon::now()->startOfMonth();
        $endDate = Carbon::now()->endOfMonth();
        return $this->model->where(['debit_period_code' => $debitPeriodCode])->first();
        // return $this->model->firstOrNew(['debit_period_code' => $debitPeriodCode]);
        // ->whereBetween('to_date', [$startDate, $endDate]);
    }

    public function getAll($building, $perPage)
    {
        $monthNow = Carbon::now()->month;
        return $this->model->where('bdc_building_id',$building)->paginate($perPage);
    }

    public function filterDebit($request, $perPage)
    {
        $response = $this->model->whereHas('building', function (Builder $query) use ($request) {
            if (isset($request['bdc_building_id'])) {
                $query->where('id', '=', $request['bdc_building_id']);
            }
        })->whereHas('apartment', function (Builder $query) use ($request) {
            if (isset($request['bdc_apartment_id'])) {
                $query->where('id', '=', $request['bdc_apartment_id']);
            }
        })->where(function ($query) use ($request) {
            if (isset($request['status']))
            {
                $query->where('status', '=', $request['status']);
            }
        })->where(function ($query) use ($request) {
            if (isset($request['from_date']) && isset($request['to_date'])) {
                $from_date = Carbon::parse($request['from_date'])->format('Y-m-d');
                $to_date   = Carbon::parse($request['to_date'])->format('Y-m-d');

                $query->whereDate('created_at', '>=', $from_date);
                $query->whereDate('created_at', '<=', $to_date);
            }
        })->paginate($perPage);

        return $response;
    }

    public function excelDebitFilter($building, $request) {
        $response = $this->model->where('bdc_building_id',$building)
            ->whereHas('apartment', function (Builder $query) use ($request) {
            if (isset($request['bdc_apartment_id'])) {
                $query->where('id', '=', $request['bdc_apartment_id']);
            }
        })->where(function ($query) use ($request) {
            if (isset($request['status']))
            {
                $query->where('status', '=', $request['status']);
            }
        })->where(function ($query) use ($request) {
            if (isset($request['from_date']) && isset($request['to_date'])) {
                $from_date = Carbon::parse($request['from_date'])->format('Y-m-d');
                $to_date   = Carbon::parse($request['to_date'])->format('Y-m-d');

                $query->whereDate('created_at', '>=', $from_date);
                $query->whereDate('created_at', '<=', $to_date);
            }
        })->get();

        $result = Excel::create('Công nợ tổng hợp', function ($excel) use ($response) {
            $excel->setTitle('Công nợ tổng hợp');
            $excel->sheet('Công nợ tổng hợp', function ($sheet) use ($response) {
                $debits = [];
                foreach ($response as $key => $value) {
                    $user = [];
                    if($value->status == 0)
                    {
                        $status = 'Quá hạn';
                    } elseif ($value->status == 1)
                    {
                        $status = 'Đã thanh toán';
                    } else {
                        $status = 'Chưa thanh toán';
                    };
                    foreach ($value->apartment->bdcCustomers as $lock)
                    {
                        if ($lock->type == TYPE)
                        {
                            array_push($user,$lock->pubUserProfile->display_name);
                        }
                    }
                    $debits[] = [
                        'STT'               => ($key + 1),
                        'Tên Khách hàng' => implode('',$user),
                        'Căn hộ'               => $value->apartment->name,
                        'Tòa nhà'             => $value->building->name,
                        'Tổng công nợ'             => number_format($value->total),
                        'Đã thanh toán'        => number_format($value->total_paid),
                        'Còn nợ'        => number_format($value->old_owed + $value->new_owed),
                        'TT lần cuối'        => $value->created_at,
                        'Trạng thái'        => $status,
                    ];
                }
                if ($debits) {
                    $sheet->fromArray($debits);
                }
            });
        })->store('xlsx',storage_path('exports/'));
$file     = storage_path('exports/'.$result->filename.'.'.$result->ext);
return response()->download($file)->deleteFileAfterSend(true);
             

    }

    public function excelDebitIndex($building)
    {
        $debit = $this->model->where('bdc_building_id',$building)->get();
        $result = Excel::create('Công nợ tổng hợp', function ($excel) use ($debit) {
            $excel->setTitle('Công nợ tổng hợp');
            $excel->sheet('Công nợ tổng hợp', function ($sheet) use ($debit) {
                $debits = [];
                foreach ($debit as $key => $value) {
                    $user = [];
                    if($value->status == 0)
                    {
                        $status = 'Quá hạn';
                    } elseif ($value->status == 1)
                    {
                        $status = 'Đã thanh toán';
                    } else {
                        $status = 'Chưa thanh toán';
                    };
                    foreach ($value->apartment->bdcCustomers as $lock)
                    {
                        if ($lock->type == TYPE)
                        {
                            array_push($user,$lock->pubUserProfile->display_name);
                        }
                    }
                    $debits[] = [
                        'STT'               => ($key + 1),
                        'Tên Khách hàng' => implode('',$user),
                        'Căn hộ'               => $value->apartment->name,
                        'Tòa nhà'             => $value->building->name,
                        'Tổng công nợ'             => number_format($value->total),
                        'Đã thanh toán'        => number_format($value->total_paid),
                        'Còn nợ'        => number_format($value->old_owed + $value->new_owed),
                        'TT lần cuối'        => $value->created_at,
                        'Trạng thái'        => $status,
                    ];
                }
                if ($debits) {
                    $sheet->fromArray($debits);
                }
            });
        })->store('xlsx',storage_path('exports/'));
$file     = storage_path('exports/'.$result->filename.'.'.$result->ext);
return response()->download($file)->deleteFileAfterSend(true);
             
    }

    public function showDebitApartment($id,$perPage)
    {
        return $this->model->where('bdc_apartment_id',$id)->paginate($perPage);
    }
}
