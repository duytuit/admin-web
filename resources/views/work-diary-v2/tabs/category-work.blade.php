<div id="danh_muc" class="tab-pane">
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
                <button class="btn btn-info show-add-cat-workdiary"><i class="fa fa-plus"></i>THÊM DANH MỤC CÔNG VIỆC</button>
            </div>
    </div>
    <!-- /.box-header -->
    <form action="" method="post">
        <table class="table table-hover">
            <thead>
                <tr class="bg-primary">
                    <th width='20px'>STT</th>
                    <th >Tên danh mục công việc</th>
                    <th >Trạng thái</th>
                    <th >Ngày tạo</th>
                    <th >Thao tác</th>
                </tr>
            </thead>
            <tbody id="list_method_cat_workdiary">
            </tbody>
        </table>
        <div class="clearfix"></div>
        <div class="row">
            <div class="col-sm-3">
            </div>
            <div class="col-sm-6 text-center">
                <div id="pagination-panel-cat-workdiary"></div>
            </div>
        </div>
    </form>
</div>
@include('work-diary-v2.model.createcategory')

<script>
    let object_list_cat_workdiary= null;
   $(document).ready(function() {
       let param_query_old = "{{ $array_search }}";
       let param_query = param_query_old.replaceAll("&amp;", "&")
       $('#pagination-panel-cat-workdiary').pagination({
           dataSource:  window.localStorage.getItem("base_url")+'admin/task/getListCateTask' + param_query,
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
                   $('#list_method_cat_workdiary').html('Loading data ...');
               }
           },
           callback: function(data, pagination) {
               if(data){
                   let html ='';
                   let stt=0;
                   object_list_cat_workdiary = data;
                   data.forEach(element => {
                       stt++;
                       let abc = JSON.stringify(element);
                       let status = element.status == 1 ? '<span class="label labela-success" style="background-color:#9788C3">Hoạt động</span>' : ' <span class="label labela-success" style="background-color:#104aec">Ngừng</span>';
                       let create_date = format_date(element.created_at);
                       html+= '<tr>'+
                           '    <td>'+stt+'</td>'+
                           '    <td>'+element.name+'</td>'+
                           '    <td>'+status+'</td>'+
                           '    <td>'+create_date+'</td>'+
                           '    <td>'+
                           '        <a data-element="'+element.id+'"'+
                           '        class="btn btn-xs btn-primary edit_method_cat_workdiary" title="Sửa thông tin"><i'+
                           '                    class="fa fa-pencil"></i></a>'+
                           '    </td>'+
                           '</tr>';
                   });
                   $('#list_method_cat_workdiary').html(html);  
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
   $('#list_method_cat_workdiary').on('click','.edit_method_cat_workdiary', function (e) {
       e.preventDefault();
       let id = $(this).data('element');
       if(id){
        object_list_cat_workdiary.forEach(element => {
               if(element.id == id){
                   $('#cat_workdiary_id').val(element.id);
                   $('#category_workdiary_name').val(element.name);
                   $('#cat_workdiary_status').prop('checked', element.status == 1 ? true : false);
               }
           });
       }
       $('#createCategory').modal('show');
   });

   $('#list_method_cat_workdiary').on('click','.delete_edit_method_cat_workdiary', function (e) {
       e.preventDefault();
       let id = $(this).data('element');
       if(id){
           data={
               id:id
           }
           postDel('admin/asset/delAssetDetail',id,data);
       }
   });
   $('.add-category-workdiary').click(function (e) { 
       e.preventDefault();
       var formCreate = new FormData($('#form-category-workdiary')[0]);
        if($('#cat_workdiary_status').is(':checked')){
        }else{
            formCreate.append('status', 0);
        }
        let param_query_old = "{{ $array_search }}";
        let param_query = param_query_old.replaceAll("&amp;", "&")
         data ={
             id:formCreate.get('id'),
             name:formCreate.get('name'),
             status:formCreate.get('status')
         }
         postMethod('admin/task/addCateTask'+param_query,data);
   });

</script>