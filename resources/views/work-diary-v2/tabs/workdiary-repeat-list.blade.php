<div id="workdiary_repeat" class="tab-pane">
    <div class="row form-group">
        <div class="col-sm-8">
            <form action="" method="get">
                <div class="col-sm-6">
                    <input type="text" name="category_name" class="form-control" placeholder="Nhập nội dung tìm kiếm" value="">
                </div>
                <div class="col-sm-1">
                    <button class="btn btn-info"><i class="fa fa-search"></i>Tìm</button>
                </div>
            </form>
        </div>
        <div class="col-sm-4">
            <a href="{{ route('admin.work-diary-v2.create') }}" class="btn btn-info" ><i class="fa fa-plus" style="margin-left: 8px;"></i>TẠO CÔNG VIỆC</a>
        </div>
    </div>
    <!-- /.box-header -->
    <form action="" method="post" >
        <table class="table table-striped table-bordered table-hover">
            <thead>
                <tr class="bg-primary">
                    <th width='20px'>STT</th>
                    <th width='30%'>Tên công việc</th>
                    <th width='15%'>Trạng thái</th>
                    <th width='20%'>Ngày tạo</th>
                    <th width='20%'>Người tạo</th>
                    <th width='15%'>Thao tác</th>
                </tr>
            </thead>
            <tbody id="list_table">
            </tbody>
        </table>
        <div class="clearfix"></div>
        <div class="row">
            <div class="col-sm-3">
            </div>
            <div class="col-sm-6 text-center">
                <div id="pagination_list_table"></div>
            </div>
        </div>
    </form>
</div>
<script>
    let object_list_cat_check_list = null;
   $(document).ready(function() {
       let param_query_old = "{{ $array_search }}";
       let param_query = param_query_old.replaceAll("&amp;", "&")
       $('#pagination_list_table').pagination({
           dataSource:  window.localStorage.getItem("base_url")+'admin/task/getListTaskSchedule' + param_query,
           locator: 'data.list',
           totalNumberLocator: function(response) {
               return response.data.count
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
               if(data){
                   let html ='';
                   let stt=0;
                   object_list_cat_check_list = data;
                   data.forEach(element => {
                       stt++;
                       let task = JSON.parse(element.data);
                       let status = element.status == 1 ? "checked" : "";
                       let create_date = format_date(element.created_at);
                       list_all_users = JSON.parse($('#permission_task_user').val());
                       let user=null;
                       if (list_all_users.length > 0) {
                           user_id = element.by.indexOf('user_') > -1 ? element.by.replace("user_",''):'';
                           user = list_all_users.filter(item=>item.user_id == user_id)
                           if(user.length > 0){
                               user = user[0];
                           }
                       }
                       html+= '<tr>'+
                           '    <td>'+stt+'</td>'+
                           '    <td>'+task.title+'</td>'+
                           '    <td>'+
                           '      <div class="onoffswitch">'+
                           '          <input type="checkbox" name="onoffswitch" class="onoffswitch-checkbox" data-id="'+element.id+'"'+
                           '          id="myonoffswitch_'+element.id+'" onclick="changeStatusTaskSchedule(this)" '+status+' >'+
                           '          <label class="onoffswitch-label" for="myonoffswitch_'+element.id+'">'+
                           '          <span class="onoffswitch-inner"></span>'+
                           '          <span class="onoffswitch-switch"></span>'+
                           '     </label>'+
                           '        </div>'+
                           '    </td>'+
                           '    <td>'+create_date+'</td>'+
                           '    <td>'+(user ? user.full_name:'')+'</td>'+
                           '    <td>'+
                           '        <a data-element="'+element.id+'"'+
                           '       href="/admin/work-diary-v2/create?task_schedule_id='+element.id+'" class="btn btn-xs btn-primary edit_schedule" title="Sửa thông tin"><i'+
                           '                    class="fa fa-pencil"></i></a>'+
                           '    </td>'+
                           '</tr>';
                   });
                   $('#list_table').html(html);
               }
           }
       })
     
   })
   $('.show-add-cat-workdiary').click(function (e) { 
        e.preventDefault();
        $('#form-category-workdiary')[0].reset();
        $('#createCategory').modal('show');
   });
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
   $('#list_method_cat_check_list').on('click','.edit_method_cat_workdiary', function (e) {
       e.preventDefault();
       let id = $(this).data('element');
       if(id){
           object_list_method.forEach(element => {
               if(element.id == id){
                   $('#method_asset_id').val(element.id);
                   $('#title-asset').val(element.name);
                   $('#type_maintain').val(element.type_maintain).change();
                   $('#maintain_time').val(element.maintain_time);
                   $('#asset_note').val(element.desc);
                   $('#asset_category_id').append($('<option>', {
                       value: element.asset_category_id,
                       text: element.name_cate,
                       selected:"selected"
                   }));
                   $('#asset_category_id').val(element.asset_category_id).change();
                   $('.select2-selection__rendered').text(element.name_cate);
               }
           });
       }
       $('#createCategory').modal('show');
   });

   $('#list_method_cat_check_list').on('click','.delete_edit_method_cat_workdiary', function (e) {
       e.preventDefault();
       let id = $(this).data('element');
       if(id){
           postDel('admin/asset/delAssetDetail',id);
       }
   });
   $('.add-category-workdiary').click(function (e) { 
       e.preventDefault();
       var form_data = $('#form-category-workdiary').serializeArray();
       postMethod('admin/task/addCateTask',form_data);
   });
    function changeStatusTaskSchedule(event) {
        let status = 0;
        let id = $(event).data('id');
        if($(event).is(":checked")) // nếu tích
        {
            status = 1;
        }
        //  $(event).parent().parent().remove();
        let param_query_old = "{{ $array_search }}";
        let param_query = param_query_old.replaceAll("&amp;", "&")
        let data={
            id: id,
            status: status
        }
        postMethod('admin/task/updateStatusSchedule' + param_query, data);

    }
</script>