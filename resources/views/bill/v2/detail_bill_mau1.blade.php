<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>{{ @$bill->bill_code }}</title>
    <link rel="stylesheet" href="{{ url('adminLTE/css/bootstrap.min.css') }}" />
    <link rel="stylesheet" href="{{ url('adminLTE/css/bootstrap-theme.min.css') }}" />
</head>
<body>
<div class="container" style="padding-top: 35px;">
    <table width="100%" border="0">
        <tr>
            <td class="text-header">
                <p>BQL tòa nhà {{ @$building->name }}</p>
            </td>
            <td class="text-header" style="padding-left: 100px;">
                <p>Số bảng kê: {{ @$bill->bill_code }}</p>
            </td>
        </tr>
        <tr>
            <td class="text-header">
                <p>{{ @$building->address }}</p>
            </td>
            <td class="text-header" style="padding-left: 100px;">
                <p>Ngày: {{ date('d') }}/{{ date('m') }}/{{ date('Y') }}</p>
            </td>
        </tr>
        <tr>
            <td class="text-header">
                SĐT: {{ @$building->phone }}
            </td>
            <td align="right" class="text-header"></td>
        </tr>
    </table>
</div>
<div class="container">
    <div style=" text-align: center; padding-top: 20px">
        @php
            $year = substr(@$bill->cycle_name, 0, -2);
            $month = substr(@$bill->cycle_name, 4);
        @endphp
        <p class="text-invoice">BẢNG KÊ DỊCH VỤ THÁNG {{ $month }}/{{ $year }}</p>
    </div>
    <div class="content_text">
        <table width="100%">
            <thead>
            <tr>
                <td></td>
                <td></td>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td colspan="3">Họ và tên: {{ @$bill->customer_name }}</td>
            </tr>
            <tr>
                <td>Căn hộ: {{ @$apartment->name }}</td>
                <td>Tòa nhà : {{ @$building->name }}</td>
                <td>Khu đô thị: {{ @$building->name }}</td>
            </tr>
            </tbody>
        </table>
        <div class="pull-left">
            <div class="text_address">
                <span>
                    Ban quản lý tòa nhà {{ @$building->name }} gửi hóa đơn dịch vụ Tháng {{ $month }}/{{ $year }}
                </span>
            </div>
        </div>
    </div>
    <div class="footer">
        <table style="width: 100%; padding: 10px" border="1">
            <tbody>
            <tr>
                <td class="padding-tb" style="text-align: center;"><b>Tên dịch vụ</b></td>
                <td class="padding-tb" style="text-align: center;"><b>Đơn giá (*)</b></td>
                <td class="padding-tb" style="text-align: center;"><b>Đơn giá / ngày</b></td>
                <td class="padding-tb" style="text-align: center;"><b>Thời gian</b></td>
                <td class="padding-tb" style="text-align: center;"><b>Số lượng</b></td>
                <td class="padding-tb" style="text-align: center;"><b>Thành tiền</b></td>
            </tr>
            @php
                $totalService = 0;
                $totalVehicle = 0;
                $totalPrice = 0;
            @endphp
            @if(count($debit_detail['service']) > 0)
                @foreach($debit_detail['service'] as $key => $service)
                    @php
                        $totalDv = $service->is_free == 0 ? $service->sumery + $service->previous_owed : 0;
                        $totalService += $totalDv;
                    @endphp
                @endforeach
                <tr>
                    <td colspan="5" class="padding-tb"><b>Tổng phí dịch vụ</b></td>
                    <td class="padding-tb" style="text-align: center;"><strong>{{ number_format($totalService) }}</strong></td>
                </tr>
                @foreach($debit_detail['service'] as $key => $service)
                    @php
                        // $totalDv = $service->is_free == 0 ? $service->sumery + $service->previous_owed : 0;
                    @endphp
                    <tr>
                        <td class="padding-tb">- {{ $service->apartmentServicePrice->service->name }}</td>
                        <td class="padding-tb" style="text-align: center;">
                            @if(@$service->apartmentServicePrice->service->type == 2 && $service->apartmentServicePrice->floor_price > 0)
                                ({{ number_format($service->apartmentServicePrice->floor_price) }} * {{ $apartment->area }}m2)
                            @endif
                        </td>
                        <td class="padding-tb" style="text-align: center;">{{ number_format($service->price) }}</td>
                        @if(@$service->apartmentServicePrice->bdc_price_type_id == 2 || @$service->apartmentServicePrice->bdc_price_type_id == 3)
                            <td class="padding-tb" style="text-align: center;">{{ date('d/m/Y', strtotime(@$service->from_date)).' - '.date('d/m/Y', strtotime($service->to_date)) }}</td>
                        @else
                            <td class="padding-tb" style="text-align: center;">{{ date('d/m/Y', strtotime(@$service->from_date)).' - '.date('d/m/Y', strtotime($service->to_date  . ' - 1 days')) }}</td>
                        @endif
                        <td class="padding-tb" style="text-align: center;">{{ $service->quantity }}</td>
                        <td class="padding-tb"  style="text-align: center;">
                            {{ number_format($service->sumery) }}
                            @if($service->is_free == 1)
                                <span class="badge badge-danger">Miễn phí</span>
                            @endif
                        </td>
                    </tr>
                    @php
                        //$totalService += $totalDv;
                    @endphp
                @endforeach
            @endif
            @if(count($debit_detail['vehicle']) > 0)
                @foreach($debit_detail['vehicle'] as $key => $service)
                    @php
                        $totalVi = $service->is_free == 0 ? $service->sumery + $service->previous_owed : 0;
                        $totalVehicle += $totalVi;
                    @endphp
                @endforeach
                <tr>
                    <td colspan="5" class="padding-tb"><b>Phí gửi xe</b></td>
                    <td class="padding-tb" style="text-align: center;"><strong>{{ number_format($totalVehicle) }}</strong></td>
                </tr>
                @foreach($debit_detail['vehicle'] as $key => $service)
                    @php
                        // $totalVi = $service->is_free == 0 ? $service->sumery + $service->previous_owed : 0;
                    @endphp
                    <tr>
                        <td class="padding-tb">{{ $service->title }}</td>
                        <td class="padding-tb" style="text-align: center;">
                            {{@$service->price_current ? number_format(@$service->price_current) : number_format($service->apartmentServicePrice->price) }}<br/>
                        </td>
                        <td class="padding-tb" style="text-align: center;">{{ number_format($service->price) }}</td>
                        @if(@$service->apartmentServicePrice->bdc_price_type_id == 2 || @$service->apartmentServicePrice->bdc_price_type_id == 3)
                            <td class="padding-tb" style="text-align: center;">{{ date('d/m/Y', strtotime(@$service->from_date)).' - '.date('d/m/Y', strtotime($service->to_date)) }}</td>
                        @else
                            <td class="padding-tb" style="text-align: center;">{{ date('d/m/Y', strtotime(@$service->from_date)).' - '.date('d/m/Y', strtotime($service->to_date  . ' - 1 days')) }}</td>
                        @endif
                        <td class="padding-tb" style="text-align: center;">{{ $service->quantity }}</td>
                        <td class="padding-tb" style="text-align: center;" >
                            {{ number_format($service->sumery) }}
                            @if($service->is_free == 1)
                                <span class="badge badge-danger">Miễn phí</span>
                            @endif
                        </td>
                    </tr>
                    @php
                        // $totalVehicle += $totalVi;
                    @endphp
                @endforeach
            @endif
            @if(count($debit_detail['other']) > 0)
                @foreach($debit_detail['other'] as $diennuoc)
                    <?php
                        $detail = json_decode(@$diennuoc->detail);
                        $totalNumber = 0;
                    ?>
                    @foreach (@$detail->data as $key => $item)
                        <?php
                            $totalNumber += @$item->to - @$item->from + 1;
                            $totalPrice += @$item->total_price;
                        ?>
                    @endforeach
                    <tr>
                        <td colspan="5" class="padding-tb" >
                            <b>
                                {{ @$diennuoc->apartmentServicePrice->service->name }} (CS Đầu : {{ @$detail->so_dau }} - CS Cuối : {{ @$detail->so_cuoi }} - Tiêu thụ : {{ @$detail->tieu_thu }})
                            </b>
                        </td>
                        <td class="padding-tb" style="text-align: center;"><strong>{{ number_format(@$diennuoc->sumery) }}</strong></td>
                    </tr>
                    @foreach (@$detail->data as $key => $item)
                        <tr>
                            <td class="padding-tb" style="text-align: center;">Từ {{ @$item->from }} - {{ $item->to }}</td>
                            <td class="padding-tb" style="text-align: center;">{{ number_format(@$item->price) }}</td>
                            <td class="padding-tb" style="text-align: center;">-</td>
                            @if(@$diennuoc->apartmentServicePrice->bdc_price_type_id == 2 || @$diennuoc->apartmentServicePrice->bdc_price_type_id == 3)
                                <td class="padding-tb" style="text-align: center;">{{ date('d/m/Y', strtotime(@$diennuoc->from_date)).' - '.date('d/m/Y', strtotime(@$diennuoc->to_date)) }}</td>
                            @else
                                <td class="padding-tb" style="text-align: center;">{{ date('d/m/Y', strtotime(@$diennuoc->from_date)).' - '.date('d/m/Y', strtotime(@$diennuoc->to_date  . ' - 1 days')) }}</td>
                            @endif
                            <td class="padding-tb" style="text-align: center;">{{ @$item->to - @$item->from + 1 }}</td>
                            <td class="padding-tb" style="text-align: center;">{{ number_format(@$item->total_price) }}</td>
                        </tr>
                    @endforeach
                @endforeach
            @endif
             @if(count($debit_detail['first_price']) > 0)
                @foreach($debit_detail['first_price'] as $key => $service)
                    @php
                        $totalDv = $service->is_free == 0 ? $service->sumery + $service->previous_owed : 0;
                        $totalService += $totalDv;
                    @endphp
                @endforeach
                <tr>
                    <td colspan="5" class="padding-tb"><b>Tổng phí dịch vụ</b></td>
                    <td class="padding-tb" style="text-align: center;"><strong>{{ number_format($totalService) }}</strong></td>
                </tr>
                @foreach($debit_detail['first_price'] as $key => $service)
                    @php
                        // $totalDv = $service->is_free == 0 ? $service->sumery + $service->previous_owed : 0;
                    @endphp
                    <tr>
                        <td class="padding-tb">- {{ $service->apartmentServicePrice->service->name }}</td>
                        <td class="padding-tb" style="text-align: center;">
                            @if(@$service->apartmentServicePrice->service->type == 2 && $service->apartmentServicePrice->floor_price > 0 && @$service->apartmentServicePrice->bdc_price_type_id != 3 )
                                ({{ number_format($service->apartmentServicePrice->floor_price) }} * {{ $apartment->area }}m2)
                            @endif
                        </td>
                        <td class="padding-tb" style="text-align: center;">{{ number_format($service->price) }}</td>
                        @if(@$service->apartmentServicePrice->bdc_price_type_id == 2 || @$service->apartmentServicePrice->bdc_price_type_id == 3)
                            <td class="padding-tb" style="text-align: center;">{{ date('d/m/Y', strtotime(@$service->from_date)).' - '.date('d/m/Y', strtotime($service->to_date)) }}</td>
                        @else
                            <td class="padding-tb" style="text-align: center;">{{ date('d/m/Y', strtotime(@$service->from_date)).' - '.date('d/m/Y', strtotime($service->to_date  . ' - 1 days')) }}</td>
                        @endif
                        <td class="padding-tb" style="text-align: center;">{{ $service->quantity }}</td>
                        <td class="padding-tb"  style="text-align: center;">
                            {{ number_format($service->sumery) }}
                            @if($service->is_free == 1)
                                <span class="badge badge-danger">Miễn phí</span>
                            @endif
                        </td>
                    </tr>
                    @php
                        //$totalService += $totalDv;
                    @endphp
                @endforeach
            @endif
            <tr>
                <td colspan="5" class="padding-tb"><b>(A)TỔNG CỘNG</b></td>
                @php
                    $total = $totalService + $totalVehicle + $totalPrice;
                @endphp
                <td class="padding-tb" style="text-align: center;">{{ number_format($total) }}</td>
            </tr>
            <tr>
                <td colspan="5" class="padding-tb"><b>(B)ĐÃ THANH TOÁN</b></td>
                <td class="padding-tb" style="text-align: center;">
                    @php
                        $paid = isset($total_paid[0]) ? $total_paid[0]->total_paid : 0;    
                    @endphp
                    {{ number_format($paid) }}
                </td>
            </tr>
            <tr>
                <td colspan="5" class="padding-tb"><b>(C)NỢ CŨ</b></td>
                @php
                    $no = @$totalPaymentDebit[0]->total_payment > 0 ? $totalPaymentDebit[0]->total_payment : 0;
                @endphp
                <td class="padding-tb"  style="text-align: center;">{{ number_format($no) }}</td>
            </tr>
            <tr>
                <td colspan="5" class="padding-tb"><b>(D)DƯ CŨ</b></td>
                @php
                    $du = App\Models\BdcAccountingVouchers\AccountingVouches::total_so_du($building->id,$apartment->id);
                @endphp
                <td class="padding-tb"  style="text-align: center;">{{ number_format(abs($du)) }}</td>
            </tr>
            <tr>
                <td colspan="5" class="padding-tb"><b>(E)CẦN THANH TOÁN (A-B+C-D)</b></td>
                <td class="padding-tb" style="text-align: center;font-weight: bold;">{{ number_format($total - $paid + $no - abs($du)) }}</td>
            </tr>
            </tbody>
        </table>
        <p><b>(*)</b> : Đơn giá đã bao gồm thuế và phí theo quy định</p>
    </div>
</div>
<br/>
<br/>
<hr>
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

    .text-building {
        padding-left: 5px;
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