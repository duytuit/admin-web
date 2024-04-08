@inject('request', 'Illuminate\Http\Request')
@extends('backend.layouts.master')

@section('content')
<section class="content-header">
    <h1>
        Lịch sử gửi thông báo - thanh toán
    </h1>
    <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Trang chủ</a></li>
    </ol>
</section>

<section class="content">
    <div class="row">
        <div class="box-body">
            <div class="col-md-12">
                <!-- Custom Tabs -->
                <div class="nav-tabs-custom">
                    <ul class="nav nav-tabs">
                        <li><a data-toggle="tab" href="#savetokenfcm">Token-FCM</a></li>
                    </ul>
                    <div class="tab-content">
                        @include('history-notify.tabs.savetokenfcm')
                    </div>
                </div>
            </div>
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
    $(document).on('change', 'select[name="per_page_email"]', function () {
                $('#form-email').submit();
            });
    $(document).on('change', 'select[name="per_page_notify_app"]', function () {
                $('#form-notify-app').submit();
            });
    $(document).on('change', 'select[name="per_page_sms"]', function () {
            $('#form-sms').submit();
            });
    $(document).on('change', 'select[name="per_page_payment"]', function () {
            $('#form-payment').submit();
            })
</script>
@endsection
