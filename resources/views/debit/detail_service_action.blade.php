@extends('backend.layouts.master')

@section('content')
    <section class="content-header">
        <h1>
            Quản lý kế toán
            <small>Chi tiết bảng kê - Dịch vụ</small> 
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('admin.home') }}"><i class="fa fa-home"></i> Trang chủ</a></li>
            <li class="active">Chi tiết bảng kê - Dịch vụ</li>
        </ol>
    </section>

    <section class="content">
        <div class="box box-primary">
            <div class="box-body font-weight-bold">
                <h3>Chi tiết bảng kê - Dịch vụ
                    <small>(Đã xử lý công nợ kì hiện tại - <i class="text-red"
                                                              style="font-weight: bolder">{{\Carbon\Carbon::now()->month}}
                            /{{\Carbon\Carbon::now()->year}}</i>)
                    </small>
                </h3>
            </div>
            <div class="box-body">
                {{-- @include('layouts.head-building') --}}
            </div>
            <div class="box-body">
                <form id="form-search-advance" action="{{route('admin.debit.detail_service_action')}}" method="get">
                    <div id="search-advance" class="search-advance">
                        <div class="row">
                            <div class="col-sm-2">
                                <input type="text" class="form-control" name="bill_code" id="bill_code" value="{{@$filter['bill_code']}}" placeholder="Mã BK...">
                            </div>
                            <div class="col-sm-2">
                                <input type="number" class="form-control" name="new_sumery" id="new_sumery" value="{{@$filter['new_sumery']}}" placeholder="Còn nợ...">
                            </div>
                            <div class="col-sm-1" style="padding-left:0">
                                <select name="ip_place_id" id="ip-place_id" class="form-control" style="width: 100%;">
                                    <option value="">Chọn tòa nhà</option>
                                    <?php $place_building = isset($get_place_building) ? $get_place_building : '' ?>
                                    @if($place_building)
                                    <option value="{{$place_building->id}}" selected>{{$place_building->name}}</option>
                                    @endif
                                </select>
                            </div>
                            <div class="col-sm-1" style="padding-left:0">
                                <select name="bdc_apartment_id" id="ip-apartment"  class="form-control">
                                    <option value="">Căn hộ</option>
                                        <?php $apartment = isset($get_apartment) ? $get_apartment: '' ?>
                                    @if($apartment)
                                    <option value="{{$apartment->id}}" selected>{{$apartment->name}}</option>
                                    @endif
                                </select>
                            </div>
                            <div class="col-sm-1">
                                <select name="cycle_name" class="form-control">
                                    <option value="" selected>Kì bảng kê</option>
                                    @foreach($cycle_names as $cycle_name)
                                        <option value="{{ $cycle_name }}"  @if(@$filter['cycle_name'] ==  $cycle_name) selected @endif>{{ $cycle_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-sm-2">
                                <div class="input-group date">
                                    <div class="input-group-addon">
                                        <i class="fa fa-calendar"></i>
                                    </div>
                                    <input type="text" class="form-control date_picker" name="from_date" id="from_date" value="{{@$filter['from_date']}}" placeholder="Từ..." autocomplete="off">
                                </div>
                            </div>
                            <div class="col-sm-2">
                                <div class="input-group date">
                                    <div class="input-group-addon">
                                        <i class="fa fa-calendar"></i>
                                    </div>
                                    <input type="text" class="form-control date_picker" name="to_date" id="to_date" value="{{@$filter['to_date']}}" placeholder="Đến..." autocomplete="off">
                                </div>
                            </div>
                            <div class="col-sm-1">
                                <select name="bdc_service_id" class="form-control">
                                    <option value="" selected>Dịch vụ...</option>
                                    @foreach($serviceBuildingFilter as $serviceBuilding)
                                        <option value="{{ $serviceBuilding->id }}"  @if(@$filter['bdc_service_id'] ==  $serviceBuilding->id) selected @endif>{{ $serviceBuilding->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-sm-1">
                                <select name="service_group" class="form-control">
                                    <option value="" selected>Phí dịch vụ...</option>
                                    <option value="1" @if(@$filter['service_group'] ==  1) selected @endif>Phí công ty</option>
                                    <option value="2" @if(@$filter['service_group'] ==  2) selected @endif>Phí thu hộ</option>
                                    <option value="3" @if(@$filter['service_group'] ==  3) selected @endif>Phí chủ đầu tư</option>
                                    <option value="4" @if(@$filter['service_group'] ==  4) selected @endif>Phí ban quản trị</option>
                                </select>
                            </div>
                            <div class="col-sm-1">
                                <select name="electric_meter_type" class="form-control">
                                    <option value="điện" selected>Điện</option>
                                    <option value="nước" @if(@$filter['electric_meter_type'] ==  'nước') selected @endif>Nước</option>
                                </select>
                            </div>
                            <div class="col-sm-2">
                                <button type="submit" class="btn btn-info"><i class="fa fa-search"></i> Tìm kiếm</button>
                                <?php
                                    $param = "?bdc_service_id=" . @$filter['bdc_service_id'] . "&cycle_name=" . @$filter['cycle_name'] . "&bdc_bill_id=" . @$filter['bdc_bill_id'];
                                ?>
                                <a class="btn btn-info" href="{{ route('admin.debit.exportExcel') }}{{ $param }}">Export</a>
                               
                            </div>
                            <div class="col-sm-2">
                                <a class="btn btn-info" href="{{ route('admin.debit.export_meter_water',Request::all()) }}">Export Điện Nước</a>
                            </div>
                            @if(\Auth::user()->isadmin == 1)
                                <div class="col-sm-1">
                                    <button type="button" data-toggle="dropdown" class="btn btn-primary dropdown-toggle pull-left" style="margin-right: 10px;">Tác vụ&nbsp;<span class="caret"></span></button>
                                    <ul class="dropdown-menu">
                                        <li>
                                            <a href="javascript:" type="button" class="btn-action-delete" data-target="#form-permission" data-method="delete"><i class="fa fa-trash text-danger"></i>&nbsp; Xóa bản ghi</a>
                                        </li>
                                        <li>
                                            <a  href="javascript:"  type="button" class="btn-action-delete"  data-target="#form-permission" data-method="restore"><i class="fa fa-undo text-danger"></i>&nbsp;Phục hồi bản ghi</a>
                                        </li>
                                    </ul>
                                </div>
                            @endif
                        </div>
                    </div>
                </form>
                <form id="form-permission" action="{{ route('admin.debit.detail-service.action_record') }}" method="post">
                    @csrf
                    <input type="hidden" name="method" value="" />
                    <div class="table-responsive">
                        <p></p>
                        <table class="table table-hover table-striped table-bordered">
                            <thead class="bg-primary">
                            <tr>
                                <th width="30"><input type="checkbox" class="iCheck checkAll" data-target=".checkSingle" /></th>
                                <th>STT</th>
                                <th>Mã BK</th>
                                <th>Kì BK</th>
                                <th>Căn hộ</th>
                                <th>Dịch vụ</th>
                                <th>Sản phẩm</th>
                                <th>Nhóm dịch vụ</th>
                                <th>Đơn giá</th>
                                <th>SL</th>
                                <th>Thành tiền</th>
                                {{-- <th>Dư nợ đầu kỳ</th> --}}
                                <th>Đã thu</th>
                                <th>Còn nợ</th>
                                <th>Ngày chốt</th>
                                <th>Ngày lập</th>
                                <th>Trạng thái</th>
                                <th>Version</th>
                                <th>Hạn thanh toán</th>
                                <th>Thời gian</th>
                                <th>Thời gian xóa</th>
                                {{-- <th>Trạng thái</th> --}}
                            </tr>
                            </thead>
                            <tbody>
                            @if($debits->count() > 0)
                                @foreach($debits as $key => $debit)
                                <tr>
                                    @if(\Auth::user()->isadmin == 1)
                                    <td><input type="checkbox" name="ids[]" value="{{$debit->id}}-{{ $debit->bdc_bill_id }}" class="iCheck checkSingle" /></td>
                                    @endif
                                    <td> @if( in_array('admin.debit.detailDebit.edit',@$user_access_router))
                                        <a data-id="{{ $debit->id }}" data-action="{{ route('admin.debit.detailDebit.edit') }}"
                                             class="btn btn-xs btn-info editService" title="Sửa thông tin">
                                            <i class="fa fa-edit"></i>
                                        </a>
                                        @if($debit->status < 0 || \Auth::user()->isadmin == 1)
                                         <a href="{{ route('admin.debit.detailDebit.delete.version',['id'=> $debit->id]) }}"
                                            onclick="return confirm('Bạn có chắc chắn muốn xóa không?')" class="btn btn-xs btn-danger" title="Xóa thông tin">
                                            <i class="fa fa-times"></i>
                                        </a>
                                        @endif
                                       @endif</td>
                                    <td>
                                       @if( in_array('admin.debit.detailDebit.edit.version',@$user_access_router))
                                        <a>
                                            {{ @$debit->bill_code }}
                                        </a>
                                       @endif
                                    </td>
                                    <td>{{ @$debit->cycle_name }}</td>
                                    <td>{{ @$debit->apartment_name }}</td>
                                    <td>{{ @$debit->service_name }}</td>
                                    <td>{{ @$debit->title }}</td>
                                    <td>
                                        @if(@$debit->service_group == 1)
                                            Phí công ty
                                        @elseif(@$debit->service_group == 2)
                                            Phí thu hộ
                                        @elseif(@$debit->service_group == 4)
                                            Phí ban quản trị
                                        @else
                                            Phí chủ đầu tư
                                        @endif
                                    </td>
                                    <td align="right">{{ number_format(@$debit->price)  }}</td>
                                    <td>{{ @$debit->quantity  }}</td>
                                    <td align="right">{{ number_format(@$debit->sumery) }}</td>
                                    {{-- <td align="right">{{ number_format(@$debit->previous_owed) }}</td> --}}
                                    @php
                                        if(@$debit->new_sumery == 0) {
                                            $dathu = @$debit->sumery;
                                            $conno = 0;
                                        } else {
                                            $dathu = @$debit->sumery - @$debit->new_sumery;
                                            $conno = @$debit->new_sumery;
                                        }
                                    @endphp
                                    <td align="right">{{ number_format(@$dathu)}}</td>
                                    <td align="right">{{ number_format(@$conno)}}</td>
                                    {{-- <td><a href="" class="btn btn-sm btn-warning">Lập PT</a></td> --}}
                                    <td>{{ @$debit->bill_date }}</td>
                                    <td>{{ date('d/m/Y', strtotime($debit->ngay_lap)) }}</td>
                                    <td style="color:green">{{ @$debit->status==-3 ? 'Chờ xác nhận' : ( @$debit->status==-2 ? 'Chờ gửi': 'Chờ thanh toán')}}</td>
                                    {{-- <td>{{ $bill->confirm_date != "0000-00-00 00:00:00" ? date('d/m/Y', strtotime(@$debit->confirm_date)) : "--/--/----" }}</td> --}}
                                    <td>{{ @$debit->version }}</td>
                                    <td>{{ date('d/m/Y', strtotime(@$debit->deadline)) }}</td>

                                    @if(@$debit->bdc_price_type_id==2 || @$debit->bdc_price_type_id==3)
                                    <td>{{ date('d/m/Y', strtotime(@$debit->from_date)).' - '.date('d/m/Y', strtotime($debit->to_date)) }}</td>
                                    @else
                                    <td>{{ date('d/m/Y', strtotime(@$debit->from_date)).' - '.date('d/m/Y', strtotime($debit->to_date  . ' - 1 days')) }}</td>
                                    @endif
                                    <td>{{ @$debit->deleted_at }}</td>
                                    {{-- <td>
                                        @if(@$debit->status == 0)
                                            <a class="#">Chờ thanh toán</a>
                                        @elseif(@$debit->status == 1)
                                            <a class="#">Đã thanh toán</a>
                                        @else
                                            <a href="#" class="">Đã quá hạn, nhắc?</a>
                                        @endif
                                    </td> --}}
                                </tr>
                            @endforeach
                            @endif
                            </tbody>
                        </table>
                    </div>
                    <div class="row mbm">
                        <div class="col-sm-3">
                            <span class="record-total">Hiển thị {{ $debits->count() }} / {{ $debits->total() }} kết quả</span>
                        </div>
                        <div class="col-sm-6 text-center">
                            <div class="pagination-panel">
                                {{ $debits->appends(request()->input())->links() }}
                            </div>
                        </div>
                        <div class="col-sm-3 text-right">
                        <span class="form-inline">
                            Hiển thị
                            <select name="per_page" class="form-control" data-target="#form-permission">
                                @php $list = [10, 20, 50, 100, 200]; @endphp
                                @foreach ($list as $num)
                                    <option value="{{ $num }}" {{ $num == $per_page ? 'selected' : '' }}>{{ $num }}</option>
                                @endforeach
                            </select>
                        </span>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <div class="modal-insert">

        </div>
    </section>
@endsection
@section('javascript')
    <!-- TinyMCE -->
    <script src="/adminLTE/plugins/tinymce/tinymce.min.js"></script>
    <script src="/adminLTE/plugins/tinymce/config.js"></script>
    <script>

        $('input.date_picker').datepicker({
            autoclose: true,
            dateFormat: "dd-mm-yy"
        }).val();
         $(function(){
             get_data_select_apartment1({
                object: '#ip-place_id',
                url: '{{ url('admin/apartments/ajax_get_building_place') }}',
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
            get_data_select({
                object: '#ip-apartment',
                url: '{{ url('admin/apartments/ajax_get_apartment') }}',
                data_id: 'id',
                data_text: 'name',
                title_default: 'Chọn căn hộ'
            });
            $("#ip-place_id").on('change', function(){ 
                if($("#ip-place_id").val()){
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
           
        })
    </script>
    <script>
        $(document).on('change', '.building-list', function (e) {
            e.preventDefault();
            var id = $(this).children(":selected").val();
            $.ajax({
                url: '{{route('admin.debit.getApartment')}}',
                type: 'POST',
                data: {
                    id: id
                },
                success: function (response) {
                    var $apartment = $('.apartment-list');
                    $apartment.empty();
                    $apartment.append('<option value="" selected>Căn hộ</option>');
                    $.each(response, function (index, val) {
                        if (index != 'debug') {
                            $apartment.append('<option value="' + index + '">' + val + '</option>')
                        }
                    });
                }
            })
        });
        $(document).ready(function () {
            $('#myCheckAll').change(function () {
                if ($(this).is(":checked")) {
                    $('.checkboxes').prop("checked", true);
                    $('.checkboxes').val(1);
                } else {
                    $('.checkboxes').prop("checked", false);
                    $('.checkboxes').val(0);
                }
            });
            //Date picker
            $('input.date_picker').datepicker({
                autoclose: true,
                dateFormat: "dd-mm-yy"
            }).val();

            $('.frees').change(function () {
                if (this.checked) {
                    $(this).val(1);
                } else {
                    $(this).val(0);
                }
            });
        });

        showModalForm('.editService', '#showModal');

        submitAjaxForm('#update-debit-detail', '#edit-debit-detail', '.div_', '.message_zone');

        $('.btn-action-delete').on('click',function () {
            var target = $(this).data('target');
            var $form = $(target);
            var method = $(this).data('method');
            var is_confirm = true;

            $('input[name=method]', $form).val(method);

            if (method == 'delete') {
                if (!confirm('Có chắc bạn muốn xóa bản ghi?')) {
                    is_confirm = false;
                }
            } else if (method == 'restore') {
                if (!confirm('Có chắc bạn muốn phục hồi bản ghi?')) {
                    is_confirm = false;
                }
            }

            if (is_confirm) {
                $form.submit();
            }

            return false;
        });

        function formatNumber(num) {
            return num.toString().replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,')
        }
    </script>
@endsection