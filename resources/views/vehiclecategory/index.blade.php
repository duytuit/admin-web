@extends('backend.layouts.master')

@section('content')

    <section class="content-header">
        <h1>
            Loại phương tiện <a href="javascript:void(0);" class="btn btn-success" title="Thêm thẻ" data-toggle="modal" data-target="#add-vehiclecard"><i class="fa fa-plus"></i>&nbsp;&nbsp;Thêm mới</a>
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('admin.home') }}"><i class="fa fa-home"></i> Trang chủ</a></li>
            <li class="active">Loại phương tiện</li>
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
                                <div class="col-sm-12">
                                    <input type="text" class="form-control" name="keyword" placeholder="Nhập từ khóa tìm kiếm" value="{{!empty($keyword)?$keyword:''}}">
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
                            <th width="130">Tên danh mục</th>
                            <th width="10%">Thao tác</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($vehiclecates as $vc)
                            <tr>
                                <td><input type="checkbox" name="ids[]" value="{{$vc->id}}" class="iCheck checkSingle" /></td>
                                <td>{{$vc->id}}</td>
                                <td>{{$vc->name}}</td>
                                <td colspan="" rowspan="" headers="">
                                    @if( in_array('admin.vehiclecategory.edit',@$user_access_router))
                                        <a href="{{ route('admin.vehiclecategory.edit',['id'=> $vc->id]) }}" class="btn btn-success" title="sửa"><i class="fa fa-edit"></i></a>
                                    @endif
                                    @if( in_array('admin.vehiclecategory.delete',@$user_access_router))
                                        <a href="{{ route('admin.vehiclecategory.delete',['id'=> $vc->id]) }}" class="btn btn-danger" title="xóa"><i class="fa fa-times"></i></a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="row mbm">
                    <div class="col-sm-3">
                        <span class="record-total">Hiển thị: {{$display_count}} / {{ $vehiclecates->total() }} Kết quả</span>
                    </div>
                    <div class="col-sm-6 text-center">
                        <div class="pagination-panel">
                            {{ $vehiclecates->appends(Request::all())->onEachSide(1)->links() }}
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
        <div id="add-vehiclecard" class="modal fade" role="dialog">
            <div class="modal-dialog  modal-lg">
                <!-- Modal content-->
                @if( in_array('admin.vehiclecategory.insert',@$user_access_router))
                    <form action="{{ route('admin.vehiclecategory.insert') }}" method="post" id="form-add-vehiclecate" class="form-validate form-horizontal">
                        {{ csrf_field() }}
                        <input type="hidden" name="hashtag">
                        <div class="modal-content">
                            <div class="modal-header bg-primary">
                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                                <h4 class="modal-title">Thêm danh mục</h4>
                            </div>
                            <div class="modal-body">
                                <div class="alert alert-danger alert_pop_add_vehiclecate" style="display: none;">
                                    <ul></ul>
                                </div>
                                <div class="row">
                                    <div class="col-sm-12">
                                        <div class="form-group">
                                            <div class="col-sm-3">
                                                <label for="in-re_name">Tên danh mục</label>
                                            </div>
                                            <div class="col-sm-8">
                                                <input type="text" name="name" id="in-name" class="form-control" placeholder="Tên danh mục">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-danger pull-right" data-dismiss="modal"><i class="fa fa-close"></i>&nbsp;&nbsp;Hủy</button>
                                <button type="button" class="btn btn-primary btn-js-action-vehiclecate" style="margin-right: 5px;"><i class="fa fa-save"></i>&nbsp;&nbsp;Xác nhận</button>
                            </div>
                        </div>
                    </form>
                @endif
            </div>
        </div>
    </section>
@endsection

@section('javascript')

    <script>
        $(function () {
            $(".btn-js-action-vehiclecate").on('click',function () {
                var _this = $(this);
                $(".alert_pop_add_vehiclecate").hide();
                var code = $("#in-name").val();
                if(code.length <=0){
                    $(".alert_pop_add_vehiclecate").show();
                    $(".alert_pop_add_vehiclecate ul").html('<li>Tên phương tiện không được bỏ trống</li>')
                }else{
                    $("#form-add-vehiclecate").submit();
                }
            });
        });
    </script>


@endsection
