@extends('layouts.app')
<link rel="stylesheet" href="{{ url('adminLTE/plugins/jquery-toastr/toastr.min.css') }}" />
@section('content')
<div class="container">
  <div class="row justify-content-center">
                            <div class="col-md-8">
                                <div class="card">
                                @if (@$_COOKIE['filmore'] == 1)
                                    <div class="login-logo" style="margin-bottom: 5px;background-color: #222222;text-align:center;padding: 25px 0;">
                                      <img src="{{asset('adminLTE/img/logo-white.svg') }}">
                                   </div>
                                @else
                                  <div class="login-logo" style="margin-bottom: 5px;background-color: #222222;text-align:center;">
                                     <img src="/adminLTE/img/logo.png" alt="Building Care">
                                  </div>
                                @endif
                                    <div class="card-header" style="font-weight: bold;">{{ __('Xác thực OTP') }}</div>

                                    <div class="card-body">
                                        <form action="{{ route('password.resetpass.post') }}" method="post">
                                            @csrf
                                            <div class="form-row">
                                                <div class="form-group col-md-8">
                                                    <div class="form-group has-feedback">
                                                        <input name="mobile" type="text" class="form-control" placeholder="Nhập Email hoặc số điện thoại" value="{{$account ?? ''}}">
                                                        <span class="glyphicon glyphicon-envelope form-control-feedback"></span>
                                                    </div>
                                                </div>
                                                <div class="form-group col-md-4">
                                                    <div class="form-group has-feedback">
                                                        <button type="submit" class="btn btn-primary btn-block btn-flat" id="count-down">Nhận OTP</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </form>
                                        @if(isset($hascode))
                                        <input type="hidden" id="custId" name="custId" value="{{$hascode}}">
                                        <input type="hidden" id="time-out" name="time-out" value="{{$timer}}">
                                          <div id="count-timer-text" style="text-align:center;font-weight: bold;"></div>
                                          <div id="count-timer" style="text-align:center;font-size:25px"></div>
                                        <div id="press-otp" class="form-group has-feedback">
                                                <div id="wrapper">
                                                    <div style="font-style: italic;">
                                                    <h4>Vui lòng nhập mã xác minh 6 chữ số mà chúng tôi đã gửi qua Email/SMS:</h4>
                                                    <h4>(chúng tôi muốn chắc chắn rằng đó là bạn trước khi bạn thay đổi mật khẩu)</h4>
                                                    </div>
                                                    <div id="form">
                                                        <input id="otpverify5" class="verify_code" type="text" maxLength="1" size="1" min="0" max="9" pattern="[0-9]{1}" />
                                                        <input id="otpverify4" class="verify_code" type="text" maxLength="1" size="1" min="0" max="9" pattern="[0-9]{1}" />
                                                        <input id="otpverify3" class="verify_code" type="text" maxLength="1" size="1" min="0" max="9" pattern="[0-9]{1}" />
                                                        <input id="otpverify2" class="verify_code" type="text" maxLength="1" size="1" min="0" max="9" pattern="[0-9]{1}" />
                                                        <input id="otpverify1" class="verify_code" type="text" maxLength="1" size="1" min="0" max="9" pattern="[0-9]{1}" />
                                                        <input id="otpverify" class="verify_code" type="text" maxLength="1" size="1" min="0" max="9" pattern="[0-9]{1}" />
                                                    </div>
                                                </div>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>                         
