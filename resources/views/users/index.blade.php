@extends('backend.layouts.master')

@section('content')

    <section class="content-header">
        <h1>
            {{$meta_title}}
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('admin.home') }}"><i class="fa fa-home"></i> Trang chủ</a></li>
            <li class="active">Nhân viên</li>
        </ol>
    </section>
    <section class="content">
        <div class="box box-primary">
            <div class="box-body ">
                <div class="row form-group">
                    <div class="col-sm-12 pull-right">
                        <a href="{{ route('admin.users.create') }}" class="btn btn-info"><i class="fa fa-edit"></i>
                           Thêm nhân viên</a>
                        <a href="{{ route('admin.department.index') }}" class="btn btn-warning"><i class="fa fa-search"></i>
                            Quản lý bộ phận</a>
                        <a href="javascript:" type="button" class="btn btn-success btn-action" data-target="#form-users" data-method="qrcode">
                            Lấy file QRcode nhân viên
                        </a>
                    </div>
                </div>
                <form id="form-search-advance" action="{{ route('admin.users.manageUser') }}" method="get">
                    <div id="search-advance" class="search-advance">
                        <div class="row form-group space-5">
                            <div class="col-sm-3">
                                <input type="text" name="keyword" value="{{ $keyword }}"
                                       placeholder="Tên, Email, SĐT, Tavico" class="form-control"/>
                            </div>
                            <div class="col-sm-3">
                                <select name="group_ids" class="form-control select2" style="width: 100%;">
                                    <option value="">Phòng ban</option>
                                    @foreach ($groups as $item)
                                        <option value="{{ @$item->id }}" {{ @$item->id == $group_ids ? 'selected' : '' }}>{{ @$item->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-sm-3">
                                <select name="status" class="form-control">
                                    <option value="">Trạng thái</option>
                                    <option value="1" {{ $status === '1' ? 'selected' : '' }}>Active</option>
                                    <option value="0" {{ $status === '0' ? 'selected' : '' }}>Inactive</option>
                                </select>
                            </div>
                            <div class="col-sm-1">
                                <button class="btn btn-primary btn-block"><i class="fa fa-search"></i></button>
                            </div>
                        </div>
                    </div>
                </form><!-- END #form-search-advance -->

                <form id="form-users" action="{{ route('admin.users.action') }}" method="post">
                    @csrf
                    <input type="hidden" name="method" value=""/>
                    <input type="hidden" name="status" value=""/>

                    <div class="table-responsive">
                        <table class="table table-hover table-striped table-bordered">
                            <thead class="bg-primary">
                            <tr>
                                <th width="30"><input type="checkbox" class="iCheck checkAll" data-target=".checkSingle" /></th>
                                <th width="140">Mã nhân viên</th>
                                <th width="160">Họ tên</th>
                                <th width="140">Email</th>
                                <th width="110">SĐT</th>
                                <th>Phòng ban</th>
                                <th width="50">Status</th>
                                <th width="150">Thao tác</th>
                            </tr>
                            </thead>
                            <tbody>
                            @php
                               $_user = Auth::user();
                            @endphp
                            @foreach ($users as $item)
                                <tr valign="middle">
                                    <td><input type="checkbox" name="ids[]" value="{{$item->pub_user_id.'_'.$item->display_name}}" class="iCheck checkSingle" /></td>
                                    <td>{{ $item->staff_code?:'' }}</td>
                                    <td><a href="{{route('admin.users.permission', $item->pub_user_id) }}">{{$item->display_name }}</a></td>
                                    <td>{{ @$item->pubusers->email }}</td>
                                    <td>{{ @$item->pubusers->phone }}</td>
                                    <td>{{ @$item->pubusers->departmentUser->department->name }}</td>
                                    <td>
                                        <div class="onoffswitch">
                                            <input type="checkbox" name="onoffswitch" class="onoffswitch-checkbox"
                                                   data-id="{{ $item->id }}"
                                                   id="myonoffswitch_{{ $item->id }}"
                                                   data-url="{{ route('admin.users.change-status') }}"
                                                   @if($item->status == true) checked @endif >
                                            <label class="onoffswitch-label" for="myonoffswitch_{{ $item->id }}">
                                                <span class="onoffswitch-inner"></span>
                                                <span class="onoffswitch-switch"></span>
                                            </label>
                                        </div>

                                    </td>
                                    <td>
                                    @if(in_array('admin.users.reset-pass',@$user_access_router))
                                        <a href="javascript:void(0);" id="{{@$item->pub_user_id}}"  email="{{@$item->pubusers->email}}" phone="{{@$item->pubusers->phone}}" class="btn btn-warning reset_password" title="Reset Password"><i class="fa fa-recycle"></i></a>
                                        @if(($_user->isadmin == 1) || in_array('admin.v3.edit.userpermisison',@$user_access_router))
                                            <a href="{{ route('admin.users.edit',['id'=>$item->pub_user_id]) }}" class="btn btn-success" title="sửa"><i class="fa fa-edit"></i></a>
                                        @endif
                                        <a href="{{ route('admin.users.destroy',['id'=>$item->pub_user_id]) }}" onclick="return confirm('Bạn có chắc chắn muốn xóa không?')" class="btn btn-danger del_user" title="Xóa tài khoản"><i class="fa fa-times"></i></a>
                                     @endif
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="row mbm">
                        <div class="col-sm-3">
                            <span class="record-total">Tổng: {{ $users->total() }} bản ghi</span>
                        </div>
                        <div class="col-sm-6 text-center">
                            <div class="pagination-panel">
                                {{ $users->appends(Request::all())->onEachSide(1)->links() }}
                            </div>
                        </div>
                        <div class="col-sm-3 text-right">
                        <span class="form-inline">
                            Hiển thị
                            <select name="per_page" class="form-control" data-target="#form-users">
                                @php $list = [10, 20, 50, 100, 200]; @endphp
                                @foreach ($list as $num)
                                    <option value="{{ $num }}" {{ $num == $per_page ? 'selected' : '' }}>{{ $num }}</option>
                                @endforeach
                            </select>
                        </span>
                        </div>
                    </div>
                </form><!-- END #form-users -->
            </div>
        </div>
    </section>
@endsection
@section('stylesheet')
    <style>
        .onoffswitch {
            position: relative;
            width: 70px;
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
        }

        .onoffswitch-checkbox {
            display: none;
        }

        .onoffswitch-label {
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

        .onoffswitch-inner:before, .onoffswitch-inner:after {
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
            background-color: #00C0EF;
            color: #FFFFFF;
        }

        .onoffswitch-inner:after {
            content: "INACTIVE";
            background-color: #EEEEEE;
            color: #999999;
            text-align: right;
        }

        .onoffswitch-switch {
            display: block;
            width: 23px;
            height: 23px;
            margin: 1px;
            background: #FFFFFF;
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
    </style>
@endsection
@section('javascript')

    <script>
          $('.reset_password').on('click',function () {
                var email = $(this).attr('email');
                var phone = $(this).attr('phone');
                var id = $(this).attr('id');
                var input = '';
                if((email !='' && phone!='') || (email !='' && phone=='')){
                    input = email;
                }
                if(email =='' && phone!=''){
                    input = phone;
                }
                if (!confirm('Bạn có chắc chắn reset mật khẩu của tài khoản này?')) {
                    e.preventDefault();
                } else {
                    $.post('{{ url('/admin/manage-user/reset-pass') }}', {
                        email:  input,
                        user_id:id
                    }, function(data) {
                        toastr.success(data.message);
                    });
                }


            });
        sidebar('users', 'users');
        //onoff status
        var requestSend = false;
        $(document).on('click', '.onoffswitch-label', function (e) {
            var div = $(this).parents('div.onoffswitch');
            var input = div.find('input');
            var id = input.attr('data-id');
            if (input.attr('checked')) {
                var checked = 0;
            } else {
                var checked = 1;
            }
            if (!requestSend) {
                requestSend = true;
                $.ajax({
                    url: input.attr('data-url'),
                    type: 'POST',
                    data: {
                        id: id,
                        status: checked
                    },
                    success: function (response) {
                        if (response.success == true) {
                            toastr.success(response.message);
                        } else {
                            toastr.warning(response.message);
                        }
                        setTimeout(() => {
                            location.reload()
                        }, 1000)
                        requestSend = false;
                    }
                });
            } else {
                e.preventDefault();
            }
        })
    </script>

@endsection