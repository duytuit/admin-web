<div id="add-asset" class="modal fade" role="dialog">
    <div class="modal-dialog  modal-lg">
        <form method="post" id="form-add-asset" class="form-validate form-horizontal">
            {{ csrf_field() }}
            <input type="hidden" name="building_id" value="{{ @$building_id }}">
            <input type="hidden" name="id" id="method_asset_id">
            <div class="modal-content">
                <div class="modal-header bg-primary">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Thêm tài sản</h4>
                </div>
                <div class="modal-body">
                    <div class="alert alert-danger alert_pop_add_asset" style="display: none;">
                        <ul></ul>
                    </div>
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="col-sm-12">
                                <div class="form-group">
                                    <div class="col-sm-3">
                                        <label for="in-re_name">Tên tài sản</label>
                                    </div>
                                    <div class="col-sm-8">
                                        <input type="text" name="name"
                                               id="title-asset" class="form-control"
                                               placeholder="Tên tài sản">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="col-sm-3">
                                        <label for="in-re_name">Danh mục</label>
                                    </div>
                                    <div class="col-sm-8 _asset_category">
                                        <select name="asset_category_id" class="form-control" id="asset_category_id" style="width: 100%">
                                            <option value="">Chọn danh mục</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="col-sm-12">
                                <div class="form-group">
                                    <div class="col-sm-4">
                                        <label for="in-re_name">Kiểu bảo trì</label>
                                    </div>
                                    <div class="col-sm-8">
                                        <select name="type_maintain" id="type_maintain" class="form-control">
                                            <option value="1" selected>Ngày</option>
                                            <option value="2">Tháng</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="col-sm-4">
                                        <label for="in-re_name">Thời gian bảo trì</label>
                                    </div>
                                    <div class="col-sm-8">
                                        <input type="number"
                                               name="maintain_time"
                                               min="1"
                                               id="maintain_time"
                                               class="form-control"
                                               placeholder="thời gian">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div>
                        <div class="form-group">
                            <div class="col-sm-12">
                                <label for="in-re_name">Ghi chú</label>
                            </div>
                            <div class="col-sm-12">
                                <textarea type="text" name="desc" rows="5" id="asset_note" class="form-control" placeholder="Ghi chú"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="row">
                        <div class="col-sm-2"></div>
                        <div class="col-sm-8">
                            <button type="button" class="btn btn-danger pull-right" data-dismiss="modal"><i class="fa fa-close"></i>&nbsp;&nbsp;Hủy</button>
                            <button type="button" class="btn btn-primary btn-js-action-add-asset" style="margin-right: 5px;"><i class="fa fa-save"></i>&nbsp;&nbsp;Xác nhận</button>
                        </div>
                        <div class="col-sm-2"></div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
