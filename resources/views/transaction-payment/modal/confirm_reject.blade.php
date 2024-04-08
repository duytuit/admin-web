<div class="modal fade" id="confirm_reject" tabindex="-1" role="dialog" style="display:none" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Nội dung xác nhận</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form class="form-horizontal" data-action="{{ route('admin.transactionpayment.status_confirm_reject') }}" method="put" id="create_confirm_reject">
                    <input type="hidden" name="id" id="transactionPaymentId">
                    <div class="form-group create_note">
                        <label class="col-md-3 control-label">Nội dung (*)</label>
                        <div class="col-md-6">
                            <textarea class="form-control" id="note" name="note" rows="3"></textarea>
                            <div class="message_zone_create"></div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer d-flex justify-content-center">
                <button type="button" class="btn btn-primary" id="submit_confirm_reject">Xác nhận</button>
                <button type="button" class="btn btn-warning" data-dismiss="modal">Hủy</button>
            </div>
        </div>
    </div>
</div>