</div>
@include('backend.layouts.notification')
@endsection
<style>
#wrapper {
  font-family: Lato;
  font-size: 1.5rem;
  text-align: center;
  box-sizing: border-box;
  color: #333;
}
#wrapper #dialog {
  border: solid 1px #ccc;
  margin: 10px auto;
  padding: 20px 30px;
  display: inline-block;
  box-shadow: 0 0 4px #ccc;
  background-color: #FAF8F8;
  overflow: hidden;
  position: relative;
  max-width: 450px;
}
#wrapper #dialog h3 {
  margin: 0 0 10px;
  padding: 0;
  line-height: 1.25;
}
#wrapper #dialog span {
  font-size: 90%;
}
#wrapper #dialog #form {
  max-width: 240px;
  margin: 25px auto 0;
}
#wrapper #dialog #form input {
  margin: 0 5px;
  text-align: center;
  line-height: 80px;
  font-size: 50px;
  border: solid 1px #ccc;
  box-shadow: 0 0 5px #ccc inset;
  outline: none;
  width: 20%;
  transition: all 0.2s ease-in-out;
  border-radius: 3px;
}
#wrapper #dialog #form input:focus {
  border-color: purple;
  box-shadow: 0 0 5px purple inset;
}
#wrapper #dialog #form input::selection {
  background: transparent;
}
#wrapper #dialog #form button {
  margin: 30px 0 50px;
  width: 100%;
  padding: 6px;
  background-color: #B85FC6;
  border: none;
  text-transform: uppercase;
}
#wrapper #dialog button.close {
  border: solid 2px;
  border-radius: 30px;
  line-height: 19px;
  font-size: 120%;
  width: 22px;
  position: absolute;
  right: 5px;
  top: 5px;
}
#wrapper #dialog div {
  position: relative;
  z-index: 1;
}
#wrapper #dialog img {
  position: absolute;
  bottom: -70px;
  right: -63px;
}
.verify_code { 
    text-align: center; 
}
</style>
<script type="text/javascript" src="/adminLTE/plugins/jquery/jquery.min.js"></script>
<script type="text/javascript" src="{{ url('adminLTE/plugins/jquery-toastr/toastr.min.js') }}"></script>
<script type="text/javascript" src="{{ url('adminLTE/plugins/jquery-toastr/ui-toastr-notifications.js') }}"></script>
<script>
  $(document).ready(function() {
   if($('#time-out').val()){
     var gettimer=$('#time-out').val();
     var counterId = setInterval(function(){
                        countUp();
                      }, 1000);
     function countUp () {
         
          if(gettimer > 0){
            gettimer--;
            document.getElementById("count-timer-text").innerText = "Thời gian xác thực còn lại";
            document.getElementById("count-timer").innerText = gettimer;
          }
         
      }
   }
  })
$(function() {
    var body = $('body');
    function goToNextInput(e) {
        var idname=event.target.id;
        var key = e.which,
        t = $(e.target),

        sib = t.next('.verify_code');
        
        if (idname == 'otpverify') {
          var code1 = $("#otpverify5").val();
          var code2 = $("#otpverify4").val();
          var code3 = $("#otpverify3").val();
          var code4 = $("#otpverify2").val();
          var code5 = $("#otpverify1").val();
          var code6 = $("#otpverify").val();
          if(code1 && code2 && code3 && code4 && code5 && code6){
            var verifycode = code1 + code2 + code3 + code4 +code5 + code6
            var _token = $('meta[name="csrf-token"]').attr('content');
            var idUser = $('#custId').val();
            var value =  {
               '_token': _token,
               'verifycode': verifycode,
               'iduser': idUser,
               'account':"{{$account}}"
            };
            $.ajax({
                url: "{{route('password.checkotp')}}",
                method: 'POST',
                dataType: 'json',
                data: value,
                success: function (response) {
                    if (response.success == true) {
                         toastr.success(response.message);
                        if (!response.href) {
                            setTimeout(() => {
                                location.reload()
                            }, 1000)
                        } else {
                            setTimeout(() => {
                                window.location.href = response.href
                            }, 1000)
                        }
                    }else if (response.success == false) {
                         toastr.error(response.message);
                        if (response.href) {
                           setTimeout(() => {
                                window.location.href = response.href
                            }, 1000)
                        }
                    } else {
                         toastr.error('Có lỗi! Xin vui lòng thử lại');
                        setTimeout(() => {
                            location.reload()
                        }, 1000)
                    }
                },
            })

          }
          e.preventDefault();
          return false;
        }

        if (key != 9 && (key < 48 || key > 57) && (key < 96 || key > 105)) {
        e.preventDefault();
        return false;
        }

        if (key === 9) {
        return true;
        }

        if (!sib || !sib.length) {
        sib = body.find('.verify_code').eq(0);
        }
        sib.select().focus();
    }

    function onKeyDown(e) {
        var key = e.which;

        if (key === 9 || (key >= 48 && key <= 57) || (key >= 96 && key <= 105)) {
        return true;
        }

        e.preventDefault();
        return false;
    }
    
    function onFocus(e) {
        $(e.target).select();
    }

    body.on('keyup', '.verify_code', goToNextInput);
    body.on('keydown', '.verify_code', onKeyDown);
    body.on('click', '.verify_code', onFocus);
    })
</script>
