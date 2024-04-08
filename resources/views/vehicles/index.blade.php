@extends('backend.layouts.master')

@section('content')

    <section class="content-header">
        <h1>
            Phương tiện
            @if( in_array('admin.vehicles.index_import',@$user_access_router)) <a href="{{ route('admin.vehicles.index_import') }}" class="btn btn-success"><i class="fa fa-file-excel-o"></i>&nbsp;&nbsp;Import Exel</a> @endif
            <a href="{{ route('admin.vehicles.export',Request::all()) }}" class="btn btn-success"><i class="fa fa-pencil-square-o"></i>&nbsp;&nbsp;Xuất ra Excel</a>
            <a href="{{ route('admin.vehicles.report_export',Request::all()) }}" class="btn btn-success"><i class="fa fa-pencil-square-o"></i>&nbsp;&nbsp;Báo cáo tổng hợp phương tiện</a>
            <p class="display_mes_summit @if($data_error) error_mes @elseif($data_success) success_mes @endif"> {{$data_vhc}} </p>
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('admin.home') }}"><i class="fa fa-home"></i> Trang chủ</a></li>
            <li class="active">Phương tiện</li>
        </ol>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="clearfix"></div>
                <ul class="nav nav-tabs" role="tablist">
                    <li class="{{$tab==''?'active':''}}"><a href="#general" role="tab" data-toggle="tab">Danh sách phương tiện</a></li>
                    <li class="{{$tab=='Category'?'active':''}}"><a href="#category" role="tab" data-toggle="tab">Loại phương tiện</a></li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane {{$tab==''?'active':''}}" id="general" style="padding: 15px 0;">
                        @include('vehicles.tabs.vehicle')
                    </div>
                    <div class="tab-pane {{$tab=='Category'?'active':''}} " id="category" style="padding: 15px 0;">
                        @include('vehicles.tabs.vehicle-category')
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('javascript')
    <script>
        sidebar('event', 'index');
    </script>
    <script>
        $(function () {
            get_data_select_apartment1({
                object: '#ip-place_id,#place_id',
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
            $('#select-vc_type').on('change',function (){
                $.ajax({
                    url: '/admin/vehicles/getPriceVehicle',
                    type: 'POST',
                    data: {
                        apartment_id: $('#ip-ap_id').val(),
                        vehicle_category_id: $('#select-vc_type').val()
                    },
                    success: function (res) {
                        let {progressive_prices, progressivePrice: progressive_price} = res.data;

                        let select_progressive_prices = "";
                        progressive_prices.forEach((item)=>{
                            const {price, id, name, priority_level} = item;
                            const {priority_level: priority_level1} = progressive_price;
                            if (priority_level === priority_level1) {
                                select_progressive_prices += `<option value=${id} selected>${name} - ${price} VNĐ - Mức ${priority_level||1}</option>`;
                            }
                            else {
                                if (priority_level < priority_level1) {
                                    select_progressive_prices += `<option value=${id} >${name} - ${price} VNĐ - Mức ${priority_level||1}</option>`;
                                }
                            }
                        })
                        let list_progressives = $('#progressive_price_id');
                        list_progressives.empty();
                        list_progressives.prepend(select_progressive_prices);
                    },
                    error: function (e) {
                        console.log(e);
                    }
                })
                // }
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

            $(".btn-js-action-vehicle").on('click',function () {
                let _this = $(this);
                let alert_pop_add_vehicle = $('.alert_pop_add_vehicle');
                alert_pop_add_vehicle.hide();
                _this.attr('type','button');
                let vehicle_number = $("#in-vc_vehicle_number").val();
                let name = $("#in-vc_name").val();
                let type = $("#select-vc_type").val();
                let apt = $("#ip-ap_id").val();
                let html = '';
                let first_time_active = $("#first_time_active").val();
                if(name.length <=2 || name.length >=50){
                    html+='<li>Tên phương tiện không được nhỏ hơn 3 hoặc lớn hơn 50 ký tự</li>';
                }if(vehicle_number == '' && (vehicle_number.length <=5 || vehicle_number.length >=13)){
                    html+='<li>Biển số không được nhỏ hơn 6 hoặc lớn hơn 12 ký tự</li>';
                }if(apt == ''){
                    html+='<li>Trường Căn hộ không được để trống</li>';
                }if(type == ''){
                    html+='<li>Trường loại phương tiện không được để trống</li>';
                }
                if (first_time_active == '') {
                    html+='<li>Ngày áp dụng tính phí không được để trống</li>';
                }

                if(html != ''){
                    $(".alert_pop_add_vehicle").show();
                    $(".alert_pop_add_vehicle ul").html(html);
                    hideLoading();
                    return;
                }

                showLoading();

                $.ajax({
                    url: '/admin/vehicles/checkNumberVehicle',
                    type: 'POST',
                    data: {
                        'number':  vehicle_number,
                        'cate_vehicle':  $('#select-vc_type').val(),
                        'apartment_id':  $('#ip-ap_id').val()
                    },
                    success: function (res) {
                        if (res.data.count === 0) {
                            $('#form-add-verhicle').submit();
                        }
                        else {
                            html+='<li>Biển số xe đã tồn tại trên hệ thống vui lòng kiểm tra lại</li>';
                            $(".alert_pop_add_vehicle").show();
                            $(".alert_pop_add_vehicle ul").html(html);
                            hideLoading();
                        }
                    }
                })

            });

        });
        sidebar('Customers', 'index');

        $(function () {
            function removeProgressPrice(that) {
                $(that).closest(".progress_price_items").remove();
                $(".progress_price_list").find(".progress_price_items").each(function (key, value) {
                    $(this).find("input").each(function () {
                        this.name = this.name.replace(/\d+/, key);
                    });
                });
            }

            $(".add_progress_price_item").click(function (e) {
                var avails = $(".progress_price_items");
                var clone = avails.eq(0).clone();
                $(".progress_price_list").append(clone).find(".progress_price_items").each(function (key, value) {
                    $(this).find("input").each(function () {
                        this.name = this.name.replace(/\d+/, key);
                    });
                });
                e.preventDefault();
            });

            $(".bdc_price_type_id_category").on("change", function () {
                var priceTypeValue = $(this).val();
                console.log(1234);
                console.log(priceTypeValue);
                if (priceTypeValue == 1) {
                    $(".add_progress_price_item").prop("disabled", true);
                    $(".add_progress_price_item").css("display", "none");
                    var avails = $(".temp_one_progress_price_items");
                    var clone = avails.eq(0).clone();
                    $(".progress_price_list").html(clone);
                } else {
                    $(".add_progress_price_item").prop("disabled", false);
                    $(".add_progress_price_item").css("display", "block");
                    var avails = $(".progress_price_items");
                    var clone = avails.eq(0).clone();
                    $(".progress_price_list").html(clone);
                }
            });

            $(".btn-js-action-vehiclecate").on('click', function () {
                var _this = $(this);
                $(".alert_pop_add_vehiclecate").hide();
                var code = $("#in-vccname").val();
                if (code.length <= 3 || code.length >= 45) {
                    $(".alert_pop_add_vehiclecate").show();
                    $(".alert_pop_add_vehiclecate ul").html('<li>Tên loại phương tiện không được nhỏ hơn 3 hoặc lớn hơn 45 ký tự</li>')
                } else {
                    showLoading();
                    $.ajax({
                        url: '/admin/vehiclecategory/checkVehicleNameCategory',
                        type: 'POST',
                        data: {
                            'name': code
                        },
                        success: function (res) {
                            console.log(res);
                            if(res.data.count===0) {
                                let progress = [];
                                $('.progress_price_list .progress_price_item').each((index, e) => {
                                    console.log(e);
                                    console.log($(e).find('.progress_from').val());
                                    progress.push({
                                        from: $(e).find('.progress_from').val(),
                                        to: $(e).find('.progress_to').val(),
                                        price: $(e).find('.progress_price').val()
                                    });

                                });

                                progress = JSON.stringify(progress);

                                $('#progressive_price').val(progress);

                                $("#form-add-vehiclecate").submit();
                            }
                            else {
                                $(".alert_pop_add_vehiclecate").show();
                                $(".alert_pop_add_vehiclecate ul").html('<li>Tên loại phương tiện đã tồn tại</li>')
                                hideLoading();
                            }
                        },
                        error: function (e) {
                            console.log(e)
                        }
                    })


                }
            });

            $(document).on('click', '.onoffswitch-label', function (e) {
                let div = $(this).parents('div.onoffswitch');
                let input = div.find('input');
                let id = input.attr('data-id');
                let checked = 1;
                if (confirm('Thay đổi trạng thái sẽ ảnh hưởng tới cư dân')) {
                    if (input.attr('checked')) {
                        checked = 0;
                    } else {
                        checked = 1;
                    }
                    showLoading();
                    $.ajax({
                        url: input.attr('data-url'),
                        type: 'PUT',
                        data: {
                            id: id,
                            status: checked
                        },
                        success: function (response) {
                            if (response.success == true) {
                                $('form#form-search-customer').submit();
                                toastr.success(response.message);
                                setTimeout(function() {
                                    location.reload();
                                }, 1000);
                            } else {
                                toastr.error('Không thay đổi trạng thái');
                                e.preventDefault();
                            }
                            hideLoading();
                        }
                    });
                }
                else {
                    e.preventDefault();
                }
            });

        });
    </script>
@endsection
