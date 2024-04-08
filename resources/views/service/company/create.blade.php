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
            <form data-action="{{route('admin.service.company.store')}}" method="POST" id="add_service">
                @csrf
                <div class="row">
                    <!-- left column -->
                    <div class="col-md-6">
                        <!-- general form elements -->
                        <div class="box box-primary">
                            <div class="box-header with-border">
                                <h3 class="box-title"> Thông tin dịch vụ</h3>
                            </div>
                            <!-- /.box-header -->
                            <!-- form start -->
                            <div class="box-body">
                                <div class="form-group div_name">
                                    <label for="exampleInputEmail1">Tên dịch vụ:</label>
                                    <input type="text" name="name" class="form-control" value="{{ old('name') }}">
                                    <div class="message_zone"></div>
                                </div>
                                <div class="form-group div_bdc_price_type_id">
                                    <label>Bảng giá:</label>
                                    <select class="form-control price-list" name="bdc_price_type_id">
                                        <option value="" selected>-- Chọn bảng giá --</option>
                                        @foreach($priceTypes as $priceType)
                                            <option value="{{ $priceType->id }}"
                                                    @if(old('bdc_price_type_id') == $priceType->id) selected @endif>{{ $priceType->name }}</option>
                                        @endforeach
                                    </select>
                                    <div class="message_zone"></div>
                                </div>
                                <div hidden
                                     class="form-group one-price div_price">
                                    <label for="exampleInputEmail1">Đơn giá:</label>
                                    <input type="text" name="price" class="form-control" maxlength="15" value="{{ old('price') }}">
                                    <div class="message_zone"></div>
                                </div>
                                <div hidden
                                     class="form-group many-price div_progressive_id">
                                    <label>Lũy tiến</label>
                                    <select id="select-progressive" class="form-control col-xs-8" name="progressive_id">
                                        <option value="" selected>-- Chọn lũy tiến --</option>
                                        @foreach($progressives as $progressive)
                                            <option value="{{ $progressive->id }}"
                                                    @if(old('progressive_id') == $progressive->id) selected @endif>{{ $progressive->name }}</option>
                                        @endforeach
                                    </select>
                                    <div class="message_zone"></div>
                                </div>
                                <div hidden class="many-price">
                                    <a class="pull-right" target="_blank" style="border: none"
                                       href="{{route('admin.progressive.create') }}"><i class="fa fa-plus"
                                                                                        aria-hidden="true"></i>
                                        Thêm bảng giá lũy tiến</a>
                                </div>
                                <div class="form-group div_unit">
                                    <label for="exampleInputEmail1">Đơn vị tính:</label>
                                    <input type="text" name="unit" class="form-control" disabled="" value="VND">
                                    <div class="message_zone"></div>
                                </div>
                                <div
                                        class="form-group div_type">
                                    <label>Loại dịch vụ: </label>
                                    <select class="form-control" name="type">
                                        <option value="" selected>-- Chọn loại dịch vụ --</option>
                                        <option value="0">Dịch vụ thường</option>
                                        <!-- <option value="1">Phương tiện</option> -->
                                        <option value="2">Sàn nhà</option>
                                    </select>
                                    <div class="message_zone"></div>
                                </div>
                                <div
                                        class="form-group div_service_group">
                                    <label>Nhóm dịch vụ: </label>
                                    <select class="form-control" name="service_group">
                                        <option value="" selected>-- Chọn nhóm dịch vụ --</option>
                                        <option value="1">Phí công ty</option>
                                        <option value="2">Phí thu hộ</option>
                                        <option value="3">Phí chủ đầu tư</option>
                                    </select>
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
                                <div class="form-group div_bdc_period_id">
                                    <label>Chu kỳ:</label>
                                    <select class="form-control" name="bdc_period_id">
                                        <option value="1" selected>1 tháng</option>
                                    </select>
                                    <div class="message_zone"></div>
                                </div>
                                <div class="form-group div_bill_date">
                                    <label for="exampleInputEmail1">Ngày đầu kỳ số liệu:</label>
                                    <input type="number" name="bill_date" class="form-control"
                                           value="{{ old('bill_date') }}">
                                    <div class="message_zone"></div>
                                </div>
                                <div class="form-group div_payment_deadline">
                                    <label for="exampleInputEmail1">Ngày thanh toán:</label>
                                    <input type="number" name="payment_deadline" class="form-control"
                                           value="{{ old('payment_deadline') }}">
                                    <div class="message_zone"></div>
                                </div>
                                <div class="form-group div_first_time_active">
                                    <label>Áp dụng từ:</label>
                                    <div class="input-group date">
                                        <div class="input-group-addon">
                                            <i class="fa fa-calendar"></i>
                                        </div>
                                        <input type="text" class="form-control pull-right date_picker"
                                               name="first_time_active"
                                               id="datepicker" value="{{ old('first_time_active') }}">
                                    </div>
                                    <div class="message_zone"></div>
                                    <!-- /.input group -->
                                </div>
                            </div>
                            <!-- /.box-body -->
                            <div class="box-footer">
                                <a href="{{ route('admin.service.company.index') }}" type="button"
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


        //save department
        submitAjaxForm('#save_service', '#add_service', '.div_', '.message_zone');
    </script>
    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        $(document).ready(function () {
            const ONEPRICE = 1;
            const MANYPRICE = 2;
            $('.price-list').on('change', function () {
                var value = $(this).val();
                if (value == ONEPRICE) {
                    $('.one-price').removeAttr("hidden");
                    $('.many-price').attr("hidden", true);
                }
                if (value == MANYPRICE) {
                    $('.many-price').removeAttr("hidden");
                    $('.one-price').attr("hidden", true);
                }
            });
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
    </script>
@endsection