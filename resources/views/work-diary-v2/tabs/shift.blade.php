<div id="quan_ly_ca" class="tab-pane">
    <div class="row form-group">
            <div class="col-sm-8">
                <form action="" method="get">
                    <div class="col-sm-6">
                        <input type="text" name="category_name" class="form-control" placeholder="Nhập nội dung tìm kiếm" value="">
                    </div>
                    <div class="col-sm-1">
                        <button class="btn btn-info"><i class="fa fa-search"></i> Tìm</button>
                    </div>
                </form>
            </div>
            <div class="col-sm-4">
                <button class="btn btn-info show-add-shift"><i class="fa fa-plus"></i>THÊM CA</button>
            </div>
    </div>
    <!-- /.box-header -->
    <form action="" method="post">
        <table class="table table-striped table-bordered table-hover">
            <thead>
                <tr class="bg-primary">
                    <th width='20px'>STT</th>
                    <th width='30%'>Tên ca</th>
                    <th width='30%'>Thời gian</th>
                    <th width='20%'>Ngày tạo</th>
                    <th width='10%'>Thao tác</th>
                </tr>
            </thead>
            <tbody id="list_method_shift">
            </tbody>
        </table>
        <div class="clearfix"></div>
        <div class="row">
            <div class="col-sm-3">
            </div>
            <div class="col-sm-6 text-center">
                <div id="pagination-panel-shift-workdiary"></div>
            </div>
        </div>
    </form>
@include('work-diary-v2.model.createshift')

<script>
   let object_list_shift = null;
   $(document).ready(function() {
       let param_query_old = "{{ $array_search }}";
       let param_query = param_query_old.replaceAll("&amp;", "&")
       $('#pagination-panel-shift-workdiary').pagination({
           dataSource:  window.localStorage.getItem("base_url")+'admin/workTime/getListWorkTime' + param_query,
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
                   $('#list_method_shift').html('Loading data ...');
               }
           },
           callback: function(data, pagination) {
               if(data){
                   let html ='';
                   let stt=0;
                   object_list_shift = data;
                   data.forEach(element => {
                       stt++;
                       html+= '<tr>'+
                           '    <td>'+stt+'</td>'+
                           '    <td>'+element.name+'</td>'+
                           '    <td>'+element.from+' -> '+element.to+'</td>'+
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
                   $('#list_method_shift').html(html);  
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
   $('.export_asset').click(function (e) { 
       e.preventDefault();
       export_excel();
   });
   $('#list_method_shift').on('click','.edit_method_shift', function (e) {
       e.preventDefault();
       let id = $(this).data('element');
       if(id){
            object_list_shift.forEach(element => {
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

   $('.show-add-shift').click(function (e) { 
        e.preventDefault();
        $('#form-shift')[0].reset();
        $('#createShift').modal('show');
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