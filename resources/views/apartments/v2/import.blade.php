@extends('backend.layouts.master')

@section('content')

    <section class="content-header">
        <h1>
            Căn hộ nhập Exel
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('admin.home') }}"><i class="fa fa-home"></i> Trang chủ</a></li>
            <li class="active">Căn hộ nhập Exel</li>
        </ol>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-heading">Thêm căn hộ với Exel</div>

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
                            {{-- @if( in_array('admin.v2.apartments.import_apartment',@$user_access_router))
                                <form action="{{ route('admin.v2.apartments.import_apartment') }}" method="post" id="form-import-apartment" autocomplete="off" enctype="multipart/form-data">
                                    {{ csrf_field() }}
                                    <div class="form-group">
                                        <label for="ip-name">Chọn file thêm mới căn hộ</label>
                                        <input type="file" name="file_import" id="ip-file_import">
                                    </div>
                                    <div class="form-group">
                                        <button type="submit" class="btn btn-sm btn-success" title="Cập nhật" form="form-import-apartment">
                                            <i class="fa fa-save"></i>&nbsp;&nbsp;Thêm mới
                                        </button>
                                        @if( in_array('admin.v2.apartments.download',@$user_access_router))
                                            <a class="btn btn-sm btn-success" title="File mẫu" href="{{ route('admin.v2.apartments.download') }}"><i class="fa fa-download"></i> File mẫu</a>
                                        @endif
                                        @if( in_array('admin.v2.apartments.index',@$user_access_router))
                                            <a class="btn btn-sm btn-success" title="Danh sách căn hộ" href="{{ route('admin.v2.apartments.index') }}"><i class="fa fa-reply"></i> Danh sách căn hộ</a>
                                        @endif
                                    </div>
                                </form>
                            @endif --}}
                            @if( in_array('admin.v2.apartments.import_update_apartment',@$user_access_router))
                                <form action="{{ route('admin.v2.apartments.import_update_apartment') }}" method="post" id="form-import-update-apartment" autocomplete="off" enctype="multipart/form-data">
                                    {{ csrf_field() }}
                                    <div class="form-group">
                                        <label for="ip-name">Chọn file cập nhật căn hộ</label>
                                        <input type="file" name="file_import" id="ip-file_import">
                                    </div>
                                    <div class="form-group">
                                        <button type="submit" class="btn btn-sm btn-success" title="Cập nhật" form="form-import-update-apartment">
                                            <i class="fa fa-save"></i>&nbsp;&nbsp;Cập nhật
                                        </button>
                                        @if( in_array('admin.v2.apartments.download_file_update',@$user_access_router))
                                            <a class="btn btn-sm btn-success" title="File mẫu" href="{{ route('admin.v2.apartments.download_file_update') }}"><i class="fa fa-download"></i> File mẫu</a>
                                        @endif
                                        @if( in_array('admin.v2.apartments.index',@$user_access_router))
                                            <a class="btn btn-sm btn-success" title="Danh sách căn hộ" href="{{ route('admin.v2.apartments.index') }}"><i class="fa fa-reply"></i> Danh sách căn hộ</a>
                                        @endif
                                    </div>
                                </form>
                            @endif
                            @if(isset($messages))
                                <p style="text-align: center;padding: 10px 10px;font-size: 25px;background-color: antiquewhite;">Kết quả được đối chiếu với file import</p>
                                @foreach ($messages as $key => $item)
                                    <div class="panel panel-default">
                                        <div class="panel-heading" @if(isset($item['color'])) style="background-color: {{$item['color']}};color: white; " @endif >{{$item['messages']}}</div>
                                        <div class="panel-body">
                                            <table class="table table-striped">
                                                <thead>
                                                <tr>
                                                    <th colspan="" rowspan="" headers="">Index</th>
                                                    <th colspan="" rowspan="" headers="">Tên</th>
                                                    <th colspan="" rowspan="" headers="">Tầng</th>
                                                    <th colspan="" rowspan="" headers="">Mô tả</th>
                                                    <th colspan="" rowspan="" headers="">Mã tòa</th>
                                                    <th colspan="" rowspan="" headers="">Tình trạng</th>
                                                    <th colspan="" rowspan="" headers="">Mã căn</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                @foreach ($item['data'] as $key => $item)
                                                    <tr>
                                                        <td colspan="" rowspan="" headers="">{!! !empty($item['index'])?$item['index']:'<span class="bg-red" style="padding: 3px 5px;border-radius: 6px;">Không có dữ liệu</span>' !!}</td>
                                                        <td colspan="" rowspan="" headers="">{!! !empty($item['name'])?$item['name']:'<span class="bg-red" style="padding: 3px 5px;border-radius: 6px;">Không có dữ liệu</span>' !!}</td>
                                                        <td colspan="" rowspan="" headers="">{!! !empty($item['floor'])?$item['floor']:'<span class="bg-red" style="padding: 3px 5px;border-radius: 6px;">Không có dữ liệu</span>' !!}</td>
                                                        <td colspan="" rowspan="" headers="">{!! !empty($item['description'])?$item['description']:'<span class="bg-red" style="padding: 3px 5px;border-radius: 6px;">Không có dữ liệu</span>' !!}</td>
                                                        <td colspan="" rowspan="" headers="">{!! !empty($item['building_place_id'])?$item['building_place_id']:'<span class="bg-red" style="padding: 3px 5px;border-radius: 6px;">Không có dữ liệu</span>' !!}</td>
                                                        <td colspan="" rowspan="" headers="">{!! is_int($item['status'])?(int)$item['status']:'<span class="bg-red" style="padding: 3px 5px;border-radius: 6px;">Không có dữ liệu</span>' !!}</td>
                                                        <td colspan="" rowspan="" headers="">{!! !empty($item['code'])?$item['code']:'<span class="bg-red" style="padding: 3px 5px;border-radius: 6px;">Không có dữ liệu</span>' !!}</td>
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
