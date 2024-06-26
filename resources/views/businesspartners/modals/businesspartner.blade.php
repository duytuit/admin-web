<div class="modal fade" id="createBusinessPartner" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" style="font-size: 25px;">Thêm mới đối tác</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div>
                    <ul id="errors"></ul>
                </div>
               <form class="form-horizontal" action="" id="modal-partners-category">
                    <div class="box-body">
                        <div class="form-group">
                            <label for="name" class="col-sm-3 control-label">Tên đối tác</label>
                            <div class="col-sm-9">
                                <input type="text" name="name" class="form-control" id="name" value="">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="contact" class="col-sm-3 control-label">Đơn vị cung cấp</label>
                            <div class="col-sm-9">
                                 <input type="text" name="contact" class="form-control" id="contact" value="">
                            </div>
                        </div>
                         <div class="form-group">
                            <label for="address" class="col-sm-3 control-label">Địa chỉ</label>
                            <div class="col-sm-9">
                                   <textarea class="form-control" id="address" name="address" rows="3"></textarea>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="mobile" class="col-sm-3 control-label">Số điện thoại</label>
                            <div class="col-sm-9">
                                  <input type="text" name="mobile" class="form-control" id="mobile" >
                            </div>
                        </div>
                         <div class="form-group">
                            <label for="email" class="col-sm-3 control-label">Email</label>
                            <div class="col-sm-9">
                                 <input type="email" name="email" class="form-control" id="email" value="">
                            </div>
                        </div>
                         <div class="form-group">
                            <label for="representative" class="col-sm-3 control-label">Người đại diện</label>
                            <div class="col-sm-9">
                                <div class="col-sm-5" style="padding: 0;">
                                   <input type="text" name="representative" class="form-control " id="representative" value="">
                                </div>
                                 <label for="position" class="col-sm-3 control-label" style="text-align: center;">Chức vụ</label>
                                 <div class="col-sm-4" style="padding: 0;">
                                     <input type="text" name="position" class="form-control col-sm-3" id="position" value="">
                                 </div>
                            </div>
                        </div>
                         <div class="form-group">
                            <label for="description" class="col-sm-3 control-label">Ghi chú</label>
                            <div class="col-sm-9">
                                   <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                            </div>
                        </div>
                    </div>
                    <input class="hidden" id="partners_id">
                    <div class="modal-footer d-flex justify-content-center">
                        <button type="button" class="btn btn-primary add" id="submit" >Thêm mới</button>
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
