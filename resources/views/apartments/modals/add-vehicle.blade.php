<div id="add-vehicle" class="modal fade" role="dialog">
    <div class="modal-dialog  modal-lg">
        <!-- Modal content-->
        @if( in_array('admin.vehicles.insert',@$user_access_router))
            <form action="{{ route('admin.vehicles.insert') }}" method="post" id="form-add-verhicle"
                  class="form-validate form-horizontal">
                {{ csrf_field() }}
                <input type="hidden" name="hashtag">
                <div class="modal-content">
                    <div class="modal-header bg-primary">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">Thêm mới phương tiện</h4>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-danger alert_pop_add_vehicle" style="display: none;">
                            <ul></ul>
                        </div>
                        <div class="row">
                            <div class="col-sm-12">
                                <div class="form-group">
                                    <div class="col-sm-2">
                                        <label for="in-re_name">Tên Phương tiện</label>
                                    </div>
                                    <div class="col-sm-8">
                                        <input type="text" name="name" id="in-vc_name" class="form-control"
                                               placeholder="Tên phương tiện">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="col-sm-2">
                                        <label>Loại phương tiện</label>
                                    </div>
                                    <div class="col-sm-8">
                                        <select name="vehicle_category_id" id="select-vc_type" class="form-control">
                                            <option value="">Chọn phương tiện</option>
                                            @foreach($vehicleCateActive as $vehiclecate)
                                                <option value="{{$vehiclecate->id}}">{{$vehiclecate->name}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="col-sm-2">
                                        <label>Biển số</label>
                                    </div>
                                    <div class="col-sm-8">
                                        <input type="text" name="number" id="in-vc_vehicle_number" class="form-control"
                                               placeholder="Biển số (Nếu có)">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="col-sm-2">
                                        <label>Mã thẻ</label>
                                    </div>
                                    <div class="col-sm-8">
                                        <input type="text" name="code" id="code_vehicle" class="form-control" placeholder="Mã thẻ">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="col-sm-2">
                                        <label>Mô tả</label>
                                    </div>
                                    <div class="col-sm-8">
                                        <textarea name="description" id="textarea-vc_description" class="form-control"
                                                  cols="30" rows="5" placeholder="Mô tả phương tiện"></textarea>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="col-sm-2">
                                        <label>Ngày áp dụng tính phí</label>
                                    </div>
                                    <div class="col-sm-8">
                                        <input type="date"
                                               class="form-control pull-right date_picker"
                                               id="first_time_active"
                                               name="first_time_active">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="col-sm-2">
                                        <label>Mức ưu tiên tính phí</label>
                                    </div>
                                    <div class="col-sm-8">
                                        <select name="progressive_price_id" class="form-control" id="progressive_price_id">
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="col-sm-2">
                                        <label for="in-re_name">Trạng thái</label>
                                    </div>
                                    <div class="col-sm-8">
                                        <input type="checkbox" name="status" checked>
                                    </div>
                                </div>
                                <div class="form-group hidden">
                                    <div class="col-sm-2">
                                        <label>Căn hộ</label>
                                    </div>
                                    <div class="col-sm-8">
                                        <input type="hidden" class="form-control" id="in-ap_id" name="bdc_apartment_id"
                                               value="{{$id}}">
                                    </div>
                                </div>
                                <div class="form-control hidden">
                                    <input type="hidden" name="tab_current" value="apartment">
                                </div>
                                <div class="form-group hidden">
                                    <div class="col-sm-2">
                                        <label>Ảnh</label>
                                    </div>
                                    <div class="col-sm-8">
                                        <div class="input-group input-image" data-file="image">
                                            <input type="text" name="vc_image" id="in-vc_image" value=""
                                                   class="form-control"><span class="input-group-btn"><button type="button"
                                                                                                              class="btn btn-primary">Chọn</button></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger pull-right" data-dismiss="modal"><i
                                    class="fa fa-close"></i>&nbsp;&nbsp;Hủy</button>
                        <button type="button" class="btn btn-primary btn-js-action-vehicle" form="form-add-verhicle"
                                style="margin-right: 5px;"><i class="fa fa-save"></i>&nbsp;&nbsp;Xác nhận</button>
                    </div>
                </div>
            </form>
        @endif
    </div>
</div>
