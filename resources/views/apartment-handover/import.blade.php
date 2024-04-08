@extends('backend.layouts.master')

@section('content')

    <section class="content-header">
        <h1>
            Bàn Giao Căn Hộ Excel
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('admin.home') }}"><i class="fa fa-home"></i> Trang chủ</a></li>
            <li class="active">Bàn Giao Căn hộ nhập Exel</li>
        </ol>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-heading">Thêm bàn giao căn hộ với Exel</div>

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
                            @if( in_array('admin.apartments.import_apartment',@$user_access_router))
                                <form action="{{ route('admin.apartment.handover.import_apartment') }}" method="post" id="form-import-apartment" autocomplete="off" enctype="multipart/form-data">
                                    {{ csrf_field() }}
                                    <div class="form-group">
                                        <label for="ip-name">Chọn file</label>
                                        <input type="file" name="file_import" id="ip-file_import">
                                    </div>
                                    <div class="form-group">
                                        <button type="submit" class="btn btn-sm btn-success" title="Cập nhật" form="form-import-apartment">
                                            <i class="fa fa-save"></i>&nbsp;&nbsp;Cập nhật
                                        </button>
                                        @if( in_array('admin.apartment.handover.download',@$user_access_router))
                                            <a class="btn btn-sm btn-success" title="File mẫu" href="{{ route('admin.apartment.handover.download') }}"><i class="fa fa-download"></i> File mẫu</a>
                                        @endif
                                        @if( in_array('admin.apartment.handover.index',@$user_access_router))
                                            <a class="btn btn-sm btn-success" title="Danh sách bàn giao căn hộ" href="{{ route('admin.apartment.handover.index') }}"><i class="fa fa-reply"></i> Danh sách bàn giao căn hộ</a>
                                        @endif
                                    </div>
                                </form>
                            @endif
                             @if($messages)
                                <p style="text-align: center;padding: 10px 10px;font-size: 25px;background-color: antiquewhite;">{{session('title')}}</p>
                                @foreach ($messages as $key => $item)
                                    <div class="panel panel-default">
                                        <div class="panel-heading" @if(isset($item['color'])) style="background-color: {{$item['color']}};color: white; " @endif >{{$item['messages']}}</div>
                                        <div class="panel-body">
                                            <table class="table table-striped">
                                                <thead>
                                                <tr>
                                                    <th colspan="" rowspan="" headers="">Index</th>
                                                    <th colspan="" rowspan="" headers="">Tên</th>
                                                    <th colspan="" rowspan="" headers="">CMND/Hộ chiếu</th>
                                                    <th colspan="" rowspan="" headers="">Email</th>
                                                    <th colspan="" rowspan="" headers="">Số điện thoại</th>
                                                    <th colspan="" rowspan="" headers="">Mật khẩu</th>
                                                    <th colspan="" rowspan="" headers="">Giới tính</th>
                                                    <th colspan="" rowspan="" headers="">Căn hộ</th>
                                                    <th colspan="" rowspan="" headers="">Tầng</th>
                                                    <th colspan="" rowspan="" headers="">Tòa</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                @foreach ($item['data'] as $key => $item)
                                                  
                                                    <tr>
                                                        <td colspan="" rowspan="" headers="">{!! !empty($item['index'])?$item['index']:'<span class="bg-red" style="padding: 3px 5px;border-radius: 6px;">Không có dữ liệu</span>' !!}</td>
                                                        <td colspan="" rowspan="" headers="">{!! !empty($item['display_name'])?$item['display_name']:'<span class="bg-red" style="padding: 3px 5px;border-radius: 6px;">Không có dữ liệu</span>' !!}</td>
                                                        <td colspan="" rowspan="" headers="">{!! !empty($item['cmt'])?$item['cmt']:'<span class="bg-red" style="padding: 3px 5px;border-radius: 6px;">Không có dữ liệu</span>' !!}</td>
                                                        <td colspan="" rowspan="" headers="">{!! !empty($item['email'])?$item['email']:'<span class="bg-red" style="padding: 3px 5px;border-radius: 6px;">Không có dữ liệu</span>' !!}</td>
                                                        <td colspan="" rowspan="" headers="">{!! !empty($item['phone'])?$item['phone']:'<span class="bg-red" style="padding: 3px 5px;border-radius: 6px;">Không có dữ liệu</span>' !!}</td>
                                                        <td colspan="" rowspan="" headers="">{!! !empty($item['password'])?$item['password']:'<span class="bg-red" style="padding: 3px 5px;border-radius: 6px;">Không có dữ liệu</span>' !!}</td>
                                                        <td colspan="" rowspan="" headers="">{!! !empty($item['gender'])?$item['gender']:'<span class="bg-red" style="padding: 3px 5px;border-radius: 6px;">Không có dữ liệu</span>' !!}</td>
                                                        <td colspan="" rowspan="" headers="">{!! !empty($item['apartment_name'])?$item['apartment_name']:'<span class="bg-red" style="padding: 3px 5px;border-radius: 6px;">Không có dữ liệu</span>' !!}</td>
                                                        <td colspan="" rowspan="" headers="">{!! !empty($item['floor'])?$item['floor']:'<span class="bg-red" style="padding: 3px 5px;border-radius: 6px;">Không có dữ liệu</span>' !!}</td>
                                                        <td colspan="" rowspan="" headers="">{!! !empty($item['place'])?$item['place']:'<span class="bg-red" style="padding: 3px 5px;border-radius: 6px;">Không có dữ liệu</span>' !!}</td>
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
