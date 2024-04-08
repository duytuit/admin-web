@extends('backend.layouts.master')

@section('content')

    <section class="content-header">
        <h1>
            Import Excel Dịch Vụ Cho Căn Hộ
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('admin.home') }}"><i class="fa fa-home"></i> Trang chủ</a></li>
            <li class="active">Import Excel Dịch Vụ Cho Căn Hộ</li>
        </ol>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-heading">Thêm Dịch Vụ Cho Căn Hộ</div>
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
                            {!! Form::open(['route' => ['admin.v2.service.building.importApartmentService'], 'method'=> 'POST','files' => true]) !!}
                                {{ csrf_field() }}
                                <div class="form-group">
                                    <label for="ip-name">Chọn file</label>
                                    <input type="file" name="file_import" id="ip-file_import">
                                </div>
                                <div class="form-group">
                                    <button type="submit" class="btn btn-sm btn-success" title="Cập nhật">
                                        <i class="fa fa-save"></i>&nbsp;&nbsp;Cập nhật
                                    </button>
                                    <!-- <a class="btn btn-sm btn-success" title="File mẫu" href="{{ route('admin.v2.service.building.download') }}"><i class="fa fa-download"></i> File mẫu</a> -->
                                    <a class="btn btn-sm btn-success" title="File mẫu" href="https://cdn.dxmb.vn/media/buildingcare/2023/1012/0200-import-dich-vu-cho-can-ho-template.xlsx"><i class="fa fa-download"></i> File mẫu</a>
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
