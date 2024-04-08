<div class="modal fade" id="createDepartment" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Thêm bộ phận</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form class="form-horizontal" method="POST" data-action="{{ route('admin.department.store') }}" id="create_department">
                    {{ csrf_field() }}
                    <div class="box-body">
                        <input type="hidden" value="{{ $active_building }}" name="bdc_building_id">
                        <div class="form-group div_name">
                            <label for="name" class="col-sm-3 control-label">Tên bộ phận</label>

                            <div class="col-sm-9">
                                <input type="text" class="form-control" name="name">
                                <div class="message_zone"></div>
                            </div>
                        </div>
                        <div class="form-group div_code">
                            <label for="code" class="col-sm-3 control-label">Mã bộ phận</label>

                            <div class="col-sm-9">
                                <input type="text" class="form-control" name="code">
                                <div class="message_zone"></div>
                            </div>
                        </div>
                        <div class="form-group div_description">
                            <label for="description" class="col-sm-3 control-label">Mô tả</label>
                            <div class="col-sm-9">
                                <textarea class="form-control" rows="3" name="description"></textarea>
                                <div class="message_zone"></div>
                            </div>
                        </div>
                        <div class="form-group div_phone">
                            <label for="phone" class="col-sm-3 control-label">Mobile</label>

                            <div class="col-sm-9">
                                <input type="number" class="form-control" name="phone">
                                <div class="message_zone"></div>
                            </div>
                        </div>
                        <div class="form-group div_email">
                            <label for="email" class="col-sm-3 control-label">Email</label>

                            <div class="col-sm-9">
                                <input type="text" class="form-control" name="email">
                                <div class="message_zone"></div>
                            </div>
                        </div>
                        <div class="form-group status_payment_info">
                            <label class="col-md-3 control-label">Nhóm Trưởng BQL:</label>
                            <div class="col-md-6">
                                <label class="switch" style="margin-top: 10px;">
                                    <input type="checkbox" name="type_manager" value="1" />
                                    <span class="slider round"></span>
                                </label>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer d-flex justify-content-center">
                <button type="submit" class="btn btn-info" id="add_department">Thêm mới</button>
                <button type="button" class="btn btn-warning" data-dismiss="modal">Hủy</button>
            </div>
        </div>
    </div>
</div>