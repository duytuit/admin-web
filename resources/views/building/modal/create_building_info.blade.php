<div class="modal fade" id="createBuildingInfo" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Thêm thông tin tòa nhà</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form class="form-horizontal" method="POST" data-action="{{ route('admin.building.building-info-store') }}" id="create_info">
                    <input type="hidden" name="bdc_building_id" value="{{ @$building->id }}">
                    <div class="box-body">
                        <div class="form-group data_content">
                            <label for="content" class="col-sm-2 control-label">Nội dung</label>

                            <div class="col-sm-10">
                                <input type="text" class="form-control" id="description" name="description">
                                <div class="message_zone_data"></div>
                            </div>
                        </div>
                        <div class="form-group data_quantity">
                            <label for="quantity" class="col-sm-2 control-label">Số lượng</label>

                            <div class="col-sm-10">
                                <input type="number" class="form-control" id="quantity" name="quantity">
                                <div class="message_zone_data"></div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="note" class="col-sm-2 control-label">Ghi chú</label>
                            <div class="col-sm-10">
                                <textarea class="form-control" rows="10" name="note"></textarea>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer d-flex justify-content-center">
                <button type="button" class="btn btn-primary" id="add_info">Lưu</button>
                <button type="button" class="btn btn-warning" data-dismiss="modal">Hủy</button>
            </div>
        </div>
    </div>
</div>