@extends('backend.layouts.master')

@section('content')

<section class="content-header">
    <h1>
        Kết quả thanh toán VNPAY
    </h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.home') }}"><i class="fa fa-home"></i> Trang chủ</a></li>
        <li class="active">{{ $heading }}</li>
    </ol>
</section>
<section class="content">
    <div class="box box-primary">
        <div class="box-body ">
            <div class="header clearfix">
                <h3 class="text-muted">VNPAY RESPONSE</h3>
            </div>
            <div class="table-responsive">
                <div class="form-group">
                    <label >Mã đơn hàng:</label>

                    <label>{{$info['vnp_TxnRef']}}</label>
                </div>
                <div class="form-group">

                    <label >Số tiền:</label>
                    <label>{{$info['vnp_Amount']}}</label>
                </div>
                <div class="form-group">
                    <label >Nội dung thanh toán:</label>
                    <label>{{$info['vnp_OrderInfo']}}</label>
                </div>
                <div class="form-group">
                    <label >Mã phản hồi (vnp_ResponseCode):</label>
                    <label>{{$info['vnp_ResponseCode']}}</label>
                </div>
                <div class="form-group">
                    <label >Mã GD Tại VNPAY:</label>
                    <label>{{$info['vnp_TransactionNo']}}</label>
                </div>
                <div class="form-group">
                    <label >Mã Ngân hàng:</label>
                    <label>{{$info['vnp_BankCode']}}</label>
                </div>
                <div class="form-group">
                    <label >Thời gian thanh toán:</label>
                    <label>{{$info['vnp_PayDate']}}</label>
                </div>
                <div class="form-group">
                    <label >Kết quả:</label>
                    <label>
                        <?php
                            if ($info['vnp_ResponseCode'] == '00') {
                                echo "GD Thanh cong";
                            } else {
                                echo "GD Khong thanh cong";
                            }
                        ?>

                    </label>
                </div>
                <div class="form-group">
                    <h3>Trang sẽ tự động quay về trang quản lý phiếu thu trong <span id="counter"></span></h3>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection

@section('javascript')

<script>
    $(function () {
        var count = 30;
        var counter = document.getElementById('counter');
        setInterval(function(){
            count--;
            counter.innerHTML = count;

            if (count === 0) {
                window.location = '{{url('/').'/'.'admin/receipt'}}';
            }
        }, 1000);
    });
    sidebar('feedback');
</script>

@endsection