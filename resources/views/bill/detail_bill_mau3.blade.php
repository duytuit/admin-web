<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <link rel="stylesheet" href="{{ url('adminLTE/css/bootstrap.min.css') }}" />
    <link rel="stylesheet" href="{{ url('adminLTE/css/bootstrap-theme.min.css') }}" />
</head>
<body>
        @foreach ($data_bill as $data)
        <div class="container pagebreak" style="margin-top: 35px;">
            <table width="100%" border="0">
                <tr>
                    <td class="text-header">
                        <p>BQL TÒA NHÀ | THE BUILDING MANAGEMENT BOARD: {{ strtoupper(@$data['building']->name)  }}</p>
                    </td>
                    <td class="pull-right" style="font-weight: bold;">
                        <p >Số bảng kê | No.: {{ @$data['bill']->bill_code }}</p>
                    </td>
                </tr>
                <tr>
                    <td class="text-header ">
                        <p>Địa chỉ | Address: {{ @$data['building']->address }}</p>
                    </td>
                    <td class="pull-right" style="font-weight: bold;">
                        <p>Ngày | Date: {{ date('d') }}/{{ date('m') }}/{{ date('Y') }}</p>
                    </td>
                </tr>
                <tr>
                    <td class="text-header">
                        SĐT | Phone: {{ @$data['building']->phone }}
                    </td>
                    <td align="right" class="text-header"></td>
                </tr>
            </table>
            <div style=" text-align: center; padding-top: 20px">
                @php
                    $year = substr(@$data['bill']->cycle_name, 0, -2);
                    $month = substr(@$data['bill']->cycle_name, 4);
                @endphp
                <p class="text-invoice">THÔNG BÁO PHÍ / PAYMENT NOTIFICATION {{ $month }}/{{ $year }}</p>
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
                        <td colspan="3">Họ và tên | Name: {{ @$data['bill']->customer_name }}</td>
                    </tr>
                    <tr>
                        <td>Căn hộ | Apartment: {{ @$data['apartment']->name }}</td>
                        <td>Tòa nhà | Place: {{ @$data['buildingPlace']->name }}</td>
                        <td>Khu đô thị | Building: {{ @$data['building']->name }}</td>
                    </tr>
                    </tbody>
                </table>
                <div class="pull-left">
                    <div class="text_address">
                        <span>
                            Ban quản lý tòa nhà {{ @$data['building']->name }} gửi hóa đơn dịch vụ Tháng {{ $month }}/{{ $year }} / <strong>We are pleased to information about the fee that is collected in</strong>
                        </span>
                    </div>
                </div>
            </div>
            <div class="footer">
                <table style="width: 100%; padding: 10px" border="1">
                    <tbody>
                    <tr>
                        <td class="padding-tb" style="text-align: center;"><b>Tên dịch vụ / Service</b></td>
                        <td class="padding-tb" style="text-align: center;"><b>Đơn giá (*) / Unit Price </b></td>
                        <td class="padding-tb" style="text-align: center;"><b>Thời gian / Period</b></td>
                        <td class="padding-tb" style="text-align: center;"><b>Tiêu thụ / Consumption</b></td>
                        <td class="padding-tb" style="text-align: center;"><b>Tổng / Total</b></td>
                        <td class="padding-tb" style="text-align: center;"><b>Giảm trừ / Discount</b></td>
                        <td class="padding-tb" style="text-align: center;"><b>Ghi chú / Note</b></td>
                        <td class="padding-tb" style="text-align: center;">
                            <b>Thành tiền / </b>
                            <div><b>Amount(VNĐ)</b></div> 
                        </td>
                    </tr>
                    @php
                        $totalService = 0;
                        $totalVehicle = 0;
                        $totalDiscountService = 0;
                        $totalDiscountVehicle = 0;
                        $totalPrice = 0;
                        $totalDienNuocPrice = 0;
                        $paid_total = 0;
                    @endphp
                    @if(count(@$data['debit_detail']['service']) > 0)
                        <?php
                            $totalDv =0;
                        ?>
                        @foreach(@$data['debit_detail']['service'] as $key => $service)
                            @php
                                $totalDiscountService += $service->discount;
                                $totalService += $service->sumery;
                                $paid_total += $service->paid;
                                $totalDv += $service->sumery;
                            @endphp
                        @endforeach
                        <tr>
                            <td colspan="4" class="padding-tb"><b>Tổng phí dịch vụ /Management fee</b></td>
                            <td class="padding-tb" style="text-align: right;padding-right: 5px;"><strong>{{ number_format($totalService + $totalDiscountService) }}</strong></td>
                            <td class="padding-tb" style="text-align: right;padding-right: 5px;"><strong>{{ number_format($totalDiscountService) }}</strong></td>
                            <td></td>
                            <td class="padding-tb" style="text-align: right;padding-right: 5px;"><strong>{{ number_format($totalService) }}</strong></td>
                        </tr>
                        @foreach(@$data['debit_detail']['service'] as $key => $service)
                            <tr>
                                <td class="padding-tb">- {{ @$service->apartmentServicePrice->service->name }}</td>
                                <td class="padding-tb" style="text-align: center;">
                                    {{ number_format($service->sumery) }} <br/>
                                    @if(@$service->apartmentServicePrice->service->type == 2 && $service->apartmentServicePrice->floor_price > 0)
                                        ({{ number_format($service->apartmentServicePrice->floor_price) }} * {{ @$data['apartment']->area }}m2)
                                    @endif
                                </td>
                                @if(@$service->apartmentServicePrice->bdc_price_type_id == 2 || @$service->apartmentServicePrice->bdc_price_type_id == 3)
                                    <td class="padding-tb" style="text-align: center;">{{ date('d/m/Y', strtotime(@$service->from_date)).' - '.date('d/m/Y', strtotime($service->to_date)) }}</td>
                                @else
                                    <td class="padding-tb" style="text-align: center;">{{ date('d/m/Y', strtotime(@$service->from_date)).' - '.date('d/m/Y', strtotime($service->to_date  . ' - 1 days')) }}</td>
                                @endif
                                <td class="padding-tb" style="text-align: center;">-</td>
                                <td class="padding-tb"  style="text-align: right;padding-right: 5px;">{{ number_format($service->sumery + $service->discount) }}</td>
                                <td class="padding-tb" style="text-align: right;padding-right: 5px;">{{ number_format($service->discount) }}</td>
                                <td></td>
                                <td class="padding-tb" style="text-align: right;padding-right: 5px;">{{ number_format($service->sumery) }}</td>
                            </tr>
                        @endforeach
                    @endif
                    @if(count(@$data['debit_detail']['vehicle']) > 0)
                        <?php
                            $totalDv =0;
                        ?>
                        @foreach(@$data['debit_detail']['vehicle'] as $key => $service)
                            @php
                                    $totalDiscountVehicle += $service->discount;
                                    $totalVehicle += $service->sumery;
                                    $paid_total += $service->paid;
                                    $totalDv += $service->sumery;
                            @endphp
                        @endforeach
                        <tr>
                            <td colspan="4" class="padding-tb"><b>Phí gửi xe / Parking fee</b></td>
                            <td class="padding-tb" style="text-align: right;padding-right: 5px;"><strong>{{ number_format($totalVehicle + $totalDiscountVehicle) }}</strong></td>
                            <td class="padding-tb" style="text-align: right;padding-right: 5px;"><strong>{{ number_format($totalDiscountVehicle) }}</strong></td>
                            <td></td>
                            <td class="padding-tb" style="text-align: right;padding-right: 5px;"><strong>{{ number_format($totalVehicle) }}</strong></td>
                        </tr>
                        @foreach(@$data['debit_detail']['vehicle'] as $key => $service)
                            <tr>
                                <td class="padding-tb">{{ @$service->apartmentServicePrice->vehicle->number }}</td>
                                <td class="padding-tb" style="text-align: center;">
                                    {{ number_format(@$service->apartmentServicePrice->price) }}<br/>
                                </td>
                                @if(@$service->apartmentServicePrice->bdc_price_type_id == 2 || @$service->apartmentServicePrice->bdc_price_type_id == 3)
                                    <td class="padding-tb" style="text-align: right;padding-right: 5px;">{{ date('d/m/Y', strtotime(@$service->from_date)).' - '.date('d/m/Y', strtotime($service->to_date)) }}</td>
                                @else
                                    <td class="padding-tb" style="text-align: center;">{{ date('d/m/Y', strtotime(@$service->from_date)).' - '.date('d/m/Y', strtotime($service->to_date  . ' - 1 days')) }}</td>
                                @endif
                                <td class="padding-tb" style="text-align: center;">-</td>
                                <td class="padding-tb"  style="text-align: right;padding-right: 5px;">{{ number_format($service->sumery + $service->discount) }}</td>
                                <td class="padding-tb" style="text-align: right;padding-right: 5px;">{{ number_format($service->discount)}}</td>
                                <td></td>
                                <td class="padding-tb" style="text-align: right;padding-right: 5px;">{{ number_format($service->sumery)}}</td>
                            </tr>
                        @endforeach
                    @endif
                    @if(count(@$data['debit_detail']['other']) > 0)
                        @foreach(@$data['debit_detail']['other'] as $diennuoc)
                            <?php
                                $detail = json_decode(@$diennuoc->detail);
                                $totalNumber = 0;
                                $totalDienNuocPrice += $diennuoc->sumery;
                                $paid_total += $diennuoc->paid;
                                $tong_tieu_thu = 0;
                                $service = @$diennuoc->apartmentServicePrice->service;
                            ?>
                            @if (@$detail->data_price)
                                <tr>
                                    <td colspan="4" class="padding-tb" >
                                        <b>
                                            {{ @$service->name }} | {{@$service->type == 5 ? 'Electric charge':'Water charge'}}
                                        </b>
                                        @foreach (@$detail->data_detail as $key => $item)
                                            @php
                                                $electric_meter = App\Models\BdcElectricMeter\ElectricMeter::find($item->id);
                                                $tieu_thu = @$electric_meter->after_number - @$electric_meter->before_number;
                                                $tong_tieu_thu += $tieu_thu;
                                            @endphp
                                            <div>Đồng hồ | Meter: {{$key+1}}</div> 
                                            <div class="col-xs-4">
                                                Chỉ số đầu | Old figure:
                                                <div>{{ @$electric_meter->before_number }}</div>
                                            </div>
                                            <div class="col-xs-4">
                                                Chỉ số cuối | New figure:
                                                <div>{{ @$electric_meter->after_number }}</div>
                                            </div>
                                            <div class="col-xs-4">
                                                Tiêu thụ | Used:
                                                <div> {{ @$tieu_thu }}</div>
                                            </div>
                                        @endforeach
                                    </td>
                                    <td class="padding-tb" style="text-align: right;padding-right: 5px;"><strong>{{ number_format(@$diennuoc->sumery + @$diennuoc->discount) }}</strong></td>
                                    <td class="padding-tb" style="text-align: right;padding-right: 5px;"><strong>{{ number_format($diennuoc->discount)}}</strong></td>
                                    <td></td>
                                    <td class="padding-tb" style="text-align: right;padding-right: 5px;"><strong>{{ number_format($diennuoc->sumery) }}</strong></td>
                                </tr>
                                @foreach (@$detail->data_price as $key => $item)
                                    <?php
                                        $totalNumber += @$item->to - @$item->from + 1;
                                        $totalPrice += @$item->total_price;
                                    ?>
                                    <tr>
                                        <td class="padding-tb"> Từ {{ @$item->from }} - {{ $item->to }} | Figure step {{$key+1}}</td>
                                        <td class="padding-tb" style="text-align: center;">{{ number_format(@$item->price) }}</td>
                                        @if(@$diennuoc->apartmentServicePrice->bdc_price_type_id == 2 || @$diennuoc->apartmentServicePrice->bdc_price_type_id == 3)
                                            <td class="padding-tb" style="text-align: center;">{{ date('d/m/Y', strtotime(@$diennuoc->from_date)).' - '.date('d/m/Y', strtotime(@$diennuoc->to_date)) }}</td>
                                        @else
                                            <td class="padding-tb" style="text-align: center;">{{ date('d/m/Y', strtotime(@$diennuoc->from_date)).' - '.date('d/m/Y', strtotime(@$diennuoc->to_date  . ' - 1 days')) }}</td>
                                        @endif
                                        <td class="padding-tb" style="text-align: center;">{{ @$item->to - @$item->from + 1 }}</td>
                                        <td class="padding-tb" style="text-align: right;padding-right: 5px;">{{ number_format((@$item->to - @$item->from + 1) * @$item->price) }}</td>
                                        <td class="padding-tb" style="text-align: right;padding-right: 5px;">0</td>
                                        <td></td>
                                        <td class="padding-tb" style="text-align: right;padding-right: 5px;">{{ number_format((@$item->to - @$item->from + 1) * @$item->price) }}</td>
                                    </tr>
                                @endforeach
                            @endif
                            @if (@$detail->data)
                                <tr>
                                    <td colspan="4" class="padding-tb" >
                                        <b>
                                            {{ @$service->name }} (CS Đầu | Old figure: {{ @$detail->so_dau }} - CS Cuối | New figure: {{ @$detail->so_cuoi }} - Tiêu thụ | Used: {{ @$detail->tieu_thu }})
                                        </b>
                                    </td>
                                    <td class="padding-tb" style="text-align: right;padding-right: 5px;"><strong>{{ number_format(@$diennuoc->sumery + @$diennuoc->discount) }}</strong></td>
                                    <td class="padding-tb" style="text-align: right;padding-right: 5px;"><strong>{{ number_format($diennuoc->discount)}}</strong></td>
                                    <td></td>
                                    <td class="padding-tb" style="text-align: right;padding-right: 5px;"><strong>{{ number_format($diennuoc->sumery) }}</strong></td>
                                </tr>
                                @foreach (@$detail->data as $key => $item)
                                    <?php
                                        $totalNumber += @$item->to - @$item->from + 1;
                                        $totalPrice += @$item->total_price;
                                    ?>
                                    <tr>
                                        <td class="padding-tb"> Từ {{ @$item->from }} - {{ $item->to }} | Figure step {{$key+1}}</td>
                                        <td class="padding-tb" style="text-align: center;">{{ number_format(@$item->price) }}</td>
                                        @if(@$diennuoc->apartmentServicePrice->bdc_price_type_id == 2 || @$diennuoc->apartmentServicePrice->bdc_price_type_id == 3)
                                            <td class="padding-tb" style="text-align: center;">{{ date('d/m/Y', strtotime(@$diennuoc->from_date)).' - '.date('d/m/Y', strtotime(@$diennuoc->to_date)) }}</td>
                                        @else
                                            <td class="padding-tb" style="text-align: center;">{{ date('d/m/Y', strtotime(@$diennuoc->from_date)).' - '.date('d/m/Y', strtotime(@$diennuoc->to_date  . ' - 1 days')) }}</td>
                                        @endif
                                        <td class="padding-tb" style="text-align: center;">{{ @$item->to - @$item->from + 1 }}</td>
                                        <td class="padding-tb" style="text-align: right;padding-right: 5px;">{{ number_format(@$item->total_price) }}</td>
                                        <td class="padding-tb" style="text-align: right;padding-right: 5px;">0</td>
                                        <td></td>
                                        <td class="padding-tb" style="text-align: right;padding-right: 5px;">{{ number_format(@$item->total_price) }}</td>
                                    </tr>
                                @endforeach
                            @endif
                        @endforeach
                    @endif
                    @if(count(@$data['debit_detail']['first_price']) > 0)
                        <?php
                            $totalDv =0;
                            $totaldiscountDv =0;
                        ?>
                        @foreach(@$data['debit_detail']['first_price'] as $key => $service)
                            @php
                                $totalDiscountService += $service->discount;
                                $totaldiscountDv += $service->discount;
                                $totalService += $service->sumery;
                                $paid_total += $service->paid;
                                $totalDv += $service->sumery;
                            @endphp
                        @endforeach
                        <tr>
                            <td colspan="4" class="padding-tb"><b>Tổng phí dịch vụ /Management fee</b></td>
                            <td class="padding-tb" style="text-align: right;padding-right: 5px;"><strong>{{ number_format($totalDv + $totaldiscountDv) }}</strong></td>
                            <td class="padding-tb" style="text-align: right;padding-right: 5px;"><strong>{{ number_format($totaldiscountDv) }}</strong></td>
                            <td></td>
                            <td class="padding-tb" style="text-align: right;padding-right: 5px;"><strong>{{ number_format($totalDv) }}</strong></td>
                        </tr>
                        @foreach(@$data['debit_detail']['first_price'] as $key => $service)
                            <tr>
                                <td class="padding-tb">- {{ @$service->apartmentServicePrice->service->name }}</td>
                                <td class="padding-tb" style="text-align: center;">
                                    {{ number_format($service->sumery) }} <br/>
                                    @if(@$service->apartmentServicePrice->service->type == 2 && $service->apartmentServicePrice->floor_price > 0 && @$service->apartmentServicePrice->bdc_price_type_id != 3 )
                                        ({{ number_format($service->apartmentServicePrice->floor_price) }} * {{ @$data['apartment']->area }}m2)
                                    @endif
                                </td>
                                @if(@$service->apartmentServicePrice->bdc_price_type_id == 2 || @$service->apartmentServicePrice->bdc_price_type_id == 3)
                                    <td class="padding-tb" style="text-align: center;">{{ date('d/m/Y', strtotime(@$service->from_date)).' - '.date('d/m/Y', strtotime($service->to_date)) }}</td>
                                @else
                                    <td class="padding-tb" style="text-align: center;">{{ date('d/m/Y', strtotime(@$service->from_date)).' - '.date('d/m/Y', strtotime($service->to_date  . ' - 1 days')) }}</td>
                                @endif
                                <td class="padding-tb" style="text-align: center;">-</td>
                                <td class="padding-tb"  style="text-align: right;padding-right: 5px;">{{ number_format($service->sumery + $service->discount) }}</td>
                                <td class="padding-tb" style="text-align: right;padding-right: 5px;">{{ number_format($service->discount) }}</td>
                                <td></td>
                                <td class="padding-tb" style="text-align: right;padding-right: 5px;">{{ number_format($service->sumery) }}</td>
                            </tr>
                        @endforeach
                    @endif
                    <tr>
                        <td colspan="7" class="padding-tb"><b>(A)TỔNG CỘNG | Total</b></td>
                        @php
                            $total = $totalService + $totalVehicle + $totalDienNuocPrice;
                        @endphp
                        <td class="padding-tb" style="text-align: right;padding-right: 5px;">{{ number_format($total) }}</td>
                    </tr>
                    <tr>
                        <td colspan="7" class="padding-tb"><b>(B)ĐÃ THANH TOÁN | Paid</b></td>
                        <td class="padding-tb" style="text-align: right;padding-right: 5px;">
                            {{ number_format($paid_total) }}
                        </td>
                    </tr>
                    <tr>
                        <td colspan="7" class="padding-tb"><b>(C)NỢ CŨ | Previous Debit Total</b></td>
                        @php
                            $no_cu = App\Repositories\BdcV2DebitDetail\DebitDetailRepository::getTotalSumeryByCycleNameCus(@$data['building']->id,@$data['apartment']->id, @$data['bill']->cycle_name, $oper = "<",false,false);
                            $no = @$no_cu ? $no_cu->tong_phat_sinh-$no_cu->tong_thanh_toan : 0;
                        @endphp
                        <td class="padding-tb"  style="text-align: right;padding-right: 5px;">{{ number_format($no) }}</td>
                    </tr>
                    <tr>
                        <td colspan="7" class="padding-tb"><b>(D)DƯ CŨ | Surplus amount</b></td>
                        @php
                            $apartId = @$data['apartment']->id;
                            $du =$apartId ? App\Repositories\BdcCoin\BdcCoinRepository::getCoin($apartId,0) : 0;
                            $du = $du ? $du->coin : 0;
                        @endphp
                        <td class="padding-tb"  style="text-align: right;padding-right: 5px;">{{ number_format($du)}}</td>
                    </tr>
                    <tr>
                        <td colspan="7" class="padding-tb"><b>(E)CẦN THANH TOÁN | Total Due(A-B+C-D)</b></td>
                        <td class="padding-tb" style="text-align: right;padding-right: 5px;font-weight: bold;">{{ number_format($total - $paid_total + $no - $du) }}</td>
                    </tr>
                    </tbody>
                </table>
                <div><b>(*)</b> : Đơn giá đã bao gồm thuế và phí theo quy định | <strong>The unit price includes all taxes and fees as prescribed</strong></div>
                <div><b>(*) Lưu ý :</b>Khi thanh toán bằng chuyển khoản, xin vui lòng ghi rõ mã số căn hộ | <strong>Please provide to us your apartment number when making payment by Bank trasfer.</strong></div>
                <p> <strong>Thông tin thanh toán:</strong> </p>
                @if (@$data['building_payment_info'])
                        <div class="row">
                            <div class="col-sm-6">
                                <div><strong>Số tài khoản | Account: </strong> {{@$data['building_payment_info']->bank_account}} </div> 
                                <div><strong> Ngân hàng | Bank: </strong> {{@$data['building_payment_info']->bank_name}}</div> 
                                <div><strong> Chủ tài khoản | Beneficiary:</strong> {{@$data['building_payment_info']->holder_name}}</div> 
                                <div><strong> Chi nhánh | Branch: </strong>{{@$data['building_payment_info']->branch}}</div> 
                            </div> 
                            <div class="col-sm-6"> 
                                 <div class="text-center">
                                     TRƯỞNG BAN QUẢN LÝ
                                     <div style="margin-top: 20px ;text-transform: uppercase;">
                                        {{-- <img src="{{ asset('images/chu_ky_bql_02.png') }}" style="object-fit: contain;width: 220px" class="img_chu_ky"> --}}+
                                        <div style="margin-top: 100px;">Lương hoàng sơn</div>
                                        {{-- {{@$data['building']->manager_building->display_name}} --}}
                                     </div>
                                 </div>
                            </div>
                        </div>
                @endif
            </div>
            <hr style="border-top: 1px solid #ccc;">
        </div>
    @endforeach
</body>
<style type="text/css">
    @media print {
        .pagebreak {
             page-break-before: always; 
             clear: both;
             page-break-after: always;
            } /* page-break-after works, as well */
    }
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