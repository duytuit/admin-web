<div id="user"
    class="tab-pane">
            <div class="box-body ">
                <div class="row form-group">
                    <div class="col-sm-12 pull-right">
                        <a href="{{ route('admin.users.create') }}" class="btn btn-info"><i class="fa fa-edit"></i>
                           Thêm nhân viên</a>
                        <a href="{{ route('admin.department.index') }}" class="btn btn-warning"><i class="fa fa-search"></i>
                            Quản lý bộ phận</a>
                    </div>
                </div>
                <form id="form-search-advance" action="" method="get">
                    <div id="search-advance" class="search-advance">
                        <div class="row form-group space-5">
                            <div class="col-sm-3">
                                <input type="text" name="keyword_user" value="{{ $keyword_user }}"
                                       placeholder="Email, SĐT" class="form-control"/>
                            </div>
                            <div class="col-sm-1">
                                <button class="btn btn-primary btn-block"><i class="fa fa-search"></i></button>
                            </div>
                        </div>
                    </div>
                </form><!-- END #form-search-advance -->

                <form id="form-user" action="{{ route('admin.users.action') }}" method="post">
                    @csrf
                    <input type="hidden" name="method" value=""/>
                    <input type="hidden" name="status" value=""/>

                    <div class="table-responsive">
                        <table class="table table-hover table-striped table-bordered">
                            <thead class="bg-primary">
                            <tr>
                                <th>Mã nhân viên</th>
                                <th>Email</th>
                                <th width="110">SĐT</th>
                                <th width="10">Admin</th>
                                <th width="100">Delete</th>
                                <th width="200">Thao tác</th>
                            </tr>
                            </thead>
                            <tbody>
                            @php
                                $_user = Auth::user();
                            @endphp
                            @foreach ($users as $item)
                                <tr valign="middle">
                                    <td>
                                        <a target="_blank" href="/admin/activity-log/log-action?row_id={{$item->id}}"> {{ $item->id }}</a>
                                    </td>
                                    <td>{{ $item->email }}</td>
                                    <td>{{ $item->mobile }}</td>
                                    <td>{{ $item->isadmin }}</td>
                                    <td>{{ $item->deleted_at }}</td>
                                    <td>
                                    @if( in_array('admin.users.reset-pass',@$user_access_router))
                                        <a href="{{ route('admin.users.restoreUser',['id'=>$item->id]) }}" onclick="return confirm('Bạn có chắc chắn muốn cập nhập không?')" class="btn btn-primary del_user" title="Active"><i class="fa fa-edit"></i></a>
                                        @if($_user->isadmin == 1)
                                        <a href="{{ route('admin.users.edit',['id'=>$item->id]) }}" class="btn btn-success" title="sửa"><i class="fa fa-edit"></i></a>
                                        @endif
                                        <a href="javascript:void(0);" id="{{$item->id}}" email="{{$item->email}}" phone="{{$item->mobile}}" class="btn btn-warning reset_password" title="Reset Password"><i class="fa fa-recycle"></i></a>
                                        <a href="{{ route('admin.users.destroyUserApp',['id'=>$item->id]) }}" onclick="return confirm('Bạn có chắc chắn muốn xóa không?')" class="btn btn-danger del_user" title="Xóa tài khoản"><i class="fa fa-times"></i></a>
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
                            <select name="per_page" class="form-control" data-target="#form-user">
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
                var input = email ?? phone;
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
                            toastr.error('Không thể thay đổi trạng thái');
                        }
                        requestSend = false;
                    }
                });
            } else {
                e.preventDefault();
            }
        })
    </script>

@endsection
   
</div>