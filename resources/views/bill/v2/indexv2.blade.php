@extends('backend.layouts.master')

@section('content')
    <section class="content-header">
        <h1>
            Quản lý kế toán
            <small>Chi tiết bảng kê - Khách hàng</small>
            <a href="{{ route('admin.v2.receipt.create') }}" class="btn bg-orange"><i class="fa fa-money"></i>
                Lập phiếu thu</a>
            <?php
            $param = '?bdc_apartment_id=' . @$filter['bdc_apartment_id'] . '&from_date=' . @$filter['from_date'] . '&to_date=' . @$filter['to_date'] . '&status=' . @$filter['status'];
            ?>
            <a href="{{ route('admin.v2.bill.exportFilterBangKeKhachHang') }}{{ $param }}" class="btn bg-olive">
                <i class="fa fa-file-excel-o"></i>Export excel
            </a>
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('admin.home') }}"><i class="fa fa-home"></i> Trang chủ</a></li>
            <li class="active">Chi tiết bảng kê - Khách hàng</li>
        </ol>
    </section>

    <section class="content">
        <div class="box box-primary">
            <div class="box-body font-weight-bold">
                <h3>Chi tiết bảng kê - Khách hàng</h3>
            </div>
            <div class="box-body">
                <div class="row form-group">
                    <div class="col-sm-1">
                        <button type="button" data-toggle="dropdown" class="btn btn-primary dropdown-toggle pull-left"
                            style="margin-right: 10px;">Tác
                            vụ&nbsp;<span class="caret"></span></button>
                        <ul class="dropdown-menu">
                            <li>
                                <a type="button" class="btn-action"
                                    data-target="#form-service-apartment" data-method="change_status_need_confirmation">Chuyển trạng thái cần xác nhận</a>
                                <a type="button" class="btn-action"
                                    data-target="#form-service-apartment" data-method="change_notice_needed">Chuyển trạng thái Chờ gửi</a>
                                <a type="button" class="btn-action"
                                    data-target="#form-service-apartment" data-method="change_paying">Chờ thanh toán</a>
                                @if (\Auth::user()->isadmin == 1)
                                <a type="button" class="btn-action"
                                    data-target="#form-service-apartment" data-method="delete_select_item">Xóa hóa đơn</a>
                                @endif
                            </li>
                        </ul>
                    </div>
                    <form id="form-search-advance" action="{{ route('admin.v2.bill.index') }}" method="get">
                        <div id="search-advance" class="col-sm-10 search-advance">
                            <div class="col-sm-2" style="padding-left:0">
                                <select name="ip_place_id" id="ip-place_id" class="form-control" style="width: 100%;">
                                    <option value="">Chọn tòa nhà</option>
                                    <?php $place_building = isset($get_place_building) ? $get_place_building : ''; ?>
                                    @if ($place_building)
                                        <option value="{{ $place_building->id }}" selected>{{ $place_building->name }}
                                        </option>
                                    @endif
                                </select>
                            </div>
                            <div class="col-sm-2" style="padding-left:0">
                                <select name="bdc_apartment_id" id="ip-apartment" class="form-control">
                                    <option value="">Căn hộ</option>
                                    <?php $apartment = isset($get_apartment) ? $get_apartment : ''; ?>
                                    @if ($apartment)
                                        <option value="{{ $apartment->id }}" selected>{{ $apartment->name }}</option>
                                    @endif
                                </select>
                            </div>
                            <div class="col-sm-2">
                                <div class="input-group date">
                                    <div class="input-group-addon">
                                        <i class="fa fa-calendar"></i>
                                    </div>
                                    <input type="text" class="form-control date_picker" name="from_date" id="from_date"
                                        value="{{ @$filter['from_date'] }}" placeholder="Từ..." autocomplete="off">
                                </div>
                            </div>
                            <div class="col-sm-2">
                                <div class="input-group date">
                                    <div class="input-group-addon">
                                        <i class="fa fa-calendar"></i>
                                    </div>
                                    <input type="text" class="form-control date_picker" name="to_date" id="to_date"
                                        value="{{ @$filter['to_date'] }}" placeholder="Đến..." autocomplete="off">
                                </div>
                            </div>
                            <div class="col-sm-2">
                                <select name="status" class="form-control status-list ">
                                    <option value="" selected> Trạng thái</option>
                                    <option value="-3" @if (@$filter['status'] == -3) selected @endif>Cần xác nhận
                                    </option>
                                    <option value="can_thong_bao" @if (@$filter['status'] == 'can_thong_bao') selected @endif>
                                        Chờ gửi
                                    </option>
                                    <option value="qua_han" @if (@$filter['status'] == 'qua_han') selected @endif>Quá hạn
                                    </option>
                                    <option value="da_thanh_toan" @if (@$filter['status'] == 'da_thanh_toan') selected @endif>
                                        Đã thanh toán</option>
                                    <option value="dang_thanh_toan" @if (@$filter['status'] == 'dang_thanh_toan') selected @endif>Đang
                                        thanh toán</option>
                                </select>
                            </div>
                            <div class="col-sm-1">
                                <button type="submit" class="btn btn-info"><i class="fa fa-search"></i> Tìm kiếm
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="table-responsive">
                    <form id="form-service-apartment" action="{{ route('admin.v2.bill.action') }}" method="post">
                        @csrf
                        <input type="hidden" name="method" value="">
                        <table class="table table-hover table-striped table-bordered">
                            <thead class="bg-primary">
                                <tr>
                                    <th><input type="checkbox" class="iCheck checkAll" data-target=".checkSingle" /></th>
                                    <th>STT</th>
                                    <th>Mã BK</th>
                                    <th>Tên khách hàng</th>
                                    <th>Kỳ BK</th>
                                    <th>Căn hộ</th>
                                    <th>Hạn TT</th>
                                    <th>Nợ phát sinh</th>
                                    <th>Giảm trừ</th>
                                    <th>Thành tiền</th>
                                    <th>Đã thanh toán</th>
                                    <th>Còn nợ</th>
                                    <th>Ngày lập</th>
                                    <th>Ngày duyệt</th>
                                    <th>Trạng thái</th>
                                    <th>Hành động</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if ($bills->count() > 0)
                                    @php
                                        $apartment_id =0;
                                    @endphp
                                    @foreach ($bills as $key => $bill)
                                        <?php
                                            $find_debit = App\Repositories\BdcV2DebitDetail\DebitDetailRepository::findByBillId($bill->id);
                                            $sumery = 0;
                                            $sumery_discount = 0;
                                            $sumery_price_after_discount = 0;
                                            $sumPaid = 0;
                                        ?>
                                        @foreach ($find_debit as $value)
                                            <?php
                                            $sumery += (int) $value->sumery + (int) $value->discount;
                                            $sumery_discount += (int) $value->discount;
                                            $sumery_price_after_discount += (int) $value->sumery ;
                                            $sumPaid += (int) $value->paid;
                                            ?>
                                        @endforeach
                                        <?php
                                            $sum = 0;
                                            $_customer = App\Repositories\Customers\CustomersRespository::findApartmentIdV3($bill->bdc_apartment_id, 0);
                                            $pubUserProfile = @$_customer->pubUserProfile;
                                            $now = \Carbon\Carbon::now();
                                            $deadline = \Carbon\Carbon::parse($bill->deadline);
                                        switch ($bill->status) {
                                            case -3:
                                                $status = 'Chờ xác nhận';
                                                $status_text = '<p class="text-warning">Chờ xác nhận</p>';
                                                break;
                                            case -2:
                                                $status = 'Chờ gửi';
                                                $status_text = '<p class="text-primary">Chờ gửi</p>';
                                                break;
                                            case 2:
                                                $status = 'Đã thanh toán';
                                                $status_text = '<p class="text-success">Đã thanh toán</p>';
                                                break;
                                            case 1:
                                                if($sumPaid >= $sumery_price_after_discount) {
                                                    $status = 'Đã thanh toán';
                                                    $status_text = '<p class="text-success">Đã thanh toán</p>';
                                                }elseif ($deadline < $now && $sumery_price_after_discount > $sumPaid) {
                                                    $status = 'Quá hạn';
                                                    $status_text = '<p class="text-danger">Quá hạn</p>';
                                                }else {
                                                    $status = 'Chờ thanh toán';
                                                    $status_text = '<p class="text-primary">Chờ thanh toán</p>';
                                                }
                                                break;
                                            default:
                                                $status = 'Chưa có';
                                                $status_text = '<p class="text-danger">Chưa có</p>';
                                                break;
                                        }
                                        ?>
                                        <tr>
                                            @if ($sumPaid > 0 )
                                                  <td></td>
                                            @elseif ($sumPaid == 0 && \Auth::user()->isadmin == 1)
                                                <td><input type="checkbox" name="ids[]" value="{{ $bill->id }}"
                                                    class="iCheck checkSingle" /></td>
                                            @else
                                                <td><input type="checkbox" name="ids[]" value="{{ $bill->id }}"
                                                    class="iCheck checkSingle" /></td>
                                            @endif
                                            <td>
                                                <a target="_blank" href="/admin/activity-log/log-action?row_id={{$bill->id}}"> {{ $bill->id }}</a>
                                            </td>
                                            <td>
                                                <a
                                                    href="{{ route('admin.v2.bill.show', $bill->id) }}">{{ @$bill->bill_code }}</a>
                                            </td>
                                            <td>{{ @$pubUserProfile->display_name }}</td>
                                            <td>{{ @$bill->cycle_name }}</td>
                                            <td>
                                                <!-- <a target="_blank" href="/admin/dev/updateDebit2?apartmentId={{$bill->bdc_apartment_id}}"> {{ @$bill->apartment->name }}</a> -->
                                                <a target="_blank" href="https://betabdc.s-tech.info/admin/dev/updateDebit_superver?apartmentId={{$bill->bdc_apartment_id}}&user_id={{\Auth::user()->id}}"> {{ @$bill->apartment->name }}</a>
                                            </td>
                                            <td>{{ date('d/m/Y', strtotime(@$bill->deadline)) }}</td>
                                            <td style="text-align: right;">
                                                {{ number_format($sumery) }}
                                            </td>
                                            <td class="text-right">{{ number_format($sumery_discount) }}</td>
                                            <td class="text-right">{{ number_format($sumery_price_after_discount) }}
                                            </td>
                                            <td class="text-right">{{ number_format($sumPaid) }}</td>
                                            <td style="text-align: right;">
                                                {{ number_format($sumery_price_after_discount - @$sumPaid) }}
                                            </td>
                                            <td>{{ date('d/m/Y', strtotime(@$bill->created_at)) }}</td>
                                            <td>{{ $bill->confirm_date != '0000-00-00 00:00:00' && $bill->confirm_date != '0000-00-00'
                                                ? date('d/m/Y', strtotime(@$bill->confirm_date))
                                                : '--/--/----' }}
                                            </td>
                                            <td>
                                                {!! @$status_text !!}
                                            <td>
                                                <a href="{{ route('admin.v2.debit.detailDebit', ['bdc_bill_id' => $bill->id]) }}"
                                                    class="btn btn-sm btn-primary" title="Chi tiết hóa đơn dịch vụ"
                                                    target="_blank"><i class="fa fa-align-right"></i></a>
                                                @if ($sumPaid == 0)
                                                    <a href="{{ route('admin.v2.bill.destroyBill', ['id' => $bill->id]) }}"
                                                        onclick="return confirm('Bạn có chắc chắn muốn xóa không?')"
                                                        class="btn btn-sm btn-danger" title="Xóa thông tin">
                                                        <i class="fa fa-times"></i>
                                                    </a>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
                </div>
                <div class="row mbm">
                    <div class="col-sm-3">
                        <span class="record-total" style="text-align: right;">Tổng: {{ $bills->total() }} bản
                            ghi</span>
                    </div>
                    <div class="col-sm-6 text-center">
                        <div class="pagination-panel">
                            {{ $bills->appends(Request::all())->onEachSide(1)->links() }}
                        </div>
                    </div>
                    <div class="col-sm-3 text-right">
                        <span class="form-inline">
                            Hiển thị
                            <select name="per_page" class="form-control" data-target="#form-service-apartment">
                                @php $list = [10, 20, 50, 100, 200]; @endphp
                                @foreach ($list as $num)
                                    <option value="{{ $num }}" {{ $num == $per_page ? 'selected' : '' }}>
                                        {{ $num }}</option>
                                @endforeach
                            </select>
                        </span>
                    </div>
                </div>
                </form>
                <div class="box-body text-center" id="show_checked">
                    <h4><b class="text-red text-red-number">0</b> mục đã chọn</h4>
                    <div class="add-submit "></div>
                </div>
            </div>
        </div>
    </section>
