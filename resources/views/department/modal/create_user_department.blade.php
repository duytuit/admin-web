<div class="modal fade" id="createUserDepartment" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Thêm nhân viên vào bộ phận</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form role="form" class="form-horizontal" action="" method="post" novalidate="novalidate">
                    <div class="form-group">
                        <label class="col-lg-2">Họ và tên</label>
                        <div class="col-lg-10">
                            <input type="text" placeholder="Nhập tên đầy đủ" class="form-control input-sm" id="name" name="name"></div>
                    </div>
                    <div class="form-group">
                        <label class="col-lg-2">Tài khoản</label>
                        <div class="col-lg-10">
                            <input type="text" placeholder="Nhập tên tài khoản" class="form-control input-sm" id="username" name="username"></div>
                    </div>
                    <div class="form-group">
                        <label class="col-lg-2">Email</label>
                        <div class="col-lg-10">
                            <input type="email" placeholder="Nhập địa chỉ email" class="form-control input-sm" id="email" name="email"></div>
                    </div>
                    <div class="form-group">
                        <label class="col-lg-2">Mật khẩu</label>
                        <div class="col-lg-10">
                            <input type="password" placeholder="Nhập mật khẩu" class="form-control input-sm" id="password" name="password"></div>
                    </div>
                    <div class="form-group">
                        <label class="col-lg-2">Nhập lại Mật khẩu</label>
                        <div class="col-lg-10">
                            <input type="password" placeholder="Nhập lại mật khẩu" class="form-control input-sm" id="password_confirmation" name="password_confirmation">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-lg-2">Bộ phận</label>
                        <div class="col-lg-10">
                            <select class="form-control input-sm" name="department" required="" aria-required="true">
                                <option value="62">Bảo Vệ</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-lg-2">Trưởng bộ phận</label>
                        <div class="col-lg-10">
                            <p>
                            <input type="radio" name="yes_no" checked>Có</input>
                            </p>
                            <p>
                                <input type="radio" name="yes_no">Không</input>
                            </p>

                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer d-flex justify-content-center text-center">
                <button type="button" class="btn btn-primary" >Thêm mới</button>
                <button type="button" class="btn btn-warning" data-dismiss="modal">Hủy</button>
            </div>
        </div>
    </div>
</div>