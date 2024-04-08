@inject('request', 'Illuminate\Http\Request')
@extends('promotion.index')

@section('tab_content')
    <div class="box box-primary">
        <div class="box-body">
            <div id="check_list" class="tab-pan active">
                <div class="row form-group">
                    <div class="col-sm-1">
                        <a class="btn btn-success show_model_promotion_apartment" > <i class="fa fa-plus"></i>Thêm khuyến mãi căn hộ</a>
                    </div>
                </div>
                <div class="row form-group">
                    <form action="" method="get">
                        <div class="col-sm-6">
                            <select name="apartment_id" id="apartment_id" class="form-control">
                                <option value="">Căn hộ</option>
                                <?php $apartment = isset($apartment) ? $apartment: '' ?>
                                @if($apartment)
                                    <option value="{{$apartment->id}}" selected>{{$apartment->name}}</option>
                                @endif
                            </select>
                        </div>
                        <div class="col-sm-1">
                            <button class="btn btn-info"><i class="fa fa-search"></i>Tìm</button>
                        </div>
                    </form>
                </div>
            <!-- /.box-header -->
            <form action="" method="post" >
                <table class="table table-striped table-bordered table-hover">
                    <thead class="bg-primary">
                        <tr>
                            <th>STT</th>
                            <th>Tên khuyến mãi</th>
                            <th>Căn hộ</th>
                            <th>Dịch vụ</th>
                            <th>Sản phẩm</th>
                            <th>Kỳ</th>
                            <th>Khuyến mãi</th>
                            <th>Bởi</th>
                            <th>Ngày tạo</th>
                            <th>Thao tác</th>
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
        @include('apartment-promotion-manager.modals.add-apartment-promotion-manager')
    </div>
