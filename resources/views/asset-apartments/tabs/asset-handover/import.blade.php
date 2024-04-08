@extends('backend.layouts.master')

@section('content')

    <section class="content-header">
        <h1>
           Bàn giao tài sản căn hộ nhập Exel
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('admin.home') }}"><i class="fa fa-home"></i> Trang chủ</a></li>
            <li class="active">Bàn giao tài sản căn hộ nhập Exel</li>
        </ol>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-heading">Thêm bàn giao tài sản căn hộ với Exel</div>

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
                            <form action="{{ route('admin.asset-apartment.asset-handover.import_store') }}" method="post" id="form-import" autocomplete="off" enctype="multipart/form-data">
                                {{ csrf_field() }}
                                <div class="form-group">
                                    <label for="file_import">Chọn file</label>
                                    <input type="file" name="file_import" id="file_import">
                                </div>
                                <div class="form-group">
                                    <button type="submit" class="btn btn-sm btn-success" id="them_moi" title="Thêm mới" form="form-import">
                                        <i class="fa fa-save"></i>&nbsp;&nbsp;Thêm mới
                                    </button>
                                    <a class="btn btn-sm btn-success" title="File mẫu" href="{{ route('admin.asset-apartment.asset-handover.download') }}"><i class="fa fa-download"></i> File mẫu</a>
                                    <a class="btn btn-sm btn-success" title="Danh sách căn hộ" href="{{ route('admin.asset-apartment.asset-handover.index') }}"><i class="fa fa-reply"></i> Danh sách bàn giao tài sản căn hộ</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('javascript')

    <script>
        // $(document).ready(function () {
        //     $("#form-import").submit(function () {
        //         $("#them_moi").attr("disabled", true);
        //         return true;
        //     });
        // });
        sidebar('import', 'index');
    </script>

@endsection
