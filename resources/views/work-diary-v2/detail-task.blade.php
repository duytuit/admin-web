@extends('backend.layouts.master')
@inject('request', 'Illuminate\Http\Request')

@section('content')
    <section class="content-header">
        <h1>
            Chi tiết công việc
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Trang chủ</a></li>
            <li class="active">Chi tiết công việc</li>
        </ol>
    </section>

    <section class="content" id="content-work-diary">
        <div>
            <ul id="errors"></ul>
        </div>
        <form action="" method="POST" id="form-create-work" enctype="multipart/form-data">
            {{ csrf_field() }}
            <input type="hidden" name="id_task" value="{{$id}}" id="id_task">
            <div class="row">
                <div class="col-sm-7">
                    <div class="box box-primary">
                        <div class="box-body">
                            <div class="form-group">
                                <div class="col-md-12 form-group show_permission" style="display: none">
                                    <div class="pull-right">
                                        <a href="javascript;:" class="show_permission"> <i class="fa fa-group"></i> Quyền truy cập</a>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="row">
                                        <div class="col-lg-3">
                                            <p>Công việc: </p>
                                        </div>
                                        <div class="row col-lg-9">
                                            <b style="font-size: 16px;" class="task_title">
                                            </b>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="row">
                                        <div class="col-lg-3">
                                            <p>Thời gian thực hiện: </p>
                                        </div>
                                        <div class="col-lg-9">
                                            <b class="task_execution_time"></b>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-form-label">Mô tả công việc</label>
                                    <p class="task_desc"></p>
                                </div>
                                <div class="row form-group">
                                    <div class="col-sm-12">
                                        <label>Check list công việc</label>
{{--                                        <button type="button" class="btn btn-sm btn-success pull-right" title="Yêu cầu duyệt">Cập nhật</button>--}}
                                        <div class="clone_image hide">
                                            <div class="upload_image control-group input-group" style="margin-top:10px">
                                                <input type="file" onchange="addImageCheckList(this)" class="form-control upload_image_value">
                                                <div class="input-group-btn">
                                                    <button class="btn btn-danger" onclick="delImage(this)" type="button" style="font-size: 14px;"><i class="glyphicon glyphicon-remove"></i></button>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="clone_file hide">
                                            <div class="upload_file control-group input-group" style="margin-top:10px">
                                                <input type="file" onchange="addFileCheckList(this)" class="form-control upload_file_value">
                                                <div class="input-group-btn">
                                                    <button class="btn btn-danger" onclick="delFile(this)" type="button" style="font-size: 14px;"><i class="glyphicon glyphicon-remove"></i></button>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-sm-12 check_list">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-5">
                    <div class="box box-primary">
                        <div class="box-body">
                            <div class="form-group">
                                <label class="col-form-label">Thông tin công việc:</label>
                                <button type="button" class="btn btn-sm btn-info request_confirm" data-type="2" title="Yêu cầu duyệt" style="display: none">Yêu cầu duyệt</button>
                                <button type="button" class="btn btn-sm btn-warning request_switch_shift" data-type="1" title="Yêu cầu chuyển ca" style="display: none">Yêu cầu chuyển ca</button>
                                <button type="button" class="btn btn-sm btn-primary update_task" onclick="updateTask(this)" title="Cập nhật" style="display: none">Cập nhật</button>
                            </div>
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-lg-3">
                                        <p>Trạng thái công việc: </p>
                                    </div>
                                    <div class="col-lg-9">
                                        <select name="status_task" class="form-control" id="status_task_select"></select>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-lg-3">
                                        <p>Mức độ: </p>
                                    </div>
                                    <div class="col-lg-9">
                                      <b class="priority_task"></b>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-3">
                                        <p>Loại công việc: </p>
                                    </div>
                                    <div class="col-lg-9">
                                        <b class="type_task"></b>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-3">
                                        <p>Ngày tạo: </p>
                                    </div>
                                    <div class="col-lg-9">
                                        <b class="created_task"></b>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-3">
                                        <p>Tạo bởi: </p>
                                    </div>
                                    <div class="col-lg-9 by_task">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="row department_task_no_manager" style="display: none">
                                    <div class="col-lg-3">
                                        <p >Bộ phận: </p>
                                    </div>
                                    <div class="col-lg-9 department_task">

                                    </div>
                                </div>
                                <div class="row department_task_manager" style="display: none">
                                    <div class="col-lg-3">
                                        <p >Bộ phận: </p>
                                    </div>
                                    <div class="col-lg-9">
                                        <select name="department_id[]" class="form-control" id="department_id" style="width: 100%" multiple></select>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="row assigned_no_manager" style="display: none">
                                    <div class="col-lg-3">
                                        <p>Người thực hiện: </p>
                                    </div>
                                    <div class="col-lg-9 assigned">
                                    </div>
                                </div>
                                <div class="row assigned_manager" style="display: none">
                                    <div class="col-lg-3">
                                        <p>Người thực hiện: </p>
                                    </div>
                                    <div class="col-lg-9">
                                        <select class="form-control" name="assigned[]" id="assigned"  style="width: 100%" multiple> </select>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="row assigned_monitor_no_manager" style="display: none">
                                    <div class="col-lg-3">
                                        <p>Người giám sát: </p>
                                    </div>
                                    <div class="col-lg-9 assigned_monitor">
                                    </div>
                                </div>
                                <div class="row assigned_monitor_manager" style="display: none">
                                    <div class="col-lg-3">
                                        <p>Người giám sát: </p>
                                    </div>
                                    <div class="col-lg-9 ">
                                        <select name="assigned_monitor[]" class="form-control" id="assigned_monitor"  style="width: 100%" multiple></select>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-lg-3">
                                        <p>Tệp đính kèm: </p>
                                    </div>
                                    <div class="col-lg-9">
                                        <div class="attach_file_task">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="box box-primary">
                        <div class="box-body">
                            <div class="form-group">
                                <label class="col-form-label">Bình luận</label>
                                <div class="box-footer box-comments list_comments">
                                </div>
                                <div class="box-footer box-comments comment_box">
                                    <div class="attach_file_comment form-group">

                                    </div>
                                    <form id="reply" action="#" method="post">
                                        <img class="img-responsive img-circle img-sm" src="/adminLTE/img/user-default.png" alt="Alt Text">
                                        <!-- .img-push is used to add margin to elements next to floating images -->
                                        <div class="img-push" style="position: relative;">
                                            <input data-parent_id="0" class="form-control input_comment_parent" onchange="addComment(this)" placeholder="Viết bình luận ... Sau đó ENTER">
                                            <label style="background-color: #3c8dbc; right: 10px; top: 2px; position: absolute; display: flex; align-items: center;justify-content: center;" class="img-responsive img-circle img-sm">
                                                <i class="fa fa-paperclip" style="font-size: large;"></i>
                                                <input class="input_file" onchange="addFileComment(this)" type="file" multiple="multiple" style="display: none;">
                                            </label>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="box box-primary">
                        <div class="box-body">
                            <div class="form-group">
                                <label class="col-form-label">Lịch sử công việc</label>
                                <ul class="timeline">

                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
        <div class="modal fade" id="form_add_check_list" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" style="padding-right:0px;" aria-hidden="true">
            <div class="modal-dialog form_add_check_list" role="document" style="padding: 20px 0; margin-top: 30px;">
                <div class="modal-content" style="border-radius: 5px;min-height: 250px">
                    <div class="modal-header" style="
            border-top-right-radius: 5px;
            border-top-left-radius: 5px;
            color: white;
            background-color: #3c8dbc;
            padding: 5px;
            border-bottom: 0;
            ">
                        <h5 class="modal-title" style="margin-top: 2px;">Thêm check list</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="margin-top: -20px;margin-right: 10px;">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body" class="col-sm-12" style="padding:0;">
                        <div>
                            <ul id="templatetotask-errors"></ul>
                        </div>
                        <form class="form-horizontal" action="" method="POST" id="modal-form_add_check_list">
                            <div class="box-body">
                                <div class="col-sm-12">
                                    <div class="form-group">
                                        <label for="recipient-name" class="control-label"><span style="color:red;font-size: 18px;">*</span> Tiêu đề:</label>
                                        <input type="text" name="title-parent" class="form-control" id="title-parent" value="">
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer d-flex justify-content-center">
                                <button type="button" class="btn btn-primary add_check_list">Lưu</button>
                                <button type="button" class="btn btn-warning" data-dismiss="modal">Đóng</button>
                            </div>
                        </form>
                    </div>

                </div>
            </div>

        </div>
        <div class="modal fade" id="_showRequest" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" style="padding-right:0px;" aria-hidden="true">
            <div class="modal-dialog switch_shift" role="document" style="padding: 20px 0;">
                <div class="modal-content" style="border-radius: 5px;">
                    <div class="modal-header" style="
            border-top-right-radius: 5px;
            border-top-left-radius: 5px;
            color: white;
            background-color: #3c8dbc;
            padding: 5px;
            border-bottom: 0;
            ">
                        <h5 class="modal-title" style="margin-top: 2px;">Gửi yêu cầu</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="margin-top: -20px;margin-right: 10px;">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body" style="padding:0;">
                        <form class="form-horizontal" action="" method="POST" id="form_confirm_request_approve">
                            {{ csrf_field() }}
                            <input type="hidden" name="building_id" value="{{ @$building_id }}">
                            <input type="hidden" name="type" id="request_type">
                            <input type="hidden" name="task_id" id="request_task_id">
                            <div class="box-body">
                                <div class="form-group" style="padding: 0 45px;">
                                    <div>
                                        <label for="recipient-name" class="control-label"><span style="color:red;font-size: 18px;">*</span>Lý do gửi yêu cầu:</label>
                                    </div>
                                    <input type="text" name="name" class="form-control desc_confirm"  value="">
                                </div>
                                <div class="row" style="padding: 0 45px;">
                                    <div class="col-sm-4">
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="row">
                                            <button type="button" class="btn btn-primary form-control confirm_request_approve">Gửi yêu cầu</button>
                                        </div>
                                    </div>
                                    <div class="col-sm-4">
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal fade" id="_showPermission" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" style="padding-right:0px;" aria-hidden="true">
            <div class="modal-dialog switch_shift" role="document" style="padding: 20px 0;">
                <div class="modal-content" style="border-radius: 5px;">
                    <div class="modal-header" style="
            border-top-right-radius: 5px;
            border-top-left-radius: 5px;
            color: white;
            background-color: #3c8dbc;
            padding: 5px;
            border-bottom: 0;
            ">
                        <h5 class="modal-title" style="margin-top: 2px;">Quyền truy cập công việc</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="margin-top: -20px;margin-right: 10px;">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body" style="padding:0;">
                        <form class="form-horizontal" action="" method="POST" id="form_confirm_permission">
                            {{ csrf_field() }}
                            <input type="hidden" name="building_id" value="{{ @$building_id }}">
                            <div class="box-body">
                                <div class="form-group" style="padding: 0 45px;">
                                    <div>
                                        <label class="control-label">Những người có quyền truy cập:</label>
                                    </div>
                                </div>
                                <div class="form-group" style="padding: 0 45px;">
                                    <div class="col-md-4">
                                        <label class="control-label">Chủ sở hữu</label>
                                    </div>
                                    <div class="col-md-8">
                                        <select  class="form-control" id="permission_owner" style="width: 100%"></select>
                                    </div>
                                </div>
                                <div class="form-group" style="padding: 0 45px;">
                                    <div class="col-md-4">
                                        <label class="control-label">Quyền chỉnh sửa</label>
                                    </div>
                                    <div class="col-md-8">
                                        <select  class="form-control select2" id="permission_member" multiple style="width: 100%"></select>
                                    </div>
                                </div>
                                <div class="form-group" style="padding: 0 45px;">
                                    <div class="col-md-4">
                                        <label class="control-label">Quyền xem</label>
                                    </div>
                                    <div class="col-md-8">
                                        <select  class="form-control select2" id="permission_view" multiple style="width: 100%"></select>
                                    </div>
                                </div>
                                <div class="row" style="padding: 0 45px;">
                                    <div class="col-sm-4">
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="row">
                                            <button type="button" class="btn btn-primary form-control confirm_permission">Áp dụng</button>
                                        </div>
                                    </div>
                                    <div class="col-sm-4">
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <input type="hidden" value="{{$permission_task_user}}" id="permission_task_user">
    <input type="hidden" value="{{$get_permission_by_user}}" id="get_permission_by_user">
    <input type="hidden" value="{{@$status_task}}" id="status_task">
    <input type="hidden" value="{{@$priority_task}}" id="priority_task">
    <input type="hidden" value="{{@$task_detail}}" id="task_detail">
    <input type="hidden" value="{{@$TaskCategory}}" id="taskCategory">
    <input type="hidden" value="{{@$status_history_task}}" id="status_history_task">
