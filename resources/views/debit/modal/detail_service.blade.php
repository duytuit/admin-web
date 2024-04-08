<div class="modal fade" id="showModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Chi tiết dịch vụ</h4>
            </div>
            <div class="modal-body">
                <form id="edit-debit-detail" data-action="{{ route('admin.debit.detail-service.update',['id' => $billDetail->id]) }}" method="PUT">
                    <input type="hidden" value="{{ $billDetail->id }}" name="id">
                    <div class="form-group">
                         <div class="row form-group">
                            <label class="col-sm-4 control-label">Kỳ tháng</label>

                            <div class="col-sm-8">
                                 <input type="text" class="form-control" name="cycle_name" value="{{ $billDetail->cycle_name }}">
                            </div>
                        </div>
                        <div class="row form-group">
                            <label class="col-sm-4 control-label">Dịch vụ</label>

                            <div class="col-sm-8">
                                {{ $billDetail->service->name }}
                            </div>
                        </div>
                        <div class="row form-group div_price">
                            <label class="col-sm-4 control-label">Đơn giá</label>

                            <div class="col-sm-8">
                                <input type="number" class="form-control" name="price" min="0" value="{{ $billDetail->price }}">
                                <div class="message_zone"></div>
                            </div>
                        </div>
                        <div class="row form-group div_from_date">
                            <label class="col-sm-4 control-label">Hạn thanh toán</label>
                            <div class="col-sm-8">
                                <div class="input-group date">
                                    <div class="input-group-addon">
                                        <i class="fa fa-calendar"></i>
                                    </div>
                                    <input type="date" class="form-control pull-right" name="deadline" value="{{ date('Y-m-d', strtotime(@$billDetail->bill->deadline)) }}">
                                </div>
                                <div class="message_zone"></div>
                            </div>
                        </div>
                        <div class="row form-group div_from_date">
                            <label class="col-sm-4 control-label">Thời gian bắt đầu</label>

                            <div class="col-sm-8">
                                <div class="input-group date">
                                    <div class="input-group-addon">
                                        <i class="fa fa-calendar"></i>
                                    </div>
                                    <input type="date" class="form-control pull-right" name="from_date" value="{{ date('Y-m-d', strtotime($billDetail->from_date)) }}">
                                </div>
                                <div class="message_zone"></div>
                            </div>
                        </div>
                        <div class="row form-group div_to_date">
                            <label class="col-sm-4 control-label">Thời gian chốt</label>
                            <div class="col-sm-8">
                                <div class="input-group date">
                                    <div class="input-group-addon">
                                        <i class="fa fa-calendar"></i>
                                    </div>
                                    <input type="date" class="form-control pull-right" name="to_date" value="{{ date('Y-m-d', strtotime($billDetail->to_date)) }}">
                                    <div class="message_zone"></div>
                                </div>
                            </div>
                        </div>
                         <div class="row form-group">
                            <label class="col-sm-4 control-label">Thời gian</label>
                            <div class="col-sm-8">
                                 @if($billDetail->bdc_price_type_id==2)
                                    <td>{{ date('d/m/Y', strtotime($billDetail->from_date)).' - '.date('d/m/Y', strtotime($billDetail->to_date)) }}</td>
                                    @else
                                    <td>{{ date('d/m/Y', strtotime($billDetail->from_date)).' - '.date('d/m/Y', strtotime($billDetail->to_date  . ' - 1 days')) }}</td>
                                    @endif
                            </div>
                        </div>
                        <hr>
                          <input type="hidden" name="quantity" value="{{ $billDetail->quantity }}">
                            <div class="row form-group">
                                <label class="col-sm-4 control-label">Previous Owed</label>

                                <div class="col-sm-8">
                                    <input type="number" class="form-control" name="previous_owed" value="{{ $billDetail->previous_owed }}">
                                </div>
                            </div>
                            <div class="row form-group">
                                <label class="col-sm-4 control-label">Paid</label>

                                <div class="col-sm-8">
                                    <input type="text" class="form-control customer_paid_string" name="paid" value="{{ number_format($billDetail->paid) }}">
                                </div>
                            </div>
                            <div class="row form-group">
                                <label class="col-sm-4 control-label">Sumery</label>

                                <div class="col-sm-8">
                                    <input type="text" class="form-control customer_paid_string" name="sumery" value="{{ number_format($billDetail->sumery) }}">
                                </div>
                            </div>
                            <div class="row form-group">
                                <label class="col-sm-4 control-label">New Sumery</label>

                                <div class="col-sm-8">
                                    <input type="text" class="form-control customer_paid_string" name="new_sumery" value="{{ number_format($billDetail->new_sumery) }}">
                                </div>
                            </div>
                            <div class="row form-group">
                                <label class="col-sm-4 control-label">Version</label>

                                <div class="col-sm-8">
                                    <input type="text" class="form-control customer_paid_string" name="version" value="{{ number_format($billDetail->version) }}">
                                </div>
                            </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer d-flex justify-content-center">
                <button type="button" class="btn btn-lg btn-primary" id="update-debit-detail">Lưu</button>
                <button type="button" class="btn btn-lg btn-warning" data-dismiss="modal">Hủy</button>
            </div>
        </div>
    </div>
</div>