@extends('backend.layouts.master')

@section('content')

    <section class="content-header">
        <h1>
            Tra cứu lịch sử tool 
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('admin.home') }}"><i class="fa fa-home"></i> Trang chủ</a></li>
            <li class="active">Tra cứu lịch sử tool</li>
        </ol>
    </section>
    <section class="content">
        <div class="box box-primary">
            <div class="box-body ">
                <form id="form-search-activity" action="{{route('admin.activitylog.LogActiveTool')}}" method="get">
                    <div class="row form-group">
                        <div id="search-advance" class="search-advance">
                                <div class="col-sm-3" style="margin-top: 25px;">
                                    <select class="form-control select2" name="tool_id" id="tool_id" >
                                        <option value="">Lựa chọn tool</option>
                                            @foreach ($permissions as $value)
                                                <option value="{{ $value->id }}" @if(@$filter['tool_id'] == $value->id) selected @endif>{{ $value->title }}</option>
                                            @endforeach
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
                                    <button type="submit" title="Tìm kiếm" class="btn btn-info" style="margin-top: 25px;" form="form-search-activity"><i class="fa fa-search"></i></button>
                                </div>
                        </div>
                    </div>
                </form><!-- END #form-search-advance -->

                <div class="table-responsive">
                    <form action='{{ route('admin.activitylog.LogActiveTool') }}' method="post" id="form-activity-action">
                            {{ csrf_field() }}
                            <input type="hidden" name="method" value="" />
                            <table class="table table-hover table-striped table-bordered">
                                <thead class="bg-primary">
                                <tr>
                                    <th width="30">STT</th>
                                    <th>Tên tool</th>
                                    <th>Hành động</th>
                                    <th>Cập nhật</th>
                                    <th>Kết quả</th>
                                    <th>Thao tác</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($log_active_tool as $key => $value)
                                    <?php
                                        $permission = App\Models\Permissions\Permission::getDetailPermissionById($value->tool_id);
                                        $user = App\Models\PublicUser\Users::get_detail_user_by_user_id($value->by);
                                        $get_action = App\Commons\Helper::action;
                                        $_action = null;
                                        foreach ($get_action as $key_1 => $value_1) {
                                            if($key_1 == $value->action){
                                                $_action = $value_1;
                                            }
                                        }
                                    ?>
                                    <tr>
                                        <td>{{($key + 1) + ($log_active_tool->currentPage() - 1) * $log_active_tool->perPage()}}</td>
                                        <td >
                                            <a href="javascript:;" onclick="toastr.success('{{$value->url.json_encode($value->param)}}')"> {{@$permission->title}}</a>
                                        </td>
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
                                            @if ($value->status === 1)
                                                    <label for="" class="label label-sm label-success">Thành công</label>
                                            @elseif($value->status === 0)
                                                    <label for="" class="label label-sm label-danger">Thất bại</label>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.activitylog.LogActionDB', ['request_id' => $value->request_id]) }}"
                                                target="_blank">{{$value->request_id}}</a>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="row mbm">
                            <div class="col-sm-3">
                                <span class="record-total">Hiển thị: {{$log_active_tool->count()}} kết quả</span>
                            </div>
                            <div class="col-sm-6 text-center">
                                <div class="pagination-panel">
                                    {{ $log_active_tool->appends(Request::all())->onEachSide(1)->links() }}
                                </div>
                            </div>
                            <div class="col-sm-3 text-right">
                                <span class="form-inline">
                                    Hiển thị
                                    <select name="per_page" class="form-control" data-target="#form-asset">
                                        @php $list = [10, 20, 50, 100, 200]; @endphp
                                        @foreach ($list as $num)
                                            <option value="{{ $num }}" {{ $num == $per_page ? 'selected' : '' }}>{{ $num }}</option>
                                        @endforeach
                                    </select>
                                </span>
                            </div>
                        </div>
                </form>
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
            if($('#user_info_id_search').val())
            {
                var user_info_id_search_all = $('#user_info_id_search').val();
                var obj_user_info_id_search_all_all = JSON.parse(user_info_id_search_all);
                var new_user_info_all = [];
                            new_user_info_all.push({
                                id:obj_user_info_id_search_all_all["id"],
                                text:obj_user_info_id_search_all_all["display_name"]
                            });
                
                $('#subject_id').select2({data:new_user_info_all});
                $('#subject_id').find('option').attr('selected', true);
                $('#subject_id').select2();
            }
            get_data_select_customer({
            object: '#subject_id',
            url: '{{url('admin/activity-log/ajax_get_customer')}}',
            data_id: 'id',
            data_text: 'display_name',
            title_default: 'Chọn cư dân'
            });

            function get_data_select_customer(options) {
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
