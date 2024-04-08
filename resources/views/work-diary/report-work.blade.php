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
    <div class="row">
        <div class="col-lg-8">
            <div class="box box-primary">
                <div class="box-body">
                    <div class="panel-group">
                        <div class="panel panel-primary">
                            <div class="panel-heading">
                                <div class="row">
                                    <div class="form-group">
                                        <label for="title" class="col-md-2">Tiêu đề</label>
                                        <b class="col-md-10">{{ @$task->title }}</b>
                                    </div>
                                </div>
                            </div>
                            <div class="panel-body">
                                <div class="row">
                                    <div class="form-group">
                                        <label for="" class="col-md-2">Liên quan đến</label>
                                        @if($maintenance_asset != null)
                                        <a class="col-md-10"
                                            href="{{route('admin.assets.show', ['id' => $maintenance_asset->asset_id])}}">{{$maintenance_asset->title}}</a>
                                        @elseif($feedback != null)
                                        <a class="col-md-10"
                                            href="{{route('admin.feedback.detail', ['id' => $feedback->id])}}">{{$feedback->title}}</a>
                                        @else
                                        <p class="col-md-10">Chưa có</p>
                                        @endif
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="form-group col-md-12">
                                        <label for="description" class="control-label">Mô tả công việc</label>
                                        <div class="form-control border" style="height: auto">{!! @$task->description
                                            !!}</div>
                                    </div>
                                    <div class="form-group col-md-12">
                                        <label for="description" class="control-label">File đính kèm</label>
                                         @if(isset($system_files->name))
                                                <div class="comment-content-file-reply"><a class="download" href="{{ route('admin.work-diary.download_file',['downloadfile'=> $system_files->url]) }}" style="height:15px">{{ $system_files->name}}</a></div>
                                         @endif
                                    </div>
                                </div>
                                <h3 class="text-primary">
                                    Lịch sử
                                </h3>


                                <div class="box box-success">
                                    <div class="slimScrollDiv"
                                        style="position: relative; overflow: hidden; width: auto; height: 500px;">
                                        <div class="box-body" id="chat-box"
                                            style="overflow: auto; width: auto; height: 500px;">
                                            <!-- chat item -->
                                            @isset($review_note)
                                            @foreach($review_note as $key => $val)
                                            <div class="item">
                                                <img src="@if($avatar[$key]){{$avatar[$key]}} @else{{ asset('adminLTE/img/user-default.png') }}@endif" alt="user image" class="direct-chat-img"
                                                    style="float:left">

                                                <p class="direct-chat-text">
                                                    <b class="name" style="display:inline-block">
                                                        {{$val['user_id']}}:
                                                    </b>
                                                    <span style="display:inline-block">Đã chuyển tình trạng
                                                        @if( $val['previous_status'] == "Chưa thực hiện" ||
                                                        $val['previous_status'] == "Đang thực hiện" ||
                                                        $val['previous_status'] == "Đã thực hiện" )
                                                        thực hiện
                                                        @else
                                                        giám sát
                                                        @endif
                                                        từ <i>"{{$val['previous_status']}}"</i> sang tình
                                                        trạng <i>"{{$val['current_status']}}"</i></span>
                                                </p>
                                                <small class="pull-right">Đã gửi {{$val['date']}}</small>
                                                <br>
                                                <div class="form-group col-md-12">
                                                    @if(isset($val['file_name']))
                                                            <div class="comment-content-file-reply"><a class="download" href="{{ route('admin.work-diary.download_file',['downloadfile'=> $val['url_file']]) }}" style="height:15px">{{ $val['file_name']}}</a></div>
                                                    @endif
                                                </div>
                                                {!!$val['note']!!}
                                                <!-- /.attachment -->
                                            </div>
                                            @endforeach
                                            @endisset
                                            <!-- /.item -->
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="box box-primary">
                <div class="box-body">
                    <div class="row">
                        <div class="col-xs-10">
                            <h3 class="text-primary">Thông tin công việc</h3>
                        </div>
                        <div class="col-md-2" style="margin-top: 20px">
                            <a href="{{ route('admin.work-diary.edit', ['id' => $task->id]) }}" type="button"
                                class="btn btn-sm btn-info" title="Sửa"><i class="fa fa-edit"></i></a>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xs-6">
                            <p>Người tạo:</p>
                        </div>
                        <div class="col-xs-6">
                            <p>{{ @$task->pub_profile->display_name }}</p>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xs-6">
                            <p>Ngày tạo:</p>
                        </div>
                        <div class="col-xs-6">
                            <p>{{ @$task->created_at }}</p>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xs-6">
                            <p>Ngày bắt đầu:</p>
                        </div>
                        <div class="col-xs-6">
                            <p>{{ @$task->start_at }}</p>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xs-6">
                            <p>Ngày kết thúc:</p>
                        </div>
                        <div class="col-xs-6">
                            <p>{{ @$task->end_at }}</p>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xs-6">
                            <p>Bộ phận:</p>
                        </div>
                        <div class="col-xs-6">
                            <p>{{ @$task->department->name }}</p>
                        </div>
                    </div>
                    <br>
                    <br>
                    <br>
                    <h3 class="text-primary">Tình trạng</h3>
                    @if($check_permission >= \App\Models\WorkDiary\WorkDiary::P_ASSIGN_TO)
                    <div class="form-group">
                        <label class="control-label">Thực hiện</label>
                        <select name="staff_status" class="form-control staff_status" style="width: 100%;"
                            data-id="{{ $task->id }}" @if($task->status >= 4) disabled @endif>
                            <option value="0" @if($task->status == 0) selected @endif>Chưa thực hiện</option>
                            <option value="1" @if($task->status == 1) selected @endif>Đang thực hiện</option>
                            <option value="2" @if($task->status == 2 || $task->status > 3) selected @endif>Đã thực hiện
                            </option>
                        </select>
                    </div>
                    @endif
                    @if($check_permission >= \App\Models\WorkDiary\WorkDiary::P_SUPERVISOR)
                    <div class="form-group">
                        <label class="control-label">Giám sát</label>
                        <select name="supervisor_status" class="form-control supervisor_status" style="width: 100%;"
                            data-id="{{ $task->id }}" @if($task->status == 5) disabled @endif>
                            <option value="">Giám sát</option>
                            <option value="3" @if($task->status == 3) selected @endif>Cần làm lại</option>
                            <option value="4" @if($task->status >= 4) selected @endif>Đã kiểm tra</option>
                        </select>
                    </div>
                    @endif
                    @if($check_permission == \App\Models\WorkDiary\WorkDiary::P_MANAGER)
                    <div class="form-group">
                        <label class="control-label">Duyệt</label>
                        <button class="btn btn-block btn-success btn-js-browse" data-id="{{ $task->id }}"
                            @if($task->status == 5) disabled @endif>Duyệt</button>
                    </div>
                    @endif
                </div>
                <div class="modal-footer d-flex justify-content-center">
                    <a type="button" class="btn btn-danger" href="{{ route('admin.work-diary.index') }}">Quay lại</a>
                </div>
            </div>
        </div>
    </div>
    @include('work-diary.modal.note')
