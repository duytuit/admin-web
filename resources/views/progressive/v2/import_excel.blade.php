@extends('backend.layouts.master')

@section('content')

    <section class="content-header">
        <h1>
            Import Excel Dịch Vụ Lũy Tiến
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('admin.home') }}"><i class="fa fa-home"></i> Trang chủ</a></li>
            <li class="active">Import Excel Dịch Vụ Lũy Tiến</li>
        </ol>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        Tạo công nợ dịch vụ lũy tiến
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
                            {!! Form::open(['route' => ['admin.v2.progressive.importexcelpost'], 'method'=> 'POST','files' => true]) !!}
                                {{ csrf_field() }}
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
                                </div>
                                <div class="form-group">
                                    <label for="ip-name">Giảm trừ</label>
                                    <div  style="display: flex;justify-content: center;">
                                        <div style="display: flex;margin-top: 5px;">
                                            <div style="display: flex;"><input type="radio" name="discount_check" value="phan_tram" class="iCheck"/> % </div>
                                            <div style="display: flex;margin-left: 10px !important;"><input type="radio" name="discount_check" value="gia_tien" class="iCheck"/> VND </div>
                                        </div>
                                        <input type="number" class="form-control" min="0"  name="discount" value="0" placeholder="Giảm trừ">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="ip-name">Chọn file</label>
                                    <input type="file" name="file_import" id="ip-file_import">
                                </div>
                                <div class="form-group">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="use_bill" value="0">
                                        <label class="form-check-label" for="exampleRadios1">
                                            Tạo mã hóa đơn mới
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="use_bill" value="1" checked>
                                        <label class="form-check-label" for="exampleRadios2">
                                            Đồng bộ mã hóa đơn công nợ hiện tại
                                        </label>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <button type="submit" class="btn btn-sm btn-success" title="Cập nhật">
                                        <i class="fa fa-save"></i>&nbsp;&nbsp;Cập nhật
                                    </button>
                                    <a class="btn btn-sm btn-success" title="File mẫu" href="https://betabdc.s-tech.info/downloads/dien_nuoc_template.xlsx"><i class="fa fa-download"></i> File mẫu</a>
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
            dateFormat: "dd-mm-yy"
        }).val();
    </script>

@endsection
