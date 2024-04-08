@extends('backend.layouts.master')
@inject('request', 'Illuminate\Http\Request')

@section('content')
<section class="content-header">
    <h1>
        Quản lý công việc
        <small>cập nhật công việc</small>
    </h1>
    <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Trang chủ</a></li>
        <li class="active">cập nhật công việc</li>
    </ol>
</section>

<section class="content" id="content-work-diary">
    <div>
        <ul id="errors"></ul>
    </div>
    <form action="" method="POST" id="form-create-work" enctype="multipart/form-data">
        {{ csrf_field() }}
        <input type="hidden" name="id_task" value="{{@$id}}" id="id_task">
        <input type="hidden" name="id_task_schedule" value="{{@$id_task_schedule}}" id="id_task_schedule">
        <div class="row">
            <div class="col-sm-6">
                <div class="box box-primary">
                    <div class="box-body">
                        <div class="form-group">
                            <div class="form-group {{ $errors->has('title') ? ' has-error' : '' }}">
                                <label for="task_name" class="col-form-label">Tên công việc:</label>
                                <input type="text" name="title" class="form-control" id="title" value="{{ @$task->title }}">
                                @if ($errors->has('title'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('title') }}</strong>
                                    </span>
                                @endif
                            </div>
                            <div class="form-group">
                                <label class="col-form-label">Danh mục</label>
                                <select name="category_task_id" class="form-control select2" id="category_task_id">
                                    <option value="" selected>Danh mục</option>
                                    @foreach($TaskCategory as $item)
                                        <option value="{{$item->id}}" {{@$task->category_task_id == $item->id ? 'selected' : ''}} >{{$item->name}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="col-form-label">Liên quan tới</label>
                                <select name="related" class="form-control" id="related">
                                    <option value="" selected>--Chọn--</option>
                                    <option value="1" >Ý kiến cư dân</option>
                                    <option value="2" >Bảo trì tài sản</option>
                                    <option value="3" >Yêu cầu sửa chữa</option>
                                </select>
                            </div>
                            <div class="asset_detail" style="display: none">
                                <div class="row form-group">
                                    <div class="col-lg-6">
                                        <label>Khu khu vực tài sản</label>
                                    </div>
                                    <div class="col-lg-6">
                                        <select class="form-control" id="office_asset_id">
                                            <option value="" selected>Khu vực tài sản</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="row form-group">
                                    <div class="col-lg-6">
                                        <label>Tài sản</label>
                                    </div>
                                    <div class="col-lg-6">
                                        <select class="form-control" id="asset_detail_id">
                                            <option value="" selected>Tài sản</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row form-group feedback" style="display: none">
                                <div class="col-lg-6">
                                    <label>Ý kiến cư dân</label>
                                </div>
                                <div class="col-lg-6">
                                    <select class="form-control" id="feedback_id"></select>
                                </div>
                            </div>
                            <div class="row form-group user_request_5" style="display: none">
                                <div class="col-lg-6">
                                    <label>Yêu cầu sửa chữa</label>
                                </div>
                                <div class="col-lg-6">
                                    <select class="form-control" id="user_request_5"></select>
                                </div>
                            </div>
                            <div class="row form-group">
                                <div class="col-sm-12">
                                    <label>Mức độ công việc</label>
                                </div>
                                <div class="col-sm-3">
                                    <div class="radio radio_priority">
                                        <label>
                                            <input type="radio" name="priority" class="priority_1" value="1" {{@$task->priority == 1 ? 'checked' : ''}}>
                                            Thấp
                                        </label>
                                    </div>
                                </div>
                                <div class="col-sm-3">
                                    <div class="radio">
                                        <label>
                                            <input type="radio" name="priority" class="priority_2" value="2" {{@$task->priority == 2 ? 'checked' : ''}}>
                                            Bình thường
                                        </label>
                                    </div>
                                </div>
                                <div class="col-sm-3">
                                    <div class="radio">
                                        <label>
                                            <input type="radio" name="priority" class="priority_3" value="3" {{@$task->priority == 3 ? 'checked' : ''}}>
                                            Cao
                                        </label>
                                    </div>
                                </div>
                                <div class="col-sm-3">
                                    <div class="radio">
                                        <label>
                                            <input type="radio" name="priority" class="priority_4" value="4" {{@$task->priority == 4 ? 'checked' : ''}}>
                                            Gấp
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-form-label">Mô tả</label>
                                <textarea name="desc" id="description" rows="5" class="form-control">{{ @$task->desc }}</textarea>
                            </div>
                            <div class="form-group">
                                <div>
                                    <label class="col-form-label">Checklist</label>
                                    <button type="button" class="btn btn-sm btn-info add-value-checklist" title="Thêm"><i class="fa fa-plus"></i>Thêm</button>
                                    <a data-toggle="modal" data-target="#addtemplatetotask_2" class="btn btn-sm btn-instagram"><i class="fa fa-plus"></i>Thêm checklist từ mẫu</a>
                                    <a class="btn btn-default show-temp-task" style="float: right;font-weight: bold; display: none"><i class="fa fa-save" style="margin-right: 5px;font-weight: bold;"></i>Lưu mẫu</a>
                                </div>
                                <input type="hidden" name="checklist_id" value="" id="checklist_id">
                                <input type="hidden" name="checklist_title" value="" id="checklist_title">
                                <div class="form-group list_detail_checklists">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="box box-primary">
                    <div class="box-body">
                        <div class="form-group">
                            <label class="control-label">Bộ phận tiếp nhận</label>
                            <select name="department_id[]" class="form-control" id="department_id" multiple>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="control-label">Người thực hiện</label>
                            <select class="form-control" name="assigned[]" id="assigned" multiple> </select>
                        </div>
                        <div class="form-group">
                            <label class="control-label">Người giám sát</label>
                            <select name="assigned_monitor[]" class="form-control" id="assigned_monitor" multiple></select>
                        </div>
                        <div class="form-group">
                            <label class="control-label">Phòng Thanh Tra</label>
                            <select name="officeinspection" class="form-control" id="officeinspection" onchange='updatedata()' >
                                @foreach($departments as $key => $item)
                                <option value="{{$item->id}}">{{$item->name}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="control-label">Người Thanh Tra</label>
                            <select name="officeinspector" class="form-control" id="officeinspector">
                                
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="control-label">Loại công việc</label>
                            <select name="type_task" class="form-control" id="type_task">
                                <option value="no_repeat" selected>Phát Sinh</option>
                                <option value="repeat_week">Lặp lại theo tuần</option>
                                <option value="repeat_month">Lặp lại theo tháng</option>
                            </select>
                        </div>
                        <div class="repeat_task" style="display: none">
                            <div class="form-group repeat_week" style="display: none">
                                <div class="row">
                                    <div class="col-lg-8">
                                        <label class="control-label">Ngày lặp lại trong tuần</label>
                                        <select name="repeat_week" class="form-control select2" multiple id="repeat_week">
                                            <option value="1" selected>Thứ Hai</option>
                                            <option value="2">Thứ Ba</option>
                                            <option value="3">Thứ Tư</option>
                                            <option value="4">Thứ Năm</option>
                                            <option value="5">Thứ Sáu</option>
                                            <option value="6">Thứ Bảy</option>
                                            <option value="0">Chủ nhật</option>
                                        </select>
                                      </div>
                                    <div class="col-lg-4">
                                        <div class="checkbox">
                                            <label>
                                                <input type="checkbox" name="choose_all_week" id="choose_all_week" value="2" >
                                                      Chọn tất cả
                                            </label>
                                        </div>
                                    </div>
                               </div>
                            </div>
                            <div class="form-group repeat_month" style="display: none">
                                <div class="form-group date_repeat_of_month">
                                    <div class="row">
                                        <div class="col-lg-8">
                                            <label class="control-label">Ngày lặp lại trong tháng</label>
                                            <select name="date_repeat_of_month" class="form-control select2" multiple id="date_repeat_of_month">
                                                <option value="1" selected>1</option>
                                                <option value="2">2</option>
                                                <option value="3">3</option>
                                                <option value="4">4</option>
                                                <option value="5">5</option>
                                                <option value="6">6</option>
                                                <option value="7">7</option>
                                                <option value="8">8</option>
                                                <option value="9">9</option>
                                                <option value="10">10</option>
                                                <option value="11">11</option>
                                                <option value="12">12</option>
                                                <option value="13">13</option>
                                                <option value="14">14</option>
                                                <option value="15">15</option>
                                                <option value="16">16</option>
                                                <option value="17">17</option>
                                                <option value="18">18</option>
                                                <option value="19">19</option>
                                                <option value="20">20</option>
                                                <option value="21">21</option>
                                                <option value="22">22</option>
                                                <option value="23">23</option>
                                                <option value="24">24</option>
                                                <option value="25">25</option>
                                                <option value="26">26</option>
                                                <option value="27">27</option>
                                                <option value="28">28</option>
                                                <option value="29">29</option>
                                                <option value="30">30</option>
                                                <option value="31">31</option>
                                            </select>
                                        </div>
                                        <div class="col-lg-4">
                                            <div class="radio">
                                                <label>
                                                    <input type="checkbox" name="choose_date_repeat_of_month" id="choose_date_repeat_of_month" class="choose_all" value="1">
                                                    Chọn tất cả ngày
                                                </label>
                                            </div>
                                            <div class="radio">
                                                <label>
                                                    <input type="checkbox" name="choose_date_repeat_of_month" class="choose_end_date" value="2" >
                                                    Chọn ngày cuối cùng
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                                <div class="form-group repeat_of_month">
                                    <div class="row">
                                        <div class="col-lg-8">
                                            <label class="control-label">Tháng lặp lại</label>
                                            <select name="repeat_month" class="form-control select2" multiple id="repeat_month">
                                                <option value="0" selected>Tháng 1</option>
                                                <option value="1">Tháng 2</option>
                                                <option value="2">Tháng 3</option>
                                                <option value="3">Tháng 4</option>
                                                <option value="4">Tháng 5</option>
                                                <option value="5">Tháng 6</option>
                                                <option value="6">Tháng 7</option>
                                                <option value="7">Tháng 8</option>
                                                <option value="8">Tháng 9</option>
                                                <option value="9">Tháng 10</option>
                                                <option value="10">Tháng 11</option>
                                                <option value="11">Tháng 12</option>
                                            </select>
                                        </div>
                                        <div class="col-lg-4">
                                            <div class="checkbox">
                                                <label>
                                                    <input type="checkbox" name="choose_all_month" id="choose_all_month" onclick="chooseAllMonth(this)" value="2" >
                                                    Chọn tất cả các tháng
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group date_time">
                                <label class="control-label">Thời gian hoàn thành</label>
                                <select name="date_time" class="form-control" id="date_time">
                                </select>
                            </div>
                        </div>
                        <div class="form-group task_no_repeat">
                            <label class="control-label">Ngày bắt đầu</label>
                            <div class="calendar_time" id="calendar"></div>
                            <div class="input-group date_time">
                                <div class="input-group-addon">
                                    <i class="fa fa-calendar"></i>
                                </div>
                                <input type="text" id="start_date" name="start_date" class="form-control pull-right date_picker" autocomplete="off" value="{{ @$task->start_date ? date('d-m-Y',strtotime(@$task->start_date)) : ''}}">
                            </div>
                            @if ($errors->has('start_date'))
                                <span class="help-block">
                                    <strong>{{ $errors->first('start_date') }}</strong>
                                </span>
                            @endif
                            <label class="control-label">Ngày kết thúc</label>
                            <div class="input-group date">
                                <div class="input-group-addon">
                                    <i class="fa fa-calendar"></i>
                                </div>
                                <input type="text" name="end_date" class="form-control pull-right date_picker" autocomplete="off" value="{{@$task->end_date ? date('d-m-Y',strtotime(@$task->end_date)): ''}}">
                            </div>
                            @if ($errors->has('end_date'))
                                <span class="help-block">
                                    <strong>{{ $errors->first('end_date') }}</strong>
                                </span>
                            @endif
                        </div>
                        <div class="form-group">
                            <label class="control-label">Chọn file đính kèm</label>
                            <div class="form-group list_file_attach_task">

                            </div>
                            <div class="list_file_upload">
                                <div class="input-group hdtuto control-group lst increment" >
                                    <input type="file" name="_file" class="myfrm form-control">
                                    <div class="input-group-btn">
                                        <button class="btn btn-success" type="button"><i class="fldemo glyphicon glyphicon-plus"></i>Add</button>
                                    </div>
                                </div>
                            </div>
                            <div class="clone hide">
                                <div class="hdtuto control-group lst input-group" style="margin-top:10px">
                                    <input type="file" name="_file" class="myfrm form-control">
                                    <div class="input-group-btn">
                                        <button class="btn btn-danger" type="button"><i class="fldemo glyphicon glyphicon-remove"></i> Remove</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="text-center" style="text-align: left;">
                            <button type="submit" class="btn btn-primary add-task">Cập nhật</button>
                            <a type="button" class="btn btn-default" href="{{ route('admin.work-diary-v2.index') }}">Trở về</a>
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
                    <h5 class="modal-title" style="margin-top: 2px;">Lưu Checklist</h5>
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
    @include('work-diary-v2.model.addtemplatetask_2')
</section>
<input type="hidden" value="{{$permission_task_user}}" id="permission_task_user">
<input type="hidden" value="{{$get_permission_by_user}}" id="get_permission_by_user">
<input type="hidden" value="{{@$schedule_detail}}" id="schedule_detail">
<input type="hidden" value="{{@$task_detail}}" id="task_detail">
@endsection
@section('stylesheet')
<style>
    .ui-datepicker-clear-month {
        position: absolute;
        top: 9px;
        right: 32px;
        height: 100%;
        line-height: 100%;
        display: inline;
        cursor: pointer;
        color: red !important;
    }

    .select2-container {
        box-sizing: border-box;
        margin: 0;
        position: relative;
        vertical-align: middle;
        width: 100%;
    }

    .select2-container--default {
        width: 100% !important;
    }
    .template_overflow_y {
        max-height: 600px;
        overflow-y: auto;
        overflow-x: hidden;
    }
</style>
@endsection
<link rel="stylesheet" href="/adminLTE/plugins/lightbox/ekko-lightbox.css" />
@section('javascript')
<script src="/adminLTE/plugins/moment/moment.min.js"></script>
<script src="/adminLTE/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js"></script>
<script src="/adminLTE/plugins/lightbox/ekko-lightbox.min.js"></script>
<script>
    $(document).delegate('*[data-toggle="lightbox"]', 'click', function(event) {
        event.preventDefault();
        $(this).ekkoLightbox();
    });
</script>
<script>
    function chooseAllMonth(event){
        console.log('event',event)
        if($(event).is(':checked')){
            $('#repeat_month').select2('destroy').find('option').prop('selected', 'selected').end().select2();
        }else{
            $('#repeat_month').select2('destroy').find('option').prop('selected', false).end().select2();
        }
    }
    $('.show-temp-task').click(function (){
        $('#form_add_check_list').modal('show');
    })
    $('.add_check_list').click(function (){
        if(per_edit_template == false){
            alert('Bạn không có quyền.')
            return;
        }
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
        if(list.length == 0){
            alert('Chưa có mẫu check list nào.')
            return;
        }
        check = validate_input($('#modal-form_add_check_list #title-parent').val(),"REQUIRE", 'Vui lòng nhập tên checklist');
        if(check == false){
            return;
        }
        var formCreateCheckList = {
            title:$('#modal-form_add_check_list #title-parent').val(),
            list:JSON.stringify(list)
        };
        postMethod('admin/task/addListFormCheckListDetail'+param_query,formCreateCheckList)
        $('#form_add_check_list').modal('hide');
    })
    function deleteFileAttach(event){
          $(event).parent().remove();
    }
    //Date picker
    $('input.date_picker').datepicker({
        autoclose: true,
        dateFormat: "dd-mm-yy"
    }).val();
    $('#type_task').change(function (){
          $('.repeat_task').hide();
          $('.repeat_week').hide();
          $('.repeat_month').hide();
          $('.task_no_repeat').show();
          if($(this).val() != 'no_repeat'){
              $('.repeat_task').show();
              $('.task_no_repeat').hide();
              if($(this).val() == 'repeat_week'){
                  $('.repeat_week').show();
                  $('.repeat_month').hide();
              }
              if($(this).val() == 'repeat_month'){
                  $('.repeat_week').hide();
                  $('.repeat_month').show();
              }
          }
    })

    var list_all_users = null
    var permission_user = null
    var all_assigned = null
    var per_t_bql = null
    var per_t_bp = null
    var list_user_by_permission = []
    var all_department = null
    var per_edit_template = false;
    var per_assigned = false
    $(document).ready(function () {
        list_user_by_permission = [];
        per_t_bql = null
        all_department = null
        task = null
        $('.choose_all').click(function () {
            if($(this).is(':checked')){
                $('#date_repeat_of_month').select2('destroy').find('option').prop('selected', 'selected').end().select2();
            }else {
                $('#date_repeat_of_month').select2('destroy').find('option').prop('selected', false).end().select2();
            }

            let repeat_month = $('#repeat_month').val();
            if (repeat_month.length == 0) {
                alert('Bạn chưa chọn tháng lặp lại')
                $(this).prop('checked', false);
            }
            $('.choose_end_date').prop('checked', false);
        })
        $('.choose_end_date').click(function () {
            $('#date_repeat_of_month').select2('destroy').find('option').prop('selected', false).end().select2();
            if($(this).is(':checked')){
                const date = new Date();
                const currentYear = date.getFullYear();
                let repeat_month = $('#repeat_month').val();
                let choose_value = [];
                repeat_month.forEach((item)=>{
                    var lastDay = new Date(currentYear, item, 0);
                    choose_value.push(lastDay.getDate())
                })
                if(choose_value.length > 0){
                    $('#date_repeat_of_month').val(choose_value).change()
                }
            }

            let repeat_month = $('#repeat_month').val();
            if (repeat_month.length == 0) {
                alert('Bạn chưa chọn tháng lặp lại')
                $(this).prop('checked', false);
            }
            $('.choose_all').prop('checked', false);
        })

        if($('#related').val()){
            if ($(this).val() == 1) { // Ý kiến cư dân
                $('.asset_detail').hide();
                $('.feedback').show();
                $('.user_request_5').hide();
            }
            if ($(this).val() == 2) { // Bảo trì tài sản
                $('.asset_detail').show();
                $('.feedback').hide();
                $('.user_request_5').hide();
            }
            if ($(this).val() == 3) { // Yêu cầu sửa chữa
                $('.asset_detail').hide();
                $('.feedback').hide();
                $('.user_request_5').show();
            }
        }
        $('#repeat_week').change(function (){
            repeat_week = $(this).val().filter(function(e) { return e !== '' })
            $('#date_time').html('');
            if(repeat_week.length > 0){
                repeat_week = repeat_week.map(Number);
                var index = repeat_week.indexOf(0);
                if (index !== -1) {
                    repeat_week[index] = 7;
                }
                min_array=[];
                for( let i = 0; (i+1) < repeat_week.length; i++ ){
                    var diff = Math.abs(repeat_week[i+1] - repeat_week[i])
                    min_array.push(diff);
                }
                if(min_array.length >0){
                    min_number = Math.min.apply(null, min_array)
                    console.log(min_array)
                    option='';
                    for (let j = 1;j<=min_number;j++){
                        $('#date_time').append("<option value="+j+"> "+j+" Ngày </option>");
                    }

                }

            }
        })
        $('#date_repeat_of_month').change(function (){
            $('#date_time').html('');
            date_repeat_of_month = $(this).val().filter(function(e) { return e !== '' })
            if(date_repeat_of_month.length > 0){
                min_array=[];
                for( let i = 0; (i+1) < date_repeat_of_month.length; i++ ){
                    var diff = Math.abs(date_repeat_of_month[i+1] - date_repeat_of_month[i])
                    min_array.push(diff);
                }
                if(min_array.length >0){
                   min_number = Math.min.apply(null, min_array)
                    console.log(min_array)
                    option='';
                    for (let j = 1;j<=min_number;j++){
                        $('#date_time').append("<option value="+j+"> "+j+" Ngày </option>");
                    }

                }

            }
        })
        $(".btn-success").click(function(){
            var lsthmtl = $(".clone").html();
            $(".increment").after(lsthmtl);
        });
        $("body").on("click",".btn-danger",function(){
            $(this).parents(".hdtuto").remove();
        });
        let task_detail = null;
        if($('#task_detail').val()){
            task_detail = JSON.parse($('#task_detail').val());
            console.log(task_detail)
        }
        let schedule_detail = null;
        if($('#schedule_detail').val()){
            schedule_detail = JSON.parse($('#schedule_detail').val());
            console.log(schedule_detail)
        }
        let task_schedule_detail =null;
        if(schedule_detail && schedule_detail.data){
            task_schedule_detail = JSON.parse(schedule_detail.data);
            $('#form-create-work #title').val(task_schedule_detail.title)
            $('#form-create-work #category_task_id').val(task_schedule_detail.category_task_id).change();
            $('#form-create-work #description').text(task_schedule_detail.desc);
            $('.priority_'+task_schedule_detail.priority).prop('checked', true);
            $('.task_no_repeat').hide()
            $('.repeat_task').show()
            if(task_schedule_detail.checklist_id.length >0){
                  get_detail(task_schedule_detail.checklist_id[0])
            }
            if(schedule_detail.day_of_week == '[\"*\"]'){  // công việc lặp lại theo tháng
                $('#type_task').val('repeat_month')
                $('.repeat_month').show()
                $('.repeat_week').hide()
                if(schedule_detail.month == '[\"*\"]'){
                    $('#repeat_month').find('option').attr('selected', true);
                    $('#choose_all_month').prop('checked', true);
                }else {
                    month = JSON.parse(schedule_detail.month)
                    $('#repeat_month').val(month).change();
                }
                if(schedule_detail.date == '[\"*\"]'){
                    $('#date_repeat_of_month').find('option').attr('selected', true);
                    $('#choose_date_repeat_of_month').prop('checked', true);
                }else {
                    date = JSON.parse(schedule_detail.date)
                    $('#date_repeat_of_month').val(date).change();
                }
            }else{    // công việc lặp lại theo tuần
                day_of_week = JSON.parse(schedule_detail.day_of_week)
                $('#type_task').val('repeat_week')
                $('.repeat_month').hide()
                $('.repeat_week').show()
                if(schedule_detail.day_of_week == '[\"*\"]'){
                    $('#repeat_week').find('option').attr('selected', true);
                    $('#choose_all_week').prop('checked', true);
                }else {
                    $('#repeat_week').val(day_of_week).change();
                }
            }
            let files='';
            if(task_schedule_detail.attack.files.length >0){
                task_schedule_detail.attack.files.forEach((item)=>{
                    let split_file = item.split("/");
                    let name_file = split_file[split_file.length - 1];
                    let html =' <div class="attach_file item_file">'+
                        '<a target="_blank" href="'+item+'">'+name_file +'</a>'+
                        '<a href="#" style="cursor: pointer;margin-left: 10px;" onclick="deleteFileAttach(this)"><i class="fa fa-trash" style="color: red"></i></a>'+
                        '</div>';
                    files+=html;
                })
                $('.list_file_attach_task').append(files);
            }
            let images='';
            if(task_schedule_detail.attack.images.length >0){
                task_schedule_detail.attack.images.forEach((item)=>{
                    let split_image = item.split("/");
                    let name_file = split_image[split_image.length - 1];
                    let html =' <div class="attach_file item_image">'+
                        '<a target="_blank" href="'+item+'">'+name_file +'</a>'+
                        '<a href="#" style="cursor: pointer;margin-left: 10px;" onclick="deleteFileAttach(this)"><i class="fa fa-trash" style="color: red"></i></a>'+
                        '</div>';
                    images+=html;
                })
                $('.list_file_attach_task').append(images);
            }

            // if(schedule_detail.){
            //
            // }
            console.log('task_schedule_detail',task_schedule_detail)
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
            if(task_detail && task_detail.task.department_id){
                let list_department = JSON.parse(task_detail.task.department_id);
                all_department = list_department
                $('#department_id').val(list_department).change();
            }
            if(task_schedule_detail){
                if(task_schedule_detail.department_id.length > 0){
                    $('#department_id').val(task_schedule_detail.department_id).change();
                }
            }
            $('#department_id').select2();
        }

        if ($('#get_permission_by_user').val()) {
            permission_user = JSON.parse($('#get_permission_by_user').val());
            list_user_by_permission.push({
                id: permission_user.user_id,
                text: permission_user.full_name
            })
            permission_user.permission.forEach(item => {
                if (item.type_manager == 1) {
                    per_t_bql = item.type_manager;
                    per_edit_template = true;
                    $('.show-temp-task').show();
                    per_assigned =true;
                }
                if (item.type == 1) {
                    per_assigned =true;
                    per_t_bp = item.type;
                    per_edit_template = true;
                    $('.show-temp-task').show();
                }
            });

            console.log(permission_user)
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
        }
            // data_users.push({
            //     id: 118630,
            //     text: 'Hương BA'
            // })
            $('#assigned_monitor').select2({data: data_users});
            // $('#supervisor').val([118439,118624]).change();
            if(task_detail && task_detail.task.assigned_monitor){
                let __assigned_monitor = JSON.parse(task_detail.task.assigned_monitor);
                $('#assigned_monitor').val(__assigned_monitor).change();
            }
            if(task_schedule_detail){
                if(task_schedule_detail.assigned_monitor.length > 0){
                    $('#assigned_monitor').val(task_schedule_detail.assigned_monitor).change();
                }
            }
            $('#assigned_monitor').select2();

            $('#assigned').select2({data: data_users});
            if(task_detail && task_detail.task.assigned){
                let __assigned = JSON.parse(task_detail.task.assigned);
                console.log('__assigned',__assigned)
                $('#assigned').val(__assigned).change();

                all_assigned = __assigned.map(Number);
            }
            if(task_schedule_detail){
                if(task_schedule_detail.assigned.length > 0){
                    $('#assigned').val(task_schedule_detail.assigned).change();
                }
            }
            $('#assigned').select2();
        if(task_detail && task_detail.task.attach_file){
            $('#list_file_attach_task').html('');
            let list_file = JSON.parse(task_detail.task.attach_file);
            let files='';
            if(list_file.files){
                list_file.files.forEach((item)=>{
                    let split_file = item.split("/");
                    let name_file = split_file[split_file.length - 1];
                    let html =' <div class="attach_file item_file">'+
                        '<a target="_blank" href="'+item+'">'+name_file +'</a>'+
                        '<a href="#" style="cursor: pointer;margin-left: 10px;" onclick="deleteFileAttach(this)"><i class="fa fa-trash" style="color: red"></i></a>'+
                        '</div>';
                    files+=html;
                })
                $('.list_file_attach_task').append(files);
            }
            let images='';
            if(list_file.images){
                list_file.images.forEach((item)=>{
                    let split_image = item.split("/");
                    let name_file = split_image[split_image.length - 1];
                    let html =' <div class="attach_file item_image">'+
                            '<a target="_blank" href="'+item+'">'+name_file +'</a>'+
                            '<a href="#" style="cursor: pointer;margin-left: 10px;" onclick="deleteFileAttach(this)"><i class="fa fa-trash" style="color: red"></i></a>'+
                            '</div>';
                    images+=html;
                })
                $('.list_file_attach_task').append(images);
            }
        }
        if(task_detail && task_detail.listFormCheck){

            $(".list_detail_checklists").append('');

            count_checklist=0;
            task_detail.listFormCheck.forEach((element)=> {
                $("#checklist_id").val(element.form_checklist_id);
                count_checklist++;
                let value_warning = '';
                if (element.values) {
                    let values = JSON.parse(element.values);
                    let values_warnings = JSON.parse(element.values_warning);
                    Object.keys(values).forEach(function (key, item) {
                        value_warning += '<div class="detail_value">' +
                            '    <div class="col-sm-9">' +
                            '        <div class="col-sm-6">' +
                            '            <label class="control-label">Giá trị</label>' +
                            '            <input type="email" class="form-control input-sm" name="name_warning" value="' + values[key] + '" placeholder="Giá trị">' +
                            '        </div>' +
                            '        <div class="col-sm-6">' +
                            '            <label class="control-label">Mức độ</label>' +
                            '            <select name="level_warning" class="form-control input-sm">' +
                            '                <option value="0" ' + (values_warnings[item]?.warring_number == 0 ? 'selected' : '') + '>Bình thường</option>' +
                            '                <option value="1" ' + (values_warnings[item]?.warring_number == 1 ? 'selected' : '') + '>Không nghiêm trọng</option>' +
                            '                <option value="2" ' + (values_warnings[item]?.warring_number == 2 ? 'selected' : '') + '>Nghiêm trọng</option>' +
                            '            </select>' +
                            '        </div>' +
                            '    </div>' +
                            '    <div class="col-sm-1" style="top: 26px;display: flex">'+
                            '        <a class="btn btn-xs btn-primary" onclick="copyValueCheckList(this)" title="Sao chép"><i class="fa fa-copy"></i></a>'+
                            '        <a class="btn btn-xs btn-danger" onclick="deleteValueCheckList(this)" style="margin-left: 10px" title="Xóa thông tin"><i class="fa fa-trash"></i></a>'+
                            '    </div>'+
                            '</div>';
                    });
                }
                var html = '<div class="form-group detail_checklist" data-id="' + count_checklist + '">' +
                    '<hr/ style="margin: 10px 0;">' +
                    '   <div class="col-sm-10" style="top: 12px;">' +
                    '       <div ><label>Checklist ' + count_checklist + '.: </label></div>' +
                    '       <label class="control-label" class="control-label">Nội dung kiểm tra:</label>' +
                    '       <input type="hidden" name="sub_checklist_id" value="' + element.id + '">' +
                    '       <input type="hidden" name="sub_checklist_sort" value="' + element.sort + '">' +
                    '       <div style="width: 100%;display: flex">' +
                    '           <textarea class="form-control" style="resize: vertical;" name="sub_checklist_title" rows="1">' + element.title + '</textarea>' +
                    '       </div>' +
                    '       <label class="control-label" class="control-label">Mô tả chi tiết:</label>' +
                    '       <div style="width: 100%;display: flex">' +
                    '           <textarea class="form-control" style="resize: vertical;" name="sub_checklist_description" rows="1">' + element.desc + '</textarea>' +
                    '       </div>' +
                    '   </div>' +
                    '   <div class="col-sm-2" style="top: 64px;display: flex">' +
                    '       <a class="btn btn-xs btn-primary" onclick="copyCheckList(this)" title="Sao chép"><i class="fa fa-copy"></i></a>' +
                    '       <a class="btn btn-xs btn-danger" onclick="deleteCheckList(this)" style="margin-left: 10px" title="Xóa thông tin"><i class="fa fa-trash"></i></a>' +
                    '   </div>' +
                    '         <div class="col-sm-12" style="padding: 0;margin-top: 12px">' +
                    '        <div style="display: flex;margin-left: 35px;">' +
                    '            <label class="checkbox">' +
                    '                <input type="checkbox" name="video_required" ' + (element.video_required == 1 ? 'checked' : '') + ' value="1" />Video' +
                    '            </label>' +
                    '            <label class="checkbox" style="margin-left: 50px;margin-top: 10px">' +
                    '                <input type="checkbox" name="image_required" ' + (element.image_required == 1 ? 'checked' : '') + ' value="1" />Hình ảnh' +
                    '            </label>' +
                    '        </div>' +
                    '         </div>' +
                    '   <div class="col-sm-12" style="margin-top: 12px">' +
                    '       <label class="control-label">Giá trị lựa chọn: <span> <button type="button" class="btn btn-sm btn-info" onclick="addValueWarning(this)" title="Thêm"><i class="fa fa-plus"></i></button></span></label>' +
                    '   </div>' +
                    '   <div class="row list_detail_values">' + value_warning +
                    '   </div>' +
                    '</div>';

                $(".list_detail_checklists").append(html);
            })


        }

        async function get_detail(id) {
            let method = 'get';
            let param_query_old = "{{ $array_search }}";
            let param_query = param_query_old.replaceAll("&amp;", "&")
            param_query += "&form_checklist_id=["+id+"]";
            var detail_checklist = await call_api(method, 'admin/task/getListFormCheckListDetail' + param_query);
            if(detail_checklist){
                count_checklist=0;
                $(".list_detail_checklists").html('');
                $("#checklist_id").val(id);
                detail_checklist.data.forEach(element => {
                    count_checklist++;
                    let value_warning ='';
                    if(element.values){
                        let values = JSON.parse(element.values);
                        let values_warnings = JSON.parse(element.values_warning);
                        Object.keys(values).forEach(function(key,item) {
                            value_warning += '<div class="detail_value">'+
                                '    <div class="col-sm-9">'+
                                '        <div class="col-sm-6">'+
                                '            <label class="control-label">Giá trị</label>'+
                                '            <input type="email" class="form-control input-sm" name="name_warning" value="'+values[key]+'" placeholder="Giá trị">'+
                                '        </div>'+
                                '        <div class="col-sm-6">'+
                                '            <label class="control-label">Mức độ</label>'+
                                '            <select name="level_warning" class="form-control input-sm">'+
                                '                <option value="0" '+(values_warnings[item]?.warring_number == 0 ? 'selected' : '')+'>Bình thường</option>'+
                                '                <option value="1" '+(values_warnings[item]?.warring_number == 1 ? 'selected' : '')+'>Không nghiêm trọng</option>'+
                                '                <option value="2" '+(values_warnings[item]?.warring_number == 2 ? 'selected' : '')+'>Nghiêm trọng</option>'+
                                '            </select>'+
                                '        </div>'+
                                '    </div>'+
                                '    <div class="col-sm-1" style="top: 26px;display: flex">'+
                                '        <a class="btn btn-xs btn-primary" onclick="copyValueCheckList(this)" title="Sao chép"><i class="fa fa-copy"></i></a>'+
                                '        <a class="btn btn-xs btn-danger" onclick="deleteValueCheckList(this)" style="margin-left: 10px" title="Xóa thông tin"><i class="fa fa-trash"></i></a>'+
                                '    </div>'+
                                '</div>';
                        });
                    }
                    var html = '<div class="form-group detail_checklist" data-id="'+count_checklist+'">'+
                        '<hr/ style="margin: 10px 0;">'+
                        '   <div class="col-sm-10" style="top: 12px;">'+
                        '       <div ><label>Checklist '+count_checklist+': </label></div>'+
                        '       <label class="control-label" class="control-label">Tiêu đề:</label>'+
                        '       <input type="hidden" name="sub_checklist_id" value="'+element.id+'">'+
                        '       <input type="hidden" name="sub_checklist_sort" value="'+element.sort+'">'+
                        '       <div style="width: 100%;display: flex">'+
                        '           <textarea class="form-control" style="resize: vertical;" name="sub_checklist_title" rows="1">'+element.title+'</textarea>'+
                        '       </div>'+
                        '       <label class="control-label" class="control-label">Mô tả:</label>'+
                        '       <div style="width: 100%;display: flex">'+
                        '           <textarea class="form-control" style="resize: vertical;" name="sub_checklist_description" rows="1">'+element.desc+'</textarea>'+
                        '       </div>'+
                        '   </div>'+
                        '   <div class="col-sm-2" style="top: 64px;display: flex">'+
                        '       <a class="btn btn-xs btn-primary" onclick="copyCheckList(this)" title="Sao chép"><i class="fa fa-copy"></i></a>'+
                        '       <a class="btn btn-xs btn-danger" onclick="deleteCheckList(this)" style="margin-left: 10px" title="Xóa thông tin"><i class="fa fa-trash"></i></a>'+
                        '   </div>'+
                        '         <div class="col-sm-12" style="padding: 0;margin-top: 12px">'+
                        '        <div style="display: flex;margin-left: 35px;">'+
                        '            <label class="checkbox">'+
                        '                <input type="checkbox" name="video_required" '+(element.video_required == 1 ? 'checked':'')+' value="1" />Video'+
                        '            </label>'+
                        '            <label class="checkbox" style="margin-left: 50px;margin-top: 10px">'+
                        '                <input type="checkbox" name="image_required" '+(element.image_required == 1 ? 'checked':'')+' value="1" />Hình ảnh'+
                        '            </label>'+
                        '        </div>'+
                        '         </div>'+
                        '   <div class="col-sm-12" style="margin-top: 12px">'+
                        '       <label class="control-label">Giá trị lựa chọn: <span> <button type="button" class="btn btn-sm btn-info" onclick="addValueWarning(this)" title="Thêm"><i class="fa fa-plus"></i></button></span></label>'+
                        '   </div>'+
                        '   <div class="row list_detail_values">'+value_warning+
                        '   </div>'+
                        '</div>';

                    $(".list_detail_checklists").append(html);
                });
            }
            console.log(detail_checklist);
        }
        // var data_departments = [];
        //     data_departments.push({
        //         id:4,
        //         text:'test'
        //     })
        // $('#department_id').select2({data:data_departments});
        // $('#department_id').find('option').attr('selected', true);
        // $('#department_id').select2();

        get_data_select_check_list({
            object: '#title_task_template',
            url: "{{route('admin.ajax.ajaxGetCheckList')}}",
            data_id: 'id',
            data_text: 'title',
            title_default: 'Chọn Check List'
        });

        function get_data_select_check_list(options) {
            $(options.object).select2({
                ajax: {
                    url: options.url,
                    dataType: 'json',
                    data: function (params) {
                        var query = {
                            search: params.term,
                        }
                        return query;
                    },
                    processResults: function (json, params) {
                        var results = [{
                            id: '',
                            text: options.title_default
                        }];

                        for (i in json.data) {
                            var item = json.data[i];
                            results.push({
                                id: item[options.data_id],
                                text: item[options.data_text]
                            });
                        }
                        return {
                            results: results,
                        };
                    },
                    minimumInputLength: 3,
                }
            });
        }

        //===========filter feedback=================
        get_data_select_feedback({
            object: '#feedback_id',
            url: "{{route('admin.ajax.ajax_get_feedback')}}",
            data_id: 'id',
            data_text: 'title',
            title_default: 'Chọn ý kiến cư dân'
        });

        function get_data_select_feedback(options) {
            $(options.object).select2({
                ajax: {
                    url: options.url,
                    dataType: 'json',
                    data: function (params) {
                        var query = {
                            search: params.term,
                        }
                        return query;
                    },
                    processResults: function (json, params) {
                        var results = [{
                            id: '',
                            text: options.title_default
                        }];

                        for (i in json.data) {
                            var item = json.data[i];
                            results.push({
                                id: item[options.data_id],
                                text: item[options.data_text]
                            });
                        }
                        return {
                            results: results,
                        };
                    },
                    minimumInputLength: 3,
                }
            });
        }
        //===========filter asset=================
        get_data_select_asset_detail({
            object: '#asset_detail_id',
            url: "{{route('admin.ajax.ajaxGetAssetDetailByName')}}",
            data_id: 'id',
            data_text: 'name',
            title_default: 'Chọn Tài sản'
        });

        function get_data_select_asset_detail(options) {
            $(options.object).select2({
                ajax: {
                    url: options.url,
                    dataType: 'json',
                    data: function (params) {
                        var query = {
                            search: params.term,
                            office_id: $('#office_asset_id').val(),
                        }
                        return query;
                    },
                    processResults: function (json, params) {
                        var results = [{
                            id: '',
                            text: options.title_default
                        }];

                        for (i in json.data) {
                            var item = json.data[i];
                            results.push({
                                id: item[options.data_id],
                                text: item[options.data_text]
                            });
                        }
                        return {
                            results: results,
                        };
                    },
                    minimumInputLength: 3,
                }
            });
        }
        //===========filter office asset=================
        get_data_select_office_asset({
            object: '#office_asset_id',
            url: "{{route('admin.ajax.ajaxGetAssetArea')}}",
            data_id: 'id',
            data_text: 'name',
            title_default: 'Chọn Khu vực'
        });

        function get_data_select_office_asset(options) {
            $(options.object).select2({
                ajax: {
                    url: options.url,
                    dataType: 'json',
                    data: function (params) {
                        var query = {
                            search: params.term,
                        }
                        return query;
                    },
                    processResults: function (json, params) {
                        var results = [{
                            id: '',
                            text: options.title_default
                        }];

                        for (i in json.data) {
                            var item = json.data[i];
                            results.push({
                                id: item[options.data_id],
                                text: item[options.data_text]
                            });
                        }
                        return {
                            results: results,
                        };
                    },
                    minimumInputLength: 3,
                }
            });
        }
        $('#title_task_template').change(function (e) {
            e.preventDefault();
            get_check_list_detail($(this).val());
        });
    })

    async function get_check_list_detail(id) {
        let method = 'get';
        let param_query_old = "{{ $array_search }}";
        let param_query = param_query_old.replaceAll("&amp;", "&")
        param_query += "&form_checklist_id=["+id+"]";
        var detail_checklist = await call_api(method, 'admin/task/getListFormCheckListDetail' + param_query);
        if(detail_checklist){
            $(".subtemp_title_task_template").html('')
            let count_check_list=0;
            detail_checklist.data.forEach(element => {
                count_check_list++;
                let value_warning ='';
                if(element.values){
                    let values = JSON.parse(element.values);
                    let values_warnings = JSON.parse(element.values_warning);
                    Object.keys(values).forEach(function(key,item) {
                        let select_warring_number='';
                        if (typeof values_warnings[item] !== "undefined") {
                            select_warring_number =
                                '                <option value="0" '+(values_warnings[item]?.warring_number == 0 ? 'selected' : '')+'>Bình thường</option>'+
                                '                <option value="1" '+(values_warnings[item]?.warring_number == 1 ? 'selected' : '')+'>Không nghiêm trọng</option>'+
                                '                <option value="2" '+(values_warnings[item]?.warring_number == 2 ? 'selected' : '')+'>Nghiêm trọng</option>';
                        }else{
                            select_warring_number =
                                '                <option value="0">Bình thường</option>'+
                                '                <option value="1">Không nghiêm trọng</option>'+
                                '                <option value="2">Nghiêm trọng</option>';
                        }
                        value_warning += '<div class="detail_value">'+
                            '    <div class="col-sm-9">'+
                            '        <div class="col-sm-6">'+
                            '            <label class="control-label">Giá trị</label>'+
                            '            <input type="email" class="form-control input-sm" name="name_warning" value="'+values[key]+'" placeholder="Giá trị">'+
                            '        </div>'+
                            '        <div class="col-sm-6">'+
                            '            <label class="control-label">Mức độ</label>'+
                            '            <select name="level_warning" class="form-control input-sm">'+select_warring_number+
                            '            </select>'+
                            '        </div>'+
                            '    </div>'+
                            '    <div class="col-sm-1" style="top: 26px;display: flex">'+
                            '        <a class="btn btn-xs btn-primary" onclick="copyValueCheckList(this)" title="Sao chép"><i class="fa fa-copy"></i></a>'+
                            '        <a class="btn btn-xs btn-danger" onclick="deleteValueCheckList(this)" style="margin-left: 10px" title="Xóa thông tin"><i class="fa fa-trash"></i></a>'+
                            '    </div>'+
                            '</div>';
                    });
                }
                var html = '<div class="form-group detail_checklist" data-id="'+count_check_list+'">'+
                    '<hr/ style="margin: 10px 0;">'+
                    '   <div class="col-sm-10" style="top: 12px;">'+
                    '       <div ><label>Checklist '+count_check_list+'.: </label></div>'+
                    '       <label class="control-label" class="control-label">Nội dung kiểm tra:</label>'+
                    '       <input type="hidden" name="sub_checklist_id" value="'+element.id+'">'+
                    '       <input type="hidden" name="sub_checklist_sort" value="'+element.sort+'">'+
                    '       <div style="width: 100%;display: flex">'+
                    '           <textarea class="form-control" style="resize: vertical;" name="sub_checklist_title" rows="1">'+element.title+'</textarea>'+
                    '       </div>'+
                    '       <label class="control-label" class="control-label">Mô tả chi tiết:</label>'+
                    '       <div style="width: 100%;display: flex">'+
                    '           <textarea class="form-control" style="resize: vertical;" name="sub_checklist_description" rows="1">'+element.desc+'</textarea>'+
                    '       </div>'+
                    '   </div>'+
                    '   <div class="col-sm-2" style="top: 64px;display: flex">'+
                    '       <a class="btn btn-xs btn-danger" onclick="deleteCheckList(this,2)" style="margin-left: 10px" title="Xóa thông tin"><i class="fa fa-trash"></i></a>'+
                    '   </div>'+
                    '         <div class="col-sm-12" style="padding: 0;margin-top: 12px">'+
                    '        <div style="display: flex;margin-left: 35px;">'+
                    '            <label class="checkbox">'+
                    '                <input type="checkbox" name="video_required" '+(element.video_required == 1 ? 'checked':'')+' value="1" />Video'+
                    '            </label>'+
                    '            <label class="checkbox" style="margin-left: 50px">'+
                    '                <input type="checkbox" name="image_required" '+(element.image_required == 1 ? 'checked':'')+' value="1" />Hình ảnh'+
                    '            </label>'+
                    '        </div>'+
                    '         </div>'+
                    '   <div class="col-sm-12" style="margin-top: 12px">'+
                    '   </div>'+
                    '   <div class="row list_detail_values">'+value_warning+
                    '   </div>'+
                    '</div>';

                $(".subtemp_title_task_template").append(html);
            });
        }
    }
    let count_checklist=0
    $('.add-templatetotask').on('click', function (e) {
        e.preventDefault();
        var list =[];
        $('.subtemp_title_task_template .detail_checklist').each(function(index,element) {
            let sub_checklist_title = $(element).find('textarea[name=sub_checklist_title]');
            let sub_checklist_description = $(element).find('textarea[name=sub_checklist_description]');
            let video_required = $(element).find('input[name=video_required]').is(':checked') ? 1 : 0;
            let image_required = $(element).find('input[name=image_required]').is(':checked') ? 1 : 0;
            console.log($(sub_checklist_title).val());
            var values =[];
            var values_warning =[];
            let value_warning ='';
            count_checklist++;
            $(element).find('.detail_value').each(function(index_1,element_1) {
                let name_warning = $(element_1).find('input[name=name_warning]');
                let level_warning = $(element_1).find('select[name=level_warning]');
                if(name_warning){
                    let lua_chon_number = 'lua_chon_'+(index_1+1);
                    values[lua_chon_number] = name_warning.val();
                    value_warning += '<div class="detail_value">'+
                        '    <div class="col-sm-9">'+
                        '        <div class="col-sm-6">'+
                        '            <label class="control-label">Giá trị</label>'+
                        '            <input type="email" class="form-control input-sm" name="name_warning" value="'+name_warning.val()+'" placeholder="Giá trị">'+
                        '        </div>'+
                        '        <div class="col-sm-6">'+
                        '            <label class="control-label">Mức độ</label>'+
                        '            <select name="level_warning" class="form-control input-sm">'+
                        '                <option value="0" '+(level_warning.val() == 0 ? 'selected' : '')+'>Bình thường</option>'+
                        '                <option value="1" '+(level_warning.val() == 1 ? 'selected' : '')+'>Không nghiêm trọng</option>'+
                        '                <option value="2" '+(level_warning.val() == 2 ? 'selected' : '')+'>Nghiêm trọng</option>'+
                        '            </select>'+
                        '        </div>'+
                        '    </div>'+
                        '    <div class="col-sm-1" style="top: 26px;display: flex">'+
                        '        <a class="btn btn-xs btn-primary" onclick="copyValueCheckList(this)" title="Sao chép"><i class="fa fa-copy"></i></a>'+
                        '        <a class="btn btn-xs btn-danger" onclick="deleteValueCheckList(this)" style="margin-left: 10px" title="Xóa thông tin"><i class="fa fa-trash"></i></a>'+
                        '    </div>'+
                        '</div>';
                }
            });
            var values = Object.assign({}, values);
            var html = '<div class="form-group detail_checklist" data-id="'+count_checklist+'">'+
                '<hr/ style="margin: 10px 0;">'+
                '   <div class="col-sm-10" style="top: 12px;">'+
                '       <div ><label>Checklist '+count_checklist+'.: </label></div>'+
                '       <label class="control-label" class="control-label">Nội dung kiểm tra:</label>'+
                '       <input type="hidden" name="sub_checklist_id">'+
                '       <input type="hidden" name="sub_checklist_sort" value="'+count_checklist+'">'+
                '       <div style="width: 100%;display: flex">'+
                '           <textarea class="form-control" style="resize: vertical;" name="sub_checklist_title" rows="1">'+sub_checklist_title.val()+'</textarea>'+
                '       </div>'+
                '       <label class="control-label" class="control-label">Mô tả chi tiết:</label>'+
                '       <div style="width: 100%;display: flex">'+
                '           <textarea class="form-control" style="resize: vertical;" name="sub_checklist_description" rows="1">'+sub_checklist_description.val()+'</textarea>'+
                '       </div>'+
                '   </div>'+
                '   <div class="col-sm-2" style="top: 64px;display: flex">'+
                '       <a class="btn btn-xs btn-primary" onclick="copyCheckList(this)" title="Sao chép"><i class="fa fa-copy"></i></a>'+
                '       <a class="btn btn-xs btn-danger" onclick="deleteCheckList(this,2)" style="margin-left: 10px" title="Xóa thông tin"><i class="fa fa-trash"></i></a>'+
                '   </div>'+
                '         <div class="col-sm-12" style="padding: 0;margin-top: 12px">'+
                '        <div style="display: flex;margin-left: 35px;">'+
                '            <label class="checkbox">'+
                '                <input type="checkbox" name="video_required" '+(video_required == 1 ? 'checked':'')+' value="1" />Video'+
                '            </label>'+
                '            <label class="checkbox" style="margin-left: 50px;margin-top: 10px">'+
                '                <input type="checkbox" name="image_required" '+(image_required == 1 ? 'checked':'')+' value="1" />Hình ảnh'+
                '            </label>'+
                '        </div>'+
                '         </div>'+
                '   <div class="col-sm-12" style="margin-top: 12px">'+
                '       <label class="control-label">Giá trị lựa chọn: <span> <button type="button" class="btn btn-sm btn-info" onclick="addValueWarning(this)" title="Thêm"><i class="fa fa-plus"></i></button></span></label>'+
                '   </div>'+
                '   <div class="row list_detail_values">'+value_warning+
                '   </div>'+
                '</div>';

            $(".list_detail_checklists").append(html);
            $("#addtemplatetotask_2").modal('hide');

        });
    });

    function deleteCheckList(event,temp=null){
        if(temp == 2){
            $(event).parent().parent().remove();
        }else{
            count_checklist--;
            let id = $(event).parent().parent().find('input[name=sub_checklist_id]').val();
            if(id){
                postDel('admin/task/delTaskFormCheckListDetail',id,false);
            }
        }
    }
    function addValueWarning(event){
        var html = '<div class="detail_value">'+
            '    <div class="col-sm-9">'+
            '        <div class="col-sm-6">'+
            '            <label class="control-label">Giá trị</label>'+
            '            <input type="email" class="form-control input-sm" name="name_warning" placeholder="Giá trị">'+
            '        </div>'+
            '        <div class="col-sm-6">'+
            '            <label class="control-label">Mức độ</label>'+
            '            <select name="level_warning" class="form-control input-sm">'+
            '                <option value="0">Bình thường</option>'+
            '                <option value="1">Không nghiêm trọng</option>'+
            '                <option value="2">Nghiêm trọng</option>'+
            '            </select>'+
            '        </div>'+
            '    </div>'+
            '    <div class="col-sm-1" style="top: 26px;display: flex">'+
            '        <a class="btn btn-xs btn-primary" onclick="copyValueCheckList(this)" title="Sao chép"><i class="fa fa-copy"></i></a>'+
            '        <a class="btn btn-xs btn-danger" onclick="deleteValueCheckList(this)" style="margin-left: 10px" title="Xóa thông tin"><i class="fa fa-trash"></i></a>'+
            '    </div>'+
            '</div>';
        $(event).parents('.detail_checklist').find('.list_detail_values').append(html);
    }
    function deleteValueCheckList(event){
        $(event).parent().parent().remove();
    }
    $('.add-value-checklist').click(function(e){
        e.preventDefault();
        count_checklist++;
        var html = '<div class="form-group detail_checklist" data-id="'+count_checklist+'">'+
            '<hr/ style="margin: 10px 0;">'+
            '   <div class="col-sm-10" style="top: 12px;">'+
            '       <div ><label>Checklist '+count_checklist+'.: </label></div>'+
            '       <label class="control-label" class="control-label">Nội dung kiểm tra:</label>'+
            '       <input type="hidden" name="sub_checklist_id">'+
            '       <input type="hidden" name="sub_checklist_sort" value="'+count_checklist+'">'+
            '       <div style="width: 100%;display: flex">'+
            '           <textarea class="form-control" style="resize: vertical;" name="sub_checklist_title" rows="1"></textarea>'+
            '       </div>'+
            '       <label class="control-label" class="control-label">Mô tả chi tiết:</label>'+
            '       <div style="width: 100%;display: flex">'+
            '           <textarea class="form-control" style="resize: vertical;" name="sub_checklist_description" rows="1"></textarea>'+
            '       </div>'+
            '   </div>'+
            '   <div class="col-sm-2" style="top: 64px;display: flex">'+
            '       <a class="btn btn-xs btn-primary" onclick="copyCheckList(this)" title="Sao chép"><i class="fa fa-copy"></i></a>'+
            '       <a class="btn btn-xs btn-danger" onclick="deleteCheckList(this,2)" style="margin-left: 10px" title="Xóa thông tin"><i class="fa fa-trash"></i></a>'+
            '   </div>'+
            '         <div class="col-sm-12" style="padding: 0;margin-top: 12px">'+
            '        <div style="display: flex;margin-left: 35px;">'+
            '            <label class="checkbox">'+
            '                <input type="checkbox" name="video_required" value="1" />Video'+
            '            </label>'+
            '            <label class="checkbox" style="margin-left: 50px;margin-top: 10px">'+
            '                <input type="checkbox" name="image_required" value="1" />Hình ảnh'+
            '            </label>'+
            '        </div>'+
            '         </div>'+
            '   <div class="col-sm-12" style="margin-top: 12px">'+
            '       <label class="control-label">Giá trị lựa chọn: <span> <button type="button" class="btn btn-sm btn-info" onclick="addValueWarning(this)" title="Thêm"><i class="fa fa-plus"></i></button></span></label>'+
            '   </div>'+
            '   <div class="row list_detail_values">'+
            '       <div class="detail_value">'+
            '           <div class="col-sm-9">'+
            '               <div class="col-sm-6">'+
            '                   <label class="control-label">Giá trị</label>'+
            '                   <input type="text" class="form-control input-sm" name="name_warning" placeholder="Giá trị">'+
            '               </div>'+
            '               <div class="col-sm-6">'+
            '                   <label class="control-label">Mức độ</label>'+
            '                   <select name="level_warning" class="form-control input-sm">'+
            '                       <option value="0">Bình thường</option>'+
            '                       <option value="1">Không nghiêm trọng</option>'+
            '                       <option value="2">Nghiêm trọng</option>'+
            '                   </select>'+
            '               </div>'+
            '           </div>'+
            '           <div class="col-sm-1" style="top: 26px;display: flex">'+
            '               <a class="btn btn-xs btn-primary" onclick="copyValueCheckList(this)" title="Sao chép"><i class="fa fa-copy"></i></a>'+
            '               <a class="btn btn-xs btn-danger" onclick="deleteValueCheckList(this)" style="margin-left: 10px" title="Xóa thông tin"><i class="fa fa-trash"></i></a>'+
            '           </div>'+
            '       </div>'+
            '   </div>'+
            '</div>';

        $(".list_detail_checklists").append(html);

    });
    function removeElement(array, elem) {
        var index = array.indexOf(elem);
        if (index > -1) {
            array.splice(index, 1);
        }
    }
    $('.add-task').click(function (e){
        e.preventDefault();
        $(this).prop('disabled',true);
        let param_query_old = "{{ $array_search }}";
        let param_query = param_query_old.replaceAll("&amp;", "&");
        var form_task = new FormData($('#form-create-work')[0]);
        department_id = $('#department_id').val().map(Number);

        if(department_id.length == 0){
            alert('bộ phận không được để trống.')
            $(this).prop('disabled',false);
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
                $(this).prop('disabled',false);
               return;
            }

            console.log(user_temp)
            console.log('all_assigned_temp',all_assigned_temp)
            console.log('all_assigned',all_assigned)
            console.log('list_user_by_permission',list_user_by_permission)
            // console.log('array1',array1)
        }
        var list =[];
        _check_validate_checklist=false;
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
                        check = validate_input(name_warning.val(),"REQUIRE", 'Vui lòng nhập giá trị checklist');
                        if(check == false){
                            _check_validate_checklist =true;
                        }
                        values_warning.push({
                            value: lua_chon_number,
                            warring_number : level_warning.val()
                        });
                    }
                });
                var values = Object.assign({}, values);
                check = validate_input(sub_checklist_title.val(),"REQUIRE", 'Vui lòng nhập tiêu đề checklist');
                if(check == false){
                    _check_validate_checklist =true;
                }
                check = validate_input(sub_checklist_description.val(),"REQUIRE", 'Vui lòng nhập mô tả checklist');
                if(check == false){
                    _check_validate_checklist =true;
                }
                if(values_warning.length == 0){
                    alert('Vui lòng nhập giá trị checklist');
                    _check_validate_checklist =true;
                }
                list.push({
                    title: sub_checklist_title.val(),
                    desc : sub_checklist_description.val(),
                    values:values,
                    values_warning : values_warning,
                    video_required : video_required,
                    image_required : image_required,
                    sort : sort.val(),
                    id : sub_checklist_id.val()
                });
            }
        });
        check = validate_input(form_task.get('title'),"REQUIRE", 'Vui lòng nhập tên công việc');
        if(check == false){
            _check_validate_checklist =true;
        }
        check = validate_input(form_task.get('category_task_id'),"REQUIRE", 'Vui lòng chọn danh mục công việc');
        if(check == false){
            _check_validate_checklist =true;
        }

        radio_priority = $('input[name=priority]:checked').length;

        if(radio_priority == 0){
            alert('Vui lòng chọn mức độ công việc');
            _check_validate_checklist =true;
        }
        if (_check_validate_checklist == true)
        {
            $('.add-task').prop('disabled',false);
            return;
        }
        var formCreateCheckList = {
            id:  $("#checklist_id").val(),
            list:JSON.stringify(list)
        };
        console.log(formCreateCheckList)
        addCheckList(formCreateCheckList,form_task);

    })
    async function addCheckList(formCreateCheckList,form_task) {
        let method='post';
        let param_query_old = "{{ $array_search }}";
        let param_query = param_query_old.replaceAll("&amp;", "&");
        let id_task = parseInt(form_task.get('id_task'));
        console.log(param_query)
        let check_list = null
        if(id_task || $("#checklist_id").val()){
            let building = "{{$building_id}}"
            check_list = await call_api_data_json(method,'admin/task/updateListFormCheckListDetail?building_id='+building,formCreateCheckList);
        }else{
            check_list = await call_api_data_json(method,'admin/task/addListFormCheckListDetail'+param_query,formCreateCheckList);
        }
        assigned = $('#assigned').val().map(Number);
        department_id = $('#department_id').val().map(Number);
        assigned_monitor = $('#assigned_monitor').val().map(Number);
        if(check_list && check_list.status == true){
            let formData = new FormData();
            console.log('start_date', format_date_no_time(form_task.get('start_date')) + ' 00:00:00')
            console.log(JSON.stringify([check_list.data.id]))

            formData.append('title',form_task.get('title'));
            formData.append('priority',form_task.get('priority'));
            formData.append('assigned',JSON.stringify(assigned));
            formData.append('checklist_id',JSON.stringify([check_list.data.id]));
            formData.append('category_task_id',form_task.get('category_task_id'));
            formData.append('department_id',JSON.stringify(department_id));
            formData.append('start_date',format_date_no_time(form_task.get('start_date')) + ' 00:00:00');
            formData.append('end_date',format_date_no_time(form_task.get('end_date'))+ ' 00:00:00');
            formData.append('desc',form_task.get('desc'));
            formData.append('assigned_monitor',JSON.stringify(assigned_monitor));
            formData.append('id',form_task.get('id_task'));
            // formData.append('list', JSON.stringify(list));
            let formDataSchedule = new FormData();
            let permission ={
                permission_owner : [permission_user.user_id] ,
                permission_member :[] ,
                permission_view :[]
            }
            formData.append('permission',JSON.stringify(permission));
            let files =[];
            $('.list_file_upload .myfrm').each(function (){
                let file = $(this).prop('files')[0];
                if(file){
                    files.push(file)
                    formData.append('file',file);
                    formDataSchedule.append('file',file);
                }
            })
            image_attach = []
            file_attach =[]
            $('.list_file_attach_task .attach_file').each(function (){
                if ($(this).hasClass("item_image")) {
                    image_attach.push($(this).find('a').attr('href'));
                }
                if ($(this).hasClass("item_file")) {
                    file_attach.push($(this).find('a').attr('href'));
                }
            })
            attach_file ={
                files:file_attach,
                images:image_attach
            }
           formData.append('attack',JSON.stringify(attach_file));

            let create_by = null;
            if($('#related').val()){
                let related = $('#related').val();
                if (related == 1) { // Ý kiến cư dân
                   if($('#feedback_id').val()){
                       create_by = 'feedBack_id_'+$('#feedback_id').val()+'_'+getTimeFormat();
                       formData.append('create_by',create_by);
                   }
                }
                if (related == 2) { // Bảo trì tài sản
                    if($('#asset_detail_id').val()){
                        create_by = 'maintain_asset_'+$('#asset_detail_id').val()+'_'+getTimeFormat();
                        formData.append('create_by',create_by);

                    }
                }
                if (related == 3) { // Yêu cầu sửa chữa
                    if($('#user_request_5').val()){
                        create_by = 'request_'+$('#user_request_5').val()+'_'+getTimeFormat();
                        formData.append('create_by',create_by);
                    }
                }
            }
            __repeat_week = $('#repeat_week').val().map(Number);
            __date_repeat_of_month = $('#date_repeat_of_month').val().map(Number);
            __repeat_month = $('#repeat_month').val().map(Number);
            if($('#type_task').val() == 'repeat_week'){ // tạo công việc lặp lại theo tuần
                let repeat_week = $('#repeat_week').val();

                if (repeat_week.length > 0) {
                    let formCreate ={
                        title:form_task.get('title'),
                        priority:form_task.get('priority'),
                        assigned:assigned,
                        checklist_id:[check_list.data.id],
                        category_task_id:form_task.get('category_task_id'),
                        department_id:department_id,
                        desc:form_task.get('desc'),
                        assigned_monitor:assigned_monitor,
                        create_by:create_by,
                        date_time:form_task.get('date_time'),
                        attack:attach_file
                    }
                    console.log(formCreate)

                    formDataSchedule.append('minute',0);
                    formDataSchedule.append('hour',0);
                    formDataSchedule.append('day_of_week',$('#choose_all_week').is(':checked') ? '["*"]' : JSON.stringify(__repeat_week));
                    formDataSchedule.append('month','["*"]');
                    formDataSchedule.append('date','["*"]');
                    formDataSchedule.append('year','["*"]');
                    formDataSchedule.append('task',JSON.stringify(formCreate));
                    formDataSchedule.append('permission',JSON.stringify(permission));
                    if($('#id_task_schedule').val()){
                        formDataSchedule.append('id',$('#id_task_schedule').val());
                    }
                    let task_repeat = await call_api_form_data(method,'admin/task/addTaskSchedule'+param_query,formDataSchedule);
                    $('.add-task').prop('disabled',false);
                    if(task_repeat.status == true){
                        toastr.success(task_repeat.mess);
                        setTimeout(function(){
                            location.href = '/admin/work-diary-v2#workdiary_repeat';
                        }, 1000);
                    }else {
                        toastr.error(task_repeat.mess);
                    }
                }else {
                    alert('Bạn chưa chọn ngày lặp lại trong tuần')
                }

            }else if($('#type_task').val() == 'repeat_month'){ // tạo công việc lặp lại theo tháng
                let date_repeat_of_month = $('#date_repeat_of_month').val();
                let repeat_month = $('#repeat_month').val();
                if (repeat_month.length > 0 && date_repeat_of_month.length > 0) {
                    let formCreate ={
                        title:form_task.get('title'),
                        priority:form_task.get('priority'),
                        assigned:assigned,
                        checklist_id:[check_list.data.id],
                        category_task_id:form_task.get('category_task_id'),
                        department_id:department_id,
                        desc:form_task.get('desc'),
                        assigned_monitor:assigned_monitor,
                        create_by:create_by,
                        date_time:form_task.get('date_time'),
                        attack:attach_file
                    }
                    console.log('date_repeat_of_month',$('#date_repeat_of_month').val())

                    formDataSchedule.append('minute',0);
                    formDataSchedule.append('hour',0);
                    formDataSchedule.append('day_of_week','["*"]');
                    formDataSchedule.append('month',$('#choose_date_repeat_of_month').is(':checked') ? '["*"]' : JSON.stringify(__date_repeat_of_month));
                    formDataSchedule.append('date',$('#choose_all_month').is(':checked') ? '["*"]' : JSON.stringify(__repeat_month));
                    formDataSchedule.append('year','["*"]');
                    formDataSchedule.append('task',JSON.stringify(formCreate));
                    formDataSchedule.append('permission',JSON.stringify(permission));
                    if($('#id_task_schedule').val()){
                        formDataSchedule.append('id',$('#id_task_schedule').val());
                    }
                    console.log('formDataSchedule',Object.fromEntries(formDataSchedule))
                    let task_repeat = await call_api_form_data(method,'admin/task/addTaskSchedule'+param_query,formDataSchedule);
                    $('.add-task').prop('disabled',false);
                    if(task_repeat.status == true){
                        toastr.success(task_repeat.mess);

                        setTimeout(function(){
                            location.href = '/admin/work-diary-v2#workdiary_repeat';
                        }, 1000);
                    }else {
                        toastr.error(task_repeat.mess);
                    }
                }else {
                     alert('Bạn chưa chọn ngày hoặc tháng lặp lại')
                }

            }else {
                //validate
                check = validate_input(format_date_no_time(form_task.get('start_date')),'C_BIRTHDAY','Vui lòng chọn Ngày bắt đầu');
                if(check == false){
                    $(this).prop('disabled',false);
                    return;
                }
                check = validate_input(format_date_no_time(form_task.get('end_date')),'C_BIRTHDAY','Vui lòng chọn Ngày kết thúc');
                if(check == false){
                    $(this).prop('disabled',false);
                    return;
                }
                start_date = new Date(format_date_no_time(form_task.get('start_date')));
                end_date = new Date(format_date_no_time(form_task.get('end_date')));
                if(start_date > end_date){
                    alert('Ngày bắt đầu không được lớn hơn ngày kết thúc.')
                    $(this).prop('disabled',false);
                    return;
                }
                console.log('formDataSchedule____',Object.fromEntries(formData))
                let task = await call_api_form_data(method,'admin/task/addTask'+param_query,formData);
                $('.add-task').prop('disabled',false);
                if(task.status == true){
                    toastr.success(task.mess);
                    setTimeout(function(){
                        location.href = '/admin/work-diary-v2';
                    }, 1000);
                }else {
                    toastr.warning(task.mess);
                }
            }
            $('.add-task').prop('disabled',false);
        }
        $('.add-task').prop('disabled',false);
    }

    $('#choose_all_week').change(function (){
        if($(this).is(':checked')){
            $('#repeat_week').select2('destroy').find('option').prop('selected', 'selected').end().select2();
        }else {
            $('#repeat_week').select2('destroy').find('option').prop('selected', false).end().select2();
        }
    })
    $('#related').change(function (){
        $('.asset_detail').hide();
        $('.feedback').hide();
        $('.user_request_5').hide();
        if ($(this).val() == 1) { // Ý kiến cư dân
            $('.asset_detail').hide();
            $('.feedback').show();
            $('.user_request_5').hide();
        }
        if ($(this).val() == 2) { // Bảo trì tài sản
            $('.asset_detail').show();
            $('.feedback').hide();
            $('.user_request_5').hide();
        }
        if ($(this).val() == 3) { // Yêu cầu sửa chữa
            $('.asset_detail').hide();
            $('.feedback').hide();
            $('.user_request_5').show();
        }
    });
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
   function updatedata() {
        var bdc_department_id = $("#officeinspection").val()
        $.ajax({
        url: '{{ url('admin/service-apartment/ajaxGetSelectInspecter')}}',
        dataType: 'json',
        data: { id: bdc_department_id },
        success: function(response) {
            $('#officeinspector').empty();
        for (var i = 0; i < response.length; i++) {
            var inspecter = response[i];
            console.log(response[i].name);
            var option = '<option value="">' + inspecter.name + '</option>';
            $('#officeinspector').append(option);
            }
        }
    });
}
document.addEventListener('DOMContentLoaded', onPageLoaded);

function onPageLoaded() {
    updatedata();
}

</script>
@endsection