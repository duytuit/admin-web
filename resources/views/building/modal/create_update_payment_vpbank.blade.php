<div class="modal fade" id="editVPBankInfo" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Cập nhập thông tin VPbank</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form class="form-horizontal" data-action="{{ route('admin.building.storePaymentVpBank') }}" method="post" id="create_edit_payment_vpbank">
                    <input type="hidden" name="id" value="{{ @$payment_vpbank->id }}">
                    <div class="form-group create_api_url">
                        <label class="col-md-3 control-label">api_url (*)</label>

                        <div class="col-md-6">
                            <input type="password" class="form-control" id="api_url" name="api_url" value="{{ @$payment_vpbank->api_url }}">
                            <div class="message_zone_create"></div>
                        </div>
                    </div>
                    <div class="form-group create_username">
                        <label class="col-md-3 control-label">username (*)</label>
                        <div class="col-md-6">
                            <input type="text" class="form-control" id="username" name="username" value="{{ @$payment_vpbank->username }}">
                            <div class="message_zone_create"></div>
                        </div>
                    </div>
                    <div class="form-group create_password">
                        <label class="col-md-3 control-label">password (*)</label>
                        <div class="col-md-6">
                            <input type="password" class="form-control" id="password" name="password" value="{{ @$payment_vpbank->password }}">
                            <div class="message_zone_create"></div>
                        </div>
                    </div>
                    <div class="form-group create_customer_no">
                        <label class="col-md-3 control-label">customer_no (*)</label>
                        <div class="col-md-6">
                            <input type="text" class="form-control" id="customer_no" name="customer_no" value="{{ @$payment_vpbank->customer_no }}">
                            <div class="message_zone_create"></div>
                        </div>
                    </div>
                    <div class="form-group create_account_no">
                        <label class="col-md-3 control-label">account_no (*)</label>
                        <div class="col-md-6">
                            <input type="text" class="form-control" id="account_no" name="account_no" value="{{ @$payment_vpbank->account_no }}">
                            <div class="message_zone_create"></div>
                        </div>
                    </div>
                    <div class="form-group create_partner">
                        <label class="col-md-3 control-label">partner (*)</label>
                        <div class="col-md-6">
                            <input type="text" class="form-control" id="partner" name="partner" value="{{ @$payment_vpbank->partner }}">
                            <div class="message_zone_create"></div>
                        </div>
                    </div>
                    <div class="form-group create_virtual_account">
                        <label class="col-md-3 control-label">virtual_account (*)</label>
                        <div class="col-md-6">
                            <input type="text" class="form-control" id="virtual_account" name="virtual_account" value="{{ @$payment_vpbank->virtual_account }}">
                            <div class="message_zone_create"></div>
                        </div>
                    </div>
                    <div class="form-group create_base_rank">
                        <label class="col-md-3 control-label">base_rank (*)</label>
                        <div class="col-md-6">
                            <input type="text" class="form-control" id="base_rank" name="base_rank" value="{{ @$payment_vpbank->base_rank }}">
                            <div class="message_zone_create"></div>
                        </div>
                    </div>
                    <div class="form-group create_service_name">
                        <label class="col-md-3 control-label">service_name</label>
                        <div class="col-md-6">
                            <input type="text" class="form-control" id="service_name" name="service_name" value="{{ @$payment_vpbank->service_name }}">
                            <div class="message_zone_create"></div>
                        </div>
                    </div>
                    <div class="form-group create_app_name">
                        <label class="col-md-3 control-label">app_name</label>
                        <div class="col-md-6">
                            <input type="text" class="form-control" id="app_name" name="app_name" value="{{ @$payment_vpbank->app_name }}">
                            <div class="message_zone_create"></div>
                        </div>
                    </div>

                </form>
            </div>
            <div class="modal-footer d-flex justify-content-center">
                <button type="button" class="btn btn-primary" id="create_update_payment_vpbank">Cập nhật</button>
                <button type="button" class="btn btn-warning" data-dismiss="modal">Hủy</button>
            </div>
        </div>
    </div>
</div>