@extends('backend.layouts.master')

@section('content')
    <section class="content-header">
        <h1>
            Quản lý kế toán
            <small>Quản lý công nợ</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('admin.home') }}"><i class="fa fa-home"></i> Trang chủ</a></li>
            <li class="active">Quản lý công nợ</li>
        </ol>
    </section>

    <section class="content">
        <div class="box box-primary">
            <div class="box-body font-weight-bold">
                <h3>Công nợ tổng hợp
                </h3>
            </div>
            <div class="box-body">
                {{-- @include('layouts.head-building') --}}
            </div>
            <div class="box-body">
                <div class="row form-group">
                    <div class=" col-md-12 ">
                        <a class="btn btn-primary showModal"><i
                                    class="fa fa-scissors"></i>
                            Tính công nợ tháng</a>
                        <a href="{{ route('admin.v2.progressive.importexcel') }}" class="btn btn-success">
                            <i class="fa fa-plus" aria-hidden="true"></i>
                            Import Phí Dịch Vụ Lũy Tiến
                        </a>
                        <a href="{{ route('admin.v2.progressive.importexcelphidauky') }}" class="btn btn-success">
                            <i class="fa fa-plus" aria-hidden="true"></i>
                            Import Phí Dịch Vụ Đầu Kỳ
                        </a>
                        <a class="btn btn-success handle_electric_water">
                            <i class="fa fa-plus" aria-hidden="true"></i>
                            Tính công nợ điện nước
                        </a>
                        <a href="{{ route('admin.debitlog.importDienNuoc') }}" class="btn btn-info">Lịch sử impport file</a>
                    </div>
                </div>
                
            </div>
        </div>
    </section>
    @include('debit.v2.modal.make_receipt_detail')
    @include('debit.v2.modal.make_receipt_detail_year')
    @include('debit.v2.modal.make_electric_water')
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
    <script>
           
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
               
                $('select#customer_handler').on('change', function (e) {
                    if($(this).val() == 'can_ho'){
                        $('#nhom_can_ho').css('display','none');
                        $('#nhom_can_ho').val('');
                        $('.select2').css('display','block');
                    }else if($(this).val() == 'nhom_can_ho'){
                        $('.select2').css('display','none');
                        $('#can_ho').val('');
                        $('#nhom_can_ho').css('display','block');
                    }else{ // tất cả
                        $('.select2').css('display','none');
                        $('#nhom_can_ho').css('display','none');
                        $('#can_ho').val('');
                        $('#nhom_can_ho').val('');
                    }
                });

                $('select#customer_handler_electric_water').on('change', function (e) {
                    if($(this).val() == 'can_ho'){
                        $('#nhom_can_ho_electric_water').css('display','none');
                        $('#nhom_can_ho_electric_water').val('');
                        $('.select2').css('display','block');
                    }else if($(this).val() == 'nhom_can_ho'){
                        $('#nhom_can_ho_electric_water').css('display','block');
                        $('.select2').css('display','none');
                        $('#can_ho').val('');
                    }else{ // tất cả
                        $('.select2').css('display','none');
                        $('#nhom_can_ho_electric_water').css('display','none');
                        $('#can_ho').val('');
                        $('#nhom_can_ho_electric_water').val('');
                    }
                });
                $('.cycle_month').change(function (e) { 
                    e.preventDefault();
                    $('table > tbody  > tr.list_item').each(function(index, tr) { 
                        let lay_ngay = $(this).find('.ngay_bat_dau').attr('data-ngay_bat_dau');

                        let ngay_bat_dau = lay_ngay + '-'+parseInt($('.cycle_month').val())+'-'+$('.cycle_year').val();
                        let last_month = parseInt($('.cycle_month').val())+1;
                        let last_year = parseInt($('.cycle_year').val());
                        console.log($(this).val());
                        if(last_month == 13){
                            last_month=1;
                            last_year+=1;
                        }
                        let ngay_ket_thuc = lay_ngay+'-'+last_month+'-'+last_year;
                        $(this).find('.ngay_bat_dau').val(ngay_bat_dau);
                        $(this).find('.ngay_ket_thuc').val(ngay_ket_thuc);

                    });
                   
                });
                $('.change_').change(function (e) { 
                    e.preventDefault();
                    let eletric_meter = [];
                    $('tr.list_item_electric_meter').each(function(index, tr) { 
                        var checked = $(this).find('.icheckbox_square-green').hasClass("checked");
                      
                        if(checked) {
                            let value = $(this).find('.change_').val();
                             if(value)
                             eletric_meter.push(parseInt(value));
                        }

                    });
                    console.log(eletric_meter);
                });
                $('.handle_electric_water').click(function (e) { 
                    e.preventDefault();
                    $('#cycle_name_handle_electric').val('');
                    $('#cycle_name_handle_meter').val('');
                    $('.select2').css('display','none');
                    $('#can_ho').val('');
                    $('#nhom_can_ho_electric_water').val('');
                    $('#HandleElectricMeter').modal('show');
                });
                $('.showModal').click(function (e) { 
                    e.preventDefault();
                    $('.select2').css('display','none');
                    $('#can_ho').val('');
                    $('#nhom_can_ho').val('');
                    $('#showModal').modal('show');
                });
           });
    </script>

@endsection