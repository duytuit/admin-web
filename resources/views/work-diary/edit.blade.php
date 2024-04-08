@extends('backend.layouts.master')

@section('content')
<section class="content-header">
    <h1>
        Quản lý tòa nhà
        <small>Danh sách công việc</small>
    </h1>
    <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Trang chủ</a></li>
        <li class="active">Danh sách công việc</li>
    </ol>
</section>

<section class="content" id="content-partner">
    @if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif
    <form action="{{ route('admin.work-diary.update', ['id' => $task->id]) }}" method="POST"
        enctype="multipart/form-data">
        {{ csrf_field() }}
        <div class="row">
            <div class="col-lg-8">
                <div class="box box-primary">
                    <div class="box-body">
                        <div class="form-group">
                            <div class="form-group">
                                <label for="recipient-name" class="col-form-label">Tiêu đề:</label>
                                <input type="text" name="title" class="form-control" id="recipient-name"
                                    value="{{ @$task->title }}">
                            </div>
                            <div class="form-group">
                                <label class="control-label">Mô tả</label>
                                <!-- <textarea id="description" name="description" rows="5" -->
                                <textarea id="content" name="description" rows="5"
                                    class="mceEditor form-control">{{ @$task->description }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="box box-primary">
                    <div class="box-body">
                        <div class="row">
                            <div class="form-group">
                                <label class="col-md-4 control-label">Liên quan tới</label>
                                <div class="col-md-8">
                                    <select name="" class="form-control" id="select">
                                        <option value="0" selected>Chọn</option>
                                        <option value="1" @isset($task->bdc_maintenance_asset_id)
                                            selected
                                            @endisset>Lịch bảo trì</option>
                                        <option value="2" @isset($task->bdc_request_id)
                                            selected
                                            @endisset>Phản hồi cư dân</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <br>

                        <div class="row" id="maintenance_schedule" @if($task->bdc_maintenance_asset_id == null)
                            hidden
                            @endif>
                            <div class="form-group">
                                <label class="col-md-4 control-label">Lịch bảo trì</label>
                                <div class="col-md-8">
                                    <select name="bdc_maintenance_asset_id" class="form-control">
                                        <option value="" selected>Chọn lịch bảo trì</option>
                                        @foreach($maintenance_assets as $maintenance_asset)
                                        <option value="{{ $maintenance_asset->id }}" @if ($maintenance_asset->id ==
                                            $task->bdc_maintenance_asset_id)
                                            selected
                                            @endif
                                            >
                                            {{ $maintenance_asset->title }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row" id="request_resident" @if($task->bdc_request_id == null)
                            hidden
                            @endif>
                            <div class="form-group">
                                <label class="col-md-4 control-label" style="padding-right: 0">Phản hồi cư dân</label>
                                <div class="col-md-8">
                                    <select name="bdc_request_id" class="form-control">
                                        <option value="" selected>Chọn phản hồi cư dân</option>
                                        @foreach($feedbacks as $feedback)
                                        <option value="{{ $feedback->id }}" @if ($feedback->id == $task->bdc_request_id)
                                            selected
                                            @endif
                                            >
                                            {{ $feedback->title }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="control-label">Bộ phận tiếp nhận</label>
                            <select name="bdc_department_id" class="form-control" id="bdc_department_id">
                                <option value="0" @if( $task->bdc_department_id == 0 ) selected @endif>Chọn bộ phận
                                </option>
                                @foreach($departments as $department)
                                <option value="{{ $department->id }}" @if( $task->bdc_department_id ==
                                    $department->id )
                                    selected @endif>
                                    {{ $department->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="control-label">Người xử lý</label>
                            <select name="assign_to" class="form-control" id="assign_to">
                                @isset($userprofiles)
                                @foreach($userprofiles as $userprofile)
                                <option value="{{ $userprofile->id }}" @if( $task->assign_to ==
                                    $userprofile->id )
                                    selected @endif>
                                    {{ $userprofile->display_name }}</option>
                                @endforeach
                                @endisset
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="control-label">Ngày bắt đầu</label>
                            <div class="input-group date">
                                <div class="input-group-addon">
                                    <i class="fa fa-calendar"></i>
                                </div>
                                <input type="text" name="start_at" class="form-control pull-right date_picker" readonly
                                    value="{{ $task->start_at }}">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label">Ngày báo cáo</label>
                            <div class="input-group date">
                                <div class="input-group-addon">
                                    <i class="fa fa-calendar"></i>
                                </div>
                                <input type="text" name="end_at" class="form-control pull-right date_picker" readonly
                                    value="{{ $task->end_at }}">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label">Chọn file đính kèm</label>
                            <div class="input-group input-image" data-file="image">
                                <input type="file" name="file_work_diarys[]" value="" class="form-control"
                                    multiple><span class="input-group-btn"></span>
                            </div>
                        </div>
                        <div class="modal-footer d-flex justify-content-center">
                            <button type="submit" class="btn btn-primary" @if ($task->status == 5)
                                disabled
                                @endif>Cập nhật</button>
                            <a type="button" class="btn btn-danger" href="{{ route('admin.work-diary.index') }}">Quay
                                lại</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</section>
@endsection

@section('stylesheet')

<link rel="stylesheet" href="/adminLTE/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css" />

@endsection

@section('javascript')
<script src="/adminLTE/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js"></script>
<!-- TinyMCE -->
<!-- <script src="/adminLTE/plugins/tinymce/tinymce.min.js"></script>
<script src="/adminLTE/plugins/tinymce/config.js"></script> -->
<script>
    //Date picker
    $('input.date_picker').datepicker({
            autoclose: true,
            dateFormat: "yy/mm/dd"
        }).val();
    
    // select maintenance_schedule or request_resident
    $(document).ready(function() {

        $('#select').change(function() {
            var id = $(this).val();
            if( id == 0 ) {
                $('#maintenance_schedule').hide();
                $('#request_resident').hide();
                $('#maintenance_schedule select option:selected').attr('selected', false);
                $('#request_resident select option:selected').attr('selected', false);
            }
            if( id == 1 ) {
                $('#maintenance_schedule').show();
                $('#request_resident').hide();
                $('#request_resident select option:selected').attr('selected', false);
            }
            if( id == 2 ) {
                $('#maintenance_schedule').hide();
                $('#request_resident').show();
                $('#maintenance_schedule select option:selected').attr('selected', false);
            }
        });

        $('#bdc_department_id').change(function() {
            var bdc_department_id = $(this).val();
            $.ajax({
                url: "{{route('admin.work-diary.ajax_get_people_hand')}}",
                method: 'GET',
                data: {
                    // _token: $("[name='_token']").val(),
                    bdc_department_id: bdc_department_id,
                },
                dataType: 'json',
                success: function(response) {
                    $('#assign_to').empty();
                    if ( jQuery.isEmptyObject(response.userprofiles) ) {
                    $('#assign_to').append('<option value="0">Chưa có nhân viên</option>')
                    } else {
                    // $('#assign_to').append('<option value="0">Chọn nhân viên</option>')
                    $.each(response.userprofiles, function(index, val) {
                        $('#assign_to').append('<option value="'+ val.id +'">'+val.display_name+'</option>')
                    });
                    }
                }
            })
        })
    })
</script>
@endsection