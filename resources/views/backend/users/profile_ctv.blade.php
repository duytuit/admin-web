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
                                <p style="padding-top: 7px;  margin-bottom: 0;">{{ $user->name }}</p>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">Mã nhân viên:</label>
                            <div class="col-sm-9">
                                <p style="padding-top: 7px;  margin-bottom: 0;">{{ $user->ub_staff_code ?: 'Chưa có mã nhân viên'}}</p>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">Email:</label>
                            <div class="col-sm-9">
                                <p style="padding-top: 7px;  margin-bottom: 0;">{{ $user->email }}</p>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">Số điện thoại:</label>
                            <div class="col-sm-9">
                                <p style="padding-top: 7px;  margin-bottom: 0;">{{ $user->phone }}</p>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">Tài khoản TVC:</label>
                            <div class="col-sm-9">
                                <p style="padding-top: 7px;  margin-bottom: 0;">{{ $user->username }}</p>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-3 control-label">QR Code</label>
                            <div class="col-sm-9 qr-code">
                                @php
                                $qr_data = [
                                    'inform' => 'Đang tra cứu thông tin nhân viên.',
                                    'navigation' => 'StaffSingle',
                                    'params' => [ 'id'=> $user->id ]
                                ];
                                @endphp

                                {!! QrCode::size(300)->generate(json_encode($qr_data)); !!}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="box-footer">
                    <a href="javascript:;" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#reset-password"><i class="fa fa-unlock-alt"></i> Đối mật khẩu</a>
                </div>
            </div>
        </div>

        <div class="col-xs-4">
            <div class="box box-primary">
                <div class="box-body">
                    <form action="" method="post" id="form-edit-add-customer" class="form-validate" autocomplete="off">
                        @csrf
                        <div class="form-group">
                            <label class="control-label">Avatar:</label> <br />
                            <div class="input-group input-image" data-file="image">
                                <input type="text" name="image" value="{{ old('image', $user->avatar) }}" class="form-control"><span class="input-group-btn"><button type="button" class="btn btn-primary">Chọn</button></span>
                            </div>
                            <img src="{{ $user->avatar ?: asset('adminLTE/img/user-default.png') }}" alt="" style="max-width: 200px;" />
                        </div>

                        <hr />
                        <button type="submit" class="btn btn-sm btn-success"><i class="fa fa-save"></i> Cập nhật Avatar</button>
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
                    <input type="hidden" name="user_id" value="{{$user->id}}">
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

function printErrorMsg(msg) {
    $(".print-error-msg").find("ul").html('');
    $(".print-error-msg").css('display', 'block');
    $.each(msg, function(key, value) {
        $(".print-error-msg").find("ul").append('<li>' + value + '</li>');
    });
}
</script>
<script>
    sidebar('campaign_assigns', 'view');
</script>
@endsection