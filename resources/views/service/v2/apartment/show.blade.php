@extends('backend.layouts.master')

@section('content')
    <section class="content-header">
        <h1>
            Quản lý
            <small>Quản lý dịch vụ</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('admin.home') }}"><i class="fa fa-home"></i> Trang chủ</a></li>
            <li class="active">Quản lý dịch vụ</li>
        </ol>
    </section>
    <section class="content">
        <div class="box box-primary">
            <div class="box-body">
                <div class="row form-group">
                    <div class="col-sm-4 col-xs-12">
                    </div>
                </div>
                <form>
                    <div class="form-group">
                        <div class="box-header with-border">
                            <h6 class="col-md-2 box-title">Căn hộ PH-202</h6>
                            <div class="col-md-2">
                                <a href="{{ route('admin.v2.service.create') }}" class="btn btn-info"><i class="fa fa-edit"></i>
                                    Thêm mới</a>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div style="text-align: center" class="row form-group">
                            <label for="inputEmail3" class="col-sm-3 control-label">Phí ô tô:</label>

                            <div class="col-sm-3">
                                <span><a href="{{ route('admin.v2.service.resident.create') }}">1.xe ô tô: 29x-12548</a></span><br>
                                <span><a href="{{ route('admin.v2.service.resident.create') }}">2.xe ô tô: 29x-12548</a></span><br>
                                <span><a href="{{ route('admin.v2.service.resident.create') }}">3.xe ô tô: 29x-12548</a></span><br>
                            </div>

                            <div class="col-sm-3">
                                <span>  <a href="#" class="btn btn-xs btn-danger"><i class="fa fa-trash"></i></a></span><br>
                                <span> <a href="#" class="btn btn-xs btn-danger"><i class="fa fa-trash"></i></a></span><br>
                                <span> <a href="#" class="btn btn-xs btn-danger"><i class="fa fa-trash"></i></a></span><br>
                            </div>
                        </div>
                        <div style="text-align: center" class="row form-group">
                            <label for="inputEmail3" class="col-sm-3 control-label">Phí ô tô:</label>

                            <div class="col-sm-3">
                                <span><a href="{{ route('admin.v2.service.resident.create') }}">1.xe ô tô: 29x-12548</a></span><br>
                                <span><a href="{{ route('admin.v2.service.resident.create') }}">2.xe ô tô: 29x-12548</a></span><br>
                                <span><a href="{{ route('admin.v2.service.resident.create') }}">3.xe ô tô: 29x-12548</a></span><br>
                            </div>

                            <div style="text-align: center" class="col-sm-3">
                                <span>  <a href="#" class="btn btn-xs btn-danger"><i class="fa fa-trash"></i></a></span><br>
                                <span> <a href="#" class="btn btn-xs btn-danger"><i class="fa fa-trash"></i></a></span><br>
                                <span> <a href="#" class="btn btn-xs btn-danger"><i class="fa fa-trash"></i></a></span><br>
                            </div>
                        </div>
                        <div style="text-align: center" class="row form-group">
                            <label for="inputEmail3" class="col-sm-3 control-label">Phí ô tô:</label>

                            <div class="col-sm-3">
                                <span><a href="{{ route('admin.v2.service.resident.create') }}">1.xe ô tô: 29x-12548</a></span><br>
                                <span><a href="{{ route('admin.v2.service.resident.create') }}">2.xe ô tô: 29x-12548</a></span><br>
                                <span><a href="{{ route('admin.v2.service.resident.create') }}">3.xe ô tô: 29x-12548</a></span><br>
                            </div>

                            <div class="col-sm-3">
                                <span>  <a href="#" class="btn btn-xs btn-danger"><i class="fa fa-trash"></i></a></span><br>
                                <span> <a href="#" class="btn btn-xs btn-danger"><i class="fa fa-trash"></i></a></span><br>
                                <span> <a href="#" class="btn btn-xs btn-danger"><i class="fa fa-trash"></i></a></span><br>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </section>
    @include('service.resident.modal_show')
@endsection

@section('javascript')
@endsection