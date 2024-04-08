@extends('backend.layouts.master')
@inject('request', 'Illuminate\Http\Request')

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
    <div>
        <ul id="errors"></ul>
    </div>
    <form action="{{ route('admin.work-diary.store') }}" method="POST" id="form-create-work"
        enctype="multipart/form-data">
        {{ csrf_field() }}
        <div class="row">
            <div class="col-lg-8">
                <div class="box box-primary">
                    <div class="box-body">
                        <div class="form-group">
                            <div class="form-group {{ $errors->has('title') ? ' has-error' : '' }}">
                                <label for="recipient-name" class="col-form-label">Tiêu đề:</label>
                                <input type="text" name="title" class="form-control" id="recipient-name"
                                    value="{{ old('title') }}">
                                @if ($errors->has('title'))
                                <span class="help-block">
                                    <strong>{{ $errors->first('title') }}</strong>
                                </span>
                                @endif
                            </div>
                            <div class="form-group {{ $errors->has('description') ? ' has-error' : '' }}">
                                <label class="control-label">Mô tả</label>
                                <!-- <textarea id="description" name="description" rows="5" -->
                                <textarea id="content" name="description" rows="5"
                                    class="mceEditor form-control">{{ old('description') }}</textarea>
                                @if ($errors->has('description'))
                                <span class="help-block">
                                    <strong>{{ $errors->first('description') }}</strong>
                                </span>
                                @endif
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
                                    <select name="select" class="form-control" id="select">
                                        <option value="0" selected>Chọn</option>
                                        <option value="1" @if(request()->exists('maintenance_id') &&
                                            in_array(request()->maintenance_id,
                                            $maintenance_assets->pluck('id')->toArray())) selected @endif>Lịch bảo trì
                                        </option>
                                        <option value="2">Phản hồi cư dân</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <br>
                        <div class="row" id="maintenance_schedule" @if(!request()->exists('maintenance_id') ||
                            !in_array(request()->maintenance_id, $maintenance_assets->pluck('id')->toArray())) hidden
                            @endif>
                            <div class="form-group">
                                <label class="col-md-4 control-label">Lịch bảo trì</label>
                                <div class="col-md-8">
                                    <select name="bdc_maintenance_asset_id" class="form-control">
                                        <option value="" selected>Chọn lịch bảo trì</option>
                                        @foreach($maintenance_assets as $maintenance_asset)
                                        <option value="{{ $maintenance_asset->id }}" @if(request()->maintenance_id ==
                                            $maintenance_asset->id) selected @endif>
                                            {{ $maintenance_asset->title }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row" id="request_resident" hidden>
                            <div class="form-group">
                                <label class="col-md-4 control-label" style="padding-right: 0">Phản hồi cư dân</label>
                                <div class="col-md-8">
                                    <select name="bdc_request_id" class="form-control">
                                        <option value="" selected>Chọn phản hồi cư dân</option>
                                        @foreach($feedbacks as $feedback)
                                        <option value="{{ $feedback->id }}">
                                            {{ $feedback->title }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label">Bộ phận tiếp nhận</label>
                            <select name="bdc_department_id" class="form-control select2" id="bdc_department_id">
                                <option value="0" selected>Bộ phận tiếp nhận</option>
                                @foreach($departments as $department)
                                <option value="{{ $department->id }}" @if ($department->id == old('bdc_department_id'))
                                    selected
                                    @endif>
                                    {{ $department->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="control-label">Người xử lý</label>
                            <select name="assign_to" class="form-control select2" id="assign_to">
                                <option value="0" selected>Chọn nhân viên</option>
                            </select>
                        </div>
                        <div class="form-group {{ $errors->has('start_at') ? ' has-error' : '' }}">
                            <label class="control-label">Ngày bắt đầu</label>
                            <div class="input-group date">
                                <div class="input-group-addon">
                                    <i class="fa fa-calendar"></i>
                                </div>
                                <input type="text" name="start_at" class="form-control pull-right date_picker" readonly
                                    autocomplete="off" value="{{old('start_at')}}">
                            </div>
                            @if ($errors->has('start_at'))
                            <span class="help-block">
                                <strong>{{ $errors->first('start_at') }}</strong>
                            </span>
                            @endif
                        </div>
                        <div class="form-group {{ $errors->has('end_at') ? ' has-error' : '' }}">
                            <label class="control-label">Ngày báo cáo</label>
                            <div class="input-group date">
                                <div class="input-group-addon">
                                    <i class="fa fa-calendar"></i>
                                </div>
                                <input type="text" name="end_at" class="form-control pull-right date_picker" readonly
                                    autocomplete="off" value="{{old('end_at')}}">
                            </div>
                            @if ($errors->has('end_at'))
                            <span class="help-block">
                                <strong>{{ $errors->first('end_at') }}</strong>
                            </span>
                            @endif
                        </div>
                        <div class="form-group">
                            <label class="control-label">Chọn file đính kèm</label>
                            <div class="input-group input-image" data-file="image">
                                <input type="file" name="file_work_diarys[]" value="    " class="form-control"
                                    multiple><span class="input-group-btn"></span>
                            </div>
                        </div>
                        <div class="text-center">
                            <button type="submit" class="btn btn-primary button-js-add">Thêm mới</button>
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
            }
            if( id == 1 ) {
                $('#maintenance_schedule').show();
                $('#request_resident').hide();
            }
            if( id == 2 ) {
                $('#maintenance_schedule').hide();
                $('#request_resident').show();
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
                    add_assign_to(response.userprofiles);
                }
            });

            function add_assign_to(userprofiles) {
                $('#assign_to').empty();
                if ( jQuery.isEmptyObject(userprofiles) ) {
                    $('#assign_to').append('<option value="0">Chưa có nhân viên</option>')
                } else {
                // $('#assign_to').append('<option value="0">Chọn nhân viên</option>')
                $.each(userprofiles, function(index, val) {
                    $('#assign_to').append('<option value="'+ val.id +'">'+val.display_name+'</option>')
                });
                }
            }
        });

        // submit
        // $('#form-create-work').submit(function(e) {
        //         e.preventDefault();
        //         var url = $(this).attr('action');
        //         var method = $(this).attr('method');
        //         var form_data = new FormData($(this)[0]);
        //         $.ajax({
        //             url: url,
        //             method: method,
        //             data: form_data,
        //             contentType: false,
        //             processData: false, 
        //             success: function(response) {
        //                 $('#errors').empty();
        //                     $('#errors').parent().removeClass('alert alert-danger');
        //                     if (response.success == true) {
        //                         toastr.success(response.message);

        //                         setTimeout(() => {
        //                             window.location.href = response.url;
        //                         }, 1000)
        //                     } else {
        //                         toastr.error('Thêm mới công việc không thành công!');
        //                     }
        //             },
        //             error: function(response) {
        //                 $('#errors').empty();
        //                 $('#errors').parent().removeClass('alert alert-danger');
        //                 showErrors(response.responseJSON.errors);
        //             }
        //         });

        //         function showErrors(errors) {
        //             var ul = $('#errors');
        //             ul.parent().addClass('alert alert-danger');
        //             $.each(errors, function(i, item) {
        //                 if (item != '') {
        //                     ul.append('<li>' + item + '</li>')
        //                 }
        //             });
        //         }
        //     });
    });
</script>
@endsection