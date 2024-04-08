@extends('backend.layouts.master')

@section('content')

    <section class="content-header">
        <h1>
            Điện nước nhập Exel
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('admin.home') }}"><i class="fa fa-home"></i> Trang chủ</a></li>
            <li class="active">Điện nước nhập Exel</li>
        </ol>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-heading">Thêm điện nước với Exel</div>

                    <div class="panel-body">
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        <div class="form-group">
                            @if( in_array('admin.electricMeter.import_save',@$user_access_router))
                                <form action="{{ route('admin.electricMeter.import_save') }}" method="post" id="form-import-apartment" autocomplete="off" enctype="multipart/form-data">
                                    {{ csrf_field() }}
                                    <div class="form-group">
                                        <strong>Tháng chốt số </strong>
                                        <select class="input-sm cycle_month" style="box-shadow: none;
                                        border-color: #3c8dbc;" name="cycle_month">
                                            <option value="01" @if(\Carbon\Carbon::now()->month == 1) selected @endif>01</option>
                                            <option value="02" @if(\Carbon\Carbon::now()->month == 2) selected @endif>02</option>
                                            <option value="03" @if(\Carbon\Carbon::now()->month == 3) selected @endif>03</option>
                                            <option value="04" @if(\Carbon\Carbon::now()->month == 4) selected @endif>04</option>
                                            <option value="05" @if(\Carbon\Carbon::now()->month == 5) selected @endif>05</option>
                                            <option value="06" @if(\Carbon\Carbon::now()->month == 6) selected @endif>06</option>
                                            <option value="07" @if(\Carbon\Carbon::now()->month == 7) selected @endif>07</option>
                                            <option value="08" @if(\Carbon\Carbon::now()->month == 8) selected @endif>08</option>
                                            <option value="09" @if(\Carbon\Carbon::now()->month == 9) selected @endif>09</option>
                                            <option value="10" @if(\Carbon\Carbon::now()->month == 10) selected @endif>10</option>
                                            <option value="11" @if(\Carbon\Carbon::now()->month == 11) selected @endif>11</option>
                                            <option value="12" @if(\Carbon\Carbon::now()->month == 12) selected @endif>12</option>
                                        </select>
                                        /
                                        <select class="input-sm cycle_year" style="box-shadow: none;
                                        border-color: #3c8dbc;" name="cycle_year">
                                            <option value="{{\Carbon\Carbon::now()->year - 1}}">{{\Carbon\Carbon::now()->year - 1}}</option>
                                            <option value="{{\Carbon\Carbon::now()->year}}" selected="selected">{{\Carbon\Carbon::now()->year}}</option>
                                            <option value="{{\Carbon\Carbon::now()->year + 1}}">{{\Carbon\Carbon::now()->year + 1}}</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="ip-name">Chọn file thêm mới điện nước</label>
                                        <input type="file" name="file_import" id="ip-file_import">
                                    </div>
                                    <div class="form-group">
                                        <button type="submit" class="btn btn-sm btn-success" title="Cập nhật" form="form-import-apartment">
                                            <i class="fa fa-save"></i>&nbsp;&nbsp;Thêm mới
                                        </button>
                                            <a class="btn btn-sm btn-success" title="File mẫu" href="{{ $file }}"><i class="fa fa-download"></i> File mẫu</a>
                                        @if( in_array('admin.electricMeter.index',@$user_access_router))
                                            <a class="btn btn-sm btn-success" title="Danh sách điện nước" href="{{ route('admin.electricMeter.index') }}"><i class="fa fa-reply"></i> Danh sách điện nước</a>
                                        @endif
                                    </div>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('javascript')

    <script>
        sidebar('import', 'index');
    </script>

@endsection
