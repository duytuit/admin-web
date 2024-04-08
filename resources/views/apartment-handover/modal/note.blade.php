<div class="modal fade" id="note_apartment_handover" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" style="padding-right:0px;display:none" aria-hidden="true">
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
                <h5 class="modal-title" style="margin-top: 2px;">Ghi chú xác nhận khách hàng</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="margin-top: -20px;margin-right: 10px;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" class="col-sm-12" style="padding:0;">
                <div>
                    <ul id="tempsample-errors"></ul>
                </div>
                <form class="form-horizontal" action="" id="modal-tempsample">
                    <div class="box-body">
                        <div class="form-group" style="padding: 0 45px;">
                            <div class="form-group">
                                <label for="recipient-name" class="control-label"><span style="color:red;font-size: 18px;">*</span>Nội dung:</label>
                                 <textarea class="form-control" name="description" rows="6"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer d-flex justify-content-center">
                        <button type="button" class="btn btn-primary add-tempsample">Lưu</button>
                        <button type="button" class="btn btn-warning" data-dismiss="modal">Hủy</button>
                    </div>
                </form>
                <input type="hidden" id="tempsample_id">
            </div>
        </div>
    </div>
</div>
<style>
    @media (min-width: 768px) {
        .shift {
            width: 600px;
            margin: 30px auto;
        }
    }
</style>
<script type="text/javascript" src="/adminLTE/plugins/jquery/jquery.min.js"></script>
<script>
     $(document).ready(function() {

     });
</script>
   
<script type="text/javascript">

</script>
