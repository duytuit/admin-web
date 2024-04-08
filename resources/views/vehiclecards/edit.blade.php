@extends('backend.layouts.master')

@section('content')

    <section class="content-header">
        <h1>
            Sửa vé xe
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('admin.home') }}"><i class="fa fa-home"></i> Trang chủ</a></li>
            <li class="active">Sửa vé xe</li>
        </ol>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-heading">Sửa vé xe</div>

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
                                        <label for="ip-name">Mã thẻ</label>
                                        <input type="text" name="code" id="ip-code" class="form-control" placeholder="Mã thẻ" value="{{ $vehiclecard->code ?? old('code') ?? ''}}">
                                    </div>
                                    <div class="form-group">
                                         <label>Tòa nhà</label>
                                         <select name="building_place_id" id="ip-place_id" class="form-control" style="width: 100%;">
                                             <option value="">Chọn tòa nhà</option>
                                         </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="ip-cmt_nc">Căn hộ</label>
                                        <select name="bdc_apartment_id" id="ip-ap_id" class="form-control" style="width: 100%;">
                                            <option value="">Chọn căn hộ</option>
                                            <?php $apartment_id = $vehicle->bdcApartment->id ?? old('bdc_apartment_id') ?? ''; ?>
                                            @if($apartment_id)
                                                <option value="{{$apartment_id}}" selected>{{ $vehicle->bdcApartment->name ?? '' }}</option>
                                            @endif
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="ip-phone">Loại phương tiện</label>
                                        <select name="vehicle_category_id" id="select-vc_type" class="form-control" style="width: 100%;">
                                            <option value="">Chọn loại phương tiện</option>
                                            <?php $vehicle_cate_id = $vehicle->bdcVehiclesCategory->id ?? old('vehicle_category_id') ?? ''; ?>
                                            @if($vehicle_cate_id)
                                                <option value="{{$vehicle_cate_id}}" selected>{{ $vehicle->bdcVehiclesCategory->name ?? '' }}</option>
                                            @endif
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="ip-phone">Biển số</label>
                                        <select name="number" id="in-vc_vehicle_number" class="form-control" style="width: 100%;">
                                            <option value="">Chọn biển số</option>
                                            <?php $vehicle_id = $vehicle->id ?? old('number') ?? ''; ?>
                                            @if($vehicle_id)
                                                <option value="{{ $vehicle->number ?? '' }}" selected>{{ $vehicle->number ?? '' }}</option>
                                            @endif
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="ip-phone">Ghi chú</label>
                                        <textarea class="form-control" placeholder="ghi chú" id="context" name="description" rows="3">{{ $vehiclecard->description ?? old('description') ?? ''}}</textarea>
                                    </div>
                                    <div class="form-group">
                                        <label for="ip-phone">Trạng thái</label>
                                        <select name="status" id="slc-status" class="form-control" style="width: 100%;">
                                            <?php $status = $vehiclecard->status ?? old('status') ?? ''; ?>
                                            <option value="">Chọn Trạng thái</option>
                                            <option value="1" @if($status == 1) selected @endif>Active</option>
                                            <option value="2" @if($status == 2) selected @endif>Inactive</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="clearfix"></div>
                                <div class="form-group">
                                    <button type="button" class="btn btn-sm btn-success btn-js-vehiclecard" title="Cập nhật" form="form-edit-vehicles">
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
        var in_partment = '{{ $vehicle->bdcApartment->id ?? "" }}';
        var in_cate = '{{ $vehicle->bdcVehiclesCategory->id ?? "" }}';
        $(function () {
            getNumberVehicle(in_partment,in_cate);
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
            function getNumberVehicle(apartment,cate){
                get_data_select2({
                    object: '#in-vc_vehicle_number',
                    url: '{{ url('admin/vehiclecards/ajax_get_vehiclecard') }}',
                    data_id: 'number',
                    data_text: 'number',
                    title_default: 'Chọn biển số',
                    apartment: apartment,
                    cate: cate
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
            function get_data_select2(options) {
                $(options.object).select2({
                    ajax: {
                        url: options.url,
                        dataType: 'json',
                        data: function(params) {
                            var query = {
                                search: params.term,
                                apartment: options.apartment,
                                cate: options.cate,
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
            $("#ip-ap_id,#select-vc_type").on('change',function () {
                var type = $("#select-vc_type").val();
                var apartment = $("#ip-ap_id").val();
                if(type && apartment){
                    getNumberVehicle(apartment,type);
                }
            });
            $(".btn-js-vehiclecard").on('click',function () {
                var _this = $(this);
                $(".alert_pop_add_edit").hide();
                _this.attr('type','button');
                var code = $("#ip-code").val();
                var apt = $("#ip-ap_id").val();
                var type = $("#select-vc_type").val();
                var vehicle_number = $("#in-vc_vehicle_number").val();
                var status = $("#slc-status").val();
                var html = '';
                if(code.length <=2 || code.length >=50){
                    html+='<li>Tên phương tiện không được nhỏ hơn 3 hoặc lớn hơn 50 ký tự</li>';
                }if(apt == ''){
                    html+='<li>Trường Căn hộ không được để trống</li>';
                }if(type == ''){
                    html+='<li>Trường loại phương tiện không được để trống</li>';
                }if(vehicle_number == ''){
                    html+='<li>Trường Biển số không được để trống</li>';
                }if(status == ''){
                    html+='<li>Trường trạng thái không được để trống</li>';
                }

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
