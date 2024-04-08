@extends('backend.layouts.master')

@section('content')

    <section class="content-header">
        <h1>
            Nhập giao dịch tự hạch toán Exel
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('admin.home') }}"><i class="fa fa-home"></i> Trang chủ</a></li>
            <li class="active">Nhập giao dịch tự hạch toán Exe</li>
        </ol>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-heading">Thêm giao dịch hạch toán với Exel</div>

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
                            @if( in_array('admin.history-transaction-accounting.import_save',@$user_access_router))
                                <form action="{{ route('admin.history-transaction-accounting.import_save') }}" method="post" id="form-history-transaction-accounting" autocomplete="off" enctype="multipart/form-data">
                                    {{ csrf_field() }}
                                    <div class="form-group">
                                        <label for="ip-name">Chọn file thêm mới giao dịch hạch toán</label>
                                        <input type="file" name="file_import" id="ip-file_import">
                                    </div>
                                    <div class="form-group">
                                        <button type="submit" class="btn btn-sm btn-success" title="Cập nhật" form="form-history-transaction-accounting">
                                            <i class="fa fa-save"></i>&nbsp;&nbsp;Thêm mới
                                        </button>
                                            <a class="btn btn-sm btn-success" title="File mẫu" href="/downloads/import_giao_dich_hach_toan.xlsx"><i class="fa fa-download"></i> File mẫu</a>
                                        @if( in_array('admin.history-transaction-accounting.index',@$user_access_router))
                                            <a class="btn btn-sm btn-success" title="Danh sách căn hộ" href="{{ route('admin.history-transaction-accounting.index') }}"><i class="fa fa-reply"></i> Danh sách</a>
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
        $('input.date_picker').datepicker({
            autoclose: true,
            dateFormat: "dd-mm-yy"
       }).val();
    </script>

@endsection
