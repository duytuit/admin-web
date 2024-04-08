@extends('backend.layouts.master')

@section('content')

<section class="content-header">
    <h1>
        Cập nhật Danh mục
    </h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.home') }}"><i class="fa fa-home"></i> Trang chủ</a></li>
        <li class="active">{{ $heading }}</li>
    </ol>
</section>

@php
$user = \Auth::user();
$per_update = $type. '.update';
@endphp

<section class="content">
    @if( in_array('admin.categories.save',@$user_access_router))
        <form id="form-categories" action="{{ route('admin.categories.save', ['id' => $id, 'type' => $type]) }}" method="post" autocomplete="off">
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
                                        @if($type == 'service')
                                            <div class="form-group {{ $errors->has('title') ? 'has-error' : '' }}">
                                                <label class="control-label required">Mã dịch vụ</label>
                                                <textarea name="title" placeholder="Mã dịch vụ" rows="1" class="form-control input-text" required>{{ old('title', $category->title) }}</textarea>
                                                @if ($errors->has('title'))
                                                <em class="help-block">{{ $errors->first('title') }}</em>
                                                @endif
                                            </div>
                                            <div class="form-group">
                                                <select name="category" class="form-control" style="width: 100%;">
                                                    @foreach($typeService as $key => $value)
                                                        <option value="{{$key}}" {{  $category->category == $key ? 'selected' : '' }}>{{$value}}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        @elseif ($type == 'receipt')
                                                <div class="form-group {{ $errors->has('title') ? 'has-error' : '' }}">
                                                    <label class="control-label required">Hình thức thu tiền</label>
                                                    <textarea name="title" placeholder="Hình thức thu tiền" rows="1" class="form-control input-text" required>{{ old('title', $category->title) }}</textarea>
                                                    @if ($errors->has('title'))
                                                    <em class="help-block">{{ $errors->first('title') }}</em>
                                                    @endif
                                                </div>
                                                <div class="form-group">
                                                    <select name="category" class="form-control" style="width: 100%;">
                                                        <option value="phieu_ke_toan" selected>Phiếu kế toán</option>
                                                        <option value="phieu_thu" {{  $category->category === 'phieu_thu' ? 'selected' : '' }}>Phiếu thu</option>
                                                        <option value="phieu_bao_co" {{  $category->category === 'phieu_bao_co' ? 'selected' : '' }}>Phiếu báo có</option>
                                                        <option value="chuyen_khoan" {{  $category->category === 'chuyen_khoan' ? 'selected' : '' }}>Chuyển khoản</option>
                                                        <option value="chuyen_khoan_auto" {{  $category->category === 'chuyen_khoan_auto' ? 'selected' : '' }}>Chuyển khoản Auto</option>
                                                        <option value="tien_mat" {{  $category->category === 'tien_mat' ? 'selected' : '' }}>Tiền mặt</option>
                                                        <option value="viet_qr" {{  $category->category === 'viet_qr' ? 'selected' : '' }}>Viet QR</option>
                                                    </select>
                                                </div>
                                        @else
                                            <div class="form-group {{ $errors->has('title') ? 'has-error' : '' }}">
                                                <label class="control-label required">Tiêu đề</label>
                                                <textarea name="title" placeholder="Tiêu đề" rows="1" class="form-control input-text" required>{{ old('title', $category->title) }}</textarea>
                                                @if ($errors->has('title'))
                                                <em class="help-block">{{ $errors->first('title') }}</em>
                                                @endif
                                            </div>
                                            <div class="form-group">
                                                <label class="control-label">Đường dẫn</label>
                                                <textarea name="alias" placeholder="Đường dẫn" rows="1" class="form-control input-text">{{ old('alias', $category->alias) }}</textarea>
                                            </div>
                                        @endif
                                        <div class="form-group">
                                            <label class="control-label">Mô tả</label>
                                            <textarea name="content" placeholder="Mô tả" rows="5" class="form-control">{{ old('content', $category->content) }}</textarea>
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
                            @if($type == 'article' || $type == 'event')
                                <div class="form-group">
                                    <label class="control-label">Icon Web</label>
                                    <div class="input-group">
                                        <input id="icon_web" type="text" name="icon_web" value="{{ old('icon_web', $category->icon_web) }}" class="form-control"><span class="input-group-btn"><button type="button" class="btn btn-primary" data-font="fontawesome" data-target="#icon_web">Chọn</button></span>
                                    </div>
                                    <div class="icon-preview">
                                        @if (old('icon_web', $category->icon_web))
                                        <span style="font-size: 32px;">
                                            <i class="fa {{ old('icon_web', $category->icon_web) }}"></i>
                                        </span>
                                        @endif
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label">Icon App</label>
                                    <div class="input-group">
                                        <input id="icon_app" type="text" name="icon_app" value="{{ old('icon_app', $category->icon_app) }}" class="form-control"><span class="input-group-btn"><button type="button" class="btn btn-primary" data-font="ionicons" data-target="#icon_app">Chọn</button></span>
                                    </div>
                                    <div class="icon-preview">
                                        @if (old('icon_app', $category->icon_app))
                                        <span style="font-size: 32px;">
                                            <i class="icon ion-md-{{ old('icon_app', $category->icon_app) }}"></i>
                                        </span>
                                        @endif
                                    </div>
                                </div>
                            @endif
                            <div class="form-group">
                                <label class="control-label">Trạng thái</label>
                                <div>
                                    @php
                                    $status = ($id == 0) || ($old ? old('status') : $category->status);
                                    @endphp
                                    <label class="switch">
                                        <input type="checkbox" name="status" value="1" {{ $status ? 'checked' : '' }} />
                                        <span class="slider round"></span>
                                    </label>
                                </div>
                            </div>
                            <div class="form-group">
                                @if( in_array('admin.categories.save',@$user_access_router))
                                    <button type="submit" class="btn btn-success" form="form-categories" value="submit">{{ $id ? 'Cập nhật' : 'Thêm mới' }}</button>
                                @endif
                                @if( in_array('admin.categories.index',@$user_access_router))
                                    <a href="{{ route('admin.categories.index',['type' => $type]) }}" class="btn btn-danger" form="form-categories" value="submit">Quay lại</a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    @endif
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

