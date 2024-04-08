<?php
    $logs = $assign->logs;
    if($logs){
        $diary = array_pop($logs);
        $content = $diary['content'];
        $project = !empty($content['project_id']) ? \App\Models\CustomerDiary::getProjectById($content['project_id']) : '';
    }else{
        $diary = '';
    }
//    dd($logs);


?>
<div class="modal-header bg-primary">
    <button type="button" class="close" data-dismiss="modal">&times;</button>
    <h4 class="modal-title" style="display: inline-block">Chăm sóc khách hàng: {{ $assign->customer_name ? $assign->customer_name :'' }} </h4>
    <div class="box_call_cus">
        <label for="">Trạng thái <span class="check_call">Đang gọi</span></label>
        <input id="callTo" call="{{ base64_encode($assign->customer_phone ? $assign->customer_phone :'') }}" type="hidden" name="toUsername" style="width: 200px;" placeholder="userId or number" value="">

        <a id="callBtn" onclick="testMakeCall()" class="hidden">Call</a>
        <a id="hangupBtn" onclick="testHangupCall()"><i class="fa fa-phone" style="color: red;transform: rotate(135deg);"></i></a>
    </div>
</div>
<div class="modal-body">
    <input type="hidden" name="campaign_assign_id" value="{{ $assign->id }}" />
    <div class="box_call_customer">
        <div class="row">
          {{--  <label for="">Đang gọi khách hàng: {{ $assign->customer_name ? $assign->customer_name :'' }}</label>
            <input id="callTo" call="{{ base64_encode($assign->customer_phone ? $assign->customer_phone :'') }}" type="hidden" name="toUsername" style="width: 200px;" placeholder="userId or number" value="">

            <a id="callBtn" onclick="testMakeCall()">Call</a>
            <a id="hangupBtn" onclick="testHangupCall()">Hangup</a>
            <div id="incoming-call-div" style="display: none;">
                Incoming call from: <span id="incoming_call_from"></span>
                <a id="answerBtn" onclick="testAnswerCall()">Answer</a>
                <a id="rejectBtn" onclick="testRejectCall()">Reject</a>
            </div>--}}
            <div class="col-sm-2"><label for="">Thao tác nhanh:</label></div>
            <div class="col-sm-10">
                <ul class="btn-status-info">
                    <li><label for="check-btn-2" class="btn btn-danger">Không bắt máy</label><input type="radio" name="feedback" class="hidden" value="2" id="check-btn-2" @if((!empty($content['status'])?$content['status']:4) == 2 )checked="checked"@endif></li>
                    <li><label for="check-btn-0" class="btn btn-danger">Không quan tâm</label><input type="radio" name="feedback" class="hidden" value="0" id="check-btn-0" @if((!empty($content['status'])?$content['status']:4) == 0 )checked="checked"@endif></li>
                    <li><label for="check-btn-3" class="btn btn-warning">Sai số điện thoại</label><input type="radio" name="feedback" class="hidden" value="3" id="check-btn-3" @if((!empty($content['status'])?$content['status']:4) == 3 )checked="checked"@endif></li>
                    <li><label for="check-btn-4" class="btn btn-success">Gọi lại sau</label><input type="radio" name="feedback" class="hidden" value="4" id="check-btn-4" @if((!empty($content['status'])?$content['status']:4) == 4 )checked="checked"@endif></li>
                    <li><label for="check-btn-1" class="btn btn-success">Quan tâm</label><input type="radio" name="feedback" class="hidden" value="1" id="check-btn-1" @if((!empty($content['status'])?$content['status']:4) == 1 )checked="checked"@endif ></li>
                </ul>
            </div>
        </div>

        <div hidden>
            <br/>
            Logged in: <span id="loggedUserId" style="color: red">Not logged</span>
        </div>



        <div style="color: #949494;">
            <br/>
            <div class="status_call" style="display: none">
                Call status: <span id="callStatus" style="color: red">Not started</span>
            </div>
        </div>

        <div hidden>
            <video id="remoteVideo" playsinline autoplay style="width: 350px"></video>
        </div>
    </div>
    <div class="box_reviews_customer">
        <div class="row">
            <div class="col-sm-6 bg-sp">
                <div class="site-small">
                    <div class="form-group">
                        <label class="control-label" style="font-size: 17px; padding-left: 10px;">Thông tin lịch sử</label>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-4 control-label label_his">Họ tên:</label>
                        <div class="col-sm-8">
                            {{ !empty($content['customer_name'])?$content['customer_name']: $assign->customer_name }}
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-4 control-label label_his">Số điện thoại:</label>
                        <div class="col-sm-8">
                            <?php
                                $phone_num = substr($assign->customer_phone, 0, 4) . '****' . substr($assign->customer_phone, 8, 4);

                                $phone_num_new = !empty($content['customer_phone'])?$content['customer_phone']:'';
                                if(!empty($content['customer_phone'])){
                                    $phone_num_new = substr($phone_num_new, 0, 4) . '****' . substr($phone_num_new, 8, 4);
                                }
                            ?>
                            {{ !empty($content['customer_phone'])?$phone_num_new: $phone_num }}
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-4 control-label label_his">Email:</label>
                        <div class="col-sm-8">
                            <?php
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
                            {{ !empty($content['customer_email'])?$email_new: $email }}
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-4 control-label label_his">Chiến dịch:</label>
                        <div class="col-sm-8">
                            {{ $assign->campaign->title }}
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-4 control-label label_his">Dự án quan tâm:</label>
                        <div class="col-sm-8">
                            @if(!empty($content['project_id']))
                                @php
                                    $project = \App\Models\Campaign::getProjectById($content['project_id']);
                                @endphp
                                {{ $project['cb_title'] }}
                            @else
                                Không có
                            @endif

                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-4 control-label label_his">Ghi chú:</label>
                        <div class="col-sm-8">
                            {{ !empty($content['description'])?$content['description']: 'Không có' }}
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-4 control-label label_his">Trạng thái:</label>
                        <div class="col-sm-8">
                            <?php $status_his = !empty($content['status'])?$content['status']:'' ?>
                            @if($status_his == 0)
                                Không quan tâm
                            @elseif($status_his == 1)
                                Quan tâm
                            @elseif($status_his == 2)
                                Không bắt máy
                            @elseif($status_his == 3)
                                Sai số điện thoại
                            @elseif($status_his == 4)
                                Gọi lại sau
                            @elseif($status_his == '')
                                chưa có trạng thái
                            @endif
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-4 control-label label_his">Duyệt:</label>
                        <div class="col-sm-8">
                            <?php $role_his = !empty($diary['role'])?$diary['role']:0 ?>
                            @if($role_his == 0)
                                @if($status_his == 2)
                                    Khách hàng không bắt máy, gọi lại
                                @elseif($status_his == 3)
                                    Sai số điện thoại khách hàng, yêu cầu hủy
                                @elseif($status_his == 4)
                                    Khách hàng yêu cầu gọi lại sau
                                @else
                                    Chưa duyệt
                                @endif
                            @elseif($role_his == 1)
                                CTV xác nhận duyệt
                            @elseif($role_his == 2)
                                Đã được thêm vào danh sách khách hàng
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 bg-his">
                <div class="form-group">
                    <label class="control-label" style="font-size: 17px; padding-left: 10px;">Thông tin khách hàng</label>
                </div>

                    <input type="hidden" name="assign_id" value="{{ $assign->id }}" />
                    <input type="hidden" name="user_name" value="" />

                    <div class="form-group">
                        <label class="col-sm-4 control-label label_info">Họ tên:</label>
                        <div class="col-sm-8" style="font-size: 12px;">
                            <input type="text" name="customer_name" class="form-control" value="{{ $assign->customer_name }}" placeholder="Tên khách hàng">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-4 control-label label_info">Email:</label>
                        <div class="col-sm-8" style="font-size: 12px;">
                            <input type="text" name="customer_email" class="form-control" value="" placeholder="Email Khách hàng">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-4 control-label label_info">Số điện thoại:</label>
                        <div class="col-sm-8" style="font-size: 12px;">
                            <input type="text" name="customer_phone" class="form-control" value="" placeholder="Số điện thoại Khách hàng">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-4 control-label label_info" style="padding-top: 0px;">Dự án quan tâm <span class="text-danger">*</span></label>
                        <div class="col-sm-8">
                            {{--        <p>{{ $project ? $project['cb_title'] : 'Không có dự án' }} </p>--}}
                            {{--        <input type="hidden" name="project_id" value="{{ !empty($content['project_id']) ? $content['project_id'] : null}}" />--}}
                            <select class="form-control" name="project_id" id="select-project-edit-diary" style="width: 100%;">
                                <option value="">Chọn dự án</option>
                                @if(old('project_id') )
                                    @php
                                        $project = \App\Models\Campaign::getProjectById(old('project_id'));
                                    @endphp
                                    <option value="{{ old('project_id') }}" selected="">{{ $project['cb_title'] }}</option>
                                @elseif(!empty($content['project_id']))
                                    @php
                                        $project = \App\Models\Campaign::getProjectById($content['project_id']);
                                    @endphp
                                    <option value="{{ $content['project_id'] }}" selected>{{ $project['cb_title'] }}</option>
                                @endif
                            </select>
                        </div>
                    </div>
                    <div class="panel panel-primary scrol-nc">
                        <div class="panel-heading">
                            <a data-toggle="collapse" href="#nangcao" class="show_up"><h4 class="panel-title">Nâng cao <i class="fr fa fa-angle-double-down"></i></h4></a>
                        </div>
                        <div id="nangcao" class="panel-collapse collapse">
                            <div class="form-horizontal">
                                <div class="panel-body">
                                    <div class="form-group">
                                        <label class="col-sm-4 control-label label_info">Điểm số <span class="text-danger">*</span></label>
                                        <div class="col-sm-8" style="font-size: 12px;">
                                            <input id="input-1" name="cd_rating" class="rating rating-loading" data-min="0" data-max="5" data-step="1" value="{{ !empty($content['cd_rating']) ? $content['cd_rating'] :  !empty($content['rating']) ? $content['rating'] :  0 }}" data-size="xs">
                                        </div>
                                    </div>
                                    @php
                                        $content['filters'] = empty($content['filters']) ? [] : $content['filters'];
                                    @endphp
                                    <div class="form-group" style="margin-bottom: 0;">
                                        <label class="col-sm-4 control-label label_info">Tiêu chí:</label>
                                        <div class="col-sm-12">
                                            @foreach ($filters as $filter)
                                                <label class="control-label">{{ $filter['title'] }}:</label>
                                                <div style="padding: 15px; display: flex;">
                                                    @foreach ($filter['value'] as $item)
                                                        <label style="display:flex;flex:1">
                                                            <input type="checkbox" name="filters[]" value="{{ $item['id'] }}"  @if(in_array($item['id'], $content['filters'])) checked @endif class="iCheck" />
                                                            &nbsp;&nbsp;{{ $item['value'] }}
                                                        </label>
                                                    @endforeach
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-4 control-label label_info" style="padding-top: 0px;text-align: left">Ghi chú</label>
                        <div class="col-sm-12">
                            <input type="hidden" name="cd_description" value="" />
                            <textarea name="cd_description" id="textarea-cd_description" cols="10" rows="5" placeholder="Ghi chú ở đây" class="form-control">{{!empty($content['description']) ? $content['description'] :''}}</textarea>
                        </div>
                    </div>
            </div>
        </div>
    </div>
