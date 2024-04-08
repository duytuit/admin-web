<div id="add-asset-maintain" class="modal fade" role="dialog">
    <div class="modal-dialog  modal-lg">
        <form action="" method="post" id="form-add-asset-category" class="form-validate form-horizontal">
            {{ csrf_field() }}
            <input type="hidden" name="hashtag">
            <div class="modal-content">
                <div class="modal-header bg-primary">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title" id="modal-title-category">Thêm lịch bảo trì</h4>
                </div>
                <div class="modal-body">
                    <div class="alert alert-danger alert_pop_add_maintain" style="display: none;">
                        <ul></ul>
                    </div>
                    <div class="row">
                        <div class="col-sm-2"></div>
                        <div class="col-sm-8">
                            <input type="hidden" id="id-asset-maitain">
                            <div class="form-group">
                                <div class="col-sm-3">
                                    <label for="in-re_name">Tiêu đề</label>
                                </div>
                                <div class="col-sm-8">
                                    <input type="text" name="title" id="title_asset_maintain" class="form-control" placeholder="Tiêu đề">
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-sm-3">
                                    <label for="in-re_name">Tài sản</label>
                                </div>
                                <div class="col-sm-8">
                                    <select name="asset_category_id"
                                            class="form-control"
                                            id="id_asset_maintain">
                                        <option value="">Chọn tài sản</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-sm-3">
                                    <label>Thời gian bảo trì</label>
                                </div>
                                <div class="col-sm-8">
                                    <div class="input-group date">
                                        <input type="date"
                                               class="form-control pull-right date_picker"
                                               id="maintainance_date_asset_maintain"
                                               name="maintainance_date" value="{{ old('maintainance_date') }}">
                                    </div>
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
                            <button type="button" class="btn btn-primary btn-js-action-add-maintain" style="margin-right: 5px;"><i class="fa fa-save"></i>&nbsp;&nbsp;Xác nhận</button>
                        </div>
                        <div class="col-sm-2"></div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>