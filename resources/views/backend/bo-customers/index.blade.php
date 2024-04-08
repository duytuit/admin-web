@extends('backend.layouts.master')

@section('content')
<section class="content-header">
    <h1>
        Khách hàng
        <small>Danh sách</small>
    </h1>
    <ol class="breadcrumb">
        <li><a href="{{ url('/admin') }}"><i class="fa fa-dashboard"></i> Trang chủ</a></li>
        <li class="active">Khách hàng</li>
    </ol>
</section>

<section class="content" id="content-bo-customer">
    <div class="box box-primary">
        <div class="box-header with-border">
            <form action="" method="get" id="form-search">
                <div class="row">
                    <div class="col-md-8 col-sm-8 col-xs-12 ">
                        <button type="button" data-toggle="dropdown" class="btn btn-primary dropdown-toggle pull-left" style="margin-right: 10px;">Tác vụ&nbsp;<span class="caret"></span></button>
                        <ul class="dropdown-menu">
                            @can('delete', app(App\Models\BoCustomer::class))
                            <li>
                                <a href="javascript:" type="button" class="btn-action" data-target="#form-bo-customer-action" data-method="delete">
                                    <i class="fa fa-trash text-danger"></i>&nbsp; Xóa
                                </a>
                            </li>
                            @endcan

                            @can('update', app(App\Models\BoCustomer::class))
                            <li>
                                <a href="javascript:" type="button" class="btn-action" data-target="#form-bo-customer-action" data-method="active">
                                    <i class="fa fa-check text-success"></i>&nbsp;Quan tâm
                                </a>
                            </li>
                            <li>
                                <a href="javascript:" type="button" class="btn-action" data-target="#form-bo-customer-action" data-method="inactive">
                                    <i class="fa fa-close text-warning"></i>&nbsp;Không quan tâm
                                </a>
                            </li>
                            @endcan

                            @can('assign', app(App\Models\BoCustomer::class))
                            <li>
                                <a href="javascript:" type="button" class="btn-assigned" data-toggle="modal" data-target="#modal-assigned-staff">
                                    <i class="fa fa-user-plus text-info"></i>&nbsp;Phân bổ
                                </a>
                            </li>
                            @endcan
                            {{-- <li>
                                <a href="javascript:" type="button" class="btn-action" ><i class="fa fa-bullhorn text-info"></i>&nbsp;Gửi thông báo</a>
                            </li> --}}
                        </ul>
                        @can('update', app(App\Models\BoCustomer::class))
                        <a href="{{ url('admin/bo-customers/edit/0') }}" type="buttom" class="btn btn-info"><i class="fa fa-plus"></i>&nbsp;&nbsp;Thêm mới</a>
                        @endcan

                        @can('export', app(App\Models\BoCustomer::class))
                        <a href="{{ url('admin/bo-customers/export?') . http_build_query($data_search) }}" form="form-search" type="submit" class="btn btn-success"><i class="fa fa-file-excel-o"></i>&nbsp;&nbsp;Xuất excel</a>
                        @endcan
                    </div>

                    <div class="col-md-4 col-sm-4 col-xs-12">
                        <div class="input-group">
                            <input type="text" class="form-control" name="cb_name" placeholder="Nhập tên khách hàng" value="{{ !empty($data_search['cb_name']) ? $data_search['cb_name'] : '' }}">
                            <div class="input-group-btn">
                                <button type="submit" title="Tìm kiếm" class="btn btn-info"><i class="fa fa-search"></i></button>
                                <button type="button" title="Tìm kiếm nâng cao" class="btn btn-warning" data-toggle="collapse" data-target="#search-advance"><i class="fa fa-filter"></i></button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="clearfix" style="height: 15px;"></div>

                <div class="collapse row" id="search-advance">
                    <div class="col-sm-1 col-xs-12 pull-right">
                        <button type="submit" form="form-search" class="btn btn-warning"><i class="fa fa-search"></i>&nbsp;&nbsp;Tìm kiếm</button>
                    </div>

                    <div class="col-sm-3 col-xs-12 pull-right no-pd-rt">
                        <div class="input-group">
                            <span class="input-group-addon" style="padding: 0px; border: none;">
                                <div class="form-group">
                                    <select class="form-control input-group-select select2" name="field" style="width: 150px;">
                                        <option value="">Tìm kiếm theo</option>
                                        @foreach( $searches['field'] as $key => $search)
                                        <option value="{{$key}}" @if( $data_search && ($data_search["field"]==$key) ) selected @endif>{{$search}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </span>
                            <input type="text" name="partner_search" value="{{ !empty($data_search['partner_search'])? $data_search['partner_search'] : ''}}" placeholder="Nhập từ khóa" class="form-control" />
                        </div>
                    </div>

                    <div class="col-sm-2 col-xs-12 form-group pull-right no-pd-rt">
                        <select class="form-control" name="cb_staff_id" id="select-bo-user" style="width: 100%;">
                            <option value="">Chọn nhân viên</option>
                            @if(!empty($data_search['cb_staff_id']))
                            <option value="{{ $data_search['cb_staff']['id'] }}" selected>{{ $data_search['cb_staff']['name']}}</option>
                            @endif
                        </select>
                    </div>

                    <div class="col-sm-2 col-xs-12 form-group pull-right no-pd-rt">
                        <select class="form-control" name="project_id" id="select-bo-project" style="width: 100%;">
                            <option value="">Chọn dự án</option>
                            @if(!empty($data_search['project_id']))
                            <option value="{{ $data_search['project']['id'] }}" selected>{{ $data_search['project']['name']}}</option>
                            @endif
                        </select>
                    </div>

                    <div class="col-sm-2 col-xs-12 form-group pull-right no-pd-rt">
                        <select class="form-control select2" name="cb_source" id="select-bo-project" style="width: 100%;">
                            <option value="">Chọn nguồn</option>
                            @foreach($searches['customer-source']->config_value as $key => $customer_source)
                            <option value="{{ $key }}" @if($data_search['cb_source']==$key) selected @endif>{{ $customer_source }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group col-sm-2 col-xs-12 pull-right no-pd-rt">
                        <select class="form-control select2" name="status" style="width: 100%;">
                            <option value=""> Chọn loại phản hồi </option>
                            <option value="1" @if($data_search && $data_search['status'] == 1) selected @endif>
                                Quan tâm
                            </option>
                            <option value="0" @if($data_search && $data_search['status'] === '0' ) selected @endif>
                                Không quan tâm
                            </option>
                        </select>
                    </div>

                </div>
                <div class="clearfix"></div>
            </form>
        </div>
        <!-- /.box-header -->

        <div class="box-body">
            <div class="table-responsive">
                <form action="{{ url("/admin/bo-customers/action") }}" method="post" id="form-bo-customer-action">
                    {{ csrf_field() }}
                    @method('post')
                    <input type="hidden" name="method" value="" />
                    <input type="hidden" name="status" value="" />
                    <input type="hidden" name="cb_staff_ids[]" value="" />

                    <table class="table table-striped table-bordered table-hover">
                        <thead>
                            <tr class="bg-primary">
                                <th width='20px'>
                                    <input class="iCheck checkAll" type="checkbox" data-target=".checkSingle" />
                                </th>
                                <th width='20px'>#</th>
                                <th>Khách hàng</th>
{{--                                <th width='14%'>Dự án</th>--}}
                                <th width='14%'>Nhân viên</th>
                                <th width='4%'>Nguồn KH</th>
                                <th width='6%'>Phản hồi</th>
                                <th width='16%'>Ghi chú</th>
                                <th width='20%'>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($bo_customers as $bo_customer)
                            <tr>
                                <td><input type="checkbox" class="iCheck checkSingle" value="{{$bo_customer->id}}" name="ids[]" /></td>
                                <td>{{ $bo_customer->id }}</td>
                                <td>
                                    <a href="{{ url("/admin/bo-customers/edit/{$bo_customer->cb_id}") }}"> {{ $bo_customer->name }} </a>
                                </td>
{{--                                <td>{{ $bo_customer->getProject() ? $bo_customer->getProject()['cb_title'] : '' }}</td>--}}
                                <td>{{ collect($bo_customer->cb_staff)->implode(', ') }}</td>
                                <td>{{ $bo_customer->cb_source }}</td>
                                <td>
                                    @can('update', app(App\Models\BoCustomer::class))
                                    @if ($bo_customer->status !== null)
                                    <a title="Thay đổi trạng thái" href="javascript:;" data-url="{{ url('admin/bo-customers/action') }}" data-id="{{ $bo_customer->id }}" data-status="{{ $bo_customer->status }}" class="btn-status label label-sm label-{{ $bo_customer->status == 1 ? 'success' : 'danger' }}">
                                        {{ $bo_customer->status == 1 ? 'Quan tâm' : 'Không quan tâm' }}
                                    </a>
                                    @endif
                                    @else
                                    <span class="btn-status label label-sm label-{{ $bo_customer->status == 1 ? 'success' : 'danger' }}">{{ $bo_customer->status == 1 ? 'Quan tâm' : 'Không quan tâm' }}</span>
                                    @endcan
                                </td>
                                <td >{{$bo_customer->getDescription($bo_customer->cb_id)}}</td>
                                <td>
                                    @can('update', app(App\Models\BoCustomer::class))
                                    <a href=" {{ url("/admin/bo-customers/edit/{$bo_customer->cb_id}") }}" type="button" class="btn btn-sm btn-info" title="Sửa"><i class="fa fa-edit"></i></a>
                                    @endcan
                                    <a data-cus_id="{{$bo_customer->id}}"  class="btn btn-sm btn-info js-btn-call" data-toggle="modal" data-target="#box_call"><i class="fa fa-phone"></i></a>
                                    <a href="mailto:{{ $bo_customer->cb_email}}" class="btn btn-sm btn-warning"><i class="fa fa-envelope"></i></a>
                                    @can('assign', app(App\Models\BoCustomer::class))
                                    <a href="javascript:;" type="button" class="btn btn-sm btn-warning btn-phan-bo" title="Phân bổ" data-customer='{{ $bo_customer->cb_id }}' data-toggle="modal" data-target="#assigned-staff">
                                        <i class="fa fa-user-plus"></i>
                                    </a>
                                    @endcan

                                    @can('delete', app(App\Models\BoCustomer::class))
                                    <a title="Xóa" href="javascript:;" data-url="{{ url('admin/bo-customers/action') }}" data-id="{{ $bo_customer->id }}" class="btn btn-sm btn-delete btn-danger">
                                        <i class="fa fa-trash"></i>
                                    </a>
                                    @endcan
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <input type="submit" class="js-submit-form-index hidden" value="" />
                </form>
            </div>
        </div>
        <!-- /.box-body -->

        <div class="box-footer clearfix">
            <div class="row">
                <div class="col-sm-3">
                    <span class="record-total">Tổng: {{ $bo_customers->total() }} bản ghi</span>
                </div>
                <div class="col-sm-6 text-center">
                    <div class="pagination-panel">
                        {{ $bo_customers->appends(Request::all())->onEachSide(1)->links() }}
                    </div>
                </div>
                <div class="col-sm-3 text-right">
                    <span class="form-inline">
                        Hiển thị
                        <select name="per_page" class="form-control" data-target="#form-bo-customer-action">
                            @php $list = [10, 20, 50, 100, 200]; @endphp
                            @foreach ($list as $num)
                            <option value="{{ $num }}" {{ $num == $per_page ? 'selected' : '' }}>{{ $num }}</option>
                            @endforeach
                        </select>
                    </span>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Phân bổ 1 khách hàng --}}
