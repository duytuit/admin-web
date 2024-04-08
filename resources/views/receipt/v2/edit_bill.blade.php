@extends('backend.layouts.master')

@section('stylesheet')
{{-- <link rel="stylesheet" href="{{ url('adminLTE/css/datxanhcare.css') }}" /> --}}
@endsection

@section('content')
<style>
        .loader {
          border: 16px solid #f3f3f3;
          border-radius: 50%;
          border-top: 16px solid #3498db;
          width: 20px;
          height: 20px;
          -webkit-animation: spin 2s linear infinite; /* Safari */
          animation: spin 2s linear infinite;
          border-top: 16px solid blue;
          border-right: 16px solid green;
          border-bottom: 16px solid red;
          border-left: 16px solid pink;
        }

        /* Safari */
        @-webkit-keyframes spin {
          0% { -webkit-transform: rotate(0deg); }
          100% { -webkit-transform: rotate(360deg); }
        }

        @keyframes spin {
          0% { transform: rotate(0deg); }
          100% { transform: rotate(360deg); }
        }
        </style>
    <section class="content-header">
        <h1>
            Quản lý dịch vụ
            <small>Lập phiếu thu</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('admin.home') }}"><i class="fa fa-home"></i> Trang chủ</a></li>
            <li class="active">Quản lý dịch vụ</li>
        </ol>
    </section>
    <section class="content">
        <div class="box-body">
            <div class="row">
                <!-- Left col -->
                <div class="col-md-9">
                    <!-- MAP & BOX PANE -->
                    <div class="box box-primary">
                        <div class="box-header with-border text-center bg-primary">
                            <h4 class="text-create-recipt">Lập phiếu thu</h4>
                        </div>
                        <!-- /.box-header -->
                        <div class="box-body no-padding">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="box-body">
                                        <form role="form">
                                            <!-- select -->
                                            <div class="form-group">
                                                <label>Căn hộ</label>
                                                <select class="form-control" id="choose_apartment" disabled="true">
                                                    <option value="0">Lựa chọn căn hộ</option>
                                                    @foreach ($apartments as $apartment)
                                                        <option value="{{ $apartment->id }}" @if($receipt->bdc_apartment_id == $apartment->id) selected="selected" @endif>
                                                            {{ $apartment->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="box-body">
                                        <form role="form">
                                            <!-- select -->
                                            <div class="form-group">
                                                <label>Kiểu phiếu thu</label>
                                                <select class="form-control" id="choose_type" data-url="{{ route('api.receipts.index') }}" disabled="true">
                                                    <option value="1">Hóa đơn</option>
                                                    <option value="2" selected="selected">Dịch vụ</option>
                                                </select>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="box-body">
                                        <form role="form">
                                            <!-- select -->
                                            <div class="form-group">
                                                <label>Dịch vụ</label>
                                                <select class="form-control" id="choose_service" data-url="{{ route('api.receipts.index') }}">
                                                    <option value="0">Lựa chọn dịch vụ...</option>
                                                    @foreach($services as $_service)
                                                        <option value="{{$_service->id}}">{{$_service->name}}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                                <input type="hidden" id="to_date" name="to_date" value=""/>
                                <input type="hidden" id="from_date" name="from_date" value=""/>
                                <input type="hidden" id="choose_provisional_receipt" name="choose_provisional_receipt" value="0"/>
                            </div>
                            <!-- /.row -->
                        </div>
                        <!-- /.box-body -->
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <button type="button" class="btn btn-success add_new_debit_detail" data-toggle="modal" data-target="#createDebitDetail" data-url="{{ route('api.debit.index') }}">Thêm mới công nợ</button>
                        </div>
                    </div>
                    <!-- /.box -->
                    <div class="row">
                        <div class="col-md-12">
                            <!-- DIRECT CHAT -->
                            <div class="box box-primary direct-chat direct-chat-warning">
                                <div class="box-header with-border text-center bg-primary">
                                    <h4 class="text-create-recipt">Danh sách hóa đơn</h4>
                                </div>
                                <div class="box-body no-padding">
                                    <div class="table-responsive result_receipt">
                                        <table class="table no-margin">
                                            <thead>
                                                <tr>
                                                    @if($receipt->type == "phieu_thu_truoc")
                                                        <th>
                                                            {{-- <input type="checkbox" class="iCheck checkAll" data-target=".checkSingle" /> --}}
                                                        </th>
                                                    @endif
                                                    <th>Dịch vụ</th>
                                                    <th style="width: 20%">Thời gian</th>
                                                    <th>Sản phẩm</th>
                                                    <th>Phát sinh</th>
                                                    {{-- <th>Nợ cũ</th> --}}
                                                    <th>Tổng tiền</th>
                                                    <th>Thanh toán</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($debitDetails as $billDetail)
                                                    <?php
                                                        $bill = $billRepository->find($billDetail->bdc_bill_id);
                                                        // $service = $billDetail->service;
                                                        $serviceName = $billDetail->title;
                                                        $datetime = date('d/m', strtotime($billDetail->from_date)) . ' - ' . date('d/m', strtotime($billDetail->to_date));
                                                        // $datetime = $billDetail->from_date . ' - ' . $billDetail->to_date;
                                                        $sumery = $billDetail->sumery;
                                                        $previousOwed = $billDetail->previous_owed;
                                                        $totalPayment = $billDetail->is_free == 1 ? 0 : $sumery + $previousOwed;
                                                        $paid = $billDetail->paid;
                                                    ?>
                                                    <tr class="checkbox_parent">
                                                        @if($receipt->type == "phieu_thu_truoc")
                                                            <td>
                                                                @if($billDetail->new_sumery != 0 && $billDetail->is_free == 0)
                                                                    <input type="checkbox" name="ids[]" onclick="checkServiceEditBill(this)" />
                                                                @endif
                                                            </td>
                                                        @endif
                                                        <td>{{ $serviceName }}</td>
                                                        <td>{{ $datetime }}</td>
                                                        <td>{{ $serviceName }}</td>
                                                        <td style="text-align: right;">{{ number_format($sumery, 0, '', '.') }}</td>
                                                        {{-- <td>{{ number_format($previousOwed, 0, '', '.') }}</td> --}}
                                                        <td style="text-align: right;">{{ number_format($totalPayment, 0, '', '.') }}</td>
                                                        <td style="text-align: right;">
                                                            @if($billDetail->new_sumery != 0 && $billDetail->is_free == 0)
                                                                <input type="text" class="total_payment" value="{{ number_format($billDetail->new_sumery, 0, '', ',') }}"/>
                                                            @else
                                                                0 (Miễn phí)
                                                            @endif
                                                        </td>
                                                        <input type="hidden" class="bill_code" value="{{ $bill->bill_code }}"/>
                                                        <input type="hidden" class="service_id" value="{{ $billDetail->bdc_service_id }}"/>
                                                        <input type="hidden" class="apartment_service_price_id" value="{{ $billDetail->bdc_apartment_service_price_id }}"/>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    <!-- /.table-responsive -->
                                </div>
                            </div>
                            <!--/.direct-chat -->
                        </div>
                        <!-- /.col -->
                    </div>
                </div>
                <!-- /.col -->

                <div class="col-md-3">
                    <div class="box box-primary">
                        <div class="box-header with-border text-center bg-primary">
                            <h4 class="text-create-recipt">Thông tin phiếu thu</h4>
                        </div>
                        <!-- /.box-header -->
                        <div class="box-body">
                            <form role="form" id="receipt_form">
                                <div class="box-body no-padding">
                                    <div class="form-group">
                                        <label for="exampleInputEmail1">Người nộp tiền</label>
                                        <input type="text" class="form-control" name="customer_fullname" id="customer_fullname" value="{{ @$receipt->customer_name }}">
                                        <input type="hidden" name="data_receipt" class="data_receipt" value=""/>
                                        <input type="hidden" name="data_receipt" class="service_ids" value=""/>
                                        <input type="hidden" name="receipt_id" id="receipt_id" value="{{ @$id }}"/>
                                    </div>
                                    <div class="form-group">
                                        <label for="exampleInputEmail1">Địa chỉ</label>
                                        <input type="text" class="form-control" name="customer_address" id="customer_address" value="{{ @$receipt->customer_address }}">
                                    </div>
                                    <div class="form-group">
                                        <label for="exampleInputPassword1">Số tiền nộp</label>
                                        <input type="text" class="form-control" name="customer_paid_string" id="customer_paid_string" value="{{ number_format(@$receipt->cost, 0, '', ',') }}" readonly>
                                        <input type="hidden" class="form-control" name="customer_paid" id="customer_paid" value="{{ @$receipt->cost }}" readonly>
                                    </div>
                                    <div class="form-group paid_money">
                                        <label for="exampleInputPassword1">Số tiền thanh toán</label>
                                        <input type="text" class="form-control" name="paid_money_string" id="paid_money_string" value="{{ number_format(@$receipt->cost_paid, 0, '', ',') }}" readonly="readonly">
                                        <input type="hidden" class="form-control" name="paid_money" id="paid_money" value="{{ @$receipt->cost_paid }}" readonly="readonly">
                                    </div>
                                    <div class="form-group">
                                        <label for="">Nội dung</label>
                                        <textarea class="form-control" rows="5" name="customer_description" id="customer_description">{{ @$receipt->description }}</textarea>
                                    </div>
                                    <div class="form-group form-horizontal">
                                        <div class="radio">
                                            <label>
                                                <input type="radio" name="customer_payments" id="optionsRadios1" value="tien_mat" checked="">
                                                Tiền mặt
                                            </label>
                                        </div>
                                        <div class="radio">
                                            <label>
                                                <input type="radio" name="customer_payments" id="optionsRadios2" value="chuyen_khoan">
                                                Chuyển khoản
                                            </label>
                                        </div>
                                        <div class="radio">
                                            <label>
                                                <input type="radio" name="customer_payments" id="optionsRadios3" value="vnpay">
                                                VNPay
                                            </label>
                                        </div>
                                    </div>
                                    @if($receipt->type == "phieu_thu_truoc")
                                        <div class="form-group row">
                                            <div class="col-md-4">
                                                <button type="button" class="btn bg-gray update_print_and_collect_money" data-url="{{ route('api.receipts.index') }}" data-url-main="{{ route('admin.v2.receipt.index') }}">Thu và In</button>
                                            </div>
                                            <div class="col-md-4">
                                                <button type="button" class="btn bg-gray update_collect_money" data-url="{{ route('api.receipts.index') }}" data-url-main="{{ route('admin.v2.receipt.index') }}">Thu tiền</button>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <!-- /.col -->
            </div>
        </div>
    </section>
    <div class="modal fade" id="createDebitDetail" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Thêm công nợ</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body debit_detail_content"></div>
                <div class="modal-footer d-flex justify-content-center">
                <button type="button" class="btn btn-primary" id="add_debit_detail_previous" data-url="{{ route('api.debit.index') }}" data-url-receipt="{{ route('api.receipts.index') }}">Lưu</button>
                    <button type="button" class="btn btn-warning" data-dismiss="modal">Hủy</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('javascript')

<script>
    var service_ids = '';
    var billCodes = '';
    var data = '';

    $('input.date_picker').datepicker({
        autoclose: true,
        dateFormat: "dd-mm-yy"
    }).val();
</script>
<script type="text/javascript" src="{{ url('adminLTE/js/function_dxmb.js') }}"></script>
<script type="text/javascript" src="{{ url('adminLTE/js/debit.js') }}"></script>
<script type="text/javascript" src="{{ url('adminLTE/js/validate-form-dxmb.js') }}"></script>
<script type="text/javascript" src="{{ url('adminLTE/js/format-currency.js') }}"></script>
@endsection