<div id="edit-asset" class="modal fade" role="dialog">
    <div class="modal-dialog  modal-lg">
        <form action="" method="post" id="form-add-asset" class="form-validate form-horizontal">
            {{ csrf_field() }}
            <input type="hidden" name="hashtag">
            <div class="modal-content">
                <div class="modal-header bg-primary">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Sửa tài sản</h4>
                </div>
                <div class="modal-body">
                    <div class="alert alert-danger alert_pop_add_asset" style="display: none;">
                        <ul></ul>
                    </div>
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="col-sm-12">
                                <input type="hidden" id="id_asset_edit">
                                <div class="form-group">
                                    <div class="col-sm-3">
                                        <label for="in-re_name">Tên tài sản</label>
                                    </div>
                                    <div class="col-sm-8">
                                        <input type="text" name="name"
                                               id="title-asset-edit" class="form-control"
                                               placeholder="Tên tài sản">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="col-sm-3">
                                        <label for="in-re_name">Danh mục</label>
                                    </div>
                                    <div class="col-sm-8">
                                        <select name="asset_category_id"
                                                class="form-control"
                                                id="asset_category_id_edit">
                                            <option value="">Chọn danh mục</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="col-sm-3">
                                        <label for="in-re_name">Số lượng</label>
                                    </div>
                                    <div class="col-sm-8">
                                        <input type="number"
                                               name="quantity"
                                               id="quantity_edit"
                                               class="form-control"
                                               placeholder="Số lượng">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="col-sm-3">
                                        <label>Kì bảo trì</label>
                                    </div>
                                    <div class="col-sm-8">
                                        <select class="form-control"
                                                name="bdc_period_id"
                                                id="bdc_period_id_edit">
                                            <option value="" selected>Chọn kì bảo trì</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="col-sm-3">
                                        <label>Bảo trì từ:</label>
                                    </div>
                                    <div class="col-sm-8">
                                        <div class="input-group date">
                                            <input type="date"
                                                   class="form-control pull-right date_picker"
                                                   id="maintainance_date_edit"
                                                   name="maintainance_date" value="{{ old('maintainance_date') }}">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="col-sm-12">
                                <div class="form-group">
                                    <div class="col-sm-3">
                                        <label for="in-re_name">Khu vực</label>
                                    </div>
                                    <div class="col-sm-8">
                                        <select name="area_id" id="area_id_edit" class="form-control">
                                            <option value="">Chọn khu vực</option>
                                           
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="col-sm-3">
                                        <label for="in-re_name">Bộ phận quản lý</label>
                                    </div>
                                    <div class="col-sm-8">
                                        <select name="department_id" id="department_id_edit" class="form-control">
                                            <option value="">Chọn bộ phận</option>
                                          
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="col-sm-3">
                                        <label for="in-re_name">Người giám sát</label>
                                    </div>
                                    <div class="col-sm-8">
                                        <select name="department_id" id="follower_edit" class="form-control">
                                            <option value="">Chọn người giám sát</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="col-sm-3">
                                        <label>Hạn bảo hành:</label>
                                    </div>
                                    <div class="col-sm-8">
                                        <div class="input-group date">
                                            <input type="date" id="warranty_period_edit" class="form-control pull-right date_picker" name="warranty_period" value="{{ old('warranty_period') }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="col-sm-3">
                                        <label>Thêm ảnh:</label>
                                    </div>
                                    <div class="col-sm-8">
                                        <input type="file" id="fileupload_edit" accept="image/*" multiple name="images" />
                                        <input type="hidden" name="array_images" id="edit_array_images" />
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="col-sm-3">

                                    </div>
                                    <div class="col-sm-8">
                                        <div id="dvPreviewEdit">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div>
                        <div class="form-group">
                            <div class="col-sm-12">
                                <label for="in-re_name">Thông số kỹ thuật</label>
                            </div>
                            <div class="col-sm-12">
                                <textarea type="text" name="asset_note" rows="5" id="asset_note_edit" class="form-control" placeholder="Ghi chú"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="row">
                        <div class="col-sm-2"></div>
                        <div class="col-sm-8">
                            <button type="button" class="btn btn-danger pull-right" data-dismiss="modal"><i class="fa fa-close"></i>&nbsp;&nbsp;Hủy</button>
                            <button type="button" class="btn btn-primary btn-js-action-edit-asset" style="margin-right: 5px;"><i class="fa fa-save"></i>&nbsp;&nbsp;Xác nhận</button>
                        </div>
                        <div class="col-sm-2"></div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    function getBase64Image(img) {
        var canvas = document.createElement("canvas");
        canvas.width = img.width;
        canvas.height = img.height;
        var ctx = canvas.getContext("2d");
        ctx.drawImage(img, 0, 0);
        var dataURL = canvas.toDataURL("image/png");
        return dataURL.replace(/^data:image\/(png|jpg);base64,/, "");
    }
</script>
