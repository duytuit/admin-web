<div class="modal fade" id="editBankInfo" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Chỉnh sửa thông tin thẻ</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form class="form-horizontal" data-action="{{ route('admin.building.updatePayment') }}" method="post" id="edit_payment">
                    <input type="hidden" name="id" value="{{ $payment->id }}">
                    <div class="form-group create_bank_account">
                        <label class="col-md-3 control-label">Số tài khoản (*)</label>

                        <div class="col-md-6">
                            <input type="text" class="form-control" id="bank_account" name="bank_account" value="{{ @$payment->bank_account }}">
                            <div class="message_zone_create"></div>
                        </div>
                    </div>
                    <div class="form-group create_bank_name">
                        <label class="col-md-3 control-label">Ngân hàng (*)</label>
                        <div class="col-md-6">
                            <select name="bank_name" id="bank_name" class="form-control select2" style="width: 100%">
                                @foreach($list_bank_vietqr as $value)
                                    <option value="{{ $value['name'] }}" {{@$payment->bank_name === $value['name'] ? 'selected' : ''}}> {{ $value['name'] }}</option>
                                @endforeach
                              </select>
                            <div class="message_zone"></div>
                        </div>
                    </div>
                    <div class="form-group create_holder_name">
                        <label class="col-md-3 control-label">Tên tài khoản (*)</label>
                        <div class="col-md-6">
                            <input type="text" class="form-control" id="holder_name" name="holder_name" value="{{ @$payment->holder_name }}">
                            <div class="message_zone_create"></div>
                        </div>
                    </div>
                    <div class="form-group create_branch">
                        <label class="col-md-3 control-label">Chi nhánh</label>
                        <div class="col-md-6">
                            <input type="text" class="form-control" id="branch" name="branch" value="{{ @$payment->branch }}">
                            <div class="message_zone_create"></div>
                        </div>
                    </div>
                    <div class="form-group active_payment">
                        <label class="col-md-3 control-label">Nhận giao dịch tự động</label>
                        <div class="col-md-6">
                            <label class="switch" style="margin-top: 10px;">
                                <input type="checkbox" name="active_payment" value="1" {{ $payment->active_payment ? 'checked' : '' }} />
                                <span class="slider round"></span>
                            </label>
                        </div>
                    </div>
                    <div class="form-group status_payment_info">
                        <label class="col-md-3 control-label">Tự động hạch toán</label>
                        <div class="col-md-6">
                            <label class="switch" style="margin-top: 10px;">
                                <input type="checkbox" name="status_payment_info" value="1" {{ $payment->status_payment_info ? 'checked' : '' }} />
                                <span class="slider round"></span>
                            </label>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer d-flex justify-content-center">
                <button type="button" class="btn btn-primary" id="update_payment">Cập nhật</button>
                <button type="button" class="btn btn-warning" data-dismiss="modal">Hủy</button>
            </div>
        </div>
    </div>
</div>