@extends('backend.layouts.master')
@inject('request', 'Illuminate\Http\Request')

@section('content')
<section class="content-header">
    <h1>
        Quản lý tòa nhà
        <small>Danh sách cấu hình</small>
    </h1>
    <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Trang chủ</a></li>
        <li class="active">Danh sách cấu hình</li>
    </ol>
</section>

<section class="content" id="content-partner">
    <div>
        <ul id="errors"></ul>
    </div>
    <form action="{{ route('admin.configs.store') }}" method="POST" id="form-create-work" enctype="multipart/form-data">
        {{ csrf_field() }}
        <div class="row">
            <div class="col-lg-8">
                <div class="box box-primary">
                    <div class="box-body">
                        <div class="form-group">
                            <div class="form-group {{ $errors->has('title') ? ' has-error' : '' }}">
                                <label for="recipient-name" class="col-form-label">Tiêu đề:</label>
                                <input type="text" name="title" class="form-control" id="recipient-name"
                                    value="{{ old('title') }}">
                                @if ($errors->has('title'))
                                <span class="help-block">
                                    <strong>{{ $errors->first('title') }}</strong>
                                </span>
                                @endif
                            </div>
                            <div class="form-group {{ $errors->has('value') ? ' has-error' : '' }}">
                                <label for="recipient-name" class="col-form-label">Giá trị:</label>
                                <input type="text" name="value" class="form-control" id="recipient-name"
                                    value="{{ old('value') }}">
                                @if ($errors->has('value'))
                                <span class="help-block">
                                    <strong>{{ $errors->first('value') }}</strong>
                                </span>
                                @endif
                            </div>
                            <div class="form-group {{ $errors->has('key') ? ' has-error' : '' }}">
                                <label for="recipient-name" class="col-form-label">Key:</label>
                                <input type="text" name="key" class="form-control" id="recipient-name"
                                    value="{{ old('key') }}">
                                @if ($errors->has('key'))
                                <span class="help-block">
                                    <strong>{{ $errors->first('key') }}</strong>
                                </span>
                                @endif
                            </div>
                            <div class="form-group {{ $errors->has('note') ? ' has-error' : '' }}">
                                <label class="control-label">Mô tả</label>
                                <textarea name="note" rows="5"
                                    class="mceEditor form-control">{{ old('note') }}</textarea>
                                @if ($errors->has('note'))
                                <span class="help-block">
                                    <strong>{{ $errors->first('note') }}</strong>
                                </span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="text-center">
                            <button type="submit" class="btn btn-primary button-js-add">Thêm mới</button>
                            <a type="button" class="btn btn-danger" href="{{ route('admin.configs.index') }}">Quay
                                lại</a>
                        </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="box box-primary">
                    <div class="box-body">

                        
                    </div>
                </div>
            </div>
        </div>
    </form>
</section>
@endsection

@section('stylesheet')

<link rel="stylesheet" href="/adminLTE/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css" />

@endsection

@section('javascript')
<script src="/adminLTE/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js"></script>
<!-- TinyMCE -->
<script src="/adminLTE/plugins/tinymce/tinymce.min.js"></script>
<script src="/adminLTE/plugins/tinymce/config.js"></script>
<script>
   
</script>
@endsection