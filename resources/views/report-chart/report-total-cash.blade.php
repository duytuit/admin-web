@extends('backend.layouts.master')

@section('content')
<section class="content-header">
    <h1>
        Báo cáo thông kê
        <small>Báo cáo thông kê</small>
    </h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.home') }}"><i class="fa fa-home"></i> Trang chủ</a></li>
        <li class="active">Báo cáo thông kê</li>
    </ol>
</section>
<section class="content">
        <form id="form-search-advance" action="{{route('admin.report-chart.report_cash')}}" method="get">
            <div id="search-advance" class="search-advance">
                <div class="row">
                        <div class="col-sm-2">
                            <select class="form-control" name="type_service"  id="type_service" style="width: 100%;">
                                <option value="" selected>-- Chọn loại dịch vụ --</option>
                                <option value="0" @if(isset($filter['type_service']) && $filter['type_service'] == 0) selected @endif>Phí khác</option>  
                                <option value="5" @if(isset($filter['type_service']) && $filter['type_service'] == 5) selected @endif>Điện</option>
                                <option value="2" @if(isset($filter['type_service']) && $filter['type_service'] == 2) selected @endif>Phí dịch vụ</option>
                                <option value="3" @if(isset($filter['type_service']) && $filter['type_service'] == 3) selected @endif>Nước</option> 
                                <option value="4" @if(isset($filter['type_service']) && $filter['type_service'] == 4) selected @endif>Phương tiện</option>                                        
                            </select>
                        </div>
                        <div class="col-sm-2">
                                <select name="service_id" id="bdc_service_id" class="form-control" style="width: 100%;">
                                    <option value="" selected>Dịch vụ...</option>
                                    <?php $get_service = isset($get_service) ? $get_service : '' ?>
                                        @if($get_service)
                                        <option value="{{$get_service->id}}" selected>{{$get_service->name}}</option>
                                        @endif
                                </select>
                        </div>
                        <div class="col-sm-2">
                            <select name="building_place" id="ip-place_id" class="form-control" style="width: 100%;">
                                <option value="">Chọn tòa nhà</option>
                                <?php $place_building = isset($get_place_building) ? $get_place_building : '' ?>
                                @if($place_building)
                                <option value="{{$place_building->id}}" selected>{{$place_building->name}}</option>
                                @endif
                            </select>
                        </div>
                        <div class="col-sm-2">
                            <select name="apartment_id" id="ip-apartment"  class="form-control" style="width: 100%;">
                                <option value="">Căn hộ</option>
                                    <?php $apartment = isset($get_apartment) ? $get_apartment: '' ?>
                                @if($apartment)
                                <option value="{{$apartment->id}}" selected>{{$apartment->name}}</option>
                                @endif
                            </select>
                        </div>
                        <div class="col-sm-2">
                            <button type="submit" class="btn btn-info"><i class="fa fa-search"></i> Tìm kiếm</button>
                        </div>
                </div>
            </div>
        </form>
        <div class="row">
            <div class="col-xl-7 col-lg-7 col-md-12 block1" style="height: 500px;">
                <div class="row">
                    <div class="col-sm-12">
                        <h3>Báo cáo doanh thu</h3>
                        <div class="col-sm-12 chart_1">
                            <canvas id="_getStatPayment"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-5 col-lg-5 col-md-12 block1 chart_10">
                <div class="row">
                    <div class="col-sm-12">
                        <h3>Tổng hợp dòng tiền</h3>
                        <div class="_container">
                            <div class="item item--1"> 
                                  <div class="chart_12">
                                    <canvas id="_getCashFlow"></canvas>
                                  </div>
                            </div>
                            <div class="item item--4">
                                <div class="col-sm-12">
                                    <div id="legend_getCashFlow"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
      <input type="hidden" value="{{ isset($loai_danh_muc) ? json_encode($loai_danh_muc) : '' }}" id="loai_danh_muc_all">
