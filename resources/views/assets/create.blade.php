@extends('backend.layouts.master')

@section('content')
    <section class="content-header">
        <h1>
            Quản lý tài sản
            <small>Thêm tài sản</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('admin.home') }}"><i class="fa fa-home"></i> Trang chủ</a></li>
            <li class="active">Quản lý tài sản</li>
        </ol>
    </section>

    <section class="content">
        <div class="box-body">
            <form action="{{ route('admin.assets.store') }}" method="POST">
                {{ csrf_field() }}
                <div class="row">
                    <!-- left column -->
                    <div class="col-md-6">
                        <!-- general form elements -->
                        <div class="box box-primary">
                            <div class="box-header with-border">
                                <h3 class="box-title">Thông tin tài sản-CCDC</h3>
                            </div>
                            <!-- /.box-header -->
                            <!-- form start -->
                            <div class="box-body">
                                <div class="form-group {{ $errors->has('name') ? ' has-error' : '' }}">
                                    <label for="exampleInputEmail1">Tên tài sản:</label>
                                    <input type="text" name="name" class="form-control" value="{{ old('name') }}">
                                    @if ($errors->has('name'))
                                        <span class="help-block">
                                        <strong>{{ $errors->first('name') }}</strong>
                                    </span>
                                    @endif
                                </div>
                                <div class="form-group {{ $errors->has('bdc_assets_type_id') ? ' has-error' : '' }}">
                                    <label>Loại tài sản:</label>
                                    <select class="form-control" name="bdc_assets_type_id">
                                        <option value="" selected>Chọn</option>
                                        @foreach($types as $key => $type)
                                            <option value="{{ $type->id }}" @if(old('bdc_assets_type_id') == $type->id) selected @endif>{{ $type->name }}</option>
                                        @endforeach
                                    </select>
                                    @if ($errors->has('bdc_assets_type_id'))
                                        <span class="help-block">
                                        <strong>{{ $errors->first('bdc_assets_type_id') }}</strong>
                                    </span>
                                    @endif
                                </div>
                                <div class="form-group {{ $errors->has('quantity') ? ' has-error' : '' }}">
                                    <label for="exampleInputEmail1">Số lượng:</label>
                                    <input type="number" name="quantity" min="1" class="form-control" value="{{ old('quantity') }}">
                                    @if ($errors->has('quantity'))
                                        <span class="help-block">
                                        <strong>{{ $errors->first('quantity') }}</strong>
                                    </span>
                                    @endif
                                </div>
                                <div class="form-group">
                                    <label>Hạn sử dụng:</label>
                                    <div class="row">
                                        <div class="col-xs-8 {{ $errors->has('using_peroid') ? ' has-error' : '' }}">
                                            <input type="number" class="form-control"  min="1" name="using_peroid" value="{{ old('using_peroid') }}">
                                            @if ($errors->has('using_peroid'))
                                                <span class="help-block">
                                                <strong>{{ $errors->first('using_peroid') }}</strong>
                                            </span>
                                            @endif
                                        </div>
                                        <div class="col-xs-4">
                                            Tháng
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group {{ $errors->has('warranty_period') ? ' has-error' : '' }}">
                                    <label>Hạn bảo hành:</label>

                                    <div class="input-group date">
                                        <div class="input-group-addon">
                                            <i class="fa fa-calendar"></i>
                                        </div>
                                        <input type="text" class="form-control pull-right date_picker" name="warranty_period" value="{{ old('warranty_period') }}">
                                    </div>
                                    @if ($errors->has('warranty_period'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('warranty_period') }}</strong>
                                        </span>
                                @endif
                                <!-- /.input group -->
                                </div>
                            </div>
                        </div>
                        <!-- /.box -->
                    </div>
                    <!--/.col (left) -->
                    <!-- right column -->
                    <div class="col-md-6">
                        <!-- Horizontal Form -->
                        <div class="box box-primary">
                            <div class="box-header with-border">
                                <h3 class="box-title">Thông tin tài sản-CCDC</h3>
                            </div>
                            <!-- /.box-header -->
                            <!-- form start -->
                            <div class="box-body">
                                <div class="form-group">
                                    <label>Giá mua:</label>
                                    <div class="row">
                                        <div class="col-xs-8 {{ $errors->has('price') ? ' has-error' : '' }}">
                                            <input type="text" class="form-control" name="price" maxlength="15" value="{{ old('price') }}">
                                            @if ($errors->has('price'))
                                                <span class="help-block">
                                                <strong>{{ $errors->first('price') }}</strong>
                                            </span>
                                            @endif
                                        </div>
                                        <div class="col-xs-4">
                                            VND
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group {{ $errors->has('buying_date') ? ' has-error' : '' }}">
                                    <label>Ngày mua:</label>

                                    <div class="input-group date">
                                        <div class="input-group-addon">
                                            <i class="fa fa-calendar"></i>
                                        </div>
                                        <input type="text" class="form-control pull-right date_picker" name="buying_date"
                                               id="datepicker" value="{{ old('buying_date') }}">
                                    </div>
                                    @if ($errors->has('buying_date'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('buying_date') }}</strong>
                                        </span>
                                @endif
                                <!-- /.input group -->
                                </div>
                                <div class="form-group {{ $errors->has('buyer') ? ' has-error' : '' }}">
                                    <label for="exampleInputEmail1">Người mua:</label>
                                    <input type="text" name="buyer" class="form-control" value="{{ old('buyer') }}">
                                    @if ($errors->has('buyer'))
                                        <span class="help-block">
                                        <strong>{{ $errors->first('buyer') }}</strong>
                                    </span>
                                    @endif
                                </div>
                                <div class="form-group {{ $errors->has('place') ? ' has-error' : '' }}">
                                    <label for="exampleInputEmail1">Nơi đặt:</label>
                                    <input type="text" name="place" class="form-control" value="{{ old('place') }}">
                                    @if ($errors->has('place'))
                                        <span class="help-block">
                                        <strong>{{ $errors->first('place') }}</strong>
                                    </span>
                                    @endif
                                </div>
                                <div class="form-group {{ $errors->has('bdc_period_id') ? ' has-error' : '' }}">
                                    <label>Kì bảo trì</label>
                                    <select class="form-control" name="bdc_period_id">
                                        <option value="" selected>Chọn kì bảo trì</option>
                                        @foreach($periods as $key => $type)
                                            <option value="{{ $type->id }}" @if(old('bdc_period_id') == $type->id) selected @endif>{{ $type->name }}</option>
                                        @endforeach
                                    </select>
                                    @if ($errors->has('bdc_period_id'))
                                        <span class="help-block">
                                        <strong>{{ $errors->first('bdc_period_id') }}</strong>
                                    </span>
                                    @endif
                                </div>
                                <div class="form-group {{ $errors->has('maintainance_date') ? ' has-error' : '' }}">
                                    <label>Ngày bảo trì:</label>

                                    <div class="input-group date">
                                        <div class="input-group-addon">
                                            <i class="fa fa-calendar"></i>
                                        </div>
                                        <input type="text" class="form-control pull-right date_picker" name="maintainance_date" value="{{ old('maintainance_date') }}">
                                    </div>
                                    @if ($errors->has('maintenance_date'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('maintainance_date') }}</strong>
                                        </span>
                                @endif
                                <!-- /.input group -->
                                </div>
                            </div>
                            <!-- /.box-body -->
                        </div>
                        <!-- /.box -->
                    </div>
                    <div class="col-md-12">
                        <div class="form-group {{ $errors->has('asset_note') ? ' has-error' : '' }}">
                            <label>Ghi chú</label>
                            <textarea id="content" class="mceEditor form-control" rows="10" name="asset_note">{{ old('asset_note') }}</textarea>
                            @if ($errors->has('asset_note'))
                                <span class="help-block">
                                            <strong>{{ $errors->first('asset_note') }}</strong>
                                    </span>
                            @endif
                        </div>
                        <div class="box-footer">
                            <a href="{{ route('admin.assets.index') }}" type="button" class="btn btn-default pull-left">Quay lại</a>
                            <button type="submit" class="btn btn-success pull-right">Lưu</button>
                        </div>
                    </div>
                    <!--/.col (right) -->
                </div>
            </form>
        </div>
    </section>
@endsection

@section('javascript')
    <!-- TinyMCE -->
    <!-- <script src="/adminLTE/plugins/tinymce/tinymce.min.js"></script>
    <script src="/adminLTE/plugins/tinymce/config.js"></script> -->
    <script>
        //Date picker
        var request = false
        $('input.date_picker').datepicker({
            autoclose: true,
            dateFormat: "dd-mm-yy"
        }).val();

        $(document).on('click', 'input[name="using_peroid"]', function () {
            if (!request) {
                request = true
                toastr.info('Khoảng thời gian DN ước tính của từ ngày mua đến khi thanh lý Tài sản hoăc\n' +
                    '                        Tài sản không sử dụng được nữa(tính bằng tháng)');
            }
        })

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