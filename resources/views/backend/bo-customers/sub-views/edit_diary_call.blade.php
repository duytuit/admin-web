<?php

    $diary = isset($customer['diaries'][0])?$customer['diaries'][0]:'' ;
//    dd($customer);
?>
<div class="modal-header bg-primary">
    <button type="button" class="close" data-dismiss="modal">&times;</button>
    <h4 class="modal-title">Chăm sóc khách hàng: {{ $customer['cb_name'] ? $customer['cb_name'] :'' }}</h4>
</div>
<div class="modal-body">
    <input type="hidden" name="campaign_assign_id" value="{{ !empty($diary['campaign_assign_id'])?$diary['campaign_assign_id']:'' }}" />
    <div class="box_call_customer" style="display: none;">
        <div>
{{--            <label for="">Đang gọi khách hàng: </label>--}}
            <input id="callTo" call="{{ base64_encode($customer['cb_phone'] ? $customer['cb_phone'] :'') }}" type="hidden" name="toUsername" style="width: 200px;" placeholder="userId or number" value="">

            <div id="incoming-call-div" style="display: none;">
                Incoming call from: <span id="incoming_call_from"></span>
                <a id="answerBtn" onclick="testAnswerCall()">Answer</a>
                <a id="rejectBtn" onclick="testRejectCall()">Reject</a>
            </div>
        </div>

        <div hidden>
            <br/>
            Logged in: <span id="loggedUserId" style="color: red">Not logged</span>
        </div>



        <div style="color: #949494;">
            <br/>

        </div>

        <div hidden>
            <video id="remoteVideo" playsinline autoplay style="width: 350px"></video>
        </div>
    </div>
    <div class="box_reviews_customer">
        <div class="">
            <div class="col-sm-6">
                <div class="form-group">
                    <label class="control-label" style="font-size: 17px; padding-left: 10px;">Thông tin khách hàng</label>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label">Họ tên:</label>
                    <div class="col-sm-10" style="font-size: 12px;">
                        <input type="text" name="cb_name" class="form-control" value="{{ $customer['cb_name'] ? $customer['cb_name'] :'' }}" placeholder="Tên khách hàng">
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label">Số điện thoại:</label>
                    <div class="col-sm-10" style="font-size: 12px;">
                        <input type="text" name="cb_phone" class="form-control" value="{{ $customer['cb_phone'] ? $customer['cb_phone'] :'' }}" placeholder="Số điện thoại">
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label">Email:</label>
                    <div class="col-sm-10" style="font-size: 12px;">
                        <input type="text" name="cb_email" class="form-control" value="{{ $customer['cb_email'] ? $customer['cb_email'] :'' }}" placeholder="vd:abc@gmail.com">
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label">CMND:</label>
                    <div class="col-sm-10" style="font-size: 12px;">
                        <input type="text" name="cmnd" class="form-control" value="{{ $customer['cmnd'] ? $customer['cmnd'] :'' }}" placeholder="Số CMND/Hộ chiếu">
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label">Địa chỉ:</label>
                    <div class="col-sm-10" style="font-size: 12px;">
                        <textarea name="address" id="ip_address" cols="30" rows="2" class="form-control" placeholder="Địa chỉ chi tiết">{{ $customer['address'] ? $customer['address'] :'' }}</textarea>
                    </div>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="form-group">
                    <label class="control-label" style="font-size: 17px; padding-left: 10px;">Thông tin phản hồi</label>
                </div>
                <div class="form-group">
                    <label class="col-sm-4 control-label" style="padding-top: 0px;">KH phản hồi</label>
                    <div class="col-sm-8">
                        <label style="margin-right: 15px;">
                            <input type="radio" name="feedback" class="iCheck" value="1" {{ !empty($diary['status'])?$diary['status']:'' == 1 ? 'checked' : '' }}>
                            Quan tâm
                        </label>
                        <label>
                            <input type="radio" name="feedback" class="iCheck" value="0" {{ !empty($diary['status'])?$diary['status']:'' == 0 ? 'checked' : '' }}>
                            Không quan tâm
                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-4 control-label" style="padding-top: 0px;">Dự án quan tâm <span class="text-danger">*</span></label>
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
                            @elseif(!empty($diary['project_id']))
                                @php
                                    $project = \App\Models\Campaign::getProjectById($diary['project_id']);
                                @endphp
                                <option value="{{ $diary['project_id'] }}" selected>{{ $project['cb_title'] }}</option>
                            @endif
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-4 control-label">Điểm số <span class="text-danger">*</span></label>
                    <div class="col-sm-8" style="font-size: 12px;">
                        <input id="input-1" name="cd_rating" class="rating rating-loading" data-min="0" data-max="5" data-step="1" value="{{ !empty($diary['cd_rating']) ? $diary['cd_rating'] : 0 }}" data-size="xs">
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-4 control-label">Tiêu chí:</label>
                    <div class="col-sm-8">
                            @foreach ($filters as $filter)
                                <label class="control-label">{{ $filter['title'] }}:</label>
                                <div style="padding: 15px; display: flex;">
                                    @foreach ($filter['value'] as $item)
                                        <label style="display:flex;flex:1">
                                            <input type="checkbox" name="filters[]" value="{{ $item['id'] }}" @if(in_array($item['id'], !empty($diary['filters'])?$diary['filters']: [])) checked @endif class="iCheck" />
                                            &nbsp;&nbsp;{{ $item['value'] }}
                                        </label>
                                    @endforeach
                                </div>
                            @endforeach


                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-4 control-label" style="padding-top: 0px;">Ghi chú</label>
                    <div class="col-sm-8">
                        <textarea class="miniEditor form-control" name="cd_description">{{ !empty($diary['cd_description']) ? $diary['cd_description'] : '' }}</textarea>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="clear_fix"></div>
<div class="status_call" style="display: none;">
    Call status: <span id="callStatus" style="color: red">Not started</span>
</div>
<div class="modal-footer" style="display: flex;justify-content: center;">
    <a id="callBtn" class="btn btn-facebook" onclick="testMakeCall()">Gọi điện</a>
    <a id="hangupBtn" class="btn btn-danger" onclick="testHangupCall()" style="width: auto;height: auto;border-radius: unset;margin: 0;background-color:#dd4b39;display: none;">Đóng cuộc gọi</a>
    <button class="btn btn-primary btn-confirm-assign" style="margin-right: 5px;"><i class="fa fa-save"></i> Cập nhật</button>
    <button type="button" class="btn btn-danger pull-right" data-dismiss="modal"><i class="fa fa-close"></i> Đóng</button>

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
    });

</script>
