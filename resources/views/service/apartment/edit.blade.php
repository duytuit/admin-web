@extends('backend.layouts.master')

@section('content')
    <section class="content-header">
        <h1>
            Quản lý dịch vụ
            <small>Sửa dịch vụ</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('admin.home') }}"><i class="fa fa-home"></i> Trang chủ</a></li>
            <li class="active">Quản lý dịch vụ</li>
        </ol>
    </section>
    <section class="content">
        <div class="box-body">
            <form data-action="{{route('admin.service.apartment.update',$apartmentService->id)}}" id="add_service"
                  method="POST">
                @method('PUT')
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
                                <div class="form-group div_bdc_apartment_id">
                                    <label> Tên căn hộ:</label>
                                    <select class="form-control apartment-list" name="bdc_apartment_id">
                                        <option value="{{ @$apartmentService->apartment->id }}"
                                                selected>{{ @$apartmentService->apartment->name }}</option>
                                    </select>
                                    <div class="message_zone"></div>
                                </div>
                                <div class="form-group div_bdc_service_id">
                                    <label>Tên dịch vụ:</label>
                                    <select class="form-control service-list" name="bdc_service_id">
                                        <option value="{{ @$apartmentService->service->id }}"
                                                selected>{{ $apartmentService->service->name }}</option>
                                    </select>
                                    <div class="message_zone"></div>
                                </div>
                                <div @if(@$apartmentService->service->type != \App\Repositories\Service\TYPEVEHICLE)
                                     hidden
                                     @endif
                                     class="form-group vehicle div_bdc_vehicle_id">
                                    <label for="exampleInputEmail1">Tên phương tiện:</label>
                                    <select id="vehicle" class="form-control vehicle-list" name="bdc_vehicle_id">
                                        <option value="{{ isset($apartmentService->vehicle->id) ? $apartmentService->vehicle->id : 0  }}"
                                                selected>{{ isset($apartmentService->vehicle->name) ? $apartmentService->vehicle->name : ''  }}</option>
                                    </select>
                                    <div class="message_zone"></div>
                                </div>
                                <div @if(@$apartmentService->service->servicePriceDefault->priceType->id == \App\Repositories\Service\MANY_PRICE | $apartmentService->service->type == \App\Repositories\BdcApartmentServicePrice\FLOOR_PRICE )
                                     hidden
                                     @endif class="form-group one-price div_price">
                                    <label for="exampleInputEmail1">Đơn giá:</label>
                                    <input id="price" type="text" name="price" maxlength="15" class="form-control"
                                           value="{{ number_format($apartmentService->price) }}">
                                    <div class="message_zone"></div>
                                </div>
                                <div @if(@$apartmentService->service->servicePriceDefault->priceType->id == \App\Repositories\Service\ONE_PRICE | $apartmentService->service->type == \App\Repositories\BdcApartmentServicePrice\FLOOR_PRICE )
                                     hidden
                                     @endif
                                     class="form-group many-price div_bdc_progressive_id">
                                    <label>Lũy tiến</label>
                                    <select id="select-progressive" class="form-control col-xs-8"
                                            name="bdc_progressive_id">
                                        <option value="" selected>Mặc định của tòa nhà</option>
                                    </select>
                                    <div class="message_zone"></div>
                                </div>
                                <div @if(@$apartmentService->service->type != \App\Repositories\BdcApartmentServicePrice\FLOOR_PRICE)
                                     hidden
                                     @endif class="form-group floor_price div_floor_price">
                                    <label for="exampleInputEmail1">Đơn giá / 1m<sup>2</sup> :</label>
                                    <input id="floor_price" type="number" name="floor_price" class="form-control"
                                           value="{{$apartmentService->floor_price}}">
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
                                           value="{{ $apartmentService->description }}">
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
                                               id="datepicker" value="{{ $apartmentService->first_time_active ? date('d-m-Y',strtotime($apartmentService->first_time_active)) :'' }}">
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
                                               id="finish" value="{{ $apartmentService->finish ? date('d-m-Y',strtotime($apartmentService->finish)) :'' }}">
                                    </div>
                                    <div class="message_zone"></div>
                                </div>
                                 <div class="form-group div_last_time_pay">
                                    <label for="exampleInputEmail1">Ngày tính phí tiếp theo:</label>
                                    <div class="input-group date">
                                        <div class="input-group-addon">
                                            <i class="fa fa-calendar"></i>
                                        </div>
                                        <input type="text" class="form-control pull-right date_picker1"
                                               name="last_time_pay"
                                               id="datepicker " value="{{ $apartmentService->last_time_pay ? date('d-m-Y',strtotime($apartmentService->last_time_pay)) :'' }}">
                                    </div>
                                    <div class="message_zone"></div>
                                </div>
                            </div>
                            <!-- /.box-body -->
                            <div class="box-footer">
                                <a href="{{ route('admin.service.apartment.index') }}" type="button"
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
         $('input.date_picker1').datepicker({
            autoclose: true,
            dateFormat: "dd-mm-yy"
        }).val();

        submitAjaxForm('#save_service', '#add_service', '.div_', '.message_zone');

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
            $(".service-list").on('change', function () {
                var type = $(this).children(":selected").attr("id");
                var priceType = $(this).children(":selected").attr("class");
                if (type == VEHICLE) {
                    $('.vehicle').removeAttr("hidden");
                } else {
                    $('.vehicle').attr("hidden", true);
                }
                if (priceType == ONEPRICE) {
                    $('.one-price').removeAttr("hidden");
                    $('.many-price').attr("hidden", true);
                }
                if (priceType == MANYPRICE) {
                    $('.many-price').removeAttr("hidden");
                    $('.one-price').attr("hidden", true);
                }

            });
            $(document).on('change', '.apartment-list', function (e) {
                e.preventDefault();
                var id = $(this).children(":selected").attr("id");
                $.ajax({
                    url: '{{route('admin.service.apartment.getVehicle')}}',
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
            $('input[name="price"]').keyup(function(event) {

                // skip for arrow keys
                if(event.which >= 37 && event.which <= 40) return;

                // format number
                $(this).val(function(index, value) {
                    return value
                        .replace(/\D/g, "")
                        .replace(/\B(?=(\d{3})+(?!\d))/g, ",")
                        ;
                });
            });
        });
    </script>
@endsection