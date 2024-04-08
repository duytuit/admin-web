<div class="modal fade" id="createShift" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" style="padding-right:0px;" aria-hidden="true">
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
                <h5 class="modal-title" style="margin-top: 2px;">Thêm ca</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="margin-top: -20px;margin-right: 10px;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" class="col-sm-12" style="padding:0;">
                <div>
                    <ul id="shift-errors"></ul>
                </div>
                <form class="form-horizontal" action="" method="POST" id="form-shift">
                    {{ csrf_field() }}
                    <input type="hidden" name="building_id" value="{{ @$building_id }}">
                    <input type="hidden" name="id" id="shift_id">
                    <div class="box-body">
                        <div class="form-group" style="padding: 0 45px;"> 
                              <div class="form-group">
                                <label for="recipient-name" class="control-label"><span style="color:red;font-size: 18px;">*</span> Tên ca:</label>
                                    <input type="text" name="name" class="form-control" id="name-shift" >
                            </div>
                            <div class="form-group">
                              <div class="row">
                                <div class="col-sm-6">
                                    <div class="row">
                                        <div class="col-sm-12">
                                            <label for="timeorder" class="control-label"><span style="color:red;font-size: 18px;">*</span> Thời gian bắt đầu</label>
                                        </div>
                                    </div>
                                    <input type="time" id="start_time" name="from">
                                </div>
                                <div class="col-sm-6">
                                   <div class="row">
                                        <div class="col-sm-12">
                                             <label for="timeorder" class="control-label"><span style="color:red;font-size: 18px;">*</span> Thời gian kết thúc</label>
                                        </div>
                                    </div>
                                    <input type="time" id="end_time" name="to">
                                </div>
                              </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer d-flex justify-content-center">
                        <button type="button" class="btn btn-primary add-shift">Lưu</button>
                        <button type="button" class="btn btn-warning" data-dismiss="modal">Hủy</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
