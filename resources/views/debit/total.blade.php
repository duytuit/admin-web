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
                {{-- @include('layouts.head-building') --}}
            </div>
            <div class="box-body">
                <form id="form-search-advance" action="{{route('admin.debit.total')}}" method="get">
                    <div id="search-advance" class="search-advance">
                        <div class="form-group pull-right">
                            @php
                                // $customerName = @$filter['customer_name'];
                                $apartmentId = @$filter['bdc_apartment_id'];
                                $from_date = @$filter['from_date'];
                                $to_date = @$filter['to_date'];
                                $paramExportExcel = "?bdc_apartment_id=$apartmentId&from_date=$from_date&to_date=$to_date";
                            @endphp
                            <a href="{{ route('admin.debit.exportExcelTotal') . $paramExportExcel }}" class="btn bg-olive">
                                <i class="fa fa-file-excel-o"></i>
                                Export excel
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
                            <div class="col-md-1 search-advance">
                                <select name="bdc_apartment_id" class="form-control apartment-list selectpicker" data-live-search="true">
                                    <option value="" selected>Căn hộ</option>
                                    @if(isset($apartments))
                                        @foreach($apartments as $key => $apartment)
                                            <option value="{{ $key }}"  @if(@$filter['bdc_apartment_id'] ==  $key) selected @endif>{{ $apartment }}</option>
                                        @endforeach
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
                            <div class="col-md-1">
                                <button type="submit" class="btn btn-info"><i class="fa fa-search"></i> Tìm kiếm
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
                <div class="table-responsive">
                    <div style="padding-top: 10px">
                        <p><strong>Tổng Đầu kỳ : {{ number_format(@$sumDayKy_all) }}</strong></p>
                        {{-- <strong><strong>Nợ phát sinh : {{ number_format(@$phatSinhTotals) }}</strong></strong> --}}
                        <p><strong>Tổng Phát Sinh : {{ number_format(@$sumPsTrongKy_all) }}</strong></p>
                        <p><strong>Tổng Thanh Toán : {{ number_format(@$sumThanhToan_all) }}</strong></p>
                        <p><strong>Tổng Dư Nợ Cuối Kỳ : {{ number_format(@$sumDayKy_all + @$sumPsTrongKy_all - @$sumThanhToan_all) }}</strong></p>
                    </div>
                    <table class="table table-hover table-striped table-bordered">
                        <thead class="bg-primary">
                        {{-- @php
                            $sumDauky = $debits->sum('dau_ky');
                            $sumPsTrongky = $debits->sum('ps_trongky');
                            $sumThanhToan = $debits->sum('thanhtoan');
                        @endphp --}}
                        <tr>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th>{{ number_format($sumDayKy_all) }}</th>
                            <th>{{ number_format($sumPsTrongKy_all) }}</th>
                            <th>{{ number_format($sumThanhToan_all) }}</th>
                            <th>{{ number_format($sumDayKy_all + $sumPsTrongKy_all - $sumThanhToan_all) }}</th>
                        </tr>
                        <tr>
                            <th><input type="checkbox" id="myCheckAll" class="myCheckAll check-check"></th>
                            <th>STT</th>
                            <th>Tên KH</th>
                            <th>Căn hộ</th>
                            <th>Tòa nhà</th>
                            <th>Đầu kỳ</th>
                            <th>Phát sinh trong kỳ</th>
                            <th>Thanh toán</th>
                            <th>Dư nợ cuối kỳ</th>
                        </tr>
                        </thead>
                        <tbody>
                        @if(isset($debits) && $debits != null)
                            @foreach($debits as $key => $debit)
                                @php
                                    $placeBuilding = $buildingPlaceRepository->findById(@$debit->building_place_id);    
                                @endphp
                                <tr>
                                    <td><input data-id="{{ $debit->bdc_apartment_id }}" data-value="{{ @$debit->name }}" type="checkbox" name="ids[]" class="checkboxes frees check-check"/></td>
                                    <td>{{ @($key + 1) + ($debits->currentpage() - 1) * $debits->perPage()  }}</td>
                                    <td>{{ @$debit->customer_name }}</td>
                                    <td>{{ @$debit->name }}</td>
                                    <th>{{ @$placeBuilding->name }}</th>
                                    <td>{{ number_format(@$debit->dau_ky) }}</td>
                                    <td>{{ number_format(@$debit->ps_trongky) }}</td>
                                    <td>{{ number_format(@$debit->thanh_toan) }}</td>
                                    <td>{{ number_format($debit->du_no_cuoi_ky) }}</td>
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
    </script>
    <script>
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
                var message = $('#push_message').val();
                var sendApp = $('#send_app').prop("checked");
                var sendMail = $('#send_email').prop("checked");
                var sendSms = $('#send_sms').prop("checked");
                $.ajax({
                    url: '{{route('admin.debit.sendMessage')}}',
                    type: 'POST',
                    data: {
                        apartmentIds: apartmentIds,
                        message: message,
                        sendApp: sendApp,
                        sendMail: sendMail,
                        sendSms: sendSms,
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
        });
    </script>

@endsection
