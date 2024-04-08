@extends('backend.layouts.master')

@section('content')
    <section class="content-header">
        <h1>
            Quản lý chung
            <small>Thêm tòa nhà thuộc công ty</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('admin.home') }}"><i class="fa fa-home"></i> Trang chủ</a></li>
            <li class="active">Thêm tòa nhà thuộc công ty</li>
        </ol>
    </section>
    <section class="content">
        <div class="box-body">
            <form data-action="{{ route('admin.company.storeStaff') }}" method="POST" class="form-horizontal" id="create_staff">
                {{ csrf_field() }}
                <div class="row">
                    <div class="col-md-12">
                        <!-- Horizontal Form -->
                        <div class="box box-info">
                            <div class="box-header with-border">
                                <h3 class="box-title">Thêm thông tin cho nhân viên</h3>
                                <hr>
                                <h5 class="box-title">Công ty: {{ $company->name }}</h5>
                            </div>
                            <!-- /.box-header -->
                            <!-- form start -->
                            <div class="box-body">
                                <div class="container">

                                    <div class="stepwizard col-xs-12">
                                        <div class="stepwizard-row setup-panel">
                                            <div class="stepwizard-step">
                                                <a href="#step-1" type="button" id="step1"
                                                   class="btn btn-primary btn-circle">1</a>
                                                <p>Step 1</p>
                                            </div>
                                            <div class="stepwizard-step">
                                                <a href="#step-2" type="button" id="step2"
                                                   class="btn btn-default btn-circle">2</a>
                                                <p>Step 2</p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row setup-content" id="step-1">
                                        <div class="col-xs-8 col-md-8">
                                            <div class="col-md-12">
                                                <h4>Kiểm tra email nhân viên đã tồn tại hay chưa</h4>
                                                <div class="form-group div_check_email">
                                                    <label class="control-label">Email kiểm tra</label>
                                                    <input maxlength="100" type="text" class="form-control"
                                                           name="check_email" placeholder="Enter Email"/>
                                                    <div class="message_zone"></div>
                                                </div>
                                                <button class="btn btn-primary nextBtn btn-sm pull-right"
                                                        type="button">Next
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row setup-content" id="step-2">
                                        <div class="col-xs-8">
                                            <div class="col-md-12">
                                                <h3> Tạo thông tin nhân viên</h3>
                                                <input type="hidden" name="bdc_company_id"
                                                       value="{{ @$company->id }}">
                                                <input type="hidden" name="is_new">
                                                <div class="form-group div_email">
                                                    <label class="col-md-3 control-label">Email đăng nhập <span
                                                                class="text-danger">*</span></label>
                                                    <div class="col-md-6">
                                                        <input type="text" class="form-control" name="email"
                                                               placeholder="Email đăng nhập"
                                                               value="{{  old('email') }}"/>
                                                        <div class="message_zone"></div>
                                                    </div>
                                                </div>

                                                <div class="form-group div_password"
                                                     id="password_new">
                                                    <label class="control-label col-md-3">Mật khẩu đăng nhập <span
                                                                class="text-danger">*</span></label>
                                                    <div class="col-md-6">
                                                        <input type="password" class="form-control" name="password"
                                                               placeholder="Mật khẩu đăng nhập"
                                                               value="{{  old('password') }}"/>
                                                        <div class="message_zone"></div>
                                                    </div>
                                                </div>
                                                <div class="form-group div_password_confirmation"
                                                     id="password_confirmation_new">
                                                    <label class="control-label col-md-3">Xác nhận mật khẩu đăng
                                                        nhập <span class="text-danger">*</span></label>
                                                    <div class="col-md-6">
                                                        <input type="password" class="form-control"
                                                               name="password_confirmation"
                                                               placeholder="Xác nhận mật khẩu đăng nhập "
                                                               value="{{  old('password') }}"/>
                                                        <div class="message_zone"></div>
                                                    </div>
                                                </div>
                                                <div class="form-group div_name">
                                                    <label class="col-md-3 control-label">Tên nhân viên (*)</label>
                                                    <div class="col-md-6">
                                                        <input type="text" class="form-control valid" id="name"
                                                               name="name"
                                                               value="{{ old('name') }}">
                                                        <div class="message_zone"></div>
                                                    </div>
                                                </div>
                                                <div class="form-group div_phone">
                                                    <label class="col-md-3 control-label">Số điện thoại</label>
                                                    <div class="col-md-6">
                                                        <input type="text" class="form-control valid" id="name"
                                                               name="phone"
                                                               value="{{ old('phone') }}">
                                                        <div class="message_zone"></div>
                                                    </div>
                                                </div>
                                                <div class="form-group div_code">
                                                    <label class="col-md-3 control-label">Code nhân viên (*)</label>
                                                    <div class="col-md-6">
                                                        <input type="text" class="form-control valid" id="name"
                                                               name="code"
                                                               value="{{ old('code') }}">
                                                        <div class="message_zone"></div>
                                                    </div>
                                                </div>
                                                <button class="btn btn-primary btn-sm pull-right" type="button"
                                                        id="add_staff">
                                                    Submit
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="box-footer">
                                <a href="/admin/company" type="button"
                                   class="btn btn-default pull-left">Quay lại</a>
                            </div>
                            <!-- /.box-footer -->
                        </div>
                        <!-- /.box -->
                    </div>
                </div>
            </form>
        </div>
    </section>
