@extends('backend.layouts.master')

@section('stylesheet')
@endsection

@section('content')
    <style>
        .mr-l {
            margin: 1px;
        }
    </style>
    <section class="content-header">
        <h1>
            Quản lý dịch vụ
            <small>Lập phiếu thu</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('admin.home') }}"><i class="fa fa-home"></i> Trang chủ</a></li>
            <li class="active">Quản lý dịch vụ</li>
        </ol>
    </section>
    <section class="content">
        <div class="box-body">
            <div class="row">
                <!-- Left col -->
                <div class="col-md-12">
                    <!-- MAP & BOX PANE -->
                    <div class="box box-primary" style="box-shadow: none;">
                        <div class="box-header with-border text-center bg-primary">
                            <h4 class="text-create-recipt">Lập phiếu thu</h4>
                        </div>
                        <!-- /.box-header -->
                        <div class="box-body no-padding">
                            <div class="row box-body" style="display: flex;justify-content: center;color: red;">
                                <label class="total_so_du"></label>
                                <input type="hidden" class="total_so_du_hidden"/>
                            </div>
                            <div class="row" style="display: flex">
                                <div class="col-md-12" style="padding: 0;">
                                    <div class="col-md-4">
                                        <div class="box-body">
                                            <form role="form">
                                                <!-- select -->
                                                <div class="form-group">
                                                    <label>Chọn căn hộ</label>
                                                    <select class="form-control selectpicker" data-live-search="true"
                                                            id="choose_apartment_v2">
                                                        <option value="">Lựa chọn căn hộ</option>
                                                        @foreach ($apartments as $apartment)
                                                            <option value="{{ $apartment->id }}"
                                                                    @if(@$apartmentId == $apartment->id) selected @endif>{{ $apartment->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="box-body">
                                            <form role="form">
                                                <!-- select -->
                                                <div class="form-group">
                                                    <label>Nghiệp vụ thực hiện</label>
                                                    <select class="form-control" id="choose_type">
                                                        <option value="1">Hạch toán dịch vụ</option>
                                                        <option value="2" selected="selected">Thu tiền dịch vụ</option>
                                                    </select>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="box-body">
                                            <form role="form">
                                                <!-- select -->
                                                <div class="form-group">
                                                    <label>Hình thức thu tiền</label>
                                                    <select class="form-control" name="customer_payments"
                                                            id="customer_payments">
                                                        @foreach ($typeReceipt as $key => $value)
                                                            <option value="{{ $value->config }}">{{ $value->title }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                <div style="width: 400px;display: none" id="detail_list">
                                    <label>Chi tiết tiền thừa</label>
                                    <form class="form-horizontal">
                                        <div class="box-body detail_tien_thua">
                                        </div>
                                    </form>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="box-body">
                                        <form role="form">
                                            <!-- select -->
                                            <div class="form-group">
                                                <label>Số tiền</label>
                                                <input type="text" class="form-control customer_paid_string" value="0"
                                                       name="customer_paid_string" id="customer_paid_string">
                                            </div>
                                        </form>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="box-body">
                                        <form role="form">
                                            <!-- select -->
                                            <div class="form-group">
                                                <label>Tổng thanh toán</label>
                                                <input type="text" class="form-control total_pay" value="0"
                                                       name="total_pay" id="total_pay" readonly>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="box-body">
                                        <!-- select -->
                                        <div class="form-group">
                                            <label>Kiểu Phiếu</label>
                                            <select class="form-control" name="type_receipt" id="type_receipt">
                                                <option value="phieu_thu">Phiếu Thu</option>
                                                <option value="phieu_bao_co">Phiếu Báo Có</option>
                                                <option value="phieu_ke_toan">Phiếu Kế Toán</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="box-body">
                                        <form role="form">
                                            <!-- select -->
                                            <div class="form-group">
                                                <label>Người nộp tiền</label>
                                                <input type="text" class="form-control" name="customer_fullname"
                                                       id="customer_fullname" value="">
                                                <input type="hidden" name="data_receipt" class="data_receipt" value=""/>
                                                <input type="hidden" name="data_receipt" class="service_ids" value=""/>
                                                <input type="hidden" name="building_id" class="building_id"
                                                       value="{{$building_id}}"/>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="box-body">
                                        <form role="form">
                                            <!-- select -->
                                            <div class="form-group">
                                                <label>Địa chỉ</label>
                                                <input type="text" class="form-control" name="customer_address"
                                                       id="customer_address" value="">
                                            </div>
                                        </form>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="box-body">
                                        <!-- select -->
                                        <div class="form-group">
                                            <label>Ngày hạch toán</label>
                                            {!! Form::text('created_date', date("d-m-Y"), ['id' => 'created_date', 'class' => 'form-control date_picker', 'placeholder' => 'Ngày tạo...', 'autocomplete' => 'off', 'data-url' => route('api.receipts.index')]) !!}
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="box-body">
                                        <form role="form">
                                            <!-- select -->
                                            <div class="form-group">
                                                <label>Nội dung thu tiền</label>
                                                <textarea class="form-control" rows="5" name="customer_description"
                                                          id="customer_description"></textarea>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <!-- /.row -->
                        </div>
                        <!-- /.box-body -->
                    </div>
                    <!-- /.box -->
                </div>
                <!-- /.col -->

                <div class="col-md-3" style="display:none">
                    <div class="box box-primary" style="box-shadow: none;">
                        <div class="box-header with-border text-center bg-primary">
                            <h4 class="text-create-recipt">Thông tin phiếu thu</h4>
                        </div>
                        <!-- /.box-header -->
                        <div class="box-body">
                            <form role="form" id="receipt_form">
                                <div class="box-body no-padding">
                                    <div class="form-group">
                                        <label for="exampleInputPassword1">Mã khách hàng hóa đơn</label>
                                        <input type="text" class="form-control" name="ma_khach_hang" id="ma_khach_hang">
                                    </div>
                                    <div class="form-group">
                                        <label for="exampleInputPassword1">Tên khách hàng hóa đơn</label>
                                        <input type="text" class="form-control" name="ten_khach_hang"
                                               id="ten_khach_hang">
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-md-4" for="exampleInputPassword1">TK Nợ</label>
                                        <div class="col-md-8">
                                            <select class="form-control" name="tai_khoan_no" id="tai_khoan_no">
                                                @foreach($tai_khoan_ke_toan_phieu_thu as $_tai_khoan_ke_toan_phieu_thu)
                                                    <option value="{{$_tai_khoan_ke_toan_phieu_thu->id}}">{{$_tai_khoan_ke_toan_phieu_thu->code}}
                                                        -{{$_tai_khoan_ke_toan_phieu_thu->name}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-md-4" for="exampleInputPassword1">TK Có</label>
                                        <div class="col-md-8">
                                            <select class="form-control" name="tai_khoan_co" id="tai_khoan_co">
                                                @foreach($tai_khoan_ke_toan_phieu_thu as $_tai_khoan_ke_toan_phieu_thu)
                                                    <option value="{{$_tai_khoan_ke_toan_phieu_thu->id}}">{{$_tai_khoan_ke_toan_phieu_thu->code}}
                                                        -{{$_tai_khoan_ke_toan_phieu_thu->name}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="exampleInputPassword1">Ngân hàng</label>
                                        <select class="form-control" name="ngan_hang" id="ngan_hang">
                                            @foreach($tai_khoan_ngan_hang as $_tai_khoan_ngan_hang)
                                                <option value="{{$_tai_khoan_ngan_hang->id}}">{{$_tai_khoan_ngan_hang->bank_name}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="row">
                        <div class="col-md-12">
                            <!-- DIRECT CHAT -->
                            <div class="box box-primary direct-chat direct-chat-warning">
                                <div class="box-header with-border text-center bg-primary">
                                    <h4 class="text-create-recipt">Danh sách hóa đơn</h4>
                                </div>
                                <div class="box-body no-padding">
                                    <div class="table-responsive result_receipt" id="result_receipt"
                                         style="min-height: 500px;">
                                    </div>
                                    <!-- /.table-responsive -->
                                </div>
                            </div>
                            <!--/.direct-chat -->
                        </div>
                        <!-- /.col -->
                    </div>
                </div>
                <!-- /.col -->
                <div class="bottom-control" style="bottom: 0;position: fixed;width: 85%;">
                    <div class="main-action container-fluid align-items-center control-item "
                         style="display: flex;justify-content: center;">
                        <a href="{{route('admin.v2.receipt.index')}}" class="btn btn-warning mr-l"><i
                                    class="bx bx-arrow-back"></i><span class="align-middle ml-25">Bỏ qua</span></a>
                        <button type="button" class="btn btn-success mr-l add_new_debit_detail_v2"
                                data-url="{{ route('api.debit.index') }}">Thêm mới công nợ
                        </button>
                        <button type="submit" class="btn btn-primary mr-l print_and_collect_money_v2" id="thu_va_in"
                                data-url="{{ route('api.receipts.index') }}"
                                data-url-main="{{ route('admin.v2.receipt.index') }}"><i class="bx bx-save"></i><span
                                    class="align-middle ml-25">Thu và in</span></button>
                        <button type="submit" class="btn btn-info mr-l collect_money_v2" id="thu_tien"
                                data-url="{{ route('api.receipts.index') }}"
                                data-url-main="{{ route('admin.v2.receipt.index') }}"><i class="bx bx-save"></i><span
                                    class="align-middle ml-25">Thu tiền</span></button>
                        <button type="submit" class="btn btn-success mr-l collect_money_review_v2" id="xem_truoc"
                                data-url="{{ route('api.receipts.index') }}"
                                data-url-main="{{ route('admin.v2.receipt.index') }}"><i class="bx bx-save"></i><span
                                    class="align-middle ml-25">Xem trước</span></button>
                    </div>
                </div>
            </div>
        </div>

    </section>
    <div class="modal fade" id="createDebitDetail" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
         aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Thêm công nợ</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body debit_detail_content"></div>
                <div>
                    <div class="form-group">
                        <div class="col-md-12">
                            <button type="button" class="btn btn-success add_progress_price_item">
                                <i class="fa fa-plus" aria-hidden="true"> Thêm kỳ công nợ</i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body progress_price_list">
                    </div>
                </div>
                <div class="modal-footer d-flex justify-content-center">
                    <div class="row col-md-12">
                        <div>
                            <button type="button" class="btn btn-primary" id="add_debit_detail_previous_v2"
                                    data-url="{{ route('api.debit.index') }}"
                                    data-url-receipt="{{ route('api.receipts.index') }}">Lưu
                            </button>
                            <button type="button" class="btn btn-warning" data-dismiss="modal">Hủy</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="ShowReviewReceipt" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel123"
         aria-hidden="true">
        <div class="modal-dialog">
            <div id="modal-content-receipt">

            </div>
        </div>
    </div>
    <input type="hidden" value="{{$promotions}}" id="list_promotions">
@endsection
<style>
    .modal-dialog {
        width: 1000px !important;
        margin: 30px auto;
    }
</style>
@section('javascript')
    <script type="text/javascript" src="{{ url('adminLTE/js/format-currency.js') }}"></script>
    <script>
        var service_ids = '';
        var billCodes = '';
        var data = '';
        let tai_khoan_no = '';
        let tai_khoan_co = '';

        $('#type_receipt').on('change', function () {
            if ($(this).val() == 'phieu_bao_co') {
                $("#customer_payments").val("chuyen_khoan").change();
            }
            if ($(this).val() == 'phieu_thu') {
                $("#customer_payments").val("tien_mat").change();
            }
        })

        $('#customer_payments').on('change', function () {
            if ($(this).val() == 'tien_mat') {
                $("#type_receipt").val("phieu_thu").change();
            } else if ($(this).val() == 'chuyen_khoan' || $(this).val() == 'vi') {
                $("#type_receipt").val("phieu_bao_co").change();
            } else {
                $("#type_receipt").val("phieu_ke_toan").change();
            }
        })

        function GetCustomerPaymentsOption(element) {
            var text = element.options[element.selectedIndex].text;
            if (text == 'Tiền mặt') {
                $("#type_receipt").val("phieu_thu").change();
            } else if (text == 'Chuyển khoản' || text == 'Ví') {
                $("#type_receipt").val("phieu_bao_co").change();
            } else {
                $("#type_receipt").val("phieu_ke_toan").change();
            }
        }

        var promotion = null;
        $(document).ready(function () {
            if ($('#list_promotions').val()) {
                promotion = JSON.parse($('#list_promotions').val())
            }
            if ($('#choose_apartment').val()) {
                data = "";
                $("#type_receipt").val("phieu_bao_co").change();
                loadDebitDetail($('#choose_apartment').attr('data-url'), false);
            }

            $('.progress_price_list').on('input', 'input.chiet_khau', function (e) {
                let phi_phat_sinh_on_change_chiet_khau = $(this).closest('.progress_price_items').find('.phi_phat_sinh').val().replace(/,/g, "") - $(this).val().replace(/,/g, "");
                if (phi_phat_sinh_on_change_chiet_khau < 0) {
                    alert("Số tiền Giảm trừ phải nhỏ hơn số tiền phát sinh");
                    $(this).val(0)
                    $(this).closest('.progress_price_items').find('.thanh_tien').val($(this).closest('.progress_price_items').find('.phi_phat_sinh').val());
                    return;
                }
                $(this).closest('.progress_price_items').find('.thanh_tien').val(formatCurrencyV2(phi_phat_sinh_on_change_chiet_khau.toString()));
                $(this).val(formatCurrency(this));
            }).on('keyup', 'input.chiet_khau', function (e) {
                if (!$.isNumeric(String.fromCharCode(e.which))) e.preventDefault();
            }).on('paste', 'input.chiet_khau', function (e) {
                var cb = e.originalEvent.clipboardData || window.clipboardData;
                if (!$.isNumeric(cb.getData('text'))) e.preventDefault();
            });
            $('.progress_price_list').on('input', 'input.phi_phat_sinh', function (e) {
                let phi_phat_sinh = $(this).val().replace(/,/g, "");
                $(this).val(formatCurrencyV2(phi_phat_sinh.toString()));
                $(this).closest('.progress_price_items').find('.thanh_tien').val(formatCurrencyV2(phi_phat_sinh.toString()));
            }).on('keyup', 'input.phi_phat_sinh', function (e) {
                if (!$.isNumeric(String.fromCharCode(e.which))) e.preventDefault();
            }).on('paste', 'input.phi_phat_sinh', function (e) {
                var cb = e.originalEvent.clipboardData || window.clipboardData;
                if (!$.isNumeric(cb.getData('text'))) e.preventDefault();
            });
        });
        $('#choose_type').change(function () {
            if ($(this).val() == 1) { // hạch toán dịch vụ
                if (!$('#choose_apartment_v2').val()) {
                    alert('Cần phải lựa chọn căn hộ !');
                    $(this).val(2);
                    return;
                }
                $("#type_receipt").val("phieu_ke_toan").change();
                $('#customer_paid_string').attr('readonly', true);
                $('#total_pay').val(0);
                $('.data_receipt').val('');
                data = '';
                loadDebitDetailV2($(this).attr('data-url'), false);
                let total_so_du = formatCurrencyV2($('.total_so_du_hidden').val());
                $('#customer_paid_string').val(total_so_du);
                $('.detail_chi_dinh_hach_toan').css('display', 'block')
            } else {                 // thu tiền dịch vụ
                $('#customer_paid_string').attr('readonly', false);
                $('#customer_paid_string').val(0);
                $('.detail_chi_dinh_hach_toan').css('display', 'none')
            }
        });
        let thang_bang_ke = null;
        let year_bang_ke = null;
        let next_thang_bang_ke = null;
        let thang_bang_ke_by_last_time_pay = null;
        $(".add_progress_price_item").click(function (e) {
            if (!$('#from_date_previous').val()) {
                alert('Bạn chưa chọn dịch vụ');
                return;
            }
            let month = null;
            let year = null;
            let from_date = null;
            let to_date = null;
            let phi_phat_sinh = null;
            let chiet_khau = null;
            let thanh_tien = null;
            if (thang_bang_ke == null) {
                let date_time = $('#from_date_previous').val();
                let cycle_name = $('#cycle_name_debit').val();
                let new_date = new Date(date_time.replace(/(\d{2})-(\d{2})-(\d{4})/, "$2/$1/$3"));
                thang_bang_ke = cycle_name ? parseInt(cycle_name.substring(4, 6)) : new_date.getMonth() + 1;
                year_bang_ke = new_date.getFullYear();

                next_thang_bang_ke = `${('00' + new_date.getDate()).slice(-2)}-${('00' + (new_date.getMonth() + 1)).slice(-2)}-${year_bang_ke}`;
                thang_bang_ke += 1;
                if (thang_bang_ke == '13') {
                    thang_bang_ke = 1;
                }
                month = `${('00' + (thang_bang_ke)).slice(-2)}`;

                new_date = new Date(next_thang_bang_ke.replace(/(\d{2})-(\d{2})-(\d{4})/, "$2/$1/$3"));
                year = new_date.getFullYear();
                from_date = next_thang_bang_ke;
                new_date = new Date(next_thang_bang_ke.replace(/(\d{2})-(\d{2})-(\d{4})/, "$2/$1/$3"));
                thang_bang_ke_by_last_time_pay = new_date.getMonth() + 2;
                if (thang_bang_ke_by_last_time_pay == 13) {
                    thang_bang_ke_by_last_time_pay = 1;
                    year_bang_ke += 1;
                }
                next_thang_bang_ke = `${('00' + new_date.getDate()).slice(-2)}-${('00' + (thang_bang_ke_by_last_time_pay)).slice(-2)}-${year_bang_ke}`;
                to_date = next_thang_bang_ke;
                phi_phat_sinh = formatCurrencyV2($('#service_price').val().toString());
                chiet_khau = 0;
                thanh_tien = formatCurrencyV2($('#service_price').val().toString());
            } else {
                thang_bang_ke += 1;
                if (thang_bang_ke == '13') {
                    thang_bang_ke = 1;
                }
                month = `${('00' + thang_bang_ke).slice(-2)}`;
                new_date = new Date(next_thang_bang_ke.replace(/(\d{2})-(\d{2})-(\d{4})/, "$2/$1/$3"));

                year = new_date.getFullYear();
                from_date = next_thang_bang_ke;
                new_date = new Date(next_thang_bang_ke.replace(/(\d{2})-(\d{2})-(\d{4})/, "$2/$1/$3"));
                thang_bang_ke_by_last_time_pay += 1;
                if (thang_bang_ke_by_last_time_pay == 13) {
                    thang_bang_ke_by_last_time_pay = 1;
                    year_bang_ke += 1;
                }
                console.log(thang_bang_ke_by_last_time_pay);
                next_thang_bang_ke = `${('00' + new_date.getDate()).slice(-2)}-${('00' + (thang_bang_ke_by_last_time_pay)).slice(-2)}-${year_bang_ke}`;
                to_date = next_thang_bang_ke;
                phi_phat_sinh = formatCurrencyV2($('#service_price').val().toString());
                chiet_khau = 0;
                thanh_tien = formatCurrencyV2($('#service_price').val().toString());
                console.log(next_thang_bang_ke);
            }

            var html = '<div class="form-row progress_price_items">' +
                '<div class="col-md-12 item_detail">' +
                '<div class="form-group col-md-2">' +
                '<label for="content" class="control-label">Kỳ tháng</label>' +
                '<div class="row" style="display: inline-flex;">' +
                '<input class="form-control check_list_cong_no" placeholder="Tháng" min="0" name="month" value="' + month + '" type="number">' +
                '/' +
                ' <input class="form-control" placeholder="Năm" min="0" name="year" value="' + year + '" type="number">' +
                '<div class="message_zone_data"></div>' +
                '</div>' +
                ' </div>' +
                ' <div class="form-group col-md-2">' +
                '<label for="content" class="control-label">Từ ngày</label>' +
                '<input class="form-control date_picker" placeholder="Từ ngày" name="from_date" value="' + from_date + '" type="text">' +
                ' </div>' +
                '<div class="form-group col-md-2">' +
                '<label for="content" class="control-label">Đến ngày</label>' +
                '<input class="form-control date_picker" placeholder="Đến ngày" name="to_date" value="' + to_date + '" type="text">' +
                '</div>' +
                '<div class="form-group col-md-2">' +
                '<label for="content" class="control-label">Phí phát sinh</label>' +
                ' <input class="form-control phi_phat_sinh" placeholder="Phí phát sinh" min="0" name="phi_phat_sinh" value="' + phi_phat_sinh + '" type="text">' +
                ' </div>' +
                ' <div class="form-group col-md-1" style="padding: 0;">' +
                '<label for="content" class="control-label">Giảm trừ</label>' +
                ' <input class="form-control chiet_khau" placeholder="Giảm trừ" min="0" name="chiet_khau" value="' + chiet_khau + '" type="text">' +
                ' </div>' +
                '<div class="form-group col-md-2">' +
                '<label for="content" class="control-label">Thành tiền</label>' +
                '<input class="form-control thanh_tien" placeholder="Thành tiền" min="0" name="thanh_tien" readonly value="' + thanh_tien + '" type="text">' +
                '</div>' +
                '<div class="form-group col-md-1" style="padding: 0;margin-top: 28px;">' +
                '<button type="button" data-remove_item="' + thang_bang_ke + '" class="btn btn-danger remove_item" onclick="removeCongNo(this)">' +
                '<i class="fa fa-minus" aria-hidden="true"></i>' +
                '</button>' +
                '</div>' +
                '</div>' +

                '</div>';
            $(".progress_price_list").append(html);
            console.log($('.progress_price_list').children().find('.remove_item').last()[0]);
            console.log('-----------------');
            // console.log($('.progress_price_list').children().find('.remove_item').not(":last"));
            if ($(".progress_price_list").children().length > 1) {
                $('.progress_price_list').children().find('.remove_item').not(":last").each(function () {
                    $(this).parent().remove();
                });
            }
            // $(".progress_price_list").children().each(function () { 
            //     //$(this).find('.remove_item').remove();
            // });
            e.preventDefault();
        });
        $('.result_receipt').on('click', '#thanh_toan_tu', function (e) {
            e.preventDefault();
            $('.result_receipt .chi_dinh_hach_toan').val('').change();
            if ($('#choose_type').val() == 1) {
                if ($('.detail_chi_dinh_hach_toan').css('display') == 'none') {
                    $('.detail_chi_dinh_hach_toan').css('display', 'block')
                } else {
                    $('.detail_chi_dinh_hach_toan').css('display', 'none')
                }
            }

        });

        function removeCongNo(element) {
            $(element).closest('.progress_price_items').remove();
            thang_bang_ke -= 1;
            if (thang_bang_ke == 0) { // nếu như tháng bảng kê = 0 thì lùi năm
                thang_bang_ke = 12;
            }
            thang_bang_ke_by_last_time_pay -= 1;
            if (thang_bang_ke_by_last_time_pay == 0) {
                thang_bang_ke_by_last_time_pay = 12;
                year_bang_ke -= 1;
            }
            new_date = new Date(next_thang_bang_ke.replace(/(\d{2})-(\d{2})-(\d{4})/, "$2/$1/$3"));
            next_thang_bang_ke = `${('00' + new_date.getDate()).slice(-2)}-${('00' + (thang_bang_ke_by_last_time_pay)).slice(-2)}-${year_bang_ke}`;
            console.log(thang_bang_ke)
            $('.progress_price_list .progress_price_items .item_detail').last().append('<div class="form-group col-md-1" style="padding: 0;margin-top: 28px;">' +
                '<button type="button" data-remove_item="' + thang_bang_ke + '" class="btn btn-danger remove_item" onclick="removeCongNo(this)">' +
                '<i class="fa fa-minus" aria-hidden="true"></i>' +
                '</button>' +
                '</div>');
        }

        function chosePromotion(even) {
            let split_event = $(even).val().split('_');
            let id_promotion = split_event[0];
            let sumery = split_event[1];
            let _promotion = promotion.find(s => s.id == id_promotion);
            //let f =  $(even).parent().parent().find("td:eq(3)").text();
            if (_promotion) {
                $('.result_receipt .list_info > tr').each(function () {
                    let service_id = $(this).find(".promotion_apartment").data('promotion');
                    if (service_id == _promotion.id) {
                        console.log(service_id);
                        $(this).find('.chose_service').val('').change();
                        $(this).find(".promotion_apartment").html('');
                    }
                })
                let sumery_discount = parseInt(sumery);
                if (_promotion.type_discount == 0) {
                    sumery_discounttest = parseInt(_promotion.discount * _promotion.number_discount);
                    sumery_discount = parseInt(_promotion.condition) * sumery_discount - sumery_discounttest;
                } else {
                    let _discount = ((parseInt(_promotion.discount) * sumery_discount) / 100) * parseInt(_promotion.number_discount);
                    sumery_discount = parseInt(_promotion.condition) * sumery_discount - _discount;
                }
                html = '<div>Thời gian áp dụng</div>';
                html += '<div>' + format_date_no_time(_promotion.begin) + ' đến ' + format_date_no_time(_promotion.end) + '</div>';
                html += '<div> Số tiền cần nộp là:</div>';
                html += '<div>' + formatCurrencyV2(parseInt(sumery_discount).toString()) + ' VND </div>';
                $(even).parent().find('.promotion_apartment').attr('data-promotion', _promotion.id).html(html);
                $(even).parent().find('.promotion_apartment').attr('data-promotion_price', sumery_discount);
            }
        }

        function editDiscount(params) {
            $(params).css('display', 'none');
            $(params).closest('.checkbox_parent').find('input.debit_discount').css('display', 'block');
        }

        $('.result_receipt').on('input', 'input.debit_discount', function (e) {

            let debit_sumery = $(this).closest('.checkbox_parent').find('.debit_sumery').text().replace(/,/g, "") - $(this).closest('.checkbox_parent').find('.debit_paid').val() - $(this).val().replace(/,/g, "");

            $(this).closest('.checkbox_parent').find('.debit_sumery_paid').text(formatCurrencyV2(debit_sumery.toString()))
            $(this).closest('.checkbox_parent').find('.total_payment').val(formatCurrencyV2(debit_sumery.toString()))
            $(this).closest('.checkbox_parent').find('.total_payment_current').val(debit_sumery)
            if (debit_sumery < 0) {
                alert("Số tiền Giảm trừ phải nhỏ hơn số tiền phát sinh");
                let debit_discount_current = $(this).closest('.checkbox_parent').find('.debit_discount_current').text().replace(/,/g, "");
                $(this).val(formatCurrencyV2(debit_discount_current.toString()));
                let sumery = $(this).closest('.checkbox_parent').find('.total_payment_old').val();
                $(this).closest('.checkbox_parent').find('.debit_sumery_paid').text(formatCurrencyV2(sumery.toString()))
                $(this).closest('.checkbox_parent').find('.total_payment').val(formatCurrencyV2(sumery.toString()))
                $(this).closest('.checkbox_parent').find('.total_payment_current').val(sumery)
                return;
            }
        });
        $('input.date_picker').datepicker({
            autoclose: true,
            dateFormat: "dd-mm-yy"
        }).val();

    </script>
    <script type="text/javascript"
            src="{{ url('adminLTE/js/function_dxmb.js') . "?v=" . \Carbon\Carbon::now()->timestamp }}"></script>
    <script type="text/javascript"
            src="{{ url('adminLTE/js/debit.js') . "?v=" . \Carbon\Carbon::now()->timestamp }}"></script>
    <script type="text/javascript"
            src="{{ url('adminLTE/js/validate-form-dxmb.js') . "?v=" . \Carbon\Carbon::now()->timestamp }}"></script>
    <script type="text/javascript" src="{{ url('adminLTE/js/format-currency.js') }}"></script>
@endsection