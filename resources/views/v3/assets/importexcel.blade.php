@extends('backend.layouts.master')

@section('content')

    <section class="content-header">
        <h1>
            Import tài sản
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('admin.home') }}"><i class="fa fa-home"></i> Trang chủ</a></li>
            <li class="active">Import tài sản</li>
        </ol>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-heading">Import tài sản</div>

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
                            <form id="form_import_file" autocomplete="off" enctype="multipart/form-data">
                                {{ csrf_field() }}
                                <div class="form-group">
                                    <label for="ip-name">Chọn file thêm mới tài sản</label>
                                    <input type="file" name="file" accept=".xls,.xlsx,.csv" id="ip-file_import">
                                </div>
                                <div class="form-group">
                                    <button type="submit" class="btn btn-sm btn-success import_file">
                                        <i class="fa fa-save"></i>&nbsp;&nbsp;Thêm mới
                                    </button>
                                    <a class="btn btn-sm btn-success" title="File mẫu" href="{{ @$file }}"><i class="fa fa-download"></i> File mẫu</a>
                                    <a href="{{ route('admin.v3.assets.index') }}" class="btn btn-warning mr-l"><i class="bx bx-arrow-back"></i><span class="align-middle ml-25">Quay lại danh sách</span></a>
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
        sidebar('import', 'index');
       $("#form_import_file").validate({
            rules: {
                file: {
                    required: true,
                },
            },
            messages: {
                file: {
                    required: "File import không đúng định dạng."
                }
            }
        });
        async function import_excel() {
            let method = 'post';
            let param_query_old = "{{ $array_search }}";
            let param_query = param_query_old.replaceAll("&amp;", "&")
            var form_data = new FormData($('#form_import_file')[0]);
            var export_excel = await call_api_export(method, 'admin/asset/impAssetDetail' + param_query,form_data)
            var blob = new Blob(
                    [export_excel],
                    {type:export_excel.type}
                );
            const url = URL.createObjectURL(blob)
            const link = document.createElement('a')
            link.download = 'ket_qua_import';
            link.href = url
            document.body.appendChild(link)
            link.click()
            document.body.removeChild(link);
        }
        $('.import_file').click(function (e) { 
            e.preventDefault();
            if (!$("#form_import_file").valid()) return;
            import_excel();
        });
    </script>

@endsection
