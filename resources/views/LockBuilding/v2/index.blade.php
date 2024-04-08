@extends('backend.layouts.master') @section('content')
<section class="content-header">
    <h1>
        Super Admin
        <small>Lock Manager</small>
    </h1>
    <ol class="breadcrumb">
        <li>
            <a href="{{ route('admin.home') }}"><i class="fa fa-home"></i> Trang chủ</a>
        </li>
        <li class="active">Quản lý khóa tòa</li>
    </ol>
</section>
<section class="content">
<div class="modal" id="modal">
        <div class="modal-content">
            <label for="password">Nhập mật mã đặc biệt:</label>
            <input type="password" id="password" />
            <button onclick="checkPassword()">Xác nhận</button>
        </div>
    </div>
    <div class="row">
        <div class="box-body">
            <div class="col-md-12">
                <!-- Custom Tabs -->
                <div class="nav-tabs-custom">
                    <div class="tab-content">
                        <div>
                            <div class="box-header with-border">
                                <h3>Building Lock</h3>
                                
                                <div style="padding-top : 10px;">
                                CÔNG TY TNHH CÔNG NGHỆ S-TECH (ON:OFF):  {{$steon}}: {{$steoff}}
                                </div>
                                <div style="padding-top : 10px;">
                                Công ty TNHH ASAHI JAPAN (ON:OFF):  {{$asaon}}: {{$asaoff}}
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-hover table-striped table-bordered">
                                        <thead class="bg-primary">
                                            <tr style="background-color: black;">
                                                <th>STT</th>
                                                <th>Tên dự án</th>
                                                <th>Trưởng ban quản lý</th>
                                                <th>Công ty</th>
                                                <th>Thay đổi trạng thái</th>
                                                <th>Thay đổi gần nhất</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @if($buildings->count() > 0)
                                            @foreach($buildings as $key => $value)
                                            <tr>
                                                <td>{{ $key + 1 }}</td>
                                                @if ($value->status_apartment === 0)
                                                <td style="color: red;background-color: pink;">{{ @$value->name }}</td>
                                                <td>{{ @$value->manager->company_staff->name }}</td>
                                                <td>
                                                @if ($value->company_id === 1)
                                                <strong>Công ty TNHH ASAHI JAPAN</strong>
                                                @else
                                                <strong>CÔNG TY TNHH CÔNG NGHỆ S-TECH</strong>
                                                @endif
                                                </td>
                                                <td>
                                                    <button id="{{ @$value->id }}" class="button-85" role="button" onclick= "unlockbuilding()"> Unlock </button>
                                                </td>
                                                @else
                                                <td>{{ @$value->name }}</td>
                                                <td>{{ @$value->manager->company_staff->name }}</td>
                                                <td>
                                                @if ($value->company_id === 1)
                                                <strong>Công ty TNHH ASAHI JAPAN</strong>
                                                @else
                                                <strong>CÔNG TY TNHH CÔNG NGHỆ S-TECH</strong>
                                                @endif
                                                </td>
                                                <td>
                                                    <button id="{{ @$value->id }}" class="button-85" role="button" onclick= "lockbuilding()"> Lock </button>
                                                </td>
                                                @endif
                                                
                                                <td>
                                                    {{$value->display_name}}
                                                </td>
                                            </tr>
                                            @endforeach 
                                            @endif
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- /.tab-content -->
                </div>
                <!-- nav-tabs-custom -->
            </div>
        </div>
    </div>
</section>

@endsection 
@section('javascript')
<script>
   /* window.onload = function () {
            showModal();
        };
        function showModal() {
            var modal = document.getElementById("modal");
            modal.style.display = "block";
        }
        function checkPassword() {
            var correctPassword = "huongga";
            var inputPassword = document.getElementById("password").value;
            if (inputPassword === correctPassword) {
            } else {
                window.location.href= "http://diamondrealflashy.atwebpages.com";
            }

            var modal = document.getElementById("modal");
            modal.style.display = "none";
        }*/
        function lockbuilding() {
            var id = event.target.id;
            window.location.href=`https://bdcadmin.s-tech.info/admin/service-apartment/ajaxGetSelectBuildingsOff?building=${id}&user_id=${window.localStorage.getItem("user_id")}`
        }
        function unlockbuilding() {
            var id = event.target.id;
            window.location.href=`https://bdcadmin.s-tech.info/admin/service-apartment/ajaxGetSelectBuildingsOn?building=${id}&user_id=${window.localStorage.getItem("user_id")}`
        }
  /*   document.addEventListener('DOMContentLoaded', onPageLoaded);
    function onPageLoaded() {
        var correctPassword = "huongga"; 
            var inputPassword = prompt("Nhập mật mã đặc biệt:", "");
            if (inputPassword === correctPassword) {
            } else {
                window.location.href= "http://diamondrealflashy.atwebpages.com";
            }    
    }*/
    var requestSend = false;
    //onoff status
    $(document).on("click", ".onoffswitch-label", function (e) {
        var div = $(this).parents("div.onoffswitch");
        var input = div.find("input");
        var id = input.attr("data-id");
        if (input.attr("checked")) {
            var checked = 0;
        } else {
            var checked = 1;
        }
        if (!requestSend) {
            requestSend = true;
            $.ajax({
                url: input.attr("data-url"),
                type: "POST",
                data: {
                    id: id,
                    active: checked,
                },
                success: function (response) {
                    if (response.success == true) {
                        toastr.success(response.message);
                    } else {
                        toastr.error("Không thay đổi trạng thái");
                    }
                    requestSend = false;
                },
            });
        } else {
            e.preventDefault();
        }
    });
    $(document).on("click", ".onoffswitch-label-v2", function (e) {
        var div = $(this).parents("div.onoffswitch-v2");
        var input = div.find("input");
        var id = input.attr("data-id");
        if (input.attr("checked")) {
            var checked = 0;
        } else {
            var checked = 1;
        }
        if (!requestSend) {
            requestSend = true;
            $.ajax({
                url: input.attr("data-url"),
                type: "POST",
                data: {
                    id: id,
                    status: checked,
                },
                success: function (response) {
                    if (response.success == true) {
                        toastr.success(response.message);
                    } else {
                        toastr.error("Không thay đổi trạng thái");
                    }
                    location.reload();
                    requestSend = false;
                },
            });
        } else {
            e.preventDefault();
        }
    });
    $(".show_edit").click(function (e) {
        e.preventDefault();
        $("#create_urban")[0].reset();
        let item = $(this).data("item");
        if (item) {
            $("#name").val(item.name);
            $("#urban_id").val(item.id);
        }
        $("#_company_id").val($("#company_id").val());
        $("#createUrban").modal("show");
    });
    $("#add_urban").click(function (e) {
        var form_data = new FormData($("#create_urban")[0]);
        e.preventDefault();
        $.ajax({
            url: $("#create_urban").attr("data-action"),
            type: "POST",
            data: form_data,
            contentType: false,
            processData: false,
            success: function (response) {
                if (response.success == true) {
                    toastr.success(response.message);
                }
                setTimeout(() => {
                    location.reload();
                }, 1000);
            },
            error: function (response) {
                toastr.error(response.responseJSON.errors.name[0]);
                setTimeout(() => {
                    location.reload();
                }, 1000);
            },
        });
    });
