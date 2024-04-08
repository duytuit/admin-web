<div class="panel panel-default">
    {{--                            <div class="panel-heading">Loại phương tiện</div>--}}
    <div class="panel-body">
        <div class="box box-primary">
            <div class="box-body ">
                <form id="form-search-cate" action="" method="post" style="display: inline-block;">
                    {{ csrf_field() }}
                    <div id="search-advance" class="col-sm-8">
                        <div class="col-sm-4 form-group">
                            <a class="form-control btn btn-success" title="Thêm thẻ" id="show-vehiclecard"><i class="fa fa-plus"></i>&nbsp;&nbsp;Thêm loại phương tiện</a>
                        </div>
                        <div class="col-sm-8 form-group">
                            <div class="input-group">
                                <input type="text" class="form-control" name="keyword_cate" placeholder="Nhập từ khóa tìm kiếm" value="{{!empty($keyword_cate)?$keyword_cate:''}}">
                                <div class="input-group-btn">
                                    <button type="submit" title="Tìm kiếm" class="btn btn-info" form="form-search-cate"><i class="fa fa-search"></i></button>
                                </div>
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
                            <th width="130">Mô tả</th>
                            <th width="50">Trạng thái</th>
                            <th width="130">Ngày bắt đầu tính phí</th>
                            <th width="130">Loại giá</th>
                            <th width="10%">Thao tác</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($vehiclecates as $vc)
                            <tr>
                                <td><input type="checkbox" name="ids[]" value="{{$vc->id}}" class="iCheck checkSingle" /></td>
                                <td>{{$vc->id}}</td>
                                <td>{{$vc->name}}</td>
                                <td>{{$vc->description}}</td>
                                <td>
{{--                                    @if($vc->status==1)--}}
{{--                                    <span class="btn btn-xs">--}}
{{--                                        <i class="fa fa-check"></i>--}}
{{--                                    </span>--}}
{{--                                    @endif--}}
                                    <div class="onoffswitch">
                                        <input type="checkbox"
                                               name="onoffswitch"
                                               class="onoffswitch-checkbox"
                                               data-id="{{ $vc->id }}"
                                               id="myonoffswitch_{{ $vc->id }}"
                                               data-url="{{ route('admin.v2.vehiclecategory.status') }}"
                                               value="{{$vc->status}}" @if($vc->status == true) checked @endif
                                        >
                                        <label class="onoffswitch-label" for="myonoffswitch_{{ $vc->id }}">
                                            <span class="onoffswitch-inner"></span>
                                            <span class="onoffswitch-switch"></span>
                                        </label>
                                    </div>
                                </td>
                                <td>{{date("d/m/Y", strtotime($vc->first_time_active))}}</td>
                                <td>{{$vc->bdc_price_type_id==1?'Một giá':'Lũy tiến'}}</td>
                                <td colspan="" rowspan="" headers="">
                                    @if( in_array('admin.v2.vehiclecategory.edit',@$user_access_router))
                                        <a href="{{ route('admin.v2.vehiclecategory.edit',['id'=> $vc->id]) }}" class="btn btn-success" title="sửa"><i class="fa fa-edit"></i></a>
                                    @endif
                                    @if(\Auth::user()->isadmin == 1)
                                        @if( in_array('admin.v2.vehiclecategory.delete',@$user_access_router))
                                            <a href="{{ route('admin.v2.vehiclecategory.delete',['id'=> $vc->id]) }}" class="btn btn-danger" title="xóa"><i class="fa fa-times"></i></a>
                                        @endif
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="row mbm">
                    <div class="col-sm-3">
                        <span class="record-total">Hiển thị: {{$vehiclecates->count()}} / {{ $vehiclecates->total() }} Kết quả</span>
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
        @include('vehicles.v2.modals.add-vehicle-category')
    </div>
</div>
<script type="text/javascript" src="{{ url('adminLTE/plugins/jquery-3.5.1/jquery-3.5.1.min.js') }}"></script>
<script type="text/javascript" src="{{ url('adminLTE/plugins/jquery-ui/jquery-ui.min.js') }}"></script>
<script>
     $("#show-vehiclecard").on('click', function () {
        showLoading();
        $.ajax({
                    url:  "{{route('admin.service.building.ajaxSelectTypeService')}}",
                    method: 'GET',
                    dataType: 'json',
                    data: {
                        category:4,
                    },
                    success: function(response) {
                        hideLoading();
                        if (response.success == true) {
                            $('.code_receipt').val(response.message);
                            $('#add-vehiclecard').modal('show');

                        } else {
                            toastr.error(response.message);
                        }
                    },
                    error: function(response) {
                        hideLoading();
                    }
                })
    })
</script>