</div>
<div class="modal-footer">
    <button class="btn btn-primary btn-confirm-assign-ctv" style="margin-right: 5px;"><i class="fa fa-save"></i>&nbsp;&nbsp;Duyệt</button>
    <button type="button" class="btn btn-danger pull-right" data-dismiss="modal"><i class="fa fa-close"></i>&nbsp;&nbsp;Hủy</button>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-star-rating/4.0.2/js/star-rating.min.js"></script>
<script type="text/javascript" src="/adminLTE/plugins/select2/js/select2.full.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-star-rating/4.0.2/css/star-rating.min.css">
<script type="text/javascript" src="{{ url('adminLTE/js/stringee/socket.io-2.2.0.js') }}"></script>
<script type="text/javascript" src="{{ url('adminLTE/js/stringee/StringeeSDK-1.5.10.js') }}"></script>

<script type="text/javascript">
    var stringeeClient;
    var fromNumber = '842473030012';
    var access_token = 'eyJjdHkiOiJzdHJpbmdlZS1hcGk7dj0xIiwidHlwIjoiSldUIiwiYWxnIjoiSFMyNTYifQ.eyJqdGkiOiJTS09YVG5ieFFTZjg0a2IwRnh3QmdLM0FqUmd0cThxakYtMTU2ODc3NjkyNyIsImlzcyI6IlNLT1hUbmJ4UVNmODRrYjBGeHdCZ0szQWpSZ3RxOHFqRiIsImV4cCI6MTU3MTM2ODkyNywidXNlcklkIjoicGhhbXRydW5nIiwiaWNjX2FwaSI6dHJ1ZX0.hoAADQgVAEA1GNGDs-7gGW1idWdHure-YPdCfjWsOsU';
    var call;
    $(document).ready(function () {
        console.log('StringeeUtil.isWebRTCSupported: ' + StringeeUtil.isWebRTCSupported());
        stringeeClient = new StringeeClient();
        settingClientEvents(stringeeClient);
        stringeeClient.connect(access_token);
    });
    function testMakeCall() {
        call = new StringeeCall(stringeeClient, fromNumber, phoneNum());
        settingCallEvents(call);
        call.makeCall(function (res) {
            console.log('make call callback: ' + JSON.stringify(res));
            if (res.r !== 0) {
                $('#callStatus').html(res.message);
                $('.status_call').show().delay(10000).fadeOut();
                if(res.message != 'GET_USER_MEDIA_ERROR'){
                    $('#hangupBtn').show();
                    $('#callBtn').hide();
                }
            }
        });
    }
</script>
<script>

    //Icheck
    $('input.iCheck').iCheck({
        checkboxClass: 'icheckbox_square-green',
        radioClass: 'iradio_square-green',
        increaseArea: '20%' // optional
    });
    $(function() {
        $("input.rating").rating();
        // Chọn dự án
        get_data_select2({
            object: '#select-project-edit-diary',
            url: '{{ route("admin.campaigns.project") }}',
            data_id: 'id',
            data_text: 'title',
            title_default: 'Chọn dự án'
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
        $('.show_up').on('click',function () {
            var _this = $(this);
            if($('#nangcao').hasClass('in')){
                _this.find('i').addClass('fa-angle-double-down').removeClass('fa-angle-double-up')
            }else{
                _this.find('i').addClass('fa-angle-double-up').removeClass('fa-angle-double-down')
            }
        });
    });
</script>
