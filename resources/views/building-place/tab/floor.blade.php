<div id="tang" class="tab-pane">
    <div class="col-sm-8">
        <div class="row">
            <table class="table table-hover table-striped table-bordered">
                <thead class="bg-primary">
                    <tr>
                        <th>STT</th>
                        <th>Tên tầng</th>
                        <th>Tòa</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody id="list_method">
                    
                </tbody>
            </table>
            <div class="row mbm">
                <div class="col-sm-3">
                </div>
                <div class="col-sm-6 text-center">
                    <div id="pagination-panel"></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-4" style="border-left: 1px solid #e8e8e8;">
        <div class="box-header">
            <h3 class="box-title">Thêm tầng</h3>
        </div>
        <br><br>
        <div class="form-horizontal">
            <form class="form-horizontal" method="post" id="create_method">
                <input type="hidden" name="building_id" value="{{ @$building_id }}">
                <input type="hidden" name="id" id="method_id">
                <div class="form-group div_bank_name">
                    <label class="col-md-4">Chọn tòa</label>
                    <div class="col-md-8">
                        <select name="place_id" id="place_id" class="form-control" style="width: 100%">
                            @foreach ($buildingPlacesAll as $key => $value)
                                <option value="{{ $value->id }}"> {{ $value->name }}</option>
                            @endforeach
                        </select>
                        <div class="message_zone"></div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-4">Tên tầng</label>
                    <div class="col-md-8">
                        <input type="text" class="form-control" id="name_floor" name="name">
                        <div class="message_zone"></div>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-md-6 col-md-offset-4">
                        <button type="submit" class="btn btn-primary" id="add_method">
                            <i class="fa fa-btn fa-check"></i> cập nhập
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@section('javascript')
<script>
      var object_list_method = null;
        $(document).ready(function() {
            let param_query_old = "{{ $array_search }}";
            let param_query = param_query_old.replaceAll("&amp;", "&")
            $('#pagination-panel').pagination({
                dataSource:  window.localStorage.getItem("base_url")+'admin/floor/getListFloor' + param_query,
                locator: 'data.list',
                totalNumberLocator: function(response) {
                    return response.data.list.length
                },
                alias: {
                    pageNumber: 'page',
                    pageSize: 'limit'
                },
                pageSize: 10,
                ajax: {
                    beforeSend: function() {
                        $('#list_method').html('Loading data ...');
                    }
                },
                callback: function(data, pagination) {
                    if(data){
                        let html ='';
                        let stt=0;
                        object_list_method = data;
                        data.forEach(element => {
                            stt++;
                            let abc = JSON.stringify(element);
                            html+= '<tr>'+
                                   '    <td>'+stt+'</td>'+
                                   '    <td>'+element.name+'</td>'+
                                   '    <td>'+element.place_name+'</td>'+
                                   '    <td>'+
                                   '        <a data-element="'+element.id+'"'+
                                   '        class="btn btn-xs btn-primary edit_method" title="Sửa thông tin"><i'+
                                   '                    class="fa fa-pencil"></i></a>'+
                                   '        <a data-element="'+element.id+'" class="btn btn-xs btn-danger delete_edit_method"'+
                                   '        title="Xóa thông tin"><i class="fa fa-trash"></i></a>'+
                                   '    </td>'+
                                   '</tr>';
                        });
                        $('#list_method').html(html);  
                    }
                }
            })
           
        })
        $('#list_method').on('click','.edit_method', function (e) {
            e.preventDefault();
            let id = $(this).data('element');
            if(id){
                object_list_method.forEach(element => {
                     if(element.id == id){
                         $('#place_id').val(element.place_id).change();
                         $('#name_floor').val(element.name);
                         $('#method_id').val(element.id);
                     }
                });
            }
        });
        $('#list_method').on('click','.delete_edit_method', function (e) {
            e.preventDefault();
            let id = $(this).data('element');
            if(id){
                postDel(id);
            }
        });
        $('#add_method').click(function (e) { 
            e.preventDefault();
            if($('#method_id').val()){ // update
                var form_data = $('#create_method').serializeArray();
                postMethod(form_data);
            }else{
                var form_data = $('#create_method').serializeArray();
                postMethod(form_data);
            }
        });
        async function postMethod(param) {
            let method='post';
            let _result = await call_api(method, 'admin/floor/addFloor',param);
            toastr.success(_result.mess);
            setTimeout(function(){
                location.reload();
            }, 1000);
        }
        async function postDel(id) {
            let method='post';
            let param_query_old = "{{ $array_search }}";
            let param_query = param_query_old.replaceAll("&amp;", "&")
            param_query +="&id="+id;
            console.log(param_query);
            let _result = await call_api(method, 'admin/floor/delFloor'+param_query);
            toastr.success(_result.mess);
            setTimeout(function(){
                location.reload();
            }, 1000);
        }
</script>
@endsection