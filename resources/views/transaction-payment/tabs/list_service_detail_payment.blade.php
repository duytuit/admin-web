@extends('backend.layouts.master')

@section('content')
    <section class="content-header">
        <h1>
            Quản lý
            <small>Quản lý giao dịch banking</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('admin.home') }}"><i class="fa fa-home"></i> Trang chủ</a></li>
            <li class="active">Quản lý giao dịch banking</li>
        </ol>
    </section>
    <section class="content">
        <div class="row">
            <div class="box-body">
                <div class="col-md-12">
                    <!-- Custom Tabs -->
                    <div class="nav-tabs-custom">
                        <ul class="nav nav-tabs">
                            <li class="{{ str_contains(url()->current(),'list') ? 'active' : null }}"><a href="{{ route('admin.transactionpayment.index') }}" >Giao dịch Banking</a></li>
                            <li class="{{ !str_contains(url()->current(),'list') ? 'active' : null }}"><a href="{{ route('admin.transactionpayment.service_detail_payment') }}" >Khoản hoạch toán phiếu thu Banking</a></li>
                        </ul>
                        <div class="tab-content">
                            <div class="tab-pane {{ !str_contains(url()->current(),'list') ? 'active' : null }}" id="{{ route('admin.transactionpayment.service_detail_payment') }}">
                                    <form id="form-search-advance" action="{{ route('admin.transactionpayment.service_detail_payment') }}" method="get">
                                        <div class="row form-group">
                                            <div class="col-12 col-md-12">
                                               <div class="row col-md-12">
                                                    <div class="col-sm-2" style="padding-left:0">
                                                        <select name="bdc_apartment_id" id="ip-apartment" class="form-control">
                                                            <option value="">Căn hộ</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-sm-2">
                                                        <div class="input-group date">
                                                            <div class="input-group-addon">
                                                                <i class="fa fa-calendar"></i>
                                                            </div>
                                                            <input type="text" class="form-control pull-right date_picker" name="from_date"
                                                                value="{{ @$filter['from_date'] }}" placeholder="Từ..." autocomplete="off">
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-2">
                                                        <div class="input-group date">
                                                            <div class="input-group-addon">
                                                                <i class="fa fa-calendar"></i>
                                                            </div>
                                                            <input type="text" class="form-control pull-right date_picker" name="to_date"
                                                                value="{{ @$filter['to_date'] }}" placeholder="Đến..." autocomplete="off">
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-1">
                                                        <button class="btn btn-info search-asset"><i class="fa fa-search"></i></button>
                                                    </div>
                                                    <div class="col-sm-1">
                                                        <a href="{{ route('admin.transactionpayment.export_service_detail_payment') }}" class="btn btn-success"><i class="fa fa-edit"></i>Export</a>
                                                    </div>
                                                </div>
                                            </div>
                                    </div>
                                </form><!-- END #form-search-advance -->
                                <form id="form-service-detail-payment" action="{{ route('admin.transactionpayment.action_service_detail_payment') }}" method="post">
                                        @csrf
                                        <input type="hidden" name="method" value="" />
                                        <div class="table-responsive">
                                            <table class="table table-hover table-striped table-bordered">
                                                <thead class="bg-primary">
                                                <tr>
                                                    <th>STT</th>
                                                    <th>Căn hộ</th>
                                                    <th>Chủ hộ</th>
                                                    <th>SĐT</th>
                                                    <th>STK</th>
                                                    <th>Tổng tiền đã nhận</th>
                                                    <th>Tổng tiền đã TT</th>
                                                    <th>Số tiền còn lại</th>
                                                    <th>Thao tác</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                    @if($transactionPaymentReceipt->count() > 0)
                                                            @foreach($transactionPaymentReceipt as $key => $value)
                                                                <tr class="list_asset_hand_over">
                                                                    <td>{{ @($key + 1) + ($transactionPaymentReceipt->currentPage() - 1) * $transactionPaymentReceipt->perPage() }}</td>
                                                                    <td>{{ @$value->name }}</td>
                                                                    <td>{{ @$value->virtual_acc_name }}</td>
                                                                    <td>{{ @$value->virtual_acc_mobile }}</td>
                                                                    <td>{{ @$value->virtual_acc_id }}</td>
                                                                    <td>{{ @number_format((int)$value->dong_tien) }}</td>
                                                                    <td>
                                                                        @php
                                                                            $thanh_toan = (int)$value->chi_tien - (int)$value->hoan_tien;
                                                                        @endphp
                                                                        {{ @number_format($thanh_toan) }}
                                                                    </td>
                                                                    <td>{{ @number_format((int)$value->so_du) }}</td>
                                                                    <td>
                                                                        <a href="{{ route('admin.receipt.create',['apartmentId'=> $value->bdc_apartment_id]) }}" class="btn btn-sm btn-success" title="lập phiếu thu"><i class="fa fa-plus"></i></a>
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
                                                    <span class="record-total">Hiển thị {{ $transactionPaymentReceipt->count() }} / {{ $transactionPaymentReceipt->total() }} kết quả</span>
                                                </div>
                                                <div class="col-sm-6 text-center">
                                                    <div class="pagination-panel">
                                                        {{ $transactionPaymentReceipt->appends(request()->input())->links() }}
                                                    </div>
                                                </div>
                                                <div class="col-sm-3 text-right">
                                                    <span class="form-inline">
                                                        Hiển thị
                                                        <select name="per_page" class="form-control" data-target="#form-service-detail-payment">
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
{{-- @include('asset-apartments.tabs.asset-handover.modal.send-notify') --}}
@section('javascript')
    <script>
        //Date picker
        $('input.date_picker').datepicker({
        autoclose: true,
        dateFormat: "dd-mm-yy"
        }).val();
       
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
