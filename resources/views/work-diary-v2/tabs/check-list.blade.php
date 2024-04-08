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
            <button class="btn btn-info show-add-check-list"><i class="fa fa-plus"></i>Thêm Checklist</button>
        </div>
    </div>
    <!-- /.box-header -->
    <form action="" method="post" >
        <table class="table table-striped table-bordered table-hover">
            <thead>
                <tr class="bg-primary">
                    <th width='1%'>STT</th>
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
@include('work-diary-v2.model.check-list')
@include('work-diary-v2.model.addtemplatetask')
<style>
    .form_add_checklist{
        max-height: 550px;
        overflow-y: auto;
        overflow-x: hidden;
    }
</style>
<script type="text/javascript">
    var count_checklist = 0;
    var count_valueCheckList = 0;
    var id_remove_checklist=[];
    $('.add-value-checklist').click(function(e){
         e.preventDefault();
         count_checklist++;
         var html = '<div class="form-group detail_checklist" data-id="'+count_checklist+'">'+
                    '<hr/ style="margin: 10px 0;">'+
                    '   <div class="col-sm-10" style="top: 12px;">'+
                    '       <div ><label>Checklist '+count_checklist+': </label></div>'+
                    '       <label class="control-label" class="control-label">Tiêu đề:</label>'+
                    '       <input type="hidden" name="sub_checklist_id">'+
                    '       <input type="hidden" name="sub_checklist_sort" value="'+count_checklist+'">'+
                    '       <div style="width: 100%;display: flex">'+
                    '           <textarea class="form-control" style="resize: vertical;" name="sub_checklist_title" rows="1"></textarea>'+
                    '       </div>'+
                    '       <label class="control-label" class="control-label">Mô tả:</label>'+
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
                        '            <label class="checkbox" style="margin-left: 50px">'+
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
    function copyCheckList(event){

    }
    function deleteCheckList(event,temp=null){
        if(temp == 2){
            $(event).parent().parent().remove();
        }else{
            count_checklist--;
            let id = $(event).parent().parent().find('input[name=sub_checklist_id]').val();
            id_remove_checklist.push(id);
            $(event).parent().parent().remove();
            // if(id){
            //     $(event).parent().parent().remove();
            //     postDel('admin/task/delTaskFormCheckListDetail',id,false);
            // }
        }
    }
    function copyValueCheckList(event){

    }
    function deleteValueCheckList(event){
         $(event).parent().parent().remove();
        // let id = $(event).parent().parent().find('input[name=checklist_id]').val();
        // alert(id)
        // if(id){
        //    postDel('admin/task/delTaskFormCheckListDetail',id,false);
        // }
    }
   
   let object_check_list = null;
   $(document).ready(function() {
       let param_query_old = "{{ $array_search }}";
       let param_query = param_query_old.replaceAll("&amp;", "&")
       param_query += "&type=1";
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
                       //let checklist_id = "{{ route('admin.work-diary-v2.detailCheckList') }}"+'?form_checklist_id=['+element.id+']';
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
                           '        <a data-element="'+element.id+'" data-element_title="'+element.title+'"'+
                           '        class="btn btn-xs btn-primary edit_method_checklist" title="Sửa thông tin"><i'+
                           '                    class="fa fa-pencil"></i></a>'+
                           '        <a data-element="'+element.id+'" class="btn btn-xs btn-danger delete_method_checklist"'+
                           '        title="Xóa thông tin"><i class="fa fa-trash"></i></a>'+
                           '    </td>'+
                          
                           '</tr>';
                   });
                   $('#list_check_list').html(html);  
               }
           }
       })
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
                    data: function(params) {
                        var query = {
                            search: params.term,
                        }
                        return query;
                    },
                    processResults: function(json, params) {
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
        console.log($(event).attr("data-id"));
        window.location.href= `https://bdcadmin.s-tech.info/admin/work-diary-v2/changestatus?id=${$(event).attr("data-id")}&status=${status}`;
   }
   $('.export_asset').click(function (e) { 
       e.preventDefault();
       export_excel();
   });
   $('#list_check_list').on('click','.edit_method_checklist', function (e) {
       e.preventDefault();
       let id = $(this).data('element');
       let title = $(this).data('element_title');
       console.log(id);
       if(id){
            get_detail(id,title);
       }
   });
   async function get_check_list_detail(id) {
        let method = 'get';
        let param_query_old = "{{ $array_search }}";
        let param_query = param_query_old.replaceAll("&amp;", "&")
        param_query += "&form_checklist_id=["+id+"]";
        var detail_checklist = await call_api(method, 'admin/task/getListFormCheckListDetail' + param_query);
        if(detail_checklist){
            $(".subtemp_title_task_template").html('');
            count_check_list = 0
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
                            '       <div ><label>Checklist '+count_check_list+': </label></div>'+
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
    async function get_detail(id,title) {
        let method = 'get';
        let param_query_old = "{{ $array_search }}";
        let param_query = param_query_old.replaceAll("&amp;", "&")
        param_query += "&form_checklist_id=["+id+"]";
        var detail_checklist = await call_api(method, 'admin/task/getListFormCheckListDetail' + param_query);
        if(detail_checklist){
            count_checklist=0;
            $(".list_detail_checklists").html('');
            $("#checklist_id").val(id);
            $("#checklist_title").val(title);
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
                                '            <label class="checkbox" style="margin-left: 50px">'+
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
            $('#createCheckList').modal('show');
        }
        console.log(detail_checklist);
   }
   $('.show-add-check-list').click(function (e) {
        e.preventDefault();
        $('#form-check-list')[0].reset();
        $(".list_detail_checklists").html('');
        $('#createCheckList').modal('show');
   });

   // $('.add_checklist_from_tempalte').click(function (e){
   //     $('#createCheckList').modal('hide');
   //     $('#addtemplatetotask').modal('show');
   // })
   $('#list_check_list').on('click','.delete_method_checklist', function (e) {
       e.preventDefault();
       let id = $(this).data('element');
       if(id){
           postDel('admin/task/delFormCheckList',id);
       }
   });
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
            $(element).find('.detail_value').each(function(index_1,element_1) {
                count_checklist++;
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
                            '       <div ><label>Checklist '+count_checklist+': </label></div>'+
                            '       <label class="control-label" class="control-label">Tiêu đề:</label>'+
                            '       <input type="hidden" name="sub_checklist_id">'+
                            '       <input type="hidden" name="sub_checklist_sort" value="'+count_checklist+'">'+
                            '       <div style="width: 100%;display: flex">'+
                            '           <textarea class="form-control" style="resize: vertical;" name="sub_checklist_title" rows="1">'+sub_checklist_title.val()+'</textarea>'+
                            '       </div>'+
                            '       <label class="control-label" class="control-label">Mô tả:</label>'+
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
                                '            <label class="checkbox" style="margin-left: 50px">'+
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
           $('#addtemplatetotask').modal('hide');
        });
   });
   $('.add_check_list').click(function (e) { 
       e.preventDefault();
       let param_query_old = "{{ $array_search }}";
       let param_query = param_query_old.replaceAll("&amp;", "&")
       var form_data = new FormData($('#form-check-list')[0]);
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
        });
       if(list.length == 0){
           alert('Vui lòng nhập checklist mẫu');
           _check_validate_checklist =true;
       }
       if (_check_validate_checklist == true)
       {
           return;
       }

        // var formCreate = new FormData();
        // formCreate.append('id', $("#checklist_id").val());
        // formCreate.append('title',form_data.get('title'));
        // formCreate.append('list', JSON.stringify(list));
        // if(!formCreate.get('title') || !formCreate.get('list')){
        //     toastr.warning('bạn chưa nhập thông tin');
        //     return false;
        // }
       check = validate_input(form_data.get('title'),"REQUIRE", 'Vui lòng nhập tên checklist');
       if(check == false){
           return;
       }

       const body = {
           id:  $("#checklist_id").val(),
           title:form_data.get('title'),
           list: JSON.stringify(list),
       }

       if(id_remove_checklist.length > 0){
           id_remove_checklist.forEach(item=>{
               postDelNoConfirm('admin/task/delTaskFormCheckListDetail',item,false);
           })
       }
        if($("#checklist_id").val()){
            postMethod('admin/task/updateListFormCheckListDetail'+param_query,body);
        }else{
            postMethod('admin/task/addListFormCheckListDetail'+param_query,body);
        }
   });
   $(document).ready(function () {
        $( ".list_detail_checklists" ).sortable({
            items: ".detail_checklist",
            cursor: 'move',
            opacity: 0.6,
            disabled: false,
            update: function() {
                sendOrderToServer();
            }
        });         
     }); 
     function sendOrderToServer() {
        $('.list_detail_checklists .detail_checklist').each(function(index,element) {
             $(element).find('input[name=sub_checklist_sort]').val(index+1);
        });
     }       
</script>