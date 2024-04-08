<div class="modal fade" id="showDetail" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-body" style="text-align:center;position: relative;overflow: auto;">
                <img id="image_detail" alt="" style="max-height: 440px;max-width: 650px;transform: rotate(0deg);">
                <a href="javascript:;" id="remove_image" style="position: absolute;
                bottom: 5px;
                right: 45px;color: red"> <i class="fa fa-trash fa-2x"></i> Xóa ảnh</a>
                <a href="javascript:;" id="set_rotate_image" style="position: absolute;
                bottom: 5px;
                right: 15px;"> <i class="fa fa-rotate-right fa-2x"></i></a>
            </div>
            <div class="modal-footer d-flex justify-content-center" style="text-align: center;">
                <div class="form-group col-sm-5">
                    <div style="text-align: left;"><strong>Căn hộ: </strong><span id="aparmtent_name"></span></div>
                </div>
                <div class="form-group col-sm-3"> 
                     <div style="text-align: left;"><strong>Chỉ số đầu: </strong><span id="before_number"></span></div>
                </div>
                <div class="form-group col-sm-4">
                    <div style="text-align: left;"><strong>Tháng chốt số: </strong><span id="cycle_name"></span></div>
                </div>
                <div class="form-group col-sm-5"> 
                     <div style="text-align: left;"><strong>Dịch vụ: </strong><span id="service_name"></span></div>
                </div>
                <div class="form-group col-sm-3">
                    <div class="col-sm-12" style="padding: 0;">
                        <div style="text-align: left;"><strong>Chỉ số cuối: </strong></div>
                    </div>
                    <input type="hidden" id="electric_meter_id" value="">
                </div>
                <div class="form-group col-sm-4"> 
                    <input type="text" name="after_number" id="after_number" class="form-control" placeholder="Chỉ số cuối" value="">
               </div>
                <div class="form-group col-sm-12">
                    <div class="col-sm-2">
                        <a class="btn btn-lg btn-success page-link" href="javascript:;" id="previous" aria-label="Previous">
                            <span aria-hidden="true">&laquo;</span>
                        </a>
                    </div>
                    <div class="col-sm-8">
                        <button type="submit" id="submit_electric" class="btn btn-lg btn-warning" >Sửa</button>
                    </div>
                    <div class="col-sm-2">
                        <a class="btn btn-lg btn-success page-link" href="javascript:;" id="next" aria-label="Next">
                            <span aria-hidden="true">&raquo;</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
