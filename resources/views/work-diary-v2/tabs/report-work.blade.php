<div id="danh_sach_cong_viec" class="tab-pane active">
    <div class="box-header with-border">
        <div class="box-body">
            <div class="row" style="margin-left: -30px">
                <div class="col-md-2 col-sm-6 col-xs-12">
                    <div class="info-box" onclick="getStatus(this,'count_total')">
                        <span class="info-box-icon bg-danger"></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Tất cả</span>
                            <span class="info-box-number" id="count_total">0</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-2 col-sm-6 col-xs-12" >
                    <div class="info-box"  onclick="getStatus(this,5)">
                        <span class="info-box-icon bg-blue"></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Đang Làm</span>
                            <span class="info-box-number" id="dang_lam">0</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-2 col-sm-6 col-xs-12">
                    <div class="info-box" onclick="getStatus(this,3)">
                        <span class="info-box-icon bg-aqua"></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Chưa thực hiện</span>
                            <span class="info-box-number"  id="chua_thuc_hien">0</span>
                        </div>
                    </div>
                </div>
                <div class="clearfix visible-sm-block"></div>
                <div class="col-md-2 col-sm-6 col-xs-12">
                    <div class="info-box" onclick="getStatus(this,1)">
                        <span class="info-box-icon bg-green"></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Đã hoàn thành</span>
                            <span class="info-box-number" id="da_hoan_thanh">0</span>
                        </div>
                    </div>
                </div>
                <div class="clearfix visible-sm-block"></div>
                <div class="col-md-2 col-sm-6 col-xs-12">
                    <div class="info-box" onclick="getStatus(this,4)">
                        <span class="info-box-icon bg-yellow"></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Quá hạn</span>
                            <span class="info-box-number" id="">0</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-2 col-sm-6 col-xs-12">
                    <div class="info-box" onclick="getStatus(this,0)">
                        <span class="info-box-icon bg-red"></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Đã hủy</span>
                            <span class="info-box-number" id="da_huy">0</span>
                        </div>
                    </div>
                </div>

            </div>
            <div class="row">
                <form action="{{ route('admin.work-diary-v2.search_workdiary') }}" method="get">
                    <div id="search-advance" class="search-advance">
                        <div class="row form-group">
                            <div class="col-sm-6">
                                <input type="text" name="task_name" class="form-control task_name" placeholder="Nhập tên công việc" value="">
                            </div>
                            <div class="col-sm-3">
                                <select name="department_id[]" class="form-control input-sm select2 department_id" multiple  style="width: 100%">
                                    <option value="">Bộ phận</option>
                                </select>
                            </div>
                            <div class="col-sm-3">
                                <select name="user_id[]"  class="form-control select2 user_id" multiple style="width: 100%">
                                    <option value="">Nhân viên</option>
                                </select>
                            </div>
                        </div>
                        <div class="row form-group">
                            <div class="col-sm-3">
                                <div class="input-group date">
                                    <div class="input-group-addon">
                                        <i class="fa fa-calendar"></i>
                                    </div>
                                    <input type="text" class="form-control date_picker start_date" name="start_date" value="" placeholder="từ ngày...">
                                </div>
                            </div>
                            <div class="col-sm-3">
                                <div class="input-group date">
                                    <div class="input-group-addon">
                                        <i class="fa fa-calendar"></i>
                                    </div>
                                    <input type="text" class="form-control date_picker to_date" name="end_date" value="" placeholder="đến ngày...">
                                </div>
                            </div>
                            <div class="col-sm-2">
                                <a class="btn btn-info search-work-diary" onclick="searchTask(this)"><i class="fa fa-search"></i>Tìm</a>
                            </div>
                            <div class="col-sm-2">
                                <a href="{{ route('admin.work-diary-v2.create') }}" class="btn btn-info" ><i class="fa fa-plus" style="margin-left: 8px;"></i>TẠO CÔNG VIỆC</a>
                            </div>
                            <div class="col-sm-2">
                                <a href="{{route('admin.work-diary-v2.exportExcel',Request::all())}}" class="btn btn-success">Xuất ra excel</a>
                            </div>
                        </div>
                    </div>
                    <input type="hidden" name="bdc_department_id" value="" >
                </form>
            </div>
        </div>
    </div>
    <!-- /.box-header -->
    <form action="" method="post">
        {{ csrf_field() }}
        <table class="table table-hover">
            <thead>
                <tr>
                    <th width="1%">STT</th>
                    <th width='15%'>Tên công việc</th>
                    <th width='10%'>Danh mục</th>
                    <th width='10%'>TG bắt đầu</th>
                    <th width='10%'>TG kết thúc</th>
                    <th width='10%'>Bộ phận</th>
                    <th width='10%'>Trạng thái</th>
                    <th width='10%'>Người thực hiện</th>
                    <th width='10%'>Thao tác</th>
                </tr>
            </thead>
            <tbody id="list_method_work">
                
            </tbody>
        </table>
        <div class="clearfix"></div>
        <div class="row">
            <div class="col-sm-3">
            </div>
            <div class="col-sm-6 text-center">
                <div id="pagination-panel-work"></div>
            </div>
        </div>
    </form>