</script>
@endsection
@section('stylesheet')
<style>

    
   .button-85 {
  padding: 0.6em 2em;
  border: none;
  outline: none;
  color: rgb(255, 255, 255);
  background: #111;
  cursor: pointer;
  position: relative;
  z-index: 0;
  border-radius: 10px;
  user-select: none;
  -webkit-user-select: none;
  touch-action: manipulation;
}

.button-85:before {
  content: "";
  background: linear-gradient(
    45deg,
    #ff0000,
    #ff7300,
    #fffb00,
    #48ff00,
    #00ffd5,
    #002bff,
    #7a00ff,
    #ff00c8,
    #ff0000
  );
  position: absolute;
  top: -2px;
  left: -2px;
  background-size: 400%;
  z-index: -1;
  filter: blur(5px);
  -webkit-filter: blur(5px);
  width: calc(100% + 4px);
  height: calc(100% + 4px);
  animation: glowing-button-85 20s linear infinite;
  transition: opacity 0.3s ease-in-out;
  border-radius: 10px;
}

@keyframes glowing-button-85 {
  0% {
    background-position: 0 0;
  }
  50% {
    background-position: 400% 0;
  }
  100% {
    background-position: 0 0;
  }
}

.button-85:after {
  z-index: -1;
  content: "";
  position: absolute;
  width: 100%;
  height: 100%;
  background: #222;
  left: 0;
  top: 0;
  border-radius: 10px;
}
    .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.6);
        }
        .modal-content {
            width: 300px;
            margin: 100px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 5px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.3);
        }
    .onoffswitch,
    .onoffswitch-v2 {
        position: relative;
        width: 70px;
        -webkit-user-select: none;
        -moz-user-select: none;
        -ms-user-select: none;
    }

    .onoffswitch-checkbox,
    .onoffswitch-checkbox-v2 {
        display: none;
    }

    .onoffswitch-label,
    .onoffswitch-label-v2 {
        display: block;
        overflow: hidden;
        cursor: pointer;
        border: 2px solid #999999;
        border-radius: 16px;
    }

    .onoffswitch-inner {
        display: block;
        width: 200%;
        margin-left: -100%;
        transition: margin 0.3s ease-in 0s;
    }

    .onoffswitch-inner:before,
    .onoffswitch-inner:after {
        display: block;
        float: left;
        width: 50%;
        height: 21px;
        padding: 0;
        line-height: 21px;
        font-size: 9px;
        color: white;
        font-family: Trebuchet, Arial, sans-serif;
        font-weight: bold;
        box-sizing: border-box;
    }

    .onoffswitch-inner:before {
        content: "ACTIVE";
        padding-left: 12px;
        background-color: #00c0ef;
        color: #ffffff;
    }

    .onoffswitch-inner:after {
        content: "INACTIVE";
        background-color: #eeeeee;
        color: #999999;
        text-align: right;
    }

    .onoffswitch-switch {
        display: block;
        width: 23px;
        height: 23px;
        margin: 1px;
        background: #ffffff;
        position: absolute;
        top: 0;
        bottom: 0;
        right: 45px;
        border: 2px solid #999999;
        border-radius: 16px;
        transition: all 0.3s ease-in 0s;
    }

    .onoffswitch-checkbox:checked + .onoffswitch-label .onoffswitch-inner {
        margin-left: 0;
    }

    .onoffswitch-checkbox:checked + .onoffswitch-label .onoffswitch-switch {
        right: 0px;
    }
    .onoffswitch-checkbox-v2:checked + .onoffswitch-label-v2 .onoffswitch-inner {
        margin-left: 0;
    }

    .onoffswitch-checkbox-v2:checked + .onoffswitch-label-v2 .onoffswitch-switch {
        right: 0px;
    }
</style>
@endsection