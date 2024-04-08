@extends('backend.layouts.master')

@section('stylesheet')
<link rel="stylesheet" href="/adminLTE/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-star-rating/4.0.2/css/star-rating.min.css" />
@endsection

@section('content')
<section class="content-header">
    <h1>
        Khách hàng phân bổ
        <small>Danh sách gọi điện CTV</small>
    </h1>
    <ol class="breadcrumb">
        <li><a href="{{ url('/admin') }}"><i class="fa fa-dashboard"></i> Trang chủ</a></li>
        <li class="active">Khách hàng phân bổ</li>
    </ol>
</section>

<section class="content" id="content-assigned-customer">
    <div class="box box-primary">
        <div class="box-body">
            <form action="" method="get" id="form-search">
                <div class="row">
                    <div class="col-sm-2 col-xs-12" style="padding-right: 0;">
                        <select class="form-control" name="feedback" id="select-feedback" style="width: 100%;">
                            <option value="">Chọn trạng thái</option>
{{--                            <option value="0" @if(isset($data_search['feedback']) && $data_search['feedback'] == 0) selected @endif>Không quan tâm</option>--}}
{{--                            <option value="1" @if(isset($data_search['feedback']) && $data_search['feedback'] == 1) selected @endif>Quan tâm</option>--}}
                            <option value="2" @if(isset($data_search['feedback']) && $data_search['feedback'] == 2) selected @endif>Không bắt máy</option>
                            <option value="3" @if(isset($data_search['feedback']) && $data_search['feedback'] == 3) selected @endif>Sai số điện thoại</option>
                            <option value="4" @if(isset($data_search['feedback']) && $data_search['feedback'] == 4) selected @endif>Gọi lại sau</option>
                        </select>
                    </div>
                    <div class="col-sm-10 col-xs-12" style="padding-left: 0;">
                        @can('view_ctv', app(App\Models\CampaignAssign::class))
                        <div class="row">
                            <div class="col-sm-11" style="padding-right: 0;">
                        @endcan
                            <div class="col-sm-2 col-xs-12 pull-right">
                                <button type="submit" form="form-search" class="btn btn-warning" style="width: 100%;"><i class="fa fa-search"></i>&nbsp;&nbsp;Tìm kiếm</button>
                            </div>

                            <div class="col-sm-2 col-xs-12 form-group pull-right no-pd-rt">
                                <select class="form-control" name="campaign_id" id="select-campaigns" style="width: 100%;">
                                    <option value="">Chọn chiến dịch</option>
                                    @if(!empty($data_search['campaign']))
                                        <option value="{{ $data_search['campaign']['id'] }}" selected>{{ $data_search['campaign']['name']}}</option>
                                    @endif
                                </select>
                            </div>

                            <div class="col-sm-2 col-xs-12 form-group pull-right no-pd-rt">
                                <input type="text" class="form-control" name="customer_email" placeholder="Nhập email" value="{{ !empty($data_search['customer_email']) ? $data_search['customer_email'] : '' }}">
                            </div>

                            <div class="col-sm-2 col-xs-12 form-group pull-right no-pd-rt">
                                <input type="text" class="form-control" name="customer_phone" placeholder="Nhập số điện thoại" value="{{ $data_search['customer_phone'] !== null ? $data_search['customer_phone'] : '' }}">
                            </div>

                            <div class="col-sm-2 col-xs-12 form-group pull-right no-pd-rt">
                                <input type="text" class="form-control" name="customer_name" placeholder="Nhập tên khách hàng" value="{{ !empty($data_search['customer_name']) ? $data_search['customer_name'] : '' }}">
                            </div>

                            <div class="col-sm-2 col-xs-12 form-group pull-right no-pd-rt">
                                <input type="text" class="form-control" name="staff_name" placeholder="Nhập tên Sale" value="{{ !empty($data_search['staff_name']) ? $data_search['staff_name'] : '' }}">
                            </div>

                        @can('view_ctv', app(App\Models\CampaignAssign::class))
                            </div>
                            <div class="col-sm-1"  style="padding-left: 0;"><a href="javascript:void(0);" class="btn btn-primary js-show-insert_camp" title="Thêm chiến dịch" data-toggle="modal" data-target="#campaign-assign-import-ctv"><i class="fa fa-file-excel-o"></i></a></div>
                        </div>
                        @endcan
                    </div>
                </div>
                <div class="clearfix"></div>
            </form>

            <form action='{{ url("/admin/campaign-assign/action") }}' method="post" id="form-assigned-customer-action">
                {{ csrf_field() }}
                <input type="hidden" name="method" value="" />
                <input type="hidden" name="status" value="" />
                <input type="hidden" name="cb_staff_ids[]" value="" />

                <div class="table-responsive" style="font-size: 13px;">
                    <table class="table table-striped table-bordered table-hover">
                        <thead>
                            <tr class="bg-primary">
                                <th width="1%">
                                    <input class="iCheck checkAll" type="checkbox" data-target=".checkSingle" />
                                </th>
                                <th width="1%">#</th>

                                <th width="15%">Khách hàng</th>
                                <th width="10%">SĐT</th>
                                <th width="15%">Email</th>
                                <th width="10%">Chiến dịch</th>
                                <th width="8%">Trạng thái</th>
                                <th width="14%">Ghi chú</th>
                                @can('view_ctv', app(App\Models\CampaignAssign::class))
                                    <th width="13%">CTV/Sale Phân bổ</th>
                                @endcan
                                <th width="7%">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($assigns as $assign)
                                <?php
                                    $logs = $assign->logs;

                                    if($logs){
                                        $diary = array_pop($logs);
                                        $content = $diary['content'];
                                    }else{
                                        $content = '';
                                    }
                                    $phone_num = substr($assign->customer_phone, 0, 4) . '****' . substr($assign->customer_phone, 8, 4);


                                    $phone_num_new = !empty($content['customer_phone'])?$content['customer_phone']:'';
                                    if(!empty($content['customer_phone'])){
                                        $phone_num_new = substr($phone_num_new, 0, 4) . '****' . substr($phone_num_new, 8, 4);
                                    }
                                    $rex = '/^(.+)@[^@]+$/';
                                    preg_match_all($rex,$assign->customer_email,$email);
                                    preg_match_all($rex,!empty($content['customer_email'])?$content['customer_email']:'',$email_new);
                                    if(!empty($content['customer_email'])){
                                        if(isset($email_new[1][0])){
                                            $email_new = substr($email_new[1][0], 0, 3) . '***' . substr($email_new[1][0], -2, 2).strstr(!empty($content['customer_email'])?$content['customer_email']:'','@');
                                        }else{
                                            $email_new = '';
                                        }
                                    }
                                    if(isset($email[1][0])){
                                        $email = substr($email[1][0], 0, 3) . '***' . substr($email[1][0], -2, 2).strstr($assign->customer_email,'@');
                                    }else{
                                        $email = '';
                                    }
                                ?>
                            <tr>
                                <td><input type="checkbox" class="iCheck checkSingle" value="{{$assign->id}}" name="ids[]" /></td>
                                <td>{{ $assign->id }}</td>

                                <td>
                                    <a href='javascript:;'>@if(!empty($content['customer_name']) && $content['customer_name'] != $assign->customer_name) <span class="text_line_through">{{$assign->customer_name}}</span> -> {{$content['customer_name']}} @elseif(!empty($content['customer_name']) && $content['customer_name'] == $assign->customer_name) {{$assign->customer_name}}@else {{$assign->customer_name}} @endif</a>
                                </td>
                                <td>@if(!empty($content['customer_phone']) && $content['customer_phone'] != $assign->customer_phone) <span class="text_line_through">{{$phone_num}}</span> -> {{$phone_num_new}} @elseif(!empty($content['customer_phone']) && $content['customer_phone'] == $assign->customer_phone) {{$phone_num}}@else {{$phone_num}} @endif</td>
                                <td>@if(!empty($content['customer_email']) && $content['customer_email'] != $assign->customer_email) <span class="text_line_through">{{$email}}</span> -> {{$email_new}} @elseif(!empty($content['customer_email']) && $content['customer_email'] == $assign->customer_email) {{$email}}@else {{$email}} @endif</td>
                                <td>{{ $assign->campaign->title }}</td>
{{--                                <td class="text-uppercase">{{ $assign->source ?: '' }} </td>--}}
                                {{--@php
                                    $project = \App\Models\Campaign::getProjectById(!empty($content['project_id'])?$content['project_id']:0);
                                @endphp
                                <td>{{ $project['cb_title'] }}</td>--}}
                                <td>
                                    <?php $status_his = !empty($content['status'])?$content['status']:5 ?>
                                    @if ($assign->status !== null)
                                            @if($status_his == 0)
                                                <span class="btn-status label label-sm label-danger">Không quan tâm</span>
                                            @elseif($status_his == 1)
                                                <span class="btn-status label label-sm label-success">Quan tâm</span>
                                            @elseif($status_his == 2)
                                                <span class="btn-status label label-sm label-danger">Không bắt máy</span>
                                            @elseif($status_his == 3)
                                                <span class="btn-status label label-sm label-warning">Sai số điện thoại</span>
                                            @elseif($status_his == 4)
                                                <span class="btn-status label label-sm label-success">Gọi lại sau</span>
                                            @endif
                                    @endif
                                </td>
                                <td>{{ !empty($content['description'])?$content['description']:'' }} </td>
                                @can('view_ctv', app(App\Models\CampaignAssign::class))
                                    <?php
                                    $user_result = \App\Models\BoUser::where('ub_account_tvc',$assign->staff_account)->select('u_type')->first();
                                    ?>
                                    <td>{{ !empty($assign->staff_account)?$assign->staff_account:'' }} @if(isset($user_result->u_type)?$user_result->u_type:0 == 1)(CTV)@endif</td>
                                @endcan
                                <td>
                                    @if ($assign->check_diary === 0)
                                          {{--  <a href='{{ route("admin.campaign_assign.edit_diary", ['id' => $assign->id]) }}' type="button" class="btn btn-sm btn-warning" title="Phản hồi" data-assign="0" data-assigned="{{ $assign->id }}">
                                                <i class="fa fa-weixin"></i>
                                            </a>--}}
                                            <a href='javascript:void(0);' type="button" data-assign='{{$assign->id}}' class="btn btn-sm btn-success js-btn-add-edit-diary-ctv" title="Phản hồi" data-assigned="{{ $assign->id }}" data-toggle="modal" data-target="#campaign-assign-diary-ctv">
                                                <i class="fa fa-phone"></i>
                                            </a>
                                    @else
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <input type="submit" class="js-submit-form-index hidden" value="" />
            </form>
        </div>
        <!-- /.box-body -->

        <div class="box-footer clearfix">
            <div class="row">
                <div class="col-sm-3">
                    <span class="record-total">Tổng: {{ $assigns->total() }} bản ghi</span>
                </div>
                <div class="col-sm-6 text-center">
                    <div class="pagination-panel">
                        {{ $assigns->appends(Request::all())->onEachSide(1)->links() }}
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

