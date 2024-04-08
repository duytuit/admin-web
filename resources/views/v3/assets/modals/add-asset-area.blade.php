<div id="add-asset-area" class="modal fade" role="dialog">
    <div class="modal-dialog  modal-lg">
        <form method="post" id="form-add-asset-area" class="form-validate form-horizontal">
            {{ csrf_field() }}
            <input type="hidden" name="building_id" value="{{ @$building_id }}">
            <input type="hidden" name="id" id="method_area_asset_id">
            <div class="modal-content">
                <div class="modal-header bg-primary">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Thêm khu vực</h4>
                </div>
                <div class="modal-body">
                    <div class="alert alert-danger alert_pop_add_area" style="display: none;">
                        <ul></ul>
                    </div>
                    <div class="row">
                        <div class="col-sm-2"></div>
                        <div class="col-sm-8">
                            <div class="form-group">
                                <div class="col-sm-3">
                                    <label for="in-re_name">Khu vực</label>
                                </div>
                                <div class="col-sm-8">
                                    <input type="text"
                                           name="name"
                                           id="title-asset-area"
                                           class="form-control"
                                           required
                                           placeholder="Tên khu vực">
                                </div>
                            </div>
                            <div class="form-group place_asset_detail">
                                <div class="col-sm-3">
                                    <label for="in-re_name">Tòa nhà</label>
                                </div>
                                <div class="col-sm-8">
                                    <select name="place_id" class="form-control" id="asset_detail_place_id" style="width: 100%">
                                        <option value="">Chọn tòa nhà</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group floor_asset_detail">
                                <div class="col-sm-3">
                                    <label for="in-re_name">Tầng</label>
                                </div>
                                <div class="col-sm-8">
                                    <select name="floor_id" class="form-control" id="asset_detail_floor_id" style="width: 100%">
                                        <option value="">Chọn tầng</option>
                                    </select>
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
                            <button type="button" class="btn btn-primary btn-js-action-add-asset-area" style="margin-right: 5px;"><i class="fa fa-save"></i>&nbsp;&nbsp;Xác nhận</button>
                        </div>
                        <div class="col-sm-2"></div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
