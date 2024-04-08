<div class="modal fade" id="createCheckList" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" style="padding-right:0px;" aria-hidden="true">
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
                <form class="form-horizontal" action="" id="form-check-list">
                    <div class="box-body form_add_checklist">
                        <div class="col-sm-12">
                            <div class="form-group">
                                <div class="col-sm-12">
                                    <input type="hidden" name="checklist_id" id="checklist_id">
                                    <div>
                                        <label for="recipient-name" class="control-label"><span style="color:red;font-size: 18px;">*</span> Tiêu đề:</label>
                                        <input type="text" name="title" id="checklist_title" class="form-control">
                                    </div>
                                </div>
                            </div>
                            <div>
                                <button type="button" class="btn btn-sm btn-info add-value-checklist" title="Thêm"><i class="fa fa-plus"></i>Thêm</button>
                                <a data-toggle="modal" data-target="#addtemplatetotask" class="btn btn-sm btn-success"><i class="fa fa-plus"></i>Thêm checklist từ mẫu</a>
{{--                                <a class="btn btn-sm btn-success add_checklist_from_tempalte"><i class="fa fa-plus"></i>Thêm checklist từ mẫu</a>--}}
                            </div>
                            <div class="list_detail_checklists">
                                <div class="form-group detail_checklist">
                                    <div class="col-sm-10" style="top: 12px;">
                                        <div ><label>Checklist 1: </label></div>
                                        <input type="hidden" name="sub_checklist_sort">
                                        <label class="control-label" class="control-label">Tiêu đề:</label>
                                        <input type="hidden" name="sub_checklist_id">
                                        <div style="width: 100%;display: flex">
                                            <textarea class="form-control" style="resize: vertical;" name="sub_checklist_title" rows="1"></textarea>
                                        </div>
                                        <label class="control-label" class="control-label">Mô tả:</label>
                                        <div style="width: 100%;display: flex">
                                            <textarea class="form-control" style="resize: vertical;" name="sub_checklist_description" rows="1"></textarea>
                                        </div>
                                    </div>
                                    <div class="col-sm-2" style="top: 64px;display: flex">
                                        <a class="btn btn-xs btn-primary" onclick="copyCheckList(this)" title="Sao chép"><i class="fa fa-copy"></i></a>
                                        <a class="btn btn-xs btn-danger" onclick="deleteCheckList(this)" style="margin-left: 10px" title="Xóa thông tin"><i class="fa fa-trash"></i></a>
                                    </div>
                                    <div class="col-sm-12">
                                        <div style="display: flex;margin-left: 35px;">
                                            <label class="checkbox">
                                                <input type="checkbox" name="video_required" value="1" />Video
                                            </label>
                                            <label class="checkbox" style="margin-left: 50px">
                                                <input type="checkbox" name="image_required" value="1" />Hình ảnh
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-sm-12" style="margin-top: 12px">
                                        <label class="control-label">Giá trị lựa chọn: <span> <button type="button" class="btn btn-sm btn-info" onclick="addValueWarning(this)" title="Thêm"><i class="fa fa-plus"></i></button></span></label>
                                    </div>
                                    <div class="row list_detail_values">
                                        <div class="detail_value">
                                            <div class="col-sm-9">
                                                <div class="col-sm-6">
                                                    <label class="control-label">Giá trị</label>
                                                    <input type="text" class="form-control input-sm" name="name_warning" placeholder="Giá trị">
                                                </div>
                                                <div class="col-sm-6">
                                                    <label class="control-label">Mức độ</label>
                                                    <select name="level_warning" class="form-control input-sm">
                                                        <option value="0">Bình thường</option>
                                                        <option value="1">Không nghiêm trọng</option>
                                                        <option value="2">Nghiêm trọng</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-sm-1" style="top: 26px;display: flex">
                                                <a class="btn btn-xs btn-primary" onclick="copyValueCheckList(this)" title="Sao chép"><i class="fa fa-copy"></i></a>
                                                <a class="btn btn-xs btn-danger" onclick="deleteValueCheckList(this)" style="margin-left: 10px" title="Xóa thông tin"><i class="fa fa-trash"></i></a>
                                            </div>
                                        </div>
                                    </div>
                                    
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer d-flex justify-content-center">
                        <button type="button" class="btn btn-primary add_check_list">Cập nhật</button>
                        <button type="button" class="btn btn-warning" data-dismiss="modal">Hủy</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

