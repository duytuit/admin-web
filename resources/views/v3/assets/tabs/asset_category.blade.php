<div class="tab-pane" id="asset_category" style="padding: 15px 0;">
    <div class="panel panel-default">
        <div class="panel-body">
            <div class="box box-primary">
                <div class="box-body ">
                    <div class="row">
                        {{-- <div class="col-sm-1">
                            <button type="button" data-toggle="dropdown"
                                class="btn btn-primary dropdown-toggle pull-left" style="margin-right: 10px;">Tác vụ&nbsp;<span class="caret"></span></button>
                            <ul class="dropdown-menu">
                                <li>
                                    <a type="button" class="btn-action deleteAll">
                                        <i class="fa fa-trash text-danger"></i>&nbsp; Xóa
                                    </a>
                                </li>
                            </ul>
                        </div> --}}
                        <form id="form-search-asset-category" action="" method="get">
                            <div id="search-advance" class="search-advance">
                                <div class="col-sm-2">
                                    <a class="btn btn-success" title="Thêm thẻ" data-toggle="modal"
                                        data-target="#add-cat-asset"><i class="fa fa-plus"></i>&nbsp;&nbsp;Thêm danh mục</a>
                                </div>
                                <div class="col-sm-10">
                                    <div class="form-group space-5" style="/*width: calc(100% - 55px);*/float: left;">
                                        <div class="col-sm-12">
                                            <input type="text" class="form-control" name="keyword_asset_category"
                                                placeholder="Nhập từ khóa tìm kiếm"
                                                value="">
                                        </div>
                                    </div>
                                    <div>
                                        <button type="submit" title="Tìm kiếm" class="btn btn-info"><i class="fa fa-search"></i>Tìm</button>
                                    </div>
                                </div>
                            </div>
                        </form><!-- END #form-search-advance -->
                    </div>
                        <table class="table table-hover table-striped table-bordered">
                            <thead class="bg-primary">
                                <tr>
                                    <th class="text-center" width="30"> STT</th>
                                    <th width="140">ID</th>
                                    <th width="140">Danh mục</th>
                                    <th width="100">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody id="list_method_cat_asset">
                    
                            </tbody>
                        </table>
                        <div class="row mbm">
                            <div class="col-sm-3">
                            </div>
                            <div class="col-sm-6 text-center">
                                <div id="pagination-panel-cat-asset"></div>
                            </div>
                        </div>
                </div>
            </div>
        </div>
    </div>
    @include('v3.assets.modals.add-asset-category')
</div>
<script>
    let object_list_cat_asset_method = null;
    $(document).ready(function() {
        let param_query_old = "{{ $array_search }}";
        let param_query = param_query_old.replaceAll("&amp;", "&")
        $('#pagination-panel-cat-asset').pagination({
            dataSource:  window.localStorage.getItem("base_url")+'admin/asset/getListAssetCate' + param_query,
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
                    $('#list_method_cat_asset').html('Loading data ...');
                }
            },
            callback: function(data, pagination) {
                if(data){
                    let html ='';
                    let stt=0;
                    object_list_cat_asset_method = data;
                    data.forEach(element => {
                        stt++;
                        let abc = JSON.stringify(element);
                        html+= '<tr>'+
                            '    <td>'+stt+'</td>'+
                            '    <td>'+element.id+'</td>'+
                            '    <td>'+element.title+'</td>'+
                            '    <td>'+
                            '        <a data-element="'+element.id+'"'+
                            '        class="btn btn-xs btn-primary edit_method" title="Sửa thông tin"><i'+
                            '                    class="fa fa-pencil"></i></a>'+
                            '        <a data-element="'+element.id+'" class="btn btn-xs btn-danger delete_edit_method"'+
                            '        title="Xóa thông tin"><i class="fa fa-trash"></i></a>'+
                            '    </td>'+
                            '</tr>';
                    });
                    $('#list_method_cat_asset').html(html);  
                }
            }
        })
    
    })
    $('#list_method_cat_asset').on('click','.edit_method', function (e) {
        e.preventDefault();
        let id = $(this).data('element');
        if(id){
            object_list_cat_asset_method.forEach(element => {
                if(element.id == id){
                    $('#method_cat_asset_id').val(element.id);
                    $('#title-asset-category').val(element.title);
                }
            });
        }
        $('#add-cat-asset').modal('show');
    });
    $('#list_method_cat_asset').on('click','.delete_edit_method', function (e) {
        e.preventDefault();
        let id = $(this).data('element');
        if(id){
            postDel(id);
        }
    });
    $('.btn-js-action-add-asset-category').click(function (e) { 
        e.preventDefault();
        var form_data = $('#form-add-asset-category').serializeArray();
        postMethod('admin/asset/addAssetCate',form_data,false);
    });
</script>