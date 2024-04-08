@inject('request', 'Illuminate\Http\Request')
@extends('backend.layouts.master')
@section('content')
    <style>
        .image-upload-item {
            width: 50px;
            height: 50px;
            position: relative;
            margin: 5px;
            border: 1px solid;
            display: flex;
            justify-content: flex-end;
            align-items: flex-start;
        }

        #dvPreview {
            display: flex;
            flex-wrap: wrap;
        }

        #dvPreviewEdit {
            display: flex;
            flex-wrap: wrap;
        }

        .image-upload-item i {
            color: #000;
            position: relative;
            top: 0;
            right: 0;
            font-size: 11px;
            z-index: 100;
            cursor: pointer;
        }

        .image-upload-item img {
            height: 100%;
            width: 100%;
            position: absolute;
            top: 0;
            left: 0;
        }

        #fileupload {
            color: #ffffff;
        }

        #fileuploadEdit {
            color: #ffffff;
        }

    </style>
    <section class="content-header">
        <h1>
            {{$meta_title}}
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('admin.home') }}"><i class="fa fa-home"></i> Trang chủ</a></li>
            <li class="active">Quản lý tài sản</li>
        </ol>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="clearfix"></div>
                <ul class="nav nav-tabs" role="tablist">
                    <li class="active">
                        <a href="#asset" role="tab" data-toggle="tab">Tài sản</a>
                    </li>
                    <li>
                        <a href="#asset-details" role="tab" data-toggle="tab">Chi tiết tài sản</a>
                    </li>
                    <li>
                        <a href="#asset_category" role="tab" data-toggle="tab">Danh mục TS</a>
                    </li>
                    <li>
                        <a href="#asset_area" role="tab" data-toggle="tab">Khu vực TS</a>
                    </li>
                </ul>
                <div class="tab-content">
                    @include('v3.assets.tabs.asset')
                    @include('v3.assets.tabs.asset_detail')
                    @include('v3.assets.tabs.asset_category')
                    @include('v3.assets.tabs.asset_area')
                </div>
            </div>
        </div>
    </section>
@endsection
<!-- jQuery 3 -->
<script type="text/javascript" src="{{ url('adminLTE/plugins/jquery-3.5.1/jquery-3.5.1.min.js') }}"></script>
<script type="text/javascript" src="{{ url('adminLTE/plugins/jquery-ui/jquery-ui.min.js') }}"></script>
<script type="text/javascript" src="{{ url('adminLTE/js/pagination.min.js') }}"></script>
<script type="text/javascript" src="{{ url('adminLTE/plugins/select2/js/select2.full.min.js') }}"></script>
<script>
    async function postMethod(url, param, file) {
        let method = 'post';
        if (file == false) {
            let _result = await call_api(method, url, param);
            toastr.success(_result.mess);
        } else {
            let _result = await call_api_form_data(method, url, param);
            toastr.success(_result.mess);
        }
        setTimeout(function () {
            location.reload();
        }, 1000);
    }

    async function postDel(url, id) {
        if (confirm("Bạn có chắc chắn muốn xóa không?")) {
            let method = 'post';
            let param_query_old = "{{ $array_search }}";
            let param_query = param_query_old.replaceAll("&amp;", "&")
            param_query += "&id=" + id;
            console.log(param_query);
            let _result = await call_api(method, url + param_query);
            toastr.success(_result.mess);
            setTimeout(function () {
                location.reload();
            }, 1000);
        } else {
            return false;
        }
    }

    $(document).ready(function () {
        $('#form-add-asset-area #asset_detail_place_id').change(function (e) {
            e.preventDefault();
            if ($(this).val()) {
                $('#asset_detail_floor_id').val('').change();
                $('#form-add-asset-area .floor_asset_detail .select2-selection__rendered').text('');
            }
        });

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
                    data: function (params) {
                        var query = {
                            search: params.term,
                        }
                        return query;
                    },
                    processResults: function (json, params) {
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
                    data: function (params) {
                        var query = {
                            search: params.term,
                        }
                        return query;
                    },
                    processResults: function (json, params) {
                        var results = [{
                            id: '',
                            text: options.title_default
                        }];

                        for (i in json.data) {
                            var item = json.data[i];
                            results.push({
                                id: item[options.data_id],
                                text: item[options.data_code] + '-' + item[options.data_text]
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
                    data: function (params) {
                        var query = {
                            search: params.term,
                            place_id: $('#asset_detail_place_id').val()
                        }
                        return query;
                    },
                    processResults: function (json, params) {
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
                    data: function (params) {
                        var query = {
                            search: params.term,
                        }
                        return query;
                    },
                    processResults: function (json, params) {
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
                    data: function (params) {
                        var query = {
                            search: params.term,
                        }
                        return query;
                    },
                    processResults: function (json, params) {
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
                    data: function (params) {
                        var query = {
                            search: params.term,
                        }
                        return query;
                    },
                    processResults: function (json, params) {
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
