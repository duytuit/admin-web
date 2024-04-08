<div id="check_list" class="tab-pane">
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
            <button class="btn btn-info show-add-check-list"><i class="fa fa-plus"></i>THÊM CHECK LIST MẪU</button>
        </div>
    </div>
    <!-- /.box-header -->
    <form action="" method="post" >
        <table class="table table-striped table-bordered table-hover">
            <thead>
                <tr class="bg-primary">
                    <th width='20px'>STT</th>
                    <th width='30%'>Tiêu đề</th>
                    <th width='10%'>Trạng thái</th>
                    <th width='20%'>Ngày tạo</th>
                    <th width='10%'>Thao tác</th>
                </tr>
            </thead>
            <tbody id="list_check_list">
            </tbody>
        </table>
        <div class="box-footer clearfix"></div>
        <div class="row">
            <div class="col-sm-3">
            </div>
            <div class="col-sm-6 text-center">
                <div id="pagination-panel-check-list"></div>
            </div>
        </div>
    </form>
</div>
@include('work-diary-v2.model.detail-check-list')
<script type="text/javascript">

    $('.add-subtemp-sample').click(function(e){
         e.preventDefault();
         var title = $("textarea[name='sub_task_title']").val();
         var description = $("textarea[name='sub_task_description']").val();

         var html = '<div class="row list-subtemp"><div class="col-sm-10 body-sutemp" style="top: 12px;"><div style="font-weight: bold;"><div class="title-label"><i class="fa fa-angle-double-right" style="font-weight: bold;margin-right: 10px;"></i><span class="title-span">'+title+
         '</span></div></div><div style="margin-left: 22px;"><div class="description-label"><i class="fa fa-file-text-o" style="color:green;margin-right: 5px;"></i><span class="description-span">'+description+
         '</span></div></div></div><div class="col-sm-2 control-subtemp" style="display: inline-flex;top: 15px;"><div class="btn btn-edit " style="padding: 5px;margin-right: 10px;">'+
         '<span style="font-size: 22px;font-weight: bold;"><i class="fa fa-edit" style="color: #0475d6;"></i></span></div><div class="btn btn-remove " style="padding: 4px;">'+
         '<span style="font-size: 22px;font-weight: bold;"><i class="fa fa-trash" style="color: red;"></i></span></div></div></div>';

        $(".subtemp").append(html);

        $("textarea[name='sub_task_title']").val('');
        $("textarea[name='sub_task_description']").val('');
    });
    
   
    $(".subtemp").on("click", ".btn-remove", function(){
        $(this).parents(".list-subtemp").remove();
    });
    
    $(".subtemp").on("click", ".btn-edit", function(){
        var title = $(this).parents(".list-subtemp").find(".title-span:eq(0)").text();
        var description = $(this).parents(".list-subtemp").find(".description-span:eq(0)").text();

        $(this).parents(".list-subtemp").find(".body-sutemp:eq(0)").css("display","inline-flex");
    
        $(this).parents(".list-subtemp").find(".title-label:eq(0)").html('<input class="form-control" name="edit_title" value="'+title+'">');

        $(this).parents(".list-subtemp").find(".description-label:eq(0)").html('<input class="form-control" name="edit_description" value="'+description+'">');
    
        $(this).parents(".list-subtemp").find(".control-subtemp:eq(0)").html('<div class="btn btn-update" style="padding: 5px;margin-right: 10px;">'+
         '<span style="font-size: 22px;font-weight: bold;"><i class="fa fa-save" style="color: #0475d6;"></i></span></div><div class="btn btn-cancel"style="padding: 4px;">'+
         '<span style="font-size: 22px;font-weight: bold;"><i class="fa fa-hand-stop-o" style="color: red;"></i></span></div>')
        

        $(this).hide();
    });
   
    $(".subtemp").on("click", ".btn-cancel", function(){
        var title = $(this).parents(".list-subtemp").find(".title-span:eq(0)").text();
        var description = $(this).parents(".list-subtemp").find(".description-span:eq(0)").text();

        $(this).parents(".list-subtemp").find(".body-sutemp:eq(0)").css("display","block");
    
        $(this).parents(".list-subtemp").find(".title-label:eq(0)").html('<i class="fa fa-angle-double-right" style="font-weight: bold;margin-right: 10px;"></i><span class="title-span">'+title+'</span>');

        $(this).parents(".list-subtemp").find(".description-label:eq(0)").html('<i class="fa fa-file-text-o" style="color:green;margin-right: 5px;"></i><span class="description-span">'+description+'</span>');

        $(this).parents(".list-subtemp").find(".control-subtemp:eq(0)").html('<div class="btn btn-edit" style="padding: 5px;margin-right: 10px;">'+
         '<span style="font-size: 22px;font-weight: bold;"><i class="fa fa-edit" style="color: #0475d6;"></i></span></div><div class="btn btn-remove"style="padding: 4px;">'+
         '<span style="font-size: 22px;font-weight: bold;"><i class="fa fa-trash" style="color: red;"></i></span></div>')

        $(this).hide();
    });
   
    $(".subtemp").on("click", ".btn-update", function(){
        var title = $(this).parents(".list-subtemp").find("input[name='edit_title']").val();
        var description = $(this).parents(".list-subtemp").find("input[name='edit_description']").val();

        $(this).parents(".list-subtemp").find(".body-sutemp:eq(0)").css("display","block");

        $(this).parents(".list-subtemp").find(".title-label:eq(0)").html('<i class="fa fa-angle-double-right" style="font-weight: bold;margin-right: 10px;"></i><span class="title-span">'+title+'</span>');
        $(this).parents(".list-subtemp").find(".description-label:eq(0)").html('<i class="fa fa-file-text-o" style="color:green;margin-right: 5px;"></i><span class="description-span">'+description+'</span>');
     
        
        $(this).parents(".list-subtemp").find(".control-subtemp:eq(0)").html('<div class="btn btn-edit" style="padding: 5px;margin-right: 10px;">'+
         '<span style="font-size: 22px;font-weight: bold;"><i class="fa fa-edit" style="color: #0475d6;"></i></span></div><div class="btn btn-remove"style="padding: 4px;">'+
         '<span style="font-size: 22px;font-weight: bold;"><i class="fa fa-trash" style="color: red;"></i></span></div>')
        $(this).hide();
    });
   let object_check_list = null;
   $(document).ready(function() {
       let param_query_old = "{{ $array_search }}";
       let param_query = param_query_old.replaceAll("&amp;", "&")
       $('#pagination-panel-check-list').pagination({
           dataSource:  window.localStorage.getItem("base_url")+'admin/task/getListFormCheckList' + param_query,
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
                   $('#list_check_list').html('Loading data ...');
               }
           },
           callback: function(data, pagination) {
               if(data){
                   let html ='';
                   let stt=0;
                   object_check_list = data;
                   data.forEach(element => {
                       stt++;
                       let status = element.status == 1 ? "checked" : "";
                       html+= '<tr>'+
                           '    <td>'+stt+'</td>'+
                           '    <td>'+element.title+'</td>'+
                           '<td>'+
                           '  <div class="onoffswitch">'+
                           '     <input type="checkbox" name="onoffswitch" class="onoffswitch-checkbox" data-id="'+element.id+'"'+
                           '     id="myonoffswitch_'+element.id+'" onclick="changeStatus(this)" '+status+' >'+
                           '     <label class="onoffswitch-label" for="myonoffswitch_'+element.id+'">'+
                           '         <span class="onoffswitch-inner"></span>'+
                           '         <span class="onoffswitch-switch"></span>'+
                           '     </label>'+
                           '  </div>'+
                           '</td>'+
                           '    <td>'+format_date(element.created_at)+'</td>'+
                           '    <td>'+
                           '        <a data-element="'+element.id+'"'+
                           '        class="btn btn-xs btn-primary edit_method_shift" title="Sửa thông tin"><i'+
                           '                    class="fa fa-pencil"></i></a>'+
                           '        <a data-element="'+element.id+'" class="btn btn-xs btn-danger delete_edit_method_shift"'+
                           '        title="Xóa thông tin"><i class="fa fa-trash"></i></a>'+
                           '    </td>'+
                          
                           '</tr>';
                   });
                   $('#list_check_list').html(html);  
               }
           }
       })
     
   })
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
   function changeStatus(event) {
        let status = 0;
        if($(event).is(":checked")) // nếu tích
        {
            status = 1;
        }
        
   }
   $('.export_asset').click(function (e) { 
       e.preventDefault();
       export_excel();
   });
   $('#list_method_shift').on('click','.edit_method_shift', function (e) {
       e.preventDefault();
       let id = $(this).data('element');
       if(id){
        object_check_list.forEach(element => {
               if(element.id == id){
                   $('#shift_id').val(element.id);
                   $('#name-shift').val(element.name);
                   $('#start_time').val(element.from);
                   $('#end_time').val(element.to);
               }
           });
       }
       $('#createShift').modal('show');
   });

   $('.show-add-check-list').click(function (e) { 
        e.preventDefault();
        $('#form-tempsample')[0].reset();
        $('#createTempSample').modal('show');
   });

   $('#list_method_shift').on('click','.delete_edit_method_shift', function (e) {
       e.preventDefault();
       let id = $(this).data('element');
       if(id){
           postDel('admin/workTime/delWorkTime',id);
       }
   });
   $('.add-shift').click(function (e) { 
       e.preventDefault();
       var form_data = $('#form-shift').serializeArray();
       postMethod('admin/workTime/addWorkTime',form_data);
   });
</script>