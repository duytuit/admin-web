<div class="tab-pane" id="asset-details" style="padding: 15px 0;">
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
                            <a class="btn btn-success" title="Thêm thẻ" id="show-add-asset-detail"><i
                                    class="fa fa-plus"></i>&nbsp;&nbsp;Thêm chi tiết tài sản</a>
                        </div>
                        <div class="col-sm-2">
                            <a href="{{ route('admin.v3.assets.importexceldetail') }}"
                                class="btn btn-info pull-right margin-r-5"><i class="fa fa-edit"></i> Import Excel</a>
                        </div>
                    </div>
                    <br>
                    <div class="row form-group">
                        <form id="form-search-cate" action="" method="GET">
                            <div class="col-sm-3">
                                <select name="asset_category_id" id="search_asset_category_id" class="form-control" style="width: 100%">
                                    <option value="">Chọn danh mục</option>
                                    <?php $_asset_category = isset($_asset_category) ? $_asset_category : '' ?>
                                    @if($_asset_category)
                                        <option value="{{$_asset_category->id}}" selected>{{$_asset_category->title}}</option>
                                    @endif
                                </select>
                            </div>
                            <div class="col-sm-2">
                                <select name="office_id" id="search_office_id" class="form-control" style="width: 100%">
                                    <option value="">Chọn khu vực</option>
                                    <?php $_office_asset = isset($_office_asset) ? $_office_asset : '' ?>
                                    @if($_office_asset)
                                        <option value="{{$_office_asset->id}}" selected>{{$_office_asset->name}}</option>
                                    @endif
                                </select>
                            </div>
                            <div class="col-sm-3">
                                <select name="department_id" id="search_department_id" class="form-control" style="width: 100%">
                                    <option value="">Chọn bộ phận</option>
                                    <?php $_department_asset = isset($_department_asset) ? $_department_asset : '' ?>
                                    @if($_department_asset)
                                        <option value="{{$_department_asset->id}}" selected>{{$_department_asset->name}}</option>
                                    @endif
                                </select>
                            </div>
                            <div class="input-group-btn col-sm-1">
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
                                    <th width="100">Kiểu bảo trì</th>
                                    <th width="100">Thời gian bảo trì</th>
                                    <th width="140">Tòa</th>
                                    <th width="100">Tầng</th>
                                    <th width="110">Khu vực</th>
                                    <th width="100">Bộ phận</th>
                                    <th width="140">Giá</th>
                                    <th width="100">Ảnh</th>
                                    <th width="100">Ghi chú</th>
                                    <th width="140">Trạng thái</th>
                                    <th width="100">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody id="list_method_asset_detail">
                    
                            </tbody>
                        </table>
                    </div>
                    <div class="clearfix">
                        <div class="row">
                            <div class="col-sm-3">
                            </div>
                            <div class="col-sm-6 text-center">
                                <div id="pagination-panel-asset-detail"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @include('v3.assets.modals.add-asset-detail')
</div>

