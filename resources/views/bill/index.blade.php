@extends('backend.layouts.master')

@section('content')
    <section class="content-header">
        <h1>
            Quản lý kế toán
            <small>Chi tiết bảng kê - Khách hàng</small>
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
                {{-- @include('layouts.head-building') --}}
            </div>
            <div class="box-body">
                <div class="row form-group pull-right">
                    <a href="{{ route('admin.receipt.create') }}" class="btn bg-orange"><i class="fa fa-money"></i>
                        Lập phiếu thu</a>
                    <?php
                        $param = "?bdc_apartment_id=".@$filter['bdc_apartment_id']."&from_date=".@$filter['from_date']."&to_date=".@$filter['to_date']."&status=".@$filter['status'];
                    ?>
                    <a href="{{route('admin.bill.exportFilterBangKeKhachHang')}}{{$param}}" class="btn bg-olive">
                        <i class="fa fa-file-excel-o"></i>Export excel
                    </a>
                </div>
                <div class="row form-group ">
                    <form id="form-search-advance" action="{{route('admin.bill.index')}}" method="get">
                        <div id="search-advance" class="search-advance">
                        <div class="col-sm-2" style="padding-left:0">
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
                                <select name="status" class="form-control status-list ">
                                    <option value="" selected> Trạng thái</option>
                                    <option value="-3" @if(@$filter['status'] == -3) selected @endif>Cần xác nhận
                                    </option>
                                    <option value="-2" @if(@$filter['status'] == -2) selected @endif>Chờ gửi
                                    </option>
                                    <option value="1" @if(@$filter['status'] == 1) selected @endif>Trạng thái khác</option>
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
                    <table class="table table-hover table-striped table-bordered">
                            <thead class="bg-primary">
                            <tr>
                                <th><input type="checkbox" id="myCheckAll" class="myCheckAll check-check"/></th>
                                <th>STT</th>
                                <th>Mã BK</th>
                                <th>Tên khách hàng</th>
                                <th>Kỳ BK</th>
                                <th>Căn hộ</th>
                                <th>Hạn TT</th>
                                {{-- <th>Dư nợ đầu kỳ</th> --}}
                                <th>Nợ phát sinh</th>
                                <th>Đã thanh toán</th>
                                <th>Còn nợ</th>
                                {{-- <th>Phiếu thu</th> --}}
                                <th>Ngày lập</th>
                                <th>Ngày duyệt</th>
                                <th>Trạng thái</th>
                                <th>Hành động</th>
                            </tr>
                            </thead>
                            <tbody>
                            @if($bills->count() > 0)
                                @foreach($bills as $key => $bill)
                                    <?php $sum = 0 ?>
                                    <tr>
                                        <td><input id="{{$bill->id}}" type="checkbox" name="ids[]"
                                                   class="checkboxes frees check-check"/></td>
                                        <td>{{@($key + 1) + ($bills->currentpage() - 1) * $bills->perPage()}}</td>
                                        <td>
                                            <a href="{{ route('admin.bill.show', $bill->id) }}">{{@$bill->bill_code}}</a>
                                        </td>
                                        <td>{{@$bill->customer_name}}</td>
                                        <td>{{@$bill->cycle_name}}</td>
                                        <td>{{@$bill->apartment->name}}</td>
                                        <td>{{date('d/m/Y', strtotime(@$bill->deadline))}}</td>
                                        {{-- <td>{{number_format($bill->previous_owed)}}</td> --}}
                                        <td style="text-align: right;">
                                            <?php
                                            $findsumMery = App\Repositories\BdcDebitDetail\DebitDetailRepository::findMaxVersionSumeryByBillId($bill->id);
                                            $sumery = 0;
                                            ?>
                                            @foreach($findsumMery as $value)
                                                <?php
                                                    $sumery += (int) $value->sumery;
                                                ?>
                                            @endforeach
                                            {{number_format($sumery)}}
                                        </td>
                                        <td style="text-align: right;">
                                            <?php
                                            $findPaid = App\Repositories\BdcDebitDetail\DebitDetailRepository::findMaxVersionPaidVersion2($bill->id);
                                            $sumPaid = 0;
                                            ?>
                                            @foreach($findPaid as $paid)
                                                <?php
                                                    $sumPaid += (int) $paid->total_paid;
                                                ?>
                                            @endforeach
                                            {{number_format($sumPaid)}}
                                        </td>
                                        <td style="text-align: right;">{{number_format($sumery - @$bill->cost_free - @$sumPaid)}}</td>
                                        {{-- <td><a href="" class="btn bg-yellow">Lập PT</a></td> --}}
                                        <td>{{date('d/m/Y', strtotime(@$bill->created_at))}}</td>
                                        <td>{{ $bill->confirm_date != "0000-00-00 00:00:00" && $bill->confirm_date != "0000-00-00"  ? date('d/m/Y', strtotime(@$bill->confirm_date)) : "--/--/----" }}</td>
                                        <td>
                                            <?php $now =  \Carbon\Carbon::now() ?>
                                            @switch(@$bill->status)
                                                @case(-3)
                                                    <p class="text-warning">Chờ xác nhận</p>
                                                @break
                                                @case(-2)
                                                    @if(($sumery - $sumPaid) == 0 || $sumPaid >= $sumery)
                                                        <p class="text-success">Đã thanh toán</p>
                                                    @elseif(date('d-m-Y', strtotime($bill->deadline . "1 day")) < $now && ($sumery - @$bill->cost_free - @$sumPaid) > 0)
                                                        <p class="text-danger">Quá hạn</p>
                                                    @else
                                                        <p class="text-primary">Chờ gửi</p>
                                                    @endif
                                                @break
                                                @case(2)
                                                    <p class="text-success">Đã thanh toán</p>
                                                @break
                                                @case(1 && date('d-m-Y', strtotime($bill->deadline . "1 day")) < $now && ($sumery - @$bill->cost_free - @$sumPaid) > 0)
                                                    <p class="text-danger">Quá hạn</p>
                                                @break
                                                @case(1 && ($sumery - $sumPaid) == 0 || $sumPaid >= $sumery)
                                                    <p class="text-success">Đã thanh toán</p>
                                                @break
                                                @case(1)
                                                    <p class="text-info">Chờ thanh toán</p>
                                                @break
                                                @default
                                                    <p class="text-danger">Chưa có</p>
                                                @break
                                            @endswitch
                                        </td>
                                        <td>
                                            @if(@$debit->status == 0)
                                                <a href="{{ route('admin.debit.detailDebit', ['bdc_bill_id' => $bill->id]) }}"
                                                   class="btn btn-sm btn-primary" title="Chi tiết hóa đơn dịch vụ"
                                                   target="_blank"><i class="fa fa-align-right"></i></a>
                                            @endif
                                            @if(@$bill->status < 0)
                                                <a href="{{ route('admin.bill.delete') }}?id={{ $bill->id }}"
                                                    onclick="return confirm('Bạn có chắc chắn muốn xóa không?')" class="btn btn-sm btn-danger" title="Xóa thông tin">
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
                <form id="form-service-apartment" action="{{route('admin.service.company.action')}}" method="post">
                    @csrf
                    <input type="hidden" name="method" value="">
                    <div class="row mbm">
                        <div class="col-sm-3">
                            <span class="record-total" style="text-align: right;">Tổng: {{ $bills->total() }} bản ghi</span>
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
                                    <option value="{{ $num }}" {{ $num == $per_page ? 'selected' : '' }}>{{ $num }}</option>
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
            -ms-transform: scale(2); /* IE */
            -moz-transform: scale(2); /* FF */
            -webkit-transform: scale(2); /* Safari and Chrome */
            -o-transform: scale(2); /* Opera */
            padding: 10px;
        }
    </style>
