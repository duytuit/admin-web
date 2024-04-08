@extends('backend.layouts.master')

@section('stylesheet')
{{-- <link rel="stylesheet" href="{{ url('adminLTE/css/datxanhcare.css') }}" /> --}}
@endsection

@section('content')
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
                    <div class="box box-primary">
                        <div class="box-header with-border text-center bg-primary">
                            <h4 class="text-create-recipt">Lập phiếu thu</h4>
                        </div>
                        <!-- /.box-header -->
                        <div class="box-body no-padding">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="box-body">
                                        <form role="form">
                                            <!-- select -->
                                            <div class="form-group">
                                                <label>Chọn căn hộ</label>
                                                <select class="form-control selectpicker" data-live-search="true" id="choose_apartment" data-url="{{ route('api.provisionalreceipt.index') }}">
                                                    <option value="0">Lựa chọn căn hộ</option>
                                                    @foreach ($apartments as $apartment)
                                                        <option value="{{ $apartment->id }}">{{ $apartment->name }}</option>
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
                                                <label>Người nộp tiền</label>
                                                <input type="text" class="form-control" name="customer_fullname" id="customer_fullname" value="">
                                            </div>
                                        </form>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="box-body">
                                        <form role="form">
                                            <!-- select -->
                                            <div class="form-group">
                                                <label>Tham chiếu</label>
                                                <select class="form-control" id="choose_provisional_receipt" data-url="{{ route('api.provisionalreceipt.index') }}">
                                                </select>
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
                    <div class="row">
                        <div class="col-md-12">
                            <!-- DIRECT CHAT -->
                            <div class="box box-primary direct-chat direct-chat-warning">
                                <div class="box-header with-border text-center bg-primary">
                                    <h4 class="text-create-recipt">Danh sách hóa đơn</h4>
                                </div>
                                <div class="box-body no-padding">
                                    <div class="table-responsive result_receipt">
                                        
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

                <div class="col-md-3">
                    <div class="box box-primary">
                        <div class="box-header with-border text-center bg-primary">
                            <h4 class="text-create-recipt">Thông tin phiếu thu</h4>
                        </div>
                        <!-- /.box-header -->
                        <div class="box-body">
                            <form role="form">
                                <div class="box-body no-padding">
                                    <div class="form-group">
                                        <label for="exampleInputEmail1">Người nộp tiền</label>
                                        <input type="text" class="form-control" name="customer_name_provisional_receipt" id="customer_name_provisional_receipt" value="" readonly>
                                        <input type="hidden" name="data_receipt" class="data_receipt" value=""/>
                                        <input type="hidden" name="data_receipt" class="service_ids" value=""/>
                                    </div>
                                    <div class="form-group">
                                        <label for="exampleInputPassword1">Số tiền nộp</label>
                                        <input type="text" class="form-control" name="price_provisional_receipt" id="price_provisional_receipt" value="" readonly>
                                    </div>
                                    <div class="form-group">
                                        <label for="">Nội dung</label>
                                        <textarea class="form-control" rows="5" name="description_provisional_receipt" id="description_provisional_receipt" readonly></textarea>
                                    </div>
                                    <div class="form-group form-horizontal">
                                        <label for="">Hình thức thanh toán</label>
                                        <input type="text" class="form-control" name="payment_type_provisional_receipt" id="payment_type_provisional_receipt" value="" readonly>
                                    </div>
                                    <div class="form-group row">
                                        <div class="col-md-4">
                                            <button type="button" class="btn bg-gray print_and_collect_money" data-url="{{ route('api.provisionalreceipt.index') }}">Thu và In</button>
                                        </div>
                                        <div class="col-md-4">
                                            <button type="button" class="btn bg-gray collect_money" data-url="{{ route('api.provisionalreceipt.index') }}">Thu tiền</button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <!-- /.col -->
            </div>
        </div>
    </section>
@endsection

@section('javascript')

