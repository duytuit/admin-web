@extends('backend.layouts.master')

@section('content')
    <section class="content-header">
        <h1>
            Quản lý chung
            <small>Quản lý kế toán</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('admin.home') }}"><i class="fa fa-home"></i> Trang chủ</a></li>
            <li class="active">Quản lý kế toán</li>
        </ol>
    </section>

    <section class="content">
        <div class="box box-primary">
            <div class="box-body">
                <br>
                <div class="col-md-12">
                    <ul class="nav nav-tabs">
                        <li class="active"><a data-toggle="tab" href="#quan_ly_cong_no">Quản lý công nợ</a></li>
                        <li><a data-toggle="tab" href="#quan_ly_phieu_thu">Quản lý phiếu thu</a></li>
                        <li><a data-toggle="tab" href="#quan_ly_hoa_don">Quản lý hóa đơn</a></li>
                    </ul>
                </div>
                <br>
                <div class="col-md-12">
                    <div class="tab-content">
                        <br>
                        @include('debit.index')
                        @include('receipts.index')
                        @include('bill.index')
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
@section('javascript')
    <!-- TinyMCE -->
    <script src="/adminLTE/plugins/tinymce/tinymce.min.js"></script>
    <script src="/adminLTE/plugins/tinymce/config.js"></script>
@endsection