<div id="campaign-assign-import-ctv" class="modal fade" role="dialog">
    <div class="modal-dialog  modal-lg">
        <!-- Modal content-->
        <div class="modal-content">


                <div class="body_import">
                </div>

        </div>
    </div>
</div>
<div id="campaign-assign-diary" class="modal fade" role="dialog">
    <div class="modal-dialog  modal-lg">
        <!-- Modal content-->
        <div class="modal-content">
            <form action="{{ route('admin.campaign_assign.confirm') }}" method="POST" class="form-validate form-horizontal" id="confirm_assing-diary" autocomplete="off">
                @csrf
                <div class="body_pop">
                </div>
            </form>
        </div>
    </div>
</div>
<div id="campaign-assign-diary-ctv" class="modal fade" role="dialog">
    <div class="modal-dialog  modal-lg">
        <!-- Modal content-->
        <div class="modal-content">
            <form action="{{ route('admin.campaign_assign.confirmctv') }}" method="POST" class="form-validate form-horizontal" id="confirm_assing-diary-ctv" autocomplete="off">
                @csrf
                <div class="body_pop_ctv">
                </div>
            </form>
        </div>
    </div>
</div>


@endsection


@section('javascript')
<script src="/adminLTE/plugins/moment/moment.min.js"></script>
<script src="/adminLTE/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-star-rating/4.0.2/js/star-rating.min.js"></script>
<script type="text/javascript">

    function settingClientEvents(client) {
        client.on('connect', function () {
            console.log('connected to StringeeServer');
        });
        client.on('authen', function (res) {
            console.log('authen: ', res);
            $('#loggedUserId').html(res.userId);
            if(res.message == 'SUCCESS'){
                $('#callBtn').click();
            }
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
            $('.status_call').show().delay(1000).fadeOut();
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
        object: '#select-campaigns',
        url: '{{ url("/admin/campaigns/ajax/get-all-campaigns") }}',
        data_id: 'id',
        data_text: 'title',
        title_default: 'Chọn chiến dịch'
    });

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
                minimumInputLength: 3,
            }
        });
    }

});