<script>
    $(document).on('change', '#choose_apartment', function (e) {
        showLoading();
        var apartmentId = $('#choose_apartment').val();
        $('#customer_fullname').val('');
        $('#customer_address').val('');
        $('#customer_paid').val('');
        $('.data_receipt').val('');
        $('#choose_provisional_receipt').empty();
        $.ajax({
            url: $(this).attr('data-url') + '/filterApartment/' + apartmentId,
            type: 'GET',
            success: function (response) {
                hideLoading();
                if (response.error_code == 200) {
                    $('.result_receipt').html(response.data.html);
                    $('#customer_fullname').val(response.data.customer_name);
                    $('#customer_address').val(response.data.customer_address);
                    $('#customer_name_provisional_receipt').val(response.data.customer_name_provisional_receipt);
                    $('#price_provisional_receipt').val(response.data.price_provisional_receipt);
                    $('#description_provisional_receipt').val(response.data.description_provisional_receipt);
                    $('#payment_type_provisional_receipt').val(response.data.payment_type_provisional_receipt);
                    $.each(response.data.provisionalReceipts, function(i, d) {
                        $('#choose_provisional_receipt').append('<option value="' + d.id + '">' + d.receipt_code + '</option>');
                    });
                } else {
                    if (response.error_code === 404) {
                        alert(response.message);
                    } else {
                        alert('Thông tin căn hộ không chính xác');
                    }    
                    $('.result_receipt').html('');
                }
            }
        });
    });

    $(document).on('click', '.collect_money', function (e) {
        var apartmentId = $('#choose_apartment').val();
        var chooseType = $('#choose_type').val();
        var buildingId = '{{ $building_id }}';
        var customer_fullname = $('#customer_fullname').val();
        var customer_address = $('#customer_address').val();
        var customer_paid = $('#customer_paid').val();
        var customer_description = $('#customer_description').val();
        var data_receipt = $('.data_receipt').val();
        // $(this).prop('disabled', true);
        $.ajax({
            url: $(this).attr('data-url') + '/create',
            type: 'POST',
            data: {
                customer_fullname: customer_fullname,
                customer_address: customer_address,
                customer_total_paid: customer_paid,
                customer_description: customer_description,
                data_receipt: data_receipt,
                type: chooseType
            },
            success: function (response) {
                if (response.error_code == 200) {
                    alert(response.message);
                } else {
                    alert('Kiểu phiếu thu không chính xác. Mời chọn lại');
                    $(this).prop('disabled', false);
                }
            }
        });
    });

    var service_ids = '';
    var billCodes = '';
    var data = '';
    function checkService(element)
    {
        var currentPaid = $('#customer_paid').val() == '' ? 0 : $('#customer_paid').val();
        var currentPaidService = $(element).closest('.checkbox_parent').find('input.total_payment').val();
        var service_id = $(element).closest('.checkbox_parent').find('input.service_id').val();
        var apartment_service_price_id = $(element).closest('.checkbox_parent').find('input.apartment_service_price_id').val();
        var billCode = $(element).closest('.checkbox_parent').find('input.bill_code').val();
        var totalPaid = 0;
        var apartmentId = $('#choose_apartment').val();
        var type = $('#choose_type').val();
        var buildingId = '{{ $building_id }}';
        
        if($(element).is(":checked")) {
            totalPaid = parseInt(currentPaid) + parseInt(currentPaidService);
            service_id = service_id == undefined ? 0 : service_id;
            apartment_service_price_id = apartment_service_price_id == undefined ? 0 : apartment_service_price_id;
            data += '{"apartment_id":' + apartmentId + ',"bill_code":"' + billCode + '","paid": ' + currentPaidService + ',"type": ' + type + ',"service_id":' + service_id + ',"apartment_service_price_id":' + apartment_service_price_id + '},';
        } else {
            service_id = service_id == undefined ? 0 : service_id;
            var dataReceipt = $.parseJSON($('.data_receipt').val());
            $flag = false;
            $.each(dataReceipt, function(i, item){
                console.log(item.bill_code + '--' + billCode);
                console.log(item.service_id + '--' + service_id);
                if(item.bill_code == billCode && item.apartment_service_price_id == apartment_service_price_id)
                {
                    var paid = item.paid;
                    totalPaid = parseInt(currentPaid) - parseInt(paid);
                    var removeElement = '{"apartment_id":' + apartmentId + ',"bill_code":"' + billCode + '","paid": ' + paid + ',"type": ' + type + ',"service_id":' + service_id + ',"apartment_service_price_id":' + apartment_service_price_id + '},';
                    data = data.replace(removeElement, '');
                    $flag = true;
                    return true;
                }
            });
            if(!$flag)
            {
                totalPaid = parseInt(currentPaid) - parseInt(currentPaidService);
                var removeElement = '{"apartment_id":' + apartmentId + ',"bill_code":"' + billCode + '","paid": ' + currentPaidService + ',"type": ' + type + ',"service_id":' + service_id + ',"apartment_service_price_id":' + apartment_service_price_id + '},';
                data = data.replace(removeElement, '');
            }
        }
        
        var newData = data.substring(0, data.length - 1);
        
        $('#customer_paid').val(totalPaid);
        $('.data_receipt').val('[' + newData + ']');
        $('.service_ids').val(service_ids);
    }
</script>

@endsection