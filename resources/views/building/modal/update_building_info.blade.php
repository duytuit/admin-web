<div class="modal fade" id="editBuildingInfo" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Thêm thông tin tòa nhà</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form class="form-horizontal" method="POST" data-action="{{ route('admin.building.updateInfo') }}" id="edit_info">
                    <input type="hidden" name="bdc_building_id" value="{{ @$info->bdc_building_id }}">
                    <input type="hidden" name="id" value="{{ @$info->id }}">
                    <div class="box-body">
                        <div class="form-group update_content">
                            <label for="content" class="col-sm-2 control-label">Nội dung</label>

                            <div class="col-sm-10">
                                <input type="text" class="form-control" id="content" name="content" value="{{ @$info->content }}">
                                <div class="message_zone_update"></div>
                            </div>
                        </div>
                        <div class="form-group update_quantity">
                            <label for="quantity" class="col-sm-2 control-label">Số lượng</label>

                            <div class="col-sm-10">
                                <input type="number" class="form-control" id="quantity" name="quantity" value="{{ @$info->quantity }}">
                                <div class="message_zone_update"></div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="note" class="col-sm-2 control-label">Ghi chú</label>
                            <div class="col-sm-10">
                                <textarea class="form-control" rows="10" name="note">{{ @$info->note }}</textarea>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer d-flex justify-content-center">
                <button type="button" class="btn btn-primary" id="update_info">Lưu</button>
                <button type="button" class="btn btn-warning" data-dismiss="modal">Hủy</button>
            </div>
        </div>
    </div>
</div>