<div id="assigned-staff" class="modal fade" role="dialog">
    <div class="modal-dialog">

        <!-- Modal content-->
        <div class="modal-content">
            <form action="{{ url('/admin/bo-customers/assigned') }}" method="post" id="form-assigned-staff" class="form-validate form-horizontal">
                @csrf
                <div class="modal-header bg-primary">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Call khách hàng</h4>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="cus_cb_id" value="" />
                    <input type="hidden" name="method" value="assigned_staff" />
                    <input type="hidden" name="target" value="#form-bo-customer-action" />

                    <label class="control-label">Nhân viên</label>
                    <select class="form-control" name="cb_staff_id[]" id="select-assigned-staff" style="width: 100%;" multiple>
                        <option value="">Chọn nhân viên</option>
                        @if(old('cb_staff_id') )
                        @foreach(old('cb_staff_id') as $staff)
                        @php
                        $staff = \App\Models\BoUser::findBY([['ub_id', $staff]])->first();
                        @endphp
                        <option value="{{ $staff->ub_id }}" selected="">{{ $staff->ub_title }} - {{ $staff->group->gb_title }}</option>
                        @endforeach
                        @elseif(!empty($bo_customer->cb_staff_id))
                        @php
                        $staff_ids = explode(',', $bo_customer->cb_staff_id);
                        $staffs = \App\Models\BoUser::whereIn('ub_id', $staff_ids )->get();
                        @endphp
                        @foreach($staffs as $staff)
                        <option value="{{ $staff->ub_id }}" selected="">{{ $staff->ub_title }} - {{ $staff->group->gb_title }}</option>
                        @endforeach
                        @endif
                    </select>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger pull-right" data-dismiss="modal"><i class="fa fa-close"></i>&nbsp;&nbsp;Hủy</button>
                    <button type="submit" class="btn btn-primary btn-js-action" style="margin-right: 5px;"><i class="fa fa-save"></i>&nbsp;&nbsp;Xác nhận</a>
                </div>
            </form>
        </div>

    </div>
