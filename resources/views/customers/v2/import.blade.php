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
                            <form id="form_import_add_resident" autocomplete="off" enctype="multipart/form-data">
                                {{ csrf_field() }}
                                <div class="form-group">
                                    <label for="ip-name">Chọn file thêm mới cư dân</label>
                                    <div>
                                        <label style="padding-right: 10px;">Enable Send Mail
                                        </label><input type="checkbox" value="None" id="squaredcheck_new" class="checkbox1" name="check" checked="">
                                    </div>
                                    <input type="file" name="file" accept=".xls,.xlsx,.csv" id="ip-file_import">
                                </div>
                                <div class="form-group">
                                    <button type="submit" class="btn btn-sm btn-success import_add_resident">
                                        <i class="fa fa-save"></i>&nbsp;&nbsp;Thêm mới
                                    </button>
                                    <a class="btn btn-sm btn-success" title="File mẫu" href="{{ route('admin.v2.customers.download') }}"><i class="fa fa-download"></i> File mẫu</a>
                                    <a class="btn btn-sm btn-success" title="Danh sách cư dân" href="{{ route('admin.v2.customers.index') }}"><i class="fa fa-reply"></i> Danh sách cư dân</a>
                                </div>
                            </form>
                            <form id="form_import_update_resident" autocomplete="off" enctype="multipart/form-data">
                                {{ csrf_field() }}
                                <div class="form-group">
                                    <label for="ip-name">Chọn file cập nhật cư dân</label>
                                    <!--<div>
                                        <label style="padding-right: 10px;">Enable Send Mail
                                        </label><input type="checkbox" value="None" id="squaredcheck_update" class="checkbox2" name="check1" checked="">
                                    </div>-->
                                    <input type="file" name="file" accept=".xls,.xlsx,.csv" id="ip-file_import">
                                </div>
                                <div class="form-group">
                                    <button type="submit" class="btn btn-sm btn-success import_update_resident">
                                        <i class="fa fa-save"></i>&nbsp;&nbsp;Cập nhật
                                    </button>
                                    <a class="btn btn-sm btn-success" title="File mẫu" href="{{ route('admin.v2.customers.downloadUpdate') }}"><i class="fa fa-download"></i> File mẫu</a>
                                    <a class="btn btn-sm btn-success" title="Danh sách cư dân" href="{{ route('admin.v2.customers.index') }}"><i class="fa fa-reply"></i> Danh sách cư dân</a>
                                </div>
                            </form>
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
                    $('#form-import-customer').attr('action', '/admin/v2/customers/viewexcel');
              });
              $("#submit-form").on('click',function () {
                    $('#form-import-customer').attr('action', '/admin/v2/customers/import_customer');
              });
         });
         $("#form_import_add_resident").validate({
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
        $("#form_import_update_resident").validate({
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
        async function import_excel(_form,_url,checked) {
            let method = 'post';
            let param_query_old = "{{ $array_search }}";
            let param_query = param_query_old.replaceAll("&amp;", "&")
            var form_data = new FormData($(_form)[0]);
            showLoading();
            var export_excel = await call_api_export(method, _url + param_query,form_data)
            hideLoading();
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
        $('.import_add_resident').click(function (e) { 
            e.preventDefault();
            console.log(`{{ $array_search }}`);
            if($('#squaredcheck_new').is(":checked")){var checked = 1;}
            else {var checked = 0;}
            if (!$("#form_import_add_resident").valid()) return;
            import_excel("#form_import_add_resident",'admin/importUserExcel',checked);
        });
        $('.import_update_resident').click(function (e) { 
            e.preventDefault();
            if (!$("#form_import_update_resident").valid()) return;
            import_excel("#form_import_update_resident",'admin/importUpdateUserExcel');
        });
    </script>

@endsection
