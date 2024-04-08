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
    <table style="width: 100%; margin-bottom: 0px;">
        <tr>
            @php
            $year = substr(@$data['bill']->cycle_name, 0, -2);
            $month = substr(@$data['bill']->cycle_name, 4);
            @endphp
            <td colspan="2" style="width: 200px;"  class="text-header">
                    @php
                        $infor = \App\Models\Logs::where('user_id',@$data['building']->id)->where('logs_type','logo1')->first();
                        $inforct = $infor->content;
                    @endphp
                    @if(!$inforct)
                    <img style="width: 100px;" > </img>
                        
                    @else
                    <!--img style="width: 100px;" > </img-->
                    <img style="height: 80px;"  src="{{$inforct}}"> </img>
                    @endif
            </td>
            <td colspan="8">
                @if(@$data['building']->id == 109)
                <p style=" text-align: center; padding-top: 100px" class="text-invoice">BẢNG KÊ DỊCH VỤ KỲ THU THÁNG {{ $month }}/{{ $year }}</p>
                
                @else
                <p style=" text-align: center; padding-top: 100px" class="text-invoice">BẢNG KÊ DỊCH VỤ THÁNG {{ $month }}/{{ $year }}</p>
                @endif
            </td>
            <td colspan="2" style="text-align: right;width: 200px;">
                    @php
                        $infor = \App\Models\Logs::where('user_id',@$data['building']->id)->where('logs_type','logo')->first();
                        $inforct = $infor->content;
                    @endphp
                    @if(!$inforct)
                        
                    @else
                    <img style="float: right; height: 80px;" 
                        src="{{$inforct}}">
                    </img>
                    @endif
            </td>
        </tr>
    </table>
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
                        <td colspan="8">Khách Hàng: {{ @@$data['bill']->customer_name }}</td>
                        <td colspan="2" style="text-align: center;"></td>
                        <td colspan="2" style="text-align: left;padding-left: 150px">Số bảng kê: {{ @$data['bill']->bill_code }}</td>
                        
                    </tr>
                    <tr>
                        <td colspan="8">Căn hộ: {{ @@$data['apartment']->name }}</td>
                        <td colspan="2" class="uppercase" style="text-align: center;padding-left: 150px;text-transform: uppercase;">Tòa Nhà: {{ strtoupper(@$data['building']->name)  }}</td>
                        <td colspan="2"style="text-align: left;padding-left: 150px;">Ngày: {{ date('d/m/Y', strtotime(@$data['bill']->created_at)) }}</td>
                    </tr>
                </tbody>
            </table>
            <div class="pull-left">
                <div class="text_address">
                    <span>
                    </span>
                </div>
            </div>
        </div>
        <div class="footer">
        
            <table style="width: 100%; padding: 10px;" border="1" >
                <tbody>
                    <tr>
                        <td class="padding-tb" style="text-align: center; width: 200px;"><b>Tên dịch vụ</b></td>
                        <td class="padding-tb" style="text-align: center; width: 100px;"><b>Đơn giá</b></td>
                        <td class="padding-tb" style="text-align: center; width: 100px;"><b>Số lượng</b></td>
                        <td class="padding-tb" style="text-align: center; width: 100px;"><b>Tiêu thụ</b></td>
                        <td class="padding-tb" style="text-align: center; width: 100px;"><b>Giảm trừ</b></td>
                        <td class="padding-tb" style="text-align: center; width: 100px;"><b>Thành tiền</b></td>
                        <td class="padding-tb" style="text-align: center;"><b>Ghi chú</b></td>
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
                        <td colspan="3" class="padding-tb"><b>Phí dịch vụ</b></td>
                        <td class="padding-tb" style="text-align: right;padding-right: 5px;">
                            <strong> </strong>
                        </td>
                        <td class="padding-tb" style="text-align: right;padding-right: 5px;">
                        @if (number_format($totalDiscountService)==0)
                            <strong> </strong>
                        @else
                            <strong>{{ number_format($totalDiscountService) }}</strong>
                        @endif
                        </td>
                        <td class="padding-tb" style="text-align: right;padding-right: 5px;">
                            <strong>{{ number_format($totalService) }} </strong>
                        </td>
                        <td></td>
                    </tr>
                    @foreach(@$data['debit_detail']['service'] as $key => $service)
                    <tr>
                        <td class="padding-tb">{{ @$service->apartmentServicePrice->service->name }}</td>
                        <td class="padding-tb" style="text-align: right;padding-right: 5px;">
                            {{ number_format($service->apartmentServicePrice->floor_price) }} <br />
                        </td>
                        <td class="padding-tb" style="text-align: center;">
                            @if(@$service->apartmentServicePrice->service->type == 2 &&
                            $service->apartmentServicePrice->floor_price > 0)
                            {{ @$data['apartment']->area }}m2
                            @endif </td>
                        <td class="padding-tb" style="text-align: right;padding-right: 5px;"> </td>
                        <td class="padding-tb" style="text-align: right;padding-right: 5px;">
                        @if (number_format($service->discount)==0) 
                            <strong> </strong>
                        @else
                        {{ number_format($service->discount) }}
                        @endif
                        </td>
                        <td class="padding-tb" style="text-align: right;padding-right: 5px;">
                            {{ number_format($service->sumery) }}</td>
                        @if ( @$service->apartmentServicePrice->service->name == 'Phí thu hộ BQT')
                        <td class="padding-tb" style="text-align: center;max-width: 100px">
                        Thù lao BQT tháng 07/2023 và chi phí thuê bàn ghế tổ chức HNNCC
                        </td>
                        @else
                        @if(@$service->apartmentServicePrice->bdc_price_type_id == 2 ||
                        @$service->apartmentServicePrice->bdc_price_type_id == 3)
                        <td class="padding-tb" style="text-align: center;">
                         {{ date('d/m/Y', strtotime(@$service->from_date)).' - '.date('d/m/Y', strtotime($service->to_date)) }}
                        </td>
                        @else
                        <td class="padding-tb" style="text-align: center;">
                         {{ date('d/m/Y', strtotime(@$service->from_date)).' - '.date('d/m/Y', strtotime($service->to_date  . ' - 1 days')) }}  
                        </td>
                        @endif
                        @endif
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
        </table>
        
        <table style="width: 100%; padding: 10px; " border="1" >
                    <tr>
                        <td colspan="3" style="width: 300px;" class="padding-tb"><b>Phí gửi xe</b></td>
                        <td class="padding-tb" style="text-align: right;padding-right: 5px;width: 100px;">
                            <!--strong>{{ number_format($totalVehicle + $totalDiscountVehicle) }}</strong-->
                        </td>
                        <td class="padding-tb" style="text-align: right;padding-right: 5px; width: 100px;"> 
                        @if (number_format($totalDiscountVehicle)==0)
                        <strong> </strong>
                        @else
                            <strong>{{ number_format($totalDiscountVehicle) }}</strong>
                        @endif
                        </td>
                        <td style="text-align: right;padding-right: 5px; width: 100px;">    <strong> {{number_format($totalDv)}}</strong>  </td>
                       <td></td>
                        
                    </tr>
                    @foreach(@$data['debit_detail']['vehicle'] as $key => $service)
                    <tr>
                        <td class="padding-tb" style="width: 200px">{{ @$service->apartmentServicePrice->vehicle->number }}</td>
                        <td class="padding-tb" style="text-align: right;padding-right: 5px;width: 100px;">
                            {{ number_format(@$service->apartmentServicePrice->price) }}<br />
                        </td>
                        <td class="padding-tb" style="text-align: center; width: 100px;">1</td>
                        <td class="padding-tb" style="text-align: center;width: 100px;"> </td>
                        <td class="padding-tb" style="text-align: right;padding-right: 5px;width: 100px;">
                        @if (number_format($service->discount)==0)
                        <strong> </strong>
                        @else
                            {{ number_format($service->discount)}} 
                        @endif
                        </td>
                        <td class="padding-tb" style="text-align: right;padding-right: 5px;">
                            {{ number_format($service->sumery + $service->discount) }}</td>
                            @if(@$service->apartmentServicePrice->bdc_price_type_id == 2 ||
                        @$service->apartmentServicePrice->bdc_price_type_id == 3)
                        <td class="padding-tb" style="text-align: center;">
                           {{ date('d/m/Y', strtotime(@$service->from_date)).' - '.date('d/m/Y', strtotime($service->to_date)) }}  
                        </td>
                        @else
                        <td class="padding-tb" style="text-align: center;">
                       {{ date('d/m/Y', strtotime(@$service->from_date)).' - '.date('d/m/Y', strtotime($service->to_date  . ' - 1 days')) }}  
                        </td>
                        @endif
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
                            ?>
        </table>
        
        <table style="width: 100%; padding: 10px;" border="1">
                    @if (@$detail->data_price)
                    <tr>
                        <td colspan="2" style="width: 300px;" class="padding-tb">
                            <b>
                                {{ @$diennuoc->apartmentServicePrice->service->name }}
                            </b>
                            @foreach (@$detail->data_detail as $key => $item)
                            @php
                            $electric_meter = App\Models\BdcElectricMeter\ElectricMeter::find($item->id);
                            $tieu_thu = @$electric_meter->after_number - @$electric_meter->before_number;
                            $tong_tieu_thu += $tieu_thu;
                            $count_custom = @count($detail->data_detail);
                            @endphp
                            @if(@$count_custom===1)
                            <div>Đồng hồ</div>
                            @else
                            <div>Đồng hồ : {{$key+1}}</div>
                            @endif
                            <div> Chỉ số đầu: {{ @$electric_meter->before_number }} - Chỉ số cuối: {{ @$electric_meter->after_number }}</div>
                            @endforeach
                        </td>
                        <td style="width: 100px;"></td>
                        <td class="padding-tb" style="text-align: right; padding-right: 5px;width: 100px;">{{ @$tong_tieu_thu }} </td>
                        <td class="padding-tb" style="text-align: right; padding-right: 5px;width: 100px;">
                        @if (number_format($diennuoc->discount)==0)
                        <strong> </strong>
                        @else
                            <strong>{{ number_format($diennuoc->discount)}}</strong>
                        @endif
                        </td>
                        <td class="padding-tb" style="text-align: right; padding-right: 5px;width: 100px; ">
                        <strong>{{ number_format(@$diennuoc->sumery + @$diennuoc->discount) }}</strong>
                        </td>
                        @if(@$diennuoc->apartmentServicePrice->bdc_price_type_id == 2 || @$diennuoc->apartmentServicePrice->bdc_price_type_id == 3)
                        <td class="padding-tb" style="text-align: center;">
                         {{ date('d/m/Y', strtotime(@$diennuoc->from_date)).' - '.date('d/m/Y', strtotime(@$diennuoc->to_date)) }}
                        </td>
                        @else
                        <td class="padding-tb" style="text-align: center;">
                        {{ date('d/m/Y', strtotime(@$diennuoc->from_date)).' - '.date('d/m/Y', strtotime(@$diennuoc->to_date  . ' - 1 days')) }}
                        </td>
                        @endif
                    </tr>
                    @foreach (@$detail->data_price as $key => $item)
                    <?php
                                        $totalNumber += @$item->to - @$item->from + 1;
                                        $totalPrice += @$item->total_price;
                                    ?>
                    <tr>
                        <td class="padding-tb" style="width:200px;"> Từ {{ @$item->from }} - {{ $item->to }}</td>
                        <td class="padding-tb" style="text-align: right;padding-right: 5px;width: 100px;">{{ number_format(@$item->price) }}</td>
                        <td style="width: 100px;"> </td>
                        <td class="padding-tb" style="text-align: right;padding-right: 5px;width: 100px;">{{ @$item->to - @$item->from + 1 }}</td>
                        <td class="padding-tb" style="text-align: right;padding-right: 5px;width: 100px;"> </td>
                        <td class="padding-tb" style="text-align: right;padding-right: 5px;width: 100px;">
                            {{ number_format((@$item->to - @$item->from + 1) * @$item->price) }}</td>
                        <td class="padding-tb" style="text-align: right;padding-right: 5px;"> </td>
                    </tr> 
                        @endforeach
                        @endif
        </table>
        
        <table style="width: 100%; padding: 10px;" border="1">
                @if (@$detail->data)
                    <tr>
                        <td colspan="2" style="width: 300px;" class="padding-tb">
                            <b>
                                 {{ @$diennuoc->apartmentServicePrice->service->name}}
                            </b>
                        </td>
                        <td></td>
                        <td class="padding-tb" style="text-align: right;padding-right: 5px;width: 100px;">
                            <!--strong>{{ number_format(@$diennuoc->sumery + @$diennuoc->discount) }}</strong-->
                        </td>
                        <td class="padding-tb" style="text-align: right;padding-right: 5px;width: 100px;">
                            <strong>{{ number_format($diennuoc->discount)}}</strong>
                        </td>
                        <td class="padding-tb" style="text-align: right;padding-right: 5px; width: 100px;">
                            <strong>{{ number_format($diennuoc->sumery) }}</strong>
                        </td>
                        <td></td>
                    </tr>
                    <tr>
                        <td class="padding-tb" style="width: 200px;"> Chỉ Số Đầu: {{ @$detail->so_dau }} Chỉ Số Cuối: {{ @$detail->so_cuoi }}</td>
                        <td style="width: 100px;"></td>
                        <td style="width: 100px;"></td>
                        <td style="text-align: right; padding-right: 5px;width: 100px;"> {{  @$detail->tieu_thu }} </td>
                        <td style="width: 100px;"></td>
                        <td style="width: 100px;"></td>
                        <td class="padding-tb" style="text-align: center;">
                            {{ date('d/m/Y', strtotime(@$diennuoc->from_date)).' - '.date('d/m/Y', strtotime(@$diennuoc->to_date)) }}
                        </td>
                    </tr>
                    @foreach (@$detail->data as $key => $item)
                    <?php
                                        $totalNumber += @$item->to - @$item->from + 1;
                                        $totalPrice += @$item->total_price;
                    ?>
                    <tr>
                        <td class="padding-tb"> Từ {{ @$item->from }} - {{ $item->to }}</td>
                        <td class="padding-tb" style="text-align: right;padding-right: 5px;">{{ number_format(@$item->price) }}</td>
                        <td></td>
                        <td class="padding-tb" style="text-align: right;padding-right: 5px;;">{{ @$item->to - @$item->from + 1 }}</td>
                        <!--td class="padding-tb" style="text-align: right;padding-right: 5px;">
                            {{ number_format(@$item->total_price) }}</td-->
                        <td class="padding-tb" style="text-align: right;padding-right: 5px;"> </td>
                        <td class="padding-tb" style="text-align: right;padding-right: 5px;">
                            {{ number_format(@$item->total_price) }}</td>
                        @if(@$diennuoc->apartmentServicePrice->bdc_price_type_id == 2 ||
                        @$diennuoc->apartmentServicePrice->bdc_price_type_id == 3)
                        <td class="padding-tb" style="text-align: center;">
                            {{ date('d/m/Y', strtotime(@$diennuoc->from_date)).' - '.date('d/m/Y', strtotime(@$diennuoc->to_date)) }}
                        </td>
                        @else
                        <td class="padding-tb" style="text-align: center;">
                            {{ date('d/m/Y', strtotime(@$diennuoc->from_date)).' - '.date('d/m/Y', strtotime(@$diennuoc->to_date  . ' - 1 days')) }}
                        </td>
                        @endif
                    </tr>
                    @endforeach
                    @endif
                    @endforeach
                    @endif
                    <tr> 
                    @if (@$detail->data)
                    <td class="padding-tb" colspan="7">
                    @php
                        $infor = \App\Models\Logs::where('user_id',@$data['building']->id)->where('logs_type','price_include')->first();
                        $inforct = $infor->content;
                    @endphp
                    @if(!$inforct)  
                        Giá trên đã bao gồm: 
                    @else
                        Giá trên đã bao gồm: {{$inforct}}.
                    @endif
                    @endif
                    </td>
                    </tr>
                    </table>
        <table style="width: 100%; padding: 10px;" border="1">
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
                        <td colspan="4" style="width: 500px;" class="padding-tb"><b>Tổng phí dịch vụ</b></td>
                        <td class="padding-tb" style="text-align: right;padding-right: 5px;">
                        @if( number_format($totaldiscountDv)==0)
                        <strong> </strong>
                        @else
                            <strong> {{ number_format($totaldiscountDv) }}</strong>
                        @endif
                        </td>
                        <td class="padding-tb" style="text-align: right;padding-right: 5px;width: 100px;">
                            <strong> {{ number_format($totalDv) }}</strong>
                        </td>
                        <td class="padding-tb" style="text-align: right;padding-right: 5px;">
                        </td>
                    </tr>
                    @foreach(@$data['debit_detail']['first_price'] as $key => $service)
                    <tr>
                        <td class="padding-tb" style="width:200px;" >{{ @$service->apartmentServicePrice->service->name }}</td>
                        <td class="padding-tb" style="text-align: right;padding-right: 5px;width: 100px;">
                            {{ number_format($service->sumery) }} <br />
                            @if(@$service->apartmentServicePrice->service->type == 2 &&
                            $service->apartmentServicePrice->floor_price > 0 &&
                            @$service->apartmentServicePrice->bdc_price_type_id != 3 )
                            ({{ number_format($service->apartmentServicePrice->floor_price) }} *
                            {{ @$data['apartment']->area }}m2)
                            @endif
                        </td>
                        <td style="width: 100px;"></td>
                        <td style="width: 100px;"></td>
                        @if(number_format($service->discount) == 0)
                        <td class="padding-tb" style="text-align: right;padding-right: 5px;width: 100px;">
                           </td>
                        @else
                        <td class="padding-tb" style="text-align: right;padding-right: 5px;width: 100px;">
                            {{ number_format($service->discount) }}</td>
                        @endif
                        <td class="padding-tb" style="text-align: right;padding-right: 5px;">
                            {{ number_format($service->sumery + $service->discount) }}</td>
                            @if(@$service->apartmentServicePrice->bdc_price_type_id == 2 ||
                        @$service->apartmentServicePrice->bdc_price_type_id == 3)
                        <td class="padding-tb" style="text-align: center;">
                            {{ date('d/m/Y', strtotime(@$service->from_date)).' - '.date('d/m/Y', strtotime($service->to_date)) }}
                        </td>
                        @else
                        <td class="padding-tb" style="text-align: center;">
                            {{ date('d/m/Y', strtotime(@$service->from_date)).' - '.date('d/m/Y', strtotime($service->to_date  . ' - 1 days')) }}
                        </td>
                        @endif
                    </tr>
                    @endforeach
                    @endif
        </table>
                       
        <table style="width: 100%; padding: 10px;" border="1">
                    <tr>
                        <td  class="padding-tb" style="width: 200px;"><b>Tổng các loại phí</b></td>
                        <td  class="padding-tb" style="text-align: center; width: 100px;"><b> Dư kỳ trước </b></td>
                        <td  class="padding-tb" style="text-align: center; width: 100px;"><b> Nợ kỳ trước </b></td>
                        <td  class="padding-tb" style="text-align: center; width: 160px;"><b> Tổng phát sinh tháng</b></td>
                        <td class="padding-tb" style="text-align: center; width: 140px;"><b> Tổng đã thu</b></td>
                        <td  class="padding-tb" style="text-align: center;width: 120px;"><b> Tổng cộng phải thu </b></td>
                        <td class="padding-tb" style="text-align: center;"><b> Ghi Chú </b></td>
                    </tr>
                    <?php
                    $sql = "select sub.id, f.type, f.name as fname,
                    sum(case when cycle_name = ".@$data['bill']->cycle_name." then sumery else 0 end) as tong_phat_sinh,
                    sum(case when cycle_name <> ".@$data['bill']->cycle_name." then 0 else paid_by_cycle_name end) as thanh_toan,
                    ABS(sum(case when (case when cycle_name = ".@$data['bill']->cycle_name." then  before_cycle_name else after_cycle_name end)  < 0 then (case when cycle_name = ".@$data['bill']->cycle_name." then  before_cycle_name else after_cycle_name end) else 0 end)) as du,
                    sum(case when (case when cycle_name = ".@$data['bill']->cycle_name." then  before_cycle_name else after_cycle_name end)  > 0 then (case when cycle_name = ".@$data['bill']->cycle_name." then  before_cycle_name else after_cycle_name end) else 0 end) as no_truoc,
                    CASE
                    WHEN f.type = 0 THEN 'Phí khác'
                    WHEN f.type = 2 THEN 'Phí dịch vụ'
                    WHEN f.type = 3 THEN 'Phí nước'
                    WHEN f.type = 4 THEN 'Phí phương tiện'
                    WHEN f.type = 5 THEN 'Phí điện'
                    WHEN f.type = 6 THEN 'Phí nước nóng'
                    WHEN f.type = 7 THEN 'Phí tiện ích'
                    WHEN f.name is null then 'Tiền Thừa Không Chỉ Định' 
                    ELSE f.name
                    end as name
                    from
                    (
                    SELECT
                        t1.*
                    FROM
                        (
                        SELECT
                            *
                        FROM
                            bdc_v2_debit_detail
                        WHERE
                            deleted_at is null
                            AND bdc_building_id = ".@$data['building']->id."
                            AND bdc_apartment_id = ".@$data['apartment']->id."
                            AND cycle_name <= ".@$data['bill']->cycle_name." ) as t1
                    INNER JOIN (
                        SELECT
                            max(cycle_name) AS cycle_name,
                            bdc_apartment_id,
                            bdc_apartment_service_price_id
                        FROM
                            bdc_v2_debit_detail
                        WHERE
                            deleted_at is null
                            AND bdc_building_id = ".@$data['building']->id."
                            AND bdc_apartment_id = ".@$data['apartment']->id."
                            AND cycle_name <= ".@$data['bill']->cycle_name."
                        GROUP BY
                            bdc_apartment_id,
                            bdc_apartment_service_price_id ) as t2 ON
                        t1.bdc_apartment_service_price_id = t2.bdc_apartment_service_price_id
                        AND t1.bdc_apartment_id = t2.bdc_apartment_id
                        AND t1.cycle_name = t2.cycle_name) as sub
                left join bdc_coin d on sub.bdc_apartment_service_price_id = d.bdc_apartment_service_price_id and d.bdc_apartment_id = ".@$data['apartment']->id."
                left join bdc_apartment_service_price e on e.id = sub.bdc_apartment_service_price_id
                left join bdc_services f on e.bdc_service_id= f.id
                where
                    `cycle_name` = '".@$data['bill']->cycle_name."'
                    or (`cycle_name` < '".@$data['bill']->cycle_name."'
                        and `after_cycle_name` != 0) group by f.type";
                        $result = Illuminate\Support\Facades\DB::select(Illuminate\Support\Facades\DB::raw($sql));
                            $total_phatsinh = 0;
                            $total_du = 0;
                            $total_no = 0;
                            $total_phaithu= 0;
                            $total_dathu=0;
                        ?>
                        @foreach(@$result as $key => $datacustom)
                        <?php
                        if( !($datacustom->fname))
                        {
                            $sqltemp= "select COUNT(*) as dem  From bdc_v2_payment_detail where bdc_receipt_id = 0 and  bdc_debit_detail_id =".$datacustom->id."";
                            $rs = Illuminate\Support\Facades\DB::select(Illuminate\Support\Facades\DB::raw($sqltemp));
                            if (number_format($rs[0]->dem) != 0) {$datacustom->thanh_toan = 0;}
                        }
                        $tongphaithu =  ($datacustom->tong_phat_sinh) + ($datacustom->no_truoc) - ($datacustom->du) - ($datacustom->thanh_toan);
                        $total_phatsinh += $datacustom->tong_phat_sinh;
                        $total_du += $datacustom->du;
                        $total_no += $datacustom->no_truoc;
                        $total_phaithu +=$tongphaithu ;
                        $total_dathu += $datacustom->thanh_toan;
                        ?>
                    <tr>
                        <td  class="padding-tb">{{$datacustom->name}}</td>
                        <td  class="padding-tb" style="text-align: right;padding-right: 5px;"> 
                        {{number_format($datacustom->du)}}
                        </td>
                        <td  class="padding-tb" style="text-align: right;padding-right: 5px;">{{number_format($datacustom->no_truoc)}} </td>
                        <td  class="padding-tb" style="text-align: right;padding-right: 5px;">
                        {{number_format($datacustom->tong_phat_sinh)}}
                        </td>
                        <td class="padding-tb" style="text-align: right;padding-right: 5px;"> 
                        {{number_format($datacustom->thanh_toan)}} </td>
                        <td  class="padding-tb" style="text-align: right;padding-right: 5px;">
                        {{number_format($tongphaithu)}}
                        </td>
                        <td style="text-align: center;">  </td>
                    </tr>
                    @endforeach
                    @php
                        $total = $totalService + $totalVehicle + $totalDienNuocPrice;
                        $no_cu =
                        App\Repositories\BdcV2DebitDetail\DebitDetailRepository::getTotalSumeryByCycleNameCus(@$data['building']->id,@$data['apartment']->id, @$data['bill']->cycle_name, $oper = "<",false, false);
                        $no=@$no_cu ? $no_cu->tong_phat_sinh-$no_cu->tong_thanh_toan : 0;
                        $apartId = @$data['apartment']->id;
                        $du = $apartId ? App\Repositories\BdcCoin\BdcCoinRepository::getCoin($apartId,0) : 0;
                        $du = $du ? $du->coin : 0;
                    @endphp
                    <tr>
                        <td  class="padding-tb"> <strong>Tổng Cộng </strong>  </td>
                            
                            <td  class="padding-tb" style="text-align: right;padding-right: 5px;"> 
                            <strong> {{ number_format($total_du)}} </strong></td>
                            <td  class="padding-tb" style="text-align: right;padding-right: 5px;"> 
                            @php
                            $no_cu = App\Repositories\BdcV2DebitDetail\DebitDetailRepository::getTotalSumeryByCycleNameCus(@$data['building']->id,@$data['apartment']->id, @$data['bill']->cycle_name, $oper = "<",false,false);
                            $no = @$no_cu ? $no_cu->tong_phat_sinh-$no_cu->tong_thanh_toan : 0;
                            @endphp
                            <strong> {{ number_format($total_no)}} </strong></td>
                            <td  class="padding-tb" style="text-align: right;padding-right: 5px;">
                            <strong>  {{ number_format($total_phatsinh) }} </strong></td>
                            <td  class="padding-tb" style="text-align: right;padding-right: 5px;">
                            <strong> {{number_format($total_dathu)}}  </strong></td>
                            <td  class="padding-tb" style="text-align: right;padding-right: 5px;">
                            <strong> {{number_format($total_phaithu)}} </strong> 
                            </td>
                            <td style="text-align: center;">  </td>
                    </tr>
                </tbody>
            </table>
            
            
        <table style="width: 100%; padding: 10px; margin-bottom: 0px">
            <td colspan="4" class="padding-tb">
            <p> <strong>Thanh toán chuyển khoản:</strong> </p>
            @if (@$data['building_payment_info'])
            <div>
                <div >
                    <p><strong> Số tài khoản: </strong> {{@$data['building_payment_info']->bank_account}} </p>
                    <div>
                        <p><strong> Ngân hàng: </strong> {{@$data['building_payment_info']->bank_name}}</p>
                    </div>
                    <div>
                        <p><strong> Chủ tài khoản:</strong> {{@$data['building_payment_info']->holder_name}}</p>
                    </div>
                    <div>
                        <p><strong> Chi nhánh: </strong>{{@$data['building_payment_info']->branch}}</p>
                    </div>
                    @if ($data['building']->id === 109 )
                    <div>
                        <p><strong> Nội dung chuyển khoản: </strong> Căn hộ ... nộp tiền phí tháng ... </p>
                    </div>
                    @endif
                </div>
                @endif
            </div>
            </td> 
            <td colspan=3 class="padding-tb">
            <div id="qrgencode{{ @$data['bill']->id}}" style="text-align: center;margin-top: 15px">
                <script>
                    console.log(`{{@$data['bill']->id}}`);
                    function callAPI() {
                        var xhr = new XMLHttpRequest();
                        var url = 'https://apibdc.dxmb.vn/dev/createPaymentQR';
                        var apt_id = {{$apartId}};
                        var bdc_id = {{@$data['building']->id}};
                        var bill_code = `{{ @$data['bill']->id }}`;
                        var bill_cost = {{@$total_phaithu}};
                        var params =`apartment_id=${apt_id}&building_id=${bdc_id}&amount=${bill_cost}&list_bill%5B0%5D=${bill_code}&type_payment=bill`;
                        //var params ='apartment_id=12853&building_id=77&amount=10000&list_bill%5B0%5D=461396&type_payment=bill';
                        //console.log(params);
                        console.log(bill_code);
                        xhr.open('POST', url, true);
                        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                        //xhr.setRequestHeader('Cookie', 'SERVERUSED=s1');
                        xhr.setRequestHeader('Authorization', 'Bearer SERVERUSED=s1');
                        xhr.onreadystatechange = function() {
                            if (xhr.readyState === XMLHttpRequest.DONE) {
                                if (xhr.status === 200) {
                                    var response = JSON.parse(xhr.responseText);
                                        //console.log(response);
                                        //console.log(response.data);
                                        //console.log(response.data.qr_code);
                                        //console.log(JSON.stringify(response.data.qr_code));
                                        //const resultElement = document.getElementById('result');
                                        //resultElement.innerHTML = `API Response: ${JSON.stringify(response.data.qrcode)}`;
                                        var option = '<img  style="width: 150px;" src="' + response.data.qr_code + '"> </img>';
                                        // document.getElementById('qrgencode').append(option);
                                        var element = document.getElementById("qrgencode{{ @$data['bill']->id}}");
                                        element.innerHTML += option;
                                        // var myImg = document.getElementById('#qrcodeImg');
                                        //  myImg.src = response.data.qr_code;
                                } else {
                                    console.error('API request failed. Status:', xhr.status);
                                }
                            }
                        };
                        xhr.send(params);
                    }
                    callAPI();
                </script>
                <style type="text/css">

                div#qrgencode{{@$data['bill']->id}} {
                    width: 150px;
                    height: 150px;
                    overflow: hidden;
                }

                #qrgencode{{@$data['bill']->id}} img {
                    clip: rect(20px, 150px, 130px, 20px);
                    position: absolute;
                }
                </style>
            </table>
            </div>
            @php
            $deadline = number_format( date('d',strtotime(@$data['bill']->deadline)));
            $createdAt = number_format( date('d',strtotime(@$data['bill']->created_at)));
            $diff = $deadline - $createdAt + 1 ;
            $deadline = new DateTime(@$data['bill']->deadline);
            $createdAt = new DateTime(@$data['bill']->created_at);
            // Tính số ngày
            $interval = $deadline->diff($createdAt);
            $diffDays = $interval->days + 1;
            // Tính số tháng
            $diffMonths = $interval->m;
            // Kết hợp số ngày và số tháng
            $totalDaysAndMonths = $diffDays + ($diffMonths * 30);
            @endphp
            <p>Thời hạn thanh toán: Quý cư dân vui lòng thanh toán trong vòng {{$totalDaysAndMonths}} ngày kể từ ngày phát hành thông báo
                này. Kính mong Quý cư dân thực hiện thanh toán phí đầy đủ và đúng hạn để Ban Quản Lý có thể đảm bảo được
                duy trì các dịch vụ chung của toàn nhà và các dịch vụ tại căn hộ. Quý cư dân vui lòng bỏ qua khoản nhắc
                nợ nếu đã thanh toán các phí trên sau ngày {{ date('d/m/Y', strtotime(@$data['bill']->created_at)) }}
                .Mọi thắc mắc vui lòng liên hệ trực tiếp Ban Quản Lý qua số điện thoại: {{ @$data['building']->phone }} trong giờ hành chính. 
            </p>
            <hr style="border-top: 2px solid #ccc;">
            <table id="duongstyle" style="width: 100%; padding: 10px; margin-bottom: 0px">
            <tr>
                <td colspan="4" style="width: 200px;"></td>
                <td colspan="4" style="width: 200px;"></td>
                <td colspan="4" style="text-align:center;">
                    <strong style= "text-transform: uppercase;"> BAN QUẢN LÝ TÒA NHÀ {{strtoupper(@$data['building']->name)  }} </strong>
                </td>
            </tr>
            <tr>
                <td colspan="4"></td>
                <td colspan="4"></td>
                <td colspan="4" style="text-align:center;">
                    <strong> Chữ Ký Trưởng Ban </strong>
                </td>
            </tr>
            <tr style="padding-right: 50px;">
                <td colspan="4"></td>
                <td colspan="4"></td>
                <td colspan="4" style="text-align:center;">    
                    @php
                        $infor = \App\Models\Logs::where('user_id',@$data['building']->id)->where('logs_type','chuky')->first();
                        $inforct = $infor->content;
                    @endphp
                    @if(!$inforct)

                    @else
                    <img style="width: 180px;" src="{{$inforct}}"> </img>
                    @endif
                </td>
            </tr>
            <tr> 
                <td colspan="4"></td>
                <td colspan="4"></td>
                <td colspan="4" style="text-align:center;">
                    <strong>
                        @php
                        $building = \App\Models\Building\Building::get_detail_building_by_building_id(@$data['building']->id); 
                        $namemanager =  \App\Models\PublicUser\UserInfo::where('pub_user_id',$building->manager_id)->where('bdc_building_id',$data['building']->id)->first(); 
                        @endphp
                        {{@$namemanager->display_name}}
                    </strong>
                </td>
            </tr>
            </td>
            </table>
            <br>
        </div>
        @endforeach
