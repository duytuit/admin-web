@extends('backend.layouts.master')

@section('content')
    <section class="content-header">
        <h1>
            Quản lý
            <small>Tài sản-Lịch bảo trì</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('admin.home') }}"><i class="fa fa-home"></i> Trang chủ</a></li>
            <li class="active">Quản lý tài sản</li>
        </ol>
    </section>
    <section class="content">
        <div class="row">
            <div class="box-body">
                <div class="col-md-12">
                    <!-- Custom Tabs -->
                    <div class="nav-tabs-custom">
                        <ul class="nav nav-tabs">
                            <li class="{{ str_contains(url()->current(),'handover') ? 'active' : null }}"><a href="{{ route('admin.asset-apartment.asset-handover.index') }}" >Bàn giao tài sản</a></li>
                            <li class="{{ !str_contains(url()->current(),'handover') ? 'active' : null }}"><a href="{{ route('admin.asset-apartment.asset.index') }}" >Tài sản</a></li>
                        </ul>
                        <div class="tab-content">
                            <div class="tab-pane {{ str_contains(url()->current(),'handover') ? 'active' : null }}" id="{{ route('admin.asset-apartment.asset-handover.index') }}">
                                    <form id="form-search-advance" action="{{ route('admin.asset-apartment.asset-handover.index') }}" method="get">
                                        <div class="row form-group">
                                                <div class="col-12 col-md-12">
                                                        <div class="row">
                                                                <div class="col-sm-3">
                                                                    <a href="{{ route('admin.asset-apartment.asset-handover.create') }}" class="btn btn-success"><i class="fa fa-edit"></i>Thêm mới</a>
                                                                    <a href="{{ route('admin.asset-apartment.asset-handover.import') }}" class="btn btn-success "><i class="fa fa-edit"></i>Import</a>
                                                                    <a href="{{ route('admin.asset-apartment.asset-handover.export') }}" class="btn btn-success"><i class="fa fa-edit"></i>Export</a>
                                                                </div>
                                                                <div class="col-sm-2">
                                                                    <input type="text" name="keyword" class="form-control"
                                                                        placeholder="Nhập nội dung tìm kiếm" value="{{ @$filter['keyword'] }}">
                                                                </div>
                                                                <div class="col-sm-2" style="padding-left:0">
                                                                    <select name="ip_place_id" id="ip-place_id" class="form-control" style="width: 100%;">
                                                                        <option value="">Chọn tòa nhà</option>
                                                                    </select>
                                                                </div>
                                                                <div class="col-sm-2" style="padding-left:0">
                                                                    <select name="bdc_apartment_id" id="ip-apartment" class="form-control">
                                                                        <option value="">Căn hộ</option>
                                                                    </select>
                                                                </div>
                                                                <div class="col-sm-2">
                                                                    <select name="asset_id" id="asset_id" class="form-control">
                                                                         <option value="">Mã tài sản</option>
                                                                    </select>
                                                                </div>
                                                        </div>
                                                </div>
                                        </div>
                                        <div class="row form-group">
                                                <div class="col-12 col-md-12">
                                                   <div class="row">
                                                        <div class="col-sm-1">
                                                            <button type="button" data-toggle="dropdown" class="btn btn-primary dropdown-toggle pull-left" style="margin-right: 10px;margin-top: 25px">Tác vụ&nbsp;<span class="caret"></span></button>
                                                            <ul class="dropdown-menu">
                                                                <li>
                                                                    <a href="javascript:" type="button" class="btn-action" data-target="#form-asset-handover" data-method="delete">
                                                                        <i class="fa fa-trash text-danger"></i>&nbsp; Xóa
                                                                    </a>
                                                                    <a href="javascript:" class="btn_action_create_notify">
                                                                        <i class="fa fa-send text-success"></i>&nbsp; Gửi thông báo
                                                                    </a>
                                                                </li>
                                                            </ul>
                                                        </div>
                                                        <div class="col-sm-2">
                                                            <label for="">Ngày dự kiến bàn giao</label>
                                                            <div class="input-group date">
                                                                <div class="input-group-addon">
                                                                    <i class="fa fa-calendar"></i>
                                                                </div>
                                                                <input type="text" class="form-control pull-right date_picker" name="from_date"
                                                                    value="{{ @$filter['from_date'] }}" placeholder="Từ..." autocomplete="off">
                                                            </div>
                                                        </div>
                                                        <div class="col-sm-2">
                                                            <div class="input-group date" style="margin-top: 25px;">
                                                                <div class="input-group-addon">
                                                                    <i class="fa fa-calendar"></i>
                                                                </div>
                                                                <input type="text" class="form-control pull-right date_picker" name="to_date"
                                                                    value="{{ @$filter['to_date'] }}" placeholder="Đến..." autocomplete="off">
                                                            </div>
                                                        </div>
                                                        <div class="col-sm-2">
                                                            <label for="">Ngày ngày bàn giao</label>
                                                            <div class="input-group date">
                                                                <div class="input-group-addon">
                                                                    <i class="fa fa-calendar"></i>
                                                                </div>
                                                                <input type="text" class="form-control pull-right date_picker" name="created_at_from_date"
                                                                    value="{{ @$filter['created_at_from_date'] }}" placeholder="Từ..." autocomplete="off">
                                                            </div>
                                                        </div>
                                                        <div class="col-sm-2">
                                                            <div class="input-group date" style="margin-top: 25px;">
                                                                <div class="input-group-addon">
                                                                    <i class="fa fa-calendar"></i>
                                                                </div>
                                                                <input type="text" class="form-control pull-right date_picker" name="created_at_to_date"
                                                                    value="{{ @$filter['created_at_to_date'] }}" placeholder="Đến..." autocomplete="off">
                                                            </div>
                                                        </div>
                                                        <div class="col-sm-2">
                                                            <select name="status" class="form-control" style="margin-top: 25px;">
                                                                <option value="" selected>Trạng thái</option>
                                                                @foreach($list_asset_handover as $key => $value)
                                                                    <option value="{{$value['text']}}" {{ $value['text'] == @$filter['status'] ? 'selected' : '' }}>{{$value['value']}}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="col-sm-1">
                                                            <button class="btn btn-info search-asset" style="margin-top: 25px;"><i class="fa fa-search"></i></button>
                                                        </div>
                                                    </div>
                                                </div>
                                        </div>
                                </form><!-- END #form-search-advance -->
                                <form id="form-asset-handover" action="{{ route('admin.asset-apartment.asset-handover.action') }}" method="post">
                                        @csrf
                                        <input type="hidden" name="method" value="" />
                                        <div class="table-responsive">
                                            <table class="table table-hover table-striped table-bordered">
                                                <thead class="bg-primary">
                                                <tr>
                                                    <th><input type="checkbox" class="iCheck checkAll" data-target=".checkSingle" /></th>
                                                    <th>STT</th>
                                                    <th>Mã tài sản</th>
                                                    <th>Tên tài sản</th>
                                                    <th>Khách hàng</th>
                                                    <th>Căn hộ</th>
                                                    <th>Bảo hành(tháng)</th>
                                                    <th>Ảnh/tài liệu</th>
                                                    <th>Mô tả</th>
                                                    <th>Dự kiến BG</th>
                                                    <th>Ngày bàn giao</th>
                                                    <th>Tình trạng</th>
                                                    <th width="10%">Thao tác</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                    @if($asset_handovers->count() > 0)
                                                                @foreach($asset_handovers as $key => $value)
                                                                    <tr class="list_asset_hand_over">
                                                                        <td><input type="checkbox" name="ids[]" data-apartment-id="{{@$value->apartment->id}}" value="{{ $value->id }}" class="iCheck checkSingle" /></td>
                                                                        <td>{{ @($key + 1) + ($asset_handovers->currentPage() - 1) * $asset_handovers->perPage() }}</td>
                                                                        <td>{{ @$value->asset->code }}</td>
                                                                        <td>{{ @$value->asset->name }}</td>
                                                                        <td>{{ $value->customer }}</td>
                                                                        <td>{{ @$value->apartment->name }}</td>
                                                                        <td style="text-align: center">{{ $value->warranty_period }}</td>
                                                                        @if($value->documents)
                                                                            <td style="text-align: center">
                                                                                <a href="{{ route('admin.asset-apartment.asset-handover.edit', $value->id) }}" title="xem chi tiết tài liệu">{{count(json_decode($value->documents,true))}} đính kèm</a>
                                                                            </td>
                                                                        @else
                                                                             <td></td>
                                                                        @endif
                                                                        <td>{{ $value->description }}</td>
                                                                        <td>{{ $value->date_expected ? date('d/m/Y', strtotime($value->date_expected)) : '--/--/----' }}</td>
                                                                        <td> <input type="text" class="form-control date_picker custom_date_picker" data-id="{{$value->id}}" id="from_date_{{$value->id}}" value="{{$value->date_of_delivery ? date('d-m-Y', strtotime($value->date_of_delivery)) : '--/--/----'}}"></td>
                                                                        <td>
                                                                            <select class="form-control customer_status_confirm" style="width:100%" data-id="{{$value->id}}">
                                                                                @foreach($list_asset_handover as $key => $value_1)
                                                                                <option value="{{$value_1['text']}}" @if($value_1['text'] == $value->status) selected @endif>{{$value_1['value']}}</option>
                                                                                @endforeach
                                                                            </select>
                                                                        </td>
                                                                        <td>
                                                                            <a href="{{ route('admin.asset-apartment.asset-handover.edit', $value->id) }}"
                                                                            class="btn btn-sm btn-primary" title="Sửa tài sản"><i
                                                                                        class="fa fa-pencil"></i></a>
                                                                        </td>
                                                                    </tr>
                                                                @endforeach
                                                        @else
                                                            <tr><td colspan="13" class="text-center">Không có kết quả tìm kiếm</td></tr>
                                                        @endif
                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="row mbm">
                                                <div class="col-sm-3">
                                                    <span class="record-total">Hiển thị {{ $asset_handovers->count() }} / {{ $asset_handovers->total() }} kết quả</span>
                                                </div>
                                                <div class="col-sm-6 text-center">
                                                    <div class="pagination-panel">
                                                        {{ $asset_handovers->appends(request()->input())->links() }}
                                                    </div>
                                                </div>
                                                <div class="col-sm-3 text-right">
                                                    <span class="form-inline">
                                                        Hiển thị
                                                        <select name="per_page" class="form-control" data-target="#form-asset-handover">
                                                            @php $list = [10, 20, 50, 100, 200]; @endphp
                                                            @foreach ($list as $num)
                                                                <option value="{{ $num }}" {{ $num == $per_page ? 'selected' : '' }}>{{ $num }}</option>
                                                            @endforeach
                                                        </select>
                                                    </span>
                                                </div>
                                        </div>
                                </form><!-- END #form-users --> 
                            </div>
                        </div>
                        <!-- /.tab-content -->
                    </div>
                    <!-- nav-tabs-custom -->
                </div>
            </div>
        </div>
        <input type="hidden" value="{{isset($filter['ip_place_id']) ? $filter['ip_place_id'] : ''}}" id="ip_place_id_search">
        <input type="hidden" value="{{isset($filter['bdc_apartment_id']) ? $filter['bdc_apartment_id'] : ''}}" id="bdc_apartment_id_search">
        <input type="hidden" value="{{isset($get_apartment) ?json_encode($get_apartment) : ''}}" id="get_apartment">
        <input type="hidden" value="{{isset($get_place_building) ?json_encode($get_place_building) : ''}}" id="get_place_building">

        <input type="hidden" value="{{isset($filter['asset_id']) ? $filter['asset_id'] : ''}}" id="asset_id_search">
        <input type="hidden" value="{{isset($get_asset_apartment) ?json_encode($get_asset_apartment) : ''}}" id="get_asset_apartment">
    </section>