</div>

{{-- Phân bổ nhiều khách hàng --}}
<div id="modal-assigned-staff" class="modal fade" role="dialog">
    <div class="modal-dialog">

        <!-- Modal content-->
        <div class="modal-content">
            <form action="" method="post" id="form-assigned-staff" class="form-validate form-horizontal">
                @csrf
                <div class="modal-header bg-primary">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Phân bổ cho nhân viên</h4>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="method_assigned" value="assigned_staff" />

                    <label class="control-label">Nhân viên</label>
                    <select class="form-control" name="cb_staff_ids[]" id="select-assigned" style="width: 100%;" multiple>
                        <option value="">Chọn nhân viên</option>
                        @if(old('cb_staff_id') )
                        @foreach(old('cb_staff_id') as $staff)
                        @php
                        $staff = \App\Models\BoUser::findBY([['ub_id', $staff]])->first();
                        @endphp
                        <option value="{{ $staff->ub_id }}" selected="">{{ $staff->ub_title }} - {{ $staff->group->gb_title }}</option>
                        @endforeach
                        @elseif(!empty($bo_customer->cb_staff_id))
                        @php
                        $staff_ids = explode(',', $bo_customer->cb_staff_id);
                        $staffs = \App\Models\BoUser::whereIn('ub_id', $staff_ids )->get();
                        @endphp
                        @foreach($staffs as $staff)
                        <option value="{{ $staff->ub_id }}" selected="">{{ $staff->ub_title }} - {{ $staff->group->gb_title }}</option>
                        @endforeach
                        @endif
                    </select>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger pull-right" data-dismiss="modal"><i class="fa fa-close"></i>&nbsp;&nbsp;Hủy</button>
                    <a href="javascript:;" class="btn btn-primary btn-js-assigned" style="margin-right: 5px;"><i class="fa fa-save"></i>&nbsp;&nbsp;Xác nhận</a>
                </div>
            </form>
        </div>

    </div>
