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
                <div class="col-md-12">
                    <div class="box box-primary">
                        <div class="box-header with-border text-center bg-primary">
                            <h4 class="text-create-recipt">Thông tin phiếu thu</h4>
                        </div>
                        <!-- /.box-header -->
                        <div class="box-body">
                            {!! Form::open(['url' =>[route('admin.v2.provisionalreceipt.store',Request::all()) ] , 'method'=> 'POST','files' => true]) !!}
                                <div class="box-body no-padding">
                                    <div class="form-group">
                                        <label>Căn hộ</label>
                                        <select class="form-control selectpicker" data-live-search="true" name="apartment_id" id="choose_apartment" data-url="{{ route('admin.apartments.ajax_get_customer') }}">
                                            <option value="">Lựa chọn căn hộ</option>
                                            @foreach ($apartments as $apartment)
                                                <option value="{{ $apartment->id }}">{{ $apartment->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="exampleInputPassword1">Mã khách hàng hóa đơn</label>
                                        <input type="text" class="form-control" name="ma_khach_hang" id="ma_khach_hang">
                                    </div>
                                    <div class="form-group">
                                        <label for="exampleInputPassword1">Tên khách hàng hóa đơn</label>
                                        <input type="text" class="form-control" name="ten_khach_hang" id="ten_khach_hang">
                                    </div>
                                    <div class="form-group">
                                        <label for="exampleInputEmail1">Danh mục</label>
                                        <select class="form-control selectpicker" data-live-search="true" name="config_id">
                                            @foreach ($configs as $_config)
                                                <option value="{{$_config->id}}">{{$_config->title}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="exampleInputEmail1">Hình thức</label>
                                        <select class="form-control selectpicker" name="payment_type">
                                            <option value="tien_mat">Tiền mặt</option>
                                            <option value="chuyen_khoan">Chuyển khoản</option>
                                            <option value="vnpay">VNPay</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="exampleInputEmail1">Ngày hạch toán</label>
                                        <input type="text" class="form-control date_picker" name="create_date" id="create_date">
                                    </div>
                                    <div class="form-group">
                                        <label for="exampleInputEmail1">Người nộp tiền</label>
                                        <input type="text" class="form-control" name="customer_fullname" id="customer_fullname" value="">
                                    </div>
                                    <div class="form-group">
                                        <label for="exampleInputPassword1">Số tiền nộp</label>
                                        <input type="text" class="form-control paid_payment" name="customer_paid" id="customer_paid" value="">
                                    </div>
                                    <div class="form-group">
                                        <label for="">Nội dung</label>
                                        <textarea class="form-control" rows="5" name="customer_description" id="customer_description"></textarea>
                                    </div>
                                    <div class="form-group row">
                                        <div class="col-md-1">
                                            <button type="submit" class="btn btn-success">Lập phiếu</button>
                                        </div>
                                        <div class="col-md-1">
                                            <a class="btn bg-gray" title="Danh sách" href="{{Request::has('type') ? route('admin.receipt.kyquy') : route('admin.v2.receipt.index')}}"> Quay lại danh sách</a>
                                        </div>
                                    </div>
                                </div>
                            {!! Form::close() !!}
                        </div>
                    </div>
                </div>
                <!-- /.col -->
            </div>
        </div>
    </section>
@endsection

@section('javascript')

<script>
     $('input.date_picker').datepicker({
        autoclose: true,
        dateFormat: "dd-mm-yy"
    }).val();
    $('input.paid_payment').on('input', function(e){  
        $(this).val(formatCurrency(this));
    }).on('keypress','input.paid_payment',function(e){
        if(!$.isNumeric(String.fromCharCode(e.which))) e.preventDefault();
    }).on('paste','input.paid_payment', function(e){   
        var cb = e.originalEvent.clipboardData || window.clipboardData;      
        if(!$.isNumeric(cb.getData('text'))) e.preventDefault();
    });
    $(document).on('change', '#choose_apartment', function (e) {
        var apartmentId = $('#choose_apartment').val();
        $('#customer_fullname').val('');
        $('#customer_address').val('');
        $('#customer_paid').val('');
        $('.data_receipt').val('');
        $.ajax({
            url: $(this).attr('data-url') + '?apartment_id=' + apartmentId,
            type: 'GET',
            success: function (response) {
                $('#customer_fullname').val(response.data.customer_name);
                $('#ma_khach_hang').val(response.data.ma_khach_hang);
                $('#ten_khach_hang').val(response.data.ten_khach_hang);
            }
        });
    });
</script>

@endsection