@extends('backend.layouts.master')

@section('content')

    <section class="content-header">
        <h3>
            Tổng Hợp SMS và Thông Báo
        </h3>
        <ol class="breadcrumb">
            <li><a href="{{ route('admin.home') }}"><i class="fa fa-home"></i> Trang chủ</a></li>
            <li class="active">Tổng hợp</li>
        </ol>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="clearfix"></div>
                <ul class="nav nav-tabs" role="tablist">
                    <li  role="tab" data-toggle="tab">Tổng hợp</a></li>
                </ul>
                <div class="tab-content">
                    <div id="general" style="padding: 15px 0;">
                        @include('Mailandsms.tabs.basic')
                        @include('Mailandsms.tabs.postlist')
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('javascript')
    <script>
        sidebar('event', 'index');
    </script>
    <script>
        $('input.date_picker').datepicker({
            autoclose: true,
            dateFormat: "dd-mm-yy"
        }).val();
    </script>
@endsection
