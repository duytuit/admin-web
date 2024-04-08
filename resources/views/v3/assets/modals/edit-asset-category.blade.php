<div id="edit-asset-category" class="modal fade" role="dialog">
    <div class="modal-dialog  modal-lg">
        <form action="" method="post" id="form-edit-asset-category" class="form-validate form-horizontal">
            {{ csrf_field() }}
            <input type="hidden" name="hashtag">
            <div class="modal-content">
                <div class="modal-header bg-primary">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title" id="modal-title-edit-category">Thêm danh mục</h4>
                </div>
                <div class="modal-body">
                    <div class="alert alert-danger alert_pop_add_category" style="display: none;">
                        <ul></ul>
                    </div>
                    <div class="row">
                        <div class="col-sm-2"></div>
                        <div class="col-sm-8">
                            <input type="hidden" name="id" id="id-edit-asset-category">
                            <div class="form-group">
                                <div class="col-sm-3">
                                    <label for="in-re_name">Tên danh mục</label>
                                </div>
                                <div class="col-sm-8">
                                    <input type="text" name="title" id="title-edit-asset-category" class="form-control" placeholder="Tên danh mục">
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-sm-3">
                                    <label for="in-re_name">Ghi chú</label>
                                </div>
                                <div class="col-sm-8">
                                    <textarea type="text" name="note" rows="5" id="note-edit-asset-category" class="form-control" placeholder="Ghi chú"></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-2"></div>
                    </div>

                </div>
                <div class="modal-footer">
                    <div class="row">
                        <div class="col-sm-2"></div>
                        <div class="col-sm-8">
                            <button type="button" class="btn btn-danger pull-right" data-dismiss="modal"><i class="fa fa-close"></i>&nbsp;&nbsp;Hủy</button>
                            <button type="button" class="btn btn-primary btn-js-action-edit-asset-category" style="margin-right: 5px;"><i class="fa fa-save"></i>&nbsp;&nbsp;Xác nhận</button>
                        </div>
                        <div class="col-sm-2"></div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>