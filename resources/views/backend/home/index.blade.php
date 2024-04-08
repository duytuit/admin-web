@extends('backend.layouts.master')

@section('content')
    <div class="page-title-breadcrumb">
        <div class="clearfix"></div>
        @include('backend.home.layout-home')
    </div>
    <div class="page-content">
    </div>
    <input type="hidden" value="{{ isset($loai_danh_muc_all) ? json_encode($loai_danh_muc_all) : '' }}"
        id="loai_danh_muc_all">
    <input type="hidden" value="{{ isset($trang_thai) ? json_encode($trang_thai) : '' }}" id="trang_thai_all">
@endsection
<style>
#legend_getCashFlow > ul > li{
     margin-bottom: 0 !important;
} 
#legend_getCashFlow > ul > li > span{
    height: 10px !important;
} 
</style>
@section('javascript')
    <script type="text/javascript"
        src="{{ url('adminLTE/js/function_dxmb.js') . '?v=' . \Carbon\Carbon::now()->timestamp }}"></script>
    <script>
        async function get_StatPayment() {
            let method = 'get';
            let param_query_old = "{{ $array_search }}";
            let param_query = param_query_old.replaceAll("&amp;", "&")
            var getStatPayment = await call_api(method, 'admin/getStatPayment' + param_query);
            if (getStatPayment.data.length > 0) {
                let cycle_names = getStatPayment.data.map(a => a.cycle_name);
                let coins = getStatPayment.data.map(a => a.coin);
                let sumerys = getStatPayment.data.map(a => a.sumery);
                let paid_by_cycle_names = getStatPayment.data.map(a => a.paid_by_cycle_name);
                var ctx = document.getElementById("_getStatPayment");
                if (cycle_names.length > 0) {
                    window._getStatPayment = new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: cycle_names,
                            datasets: [{
                                    "label": "Phải thu trong kỳ",
                                    "backgroundColor": ["#FD9670"],
                                    "data": sumerys
                                },
                                {
                                    "label": "Thực thu trong kỳ",
                                    "backgroundColor": ["#6997F8"],
                                    "data": paid_by_cycle_names
                                },
                                {
                                    "label": "Thu dư trong kỳ",
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
                                        "text": "vnđ"
                                    },
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
                }

            }
        }
        async function get_CashFlow() {
            let method = 'get';
            let param_query_old = "{{ $array_search }}";
            let param_query = param_query_old.replaceAll("&amp;", "&")
            var getCashFlow = await call_api(method, 'admin/getCashFlow' + param_query);
            if (getCashFlow.data.length > 0) {
                let sum_cost = 0;
                let list_payments = [];
                var loai_danh_muc_all = $('#loai_danh_muc_all').val();
                var obj_loai_danh_muc_all = JSON.parse(loai_danh_muc_all);
                getCashFlow.data.forEach(element => {
                    sum_cost += element.cost;
                    list_payments.push(obj_loai_danh_muc_all[element.type_payment]);
                });
                let costs = getCashFlow.data.map(a => a.cost);
                var configd_getCashFlow = {
                    type: 'doughnut',
                    data: {
                        datasets: [{
                            "label": "",
                            "backgroundColor": ["#DEB0B2", "#D1C7A0",
                                "#FFCC33", "#33CC00", "#FF99FF", "#CC9900", "#CC9999", "#999900",
                                "#669900", "#CC6633", "#FF0066", "#00DD00", "#330066"
                            ],
                            "data": costs
                        }],
                        labels: list_payments,
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
                                        "text": "vnđ"
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
                }

            }
        }
        async function get_StatFeedback() {
            let method = 'get';
            let param_query_old = "{{ $array_search }}";
            let param_query = param_query_old.replaceAll("&amp;", "&")
            var getStatFeedback = await call_api(method, 'admin/getStatFeedback' + param_query);
            if (getStatFeedback.data.length > 0) {
                let list_status = [];
                var trang_thai_all = $('#trang_thai_all').val();
                var obj_trang_thai_all = JSON.parse(trang_thai_all);
                getStatFeedback.data.forEach(element => {
                    list_status.push(obj_trang_thai_all[element.status]);
                });
                let counts = getStatFeedback.data.map(a => a.count);
                var ctx = document.getElementById("_getStatFeedback");
                window._getStatFeedback = new Chart(ctx, {
                    type: 'pie',
                    data: {
                        labels: list_status,
                        datasets: [{
                            "label": "",
                            "backgroundColor": ["#CC9999", "#999900",
                                "#669900", "#CC6633", "#FF0066", "#00DD00", "#330066"
                            ],
                            "data": counts
                        }],
                        options: {
                            "responsive": true
                        }
                    }

                });
            }

        }
        async function get_NotifyEvent() {
            let method = 'get';
            let param_query_old = "{{ $array_search }}";
            let param_query = param_query_old.replaceAll("&amp;", "&")
            var getNotifyEvent = await call_api(method, 'admin/getNotifyEvent' + param_query);
            if (getNotifyEvent) {
                $("#notify_count").text(getNotifyEvent.data.count);
                $("#category_id").text(getNotifyEvent.data.category_id);
                $("#interaction_user_comment").text(Object.keys(getNotifyEvent.data).length === 0 && getNotifyEvent.data.constructor === Object ? getNotifyEvent.data.interaction_user + " / " + getNotifyEvent.data.interaction_comment : 0);
            }
        }
        async function get_StatVehicle() {
            let method = 'get';
            let param_query_old = "{{ $array_search }}";
            let param_query = param_query_old.replaceAll("&amp;", "&")
            var getStatVehicle = await call_api(method, 'admin/getStatVehicle' + param_query);
            if (getStatVehicle.data.length > 0) {
                let sum_count = 0;
                getStatVehicle.data.forEach(element => {
                    sum_count += element.count;
                });
                let names = getStatVehicle.data.map(a => a.name);
                let counts = getStatVehicle.data.map(a => a.count);
                var configd_getStatVehicle = {
                    type: 'doughnut',
                    data: {
                        datasets: [{
                            "label": "",
                            "backgroundColor": ["#3214C1", "#A464CF", "#91E2EE", "#DEB0B2", "#D1C7A0",
                                "#FFCC33", "#33CC00", "#FF99FF", "#CC9900", "#CC9999", "#999900",
                                "#669900", "#CC6633", "#FF0066", "#00DD00", "#330066"
                            ],
                            "data": counts
                        }],
                        labels: names,
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            htmlLegend: {
                                // ID of the container to put the legend in
                                containerID: 'legend_getStatVehicle',
                            },
                            legend: {
                                display: false,
                            }
                        }
                    },
                    plugins: [htmlLegendPlugin],
                };
                var ctx_getStatVehicle = document.getElementById("_getStatVehicle");
                window._getStatVehicle = new Chart(ctx_getStatVehicle, configd_getStatVehicle);
            }
        }
        async function get_StatBuilding() {
            let method = 'get';
            let param_query_old = "{{ $array_search }}";
            let param_query = param_query_old.replaceAll("&amp;", "&")
            var getStatBuilding = await call_api(method, 'admin/getStatBuilding' + param_query);
            if (getStatBuilding) {
                $("#apartment_count").text(getStatBuilding.data.apartment);
                $("#uses_count").text(getStatBuilding.data.user);
                $("#vehicle_count").text(getStatBuilding.data.vehicle);
                $("#login_app").text(getStatBuilding.data.login_app);
            }
        }
        async function get_StatVehicleReg() {
            let method = 'get';
            let param_query_old = "{{ $array_search }}";
            let param_query = param_query_old.replaceAll("&amp;", "&")
            var getStatVehicleReg = await call_api(method, 'admin/getStatVehicleReg' + param_query);
            if (getStatVehicleReg.data.length > 0) {
                let cycle_names = getStatVehicleReg.data.map(a => a.date);
                let registers = getStatVehicleReg.data.map(a => a.register);
                let cancels = getStatVehicleReg.data.map(a => a.cancel);
                var ctx = document.getElementById("_getStatVehicleReg");
                if (cycle_names.length > 0) {
                    window._getStatVehicleReg = new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: cycle_names,
                            datasets: [{
                                    "label": "Vào",
                                    "backgroundColor": ["#FD9670"],
                                    "data": registers
                                },
                                {
                                    "label": "Ra",
                                    "backgroundColor": ["#6997F8"],
                                    "data": cancels
                                }
                            ]
                        },
                        options: {
                            "responsive": true,
                            "scales": {
                                "y": {
                                    "title": {
                                        "display": true,
                                        "text": "Xe"
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
                }

            }
        }
        $(document).ready(function() {
            get_StatPayment();
            get_CashFlow();
            get_BalanceChange();
            get_StatFeedback();
            get_NotifyEvent();
            get_StatVehicle()
            get_StatBuilding();
            get_StatVehicleReg();
        });
    </script>
@endsection
