@inject('request', 'Illuminate\Http\Request')
@extends('backend.layouts.master')
@section('content')
<section class="content-header">
    <h1>
        Quản lý công việc
    </h1>
    <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Trang chủ</a></li>
        <li class="active">danh sách công việc</li>
    </ol>
</section>

<section class="content">
    <div class="row">
        <div class="box-body">
            <div class="col-md-12">
                <!-- Custom Tabs -->
                <div class="nav-tabs-custom">
                    <ul class="nav nav-tabs">
                        <li class="active"><a data-toggle="tab" href="#danh_sach_cong_viec">Danh sách công việc</a></li>
                        <li ><a data-toggle="tab" href="#workdiary_repeat">Quản lý công việc lặp lại</a></li>
                        <li ><a data-toggle="tab" href="#switch_shift">Quản lý yêu cầu</a></li>
                        @if($type_manager == 1)
                            <li ><a data-toggle="tab" href="#danh_muc">Danh mục công việc</a></li>
                            <li ><a data-toggle="tab" href="#check_list">Checklist</a></li>
                        @endif


{{--                        <li ><a data-toggle="tab" href="#quan_ly_ca">Quản lý ca</a></li>--}}
                    </ul>
                    <div class="tab-content">
                        @include('work-diary-v2.tabs.report-work')
                        @include('work-diary-v2.tabs.workdiary-repeat-list')
                        @include('work-diary-v2.tabs.switch-shift')
                        @if($type_manager == 1)
                            @include('work-diary-v2.tabs.category-work')
                            @include('work-diary-v2.tabs.check-list')
                        @endif
{{--                        @include('work-diary-v2.tabs.shift')--}}
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
@section('stylesheet')
    <style>
         @media (min-width: 768px) {
            .shift {
                width: 600px;
                margin: 30px auto;
            }
        }
        .onoffswitch {
            position: relative;
            width: 70px;
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
        }

        .onoffswitch-checkbox {
            display: none;
        }

        .onoffswitch-label {
            display: block;
            overflow: hidden;
            cursor: pointer;
            border: 2px solid #999999;
            border-radius: 16px;
        }

        .onoffswitch-inner {
            display: block;
            width: 200%;
            margin-left: -100%;
            transition: margin 0.3s ease-in 0s;
        }

        .onoffswitch-inner:before, .onoffswitch-inner:after {
            display: block;
            float: left;
            width: 50%;
            height: 21px;
            padding: 0;
            line-height: 21px;
            font-size: 9px;
            color: white;
            font-family: Trebuchet, Arial, sans-serif;
            font-weight: bold;
            box-sizing: border-box;
        }

        .onoffswitch-inner:before {
            content: "ACTIVE";
            padding-left: 12px;
            background-color: #00C0EF;
            color: #FFFFFF;
        }

        .onoffswitch-inner:after {
            content: "INACTIVE";
            background-color: #EEEEEE;
            color: #999999;
            text-align: right;
        }

        .onoffswitch-switch {
            display: block;
            width: 23px;
            height: 23px;
            margin: 1px;
            background: #FFFFFF;
            position: absolute;
            top: 0;
            bottom: 0;
            right: 45px;
            border: 2px solid #999999;
            border-radius: 16px;
            transition: all 0.3s ease-in 0s;
        }

        .onoffswitch-checkbox:checked + .onoffswitch-label .onoffswitch-inner {
            margin-left: 0;
        }

        .onoffswitch-checkbox:checked + .onoffswitch-label .onoffswitch-switch {
            right: 0px;
        }
    </style>
