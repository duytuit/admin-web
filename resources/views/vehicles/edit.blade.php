@extends('backend.layouts.master')

@section('content')

    <section class="content-header">
        <h1>
            Sửa phương tiện
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('admin.home') }}"><i class="fa fa-home"></i> Trang chủ</a></li>
            <li class="active">Sửa phương tiện</li>
        </ol>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-heading">Sửa phương tiện</div>

                    <div class="panel-body">
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        <div class="alert alert-danger alert_pop_add_edit" style="display: none;">
                            <ul></ul>
                        </div>
                        <div class="form-group">
                            <form action="" method="post" id="form-edit-vehicles">
                                {{ csrf_field() }}
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="ip-name">Tên phương tiện</label>
                                        <input type="text" name="name" id="ip-name" class="form-control" placeholder="Tên phương tiện" value="{{ $vehicle->name ?? old('name') ?? ''}}">
                                    </div>
                                    <div class="form-group">
                                        @if (\Auth::user()->isadmin == 1)
                                            <label for="ip-phone">Loại phương tiện</label>
                                            <select name="vehicle_category_id" id="vehicle_category_id" class="form-control" style="width: 100%;">
                                                @foreach($vehicleCateActive as $vehiclecate)
                                                    <option value="{{$vehiclecate->id}}" @if($vehiclecate->id == $vehicle->vehicle_category_id) selected @endif>{{$vehiclecate->name}}</option>
                                                @endforeach
                                            </select>
                                        @else
                                            <label for="ip-phone">Loại phương tiện</label>
                                            <select disabled
                                                    class="form-control"
                                                    style="width: 100%;">
                                                <option value="">Chọn loại phương tiện</option>
                                                <?php $vehicle_cate_id = $vehiclecate->id ?? old('vehicle_category_id') ?? ''; ?>
                                                @if($vehicle_cate_id)
                                                    <option value="{{$vehicle_cate_id}}" selected>{{ $vehiclecate->name ?? '' }}</option>
                                                @endif
                                            </select>
                                            <input type="hidden" name="vehicle_category_id" value="{{$vehicle_cate_id}}">
                                        @endif
                                    </div>
                                    <div class="form-group">
                                        <label for="ip-email">Biển số</label>
                                        <input type="text" name="number" id="ip-number" class="form-control" placeholder="Email" value="{{ $vehicle->number ?? old('number') ?? ''}}">
                                    </div>
                                    <div class="form-group">
                                        <label for="ip-address">Mô tả</label>
                                        <textarea name="description"
                                                  id="ip-description"
                                                  class="form-control" cols="30" rows="5"
                                                  placeholder="Mô tả">{{ $vehicle->description ?? old('description') ?? ''}}</textarea>
                                    </div>
                                    <div class="form-group">
                                        <label for="ip-cmt_nc">căn hộ</label>
                                        <select disabled name="bdc_apartment_id" id="ip-ap_id" class="form-control" style="width: 100%;">
                                            <option value="">Chọn căn hộ</option>
                                            <?php $apartment_id = $bdcApartment->id ?? old('bdc_apartment_id') ?? ''; ?>
                                            @if($apartment_id)
                                                <option value="{{$apartment_id}}" selected>{{ $bdcApartment->name ?? '' }}</option>
                                            @endif
                                        </select>
                                        <input type="hidden" name="bdc_apartment_id" value="{{$apartment_id}}">
                                    </div>
                                    <div class="form-group">
                                        <label>Ngày áp dụng tính phí</label>
                                        <input type="date"
                                               class="form-control pull-right date_picker"
                                               id="first_time_active"
                                               name="first_time_active"
                                               value="{{isset($apartment_service_vehicle)? $apartment_service_vehicle->first_time_active:''}}"
                                        >
                                    </div>
                                    <div class="form-group">
                                        <label>Ngày kết thúc tính phí</label>
                                        <input type="text"
                                               class="form-control pull-right date_picker"
                                               id="finish"
                                               name="finish"
                                               value="{{$vehicle->finish ? date('d-m-Y',strtotime($vehicle->finish)) : ''}}"
                                        >
                                    </div>
                                    <div class="form-group">
                                        <label>Mức ưu tiên tính phí</label>
                                        <select name="progressive_price_id" class="form-control" id="progressive_price">
                                            @if(isset($progressive_prices))
                                                @foreach($progressive_prices as $progressive_price)
                                                    @if($vehicle->bdc_progressive_price_id == $progressive_price->id)
                                                        <option value="{{$progressive_price->id}}" selected>{{ $progressive_price->name.' - '.$progressive_price->price.' - Mức '.$progressive_price->priority_level  }}</option>
                                                    @else
                                                        <option value="{{$progressive_price->id}}">{{ $progressive_price->name.' - '.$progressive_price->price.' - Mức '.$progressive_price->priority_level  }}</option>
                                                    @endif
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="in-re_name">Trạng thái</label>
                                        <input type="checkbox" name="status" {{$vehicle->status==1?'checked':''}}>
                                    </div>
                                </div>
                                <div class="clearfix"></div>
                                <div class="form-group">
                                    <button type="button" class="btn btn-sm btn-success btn-js-action-vehicle" title="Cập nhật" form="form-edit-vehicles">
                                        <i class="fa fa-save"></i>&nbsp;Cập nhật
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

