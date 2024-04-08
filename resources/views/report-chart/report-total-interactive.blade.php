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
            <h3>Tổng hợp thông tin - sự kiện</h3>
        </div>
        <div class="box-body">
            <div class="row">
                <div class="col-lg-4 col-xs-6">
                    <!-- small box -->
                    <div class="small-box bg-green">
                        <div class="icon" style="color: white !important;">
                            <i class="fa fa-bullhorn"></i>
                        </div>
                        <div class="inner">
                            <h3>{{@$getNotifyEvent->data->count}}</h3>
            
                            <p>Thông báo</p>
                        </div>
                    </div>
                </div>
                <!-- ./col -->
                <div class="col-lg-4 col-xs-6">
                    <!-- small box -->
                    <div class="small-box bg-yellow-gradient">
                        <div class="icon" style="color: white !important;">
                            <i class="fa fa-bell-o"></i>
                        </div>
                        <div class="inner">
                            <h3>{{@$getNotifyEvent->data->category_id}}</h3>
            
                            <p>Sự kiện</p>
                        </div>
                    </div>
                </div>
                <!-- ./col -->
                <div class="col-lg-4 col-xs-6">
                    <!-- small box -->
                    <div class="small-box bg-light-blue-active">
                        <div class="icon" style="color: white !important;">
                            <i class="fa fa-comments"></i>
                        </div>
                        <div class="inner">
                            <h3>{{@$getNotifyEvent->data->interaction_user .' / '.@$getNotifyEvent->data->interaction_comment}}</h3>
                            <p>Người / Bình luận</p>
                        </div>
                    </div>
                </div>
                <!-- ./col -->
            </div>
  
        </div>
    </div>
    <div class="box box-primary">
        <div class="box-body font-weight-bold">
            <h3>Báo cáo thông kê</h3>
        </div>
        <div class="box-body">
            <form id="form-search-advance" action="{{route('admin.report-chart.report_total_interactive')}}" method="get">
                <div id="search-advance" class="search-advance">
                    <div class="row space-5">
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
                 <div class="col-sm-6">
                     <div class="col-sm-8">
                        {!!@$chartjs ? @$chartjs->render() : null !!}
                     </div>
                 </div>
                 <div class="col-sm-6">
                    <div class="col-sm-8">
                        {!!@$chartjs_1 ? @$chartjs_1->render() : null !!}
                    </div>
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
            if(window.getStatFeedback){
                window.getStatFeedback.options.animation.onComplete= function() {
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
                                    slice_angle = 2 * Math.PI * data / "{{$sum_count_feedback}}";
                                    
                                    var pieRadius = Math.min(window.getStatFeedback.width/2,window.getStatFeedback.height/2);
                                
                                    var labelX = window.getStatFeedback.width/2 + (pieRadius / 2) * Math.cos(start_angle + slice_angle/2);
                                
                                    var labelY = window.getStatFeedback.height/2 + (pieRadius / 2) * Math.sin(start_angle + slice_angle/2);
                                
                                    var labelText = Math.round(100 * data / "{{$sum_count_feedback}}");
                                
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
            if(window.getStatVote){
                window.getStatVote.options.animation.onComplete= function() {
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
                                    slice_angle = 2 * Math.PI * data / "{{$sum_count_vote}}";
                                    
                                    var pieRadius = Math.min(window.getStatVote.width/2,window.getStatVote.height/2);
                                
                                    var labelX = window.getStatVote.width/2 + (pieRadius / 2) * Math.cos(start_angle + slice_angle/2);
                                
                                    var labelY = window.getStatVote.height/2 + (pieRadius / 2) * Math.sin(start_angle + slice_angle/2);
                                
                                    var labelText = Math.round(100 * data / "{{$sum_count_vote}}");
                                
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