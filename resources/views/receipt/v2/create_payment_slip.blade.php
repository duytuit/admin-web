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
            <small>Phiếu chi</small>
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
                            <h4 class="text-create-recipt">Phiếu chi</h4>
                        </div>
                        <!-- /.box-header -->
                        <div class="box-body no-padding">
                            <div class="row box-body" style="display: flex;justify-content: center;color: red;">
                                <label class="total_so_du"></label>
                                <input type="hidden" class="total_so_du_hidden" />
                            </div>
                            <div class="row" style="display: flex">
                                <div class="col-md-12" style="padding: 0;">
                                    <div class="col-md-3">
                                        <div class="box-body">
                                            <form role="form">
                                                <!-- select -->
                                                <div class="form-group">
                                                    <label>Chọn căn hộ</label>
                                                    <select class="form-control selectpicker" name="bdc_apartment_id" id="bdc_apartment_id"
                                                        data-live-search="true" onchange='this.form.submit()'>
                                                        <option value="">Lựa chọn căn hộ</option>
                                                        @foreach ($apartments as $apartment)
                                                            <option value="{{ $apartment->id }}"
                                                                @if (@$filter['bdc_apartment_id'] == $apartment->id) selected @endif>
                                                                {{ $apartment->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <noscript><input type="submit" value="Submit"></noscript>
                                            </form>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="box-body">
                                            <form role="form">
                                                <!-- select -->
                                                <div class="form-group">
                                                    <label>Số tiền</label>
                                                    <input type="text" class="form-control" value="0" name="total_paid_payment" id="total_paid_payment" readonly>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="box-body">
                                            <form role="form">
                                                <!-- select -->
                                                <div class="form-group">
                                                    <label>Hình thức</label>
                                                    <select class="form-control" name="customer_payments"
                                                        id="customer_payments" onChange="GetCustomerPaymentsOption(this);">
                                                        @foreach ($typeReceipt as $key => $value)
                                                            <option value="{{ $value->config }}">{{ $value->title }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="box-body">
                                            <!-- select -->
                                            <div class="form-group">
                                                <label>Ngày hạch toán</label>
                                                {!! Form::text('created_date', date('d-m-Y'), ['id' => 'created_date', 'class' => 'form-control date_picker', 'placeholder' => 'Ngày tạo...', 'autocomplete' => 'off', 'data-url' => route('api.receipts.index')]) !!}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="box-body">
                                        <form role="form">
                                            <!-- select -->
                                            <div class="form-group">
                                                <label>Nội dung</label>
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
                                        <form id="form-receipt_apartment"
                                            action="{{ route('admin.debit.detail-service.action') }}" method="post">
                                            @csrf
                                            <input type="hidden" name="method" value="" />
                                            <table class="table no-margin" id="table_receipt_debit">
                                                <thead>
                                                    <tr>
                                                        <th></th>
                                                        <th>
                                                            <input type="checkbox" class="checkServiceAll"
                                                                onclick="check_chose_all(this)"
                                                                data-target=".checkSingle" />
                                                        </th>
                                                        <th>Dịch vụ</th>
                                                        <th style="width: 20%">Thời gian</th>
                                                        <th>Sản phẩm</th>
                                                        <th>Phát sinh</th>
                                                        <th>Thời gian hạch toán</th>
                                                        <th>Số dư/Đã hạch toán</th>
                                                        <th>Điểu chỉnh giảm</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="list_info">
                                                    <tr>
                                                        <td colspan="8" style="font-style: italic;">Tiền thừa căn hộ:</td>
                                                    </tr>
                                                    @if ($coin_apartment)
                                                        @foreach ($coin_apartment as $value)
                                                            <tr class="checkbox_parent">
                                                                @php
                                                                    
                                                                    $service_apartment = $value->bdc_apartment_service_price_id > 0 ? $serviceApartmentRepository->findApartmentServicePrice_v2($value->bdc_apartment_service_price_id) : null;
                                                                    $service = $service_apartment ? $serviceRepository->getInfoServiceById($service_apartment->bdc_service_id) : null;
                                                                    $vechicle = $service_apartment && $service_apartment->bdc_vehicle_id > 0 ? $vehicle->find($service_apartment->bdc_vehicle_id) : null;
                                                                @endphp
                                                                <td></td>
                                                                <td>
                                                                    <input type="checkbox" name="bdc_apartment_service_price_id" value="tien_thua_{{$value->bdc_apartment_service_price_id}}"
                                                                        class="checkSingle check_box_debit_receipt"
                                                                        onclick="check_chose(this)" />
                                                                </td>
                                                                <td>{{ $service ? @$service->name : 'Chưa chỉ định' }}
                                                                </td>
                                                                <td></td>
                                                                <td>{{ @$vechicle->number }}</td>
                                                                <td></td>
                                                                <td></td>
                                                                <td>{{ number_format($value->coin) }}</td>
                                                                <td> <input type="text" class="form-control paid_payment" value="{{ number_format($value->coin) }}" /></td>
                                                            </tr>
                                                        @endforeach
                                                    @else
                                                        <tr style="text-align: center;">
                                                            <td colspan="9">không có dữ liệu</td>
                                                        </tr>
                                                    @endif
                                                </tbody>
                                            </table>
                                            <div class="row mbm">
                                            </div>
                                        </form>
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
                        <a href="{{ route('admin.v2.receipt.index') }}" class="btn btn-warning mr-l"><i
                                class="bx bx-arrow-back"></i><span class="align-middle ml-25">Bỏ qua</span></a>
                        <button type="submit" class="btn btn-info mr-l tao_phieu_dieu_chinh" id="phieu_dieu_chinh"
                            data-url="{{ route('api.receipts.index') }}"
                            data-url-main="{{ route('admin.v2.receipt.index') }}"><i class="bx bx-save"></i><span
                                class="align-middle ml-25">Lập phiếu</span></button>
                    </div>
                </div>
            </div>
        </div>

    </section>
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
        function check_chose_all(element)
        {
            let total_paid_payment = 0;
            if($(element).is(":checked")) // nếu tích all
            {
                $('#table_receipt_debit > tbody  > tr.checkbox_parent').each(function() {
                        $(this).closest('.list_info').find('input.paid_payment').attr('readonly', true);
                        $(this).find("input.check_box_debit_receipt").prop('checked', true);

                        let currentPaidService = $(this).find('input.paid_payment').val() ? $(this).find('input.paid_payment').val() : 0;
                        let currentPaidServiceInt = currentPaidService.replace(/,/g, "");
                        total_paid_payment += parseInt(currentPaidServiceInt);
                });

                let totalPaidString = formatCurrencyV2(total_paid_payment.toString());
                $('#total_paid_payment').val(totalPaidString);

            }else{
                $('#table_receipt_debit > tbody  > tr').each(function(index, tr) {
                    $(this).find("input.check_box_debit_receipt").prop('checked',false);
                    $(this).closest('.checkbox_parent').find('input.paid_payment').attr('readonly', false);
                });
                $('#total_paid_payment').val(0);
            }
            
        }
        function check_chose(element)
        {

            let total_paid_payment = 0;
            $('#table_receipt_debit > tbody  > tr.checkbox_parent').each(function() {
                let check_box_input = $(this).find("input.check_box_debit_receipt");
                if(check_box_input.is(":checked") && $(this).find('input.paid_payment').val() != 0){
                                         $(this).find('input.paid_payment').attr('readonly', true);
                      let paid_payment = $(this).find('input.paid_payment').val();
                      total_paid_payment += parseInt(paid_payment.replace(/,/g, "").trim());
                }else{
                    $(this).find('input.paid_payment').attr('readonly', false);
                }
            });

            let totalPaidString = formatCurrencyV2(total_paid_payment.toString());
            $('#total_paid_payment').val(totalPaidString);
 
        }

        $('.list_info').on('input','input.paid_payment', function(e){  
            $(this).val(formatCurrency(this));
        }).on('keypress','input.paid_payment',function(e){
            if(!$.isNumeric(String.fromCharCode(e.which))) e.preventDefault();
        }).on('paste','input.paid_payment', function(e){   
            var cb = e.originalEvent.clipboardData || window.clipboardData;      
            if(!$.isNumeric(cb.getData('text'))) e.preventDefault();
        });

        $('.tao_phieu_dieu_chinh').click(function(e){
            e.preventDefault();
            var list_items = [];
            $('#table_receipt_debit > tbody  > tr.checkbox_parent').each(function() {
                let check_box_input = $(this).find("input.check_box_debit_receipt");
                if(check_box_input.is(":checked") && $(this).find('input.paid_payment').val() != 0){
                      let paid_payment = $(this).find('input.paid_payment').val();
                      list_items.push({
                        service_apartment_id: check_box_input.val(),
                        paid_payment: parseInt(paid_payment.replace(/,/g, "").trim())
                      });
                }
            });
            console.log(list_items);
            let total_paid_payment = $('#total_paid_payment').val();
            showLoading();
            $.ajax({
                url: '/admin/v2/receipt/save_payment_slip',
                type: 'POST',
                data: {
                    description:  $('#customer_description').val(),
                    type: $('#customer_payments').val(),
                    paid_payment:  0 -  parseInt(total_paid_payment.replace(/,/g, "")),
                    apartment_id: $('#bdc_apartment_id').val(),
                    created_date: $('#created_date').val(),
                    list_items:list_items
                },
                success: function(response) {
                    hideLoading();
                    if (response.error_code == 200) {
                        alert(response.message);
                    } else {
                        alert(response.message);
                    }
                    location.reload();
                }
            });

        })
        $('input.date_picker').datepicker({
            autoclose: true,
            dateFormat: "dd-mm-yy"
        }).val();
    </script>
    <script type="text/javascript" src="{{ url('adminLTE/js/function_dxmb.js') . '?v=' . \Carbon\Carbon::now()->timestamp }}"></script>
    <script type="text/javascript" src="{{ url('adminLTE/js/debit.js') . '?v=' . \Carbon\Carbon::now()->timestamp }}">
    </script>
    <script type="text/javascript"
        src="{{ url('adminLTE/js/validate-form-dxmb.js') . '?v=' . \Carbon\Carbon::now()->timestamp }}"></script>
    <script type="text/javascript" src="{{ url('adminLTE/js/format-currency.js') }}"></script>
@endsection
