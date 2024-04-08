<div id="add-apartment-group" class="modal fade" role="dialog">
    <div class="modal-dialog modal-lg">
        <form action="post">
            {{ csrf_field() }}
            <div class="modal-content">
                <div class="modal-header bg-primary">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Thêm mới nhóm căn hộ</h4>
                </div>
                <div class="modal-body">
                    <div class="alert alert-danger alert_pop_add_vehicle" style="display: none;">
                        <ul></ul>
                    </div>
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="form-group row">
                                <div class="col-sm-3">
                                    <label for="name-apartment-group">Tiêu đề</label>
                                </div>
                                <div class="col-sm-9">
                                    <input type="text" name="name" id="name-apartment-group"
                                           class="form-control"
                                           placeholder="Tên nhóm căn hộ"
                                    >
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-sm-3">
                                    <label for="description-apartment-group">Mô tả</label>
                                </div>
                                <div class="col-sm-9">
                                    <textarea name="description" id="description-apartment-group" class="form-control" cols="30" rows="5" placeholder="Mô tả"></textarea>
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-sm-3">
                                    <label for="apartment-list">Căn hộ</label>
                                </div>
                                <div class="col-sm-9">
                                    <select name="apartment-list"
                                            id="apartment-list"
                                            multiple="multiple"
                                            class="form-control" style="width: 100%;">
                                        <option value="">Chọn căn hộ</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger pull-right" data-dismiss="modal"><i class="fa fa-close"></i>&nbsp;&nbsp;Hủy</button>
                    <button type="button" class="btn btn-primary btn-js-action-add-apartment-group" form="form-add-verhicle" style="margin-right: 5px;"><i class="fa fa-save"></i>&nbsp;&nbsp;Xác nhận</button>
                </div>
            </div>
        </form>
    </div>
</div>
