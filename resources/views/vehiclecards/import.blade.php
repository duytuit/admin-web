@extends('backend.layouts.master')

@section('content')

    <section class="content-header">
        <h1>
            Vé xe nhập Exel
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('admin.home') }}"><i class="fa fa-home"></i> Trang chủ</a></li>
            <li class="active">Vé xe nhập Exel</li>
        </ol>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-heading">Thêm vé xe với Exel</div>

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
                            @if( in_array('admin.vehiclecards.import_vehiclecards',@$user_access_router))
                                <form action="{{ route('admin.vehiclecards.import_vehiclecards') }}" method="post" id="form-import-vehiclecard" autocomplete="off" enctype="multipart/form-data">
                                    {{ csrf_field() }}
                                    <div class="form-group">
                                        <label for="ip-name">Chọn file</label>
                                        <input type="file" name="file_import" id="ip-file_import">
                                    </div>
                                    <div class="form-group">
                                        <button type="submit" class="btn btn-sm btn-success" title="Cập nhật" form="form-import-vehiclecard">
                                            <i class="fa fa-save"></i>&nbsp;&nbsp;Cập nhật
                                        </button>
                                        @if( in_array('admin.vehiclecards.download',@$user_access_router))
                                            <a class="btn btn-sm btn-success" title="File mẫu" href="{{ route('admin.vehiclecards.download') }}"><i class="fa fa-download"></i> File mẫu</a>
                                        @endif
                                        @if( in_array('admin.vehiclecards.index',@$user_access_router))
                                            <a class="btn btn-sm btn-success" title="Danh sách vé xe" href="{{ route('admin.vehiclecards.index') }}"><i class="fa fa-reply"></i> Danh sách vé xe</a>
                                        @endif
                                    </div>
                                </form>
                            @endif
                            @if($messages)
                                <p style="text-align: center;padding: 10px 10px;font-size: 25px;background-color: antiquewhite;">Kết quả được đối chiếu với file import</p>
                                @foreach ($messages as $key => $item)
                                    <div class="panel panel-default">
                                        <div class="panel-heading">{{$item['messages']}}</div>
                                        <div class="panel-body">
                                            <table class="table table-striped">
                                                <thead>
                                                <tr>
                                                    <th colspan="" rowspan="" headers="">Index</th>
                                                    <th colspan="" rowspan="" headers="">Code</th>
                                                    <th colspan="" rowspan="" headers="">Biển số</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                @foreach ($item['data'] as $key => $item)
                                                    <tr>
                                                        <td colspan="" rowspan="" headers="">{!! !empty($item['index'])?$item['index']:'<span class="bg-red" style="padding: 3px 5px;border-radius: 6px;">Không có dữ liệu</span>' !!}</td>
                                                        <td colspan="" rowspan="" headers="">{!! !empty($item['code'])?$item['code']:'<span class="bg-red" style="padding: 3px 5px;border-radius: 6px;">Không có dữ liệu</span>' !!}</td>
                                                        <td colspan="" rowspan="" headers="">{!! !empty($item['number'])?$item['number']:'<span class="bg-red" style="padding: 3px 5px;border-radius: 6px;">Không có dữ liệu</span>' !!}</td>
                                                    </tr>
                                                @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                @endforeach
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
