<div class="modal fade" id="addtemplatetotask" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" style="padding-right:0px;" aria-hidden="true">
    <div class="modal-dialog addtemplatetotask" role="document" style="padding: 20px 0; margin-top: 30px;">
        <div class="modal-content" style="border-radius: 5px;min-height: 250px">
            <div class="modal-header" style="
            border-top-right-radius: 5px;
            border-top-left-radius: 5px;
            color: white;
            background-color: #3c8dbc;
            padding: 5px;
            border-bottom: 0;
            ">
                <h5 class="modal-title" style="margin-top: 2px;">Thêm Checklist từ mẫu có sẵn</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="margin-top: -20px;margin-right: 10px;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" class="col-sm-12" style="padding:0;">
                <div>
                    <ul id="templatetotask-errors"></ul>
                </div>
                <form class="form-horizontal" action="" method="POST" id="modal-templatetotask">

                    <div class="box-body">
                        <div class="row">
                            <div class="col-sm-12">
                                <label for="recipient-name" class="control-label"><span style="color:red;font-size: 18px;">*</span>Chọn Checklist mẫu</label>
                                <select name="title" id="title_task_template" class="form-control" style="width: 100%">
                                    <option value="">Chọn Checklist mẫu</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group" style="display: inline;">
                              <div class="col-sm-12 subtemp_title_task_template">

                              </div>
                        </div>
                    </div>
                    <div class="modal-footer d-flex justify-content-center">
                        <button type="button" class="btn btn-primary add-templatetotask">Thêm</button>
                        <button type="button" class="btn btn-warning" data-dismiss="modal">Đóng</button>
                    </div>

                </form>
                <input type="hidden" id="category_id">
            </div>

        </div>
    </div>
</div>