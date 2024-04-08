<div class="modal fade" id="showModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Chi tiết dịch vụ</h4>
            </div>
            <div class="modal-body">
                @php
                    $apartmentServicePrice = $billDetail->apartmentServicePrice;
                @endphp
                <form id="edit-debit-detail" data-action="{{ route('admin.v2.debit.detail-service.update',['id' => $billDetail->id]) }}" method="PUT">
                    <input type="hidden" value="{{ $billDetail->id }}" name="id">
                    <input type="hidden" value=" {{ @$apartmentServicePrice->service->ngay_chuyen_doi }}" id="ngay_chuyen_doi">
                    <input type="hidden" value=" {{ @$apartmentServicePrice->price }}" id="apartmentServicePrice">
                    <input type="hidden" value=" {{ @$billDetail->discount }}" id="apartmentServiceDiscount">
                    <input type="hidden" value=" {{ @$billDetail->sumery }}" id="apartmentServiceSumery">
                    <input type="hidden" value=" {{ @$billDetail->paid }}" id="apartmentServicePaid">
                    <input type="hidden" value=" {{ @$apartmentServicePrice->bdc_price_type_id }}" id="apartmentServiceType">
                    <input type="hidden" value=" {{date('Y/m/d',strtotime(@$apartmentServicePrice->last_time_pay))}}" id="last_time_pay">
                    <div class="form-group">
                         <div class="row form-group">
                            <label class="col-sm-4 control-label">Kỳ tháng</label>

                            <div class="col-sm-8">
                                 <input type="text" class="form-control" name="cycle_name" value="{{ $billDetail->cycle_name }}" readonly>
                            </div>
                        </div>
                        <div class="row form-group">
                            <label class="col-sm-4 control-label">Dịch vụ</label>

                            <div class="col-sm-8">
                                {{ @$apartmentServicePrice->service->name }}
                            </div>
                        </div>
                        <div class="row form-group div_price">
                            <label class="col-sm-4 control-label">Đơn giá</label>

                            <div class="col-sm-8">
                                <input type="number" class="form-control price" name="price" min="0" value="{{ $billDetail->price }}">
                                <div class="message_zone"></div>
                            </div>
                        </div>
                        <div class="row form-group">
                            <label class="col-sm-4 control-label">Hạn thanh toán</label>
                            <div class="col-sm-8">
                                <div class="input-group date">
                                    <div class="input-group-addon">
                                        <i class="fa fa-calendar"></i>
                                    </div>
                                    <input type="date" class="form-control pull-right date_picker" name="deadline" value="{{ date('Y-m-d', strtotime($billDetail->bill->deadline)) }}">
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
                                    <input type="date" class="form-control pull-right date_picker change_from_date" name="from_date" value="{{ date('Y-m-d', strtotime($billDetail->from_date)) }}">
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
                                    <input type="date" class="form-control pull-right date_picker change_to_date" name="to_date" value="{{ date('Y-m-d', strtotime($billDetail->to_date)) }}">
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
                       
                        @if(\Auth::user()->isadmin == 1)
                          
                            <div class="row form-group">
                                <label class="col-sm-4 control-label">Phát sinh</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="phatsinh" value="{{ number_format($billDetail->sumery + $billDetail->discount) }}">
                                </div>
                            </div>
                            <div class="row form-group">
                                <label class="col-sm-4 control-label">Giảm trừ</label>
    
                                <div class="col-sm-8">
                                    <input type="text" class="form-control customer_paid_string discount" name="discount" value="{{ number_format($billDetail->discount) }}">
                                </div>
                            </div>
                            <div class="row form-group">
                                <label class="col-sm-4 control-label">Thành tiền</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control customer_paid_string sumery" name="sumery" value="{{ number_format($billDetail->sumery) }}">
                                </div>
                            </div>
                            <div class="row form-group">
                                <label class="col-sm-4 control-label">Thanh toán</label>

                                <div class="col-sm-8">
                                    <input type="text" class="form-control customer_paid_string paid" name="paid" value="{{ number_format($billDetail->paid) }}">
                                </div>
                            </div>
                        @else
                            <div class="row form-group">
                                <label class="col-sm-4 control-label">Phát sinh</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="phatsinh" readonly value="{{ number_format($billDetail->sumery + $billDetail->discount) }}">
                                </div>
                            </div>
                            <div class="row form-group">
                                <label class="col-sm-4 control-label">Giảm trừ</label>
    
                                <div class="col-sm-8">
                                    <input type="text" class="form-control customer_paid_string discount" name="discount" value="{{ number_format($billDetail->discount) }}">
                                </div>
                            </div>
                            <div class="row form-group">
                                <label class="col-sm-4 control-label">Thành tiền</label>

                                <div class="col-sm-8">
                                    <input type="text" class="form-control customer_paid_string sumery" readonly name="sumery" value="{{ number_format($billDetail->sumery) }}">
                                </div>
                            </div>
                            <div class="row form-group">
                                <label class="col-sm-4 control-label">Thanh toán</label>

                                <div class="col-sm-8">
                                    <input type="text" class="form-control customer_paid_string" readonly name="paid" value="{{ number_format($billDetail->paid) }}">
                                </div>
                            </div>
                        @endif
                        <div class="row form-group">
                            <label class="col-sm-4 control-label">Lý do giảm trừ</label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" name="discount_note" value="{{ $billDetail->discount_note }}">
                            </div>
                        </div>
                        <div class="row form-group">
                            <label class="col-sm-4 control-label">Ghi chú</label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" name="note" value="{{ $billDetail->note }}">
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