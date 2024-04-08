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
<div id="add-promotion-manager" class="modal fade" role="dialog">
    <div class="modal-dialog modal-lg">
        <form id="form-add-promotion-manager" class="form-validate form-horizontal">
            {{ csrf_field() }}
            <div class="modal-content">
                <input type="hidden" name="type" value="service_vehicle">
                <div class="modal-header bg-primary">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Thêm khuyến mãi</h4>
                </div>
                <div class="modal-body">
                    <div class="alert alert-danger alert_pop_add_promotion-manager" style="display: none;">
                        <ul></ul>
                    </div>
                    <div class="row box-body">
                            <input type="hidden" name="id" id="promotion_id">
                            <div class="form-group row">
                                <div class="col-sm-3">
                                    <label for="title-promotion-manager">Tên khuyến mãi</label>
                                </div>
                                <div class="col-sm-9">
                                    <input type="text" name="name" class="form-control" placeholder="Tên khuyến mãi">
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-sm-3">
                                    <label for="category-promotion-manager">Dịch vụ</label>
                                </div>
                                <div class="col-sm-9 service_select">
                                    <select class="form-control" name="service_id" id="bdc_service_id" style="width: 100%">
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-sm-3">
                                    <label for="time-promotion-manager">Thời gian áp dụng</label>
                                </div>
                                <div class="col-sm-9">
                                    <div class="col-sm-6" style="padding-left: 0">
                                        từ ngày : 
                                        <input type="date" class="form-control" name="begin" id="begin">
                                    </div>
                                    <div class="col-sm-6" style="padding-right: 0">
                                        đến ngày :
                                        <input type="date" class="form-control" name="end" id="end">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-sm-3">
                                    <label for="category-promotion-manager">Điều kiện khuyến mại</label>
                                </div>
                                <div class="col-sm-9">
                                    <input type="number" name="condition" min="0" class="form-control" placeholder="Nhập giá trị">
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-sm-3">
                                    <label for="category-promotion-manager">Số tháng khuyến mại</label>
                                </div>
                                <div class="col-sm-9">
                                    <input type="number" name="number_discount" min="0" class="form-control" placeholder="Nhập giá trị">
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-sm-3">
                                    <label for="discount-promotion-manager">Giá trị khuyến mãi</label>
                                </div>
                                <div class="col-sm-9">
                                    <div style="display: flex;" id="promotion-discount-main">
                                        <input type="number" name="discount" min="0" class="form-control" placeholder="Nhập giá trị">
                                        <select name="type_discount" id="type_discount" class="form-control" style="width: 25%">
                                            <option value="0">VND</option>
                                            <option value="1">%</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger pull-right" id="close-form" data-dismiss="modal"><i
                            class="fa fa-close"></i>&nbsp;&nbsp;Hủy</button>
                    <button type="button" class="btn btn-primary save_promotion_manager" style="margin-right: 5px;"><i
                            class="fa fa-save"></i>&nbsp;&nbsp;Xác nhận</button>
                </div>
            </div>
        </form>
    </div>
</div>