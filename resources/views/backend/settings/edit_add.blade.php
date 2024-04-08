@extends('backend.layouts.master')

@section('content')
<section class="content-header">
    <h1>
        Cấu hình
        <small>Thêm mới</small>
    </h1>
    <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Trang chủ</a></li>
        <li class="active"><a href="{{ url('/admin/settings') }}">Cấu hình</a></li>
    </ol>
</section>
@can('update', app(App\Models\Setting::class))
<section class="content">

    <form action="" method="post" id="form-add-setting" class="form-validate">
        {{ csrf_field() }}
        <div class="row">
            <div class="col-lg-8 col-md-8 col-sm-8 col-xs-8">
                <div class="box box-info">
                    <div class="box-body">
                        <div class="form-group {{ $errors->has('config_key') ? 'has-error': '' }}">
                            <label class="control-label">Config key <span class="text-danger">*</span></label>
                            <input type="text" name="config_key" class="form-control" placeholder="VD: config-key" value="" />
                            @if ($errors->has('config_key'))
                            <em class="help-block">{{ $errors->first('config_key') }}</em>
                            @endif
                        </div>
                        <div class="form-group">
                            <label class="control-label">
                                Config value &nbsp;&nbsp;&nbsp;
                                <button type="button" class="btn btn-sm btn-primary btn-social-icon btn-dropbox btn-add-option"><i class="fa fa-plus"></i></button>
                            </label>
                            <div class="config-setting">
                                <div class="row" style="margin-top: 15px;">
                                    <div class="col-sm-3">
                                        <input type="text" name="config_value[0][cf_key]" value="" class="form-control" placeholder="Key 1">
                                    </div>
                                    <div class="col-sm-7">
                                        <input type="text" name="config_value[0][cf_value]" value="" class="form-control" placeholder="Value 1">
                                    </div>
                                    <span class="input-group-btn">
                                        <button class="btn btn-danger btn-remove" type="button"><i class="fa fa-trash"></i></button>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="box-footer">
                        <button type="submit" class="btn btn-sm btn-success" title="Cập nhật" form="form-add-setting"><i class="fa fa-save"></i>&nbsp;&nbsp;Thêm mới</button>
                    </div>
                </div>
            </div>
            <div class="col-xs-4">

            </div>
        </div>
    </form>

</section>
@endcan
@endsection

@section('javascript')

<!-- Datetime Picker -->
<script src="/adminLTE/plugins/moment/moment.min.js"></script>
<script src="/adminLTE/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js"></script>

<!-- TinyMCE -->
<script src="/adminLTE/plugins/tinymce/tinymce.min.js"></script>
<script src="/adminLTE/plugins/tinymce/config.js"></script>

<script>
    $(function() {
    
    var $poll_options = $('.config-setting');

    $poll_options.sortable({
        handle: '.btn-handle'
    });

    $poll_options.on('click', '.btn-remove', function() {
        if ($('>div', $poll_options).length > 1) {
            $(this).parent().parent().remove();
        }
    });
    var index = 0;

    $('.btn-add-option').click(function() {

        index = parseInt(index) + 1;

        var html = '<div class="row" style="margin-top: 15px;">' +
            '<div class="col-sm-3">' +
            '<input type="text" name="config_value[' + index + '][cf_key]" class="form-control" placeholder="Key ' + index  + '">' +
            '</div>' +
            '<div class="col-sm-7">' +
            '<input type="text" name="config_value[' + index + '][cf_value]" class="form-control" placeholder="Value ' + index + '">' +
            '</div>' +
            '<span class="input-group-btn">' +
            '<button class="btn btn-danger btn-remove" type="button"><i class="fa fa-trash"></i></button>' +
            '</span>' +
            '</div>';

        $(".config-setting").append(html);
    });
});

sidebar('setting');
</script>


@endsection