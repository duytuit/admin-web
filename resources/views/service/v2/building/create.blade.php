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
            <form data-action="{{route('admin.v2.service.building.store')}}" method="POST" id="add_service">
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
                                        @foreach($priceTypes as $priceType)
                                            <option value="{{ $priceType->id }}"
                                                    @if(old('bdc_price_type_id') == $priceType->id) selected @endif>{{ $priceType->name }}</option>
                                        @endforeach
                                    </select>
                                    <div class="message_zone"></div>
                                </div>
                                <div class="form-group one-price div_price">
                                    <label for="exampleInputEmail1">Đơn giá:</label>
                                    <input type="number" name="price" maxlength="15" class="form-control" value="{{ old('price') }}">
                                    <div class="message_zone"></div>
                                </div>
                                <div hidden
                                        class="form-group many-price div_progressive_id">
                                    <label>Giá theo dịch vụ</label>
                                    <select  class="form-control select2" name="progressive_id" style="width:100%">
                                        <option value="" selected>-- Chọn loại giá --</option>
                                        @foreach($progressives as $progressive)
                                            <option value="{{ $progressive->id }}"
                                                    @if(old('progressive_id') == $progressive->id) selected @endif>{{ $progressive->name }}</option>
                                        @endforeach
                                    </select>
                                    <div class="message_zone"></div>
                                </div>
                                <div hidden class="many-price" >
                                    <a class="pull-right" target="_blank" style="border: none" href="{{route('admin.progressive.create') }}"><i class="fa fa-plus" aria-hidden="true"></i>
                                        Thêm bảng giá lũy tiến</a>
                                </div>
                                <div class="form-group div_unit">
                                    <label for="exampleInputEmail1">Đơn vị tính:</label>
                                    <input type="text" name="unit" class="form-control" disabled="" value="VND">
                                    <div class="message_zone"></div>
                                </div>
                                <div class="form-group div_type">
                                    <label>Loại dịch vụ: </label>
                                    <select class="form-control" id="change_type_service" name="type">
                                        <option value="" selected>-- Chọn loại dịch vụ --</option>
                                        @foreach($typeService as $value)
                                          <option value="{{ $value->category }}"  @if(old('type') == $value->category) selected @endif>{{ App\Commons\Helper::loai_phi_dich_vu[$value->category] }}</option>
                                        @endforeach                                    
                                    </select>
                                    <div class="message_zone"></div>
                                </div>
                                <div class="form-group div_type">
                                    <label>Loại hình dịch vụ: </label>
                                    <select class="form-control" id="service_type" name="service_type">
                                        <option value="" selected>-- Chọn loại hình dịch vụ --</option>
                                        @foreach($service_types as $key => $value)
                                          <option value="{{ $key }}">{{ $value }}</option>
                                        @endforeach                                    
                                    </select>
                                    <div class="message_zone"></div>
                                </div>
                                <div class="form-group div_code_receipt">
                                    <label>Mã thu: </label>
                                    <input type="text" name="code_receipt" class="form-control code_receipt" placeholder="Mã thu" value="{{ old('code_receipt')}}">
                                    <div class="message_zone"></div>
                                </div>
                                <div class="form-group div_ngay_chuyen_doi">
                                    <label>Ngày chuyển đổi: </label>
                                    <input type="text" name="ngay_chuyen_doi" class="form-control" placeholder="Ngày chuyển đổi" value="{{ old('ngay_chuyen_doi')}}">
                                    <div class="message_zone"></div>
                                </div>
                                <div class="form-group div_service_group">
                                    <label>Đối tượng: </label>
                                    <select class="form-control" name="service_group" id="service_group">
                                        <option value="" selected>-- Chọn đối tượng --</option>
                                        <option value="1" >Công ty</option>
                                        <option value="2">Thu hộ</option>
                                        <option value="3">Chủ đầu tư</option>
                                        <option value="4">Ban quản trị</option>
                                    </select>
                                    <div class="message_zone"></div>
                                </div>
                                <div hidden class="form-group partner_service">
                                    <div class="form-group">
                                        <label>Đối tác</label>
                                        <select  class="form-control select2" name="partner_id" style="width:100%">
                                            @foreach($partners as $value)
                                                <option value="{{ $value->id }}" @if(old('partner_id') == $value->id) selected @endif>{{ $value->name }}</option>
                                            @endforeach
                                        </select>
                                        <div class="message_zone"></div>
                                    </div>
                                    <div class="form-group">
                                        <div class="radio">
                                            <label><input type="radio" name="price_free" value="0">Miễn phí</label>
                                            <label style="margin-left: 10px"><input type="radio" name="price_free" value="1" checked>Tính phí</label>
                                        </div>
                                        <div class="message_zone"></div>
                                    </div>
                                    <div class="form-group">
                                        <label>Yêu cầu xác nhận QR: </label>
                                        <div class="radio">
                                            <label><input type="radio" name="check_confirm" id="check_one" value="1" checked> Xác nhận 1 lần </label>
                                            <label style="margin-left: 10px"><input type="radio" name="check_confirm" id="check_two" value="2"> Xác nhận 2 lần </label>
                                        </div>
                                        <div class="message_zone"></div>
                                    </div>
                                    <div class="form-group">
                                        <label>Thành viên được đăng ký dịch vụ</label>
                                        <div class="radio">
                                            <label><input type="radio" name="persion_register" value="1" checked>Chủ nhà</label>
                                            <label style="margin-left: 10px"><input type="radio" name="persion_register" value="2">Tất cả thành viên</label>
                                        </div>
                                        <div class="message_zone"></div>
                                    </div>
                                </div>
                                <div class="form-group div_description">
                                    <label for="exampleInputEmail1">Mô tả:</label>
                                    <i style="color: red; font-size: 12px;">(không được để trống)</i>
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
                                        <option value="6">1 năm</option>
                                    </select>
                                    <div class="message_zone"></div>
                                </div>
                                <div class="cycle">
                                    <div class="form-group div_bill_date">
                                        <label for="exampleInputEmail1">Ngày đầu kỳ số liệu:</label>
                                        <input type="number" name="bill_date" class="form-control"
                                               value="{{ old('bill_date') ?? 1 }}">
                                        <div class="message_zone"></div>
                                    </div>
                                    <div class="form-group div_payment_deadline">
                                        <label for="exampleInputEmail1">Ngày thanh toán:</label>
                                        <input type="number" name="payment_deadline" class="form-control"
                                               value="{{ old('payment_deadline') ?? 5 }}">
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
                            </div>
                            <!-- /.box-body -->
                            <div class="box-footer">
                                <a href="{{ route('admin.v2.service.building.index') }}" type="button"
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
        submitAjaxForm('#save_service', '#add_service', '.div_', '.message_zone');
    </script>
    <script>
        $(document).ready(function () {
            const ONEPRICE = 1;  // loại 1 giá
            const MANYPRICE = 2;  // loại lũy tiến và tiện ích dịch vụ
            const TIEN_ICH = 4;  // loại tiện ích dịch vụ
            $('.price-list').on('change', function () {
                var value = $(this).val();
                if (value == ONEPRICE) {
                    $('.one-price').removeAttr("hidden");
                    $('.many-price').attr("hidden", true);
                }
                if (value == MANYPRICE || value == TIEN_ICH) {
                $('.many-price').removeAttr("hidden");
                    $('.one-price').attr("hidden", true);
                }
            });
        });
        
        $(document).ready(function () {
            if($('select[name="bdc_period_id"]').val() == '1'){
                $('.cycle_month').show();
                $('.cycle_year').hide();

            }else{
                $('.cycle_month').hide();
                $('.cycle_year').show();
            }
            $('select[name="bdc_period_id"]').change(function () { 
                if($('select[name="bdc_period_id"]').val() == '1'){
                    $('.cycle_month').show();
                    $('.cycle_year').hide();
                }else{
                    $('.cycle_month').hide();
                    $('.cycle_year').show();
                }
            });
        });

         // change type service
         $('#change_type_service').on('change', function(e) {
            showLoading();
            $.ajax({
                        url:  "{{route('admin.v2.service.building.ajaxSelectTypeService')}}",
                        method: 'GET',
                        dataType: 'json',
                        data: {
                            category:$('#change_type_service').val(),
                        },
                        success: function(response) {
                            hideLoading();
                            if (response.success == true) {
                                $('.code_receipt').val(response.message);
                            } else {
                                toastr.error(response.message);
                            }
                        },
                        error: function(response) {
                            hideLoading();
                        }
                    })
        });

     $('#service_group').change(function (e) { 
        e.preventDefault();
        let service_group = $(this).val();
        if(service_group == 2){ // nếu như là dịch vụ thu hộ cho đối tác
            $('.partner_service').show();
        }else{
            $('.partner_service').hide();
        }
     });
    </script>
@endsection