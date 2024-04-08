@extends('backend.layouts.master')

@section('content')
    <section class="content-header">
        <h1>
            Quản lý bộ phận
            <small>Chi tiết bộ phận</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('admin.home') }}"><i class="fa fa-home"></i> Trang chủ</a></li>
            <li class="active">Quản lý bộ phận</li>
        </ol>
    </section>
    <section class="content">
        <div class="box-body">
            <div class="row box box-default">
                <div class="box-header with-border">
                    <h3 class="box-title bold">Chi tiết bộ phận {{ $department->name }}</h3>
                </div>
                <br>
                <div class="col-md-12">
                    <!-- form start -->
                    <div class="box-body">
                        <ul class="list-group list-group-unbordered">
                            <li class="list-group-item">
                                <b>Tên bộ phận</b> <span class="pull-right">{{ @$department->name }}</span>
                            </li>
                            <li class="list-group-item">
                                <b>Mã bộ phận</b> <span class="pull-right">{{ @$department->code }}</span>
                            </li>
                            <li class="list-group-item">
                                <b>Số điện thoại</b> <span class="pull-right">{{ @$department->phone }}</span>
                            </li>
                            <li class="list-group-item">
                                <b>Email</b> <span
                                        class="pull-right">{{ @$department->email }}</span>
                            </li>
                            <li class="list-group-item">
                                <b>Mô tả</b> <span
                                        class="pull-right">{{ @$department->description }}</span>
                            </li>
                        </ul>
                    </div>
                    <!-- /.box -->
                </div>
            </div>
        </div>
        @include('department.staff')
{{--        @include('department.permission')--}}
        <div class="modal-insert">

        </div>
    </section>
@endsection