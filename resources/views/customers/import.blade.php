@extends('backend.layouts.master')

@section('content')

    <section class="content-header">
        <h1>
            Cư dân nhập Exel
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('admin.home') }}"><i class="fa fa-home"></i> Trang chủ</a></li>
            <li class="active">Cư dân nhập Exel</li>
        </ol>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-heading">Thêm cư dân với Exel</div>

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
                            @if( in_array('admin.customers.import_customer',@$user_access_router))
                                <form action="{{ route('admin.customers.import_customer') }}" method="post" id="form-import-customer" autocomplete="off" enctype="multipart/form-data">
                                    {{ csrf_field() }}
                                    <div class="form-group">
                                        <label for="ip-name">Chọn file thêm mới cư dân</label>
                                        <input type="file" name="file_import" id="ip-file_import">
                                    </div>
                                    <div class="form-group">
                                        <button type="submit" class="btn btn-sm btn-success" id="view-form" title="Xem trước" form="form-import-customer">
                                            <i class="fa fa-eye"></i>&nbsp;&nbsp;Xem trước
                                        </button>
                                        <button type="submit" class="btn btn-sm btn-success" id="submit-form" title="Cập nhật" form="form-import-customer">
                                            <i class="fa fa-save"></i>&nbsp;&nbsp;Thêm mới
                                        </button>
                                        @if( in_array('admin.customers.download',@$user_access_router))
                                            <a class="btn btn-sm btn-success" title="File mẫu" href="{{ route('admin.customers.download') }}"><i class="fa fa-download"></i> File mẫu</a>
                                        @endif
                                        @if( in_array('admin.customers.index',@$user_access_router))
                                            <a class="btn btn-sm btn-success" title="Danh sách cư dân" href="{{ route('admin.customers.index') }}"><i class="fa fa-reply"></i> Danh sách cư dân</a>
                                        @endif
                                    </div>
                                </form>
                                @if( in_array('admin.customers.import_customer_new',@$user_access_router))
                                    <form action="{{ route('admin.customers.import_customer_new') }}" method="post" id="form-import-customer-new" autocomplete="off" enctype="multipart/form-data">
                                        {{ csrf_field() }}
                                        <div class="form-group">
                                            <label for="ip-name">Chọn file cập nhật cư dân</label>
                                            <input type="file" name="file_import" id="ip-file_import">
                                        </div>
                                        <div class="form-group">
                                            <button type="submit" class="btn btn-sm btn-success" title="Cập nhật" form="form-import-customer-new">
                                                <i class="fa fa-save"></i>&nbsp;&nbsp;Cập nhật
                                            </button>
                                            @if( in_array('admin.customers.downloadUpdate',@$user_access_router))
                                            <a class="btn btn-sm btn-success" title="File mẫu" href="{{ route('admin.customers.downloadUpdate') }}"><i class="fa fa-download"></i> File mẫu</a>
                                            @endif
                                            @if( in_array('admin.customers.index',@$user_access_router))
                                                <a class="btn btn-sm btn-success" title="Danh sách cư dân" href="{{ route('admin.customers.index') }}"><i class="fa fa-reply"></i> Danh sách cư dân</a>
                                            @endif
                                        </div>
                                    </form>
                                @endif
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
                                                    <th colspan="" rowspan="" headers="">Quan hệ</th>
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
                                                        <td colspan="" rowspan="" headers="">{!! is_int($item['type'])?(int)$item['type']:'<span class="bg-red" style="padding: 3px 5px;border-radius: 6px;">Không có dữ liệu</span>' !!}</td>
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
       
         $(function () {
              $("#view-form").on('click',function () {
                    $('#form-import-customer').attr('action', '/admin/customers/viewexcel');
              });
              $("#submit-form").on('click',function () {
                    $('#form-import-customer').attr('action', '/admin/customers/import_customer');
              });
         });
    </script>

@endsection