</section>
@endsection
@section('javascript')
<script type="text/javascript" src="{{ url('adminLTE/js/function_dxmb.js') . "?v=" . \Carbon\Carbon::now()->timestamp }}"></script>
<script>
      $(function(){
            $("#type_service").on('change', function(){ 
                if($("#type_service").val()){
                    get_data_select_service({
                    object:  '#bdc_service_id,#ip_place_id',
                    url: '{{ url('admin/service-apartment/ajax_get_service') }}',
                    data_id: 'id',
                    data_text: 'name',
                    title_default: 'Chọn dịch vụ'
                    });
                }
            });
            get_data_select_service({
                object: '#bdc_service_id,#ip_place_id',
                url: '{{ url('admin/service-apartment/ajax_get_service') }}',
                data_id: 'id',
                data_text: 'name',
                title_default: 'Chọn dịch vụ'
            });
            function get_data_select_service(options) {
                $(options.object).select2({
                    ajax: {
                        url: options.url,
                        dataType: 'json',
                        data: function(params) {
                            var query = {
                                search: params.term,
                                type_service:$("#type_service").val(),
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
             get_data_select_apartment1({
                object: '#ip-place_id,#ip_place_id',
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
                object: '#ip-apartment,#ip_apartment',
                url: '{{ url('admin/apartments/ajax_get_apartment') }}',
                data_id: 'id',
                data_text: 'name',
                title_default: 'Chọn căn hộ'
            });
            $("#ip-place_id").on('change', function(){ 
                if($("#ip-place_id").val()){
                    get_data_select({
                    object: '#ip-apartment,#ip_apartment',
                    url: '{{ url('admin/apartments/ajax_get_apartment_with_place') }}',
                    data_id: 'id',
                    data_text: 'name',
                    title_default: 'Chọn căn hộ'
                    });
                }
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
           
        })
        async function get_StatPayment() {
            let method = 'get';
            let param_query_old = "{{ $array_search }}";
            let param_query = param_query_old.replaceAll("&amp;", "&")
            var getStatPayment = await call_api(method, 'admin/getStatPayment' + param_query);
            if (getStatPayment.data.length > 0) {
             
                let cycle_names = getStatPayment.data.map(a => a.cycle_name);
                let sumerys = getStatPayment.data.map(a => a.sumery);
                let paid_by_cycle_names = getStatPayment.data.map(a => a.paid_by_cycle_name);
                let coins = getStatPayment.data.map(a => a.coin);

                var ctx = document.getElementById("_getStatPayment");
                if (cycle_names.length > 0) {
                    window._getStatPayment = new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: cycle_names,
                            datasets: [
                                {
                                    "label": "Phát sinh",
                                    "backgroundColor": ["#FD9670"],
                                    "data": sumerys
                                },
                                {
                                    "label": "Thanh toán",
                                    "backgroundColor": ["#6997F8"],
                                    "data": paid_by_cycle_names
                                },
                                {
                                    "label": "Tiền thừa",
                                    "backgroundColor": ["#CE00FF"],
                                    "data": coins
                                }
                            ]
                        },
                        options: {
                            "responsive": true,
                            "scales": {
                                "y": {
                                    "title": {
                                        "display": true,
                                        "text": "VND"
                                    }
                                },
                                "x": {
                                    "title": {
                                        "display": true,
                                        "text": "Tháng"
                                    }
                                }
                            }
                        }
                    });
                    window._getStatPayment.options.animation.onComplete= function() {
                        ctx = this.ctx;
                        ctx.font = Chart.helpers.fontString(Chart.defaults.font.size, Chart.defaults.font.style, Chart.defaults.font.family);
                        ctx.textAlign = 'center';
                        ctx.textBaseline = 'bottom';
                        chartinst = this;
                        let line = 0;             
                            this.data.datasets.forEach(function(dataset, i) {
                                line += 12;
                                ctx.fillStyle = dataset.backgroundColor[0];
                                if(chartinst.isDatasetVisible(i)){
                                    var meta = chartinst.getDatasetMeta(i);
                                    meta.data.forEach(function(bar, index) {
                                        var data = dataset.data[index];
                                        ctx.fillText(formatCurrencyV2(data.toString()), bar.x, bar.y - line);
                                    });
                                }
                            });
                    }
                }

            }
        }
        async function get_CashFlow() {
            let method = 'get';
            let param_query_old = "{{ $array_search }}";
            let param_query = param_query_old.replaceAll("&amp;", "&")
            var getCashFlow = await call_api(method, 'admin/getCashFlow' + param_query);
            if (getCashFlow.data.length > 0) {
                let list_payment = [];
                let sum_cost = 0;
                var loai_danh_muc_all = $('#loai_danh_muc_all').val();
                var obj_loai_danh_muc_all = JSON.parse(loai_danh_muc_all);
                getCashFlow.data.forEach(element => {
                    sum_cost+= element.cost;
                    list_payment.push(obj_loai_danh_muc_all[element.type_payment]);
                });
                let costs = getCashFlow.data.map(a => a.cost);
                var configd_getCashFlow = {
                    type: 'pie',
                    data: {
                        datasets: [{
                            "label": "",
                            "backgroundColor": ["#3214C1", "#A464CF", "#91E2EE", "#DEB0B2", "#D1C7A0",
                                "#FFCC33", "#33CC00", "#FF99FF", "#CC9900", "#CC9999", "#999900",
                                "#669900", "#CC6633", "#FF0066", "#00DD00", "#330066"
                            ],
                            "data": costs
                        }],
                        labels: list_payment
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            htmlLegend: {
                                // ID of the container to put the legend in
                                containerID: 'legend_getCashFlow',
                            },
                            legend: {
                                display: false,
                            }
                        }
                    },
                    plugins: [htmlLegendPlugin],
                };
                var ctx_getCashFlow = document.getElementById("_getCashFlow");
                window._getCashFlow = new Chart(ctx_getCashFlow, configd_getCashFlow);
            }

        }
        $(document).ready(function() {
            get_StatPayment();
            get_CashFlow();
        });
    </script>
@endsection