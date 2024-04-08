<div class="modal fade" id="create_apartment_handover" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" style="padding-right:0px;display:none" aria-hidden="true">
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
                <h5 class="modal-title" style="margin-top: 2px;">Thêm khách hàng bàn đủ điều kiện BGCH</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="margin-top: -20px;margin-right: 10px;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" class="col-sm-12" style="padding:0;">
                <div>
                    <ul id="apartment-handover-errors"></ul>
                </div>
                <form class="form-horizontal" action="" id="modal-apartment-handover">
                    <div class="box-body">
                        <div class="form-group" style="padding: 0 45px;">
                        <div class="form-group">
                              <div class="row">
                                <div class="col-sm-6">
                                    <div class="row">
                                        <div class="col-sm-12">
                                            <label for="timeorder" class="control-label"><span style="color:red;font-size: 18px;">*</span>Tòa nhà</label>
                                        </div>
                                    </div>
                                    <select id="ip_place_id_create" class="form-control" style="width: 100%;">
                                        <option value="">Chọn tòa nhà</option>
                                    </select>
                                </div>
                                <div class="col-sm-6">
                                   <div class="row">
                                        <div class="col-sm-12">
                                             <label for="timeorder" class="control-label"><span style="color:red;font-size: 18px;">*</span>Căn hộ</label>
                                        </div>
                                    </div>
                                    <select name="create_bdc_apartment_id" id="ip_apartment_create" style="width: 100%;" class="form-control">
                                        <option value="">Căn hộ</option>
                                    </select>
                                </div>
                              </div>
                            </div>
                             <div class="form-group">
                                <label for="recipient-name" class="control-label"><span style="color:red;font-size: 18px;">*</span> Tên khách hàng:</label>
                                <input type="text" name="name" class="form-control" id="name-customer" value="">
                            </div>
                             <div class="form-group">
                                <label for="recipient-name" class="control-label"><span style="color:red;font-size: 18px;">*</span> Điện thoại:</label>
                                <input type="text" name="phone" class="form-control" id="phone-customer" value="">
                            </div>
                             <div class="form-group">
                                <label for="recipient-name" class="control-label"><span style="color:red;font-size: 18px;">*</span> Email:</label>
                                <input type="email" name="email" class="form-control" id="email" value="">
                            </div>
                            <div class="form-group">
                                <label for="recipient-name" class="control-label"><span style="color:red;font-size: 18px;">*</span> Địa chỉ:</label>
                                 <textarea class="form-control" id="address" name="address" rows="6"></textarea>
                            </div>
                            <div class="form-group">
                              <div class="row">
                                <div class="col-sm-4">
                                    <div class="row">
                                        <div class="col-sm-12">
                                            <label for="timeorder" class="control-label"><span style="color:red;font-size: 18px;">*</span>Mật khẩu</label>
                                        </div>
                                    </div>
                                    <input type="password" name="password" class="form-control" id="password" value="">
                                </div>
                                <div class="col-sm-4">
                                   <div class="row">
                                        <div class="col-sm-12">
                                             <label for="timeorder" class="control-label"><span style="color:red;font-size: 18px;">*</span>Ngày dự kiến bàn giao</label>
                                        </div>
                                    </div>
                                    <input type="text" class="form-control date_picker" name="from_date" id="from_date" value="" placeholder="Ngày dự kiến" autocomplete="off">
                                </div>
                                <div class="col-sm-4">
                                   <div class="row">
                                        <div class="col-sm-12">
                                             <label for="timeorder" class="control-label"><span style="color:red;font-size: 18px;">*</span>Trạng thái xác nhận</label>
                                        </div>
                                    </div>
                                    <select class="form-control" id="create_status_confirm" name="create_status_confirm" style="width: 100%">
                                        @foreach($list_apartment_handover as $key => $value)
                                            <option value="{{$value['text']}}" {{ $value['text'] == 2 ? 'selected' : '' }}>{{$value['value']}}</option>
                                        @endforeach
                                    </select>
                                </div>
                              </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer d-flex justify-content-center">
                        <button type="button" class="btn btn-primary add-apartment-handover">Thêm mới</button>
                        <button type="button" class="btn btn-warning" data-dismiss="modal">Hủy</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<style>
    @media (min-width: 768px) {
        .shift {
            width: 600px!important;
            margin: 30px auto;
        }
    }
</style>
