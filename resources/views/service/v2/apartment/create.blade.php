@extends('backend.layouts.master')

@section('content')
    <section class="content-header">
        <h1>
            Quản lý dịch vụ
            <small>Thêm dịch vụ</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('admin.home') }}"><i class="fa fa-home"></i> Trang chủ</a></li>
            <li class="active">Quản lý dịch vụ</li>
        </ol>
    </section>
    <section class="content">
        <div class="box-body">
            <form data-action="{{route('admin.v2.service.apartment.store')}}" id="add_service" method="POST">
                @csrf
                <div class="row">
                    <!-- left column -->
                    <div class="col-md-6">
                        <!-- general form elements -->
                        <div class="box box-primary">
                            <div class="box-header with-border">
                                <h3 class="box-title">Dịch vụ</h3>
                            </div>
                            <!-- /.box-header -->
                            <!-- form start -->
                            <div class="box-body">
                                <div class="form-group div_bdc_apartment_id search-advance">
                                    <label> Tên căn hộ:</label>
                                    <select class="form-control apartment-list selectpicker" name="bdc_apartment_id"
                                            id="bdc_apartment_id" data-live-search="true">
                                        <option value="" selected>-- Chọn căn hộ --</option>
                                        @foreach($apartments as $apartment)
                                            <option id="{{$apartment->id}}" value="{{ $apartment->id }}"
                                                    @if(old('bdc_apartment_id') == $apartment->id) selected @endif>{{ $apartment->name }}</option>
                                        @endforeach
                                    </select>
                                    <div class="message_zone"></div>
                                </div>
                                <div class="form-group div_bdc_service_id">
                                    <label>Tên dịch vụ:</label>
                                    <select class="form-control service-list select2" name="bdc_service_id"
                                            id="bdc_service_id">
                                        <option value="" selected>-- Chọn dịch vụ --</option>
                                        @foreach($services as $service)
                                            <option id="{{$service->type}}" value="{{ $service->id }}"
                                                    class="{{ $service->servicePriceDefault != null ? $service->servicePriceDefault->priceType->id : ""}}"
                                                    @if(old('bdc_service_id') == $service->id) selected @endif>{{ $service->name }}</option>
                                        @endforeach
                                    </select>
                                    <div class="message_zone"></div>
                                </div>
                                <div hidden
                                     class="form-group vehicle div_bdc_vehicle_id">
                                    <label for="exampleInputEmail1">Tên phương tiện:</label>
                                    <select id="vehicle" class="form-control vehicle-list" name="bdc_vehicle_id">
                                    </select>
                                    <div class="message_zone"></div>
                                </div>
                                <div class="form-group one-price div_price">
                                    <label class="price" for="exampleInputEmail1">Đơn giá:</label>
                                    <input id="price" type="number" step="any" min="0" name="price" class="form-control"
                                           value="{{ old('price') }}">
                                    <div class="message_zone"></div>
                                </div>
                                <div hidden
                                     class="form-group many-price div_bdc_progressive_id">
                                    <label>Lũy tiến</label>
                                    <select id="select-progressive" class="form-control col-xs-8"
                                            name="bdc_progressive_id">
                                        <option value="" selected>Mặc định của tòa nhà</option>
                                    </select>
                                    <div class="message_zone"></div>
                                </div>
                                <div hidden class="form-group floor_price div_floor_price">
                                    <label class="floor_price_1" for="exampleInputEmail1">Đơn giá / 1m<sup>2</sup>
                                        :</label>
                                    <input id="floor_price" type="number" name="floor_price" class="form-control"
                                           value="{{ old('floor_price') }}">
                                    <div class="message_zone"></div>
                                </div>
                                <div class="form-group div_unit">
                                    <label for="exampleInputEmail1">Đơn vị tính:</label>
                                    <input type="text" name="unit" class="form-control" disabled="" value="VND">
                                    <div class="message_zone"></div>
                                </div>
                                <div class="form-group div_description">
                                    <label for="exampleInputEmail1">Mô tả:</label>
                                    <input type="text" name="description" class="form-control"
                                           value="{{ old('description') }}">
                                    <div class="message_zone"></div>
                                </div>
                            </div>
                        </div>
                        <!-- /.box -->
                    </div>
                    <!--/.col (left) -->
                    <!-- right column -->
                    <div class="col-md-6">
                        <!-- Horizontal Form -->
                        <div class="box box-info">
                            <div class="box-header with-border">
                                <h3 class="box-title">Hạn dịch vụ</h3>
                            </div>
                            <!-- /.box-header -->
                            <!-- form start -->
                            <div class="box-body">
                                <div class="form-group div_first_time_active">
                                    <label for="exampleInputEmail1">Ngày bắt đầu:</label>
                                    <div class="input-group date">
                                        <div class="input-group-addon">
                                            <i class="fa fa-calendar"></i>
                                        </div>
                                        <input type="text" class="form-control pull-right date_picker"
                                               name="first_time_active"
                                               id="datepicker" value="{{ old('first_time_active') }}">
                                    </div>
                                    <div class="message_zone"></div>
                                </div>
                                <div class="form-group div_finish">
                                    <label for="exampleInputEmail1">Ngày kết thúc:</label>
                                    <div class="input-group date">
                                        <div class="input-group-addon">
                                            <i class="fa fa-calendar"></i>
                                        </div>
                                        <input type="text" class="form-control pull-right date_picker"
                                               name="finish"
                                               id="finish" value="{{ old('finish') }}">
                                    </div>
                                    <div class="message_zone"></div>
                                </div>
                            </div>
                            <!-- /.box-body -->
                            <div class="box-footer">
                                <a href="{{ route('admin.v2.service.apartment.index') }}" type="button"
                                   class="btn btn-default pull-left">Quay lại</a>
                                <button type="submit" class="btn btn-success pull-right" id="save_service">Lưu</button>
                            </div>
                            <!-- /.box-footer -->
                        </div>
                        <!-- /.box -->
                    </div>
                    <!--/.col (right) -->
                </div>
            </form>
        </div>
    </section>