@endsection
@section('stylesheet')
    <link rel="stylesheet" href="/adminLTE/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css" />
@endsection
@section('javascript')
    <script src="/adminLTE/plugins/moment/moment.min.js"></script>
    <script src="/adminLTE/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js"></script>

    <script>
        $('input.date_picker').datepicker({
            autoclose: true,
            dateFormat: "dd-mm-yy"
        }).val();
        $(function () {
            get_data_select_apartment1({
                object: '#ip-place_id',
                url: '{{ url('admin/apartments/ajax_get_building_place') }}',
                data_id: 'id',
                data_text: 'name',
                data_code: 'code',
                title_default: 'Chọn tòa nhà'
            });
            function get_data_select_apartment1(options) {
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
                                    text: item[options.data_text]+' - '+item[options.data_code]
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
            get_data_select({
                object: '#ip-apartment',
                url: '{{ url('admin/apartments/ajax_get_apartment') }}',
                data_id: 'id',
                data_text: 'name',
                title_default: 'Chọn căn hộ'
            });
            $("#ip-place_id").on('change', function(){
                if($("#ip-place_id").val()){
                    get_data_select({
                    object: '#ip-ap_id',
                    url: '{{ url('admin/apartments/ajax_get_apartment_with_place') }}',
                    data_id: 'id',
                    data_text: 'name',
                    title_default: 'Chọn căn hộ'
                    });
                }
            });
            get_data_select({
                object: '#select-vc_type',
                url: '{{ url('admin/vehiclecategory/ajax_get_vehicle_cate') }}',
                data_id: 'id',
                data_text: 'name',
                title_default: 'Chọn loại phương tiện'
            });
            function get_data_select(options) {
                $(options.object).select2({
                    ajax: {
                        url: options.url,
                        dataType: 'json',
                        data: function(params) {
                            var query = {
                                search: params.term,
                                place_id: $("#ip-place_id").val(),
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
            $('#vehicle_category_id').on('change',function (){
                $.ajax({
                    url: '/admin/vehicles/getPriceVehicle',
                    type: 'POST',
                    data: {
                        apartment_id: $('#ip-ap_id').val(),
                        vehicle_category_id: $(this).val()
                    },
                    success: function (res) {
                        let {progressive_prices, progressivePrice: progressive_price} = res.data;
                        let select_progressive_prices = "";
                        progressive_prices.forEach((item)=>{
                            const {price, id, name, priority_level} = item;
                            const {priority_level: priority_level1} = progressive_price;
                            if (priority_level === priority_level1) {
                                    select_progressive_prices += `<option value=${id} selected>${name} - ${price} VNĐ - Mức ${priority_level}</option>`;
                            }
                            else {
                                if (priority_level < priority_level1){ 
                                    select_progressive_prices +=`<option value=${id}>${name} - ${price} VNĐ - Mức ${priority_level}</option>`;
                                }
                            }
                        })
                        console.log(select_progressive_prices);
                        let list_progressives = $('#progressive_price');
                        list_progressives.empty();
                        list_progressives.prepend(select_progressive_prices);

                    },
                    error: function (e) {
                        console.log(e);
                    }
                })
                // }
            });
            $(".btn-js-action-vehicle").on('click',function () {
                var _this = $(this);
                $(".alert_pop_add_vehicle").hide();
                _this.attr('type','button');
                var vehicle_number = $("#ip-number").val();
                var name = $("#ip-name").val();
                var type = $("#select-vc_type").val();
                var apt = $("#ip-ap_id").val();
                var html = '';
                if(name.length <=2 || name.length >=50){
                    html+='<li>Tên phương tiện không được nhỏ hơn 3 hoặc lớn hơn 50 ký tự</li>';
                }if(vehicle_number == '' || (vehicle_number.length <=5 || vehicle_number.length >13)){
                    html+='<li>Biển số không được nhỏ hơn 6 hoặc lớn hơn 12 ký tự</li>';
                }if(apt == ''){
                    html+='<li>Trường Căn hộ không được để trống</li>';
                }if(type == ''){
                    html+='<li>Trường loại phương tiện không được để trống</li>';
                }

                $.get('{{ route('admin.vehicles.ajax_check_number') }}', {
                    type: vehicle_number,
                    id: {{$id}}
                }, function(data) {
                    if(data.status == 1){
                        html+='<li>'+ data.message +'</li>';
                    }
                });

                setTimeout(function(){
                    if(html != ''){
                        $(".alert_pop_add_edit").show();
                        $(".alert_pop_add_edit ul").html(html)
                    }else{
                        $('#form-edit-vehicles').submit();
                    }
                }, 600);

            });
        });
        sidebar('apartments', 'create');
    </script>

@endsection
