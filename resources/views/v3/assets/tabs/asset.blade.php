<div class="tab-pane active" id="asset" style="padding: 15px 0;">
    <div class="panel panel-default">
        <div class="panel-body">
            <div class="box box-primary">
                <div class="box-body ">
                    <div class="row">
                        {{-- <div class="col-sm-1">
                            <button type="button" data-toggle="dropdown"
                                class="btn btn-primary dropdown-toggle pull-left" style="margin-right: 10px;">Tác
                                vụ&nbsp;<span class="caret"></span></button>
                            <ul class="dropdown-menu">
                                <li>
                                    <a type="button" class="btn-action deleteAllAsset">
                                        <i class="fa fa-trash text-danger"></i>&nbsp; Xóa
                                    </a>
                                </li>
                            </ul>
                        </div> --}}
                        <div class="col-sm-2">
                            <a class="btn btn-success" title="Thêm thẻ" data-toggle="modal" data-target="#add-asset"><i class="fa fa-plus"></i>&nbsp;&nbsp;Thêm tài sản</a>
                        </div>
                        <div class="col-sm-2">
                            <a href="{{ route('admin.v3.assets.importexcel') }}" class="btn btn-info"><i class="fa fa-edit"></i> Import Excel</a>
                        </div>
                        <div class="col-sm-2">
                            <a href="javascript:" class="btn btn-warning export_asset"><i class="fa fa-download"></i>&nbsp;&nbsp;Export Excel</a> 
                        </div>
                    </div>
                    <br>
                    <div class="row form-group">
                        <form id="form-search-cate" action="" method="GET">
                            <div class="col-sm-3">
                                <select name="asset_category_id" id="search_asset_category_id" class="form-control">
                                    <option value="">Chọn danh mục</option>
                                </select>
                            </div>
                            <div class="col-sm-3">
                                <div class="col-sm-12">
                                    <input type="text" class="form-control" name="keyword_asset" placeholder="Nhập từ khóa tìm kiếm" value="{{!empty($filter['keyword_asset'])?$filter['keyword_asset']:''}}">
                                </div>
                            </div>
                            <div class="col-sm-2">
                                <button type="submit" title="Tìm kiếm" class="btn btn-info" ><i class="fa fa-search"></i> Tìm</button>
                            </div>
                        </form>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover table-striped table-bordered">
                            <thead class="bg-primary">
                                <tr>
                                    <th width="50">TT</th>
                                    <th width="140">Tài sản</th>
                                    <th width="140">Danh mục</th>
                                    <th width="110">Kiểu bảo trì</th>
                                    <th width="100">Thời gian bảo trì</th>
                                    <th width="100">Ghi chú</th>
                                    <th width="100">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody id="list_method_asset">
                    
                            </tbody>
                        </table>
                    </div>
                    <div class="clearfix"></div>
                    <div class="row">
                        <div class="col-sm-3">
                        </div>
                        <div class="col-sm-6 text-center">
                            <div id="pagination-panel-asset"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @include('v3.assets.modals.add-asset')
</div>
<script>
    let object_list_method = null;
    $(document).ready(function() {
        let param_query_old = "{{ $array_search }}";
        let param_query = param_query_old.replaceAll("&amp;", "&")
        $('#pagination-panel-asset').pagination({
            dataSource:  window.localStorage.getItem("base_url")+'admin/asset/getListAssetDetail' + param_query,
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
                    $('#list_method_asset').html('Loading data ...');
                }
            },
            callback: function(data, pagination) {
                if(data){
                    let html ='';
                    let stt=0;
                    object_list_method = data;
                    data.forEach(element => {
                        stt++;
                        let type_maintain = element.type_maintain == 1 ? 'ngày':'tháng'
                        let abc = JSON.stringify(element);
                        html+= '<tr>'+
                            '    <td>'+stt+'</td>'+
                            '    <td>'+element.name+'</td>'+
                            '    <td>'+element.name_cate+'</td>'+
                            '    <td>'+type_maintain+'</td>'+
                            '    <td>'+element.maintain_time+'</td>'+
                            '    <td>'+element.desc+'</td>'+
                            '    <td>'+
                            '        <a data-element="'+element.id+'"'+
                            '        class="btn btn-xs btn-primary edit_method_asset" title="Sửa thông tin"><i'+
                            '                    class="fa fa-pencil"></i></a>'+
                            '        <a data-element="'+element.id+'" class="btn btn-xs btn-danger delete_edit_method_asset"'+
                            '        title="Xóa thông tin"><i class="fa fa-trash"></i></a>'+
                            '    </td>'+
                            '</tr>';
                    });
                    $('#list_method_asset').html(html);  
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
    $('#list_method_asset').on('click','.edit_method_asset', function (e) {
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
                    $('._asset_category .select2-selection__rendered').text(element.name_cate);
                }
            });
        }
        $('#add-asset').modal('show');
    });

    $('#list_method_asset').on('click','.delete_edit_method_asset', function (e) {
        e.preventDefault();
        let id = $(this).data('element');
        if(id){
            postDel('admin/asset/delAssetDetail',id);
        }
    });
    $('.btn-js-action-add-asset').click(function (e) { 
        e.preventDefault();
        var form_data = $('#form-add-asset').serializeArray();
        postMethod('admin/asset/addAssetDetail',form_data,false);
    });

</script>