</div>
<input type="hidden" value="{{$status_task_html}}" id="all_status_task_html">
<input type="hidden" value="{{$permission_task_user}}" id="permission_task_user">
<input type="hidden" value="{{@$TaskCategory}}" id="taskCategory">
<input type="hidden" value="{{$get_permission_by_user}}" id="get_permission_by_user">
@include('work-diary-v2.model.detailwork')
<style>
    .info-box-content {
        margin: 0;
        text-align: center;
    }
    div.info-box {
        border-top: 1px solid #e7e7e7;
        border-left: 1px solid #e7e7e7;
        border-right: 1px solid #e7e7e7;
        border-bottom: none;
    }
    .info-box {
        min-height: 54px;
        cursor: pointer;

    }
    .info-box-icon {
        height: 49px;
        width: 40px;
    }
    .info-box-content {
        padding: 0;
    }
    .info-box:after {
        display      : block;
        content      : '';
        border-bottom: 3px solid #169fee;
        height: 0;
        transform    : scaleX(0);
        transition   : transform 300ms ease-in-out;
        transform-origin: 0 50%;
        border-top: 1px solid #169fee;
        border-left: 1px solid #169fee;
        border-right: 1px solid #169fee;
    }
    .info-box:hover:after {
        transform: scaleX(1);
    }
    .info-box-text {
        text-transform: initial;
    }
