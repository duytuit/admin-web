@extends('backend.layouts.master')

@section('content')
    <section class="content-header">
        <h1>
            Quản lý kế toán
            <small>Chi tiết bảng kê</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('admin.home') }}"><i class="fa fa-home"></i> Trang chủ</a></li>
            <li class="active">Chi tiết bảng kê</li>
        </ol>
    </section>

    <section class="content">
        <div class="box box-primary">
            <div class="box-body font-weight-bold">
                <h3>Chi tiết bảng kê
                    <small>(Đã xử lý công nợ kì hiện tại - <i class="text-red"
                                                              style="font-weight: bolder">{{\Carbon\Carbon::now()->month}}
                            /{{\Carbon\Carbon::now()->year}}</i>)
                    </small>
                </h3>
            </div>
            <div class="box-body">
                {{-- @include('layouts.head-building') --}}
            </div>
            <section class="invoice border-bill">
                <!-- title row -->
                <div class="row">
                    <div class="col-xs-12">
                        <h2 class="page-header text-center">
                            Thông tin bảng kê
                        </h2>
                    </div>
                    <!-- /.col -->
                </div>
                <!-- info row -->
                <div class="table-responsive" style="padding-left: 100px">
                    <table width="75%">
                        <thead>
                        <tr>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td align="left"  width="50%">Mã hóa đơn: <b>{{ @$bill->bill_code }}</b></td>
                            <td align="left">Kỳ: <b>{{ @$bill->cycle_name }}</b></td>
                            <td align="left" width="20%">Hạn thanh toán: <b>{{ date('d/m/Y', strtotime(@$bill->deadline)) }}</b></td>
                        </tr>
                        </tbody>
                    </table>
                    <br>
                    <table width="75%">
                        <thead>
                        <tr>
                            <td></td>
                            <td></td>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td align="left" width="50%">Căn hộ: <b>{{ $bill->apartment->name }}</b></td>
                            <td align="left">Chủ hộ: <b>{{ $bill->customer_name }}</b></td>
                        </tr>
                        </tbody>
                    </table>
                </div>
                <!-- /.row -->
                @php
                $totalService = 0;
                $totalVehicle = 0;
                $totalPrice = 0;
                $totalDienNuocPrice = 0;
                @endphp
                <!-- Table row -->
                <div class="row border-bill-detail">
                    @if(count($debit_detail['service']) > 0)
                    <div class="box-body box-solid">
                        <div class="box-header with-border">
                            <h3 class="box-title">I: Phí dịch vụ </h3>
                        </div>
                        <div class="col-xs-12 table-responsive">
                            <table class="table table-striped">
                                <thead class="bg-light-blue">
                                <tr>
                                    <th>STT</th>
                                    <th>Dịch vụ</th>
                                    <th>Chi tiết</th>
                                    <th>Đơn giá</th>
                                    <th>SL</th>
                                    <th>Tổng</th>
                                    <th>Giảm trừ</th>
                                    <th>Thành tiền</th>
                                    <th>Nợ cũ</th>
                                    <th>Ghi chú</th>
                                </tr>
                                </thead>
                                <tbody>
                                    <?php
                                            $totalDv =0;
                                    ?>
                                @foreach($debit_detail['service'] as $key => $service)
                                    <?php
                                            $totalDv = $service->sumery;
                                            $totalService += $totalDv;
                                    ?>
                                <tr>
                                    <td><i class="fa fa-times-circle-o" style="color: red"></i> {{ $key + 1 }}</td>
                                    <td>{{ @$service->apartmentServicePrice->service->name }}</td>
                                    <td>{{ @$service->apartmentServicePrice->name }}</td>
                                    <td class="price-service" style="text-align: left;" >{{ number_format($service->price) }}</td>
                                    <td class="amount-service">{{ $service->quantity }}</td>
                                    <td >{{ number_format($service->sumery + $service->discount) }}</td>
                                    <td style="text-align: left;">{{ number_format($service->discount) }}</td>
                                    <td style="text-align: left;">{{ number_format($service->sumery) }}</td>
                                    <td style="text-align: left;">{{ number_format($service->previous_owed) }}</td>
                                    <td>{{ date('d/m/Y', strtotime($service->from_date)).' - '.date('d/m/Y', strtotime($service->to_date . ' - 1 days')) }}</td>
                                </tr style="text-align: left;">
                                @endforeach
                                <tr>
                                    <td></td>
                                    <td><b>Tổng</b></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td style="text-align: right;"><b>{{ number_format($totalService) }}</b> <b>VND</b></td>
                                    <td></td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @endif
                     @if(count($debit_detail['first_price']) > 0)
                    <div class="box-body box-solid">
                        <div class="box-header with-border">
                            <h3 class="box-title">II: Phí dịch vụ</h3>
                        </div>
                        <div class="col-xs-12 table-responsive">
                            <table class="table table-striped">
                                <thead class="bg-light-blue">
                                <tr>
                                    <th>STT</th>
                                    <th>Dịch vụ</th>
                                    <th>Chi tiết</th>
                                    <th>Đơn giá</th>
                                    <th>SL</th>
                                    <th>Tổng</th>
                                    <th>Giảm trừ</th>
                                    <th>Thành tiền</th>
                                    <th>Nợ cũ</th>
                                    <th>Ghi chú</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php
                                    $totalDv =0;
                                ?>
                                @foreach($debit_detail['first_price'] as $key => $service)
                                <?php
                                        $totalDv += $service->sumery;
                                        $totalService += $totalDv;
                                ?>
                                <tr>
                                    <td><i class="fa fa-times-circle-o" style="color: red"></i> {{ $key + 1 }}</td>
                                    <td>{{ @$service->apartmentServicePrice->service->name }}</td>
                                    <td>{{ @$service->apartmentServicePrice->name }}</td>
                                    <td class="price-service"  >{{ number_format($service->price) }}</td>
                                    <td class="amount-service">{{ $service->quantity }}</td>
                                    <td >{{ number_format($service->sumery + $service->discount) }}</td>
                                    <td style="text-align: left;">{{ number_format($service->discount) }}</td>
                                    <td style="text-align: left;">{{ number_format($totalDv) }}</td>
                                    <td style="text-align: left;">{{ number_format($service->previous_owed) }}</td>
                                    <td>{{ date('d/m/Y', strtotime($service->from_date)).' - '.date('d/m/Y', strtotime($service->to_date . ' - 1 days')) }}</td>
                                </tr style="text-align: right;">
                                @endforeach
                                <tr>
                                    <td></td>
                                    <td><b>Tổng</b></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td style="text-align: right;"><b>{{ number_format($totalDv) }}</b> <b>VND</b></td>
                                    <td></td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @endif
                    <!-- /.col -->
                </div>
                <div class="row border-bill-detail">
                    @if(count($debit_detail['vehicle']) > 0)
                    <div class="box-body box-solid">
                        <div class="box-header with-border">
                            <h3 class="box-title">III: Phí gửi xe</h3>
                        </div>
                        <div class="col-xs-12 table-responsive">
                            <table class="table table-striped">
                                <thead class="bg-light-blue">
                                <tr>
                                    <th>STT</th>
                                    <th>Dịch vụ</th>
                                    <th>Chi tiết</th>
                                    <th>Đơn giá</th>
                                    <th>SL</th>
                                    <th>Tổng</th>
                                    <th>Giảm trừ</th>
                                    <th>Thành tiền</th>
                                    <th>Nợ cũ</th>
                                    <th>Ghi chú</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php
                                    $totalDv =0;
                                ?>
                                @foreach($debit_detail['vehicle'] as $key => $service)
                                    <?php
                                            $totalDv = $service->sumery;
                                            $totalVehicle += $totalDv;
                                    ?>
                                    <tr>
                                        <td><i class="fa fa-times-circle-o" style="color: red"></i>{{ $key + 1 }}</td>
                                        <td>{{ @$service->apartmentServicePrice->service->name }}</td>
                                        <td>{{@$service->apartmentServicePrice->vehicle ? @$service->apartmentServicePrice->name.' - '.@$service->apartmentServicePrice->vehicle->number : @$service->apartmentServicePrice->name }}</td>
                                        <td class="price-service">{{ number_format($service->price) }}</td>
                                        <td class="amount-service">{{ $service->quantity }}</td>
                                        <td >{{ number_format($service->sumery + $service->discount) }}</td>
                                        <td style="text-align: left;">{{ number_format($service->discount) }}</td>
                                        <td style="text-align: left;">{{ number_format($totalDv) }}</td>
                                        <td style="text-align: left;">{{ number_format($service->previous_owed) }}</td>
                                        <td>{{ date('d/m/Y', strtotime($service->from_date)).' - '.date('d/m/Y', strtotime($service->to_date . ' - 1 days')) }}</td>
                                    </tr>
                                @endforeach
                                <tr>
                                    <td></td>
                                    <td><b>Tổng</b></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td><b>{{ number_format($totalVehicle) }}</b> <b>VND</b></td>
                                    <td></td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @endif
                    <!-- /.col -->
                </div>
                <div class="row border-bill-detail">
                    @if(count($debit_detail['other']) > 0)
                            <div class="box-body box-solid">
                                @foreach($debit_detail['other'] as $diennuoc)
                                <?php
                                    $detail = json_decode(@$diennuoc->detail) ?? $diennuoc->detail;
                                    //dd($detail);
                                    $totalNumber = 0;
                                    $totalDienNuocPrice += $diennuoc->sumery;
                                ?>
                                <div class="box-header with-border">
                                    <h3 class="box-title" style="float: left;">{{ @$diennuoc->apartmentServicePrice->name }} </h3>
                                    <div style="float: right;">Từ {{date('d/m/Y', strtotime($diennuoc->from_date))}} đến {{ date('d/m/Y', strtotime($diennuoc->to_date)) }}</div>
                                </div>
                                <div class="box-header with-border">
                                    @if (isset($detail->data))
                                        <div class="col-xs-4">Chỉ số đầu : {{ @$detail->so_dau }}</div>
                                        <div class="col-xs-4">Chỉ số cuối : {{ @$detail->so_cuoi }}</div>
                                        <div class="col-xs-4">Tiêu thụ : {{ @$detail->tieu_thu }}</div>
                                    @endif
                                    @if (isset($detail->data_detail))
                                            @php
                                                $tong_tieu_thu=0;
                                            @endphp
                                        @foreach ($detail->data_detail as $key => $value)
                                            @php
                                                $electric_meter = App\Models\BdcElectricMeter\ElectricMeter::find($value->id);
                                                $tieu_thu = @$electric_meter->after_number - @$electric_meter->before_number;
                                                $tong_tieu_thu += $tieu_thu;
                                            @endphp
                                            <div>Đồng hồ : {{$key+1}}</div>
                                            <div class="col-xs-4">Chỉ số đầu : {{ @$electric_meter->before_number }}</div>
                                            <div class="col-xs-4">Chỉ số cuối : {{ @$electric_meter->after_number }}</div>
                                            <div class="col-xs-4">Tiêu thụ : {{ @$tieu_thu }}</div>
                                        @endforeach
                                    @endif
                                </div>
                                <div class="col-xs-12 table-responsive">
                                    <table class="table table-striped">
                                        <thead class="bg-light-blue">
                                        <tr>
                                            <th>STT</th>
                                            <th>Định mức</th>
                                            <th>Tiêu thụ</th>
                                            <th>Đơn giá</th>
                                            <th>Thành tiền</th>
                                            <th>Ghi chú</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                            @if (isset($detail->data))
                                                    @foreach (@$detail->data as $key => $item)
                                                    <tr>
                                                        <td>{{ $key + 1 }}</td>
                                                        <td>Từ {{ @$item->from }} - {{ $item->to }}</td>
                                                        <td>{{ @$item->to - @$item->from + 1 }}</td>
                                                        <td>{{ number_format(@$item->price, 2, '.', ',') }}</td>
                                                        <td>{{ number_format((@$item->to - @$item->from + 1)*@$item->price, 2, '.', ',') }}</td>
                                                        <td></td>
                                                    </tr>
                                                     @endforeach
                                                    <tr style="border-bottom: solid 2px darkgray;">
                                                    </tr>
                                                    <tr>
                                                        <td></td>
                                                        <td><b>Tổng</b></td>
                                                        <td colspan="2"></td>
                                                        <td><b>{{ number_format($diennuoc->sumery) }}</b></td>
                                                        <td></td>
                                                    </tr>
                                                    <tr>
                                                        <td></td>
                                                        <td><b>Giảm trừ</b></td>
                                                        <td colspan="2"></td>
                                                        <td><b>{{ number_format($diennuoc->discount) }}</b></td>
                                                        <td></td>
                                                    </tr>
                                                    <tr>
                                                        <td></td>
                                                        <td><b>Cần thanh toán</b></td>
                                                        <td colspan="2"></td>
                                                        <td><b>{{number_format($diennuoc->sumery)}}</b></td>
                                                        <td></td>
                                                    </tr>
                                            @endif
                                            @if (isset($detail->data_price))
                                                    @foreach (@$detail->data_price as $key => $item)
                                                    <tr>
                                                        <td>{{ $key + 1 }}</td>
                                                        <td>Từ {{ @$item->from }} - {{ $item->to }}</td>
                                                        <td>{{ @$item->to - @$item->from + 1 }}</td>
                                                        <td>{{ number_format(@$item->price, 2, '.', ',') }}</td>
                                                        <td>{{ number_format((@$item->to - @$item->from + 1)*@$item->price, 2, '.', ',') }}</td>
                                                        <td></td>
                                                    </tr>
                                                     @endforeach
                                                    <tr style="border-bottom: solid 2px darkgray;">
                                                    </tr>
                                                    <tr>
                                                        <td></td>
                                                        <td><b>Tổng</b></td>
                                                        <td>{{$tong_tieu_thu}}</td>
                                                        <td></td>
                                                        <td><b>{{ number_format($detail->total) }}</b></td>
                                                        <td></td>
                                                    </tr>
                                                    <tr>
                                                        <td></td>
                                                        <td><b>Giảm trừ</b></td>
                                                        <td colspan="2"></td>
                                                        <td><b>{{ number_format($diennuoc->discount) }}</b></td>
                                                        <td></td>
                                                    </tr>
                                                    <tr>
                                                        <td></td>
                                                        <td><b>Cần thanh toán</b></td>
                                                        <td colspan="2"></td>
                                                        <td><b>{{number_format($diennuoc->sumery)}}</b></td>
                                                        <td></td>
                                                    </tr>
                                            @endif
                                        </tbody>
                                    </table>
                                </div>
                             @endforeach
                        </div>
                    @endif
                </div>
                <div class="row">
                    <div class="box-body box-solid text-center">
                        <div class="box-header with-border">
                            <h4 class="box-title"><b>Tổng bảng kê:</b> <b>{{ number_format($totalService + $totalVehicle + $totalDienNuocPrice) }}</b></h4>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </section>
