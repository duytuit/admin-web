<div class="tab-pane" id="asset_area" style="padding: 15px 0;">
    <div class="panel panel-default">
        <div class="panel-body">
            <div class="box box-primary">
                <div class="box-body ">
                    <div class="row">
                        {{-- <div class="col-sm-1">
                            <button type="button" data-toggle="dropdown" class="btn btn-primary dropdown-toggle pull-left" style="margin-right: 10px;">Tác vụ&nbsp;<span class="caret"></span></button>
                            <ul class="dropdown-menu">
                                <li>
                                    <a type="button" class="btn-action deleteAllArea">
                                        <i class="fa fa-trash text-danger"></i>&nbsp; Xóa
                                    </a>
                                </li>
                            </ul>
                        </div> --}}
                        <form id="form-search-asset-area" action="" method="GET">
                            <div id="search-advance" class="search-advance">
                                <div class="col-sm-2">
                                    <a class="btn btn-success" title="Thêm khu vực" data-toggle="modal"
                                       data-target="#add-asset-area"><i class="fa fa-plus"></i>&nbsp;&nbsp;Thêm khu vực</a>
                                </div>
                                <div class="col-sm-10">
                                    <div class="form-group space-5" style="/*width: calc(100% - 55px);*/float: left;">
                                        <div class="col-sm-12">
                                            <input type="text" class="form-control" name="keyword_asset_area"
                                                   placeholder="Nhập từ khóa tìm kiếm"
                                                   value="{{!empty($filter['keyword_asset_area'])?$filter['keyword_asset_area']:''}}">
                                        </div>
                                    </div>
                                    <div>
                                        <button type="submit" title="Tìm kiếm" class="btn btn-info"><i
                                                    class="fa fa-search"></i> Tìm
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form><!-- END #form-search-advance -->
                    </div>
                    <table class="table table-hover table-striped table-bordered">
                        <thead class="bg-primary">
                        <tr>
                            <th class="text-center" width="30">STT</th>
                            <th width="140">Tên</th>
                            <th>Tòa</th>
                            <th>Tầng</th>
                            <th width="100">Thao tác</th>
                        </tr>
                        </thead>
                        <tbody id="list_method_area_asset">
                        </tbody>
                    </table>
                    <div class="row">
                        <div class="col-sm-3">
                        </div>
                        <div class="col-sm-6 text-center">
                            <div id="pagination-panel-area-asset"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @include('v3.assets.modals.add-asset-area')
</div>
<script>
    let object_list_area_asset_method = null;
    $(document).ready(function () {
        let param_query_old = "{{ $array_search }}";
        let param_query = param_query_old.replaceAll("&amp;", "&")
        $('#pagination-panel-area-asset').pagination({
            dataSource: window.localStorage.getItem("base_url") + 'admin/asset/getListAssetOffice' + param_query,
            locator: 'data.list',
            totalNumberLocator: function (response) {
                return response.data.list.length
            },
            alias: {
                pageNumber: 'page',
                pageSize: 'limit'
            },
            pageSize: 10,
            ajax: {
                beforeSend: function () {
                    $('#list_method_area_asset').html('Loading data ...');
                }
            },
            callback: function (data, pagination) {
                if (data) {
                    let html = '';
                    let stt = 0;
                    object_list_area_asset_method = data;
                    data.forEach(element => {
                        stt++;
                        let abc = JSON.stringify(element);
                        html += '<tr>' +
                            '    <td>' + stt + '</td>' +
                            '    <td>' + element.name + '</td>' +
                            '    <td>' + element.place_name + '</td>' +
                            '    <td>' + element.floor_name + '</td>' +
                            '    <td>' +
                            '        <a data-element="' + element.id + '"' +
                            '        class="btn btn-xs btn-primary edit_method" title="Sửa thông tin"><i' +
                            '                    class="fa fa-pencil"></i></a>' +
                            '        <a data-element="' + element.id + '" class="btn btn-xs btn-danger delete_edit_method"' +
                            '        title="Xóa thông tin"><i class="fa fa-trash"></i></a>' +
                            '    </td>' +
                            '</tr>';
                    });
                    $('#list_method_area_asset').html(html);
                }
            }
        })

    })
    $('#list_method_area_asset').on('click', '.edit_method', function (e) {
        e.preventDefault();
        let id = $(this).data('element');
        if (id) {
            object_list_area_asset_method.forEach(element => {
                if (element.id == id) {
                    $('#method_area_asset_id').val(element.id);
                    $('#title-asset-area').val(element.name);

                    $('#asset_detail_place_id').append($('<option>', {
                        value: element.place_id,
                        text: element.place_name,
                        selected: "selected"
                    }));
                    $('#asset_detail_place_id').val(element.place_id).change();
                    $('.place_asset_detail .select2-selection__rendered').text(element.place_name);

                    $('#asset_detail_floor_id').append($('<option>', {
                        value: element.floor_id,
                        text: element.floor_name,
                        selected: "selected"
                    }));
                    $('#asset_detail_floor_id').val(element.floor_id).change();
                    $('.floor_asset_detail .select2-selection__rendered').text(element.floor_name);

                }
            });
        }
        $('#add-asset-area').modal('show');
    });
    $('#list_method_area_asset').on('click', '.delete_edit_method', function (e) {
        e.preventDefault();
        let id = $(this).data('element');
        if (id) {
            postDel('admin/asset/delAssetOffice', id);
        }
    });
    $('.btn-js-action-add-asset-area').click(function (e) {
        e.preventDefault();
        var form_data = $('#form-add-asset-area').serializeArray();
        console.log(form_data);
        postMethod('admin/asset/addAssetAreaOffice', form_data,false);
    });
</script>