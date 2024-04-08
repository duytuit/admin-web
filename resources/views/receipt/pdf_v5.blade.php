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
                    <td align="left" class="text-header">
                        <p class="text-building">BQL TÒA NHÀ</p>
                        <p>{{ @$building->name }}</p>
                    </td>
                    <td align="right" class="text-header">
                        <p>PT số : {{ $receipt->receipt_code }}</p>
                    </td>
                </tr>
            </table>
        </div>
        <div class="invoice">
            <div style=" text-align: center;">
                @if(@$receipt->type == 'phieu_thu')
                    <p class="text-invoice">Phiếu thu</p>
                @elseif(@$receipt->type == 'phieu_thu_truoc')
                    <p class="text-invoice">Phiếu thu khác</p>
                @elseif(@$receipt->type == 'phieu_chi')
                    <p class="text-invoice">Phiếu chi</p>
                @elseif(@$receipt->type == 'phieu_chi_khac')
                    <p class="text-invoice">Phiếu chi khác</p>
                @elseif(@$receipt->type == 'phieu_bao_co')
                    <p class="text-invoice">Phiếu báo có</p>
                @elseif(@$receipt->type == 'phieu_dieu_chinh')
                    <p class="text-invoice">Phiếu điều chỉnh</p>     
                @else
                    <p class="text-invoice">Phiếu kế toán</p>
                @endif
                    <p class="date-invoice">Ngày {{ date('d', strtotime(@$receipt->create_date))}} tháng {{ date('m', strtotime(@$receipt->create_date)) }} năm {{ date('Y', strtotime(@$receipt->create_date)) }}</p>
        
            </div>
            <div class="content_text">
                <div class="list_content_text">
                    <table width="70%">
                        <thead>
                        <tr>
                            <td></td>
                            <td></td>
                        </tr>
                        </thead>
                        <tbody>
                        @if (@$receipt->type == 'phieu_bao_co')
                            <tr>
                                <td align="left">Người chuyển khoản:</td>
                                <td width="65%"><b>{{@$receipt->corresponsiveName ?? @$receipt->customer_name }}</b></td>
                            </tr>
                            <tr>
                                <td align="left">Mã giao dịch:</td>
                                <td width="65%"><b>{{ @$receipt->payment_transaction }}</b></td>
                            </tr>
                        @else
                            <tr>
                                <td align="left">Người nộp tiền:</td>
                                <td width="65%"><b>{{ @$receipt->customer_name }}</b></td>
                            </tr>
                        @endif
                      
                        <tr>
                            <td align="left">Căn hộ:</td>
                            <td width="65%">{{ @$apartment->name }}</td>
                        </tr>
                        <tr>
                            <td align="left">Hình thức:</td>
                            <td width="65%">{{ @$receipt->type_payment_name }}</td>
                        </tr>
                        @if(@$receipt->type == 'phieu_thu' || @$receipt->type == 'phieu_thu_truoc' || @$receipt->type == 'phieu_chi' || @$receipt->type == 'phieu_chi_khac' || @$receipt->type == 'phieu_bao_co' || @$receipt->type == 'phieu_dieu_chinh')
                        <tr>
                            <td align="left">Số tiền:</td>
                            <td width="65%"><b>{{ number_format(@$sum_total_paid, 0, '', '.') }}</b> <b>VND</b></td>
                        </tr>
                        <tr>
                            <td align="left">Bằng chữ:</td>
                            <td width="65%">{{ucfirst(@$converMoney)}} đồng</td>
                        </tr>
                        @endif
                        <tr>
                            <td align="left">Nội dung:</td>
                            <td width="65%">{{ @$receipt->description }}</td>
                        </tr>
                        </tbody>
                    </table>
                    <div class="pull-right" style="padding-right: 15px;">
                        <div class="text_address">
                        </div>
                    </div>
                </div>
            </div>
            <div class="footer">
                <table style="width: 100%;text-align: center;margin:20px;font-size: 11px">
                    <tbody>
                    <tr>
                        <td><b>Giám đốc</b></td>
                        <td><b>Kế toán</b></td>
                        <td><b>Thủ quỹ</b></td>
                        <td><b>Người lập</b></td>
                        <td><b>Người nộp</b></td>
                    </tr>
                    <tr>
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
        <br/>
        <br/>
        <hr>
        <div class="invoice">
            <div class="content_text">
                <div class="list_service">
                        <table>
                            <thead>
                            <tr>
                                <td>STT</td>
                                <td>Dịch vụ</td>
                                <td>Sản phẩm</td>
                                <td width="110">Thời gian</td>
                                <td>Phát sinh</td>
                                <td width="110">Giảm trừ</td>
                                <td width="110">Ghi chú</td>
                                <td>Thanh toán</td>
                            </tr>
                            </thead>
                            <tbody>
                                    @if($listService)
                                        @php
                                            $check_paid_coin = 0;   
                                            $debit_metadata = @$receipt->metadata;
                                        @endphp
                                        @foreach ($listService as $key => $_listService)
                                            @php
                                                    $apartmentServicePrice = @$_listService->bdc_apartment_service_price_id != 0 ? App\Models\BdcApartmentServicePrice\ApartmentServicePrice::get_detail_bdc_apartment_service_price_by_apartment_id($_listService->bdc_apartment_service_price_id) : null;
                                                    $service = $apartmentServicePrice ? App\Models\Service\Service::get_detail_bdc_service_by_bdc_service_id($apartmentServicePrice->bdc_service_id) : null;
                                                    $vehicle = @$apartmentServicePrice->bdc_vehicle_id > 0 ? App\Models\Vehicles\Vehicles::get_detail_vehicle_by_id($apartmentServicePrice->bdc_vehicle_id) : null;
                                            @endphp
                                            @php
                                                    $paid_coin =@$_listService->bdc_apartment_service_price_id != 0 ? App\Repositories\BdcV2LogCoinDetail\LogCoinDetailRepository::getCountTienthuaByRecieptid($receipt->id,$_listService->bdc_apartment_service_price_id): null;
                                                    $paid_coin = $paid_coin ? $paid_coin :  App\Repositories\BdcV2LogCoinDetail\LogCoinDetailRepository::getDataByIdAndFromTypeV2(@$_listService->bdc_log_coin_id??0);
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
                                                        <td>{{ date('d/m/Y', strtotime(@$debit->from_date)) . "-" . date('d/m/Y', strtotime(@$debit->to_date)) }}</td>
                                                    @else
                                                        <td>{{ date('d/m/Y', strtotime(@$debit->from_date)) . "-" . date('d/m/Y', strtotime(@$debit->to_date . ' - 1 days')) }}</td>    
                                                    @endif
                                                    <td style="text-align: right;">{{ number_format(@$debit->sumery + @$debit->discount) }}</td>
                                                    <td style="text-align: right;">
                                                        {{ number_format(@$debit->discount) }}
                                                        <p><small>{{@$service_v1 ? @$service_v1->name : null}}</small></p>
                                                    </td>
                                                    <td>
                                                        <p><small>{{@$debit->note}}</small></p>
                                                    </td>
                                                    <!-- Duong change  -->
                                                    <td style="text-align: right;">{{ number_format($_listService->paid + ( $paid_coin), 0, '', '.') }}</td>
                                                    <!-- <td style="text-align: right;">{{ number_format($_listService->paid + ($check_paid_coin != $_listService->bdc_apartment_service_price_id ? 0 : $paid_coin), 0, '', '.') }}</td> -->
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
                                                $apartmentServicePrice = @$item->bdc_apartment_service_price_id != 0 ? App\Models\BdcApartmentServicePrice\ApartmentServicePrice::get_detail_bdc_apartment_service_price_by_apartment_id($item->bdc_apartment_service_price_id) : null;
                                                $service = $apartmentServicePrice ? App\Models\Service\Service::get_detail_bdc_service_by_bdc_service_id($apartmentServicePrice->bdc_service_id) : null;
                                                $vehicle = @$apartmentServicePrice->bdc_vehicle_id > 0 ? App\Models\Vehicles\Vehicles::get_detail_vehicle_by_id($apartmentServicePrice->bdc_vehicle_id) : null;
                                            @endphp
                                            <tr>
                                                <td>{{ @$listService ? count(@$listService) + $key + 1 : $key + 1 }}</td>
                                                <td>{{$item->bdc_apartment_service_price_id == 0 ? 'Tiền thừa chưa chỉ định' : @$service->name.'(tiền thừa)' }}</td>
                                                <td>{{$item->bdc_apartment_service_price_id == 0 ? '' : @$vehicle->number }}</td>
                                                <td colspan="4"></td>
                                                <td style="text-align: right;">{{ number_format($item->coin, 0, '', '.') }}</td>
                                            </tr>   
                                        @endforeach
                                    @endif
                                    @if (@$nguon_hach_toan)
                                        @foreach ($nguon_hach_toan as $key => $item)
                                                 @php
                                                    $apartmentServicePrice = @$_listService->bdc_apartment_service_price_id != 0 ? App\Models\BdcApartmentServicePrice\ApartmentServicePrice::get_detail_bdc_apartment_service_price_by_apartment_id($item->bdc_apartment_service_price_id) : null;
                                                    $service = $apartmentServicePrice ? App\Models\Service\Service::get_detail_bdc_service_by_bdc_service_id($apartmentServicePrice->bdc_service_id) : null;
                                                    $vehicle = @$apartmentServicePrice->bdc_vehicle_id > 0 ? App\Models\Vehicles\Vehicles::get_detail_vehicle_by_id($apartmentServicePrice->bdc_vehicle_id) : null;
                                                 @endphp
                                            <tr>
                                                <td>{{ @$listService ? count(@$listService) + $key + 1 : $key + 1 }}</td>
                                                <td>{{ @$service->name ?? 'Tiền thừa chưa chỉ định' }}</td>
                                                <td>{{ @$vehicle->number }}</td>
                                                <td></td>
                                                <td></td>
                                                <td></td>
                                                <td></td>
                                                <td style="text-align: right;">{{ number_format(@$item->coin, 0, '', '.') }}</td>
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
                                            <td></td>
                                            <td style="text-align: right;">{{ number_format(0-@$item->coin, 0, '', '.') }}</td>
                                        </tr>   
                                        @endforeach
                                    @endif
                            </tbody>
                        </table>
                </div>
            </div>
        </div>
    </div>
</body>
<style type="text/css">
    * {
        font-size: 15px;
    }

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
        font-size: 18px;
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

    table {
        font-size: 15px;
    }

    tfoot tr td {
        font-weight: bold;
        font-size: 15px;
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

    .list_service table{
        width: 100%
    }

    .list_service table td{
        border-collapse: collapse;
        border: 1px solid black;
    }

    .list_service thead td{
        font-weight: bold;
    }

    .list_service td{
        padding: 10px;
    }
</style>

</html>