</style>
<script>
    $('input.date_picker').datepicker({
        autoclose: true,
        dateFormat: "dd-mm-yy"
    }).val();
     let object_list_method = null;
     var object_permission_task_user = null;
     var object_departments = null;
     var count_total =0;
    let param_query_old = "{{ $array_search }}";
    let param_query_curent = param_query_old.replaceAll("&amp;", "&")
    param_query = param_query_curent;
    permission_user = null;
    $(document).ready(function() {
        // if($('#taskCategory').val()){
        //     taskCategory = JSON.parse($('#taskCategory').val())
        //     _object_cate = [];
        //     taskCategory.forEach((v,i)=>{
        //         _object_cate.push({
        //             id: v.id,
        //             text: v.name
        //         })
        //     })
        //     $('.task_category_id').select2({data: _object_cate});
        //
        // }
        if ($('#get_permission_by_user').val()) {
            permission_user = JSON.parse($('#get_permission_by_user').val());

            console.log(permission_user)
        }
        if($('#permission_task_user').val()){
            object_permission_task_user = JSON.parse($('#permission_task_user').val());

            var data_users = [];
            for (let index = 0; index < object_permission_task_user.length; index++) {
                var user = object_permission_task_user[index];
                data_users.push({
                    id: user.user_id,
                    text: user.full_name
                })
            }
            // $('.user_id').select2({data: data_users});
             $('.user_id').val(['']).change();
        }
        object_departments = JSON.parse(window.localStorage.getItem("departments"));

        if(object_departments){
            let list_department = [];
            object_departments.forEach((item) => {
                list_department.push({
                    id: item.id,
                    text: item.name
                })
            })
            $('.department_id').select2({data: list_department});
            $('.department_id').val(['']).change();
        }
        getCount();
        $('.department_id').change(function (){
            department_id = $(this).val().filter(function(e) { return e !== '' })
            if(department_id.length > 0){
                var data_users = [];
                for (let index = 0; index < object_permission_task_user.length; index++) {
                    var user = object_permission_task_user[index];
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
                $('.user_id').select2({data: data_users});
                $('.user_id').select2();
            }
        })
    })
    async function getCount(){
        await  getStatusTask();
        await  getStatusTask_4();
        await  getStatusTask_6()
        console.log('count_total',count_total)
        $('#count_total').text(count_total);
        const obj = document.getElementById("count_total");
        animateValue(obj, 0, count_total, 500);
        $('#pagination-panel-work').pagination({
            dataSource:  window.localStorage.getItem("base_url")+'admin/task/getListTask' + param_query,
            locator: 'data',
            totalNumberLocator: function(response) {
                return count_total
            },
            alias: {
                pageNumber: 'page',
                pageSize: 'limit'
            },
            pageSize: 10,
            ajax: {
                beforeSend: function() {
                    $('#list_method_work').html('Loading data ...');
                }
            },
            callback: function(data, pagination) {
                if(data){
                    let html ='';
                    let stt=0;
                    object_list_method = data;
                    let object_all_status_task = JSON.parse($('#all_status_task_html').val());
                    data.forEach(element => {
                        stt++;
                        let list_users = '';
                        assigned =JSON.parse(element.assigned)
                        if(assigned.length > 0){
                            assigned.forEach(item=>{
                                user = object_permission_task_user.filter(v=>v.user_id == item)
                                if(user.length > 0){
                                    list_users += '<span tooltip="'+user[0]?.full_name+'" style="display: inline-block;"><img src="'+(user[0].avatar ? user[0].avatar : '/adminLTE/img/user-default.png')+'" class="img-circle img-sm" alt="'+user[0]?.full_name+'" /></span>'
                                }
                            })
                        }
                        let status = '';
                        let per_edit_delete ='';
                        Object.entries(object_all_status_task).forEach(([key, val]) => {
                            if(key == element.status){
                                status = val;
                            }
                        })
                        permission_owner = null;
                        permission_member = null;
                        permission_view = null;
                        if(element.permission){
                            try {
                                permission = JSON.parse(element.permission);
                                permission_owner = permission.permission_owner;
                                permission_member = permission.permission_member;
                                permission_view = permission.permission_view;
                            } catch (e) {
                                return false;
                            }
                        }
                            if(element.status == 3){
                                if(permission_owner){
                                    permission_owner.forEach(item=>{
                                        if(item == permission_user.user_id){
                                            per_edit_delete =   '        <a data-element="'+element.id+'"'+
                                                '       href="/admin/work-diary-v2/create?id='+element.id+'"  class="btn btn-xs btn-primary edit_task" title="Sửa thông tin"><i'+
                                                '                    class="fa fa-pencil"></i></a>'+
                                                '        <a data-element="'+element.id+'" class="btn btn-xs btn-danger delete_task"'+
                                                '        title="Xóa thông tin"><i class="fa fa-trash"></i></a>';
                                        }
                                    })
                                }
                            }

                            let departments='';

                            department_id = JSON.parse(element.department_id);
                            if (department_id.length > 0) {
                                department_id.forEach((item) => {
                                    department= object_departments.filter(v=>v.id == item)
                                    if(department.length >0){
                                        departments += '<div><span>' + department[0]?.name + '</span></div>'
                                    }
                                    permission_user.permission.forEach(item_2=>{

                                        if(item_2.bdc_department_id == item){
                                            if(element.status == 3){
                                                if(item_2.type == 1 || item_2.type_manager == 1){
                                                    per_edit_delete =   '        <a data-element="'+element.id+'"'+
                                                        '       href="/admin/work-diary-v2/create?id='+element.id+'"  class="btn btn-xs btn-primary edit_task" title="Sửa thông tin"><i'+
                                                        '                    class="fa fa-pencil"></i></a>'+
                                                        '        <a data-element="'+element.id+'" class="btn btn-xs btn-danger delete_task"'+
                                                        '        title="Xóa thông tin"><i class="fa fa-trash"></i></a>';
                                                }
                                            }
                                        }

                                    })
                                })
                            }

                        if(element.status == 0 || element.status == 1){
                            per_edit_delete='';
                        }
                        category='';
                        _taskCategory = JSON.parse($('#taskCategory').val());
                        if(_taskCategory.length >0){
                            category_detail = _taskCategory.filter(v=>v.id == element.category_task_id)
                            if(category_detail.length > 0){
                                category = category_detail[0].name
                            }
                        }
                        // service_apartment = departments.filter(o => o.id == );
                        html+= '<tr>'+
                            '    <td>'+stt+'</td>'+
                            '    <td>'+element.title+'</td>'+
                            '    <td>'+category+'</td>'+
                            '    <td>'+format_date(element.start_date)+'</td>'+
                            '    <td>'+format_date(element.end_date)+'</td>'+
                            '    <td>'+departments+'</td>'+
                            '    <td>'+status+'</td>'+
                            '    <td>'+list_users+'</td>'+
                            '    <td>'+
                            '        <a data-element="'+element.id+'"'+
                            '      target="_blank" href="/admin/work-diary-v2/detail?id='+element.id+'" class="btn btn-xs btn-primary" title="Chi tiết công việc"><i'+
                            '                    class="fa fa-align-left"></i></a>'+per_edit_delete+
                            '    </td>'+
                            '</tr>';
                    });
                    $('#list_method_work').html(html);
                }
            }
        })
    }
     async function getStatusTask() {
         let method = 'get';
         let building_id = "{{ $building_id }}";
         _result = await call_api(method, 'admin/task/countTask?building_id=' + building_id)
         console.log('_result',_result)
         if(_result.status == true){
             _result.data.forEach(item =>{
                 if(item.status == 0){
                      $('#da_huy').text(item.count);
                    // count_total+=item.count;
                     const obj = document.getElementById("da_huy");
                     animateValue(obj, 0, item.count, 500);
                 }
                 if(item.status == 1){
                     $('#da_hoan_thanh').text(item.count);
                     const obj = document.getElementById("da_hoan_thanh");
                     animateValue(obj, 0, item.count, 500);
                 }
                 if(item.status == 3){
                     $('#chua_thuc_hien').text(item.count);
                     count_total+=item.count;
                     const obj = document.getElementById("chua_thuc_hien");
                     animateValue(obj, 0, item.count, 500);
                 }
                 if(item.status == 5){
                     $('#dang_lam').text(item.count);
                     count_total+=item.count;
                     const obj = document.getElementById("dang_lam");
                     animateValue(obj, 0, item.count, 500);
                 }
             })
         }
     }
     async function getStatusTask_4() {
         let method = 'get';
         let building_id = "{{ $building_id }}";
         _result = await call_api(method, 'admin/task/countTask?building_id=' + building_id+'&status=4')
         if(_result.status == true){
             _result.data.forEach(item =>{
                 if(item.status == 4){
                     $('#qua_han').text(item.count);
                     const obj = document.getElementById("qua_han");
                     animateValue(obj, 0, item.count, 500);
                 }
             })
         }
     }
     async function getStatusTask_6() {
         let method = 'get';
         let building_id = "{{ $building_id }}";
         _result = await call_api(method, 'admin/task/countTask?building_id=' + building_id+'&status=6')
         if(_result.status == true){
             _result.data.forEach(item =>{
                 if(item.status == 6){
                     $('#cho_giam_sat_duyet').text(item.count);
                     const obj = document.getElementById("cho_giam_sat_duyet");
                     animateValue(obj, 0, item.count, 500);
                 }
             })
         }
     }
     function animateValue(obj, start, end, duration) {
         let startTimestamp = null;
             const step = (timestamp) => {
                 if (!startTimestamp) startTimestamp = timestamp;
                 const progress = Math.min((timestamp - startTimestamp) / duration, 1);
                     obj.innerHTML = Math.floor(progress * (end - start) + start);
                 if (progress < 1) {
                     window.requestAnimationFrame(step);
                 }
             };
             window.requestAnimationFrame(step);
     }
    function getStatus(event,status){
        count_total=0;
        param_query = param_query_curent;
        if(status == 'count_total'){
            param_query+='&status='

        }
        if(status == '0'){
            param_query+='&status=[0]'
        }
        if(status == '1'){
            param_query+='&status=[1]'
        }
        if(status == '3'){
            param_query+='&status=[3]'
        }
        if(status == '4'){
            param_query+='&status=[4]'
        }
        if(status == '5'){
            param_query+='&status=[5]'
        }
        if(status == '6'){
            param_query+='&status=[6]'
        }
        $('.info-box').each(function (){
             $(this).css({"height": "", "border-bottom": ""})
        })
        $(event).css({"height": "0", "border-bottom": "4px solid #169fee"});
        console.log('param_query',param_query)
        console.log('count_total',count_total)
        getCount();
    }

    function searchTask(event){
        count_total=0;
        param_query = param_query_curent;

        param_query += '&manager=1';

        console.log('param_query_curent',param_query_curent)
        department_id = $('.department_id').val().filter(function(e) { return e !== '' })
        user_id = $('.user_id').val().filter(function(e) { return e !== '' })
        if(department_id.length > 0){
            if(user_id.length > 0){
                user_id = user_id.map(Number);
            }
            department_id = department_id.map(Number);
            param_depart=[];
            param_depart.push({
                department:department_id,
                user_id:user_id
            })
            param_query+='&department_id='+JSON.stringify(param_depart);

        }
        // if(user_id.length > 0){
        //     user_id = user_id.map(Number);
        //     param_query+='&assigned='+JSON.stringify(user_id);
        // }
        if($('.task_name').val()){
            task_name = $('.task_name').val()
        }
        if($('.start_date').val() && $('.to_date').val()){
            start_date = format_date_no_time($('.start_date').val());
            to_date = format_date_no_time($('.to_date').val());
            param_query+='&start_date='+start_date+'&to_date='+to_date;
        }
        // if(status == 'count_total'){
        //     param_query+='&status='
        // }
        // if(status == '0'){
        //     param_query+='&status=[0]'
        // }
        // if(status == '1'){
        //     param_query+='&status=[1]'
        // }
        // if(status == '3'){
        //     param_query+='&status=[3]'
        // }
        // if(status == '4'){
        //     param_query+='&status=[4]'
        // }
        // if(status == '5'){
        //     param_query+='&status=[5]'
        // }
        // if(status == '6'){
        //     param_query+='&status=[6]'
        // }
        console.log('count_total',count_total)
        getCount();
    }
    async function export_excel() {
        let method = 'get';
        let param_query_old = "{{ $array_search }}";
        let param_query = param_query_old.replaceAll("&amp;", "&")
        var headers = null;
        var export_excel = await call_api_export(method, 'admin/asset/expListAssetDetail' + param_query)
        var blob = new Blob(
                [export_excel],
                {type:export_excel.type}
            );
        const url = URL.createObjectURL(blob)
        const link = document.createElement('a')
        link.download = 'ket_qua_export';
        link.href = url
        document.body.appendChild(link)
        link.click()
        document.body.removeChild(link);
    }
    $('.export_asset').click(function (e) { 
        e.preventDefault();
        export_excel();
    });

    $('#list_method_work').on('click','.delete_task', function (e) {
        e.preventDefault();
        let id = $(this).data('element');
        if(id){
            let data ={
                id:id
            }
            postDel('admin/task/delTask',null,true,data);
        }
    });
    $('.btn-js-action-add-asset').click(function (e) { 
        e.preventDefault();
        var form_data = $('#form-add-asset').serializeArray();
        postMethod('admin/asset/addAssetDetail',form_data);
    });

</script>