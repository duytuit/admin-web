@extends('backend.layouts.master')

@section('content')

    <section class="content-header">
        <h1>
            Files <a href="javascript:void(0);" class="btn btn-success" title="Thêm files" data-toggle="modal" data-target="#add-file"><i class="fa fa-plus"></i>&nbsp;&nbsp;Thêm mới</a>
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('admin.home') }}"><i class="fa fa-home"></i> Trang chủ</a></li>
            <li class="active">Files</li>
        </ol>
    </section>
    <section class="content">
        <div class="box box-primary">
            <div class="box-body ">
                <form id="form-search-customer" action="" method="post" style="display: inline-block;">
                    {{ csrf_field() }}
                    <div id="search-advance" class="search-advance">
                        <div class=" ">
                            <div class="form-group space-5" style="/*width: calc(100% - 55px);*/float: left;">
                                <div class="col-sm-6">
                                    <input type="text" class="form-control" name="keyword" placeholder="Nhập từ khóa tìm kiếm" value="{{!empty($data_search['keyword'])?$data_search['keyword']:''}}">
                                </div>
                                <div class="col-sm-6">
                                    <select name="apartment" id="ip-apartment"  class="form-control">
                                        <option value="">Chọn căn hộ</option>
                                    </select>
                                </div>
                            </div>
                            <div class="input-group-btn">
                                <button type="submit" title="Tìm kiếm" class="btn btn-info" form="form-search-customer"><i class="fa fa-search"></i></button>
                            </div>
                        </div>
                    </div>
                </form><!-- END #form-search-advance -->

                <div class="table-responsive">
                    <table class="table table-hover table-striped table-bordered">
                        <thead class="bg-primary">
                        <tr>
                            <th width="1%"><input type="checkbox" class="iCheck checkAll" data-target=".checkSingle" /></th>
                            <th width="1%">Stt</th>
                            <th width="130">Tên file</th>
                            <th width="130">Mô tả</th>
                            <th width="150">Thao tác</th>
                        </tr>
                        </thead>
                        <tbody>
                            @foreach($files as $f)
                                <tr>
                                    <td><input type="checkbox" name="ids[]" value="{{$f->id}}" class="iCheck checkSingle" /></td>
                                    <td>{{$f->id}}</td>
                                    <td>{{$f->name}}</td>
                                    <td>{{$f->description}}</td>
                                    <td colspan="" rowspan="" headers="">
                                        <a href="{{ route('admin.systemfiles.edit',['id'=> $f->id]) }}" class="btn btn-success" title="sửa"><i class="fa fa-edit"></i></a>
                                        <a href="{{ route('admin.systemfiles.delete',['id'=> $f->id]) }}" class="btn btn-danger" title="xóa"><i class="fa fa-times"></i></a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="row mbm">
                    <div class="col-sm-3">
                        <span class="record-total">Tổng: {{ $files->total() }} bản ghi</span>
                    </div>
                    <div class="col-sm-6 text-center">
                        <div class="pagination-panel">
                            {{ $files->appends(Request::all())->onEachSide(1)->links() }}
                        </div>
                    </div>
                    <div class="col-sm-3 text-right">
                        <span class="form-inline">
                            Hiển thị
                            <select name="per_page" class="form-control" data-target="#form-users">
                                @php $list = [10, 20, 50, 100, 200]; @endphp
                                @foreach ($list as $num)
                                    <option value="{{ $num }}" {{ $num == $per_page ? 'selected' : '' }}>{{ $num }}</option>
                                @endforeach
                            </select>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <div id="add-file" class="modal fade" role="dialog">
            <div class="modal-dialog  modal-lg">
                <!-- Modal content-->
                <form action="{{ route('admin.systemfiles.insert') }}" method="POST" id="form-add-files" class="form-validate form-horizontal"  autocomplete="off" enctype="multipart/form-data">
                    {{ csrf_field() }}
                    <div class="modal-content">
                        <div class="modal-header bg-primary">
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                            <h4 class="modal-title">Thêm mới file</h4>
                        </div>
                        <div class="modal-body">
                            <div class="alert alert-danger alert_pop_add_file" style="display: none;">
                                <ul></ul>
                            </div>
                            <div class="row">
                                <div class="col-sm-12">
                                    <div class="form-group">
                                        <div class="col-sm-2">
                                            <label for="in-re_name">Tên file</label>
                                        </div>
                                        <div class="col-sm-8">
                                            <input type="text" name="name" id="in-name-file" class="form-control" placeholder="Tên file">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="col-sm-2">
                                            <label>Căn hộ</label>
                                        </div>
                                        <div class="col-sm-8">
                                            <select name="bdc_apartment_id" id="ip-ap_id" class="form-control" style="width: 100%;">
                                                <option value="">Chọn căn hộ</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="col-sm-2">
                                            <label>file</label>
                                        </div>
                                        <div class="col-sm-8">
                                            <input type="file" name="file_apartment" id="ip-file" class="form-control">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="col-sm-2">
                                            <label>Mô tả</label>
                                        </div>
                                        <div class="col-sm-8">
                                            <textarea name="description" id="textarea-vc_description" class="form-control" cols="30" rows="5" placeholder="Mô tả file"></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-danger pull-right" data-dismiss="modal"><i class="fa fa-close"></i>&nbsp;&nbsp;Hủy</button>
                            <button type="button" class="btn btn-primary btn-js-action-file" form="form-add-files" style="margin-right: 5px;"><i class="fa fa-save"></i>&nbsp;&nbsp;Xác nhận</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </section>



@endsection

@section('javascript')

    <script>
        $(function () {
            get_data_select({
                object: '#ip-apartment,#ip-ap_id',
                url: '{{ url('admin/apartments/ajax_get_apartment') }}',
                data_id: 'id',
                data_text: 'name',
                title_default: 'Chọn căn hộ'
            });
            function get_data_select(options) {
                $(options.object).select2({
                    ajax: {
                        url: options.url,
                        dataType: 'json',
                        data: function(params) {
                            var query = {
                                search: params.term,
                            }
                            return query;
                        },
                        processResults: function(json, params) {
                            var results = [{
                                id: '',
                                text: options.title_default
                            }];

                            for (i in json.data) {
                                var item = json.data[i];
                                results.push({
                                    id: item[options.data_id],
                                    text: item[options.data_text]
                                });
                            }
                            return {
                                results: results,
                            };
                        },
                        minimumInputLength: 3,
                    }
                });
            }
            $(".btn-js-action-file").on('click',function () {
                var _this = $(this);
                $(".alert_pop_add_file").hide();
                var name = $("#in-name-file").val();
                if(name.length <=0){
                    $(".alert_pop_add_file").show();
                    $(".alert_pop_add_file ul").html('<li>Tên file không được bỏ trống</li>')
                }else if(name.length <=3 || name.length >=255){
                    $(".alert_pop_add_file").show();
                    $(".alert_pop_add_file ul").html('<li>Tên file không được nhỏ hơn 3 hoặc lớn hơn 255 ký tự</li>')
                }else{
                    $("#form-add-files").submit();
                }
            });
        });
        sidebar('Customers', 'index');
    </script>


@endsection
