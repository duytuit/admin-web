@extends('backend.layouts.master')

@section('content')

    <section class="content-header">
        <h3>
            Tổng Lượt Xe
        </h3>
        <ol class="breadcrumb">
            <li><a href="{{ route('admin.home') }}"><i class="fa fa-home"></i> Trang chủ</a></li>
            <li class="active">Giám sát bãi xe</li>
        </ol>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="clearfix"></div>
                <ul class="nav nav-tabs" role="tablist">
                    <li  role="tab" data-toggle="tab">Giám sát bãi xe</a></li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane {{$tab==''?'active':''}}" id="general" style="padding: 15px 0;">
                        @include('vehicles.v3.tabs.basicinformation')
                        @include('vehicles.v3.tabs.vehicle')
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
        
    </script>
@endsection