@endsection
<script src="/js/validate.js"></script>
@section('javascript')
    <script>
        showModalForm('.edit_apartment_promotion_manager', '#edit-apartment-promotion-manager');
        let object_table = null;
        $(document).ready(function() {
            let param_query_old = "{{ $array_search }}";
            let param_query = param_query_old.replaceAll("&amp;", "&")
            $('#pagination_list_table').pagination({
                dataSource:  window.localStorage.getItem("base_url")+'admin/promotion/getListPromotionApartment' + param_query,
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
                        $('#list_table').html('Loading data ...');
                    }
                },
                callback: function(data, pagination) {
                    if(data){
                        let html ='';
                        let stt=0;
                        object_table = data;
                        data.forEach(element => {
                            stt++;
                            let status = element.status == 1 ? "checked" : "";

                            html+= '<tr>'+
                                '    <td>'+stt+'</td>'+
                                '    <td>'+element.promotion_name+'</td>'+
                                '    <td>'+element.apartment_name+'</td>'+
                                '    <td>'+element.service_name+'</td>'+
                                '    <td>'+(element.vehicle_number ? element.vehicle_number : '')+'</td>'+
                                '    <td>'+element.cycle_name+'</td>'+
                                '    <td>'+(element.type_discount == 0 ? format_monney(element.discount)+' VND':element.discount+'%')+'</td>'+
                                '    <td>'+element.by+'</td>'+
                                '    <td>'+format_date(element.updated_at)+'</td>'+
                                '    <td>'+
                                '        <a data-element="'+element.id+'" data-element_title="'+element.title+'"'+
                                '        class="btn btn-xs btn-primary" onclick="editElement(this)" title="Sửa thông tin"><i'+
                                '                    class="fa fa-pencil"></i></a>'+
                                '        <a data-element="'+element.id+'" class="btn btn-xs btn-danger"'+
                                '        title="Xóa thông tin" onclick="deleteElement(this)"><i class="fa fa-trash"></i></a>'+
                                '    </td>'+
                                '</tr>';
                        });
                        $('#list_table').html(html);  
                    }
                }
            })

            get_data_select_apartment({
                object: '#apartment_id,#bdc_apartment_id',
                url: "{{route('admin.ajax.ajax_get_apartment')}}",
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
            get_data_select_service_apartment({
                object: '#service_price_id',
                url: "{{route('admin.ajax.ajaxGetServiceApartment')}}",
                data_id: 'id',
                data_text: 'name',
                title_default: 'Chọn dịch vụ căn hộ'
            });
            function get_data_select_service_apartment(options) {
                $(options.object).select2({
                    ajax: {
                        url: options.url,
                        dataType: 'json',
                        data: function(params) {
                            var query = {
                                search: params.term,
                                apartment_id: $('#form-add-apartment-promotion-manager').find('select[name=apartment_id]').val()
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
                                    text: item[options.data_text] +' - '+ item['vehicle_name']
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
            get_data_select_promotion({
                object: '#promotion_id',
                url: "{{route('admin.ajax.ajaxGetPromotion')}}",
                data_id: 'id',
                data_text: 'name',
                title_default: 'Chọn khuyến mại'
            });
            function get_data_select_promotion(options) {
                $(options.object).select2({
                    ajax: {
                        url: options.url,
                        dataType: 'json',
                        data: function(params) {
                            var query = {
                                search: params.term,
                                service_id: $('#service_id').val(),
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
            $('#service_price_id').change(function(e){
                e.preventDefault()
                $('#service_id').val('');
                let apartment_id = $('#form-add-apartment-promotion-manager').find('select[name=apartment_id]').val();
                console.log(apartment_id);
                if($(this).val()){
                   service_apartments = JSON.parse(window.localStorage.getItem("service_apartments"));
                   service_apartment = service_apartments.filter(o => o.id == parseInt($(this).val()));
                    console.log(service_apartment);
                    $('#service_id').val(service_apartment.bdc_service_id);
                    console.log($(this).val());
                }
            })
        })
  
    $('.show_model_promotion_apartment').click(function (e) { 
        e.preventDefault();
        $('#apartment_promotion_id').val('');
        $('.apartment .select2-container').css('pointer-events','auto');
        $('.service_apartment .select2-container').css('pointer-events','auto');
        $('.promotion .select2-container').css('pointer-events','auto');
        $('#form-add-apartment-promotion-manager')[0].reset();
        $('#add-apartment-promotion-manager').modal('show')
    });
    $('.save_apartment_promotion_manager').click(function (e) { 
        e.preventDefault();
        let param_query_old = "{{ $array_search }}";
        let param_query = param_query_old.replaceAll("&amp;", "&")
        var formCreate = new FormData($('#form-add-apartment-promotion-manager')[0]);
        var service_apartment = null;
        if(formCreate.get('service_price_id')){
            service_apartments = JSON.parse(window.localStorage.getItem("service_apartments"));
            service_apartment = service_apartments.filter(o => o.id == parseInt(formCreate.get('service_price_id')));
        }
        let cycle_name = $('#form-add-apartment-promotion-manager').find('select[name=cycle_year]').val()+$('#form-add-apartment-promotion-manager').find('select[name=cycle_month]').val()
        if($('#apartment_promotion_id').val()){
            let from_date ={
                id: parseInt($('#apartment_promotion_id').val()),
                type:  'service_vehicle',
                service_id: service_apartment.length > 0 ? service_apartment[0].bdc_service_id : '',
                service_price_id:  service_apartment.length > 0 ? service_apartment[0].id : '',
                apartment_id: parseInt(formCreate.get('apartment_id')),
                promotion_id: parseInt(formCreate.get('promotion_id')),
                cycle_name:  parseInt(cycle_name)
            }
            console.log(from_date);
            postMethod('admin/promotion/updatePromotionApartment'+param_query,from_date,true);
        }else{
            let from_date ={
                type:  'service_vehicle',
                service_id:  service_apartment.length > 0 ? service_apartment[0].bdc_service_id : '',
                service_price_id: service_apartment.length > 0 ? service_apartment[0].id : '',
                apartment_id: parseInt(formCreate.get('apartment_id')),
                promotion_id: parseInt(formCreate.get('promotion_id')),
                begin_cycle_name:  parseInt(cycle_name)
            }
            console.log(from_date);
            postMethod('admin/promotion/addPromotionApartment'+param_query,from_date,true);
        }
    });
    async function postMethod(url,param,file) {
        let method='post';
        if(file){
            let _result = await call_api(method, url,param);
            toastr.success(_result.mess);
        }else{
            let _result = await call_api_form_data(method, url,param);
            toastr.success(_result.mess);
        }
        setTimeout(function(){
            location.reload();
        }, 1000);
    }
    async function postDel(url,id,reload=true,param=null) {
        if(confirm("Bạn có chắc chắn muốn xóa không?")){
            let method='post';
            let param_query_old = "{{ $array_search }}";
            let param_query = param_query_old.replaceAll("&amp;", "&")
            param_query +="&id="+id;
            console.log(param_query);
            let _result = await call_api(method,url+param_query,param);
            toastr.success(_result.mess);
            if(reload == true){
                setTimeout(function(){
                  location.reload();
                }, 1000);
            }
        }
        else{
            return false;
        }
    }
   function editElement(event) {
        let id = $(event).data('element');
        if(id){
        object_table.forEach(element => {
               if(element.id == id){
                   console.log(element);
                   $('#apartment_promotion_id').val(element.id)
                   let year = element.cycle_name.toString().slice(0,4);
                   let month = element.cycle_name.toString().slice(4,6);

                   $('#form-add-apartment-promotion-manager').find('select[name=cycle_month]').val(month);

                   $('#form-add-apartment-promotion-manager').find('select[name=cycle_year]').val(year);
                   // căn hộ
                   $('#bdc_apartment_id').append($('<option>', {
                        value: element.apartment_id,
                        text: element.apartment_name,
                        selected:"selected"
                    }));
                   $('#bdc_apartment_id').val(element.apartment_id).change();
                   $('.apartment .select2-selection__rendered').text(element.apartment_name);
                   $('.apartment .select2-container').css('pointer-events','none');
                   // dịch vụ căn hộ
                   $('#service_price_id').append($('<option>', {
                        value: element.service_price_id,
                        text: element.service_name,
                        selected:"selected"
                    }));
                   $('#service_price_id').val(element.service_price_id).change();
                   $('.service_apartment .select2-selection__rendered').text(element.service_name);
                   $('.service_apartment .select2-container').css('pointer-events','none');
                   // khuyến mại
                   $('#promotion_id').append($('<option>', {
                        value: element.promotion_id,
                        text: element.promotion_name,
                        selected:"selected"
                    }));
                   $('#promotion_id').val(element.promotion_id).change();
                   $('.promotion .select2-selection__rendered').text(element.promotion_name);
                   $('.promotion .select2-container').css('pointer-events','none');
                }
           });
       }
       $('#add-apartment-promotion-manager').modal('show')
   }
   function deleteElement(event) {
        let id = $(event).data('element');
        if(id){
            postDel('admin/promotion/delPromotionApartment',id)
        }
   }
    </script>
@endsection
