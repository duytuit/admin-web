@extends('backend.layouts.master')
@section('stylesheet')
<link rel="stylesheet" href="{{ url('adminLTE/plugins/tags-input/bootstrap-tagsinput.css') }}" />
<style>
    .bootstrap-tagsinput {
        width: 100% !important;
    }
</style>
@endsection
@section('content')
    <section class="content-header">
        <h1>
            Quản lý kế toán
            <small>Tổng hợp công nợ</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('admin.home') }}"><i class="fa fa-home"></i> Trang chủ</a></li>
            <li class="active">Tổng hợp công nợ</li>
        </ol>
    </section>

    <section class="content">
        <div class="box box-primary">
            <div class="box-body font-weight-bold">
                <h3>Tổng hợp công nợ</h3>
            </div>
            <div class="box-body">
                <form id="form-search-advance" action="{{route('admin.v2.debit.total')}}" method="get" onsubmit="return validateForm()">
                    <div id="search-advance" class="search-advance">
                        <div class="form-group pull-right">
                            @php
                                // $customerName = @$filter['customer_name'];
                                $apartmentId = @$filter['bdc_apartment_id'];
                                $from_date = @$filter['from_date'];
                                $to_date = @$filter['to_date'];
                                $cycle_name = @$filter['cycle_name'];
                                $cycle_name_more = @$filter['cycle_name_more'];
                                $paramExportExcel = "?bdc_apartment_id=$apartmentId&from_date=$from_date&to_date=$to_date&cycle_name=$cycle_name&cycle_name_more=$cycle_name_more";
                            @endphp
                            <a href="{{ route('admin.v2.debit.exportExcelTotal') . $paramExportExcel }}" class="btn bg-olive">
                                <i class="fa fa-file-excel-o"></i>
                                Xuất excel
                            </a>
                            <a href="#" class="btn bg-olive nhac_no_can_ho">
                                <i class="fa fa-bell" aria-hidden="true"></i>
                                Nhắc phí
                            </a>
                        </div>
                        <div class="row space-5">
                            <div class="col-md-2 search-advance">
                                <input type="number" class="form-control" name="du_no_cuoi_ky" id="du_no_cuoi_ky" value="{{@$filter['du_no_cuoi_ky']}}" placeholder="Dư nợ cuối kỳ...">
                            </div>
                            <div class="col-md-2 search-advance">
                                <select name="bdc_apartment_id" class="form-control apartment-list selectpicker" data-live-search="true">
                                    <option value="" selected>Căn hộ</option>
                                    @if(isset($apartments))
                                        @foreach($apartments as $key => $apartment)
                                            <option value="{{ $key }}"  @if(@$filter['bdc_apartment_id'] ==  $key) selected @endif>{{ $apartment }}</option>
                                        @endforeach
                                        @endif
                                </select>
                            </div>
                        </div>
                        <div class="row space-5">
                            <div class="col-sm-2">
                                <select class="form-control" id="filter_custom" name="filter_custom">
                                    <option value="ky_bang_ke" @if(@$filter['filter_custom'] ==  "ky_bang_ke") selected @endif>Một kỳ bảng kê</option>
                                    <option value="ky_bang_ke_more" @if(@$filter['filter_custom'] ==  "ky_bang_ke_more") selected @endif>Một khoảng kỳ bảng kê</option>
                                    <option value="range_time" @if(@$filter['filter_custom'] ==  "range_time") selected @endif>Khoảng Thời Gian</option>
                                </select>
                            </div>
                            <div class="ky_bang_ke">
                                <div class="col-sm-2" style="padding-left:5px;padding-right:5px">
                                    <select name="cycle_name" id="cycle_name" class="form-control">
                                        <option value="" selected>Kỳ bảng kê</option>
                                        @foreach($cycle_names as $cycle_name)
                                            <option value="{{ $cycle_name }}"  @if(@$filter['cycle_name'] ==  $cycle_name) selected @endif>{{ $cycle_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="ky_bang_ke_more" @if(@$filter['filter_custom'] ==  "ky_bang_ke_more") style="display: none" @endif>
                                <div class="col-sm-2" style="padding-left:5px;padding-right:5px">
                                    <select name="cycle_name_more" id="cycle_name_more" class="form-control">
                                        <option value="" selected>Đến Kỳ bảng kê</option>
                                        @foreach($cycle_names as $cycle_name)
                                            <option value="{{ $cycle_name }}"  @if(@$filter['cycle_name_more'] ==  $cycle_name) selected @endif>{{ $cycle_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="range_time" @if(@$filter['filter_custom'] ==  "range_time") style="display: none" @endif>
                                <div class="col-sm-2" style="padding-left:5px;padding-right:5px">
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
                            </div>
                            <div class="col-md-1">
                                <button type="submit" class="btn btn-info"><i class="fa fa-search"></i> Tìm kiếm
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
                <form id="form-permission" action="{{ route('admin.v2.debit.total.action') }}" method="get">
                <div class="table-responsive">
                    <div style="padding-top: 10px">
                        <p><strong>Tổng Đầu kỳ : {{ number_format(@$tong_dau_ky) }}</strong></p>
                        <p><strong>Tổng Phát Sinh : {{ number_format(@$tong_trong_ky) }}</strong></p>
                        <p><strong>Tổng Thanh Toán : {{ number_format(@$tong_thanh_toan) }}</strong></p>
                        <p><strong>Tổng Dư Nợ Cuối Kỳ : {{ number_format(@$tong_cuoi_ky) }}</strong></p>
                    </div>
                    <table class="table table-hover table-striped table-bordered">
                        <thead class="bg-primary">
                            <tr>
                                <th></th>
                                <th></th>
                                <th style="text-align: center;"></th>
                                <th style="text-align: center;"></th>
                                <th style="text-align: center;"></th>
                                <th style="text-align: center;"></th>
                                <th style="text-align: center;"></th>
                                <th style="text-align: center;"></th>
                                <th colspan="1" style="width: 100px;" class="text-right">{{ number_format( @$tong_dau_ky) }}</th>
                                <th colspan="1" style="width: 100px;" class="text-right">{{ number_format( @$tong_trong_ky) }}</th>
                                <th colspan="1" style="width: 100px;" class="text-right">{{ number_format( @$tong_thanh_toan) }}</th>
                                <th colspan="1" style="width: 100px;" class="text-right">{{ number_format( @$tong_cuoi_ky) }}</th>
                            </tr>
                            <tr>
                                <th><input type="checkbox" id="myCheckAll" class="myCheckAll check-check"></th>
                                <th rowspan="2">STT</th>
                                <th rowspan="2" style="text-align: center;">Mã KH/NCC</th>
                                <th rowspan="2" style="text-align: center;">Tên KH/NCC</th>
                                <th rowspan="2" style="text-align: center;">Căn hộ</th>
                                <th rowspan="2" style="text-align: center;">Mã dự án</th>
                                <th rowspan="2" style="text-align: center;">Mã sản phẩm</th>
                                <th rowspan="2" style="text-align: center;">Tên dự án</th>
                                <th colspan="1" style="width: 100px;">Số dư đầu kỳ</th>
                                <th colspan="1" style="width: 100px;">Phát sinh trong kỳ</th>
                                <th colspan="1" style="width: 100px;">Thanh toán</th>
                                <th colspan="1" style="width: 100px;">Số dư cuối kỳ</th>
                            </tr>
                        </thead>
                        <tbody>
                        @if(isset($debitsTotal) && $debitsTotal != null)
                            @foreach($debitsTotal as $key => $debit)
                                <tr>
                                    <td><input data-id="{{ @$debit['id'] }}" data-value="{{ @$debit['ten_khach_hang'] }}" type="checkbox" name="ids[]" class="checkboxes frees check-check"/></td>
                                    <td>{{ @($key + 1) + ($getServiceApartments->currentpage() - 1) * $getServiceApartments->perPage()  }}</td>
                                    <td>{{ @$debit['ma_khach_hang'] }}</td>
                                    <td>{{ @$debit['ten_khach_hang'] }}</td>
                                    <td>{{ @$debit['can_ho'] }}</td>
                                    <td>{{ @$debit['ma_du_an'] }}</td>
                                    <td>{{ @$debit['ma_san_pham'] }}</td>
                                    <td>{{ @$debit['ten_du_an'] }}</td>

                                    <td class="text-right">{{ number_format( @$debit['dau_ky']) }}</td>
                                    <td class="text-right">{{ number_format( @$debit['trong_ky']) }}</td>
                                    <td class="text-right">{{ number_format( @$debit['thanh_toan']) }}</td>
                                    <td class="text-right">{{ number_format( @$debit['cuoi_ky']) }}</td>
                                </tr>
                            @endforeach
                        @endif
                        </tbody>
                    </table>
                </div>
                <div class="row mbm">
                    @if(isset($getServiceApartments) && $getServiceApartments != null)
                        <div class="col-sm-3">
                            <span class="record-total">Hiển thị {{ $getServiceApartments->count() }} / {{ $getServiceApartments->total() }} kết quả</span>
                        </div>
                        <div class="col-sm-6 text-center">
                            <div class="pagination-panel">
                                {{ $getServiceApartments->appends(request()->input())->links() }}
                            </div>
                        </div>
                    @endif
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
    </section>

    <div id="nhacNoCanHo" class="modal fade" role="dialog">
        <div class="modal-dialog">
          <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Gửi Thông Báo</h4>
                </div>
                <div class="modal-body updateBoardCategoryContent">
                    <div class="form-group">
                        <label for="recipient-name" class="col-form-label">Danh sách căn hộ:</label>
                        <input type="text" class="form-control" id="list_apartment">
                        <div class="notify-group">
                            <div class="col-sm-6 text-center">
                                <label class="notify-label">
                                    <input type="radio" name="send_to" value="0" checked>
                                   Chủ hộ
                                </label>
                            </div>
                            <div class="col-sm-6 text-center">
                                <label class="notify-label">
                                    <input type="radio" name="send_to" value="1">
                                    Tất cả thành viên căn hộ
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="recipient-name">Kỳ bảng kê</label>
                        <select name="cycle_name" id="cycle_name_send_notify" class="form-control select2" style="width: 100%">
                            <option value="" selected>Kỳ bảng kê</option>
                            @foreach($cycle_names as $cycle_name)
                                <option value="{{ $cycle_name }}">{{ $cycle_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="recipient-name" class="col-form-label">Nội dung thông báo:</label>
                        <textarea class="form-control mceEditor" id="push_message"></textarea>
                    </div>
                    <div class="form-group">
                        <input class="form-check-input" type="checkbox" value="1" id="send_app">
                        <label class="form-check-label" for="flexCheckDefault">
                            App
                        </label>
                    </div>
                    <div class="form-group">
                        <input class="form-check-input" type="checkbox" value="1" id="send_email">
                        <label class="form-check-label" for="flexCheckDefault">
                            Email
                        </label>
                    </div>
                    <div class="form-group">
                        <input class="form-check-input" type="checkbox" value="1" id="send_sms">
                        <label class="form-check-label" for="flexCheckDefault">
                            SMS <span style="font-size: 12px">(Nội dung tiếng việt không dấu)</span>
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success send_message">Gửi</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">Đóng</button>
                </div>
            </div>  
        </div>
    </div>
@endsection
@section('stylesheet')
    <style>
        input.check-check {
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
    <script src="/adminLTE/plugins/tinymce/tinymce.min.js"></script>
    <script src="/adminLTE/plugins/tinymce/config.js"></script>
    <script src="/adminLTE/plugins/tags-input/bootstrap-tagsinput.js"></script>
    <script>
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
                    url: '{{route('admin.v2.debit.getApartment')}}',
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
    </script>
    <script>
        $(document).ready(function () {
            // $('#filter_custom').val() === "ky_bang_ke" && $("#cycle_name_more").hide();
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

            $('.nhac_no_can_ho').click(function() {
                $("#list_apartment").tagsinput({
                    itemValue: 'id',
                    itemText: 'text',
                });
                $("#list_apartment").tagsinput('removeAll');
                $("input[name='ids[]']:checked").each(function () {
                    // ids.push(parseInt($(this).attr('id')));
                    console.log($(this).data('value'));
                    $('#list_apartment').tagsinput('add', { id: $(this).data('id'), text: $(this).data('value') });
                });
                if(!$("#list_apartment").val()){
                   alert('Chưa có căn hộ nào được chọn để Nhắc phí.');
                   return;
                }
                $('#nhacNoCanHo').modal('show');
            });

            $('.send_message').click(function() {
                showLoading();
                var apartmentIds = $('#list_apartment').val();
                var cycle_name = $('#cycle_name_send_notify').val();
                var message = $('#push_message').val();
                var sendApp = $('#send_app').prop("checked");
                var sendMail = $('#send_email').prop("checked");
                var sendSms = $('#send_sms').prop("checked");
                var send_to = $("input[name='send_to']:checked").val();
                $.ajax({
                    url: '{{route('admin.v2.debit.sendMessage')}}',
                    type: 'POST',
                    data: {
                        apartmentIds: apartmentIds,
                        cycle_name: cycle_name,
                        message: message,
                        sendApp: sendApp,
                        sendMail: sendMail,
                        sendSms: sendSms,
                        send_to: send_to,
                    },
                    success: function (response) {
                        hideLoading();
                        toastr.success(response.message);
                        setTimeout(() => {
                            location.reload()
                        }, 5000);
                    }
                })
            });
            if($('#filter_custom').val() == "ky_bang_ke"){
                $('.ky_bang_ke_more').hide();
                $('.range_time').hide();
                $('.ky_bang_ke').show();
                document.querySelector("input[name='from_date']").value = "";
                document.querySelector("input[name='to_date']").value = "";
            }
            if($('#filter_custom').val() == "ky_bang_ke_more"){
                $('.ky_bang_ke_more').show();
                $('.range_time').hide();
                $('.ky_bang_ke').show();
                document.querySelector("input[name='from_date']").value = "";
                document.querySelector("input[name='to_date']").value = "";
            }
            if($('#filter_custom').val() == "range_time"){
                $('.range_time').show();
                $('.ky_bang_ke_more').hide();
                $('.ky_bang_ke').hide();
                var cycleNameSelect = document.getElementById("cycle_name");
                var desiredValue = "";
                cycleNameSelect.value = desiredValue;
                var cycleNamemoreSelect = document.getElementById("cycle_name_more");
                var desiredValuemore = "";
                cycleNamemoreSelect.value = desiredValuemore;
            }
        });
        $('#filter_custom').change(function () {
            if ($(this).val() == "ky_bang_ke") {
                $('.ky_bang_ke_more').hide();
                $('.range_time').hide();
                $('.ky_bang_ke').show();
                document.querySelector("input[name='from_date']").value = "";
                document.querySelector("input[name='to_date']").value = "";
            }
            if ($(this).val() == "ky_bang_ke_more") {
                $('.ky_bang_ke_more').show();
                $('.range_time').hide();
                $('.ky_bang_ke').show();
                document.querySelector("input[name='from_date']").value = "";
                document.querySelector("input[name='to_date']").value = "";
            }
            if($(this).val() == "range_time"){
                $('.ky_bang_ke_more').hide();
                $('.ky_bang_ke').hide();
                $('.range_time').show();
                var cycleNameSelect = document.getElementById("cycle_name");
                var desiredValue = "";
                cycleNameSelect.value = desiredValue;
                var cycleNamemoreSelect = document.getElementById("cycle_name_more");
                var desiredValuemore = "";
                cycleNamemoreSelect.value = desiredValuemore;
            }
        });

        function validateForm(){
            if($('#filter_custom').val() == "ky_bang_ke_more" && $('#cycle_name_more').val() < $('#cycle_name').val()){
                alert("Khoảng thời gian kỳ không hợp lệ!");
                return false;
            }
            return true;
        }
    </script>
@endsection
