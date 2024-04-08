<div id="add-vehicle" class="modal fade" role="dialog">
    <div class="modal-dialog  modal-lg">
        <!-- Modal content-->
        @if( in_array('admin.vehicles.insert',@$user_access_router))
            <form action="{{ route('admin.vehicles.insert') }}" method="post" id="form-add-verhicle" class="form-validate form-horizontal">
                {{ csrf_field() }}
                <input type="hidden" name="hashtag">
                <div class="modal-content">
                    <div class="modal-header bg-primary">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">Thêm mới phương tiện</h4>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-danger alert_pop_add_vehicle" style="display: none;">
                            <ul></ul>
                        </div>
                        <div class="row">
                            <div class="col-sm-12">
                                <div class="form-group">
                                    <div class="col-sm-2">
                                        <label for="in-re_name">Tên Phương tiện</label>
                                    </div>
                                    <div class="col-sm-8">
                                        <input type="text" name="name" id="in-vc_name" class="form-control" placeholder="Tên phương tiện">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="col-sm-2">
                                        <label>Tòa nhà</label>
                                    </div>
                                    <div class="col-sm-8">
                                        <select name="building_place_id" id="ip-place_id" class="form-control" style="width: 100%;">
                                            <option value="">Chọn tòa nhà</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="col-sm-2">
                                        <label>Căn hộ</label>
                                    </div>
                                    <div class="col-sm-8">
                                        <select name="bdc_apartment_id" id="ip-ap_id" class="form-control" style="width: 100%;">
                                            <option value="">Chọn căn hộ</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="col-sm-2">
                                        <label>Loại phương tiện</label>
                                    </div>
                                    <div class="col-sm-8">
                                        <select name="vehicle_category_id" id="select-vc_type" class="form-control" style="width: 100%;">
                                            <option value="">Chọn loại phương tiện</option>
                                            @foreach($vehicleCateActive as $vehiclecate)
                                                <option value="{{$vehiclecate->id}}">{{$vehiclecate->name}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="col-sm-2">
                                        <label>Biển số</label>
                                    </div>
                                    <div class="col-sm-8">
                                        <input type="text" name="number" id="in-vc_vehicle_number" class="form-control" placeholder="Biển số (Nếu có)">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="col-sm-2">
                                        <label>Mã thẻ</label>
                                    </div>
                                    <div class="col-sm-8">
                                        <input type="text" name="code" id="code_vehicle" class="form-control" placeholder="Mã thẻ">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="col-sm-2">
                                        <label>Mô tả</label>
                                    </div>
                                    <div class="col-sm-8">
                                        <textarea name="description" id="textarea-vc_description" class="form-control" cols="30" rows="5" placeholder="Mô tả phương tiện"></textarea>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="col-sm-2">
                                        <label>Ngày áp dụng tính phí</label>
                                    </div>
                                    <div class="col-sm-8">
                                        <input type="date"
                                               class="form-control pull-right date_picker"
                                               id="first_time_active"
                                               name="first_time_active">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="col-sm-2">
                                        <label>Ngày kết thúc tính phí</label>
                                    </div>
                                    <div class="col-sm-8">
                                        <input type="date"
                                               class="form-control pull-right date_picker"
                                               id="finish"
                                               name="finish">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="col-sm-2">
                                        <label>Mức ưu tiên tính phí</label>
                                    </div>
                                    <div class="col-sm-8">
                                        <select name="progressive_price_id" class="form-control" id="progressive_price_id">
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group" style="display: none" >
                                    <div class="col-sm-2">
                                        <label>Mức ưu tiên tính phí</label>
                                    </div>
                                    <div class="col-sm-8">
                                        <input type="number" readonly name="priority_level" id="priority_level" class="form-control" placeholder="Mức ưu tiên tính phí">
                                    </div>
                                </div>
                                <div class="form-group" style="display: none">
                                    <div class="col-sm-2">
                                        <label>Phí</label>
                                    </div>
                                    <div class="col-sm-8">
                                        <input type="text" readonly name="priority_price" id="priority_price" class="form-control" placeholder="Phí">
{{--                                        <input type="hidden" name="progressive_price_id" id="progressive_price_id">--}}
                                        <input type="hidden" name="service_id" id="service_id">
                                        <input type="hidden" name="bdc_price_type_id" id="bdc_price_type_id">
                                        <input type="hidden" name="service_name" id="service_name">
                                        <input type="hidden" name="bdc_progressive_id" id="bdc_progressive_id">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="col-sm-2">
                                        <label for="in-re_name">Trạng thái</label>
                                    </div>
                                    <div class="col-sm-8">
                                        <input type="checkbox" name="status" checked>
                                    </div>
                                </div>
                                <div class="form-group hidden">
                                    <div class="col-sm-2">
                                        <label>Ảnh</label>
                                    </div>
                                    <div class="col-sm-8">
                                        <div class="input-group input-image" data-file="image">
                                            <input type="text" name="vc_image" id="in-vc_image" value="" class="form-control"><span class="input-group-btn"><button type="button" class="btn btn-primary">Chọn</button></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger pull-right" data-dismiss="modal"><i class="fa fa-close"></i>&nbsp;&nbsp;Hủy</button>
                        <button type="button" class="btn btn-primary btn-js-action-vehicle" form="form-add-verhicle" style="margin-right: 5px;"><i class="fa fa-save"></i>&nbsp;&nbsp;Xác nhận</button>
                    </div>
                </div>
            </form>
        @endif
    </div>
</div>

{{--@section('javascript')--}}
{{--    <script>--}}
{{--        sidebar('event', 'index');--}}
{{--    </script>--}}
{{--    <script>--}}
{{--        $(function () {--}}
{{--            get_data_select_apartment1({--}}
{{--                object: '#ip-place_id',--}}
{{--                url: '{{ url('admin/apartments/ajax_get_building_place') }}',--}}
{{--                data_id: 'id',--}}
{{--                data_text: 'name',--}}
{{--                data_code: 'code',--}}
{{--                title_default: 'Chọn tòa nhà'--}}
{{--            });--}}
{{--            function get_data_select_apartment1(options) {--}}
{{--                $(options.object).select2({--}}
{{--                    ajax: {--}}
{{--                        url: options.url,--}}
{{--                        dataType: 'json',--}}
{{--                        data: function(params) {--}}
{{--                            var query = {--}}
{{--                                search: params.term,--}}
{{--                            }--}}
{{--                            return query;--}}
{{--                        },--}}
{{--                        processResults: function(json, params) {--}}
{{--                            var results = [{--}}
{{--                                id: '',--}}
{{--                                text: options.title_default--}}
{{--                            }];--}}

{{--                            for (i in json.data) {--}}
{{--                                var item = json.data[i];--}}
{{--                                results.push({--}}
{{--                                    id: item[options.data_id],--}}
{{--                                    text: item[options.data_text]+' - '+item[options.data_code]--}}
{{--                                });--}}
{{--                            }--}}
{{--                            return {--}}
{{--                                results: results,--}}
{{--                            };--}}
{{--                        },--}}
{{--                        minimumInputLength: 3,--}}
{{--                    }--}}
{{--                });--}}
{{--            }--}}
{{--            get_data_select({--}}
{{--                object: '#ip-apartment',--}}
{{--                url: '{{ url('admin/apartments/ajax_get_apartment') }}',--}}
{{--                data_id: 'id',--}}
{{--                data_text: 'name',--}}
{{--                title_default: 'Chọn căn hộ'--}}
{{--            });--}}
{{--            get_data_select({--}}
{{--                object: '#ip-cate,#select-vc_type',--}}
{{--                url: '{{ url('admin/vehiclecategory/ajax_get_vehicle_cate') }}',--}}
{{--                data_id: 'id',--}}
{{--                data_text: 'name',--}}
{{--                title_default: 'Chọn loại phương tiện'--}}
{{--            });--}}

{{--            $("#ip-place_id").on('change', function(){--}}
{{--                if($("#ip-place_id").val()){--}}
{{--                    get_data_select({--}}
{{--                        object: '#ip-ap_id',--}}
{{--                        url: '{{ url('admin/apartments/ajax_get_apartment_with_place') }}',--}}
{{--                        data_id: 'id',--}}
{{--                        data_text: 'name',--}}
{{--                        title_default: 'Chọn căn hộ'--}}
{{--                    });--}}
{{--                }--}}
{{--            });--}}
{{--            $('#select-vc_type').on('change',function (){--}}
{{--                // console.log(1234);--}}
{{--                // console.log($('#select-vc_type').val());--}}
{{--                // console.log($('#ip-ap_id').val());--}}
{{--                // if ($('#select-vc_type').val() && $('#ip-apartment').val()) {--}}
{{--                $.ajax({--}}
{{--                    url: '/admin/vehicles/getPriceVehicle',--}}
{{--                    type: 'POST',--}}
{{--                    data: {--}}
{{--                        apartment_id: $('#ip-ap_id').val(),--}}
{{--                        vehicle_category_id: $('#select-vc_type').val()--}}
{{--                    },--}}
{{--                    success: function (res) {--}}
{{--                        console.log("res", res);--}}
{{--                        $('#priority_level').val(res.data.priority_level)--}}
{{--                        $('#priority_price').val(res.data.price)--}}
{{--                        $('#progressive_price_id').val(res.data.progressive_price_id);--}}
{{--                        $('#service_id').val(res.data.service_id);--}}
{{--                        $('#bdc_price_type_id').val(res.data.bdc_price_type_id);--}}
{{--                        $('#service_name').val(res.data.service_name);--}}
{{--                        $('#bdc_progressive_id').val(res.data.bdc_progressive_id);--}}
{{--                    },--}}
{{--                    error: function (e) {--}}
{{--                        console.log(e);--}}
{{--                    }--}}
{{--                })--}}
{{--                // }--}}
{{--            });--}}
{{--            function get_data_select(options) {--}}
{{--                $(options.object).select2({--}}
{{--                    ajax: {--}}
{{--                        url: options.url,--}}
{{--                        dataType: 'json',--}}
{{--                        data: function(params) {--}}
{{--                            var query = {--}}
{{--                                search: params.term,--}}
{{--                                place_id: $("#ip-place_id").val(),--}}
{{--                            }--}}
{{--                            return query;--}}
{{--                        },--}}
{{--                        processResults: function(json, params) {--}}
{{--                            var results = [{--}}
{{--                                id: '',--}}
{{--                                text: options.title_default--}}
{{--                            }];--}}

{{--                            for (i in json.data) {--}}
{{--                                var item = json.data[i];--}}
{{--                                results.push({--}}
{{--                                    id: item[options.data_id],--}}
{{--                                    text: item[options.data_text]--}}
{{--                                });--}}
{{--                            }--}}
{{--                            return {--}}
{{--                                results: results,--}}
{{--                            };--}}
{{--                        },--}}
{{--                        minimumInputLength: 3,--}}
{{--                    }--}}
{{--                });--}}
{{--            }--}}

{{--            $(".btn-js-action-vehicle").on('click',function () {--}}
{{--                var _this = $(this);--}}
{{--                $(".alert_pop_add_vehicle").hide();--}}
{{--                _this.attr('type','button');--}}
{{--                var vehicle_number = $("#in-vc_vehicle_number").val();--}}
{{--                var name = $("#in-vc_name").val();--}}
{{--                var type = $("#select-vc_type").val();--}}
{{--                var apt = $("#ip-ap_id").val();--}}
{{--                var html = '';--}}
{{--                let first_time_active = $("#first_time_active").val();--}}
{{--                if(name.length <=2 || name.length >=50){--}}
{{--                    html+='<li>Tên phương tiện không được nhỏ hơn 3 hoặc lớn hơn 50 ký tự</li>';--}}
{{--                }if(vehicle_number == '' && (vehicle_number.length <=5 || vehicle_number.length >=10)){--}}
{{--                    html+='<li>Biển số không được nhỏ hơn 6 hoặc lớn hơn 12 ký tự</li>';--}}
{{--                }if(apt == ''){--}}
{{--                    html+='<li>Trường Căn hộ không được để trống</li>';--}}
{{--                }if(type == ''){--}}
{{--                    html+='<li>Trường loại phương tiện không được để trống</li>';--}}
{{--                }--}}
{{--                if (first_time_active == '') {--}}
{{--                    html+='<li>Ngày áp dụng tính phí không được để trống</li>';--}}
{{--                }--}}

{{--                $.get('{{ route('admin.vehicles.ajax_check_number') }}', {--}}
{{--                    type: vehicle_number--}}
{{--                }, function(data) {--}}
{{--                    if(data.status == 1){--}}
{{--                        html+='<li>Biển số xe đã tồn tại trên hệ thống vui lòng kiểm tra lại</li>';--}}
{{--                    }--}}
{{--                });--}}
{{--                showLoading();--}}
{{--                setTimeout(function(){--}}
{{--                    if(html != ''){--}}
{{--                        $(".alert_pop_add_vehicle").show();--}}
{{--                        $(".alert_pop_add_vehicle ul").html(html);--}}
{{--                        hideLoading();--}}
{{--                    }else{--}}
{{--                        $('#form-add-verhicle').submit();--}}
{{--                    }--}}
{{--                }, 600);--}}

{{--            });--}}

{{--        });--}}
{{--        sidebar('Customers', 'index');--}}
{{--    </script>--}}
{{--@endsection--}}
