@extends('backend.layouts.master')

@section('content')

    <section class="content-header">
        <h1>
            Tra cứu lịch sử thay đổi dữ liệu
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('admin.home') }}"><i class="fa fa-home"></i> Trang chủ</a></li>
            <li class="active">Tra cứu lịch sử thay đổi dữ liệu</li>
        </ol>
    </section>
    <section class="content">
        <div class="box box-primary">
            <div class="box-body ">
                <form id="form-search-activity" action="{{route('admin.activitylog.LogActionDB')}}" method="get">
                    <div class="row form-group">
                        <div id="search-advance" class="search-advance">
                            <div class="col-sm-1">
                                <input type="text" style="margin-top: 25px;" name="row_id" value="{{isset($filter['row_id']) ? $filter['row_id'] : '' }}" placeholder="Id bản ghi" class="form-control"/>
                            </div>
                            <div class="col-sm-2" style="margin-top: 25px;">
                                <select name="table" id="table_name" class="form-control" style="width: 100%;">
                                    <option value="">Chọn bảng</option>
                                </select>
                            </div>
                            <div class="col-sm-2" style="margin-top: 25px;">
                                <select name="bdc_apartment_id" id="ip-apartment" class="form-control">
                                    <option value="">Căn hộ</option>
                                    <?php $apartment = isset($get_apartment) ? $get_apartment: '' ?>
                                    @if($apartment)
                                        <option value="{{$apartment->id}}" selected>{{$apartment->name}}</option>
                                    @endif
                                </select>
                            </div>
                            <div class="col-sm-2">
                                <select class="form-control" name="action" id="action" style="margin-top: 25px;">
                                    <option value="">Hành động</option>
                                    <option value="insert" @if(@$filter['action'] == 'insert') selected @endif>Thêm mới</option>
                                    <option value="view" @if(@$filter['action'] == 'view') selected @endif>Xem</option>
                                    <option value="update" @if(@$filter['action'] == 'update') selected @endif>Cập nhật</option>
                                    <option value="delete" @if(@$filter['action'] == 'delete') selected @endif>Xóa</option>
                                </select>
                            </div>
                            <div class="col-sm-2">
                                <label for="">Từ ngày</label>
                                <div class="input-group date">
                                    <div class="input-group-addon">
                                        <i class="fa fa-calendar"></i>
                                    </div>
                                    <input type="text" class="form-control pull-right date_picker" name="from_date"
                                           value="{{ @$filter['from_date'] }}" placeholder="Từ..." autocomplete="off">
                                </div>
                            </div>
                            <div class="col-sm-2">
                                <label for="">Đến ngày</label>
                                <div class="input-group date">
                                    <div class="input-group-addon">
                                        <i class="fa fa-calendar"></i>
                                    </div>
                                    <input type="text" class="form-control pull-right date_picker" name="to_date"
                                           value="{{ @$filter['to_date'] }}" placeholder="Đến..." autocomplete="off">
                                </div>
                            </div>
                            <div class="input-group-btn" style="display: block;">
                                <button type="submit" title="Tìm kiếm" style="margin-top: 25px;" class="btn btn-info" form="form-search-activity"><i class="fa fa-search"></i></button>
                            </div>
                        </div>
                    </div>
                </form><!-- END #form-search-advance -->

                <div class="table-responsive">
                    <form action='{{ route('admin.activitylog.LogActionDB') }}' method="post" id="form-activity-action">
                        {{ csrf_field() }}
                        <input type="hidden" name="method" value="" />
                        <table class="table table-hover table-striped table-bordered">
                            <thead class="bg-primary">
                            <tr>
                                <th>STT</th>
                                <th>Tên bảng</th>
                                <th>Hành động</th>
                                <th>Cập nhật</th>
                                <th >Dữ liệu trước</th>
                                <th >Dữ liệu sau thay đổi</th>
                                <th>Sql</th>
                                <th>RequestID</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($log_action_db as $key => $value)
                                    <?php
                                    $user = App\Models\PublicUser\Users::get_detail_user_by_user_id($value->by);
                                    $data_old = json_decode(@$value->data_old);
                                    $data_new = json_decode(@$value->data_new);
                                    $diffident=null;

                                    ?>
                                <tr>
                                    <td>{{($key + 1) + ($log_action_db->currentPage() - 1) * $log_action_db->perPage()}}</td>
                                    <td>{{$value->table}}</td>
                                    <td>
                                        @if(@$value->action == 'view')
                                            <span class="text-light-blue">{{$value->action}}</span>
                                        @elseif(@$value->action == 'insert')
                                            <span class="text-green">{{$value->action}}</span>
                                        @elseif(@$value->action == 'delete')
                                            <span class="text-red">{{$value->action}}</span>
                                        @elseif(@$value->action == 'update')
                                            <span class="text-orange">{{$value->action}}</span>
                                        @elseif(@$value->action == 'import')
                                            <span class="text-gray">{{$value->action}}</span>
                                        @elseif(@$value->action == 'export')
                                            <span class="text-purple">{{$value->action}}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <small>
                                            {{ @$user->email }}<br />
                                            {{ $value->updated_at->format('Y-m-d H:i') }}
                                        </small>
                                    </td>
                                    <td>
                                        @php
                                            if (@$data_old){
                                                 foreach ($data_old as $index => $item) {
                                                   if(@$index == 'remember_token'){
                                                       continue;
                                                   }
                                                    @$_index = isset(trans('model')[@$index]) ? trans('model')[@$index] : @$index;
                                                    echo '<p  style="width:300px;word-wrap: break-word;">'. @$_index .' : '. App\Commons\Helper::decode_string(json_encode(@$data_old->$index)).'</p>';
                                               }
                                            }
                                        @endphp
                                    </td>
                                    <td >
                                        @php
                                            foreach ($data_new as $index => $item) {
                                               if(@$index == 'remember_token'){
                                                   continue;
                                               }
                                               if(@$data_old->$index != @$data_new->$index){

                                                   if(@$data_old->$index){
                                                        @$_index = isset(trans('model')[@$index]) ? trans('model')[@$index] : @$index;
                                                        echo '<p  style="width:300px;word-wrap: break-word;">'. @$_index .' : '. App\Commons\Helper::decode_string(json_encode(@$data_old->$index)).' => '. App\Commons\Helper::decode_string(json_encode(@$data_new->$index)) .'</p>';
                                                   }else{
                                                         @$_index = isset(trans('model')[@$index]) ? trans('model')[@$index] : @$index;
                                                        echo '<p  style="width:300px;word-wrap: break-word;">'. @$_index .' : '.App\Commons\Helper::decode_string(json_encode(@$data_new->$index)).'</p>';
                                                   }
                                               }

                                           }
                                        @endphp
                                    </td>
                                    <td >
                                        <p  style="width:300px;word-wrap: break-word;">{{print_r(json_decode(@$value->sql))}}</p>
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.activitylog.LogActiveTool', ['request_id' => $value->request_id]) }}"
                                           target="_blank">{{$value->request_id}}</a>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </form>

                </div>
                <div class="row mbm">
                    <div class="col-sm-3">
                        <span class="record-total">Hiển thị: {{$log_action_db->count()}} kết quả</span>
                    </div>
                    <div class="col-sm-6 text-center">
                        <div class="pagination-panel">
                            {{ $log_action_db->appends(Request::all())->onEachSide(1)->links() }}
                        </div>
                    </div>
                    <div class="col-sm-3 text-right">
                        <span class="form-inline">
                            Hiển thị
                            <select name="per_page" class="form-control" data-target="#form-activity-action">
                                @php $list = [10, 20, 50, 100, 200]; @endphp
                                @foreach ($list as $num)
                                    <option value="{{ $num }}" {{ $num == $per_page ? 'selected' : '' }}>{{ $num }}</option>
                                @endforeach
                            </select>
                        </span>
                    </div>
                </div>
            </div>
            <input type="hidden" value="{{isset($user_info) ? $user_info : ''}}" id="user_info_id_search">
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
        $(document).ready(() => {
            get_data_select_table({
                object: '#table_name',
                url: '{{url('admin/activity-log/ajaxGetSelectTable')}}',
                data_id: 'table',
                data_text: 'table',
                title_default: 'Chọn bảng'
            });
            function get_data_select_table(options) {
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
                        object: '#ip-apartment',
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
        });

    </script>


@endsection
