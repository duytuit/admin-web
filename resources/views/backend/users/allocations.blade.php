@extends('backend.layouts.master')

@section('stylesheet')
    <link rel="stylesheet" href="/adminLTE/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-star-rating/4.0.2/css/star-rating.min.css" />
@endsection

@section('content')

    <section class="content-header">
        <h1>
            Thêm mới cộng tác viên
            <small>Cập nhật</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{ url('/admin') }}"><i class="fa fa-dashboard"></i> Trang chủ</a></li>
            <li class="active">Thêm mới nhân viên</li>
        </ol>
    </section>

    @can('view', app(App\Models\BoCustomer::class))
        <section class="content">
            <div class="row">
                <div class="col-sm-12">
                    <div class="col-sm-6 col-xs-12 form-group">
                        <label class="control-label">Cộng tác viên phân bổ</label>
                        <select name="user_id" class="form-control select2" id="ip_user_id" style="width: 100%;">
                            <option value="">Cộng tác viên</option>
                            @foreach ($users_ctv as $item)
                                <option value="{{ $item->ub_id }}" >{{ $item->ub_title }}({{ $item->ub_account_tvc }})</option>
                            @endforeach
                        </select>
                        <em class="help-block-ctv"></em>
                    </div>
                </div>
                <div class="clear-fix"></div>
                <div class="col-sm-6">
                    <div class="list_customer_allocation table-responsive"></div>
                </div>
                <div class="col-sm-6">
                    <div class="list_ctv_allocation table-responsive"></div>
                </div>
            </div>
        </section>
    @endcan
@endsection

@section('javascript')
    <script src="/adminLTE/plugins/moment/moment.min.js"></script>
    <script src="/adminLTE/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-star-rating/4.0.2/js/star-rating.min.js"></script>

    <script src="/adminLTE/plugins/input-mask/jquery.inputmask.js"></script>
    <script src="/adminLTE/plugins/input-mask/jquery.inputmask.date.extensions.js"></script>
    <script src="/adminLTE/plugins/input-mask/jquery.inputmask.extensions.js"></script>

    <script>
        $(function() {
            // Chọn tỉnh/ thành phố
            get_data_select2({
                object: '#select-city',
                url: '{{ url("/admin/cities/ajax-get-city") }}',
                data_id: 'code',
                data_text: 'name',
                title_default: 'Chọn tỉnh/thành phố'
            });

            // Chọn đơn vị
            get_data_select2({
                object: '#select-branch',
                url: '{{ url("/admin/bo-customers/ajax/get-all-branches") }}',
                data_id: 'id',
                data_text: 'title',
                title_default: 'Chọn đơn vị'
            });

            // Chọn dự án cho khách hàng
            get_data_select2({
                object: '#select-bo-project',
                url: '{{ route("admin.campaigns.project") }}',
                data_id: 'id',
                data_text: 'title',
                title_default: 'Chọn dự án'
            });

            // Chọn dự án khi tải file
            get_data_select2({
                object: '#select-project-file',
                url: '{{ url("/admin/bo-customers/ajax/get-all-project") }}',
                data_id: 'cb_id',
                data_text: 'cb_title',
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

            // Chọn nhân viên
            get_data_select_user({
                object: '#select-bo-user',
                data_id: 'ub_id',
                data_text1: 'ub_title',
                data_text2: 'gb_title',
                title_default: 'Chọn nhân viên'
            });

            get_data_select_user({
                object: '#select-user',
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

            //Chọn quận huyện
            $('#select-district').select2({
                ajax: {
                    url: '{{ url("/admin/branches/ajax/address") }}',
                    dataType: 'json',
                    data: function(params) {
                        var city = $('#select-city').val();
                        var query = {
                            search: params.term,
                            city: city
                        }
                        return query;
                    },
                    processResults: function(data, params) {
                        var results = [];

                        for (i in data) {
                            var item = data[i];
                            results.push({
                                id: item.code,
                                text: item.name
                            });
                        }
                        return {
                            results: results
                        };
                    },
                }
            });

        });

        // Ratting
        $("input.rating").rating();

        // Validation
        $(".btn-js-action").click(function(e) {
            e.preventDefault();

            var _token = $("[name='_token']").val();
            var customer_id = $("[name='diary[cd_customer_id]']").val();
            var project_id = $("[name='diary[project_id]']").val();
            var status = $("[name='diary[status]']").val();
            var cd_rating = $("[name='diary[cd_rating]']").val();
            var cd_description = $("[name='diary[cd_description]']").val();

            $.ajax({
                url: "{{ url('/admin/bo-customers/validator-add-diary') }}",
                type: 'POST',
                data: {
                    _token: _token,
                    cd_customer_id: customer_id,
                    project_id: project_id,
                    status: status,
                    cd_rating: cd_rating,
                    cd_description: cd_description,
                },
                success: function(data) {
                    if ($.isEmptyObject(data.error_branches)) {
                        var hash = location.hash;
                        $('input[name="hashtag"]').val(hash);
                        $('#form-add-diary').submit();
                    } else {
                        printErrorMsg(data.error_branches);
                    }
                }
            });
        });

        $(".btn-upload-file").click(function(e) {
            e.preventDefault();

            var _token = $("[name='_token']").val();
            var cb_staff_id = $("#select-user").val();
            var cb_source = $("#cb-source").val();
            var project_id = $("#select-project-file").val();
            var import_file = $("[name='import_file']").val();

            $.ajax({
                url: "{{ url('/admin/bo-customers/validator-upload') }}",
                type: 'POST',
                data: {
                    _token: _token,
                    cb_staff_id: cb_staff_id,
                    project_id: project_id,
                    cb_source: cb_source,
                    import_file: import_file,
                },
                success: function(data) {
                    if ($.isEmptyObject(data.error_upload)) {
                        $('#upload-file-customer').submit();
                    } else {
                        printErrorMsg(data.error_upload);
                    }
                }
            });
        });

        function printErrorMsg(msg) {
            $(".print-error-msg").find("ul").html('');
            $(".print-error-msg").css('display', 'block');
            $.each(msg, function(key, value) {
                $(".print-error-msg").find("ul").append('<li>' + value + '</li>');
            });
        }

        $('.js-btn-add-edit-diary').click(function() {
            $(".print-error-msg").find("ul").html('');
            $(".print-error-msg").css('display', 'none');

            var diary_id = $(this).data('diary');
            var customer_id = $(this).data('customer');
            $.get('{{ url("/admin/bo-customers/ajax-edit-diary") }}', {
                diary_id: diary_id,
                customer_id: customer_id
            }, function(data) {
                $('#edit-add-diary .modal-body').html(data);
            });
        });
        $('[data-mask]').inputmask();
        $.get('{{ url("/admin/users/ajax-show-campassign") }}', {
            user_id : $('#ip_user_id').val(),
        }, function(data) {
            $('.list_customer_allocation').html(data);
        });
        $('#ip_user_id').on('change',function () {
            var _this = $(this);
            show_campassign(_this.val());
            show_user_campassign(_this.val());
        });
        function show_campassign(i){
            $.get('{{ url("/admin/users/ajax-show-campassign") }}', {
                user_id: i,
            }, function(data) {
                $('.list_customer_allocation').html(data);
            });
        }
        function show_user_campassign(i){
            $.get('{{ url("/admin/users/ajax-show-user-campassign") }}', {
                user_id: i,
            }, function(data) {
                $('.list_ctv_allocation').html(data);
            });
        }
        sidebar('campaign_assigns', 'alloctions');
    </script>
@endsection