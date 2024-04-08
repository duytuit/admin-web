<div id="add-cat-asset" class="modal fade" role="dialog">
    <div class="modal-dialog  modal-lg">
        <form method="post" id="form-add-asset-category" class="form-validate form-horizontal">
            {{ csrf_field() }}
            <input type="hidden" name="building_id" value="{{ @$building_id }}">
            <input type="hidden" name="id" id="method_cat_asset_id">
            <div class="modal-content">
                <div class="modal-header bg-primary">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title" id="modal-title-category">Thêm danh mục</h4>
                </div>
                <div class="modal-body">
                    <div class="alert alert-danger alert_pop_add_category" style="display: none;">
                        <ul></ul>
                    </div>
                    <div class="row">
                        <div class="col-sm-2"></div>
                        <div class="col-sm-8">
                            <div class="form-group">
                                <div class="col-sm-3">
                                    <label for="in-re_name">Tên danh mục</label>
                                </div>
                                <div class="col-sm-8">
                                    <input type="text" name="title" id="title-asset-category" class="form-control" placeholder="Tên danh mục">
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
                            <button type="button" class="btn btn-primary btn-js-action-add-asset-category" style="margin-right: 5px;"><i class="fa fa-save"></i>&nbsp;&nbsp;Xác nhận</button>
                        </div>
                        <div class="col-sm-2"></div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>