<link rel="stylesheet" href="/adminLTE/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css" />
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
<link rel="stylesheet" href="https://unpkg.com/ionicons@4.5.5/dist/css/ionicons.min.css">

@endsection

@section('javascript')

<!-- Datetime Picker -->
<script src="/adminLTE/plugins/moment/moment.min.js"></script>
<script src="/adminLTE/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js"></script>

<!-- TinyMCE -->
<script src="/adminLTE/plugins/tinymce/tinymce.min.js"></script>
<script src="/adminLTE/plugins/tinymce/config.js"></script>

<script>
    sidebar('{{ $type }}', 'category');

    var ionicons = '';
    var fontawesome = '';

    $.getJSON('/adminLTE/js/ionicons.json', function(json) {
        $.each(json, function(index, value) {
            ionicons += '<div class="col-sm-3"><div class="icon-btn" data-icon="' + value + '"><span class="icon-demo"><i class="icon ion-md-' + value + '"></i></span> <span class="icon-name">' + value + '</span></div></div>';
        });
    });

    $.getJSON('/adminLTE/js/fontawesome.json', function(json) {
        $.each(json, function(index, value) {
            fontawesome += '<div class="col-sm-3"><div class="icon-btn" data-icon="' + value + '"><span class="icon-demo"><i class="fa ' + value + '"></i></span> <span class="icon-name">' + value + '</span></div></div>';
        });
    });

    var $icons = $('#icons');
    var $modal_icons = $('#modal-icons');
    var $icon_search = $('input[name=keyword]', $modal_icons);
    var $icon_target;

    $('body').on('click', '[data-font]', function() {
        var font = $(this).data('font');
        var target = $(this).data('target');

        if (font == 'ionicons') {
            $icons.html(ionicons)
        } else if (font == 'fontawesome') {
            $icons.html(fontawesome)
        }
        $icon_target = $(target);
        $icon_search.val('');
        $modal_icons.modal('show');
    });

    $('body').on('click', '[data-icon]', function() {
        var icon = $(this).data('icon');
        $icon_target.val(icon);
        $modal_icons.modal('hide');
    });

    $icon_search.on('input change', function() {
        var keyword = $(this).val();

        $('#icons > div', $modal_icons).hide();

        $('.icon-btn', $modal_icons).each(function() {
            var icon = $(this).data('icon');
            if (icon.indexOf(keyword) > -1) {
                $(this).parent().show();
            }
        });
    });
</script>
@endsection