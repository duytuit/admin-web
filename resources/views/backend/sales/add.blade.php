@extends('backend.layouts.master')

@section('content')

<section class="content-header">
    <h1>
        {{ $heading }}
    </h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.home') }}"><i class="fa fa-home"></i> Trang chủ</a></li>
        <li class="active">{{ $heading }}</li>
    </ol>
</section>

<section class="content">
    <form id="form-sales" action="{{ route('admin.sales.save', ['id' => $id]) }}" method="post" autocomplete="off">
        @csrf
        @method('POST')

        @php
        $old = old();
        @endphp

        <div class="row">
            <div class="col-sm-8">
                <div class="box no-border-top">
                    <div class="box-body no-padding">
                        <div class="nav-tabs-custom no-margin">
                            <!-- Nav tabs -->
                            <ul class="nav nav-tabs">
                                <li class="active"><a href="#general" data-toggle="tab">Tổng quan</a></li>
                            </ul>
                            <!-- Tab panes -->
                            <div class="tab-content">
                                <div role="tabpanel" class="tab-pane active" id="general">
                                    <div class="form-group">
                                        <label class="control-label">Chiến dịch</label>
                                        <textarea readonly rows="1" class="form-control input-text">{{ $customer->campaign->title }}</textarea>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label">Dự án</label>
                                        <textarea readonly rows="1" class="form-control input-text">{{ $customer->campaign->project->cb_title }}</textarea>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label">Đường dẫn</label>
                                        <textarea name="alias" placeholder="Đường dẫn" rows="1" class="form-control input-text">{{ old('alias', $customer->alias) }}</textarea>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label">Mô tả</label>
                                        <textarea name="content" placeholder="Mô tả" rows="5" class="form-control mceEditor">{{ old('content', $customer->content) }}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-4">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        Thông tin
                    </div>
                    <div class="box-body">
                        <div class="form-group">
                            <label class="control-label">Icon Web</label>
                            <div class="input-group">
                                <input id="icon_web" type="text" name="icon_web" value="{{ old('icon_web', $customer->icon_web) }}" class="form-control"><span class="input-group-btn"><button type="button" class="btn btn-primary" data-font="fontawesome" data-target="#icon_web">Chọn</button></span>
                            </div>
                            <div class="icon-preview">
                                @if (old('icon_web', $customer->icon_web))
                                <span style="font-size: 32px;">
                                    <i class="fa {{ old('icon_web', $customer->icon_web) }}"></i>
                                </span>
                                @endif
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label">Icon App</label>
                            <div class="input-group">
                                <input id="icon_app" type="text" name="icon_app" value="{{ old('icon_app', $customer->icon_app) }}" class="form-control"><span class="input-group-btn"><button type="button" class="btn btn-primary" data-font="ionicons" data-target="#icon_app">Chọn</button></span>
                            </div>
                            <div class="icon-preview">
                                @if (old('icon_app', $customer->icon_app))
                                <span style="font-size: 32px;">
                                    <i class="icon ion-md-{{ old('icon_app', $customer->icon_app) }}"></i>
                                </span>
                                @endif
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label">Trạng thái</label>
                            <div>
                                @php
                                $status = ($id == 0) || ($old ? old('status') : $customer->status);
                                @endphp
                                <label class="switch">
                                    <input type="checkbox" name="status" value="1" {{ $status ? 'checked' : '' }} />
                                    <span class="slider round"></span>
                                </label>
                            </div>
                        </div>
                        <div class="form-group">
                            <button type="submit" class="btn btn-success" form="form-sales" value="submit">{{ $id ? 'Cập nhật' : 'Thêm mới' }}</button>
                            &nbsp;
                            <a href="{{ route('admin.sales.index') }}" class="btn btn-danger" form="form-sales" value="submit">Quay lại</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</section>

<div id="modal-icons" class="modal fade" data-target="">
    <div class="modal-dialog modal-lg" style="overflow: hidden;">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <div class="input-group" style="width: 280px;">
                    <input type="text" name="keyword" class="form-control" placeholder="Nhập tên icon" autocomplete="off">
                    <span class="input-group-btn">
                        <button type="button" class="btn btn-primary"><i class="fa fa-search"></i></button>
                    </span>
                </div>
            </div>
            <div class="modal-body" style="height: 450px; overflow: auto;">
                <div id="icons" class="row"></div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('stylesheet')

@endsection

@section('javascript')

<script>
    sidebar('sales', 'index');
</script>

@endsection