@endsection
@include('asset-apartments.tabs.asset-handover.modal.send-notify')
@section('javascript')
    <script>
        //Date picker
        $('input.date_picker').datepicker({
        autoclose: true,
        dateFormat: "dd-mm-yy"
        }).val();
        $(".list_asset_hand_over").on("change", ".custom_date_picker", function(e) {
            e.preventDefault();
            var _token = $('meta[name="csrf-token"]').attr('content');
            $.ajax({
                url: "{{ route('admin.asset-apartment.asset-handover.change_date_of_delivery') }}",
                type: 'POST',
                data: {
                    _token: _token,
                    id: $(this).attr("data-id"),
                    date_of_delivery: $(this).val()
                },
                success: function(response) {
                    if (response.success == true) {
                        toastr.success(response.message);
                    } else {
                        toastr.error(response.message);
                        setTimeout(function() {
                            location.reload();
                        }, 1000);

                    }
                }
            });
        });
        $(".list_asset_hand_over").on("change", ".customer_status_confirm", function(e) {
            e.preventDefault();
            var _token = $('meta[name="csrf-token"]').attr('content');
            let date_of_delivery = new Date().toJSON().slice(0,10).split('-').reverse().join('-');
            let status_asset_handover = $(this).parents(".list_asset_hand_over").find('.customer_status_confirm').val();
            if(status_asset_handover == '1'){
                $(this).parents(".list_asset_hand_over").find('.custom_date_picker').val(date_of_delivery)
            }else{
                $(this).parents(".list_asset_hand_over").find('.custom_date_picker').val('--/--/----')
            }
            
            $.ajax({
                url: "{{ route('admin.asset-apartment.asset-handover.change_status') }}",
                type: 'POST',
                data: {
                    _token: _token,
                    id: $(this).parents(".list_asset_hand_over").find('.customer_status_confirm').attr("data-id"),
                    status: $(this).parents(".list_asset_hand_over").find('.customer_status_confirm').val(),
                    date_of_delivery : status_asset_handover == '1' ? date_of_delivery : null,
                },
                success: function(response) {
                    if (response.success == true) {
                        toastr.success(response.message);
                    } else {
                        toastr.error(response.message);
                        setTimeout(function() {
                            location.reload();
                        }, 1000);

                    }
                }
            });
        });
        $(document).ready(() => {
            $('#custom_template_email').change(function() {
                let value = $(this).val();
                if(value == 'noi_dung'){
                    $('#form_noi_dung').show();
                    $('#form_mac_dinh').hide();
                }else{
                    $('#form_noi_dung').hide();
                    $('#form_mac_dinh').show();
                }

            });
            
            if($('#custom_template_email').val() == 'noi_dung'){
                $('#form_noi_dung').show();
                $('#form_mac_dinh').hide();
            }else{
                $('#form_noi_dung').hide();
                $('#form_mac_dinh').show();
            }
            if($('#bdc_apartment_id_search').val())
            {
                var bdc_apartment_id_all = $('#get_apartment').val();
                var obj_bdc_apartment_id_all = JSON.parse(bdc_apartment_id_all);
                var new_bdc_apartment_id_all = [];
                            new_bdc_apartment_id_all.push({
                                id:obj_bdc_apartment_id_all["id"],
                                text:obj_bdc_apartment_id_all["name"]
                            });
                
                $('#ip-apartment').select2({data:new_bdc_apartment_id_all});
                $('#ip-apartment').find('option').attr('selected', true);
                $('#ip-apartment').select2();
            }
            if($('#ip_place_id_search').val())
            {
                var get_place_building_all = $('#get_place_building').val();
                var obj_get_place_building_all = JSON.parse(get_place_building_all);
                var new_get_place_building_all = [];
                            new_get_place_building_all.push({
                                id:obj_get_place_building_all["id"],
                                text:obj_get_place_building_all["name"]
                            });
                
                $('#ip-place_id').select2({data:new_get_place_building_all});
                $('#ip-place_id').find('option').attr('selected', true);
                $('#ip-place_id').select2();
            }

            if($('#asset_id_search').val())
            {
                var get_asset_apartment_all = $('#get_asset_apartment').val();
                var obj_get_asset_apartment_all = JSON.parse(get_asset_apartment_all);
                var new_get_asset_apartment_all = [];
                    new_get_asset_apartment_all.push({
                                id:obj_get_asset_apartment_all["id"],
                                text:obj_get_asset_apartment_all["code"]
                            });
                
                $('#asset_id').select2({data:new_get_asset_apartment_all});
                $('#asset_id').find('option').attr('selected', true);
                $('#asset_id').select2();
            }

            // ========================
            get_data_select_apartment1({
                object: '#ip-place_id',
                url: '{{url('admin/apartments/ajax_get_building_place')}}',
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
                                    text: item[options.data_text] + ' - ' + item[options.data_code]
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
            get_data_select({
                object: '#ip-apartment',
                url: '{{ url('admin/apartments/ajax_get_apartment') }}',
                data_id: 'id',
                data_text: 'name',
                title_default: 'Chọn căn hộ'
            });
            $("#ip-place_id").on('change', function() {
                if ($("#ip-place_id").val()) {
                    get_data_select({
                        object: '#ip-apartment',
                        url: '{{ url('admin/apartments/ajax_get_apartment_with_place') }}',
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
            // =====================================tài sản=====================================
            
            get_data_select_code_asset({
                        object: '#asset_id',
                        url: '{{ url('admin/asset-apartment/asset/ajaxGetSelect') }}',
                        data_id: 'id',
                        data_text: 'code',
                        title_default: 'Mã tài sản'
            });
            function get_data_select_code_asset(options) {
                $(options.object).select2({
                    ajax: {
                        url: options.url,
                        dataType: 'json',
                        data: function(params) {
                            var query = {
                                search: params.term,
                                asset_category_id: "no_category_id"
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
        });
        // show modal gửi thông báo
        $('.btn_action_create_notify').click(function() {

            var ids = [];
            var asset_handover = [];
            var div = $('div.icheckbox_square-green[aria-checked="true"]');
                div.each(function(index, value) {
                    var id = $(value).find('input.checkSingle').data('apartment-id');
                    var asset_handover_id = $(value).find('input.checkSingle').val();
                    if (id) {
                        ids.push(id);
                    }
                    // if (asset_handover_id && customers_phone.findIndex(item_1 => item_1.text === phone) === -1) { // check nếu chưa có trong mảng thì push vào
                    //     customers_phone.push({
                    //                     id:phone,
                    //                     text:phone
                    //                 });
                    // }
                    if (asset_handover_id) { 
                        asset_handover.push(parseInt(asset_handover_id));
                    }
                });
                if(ids.length > 0){
                    $('#send_notify_apartment').modal('show');
                    $('#ip_apartment_send_notify').val(ids).change();
                }
                if(asset_handover.length > 0){
                    let ids_asset = JSON.stringify(asset_handover)
                    $('#asset_handover_ids').val(ids_asset);
                }
            
        })
        $('.post_save').click(function() {
            var desc = CKEDITOR.instances['content'].getData();
            var values = $('#create_form_notify_apartment').serializeArray();
            values.find(input => input.name == 'content').value = desc;
            var customers_id = $('#ip_apartment_send_notify').val();
            var json_customers_id = JSON.stringify(customers_id);
            values.push({
                name: "customers_id",
                value: json_customers_id
            });

            $.ajax({
                url: "{{ route('admin.posts_customers.save_asset_hand_over') }}",
                type: 'POST',
                data: values,
                success: function(response) {
                    if (response.success == true) {
                        toastr.success(response.message);
                        setTimeout(function() {
                            location.reload();
                        }, 1000);
                    } else {
                        toastr.error(response.message);
                        setTimeout(function() {
                            location.reload();
                        }, 1000);
                    }
                }
            });

        })
    </script>
@endsection
