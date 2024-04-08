<div class="modal fade" id="editDepartment" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Chỉnh sửa bộ phận</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form class="form-horizontal" method="POST" data-action="{{ route('admin.department.update') }}" id="edit_department">
                    {{ csrf_field() }}
                    <div class="box-body">
                        <input type="hidden" name="id" value="{{ $department->id }}">
                        <div class="form-group create_name">
                            <label for="name" class="col-sm-3 control-label">Tên bộ phận</label>

                            <div class="col-sm-9">
                                <input type="text" class="form-control" name="name" value="{{ @$department->name }}">
                                <div class="message_zone_create"></div>
                            </div>
                        </div>
                        <div class="form-group create_code">
                            <label for="code" class="col-sm-3 control-label">Mã bộ phận</label>

                            <div class="col-sm-9">
                                <input type="text" class="form-control" name="code" value="{{ @$department->code }}">
                                <div class="message_zone_create"></div>
                            </div>
                        </div>
                        <div class="form-group create_description">
                            <label for="description" class="col-sm-3 control-label">Mô tả</label>
                            <div class="col-sm-9">
                                <textarea class="form-control" rows="3" name="description">{{ @$department->description }}</textarea>
                                <div class="message_zone_create"></div>
                            </div>
                        </div>
                        <div class="form-group create_phone">
                            <label for="phone" class="col-sm-3 control-label">Mobile</label>

                            <div class="col-sm-9">
                                <input type="number" class="form-control" name="phone" value="{{ @$department->phone }}">
                                <div class="message_zone_create"></div>
                            </div>
                        </div>
                        <div class="form-group create_email">
                            <label for="email" class="col-sm-3 control-label">Email</label>

                            <div class="col-sm-9">
                                <input type="text" class="form-control" name="email" value="{{ @$department->email }}">
                                <div class="message_zone_create"></div>
                            </div>
                        </div>
                        <div class="form-group status_payment_info">
                            <label class="col-md-3 control-label">Nhóm Trưởng BQL:</label>
                            <div class="col-md-9">
                                <label class="switch1" style="margin-top: 10px;">
                                    <input type="checkbox" name="type_manager" value="1" {{ @$department->type_manager ? 'checked' : '' }}/>
                                    <div class="message_zone_create"></div>
                                </label>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer d-flex justify-content-center">
                <button type="submit" class="btn btn-info" id="update_department">Cập nhật</button>
                <button type="button" class="btn btn-warning" data-dismiss="modal">Hủy</button>
            </div>
        </div>
    </div>
</div>