</div>

@can('index', app(App\Models\CustomerDiary::class))
    <div id="box_call" class="modal fade" role="dialog">
        <div class="modal-dialog  modal-lg">
            <!-- Modal content-->
            <div class="modal-content">
                <form action="{{ route('admin.bo-customers.confirm') }}" method="POST" class="form-validate form-horizontal" id="confirm_assing-diary" autocomplete="off">
                    @csrf
                    <div class="box_pop_cus">

                    </div>
                </form>
            </div>
        </div>
    </div>
@endcan


@endsection


@section('javascript')
    <script type="text/javascript">

        function settingClientEvents(client) {
            client.on('connect', function () {
                console.log('connected to StringeeServer');
            });
            client.on('authen', function (res) {
                console.log('authen: ', res);
                $('#loggedUserId').html(res.userId);
                // if(res.message == 'SUCCESS'){
                //     $('#callBtn').click();
                // }
            });
            client.on('disconnect', function () {
                console.log('disconnected');
            });
            client.on('incomingcall', function (incomingcall) {
                call = incomingcall;
                settingCallEvents(incomingcall);
                $('#incoming-call-div').show();
                $('#incoming_call_from').html(call.fromNumber);
                console.log('incomingcall: ', incomingcall);
            });
            client.on('requestnewtoken', function () {
                console.log('request new token; please get new access_token from YourServer and call client.connect(new_access_token)');
            });
            client.on('otherdeviceauthen', function (data) {
                console.log('otherdeviceauthen: ', data);
            });
        }
        function settingCallEvents(call1) {
            call1.on('addlocalstream', function (stream) {
            });
            call1.on('addremotestream', function (stream) {
                remoteVideo.srcObject = null;
                remoteVideo.srcObject = stream;
            });
            call1.on('signalingstate', function (state) {
                console.log('signalingstate ', state);
                if (state.code == 6) {
                    $('#incoming-call-div').hide();
                }
                var reason = state.reason;
                $('#callStatus').html(reason);
            });
            call1.on('mediastate', function (state) {
                console.log('mediastate ', state);
            });
            call1.on('info', function (info) {
                console.log('on info', info);
            });
            call1.on('otherdevice', function (data) {
                console.log('on otherdevice:' + JSON.stringify(data));
                if ((data.type === 'CALL_STATE' && data.code >= 200) || data.type === 'CALL_END') {
                    $('#incoming-call-div').hide();
                }
            });
        }
        function testAnswerCall() {
            call.answer(function (res) {
                console.log('answer res', res);
                $('#incoming-call-div').hide();
            });
        }
        function testRejectCall() {
            call.reject(function (res) {
                console.log('reject res', res);
                $('#incoming-call-div').hide();
            });
        }
        function testHangupCall() {
            remoteVideo.srcObject = null;
            call.hangup(function (res) {
                console.log('hangup res', res);
            });
            $('#callBtn').show();
            $('#hangupBtn').hide();
        }
    </script>