@endsection
@section('stylesheet')
    <style>
        body {
            margin-top: 40px;
        }

        .stepwizard-step p {
            margin-top: 10px;
        }

        .stepwizard-row {
            display: table-row;
        }

        .stepwizard {
            display: table;
            width: 60%;
            position: relative;
        }

        .stepwizard-step button[disabled] {
            opacity: 1 !important;
            filter: alpha(opacity=100) !important;
        }

        .stepwizard-row:before {
            top: 14px;
            bottom: 0;
            position: absolute;
            content: " ";
            width: 100%;
            height: 1px;
            background-color: #ccc;
            z-order: 0;
        }

        .stepwizard-step {
            display: table-cell;
            text-align: center;
            position: relative;
        }

        .btn-circle {
            width: 30px;
            height: 30px;
            text-align: center;
            padding: 6px 0;
            font-size: 12px;
            line-height: 1.428571429;
            border-radius: 15px;
        }
    </style>
@endsection
@section('javascript')
    <script>
        $(document).ready(function () {
            var navListItems = $('div.setup-panel div a'),
                allWells = $('.setup-content'),
                allNextBtn = $('.nextBtn, a#step2'),
                request = false;

            allWells.hide();

            navListItems.click(function (e) {
                e.preventDefault();
                var $target = $($(this).attr('href')),
                    $item = $(this);
                if ($(this).attr('href') == '#step-2') {
                    if (!$('input[name="check_email"]').is(':disabled')) {
                        $target = $('#step-1');
                        $item = $('#step1');
                    } else {
                        $target = $($(this).attr('href'));
                        $item = $(this);
                    }
                }

                if (!$item.hasClass('disabled')) {
                    navListItems.removeClass('btn-primary').addClass('btn-default');
                    $item.addClass('btn-primary');
                    allWells.hide();
                    $target.show();
                    $target.find('input:eq(0)').focus();
                }
            });

            allNextBtn.click(function (e) {
                e.preventDefault();
                if (!request) {
                    var curStep = $(this).closest(".setup-content"),
                        curStepBtn = curStep.attr("id"),
                        nextStepWizard = $('div.setup-panel div a[href="#' + curStepBtn + '"]').parent().next().children("a");

                    $.ajax({
                        url: '{{ route('admin.company.create_staff') }}',
                        type: 'post',
                        data: {
                            check_email: $('input[name="check_email"]').val(),
                            company_id: '{{ $company->id }}'
                        },
                        success: function (response) {
                            console.log(response);
                            if (response.is_new == false) {
                                $('#password_new').hide();
                                $('#password_confirmation_new').hide();
                            }
                            $('input[name="email"]').val(response.email);
                            $('input[name="email"]').attr('readonly', true);
                            $('input[name="is_new"]').val(response.is_new);
                            $('input[name="check_email"]').attr("disabled", "disabled");
                            $(document).find('.has-error').removeClass('has-error');
                            if ($(document).find('.help-block').length) {
                                $(document).find('.help-block').remove();
                            }
                            var $target = $('#step-2');
                            var $item = $('#step2');
                            if (!$item.hasClass('disabled')) {
                                navListItems.removeClass('btn-primary').addClass('btn-default');
                                $item.addClass('btn-primary');
                                allWells.hide();
                                $target.show();
                                $target.find('input:eq(0)').focus();
                            }
                        },
                        error: function (response) {
                            $(document).find('.has-error').removeClass('has-error');
                            if ($(document).find('.help-block').length) {
                                $(document).find('.help-block').remove();
                            }
                            showErrorsCreate(response.responseJSON.errors, '.div_', '.message_zone');
                            request = false;
                        }
                    });
                }
            });

            submitAjaxForm('#add_staff', '#create_staff', '.div_', '.message_zone');

            $('div.setup-panel div a.btn-primary').trigger('click');
        });
    </script>
@endsection