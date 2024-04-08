@inject('request', 'Illuminate\Http\Request')
@extends('promotion.index')

@section('tab_content')
    <div class="box box-primary">
        <div class="box-body">
            <div id="check_list" class="tab-pan active">
                <div class="row form-group">
                    <div class="col-sm-1">
                        <a class="btn btn-success show_model_promotion_manager"> <i class="fa fa-plus"></i>Thêm khuyến
                            mãi</a>
                    </div>
                </div>
                <div class="row form-group">
                    <form action="" method="get">
                        <div class="col-sm-6">
                            <input type="text" name="category_name" class="form-control"
                                   placeholder="Nhập nội dung tìm kiếm" value="">
                        </div>
                        <div class="col-sm-1">
                            <button class="btn btn-info"><i class="fa fa-search"></i>Tìm</button>
                        </div>
                    </form>
                </div>
                <!-- /.box-header -->
                <form>
                    <table class="table table-striped table-bordered table-hover">
                        <thead class="bg-primary">
                        <tr>
                            <th>STT</th>
                            <th>Tên KM</th>
                            <th>Loại KM</th>
                            <th>Thời gian áp dụng</th>
                            <th>Giá trị KM</th>
                            <th>Người tạo</th>
                            <th>Ngày tạo</th>
                            <th>Trạng thái</th>
                            <th>Tác vụ</th>
                        </tr>
                        </thead>
                        <tbody id="list_table">
                        </tbody>
                    </table>
                    <div class="box-footer clearfix"></div>
                    <div class="row">
                        <div class="col-sm-3">
                        </div>
                        <div class="col-sm-6 text-center">
                            <div id="pagination_list_table"></div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @include('promotion_manager.modals.add-promotion-manager')
