<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <link rel="stylesheet" href="{{ url('adminLTE/css/bootstrap.min.css') }}" />
    <link rel="stylesheet" href="{{ url('adminLTE/css/bootstrap-theme.min.css') }}" />
</head>

<body>
   
    @foreach ($data_bill as $data)
    @php
        $_building =strtoupper(App\Commons\Helper::convert_vi_to_en(@$data['building']->name));
    @endphp
        <div class="container pagebreak">
            <div class="container" style="padding-top: 15px;">
                <table width="100%">
                    <tr>
                        <td class="col-sm-3">
                            {{-- @if (@$data['building']->id == 102) --}}
                            <img src="{{ asset('images/rivera_park_hanoi.jpeg') }}" class="img_logo_rivera">
                            {{-- @endif --}}
                        </td>
                        <td class="text-center col-sm-6" style="font-weight: bold;color: #0E154D">
                            <strong>BAN QUẢN LÝ TÒA NHÀ {{ $_building }}</strong>
                            <br>
                            <br>
                            <p>
                            <h3 style="font-size: 18px;
                            font-weight: bolder;">THÔNG BÁO PHÍ | FEE NOTIFICATIONS</h3>
                            </p>
                        </td>
                        <td class="pull-right" style="font-weight: bold;">
                            <img src="{{ asset('images/rivera_home.png') }}" width="100" height="70">
                        </td>
                    </tr>
                </table>
                <hr style="border-top: 1px solid #0d154d;margin: 0">
            </div>
            <div class="container">
                <div class="content_text">
                    @php
                        $year = substr(@$data['bill']->cycle_name, 0, -2);
                        $month = substr(@$data['bill']->cycle_name, 4);
                    @endphp
                    <table width="100%">
                        <tbody>
                            <tr>
                                <td colspan="3">Kính gửi Quý Cư dân | Dear Valued Residents&nbsp;&nbsp;Ông/Bà | Mr/Mrs:
                                    <span><strong> {{ @$data['bill']->customer_name }}</strong></span></td>
                                <td></td>
                                <td></td>
                            </tr>
                            <tr>
                                <td>Mã căn hộ | Apartment's Code: <strong>{{ @$data['apartment']->name }}</strong> </td>
                                <td>Ngày bàn giao căn hộ | Handover Date: </td>
                                <td></td>
                            </tr>
                            <tr>
                                <td>Ngày phát hành TB phí | Date of Issue:
                                    <strong>{{ date('d/m/Y', strtotime(@$data['bill']->created_at)) }}</strong> </td>
                                <td>Kỳ tháng | Month: <strong>{{ $month }}/{{ $year }}</strong></td>
                                <td></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                    <table style="width: 100%; padding: 10px" border="1">
                        <tbody>
                            <tr style="background-color: black;color: #fff">
                                <td class="padding-tb" style="text-align: center;"><b>TT</b></td>
                                <td class="padding-tb" style="text-align: center;"><b>Diễn giải | Descrtiption</b></td>
                                <td class="padding-tb" style="text-align: center;">
                                    <b>ĐVT</b>
                                    <div><b>Unit</b></div> 
                                </td>
                                <td class="padding-tb" style="text-align: center;">
                                    <b>Số lượng</b>
                                    <div><b>Quantity</b></div> 
                                </td>
                                <td class="padding-tb" style="text-align: center;">
                                    <b>Đơn giá</b>
                                    <div><b>Unit Price</b></div> 
                                </td>
                                <td class="padding-tb" style="text-align: center;">
                                    <b>Thành tiền</b>
                                    <div><b>Amount</b></div> 
                                </td>
                                <td class="padding-tb" style="text-align: center;">
                                    <b>Thuế suất</b>
                                    <div><b>VAT%</b></div> 
                                </td>
                                <td class="padding-tb" style="text-align: center;">
                                    <b>Tổng cộng</b>
                                    <div><b>Total Amount</b></div> 
                                </td>
                            </tr>
                            <tr>
                                <td class="padding-tb"><b>A</b></td>
                                <td colspan="5" class="padding-tb"><b>PHÍ TRONG THÁNG | FEE OF MONTH (A) = (A1) +
                                        (A2) + (A3) + (A4) + (A5)</b></td>
                                <td></td>
                                <td class="padding-tb" style="text-align: right;padding-right: 5px;"> <strong
                                        id="total_payment">{{number_format(@$data['debit_detail']['sumery_total'], 0, '.', ',')}}</strong></td>
                            </tr>
                            @php
                                $totalService = 0;
                                $totalVehicle = 0;
                                $totalWaterElecttric = 0;
                                $totalServiceOrther = 0;
                                
                                $totalDiscountService = 0;
                                $totalDiscountVehicle = 0;
                                $totalDiscountWaterElecttric = 0;
                                $totalDiscountServiceOrther = 0;
                                
                                $totalPrice = 0;
                                $paid_total = 0;
                                $paid_electric_total = 0;
                                $paid_service_total = 0;
                                $paid_meter_total = 0;
                                $paid_vehicle_total = 0;
                                
                                $totalDv = 0;
                                $totalDc = 0;
                                $totalDv_electric =0;
                                $totalDc_electric =0;
                                $totalDv_water =0;
                                $totalDc_water =0;
                            @endphp
                            @if (count(@$data['debit_detail']['other']) > 0)
                                @foreach (@$data['debit_detail']['other'] as $key => $diennuoc)
                                    <?php
                                    $detail = json_decode(@$diennuoc->detail);
                                    $totalNumber = 0;
                                    $totalWaterElecttric += $diennuoc->sumery;
                                    $totalDv += $diennuoc->sumery;
                                    $totalDc += $diennuoc->discount;
                                    $paid_total += $diennuoc->paid;
                                    $tong_tieu_thu = 0;
                                    $diennuoc_year = substr($diennuoc->cycle_name, 0, -2);
                                    $diennuoc_month = substr($diennuoc->cycle_name, 4);
                                    $apartmentServicePrice = @$diennuoc->bdc_apartment_service_price_id != 0 ? App\Models\BdcApartmentServicePrice\ApartmentServicePrice::get_detail_bdc_apartment_service_price_by_apartment_id($diennuoc->bdc_apartment_service_price_id) : null;
                                    $service = @$diennuoc->bdc_apartment_service_price_id != 0 ? App\Models\Service\Service::get_detail_bdc_service_by_bdc_service_id($apartmentServicePrice->bdc_service_id) : null;
                                    $vehicle = @$apartmentServicePrice->bdc_vehicle_id > 0 ? App\Models\Vehicles\Vehicles::get_detail_vehicle_by_id($apartmentServicePrice->bdc_vehicle_id) : null;
                                    if($service->type == 5){
                                        $totalDv_electric += $diennuoc->sumery;
                                        $totalDc_electric += $diennuoc->discount;
                                        $paid_electric_total += $diennuoc->paid;
                                    }else{
                                        $totalDv_water += $diennuoc->sumery;
                                        $totalDc_water += $diennuoc->discount;
                                        $paid_meter_total += $diennuoc->paid;
                                    }
                                   ?>
                                    @if (@$detail->data_price)
                                        <tr>
                                            <td class="padding-tb" style="width: 3%;"><b>A{{ $key + 1 }}</b></td>
                                            <td class="padding-tb"><b>{{$service->type == 5 ? 'Phí điện sử dụng T'.($diennuoc_month-1).'/'.$diennuoc_year.' | Electric Fee':'Phí nước sử dụng T'.($diennuoc_month-1).'/'.$diennuoc_year.' | Water Fee'}}</b></td>
                                            <td class="padding-tb"></td>
                                            <td class="padding-tb" style="text-align: right;padding-right: 5px;">
                                                <strong></strong></td>
                                            <td class="padding-tb" style="text-align: right;padding-right: 5px;">
                                                <strong></strong></td>
                                            <td class="padding-tb" style="text-align: right;padding-right: 5px;">
                                                <strong>{{ $service->type == 5 ? number_format($totalDv_electric + $totalDc_electric):number_format($totalDv_water + $totalDc_water)  }}</strong></td>
                                            <td class="padding-tb" style="text-align: center;padding-right: 5px;">
                                                <strong>{{$service->type == 5 ? '8%':'15%'}} </strong></td>
                                            <td class="padding-tb" style="text-align: right;padding-right: 5px;">
                                                <strong>{{ $service->type == 5 ? number_format($totalDv_electric):number_format($totalDv_water) }}</strong></td>
                                        </tr>

                                        @if(@$detail->check_two_price == 1)
                                            @foreach (@$detail->data_detail as $key_1 => $item)
                                                <tr>
                                                    @php
                                                        $electric_meter = App\Models\BdcElectricMeter\ElectricMeter::find($item->id);
                                                        $tieu_thu = @$electric_meter->after_number - @$electric_meter->before_number;
                                                        $tong_tieu_thu += $tieu_thu;
                                                        $count_meter_1 = collect(@$detail->data_price)->whereStrict('meter',null)->sum('total_price');
                                                        $date_using =$key_1 == 0 ? 7 : 23;
                                                    @endphp
                                                    <td align="center"><b>A 1.{{$key_1+1}}</b> </td>
                                                    <td class="padding-tb" width="40%">
                                                        <div><b>{{ 'Từ '.$item->from_date .' đến '.$item->to_date.' - '}}{{$date_using}} Ngày</b></div>
                                                        <div style="display: flex;">
                                                            <div style="width: 40%">Đồng hồ:
                                                                {{ $key_1 + 1 == count($detail->data_detail) ? '' : '' }}
                                                            </div>
                                                            <div style="width: 40%">Chỉ số đầu :
                                                                {{ @$electric_meter->before_number }}</div>
                                                            <div style="width: 40%">Chỉ số cuối :
                                                                {{ @$electric_meter->after_number }}</div>
                                                        </div>
                                                    </td>
                                                    <td class="padding-tb" style="text-align: center;padding-right: 5px;">{{$service->type == 5 ? 'KWh':'M3'}}</td>
                                                    <td class="padding-tb" style="text-align: center;padding-right: 5px;">
                                                        {{ @$tieu_thu }}</td>
                                                    <td class="padding-tb" style="text-align: center;padding-right: 5px;">
                                                        <strong style="font-size: 10px;"> <!--  {{$service->type == 5 ? 'Đơn giá theo Quyết định số 648/QĐ-BCT':''}}-->
                                                            @if($electric_meter->type_action == 2)
                                                                {{$service->type == 5 ? 'Đơn giá theo Quyết định số 1.062/QĐ-BCT':''}}
                                                            @else
                                                                {{$service->type == 5 ? 'Đơn giá theo Quyết định số 1.062/QĐ-BCT':''}}
                                                            @endif
                                                        </strong>
                                                    </td>
                                                    <td class="padding-tb" style="text-align: right;padding-right: 5px;">
                                                        @if($key_1 ==0)
                                                            <strong >{{ number_format($count_meter_1)  }}</strong>
                                                        @else
                                                            <strong>{{ $service->type == 5 ? number_format($totalDv_electric - $count_meter_1):number_format($totalDv_water - $count_meter_1)  }}</strong>
                                                        @endif
                                                    </td>
                                                    <td colspan="2"></td>
                                                </tr>
                                                @php
                                                   $_count=1;
                                                   $meter_1 = collect(@$detail->data_price)->whereStrict('meter',null);
                                                   $meter_2 = collect(@$detail->data_price)->where('meter.id',$item->id);

                                                @endphp
                                                @if(count($meter_1) >0 && $key_1 ==0)
                                                    @php
                                                        $count_meter =0;
                                                        $count_new_level =0;
                                                    @endphp
                                                    @foreach (@$meter_1 as $key_3 => $item_3)
                                                        @php
                                                            $count_meter+=(@$item_3->to - @$item_3->from + 1)*30/$date_using;
                                                            $new_level = ($count_meter < 105)? 50 : 100;
                                                            $count_new_level +=$new_level;
                                                        @endphp
                                                        <tr>
                                                           <td></td>
                                                            <td class="padding-tb">
                                                                @if($_count == 6)
                                                                    Bậc {{ $_count++ }} ({{$_count == 1 ? 1 : $count_new_level - $new_level+1 }} trở lên) Bậc giá tính mới {{ @$item_3->from }} trở lên
                                                                @else
                                                                    Bậc {{ $_count++ }} ({{$_count == 1 ? 1 : $count_new_level - $new_level+1 }} - {{ $count_new_level }}) Bậc giá tính mới {{$new_level.'*'.$date_using.'/30'}} = {{  round($new_level*$date_using/30)  }} ({{ @$item_3->from }} - {{ $item_3->to }})
                                                                @endif
                                                            </td>
                                                            <td class="padding-tb" style="text-align: center;"></td>
                                                            <td class="padding-tb" style="text-align: center;padding-right: 5px;">
                                                                {{ @$item_3->to - @$item_3->from + 1 }}</td>
                                                            <td class="padding-tb" style="text-align: right;padding-right: 5px;">
                                                                {{ number_format(@$item_3->price, 2, '.', ',') }}</td>
                                                            <td class="padding-tb" style="text-align: right;padding-right: 5px;"> 
                                                            <!-- Duong change format Number to round  {{ number_format((@$item_3->to - @$item_3->from + 1) * @$item_3->price, 2, '.', ',') }}  -->
                                                            {{ number_format(round((@$item_3->to - @$item_3->from + 1) * @$item_3->price), 0, '.', ',') }} 
                                                            </td>
                                                            @if ($key_3 == 0)
                                                                <td colspan="2" rowspan="{{count(@$detail->data_price)}}"></td>
                                                            @endif
                                                        </tr>
                                                    @endforeach
                                                @endif
                                                @if(count($meter_2) >0)
                                                    @php
                                                        $count_meter =0;
                                                        $count_new_level =0;
                                                    @endphp
                                                    @foreach (@$meter_2 as $key_4 => $item_3)
                                                        @php
                                                            $count_meter+=(@$item_3->to - @$item_3->from + 1)*30/$date_using;
                                                            $new_level = ($count_meter < 105)? 50 : 100;
                                                            $count_new_level +=$new_level;
                                                        @endphp
                                                        <tr>
                                                           <td></td>
                                                            <td class="padding-tb">
                                                                @if($_count == 6)
                                                                    Bậc {{ $_count++ }} ({{$_count == 1 ? 1 : $count_new_level - $new_level+1 }} trở lên) Bậc giá tính mới {{ @$item_3->from }} trở lên
                                                                @else
                                                                    Bậc {{ $_count++ }} ({{$_count == 1 ? 1 : $count_new_level - $new_level+1 }} - {{ $count_new_level }}) Bậc giá tính mới {{$new_level.'*'.$date_using.'/30'}} = {{  round($new_level*$date_using/30)  }} ({{ @$item_3->from }} - {{ $item_3->to }})
                                                                @endif
                                                            </td>
                                                            <td class="padding-tb" style="text-align: center;"></td>
                                                            <td class="padding-tb" style="text-align: center;padding-right: 5px;">
                                                                {{ @$item_3->to - @$item_3->from + 1 }}</td>
                                                            <td class="padding-tb" style="text-align: right;padding-right: 5px;">
                                                                {{ number_format(@$item_3->price, 2, '.', ',') }}</td>
                                                            <td class="padding-tb" style="text-align: right;padding-right: 5px;">
                                                                {{ number_format(round((@$item_3->to - @$item_3->from + 1) * @$item_3->price), 0, '.', ',') }}
                                                            </td>
                                                            @if ($key_4 == 0)
                                                                <td colspan="2" rowspan="{{count(@$detail->data_price)}}"></td>
                                                            @endif
                                                        </tr>
                                                    @endforeach
                                                @endif

                                            @endforeach

                                        @else
                                            @foreach (@$detail->data_detail as $key_1 => $item)
                                                <tr>
                                                    @php
                                                        $electric_meter = App\Models\BdcElectricMeter\ElectricMeter::find($item->id);
                                                        $tieu_thu = @$electric_meter->after_number - @$electric_meter->before_number;
                                                        $tong_tieu_thu += $tieu_thu;
                                                    @endphp
                                                    <td></td>
                                                    <td class="padding-tb" width="40%">
                                                        <div style="display: flex;">
                                                            <div style="width: 40%">Đồng hồ:
                                                                {{ $key_1 + 1 == count($detail->data_detail) ? '' : '(cũ)' }}
                                                            </div>
                                                            <div style="width: 40%">Chỉ số đầu :
                                                                {{ @$electric_meter->before_number }}</div>
                                                            <div style="width: 40%">Chỉ số cuối :
                                                                {{ @$electric_meter->after_number }}</div>
                                                        </div>
                                                    </td>
                                                    <td class="padding-tb" style="text-align: center;padding-right: 5px;">{{$service->type == 5 ? 'KWh':'M3'}}</td>
                                                    <td class="padding-tb" style="text-align: center;padding-right: 5px;">
                                                        {{ @$tieu_thu }}</td>
                                                    <td class="padding-tb" style="text-align: center;padding-right: 5px;">
                                                        <strong style="font-size: 10px;"> <!--{{$service->type == 5 ? 'Đơn giá theo Quyết định số 648/QĐ-BCT':''}}-->
                                                            {{$service->type == 5 ? 'Đơn giá theo Quyết định số 1.062/QĐ-BCT':''}}
                                                        </strong>
                                                    </td>
                                                    <td></td>
                                                    <td colspan="2"></td>
                                                </tr>
                                            @endforeach
                                            @foreach (@$detail->data_price as $key => $item)
                                                    <?php
                                                    $totalNumber += @$item->to - @$item->from + 1;
                                                    $totalPrice += @$item->total_price;
                                                    ?>
                                                <tr>
                                                    <td></td>
                                                    <td class="padding-tb"> Bậc {{ $key + 1 }} ({{ @$item->from }} -
                                                        {{ $item->to }})</td>
                                                    <td class="padding-tb" style="text-align: center;"></td>
                                                    <td class="padding-tb" style="text-align: center;">
                                                        {{ @$item->to - @$item->from + 1 }}</td>
                                                    <td class="padding-tb" style="text-align: right;padding-right: 5px;">
                                                        {{ number_format(@$item->price, 2, '.', ',') }}</td>
                                                    <td class="padding-tb" style="text-align: right;padding-right: 5px;">
                                                        {{ number_format(round((@$item->to - @$item->from + 1) * @$item->price), 0, '.', ',') }}
                                                    </td>
                                                    @if ($key == 0)
                                                        <td colspan="2" rowspan="{{count(@$detail->data_price)}}"></td>
                                                    @endif
                                                </tr>
                                            @endforeach
                                        @endif
                                    @endif
                                    {{-- xong --}}
                                    @if (@$detail->data)
                                        <tr>
                                            <td class="padding-tb" style="width: 3%;"><b>A{{ $key + 1 }}</b></td>
                                            <td class="padding-tb"><b>{{$service->type == 5 ? 'Phí điện sử dụng T'.$diennuoc_month.'/'.$diennuoc_year.' | Electric Fee':'Phí nước sử dụng T'.$diennuoc_month.'/'.$diennuoc_year.' | Water Fee'}}</b></td>
                                            <td class="padding-tb" style="text-align: center;padding-right: 5px;">{{$service->type == 5 ? 'KWh':'M3'}} </td>
                                            <td class="padding-tb" style="text-align: center;padding-right: 5px;">
                                                {{ @$detail->tieu_thu }}</td>
                                            <td class="padding-tb" style="text-align: right;padding-right: 5px;">
                                                <strong></strong></td>
                                            <td class="padding-tb" style="text-align: right;padding-right: 5px;">
                                                <strong>{{ $service->type == 5 ? number_format($totalDv_electric + $totalDc_electric):number_format($totalDv_water + $totalDc_water)  }}</strong></td>
                                            <td class="padding-tb" style="text-align: center;padding-right: 5px;">
                                                <strong>{{$service->type == 5 ? '8%':'15%'}} </strong></td>
                                            <td class="padding-tb" style="text-align: right;padding-right: 5px;">
                                                <strong>{{ $service->type == 5 ? number_format($totalDv_electric):number_format($totalDv_water) }}</strong></td>
                                        </tr>
                                        <tr>
                                            <td></td>
                                            <td class="padding-tb">
                                                <div style="display: flex;">
                                                    <div style="width: 40%">Đồng hồ :</div>
                                                    <div style="width: 40%">Chỉ số đầu : {{ @$detail->so_dau }}</div>
                                                    <div style="width: 40%">Chỉ số cuối : {{ @$detail->so_cuoi }}</div>
                                                </div>
                                            </td>
                                            <td class="padding-tb" style="text-align: center;padding-right: 5px;">{{$service->type == 5 ? 'KWh':'M3'}}</td>
                                            <td class="padding-tb" style="text-align: center;">
                                                {{ @$detail->tieu_thu }}</td>
                                            <td></td>
                                            <td class="padding-tb" style="text-align: right;padding-right: 5px;">
                                               
                                            </td>
                                            <td class="padding-tb" style="text-align: center;padding-right: 5px;">
                                            </td>
                                            <td class="padding-tb" style="text-align: right;padding-right: 5px;">
                                                <strong></strong></td>
                                        </tr>
                                        @foreach (@$detail->data as $key => $item)
                                            <?php
                                            $totalNumber += @$item->to - @$item->from + 1;
                                            $totalPrice += @$item->total_price;
                                            ?>
                                            <tr>
                                                <td></td>
                                                <td class="padding-tb"> Bậc {{ $key + 1 }} ({{ @$item->from }}
                                                    - {{ $item->to }})</td>
                                                <td class="padding-tb"></td>
                                                <td class="padding-tb" style="text-align: center;">
                                                    {{ @$item->to - @$item->from + 1 }}</td>
                                                <td class="padding-tb" style="text-align: right;padding-right: 5px;">
                                                    {{ number_format(@$item->price, 2, '.', ',') }}</td>
                                                <td class="padding-tb" style="text-align: right;padding-right: 5px;">
                                                    {{ number_format(round((@$item->to - @$item->from + 1) * @$item->price), 0, '.', ',') }}
                                                </td>
                                                @if ($key == 0)
                                                  <td colspan="2" rowspan="{{count(@$detail->data)}}"></td>
                                                @endif
                                            </tr>
                                        @endforeach
                                    @endif
                                @endforeach
                            @endif
                            @if (count(@$data['debit_detail']['service']) > 0)
                                <?php
                                $totalDv = 0;
                                $totalDc = 0;
                                ?>
                                @foreach (@$data['debit_detail']['service'] as $key => $service)
                                    @php
                                        $totalDiscountService += $service->discount;
                                        $totalService += $service->sumery;
                                        $paid_total += $service->paid;
                                        $paid_service_total += $service->paid;
                                        $totalDc += $service->discount;
                                        $totalDv += $service->sumery;
                                    @endphp
                                @endforeach
                                <tr>
                                    <td class="padding-tb"><strong>A3</strong></td>
                                    <td colspan="3" class="padding-tb"><b>Phí dịch vụ</b></td>
                                    <td class="padding-tb" style="text-align: right;padding-right: 5px;"></td>
                                    <td class="padding-tb" style="text-align: right;padding-right: 5px;">
                                        <strong>{{ number_format($totalDv + $totalDc) }}</strong></td>
                                    <td class="padding-tb" style="text-align: center;padding-right: 5px;">
                                        <strong>10%</strong> </td>
                                    <td class="padding-tb" style="text-align: right;padding-right: 5px;">
                                        <strong>{{ number_format($totalDv) }}</strong></td>
                                </tr>
                                @foreach (@$data['debit_detail']['service'] as $key => $value)
                                    @php
                                        $value_year = substr(@$data['bill']->cycle_name, 0, -2);
                                        $value_month = substr(@$data['bill']->cycle_name, 4);
                                        $apartmentServicePrice = @$value->bdc_apartment_service_price_id != 0 ? App\Models\BdcApartmentServicePrice\ApartmentServicePrice::get_detail_bdc_apartment_service_price_by_apartment_id($value->bdc_apartment_service_price_id) : null;
                                        $service = @$value->bdc_apartment_service_price_id != 0 ? App\Models\Service\Service::get_detail_bdc_service_by_bdc_service_id($apartmentServicePrice->bdc_service_id) : null;
                                        $vehicle = @$apartmentServicePrice->bdc_vehicle_id > 0 ? App\Models\Vehicles\Vehicles::get_detail_vehicle_by_id($apartmentServicePrice->bdc_vehicle_id) : null;
                                    @endphp
                                    <tr>
                                        <td></td>
                                        <td class="padding-tb">
                                            {{ @$service->name . ' (T' . $value_month . '/' . $value_year . ')' }}</td>
                                        @if (@$service->type == 2 && $apartmentServicePrice->floor_price > 0)
                                            <td class="padding-tb" style="text-align: center;">M2</td>
                                            <td class="padding-tb" style="text-align: center;">
                                                {{ @$data['apartment']->area }}
                                            </td>
                                            <td class="padding-tb" style="text-align: right;padding-right: 5px;">
                                                {{ number_format($apartmentServicePrice->floor_price) }}
                                            </td>
                                        @else
                                            <td class="padding-tb" style="text-align: center;"></td>
                                            <td class="padding-tb" style="text-align: center;"></td>
                                            <td class="padding-tb" style="text-align: right;padding-right: 5px;">
                                                {{ number_format($apartmentServicePrice->floor_price) }}
                                            </td>
                                        @endif
                                        <td class="padding-tb" style="text-align: right;padding-right: 5px;">
                                            {{ number_format($value->sumery + $value->discount) }}</td>
                                        <td class="padding-tb" style="text-align: center;"></td>
                                        <td class="padding-tb" style="text-align: right;padding-right: 5px;"></td>
                                    </tr>
                                @endforeach
                            @endif
                            @if (count(@$data['debit_detail']['vehicle']) > 0)
                                <?php
                                $totalDv = 0;
                                $totalDc = 0;
                                ?>
                                @foreach (@$data['debit_detail']['vehicle'] as $key => $value)
                                    @php
                                        $totalDiscountVehicle += $value->discount;
                                        $totalVehicle += $value->sumery;
                                        $paid_total += $value->paid;
                                        $paid_vehicle_total += $value->paid;
                                        $totalDv += $value->sumery;
                                        $totalDc += $value->discount;
                                    @endphp
                                @endforeach
                                <tr>
                                    <td class="padding-tb"> <strong>A4</strong></td>
                                    <td colspan="3" class="padding-tb"><b>Phí gửi xe</b></td>
                                    <td class="padding-tb" style="text-align: right;padding-right: 5px;">
                                        <strong></strong></td>
                                    <td class="padding-tb" style="text-align: right;padding-right: 5px;">
                                        <strong>{{ number_format($totalDv + $totalDc) }}</strong></td>
                                    <td style="text-align: center;padding-right: 5px;"><strong>10%</strong></td>
                                    <td class="padding-tb" style="text-align: right;padding-right: 5px;">
                                        <strong>{{ number_format($totalDv) }}</strong></td>
                                </tr>
                                @foreach (@$data['debit_detail']['vehicle'] as $key => $value)
                                    @php
                                        $vehicle_year = substr(@$data['bill']->cycle_name, 0, -2);
                                        $vehicle_month = substr(@$data['bill']->cycle_name, 4);
                                        $apartmentServicePrice = @$value->bdc_apartment_service_price_id != 0 ? App\Models\BdcApartmentServicePrice\ApartmentServicePrice::get_detail_bdc_apartment_service_price_by_apartment_id($value->bdc_apartment_service_price_id) : null;
                                        $service = @$value->bdc_apartment_service_price_id != 0 ? App\Models\Service\Service::get_detail_bdc_service_by_bdc_service_id($apartmentServicePrice->bdc_service_id) : null;
                                        $vehicle = @$apartmentServicePrice->bdc_vehicle_id > 0 ? App\Models\Vehicles\Vehicles::get_detail_vehicle_by_id($apartmentServicePrice->bdc_vehicle_id) : null;
                                        $vehicle_card = @$apartmentServicePrice->bdc_vehicle_id > 0 ? App\Models\VehicleCategory\VehicleCategory::get_detail_vehicles_category_by_id($vehicle->vehicle_category_id) : null;
                                    @endphp
                                    <tr>
                                        <td></td>
                                        <td class="padding-tb">
                                            {{@$vehicle_card->name .' '. @$vehicle->number . ' (T' . $vehicle_month . '/' . $vehicle_year . ')' }}</td>
                                        <td style="text-align: center;padding-right: 5px;">VND</td>
                                        <td></td>
                                        <td class="padding-tb" style="text-align: right;padding-right: 5px;">
                                            {{ number_format(@$apartmentServicePrice->price) }}<br /> </td>
                                        <td class="padding-tb" style="text-align: right;padding-right: 5px;">
                                            {{ number_format($value->sumery + $value->discount) }}</td>
                                        <td></td>
                                        <td class="padding-tb" style="text-align: right;padding-right: 5px;"></td>
                                    </tr>
                                @endforeach
                            @endif
                            @if (count(@$data['debit_detail']['first_price']) > 0)
                                <?php
                                $totalDv = 0;
                                $totalDc = 0;
                                ?>

                                @foreach (@$data['debit_detail']['first_price'] as $key => $value)
                                    @php
                                        $totalDiscountServiceOrther += $value->discount;
                                        $totalDc += $value->discount;
                                        $totalDv += $value->sumery;
                                        $totalServiceOrther += $value->sumery;
                                        $paid_total += $value->paid;
                                    @endphp
                                @endforeach
                                <tr>
                                    <td class="padding-tb"> <strong>A5</strong></td>
                                    <td colspan="3" class="padding-tb"><b>Dịch vụ khác</b></td>
                                    <td class="padding-tb" style="text-align: right;padding-right: 5px;">
                                        <strong></strong></td>
                                    <td class="padding-tb" style="text-align: right;padding-right: 5px;">
                                        <strong>{{ number_format($totalDv + $totalDc) }}</strong></td>
                                    <td style="text-align: center;padding-right: 5px;"><strong>10%</strong></td>
                                    <td class="padding-tb" style="text-align: right;padding-right: 5px;">
                                        <strong>{{ number_format($totalDv) }}</strong></td>
                                </tr>
                                @foreach (@$data['debit_detail']['first_price'] as $key => $value)
                                    @php
                                        $apartmentServicePrice = @$value->bdc_apartment_service_price_id != 0 ? App\Models\BdcApartmentServicePrice\ApartmentServicePrice::get_detail_bdc_apartment_service_price_by_apartment_id($value->bdc_apartment_service_price_id) : null;
                                        $service = @$value->bdc_apartment_service_price_id != 0 ? App\Models\Service\Service::get_detail_bdc_service_by_bdc_service_id($apartmentServicePrice->bdc_service_id) : null;
                                        $vehicle = @$apartmentServicePrice->bdc_vehicle_id > 0 ? App\Models\Vehicles\Vehicles::get_detail_vehicle_by_id($apartmentServicePrice->bdc_vehicle_id) : null;
                                    @endphp
                                    <tr>
                                        <td></td>
                                        <td class="padding-tb">- {{ @$service->name }}</td>
                                        <td class="padding-tb" style="text-align: center;">
                                        <td class="padding-tb" style="text-align: center;"></td>
                                        <td class="padding-tb" style="text-align: right;padding-right: 5px;"></td>
                                        <td class="padding-tb" style="text-align: right;padding-right: 5px;">
                                            {{ number_format($value->sumery) }}</td>
                                        <td></td>
                                        <td></td>
                                    </tr>
                                @endforeach
                            @endif
                            <tr>
                                @php
                                    $no_cu = App\Repositories\BdcV2DebitDetail\DebitDetailRepository::getTotalSumeryByCycleNameCus(@$data['building']->id, @$data['apartment']->id, $data['bill']->cycle_name, $oper = '<',false,false);
                                    $no = @$no_cu ? $no_cu->tong_phat_sinh - $no_cu->tong_thanh_toan : 0;
                                    $excess_money = App\Repositories\BdcCoin\BdcCoinRepository::getCoinByTypeService(@$data['apartment']->id);
                                    $total_excess_money = 0;
                                    if ($excess_money) {
                                        $total_excess_money = $paid_service_total + $paid_vehicle_total + $paid_meter_total + $paid_electric_total;
                                    }
                                @endphp
                                <td class="padding-tb"><b>B</b></td>
                                <td colspan="6" class="padding-tb"><b>PHÍ CHƯA THANH TOÁN | OUTSTANDING FEES</b>
                                </td>
                                <td class="padding-tb" style="text-align: right;padding-right: 5px;">
                                    <strong>{{ number_format($no) }}</strong></td>
                            </tr>
                            <tr>
                                <td class="padding-tb"><b>C</b></td>
                                <td colspan="6" class="padding-tb"><b>PHÍ ĐÃ THANH TOÁN TRƯỚC | PAID AMOUNT</b>
                                </td>
                                <td class="padding-tb" style="text-align: right;padding-right: 5px;"><strong>
                                        {{ number_format($total_excess_money) }}</strong></td>
                            </tr>
                            @if (@$excess_money)
                                @foreach ($excess_money as $key => $value)
                                    <tr>
                                        <td></td>
                                        <td class="padding-tb">{{ $key }}</td>
                                        <td class="padding-tb" style="text-align: center;padding-right: 5px;">VND</td>
                                        <td></td>
                                        <td></td>
                                        <td class="padding-tb" style="text-align: right;padding-right: 5px;">
                                            @if($key == 'Phí quản lý')
                                                {{ number_format($paid_service_total) }}
                                            @endif
                                            @if($key == 'Phí phương tiện')
                                                {{ number_format($paid_vehicle_total) }}
                                            @endif
                                            @if($key == 'Phí nước')
                                                {{ number_format($paid_meter_total) }}
                                                @endif
                                            @if($key == 'Phí điện')
                                                {{ number_format($paid_electric_total) }}
                                            @endif
                                        </td>
                                        <td></td>
                                        <td></td>
                                    </tr>
                                   
                                @endforeach
                            @endif

                            <tr>
                                <td class="padding-tb"><b>D</b></td>
                                <td colspan="6" class="padding-tb"><b>TỔNG PHÍ PHẢI THANH TOÁN (D) = (A) + (B) -
                                        (C)</b></td>
                                <td class="padding-tb" style="text-align: right;padding-right: 5px;">
                                    <strong style="font-size: 14px;">{{ number_format($totalService + $totalVehicle + $totalWaterElecttric + $totalServiceOrther + $no - $total_excess_money) }}</strong>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    {{-- @if (@$data['building']->id == 102) --}}
                    <div><strong style="text-decoration: underline;"> Ghi chú: </strong></div>
                    <div style="font-style: italic;">
                        {{-- <div><strong>- Giảm 2% thuế VAT phí điện sinh hoạt theo Nghị quyết số 43/2022/QH15 của Quốc hội ban
                            hành ngày 11/01/2022.</strong></div> --}}
                        {{-- <div><strong>- Nước sinh hoạt = 10% phí bảo vệ môi trường + 5% thuế VAT%.</strong></div>
                        <div><strong>- Phí quản lý giảm từ 9.500 đ/m2 xuống 9.200 đ/m2 căn cứ vào Hợp đồng số
                            01/2022/HĐQLVH/RHS-BQTHN ngày 28/08/2022 ký giữa Ban quản trị CNCC {{ $_building }} với
                            Công ty CP Quản lý và Khai thác BĐS Rivera Homes.</strong></div> --}}
                    </div>
                    <div> 1. Nếu Quý Cư dân đã hoàn tất việc thanh toán phí của kỳ trước (Mục B) sau ngày
                        <strong>{{ \Carbon\Carbon::parse("1-$month-$year")->subMonth(1)->endOfMonth()->format('d/m/Y') }}</strong>, xin
                        vui lòng thanh toán phí <strong>T{{ $month }}/{{ $year }}</strong> (Mục A)</div>
                    <div> 2. Giảm 2% thuế VAT phí điện sinh hoạt theo Nghị quyết số 101/2023/QH15 của Quốc hội ban hành ngày 24/06/2023.</div>

                    <div> 3. Quý Cư dân vui lòng thanh toán đúng kỳ hạn quy định, Ban quản lý tòa nhà ("BQLTN") sẽ ngưng cung
                        cấp dịch vụ tiện ích và áp dụng lãi suất 0,05%/ngày/số tiền chậm thanh toán. Quý cư dân sẽ phải
                        thanh toán chi phí kết nối lại các tiện ích bị cắt (nếu có).</div>
                    <div> 4. Mọi thắc mắc xin Quý Cư dân vui lòng liên hệ Văn phòng Ban quản lý Tòa nhà
                        <b>{{ $_building }}</b> qua số điện thoại <b>{{$data['building']->phone}}</b></div>
                    <div> 5. Quý cư dân có nhu cầu viết hóa đơn GTGT, vui lòng ghi "Phiếu đề nghị xuất hóa đơn" tại quầy
                        lễ tân trước ngày 25 hàng tháng và nhận Hóa đơn điện tử gửi qua mail vào ngày mùng 10 tháng sau.
                    </div>
                    <div> 6. Giá bán nước từ ngày 01/07/2023 áp dụng theo TB số 989/vinaco-KD của công ty CP Vinaco về việc điều chỉnh đơn giá nước sinh hoạt.</div>
                    <div> 7. Quý Cư dân có thể thanh toán bằng tiền mặt tại Quầy lễ tân Sảnh căn hộ hoặc chuyển khoản theo
                        thông tin dưới đây </div>

                    @if($data['bill']->cycle_name == 202306)
                        <div> 7. Giá bán điện từ ngày 04/05/2023 áp dụng theo Quyết định số 1.062/QĐ-BCT ngày 04/05/2023 của Bộ Công Thương về quy định giá bán điện</div>
                    @endif
                    @if (@$data['building_payment_info'])
                        <table style="width: 100%; padding: 10px" border="1">
                            <tr style="background-color: #D0D9EF">
                                <td colspan="2" style=" width: 50%;" class="padding-tb"><b>Giấy báo chuyển tiền |
                                        Remittance Advice</b></td>
                                <td class="padding-tb"><b> Thông tin chuyển khoản | Bank Transfer's Information</b></td>
                            </tr>
                            <tr>
                                <td colspan="2" class="padding-tb"><b>Tên Cư dân/Công ty | Name of Resident/Company:
                                    </b></td>
                                <td class="padding-tb"><strong> CÔNG TY CỔ PHẦN QUẢN LÝ VÀ KHAI THÁC BẤT ĐỘNG SẢN RIVERA
                                        HOMES</strong></td>
                            </tr>
                            <tr>
                                <td colspan="2" class="padding-tb"><b>Số tham chiếu | Reference No: </b></td>
                                <td class="padding-tb">Số tài khoản | Account No: <strong>{{@$data['building_payment_info']->bank_account}}</strong></td>
                            </tr>
                            <tr>
                                <td class="padding-tb"><b>Thanh toán trước ngày</b></td>
                                <td class="padding-tb" style="text-align: right;padding-right: 5px;">
                                   <strong> {{ date('d/m/Y', strtotime($data['bill']->deadline)) }}</strong></td>
                                <td class="padding-tb">Ngân hàng: <strong>{{@$data['building_payment_info']->bank_name}}</strong></td>
                            </tr>
                            <tr>
                                <td class="padding-tb"><b>Tổng cộng số tiền | Total Due</b></td>
                                <td class="padding-tb" style="text-align: right;padding-right: 5px;">
                                    <strong style="font-size: 14px;">{{ number_format($totalService + $totalVehicle + $totalWaterElecttric + $totalServiceOrther + $no - $total_excess_money) }}</strong>
                                </td>
                                <td class="padding-tb"> Địa chỉ: <strong>{{@$data['building_payment_info']->branch}}</strong> </td>
                            </tr>
                            <tr>
                                <td colspan="2" class="padding-tb"><b>Nội dung thanh toán | Purpose of Payment</b></td>
                                <td class="padding-tb"> <strong>{{ @$data['apartment']->name }} - TT PHI T{{ $month }}/{{ $year }}</strong> </td>
                            </tr>
                        </table>
                    @endif
                    <div class="text-center"><strong>Cảm ơn Quý Cư dân đã lựa chọn và đồng hành cùng {{ $_building }}
                            | Thank you for choosing {{ $_building }}. Trân trọng | Your Sincerely</strong></div>
            </div>
        </div>
    @endforeach
</body>
<style type="text/css">
    @media print {
        .pagebreak {
            page-break-before: always;
            clear: both;
            page-break-after: always;
        }
        tr td{
            page-break-inside: avoid;
        }
        /* page-break-after works, as well */
    }
   .content_text{
    line-height: 1.8;
    margin-bottom: 5px;
   }
    * {
        font-size: 10px;
    }

    @page {
        margin: 0px;
    }

    body {
        margin: 0px;
    }


    * {
        font-family: sans-serif !important;
    }

    .padding-tb {
        padding-top: 1px;
        padding-bottom: 1px;
        padding-left: 5px;
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
    .img_logo_rivera{
       width: 100px;
       height: 70px;;
    }
</style>
</html>
