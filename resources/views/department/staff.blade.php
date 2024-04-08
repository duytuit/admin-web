<div class="box-body">
    <div class="row box box-default">
        <div class="box-header with-border">
            <h3 class="box-title bold">Danh sách nhân viên bộ phận</h3>
            <a href="{{ route('admin.users.create') }}" class="btn btn-warning"><i
                        class="fa fa-edit"></i>
                Thêm mới người dùng</a>
            <a href="{{ route('admin.users.manageUser') }}" class="btn btn-info"><i
                        class="fa fa-search"></i>
                Quản lý người dùng</a>
        </div>
        <br>
        <br>
        <form class="form-horizontal" data-action="{{ route('admin.department.addStaff', $department->id) }}" method="post" id="create_staff">
            {{ csrf_field() }}
            <div class="col-md-6">
                <div class="form-group div_pub_user_ids">
                    <label class="col-md-4">Chọn nhân viên</label>
                    <div class="col-md-8">
                        <select name="pub_user_ids[]" class="form-control select2" multiple>
                            @if($employee->count() > 0)
                                @foreach($employee as $item)
                                    <option value="{{ $item->pub_user_id }}">{{ $item->display_name }}</option>
                                @endforeach
                            @else
                                <option value="">Chưa có nhân viên nào</option>
                            @endif
                        </select>
                        <div class="message_zone"></div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <button class="btn btn-danger add_staff">Thêm nhân viên</button>
            </div>
        </form>
        <div class="col-md-12">
            <div class="table-responsive">
                <table class="table table-hover table-striped table-bordered">
                    <thead class="bg-primary">
                    <tr>
                        <th>STT</th>
                        <th>Họ và tên</th>
                        <th>Tài khoản</th>
                        <th>Email</th>
                        <th>Chức vụ</th>
                        <th>Thao tác</th>
                    </tr>
                    </thead>
                    <tbody>
                    @if($staff->count() > 0)

                        @foreach($staff as $key => $item)
                            <tr>
                                <td>{{ @($key + 1) }}</td>
                                <td>
                                    <a>{{ @$item->publicUser->BDCprofile->display_name??'unknow' }}</a>
                                </td>
                                <td>{{ @$item->publicUser->BDCprofile->email??'unknow' }}</td>
                                <td>{{ @$item->publicUser->BDCprofile->email??'unknow' }}</td>
                                <td>
                                    <span class="label label-warning">{!! \App\Models\DepartmentStaff\DepartmentStaff::REGENCY[$item->type] !!}</span>
                                </td>
                                <td>
                                    <a data-url="{{ route('admin.department.destroyStaff', $item->id) }}" class="btn btn-xs btn-danger" id="del-staff">
                                        <i class="fa fa-trash-o"></i></a>
                                    @if($item->type == 0)
                                    <a data-url="{{ route('admin.department.headStaff') }}" data-id="{{ $item->id }}" data-department="{{ $department->id }}" class="btn btn-xs btn-primary" id="head-staff"><i class="fa fa-check"></i> Chọn làm trưởng bộ phận</a>
                                    {{--<a data-url="{{ route('admin.department.headBuilding') }}" data-id="{{ $item->id }}" data-department="{{ $department->id }}" class="btn btn-xs btn-primary" id="head-building"><i class="fa fa-check"></i> Chọn làm trưởng ban quản lý</a>--}}
                                    @else
                                    <a data-url="{{ route('admin.department.changeStaff') }}" data-id="{{ $item->id }}" data-department="{{ $department->id }}" class="btn btn-xs btn-primary" id="change-staff"><i class="fa fa-check"></i> Chọn làm nhân viên</a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    @endif
                    </tbody>
                </table>
            </div>
            <div class="row mbm">
                <div class="col-sm-3">
                    <span class="record-total">Tổng nhân viên :  {{ $staff->count() }}</span>
                </div>
            </div>
        </div>
    </div>
</div>
@section('javascript')
    <script>
        submitAjaxForm('.add_staff', '#create_staff', '.div_', '.message_zone');

        deleteSubmit('#del-staff');

        //dat lam truong phong
        $(document).on('click', '#head-staff', function (e) {
            if (!requestSend) {
                e.preventDefault();
                requestSend = true;
                $.ajax({
                    url: $(this).attr('data-url'),
                    type: 'POST',
                    data: {
                        id: $(this).attr('data-id'),
                        departmentID: $(this).attr('data-department'),
                    },
                    success: function (response) {
                        if (response.success == true) {
                            toastr.success(response.message);
                        } else {
                            toastr.error('Có lỗi! Xin vui lòng thử lại');
                        }
                        setTimeout(() => {
                            location.reload()
                        }, 2000);
                        requestSend = false;
                    }
                })
            }
        })
        //dat lam truong ban quan ly
        $(document).on('click', '#head-building', function (e) {
            if (!requestSend) {
                e.preventDefault();
                requestSend = true;
                $.ajax({
                    url: $(this).attr('data-url'),
                    type: 'POST',
                    data: {
                        id: $(this).attr('data-id'),
                        departmentID: $(this).attr('data-department'),
                    },
                    success: function (response) {
                        if (response.success == true) {
                            toastr.success(response.message);
                        } else {
                            toastr.error('Có lỗi! Xin vui lòng thử lại');
                        }
                        setTimeout(() => {
                            location.reload()
                        }, 2000);
                        requestSend = false;
                    }
                })
            }
        })
         //dat lam nhan vien
         $(document).on('click', '#change-staff', function (e) {
            if (!requestSend) {
                e.preventDefault();
                requestSend = true;
                $.ajax({
                    url: $(this).attr('data-url'),
                    type: 'POST',
                    data: {
                        id: $(this).attr('data-id'),
                        departmentID: $(this).attr('data-department'),
                    },
                    success: function (response) {
                        if (response.success == true) {
                            toastr.success(response.message);
                        } else {
                            toastr.error('Có lỗi! Xin vui lòng thử lại');
                        }
                        setTimeout(() => {
                            location.reload()
                        }, 2000);
                        requestSend = false;
                    }
                })
            }
        })
    </script>
@endsection