@endsection
@section('javascript')
    <script>
        $('input.date_picker').datepicker({
            autoclose: true,
            dateFormat: "dd-mm-yy"
        }).val();
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        $(document).ready(function () {
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
            $("#datepicker").datepicker({
                format: "mm-yyyy",
                autoclose: true,
                viewMode: "months",
                minViewMode: "months"
            }).val();
        });

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
        //click  vao cac muc da chon
        $(document).ready(function () {
            $('#myCheckAll').change(function () {
                if ($(this).is(":checked")) {
                    $('.checkboxes').prop("checked", true);
                } else {
                    $('.checkboxes').prop("checked", false);
                }
            });
            $('.frees').change(function () {
                if (this.checked) {
                    $(this).val(1);
                } else {
                    $(this).val(0);
                }
            });
            $('.check-check').change(function (e) {
                e.preventDefault();
                var ids = [];
                var countCheckBoxs = $('.frees').filter(':checked').length;
                var id = $(this).filter(':checked').attr('id');
                $('.text-red-number').text(countCheckBoxs);
                $("input[name='ids[]']:checked").each(function () {
                    ids.push(parseInt($(this).attr('id')));
                });
                $.ajax({
                    url: '{{route('admin.bill.changeStatus')}}',
                    type: 'POST',
                    data: {
                        ids: ids
                    },
                    success: function (response) {
                        if (response.responseStatusNumber == -2) {
                            $('.add-submit').html('<button id="add-submit" class=" button-submit btn btn-success">Gửi thông báo</button>')

                        } else if (response.responseStatusNumber == -3) {
                            $('.add-submit').html('<button id="add-submit" class=" button-submit btn btn-warning">Xác nhận duyệt</button>')
                        } else {
                            $('.add-submit').html('<a class="btn btn-danger">Chọn dữ liệu không hợp lệ</a>')
                        }
                    }
                })
            });
            $('.add-submit').click(function (e) {
                e.preventDefault();
                console.log(1);
                var ids = [];
                $("input[name='ids[]']:checked").each(function () {
                    ids.push(parseInt($(this).attr('id')));
                });
                $.ajax({
                    url: '{{route('admin.bill.postChangeStatus')}}',
                    type: 'POST',
                    data: {
                        ids: ids
                    },
                    success: function (response) {
                        if (response.success == true)
                        {
                            toastr.success(response.message);

                            setTimeout(() => {
                                location.reload()
                            }, 2000)
                        }
                    }
                })
            });
        });

    </script>
@endsection