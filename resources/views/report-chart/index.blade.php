@extends('backend.layouts.master')

@section('content')
<section class="content-header">
    <h3>Báo cáo thông kê</h3>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.home') }}"><i class="fa fa-home"></i> Trang chủ</a></li>
        <li class="active">Báo cáo thông kê</li>
    </ol>
</section>
<section class="content">
        <form id="form-search-advance" action="{{route('admin.report-chart.index')}}" method="get">
            <div id="search-advance" class="search-advance">
                <div class="row form-group space-5">
                    <div class="col-sm-2">
                        <select class="form-control" name="type_service"  id="type_service">
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
                        <h3>Báo cáo tỷ lệ thanh toán</h3>
                        <div class="col-sm-12 chart_1">
                            <canvas id="_getStatDebitPayment"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-5 col-lg-5 col-md-12 block1" style="height: 500px;">
                <div class="row">
                    <div class="col-sm-12">
                        <h3>Dư nợ cuối kỳ</h3>
                        <div class="col-sm-12 chart_1">
                            <canvas id="_getBalanceChange"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-xl-12 col-lg-12 col-md-12 block1">
                <div class="row">
                    <div class="col-lg-6">
                        <h3>Tỷ lệ dư nợ hiện tại theo loại DV:</h3>
                        <h4 id="tong_no_con_lai"></h4>
                        <div class="col-sm-12">
                            <div class="_container">
                                <div class="item item--1"> 
                                    <div class="chart_12">
                                        <canvas id="_getStatDebitOwe"></canvas>
                                    </div>
                                </div>
                                <div class="item item--4">
                                    <div class="col-sm-12">
                                        <div id="legend-getStatDebitOwe"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <h3>Tỷ lệ dư nợ hiện tại theo nhóm nợ: </h3>
                        <h4 id="tong_no_nhom"></h4>
                        <div class="col-sm-12">
                            <div class="_container">
                                <div class="item item--1"> 
                                      <div class="chart_12" >
                                          <canvas id="_getDebtWarring"></canvas>
                                      </div>
                                </div>
                                <div class="item item--4">
                                    <div class="col-sm-12">
                                        <div id="legend-container"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <input type="hidden" value="{{ isset($loai_phi_dich_vu) ? json_encode($loai_phi_dich_vu) : '' }}" id="loai_phi_dich_vu_all">

