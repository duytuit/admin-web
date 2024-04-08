<style>

    .image-upload-item {
        width: 50px;
        height: 50px;
        position: relative;
        margin: 5px;
        border: 1px solid;
        display: flex;
        justify-content: flex-end;
        align-items: flex-start;
    }

    #dvPreview {
        display: flex;
        flex-wrap: wrap;
    }

    #dvPreviewEdit {
        display: flex;
        flex-wrap: wrap;
    }

    .image-upload-item i {
        color: #000;
        position: relative;
        top: 0;
        right: 0;
        font-size: 11px;
        z-index: 100;
        cursor: pointer;
    }

    .image-upload-item img {
        height: 100%;
        width: 100%;
        position: absolute;
        top: 0;
        left: 0;
    }

    #fileupload {
        color: #ffffff;
    }

    #fileuploadEdit {
        color: #ffffff;
    }

    #file_other {
        color: #ffffff;
    }

    #file-upload-item-edit {
        color: #ffffff;
    }

</style>
<div id="update_status_maintain" class="modal fade" role="dialog">
    <div class="modal-dialog  modal-lg">
        <form action="" method="post" id="form-add-asset-category" class="form-validate form-horizontal">
            {{ csrf_field() }}
            <input type="hidden" name="hashtag">
            <div class="modal-content">
                <div class="modal-header bg-primary">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title" id="modal-title-category">Kết quả bảo trì</h4>
                </div>
                <div class="modal-body">
                    <div class="alert alert-danger alert_pop_update_maintain" style="display: none;">
                        <ul></ul>
                    </div>
                    <div class="row">
                        <div class="col-sm-2"></div>
                        <div class="col-sm-8">
                            <input type="hidden" id="id-asset-maitain">
                            <div class="form-group">
                                <div class="col-sm-3">
                                    <label for="in-re_name">Đơn vị thực hiện</label>
                                </div>
                                <div class="col-sm-8">
                                    <input type="text" name="provider" id="provider-asset-maintain" class="form-control" placeholder="Đơn vị thực hiện">
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-sm-3">
                                    <label for="in-re_name">Kết quả</label>
                                </div>
                                <div class="col-sm-8">
                                    <textarea type="text" name="description" rows="5" id="description-asset-maintain" class="form-control" placeholder="Ghi chú"></textarea>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-sm-3">
                                    <label>Thêm ảnh:</label>
                                </div>
                                <div class="col-sm-8">
                                    <input type="file" id="fileupload" accept="image/*" multiple name="images" />
                                    <input type="hidden" id="array_fileupload" />
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-sm-3">

                                </div>
                                <div class="col-sm-8">
                                    <div id="dvPreview">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-sm-3">
                                    <label>File khác:</label>
                                </div>
                                <div class="col-sm-8">
                                    <input type="file" id="file_other" multiple name="images" />
                                    <input type="hidden" id="array_file_other" />
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-sm-3">

                                </div>
                                <div class="col-sm-8">
                                    <div id="filePreview">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-sm-3">
                                    <label for="in-re_name">Phí phát sinh (VNĐ)</label>
                                </div>
                                <div class="col-sm-8">
                                    <input type="number" name="price" id="price-asset-maintain" class="form-control" placeholder="Phí phát sinh">
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
                            <button type="button" class="btn btn-primary btn-js-action-update-maintain" style="margin-right: 5px;"><i class="fa fa-save"></i>&nbsp;&nbsp;Xác nhận</button>
                        </div>
                        <div class="col-sm-2"></div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
