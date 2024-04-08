<div class="modal fade" id="createServicePartner" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" style="font-size: 25px;">Đăng ký dịch vụ</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div>
                    <ul id="errors"></ul>
                </div>
               <form class="form-horizontal" action="" id="modal-service-partners-category">
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
                    <input class="hidden" id="service_partners_id">
                    <div class="modal-footer d-flex justify-content-center">
                        <button type="button" class="btn btn-primary add-service-partners">Thêm mới</button>
                        <button type="button" class="btn btn-warning" data-dismiss="modal">Hủy</button>
                    </div>
                </form>
                  <input type="hidden" id="custId">
            </div>

        </div>
    </div>
</div>
<script type="text/javascript" src="/adminLTE/plugins/jquery/jquery.min.js"></script>
<script>

</script>
