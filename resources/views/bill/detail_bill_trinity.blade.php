<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <link rel="stylesheet" href="{{ url('adminLTE/css/bootstrap.min.css') }}" />
    <link rel="stylesheet" href="{{ url('adminLTE/css/bootstrap-theme.min.css') }}" />
</head>

<body>
    @foreach ($data_bill as $data)
    <div class="container pagebreak" style="margin-top: 35px;padding-right: 50px;padding-left: 50px;">
    <table style="width: 100%; margin-bottom: 20px;">
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
                    <img style="height: auto;"  src="{{$inforct}}"> </img>
                    @endif
            </td>
            <td colspan="8">
                <p style=" text-align: center; padding-top: 20px; margin: 0px; font-size: 12px;" class="text-invoice"> BAN QUẢN LÝ TÒA NHÀ TRINITY TOWER</p>
                <p style=" text-align: center;  margin: 0px; font-size: 12px;" class="text-invoice"> Địa chỉ: Số 145 Hồ Mễ Trì, P. Nhân Chính, Q. Thanh Xuân, TP. Hà Nội </p>
                <p style=" text-align: center;  margin: 0px; font-size: 12px;" class="text-invoice"> Tel: 0347.699.455 - Email: bqltnt@sol-asia.com </p>
            </td>
            <td colspan="2" style="text-align: right;width: 200px;">
                    @php
                        $infor = \App\Models\Logs::where('user_id',@$data['building']->id)->where('logs_type','logo')->first();
                        $inforct = $infor->content;
                    @endphp
                    @if(!$inforct)
                        
                    @else
                    <img style="float: right; height: auto;" 
                        src="{{$inforct}}">
                    </img>
                    @endif
            </td>
        </tr>
    </table>
        <div class="content_text">
            
            <div class="pull-left">
                <div class="text_address">
                    <span>
                    </span>
                </div>
            </div>
        </div>
        <div class="footer">
            <table style="width: 100%; padding: 10px;" >
                <tbody>
                    <tr style="height: 30px;">
                        <td colspan="8" class="padding-tb" style="text-align: left;padding-left: 5px;">
                            <strong>Chi tiết thanh toán</strong>
                        </td>
                        <td colspan="4" class="padding-tb" style="text-align: center;"> 
                            <strong> THÔNG BÁO PHÍ THÁNG {{ $month }}/{{ $year }} </strong>
                        </td>
                    </tr>
                    <tr> 
                        <td colspan="8" style="text-align: left;padding-left: 8px;">  - Thanh toán bằng tiền mặt tại: Văn phòng BQL Tòa nhà Trinity Tower </td>
                        <td colspan="4" style="text-align: left;padding-left: 7px;"> Căn hộ: {{ @@$data['apartment']->name }}</td>
                    </tr>
                    <tr> 
                        <td colspan="8" style="text-align: left;padding-left: 8px;">  - Thanh toán bằng chuyển khoản:</td>
                        <td colspan="4" style="text-align: left;padding-left: 7px;"> Khách hàng: {{ @@$data['bill']->customer_name }}</td>
                    </tr>
                    <tr> 
                        <td colspan="8" style="text-align: left;padding-left: 15px;">  + Đơn vị thụ hưởng: Công ty TNHH Tư vấn và Quản lý Bất động Sản Sol Asia </td>
                        <td colspan="4" style="text-align: left;padding-left: 7px;"> </td>
                    </tr>
                    <tr> 
                        <td colspan="8" style="text-align: left;padding-left: 15px;">  + Số tài khoản: 8575686868 </td>
                        <td colspan="4" style="text-align: left;padding-left: 7px;"> </td>
                    </tr>
                    <tr> 
                        <td colspan="8" style="text-align: left;padding-left: 15px;">  + Ngân hàng: MB - Chi nhanh Thăng Long</td>
                        <td colspan="4" style="text-align: left;padding-left: 7px;"> </td>
                    </tr>
                    <tr> 
                        <td colspan="8" style="text-align: left;padding-left: 8px;">  - Nội dung: Căn hộ {{ @@$data['apartment']->name }} - Thanh toán phí {{ $month }}/{{ $year }} </td>
                        <td colspan="4" style="text-align: left;padding-left: 7px;"> </td>
                    </tr>
                    <tr> 
                        <td colspan="8" style="text-align: left;padding-left: 8px;">  - Thời gian thanh toán: Từ {{date('d/m/Y',strtotime(@$data['bill']->created_at))}} đến ngày {{date('d/m/Y',strtotime(@$data['bill']->deadline))}} </td>
                        <td colspan="4" style="text-align: left;padding-left: 7px;"> </td>
                    </tr>
            </table>
                    <table style="width: 100%; padding: 10px;" border="1" >
                    <tr style="background-color: yellow;">
                        <td class="padding-tb" style="text-align: center;"><b>STT</b></td>
                        <td class="padding-tb" style="text-align: center;"><b>Nội Dung</b></td>
                        <td class="padding-tb" style="text-align: center;"><b>Chỉ số đầu</b></td>
                        <td class="padding-tb" style="text-align: center;"><b>Chỉ số cuối</b></td>
                        <td class="padding-tb" style="text-align: center;"><b>Đơn vị tính</b></td>
                        <td class="padding-tb" style="text-align: center;"><b>Diện tích/Số lượng</b></td>
                        <td class="padding-tb" style="text-align: center;"><b>Đơn giá</b></td>
                        <td class="padding-tb" style="text-align: center;"><b>Thành tiền</b></td>
                        <td class="padding-tb" style="text-align: center;"><b>Ghi chú</b></td>
                    </tr>
                    <!--<tr>
                        <td class="padding-tb" style="text-align: center;"><b>(1)</b></td>
                        <td class="padding-tb" style="text-align: center;"><b>(2)</b></td>
                        <td class="padding-tb" style="text-align: center;"><b>(3)</b></td>
                        <td class="padding-tb" style="text-align: center;"><b>(4)</b></td>
                        <td class="padding-tb" style="text-align: center;"><b>(5)</b></td>
                        <td class="padding-tb" style="text-align: center;"><b>(6)</b></td>
                        <td class="padding-tb" style="text-align: center;"><b>(7)</b></td>
                        <td class="padding-tb" style="text-align: center;"><b>(8)</b></td>
                        <td class="padding-tb" style="text-align: center;"><b>(9)</b></td>
                    </tr>-->
                    <?php
                    $sql="select e.bdc_price_type_id,e.floor_price, sub.quantity, sub.from_date, sub.to_date, sub.id, f.type, sum(case when cycle_name = ".@$data['bill']->cycle_name." then sumery else 0 end) as tong_phat_sinh, sum(case when cycle_name <> ".@$data['bill']->cycle_name." then 0 else paid_by_cycle_name end) as thanh_toan, ABS(sum(case when (case when cycle_name = ".@$data['bill']->cycle_name." then before_cycle_name else after_cycle_name end) < 0 then (case when cycle_name = ".@$data['bill']->cycle_name." then before_cycle_name else after_cycle_name end) else 0 end)) as du, sum(case when (case when cycle_name = ".@$data['bill']->cycle_name." then before_cycle_name else after_cycle_name end) > 0 then (case when cycle_name = ".@$data['bill']->cycle_name." then before_cycle_name else after_cycle_name end) else 0 end) as no_truoc, CASE WHEN f.type = 0 THEN 'Phí khác' WHEN f.type = 2 THEN 'Phí dịch vụ' WHEN f.type = 3 THEN 'Phí nước' WHEN f.type = 4 THEN 'Phí phương tiện' WHEN f.type = 5 THEN 'Phí điện' WHEN f.type = 6 THEN 'Phí nước nóng' WHEN f.type = 7 THEN 'Phí tiện ích' WHEN f.name is null then 'Tiền Thừa Không Chỉ Định' ELSE f.name end as name from ( SELECT t1.* FROM ( SELECT * FROM bdc_v2_debit_detail WHERE deleted_at is null AND bdc_building_id = ".@$data['building']->id." AND bdc_apartment_id = ".@$data['apartment']->id." AND cycle_name <= ".@$data['bill']->cycle_name." ) as t1 INNER JOIN ( SELECT max(cycle_name) AS cycle_name, bdc_apartment_id, bdc_apartment_service_price_id FROM bdc_v2_debit_detail WHERE deleted_at is null AND bdc_building_id = ".@$data['building']->id." AND bdc_apartment_id = ".@$data['apartment']->id." AND cycle_name <= ".@$data['bill']->cycle_name." GROUP BY bdc_apartment_id, bdc_apartment_service_price_id ) as t2 ON t1.bdc_apartment_service_price_id = t2.bdc_apartment_service_price_id AND t1.bdc_apartment_id = t2.bdc_apartment_id AND t1.cycle_name = t2.cycle_name) as sub left join bdc_coin d on sub.bdc_apartment_service_price_id = d.bdc_apartment_service_price_id and d.bdc_apartment_id = ".@$data['apartment']->id." left join bdc_apartment_service_price e on e.id = sub.bdc_apartment_service_price_id left join bdc_services f on e.bdc_service_id = f.id where `cycle_name` = '".@$data['bill']->cycle_name."' or (`cycle_name` < '".@$data['bill']->cycle_name."' and `after_cycle_name` != 0) group by f.type";
                    $result = Illuminate\Support\Facades\DB::select(Illuminate\Support\Facades\DB::raw($sql));
                    $total_phatsinh = 0;
                    $total_du = 0;
                    $total_no = 0;
                    $total_phaithu= 0;
                    $total_dathu=0;
                    foreach(@$result as $key => $datacustom){
                        if($datacustom->type === 0)
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
                        $count_stt =0;
                        $count_stt1 =0;
                    }
                    ?>
                    <tr>
                        <td class="padding-tb" style="text-align: center;"><b>I.</b></td>
                        <td class="padding-tb" style="text-align: left;padding-left: 5px;"><b>Số tiền còn phải thanh toán của kỳ trước</b></td>
                        <td class="padding-tb" style="text-align: left;padding-left: 5px;"><b></b></td>
                        <td class="padding-tb" style="text-align: left;padding-left: 5px;"><b></b></td>
                        <td class="padding-tb" style="text-align: left;padding-left: 5px;"><b></b></td>
                        <td class="padding-tb" style="text-align: left;padding-left: 5px;"><b></b></td>
                        <td class="padding-tb" style="text-align: left;padding-left: 5px;"><b></b></td>
                        <td class="padding-tb" style="text-align: right;padding-right: 5px;"><b>{{number_format($total_no)}}</b></td>
                        <td class="padding-tb" style="text-align: center;"><b></b></td>
                    </tr>
                    @foreach(@$result as $key => $showdata_no)
                    @if($showdata_no->no_truoc != 0)
                    <?php $count_stt += 1; ?>
                    <tr>
                        <td class="padding-tb" style="text-align: center;"><b>{{$count_stt}}</b></td>
                        <td class="padding-tb" style="text-align: left;padding-left: 5px;">{{$showdata_no->name}}</td>
                        <td class="padding-tb" style="text-align: left;padding-left: 5px;"><b></b></td>
                        <td class="padding-tb" style="text-align: left;padding-left: 5px;"><b></b></td>
                        <td class="padding-tb" style="text-align: left;padding-left: 5px;"><b></b></td>
                        <td class="padding-tb" style="text-align: left;padding-left: 5px;"><b></b></td>
                        <td class="padding-tb" style="text-align: left;padding-left: 5px;"><b></b></td>
                        <td class="padding-tb" style="text-align: right;padding-right: 5px;">{{number_format($showdata_no->no_truoc)}}</td>
                        <td class="padding-tb" style="text-align: left;padding-left: 5px;"><b></b></td>
                    </tr>
                    @endif
                    @endforeach
                    <tr>
                        <td class="padding-tb" style="text-align: center;"><b>II.</b></td>
                        <td class="padding-tb" style="text-align: left;padding-left: 5px;"><b>Số tiền phí tháng {{ $month }}/{{ $year }} </b></td>
                        <td class="padding-tb" style="text-align: left;padding-left: 5px;"><b></b></td>
                        <td class="padding-tb" style="text-align: left;padding-left: 5px;"><b></b></td>
                        <td class="padding-tb" style="text-align: left;padding-left: 5px;"><b></b></td>
                        <td class="padding-tb" style="text-align: left;padding-left: 5px;"><b></b></td>
                        <td class="padding-tb" style="text-align: left;padding-left: 5px;"><b></b></td>
                        <td class="padding-tb" style="text-align: right;padding-right: 5px;"><b> {{number_format($total_phatsinh)}}</b></td>
                        <td class="padding-tb" style="text-align: center;"><b></b></td>
                    </tr>
                    <!--Dich vu-->
                    <?php
                    $totalService = 0;
                    $totalVehicle = 0;
                    $totalDiscountService = 0;
                    $totalDiscountVehicle = 0;
                    $totalPrice = 0;
                    $totalDienNuocPrice = 0;
                    $paid_total = 0;
                    $count_services= 0;
                    $count_electric_water=0;
                    $count_vehicles=0;
                    $count_others=0;
                    ?>
                @if(count(@$data['debit_detail']['service']) > 0)
                    <?php
                            $totalDv =0;
                            $count_services= 1;
                        ?>

                @foreach(@$data['debit_detail']['service'] as $key => $service)
                    <?php
                    $totalDiscountService += $service->discount;
                    $totalService += $service->sumery;
                    $paid_total += $service->paid;
                    $totalDv += $service->sumery;
                    ?>
                @endforeach
                @foreach(@$data['debit_detail']['service'] as $key => $service)
                    @if(count(@$data['debit_detail']['service']) > 1)
                    <tr>    
                    <td class="padding-tb" style="text-align: center;" ><b> 1 </b>  </td>
                        <td class="padding-tb" style="text-align: left;padding-left: 5px;"> Phí Dịch Vụ </td>
                        <td class="padding-tb" style="text-align: left;padding-left: 5px;"><b></b></td>
                        <td class="padding-tb" style="text-align: left;padding-left: 5px;"><b></b></td>
                        <td class="padding-tb" style="text-align: center;"> </td>
                        <td class="padding-tb" style="text-align: center;"></td>
                        <td class="padding-tb" style="text-align: right;padding-right: 5px;"><br/></td>
                        <td class="padding-tb" style="text-align: right;padding-right: 5px;">{{ number_format($totalDv) }}</td>
                        <td class="padding-tb" style="text-align: right;padding-right: 5px;"></td>
                    </tr>
                    <tr>
                        <td class="padding-tb" style="text-align: center;" > 1.{{$key + 1}} </td>
                        <td class="padding-tb" style="text-align: left;padding-left: 5px;">
                        <div>
                            {{@$service->apartmentServicePrice->service->name }}
                        </div>
                        <div>
                            @if(@$service->apartmentServicePrice->bdc_price_type_id == 2 || @$service->apartmentServicePrice->bdc_price_type_id == 3)
                            (Từ ngày {{ date('d/m/Y', strtotime(@$service->from_date))}} đến ngày {{date('d/m/Y', strtotime($service->to_date)) }} )
                            @else
                            (Từ ngày {{ date('d/m/Y', strtotime(@$service->from_date))}} đến ngày {{date('d/m/Y', strtotime($service->to_date  . ' - 1 days')) }}  )
                            @endif
                        </div>
                        </td>
                        <td class="padding-tb" style="text-align: left;padding-left: 5px;"><b></b></td>
                        <td class="padding-tb" style="text-align: left;padding-left: 5px;"><b></b></td>
                        <td class="padding-tb" style="text-align: center;">
                            @if(@$service->apartmentServicePrice->service->type == 2 && $service->apartmentServicePrice->floor_price > 0)
                            m2
                            @endif </td>
                        <td class="padding-tb" style="text-align: center;"> {{ @$data['apartment']->area }}</td>
                        <td class="padding-tb" style="text-align: right;padding-right: 5px;">{{ number_format($service->apartmentServicePrice->floor_price) }}<br/></td>
                        <td class="padding-tb" style="text-align: right;padding-right: 5px;">{{ number_format($service->sumery) }}</td>
                        <td class="padding-tb" style="text-align: right;padding-right: 5px;"></td>
                    </tr>
                    @else
                    <tr>
                        <td class="padding-tb" style="text-align: center;" ><b> 1</b> </td>
                        <td class="padding-tb" style="text-align: left;padding-left: 5px;">
                        <div>
                            {{@$service->apartmentServicePrice->service->name }}
                        </div>
                        <div>
                            @if(@$service->apartmentServicePrice->bdc_price_type_id == 2 || @$service->apartmentServicePrice->bdc_price_type_id == 3)
                            (Từ ngày {{ date('d/m/Y', strtotime(@$service->from_date))}} đến ngày {{date('d/m/Y', strtotime($service->to_date)) }} )
                            @else
                            (Từ ngày {{ date('d/m/Y', strtotime(@$service->from_date))}} đến ngày {{date('d/m/Y', strtotime($service->to_date  . ' - 1 days')) }}  )
                            @endif
                        </div>
                        </td>
                        <td class="padding-tb" style="text-align: left;padding-left: 5px;"><b></b></td>
                        <td class="padding-tb" style="text-align: left;padding-left: 5px;"><b></b></td>
                        <td class="padding-tb" style="text-align: center;">
                            @if(@$service->apartmentServicePrice->service->type == 2 && $service->apartmentServicePrice->floor_price > 0)
                            m2
                            @endif </td>
                        <td class="padding-tb" style="text-align: center;"> {{ @$data['apartment']->area }}</td>
                        <td class="padding-tb" style="text-align: right;padding-right: 5px;">{{ number_format($service->apartmentServicePrice->floor_price) }}<br/></td>
                        <td class="padding-tb" style="text-align: right;padding-right: 5px;">{{ number_format($service->sumery) }}</td>
                        <td class="padding-tb" style="text-align: right;padding-right: 5px;"></td>
                    </tr>
                    @endif
                    @endforeach
                    @endif
                    <!--end Dịch vụ-->

                    <!-- Dien nuoc-->
                    @if(count($data['debit_detail']['other']) > 0)
                    <?php 
                        $count_electric_water=1;
                        if($count_services == 1){
                            $count_electric_water = 2;
                        } 
                    ?>
                    @foreach($data['debit_detail']['other'] as $stt => $diennuoc)
                            <?php
                                $detail = json_decode(@$diennuoc->detail);
                                $totalNumber = 0;
                                $totalDienNuocPrice += $diennuoc->sumery;
                                $paid_total += $diennuoc->paid;
                                $tong_tieu_thu = 0;
                            ?>
                    @if (@$detail->data_price)
                    @foreach (@$detail->data_detail as $key => $item)
                            <?php
                            $electric_meter = App\Models\BdcElectricMeter\ElectricMeter::find($item->id);
                            $tieu_thu = @$electric_meter->after_number - @$electric_meter->before_number;
                            $tong_tieu_thu += $tieu_thu;
                            $count_custom = @count($detail->data_detail);
                            $count_vehicles= $count_electric_water + $count_custom;
                            ?>
                            
                    @endforeach
                    <tr>
                        <td class="padding-tb" style="text-align: center;" > <b> {{$count_electric_water + $stt}}  </b></td>
                        <td style="" class="padding-tb">
                            <div>
                                {{ @$diennuoc->apartmentServicePrice->service->name }}
                            </div>
                            <div>
                            @if(@$diennuoc->apartmentServicePrice->bdc_price_type_id == 2 || @$diennuoc->apartmentServicePrice->bdc_price_type_id == 3)
                                (Từ ngày {{ date('d/m/Y', strtotime(@$diennuoc->from_date))}} đến ngày {{date('d/m/Y', strtotime(@$diennuoc->to_date)) }})
                            @else
                               (Từ ngày {{ date('d/m/Y', strtotime(@$diennuoc->from_date))}} đến ngày {{date('d/m/Y', strtotime(@$diennuoc->to_date  . ' - 1 days')) }})
                            @endif
                            </div>
                        </td>
                        <td class="padding-tb" style="text-align: center;">{{ @$electric_meter->before_number }}</td>
                        <td class="padding-tb" style="text-align: center;"> {{ @$electric_meter->after_number }}</td>
                        <td class="padding-tb" style="text-align: center;"> m3 </td>
                        <td class="padding-tb" style="text-align: center;">{{@$tong_tieu_thu}} </td>
                        <td class="padding-tb" style="text-align: center;"> </td>
                        <td class="padding-tb" style="text-align: right; padding-right: 5px;">
                        <strong>{{ number_format(@$diennuoc->sumery) }}</strong>
                        </td>
                        <td class="padding-tb" style="text-align: right; padding-right: 5px;"> </td>
                    </tr>
                        @foreach (@$detail->data_price as $key => $item)
                                <?php
                                    $totalNumber += @$item->to - @$item->from + 1;
                                    $totalPrice += @$item->total_price;
                                ?>
                        @if(count(@$detail->data_price) > 1 )
                        <tr>
                            <td class="padding-tb"></td>
                            <td class="padding-tb" style="text-align: center;"> {{ @$item->from }} - {{ $item->to }}</td>
                            <td class="padding-tb" style="text-align: right;padding-right: 5px;"></td>
                            <td class="padding-tb"style=""> </td>
                            <td class="padding-tb"style=""> </td>
                            <td class="padding-tb" style="text-align: center;"> {{ @$item->to - @$item->from + 1 }}</td>
                            <td class="padding-tb" style="text-align: right;padding-right: 5px;"> {{ number_format(@$item->price) }} </td>
                            <td class="padding-tb" style="text-align: right;padding-right: 5px;"> {{ number_format((@$item->to - @$item->from + 1) * @$item->price) }}</td>
                            <td class="padding-tb" style="text-align: right;padding-right: 5px;"> </td>
                        </tr> 
                        @endif
                        @endforeach
                        @endif
                    @endforeach
                    @endif
                    <!--end điện nước -->
                    <!--Phí Phương tiện-->
                    @foreach(@$data['debit_detail']['vehicle'] as $key => $service)
                    <?php
                        $totalVehicle += $service->sumery;
                    ?>
                    @endforeach
                    @if(count(@$data['debit_detail']['vehicle']) > 1 )
                    <tr>
                    <td class="padding-tb" style="text-align: center;" ><b> {{$count_vehicles+ 1}}</b>  </td>
                        <td class="padding-tb" style="text-align: left;padding-left: 5px;">
                             Phí phương tiện tháng {{ $month }}/{{ $year }}
                        </td>
                        <td class="padding-tb" style="text-align: right;padding-right: 5px;"> </td>
                        <td class="padding-tb" style="text-align: right;padding-right: 5px;"> </td>
                        <td class="padding-tb" style="text-align: center;">  </td>
                        <td class="padding-tb" style="text-align: center;">  </td>
                        <td class="padding-tb" style="text-align: right;padding-right: 5px;"> </td>
                        <td class="padding-tb" style="text-align: right;padding-right: 5px;"> {{number_format($totalVehicle)}} </td>
                        <td class="padding-tb" style="text-align: right;padding-right: 5px;"> </td>
                    </tr>
                    @endif
                    @foreach(@$data['debit_detail']['vehicle'] as $key => $service)
                    <tr>
                        @if(count(@$data['debit_detail']['vehicle']) > 1 )
                        <td class="padding-tb" style="text-align: center;" > {{$count_vehicles+ 1}}.{{ $key + 1}} </td>
                        @else
                        <td class="padding-tb" style="text-align: center;" ><b> {{$count_vehicles + $key + 1}}</b>  </td>
                        @endif
                        <td class="padding-tb" style="text-align: left;padding-left: 5px;">
                            <div> 
                             {{ @$service->apartmentServicePrice->service->name }}
                            </div>
                            <div>
                                @if(@$service->apartmentServicePrice->bdc_price_type_id == 2 || @$service->apartmentServicePrice->bdc_price_type_id == 3)
                                (Từ ngày {{ date('d/m/Y', strtotime(@$service->from_date))}} đến ngày {{date('d/m/Y', strtotime($service->to_date)) }} )
                                @else
                                (Từ ngày {{ date('d/m/Y', strtotime(@$service->from_date))}} đến ngày {{date('d/m/Y', strtotime($service->to_date  . ' - 1 days')) }}) 
                                @endif
                            </div>
                        </td>
                        <td class="padding-tb" style="text-align: right;padding-right: 5px;"> </td>
                        <td class="padding-tb" style="text-align: right;padding-right: 5px;"> </td>
                        <td class="padding-tb" style="text-align: center;"> xe </td>
                        <td class="padding-tb" style="text-align: center;"> 1 </td>
                        <td class="padding-tb" style="text-align: right;padding-right: 5px;">
                            {{ number_format(@$service->apartmentServicePrice->price) }}<br />
                        </td>
                        <td class="padding-tb" style="text-align: right;padding-right: 5px;"> {{number_format($service->sumery)}} </td>
                        <td class="padding-tb" style="text-align: right;padding-right: 5px;"> </td>
                    </tr>
                    @endforeach
                    <!--End Phương tiện-->
                    <tr>
                        <td class="padding-tb" style="text-align: center;"><b>III.</b></td>
                        <td class="padding-tb" style="text-align: left;padding-left: 5px;"><b> Số tiền đã thanh toán: </b></td>
                        <td class="padding-tb" style="text-align: left;padding-left: 5px;"><b></b></td>
                        <td class="padding-tb" style="text-align: left;padding-left: 5px;"><b></b></td>
                        <td class="padding-tb" style="text-align: left;padding-left: 5px;"><b></b></td>
                        <td class="padding-tb" style="text-align: left;padding-left: 5px;"><b></b></td>
                        <td class="padding-tb" style="text-align: left;padding-left: 5px;"><b></b></td>
                        <td class="padding-tb" style="text-align: right;padding-right: 5px;"><b> {{number_format($total_dathu)}}</b></td>
                        <td class="padding-tb" style="text-align: center;"><b></b></td>
                    </tr>
                    <tr>
                        <td class="padding-tb" style="text-align: center;"><b>IV.</b></td>
                        <td class="padding-tb" style="text-align: left;padding-left: 5px;">
                        <div>
                        <b> Số tiền còn phải thanh toán: </b>
                        </div>
                        <div>
                            <b>
                                (IV=I+II-III)
                            </b>
                        </div>
                        </td>
                        <td class="padding-tb" style="text-align: left;padding-left: 5px;"><b></b></td>
                        <td class="padding-tb" style="text-align: left;padding-left: 5px;"><b></b></td>
                        <td class="padding-tb" style="text-align: left;padding-left: 5px;"><b></b></td>
                        <td class="padding-tb" style="text-align: left;padding-left: 5px;"><b></b></td>
                        <td class="padding-tb" style="text-align: left;padding-left: 5px;"><b></b></td>
                        <td class="padding-tb" style="text-align: right;padding-right: 5px;"><b> {{number_format($total_no + $total_phatsinh - $total_dathu )}}</b></td>
                        <td class="padding-tb" style="text-align: center;"><b></b></td>
                    </tr>
        </table>
        
            
        <table style="width: 100%; padding: 10px; margin-bottom: 0px">
            <td colspan="4" class="padding-tb">
                <?php   
                $spellOut = $total_no + $total_phatsinh - $total_dathu;
                $curl = curl_init();
                curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://forum.vdevs.net/nossl/mtw.php?number='.$spellOut,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
                ));
                $response = curl_exec($curl);
                curl_close($curl);
                $data_vietsub = json_decode($response, true);
                ?>
            <p> <strong>Số tiền bằng chữ: {{$data_vietsub['result']}} </strong></p>
            <div>
                <div >
                    <p><strong> Ghi chú: </strong> </p>
                    <div>
                        <p> Quý cư dân vui lòng chụp lại giao dịch và gửi về Ban quản lý nếu chuyển khoản.</p>
                    </div>
                    <div>
                        <p> - Mọi thắc mắc xin liên hệ: Văn phòng BQL Tòa nhà Trinity Tower:</p>
                    </div>
                    <div>
                        <p>+ Địa chỉ: Số 145 Hồ Mễ Trì, P.Nhân Chính, Q.Thanh Xuân, TP Hà Nội</p>
                    </div>
                    <div>
                        <p>+ Số điện thoại: 0347699455</p>
                    </div>
                    <div>
                        <p>+ Email: bqltnt@sol-asia.com</p>
                    </div>
                </div>
            </div>
            </td> 
            <td colspan=3 class="padding-tb">
            <div id="qrgencode{{ @$data['bill']->id}}" style="text-align: center;margin-top: 15px">
            <script>
                console.log(`{{@$data['bill']->id}}`);
                function callAPI() {
                    var xhr = new XMLHttpRequest();
                    var url = 'https://apibdc.dxmb.vn/dev/createPaymentQR';
                    var apt_id = {{$data['apartment']->id}};
                    var bdc_id = {{@$data['building']->id}};
                    var bill_code = `{{ @$data['bill']->id }}`;
                    var bill_cost = {{$spellOut}};
                    var params =`apartment_id=${apt_id}&building_id=${bdc_id}&amount=${bill_cost}&list_bill%5B0%5D=${bill_code}&type_payment=bill`;
                    console.log(bill_code);
                    xhr.open('POST', url, true);
                    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                    xhr.setRequestHeader('Authorization', 'Bearer SERVERUSED=s1');
                    xhr.onreadystatechange = function() {
                        if (xhr.readyState === XMLHttpRequest.DONE) {
                            if (xhr.status === 200) {
                                var response = JSON.parse(xhr.responseText);
                                    var option = '<img  style="width: 150px;" src="' + response.data.qr_code + '"> </img>';
                                    var element = document.getElementById("qrgencode{{ @$data['bill']->id}}");
                                    element.innerHTML += option;
                            } else {
                                console.error('API request failed. Status:', xhr.status);
                            }
                        }
                    };
                    xhr.send(params);
                }
                callAPI();
            </script>
            </table>
            </div>
            @php
            $deadline = number_format( date('d',strtotime(@$data['bill']->deadline)));
            $createdAt = number_format( date('d',strtotime(@$data['bill']->created_at)));
            $diff = $deadline - $createdAt + 1 ;
            @endphp
            <hr style="border-top: 2px solid #ccc;">
            <table id="duongstyle" style="width: 100%; padding: 10px; margin-bottom: 0px">
            <tr>
                <td colspan="4" style="width: 200px;"></td>
                <td colspan="4" style="width: 200px;"></td>
                <td colspan="4" style="text-align:center;">
                    <strong style= "text-transform: uppercase;"> BAN QUẢN LÝ TÒA NHÀ TRINITY TOWER </strong>
                </td>
            </tr>
           <!-- <tr>
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
            -->
            </td>
            </table>
            <br>
        </div>
    @endforeach
</body>
</html>
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
    line-height: 1.5;
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