</section>
@endsection

@section('javascript')
<!-- TinyMCE -->
<!-- <script src="/adminLTE/plugins/tinymce/tinymce.min.js"></script>
<script src="/adminLTE/plugins/tinymce/config.js"></script> -->
<script>
    $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $('.modal').on('hidden.bs.modal', function(){
            $(this).find('form')[0].reset();
            $('#errors').empty();
        });

    $(document).ready(function(){
        var previous_status;
        var new_status;
        $('.staff_status').change(function() {
            new_status = $(this).val();
            var id     = $(this).attr('data-id');
            $.ajax({
                url: `/admin/work-diary/${id}/ajax_get_previous_status`,
                method: 'POST',
                data: {
                    status: new_status,
                },
                success: function(response) {
                    previous_status = response.previous_status;
                    $('#noteWork').modal('show');
                }
            })
        });

        $('.supervisor_status').change(function() {
            new_status = $(this).val();
            var id     = $(this).attr('data-id');
            $.ajax({
                url: `/admin/work-diary/${id}/ajax_get_previous_status`,
                method: 'POST',
                data: {
                    status: new_status,
                },
                success: function(response) {
                    previous_status = response.previous_status;
                    $('#noteWork').modal('show');
                }
            })
        });

        $('.btn-js-browse').click(function() {
            new_status = 5;
            var id     = $(this).attr('data-id');
            $.ajax({
                url: `/admin/work-diary/${id}/ajax_get_previous_status`,
                method: 'POST',
                data: {
                    status: new_status,
                },
                success: function(response) {
                    previous_status = response.previous_status;
                    $('#noteWork').modal('show');
                }
            })
        });

        $('.btn-js-add-note').click(function() {
            var check = true;
            var form_data = new FormData($('#form-note')[0]);
            form_data.append('new_status', new_status);
            form_data.append('previous_status', previous_status);
            // var desc =tinymce.get("description").getContent();
             var desc = CKEDITOR.instances['content'].getData();
            form_data.append('content', desc);
            var fileReader = new FileReader();
            var file_name=null;
            if($('#inputFile').val() ){
               file_name= $('#inputFile').prop('files')[0].name;  
               fileReader.readAsDataURL($('#inputFile').prop('files')[0]);
               fileReader.onload = function () {
               var data = fileReader.result;  // data <-- in this var you have the file data in Base64 format
               form_data.append('name_fileupload', file_name);
               form_data.append('fileBase64', data);
               if( desc == '' ) {
                if( !confirm("Bạn chưa nhập ghi chú! Bạn có muốn lưu không ?") ) {
                    check = false;
                }
                }

                if( check == true) {
                    $.ajax({
                    url: $('#form-note').attr('action'),
                    method: 'POST',
                    data: form_data,
                    contentType: false,
                    processData: false, 
                    success: function(response) {
                        // location.reload();
                        if (response.success == true) {
                            toastr.success('Thay đổi trạng thái công việc thành công!');

                            setTimeout(() => {
                                location.reload();
                            }, 1000)
                        } else {
                            toastr.error('Thay đổi trạng thái công việc không thành công!');
                        }
                    }
                })
                }                 
              };
            }else{
                if( desc == '' ) {
                    if( !confirm("Bạn chưa nhập ghi chú! Bạn có muốn lưu không ?") ) {
                        check = false;
                    }
                }

                if( check == true) {
                    $.ajax({
                    url: $('#form-note').attr('action'),
                    method: 'POST',
                    data: form_data,
                    contentType: false,
                    processData: false, 
                    success: function(response) {
                        // location.reload();
                        if (response.success == true) {
                            toastr.success('Thay đổi trạng thái công việc thành công!');

                            setTimeout(() => {
                                location.reload();
                            }, 1000)
                        } else {
                            toastr.error('Thay đổi trạng thái công việc không thành công!');
                        }
                    }
                })
                }
            }
        });
    });
</script>
@endsection