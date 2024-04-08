@extends('backend.layouts.master')

@section('content')

<section class="content-header">
    <h1>
        Nhân viên
        <small>Thông tin</small>
    </h1>
    <ol class="breadcrumb">
        <li><a href="{{ url('/admin') }}"><i class="fa fa-dashboard"></i> Trang chủ</a></li>
        <li class="active">Nhân viên</li>
    </ol>
</section>

<section class="content">
    <div class="row">
        <div class="col-sm-8 col-xs-8">
            <div class="box box-primary">
                <div class="box-body">
                    <div class="form-horizontal">
                        <div class="form-group">
                            <label class="col-sm-3 control-label">Họ tên:</label>
                            <div class="col-sm-9">
                                <p style="padding-top: 7px;  margin-bottom: 0;">{{ @$user->BDCprofile->name }}</p>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">Mã nhân viên:</label>
                            <div class="col-sm-9">
                                <p style="padding-top: 7px;  margin-bottom: 0;">{{ @$user->BDCprofile->staff_code ?: 'Chưa có mã nhân viên'}}</p>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">Email:</label>
                            <div class="col-sm-9">
                                <p style="padding-top: 7px;  margin-bottom: 0;">{{ @$user->email }}</p>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">Số điện thoại:</label>
                            <div class="col-sm-9">
                                <p style="padding-top: 7px;  margin-bottom: 0;">{{ @$user->BDCprofile->phone }}</p>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">Phòng ban:</label>
                            <div class="col-sm-9">
                                <p style="padding-top: 7px;  margin-bottom: 0;">{{ @$user->departmentUser->department->name }}</p>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">Chức vụ:</label>
                            <div class="col-sm-9">
                                <p style="padding-top: 7px;  margin-bottom: 0;">{{ @$user->departmentUser->type == 1 ? 'Trưởng phòng' : 'Nhân viên'  }}</p>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">Tài khoản TVC:</label>
                            <div class="col-sm-9">
                                <p style="padding-top: 7px;  margin-bottom: 0;">{{ @$user->username }}</p>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-3 control-label">QR Code</label>
                            <div class="col-sm-9 qr-code">
                                @php
                                $qr_data = [
                                    'inform' => 'Đang tra cứu thông tin nhân viên.',
                                    'navigation' => 'StaffSingle',
                                    'params' => [ 'id'=> @$user->id ]
                                ];
                                @endphp

                                {!! QrCode::size(300)->generate(json_encode($qr_data)); !!}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="box-footer">
                    <a href="javascript:;" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#reset-password"><i class="fa fa-unlock-alt"></i> Đổi mật khẩu</a>
                    <a href="javascript:;" class="btn btn-primary btn-sm pull-right" data-toggle="modal" data-target="#update-profile"><i class="fa fa-edit"></i> Cập nhật profile</a>
                </div>
            </div>
        </div>
        <div class="col-xs-4">
            <div class="box box-primary">
                <div class="box-body">
                    <form action="{{ action("Backend\UserController@upload_avatar") }}" method="post" id="form-edit-add-customer" class="form-validate" autocomplete="off">
                        @csrf
                        <div class="input-group">
                            <input type="text" name="image" id="upload_file_image_input" value="{{ old('image', @$user->BDCprofile->avatar) }}" class="form-control"><span class="input-group-btn"> <label class="btn btn-primary"  for="uploadBtnImage">Chọn ảnh
                                <input id="uploadBtnImage" type="file" accept="image/*"  class="upload_file_image" style="display: none;"/>
                             </label></span>
                        </div>
                        <img src="{{ @$user->BDCprofile->avatar ?: asset('adminLTE/img/user-default.png') }}" alt="" style="max-width: 200px;" />
                        <hr />
                        <button type="submit" class="btn btn-sm btn-success"><i class="fa fa-save"></i> Cập nhật Avatar </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Modal đổi mật khẩu-->
<div id="reset-password" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Đổi mật khẩu</h4>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger print-error-msg" style="display:none">
                    <ul></ul>
                </div>

                <form action="{{ route('admin.users.update-pass') }}" method="post" id="form-resset-pass" class="form-validate form-horizontal" autocomplete="off">
                    {{ csrf_field() }}
                    <input type="hidden" name="user_id" value="{{@$user->id}}">
                    <input type="hidden" name="account" value="{{@$user->id}}">
                    <div class="form-group">
                        <label class="col-sm-4 control-label">Mật khẩu mới:</label>
                        <div class="col-sm-8">
                            <input type="password" name="password" class="form-control" required placeholder="Mật khẩu mới">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-4 control-label">Xác nhận mật khẩu:</label>
                        <div class="col-sm-8">
                            <input type="password" name="password_confirm" class="form-control" required placeholder="Xác nhận mật khẩu">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger pull-right" data-dismiss="modal"><i class="fa fa-close"></i>&nbsp;&nbsp;Hủy</button>
                <button type="submit" class="btn btn-primary btn-js-reset-pass" style="margin-right: 5px;"><i class="fa fa-save"></i>&nbsp;&nbsp;Xác nhận</button>
            </div>
        </div>

    </div>