@endsection
<script src="/js/validate.js"></script>
@section('javascript')
    <script>
        let object_table = null;
        $(document).ready(function () {
            let param_query_old = "{{ $array_search }}";
            let param_query = param_query_old.replaceAll("&amp;", "&")
            param_query += "&type=service_vehicle";
            $('#pagination_list_table').pagination({
                dataSource: window.localStorage.getItem("base_url") + 'admin/promotion/getListPromotion' + param_query,
                locator: 'data.list',
                totalNumberLocator: function (response) {
                    return response.data.count
                },
                alias: {
                    pageNumber: 'page',
                    pageSize: 'limit'
                },
                pageSize: 10,
                ajax: {
                    beforeSend: function () {
                        $('#list_table').html('Loading data ...');
                    }
                },
                callback: function (data, pagination) {
                    if (data) {
                        let html = '';
                        let stt = 0;
                        object_table = data;
                        data.forEach(element => {
                            stt++;
                            let status = element.status == 1 ? "checked" : "";
                            html += '<tr>' +
                                '    <td>' + stt + '</td>' +
                                '    <td>' + element.name + '</td>' +
                                '    <td>' + element.serivce_name + '</td>' +
                                '    <td><div>Từ: ' + (element.begin?format_date_no_time(element.begin):'') + '</div>' +
                                '    <div>Đến: ' + (element.end?format_date_no_time(element.end):'') + '</div></td>' +
                                '    <td>' + (element.type_discount == 0 ? format_monney(element.discount) + ' VND' : element.discount + '%') + '</td>' +
                                '    <td>' + element.by_name + '</td>' +
                                '    <td>' + format_date(element.updated_at) + '</td>' +
                                '    <td>' +
                                '       <div class="onoffswitch">' +
                                '         <input type="checkbox" name="onoffswitch" class="onoffswitch-checkbox" data-id="' + element.id + '"' +
                                '         id="myonoffswitch_' + element.id + '" onclick="changeStatus(this)" ' + status + ' >' +
                                '          <label class="onoffswitch-label" for="myonoffswitch_' + element.id + '">' +
                                '            <span class="onoffswitch-inner"></span>' +
                                '            <span class="onoffswitch-switch"></span>' +
                                '          </label>' +
                                '       </div>' +
                                '    </td>' +
                                '    <td>' +
                                '        <a data-element="' + element.id + '" data-element_title="' + element.title + '"' +
                                '        class="btn btn-xs btn-primary" onclick="editElement(this)" title="Sửa thông tin"><i' +
                                '                    class="fa fa-pencil"></i></a>' +
                                '        <a data-element="' + element.id + '" class="btn btn-xs btn-danger"' +
                                '        title="Xóa thông tin" onclick="deleteElement(this)"><i class="fa fa-trash"></i></a>' +
                                '    </td>' +
                                '</tr>';
                        });
                        $('#list_table').html(html);
                    }
                }
            })
            get_data_select_service({
                object: '#bdc_service_id',
                url: "{{route('admin.ajax.ajax_get_service')}}",
                data_id: 'id',
                data_text: 'name',
                title_default: 'Chọn dịch vụ'
            });

            function get_data_select_service(options) {
                $(options.object).select2({
                    ajax: {
                        url: options.url,
                        dataType: 'json',
                        data: function (params) {
                            var query = {
                                search: params.term,
                            }
                            return query;
                        },
                        processResults: function (json, params) {
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
            get_data_select_apartment({
                object: '#apartment_id',
                url: '{{ url('admin/apartments/ajax_get_apartment') }}',
                data_id: 'id',
                data_text: 'name',
                title_default: 'Chọn căn hộ'
            });
            function get_data_select_apartment(options) {
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
        })
        $('.show_model_promotion_manager').click(function (e) {
            e.preventDefault();
            $('#form-add-promotion-manager').find('input[name=id]').val('');
            $('#form-add-promotion-manager')[0].reset();
            $('#add-promotion-manager').modal('show');
        });
        $('.save_promotion_manager').click(function (e) {
            e.preventDefault();
            // var form_data = $('#form-add-promotion-manager').serializeArray();
            let param_query_old = "{{ $array_search }}";
            let param_query = param_query_old.replaceAll("&amp;", "&")
            console.log(param_query);
            var formCreate = new FormData($('#form-add-promotion-manager')[0]);
            if (formCreate.get('service_id')) {
                services = JSON.parse(window.localStorage.getItem("services"));
                let service = services.filter(o => o.id == parseInt(formCreate.get('service_id')));
                console.log('service', service);
                formCreate.append('service_type', parseInt(service[0].type));
            }
            if (formCreate.get('begin')) {
                formCreate.append('begin', formCreate.get('begin') + ' 00:00:00');
            }
            if (formCreate.get('end')) {
                formCreate.append('end', formCreate.get('end') + ' 23:59:59');
            }
            if ($('#form-add-promotion-manager').find('input[name=id]').val()) {
                let from_date = {
                    id: parseInt(formCreate.get('id')),
                    begin: formCreate.get('begin') + ' 00:00:00',
                    condition: parseInt(formCreate.get('condition')),
                    discount: parseInt(formCreate.get('discount')),
                    end: formCreate.get('end') + ' 23:59:59',
                    name: formCreate.get('name'),
                    number_discount: parseInt(formCreate.get('number_discount')),
                    service_id: parseInt(formCreate.get('service_id')),
                    service_type: parseInt(formCreate.get('service_type')),
                    type: "service_vehicle",
                    type_discount: parseInt(formCreate.get('type_discount'))
                }
                console.log('update')
                console.log(from_date)
                postMethod('admin/promotion/addPromotion' + param_query, from_date,true);
            } else {
                let from_date = {
                    begin: formCreate.get('begin') + ' 00:00:00',
                    condition: parseInt(formCreate.get('condition')),
                    discount: parseInt(formCreate.get('discount')),
                    end: formCreate.get('end') + ' 23:59:59',
                    name: formCreate.get('name'),
                    number_discount: parseInt(formCreate.get('number_discount')),
                    service_id: parseInt(formCreate.get('service_id')),
                    service_type: parseInt(formCreate.get('service_type')),
                    type: "service_vehicle",
                    type_discount: parseInt(formCreate.get('type_discount')),
                    status: 1
                }
                console.log(from_date)
                postMethod('admin/promotion/addPromotion' + param_query, from_date,true);
            }
        })

        async function postMethod(url, param, file) {
            let method = 'post';
            if (file) {
                let _result = await call_api(method, url, param);
                toastr.success(_result.mess);
            } else {
                let _result = await call_api_form_data(method, url, param);
                toastr.success(_result.mess);
            }
            setTimeout(function () {
                location.reload();
            }, 1000);
        }

        async function postDel(url, id, reload = true, param = null) {
            if (confirm("Bạn có chắc chắn muốn xóa không?")) {
                let method = 'post';
                let param_query_old = "{{ $array_search }}";
                let param_query = param_query_old.replaceAll("&amp;", "&")
                param_query += "&id=" + id;
                console.log(param_query);
                let _result = await call_api(method, url + param_query, param);
                toastr.success(_result.mess);
                if (reload == true) {
                    setTimeout(function () {
                        location.reload();
                    }, 1000);
                }
            } else {
                return false;


            }
        }

        function changeStatus(event) {
            //  $(event).parent().parent().remove();
            let param_query_old = "{{ $array_search }}";
            let param_query = param_query_old.replaceAll("&amp;", "&")
            let status = 0;
            let id = $(event).data('id');
            if (id) {
                object_table.forEach(element => {
                    if (element.id == id) {
                        if ($(event).is(":checked")) // nếu tích
                        {
                            element.status = 1;
                        } else {
                            element.status = 0;
                        }
                        postMethod('admin/promotion/updatePromotionStatus' + param_query, element);
                    }
                });
            }
        }

        function editElement(event) {
            // $(event).parent().parent().remove();
            //let status = 0;
            let id = $(event).data('element');
            if (id) {
                object_table.forEach(element => {
                    if (element.id == id) {
                        console.log(element);
                        $('#form-add-promotion-manager').find('input[name=id]').val(element.id);
                        $('#form-add-promotion-manager').find('input[name=name]').val(element.name);
                        $('#form-add-promotion-manager').find('input[name=begin]').val(format_date_to_input(element.begin));
                        $('#form-add-promotion-manager').find('input[name=end]').val(format_date_to_input(element.end));
                        $('#form-add-promotion-manager').find('input[name=condition]').val(element.condition);
                        $('#form-add-promotion-manager').find('input[name=number_discount]').val(element.number_discount);
                        $('#type_discount').val(element.type_discount).change();
                        $('#form-add-promotion-manager').find('input[name=discount]').val(element.discount);
                        $('#bdc_service_id').append($('<option>', {
                            value: element.service_id,
                            text: element.serivce_name,
                            selected: "selected"
                        }));
                        $('#bdc_service_id').val(element.service_id).change();
                        $('.service_select .select2-selection__rendered').text(element.serivce_name);
                    }
                });
            }
            $('#add-promotion-manager').modal('show');
        }

        function deleteElement(event) {
            let id = $(event).data('element');
            if (id) {
                postDel('admin/promotion/delPromotion', id)
            }
        }
    </script>
@endsection
