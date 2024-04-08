<div class="modal fade" id="send_notify_apartment" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" style="padding-right:0px;display:none" aria-hidden="true">
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
                <h5 class="modal-title" style="margin-top: 2px;">Gửi thông báo bàn giao căn hộ tới khách hàng</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="margin-top: -20px;margin-right: 10px;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" class="col-sm-12" style="padding:0;">
                <div>
                    <ul id="tempsample-errors"></ul>
                </div>
                <form action="" id="create_form_notify_apartment">
                    <div class="row">
                        <div class="col-sm-8">
                                <div class="box-body">
                                        <div class="form-group">
                                            <label class="control-label required">Tiêu đề</label>
                                            <textarea name="title" placeholder="Tiêu đề" rows="1" class="form-control input-text" required></textarea>
                                        
                                        </div>
                                        <div class="form-group">
                                            <label class="control-label">Nội dung gửi email</label>
                                            <textarea name="content" placeholder="Nội dung" rows="10" class="form-control mceEditor"></textarea>
                                        </div>
                                        <div class="form-group">
                                            <label class="control-label">Mẫu gửi sms</label>
                                            <select name="custom_template_email" id="custom_template_email" class="form-control">
                                                <option value="mac_dinh" selected>Mẫu mặc định</option>
                                                <option value="noi_dung">Nội dung</option>
                                            </select>
                                        </div>
                                        <div class="form-group" id="form_noi_dung">
                                            <textarea class="form-control" name="description" rows="6"></textarea>
                                            <div>Tham số: <strong>@khachhang, @canho, @ngaybangiao</strong></div>
                                        </div>
                                        <div class="form-group" id="form_mac_dinh">
                                            <div>Kinh moi quy KH <strong>@khachhang</strong> toi nhan ban giao can ho <strong>@canho</strong> vao ngay <strong>@ngaybangiao</strong>. Tran trong ./.</div>
                                        </div>
                                </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="box box-primary">
                                <div class="box-header with-border">
                                    Thiết lập thông báo
                                </div>
                                <div class="box-body">
                                    <div class="form-group">
                                        <label class="control-label">Gửi đến</label>
                                        <div class="notify-group">
                                            <label class="notify-label">
                                                <input type="checkbox" name="notify[send_mail]" value="1" class="iCheck">
                                                Email
                                            </label>
                                            <label class="notify-label">
                                                <input type="checkbox" name="notify[send_sms]" value="1" class="iCheck" >
                                                SMS
                                            </label>
                                            <label class="notify-label">
                                                <input type="checkbox" name="notify[send_app]" value="1" class="iCheck">
                                                App Notify
                                            </label>
                                        </div>
                                    </div>

                                    <div class="form-group" id="private" >
                                        <label class="control-label">Căn hộ nhận tin thông báo</label>
                                        <div class="notify-group">
                                            <label class="control-label">Căn hộ</label>
                                            <div id="notify-customer">
                                                <select id="ip_apartment_send_notify" style="width:100%" multiple class="form-control select2">
                                                    <option value="">Căn hộ</option>
                                                    @foreach($customers_v2 as $key => $value)
                                                        <option value="{{$value->id}}">{{@$value->bdcApartment->name}}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label">Trạng thái</label>
                                        <div>
                                            <label class="switch">
                                                <input type="checkbox" name="status" value="1" />
                                                <span class="slider round"></span>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <button type="button" class="btn btn-success post_save">Lưu</button>
                                        &nbsp;
                                        <a href="" class="btn btn-danger" form="form-posts" value="submit">Quay lại</a>
                                    </div>
                                </div>
                            </div>
                        </div>
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
            width: 1200px!important;
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
