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
            <form action="{{route('admin.service.building.update',$service->id)}}" method="POST" id="add_service">
                @method('PUT')
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
                                <div class="form-group {{ $errors->has('name') ? ' has-error' : '' }}">
                                    <label for="exampleInputEmail1">Tên dịch vụ:</label>
                                    <input type="text" name="name" class="form-control" value="{{ @$service->name }}">
                                    @if ($errors->has('name'))
                                        <span class="help-block">
                                        <strong>{{ $errors->first('name') }}</strong>
                                    </span>
                                    @endif
                                </div>
                                <div class="form-group {{ $errors->has('bdc_price_type_id') ? ' has-error' : '' }}">
                                    <label>Bảng giá:</label>
                                    <select class="form-control price-list" name="bdc_price_type_id">
                                            <option value="{{ @$service->servicePriceDefault->priceType->id }}"
                                                     selected>{{ @$service->servicePriceDefault->priceType->name }}</option>
                                    </select>
                                    @if ($errors->has('bdc_price_type_id'))
                                        <span class="help-block">
                                        <strong>{{ $errors->first('bdc_price_type_id') }}</strong>
                                    </span>
                                    @endif
                                </div>
                                <div
                                        @if(@$service->servicePriceDefault->priceType->id == \App\Repositories\Service\MANY_PRICE)
                                        hidden
                                        @endif
                                        class="form-group one-price {{ $errors->has('price') ? ' has-error' : '' }}">
                                    <label for="exampleInputEmail1">Đơn giá:</label>
                                    <input type="text" name="price" maxlength="15" class="form-control"
                                           value="{{ number_format(@$service->servicePriceDefault->price) }}">
                                    @if ($errors->has('price'))
                                        <span class="help-block">
                                        <strong>{{ $errors->first('price') }}</strong>
                                    </span>
                                    @endif
                                </div>
                                <div
                                        @if(@$service->servicePriceDefault->priceType->id == \App\Repositories\Service\ONE_PRICE)
                                        hidden
                                        @endif
                                        class="form-group many-price {{ $errors->has('progressive_id') ? ' has-error' : '' }}">
                                    <label>Lũy tiến</label>
                                    <select class="form-control col-xs-8" name="progressive_id">
                                        <option value="" selected>-- Chọn lũy tiến --</option>
                                        @foreach($progressives as $progressive)
                                            <option value="{{$progressive->id}}"
                                                    @if(@$service->servicePriceDefault->progressive->id == $progressive->id) selected @endif>{{ $progressive->name }}</option>
                                        @endforeach
                                    </select>
                                    @if ($errors->has('progressive_id'))
                                        <span class="help-block">
                                        <strong>{{ $errors->first('progressive_id') }}</strong>
                                    </span>
                                    @endif
                                </div>
                                <div        @if(@$service->servicePriceDefault->priceType->id == \App\Repositories\Service\ONE_PRICE)
                                            hidden
                                            @endif class="many-price">
                                    <a class="pull-right" target="_blank" style="border: none"
                                       href="{{route('admin.progressive.create') }}"><i class="fa fa-plus"
                                                                                        aria-hidden="true"></i>
                                        Thêm bảng giá lũy tiến</a>
                                </div>
                                <div class="form-group {{ $errors->has('unit') ? ' has-error' : '' }}">
                                    <label for="exampleInputEmail1">Đơn vị tính:</label>
                                    <input type="text" name="unit" class="form-control" disabled="" value="VND">
                                    @if ($errors->has('unit'))
                                        <span class="help-block">
                                        <strong>{{ $errors->first('unit') }}</strong>
                                    </span>
                                    @endif
                                </div>
                                <div class="form-group">
                                    <label>Loại dịch vụ: </label>
                                    <select class="form-control" id="change_type_service" name="type">
                                        @foreach($typeService as $value)
                                          <option value="{{ $value->category }}" @if($service->type == $value->category) selected @endif>{{ App\Commons\Helper::loai_phi_dich_vu[$value->category] }}</option>
                                        @endforeach                                    
                                    </select>
                                </div>
                                <div class="form-group {{ $errors->has('code_receipt') ? ' has-error' : '' }}">
                                    <label>Mã thu: </label>
                                    <input type="text" name="code_receipt" class="form-control code_receipt" placeholder="Mã thu" value="{{$service->code_receipt ?? '' }}">
                                    @if ($errors->has('code_receipt'))
                                        <span class="help-block">
                                        <strong>{{ $errors->first('code_receipt') }}</strong>
                                    </span>
                                    @endif
                                </div>
                                <div class="form-group {{ $errors->has('ngay_chuyen_doi') ? ' has-error' : '' }}">
                                    <label>Ngày chuyển đổi: </label>
                                    <input type="text" name="ngay_chuyen_doi" class="form-control" placeholder="Ngày chuyển đổi" value="{{$service->ngay_chuyen_doi ?? '' }}">
                                    @if ($errors->has('ngay_chuyen_doi'))
                                        <span class="help-block">
                                        <strong>{{ $errors->first('ngay_chuyen_doi') }}</strong>
                                    </span>
                                    @endif
                                </div>
                                <div class="form-group">
                                    <label>Nhóm dịch vụ: </label>
                                    <select class="form-control" name="service_group">
                                        <option value="1" @if($service->service_group == 1) selected @endif>Phí công ty</option>
                                        <option value="2" @if($service->service_group == 2) selected @endif>Phí thu hộ</option>
                                        <option value="3" @if($service->service_group == 3) selected @endif>Phí chủ đầu tư</option>
                                        <option value="4" @if($service->service_group == 4) selected @endif>Ban quản trị</option>
                                    </select>
                                </div>
                                <div class="form-group {{ $errors->has('description') ? ' has-error' : '' }}">
                                    <label for="exampleInputEmail1">Mô tả:</label>
                                    <input type="text" name="description" class="form-control"
                                           value="{{ $service->description }}">
                                    @if ($errors->has('description'))
                                        <span class="help-block">
                                        <strong>{{ $errors->first('description') }}</strong>
                                    </span>
                                    @endif
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
                                <div class="form-group {{ $errors->has('bdc_period_id') ? ' has-error' : '' }}">
                                    <label>Chu kỳ:</label>
                                    <select class="form-control" name="bdc_period_id">
                                        <option value="1" @if($service->bdc_period_id == 1) selected @endif>1 tháng</option>
                                        <option value="6" @if($service->bdc_period_id == 6) selected @endif>1 năm</option>
                                    </select>
                                    @if ($errors->has('bdc_period_id'))
                                        <span class="help-block">
                                        <strong>{{ $errors->first('bdc_period_id') }}</strong>
                                    </span>
                                    @endif
                                </div>
                                <div class="cycle">
                                    <div class="form-group {{ $errors->has('bill_date') ? ' has-error' : '' }}">
                                        <label for="exampleInputEmail1">Ngày đầu kỳ số liệu:</label>
                                        <input type="number" name="bill_date" class="form-control"
                                               value="{{ $service->bill_date }}">
                                        @if ($errors->has('bill_date'))
                                            <span class="help-block">
                                            <strong>{{ $errors->first('bill_date') }}</strong>
                                        </span>
                                        @endif
                                    </div>
                                    <div class="form-group {{ $errors->has('payment_deadline') ? ' has-error' : '' }}">
                                        <label for="exampleInputEmail1">Ngày thanh toán:</label>
                                        <input type="number" name="payment_deadline" class="form-control"
                                               value="{{ $service->payment_deadline }}">
                                        @if ($errors->has('payment_deadline'))
                                            <span class="help-block">
                                            <strong>{{ $errors->first('payment_deadline') }}</strong>
                                        </span>
                                        @endif
                                    </div>
                                    <div class="form-group {{ $errors->has('first_time_active') ? ' has-error' : '' }}">
                                        <label>Áp dụng từ:</label>
                                        <div class="input-group date">
                                            <div class="input-group-addon">
                                                <i class="fa fa-calendar"></i>
                                            </div>
                                            <input type="text" class="form-control pull-right date_picker"
                                                   name="first_time_active"
                                                   id="datepicker" value="{{ $service->first_time_active ? date('d-m-Y',strtotime($service->first_time_active)) :'' }}">
                                        </div>
                                        @if ($errors->has('first_time_active'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('first_time_active') }}</strong>
                                            </span>
                                        @endif
                                    <!-- /.input group -->
                                    </div>
                                </div>
                                {{-- <div class="cycle_year">
                                    <div class="form-group {{ $errors->has('first_time_active') ? ' has-error' : '' }}">
                                        <label>Áp dụng từ:</label>
                                        <div class="input-group date">
                                            <div class="input-group-addon">
                                                <i class="fa fa-calendar"></i>
                                            </div>
                                            <input type="text" class="form-control pull-right date_picker"
                                                   name="first_time_active" value="{{ $service->first_time_active }}">
                                        </div>
                                        @if ($errors->has('first_time_active'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('first_time_active') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                </div> --}}
                            </div>
                            <!-- /.box-body -->
                            <div class="box-footer">
                                <a href="{{ route('admin.service.building.index') }}" type="button"
                                   class="btn btn-default pull-left">Quay lại</a>
                                <button type="submit" class="btn btn-success pull-right">Lưu</button>
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
        ;

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
                        url:  "{{route('admin.service.building.ajaxSelectTypeService')}}",
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
    </script>
@endsection