@endsection
@section('javascript')
    <script>
        var request = false;
        $(document).on('click', '.edit_row', function (e) {
            e.preventDefault();
            if (!request) {
                request = true;
                var tr = $(this).parents('tr');
                var td = $(this).parents('td');
                var amount = tr.find('td.amount-service');
                var price = tr.find('td.price-service');
                amount.attr('data-value', amount.text());
                price.attr('data-value', price.text());
                amount.html(`<input type="text" class="form-control" name="amount" value="`+ amount.text() +`">`);
                price.html(`<input type="text" class="form-control" name="price" value="`+ price.text() +`">`);
                td.html(`<a href="" class="btn btn-sm btn-success save-row"></i>Lưu</a>
                          <a href="" class="btn btn-sm btn-warning cancel-row">Cancel</a>`);
            }
            request = false;
        })

        $(document).on('click', 'a.cancel-row', function (e) {
            e.preventDefault();
            if (!request) {
                request = true;
                var tr = $(this).parents('tr');
                var td = $(this).parents('td');
                var amount = tr.find('td.amount-service');
                var price = tr.find('td.price-service');
                amount.html(amount.attr('data-value'));
                price.html(price.attr('data-value'));
                td.html(`<a href="" class="btn btn-sm btn-primary edit_row"><i class="fa fa-pencil"></i> Sửa</a>`);
                request = false;
            }
        })
    </script>
@endsection