@extends('backend.layouts.master')

@section('content')
    <section class="content-header">
        <h1>
            Quản lý
            <small>Chi tiết đánh giá</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('admin.home') }}"><i class="fa fa-home"></i> Trang chủ</a></li>
            <li class="active">Chi tiết đánh giá</li>
        </ol>
    </section>
    <section class="content">
        <div class="row">
            <div class="box-body">
                <div class="col-md-12">
                    <!-- Custom Tabs -->
                    <div class="nav-tabs-custom">
                        <ul class="nav nav-tabs">
                            <li class="{{ str_contains(url()->current(),'total') ? 'active' : null }}"><a href="{{ route('admin.rated_service.total') }}" >Tổng hợp đánh giá</a></li>
                            <li class="{{ !str_contains(url()->current(),'total') ? 'active' : null }}"><a href="{{ route('admin.rated_service.detail') }}" >Chi tiết đánh giá</a></li>
                        </ul>
                        <div class="tab-content">
                            <div class="tab-pane {{ !str_contains(url()->current(),'total') ? 'active' : null }}" id="{{ route('admin.rated_service.detail') }}">
                                    <form id="form-search-advance" action="{{ route('admin.rated_service.detail') }}" method="get">
                                        <div class="row">
                                                <div class="col-sm-2 form-group">
                                                    <input type="text" name="keyword" class="form-control"
                                                        placeholder="Tìm khách hàng, nhân viên, căn hộ..." value="{{ @$filter['keyword'] }}">
                                                </div>
                                                <div class="col-sm-2 form-group">
                                                    <select name="bdc_department_id" class="form-control">
                                                        <option value="" selected>Bộ phận</option>
                                                        @foreach ($bo_phan as $item)
                                                            <option value="{{ @$item->id }}" {{ @$item->id == @$filter['bdc_department_id'] ? 'selected' : '' }}>{{ @$item->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-sm-2 form-group">
                                                    <div class="input-group date">
                                                        <div class="input-group-addon">
                                                            <i class="fa fa-calendar"></i>
                                                        </div>
                                                        <input type="text" class="form-control pull-right date_picker" name="from_date"
                                                            value="{{ @$filter['from_date'] }}" placeholder="Từ..." autocomplete="off">
                                                    </div>
                                                </div>
                                                <div class="col-sm-2 form-group">
                                                    <div class="input-group date">
                                                        <div class="input-group-addon">
                                                            <i class="fa fa-calendar"></i>
                                                        </div>
                                                        <input type="text" class="form-control pull-right date_picker" name="to_date"
                                                            value="{{ @$filter['to_date'] }}" placeholder="Đến..." autocomplete="off">
                                                    </div>
                                                </div>
                                                <div class="col-sm-2 form-group">
                                                    <select name="from_where" class="form-control">
                                                        <option value="" selected>Nguồn đánh giá</option>
                                                        @foreach ($app_danh_gia as $key => $item)
                                                            <option value="{{ $key }}" {{ @$filter['from_where'] === $key ? 'selected' : '' }}>{{ $item }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-sm-2 form-group">
                                                    <select name="type_object" class="form-control">
                                                        <option value="" selected>Đối tượng</option>
                                                        <option value="1" {{ @$filter['type_object'] == 1 ? 'selected' : ''}}>Cư dân</option>
                                                        <option value="2" {{ @$filter['type_object'] == 2 ? 'selected' : ''}}>Vãng lai</option>
                                                    </select>
                                                </div>
                                                <div class="col-sm-2 form-group">
                                                    <button class="btn btn-info search-asset"><i class="fa fa-search"></i></button>
                                                    <a href="{{ route('admin.rated_service.export_detail',Request::all()) }}" class="btn btn-success"><i class="fa fa-edit"></i>Export</a>
                                                </div>
                                        </div>
                                </form><!-- END #form-search-advance -->
                                <form id="form-chi-tiet" action="{{ route('admin.rated_service.action') }}" method="post">
                                        @csrf
                                        <input type="hidden" name="method" value="" />
                                        <div class="table-responsive">
                                            <table class="table table-hover table-striped table-bordered">
                                                <thead class="bg-primary">
                                                <tr>
                                                    <th>STT</th>
                                                    <th>Khách hàng</th>
                                                    <th>Căn hộ</th>
                                                    <th>Nguồn đánh giá</th>
                                                    <th>Nhân viên/ nhà thầu</th>
                                                    <th>Mã nhân viên/ Nhà thầu</th>
                                                    <th>Bộ phận</th>
                                                    <th>SĐT</th>
                                                    <th>Email</th>
                                                    <th>Đánh giá</th>
                                                    <th>Ghi chú</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                    @if($danh_gia_chi_tiet->count() > 0)
                                                                @foreach($danh_gia_chi_tiet as $key => $value)
                                                                    @php
                                                                         $department = App\Models\Department\Department::get_detail_department_by_id($value->department_id);
                                                                    @endphp
                                                                    <tr class="list_asset_hand_over">
                                                                        <td>{{ @($key + 1) + ($danh_gia_chi_tiet->currentPage() - 1) * $danh_gia_chi_tiet->perPage() }}</td>
                                                                        <td>{{ @$value->customer_name }}</td>
                                                                        <td>{{ @$value->apartment_name }}</td>
                                                                        <td>
                                                                           {{$app_danh_gia[$value->from_where]}}
                                                                        </td>
                                                                        <td>{{ @$value->user_info_rated->display_name }}</td>
                                                                        <td>{{ @$value->user_info_rated->staff_code }}</td>
                                                                        <td>{{ @$department->name }}</td>
                                                                        <td>{{ @$value->user->phone ?? @$value->phone }}</td>
                                                                        <td>{{ @$value->user->email ?? @$value->email}}</td>
                                                                        <td>
                                                                            @if ($value->point == -3)
                                                                                    <strong>Rất không hài lòng</strong>
                                                                                    <p>{{ $value->updated_at->format('d-m-Y H:i') }}</p>
                                                                            @endif
                                                                            @if ($value->point == -1)
                                                                                    <strong>Chưa hài lòng</strong>
                                                                                    <p>{{ $value->updated_at->format('d-m-Y H:i') }}</p>
                                                                            @endif
                                                                            @if ($value->point == 1)
                                                                                    <strong>Bình thường</strong>
                                                                                    <p>{{ $value->updated_at->format('d-m-Y H:i') }}</p>
                                                                            @endif
                                                                            @if ($value->point == 3)
                                                                                    <strong>Hài lòng</strong>
                                                                                    <p>{{ $value->updated_at->format('d-m-Y H:i') }}</p>
                                                                            @endif
                                                                            @if ($value->point == 5)
                                                                                    <strong>Rất hài lòng</strong>
                                                                                    <p>{{ $value->updated_at->format('d-m-Y H:i') }}</p>
                                                                            @endif
                                                                        </td>
                                                                        <td>{{$value->description}}</td>
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
                                                    <span class="record-total">Hiển thị {{ $danh_gia_chi_tiet->count() }} / {{ $danh_gia_chi_tiet->total() }} kết quả</span>
                                                </div>
                                                <div class="col-sm-6 text-center">
                                                    <div class="pagination-panel">
                                                        {{ $danh_gia_chi_tiet->appends(request()->input())->links() }}
                                                    </div>
                                                </div>
                                                <div class="col-sm-3 text-right">
                                                    <span class="form-inline">
                                                        Hiển thị
                                                        <select name="per_page" class="form-control" data-target="#form-chi-tiet">
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
    </section>
@endsection
{{-- @include('transaction-payment.modal.confirm_reject') --}}
@section('javascript')
    <script>
        //Date picker
        $('input.date_picker').datepicker({
        autoclose: true,
        dateFormat: "dd-mm-yy"
        }).val();

        $('.edit-confirm-transaction').click(function() {
            $('#transactionPaymentId').val($(this).attr('id'));
            $('#confirm_reject').modal('show');
        });
        submitAjaxForm('#submit_confirm_reject', '#create_confirm_reject', '.create_', '.message_zone_create');

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
    </script>
@endsection