</section>
@endsection
<style>
</style>
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
        async function get_StatDebitPayment() {
            let method = 'get';
            let param_query_old = "{{ $array_search }}";
            let param_query = param_query_old.replaceAll("&amp;", "&")

            var getStatDebitPayment = await call_api(method, 'admin/getStatDebitPayment' + param_query);
            if (getStatDebitPayment.data.length > 0) {
                let cycle_names = getStatDebitPayment.data.map(a => a.cycle_name);
                let sumerys = getStatDebitPayment.data.map(a => a.sumery);
                let paids = getStatDebitPayment.data.map(a => a.paid);
                var ctx = document.getElementById("_getStatDebitPayment");
                if (cycle_names.length > 0) {
                    window._getStatDebitPayment = new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: cycle_names,
                            datasets: [{
                                    "label": "Phát sinh",
                                    "backgroundColor": ["#FD9670"],
                                    "data": sumerys
                                },
                                {
                                    "label": "Thanh toán",
                                    "backgroundColor": ["#6997F8"],
                                    "data": paids
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
                                    },
                                    'max' : Math.max(...sumerys)*1.2
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
                    window._getStatDebitPayment.options.animation.onComplete= function() {
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
        async function get_BalanceChange() {
            let method = 'get';
            let param_query_old = "{{ $array_search }}";
            let param_query = param_query_old.replaceAll("&amp;", "&")
            var getBalanceChange = await call_api(method, 'admin/getBalanceChange' + param_query);
            if (getBalanceChange.data.length > 0) {
                let oweds = [];
                getBalanceChange.data.forEach(element => {
                    oweds.push(element.sumery - element.paid);
                });
                let cycle_names = getBalanceChange.data.map(a => a.date);
                var ctx = document.getElementById("_getBalanceChange");
                if (cycle_names.length > 0) {
                    window._getBalanceChange = new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: cycle_names,
                            datasets: [{
                                "label": "Biến động số dư",
                                "backgroundColor": ["#FD9670"],
                                "data": oweds
                            }]
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
                    window._getBalanceChange.options.animation.onComplete= function() {
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
        async function get_StatDebitOwe() {
            let method = 'get';
            let param_query_old = "{{ $array_search }}";
            let param_query = param_query_old.replaceAll("&amp;", "&")
            var getStatDebitOwe = await call_api(method, 'admin/getStatDebitOwe' + param_query);
            if (getStatDebitOwe.data.length > 0) {
                let $name_service = [];
                let sum_owe = 0;
                var loai_phi_dich_vu_all = $('#loai_phi_dich_vu_all').val();
                var obj_loai_phi_dich_vu_all = JSON.parse(loai_phi_dich_vu_all);
                getStatDebitOwe.data.forEach(element => {
                    sum_owe+= element.owe;
                    $name_service.push(obj_loai_phi_dich_vu_all[element.type_service]);
                });
                $('#tong_no_con_lai').text('Tổng nợ còn lại: '+formatCurrencyV2(sum_owe.toString()));
                let owes = getStatDebitOwe.data.map(a => a.owe);
                var configd_getStatDebitOwe = {
                    type: 'pie',
                    data: {
                        datasets: [{
                            "label": "",
                            "backgroundColor": ["#3214C1", "#A464CF", "#91E2EE", "#DEB0B2", "#D1C7A0",
                                "#FFCC33", "#33CC00", "#FF99FF", "#CC9900", "#CC9999", "#999900",
                                "#669900", "#CC6633", "#FF0066", "#00DD00", "#330066"
                            ],
                            "data": owes
                        }],
                        labels: $name_service
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            htmlLegend: {
                                // ID of the container to put the legend in
                                containerID: 'legend-getStatDebitOwe',
                            },
                            legend: {
                                display: false,
                            }
                        }
                    },
                    plugins: [htmlLegendPlugin],
                };
                var ctx_getStatDebitOwe = document.getElementById("_getStatDebitOwe");
                window._getStatDebitOwe = new Chart(ctx_getStatDebitOwe, configd_getStatDebitOwe);
            }

        }
        async function get_DebtWarring() {
            let method = 'get';
            let param_query_old = "{{ $array_search }}";
            let param_query = param_query_old.replaceAll("&amp;", "&")
            var getDebtWarring = await call_api(method, 'admin/getDebtWarring' + param_query);
            if (getDebtWarring.data.length > 0) {
                let sum_sumery = 0;
                let debts = [];
                getDebtWarring.data.forEach(element => {
                    sum_sumery += element.sumery - element.paid;
                    let debt = element.sumery - element.paid;
                    debts.push(debt);
                });
                $('#tong_no_nhom').text('Tổng nợ nhóm: '+formatCurrencyV2(sum_sumery.toString()));
                let names = getDebtWarring.data.map(a => a.name);
                let details = getDebtWarring.data.map(a => a.detail);
                let paids = getDebtWarring.data.map(a => a.paid);
                let sumerys = getDebtWarring.data.map(a => a.sumery);
                var configd = {
                    type: 'doughnut',
                    data: {
                        datasets: [{
                            "label": "",
                            "backgroundColor": ["#3214C1", "#A464CF", "#91E2EE", "#DEB0B2", "#D1C7A0",
                                "#FFCC33", "#33CC00", "#FF99FF", "#CC9900", "#CC9999", "#999900",
                                "#669900", "#CC6633", "#FF0066", "#00DD00", "#330066"
                            ],
                            "data": debts
                        }],
                        labels: names,
                        details: details
                    },
                    options: {
                        responsive: true,
                        plugins: {
                        htmlLegend: {
                            // ID of the container to put the legend in
                            containerID: 'legend-container',
                        },
                        legend: {
                            display: false,
                        }
                        }
                       
                    },
                    plugins: [htmlLegendPlugin],
                };
                var ctx = document.getElementById("_getDebtWarring");
                window._getDebtWarring = new Chart(ctx, configd);
            }
        }
      
        $(document).ready(function() {
            get_StatDebitPayment();
            get_BalanceChange();
            get_StatDebitOwe();
            get_DebtWarring();
        });
    </script>
@endsection