<script>
      $('#image_asset_detail').change(function(e){
            let fileName = e.target.value.split(/(\\|\/)/g).pop();
            //$('#fileName').text(fileName); 
            // if(fileName){
            //     $('#iconRemoveFile').show().css("color", "red");
            //     $('#fileName').css("margin-left", "15px"); 
            // }
            if (e.target.files[0]) {
                var reader = new FileReader();
                reader.onload = function (e) {
                    $(".show_image_asset_detail").html("<img src='"+e.target.result+"' style='width:100%;height:300px;object-fit: contain;'/>");
                };
                reader.readAsDataURL(e.target.files[0]);
            }
      })
      $('#show-add-asset-detail').click(function (e) { 
        e.preventDefault();
        $('#form-add-asset-detail')[0].reset();
        $('#add-asset-detail').modal('show');
        $(".show_image_asset_detail").html('');
      });
    let object_list_method_asset_detail = null;
    $(document).ready(function() {
        let param_query_old = "{{ $array_search }}";
        let param_query = param_query_old.replaceAll("&amp;", "&")
        $('#pagination-panel-asset-detail').pagination({
            dataSource:  window.localStorage.getItem("base_url")+'admin/asset/getListAssetInfo' + param_query,
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
                    object_list_method_asset_detail = data;
                    data.forEach(element => {
                        stt++;
                        let type_maintain = element.type_maintain == 1 ? 'ngày':'tháng'
                        let abc = JSON.stringify(element);
                        let status = element.status == 1 ? '<span class="label labela-success" style="background-color:#9788C3">Hoạt động</span>' : ' <span class="label labela-success" style="background-color:#104aec">Ngừng</span>';
                        let _image = element.file ? '<img src="'+element.file+'" style="width:100%;height:40px;object-fit: contain"/>' : '';
                        html+= '<tr>'+
                            '    <td>'+stt+'</td>'+
                            '    <td>'+element.asset_detail_name+'</td>'+
                            '    <td>'+type_maintain+'</td>'+
                            '    <td>'+element.maintain_time+'</td>'+
                            '    <td>'+element.place_name+'</td>'+
                            '    <td>'+element.floor_name+'</td>'+
                            '    <td>'+element.office_name+'</td>'+
                            '    <td>'+element.department_name+'</td>'+
                            '    <td>'+element.amount+'</td>'+
                            '    <td>'+_image+'</td>'+
                            '    <td>'+element.desc+'</td>'+
                            '    <td>'+status+'</td>'+
                            '    <td>'+
                            '        <a data-element="'+element.id+'"'+
                            '      target="_blank" href="/admin/v3/maintenance-asset/detail/'+element.id+'" class="btn btn-xs btn-primary maintenance_detail" title="Chi tiết lịch bảo trì"><i'+
                            '                    class="fa fa-align-left"></i></a>'+
                            '        <a data-element="'+element.id+'"'+
                            '        class="btn btn-xs btn-primary edit_method_asset_detail" title="Sửa thông tin"><i'+
                            '                    class="fa fa-pencil"></i></a>'+
                            '        <a data-element="'+element.id+'" class="btn btn-xs btn-danger delete_method_asset_detail"'+
                            '        title="Xóa thông tin"><i class="fa fa-trash"></i></a>'+
                            '    </td>'+
                            '</tr>';
                    });
                    $('#list_method_asset_detail').html(html);  
                }
            }
        })
      
    })
    
    $('#list_method_asset_detail').on('click','.edit_method_asset_detail', function (e) {
        e.preventDefault();
        let id = $(this).data('element');
        if(id){
            object_list_method_asset_detail.forEach(element => {
                if(element.id == id){
                     console.log(element);
                    $('#asset_detail_id').append($('<option>', {
                        value: element.asset_detail_id,
                        text: element.asset_detail_name,
                        selected:"selected"
                    }));
                    $('#asset_detail_id').val(element.asset_detail_id).change();
                    $('.name_asset_detail .select2-selection__rendered').text(element.asset_detail_name);

                    $('#asset_detail_office_id').append($('<option>', {
                        value: element.office_id,
                        text: element.office_name,
                        selected:"selected"
                    }));
                    $('#asset_detail_office_id').val(element.office_id).change();
                    $('.office_asset_detail .select2-selection__rendered').text(element.office_name);

                    $('#asset_detail_department_id').append($('<option>', {
                        value: element.department_id,
                        text: element.department_name,
                        selected:"selected"
                    }));
                    $('#asset_detail_department_id').val(element.department_id).change();
                    $('.department_asset_detail .select2-selection__rendered').text(element.department_name);

                    $('#amount').val(element.amount);
                    $('#method_asset_detail_id').val(element.id);
                    $('#form-add-asset-detail #type_maintain').val(element.type_maintain).change();
                    $('#form-add-asset-detail #maintain_time').val(element.maintain_time);
                    $('#asset_detail_status').prop('checked', element.status == 1 ? true : false);
                    if(element.file){
                        $(".show_image_asset_detail").html( "<img src='"+element.file+"' style='width:100%;height:300px;object-fit: contain;'/>");
                    }
                    if(element.last_time_maintain){
                        $('#last_time_maintain').val(format_date_to_input(element.last_time_maintain));
                    }

                }
            });
        }
        $('#add-asset-detail').modal('show');
    });

    $('#list_method_asset_detail').on('click','.delete_method_asset_detail', function (e) {
        e.preventDefault();
        let id = $(this).data('element');
        if(id){
            postDel('admin/asset/delAssetDetail',id);
        }
    });
    $('.btn-js-action-add-asset-detail').click(function (e) { 
        e.preventDefault();
        var formCreate = new FormData($('#form-add-asset-detail')[0]);
        if($('#asset_detail_status').is(':checked')){
        }else{
            formCreate.append('status', 0);
        }

        // if($('#image_asset_detail').val()){
        //     let files = $('#image_asset_detail').prop('files');
        //     formCreate.append('file',files[0]);
        // }
        console.log(Object.fromEntries(formCreate));

        let param_query_old = "{{ $array_search }}";
        let param_query = param_query_old.replaceAll("&amp;", "&")
        postMethod('admin/asset/addAssetInfo'+param_query,formCreate,true);
    });
</script>   