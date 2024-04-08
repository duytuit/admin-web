<div class="modal fade" id="detailCheckList" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" style="padding-right:0px;" aria-hidden="true">
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
                <h5 class="modal-title" style="margin-top: 2px;">Thêm chi tiết check list</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="margin-top: -20px;margin-right: 10px;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" class="col-sm-12" style="padding:0;">
                <div>
                    <ul id="tempsample-errors"></ul>
                </div>
                <form class="form-horizontal" action="" id="form-tempsample">
                    <div class="box-body">
                        <div class="form-group" style="padding: 0 45px;">
                            <div class="form-group">
                                <label for="recipient-name" class="control-label"><span style="color:red;font-size: 18px;">*</span> Tiêu đề:</label>
                                <input type="text" name="title-parent" class="form-control" id="title-parent" value="">
                            </div>
                            <div class="form-group">
                                <div class="subtemp">
                                    <label>Chi tiết checklist</label>
                                 </div>
                                <div class="row form-group">
                                    <div class="col-sm-10" style="top: 12px;">
                                        <label class="control-label" for="recipient-name" class="control-label">Tiêu đề:</label>
                                        <div style="width: 100%;display: flex">
                                             <textarea class="form-control" style="resize: vertical;" id="sub_task_title" name="sub_task_title" rows="1"></textarea>
                                        </div>
                                        <label class="control-label" for="recipient-name" class="control-label">Mô tả:</label>
                                        <div style="width: 100%;display: flex">
                                            <textarea class="form-control" style="resize: vertical;" id="sub_task_description" name="sub_task_description" rows="1"></textarea>
                                        </div>
                                    </div>
                                    <div class="col-sm-2" style="top: 40px;">
                                        <button type="button" class="btn btn-sm btn-info add-subtemp-sample" title="Thêm"><i class="fa fa-plus"></i></button>
                                    </div>
                                </div>
                                <div class="clearfix"></div>
                                <div class="row">
                                    <div class="col-sm-12">
                                        <label class="control-label">Giá trị lựa chọn:</label>
                                    </div>
                                    <div class="col-sm-8 form-horizontal" style="top: 12px;">
                                        <div class="list_value_item">
                                            <div class="row form-group list-subtemp" style="display: flex;
                                            align-items: center;
                                            justify-content: center;">
                                                <div class="col-sm-9">
                                                    <div class="title-label">
                                                        <i class="fa fa-angle-double-right" style="font-weight: bold;margin-right: 10px;"></i>
                                                        <span class="title-span">test</span>
                                                    </div>
                                                    <div class="description-label">
                                                        <i class="fa fa-file-text-o" style="color:green;margin-right: 5px;"></i>
                                                        <span class="description-span">nội dung test</span>
                                                    </div>
                                                </div>
                                                <div class="col-sm-3">
                                                    <span style="font-size: 16px;font-weight: bold;cursor: pointer;"><i class="fa fa-edit" style="color: #0475d6;"></i></span>
                                                    <span style="font-size: 18px;font-weight: bold;cursor: pointer;margin-left: 5px"><i class="fa fa-trash" style="color: red;"></i></span>
                                                </div>
                                            </div>
                                            <div class="row form-group list-subtemp" style="display: flex;
                                            align-items: center;
                                            justify-content: center;">
                                                <div class="col-sm-9">
                                                    <div class="col-sm-12 form-group edit-title-label">
                                                        <input class="form-control" name="edit_title" value="fghfgh" placeholder="Giá trị">
                                                    </div>
                                                    <div class="col-sm-12 form-group edit-description-label">
                                                        <input class="form-control" name="edit_description" value="fghfghfgh" placeholder="Mức độ">
                                                    </div>
                                                </div>
                                                <div class="col-sm-3">
                                                    <span style="font-size: 16px;font-weight: bold;cursor: pointer;"><i class="fa fa-save" style="color: #0475d6;"></i></span>
                                                    <span style="font-size: 18px;font-weight: bold;cursor: pointer;margin-left: 5px"><i class="fa fa-hand-stop-o" style="color: red;"></i></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-9">
                                            <div class="col-sm-6">
                                                <label for="inputName" class="control-label">Giá trị</label>
                                                <input type="email" class="form-control" id="inputName" placeholder="Giá trị">
                                            </div>
                                            <div class="col-sm-6">
                                                <label for="inputName" class="control-label">Mức độ</label>
                                                <select name="department_id" id="dfdf" class="form-control">
                                                    <option value="0">Bình thường</option>
                                                    <option value="1">Không nghiêm trọng</option>
                                                    <option value="2">Nghiêm trọng</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-sm-1" style="top: 26px;">
                                            <button type="button" class="btn btn-sm btn-info add-value-checklist" title="Thêm"><i class="fa fa-plus"></i></button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer d-flex justify-content-center">
                        <button type="button" class="btn btn-primary add-tempsample">Thêm mới</button>
                        <button type="button" class="btn btn-warning" data-dismiss="modal">Hủy</button>
                    </div>
                </form>
                <input type="hidden" id="tempsample_id">
            </div>
        </div>
    </div>
</div>

