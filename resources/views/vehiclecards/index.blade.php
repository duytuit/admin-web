@extends('backend.layouts.master')

@section('content')

    <section class="content-header">
        <h1>
            Vé xe 
            <a href="javascript:void(0);" class="btn btn-success" title="Thêm thẻ" data-toggle="modal" data-target="#add-vehiclecard"><i class="fa fa-plus"></i>&nbsp;&nbsp;Thêm mới</a> 
            <a href="{{ route('admin.vehiclecards.index_import') }}" class="btn btn-success"><i class="fa fa-file-excel-o"></i>&nbsp;&nbsp;Import Exel</a> 
            <a href="{{ route('admin.vehiclecards.export',Request::all()) }}" class="btn btn-success"><i class="fa fa-file-excel-o"></i>&nbsp;&nbsp;Export Excel</a> 
            <p class="display_mes_summit @if($data_error) error_mes @elseif($data_success) success_mes @endif"> {{$data_cus}} </p>
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('admin.home') }}"><i class="fa fa-home"></i> Trang chủ</a></li>
            <li class="active">Vé xe</li>
        </ol>
    </section>
    <section class="content">
        <div class="box box-primary">
            <div class="box-body ">
                <form id="form-search-customer" action="" method="get" style="display: inline-block;">
                    {{ csrf_field() }}
                    <div class="col-sm-1">
                        <button type="button" data-toggle="dropdown" class="btn btn-primary dropdown-toggle pull-left" style="margin-right: 10px;">Tác vụ&nbsp;<span class="caret"></span></button>
                        <ul class="dropdown-menu">
                            <li>
                                <a href="javascript:" type="button" class="btn-action" data-target="#form-vehiclecards-action" data-method="delete">
                                    <i class="fa fa-trash text-danger"></i>&nbsp; Xóa
                                </a>
                            </li>

                        </ul>
                    </div>
                    <div class="col-sm-11">
                        <div id="search-advance" class="search-advance">
                            <div class=" ">
                                <div class="form-group space-5" style="width: calc(100% - 55px);float: left;">
                                    <div class="col-sm-12">
                                        <div class="col-sm-2">
                                            <input type="text" class="form-control" name="keyword" placeholder="Nhập Code hoặc biển số tìm kiếm" value="{{!empty($data_search['keyword'])?$data_search['keyword']:''}}">
                                        </div>
                                        <div class="col-sm-2" style="padding-left:0">
                                            <select name="place_id" id="place_id" class="form-control" style="width: 100%;">
                                                <option value="">Tòa nhà</option>
                                                <?php $place_building = isset($get_place_building) ? $get_place_building : '' ?>
                                                @if($place_building)
                                                    <option value="{{$place_building->id}}" selected>{{$place_building->name}}</option>
                                                @endif
                                            </select>
                                        </div>
                                        <div class="col-sm-3">
                                            <select name="apartment" id="ip-apartment" class="form-control" style="width: 100%">
                                                <option value="">Chọn căn hộ</option>
                                                <?php $apt = !empty($data_search['apartment'])?$data_search['apartment']:''; ?>
                                                @if($apt)
                                                    <option value="{{$apt->id}}" selected>{{$apt->name}}</option>
                                                @endif
                                            </select>
                                        </div>
                                        <div class="col-sm-3">
                                            <select name="cate" id="ip-cate" class="form-control" style="width: 100%">
                                                <option value="">Chọn phương tiện</option>
                                                <?php $cat = !empty($data_search['cate'])?$data_search['cate']:''; ?>
                                                @if($cat)
                                                    <option value="{{$cat->id}}" selected>{{$cat->name}}</option>
                                                @endif
                                            </select>
                                        </div>
                                        <div class="col-sm-2">
                                            <select name="status" id="ip-status"  class="form-control">
                                                <option value="">Chọn tình trạng</option>
                                                <?php $status = !empty($data_search['status'])?$data_search['status']:''; ?>
                                                <option value="1" @if($status == 1) selected @endif>Active</option>
                                                <option value="0" @if($status === 0) selected @endif>Inactive</option>
                                            </select>

                                        </div>
                                    </div>
                                </div>
                                <div class="input-group-btn">
                                    <button type="submit" title="Tìm kiếm" class="btn btn-info" form="form-search-customer"><i class="fa fa-search"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form><!-- END #form-search-advance -->

                <div class="table-responsive">
                    @if( in_array('admin.vehiclecards.action',@$user_access_router))
                        <form action='{{ route('admin.vehiclecards.action') }}' method="post" id="form-vehiclecards-action">
                            {{ csrf_field() }}
                            <input type="hidden" name="method" value="" />
                            <table class="table table-hover table-striped table-bordered">
                                <thead class="bg-primary">
                                <tr>
                                    <th width="30"><input type="checkbox" class="iCheck checkAll" data-target=".checkSingle" /></th>
                                    <th width="30">Stt</th>
                                    <th width="130">Mã thẻ</th>
                                    <th width="130">Căn hộ</th>
                                    <th width="90">Nhóm căn hộ</th>
                                    <th width="30">Phương tiện</th>
                                    <th width="90">Biển số</th>
                                    <th width="130">Ghi chú</th>
                                    <th width="20">Tình trạng thẻ</th>
                                    <th width="30">Thao tác</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($vehiclecards as $vc)
                                    @php
                                        $vehicle  =  App\Models\Vehicles\Vehicles::get_detail_vehicle_by_id($vc->bdc_vehicle_id);
                                        $apartment = $vehicle ? App\Models\Apartments\Apartments::get_detail_apartment_by_apartment_id($vehicle->bdc_apartment_id) : null;
                                        $category =  $vehicle ?  App\Models\VehicleCategory\VehicleCategory::get_detail_vehicles_category_by_id($vehicle->vehicle_category_id) : null;
                                        $apartment_group =$vehicle ?  App\Models\Apartments\ApartmentGroup::get_detail_apartment_group_by_apartment_group_id($apartment->bdc_apartment_group_id) : null;
                                    @endphp
                                    <tr>
                                        <td><input type="checkbox" name="ids[]" value="{{$vc->id}}" class="iCheck checkSingle" /></td>
                                        <td>{{$vc->id}}</td>
                                        <td>{{$vc->code}}</td>
                                        <td>{{@$apartment->name}}</td>
                                        <td>{{@$apartment_group->name}}</td>
                                        <td>{{@$category->name??''}}</td>
                                        <td>{{@$vehicle->number??''}}</td>
                                        <td>{{$vc->description}}</td>
                                        <td>
                                            @if( in_array('admin.vehiclecards.status',@$user_access_router))
                                                <div class="onoffswitch">
                                                    <input type="checkbox" name="onoffswitch" class="onoffswitch-checkbox" data-id="{{ $vc->id }}"
                                                           id="myonoffswitch_{{ $vc->id }}" data-url="{{ route('admin.vehiclecards.status') }}" @if($vc->status == 1) checked @endif >
                                                    <label class="onoffswitch-label" for="myonoffswitch_{{ $vc->id }}">
                                                        <span class="onoffswitch-inner"></span>
                                                        <span class="onoffswitch-switch"></span>
                                                    </label>
                                                </div>
                                            @endif
                                        </td>
                                        <td colspan="" rowspan="" headers="">
                                            @if( in_array('admin.vehiclecards.edit',@$user_access_router))
                                                <a href="{{ route('admin.vehiclecards.edit',['id'=> $vc->id]) }}" class="btn btn-success" title="sửa"><i class="fa fa-edit"></i></a>
                                            @endif
                                            @if( in_array('admin.vehiclecards.delete',@$user_access_router))
                                                <a href="{{ route('admin.vehiclecards.delete',['id'=> $vc->id]) }}" class="btn btn-danger" title="xóa" onclick="return confirm('Có chắc chắn xóa?')"><i class="fa fa-times"></i></a>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </form>
                    @endif
                </div>
                <div class="row mbm">
                    <div class="col-sm-3">
                        <span class="record-total">Hiển thị: {{$display_count}} / {{ $vehiclecards->total() }} kết quả</span>
                    </div>
                    <div class="col-sm-6 text-center">
                        <div class="pagination-panel">
                            {{ $vehiclecards->appends(Request::all())->onEachSide(1)->links() }}
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
                @if( in_array('admin.vehiclecards.insert',@$user_access_router))
                    <form action="{{ route('admin.vehiclecards.insert') }}" method="post" id="form-add-vehiclecard" class="form-validate form-horizontal">
                        {{ csrf_field() }}
                        <input type="hidden" name="hashtag">
                        <div class="modal-content">
                            <div class="modal-header bg-primary">
                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                                <h4 class="modal-title">Thêm mới thẻ</h4>
                            </div>
                            <div class="modal-body">
                                <div class="alert alert-danger alert_pop_add_vehiclecard" style="display: none;">
                                    <ul></ul>
                                </div>
                                <div class="row">
                                    <div class="col-sm-12">
                                        <div class="form-group">
                                            <div class="col-sm-2">
                                                <label for="in-re_name">Mã thẻ</label>
                                            </div>
                                            <div class="col-sm-8">
                                                <input type="text" name="code" id="in-code" class="form-control" placeholder="Mã thẻ">
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <div class="col-sm-2">
                                                 <label>Tòa nhà</label>
                                            </div>
                                            <div class="col-sm-8">
                                                  <select name="building_place_id" id="ip-place_id" class="form-control" style="width: 100%;">
                                                        <option value="">Chọn tòa nhà</option>
                                                  </select>
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
                                                <label>Loại phương tiện</label>
                                            </div>
                                            <div class="col-sm-8">
                                                <select name="vehicle_category_id" id="select-vc_type" class="form-control" style="width: 100%;">
                                                    <option value="">Chọn phương tiện</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <div class="col-sm-2">
                                                <label>Biển số</label>
                                            </div>
                                            <div class="col-sm-8">
                                                <select name="number" id="in-vc_vehicle_number" class="form-control" style="width: 100%;">
                                                    <option value="">Chọn biển số</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <div class="col-sm-2">
                                                <label>Ghi chú</label>
                                            </div>
                                            <div class="col-sm-8">
                                                <textarea class="form-control" placeholder="ghi chú" id="context" name="description"
                                                rows="3"></textarea>
                                            </div>
                                        </div>
                                       
                                    </div>
                                </div>

                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-danger pull-right" data-dismiss="modal"><i class="fa fa-close"></i>&nbsp;&nbsp;Hủy</button>
                                <button type="button" class="btn btn-primary btn-js-action-vehiclecard" style="margin-right: 5px;"><i class="fa fa-save"></i>&nbsp;&nbsp;Xác nhận</button>
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
            get_data_select_apartment1({
                object: '#ip-place_id,#place_id',
                url: '{{ route("admin.ajax.ajax_get_building_place") }}',
                data_id: 'id',
                data_text: 'name',
                data_code: 'code',
                title_default: 'Chọn tòa nhà'
            });
            function get_data_select_apartment1(options) {
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
                                    text: item[options.data_text]+' - '+item[options.data_code]
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
            function getNumberVehicle(apartment,cate){
                get_data_select2({
                    object: '#in-vc_vehicle_number',
                    url: '{{ url('admin/vehiclecards/ajax_get_vehiclecard') }}',
                    data_id: 'number',
                    data_text: 'number',
                    title_default: 'Chọn biển số',
                    apartment: apartment,
                    cate: cate
                });
            }
            get_data_select({
                object: '#ip-apartment',
                url: '{{ route("admin.ajax.ajax_get_apartment_with_place") }}',
                data_id: 'id',
                data_text: 'name',
                title_default: 'Chọn căn hộ'
            });
            get_data_select({
                object: '#ip-cate,#select-vc_type',
                url: '{{ route("admin.ajax.ajax_get_vehicle_cate") }}',
                data_id: 'id',
                data_text: 'name',
                title_default: 'Chọn loại phương tiện'
            });
            $("#ip-place_id").on('change', function(){ 
                if($("#ip-place_id").val()){
                    get_data_select({
                    object: '#ip-ap_id',
                    url: '{{ route("admin.ajax.ajax_get_apartment_with_place") }}',
                    data_id: 'id',
                    data_text: 'name',
                    title_default: 'Chọn căn hộ'
                    });
                }
            });
            function get_data_select(options) {
                $(options.object).select2({
                    ajax: {
                        url: options.url,
                        dataType: 'json',
                        data: function(params) {
                            var query = {
                                search: params.term,
                                place_id: $("#ip-place_id").val(),
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
            function get_data_select2(options) {
                $(options.object).select2({
                    ajax: {
                        url: options.url,
                        dataType: 'json',
                        data: function(params) {
                            var query = {
                                search: params.term,
                                apartment: options.apartment,
                                cate: options.cate,
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
            $(".btn-js-action-vehiclecard").on('click',function () {
                var _this = $(this);
                $(".alert_pop_add_vehiclecard").hide();
                var vehicle_number = $("#in-vc_vehicle_number").val();
                var code = $("#in-code").val();
                var type = $("#select-vc_type").val();
                var apartment = $("#ip-ap_id").val();
                if(code.length <=0){
                    $(".alert_pop_add_vehiclecard").show();
                    $(".alert_pop_add_vehiclecard ul").html('<li>Mã code không được bỏ trống</li>')
                }else if(code.length >=45){
                    $(".alert_pop_add_vehiclecard").show();
                    $(".alert_pop_add_vehiclecard ul").html('<li>Mã code không được nhỏ hơn 5 hoặc lớn hơn 45 ký tự</li>')
                }else if(apartment == ''){
                    $(".alert_pop_add_vehiclecard").show();
                    $(".alert_pop_add_vehiclecard ul").html('<li>Phải chọn căn hộ</li>')
                }else if(type == ''){
                    $(".alert_pop_add_vehiclecard").show();
                    $(".alert_pop_add_vehiclecard ul").html('<li>Phải chọn loại phương tiện</li>')
                }else if(!vehicle_number){
                    $(".alert_pop_add_vehiclecard").show();
                    $(".alert_pop_add_vehiclecard ul").html('<li>Biển số phải được chọn</li>')
                }else{
                    $("#form-add-vehiclecard").submit();
                }
            });
            $("#ip-ap_id,#select-vc_type").on('change',function () {
                var type = $("#select-vc_type").val();
                var apartment = $("#ip-ap_id").val();
                $('#in-vc_vehicle_number').html('<option value="">Chọn biển số</option>');
                if(type || apartment){
                    getNumberVehicle(apartment,type);
                }
            });
            $(document).ready(function () {
                $('.tag_check_card').on('click',function () {
                    var _this= $(this);
                    $.get('{{ route('admin.vehiclecards.ajax_change_status') }}', {
                        status: $(this).find('span').attr('status'),
                        id:  $(this).data('id')
                    }, function(data) {
                        if(data.status === 1){
                            _this.html('<span class="tag-relats bg-submain2 s-file" status="1">Active</span>');
                        }else{
                            _this.html('<span class="tag-relats bg-submain1 s-file" status="2">Inactive</span>');
                        }
                    });
                });
            });
            $(document).on('click', '.onoffswitch-label', function (e) {
                var div = $(this).parents('div.onoffswitch');
                var input = div.find('input');

                var id = input.attr('data-id');
                if (input.attr('checked')) {
                    var checked = 0;
                } else {
                    var checked = 1;
                }
                if (!requestSend) {
                    requestSend = true;
                    $.ajax({
                        url: input.attr('data-url'),
                        type: 'put',
                        data: {
                            id: id,
                            status: checked
                        },
                        success: function (response) {
                            if (response.success == true) {
                                toastr.success(response.message);
                            } else {
                                toastr.error(response.message);
                                setTimeout(function(){
                                     location.reload();
                                }, 1000);
                              
                            }
                            requestSend = false;
                        }
                    });
                } else {
                    e.preventDefault();
                }
            })
        });

        sidebar('Customers', 'index');
    </script>


@endsection
