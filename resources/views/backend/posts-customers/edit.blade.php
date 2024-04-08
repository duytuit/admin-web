@extends('backend.layouts.master')

@section('content')

    <section class="content-header">
        <h1>
            Gửi thông báo bàn giao căn hộ tới khách hàng
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('admin.home') }}"><i class="fa fa-home"></i> Trang chủ</a></li>
            <li class="active">{{ $heading }}</li>
        </ol>
    </section>

    <section class="content">
        @if( in_array('admin.posts.save',@$user_access_router))
            <form id="form-posts" action="{{ route('admin.posts.save', ['id' => $id, 'type' => $type]) }}" method="post" autocomplete="off">
                @csrf
                @method('POST')

                @php
                    $old = old();
                @endphp

                <div class="row">
                    <div class="col-sm-8">
                        <div class="box no-border-top">
                            <div class="box-body no-padding">
                                <div class="nav-tabs-custom no-margin">
                                    <!-- Nav tabs -->
                                    <ul class="nav nav-tabs">
                                        <li class="active"><a href="#general" data-toggle="tab">Tổng quan</a></li>
                                    </ul>
                                    <!-- Tab panes -->
                                    <div class="tab-content">
                                        @include('backend.posts-customers.edit.general')
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="box box-primary">
                            <div class="box-header with-border">
                                Thiết lập thông báo
                            </div>
                            <div class="box-body">
                                <div class="form-group">
                                    <label class="control-label">Gửi đến</label>
                                    <div class="notify-group">
                                        <input type="hidden" name="notify[is_sent]" value="" >
                                        <input type="hidden" name="notify[is_sent_sms]" value="" >
                                        <label class="notify-label">
                                            <input type="checkbox" name="notify[send_mail]" value="1" class="iCheck">
                                            Email
                                        </label>
                                        <label class="notify-label">
                                            <input type="checkbox" name="notify[send_sms]" value="1" class="iCheck" >
                                            SMS
                                        </label>
                                        <label class="notify-label">
                                            <input type="checkbox" name="notify[send_app]" value="1" class="iCheck">
                                            App Notify
                                        </label>
                                    </div>
                                </div>

                                <div class="form-group hidden">
                                    <label class="control-label">Thông tin</label>
                                    <div class="notify-group">
                                        <label class="private-label" data-visible="show" data-target="#private">
                                            <input type="radio" name="private" value="1" class="iCheck input-private">
                                            Nội bộ
                                        </label>
                                        <label class="private-label" data-visible="hide" data-target="#private">
                                            <input type="radio" name="private" value="0" class="iCheck input-private">
                                            Công khai
                                        </label>
                                    </div>
                                </div>

                                <div class="form-group" id="private" >
                                    <label class="control-label">Căn hộ nhận tin thông báo</label>
                                    <div class="notify-group">
                                        <label class="control-label">Căn hộ</label>
                                        <div id="notify-customer" style="margin: 5px 0px 15px;">
                                            <select name="bdc_apartment_id" id="ip-apartment" multiple class="form-control">
                                                <option value="">Căn hộ</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label">Trạng thái</label>
                                    <div>
                                        <label class="switch">
                                            <input type="checkbox" name="status" value="1" />
                                            <span class="slider round"></span>
                                        </label>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <button type="submit" class="btn btn-success" form="form-posts" value="submit">{{ $id ? 'Cập nhật' : 'Thêm mới' }}</button>
                                    &nbsp;
                                        <a href="" class="btn btn-danger" form="form-posts" value="submit">Quay lại</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        @endif
    </section>

@endsection

@section('stylesheet')

    <link rel="stylesheet" href="/adminLTE/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css" />

@endsection

@section('javascript')

    <!-- Datetime Picker -->
    <script src="/adminLTE/plugins/moment/moment.min.js"></script>
    <script src="/adminLTE/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js"></script>
    <!-- TinyMCE -->
    <!-- <script src="/adminLTE/plugins/tinymce/tinymce.min.js"></script>
    <script src="/adminLTE/plugins/tinymce/config.js"></script> -->

    @include('backend.posts-customers.edit.js-poll_options')
    @include('backend.posts-customers.edit.js-images')
    @include('backend.posts-customers.edit.js-attaches')
    @include('backend.posts-customers.edit.js-notify')

    <script>
        @if($id > 0)
        sidebar('{{ $type }}', 'index');
        @else
        sidebar('{{ $type }}', 'create');
        @endif

        $(function(){
             get_data_select_apartment1({
                object: '#ip-place_id',
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
        })
    </script>

@endsection