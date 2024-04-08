<div class="modal fade" id="createSubListSample" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" style="padding-right:0px;" aria-hidden="true">
    <div class="modal-dialog shift" role="document" style="padding: 20px 0;">
        <div class="modal-content" style="border-radius: 5px;">
            <div class="modal-header" style="
            border-top-right-radius: 5px;
            border-top-left-radius: 5px;
            color: white;
            background-color: #3c8dbc;
            padding: 5px;
            border-bottom: 0;
            ">
                <h5 class="modal-title" style="margin-top: 2px;">Check list</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="margin-top: -20px;margin-right: 10px;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" class="col-sm-12" style="padding:0;">
                <div>
                    <ul id="sublistsample-errors"></ul>
                </div>
                <form class="form-horizontal" action="" id="modal-sublistsample">
                    <div class="box-body">
                        <div class="form-group">
                            <label for="timeorder" class="col-sm-3 control-label">Thời gian đặt</label>
                            <div class="col-sm-9">
                                <textarea class="form-control" id="timeorder" name="timeorder" rows="3"></textarea>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="description" class="col-sm-3 control-label">Ghi chú</label>
                            <div class="col-sm-9">
                                <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer d-flex justify-content-center">
                        <button type="button" class="btn btn-primary add-sublistsample">Thêm mới</button>
                        <button type="button" class="btn btn-warning" data-dismiss="modal">Hủy</button>
                    </div>
                </form>
                <input type="hidden" id="sublistsample_id">
            </div>

        </div>
    </div>
</div>