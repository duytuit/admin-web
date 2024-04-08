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

    #file_other_edit {
        color: #ffffff;
    }

    #file-upload-item-edit {
        color: #ffffff;
    }

</style>
<div id="edit-document-building" class="modal fade" role="dialog">
    <div class="modal-dialog modal-lg">
        <form action="POST" id="form-add-document-building" class="form-validate form-horizontal">
            {{ csrf_field() }}
            <div class="modal-content">
                <div class="modal-header bg-primary">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Sửa tài liệu BQL</h4>
                </div>
                <div class="modal-body">
                    <div class="alert alert-danger alert_pop_edit_document_building" style="display: none;">
                        <ul></ul>
                    </div>
                    <div class="row">
                        <div class="col-sm-12">
                            <span style="display: none" id="id-document-building-edit" data-id=""></span>
                            <div class="form-group row">
                                <div class="col-sm-3">
                                    <label for="title-document-building">Tiêu đề</label>
                                </div>
                                <div class="col-sm-9">
                                    <input type="text" name="title" id="title-document-building-edit"
                                           class="form-control"
                                           placeholder="Tiêu đề"
                                    >
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-sm-3">
                                    <label for="description-document-building">Mô tả</label>
                                </div>
                                <div class="col-sm-9">
                                    <textarea name="description" id="description-document-building-edit" class="form-control" cols="30" rows="5" placeholder="Mô tả"></textarea>
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-sm-3">
                                    <label for="description-document-building">Tải file</label>
                                </div>
                                <div class="col-sm-9">
                                    <input type="file" id="file_other_edit" multiple name="images" />
                                    <input type="hidden" id="base64" />
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-sm-3">

                                </div>
                                <div class="col-sm-9">
                                    <div id="filePreviewEdit">
                                    </div>
                                </div>
                                <script>
                                    function removeThisFile(ele) {
                                        $(ele).closest('.file-upload-item').remove();
                                    }
                                </script>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger pull-right" data-dismiss="modal"><i class="fa fa-close"></i>&nbsp;&nbsp;Hủy</button>
                    <button type="button" class="btn btn-primary btn-js-action-edit-document-building" form="form-add-verhicle" style="margin-right: 5px;"><i class="fa fa-save"></i>&nbsp;&nbsp;Xác nhận</button>
                </div>
            </div>
        </form>
    </div>
</div>
