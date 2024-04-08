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
                        <a data-toggle="modal" data-target="#showModal" class="btn btn-primary"><i
                                    class="fa fa-scissors"></i>
                            Tính công nợ tháng</a>
                        <a data-toggle="modal" data-target="#showModalYear" class="btn btn-primary"><i
                                class="fa fa-scissors"></i>
                        Tính công nợ năm</a>
                        <a href="{{ route('admin.progressive.importexcel') }}" class="btn btn-success">
                            <i class="fa fa-plus" aria-hidden="true"></i>
                            Import điện nước
                        </a>
                        <a href="{{ route('admin.progressive.importexcelphidauky') }}" class="btn btn-success">
                            <i class="fa fa-plus" aria-hidden="true"></i>
                            Import phí dịch vụ
                        </a>
                        <a href="{{ route('admin.debitlog.importDienNuoc') }}" class="btn btn-info">Lịch sử impport file</a>
                    </div>
                </div>
                
            </div>
        </div>
    </section>
    @include('debit.modal.make_receipt_detail')
    @include('debit.modal.make_receipt_detail_year')
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
            // $("#datepicker").datepicker({
            //     format: "mm-yyyy",
            //     autoclose: true,
            //     viewMode: "months",
            //     minViewMode: "months"
            // }).val();
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
        });
    </script>

@endsection