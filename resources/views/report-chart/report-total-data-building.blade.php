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
    <div class="box box-primary">
        <div class="box-body font-weight-bold">
            <h3>Báo cáo thông tin căn hộ tòa nhà</h3>
        </div>
        <div class="box-body">
            <div class="row">
                <div class="col-lg-3 col-xs-6">
                    <!-- small box -->
                    <div class="small-box bg-green">
                        <div class="icon" style="color: white !important;">
                            <i class="fa fa-home"></i>
                        </div>
                        <div class="inner">
                            <h3>{{@$getStatBuilding->data->apartment}}</h3>
            
                            <p>Căn hộ</p>
                        </div>
                    </div>
                </div>
                <!-- ./col -->
                <div class="col-lg-3 col-xs-6">
                    <!-- small box -->
                    <div class="small-box bg-yellow-gradient">
                        <div class="icon" style="color: white !important;">
                            <i class="fa fa-group"></i>
                        </div>
                        <div class="inner">
                            <h3>{{@$getStatBuilding->data->user}}</h3>
                            <p>Cư dân</p>
                        </div>
                    </div>
                </div>
                <!-- ./col -->
                <div class="col-lg-3 col-xs-6">
                    <!-- small box -->
                    <div class="small-box bg-light-blue-active">
                        <div class="icon" style="color: white !important;">
                            <i class="fa fa-sign-in"></i>
                        </div>
                        <div class="inner">
                            <h3>{{@$getStatBuilding->data->login_app}}</h3>
                            <p>Đăng nhập App</p>
                        </div>
                    </div>
                </div>
                <!-- ./col -->
                <div class="col-lg-3 col-xs-6">
                    <!-- small box -->
                    <div class="small-box bg-red">
                        <div class="icon" style="color: white !important;">
                            <i class="fa fa-motorcycle"></i>
                        </div>
                        <div class="inner">
                            <h3>{{@$getStatBuilding->data->vehicle}}</h3>
                            <p>Phương tiện</p>
                        </div>
                    </div>
                </div>
                <!-- ./col -->
            </div>
        </div>
    </div>
    <div class="box box-primary">
        <div class="box-body font-weight-bold">
            <h3>Thống kê phương tiện</h3>
        </div>
        <div class="box-body">
            <form id="form-search-advance" action="{{route('admin.report-chart.report_total_data_building')}}" method="get">
                <div id="search-advance" class="search-advance">
                    <div class="row">
                        <div class="col-sm-12">
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
                </div>
            </form>
            <div class="row">
              <div class="col-sm-6">
                <div class="col-sm-8">
                    {!! @$chartjs_1 ? @$chartjs_1->render() : null !!}
                </div>
              </div>
              <div class="col-sm-6">
                    {!! @$chartjs ? @$chartjs->render() : null !!}
              </div>
            </div>
        </div>
    </div>
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
        $(document).ready(function () {
            if(window.barChartTest){
                window.barChartTest.options.animation.onComplete= function() {
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
            if(window.getStatVehicle){
                window.getStatVehicle.options.animation.onComplete= function() {
                ctx = this.ctx;
                ctx.font = Chart.helpers.fontString(Chart.defaults.font.size, Chart.defaults.font.style, Chart.defaults.font.family);
                ctx.textAlign = 'center';
                ctx.textBaseline = 'bottom';
                chartinst = this;
                let line = 0;         
                    this.data.datasets.forEach(function(dataset, i) {
                        ctx.fillStyle = dataset.backgroundColor[0];
                        if(chartinst.isDatasetVisible(i)){
                            var meta = chartinst.getDatasetMeta(i);
                            start_angle = 300;
                            meta.data.forEach(function(bar, index) {
                                var data = dataset.data[index];
                                    slice_angle = 2 * Math.PI * data / "{{$sum_count}}";
                                    
                                    var pieRadius = Math.min(window.getStatVehicle.width/2,window.getStatVehicle.height/2);
                                
                                    var labelX = window.getStatVehicle.width/2 + (pieRadius / 2) * Math.cos(start_angle + slice_angle/2);
                                
                                    var labelY = window.getStatVehicle.height/2 + (pieRadius / 2) * Math.sin(start_angle + slice_angle/2);
                                
                                    var labelText = Math.round(100 * data / "{{$sum_count}}");
                                
                                    this.ctx.fillStyle = "white";
                                
                                    this.ctx.font = "bold 20px Arial";

                                    if(slice_angle > 0){
                                        this.ctx.fillText(labelText+"%", labelX,labelY);
                                    }
                                    start_angle += slice_angle;
                              
                                });
                        }
                    });
               }
            }
        });
    </script>
@endsection