<script>
    $(function() {

    get_data_select2({
        object: '#select-bo-project',
        url: '{{ url("/admin/bo-customers/ajax/get-all-project") }}',
        data_id: 'cb_id',
        data_text: 'cb_title',
        title_default: 'Chọn dự án'
    });

    get_data_select_user({
        object: '#select-bo-user',
        data_id: 'ub_id',
        data_text1: 'ub_title',
        data_text2: 'gb_title',
        title_default: 'Chọn nhân viên'
    });

    get_data_select_user({
        object: '#select-assigned-staff',
        data_id: 'ub_id',
        data_text1: 'ub_title',
        data_text2: 'gb_title',
        title_default: 'Chọn nhân viên'
    });

    get_data_select_user({
        object: '#select-assigned',
        data_id: 'ub_id',
        data_text1: 'ub_title',
        data_text2: 'gb_title',
        title_default: 'Chọn nhân viên'
    });

    function get_data_select_user(options) {
        $(options.object).select2({
            ajax: {
                url: '{{ url("/admin/bo-customers/get-user-group") }}',
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
                            text: item[options.data_text1] + ' - ' + item[options.data_text2]
                        });
                    }
                    return {
                        results: results,
                    };
                },
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
            }
        });
    }
    
    $('.btn-phan-bo').click(function() {
        var cb_id = $(this).data('customer');
        $('input[name="cus_cb_id"]').val(cb_id);
    });

    $('.btn-js-assigned').click(function(e) {
        e.preventDefault();

        var method = $('input[name="method_assigned"]').val();
        var cb_staff_id = $('select[name="cb_staff_ids[]"]').val();

        $('input[name="method"]').val(method);
        $('input[name="cb_staff_ids[]"]').val(cb_staff_id);

        $('#form-bo-customer-action').submit();
    });
        $('.js-btn-call').click(function() {
            var cus_id = $(this).data('cus_id');
            $.get('{{ url("/admin/bo-customers/ajax-edit-diary-call") }}', {
                cus_id: cus_id,
            }, function(data) {
                $('#box_call .box_pop_cus').html(data);
            });
        });
        // $('#box_call').on('hidden.bs.modal', function (e) {
        //     testHangupCall();
        // })
});



sidebar('bo-customers', 'bo-customer');
</script>
@endsection