</body>
<script>
document.addEventListener('DOMContentLoaded', onPageLoaded);

function onPageLoaded() {
    checkAndRemoveTables();
}

function addRowToLastTable() {
            var tables = document.getElementsByTagName('table');
            var lastTable = tables[tables.length - 2]; // Lấy bảng cuối cùng
            console.log(lastTable);
            if (lastTable) {
                var captionRow = document.createElement('tr');
                var captionCell = document.createElement('td');
                captionCell.colSpan = lastTable.rows[0].cells.length; 
                captionCell.innerHTML = `<tr> 
                test 
                 </tr>`;
                captionRow.appendChild(captionCell);
                lastTable.appendChild(captionRow);
                console.log('done');
            }
            else
            {
                console.log("fail roi");
            }
        }
function checkAndRemoveTables() {
            var tables = document.getElementsByTagName('table');
            for (var i = tables.length - 1; i >= 0; i--) {
                var table = tables[i];
                if (table && table.rows.length === 0) {
                    table.parentNode.removeChild(table);
                }
            }
           // addRowToLastTable();
        }


</script>
<style type="text/css">
@media print {
    .pagebreak {
        page-break-before: always;
        clear: both;
        page-break-after: always;
    }
}
@media print { 
    img {
         max-width : 100px;
         height : auto;
    }
} 

.uppercase {
    text-transform: uppercase;
  }

div#qrgencode{{@$data['bill']->id}} {
    width: 150px;
    height: 150px;
    overflow: hidden;
}

#qrgencode{{@$data['bill']->id}} img {
    clip: rect(20px, 150px, 130px, 20px);
    position: absolute;
}

thead {
    display: table-header-group;
  }

  thead {
    display: table-header-group;
  } 

tr {
    page-break-inside: avoid;
  }

table {
            margin-bottom: 10px; 
        }

.table-container {
        display: flex;
    }

.table-container table {
        flex: 1;
        margin-right: 10px;
    }

.table-container table:last-child {
        margin-right: 0;
    }

* {
    font-size: 13px;
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
    font-size: 10px;
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
    margin: 0px 0;
}

.list_service table {
    width: 100%
}

.list_service table td {
    border-collapse: collapse;
    border: 1px solid black;
}

.list_service thead td {
    font-weight: bold;
}

.list_service td {
    padding: 10px;
}
</style>

</html>