$('.js-btn-add-edit-diary').click(function() {
    var assign_id = $(this).data('assign');
    $.get('{{ url("/admin/campaign-assign/ajax-edit-diary") }}', {
        assign_id: assign_id,
    }, function(data) {
        $('#campaign-assign-diary .body_pop').html(data);
    });
});

$('.js-show-insert_camp').click(function() {
    $.get('{{ url("/admin/campaign-assign/ajax-show-campain-insert") }}', {
    }, function(data) {
        $('#campaign-assign-import-ctv .body_import').html(data);
    });
});
$('.js-btn-add-edit-diary-ctv').click(function() {
    var assign_id = $(this).data('assign');
    $.get('{{ url("/admin/campaign-assign/ajax-edit-diary-ctv") }}', {
        assign_id: assign_id,
    }, function(data) {
        $('#campaign-assign-diary-ctv .body_pop_ctv').html(data);
    });
});
    $('#campaign-assign-diary-ctv').on('hidden.bs.modal', function (e) {
        testHangupCall();
    });
$('.check_cus_add').click(function() {
    var assign_id = $(this).data('assign');
    $.get('{{ url("/admin/campaign-assign/ajax-edit-diary-fast") }}', {
        assign_id: assign_id,
    }, function(data) {
        $('#campaign-assign-diary-ctv .body_pop_ctv').html(data);
    });
});

$('.btn-confirm-assign').click(function(e) {
    e.preventDefault();

    if (confirm('Có chắc bạn muốn duyệt thông tin này?')) {
            $('#confirm_assing-diary').submit();
        }
});
$('.btn-confirm-assign-ctv').click(function(e) {
    e.preventDefault();

    if (confirm('Có chắc bạn muốn duyệt thông tin này?')) {
            $('#confirm_assing-diary-ctv').submit();
        }
});

sidebar('campaign_assigns', 'assigned');
</script>
@endsection