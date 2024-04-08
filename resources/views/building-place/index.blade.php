@extends('backend.layouts.master')
@section('content')
    <section class="content-header">
        <h1>
            Tòa nhà <a href="{{ route('admin.buildingplace.create') }}" class="btn btn-success"><i class="fa fa-plus"></i>&nbsp;&nbsp;Thêm mới</a>
        </h1>
        <ol class="breadcrumb">
                <li><a href="{{ route('admin.home') }}"><i class="fa fa-home"></i> Trang chủ</a></li>
            <li class="active">Quản lý tòa nhà</li>
        </ol>
    </section>
    <section class="content">
        <div class="box box-primary">
            <div class="box-body">
                <div class="box-header with-border">
                    <h3 class="box-title">Thông tin chung</h3>
                </div>
                <br>
                <div class="col-md-12">
                    <!-- Custom Tabs -->
                    <div class="nav-tabs-custom">
                        <ul class="nav nav-tabs">
                            <li class="active">
                                <a data-toggle="tab" href="#toa-nha">Quản lý tòa nhà</a
                            ></li>
                            <li><a data-toggle="tab" href="#tang">Quản lý tầng</a></li>
                        </ul>
                    </div>
                </div>
                <br>
                <div class="col-md-12">
                    <div class="tab-content">
                        <br>
                        @include('building-place.tab.place')
                        @include('building-place.tab.floor')
                    </div>
                </div>
            </div>
        </div>
    </section>

@endsection

@section('javascript')

    <script>
        $(function () {
            get_data_select_apartment({
                object: '#select-re_name',
                url: '{{ url('admin/apartments/ajax_get_resident') }}',
                data_id: 'id',
                data_text: 'display_name',
                title_default: 'Chọn chủ hộ'
            });
            get_data_select_email({
                object: '#select-email',
                url: '{{ url('admin/building-place/get-email') }}',
                data_id: 'id',
                data_text: 'email',
                title_default: 'Chọn Email'
            });
            function get_data_select_apartment(options) {
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
            function get_data_select_email(options) {
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
                                    id: item[options.data_text],
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
        sidebar('apartments', 'index');
    </script>


@endsection