@endsection
@section('stylesheet')
    <style>
        .dropdown-menu>li>a:hover {
            background-color: #3eb06f;
            color: white;
        }
        .dropdown-menu>li>a{
            cursor: pointer;
        }
        .box-comments .box-comment a img.set-custom-img {
            width: 70px !important;
            max-height: 60px !important;
            object-fit: contain;
            min-height: 60px;
        }
    </style>
@endsection
@section('javascript')
    <script src="/adminLTE/plugins/moment/moment.min.js"></script>
    <script src="/adminLTE/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js"></script>
    <script>
        function addImage(even){
            html_image = $(".clone_image").html();
            $(even).parent().parent().after(html_image);
        }
        function addFile(even){
            html_file = $(".clone_file").html();
            $(even).parent().parent().after(html_file);
        }
        function delImage(even){
            $(even).parents('.upload_image').remove();
        }
        function delFile(even){
            $(even).parents('.upload_file').remove();
        }
        function addFileCheckList(event){
            files = $(event).prop('files')[0];
            if (files) {
                let formData = new FormData();
                formData.append('file',files);
                formData.append('folder',"{{auth()->user() ? auth()->user()->id : null}}");
                $.ajax({
                    url: "{{route('api.v1.upload.upload_v2')}}",
                    type: 'POST',
                    data: formData,
                    contentType: false, //tell jquery to avoid some checks
                    processData: false,
                    success: function (response) {
                        console.log(response);
                        if (response.success == true) {
                            $(event).data('file_value',response.origin)
                            console.log('file_value',$(event).data('file_value'))
                            toastr.success(response.msg);

                        } else {
                            toastr.error('thất bại');
                        }
                    },
                    error: function(response) {
                        toastr.error('đã có lỗi xảy ra.');
                    }
                });

            }
        }
        function addImageCheckList(event){
            files = $(event).prop('files')[0];
            if (files) {
                let formData = new FormData();
                formData.append('file',files);
                formData.append('folder',"{{auth()->user() ? auth()->user()->id : null}}");
                $.ajax({
                    url: "{{route('api.v1.upload.upload_v2')}}",
                    type: 'POST',
                    data: formData,
                    contentType: false, //tell jquery to avoid some checks
                    processData: false,
                    success: function (response) {
                        console.log(response);
                        if (response.success == true) {
                            $(event).data('image_value',response.location)
                            toastr.success(response.msg);

                        } else {
                            toastr.error('thất bại');
                        }
                    },
                    error: function(response) {
                        toastr.error('đã có lỗi xảy ra.');
                    }
                });

            }
        }

        $('.show-temp-task').click(function (){
            $('#form_add_check_list').modal('show');
        })
        $('.add_check_list').click(function (){
            let param_query_old = "{{ $array_search }}";
            let param_query = param_query_old.replaceAll("&amp;", "&");
            var list =[];
            $('.list_detail_checklists .detail_checklist').each(function(index,element) {
                let sub_checklist_title = $(element).find('textarea[name=sub_checklist_title]');
                let sub_checklist_description = $(element).find('textarea[name=sub_checklist_description]');
                let sub_checklist_id = $(element).find('input[name=sub_checklist_id]');
                let video_required = $(element).find('input[name=video_required]').is(':checked') ? 1 : 0;
                let image_required = $(element).find('input[name=image_required]').is(':checked') ? 1 : 0;
                let sort =  $(element).find('input[name=sub_checklist_sort]');
                console.log($(sub_checklist_title).val());
                var values =[];
                var values_warning =[];
                if(sub_checklist_title != '' && sub_checklist_description != ''){
                    $(element).find('.detail_value').each(function(index_1,element_1) {
                        let name_warning = $(element_1).find('input[name=name_warning]');
                        let level_warning = $(element_1).find('select[name=level_warning]');
                        if(name_warning){
                            let lua_chon_number = 'lua_chon_'+(index_1+1);
                            values[lua_chon_number] = name_warning.val();
                            values_warning.push({
                                value: lua_chon_number,
                                warring_number : level_warning.val()
                            });
                        }
                    });
                    var values = Object.assign({}, values);
                    list.push({
                        title: sub_checklist_title.val(),
                        desc : sub_checklist_description.val(),
                        values:values,
                        values_warning : values_warning,
                        video_required : video_required,
                        image_required : image_required,
                        sort : sort.val(),
                    });
                }
            });
            var formCreateCheckList = {
                title:$('#modal-form_add_check_list #title-parent').val(),
                list:JSON.stringify(list)
            };
            postMethod('admin/task/addListFormCheckListDetail'+param_query,formCreateCheckList)
        })
        function deleteFileAttach(event){
            $(event).parent().remove();
        }
        $('.request_switch_shift,.request_confirm').click(function (){
            task_id = $(this).data('task_id')
            type = $(this).data('type')
            $('#request_type').val(type)
            $('#request_task_id').val(task_id)
            $('#_showRequest').modal('show');
        })
        $('.confirm_request_approve').click(function (){
            if( edit_request == false){
                alert('Bạn không có quyền.')
                return
            }
            if($('#request_type').val() == 2 && (task.status == 0 || task.status == 1 || task.status == 3)){
                alert('Trạng thái công việc chưa hợp lệ. để gửi yêu cầu')
                return
            }
            if($('#request_type').val() == 1 && (task.status == 0 || task.status == 1)){
                alert('Trạng thái công việc chưa hợp lệ. để gửi yêu cầu')
                return
            }
            let method='post';
            let param_query_old = "{{ $array_search }}";
            let param_query = param_query_old.replaceAll("&amp;", "&")
            let building_id = "{{ $building_id }}";
            data ={
                task_id:$('#request_task_id').val(),
                reason:$('.desc_confirm').val(),
                type:$('#request_type').val()
            }
           postMethod('admin/task/addReqChangeAssigner?building_id='+building_id,data);
        })
        var list_all_users = null
        var permission_user = null
        var status_task = null
        var task =null
        var edit_per = false
        var edit_request = false
        var edit_update = false
        var edit_checked = false
        var task_status = null
        var all_assigned = null
        var per_t_bql = null
        var list_user_by_permission = []
        var all_department = null
        var per_assigned = false
        let task_detail = null;
        permission_owner = null;
        permission_member = null;
        permission_view = null;
        $(document).ready(function () {

            task_detail = null;
            if($('#task_detail').val()){
                task_detail = JSON.parse($('#task_detail').val());
                $('.task_title').text(task_detail.task.title)
                $('.task_execution_time').text(task_detail.task.start_date + ' - '+task_detail.task.start_date )
                $('.task_desc').text(task_detail.task.desc)
                console.log(task_detail)
                task =task_detail.task
                $('.input_comment_parent').attr('data-id_request',task.id)
                getComment(task.id);
                getHistory(task.id);
                $('.request_confirm').data('task_id',task.id)
                $('.request_switch_shift').data('task_id',task.id)
                if($('#priority_task').val()){
                    priority_task = JSON.parse($('#priority_task').val());
                    Object.keys(priority_task).forEach(function(key) {
                        if(key == task_detail.task.priority){
                            $('.priority_task').html(priority_task[key])
                        }
                    });
                }
                if($('#taskCategory').val()){
                    taskCategory = JSON.parse($('#taskCategory').val())
                    taskCategory.forEach((v,i)=>{
                        if(v.id == task.category_task_id){
                            $('.type_task').text(v.name)
                        }
                    })
                }

                $('.created_task').text(task.created_at)

                list_checked = null;
                if(task_detail.list_checked){
                    list_checked = task_detail.list_checked;
                }

                if(task_detail.listFormCheck){
                      html='';
                      task_detail.listFormCheck.forEach((item,index)=>{
                          index+=1;
                          _values='';
                          get_list_checked=null;

                          get_list_checked = list_checked.filter(v=>v.form_checklist_detail_id == item.id);

                          if(get_list_checked.length > 0){
                              get_list_checked = get_list_checked[0]
                          }
                          if(item.values){
                              values = JSON.parse(item.values);
                              values_warning = JSON.parse(item.values_warning);
                              Object.keys(values).forEach((item_1,index_1)=>{
                                  value_list_checked=null;
                                  _value_list_checked=null;
                                  if(get_list_checked && get_list_checked.value){
                                      value_list_checked = JSON.parse(get_list_checked.value)
                                      Object.keys(value_list_checked).forEach((item_4,index_4)=>{
                                          if(item_4==item_1){
                                              _value_list_checked = item_4;
                                          }
                                      })
                                  }
                                  console.log('_value_list_checked',_value_list_checked)
                                  _values_warning=' <b>(bình thường)</b>';
                                  if(values_warning[index_1]?.warring_number == 1){
                                      _values_warning=' <b>(không nghiêm trọng)</b>';
                                  }
                                  if(values_warning[index_1]?.warring_number == 2){
                                      _values_warning=' <b>(nghiêm trọng)</b>';
                                  }
                                  _values+= '             <div class="checkbox">'+
                                        '                     <label>'+
                                        '                         <input type="checkbox" '+(_value_list_checked ? 'checked':'')+' disabled class="choose_no" data-action="'+index_1+'" data-item_value="'+item_1+'" data-item_text="'+values[item_1]+'"  data-is_warning="'+(values_warning[index_1]? values_warning[index_1]?.warring_number : '')+'" onclick="chooseNoValue(this)" value="1">'+values[item_1]+_values_warning+
                                        '                     </label>'+
                                        '                 </div>';
                              })
                          }
                          _images='';
                          if(get_list_checked.images){
                              let list_image = JSON.parse(get_list_checked.images);
                              if(list_image.length >0){
                                  list_image.forEach((item)=>{
                                      let split_file = item.split("/");
                                      let name_file = split_file[split_file.length - 1];
                                      let html =' <div>'+
                                          '<a target="_blank" href="'+item+'">'+name_file +'</a>'+
                                          '</div>';
                                      _images+=html;
                                  })
                              }
                          }
                          _videos='';
                          if(get_list_checked.videos){
                              let list_file = JSON.parse(get_list_checked.videos);
                              if(list_file.length >0){
                                  list_file.forEach((item)=>{
                                      let split_file = item.split("/");
                                      let name_file = split_file[split_file.length - 1];
                                      let html =' <div>'+
                                          '<a target="_blank" href="'+item+'">'+name_file +'</a>'+
                                          '</div>';
                                      _videos+=html;
                                  })
                              }
                          }
                          html+=' <div class="check_list_item">'+
                                '         <b>'+index+'. '+item.title+'</b>'+
                                '         <a type="button" class="btn btn-success pull-right action_add_form" style="display: none" data-image_required="'+item.image_required+'" data-video_required="'+item.video_required+'"  data-checked_id="'+(get_list_checked.id?get_list_checked.id:'')+'" data-task_id="'+task.id+'" data-form_checklist_detail_id="'+item.id+'" data-form_checklist_id="'+item.form_checklist_id+'" style="color:white" onclick="updateFormCheckListTask(this)">Sửa</a>'+
                                '     <p>'+item.desc+'</p>'+
                                '     <table class="table table-hover table-striped table-bordered">'+
                                '         <tr>'+
                                '             <td>'+_values+
                                '             </td>'+
                                '         </tr>'+
                                '         <tr>'+
                                '             <td>'+
                                '                 <label>Mô tả vấn đề</label>'+
                                '                 <input type="text" readonly name="problem" class="form-control" value="'+(get_list_checked.problem?get_list_checked.problem:'')+'" placeholder="Mô tả vấn đề">'+
                                '             </td>'+
                                '         </tr>'+
                                '         <tr>'+
                                '             <td>'+
                                '                 <div>'+
                                '                     <label>Dự đoán vấn đề</label>'+
                                '                     <input type="text" readonly name="guess" class="form-control" value="'+(get_list_checked.guess?get_list_checked.guess:'')+'" placeholder="Dự đoán vấn đề">'+
                                '                 </div>'+
                                '             </td>'+
                                '         </tr>'+
                                '         <tr>'+
                                '             <td>'+
                                '                 <div>'+
                                '                     <label>Cách giải quyết</label>'+
                                '                     <input type="text" readonly  name="solution" class="form-control" value="'+(get_list_checked.solution?get_list_checked.solution:'')+'" placeholder="Cách giải quyết">'+
                                '                 </div>'+
                                '             </td>'+
                                '         </tr>'+
                                '         <tr>'+
                                '             <td>'+
                                '                 <div class="row">'+
                                '                     <div class="col-sm-6">'+
                                '                         <label class="control-label">' +
                                '                             <input type="checkbox" class="checkbox_image" '+(item.image_required == 1 ? 'checked' : '')+'  disabled>'+
                                '                              Tải hình ảnh</label>'+
                                '                         <div class="form-group list_upload_image">'+_images+

                                '                         </div>'+
                                '                         <div>'+
                                '                             <div class="upload_image input-group control-group" style="display: none">'+
                                '                                 <input type="file" onchange="addImageCheckList(this)" class="form-control upload_image_value">'+
                                '                                     <div class="input-group-btn">'+
                                '                                         <button class="btn btn-success" type="button" onclick="addImage(this)" style="font-size: 14px;"><i class="glyphicon glyphicon-plus"></i></button>'+
                                '                                     </div>'+
                                '                             </div>'+
                                '                         </div>'+
                                '                     </div>'+
                                '                     <div class="col-sm-6">'+
                                '                         <label class="control-label">' +
                                '                           <input type="checkbox" class="checkbox_file" '+(item.video_required == 1 ? 'checked' : '')+' disabled>'+
                                '                           Tải video</label>'+
                                '                         <div class="form-group list_upload_file">'+_videos+

                                '                         </div>'+
                                '                         <div>'+
                                '                             <div class="upload_file input-group control-group" style="display: none">'+
                                '                                 <input type="file" onchange="addFileCheckList(this)" class="form-control upload_file_value">'+
                                '                                     <div class="input-group-btn">'+
                                '                                         <button class="btn btn-success" type="button" onclick="addFile(this)" style="font-size: 14px;"><i class="glyphicon glyphicon-plus"></i></button>'+
                                '                                     </div>'+
                                '                             </div>'+
                                '                         </div>'+
                                '                     </div>'+
                                '                 </div>'+
                                '             </td>'+
                                '         </tr>'+
                                '     </table>'+
                                '</div>';
                      })
                      $('.check_list').html(html);
                        $('.check_list .check_list_item').each(function (){
                            edit_checked =false;
                            if($(this).find('.choose_no').is(":checked")){
                                edit_checked =true;
                            }
                        });
                }
            }
            if(task.permission){
                permission = JSON.parse(task.permission);
                permission_owner = permission.permission_owner;
                permission_member = permission.permission_member;
                permission_view = permission.permission_view;
            }

            if ($('#get_permission_by_user').val()) {
                permission_user = JSON.parse($('#get_permission_by_user').val());
                permission_user.permission.forEach(item_2=>{

                    if(item_2.type_manager == 1){
                        per_t_bql = item_2.type_manager;
                    }
                })
                console.log(permission_user)

            }
            if(permission_owner) {
                permission_owner.forEach(item => {
                    if (item == permission_user.user_id) {
                        edit_per = true;
                        $('.show_permission').show();
                    }
                })
            }
            departments = JSON.parse(window.localStorage.getItem("departments"));
            if(departments){
                let list_department = [];
                departments.forEach((item) => {
                    list_department.push({
                        id: item.id,
                        text: item.name
                    })

                })
                $('#department_id').select2({data: list_department});
            }

            if(task.department_id){
                department_ids = JSON.parse(task.department_id);
                department_ids.forEach(item_1=>{
                    department = departments.filter(item=>item.id == item_1)
                    if(department){
                        $('.department_task').append('<p><b>'+department[0].name+'</b></p>');
                    }
                    permission_user.permission.forEach(item_2=>{

                        if(item_2.bdc_department_id == item_1){
                            per_assigned=true;
                            $('.request_confirm').show();
                            $('.request_switch_shift').show();
                            edit_request = true;
                            if(item_2.type == 1 || item_2.type_manager == 1){
                                $('.show_permission').show();
                                $('.request_confirm').hide();
                                $('.request_switch_shift').hide();
                                edit_per =true
                                edit_request = false;
                                per_assigned=false;
                            }
                        }
                    })

                })
                $('#department_id').val(department_ids).change();
                all_department = department_ids
            }
            permission_user.permission.forEach(item_2=>{
                if(item_2.type == 1 || item_2.type_manager == 1){
                    edit_per =true
                }
            })
            if(edit_per == true){
                $('.assigned_monitor_no_manager').hide();
                $('.department_task_no_manager').hide();
                $('.assigned_no_manager').hide();

                $('.assigned_monitor_manager').show();
                $('.department_task_manager').show();
                $('.assigned_manager').show();
                $('.check_list .check_list_item').each(function (){
                    $(this).find('.action_add_form').hide();
                });
            }else{
                $('.assigned_monitor_no_manager').show();
                $('.department_task_no_manager').show();
                $('.assigned_no_manager').show();

                $('.assigned_monitor_manager').hide();
                $('.department_task_manager').hide();
                $('.assigned_manager').hide();

                $('.check_list .check_list_item').each(function (){
                    $(this).find('.action_add_form').show();
                });
            }





            if ($('#permission_task_user').val()) {
                list_all_users = JSON.parse($('#permission_task_user').val());
                console.log(list_all_users);
                var data_users = [];
                for (let index = 0; index < list_all_users.length; index++) {
                    var user = list_all_users[index];
                    data_users.push({
                        id: user.user_id,
                        text: user.full_name
                    })
                    if(task.create_by){
                        user_id = task.create_by.indexOf('user_') > -1 ? task.create_by.replace("user_",''):'';
                        if(user_id  == user.user_id){

                             $('.by_task').append('<p><b>'+user.full_name + '</b> - '+user.email+'</p>')
                            let __user=[];
                              __user.push({
                                  id: user_id,
                                  text:  user.full_name
                              })


                        }

                        if(permission_user.user_id == user_id){
                            edit_update = true;
                            $('.show_permission').show();
                            $('.request_confirm').hide();
                            $('.request_switch_shift').hide();
                            $('.assigned_monitor_no_manager').hide();
                            $('.department_task_no_manager').hide();
                            $('.assigned_no_manager').hide();

                            $('.assigned_monitor_manager').show();
                            $('.department_task_manager').show();
                            $('.assigned_manager').show();
                            edit_per = true
                        }
                    }
                    if(user.permission){
                        user.permission.forEach(element => {
                            if(permission_user){
                                permission_user.permission.forEach(item => {
                                    if (element.bdc_department_id == item.bdc_department_id) {
                                        list_user_by_permission.push({
                                            id: user.user_id,
                                            text: user.full_name
                                        })
                                    }
                                });
                            }
                        })

                    }
                }
                console.log(list_user_by_permission)
                $('#permission_member').select2({data: data_users})
                $('#permission_view').select2({data: data_users})
                $('#permission_owner').select2({data: data_users})
                if( task.permission){
                    task_permission = JSON.parse(task.permission);
                    if(task_permission.permission_owner.length > 0){
                        $('#permission_owner').val(task_permission.permission_owner).change()
                    }
                    if(task_permission.permission_member.length > 0){
                        $('#permission_member').val(task_permission.permission_member).change()
                    }
                    if(task_permission.permission_view.length > 0){
                        $('#permission_view').val(task_permission.permission_view).change()
                    }
                    console.log(task_permission)
                }
                $('#assigned_monitor').select2({data: data_users});
                $('#assigned').select2({data: data_users});


                if(task.assigned){

                    assigned = JSON.parse(task.assigned);
                    assigned.forEach(item_1=>{
                        user = list_all_users.filter(item=>item.user_id == item_1)
                        console.log('assigned',user)
                        if(user){
                            $('.assigned').append('<p><b>'+user[0].full_name + '</b> - '+user[0].email+'</p>')
                        }
                        user_id = task.create_by.indexOf('user_') > -1 ? task.create_by.replace("user_",''):'';
                        if(item_1 == user_id){
                            $('.check_list .check_list_item').each(function (){
                                $(this).find('.action_add_form').show();
                            });
                        }
                        if(item_1 == permission_user.user_id){
                            edit_update = true;
                        }
                    })
                    $('#assigned').val(assigned).change();
                    all_assigned = assigned.map(Number);
                }
                if(edit_update == false){
                    $('.check_list .check_list_item').each(function (){
                        $(this).find('.action_add_form').hide();
                    });
                }
                if(task.assigned_monitor){
                    assigned_monitor = JSON.parse(task.assigned_monitor);
                    assigned_monitor.forEach(item_1=>{
                        user = list_all_users.filter(item=>item.user_id == item_1)
                        console.log('assigned_monitor',user)
                        if(user){
                            $('.assigned_monitor').append('<p><b>'+user[0].full_name + '</b> - '+user[0].email+'</p>')
                        }
                    })
                    $('#assigned_monitor').val(assigned_monitor).change();
                }

            }
            if(task_detail && task_detail.task.assigned_monitor){
                let __assigned_monitor = JSON.parse(task_detail.task.assigned_monitor);
            }

            if(task_detail && task_detail.task.assigned){
                let __assigned = JSON.parse(task_detail.task.assigned);
            }

            if($('#status_task').val()){
                status_task = JSON.parse($('#status_task').val());
                status_task_select =[];
                Object.keys(status_task).forEach(function(key) {
                    status_task_select.push({
                        id: key,
                        text: status_task[key]
                    })
                });
                $('#status_task_select').select2({data: status_task_select})

                task_status = task.status;

                $('#status_task_select').val(task.status).change()
            }
            if(task_detail && task_detail.task.attach_file){
                $('.attach_file_task').html('');
                let list_file = JSON.parse(task_detail.task.attach_file);
                let files='';
                if(list_file.files){
                    list_file.files.forEach((item)=>{
                        let split_file = item.split("/");
                        let name_file = split_file[split_file.length - 1];
                        let html =' <div>'+
                            '<a target="_blank" href="'+item+'">'+name_file +'</a>'+
                            '</div>';
                        files+=html;
                    })
                    $('.attach_file_task').append(files);
                }
                let images='';
                if(list_file.images){
                    list_file.images.forEach((item)=>{
                        let split_image = item.split("/");
                        let name_file = split_image[split_image.length - 1];
                        let html =' <div>'+
                            '<a target="_blank" href="'+item+'">'+name_file +'</a>'+
                            '</div>';
                        images+=html;
                    })
                    $('.attach_file_task').append(images);
                }
            }
            if(task.status == 0 || task.status == 1){
                $('.update_task').hide();
                edit_update = false;
                $('.assigned_monitor_no_manager').show();
                $('.department_task_no_manager').show();
                $('.assigned_no_manager').show();

                $('.assigned_monitor_manager').hide();
                $('.department_task_manager').hide();
                $('.assigned_manager').hide();
                $('.check_list .check_list_item').each(function (){
                    $(this).find('.action_add_form').hide();
                });
            }else {
                $('.update_task').show();
                edit_update = true;
            }
        })
        function chooseNoValue(event){
            index = $(event).data('action')
            $(event).parents('td').find('.choose_no').each(function (){
                index_1 = $(this).data('action');
                if(index != index_1){
                    $(this).prop('checked',false);
                }
            })
        }

       async function updateFormCheckListTask(event){
            check_action = $(event).text();
            task_id = $(event).data('task_id');
            form_checklist_id = $(event).data('form_checklist_id');
            form_checklist_detail_id = $(event).data('form_checklist_detail_id');
            image_required = $(event).data('image_required');
            video_required = $(event).data('video_required');
            console.log(form_checklist_detail_id)
            if(check_action =='Sửa'){
               $(event).text('Lưu');
                $(event).parents('.check_list_item').find('.choose_no').attr('disabled',false)
                $(event).parents('.check_list_item').find('input[name=problem]').removeAttr('readonly')
                $(event).parents('.check_list_item').find('input[name=guess]').removeAttr('readonly')
                $(event).parents('.check_list_item').find('input[name=solution]').removeAttr('readonly')
                $(event).parents('.check_list_item').find('.upload_image').show()
                $(event).parents('.check_list_item').find('.upload_file').show()

            }else {
                $(event).text('Sửa');
                $(event).parents('.check_list_item').find('.choose_no').attr('disabled',true)
                problem= $(event).parents('.check_list_item').find('input[name=problem]').attr('readonly',true)
                guess = $(event).parents('.check_list_item').find('input[name=guess]').attr('readonly',true)
                solution= $(event).parents('.check_list_item').find('input[name=solution]').attr('readonly',true)
                $(event).parents('.check_list_item').find('.upload_image').hide()
                $(event).parents('.check_list_item').find('.upload_file').hide()
                item_text = null
                is_warning = null
                item_value = null
                 $(event).parents('.check_list_item').find('.choose_no').each(function (){
                    if($(this).is(":checked")){
                        item_text = $(this).data('item_text')
                        is_warning = $(this).data('is_warning')
                        item_value = $(this).data('item_value')
                        edit_checked =true;
                    }
                });
                $('.check_list .check_list_item').each(function (){
                    edit_checked =false;
                    if($(this).find('.choose_no').is(":checked")){
                        edit_checked =true;
                    }
                });
                _image=[];
                $(event).parents('.check_list_item').find('.list_upload_image a').each(function (){
                     __image = $(this).attr('href');
                     if(__image){
                         _image.push(__image)
                     }
                });
                $(event).parents('.check_list_item').find('.upload_image_value').each(function (){
                    if($(this).data('image_value')){
                        _image.push($(this).data('image_value'))
                    }
                    $(this).data('image_value','')

                });
                console.log('_image',_image)
                _videos=[];
                $(event).parents('.check_list_item').find('.list_upload_file a').each(function (){
                    __videos = $(this).attr('href');
                    if(__videos){
                        _videos.push(__videos)
                    }
                });
                $(event).parents('.check_list_item').find('.upload_file_value').each(function (){
                    file_value =$(this).data('file_value');
                    if(file_value){
                        _videos.push(file_value)
                    }
                    $(this).data('file_value','')
                });
                check_required = true; //mặc địch được tạo
                if((image_required == 1 && _image.length ==0) || (video_required == 1 && _videos.length == 0)){
                    check_required =false
                }
                console.log('_videos',_videos)
                if(check_required == false){
                    alert('Check list công việc bắt buộc phải có ảnh hoặc video.');
                    $(event).parents('.check_list_item').find('.choose_no').each(function (){
                        $(this).prop('checked', false);
                    });
                    edit_checked =false;
                    return;
                }
                if(item_text && item_value){
                    let data_value_check_list=[];
                    let method='post';
                    let building_id = "{{ $building_id }}";
                    let checked_id = $(event).data('checked_id')

                    console.log('checked_id',checked_id)

                    data_value_check_list[item_value] = item_text
                    data_value_check_list = Object.assign({}, data_value_check_list);
                    console.log('data_value_check_list',data_value_check_list)
                    createData = new FormData();
                    createData.append('task_id',task_id)
                    createData.append('value',JSON.stringify(data_value_check_list))
                    createData.append('problem',problem.val())
                    createData.append('guess',guess.val())
                    createData.append('solution',solution.val())
                    createData.append('form_checklist_id',form_checklist_id)
                    createData.append('form_checklist_detail_id',form_checklist_detail_id)
                    createData.append('is_warning',is_warning)
                    createData.append('images',JSON.stringify(_image))
                    createData.append('videos',JSON.stringify(_videos))
                    createData.append('id',checked_id)

                    let _result = await call_api_form_data(method, 'admin/task/addFormCheckListTask?building_id='+building_id,createData);
                    console.log(_result)
                    if (_result.status == true) {
                        console.log(_result.data.id)
                        if(_videos.length >0){
                            $(event).parents('.check_list_item').find('.list_upload_file').html('')
                            _videos.forEach(item=>{
                                let split_file = item.split("/");
                                let name_file = split_file[split_file.length - 1];
                                let html =' <div>'+
                                    '<a target="_blank" href="'+item+'">'+name_file +'</a>'+
                                    '</div>';
                                $(event).parents('.check_list_item').find('.list_upload_file').append(html)
                            })
                        }
                        if(_image.length >0) {
                            $(event).parents('.check_list_item').find('.list_upload_image').html('')
                            _image.forEach(item => {
                                let split_file = item.split("/");
                                let name_file = split_file[split_file.length - 1];
                                let html = ' <div>' +
                                    '<a target="_blank" href="' + item + '">' + name_file + '</a>' +
                                    '</div>';
                                $(event).parents('.check_list_item').find('.list_upload_image').append(html)
                            })
                        }
                        $(event).data('checked_id',_result.data.id ?_result.data.id : _result.data.before.id)
                        toastr.success(_result.mess);

                    }

                }
                else {
                    alert('Bạn chưa check list công việc')
                }
            }

        }
        async function postMethod(url,param,file) {
            let method='post';
            if(file){
                let _result = await call_api_form_data(method, url,param);
                toastr.success(_result.mess);
            }else{
                let _result = await call_api_data_json(method, url,param);
                toastr.success(_result.mess);
            }
            // setTimeout(function(){
            //     location.reload();
            // }, 1000);
        }
        async function postDel(url,id,reload=true,param=null) {
            if(confirm("Bạn có chắc chắn muốn xóa không?")){
                let method='post';
                let param_query_old = "{{ $array_search }}";
                let param_query = param_query_old.replaceAll("&amp;", "&")
                param_query +="&id="+id;
                console.log(param_query);
                let _result = await call_api(method,url+param_query,param);
                toastr.success(_result.mess);
                if(reload == true){
                    setTimeout(function(){
                        location.reload();
                    }, 1000);
                }
            }
            else{
                return false;
            }
        }

       async function getComment(task_id){
           let method='get';
           let param_query_old = "{{ $array_search }}";
           let param_query = param_query_old.replaceAll("&amp;", "&");
           param_query+='&task_id='+task_id;
           let _result = await call_api(method,'admin/task/getListComment'+param_query);
           if(_result.status == true){
               $('.list_comments').html('');
               _result.data.forEach((item)=>{
                   comments_child ='';
                   if(item.comments_child.length > 0){
                       item.comments_child.forEach((item_1)=>{
                           attach_file_parent =''
                           if(item_1.files){
                               list_files = JSON.parse(item_1.files);
                               let files='';
                               if(list_files.files){
                                   list_files.files.forEach((item_3)=>{
                                       let split_file = item_3.split("/");
                                       let name_file = split_file[split_file.length - 1];
                                       let html =' <div>'+
                                           '<a target="_blank" href="'+item_3+'">'+name_file +'</a>'+
                                           '</div>';
                                       files+=html;
                                   })
                                   attach_file_parent+=files;
                               }
                               let images='';
                               if(list_files.images){
                                   list_files.images.forEach((item_3)=>{
                                       let html ='<span class="comment-content-file-item">'+
                                           '<a target="_blank" href="'+item_3+'" style="height:15px;display: inline-flex;"><img src="'+item_3+'" class="set-custom-img"></a>'+
                                           '</span>';
                                       images+=html;
                                   })
                                   attach_file_parent+=images;
                               }
                           }
                           user = list_all_users.filter(item_2=>item_2.user_id == item_1.user_id)
                           mydate = new Date(item_1.created_at);
                           _timeSince= timeSince(mydate);
                           comments_child= '            <div class="comment-reply">'+
                                           '                <div class="box-comment" id="comment-'+item_1.id+'">'+
                                           '                    <div class="img-user img-circle img-sm" style="background: #6a00ff">'+
                                           '                        <img src="'+(user[0]?.avatar ? user[0].avatar : '/adminLTE/img/user-default.png')+'" alt="'+(user ? user[0].full_name : '')+'" style="border-radius: 50%;">'+
                                           '                    </div>'+
                                           '                    <div class="comment-text">'+
                                           '                        <div class="comment-body">'+
                                           '                            <span class="username">'+(user ? user[0].full_name : '')+'</span>'+attach_file_parent+
                                           '                            <div class="comment-content">'+item_1.content+'</div>'+
                                           '                        </div>'+
                                           '                        <div class="comment-info">'+
                                           '                            <a class="text-muted btn-comment-delete" href="javascript:;" data-id="'+item_1.id+'" onclick="delComment(this)" data-id_request="'+item_1.task_id+'">Xóa</a>'+
                                           '                            <span class="text-muted">'+_timeSince+'</span>'+
                                           '                        </div>'+
                                           '                    </div>'+
                                           '                </div>'+
                                           '            </div>';
                       })
                   }
                   attach_file_parent =''
                   if(item.files){
                       list_files = JSON.parse(item.files);
                       let files='';
                       if(list_files.files){
                           list_files.files.forEach((item_3)=>{
                               let split_file = item_3.split("/");
                               let name_file = split_file[split_file.length - 1];
                               let html =' <div>'+
                                   '<a target="_blank" href="'+item_3+'">'+name_file +'</a>'+
                                   '</div>';
                               files+=html;
                           })
                           attach_file_parent+=files;
                       }
                       let images='';
                       if(list_files.images){
                           list_files.images.forEach((item_3)=>{
                               let html ='<span class="comment-content-file-item">'+
                                         '<a target="_blank" href="'+item_3+'" style="height:15px;display: inline-flex;"><img src="'+item_3+'" class="set-custom-img"></a>'+
                                         '</span>';
                               images+=html;
                           })
                           attach_file_parent+=images;
                       }
                   }
                   user = list_all_users.filter(item_1=>item_1.user_id == item.user_id)
                   mydate = new Date(item.created_at);
                   _timeSince= timeSince(mydate);
                   html = '<div class="box-comment" id="comment-'+item.id+'">'+
                          '<div class="img-user img-circle img-sm" style="background: #008a00">'+
                          '    <img class="img-responsive img-circle img-sm" src="'+(user[0]?.avatar ? user[0].avatar : '/adminLTE/img/user-default.png')+'" alt="Alt Text">'+
                          '    </div>'+
                          '        <div class="comment-text">'+
                          '            <div class="comment-body">'+
                          '                <span class="username">'+(user ? user[0].full_name : '')+'</span>'+attach_file_parent+
                          '                <div class="comment-content">'+item.content+'</div>'+
                          '            </div><!-- /.comment-body -->'+
                          '            <div class="comment-info">'+
                          '                <a class="text-muted btn-comment-delete" href="javascript:;" data-id="'+item.id+'" onclick="delComment(this)" data-id_request="'+item.task_id+'">Xóa</a>'+
                          '                <span class="text-muted">'+_timeSince+'</span>'+
                          '            </div>'+comments_child+
                          '            <div class="comment_box" id="reply-'+item.id+'">'+
                          '                    <div class="attach_file_comment form-group"></div>'+
                          '                        <img class="img-responsive img-circle img-sm" src="'+(user[0]?.avatar ? user[0].avatar : '/adminLTE/img/user-default.png')+'" alt="Alt Text">'+
                          '                    <div class="img-push" style="position: relative;">'+
                          '                        <input data-id_request="'+item.task_id+'" data-parent_id="'+item.id+'" data-action="reply" class="form-control input_comment" onchange="addComment(this)"  placeholder="Viết bình luận ... Sau đó ENTER">'+
                          '                        <label style="background-color: #3c8dbc; right: 10px; top: 2px; position: absolute; display: flex; align-items: center;justify-content: center;" class="img-responsive img-circle img-sm">'+
                          '                            <i class="fa fa-paperclip" style="font-size: large;"></i>'+
                          '                            <input class="input_file" onchange="addFileComment(this)" multiple="multiple" type="file" style="display: none;">'+
                          '                        </label>'+
                          '                    </div>'+
                          '            </div>'+
                          '        </div>'+
                          ' </div>';
                   $('.list_comments').append(html);
                   console.log(item)
               })
           }
           console.log(_result)
        }
        async function addComment(event){
            let method='post';
            let param_query_old = "{{ $array_search }}";
            let param_query = param_query_old.replaceAll("&amp;", "&");
                let create_data = new FormData();
                task_id = $(event).data('id_request');
                create_data.append('task_id',task_id);
                create_data.append('parent_id',$(event).data('parent_id'));
                create_data.append('form_checklist_task_id',0);
                create_data.append('type',1);
                create_data.append('content',$(event).val());

              let files = $(event).parents('.comment_box').find('.input_file').prop('files');
                for (let index = 0; index < files.length; index++) {
                    create_data.append('file',files[index]);
                }

            let _result = await call_api_form_data(method,'admin/task/addTaskComment'+param_query,create_data);
            if(_result.status == true){
                $(event).val('')
                $(event).parents('.comment_box').find('.attach_file_comment').html('')
                await getComment(task_id);
            }

        }
        async function delComment(event){
            if (confirm('Có chắc bạn muốn xóa?')) {
                let method='post';
                let building_id = "{{ $building_id }}";
                task_id = $(event).data('id_request');
                console.log('task_id',task_id)
                _id = parseInt( $(event).data('id'));
                data ={
                    id:_id
                }
                let _result = await call_api_data_json(method,'admin/task/delTaskComment?building_id='+building_id,data);
                if(_result.status == true){
                    await getComment(task_id);
                }
            }

        }
        function addFileComment(event){

            let files =  $(event).prop('files');
            $(event).parents('.comment_box').find('.attach_file_comment').html('');

            for (let index = 0; index < files.length; index++) {
                const element = files[index];
                let html_image = '<div><a href="#" style="margin-left: 10px;">'+element.name+'</a></div>';
                $(event).parents('.comment_box').find('.attach_file_comment').append(html_image);
            }
        }
        async function getHistory(task_id){
            let method='get';
            let param_query_old = "{{ $array_search }}";
            let param_query = param_query_old.replaceAll("&amp;", "&");
            param_query+='&task_id='+task_id+'&page=1&limit=15';
            let _result = await call_api(method,'admin/task/getListTaskHistory'+param_query);
            console.log(_result);
            if(_result.status == true){
                html='';
                status_history_task=null;
                if($('#status_history_task').val()){
                    status_history_task =JSON.parse($('#status_history_task').val());
                }
                _result.data.forEach((item)=>{
                    action='';
                    detail_action='';
                     mydate = new Date(item.created_at);
                    _timeSince= timeSince(mydate);
                    Object.keys(status_history_task).forEach(function(key) {
                        if(key == item.action){
                            action=status_history_task[key];
                            if(key == 'CREATE'){
                                user = list_all_users.filter(item_1=>item_1.user_id == item.by)
                                detail_action=(user[0]?.full_name ? user[0]?.full_name :'');
                            }if(key == 'UPDATE'){
                                user = list_all_users.filter(item_1=>item_1.user_id == item.by)
                                detail_action=(user[0]?.full_name ? user[0]?.full_name :'');
                            }if(key == 'DELETE'){
                                user = list_all_users.filter(item_1=>item_1.user_id == item.by)
                                detail_action=(user[0]?.full_name ? user[0]?.full_name :'');
                            }if(key == 'CHANGE_STATUS'){
                                user = list_all_users.filter(item_1=>item_1.user_id == item.by)
                                if(item.data){
                                    data = JSON.parse(item.data);
                                    Object.keys(status_task).forEach(function(key_1) {
                                          if(key_1 == data.after_value.status){
                                              detail_action=status_task[key_1]+' - '+(user ? user[0]?.full_name :'');
                                          }
                                    });
                                }
                            }if(key == 'CHANGE_ASSIGNED'){
                                user = list_all_users.filter(item_1=>item_1.user_id == item.by)
                                detail_action=(user[0]?.full_name ? user[0]?.full_name :'');
                            }if(key == 'CHANGE_ASSIGNED_MONITOR'){
                                user = list_all_users.filter(item_1=>item_1.user_id == item.by)
                                detail_action=(user[0]?.full_name ? user[0]?.full_name :'');
                            }if(key == 'NOT_ACCEPT_SHIFTS'){
                                user = list_all_users.filter(item_1=>item_1.user_id == item.by)
                                detail_action=(user[0]?.full_name ? user[0]?.full_name :'');
                            }if(key == 'ACCEPT_SHIFTS'){
                                user = list_all_users.filter(item_1=>item_1.user_id == item.by)
                                detail_action=(user[0]?.full_name ? user[0]?.full_name :'');
                            }if(key == 'SEND_SHIFTS'){
                                user = list_all_users.filter(item_1=>item_1.user_id == item.by)
                                detail_action=(user[0]?.full_name ? user[0]?.full_name :'');
                            }
                        }
                    });
                        html+='       <li>'+
                             '           <i class="fa fa-user bg-aqua"></i>'+
                             '           <div class="timeline-item">'+
                             '               <span class="time"><i class="fa fa-clock-o"></i> '+_timeSince+'</span>'+
                             '               <h3 class="timeline-header no-border"><a href="#">'+action+'</a> '+detail_action+'</h3>'+
                             '           </div>'+
                             '       </li>';
                })
                $('.timeline').append(html);
            }
        }
       async function updateTask(event){
           let building_id = "{{ $building_id }}";
           if(edit_update == false){
               alert('Bạn không có quyền chỉnh sửa trạng thái.')
               return
           }
           if(task_detail.listFormCheck.length > 0 && per_t_bql == null){
               if(edit_checked == false && edit_update == false){
                   alert('Check list công việc chưa được hoàn thành.')
                   $('#status_task_select').val(task_status).change()
                   return
               }
           }

           if(per_assigned == true && ($('#status_task_select').val() == 0 || $('#status_task_select').val() == 1)){
               alert('Nhân viên không có quyền chỉnh sửa trạng thái.')
               $('#status_task_select').val(task_status).change()
               return
           }
           if(task_status == 0 || task_status == 1){
               alert('Công việc đã kết thúc.')
               return;
           }
           department_id = $('#department_id').val().map(Number);
           assigned_monitor = $('#assigned_monitor').val().map(Number);
           if(department_id.length == 0){
               alert('bộ phận không được để trống.')
               return;
           }else{
               // permission_user
               // all_assigned
               // so sánh user người thực hiện

               assigned = $('#assigned').val().map(Number);
               let user_temp = [];
               let all_assigned_temp = [];
               if(all_assigned){
                   all_assigned.forEach(item=>{
                       all_assigned_temp.push(item);
                   })
               }
               department_by_assigned=[];
               check_user_in_department =[];
               assigned.forEach(item=>{
                   user_temp.push(item);
                   check_user_in_department.push(item);
                   __user = list_all_users.filter(v=>v.user_id == item);
                   if(__user.length >0){
                       if(__user[0].permission.length > 0){
                           permission = __user[0].permission;
                           permission.forEach(item_1=>{
                               department_by_assigned.push(item_1.bdc_department_id)
                           })
                       }
                   }
               })
               check_per = true;
               // kiểm tra user thêm vào xóa có nằm trong quyền của user không
               if(all_assigned) {
                   all_assigned.forEach(item => {
                       assigned.forEach(item_1 => {
                           if (item == item_1) {
                               removeElement(user_temp, item_1)
                               removeElement(all_assigned_temp, item_1)
                           }
                       })
                   })
               }
               // user_temp danh sách user được thêm
               // all_assigned_temp  danh sách user đã bị xóa

               if(per_t_bql == null){
                   all_assigned_temp.forEach(item=>{
                       __user = list_all_users.filter(v=>v.user_id == item);
                       alert('Bạn không có quyền xóa ['+(__user[0].full_name ?__user[0].full_name : '')+'] ở bộ phận khác.');
                       check_per = false;
                   })
                   array1 = [];
                   user_temp.forEach(item=>{
                       array1.push(item)
                   })


                   user_temp.forEach(item_1=>{

                       list_user_by_permission.forEach(item=>{
                           if(item_1 == item.id){
                               removeElement(array1,item_1)
                           }
                       })
                   })

                   array1.forEach(item=>{
                       __user = list_all_users.filter(v=>v.user_id == item);
                       alert('Bạn không có quyền thêm ['+(__user[0].full_name ?__user[0].full_name : '')+'] ở bộ phận khác vào người thực hiện công việc');
                       check_per = false;

                   })
               }
               // check tiếp xem user chọn có nằm trong bộ phận tiếp nhận không
               assigned.forEach(item_1 => {
                   __user = list_all_users.filter(v=>v.user_id == item_1);
                   if(__user.length >0){
                       if(__user[0].permission.length > 0){
                           permission = __user[0].permission;
                           permission.forEach(item_2=>{
                               department_id.forEach(item_3=>{
                                   if(item_2.bdc_department_id == item_3 ){
                                       removeElement(check_user_in_department,item_1)
                                   }
                               })
                           })
                       }
                   }
               })
               if(check_user_in_department.length > 0){
                   check_user_in_department.forEach(item =>{
                       __user = list_all_users.filter(v=>v.user_id == item);
                       alert('Người thực hiện ['+(__user[0].full_name ?__user[0].full_name : '')+'] không có trong bộ phận được giao công việc');
                   })
                   check_per = false;
                   $('#department_id').val(all_department).change();
               }
               if(check_per == false){
                   $('#assigned').val(all_assigned).change()
                   return;
               }


               console.log(user_temp)
               console.log('all_assigned_temp',all_assigned_temp)
               console.log('all_assigned',all_assigned)
               console.log('list_user_by_permission',list_user_by_permission)
               // console.log('array1',array1)
           }
           param={
               id:task.id,
               status:$('#status_task_select').val()
           }
           if($('#status_task_select').val() == 0 || $('#status_task_select').val() ==1){
               task_status = parseInt($('#status_task_select').val());
           }
           //postMethod('admin/task/changeStatusTask?building_id='+building_id,param);
           let ___result = await call_api_data_json('post', 'admin/task/changeStatusTask?building_id='+building_id,param);
           console.log('___result',___result)
           if (___result.status == true) {
               toastr.success(___result.mess);
           }else {
               toastr.error(___result.mess);
           }

           let formData = new FormData();
           formData.append('title',task.title);
           formData.append('priority',task.priority);
           formData.append('assigned',JSON.stringify(assigned));
           formData.append('checklist_id',task.checklist_id);
           formData.append('category_task_id',task.category_task_id);
           formData.append('department_id',JSON.stringify(department_id));
           formData.append('start_date',task.start_date);
           formData.append('end_date',task.end_date);
           formData.append('desc',task.desc);
           formData.append('assigned_monitor',JSON.stringify(assigned_monitor));
           formData.append('id',task.id);
           formData.append('permission',task.permission);
           formData.append('attack',task.attach_file);
           formData.append('create_by',task.create_by);
           console.log('formDataSchedule____',Object.fromEntries(formData))
           let __task = await call_api_form_data('post','admin/task/addTask?building_id='+building_id,formData);
           // if(__task.status == true){
           //     toastr.success(__task.mess);
           // }else {
           //     toastr.error(__task.mess);
           // }
        }

        $('.show_permission').click(function (e){
            e.preventDefault()
            $('#_showPermission').modal('show')
        })
        $('.confirm_permission').click(function (e){
            e.preventDefault()
            if(edit_per == false){
                alert('Bạn không có quyền.')
                return
            }
            let building_id = "{{ $building_id }}";
            permission = {
                permission_owner: [parseInt($('#permission_owner').val())],
                permission_member:$('#permission_member').val().map(Number),
                permission_view: $('#permission_view').val().map(Number)
            }
            param={
                task_id:task.id,
                permission:JSON.stringify(permission)
            }
            $('#_showPermission').modal('hide')
            postMethod('admin/task/updateTaskPermission?building_id='+building_id,param);

        })
        function removeElement(array, elem) {
            var index = array.indexOf(elem);
            if (index > -1) {
                array.splice(index, 1);
            }
        }
    </script>
@endsection