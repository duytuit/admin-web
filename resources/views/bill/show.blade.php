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
                <br/>
                <hr>
                <!-- /.row -->
                @php
                $totalService = 0;
                $totalVehicle = 0;
                $totalPrice = 0;
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
                                    <th style="text-align: right;">STT</th>
                                    <th style="text-align: right;">Dịch vụ</th>
                                    <th style="text-align: right;">Chi tiết</th>
                                    <th style="text-align: right;">Đơn giá</th>
                                    <th style="text-align: right;">SL</th>
                                    <th style="text-align: right;">Thành tiền</th>
                                    <th style="text-align: right;">Nợ cũ</th>
                                    <th style="text-align: right;">Tổng</th>
                                    <th style="text-align: right;">Ghi chú</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($debit_detail['service'] as $key => $service)
                                <?php
                                    $totalDv = $service->is_free == 0 ? $service->sumery + $service->previous_owed : 0;
                                ?>
                                <tr>
                                    <td style="text-align: right;"><i class="fa fa-times-circle-o" style="color: red"></i> {{ $key + 1 }}</td>
                                    <td style="text-align: right;">{{ $service->apartmentServicePrice->service->name }}</td>
                                    <td style="text-align: right;">{{ $service->title }}</td>
                                    <td class="price-service" style="text-align: right;" >{{ number_format($service->price) }}</td>
                                    <td style="text-align: right;" class="amount-service">{{ $service->quantity }}</td>
                                    <td style="text-align: right;">{{ number_format($service->sumery) }}@if($service->is_free == 1) <span class="badge badge-danger">Miễn phí</span> @endif</td>
                                    <td style="text-align: right;">{{ number_format($service->previous_owed) }}</td>
                                    <td style="text-align: right;">{{ number_format($totalDv) }}</td>
                                    <td style="text-align: right;">{{ date('d/m/Y', strtotime($service->from_date)).' - '.date('d/m/Y', strtotime($service->to_date . ' - 1 days')) }}</td>
                                </tr style="text-align: right;">
                                @php
                                    $totalService += $totalDv;
                                @endphp
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
                                    <th style="text-align: right;">STT</th>
                                    <th style="text-align: right;">Dịch vụ</th>
                                    <th style="text-align: right;">Chi tiết</th>
                                    <th style="text-align: right;">Đơn giá</th>
                                    <th style="text-align: right;">SL</th>
                                    <th style="text-align: right;">Thành tiền</th>
                                    <th style="text-align: right;">Nợ cũ</th>
                                    <th style="text-align: right;">Tổng</th>
                                    <th style="text-align: right;">Ghi chú</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php
                                    $totalSerDv = 0;
                                ?>
                                @foreach($debit_detail['first_price'] as $key => $service)
                                <?php
                                    $totalDv = $service->is_free == 0 ? $service->sumery + $service->previous_owed : 0;
                                    $totalSerDv += $service->is_free == 0 ? $service->sumery + $service->previous_owed : 0;
                                ?>
                                <tr>
                                    <td style="text-align: right;"><i class="fa fa-times-circle-o" style="color: red"></i> {{ $key + 1 }}</td>
                                    <td style="text-align: right;">{{ $service->apartmentServicePrice->service->name }}</td>
                                    <td style="text-align: right;">{{ $service->title }}</td>
                                    <td style="text-align: right;" class="price-service"  >{{ number_format($service->price) }}</td>
                                    <td style="text-align: right;" class="amount-service">{{ $service->quantity }}</td>
                                    <td style="text-align: right;">{{ number_format($service->sumery) }}@if($service->is_free == 1) <span class="badge badge-danger">Miễn phí</span> @endif</td>
                                    <td style="text-align: right;">{{ number_format($service->previous_owed) }}</td>
                                    <td style="text-align: right;">{{ number_format($totalDv) }}</td>
                                    <td style="text-align: right;">{{ date('d/m/Y', strtotime($service->from_date)).' - '.date('d/m/Y', strtotime($service->to_date . ' - 1 days')) }}</td>
                                </tr style="text-align: right;">
                                @php
                                    $totalService += $totalDv;
                                @endphp
                                @endforeach
                                <tr>
                                    <td></td>
                                    <td><b>Tổng</b></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td style="text-align: right;"><b>{{ number_format($totalSerDv) }}</b> <b>VND</b></td>
                                    <td></td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @endif
                    <!-- /.col -->
                </div>
                <br>
                <div class="row border-bill-detail">
                    @if(count($debit_detail['vehicle']) > 0)
                    <div class="box-body box-solid">
                        <div class="box-header with-border">
                            <h3 class="box-title">II: Phí gửi xe</h3>
                        </div>
                        <div class="col-xs-12 table-responsive">
                            <table class="table table-striped">
                                <thead class="bg-light-blue">
                                <tr>
                                    <th style="text-align: right;">STT</th>
                                    <th style="text-align: right;">Dịch vụ</th>
                                    <th style="text-align: right;">Chi tiết</th>
                                    <th style="text-align: right;">Đơn giá</th>
                                    <th style="text-align: right;">SL</th>
                                    <th style="text-align: right;">Thành tiền</th>
                                    <th style="text-align: right;">Nợ cũ</th>
                                    <th style="text-align: right;">Tổng</th>
                                    <th style="text-align: right;">Ghi chú</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($debit_detail['vehicle'] as $key => $service)
                                    <?php
                                        $totalVi = $service->is_free == 0 ? $service->sumery + $service->previous_owed : 0;
                                    ?>
                                    <tr>
                                        <td style="text-align: right;"><i class="fa fa-times-circle-o" style="color: red"></i>{{ $key + 1 }}</td>
                                        <td style="text-align: right;">{{ $service->apartmentServicePrice->service->name }}</td>
                                        <td style="text-align: right;">{{ $service->title }}</td>
                                        <td style="text-align: right;" class="price-service">{{ number_format($service->price) }}</td>
                                        <td style="text-align: right;" class="amount-service">{{ $service->quantity }}</td>
                                        <td style="text-align: right;">{{ number_format($service->sumery) }}</td>
                                        <td style="text-align: right;">{{ number_format($service->previous_owed) }}</td>
                                        <td style="text-align: right;">{{ number_format($totalVi) }}</td>
                                        <td style="text-align: right;">{{ date('d/m/Y', strtotime($service->from_date)).' - '.date('d/m/Y', strtotime($service->to_date . ' - 1 days')) }}</td>
                                    </tr>
                                    @php
                                        // $totalVehicle += $service->new_sumery;
                                        $totalVehicle += $totalVi;
                                    @endphp
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
                <br>
                <div class="row border-bill-detail">
                    @if(count($debit_detail['other']) > 0)
                        @foreach($debit_detail['other'] as $diennuoc)
                            <?php
                                $detail = json_decode(@$diennuoc->detail) ?? $diennuoc->detail;
                                $totalNumber = 0;
                            ?>
                            <div class="box-body box-solid">
                                <div class="box-header with-border">
                                    <h3 class="box-title" style="float: left;">{{ $diennuoc->title }} </h3>
                                    <div style="float: right;">Từ {{ $diennuoc->from_date }} đến {{ $diennuoc->to_date }}</div></div>
                                </div>
                                <div class="box-header with-border">
                                    <div class="col-xs-4">Chỉ số đầu : {{ @$detail->so_dau }}</div>
                                    <div class="col-xs-4">Chỉ số cuối : {{ @$detail->so_cuoi }}</div>
                                    <div class="col-xs-4">Tiêu thụ : {{ @$detail->tieu_thu }}</div>
                                </div>
                                <div class="col-xs-12 table-responsive">
                                    <table class="table table-striped">
                                        <thead class="bg-light-blue">
                                        <tr>
                                            <th>STT</th>
                                            <th>Định mức</th>
                                            <th>SL</th>
                                            <th>Đơn giá</th>
                                            <th>Thành tiền</th>
                                            <th>Ghi chú</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                            @if (isset($detail->data))
                                                    @foreach (@$detail->data as $key => $item)
                                                    <?php
                                                        $totalNumber += @$item->to - @$item->from + 1;
                                                        $totalPrice += @$item->total_price;
                                                    ?>
                                                    <tr>
                                                        <td>{{ $key + 1 }}</td>
                                                        <td>Từ {{ @$item->from }} - {{ $item->to }}</td>
                                                        <td>{{ @$item->to - @$item->from + 1 }}</td>
                                                        <td>{{ number_format(@$item->price) }}</td>
                                                        <td>{{ number_format(@$item->total_price) }}</td>
                                                        <td></td>
                                                    </tr>
                                                @endforeach
                                                <tr>
                                                    <td></td>
                                                    <td><b>Tổng</b></td>
                                                    <td>{{ @$totalNumber }}</td>
                                                    <td></td>
                                                    <td><b>{{ number_format(@$totalPrice) }}</b></td>
                                                    <td></td>
                                                </tr>
                                            @endif
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endforeach
                    @endif
                    <!-- /.col -->
                </div>
                <div class="row">
                    <div class="box-body box-solid text-center">
                        <div class="box-header with-border">
                            <h4 class="box-title"><b>Tổng bảng kê:</b> <b>{{ number_format($totalService + $totalVehicle + $totalPrice) }}</b></h4>
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