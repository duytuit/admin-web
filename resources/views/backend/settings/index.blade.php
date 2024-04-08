@extends('backend.layouts.master')

@section('content')
<section class="content-header">
    <h1>
        Cấu hình
        <small>Danh sách</small>
    </h1>
    <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Trang chủ</a></li>
        <li class="active">Cấu hình</li>
    </ol>
</section>

<section class="content" id="config-settings">
    <div class="box box-info">
        <div class="box-header with-border">
            <form action="" method="get" id="form-search">
                {{ csrf_field() }}
                @method('get')
                <div class="row">
                    <div class="col-md-8 col-sm-8 col-xs-12 ">
                        @can('update', app(App\Models\Setting::class))
                        <a href="{{ url('admin/settings/edit/0') }}" type="buttom" class="btn btn-info"><i class="fa fa-plus"></i>&nbsp;&nbsp;Thêm mới</a>
                        @endcan
                    </div>

                    {{-- <div class="col-md-4 col-sm-4 col-xs-12">
                        <div class="input-group">
                            <input type="text" class="form-control" name="config_key" placeholder="Nhập tên setting" value="{{ !empty($data_search['config_key']) ? $data_search['config_key'] : '' }}">
                    <div class="input-group-btn">
                        <button type="submit" title="Tìm kiếm" class="btn btn-info"><i class="fa fa-search"></i></button>
                    </div>
                </div>
        </div> --}}
    </div>
    <div class="clearfix" style="height: 15px;"></div>
    </form>
    </div>
    <!-- /.box-header -->
    <div class="box-body">
        <form action="" method="post" id="form-edit-settings" autocomplete="off">
            {{ csrf_field() }}
            <div class="panel">
                @foreach($settings as $setting)
                <input type="hidden" name="data[{{ $setting->id }}][config_key]" value="{{ $setting->config_key }}" />

                <div class="panel-heading">
                    <h3 class="control-label" style="margin: 0px;">
                        {{ $setting->config_key }}
                        @can('edit', app(App\Models\Setting::class))
                        <button type="button" class="btn btn-sm btn-social-icon btn-dropbox btn-add-option" data-id="{{ $setting->id }}" data-index="{{ count($setting->config_value) }}"><i class="fa fa-plus"></i></button>
                        @endcan
                    </h3>
                </div>

                <div class="panel-body">
                    <div class="row">

                        <div class="col-sm-3">
                            <label class="control-label">Config key</label>
                        </div>
                        <div class="col-sm-7">
                            <label class="control-label">Config value</label>
                        </div>
                    </div>
                    <div class="config-setting data-setting-{{$setting->id}}">
                        @foreach($setting->config_value as $cf_key => $cf_value)
                        <div class="row" style="margin-top: 15px;">
                            <div class="col-sm-3">
                                <input type="text" name="data[{{ $setting->id }}][config_value][{{ $loop->index }}][cf_key]" value="{{ $cf_key }}" class="form-control" placeholder="Key 1">
                            </div>
                            <div class="col-sm-7">
                                <input type="text" name="data[{{ $setting->id }}][config_value][{{ $loop->index }}][cf_value]" value="{{ $cf_value }}" class="form-control" placeholder="Value 1">
                            </div>
                            <span class="input-group-btn">
                                <button class="btn btn-danger btn-remove" type="button"><i class="fa fa-trash"></i></button>
                            </span>
                        </div>
                        @endforeach
                    </div>
                </div>

                @if(!$loop->last)
                <hr>
                @endif
                @endforeach
                @if(!empty($settings->toArray()))
                <div class="panel-footer">
                    @can('edit', app(App\Models\Setting::class))
                    <button type="button" class="btn btn-success pull-left" data-toggle="modal" data-target="#submit-setting">Save settings</button>
                    @endcan
                </div>
                @endif
            </div>
        </form>
    </div>
    </div>
</section>

<!-- Modal -->
<div id="submit-setting" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <div class="box box-warning box-solid">
            <div class="box-header with-border">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Cảnh báo</h4>
                <!-- /.box-tools -->
            </div>
            <!-- /.box-header -->
            <div class="box-body">
                <p><strong>Mọi sự thay đổi đều có thể ảnh hưởng tới hệ thống.</strong></p>
                <p><strong>Bạn vẫn muốn tiếp tục?</strong></p>
            </div>
            <!-- /.box-body -->
            <div class="box-footer">
                <button type="button" class="btn btn-danger pull-right" data-dismiss="modal">Hủy</button>
                <button class="btn btn-primary pull-right btn-submit-setting" style="margin-right: 5px;">Đồng ý</button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('javascript')

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

    // var index = 0;
    $('.btn-add-option').click(function() {

        var index = $(this).data('index');
        id = $(this).data('id');
        index = parseInt(index) + 1;

        var html = '<div class="row" style="margin-top: 15px;">' +
            '<div class="col-sm-3">' +
            '<input type="text" name="data[' + id + '][config_value][' + index + '][cf_key]" class="form-control" placeholder="Key ' + index + '">' +
            '</div>' +
            '<div class="col-sm-7">' +
            '<input type="text" name="data[' + id + '][config_value][' + index + '][cf_value]" class="form-control" placeholder="Value ' + index + '">' +
            '</div>' +
            '<span class="input-group-btn">' +
            '<button class="btn btn-danger btn-remove" type="button"><i class="fa fa-trash"></i></button>' +
            '</span>' +
            '</div>';

        $(".data-setting-" + id).append(html);
        $(this).data('index', index);
    });

    // var index = 0;
    $('.btn-submit-setting').click(function() {
        $("#form-edit-settings").submit();
    });
});
</script>
@endsection