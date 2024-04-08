@extends('backend.layouts.master')

@section('content')

    <section class="content-header">
        <h1>
            Tài sản căn hộ nhập Exel
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('admin.home') }}"><i class="fa fa-home"></i> Trang chủ</a></li>
            <li class="active">Tài sản căn hộ nhập Exel</li>
        </ol>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-heading">Thêm tài sản căn hộ với Exel</div>

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
                            <form action="{{ route('admin.asset-apartment.asset.import_store') }}" method="post" id="form-import" autocomplete="off" enctype="multipart/form-data">
                                {{ csrf_field() }}
                                <div class="form-group">
                                    <label for="file_import">Chọn file</label>
                                    <input type="file" name="file_import" id="file_import">
                                </div>
                                <div class="form-group">
                                    <button type="submit" class="btn btn-sm btn-success" id="them_moi" title="Thêm mới" form="form-import">
                                        <i class="fa fa-save"></i>&nbsp;&nbsp;Thêm mới
                                    </button>
                                    <a class="btn btn-sm btn-success" title="File mẫu" href="{{ route('admin.asset-apartment.asset.download') }}"><i class="fa fa-download"></i> File mẫu</a>
                                    <a class="btn btn-sm btn-success" title="Danh sách căn hộ" href="{{ route('admin.asset-apartment.asset.index') }}"><i class="fa fa-reply"></i> Danh sách tài sản căn hộ</a>
                                </div>
                            </form>
                            <form action="{{ route('admin.asset-apartment.asset.import_update') }}" method="post" id="form-import-update" autocomplete="off" enctype="multipart/form-data">
                                {{ csrf_field() }}
                                <div class="form-group">
                                    <label for="file_import">Chọn file</label>
                                    <input type="file" name="file_import" id="file_import">
                                </div>
                                <div class="form-group">
                                    <button type="submit" class="btn btn-sm btn-success" id="cap_nhat" title="Cập nhật" form="form-import-update">
                                        <i class="fa fa-save"></i>&nbsp;&nbsp;Cập nhật
                                    </button>
                                    <a class="btn btn-sm btn-success" title="File mẫu" href="{{ route('admin.asset-apartment.asset.download_file_update') }}"><i class="fa fa-download"></i> File mẫu</a>
                                    <a class="btn btn-sm btn-success" title="Danh sách căn hộ" href="{{ route('admin.asset-apartment.asset.index') }}"><i class="fa fa-reply"></i> Danh sách tài sản căn hộ</a>
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
        //     $("#form-import-update").submit(function () {
        //         $("#cap_nhat").attr("disabled", true);
        //         return true;
        //     });
        // });
        sidebar('import', 'index');
        // $("#them_moi").on("click",function(e) {
        //     showLoading();
        //     e.preventDefault();
        //     var formCreate = new FormData($('#form-import')[0]);
        //     $.ajax({
        //         url: "{{ route('admin.asset-apartment.asset.import_store',['action' => 'them_moi']) }}",
        //         type: 'POST',
        //         data: formCreate,
        //         contentType: false,
        //         processData: false, 
        //         xhrFields: {
        //         responseType: 'blob'
        //         },
        //         success: function(response) {
        //             var blob = new Blob(
        //                 [response],
        //                 {type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet;base64,"}
        //             );
        //             //var fileName = xhr.getResponseHeader('content-disposition').split('filename=')[1].split(';')[0];
        //             const url = URL.createObjectURL(blob)
        //             const link = document.createElement('a')
        //             link.download = 'ket_qua_import';
        //             link.href = url
        //             document.body.appendChild(link)
        //             link.click()
        //             document.body.removeChild(link);
        //             hideLoading();
        //         },
        //         error: function (response) {
        //             hideLoading();
        //         }
        //     });
        // });
        // $("#cap_nhat").on("click",function(e) {
        //     showLoading();
        //     e.preventDefault();
        //     var formCreate = new FormData($('#form-import')[0]);
        //     $.ajax({
        //         url: "{{ route('admin.asset-apartment.asset.import_store',['action' => 'cap_nhat']) }}",
        //         type: 'POST',
        //         data: formCreate,
        //         contentType: false,
        //         processData: false, 
        //         success: function(response) {
        //             hideLoading();
        //         },
        //         error: function (response) {
        //             hideLoading();
        //         }
        //     });
        // });
    </script>

@endsection
