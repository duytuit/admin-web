@extends('backend.layouts.master')

@section('content')

    <section class="content-header">
        <h1>
            Import Excel
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('admin.home') }}"><i class="fa fa-home"></i> Trang chủ</a></li>
            <li class="active">Import Excel</li>
        </ol>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        Tạo công nợ
                        <a href="{{ route('admin.debitlog.importDienNuoc') }}" class="btn btn-info" style="float: right;margin-bottom: 47px;">Lịch sử impport file</a>
                    </div>

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
                        <div class="form-group col-md-4">
                            {!! Form::open(['route' => ['admin.progressive.import_excel_service_post'], 'method'=> 'POST','files' => true]) !!}
                                {{ csrf_field() }}
                                <div class="form-group">
                                    <label for="ip-name">kỳ bảng kê</label>
                                    {!! Form::number('cycle_name', '', ['class' => 'form-control', 'autocomplete' => 'off']) !!}
                                </div>
                                <div class="form-group">
                                    <label for="ip-name">Ngày bắt đầu</label>
                                    {!! Form::text('from_date', '', ['class' => 'form-control date_picker', 'autocomplete' => 'off']) !!}
                                </div>
                                <div class="form-group">
                                    <label for="ip-name">Ngày kết thúc</label>
                                    {!! Form::text('to_date', '', ['class' => 'form-control date_picker', 'autocomplete' => 'off']) !!}
                                </div>
                                <div class="form-group">
                                    <label for="ip-name">Hạn thanh toán</label>
                                    {!! Form::text('deadline', '', ['class' => 'form-control date_picker', 'autocomplete' => 'off']) !!}
                                    {{-- <input type="text" name="deadline" class="form-control" placeholder=""> --}}
                                </div>
                                <div class="form-group">
                                    <label for="ip-name">Chọn file</label>
                                    <input type="file" name="file_import" id="ip-file_import">
                                </div>
                                <div class="form-group">
                                    <button type="submit" class="btn btn-sm btn-success" title="Cập nhật">
                                        <i class="fa fa-save"></i>&nbsp;&nbsp;Cập nhật
                                    </button>
                                    <a class="btn btn-sm btn-success" title="File mẫu" href="{{ route('admin.progressive.downloadtoolcongno') }}"><i class="fa fa-download"></i> File mẫu</a>
                                </div>
                                {!! Form::close() !!}
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
        $('input.date_picker').datepicker({
            autoclose: true,
            dateFormat: "yy-mm-dd"
        }).val();
    </script>

@endsection