@endsection

@section('javascript')
    <!-- TinyMCE -->
    <script src="/adminLTE/plugins/tinymce/tinymce.min.js"></script>
    <script src="/adminLTE/plugins/tinymce/config.js"></script>
    <script>
        //Date picker
        $('input.date_picker').datepicker({
            autoclose: true,
            dateFormat: "dd-mm-yy"
        }).val();

        //submitAjaxForm('#save_service', '#add_service', '.div_', '.message_zone');
        $('#save_service').on('click', function (e) {
            e.preventDefault();
            $.ajax({
                url: "{{route('admin.ajax.check_type_electric_water')}}",
                method: 'POST',
                dataType: 'json',
                data: {
                    apartment_id: $('#bdc_apartment_id').val(),
                    service_id: $('#bdc_service_id').val()
                },
                success: function (response) {
                    console.log(response);
                    var formCreate = $('#add_service');
                    if (response.count > 0) {
                        if (confirm("Căn hộ đã tồn tại dịch vụ điện nước. Bạn có muốn thay đổi?")) {
                            showLoading();
                            $.ajax({
                                url: formCreate.attr('data-action'),
                                type: formCreate.attr('method'),
                                data: formCreate.serialize(),
                                success: function (response) {
                                    if (response.success == true) {
                                        toastr.success(response.message);
                                        if (!response.href) {
                                            setTimeout(() => {
                                                location.reload()
                                            }, 2000)
                                        } else {
                                            setTimeout(() => {
                                                location.reload()
                                            }, 2000)
                                        }
                                    } else if (response.success == false) {
                                        toastr.error(response.message);
                                        if (!response.href) {
                                            setTimeout(() => {
                                                location.reload()
                                            }, 2000)
                                        } else {
                                            setTimeout(() => {
                                                location.reload()
                                            }, 2000)
                                        }
                                    } else {
                                        toastr.error('Có lỗi! Xin vui lòng thử lại');
                                        setTimeout(() => {
                                            location.reload()
                                        }, 2000)
                                    }
                                    hideLoading();
                                    requestSend = false;
                                },
                                error: function (response) {
                                    $(document).find('.has-error').removeClass('has-error');
                                    if ($(document).find('.help-block').length) {
                                        $(document).find('.help-block').remove();
                                    }
                                    showErrorsCreate(response.responseJSON.errors, '.div_', '.message_zone');
                                    hideLoading();
                                    requestSend = false;
                                }
                            })
                        } else {
                            return false;
                        }
                    } else {
                        showLoading();
                        $.ajax({
                            url: formCreate.attr('data-action'),
                            type: formCreate.attr('method'),
                            data: formCreate.serialize(),
                            success: function (response) {
                                if (response.success == true) {
                                    toastr.success(response.message);
                                    if (!response.href) {
                                        setTimeout(() => {
                                            location.reload()
                                        }, 2000)
                                    } else {
                                        setTimeout(() => {
                                            location.reload()
                                        }, 2000)
                                    }
                                } else if (response.success == false) {
                                    toastr.error(response.message);
                                    if (!response.href) {
                                        setTimeout(() => {
                                            location.reload()
                                        }, 2000)
                                    } else {
                                        setTimeout(() => {
                                            location.reload()
                                        }, 2000)
                                    }
                                } else {
                                    toastr.error('Có lỗi! Xin vui lòng thử lại');
                                    setTimeout(() => {
                                        location.reload()
                                    }, 2000)
                                }
                                hideLoading();
                                requestSend = false;
                            },
                            error: function (response) {
                                $(document).find('.has-error').removeClass('has-error');
                                if ($(document).find('.help-block').length) {
                                    $(document).find('.help-block').remove();
                                }
                                showErrorsCreate(response.responseJSON.errors, '.div_', '.message_zone');
                                hideLoading();
                                requestSend = false;
                            }
                        })
                    }
                },
                error: function (response) {
                    hideLoading();
                }
            })
        });
    </script>

    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        $(document).ready(function () {
            const VEHICLE = 1;
            const ONEPRICE = 1;
            const MANYPRICE = 2;
            const FLOORPRICE = 2;
            $(".service-list").on('change', function () {
                var type = $(this).children(":selected").attr("id");
                var priceType = $(this).children(":selected").attr("class");
                if (type == VEHICLE) {
                    $('.vehicle').removeAttr("hidden");
                    $('.one-price').removeAttr("hidden");
                    $('.floor_price').attr("hidden", true);
                } else if (type == FLOORPRICE) {
                    $('.floor_price').removeAttr("hidden");
                    $('.vehicle').attr("hidden", true);
                    $('.one-price').attr("hidden", true);
                    $('.many-price').attr("hidden", true);
                } else {
                    $('.one-price').removeAttr("hidden");
                    $('.floor_price').attr("hidden", true);
                    $('.vehicle').attr("hidden", true);
                }
                if (priceType == ONEPRICE && type != FLOORPRICE) {
                    $('.one-price').removeAttr("hidden");
                    $('.many-price').attr("hidden", true);
                }
                if (priceType == MANYPRICE && type != FLOORPRICE) {
                    $('.many-price').removeAttr("hidden");
                    $('.one-price').attr("hidden", true);
                }
            });
            $(document).on('change', '.apartment-list', function (e) {
                e.preventDefault();
                var id = $(this).children(":selected").attr("id");
                $.ajax({
                    url: '{{route('admin.v2.service.apartment.getVehicle')}}',
                    type: 'POST',
                    data: {
                        id: id
                    },
                    success: function (response) {
                        var $vehicle = $('#vehicle');
                        $vehicle.empty();
                        $vehicle.append('<option value="" selected>-- Chọn phương tiện --</option>');
                        $.each(response, function (index, val) {
                            if (index != 'debug') {
                                $vehicle.append('<option value="' + index + '">' + val + '</option>')
                            }
                        });
                    }
                })
            });
            $(document).on('change', '.service-list', function (e) {
                e.preventDefault();
                var type = $(this).children(":selected").attr("id");
                var id = $(this).children(":selected").val();
                $.ajax({
                    url: '{{route('admin.v2.service.apartment.getServiceApartmentAjax')}}',
                    type: 'POST',
                    data: {
                        id: id
                    },
                    success: function (response) {
                        // if (type != FLOORPRICE)
                        // {
                        $('#datepicker').val('');
                        $('#price').val(response.price);
                        $('.price').html('Đơn giá: ( mặc định: ' + response.price + ' )')
                        $('#floor_price').val(response.price);
                        $('.floor_price_1').html('Đơn giá: ( mặc định: ' + response.price + ' )')
                        if (response.bdc_period_id == 6) { // chu kỳ theo năm
                            $('#datepicker').val(response.first_time_active);
                        }
                        //}
                    }
                })
            });
            $('input[name="price"]').keyup(function (event) {

                // skip for arrow keys
                if (event.which >= 37 && event.which <= 40) return;

                // format number
                $(this).val(function (index, value) {
                    return value
                        .replace(/\D/g, "")
                        .replace(/\B(?=(\d{3})+(?!\d))/g, ",")
                        ;
                });
            });
        });
    </script>
@endsection