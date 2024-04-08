@extends('layouts.app')
<link rel="stylesheet" href="{{ url('adminLTE/plugins/jquery-toastr/toastr.min.css') }}" />
@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
            <div class="login-logo" style="margin-bottom: 5px;background-color: #222222;text-align:center;">
                 <img src="/adminLTE/img/logo.png" alt="Building Care">
            </div>
                <div class="card-header" style="font-weight: bold;">{{ __('Thay Đổi Mật khẩu') }}</div>
                <div class="card-body">
                     
                    <div class="form-horizontal">
                         @csrf
                        <div class="form-group">
                            <label for="new-password" class="col-md-4 control-label">New Password</label>

                            <div class="col-md-6" style=" max-width: 100%;">
                                <input id="new-password" type="password" class="form-control" autocomplete="off" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="new-password-confirm" class="col-md-4 control-label">Confirm New Password</label>

                            <div class="col-md-6" style=" max-width: 100%;">
                                <input id="new-password-confirm" type="password" class="form-control" autocomplete="off"  required>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-md-6 col-md-offset-4">
                                <button type="submit" class="btn btn-primary change-pass">
                                    Change Password
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@include('backend.layouts.notification')
@endsection
<style>

</style>
<script type="text/javascript" src="/adminLTE/plugins/jquery/jquery.min.js"></script>
<script type="text/javascript" src="{{ url('adminLTE/plugins/jquery-toastr/toastr.min.js') }}"></script>
<script type="text/javascript" src="{{ url('adminLTE/plugins/jquery-toastr/ui-toastr-notifications.js') }}"></script>
<script>
var x = document.referrer;
if(x){
var res = x.split("=");
var account=res[res.length-2].split("&")[0];
var id=res[res.length-3].split("&")[0];
}
$(document).on('click', '.change-pass', function (e) {
    var newp = $("#new-password").val();
    var confinewp = $("#new-password-confirm").val();
    var _token = $('meta[name="csrf-token"]').attr('content');
    var url_base = window.location.origin;
            var value =  {
               '_token': _token,
               'new_password': newp,
               'new_password_confirmation': confinewp,
               'account':account,
               'id':id
            };
            $.ajax({
                url: "{{route('password.newpass.post')}}",
                method: 'POST',
                dataType: 'json',
                data: value,
                success: function (response) {
                    if (response.success == true) {
                         toastr.success(response.message);
                        if (!response.href) {
                            setTimeout(() => {
                                location.reload()
                            }, 2000)
                        } else {
                            setTimeout(() => {
                                window.location.href =url_base + response.href
                            }, 2000)
                        }
                    }else if (response.success == false) {
                         toastr.error(response.message);
                        if (!response.href) {
                            setTimeout(() => {
                                location.reload()
                            }, 2000)
                        } else {
                            setTimeout(() => {
                                window.location.href = response.href
                            }, 2000)
                        }
                    } else {
                         toastr.error('Có lỗi! Xin vui lòng thử lại');
                        setTimeout(() => {
                            location.reload()
                        }, 2000)
                    }
                },
            })
})

</script>