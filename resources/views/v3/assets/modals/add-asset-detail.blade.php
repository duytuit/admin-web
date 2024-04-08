<div id="add-asset-detail" class="modal fade" role="dialog">
    <div class="modal-dialog  modal-lg">
        <form method="post" id="form-add-asset-detail" class="form-validate form-horizontal" enctype="multipart/form-data">
            {{ csrf_field() }}
            <input type="hidden" name="building_id" value="{{ @$building_id }}">
            <input type="hidden" name="id" id="method_asset_detail_id">
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
                                <div class="form-group name_asset_detail">
                                    <div class="col-sm-3">
                                        <label for="in-re_name">Tên tài sản</label>
                                    </div>
                                    <div class="col-sm-8">
                                        <select name="asset_detail_id" class="form-control" id="asset_detail_id" style="width: 100%">
                                            <option value="">Chọn tài sản</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group office_asset_detail">
                                    <div class="col-sm-3">
                                        <label for="in-re_name">Khu vực</label>
                                    </div>
                                    <div class="col-sm-8">
                                        <select name="office_id" class="form-control" id="asset_detail_office_id" style="width: 100%">
                                            <option value="">Chọn khu vực</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-12">
                                <div class="form-group department_asset_detail">
                                    <div class="col-sm-3">
                                        <label for="in-re_name">Bộ phận</label>
                                    </div>
                                    <div class="col-sm-8">
                                        <select name="department_id" class="form-control" id="asset_detail_department_id" style="width: 100%">
                                            <option value="">Chọn bộ phận</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="col-sm-3">
                                        <label for="in-re_name">Trạng thái</label>
                                    </div>
                                    <div class="col-sm-8">
                                        <label class="switch" style="margin-top: 10px;">
                                            <input type="checkbox" name="status" value="1" id="asset_detail_status" {{ @$payment->active_payment ? 'checked' : '' }} />
                                            <span class="slider round"></span>
                                        </label>
                                    </div>
                                </div>
                                <input type="hidden" name="amount" min="1"value="1"id="amount">
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="col-sm-12">
                                <div class="form-group">
                                    <div class="col-sm-3">
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
                                    <div class="col-sm-3">
                                        <label for="in-re_name">Thời gian bảo trì</label>
                                    </div>
                                    <div class="col-sm-8">
                                        <input type="number"
                                               name="maintain_time"
                                               id="maintain_time"
                                               class="form-control"
                                               placeholder="Thời gian bảo trì">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="col-sm-3">
                                        <label for="in-re_name">Thời gian bảo trì lần cuối</label>
                                    </div>
                                    <div class="col-sm-8">
                                        <input type="date" class="form-control" name="last_time_maintain" id="last_time_maintain">
                                    </div>
                                </div>
                                
                            </div>
                            <div class="col-sm-12">
                                <div class="form-group">
                                    <div class="col-sm-3">
                                        <label for="in-re_name">Hình ảnh</label>
                                    </div>
                                    <div class="col-sm-8">
                                        <label class="btn btn-default" for="image_asset_detail">Tải ảnh
                                            <i class="fa fa-files-o" style="font-size: large;"></i>
                                            <input id='image_asset_detail' name="file" type="file" accept="image/*" style="display: none;"/>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-sm-12">
                            <label for="in-re_name">Chi tiết hình ảnh</label>
                        </div>
                        <div class="col-sm-12 show_image_asset_detail" style="width:100%">
                        
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="row">
                        <div class="col-sm-2"></div>
                        <div class="col-sm-8">
                            <button type="button" class="btn btn-danger pull-right" data-dismiss="modal"><i class="fa fa-close"></i>&nbsp;&nbsp;Hủy</button>
                            <button type="button" class="btn btn-primary btn-js-action-add-asset-detail" style="margin-right: 5px;"><i class="fa fa-save"></i>&nbsp;&nbsp;Xác nhận</button>
                        </div>
                        <div class="col-sm-2"></div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