@endsection
@section('stylesheet')
    <style>
        input[type=checkbox] {
            /* Double-sized Checkboxes */
            -ms-transform: scale(2);
            /* IE */
            -moz-transform: scale(2);
            /* FF */
            -webkit-transform: scale(2);
            /* Safari and Chrome */
            -o-transform: scale(2);
            /* Opera */
            padding: 10px;
        }

    </style>
@endsection
@section('javascript')
    <script>
        console.log($('meta[name="csrf-token"]').attr('content'));
        $('input.date_picker').datepicker({
            autoclose: true,
            dateFormat: "dd-mm-yy"
        }).val();
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        $(document).ready(function() {
            $(document).on('change', '.building-list', function(e) {
                e.preventDefault();
                var id = $(this).children(":selected").val();
                $.ajax({
                    url: '{{ route('admin.v2.debit.getApartment') }}',
                    type: 'POST',
                    data: {
                        id: id
                    },
                    success: function(response) {
                        var $apartment = $('.apartment-list');
                        $apartment.empty();
                        $apartment.append('<option value="" selected>Căn hộ</option>');
                        $.each(response, function(index, val) {
                            if (index != 'debug') {
                                $apartment.append('<option value="' + index + '">' +
                                    val + '</option>')
                            }
                        });
                    }
                })
            });
            $("#datepicker").datepicker({
                format: "mm-yyyy",
                autoclose: true,
                viewMode: "months",
                minViewMode: "months"
            }).val();
        });

        $(function() {
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
                                    text: item[options.data_text] + ' - ' + item[options
                                        .data_code]
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

        })
        //click  vao cac muc da chon
        $(document).ready(function() {});
    </script>
@endsection
