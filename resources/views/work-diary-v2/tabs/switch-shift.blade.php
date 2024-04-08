<div id="switch_shift" class="tab-pane">
    <div class="row form-group">
                <form action="" method="get">
{{--                    <div class="col-sm-2 change_department">--}}
{{--                        <select name="department_id" class="form-control input-sm department_id" id="change_department" style="width: 100%">--}}
{{--                            <option value="">Bộ phận</option>--}}
{{--                        </select>--}}
{{--                    </div>--}}
                    <div class="col-sm-2">
                        <select name="type" class="form-control" id="request_type">
                            <option value="1" selected>Yêu cầu chuyển ca</option>
                            <option value="2" >Yêu cầu duyệt</option>
                        </select>
                    </div>
                    <div class="col-sm-2">
                        <select name="status" class="form-control" id="request_status">
                            <option value="" selected>Trạng thái</option>
                            <option value="0">Chờ xác nhận</option>
                            <option value="1" >Chấp nhận</option>
                            <option value="2" >Không chấp nhận</option>
                        </select>
                    </div>
                </form>
    </div>
    <!-- /.box-header -->
    <form action="" method="post">
        <table class="table table-hover">
            <thead>
                <tr class="bg-primary">
                    <th width='20px'>STT</th>
                    <th >Yêu cầu</th>
                    <th >Tên công việc</th>
                    <th >Ngày gửi</th>
                    <th >Người gửi</th>
                    <th >Lý do</th>
                    <th >Trạng thái</th>
                    <th >Thao tác</th>
                </tr>
            </thead>
            <tbody id="list_table_getListReqAssigner">
            </tbody>
        </table>
        <div class="clearfix"></div>
        <div class="row">
            <div class="col-sm-3">
            </div>
            <div class="col-sm-6 text-center">
                <div id="pagination_list_table_getListReqAssigner"></div>
            </div>
        </div>
    </form>
    <input type="hidden" value="{{$request_task}}" id="request_task">
    <div class="modal fade" id="showConfigRequest" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" style="padding-right:0px;" aria-hidden="true">
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
                    <h5 class="modal-title" style="margin-top: 2px;">Xác nhận yêu cầu</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="margin-top: -20px;margin-right: 10px;">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" style="padding:0;">
                    <form class="form-horizontal" action="" method="POST" id="form_confirm_request_switch_shift">
                        {{ csrf_field() }}
                        <input type="hidden" name="building_id" value="{{ @$building_id }}">
                        <input type="hidden" name="type" id="type_switch_shift">
                        <input type="hidden" name="request_id" id="request_switch_shift_id">
                        <div class="box-body">
                            <div class="form-group" style="padding: 0 45px;">
                                <div>
                                    <label for="recipient-name" class="control-label"><span style="color:red;font-size: 18px;">*</span>Lý do xác nhận yêu cầu:</label>
                                </div>
                                <input type="text" name="name" class="form-control desc_confirm"  value="">
                            </div>
                            <div class="form-group" style="padding: 0 45px;">
                                <div><b>Người thực hiện</b></div>
                                <select class="form-control" name="assigners[]" style="width: 100%" id="assigned_request" multiple> </select>
                            </div>
                            <div class="form-group" style="padding: 0 45px;">
                                <div><b>Người giám sát</b></div>
                                <select name="assigned_monitor[]" class="form-control" style="width: 100%" id="assigned_monitor_request"></select>
                            </div>
                            <div class="row" style="padding: 0 45px;">
                                <div class="col-sm-4">
                                </div>
                                <div class="col-sm-4">
                                    <div class="row">
                                        <button type="button" class="btn btn-primary confirm_request_approve">Đồng ý</button>
                                        <button type="button" class="btn btn-danger confirm_request_return">Từ chối</button>
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
        <div class="modal fade" id="showConfigRequestApprove" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" style="padding-right:0px;" aria-hidden="true">
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
                        <h5 class="modal-title" style="margin-top: 2px;">Xác nhận yêu cầu</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="margin-top: -20px;margin-right: 10px;">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body" style="padding:0;">
                        <form class="form-horizontal" action="" method="POST" id="form_confirm_request_approve">
                            {{ csrf_field() }}
                            <input type="hidden" name="building_id" value="{{ @$building_id }}">
                            <input type="hidden" name="type" id="type_approve">
                            <input type="hidden" name="request_id" id="request_approve_id">
                            <div class="box-body">
                                <div class="form-group" style="padding: 0 45px;">
                                    <div>
                                        <label for="recipient-name" class="control-label"><span style="color:red;font-size: 18px;">*</span>Lý do xác nhận yêu cầu:</label>
                                    </div>
                                    <input type="text" name="name" class="form-control desc_confirm"  value="">
                                </div>
                                <div class="row" style="padding: 0 45px;">
                                    <div class="col-sm-4">
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="row">
                                            <button type="button" class="btn btn-primary confirm_request_approve">Đồng ý</button>
                                            <button type="button" class="btn btn-danger confirm_request_return">Từ chối</button>
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
</div>
<script>
    let object_list_switch_shift = null;
     param_query_old = "{{ $array_search }}";
     param_query_old = param_query_old.replaceAll("&amp;", "&")
     param_query = param_query_old
     var per_t_bql = null
     list_all_users =null
     permission_user = null
     _assigned = null
   $(document).ready(function() {
       getList()
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
           }
       }
       if ($('#get_permission_by_user').val()) {
           permission_user = JSON.parse($('#get_permission_by_user').val());
           permission_user.permission.forEach(item => {
               if (item.type_manager == 1) {
                   per_t_bql = item.type_manager;
               }
               if (item.type == 1) {
                   per_t_bp = item.type;
               }
           });

       }

       $('#assigned_monitor_request').select2({data: data_users});
       $('#assigned_monitor_request').select2();
       $('#change_department').change(function (){
           getList()
       })
   })

    $('#request_type').change(function (){
        getList()
    })
    $('#request_status').change(function (){
        getList()
    })
    function getList(){
        param_query = param_query_old
        if($('#request_type').val()){
            param_query +='&type='+$('#request_type').val();
        }
        if($('#request_status').val()){
            param_query +='&status='+$('#request_status').val();
        }
        if($('#change_department').val()){
            param_query +='&department_id='+$('#change_department').val();
        }
        $('#pagination_list_table_getListReqAssigner').pagination({
            dataSource:  window.localStorage.getItem("base_url")+'admin/task/getListReqAssigner' + param_query,
            locator: 'data',
            totalNumberLocator: function(response) {
                return 10
            },
            alias: {
                pageNumber: 'page',
                pageSize: 'limit'
            },
            pageSize: 10,
            ajax: {
                beforeSend: function() {
                    $('#list_table').html('Loading data ...');
                }
            },
            callback: function(data, pagination) {
                $('#list_table_getListReqAssigner').html('');
                if(data){
                    let stt=0;
                    object_list_switch_shift = data;
                    html='';
                    data.forEach(element => {
                        stt++;
                        let create_date = format_date(element.created_at);
                        list_all_users = JSON.parse($('#permission_task_user').val());
                        let user=null;
                        if (list_all_users.length > 0) {
                            user = list_all_users.filter(item=>item.user_id == element.user_id)
                            if(user.length > 0){
                                user = user[0];
                            }
                        }
                        if($('#request_task').val()){
                            request_task  = JSON.parse($('#request_task').val());
                            request_task = request_task.filter((v,i)=>i == element.status)
                            // console.log(request_task)
                        }
                        let status = '<td></td>'
                        if(element.status == 0){
                            status ='<td>'+
                                '        <a data-element="'+element.id+'" data-task_id="'+element.task_id+'" data-type="'+element.type+'"'+
                                '        class="btn btn-xs btn-primary" onclick="editMethodSwitchShift(this)" title="Sửa thông tin"><i'+
                                '        class="fa fa-pencil"></i></a>'+
                                '    </td>'
                        }
                        html+= '<tr>'+
                            '    <td>'+stt+'</td>'+
                            '    <td>'+(element.type == 1 ? 'Yêu cầu chuyển ca' : 'Yêu cầu duyệt')+'</td>'+
                            '    <td>'+element.title+'</td>'+
                            '    <td>'+create_date+'</td>'+
                            '    <td>'+(user ? user.full_name:'')+'</td>'+
                            '    <td>'+element.reason+'</td>'+
                            '    <td>'+(request_task ? request_task[0] : '')+'</td>'+status+'</tr>';
                    });
                    $('#list_table_getListReqAssigner').append(html);
                }
            }
        })
    }
    async function editMethodSwitchShift(event){
        type = $(event).data('type');
        id = $(event).data('element');
        task_id = $(event).data('task_id');
        task_shift = await getTaskDetail(task_id);
        if(task_shift.status == true){

            task_shift = task_shift.data.task;
            assigned  = JSON.parse(task_shift.assigned);
            assigned_monitor = JSON.parse(task_shift.assigned_monitor);
            department_id = JSON.parse(task_shift.department_id);
            var data_users = [];
            for (let index = 0; index < list_all_users.length; index++) {
                var user = list_all_users[index];
                if(user.permission){
                    user.permission.forEach(element => {
                        if(department_id){
                            department_id.forEach(item => {
                                if (element.bdc_department_id == item) {
                                    data_users.push({
                                        id: user.user_id,
                                        text: user.full_name
                                    })
                                }
                            });
                        }
                    })

                }
            }

            $('#assigned_request').select2({data: data_users});
            $('#assigned_request').select2();
            $('#assigned_request').val(assigned).change();
            _assigned = assigned;
            $('#assigned_monitor_request').val(assigned_monitor).change();
            if(type == 1){
                $('#type_switch_shift').val(type);
                $('#request_switch_shift_id').val(id);
                $('#showConfigRequest').modal('show');
            }else {
                $('#type_approve').val(type);
                $('#request_approve_id').val(id);
                $('#showConfigRequestApprove').modal('show');
            }
            console.log(task_shift);
        }

    }
   // $('#list_table_getListReqAssigner').on('click','.edit_method_switch_shift',function (e) {
   //      e.preventDefault();
   //      type = $(this).data('type');
   //      id = $(this).data('element');
   //
   // });

   $('#form_confirm_request_switch_shift .confirm_request_approve').click(function (){
       let param_query_old = "{{ $array_search }}";
       let param_query = param_query_old.replaceAll("&amp;", "&")
       let assigners =  null;
       if($('#assigned_request').val()){
           assigners = $('#assigned_request').val().map(Number);
       }
       if(assigners.length == 0){
           alert('không được trống nhân viên thực hiện');
            $('#assigned_request').val(_assigned).change();
           return
       }
       if(!$('#form_confirm_request_switch_shift .desc_confirm').val()){
           alert('Vui lòng nhập nội dung xác nhận');
           return
       }
       data ={
           request_change_assigner_id: $('#request_switch_shift_id').val(),
           reason:$('#form_confirm_request_switch_shift .desc_confirm').val(),
           status:1, // đồng ý
           assigned_monitor:JSON.stringify([assigned_monitor]),
           assigners:JSON.stringify(assigners),
           type:1,
       }
       postMethod('admin/task/confirmReqChangeAssigner'+param_query,data);
   })
    $('#form_confirm_request_switch_shift .confirm_request_return').click(function (){
        let param_query_old = "{{ $array_search }}";
        let param_query = param_query_old.replaceAll("&amp;", "&")
        if(!$('#form_confirm_request_switch_shift .desc_confirm').val()){
            alert('Vui lòng nhập nội dung xác nhận');
            return
        }
        data ={
            request_change_assigner_id: $('#request_switch_shift_id').val(),
            reason:$('#form_confirm_request_switch_shift .desc_confirm').val(),
            status:2, // từ chối
            assigned_monitor:JSON.stringify([]),
            assigners:JSON.stringify([]),
            type:1,
        }
        postMethod('admin/task/confirmReqChangeAssigner'+param_query,data);
    })
    $('#form_confirm_request_approve .confirm_request_approve').click(function (){
        let param_query_old = "{{ $array_search }}";
        let param_query = param_query_old.replaceAll("&amp;", "&")
        if(!$('#form_confirm_request_approve .desc_confirm').val()){
            alert('Vui lòng nhập nội dung xác nhận');
            return
        }
        data ={
            request_change_assigner_id: $('#request_approve_id').val(),
            reason:$('#form_confirm_request_approve .desc_confirm').val(),
            status:1, // đồng ý
            type:2,
        }
        postMethod('admin/task/confirmReqApprove'+param_query,data);
    })

    $('#form_confirm_request_approve .confirm_request_return').click(function (){
        let param_query_old = "{{ $array_search }}";
        let param_query = param_query_old.replaceAll("&amp;", "&")
        if(!$('#form_confirm_request_approve .desc_confirm').val()){
            alert('Vui lòng nhập nội dung xác nhận');
            return
        }
        data ={
            request_change_assigner_id: $('#request_approve_id').val(),
            reason:$('#form_confirm_request_approve .desc_confirm').val(),
            status:2, // từ chối
            type:2,
        }
        postMethod('admin/task/confirmReqApprove'+param_query,data);
    })
   async function getTaskDetail(task_id){
       let building_id = "{{ $building_id }}";
       return await call_api('get','admin/task/getDetailTask?building_id='+building_id+'&id='+task_id);
    }

</script>