@endsection
<link rel="stylesheet" href="{{ url('adminLTE/plugins/jquery-ui/jquery-ui.min.css') }}" />
<script type="text/javascript" src="{{ url('adminLTE/plugins/jquery-3.5.1/jquery-3.5.1.min.js') }}"></script>
<script type="text/javascript" src="{{ url('adminLTE/plugins/jquery-ui/jquery-ui.min.js') }}"></script>
<script type="text/javascript" src="{{ url('adminLTE/js/pagination.min.js') }}"></script>
<script type="text/javascript" src="{{ url('adminLTE/plugins/select2/js/select2.full.min.js') }}"></script>
<script>
    async function postMethod(url,param,file) {
        let method='post';
        if(file){
            let _result = await call_api_form_data(method, url,param);
            if(_result.status == true){
                toastr.success(_result.mess);
                setTimeout(function(){
                    location.reload();
                }, 1000);
            }else {
                toastr.warning(_result.mess);
            }

        }else{
            let _result = await call_api_data_json(method, url,param);
            if(_result.status == true){
                toastr.success(_result.mess);
                setTimeout(function(){
                    location.reload();
                }, 1000);
            }else {
                toastr.warning(_result.mess);
            }
        }

    }
    async function postDel(url,id,reload=true,param=null) {
        if(confirm("Bạn có chắc chắn muốn xóa không?")){
            let method='post';
            let param_query_old = "{{ $array_search }}";
            let param_query = param_query_old.replaceAll("&amp;", "&")
            if(id){
                param_query +="&id="+id;
            }
            console.log(param_query);
            let _result = await call_api(method,url+param_query,param);
            toastr.success(_result.mess);
            if(reload == true){
                setTimeout(function(){
                  location.reload();
                }, 1000);
            }
        }
        else{
            return false;
        }
    }
    async function postDelNoConfirm(url,id,reload=true,param=null) {
            let method='post';
            let param_query_old = "{{ $array_search }}";
            let param_query = param_query_old.replaceAll("&amp;", "&")
            if(id){
                param_query +="&id="+id;
            }
            console.log(param_query);
            let _result = await call_api(method,url+param_query,param);
            toastr.success(_result.mess);
            if(reload == true){
                setTimeout(function(){
                    location.reload();
                }, 1000);
            }
    }
    $(document).ready(function () {
        get_data_select_cat_asset({
            object: '#asset_category_id,#search_asset_category_id,#search_asset_category_id',
            url: "{{route('admin.ajax.ajaxGetAssetCategory')}}",
            data_id: 'id',
            data_text: 'title',
            title_default: 'Chọn danh mục'
        });

        function get_data_select_cat_asset(options) {
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
        get_data_select_place_apartment({
            object: '#asset_detail_place_id',
            url: "{{route('admin.ajax.ajax_get_building_place')}}",
            data_id: 'id',
            data_code: 'code',
            data_text: 'name',
            title_default: 'Chọn Tòa'
        });

        function get_data_select_place_apartment(options) {
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
                                text: item[options.data_code]+'-'+item[options.data_text]
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
        get_data_select_floor_apartment({
            object: '#asset_detail_floor_id',
            url: "{{route('admin.ajax.ajaxGetFloor')}}",
            data_id: 'id',
            data_text: 'name',
            title_default: 'Chọn Tầng'
        });

        function get_data_select_floor_apartment(options) {
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
        get_data_select_asset_area({
            object: '#asset_detail_office_id,#search_office_id',
            url: "{{route('admin.ajax.ajaxGetAssetArea')}}",
            data_id: 'id',
            data_text: 'name',
            title_default: 'Chọn Khu vực'
        });

        function get_data_select_asset_area(options) {
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
        get_data_select_department({
            object: '#asset_detail_department_id,#search_department_id',
            url: "{{route('admin.ajax.ajaxGetDepartment')}}",
            data_id: 'id',
            data_text: 'name',
            title_default: 'Chọn bộ phận'
        });

        function get_data_select_department(options) {
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
        get_data_select_asset({
            object: '#asset_detail_id',
            url: "{{route('admin.ajax.ajaxGetAsset')}}",
            data_id: 'id',
            data_text: 'name',
            title_default: 'Chọn tài sản'
        });

        function get_data_select_asset(options) {
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
