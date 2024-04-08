<!DOCTYPE html>
<html lang="en">

@include('backend.layouts.head')

<body class="hold-transition login-page">
    <div class="login-box">
        <div class="login-logo" style="margin-bottom: 5px;">
            @if (@$_COOKIE['filmore'] == 1)
                <img src="{{asset('adminLTE/img/logo-white.svg') }}">
            @else
                <img src="/adminLTE/img/logo.png" alt="Building Care">
            @endif
        </div>
        <!-- /.login-logo -->
        <div class="login-box-body">
            <h3 class="login-box-msg">Đăng nhập hệ thống</h3>
            @if(isset($errors))

                <div class="row">
                    <div class="col-xs-12">
                        @foreach ($errors->all() as $error)
                              <div class="text-danger">{{ $error }}</div>
                        @endforeach
                    </div>
                </div><br>
                @endif

            <form id="form_login" action="{{ route('admin.auth.login') }}" method="post">
                @csrf
                <div class="form-group has-feedback">
                    <input name="email" type="text" class="form-control" placeholder="Tài khoản" value="{{old('email')}}">
                    <span class="glyphicon glyphicon-envelope form-control-feedback"></span>
                </div>
                <div class="form-group has-feedback">
                    <input name="password" type="password" class="form-control" placeholder="Mật khẩu">
                    <span class="glyphicon glyphicon-lock form-control-feedback"></span>
                </div>
                <div class="row">
                    <div class="col-xs-7">
                        <div class="checkbox icheck">
                            <label>
                                <input name="remember" type="checkbox" class="iCheck" {{ old('remember') ? 'checked' : '' }}> Nhớ tài khoản
                            </label>
                        </div>

                        @if (Route::has('password.verify'))
                        <a class="btn btn-link" href="{{ route('password.verify') }}">
                            {{ __('Forgot Your Password?') }}
                        </a>
                        @endif
                    </div>
                    <!-- /.col -->
                    <div class="col-xs-5">
                        <button type="submit" class="btn btn-primary btn-block btn-flat">Đăng nhập</button>
                    </div>
                    <!-- /.col -->
                </div>

            </form>

            <div class="text-center" style="padding: 15px;">
                <p class="support">----- Hỗ trợ -----</p>
{{--                <div class="form-group"><a class="btn btn-primary" onclick="loginTask(this)" data-user_name="tbql@s-tech.info" >Trưởng Bql(tbql@s-tech.info)</a></div>--}}
{{--                <div class="form-group"><a class="btn btn-primary" onclick="loginTask(this)" data-user_name="tbpkt@s-tech.info" >Trưởng BP Kỹ thuật(tbpkt@s-tech.info)</a></div>--}}
{{--                <div class="form-group"><a class="btn btn-primary" onclick="loginTask(this)" data-user_name="gscv1@s-tech.info" >Giám sát(gscv1@s-tech.info)</a></div>--}}
{{--                <div class="form-group"><a class="btn btn-primary" onclick="loginTask(this)" data-user_name="nvqlcv3@s-tech.info" >Lễ tân(nvqlcv3@s-tech.info)</a></div>--}}
{{--                <div class="form-group"><a class="btn btn-primary" onclick="loginTask(this)" data-user_name="nvqlcv1@s-tech.info" >Nhân viên1(nvqlcv1@s-tech.info)</a></div>--}}
{{--                <div class="form-group"><a class="btn btn-primary" onclick="loginTask(this)" data-user_name="nvqlcv2@s-tech.info" >Nhân viên2(nvqlcv2@s-tech.info)</a></div>--}}
                <a class="btn btn-support btn-facebook" href="#" target="_blank"><i class="fa fa-facebook"> </i></a>
                &nbsp;
                <a class="btn btn-support btn-email" href="#"><i class="fa fa-envelope"></i></a>
                &nbsp;
                <a class="btn btn-support btn-phone" href="#"><i class="fa fa-phone"></i></a>
            </div>

        </div>
        <!-- /.login-box-body -->
    </div>
    <!-- /.login-box -->

    <script type="text/javascript" src="/adminLTE/plugins/jquery/jquery.min.js"></script>
    <script type="text/javascript" src="/adminLTE/plugins/bootstrap/js/bootstrap.min.js"></script>
    <script type="text/javascript" src="/adminLTE/plugins/iCheck/icheck.js"></script>
    <script type="text/javascript" src="{{ url('adminLTE/plugins/jquery-toastr/toastr.min.js') }}"></script>
    <script type="text/javascript" src="{{ url('adminLTE/plugins/jquery-toastr/ui-toastr-notifications.js') }}"></script>
    <script>
    $('input.iCheck').iCheck({
        checkboxClass: 'icheckbox_square-green',
        radioClass: 'iradio_square-green',
        increaseArea: '20%' // optional
    });
    function loginTask(event){
        user_name = $(event).data('user_name');
        $('#form_login input[name="email"]').val(user_name)
        $('#form_login input[name="password"]').val(123456)

        $('#form_login').submit()
    }
    </script>
</body>

</html>