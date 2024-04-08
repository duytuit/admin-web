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

    #filePreview_1_edit {
        display: flex;
        flex-wrap: wrap;
        flex-direction: column;
    }

    #filePreview_1_edit {
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

    #file_other1_edit {
        color: #ffffff;
    }

    #file-upload-item-edit {
        color: #ffffff;
    }

</style>

<div id="edit-document-apartment"  class="modal fade" role="dialog">
    <div class="modal-dialog modal-lg">
        <form action="POST" id="form-edit-document-apartment" class="form-validate form-horizontal">
            {{ csrf_field() }}
            <div class="modal-content">
                <div class="modal-header bg-primary">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Cập nhật tài liệu Căn hộ</h4>
                </div>
                <div class="modal-body">
                    <div class="alert alert-danger alert_pop_edit_document_apartment" style="display: none;">
                        <ul></ul>
                    </div>
                    <div class="row">
                        <div class="col-sm-12">
                            <span style="display: none" id="id-document-apartment-edit" data-id=""></span>
                            <div class="form-group row">
                                <div class="col-sm-3">
                                    <label for="title-document-apartment-edit">Tiêu đề</label>
                                </div>
                                <div class="col-sm-9">
                                    <input type="text" name="title" id="title-document-apartment-edit"
                                           class="form-control"
                                           placeholder="Tiêu đề"
                                    >
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-sm-3">
                                    <label for="description-document-apartment-edit">Mô tả</label>
                                </div>
                                <div class="col-sm-9">
                                    <textarea name="description" id="description-document-apartment-edit" class="form-control" cols="30" rows="5" placeholder="Mô tả"></textarea>
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-sm-3">
                                    <label for="description-document-building"></label>
                                </div>
                                <div class="col-sm-9 document-type-edit">
                                    <div>
                                        <input type="radio" id="apartment_group-edit" class="document_type-edit" name="document_type_edit" value="3">
                                        <label for="dewey">Nhóm căn hộ</label>
                                    </div>
                                    <div>
                                        <input type="radio" id="apartment-edit" class="document_type-edit" name="document_type_edit" value="2"
                                               >
                                        <label for="huey">Căn hộ</label>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-sm-3">
                                    <label for="description-document-building-edit">Chọn nhóm căn hộ</label>
                                </div>
                                <div class="col-sm-9">
                                    <select name="apartment_group_list"
                                            id="apartment_group_list-edit"
                                            multiple="multiple"
                                            class="form-control" style="width: 100%;">
                                        <option value="">Chọn nhóm căn hộ</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-sm-3">
                                    <label for="description-document-apartment">Chọn căn hộ</label>
                                </div>
                                <div class="col-sm-9">
                                    <select name="apartment_list"
                                            id="apartment_list-edit"
                                            multiple="multiple"
                                            class="form-control" style="width: 100%;">
                                        <option value="">Chọn căn hộ</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group row">
                                <div class="col-sm-3">
                                    <label for="description-document-building">Tải file</label>
                                </div>
                                <div class="col-sm-9">
                                    <input type="file" id="file_other1_edit" multiple name="images" />
                                    <input type="hidden" id="base64_code" />
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-sm-3">

                                </div>
                                <div class="col-sm-9">
                                    <div id="filePreview_1_edit">
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
                    <button type="button" class="btn btn-primary btn-js-action-edit-document-apartment" style="margin-right: 5px;"><i class="fa fa-save"></i>&nbsp;&nbsp;Xác nhận</button>
                </div>
            </div>
        </form>
    </div>
</div>
