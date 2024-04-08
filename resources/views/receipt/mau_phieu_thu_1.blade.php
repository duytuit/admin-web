<?php $converMoney = App\Services\ConvertMoney::NumberToWords(@$sum_total_paid); ?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>{{ @$building->name }}</title>
    <link rel="stylesheet" href="{{ url('adminLTE/css/bootstrap.min.css') }}" />
    <link rel="stylesheet" href="{{ url('adminLTE/css/bootstrap-theme.min.css') }}" />
</head>

<body>
    <div class="container" style="padding: 25px 10%;">
        <div class="information">
            <table width="100%">
                <tr>
                    <td align="left">
                        <div>BAN QUẢN LÝ TÒA NHÀ {{ @$building->name }}</div>
                        <p>{{ @$building->address }}</p>
                    </td>
                </tr>
            </table>
        </div>
        <div style=" text-align: center;">
            <p class="text-invoice">{{App\Commons\Helper::loai_danh_muc[$receipt->type]}}</p>
        </div>
        <div class="content_text">
            <div class="list_service">
                <table width="100%" border="1">
                    <thead>
                        <tr style="font-size: 16px;">
                            <td colspan="5">
                                @if (@$receipt->type == 'phieu_bao_co')
                                    <div align="left">Người chuyển khoản: &nbsp;<b>{{@$receipt->corresponsiveName ?? @$receipt->customer_name }} - {{ @$apartment->name }}</b></div>
                                    <div align="left">Mã giao dịch: &nbsp;{{ @$receipt->payment_transaction }}</div>
                                @else
                                    @if (@$receipt->type == 'phieu_chi' || @$receipt->type == 'phieu_chi_khac' || @$receipt->type == 'phieu_hoan_ky_quy')
                                        <div align="left">Người nhận tiền: &nbsp;<b>{{ @$receipt->customer_name }} - {{ @$apartment->name }}</b></div>
                                    @else
                                         <div align="left">Người nộp tiền: &nbsp;<b>{{ @$receipt->customer_name }} - {{ @$apartment->name }}</b></div>
                                    @endif
                                @endif
                                    {{-- <div align="left">Hình thức: {{ @$receipt->type_payment_name }}</div> --}}
                                    @php
                                         $user = App\Models\PublicUser\UserInfo::where('pub_user_id',$receipt->user_id)->first();
                                    @endphp
                                    <div align="left">Lý do thanh toán: &nbsp;{{ @$receipt->description }}</div>
                                    <div align="left">Nhân viên: &nbsp;{{ @$user->display_name }}</div>
                            </td>
                            <td colspan="4">
                                <div align="left">Số: {{ @$receipt->receipt_code }}</div>
                                <div align="left">Ngày: {{ date('d/m/Y', strtotime(@$receipt->create_date))}}</div>
                                <div align="left">Tài khoản:</div>
                            </td>
                        </tr>
                        <tr  style="font-size: 16px;">
                            <td colspan="9">
                                @if(@$receipt->type == 'phieu_thu' || @$receipt->type == 'phieu_thu_truoc' || @$receipt->type ==
                                'phieu_chi' || @$receipt->type == 'phieu_chi_khac' || @$receipt->type == 'phieu_bao_co' ||
                                @$receipt->type == 'phieu_dieu_chinh' || @$receipt->type == 'phieu_thu_ky_quy'|| @$receipt->type == 'phieu_hoan_ky_quy')
                                    <div align="left">Số tiền: <b>{{ number_format(@$sum_total_paid, 0, '', '.') }}</b> <b>VND</b>
                                        <div class="pull-right">
                                            Loại tiền: VND
                                        </div>
                                    </div>
                                    <div align="left">Bằng chữ: <b>{{ucfirst(@$converMoney)}} đồng</b></div>
                                    <div align="left">Kèm theo: ............ chứng từ gốc</div>
                                @endif
                            </td>
                        </tr>
                        @if (@$receipt->type == 'phieu_chi_khac' || @$receipt->type == 'phieu_thu_khac' || @$receipt->type == 'phieu_thu_truoc' || @$receipt->type == 'phieu_thu_ky_quy' || @$receipt->type == 'phieu_hoan_ky_quy')
                            <tr style="font-weight: bold;text-align: center;">
                                <td style="padding: 1px;width: 5px;">STT</td>
                                <td colspan="2">Diễn giải</td>
                                <td colspan="2">Danh mục</td>
                                <td>Số tiền nguyên tệ(VND)</td>
                                <td>Số tiền(VND)</td>
                            </tr>
                            <tr>
                                <td>1</td>
                                <td colspan="2">{{ @$receipt->description }}</td>
                                <td colspan="2">{{@$receipt->config_id}}</td>
                                <td width="160" style="text-align: right;">{{ number_format(@$sum_total_paid, 0, '', '.') }}</td>
                                <td width="160" style="text-align: right;">{{ number_format(@$sum_total_paid, 0, '', '.') }}</td>
                            </tr>
                        @else
                            <tr style="font-weight: bold;text-align: center;">
                                <td style="padding: 1px;">STT</td>
                                <td>Dịch vụ</td>
                                <td>Sản phẩm</td>
                                <td width="70">Thời gian</td>
                                <td width="70">Phát sinh</td>
                                <td width="70">Giảm trừ</td>
                                <td width="70">Thanh toán</td>
                                <td width="70">Ghi chú</td>
                            </tr>
                        @endif
                    </thead>
                    <tbody>
                        @if($listService)
                            @php
                            $check_paid_coin = 0;
                            $debit_metadata = @$receipt->metadata;
                            @endphp
                            @foreach ($listService as $key => $_listService)
                                @php
                                $apartmentServicePrice = @$_listService->bdc_apartment_service_price_id != 0 ?
                                App\Models\BdcApartmentServicePrice\ApartmentServicePrice::get_detail_bdc_apartment_service_price_by_apartment_id($_listService->bdc_apartment_service_price_id)
                                : null;
                                $service = $apartmentServicePrice ?
                                App\Models\Service\Service::get_detail_bdc_service_by_bdc_service_id($apartmentServicePrice->bdc_service_id)
                                : null;
                                $vehicle = @$apartmentServicePrice->bdc_vehicle_id > 0 ?
                                App\Models\Vehicles\Vehicles::get_detail_vehicle_by_id($apartmentServicePrice->bdc_vehicle_id) :
                                null;
                                @endphp
                                @php
                                $paid_coin =@$_listService->bdc_apartment_service_price_id != 0 ?
                                App\Repositories\BdcV2LogCoinDetail\LogCoinDetailRepository::getCountTienthuaByRecieptid($receipt->id,$_listService->bdc_apartment_service_price_id):
                                null;
                                $paid_coin = $paid_coin ? $paid_coin :
                                App\Repositories\BdcV2LogCoinDetail\LogCoinDetailRepository::getDataByIdAndFromTypeV2(@$_listService->bdc_log_coin_id??0);
                                $service_v1 = null;
                                if(@$debit_metadata){
                                    $debit = json_decode(@$debit_metadata);
                                    if($debit){
                                        $debit = collect(@$debit_metadata)->where('id',$_listService->bdc_debit_detail_id);
                                        if($debit->count()>0){
                                            $debit = @$debit[0];
                                            $debit_id_v1 = str_contains(@$debit->discount_note, 'convert|') ? explode('convert|',@$debit->discount_note) : null;
                                            $get_debit = $debit_id_v1 ? App\Models\BdcDebitDetail\DebitDetail::find($debit_id_v1[1]): null;
                                            $service_v1 =$get_debit ? App\Models\Service\Service::get_detail_bdc_service_by_bdc_service_id($get_debit->bdc_service_id):null;
                                        }else{
                                            $debit = @$_listService->debit;
                                        }
                                    }else{
                                        $debit = @$_listService->debit;
                                    }
                                }else{
                                    $debit = @$_listService->debit;
                                }
                                @endphp
                                <tr>
                                    <td>{{ $key + 1 }}</td>
                                    <td>{{ @$service->name }}</td>
                                    <td>{{ @$vehicle->number }}</td>
                                    @if(@$apartmentServicePrice->bdc_price_type_id == 2)
                                    <td style="text-align: center">{{ date('d/m/Y', strtotime(@$debit->from_date)) . "-" . date('d/m/Y', strtotime(@$debit->to_date)) }}
                                    </td>
                                    @else
                                    <td style="text-align: center">{{ date('d/m/Y', strtotime(@$debit->from_date)) . "-" . date('d/m/Y', strtotime(@$debit->to_date . ' - 1 days')) }}
                                    </td>
                                    @endif
                                    <td style="text-align: right;">
                                        {{ number_format((@$debit->sumery + @$debit->discount), 0, '', '.') }}
                                    </td>
                                    <td style="text-align: right;">
                                        {{ number_format(@$debit->discount, 0, '', '.') }}
                                        <p><small>{{@$service_v1 ? @$service_v1->name : null}}</small></p>
                                    </td>
                                    <td style="text-align: right;">
                                        {{ number_format($_listService->paid + ($check_paid_coin == $_listService->bdc_apartment_service_price_id ? 0 : $paid_coin), 0, '', '.') }}
                                    </td>
                                    <td>
                                        <p><small>{{@$debit->note}}</small></p>
                                    </td>
                                </tr>
                                @php
                                if ($check_paid_coin != $_listService->bdc_apartment_service_price_id) {
                                $check_paid_coin = $_listService->bdc_apartment_service_price_id;
                                }
                                @endphp
                            @endforeach
                        @endif
                        @if (@$getTienThua)
                            @foreach ($getTienThua as $key => $item)
                            @php
                            $apartmentServicePrice = @$item->bdc_apartment_service_price_id != 0 ?
                            App\Models\BdcApartmentServicePrice\ApartmentServicePrice::get_detail_bdc_apartment_service_price_by_apartment_id($item->bdc_apartment_service_price_id)
                            : null;
                            $service = $apartmentServicePrice ?
                            App\Models\Service\Service::get_detail_bdc_service_by_bdc_service_id($apartmentServicePrice->bdc_service_id)
                            : null;
                            $vehicle = @$apartmentServicePrice->bdc_vehicle_id > 0 ?
                            App\Models\Vehicles\Vehicles::get_detail_vehicle_by_id($apartmentServicePrice->bdc_vehicle_id) :
                            null;
                            @endphp
                            <tr>
                                <td>{{ @$listService ? count(@$listService) + $key + 1 : $key + 1 }}</td>
                                <td>{{$item->bdc_apartment_service_price_id == 0 ? 'Tiền thừa chưa chỉ định' : @$service->name.'(tiền thừa)' }}
                                </td>
                                <td>{{$item->bdc_apartment_service_price_id == 0 ? '' : @$vehicle->number }}</td>
                                <td colspan="3"></td>
                                <td style="text-align: right;">{{ number_format($item->coin, 0, '', '.') }}</td>
                                <td></td>
                            </tr>
                            @endforeach
                        @endif
                        @if (@$nguon_hach_toan)
                            @foreach ($nguon_hach_toan as $key => $item)
                            @php
                            $apartmentServicePrice = @$_listService->bdc_apartment_service_price_id != 0 ?
                            App\Models\BdcApartmentServicePrice\ApartmentServicePrice::get_detail_bdc_apartment_service_price_by_apartment_id($item->bdc_apartment_service_price_id)
                            : null;
                            $service = $apartmentServicePrice ?
                            App\Models\Service\Service::get_detail_bdc_service_by_bdc_service_id($apartmentServicePrice->bdc_service_id)
                            : null;
                            $vehicle = @$apartmentServicePrice->bdc_vehicle_id > 0 ?
                            App\Models\Vehicles\Vehicles::get_detail_vehicle_by_id($apartmentServicePrice->bdc_vehicle_id) :
                            null;
                            @endphp
                            <tr>
                                <td>{{ @$listService ? count(@$listService) + $key + 1 : $key + 1 }}</td>
                                <td>{{ @$service->name ?? 'Tiền thừa chưa chỉ định' }}</td>
                                <td>{{ @$vehicle->number }}</td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td style="text-align: right;">{{ number_format(@$item->coin, 0, '', '.') }}</td>
                                <td></td>
                            </tr>
                            @endforeach
                        @endif

                        @if (@$nguon_hach_toan_v1)
                            @foreach ($nguon_hach_toan_v1 as $key => $item)
                            <tr>
                                <td>{{ @$listService ? count(@$listService) + $key + 1 : $key + 1 }}</td>
                                <td>Tiền thừa</td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td style="text-align: right;">{{ number_format(0-@$item->coin, 0, '', '.') }}</td>
                                <td></td>
                            </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
        <div class="footer">
            <table style="width: 100%;text-align: center;font-size: 12px">
                <tbody>
                    <tr>
                        <td><b>Giám đốc BQLTN</b></td>
                        <td><b>Kế toán BQLTN</b></td>
                        @if (@$receipt->type == 'phieu_chi' || @$receipt->type == 'phieu_chi_khac' || @$receipt->type == 'phieu_hoan_ky_quy')
                            <td><b>Người nhận tiền</b></td>
                        @else
                            <td><b>Người nộp tiền</b></td>
                        @endif
                        <td><b>Người lập phiếu</b></td>
                        <td><b>Thủ quỹ</b></td>
                    </tr>
                    <tr style="font-style: italic">
                        <td>(Ký, ghi rõ họ tên)</td>
                        <td>(Ký, ghi rõ họ tên)</td>
                        <td>(Ký, ghi rõ họ tên)</td>
                        <td>(Ký, ghi rõ họ tên)</td>
                        <td>(Ký, ghi rõ họ tên)</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</body>
<style type="text/css">
    @page {
        margin: 0px;
    }

    body {
        margin: 0px;
    }


    * {
        font-family: DejaVu Sans !important;
    }

    .padding-tb {
        padding-top: 10px;
        padding-bottom: 10px;
        padding-left: 3px;
    }

    td.text-header {
        font-weight: bold;
        margin-bottom: 0px;
        width: 40%;
    }

    p.text-invoice {
        text-transform: uppercase;
        font-weight: bold;
        font-size: 22px;
    }

    p.date-invoice {
        font-style: italic;
    }

    .list_content_text {
        padding-left: 10px !important;
        width: 100%;
    }

    a {
        color: #fff;
        text-decoration: none;
    }

    .list_content_text tbody td {
        padding-left: 25px;
    }

    .invoice table {
        margin: 15px;
    }

    .invoice h3 {
        margin-left: 15px;
    }

    .information table {
        padding-left: 60px;
        padding-right: 60px;
        padding-top: 20px;
    }

    .footer {
        display: inline-block;
        width: 100%;
        margin: 20px 0;
    }

    .list_service table {
        width: 100%
    }

    .list_service td {
        padding: 5px;
    }
</style>

</html>