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
                <div class="col-md-9">
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
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="box-body">
                                        <form role="form">
                                            <!-- select -->
                                            <div class="form-group">
                                                <label>Chọn căn hộ</label>
                                                <select class="form-control selectpicker" data-live-search="true" id="choose_apartment_v2">
                                                    <option value="">Lựa chọn căn hộ</option>
                                                    @foreach ($apartments as $apartment)
                                                        <option value="{{ $apartment->id }}" @if(@$apartmentId == $apartment->id) selected @endif>{{ $apartment->name }}</option>
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
                                                <select class="form-control" name="customer_payments" id="customer_payments" onChange="GetCustomerPaymentsOption(this);">
                                                    <option value="tien_mat">Tiền mặt</option>
                                                    <option value="chuyen_khoan">Chuyển khoản</option>
                                                    <option value="vi">Ví</option>
                                                </select>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="box-body">
                                        <!-- select -->
                                        <div class="form-group">
                                            <label>Kiểu Phiếu</label>
                                            <select class="form-control" name="type_receipt" id="type_receipt" onChange="GetTypeReceiptOption(this);">
                                                <option value="phieu_thu">Phiếu Thu</option>
                                                <option value="phieu_bao_co">Phiếu Báo Có</option>
                                                <option value="phieu_ke_toan">Phiếu Kế Toán</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="box-body">
                                        <div class="form-group">
                                            <label>Dịch vụ</label>
                                            <select class="form-control" id="choose_service" data-url="{{ route('api.receipts.index') }}">
                                                <option value="0">Lựa chọn dịch vụ...</option>
                                                @foreach($services as $_service)
                                                    <option value="{{$_service->id}}">{{$_service->name}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="box-body">
                                        <form role="form">
                                            <!-- select -->
                                            <div class="form-group">
                                                <label>Tháng công nợ</label>
                                                <select class="form-control" id="choose_provisional_receipt" data-url="{{ route('api.receipts.index') }}">
                                                </select>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="box-body">
                                        <form role="form">
                                            <!-- select -->
                                            <div class="form-group">
                                                <label>Số tiền nộp</label>
                                                <input type="text" class="form-control customer_paid_string" value="0"  name="customer_paid_string" id="customer_paid_string">
                                            </div>
                                        </form>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="box-body">
                                        <form role="form">
                                            <!-- select -->
                                            <div class="form-group">
                                                <label>Tổng thanh toán</label>
                                                <input type="text" class="form-control total_pay" value="0"  name="total_pay" id="total_pay" readonly>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="box-body">
                                        <div class="form-group">
                                            <label for="exampleInputPassword1">Ví căn hộ</label>
                                            <input type="text" class="form-control" name="vi_can_ho" id="vi_can_ho" value="{{ number_format($vi_can_ho) }}" readonly>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="box-body">
                                        <!-- select -->
                                        <div class="form-group">
                                            <label>Ngày tạo</label>
                                            {!! Form::text('created_date', date("d-m-Y"), ['id' => 'created_date', 'class' => 'form-control date_picker', 'placeholder' => 'Ngày tạo...', 'autocomplete' => 'off', 'data-url' => route('api.receipts.index')]) !!}
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="box-body">
                                        <form role="form">
                                            <!-- select -->
                                            <div class="form-group">
                                                <label>Người nộp tiền</label>
                                                <input type="text" class="form-control" name="customer_fullname" id="customer_fullname" value="">
                                                <input type="hidden" name="data_receipt" class="data_receipt" value=""/>
                                                <input type="hidden" name="data_receipt" class="service_ids" value=""/>
                                                <input type="hidden" name="building_id" class="building_id" value="{{$building_id}}"/>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="box-body">
                                        <form role="form">
                                            <!-- select -->
                                            <div class="form-group">
                                                <label>Địa chỉ</label>
                                                <input type="text" class="form-control" name="customer_address" id="customer_address" value="">
                                            </div>
                                        </form>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="box-body">
                                        <form role="form">
                                            <!-- select -->
                                            <div class="form-group">
                                                <label>Nội dung thu tiền</label>
                                                <textarea class="form-control" rows="5" name="customer_description" id="customer_description"></textarea>
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

                <div class="col-md-3">
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
                                        <input type="text" class="form-control" name="ten_khach_hang" id="ten_khach_hang">
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-md-4" for="exampleInputPassword1">TK Nợ</label>
                                        <div class="col-md-8">
                                            <select class="form-control" name="tai_khoan_no" id="tai_khoan_no">
                                                @foreach($tai_khoan_ke_toan_phieu_thu as $_tai_khoan_ke_toan_phieu_thu)
                                                    <option value="{{$_tai_khoan_ke_toan_phieu_thu->id}}">{{$_tai_khoan_ke_toan_phieu_thu->code}}-{{$_tai_khoan_ke_toan_phieu_thu->name}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-md-4" for="exampleInputPassword1">TK Có</label>
                                        <div class="col-md-8">
                                            <select class="form-control" name="tai_khoan_co" id="tai_khoan_co">
                                                @foreach($tai_khoan_ke_toan_phieu_thu as $_tai_khoan_ke_toan_phieu_thu)
                                                    <option value="{{$_tai_khoan_ke_toan_phieu_thu->id}}">{{$_tai_khoan_ke_toan_phieu_thu->code}}-{{$_tai_khoan_ke_toan_phieu_thu->name}}</option>
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
                                    <div class="table-responsive result_receipt" id="result_receipt" style="min-height: 500px;">
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
                    <div class="main-action container-fluid align-items-center control-item " style="display: flex;justify-content: center;">
                            <a href="{{route('admin.receipt.index')}}" class="btn btn-warning mr-l"><i class="bx bx-arrow-back"></i><span class="align-middle ml-25">Bỏ qua</span></a>
                            <button type="button" class="btn btn-success mr-l add_new_debit_detail_v2" data-toggle="modal" data-target="#createDebitDetail" data-url="{{ route('api.debit.index') }}">Thêm mới công nợ</button>
                            <button type="submit" class="btn btn-primary mr-l print_and_collect_money_v2" id="thu_va_in" data-url="{{ route('api.receipts.index') }}" data-url-main="{{ route('admin.receipt.index') }}"><i class="bx bx-save"></i><span class="align-middle ml-25">Thu và in</span></button>
                            <button type="submit" class="btn btn-info mr-l collect_money_v2" id="thu_tien" data-url="{{ route('api.receipts.index') }}" data-url-main="{{ route('admin.receipt.index') }}"><i class="bx bx-save"></i><span class="align-middle ml-25">Thu tiền</span></button>
                            <button type="submit" class="btn btn-success mr-l collect_money_review_v2" id="xem_truoc" data-url="{{ route('api.receipts.index') }}" data-url-main="{{ route('admin.receipt.index') }}"><i class="bx bx-save"></i><span class="align-middle ml-25">Xem trước</span></button>
                    </div>
                </div>
            </div>
        </div>
        
    </section>
    <div class="modal fade" id="createDebitDetail" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Thêm công nợ</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body debit_detail_content"></div>
                <div class="modal-footer d-flex justify-content-center">
                <button type="button" class="btn btn-primary" id="add_debit_detail_previous" data-url="{{ route('api.debit.index') }}" data-url-receipt="{{ route('api.receipts.index') }}">Lưu</button>
                    <button type="button" class="btn btn-warning" data-dismiss="modal">Hủy</button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="ShowReviewReceipt" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel123" aria-hidden="true">
        <div class="modal-dialog">
            <div id="modal-content-receipt">
               
            </div>
        </div>
    </div>
@endsection

@section('javascript')
<script type="text/javascript" src="{{ url('adminLTE/js/format-currency.js') }}"></script>
<script>
    var service_ids = '';
    var billCodes = '';
    var data = '';
    let tai_khoan_no = '';
    let tai_khoan_co = '';
    function GetTypeReceiptOption(element){
        var text= element.options[element.selectedIndex].text;
        if (text=='Phiếu Thu') {

             tai_khoan_no = "111100-Tiền mặt";
            $("#tai_khoan_no option:contains(" + tai_khoan_no + ")").attr('selected', 'selected');

             tai_khoan_co = "131700-Phải thu phí dịch vụ";
            $("#tai_khoan_co option:contains(" + tai_khoan_co + ")").attr('selected', 'selected');
            $("#customer_payments").val("tien_mat").change();
        }
        if (text=='Phiếu Báo Có') {
             tai_khoan_no = "112100-Tiền ngân hàng";
            $("#tai_khoan_no option:contains(" + tai_khoan_no + ")").attr('selected', 'selected');

             tai_khoan_co = "131700-Phải thu phí dịch vụ";
            $("#tai_khoan_co option:contains(" + tai_khoan_co + ")").attr('selected', 'selected');
            $("#customer_payments").val("chuyen_khoan").change();
        }
    }

    function GetCustomerPaymentsOption(element){
        var text= element.options[element.selectedIndex].text;
        if (text=='Tiền mặt') {
             tai_khoan_no = "111100-Tiền mặt";
            $("#tai_khoan_no option:contains(" + tai_khoan_no + ")").attr('selected', 'selected');

             tai_khoan_co = "131700-Phải thu phí dịch vụ";
            $("#tai_khoan_co option:contains(" + tai_khoan_co + ")").attr('selected', 'selected');
            $("#type_receipt").val("phieu_thu").change();
           
        }
        if (text=='Chuyển khoản' || text=='Ví') {

             tai_khoan_no = "112100-Tiền ngân hàng";
            $("#tai_khoan_no option:contains(" + tai_khoan_no + ")").attr('selected', 'selected');

             tai_khoan_co = "131700-Phải thu phí dịch vụ";
            $("#tai_khoan_co option:contains(" + tai_khoan_co + ")").attr('selected', 'selected');
            $("#type_receipt").val("phieu_bao_co").change();
        }
    }
     
    $(document).ready(function () {
        if($('#choose_apartment').val()){
            data = "";
            $("#type_receipt").val("phieu_bao_co").change();
            loadDebitDetail($('#choose_apartment').attr('data-url'), false); 
        }

        tai_khoan_no = "111100-Tiền mặt";
        $("#tai_khoan_no option:contains(" + tai_khoan_no + ")").attr('selected', 'selected');

        tai_khoan_co = "131700-Phải thu phí dịch vụ";
        $("#tai_khoan_co option:contains(" + tai_khoan_co + ")").attr('selected', 'selected');
    });
    $('#choose_type').change(function() {
        if($(this).val() == 1){ // hạch toán dịch vụ
              if(!$('#choose_apartment_v2').val()){
                   alert('Cần phải lựa chọn căn hộ !');
                   $(this).val(2);
                   return;
              }
              $("#type_receipt").val("phieu_ke_toan").change();
              $('#customer_paid_string').attr('readonly', true);
              $('#total_pay').val(0);
              $('.data_receipt').val('');
              data='';
              loadDebitDetailV2($(this).attr('data-url'), false);
              let total_so_du = formatCurrencyV2($('.total_so_du_hidden').val());
              $('#customer_paid_string').val(total_so_du);
        }else {                 // thu tiền dịch vụ
              $('#customer_paid_string').attr('readonly', false);
              $('#customer_paid_string').val(0);
        }
    });
    $('input.date_picker').datepicker({
        autoclose: true,
        dateFormat: "dd-mm-yy"
    }).val();
</script>
<script type="text/javascript" src="{{ url('adminLTE/js/function_dxmb.js') . "?v=" . \Carbon\Carbon::now()->timestamp }}"></script>
<script type="text/javascript" src="{{ url('adminLTE/js/debit.js') . "?v=" . \Carbon\Carbon::now()->timestamp }}"></script>
<script type="text/javascript" src="{{ url('adminLTE/js/validate-form-dxmb.js') . "?v=" . \Carbon\Carbon::now()->timestamp }}"></script>
<script type="text/javascript" src="{{ url('adminLTE/js/format-currency.js') }}"></script>
@endsection