<div id="thong_tin_thanh_toan" class="tab-pane">
    <div class="col-sm-8">
    <div class="box box-primary">
        <div class="box-body">
            <div class="box-header">
                <h3 class="box-title">Danh sách thông tin thanh toán</h3>
            </div>
            <div class="col-md-12">
                <div class="form-horizontal">
                    @if ($payments->count() > 0)
                        @foreach ($payments as $payment)
                            <div class="col-md-8">
                                <div class="box box-warning">
                                    <div class="col-sm-8">
                                        <h3><strong>{{ @$payment->bank_name }}</strong></h3>
                                        <p> Chi nhánh: {{ @$payment->branch }}</p>
    
                                        <strong>STK: {{ @$payment->bank_account }}</strong><br>
                                        <strong>Chủ tài khoản: {{ @$payment->holder_name }}</strong><br>
                                    </div>
                                    <div class="col-sm-4" style="text-align: center">
                                        <i class="fa fa-credit-card-alt fa-5x" style="padding-top: 10px"></i>
                                        <div class="btn-group" style="text-align: center;bottom: 0px;">
                                            <a type="button" data-id="{{ $payment->id }}"
                                                data-action="{{ route('admin.building.editPayment') }}"
                                                class="btn btn-xs btn-white edit-payment"><i class="fa fa-pencil"></i>
                                                Sửa</a>
                                            <a class="btn btn-xs btn-white delete-payment"
                                                data-url="{{ route('admin.building.destroyPayment', $payment->id) }}"><i
                                                    class="fa fa-trash"></i> Xóa</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>
        </div>
    </div>
    </div>
    <div class="col-sm-4" style="border-left: 1px solid #e8e8e8;">
        <div>
            <div class="panel-body">
                <div class="box-header">
                    <h3 class="box-title">Thêm mới thông tin thanh toán</h3>
                </div>
                <div class="form-horizontal">
                    <form class="form-horizontal" data-action="{{ route('admin.building.store-payment') }}"
                        method="post" id="create_payment">
                        {{ csrf_field() }}
                        <input type="hidden" name="bdc_building_id" value="{{ @$building->id }}">
                        <div class="form-group div_bank_account">
                            <label class="col-md-4">Số tài khoản (*)</label>
                            <div class="col-md-8">
                                <input type="text" class="form-control" id="bank_account" name="bank_account">
                                <div class="message_zone"></div>
                            </div>
                        </div>
                        <div class="form-group div_bank_name">
                            <label class="col-md-4">Ngân hàng (*)</label>
                            <div class="col-md-8">
                                <select name="bank_name" id="bank_name" class="form-control select2"
                                    style="width: 100%">
                                    @foreach ($list_bank_vietqr as $value)
                                        <option value="{{ $value['name'] }}"> {{ $value['name'] }}</option>
                                    @endforeach
                                </select>
                                <div class="message_zone"></div>
                            </div>
                        </div>
                        <div class="form-group div_holder_name">
                            <label class="col-md-4">Tên tài khoản (*)</label>

                            <div class="col-md-8">
                                <input type="text" class="form-control" id="holder_name" name="holder_name">
                                <div class="message_zone"></div>
                            </div>
                        </div>
                        <div class="form-group div_branch">
                            <label class="col-md-4">Chi nhánh</label>

                            <div class="col-md-8">
                                <input type="text" class="form-control" id="branch" name="branch">
                                <div class="message_zone"></div>
                            </div>
                        </div>
                        <div class="form-group div_active_payment">
                            <label class="col-md-6">Nhận giao dịch tự động</label>
                            <div class="col-md-6">
                                <label class="switch" style="margin-top: 10px;">
                                    <input type="checkbox" name="active_payment" value="1" />
                                    <span class="slider round"></span>
                                </label>
                                <div class="message_zone"></div>
                            </div>
                        </div>
                        <div class="form-group div_status_payment_info">
                            <label class="col-md-6">Tự động hạch toán</label>
                            <div class="col-md-6">
                                <label class="switch" style="margin-top: 10px;">
                                    <input type="checkbox" name="status_payment_info" value="1" />
                                    <span class="slider round"></span>
                                </label>
                                <div class="message_zone"></div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-md-6 col-md-offset-4">
                                <button type="submit" class="btn btn-primary" id="add_payment">
                                    <i class="fa fa-btn fa-check"></i> Thêm mới
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal-insert">

</div>
