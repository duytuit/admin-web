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
            <small>Lập phiếu chi</small>
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
                            <h4 class="text-create-recipt">Lập phiếu chi</h4>
                        </div>
                        <!-- /.box-header -->
                        <div class="box-body no-padding">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="box-body">
                                        <form role="form">
                                            <!-- select -->
                                            <div class="form-group">
                                                <label>Chọn căn hộ</label>
                                                <select class="form-control selectpicker" data-live-search="true" id="choose_apartment_phieu_chi" data-url="{{ route('api.receipts.index') }}">
                                                    <option value="0">Lựa chọn căn hộ</option>
                                                    @foreach ($apartments as $apartment)
                                                        <option value="{{ $apartment->id }}">{{ $apartment->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                                <div class="col-md-4" style="display: none">
                                    <div class="box-body">
                                        <form role="form">
                                            <!-- select -->
                                            <div class="form-group">
                                                <label>Kiểu phiếu thu</label>
                                                <select class="form-control" id="choose_type" data-url="{{ route('api.receipts.index') }}">
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
                                {{-- <div class="col-md-4">
                                    <div class="box-body">
                                        <form role="form">
                                            <!-- select -->
                                            <div class="form-group">
                                                <label>Từ ngày</label>
                                                {!! Form::text('to_date', '', ['id' => 'to_date', 'class' => 'form-control date_picker', 'placeholder' => 'Từ ngày...', 'autocomplete' => 'off', 'data-url' => route('api.receipts.index')]) !!}
                                            </div>
                                        </form>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="box-body">
                                        <form role="form">
                                            <!-- select -->
                                            <div class="form-group">
                                                <label>Đến ngày</label>
                                                {!! Form::text('from_date', '', ['id' => 'from_date', 'class' => 'form-control date_picker', 'placeholder' => 'Đến ngày...', 'autocomplete' => 'off', 'data-url' => route('api.receipts.index')]) !!}
                                            </div>
                                        </form>
                                    </div>
                                </div> --}}
                                <div class="col-md-4" style="display: none">
                                    <div class="box-body">
                                        <form role="form">
                                            <!-- select -->
                                            <div class="form-group">
                                                <label>Tham chiếu</label>
                                                <select class="form-control" id="choose_provisional_receipt" data-url="{{ route('api.receipts.index') }}">
                                                    <option value="0">Lựa chọn tham chiếu...</option>
                                                </select>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <!-- /.row -->
                        </div>
                        <!-- /.box-body -->
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            {{-- <button type="button" class="btn btn-success add_new_debit_detail" data-toggle="modal" data-target="#createDebitDetail" data-url="{{ route('api.debit.index') }}">Thêm mới công nợ</button> --}}
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
                                    <div class="table-responsive result_receipt"></div>
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
                                        <input type="text" class="form-control" name="customer_fullname" id="customer_fullname" value="">
                                        <input type="hidden" name="data_receipt" class="data_receipt" value=""/>
                                        <input type="hidden" name="data_receipt" class="service_ids" value=""/>
                                        <input type="hidden" name="building_id" class="building_id" value="{{$building_id}}"/>
                                    </div>
                                    <div class="form-group">
                                        <label for="exampleInputEmail1">Địa chỉ</label>
                                        <input type="text" class="form-control" name="customer_address" id="customer_address" value="">
                                    </div>
                                    <div class="form-group" style="display:none">
                                        <label for="exampleInputPassword1">Số tiền nộp</label>
                                        <input type="text" class="form-control" name="customer_paid_string" id="customer_paid_string" value="" readonly>
                                        <input type="hidden" class="form-control" name="customer_paid" id="customer_paid" value="" readonly>
                                    </div>
                                    <div class="form-group paid_money">
                                        <label for="exampleInputPassword1">Số chi trả</label>
                                        <input type="text" class="form-control" name="paid_money_string" id="paid_money_string" value="0" readonly="readonly">
                                        <input type="hidden" class="form-control" name="paid_money" id="paid_money" value="0" readonly="readonly">
                                    </div>
                                    <div class="form-group">
                                        <label>Ngày tạo</label>
                                        {!! Form::text('created_date', date("d-m-Y"), ['id' => 'created_date', 'class' => 'form-control date_picker', 'placeholder' => 'Ngày tạo...', 'autocomplete' => 'off', 'data-url' => route('api.receipts.index')]) !!}
                                    </div>
                                    <div class="form-group">
                                        <label for="">Nội dung</label>
                                        <textarea class="form-control" rows="5" name="customer_description" id="customer_description"></textarea>
                                    </div>
                                    <div class="form-group" style="display: none">
                                        <label for="">Kiểu Phiếu</label>
                                        <select class="form-control" name="type_receipt" id="type_receipt" onChange="GetNameOption(this);">
                                            <option value="phieu_chi" selected="selected">Phiếu Chi</option>
                                            <option value="phieu_thu">Phiếu Thu</option>
                                            <option value="phieu_bao_co">Phiếu Báo Có</option>
                                            <option value="phieu_ke_toan">Phiếu Kế Toán</option>
                                        </select>
                                    </div>
                                    <div class="form-group form-horizontal">
                                        <div class="radio">
                                            <label>
                                                <input type="radio" name="customer_payments" id="optionsRadios1" value="tien_mat" checked>
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
                                         {{-- <div class="list-bank">
                                             <label for="">Bank</label>
                                                <select name="bank" id="bank" class="form-control">
                                                    <option value="" selected>Chọn</option>
                                                        @foreach ($banks as $value)
                                                            <option value="{{ $value }}"> {{ $value }}</option>
                                                        @endforeach
                                                </select>
                                         </div> --}}
                                    </div>
                                    <div class="form-group row">
                                        <div class="col-md-4">
                                            <button type="button" class="btn bg-gray print_and_collect_money" data-url="{{ route('api.receipts.index') }}" data-url-main="{{ route('admin.v2.receipt.index') }}">Chi và In</button>
                                        </div>
                                        <div class="col-md-4">
                                            <button type="button" class="btn bg-gray collect_money" data-url="{{ route('api.receipts.index') }}" data-url-main="{{ route('admin.v2.receipt.index') }}">Chi trả</button>
                                        </div>
                                    </div>
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
    var sel = document.getElementById("type_receipt");
    var text= sel.options[sel.selectedIndex].text;
    if (text=='Phiếu Thu') {
        radiobtn = document.getElementById("optionsRadios1");
        radiobtn.checked = true;
    }
    function GetNameOption(element){
        var text= element.options[element.selectedIndex].text;
        if (text=='Phiếu Thu'||text=='Phiếu Kế Toán') {
            radiobtn = document.getElementById("optionsRadios1");
            radiobtn.checked = true;
        }else{
            radiobtn = document.getElementById("optionsRadios2");
            radiobtn.checked = true;
        }
    }
     
    $(document).ready(function () {
        $('.list-bank').css('display','none');
        $('.form-horizontal').change(function () {
                radio1_btn = document.getElementById("optionsRadios1");
                radio2_btn = document.getElementById("optionsRadios2");
                radio3_btn = document.getElementById("optionsRadios3");
                if(radio1_btn.checked == true || radio2_btn.checked == true){
                    $('.list-bank').css('display','none');
                }
                if (radio3_btn.checked == true) {
                    $('.list-bank').css('display','block'); 
                }
        });
        
    });
    $('input.date_picker').datepicker({
        autoclose: true,
        dateFormat: "dd-mm-yy"
    }).val();
</script>
<script type="text/javascript" src="{{ url('adminLTE/js/function_dxmb.js') . "?v=" . \Carbon\Carbon::now()->timestamp }}"></script>
<script type="text/javascript" src="{{ url('adminLTE/js/debit.js') . "?v=" . \Carbon\Carbon::now()->timestamp }}"></script>
<script type="text/javascript" src="{{ url('adminLTE/js/validate-form-dxmb.js') . "?v=" . \Carbon\Carbon::now()->timestamp }}"></script>
@endsection