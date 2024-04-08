<div id="thong_tin_phuong_thuc_thanh_toan" class="tab-pane">
    <div class="col-sm-8">
        <div class="table-responsive">
            <table class="table table-hover table-striped table-bordered">
                <thead class="bg-primary">
                    <tr>
                        <th>STT</th>
                        <th>Phương thức</th>
                        <th>Phí cố định/giao dịch(VND)</th>
                        <th>Phí giao dịch(%)</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody id="list_methodpayment">
                   
                </tbody>
            </table>
            <div class="row mbm">
                <div class="col-sm-3">
                 </div>
                 <div class="col-sm-6 text-center">
                    <div id="pagination-panel"></div>
                 </div>
            </div>
        </div>
    </div>
    <div class="col-sm-4" style="border-left: 1px solid #e8e8e8;">
        <div class="box-header">
            <h3 class="box-title">Thêm phương thức thanh toán</h3>
        </div>
        <br><br>
        <div class="form-horizontal">
            <form class="form-horizontal" method="post" id="create_method_payment">
                <input type="hidden" name="building_id" value="{{ @$building->id }}">
                <input type="hidden" name="id" id="method_payment_id">
                <div class="form-group div_bank_name">
                    <label class="col-md-4">Chọn phương thức</label>
                    <div class="col-md-8">
                        <select name="type_payment" id="type_payment" class="form-control" style="width: 100%">
                            @foreach ($method_payment as $key => $value)
                                <option value="{{ $key }}"> {{ $value }}</option>
                            @endforeach
                        </select>
                        <div class="message_zone"></div>
                    </div>
                </div>
                <div class="form-group div_holder_name">
                    <div class="col-sm-12">
                        <div class="col-sm-12">
                            <label>Phí giao dịch</label>
                            <div style="font-style:italic;">Có thể phát sinh cả 2 loại phí khi thanh toán bằng hình thức visa </div>
                        </div>
                    </div>
                </div>


                <div class="form-group">
                    <label class="col-md-4">Phí cố định (vnđ): </label>
                    <div class="col-md-8">
                        <input type="number" class="form-control" id="fixed_charge" name="fixed_charge">
                        <div class="message_zone"></div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-4">Phí giao dịch (%): </label>
                    <div class="col-md-8">
                        <input type="number" min="0" max="10" class="form-control" id="fee_percentage"
                            name="fee_percentage">
                        <div class="message_zone"></div>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-md-6 col-md-offset-4">
                        <button type="submit" class="btn btn-primary" id="add_method_payment">
                            <i class="fa fa-btn fa-check"></i> cập nhập
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
</div>
@include('building.modal.create_update_payment_vpbank')
