@extends('backend.layouts.master')

@section('stylesheet')
    <link rel="stylesheet" href="/adminLTE/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-star-rating/4.0.2/css/star-rating.min.css" />
@endsection

@section('content')

    <section class="content-header">
        <h1>
            Thêm mới nhân viên
            <small>Cập nhật</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{ url('/admin') }}"><i class="fa fa-dashboard"></i> Trang chủ</a></li>
            <li class="active">Thêm mới nhân viên</li>
        </ol>
    </section>

    @can('view', app(App\Models\BoCustomer::class))
        <section class="content">
            <form action="" method="post" id="form-edit-add-user" class="form-validate" autocomplete="off">
                {{ csrf_field() }}
                <div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                        <div class="box no-border-top">
                            <div class="box-body no-padding">
                                <div class="col-sm-6">
                                    <div class="col-sm-12 col-xs-12 form-group {{ $errors->has('ub_title') ? 'has-error': '' }}">
                                        <label class="control-label">Tên nhân viên <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="ub_title" placeholder="Tên nhân viên" value="{{ $bo_user->ub_title ?? old('ub_title') ?? ''}}" />
                                        @if ($errors->has('ub_title'))
                                            <em class="help-block">{{ $errors->first('ub_title') }}</em>
                                        @endif
                                    </div>
                                    <div class="col-sm-12 col-xs-12 form-group {{ $errors->has('ub_email') ? 'has-error': '' }}">
                                        <label class="control-label">Email nhân viên</label>
                                        <input type="text" class="form-control" name="ub_email" placeholder="Email nhân viên" value="{{ $bo_user->ub_email ?? old('ub_email') ?? ''}}" />
                                        @if ($errors->has('ub_email'))
                                            <em class="help-block">{{ $errors->first('ub_email') }}</em>
                                        @endif
                                    </div>
                                    <div class="col-sm-12 col-xs-12 form-group {{ $errors->has('ub_phone') ? 'has-error': '' }}">
                                        <label class="control-label">Số điện thoại</label>
                                        <input type="text" class="form-control" name="ub_phone" placeholder="Số điện thoại" value="{{ $bo_user->ub_phone ?? old('ub_phone') ?? ''}}" />
                                        @if ($errors->has('ub_phone'))
                                            <em class="help-block">{{ $errors->first('ub_phone') }}</em>
                                        @endif
                                    </div>
                                    <div class="col-sm-12 col-xs-12 form-group {{ $errors->has('ub_staff_code') ? 'has-error': '' }}">
                                        <label class="control-label">Mã nhân viên <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="ub_staff_code" placeholder="Mã nhân viên" value="{{ $bo_user->ub_staff_code ?? old('ub_staff_code') ?? ''}}" />
                                        @if ($errors->has('ub_staff_code'))
                                            <em class="help-block">{{ $errors->first('ub_staff_code') }}</em>
                                        @endif
                                    </div>
                                    <div class="col-sm-12 col-xs-12 form-group {{ $errors->has('password') ? 'has-error': '' }}">
                                        <label class="control-label">Mật khẩu <span class="text-danger">*</span></label>
                                        <input type="password" class="form-control" name="password" placeholder="Mật khẩu" value="" />
                                        @if ($errors->has('password'))
                                            <em class="help-block">{{ $errors->first('password') }}</em>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="col-sm-12 col-xs-12 form-group {{ $errors->has('ub_account_tvc') ? 'has-error': '' }}">
                                        <label class="control-label">Tên đăng nhập(Tên tài khoản TVC) <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="ub_account_tvc" placeholder="Tên đăng nhập(Tên tài khoản TVC)" value="{{ $bo_user->ub_account_tvc ?? old('ub_account_tvc') ?? ''}}"/>
                                        @if ($errors->has('ub_account_tvc'))
                                            <em class="help-block">{{ $errors->first('ub_account_tvc') }}</em>
                                        @endif
                                    </div>
                                    <div class="col-sm-12 col-xs-12 form-group {{ $errors->has('group_ids') ? 'has-error': '' }}">
                                        <label class="control-label">Phòng ban <span class="text-danger">*</span></label>
                                        <select name="group_ids" class="form-control select2" style="width: 100%;">
                                            <option value="">Phòng ban</option>
                                            @foreach ($groups as $item)
                                                <option value="{{ $item->gb_id }}" {{ $item->gb_id == ($bo_user->group_ids?? old('group_ids')??'') ? 'selected' : '' }}>{{ $item->gb_title }}</option>
                                            @endforeach
                                        </select>
                                        @if ($errors->has('group_ids'))
                                            <em class="help-block">{{ $errors->first('group_ids') }}</em>
                                        @endif
                                    </div>
                                    <div class="col-sm-12 col-xs-12 form-group {{ $errors->has('ub_avatar') ? 'has-error': '' }}">
                                        <label class="control-label">Avatar</label>
                                        <div class="input-group input-image" data-file="image">
                                            <input type="text" name="ub_avatar" value="{{ old('ub_avatar', $bo_user->ub_avatar) }}" class="form-control"><span class="input-group-btn"><button type="button" class="btn btn-primary">Chọn</button></span>
                                        </div>
                                        @if ($errors->has('ub_avatar'))
                                            <em class="help-block">{{ $errors->first('ub_avatar') }}</em>
                                        @endif
                                    </div>
                                    <div class="col-sm-12 col-xs-12 form-group {{ $errors->has('ub_tvc_code') ? 'has-error': '' }}">
                                        <label class="control-label">Mã nhân viên TVC <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="ub_tvc_code" placeholder="Mã nhân viên TVC" value="{{ $bo_user->ub_tvc_code ?? old('ub_tvc_code') ?? ''}}" />
                                        @if ($errors->has('ub_tvc_code'))
                                            <em class="help-block">{{ $errors->first('ub_tvc_code') }}</em>
                                        @endif
                                    </div>
                                    <div class="col-sm-12 col-xs-12 form-group {{ $errors->has('confirm_password') ? 'has-error': '' }}">
                                        <label class="control-label">Nhập lại mật khẩu <span class="text-danger">*</span></label>
                                        <input type="password" class="form-control" name="confirm_password" placeholder="Nhập lại mật khẩu" value="" />
                                        @if ($errors->has('confirm_password'))
                                            <em class="help-block">{{ $errors->first('confirm_password') }}</em>
                                        @endif
                                    </div>
                                </div>
                                @can('update', app(App\Models\BoUser::class))
                                    <button type="submit" class="btn btn-sm btn-success" title="Cập nhật" form="form-edit-add-user">
                                        <i class="fa fa-save"></i>&nbsp;&nbsp;{{ $id ? 'Cập nhật' : 'Thêm mới'}}
                                    </button>
                                @endcan
                            </div>
                        </div>
                    </div>
                </div>
            </form>
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
            var import_file = $("[name='import_file'").val();

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
        sidebar('users', 'update');
    </script>
@endsection