</div>

<!-- Modal update profile-->
<div id="update-profile" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Cập nhật profile</h4>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger print-error-msg" style="display:none">
                    <ul></ul>
                </div>

                <form action="{{ route('admin.users.processProfile') }}" method="post" id="form-update-profile" class="form-validate form-horizontal" autocomplete="off">
                    {{ csrf_field() }}
                    <div class="form-group">
                        <label class="col-sm-4 control-label">Họ tên:</label>
                        <div class="col-sm-8">
                            <input type="text" name="display_name" class="form-control" required value="{{ @$user->BDCprofile->display_name }}">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-4 control-label">Mã nhân viên:</label>
                        <div class="col-sm-8">
                            <input type="text" name="staff_code" class="form-control" required value="{{ @$user->BDCprofile->staff_code }}">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-4 control-label">Email:</label>
                        <div class="col-sm-8">
                            <input type="text" name="email" class="form-control" required value="{{ @$user->email }}">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-4 control-label">Số điện thoại:</label>
                        <div class="col-sm-8">
                            <input type="text" name="phone" class="form-control" value="{{ @$user->BDCprofile->phone }}">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger pull-right" data-dismiss="modal"><i class="fa fa-close"></i>&nbsp;&nbsp;Hủy</button>
                <button type="submit" class="btn btn-primary btn-js-change-profile" style="margin-right: 5px;"><i class="fa fa-save"></i>&nbsp;&nbsp;Xác nhận</button>
            </div>
        </div>

    </div>
</div>
@endsection

@section('javascript')
<script src="/adminLTE/plugins/moment/moment.min.js"></script>
<script>
    $(".btn-js-reset-pass").click(function(e) {
      e.preventDefault();

      var _token = $("[name='_token']").val();
      var password = $('input[name="password"]').val();
      var password_confirm = $('input[name="password_confirm"]').val();
      console.log(password, password_confirm)
      $.ajax({
        url: "{{ route('admin.users.validator') }}",
        type: 'POST',
        data: {
          _token: _token,
          password: password,
          password_confirm: password_confirm,
        },
        success: function(data) {
          if ($.isEmptyObject(data.error_resset)) {
            $('#form-resset-pass').submit();
          } else {
            printErrorMsg(data.error_resset);
          }
        }
      });
    });

    $(".btn-js-change-profile").click(function(e) {
      e.preventDefault();

      let $input = $('#form-update-profile').serialize();
      $.ajax({
        url: "{{ route('admin.users.processProfile') }}",
        type: 'POST',
        data: $input,
        success: function(data) {
             toastr.success(data.message);
             setTimeout(() => {
                location.reload()
             }, 2000)
        },
        error: function(jqXHR, textStatus, errorThrown) {
          let errors = jqXHR.responseJSON.errors;
          printErrorMsg(errors);
        }
      });
    });
    $('.upload_file_image').on('change', function(e) {
            if (e.target.files[0]) {
                let formData = new FormData();
                formData.append('file',e.target.files[0]);
                formData.append('folder',"{{auth()->user() ? auth()->user()->id : null}}");
                $.ajax({
                        url: "{{route('api.v1.upload.upload_v2')}}",
                        type: 'POST',
                        data: formData,
                        contentType: false, //tell jquery to avoid some checks
                        processData: false,
                        success: function (response) {
                            console.log(response);
                            if (response.success == true) {
                                $('#upload_file_image_input').val(response.location);
                                toastr.success(response.msg);

                            } else {
                                toastr.error('thất bại');
                            }
                        },
                        error: function(response) {
                            toastr.error('đã có lỗi xảy ra.');
                        }
                });
                
            }
        
        });
function printErrorMsg(msg) {
    $(".print-error-msg").find("ul").html('');
    $(".print-error-msg").css('display', 'block');
    $.each(msg, function(key, value) {
        $(".print-error-msg").find("ul").append('<li>' + value + '</li>');
    });
}
</script>
<script>
    sidebar('users', 'users');
</script>
@endsection
