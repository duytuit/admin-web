@extends('backend.layouts.master')

@section('content')
    <section class="content-header">
        <h1>
            Mẫu gửi mail dư dân
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Trang chủ</a></li>
        </ol>
    </section>

    <section class="content_new">
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <form class="form-horizontal" action="{{ route('admin.system.template_send_notification.send') }}" method="GET" id="" class="form-validate">
            {{ csrf_field() }}
            <div class="row">
                <div class="col-lg-8 col-md-8 col-sm-8 col-xs-8">
                    <div class="box">
                        <div class="box-body no-padding">
                            <div class="nav-tabs-custom no-margin">

                                <div class="tab-content">
                                    <!-- Thông tin cẩm nang -->
                                    <div class="tab-pane active" id="partner">
                                        <div class="row">
                                            <div class="col-sm-12 col-xs-12 form-group div_title">
                                                <label class="control-label">Tên mẫu email <span
                                                            class="text-danger">*</span></label>
                                                <select name="name_template_send_email" id="name_template_send_email" class="form-control">
                                                    <option value="100" selected>Tạo mới tài khoản</option>
                                                    <option value="69">Hóa đơn</option>
                                                    <option value="70">Nhắc phí</option>
                                                    <option value="4" >Tạo căn hộ mới</option>
                                                    <option value="20">Bài viết mới</option>
                                                    <option value="33">Xác thực OTP</option>
                                                    <option value="3" >Quên mật khẩu</option>
                                                    <option value="25">Đăng ký dịch vụ đối tác</option>
                                                </select>
                                            </div>
                                        </div>
                                                <div class="col-md-8">
                                                    <strong>Quý khách hàng có thể sử dụng một số các biên sau để cá nhân hóa template</strong>
                                                    <div class="box-body">
                                                        <div class="form-group ten_khach_hang">
                                                            <label for="ten_khach_hang" class="col-sm-3 control-label">Tên Khách hàng</label>
                                                            <div class="col-sm-9">
                                                                <input type="text" name="ten_khach_hang" class="form-control" id="ten_khach_hang" value="">
                                                            </div>
                                                        </div>
                                                        <div class="form-group toa_nha">
                                                            <label for="toa_nha" class="col-sm-3 control-label">Tòa nhà</label>
                                                            <div class="col-sm-9">
                                                                <input type="text" name="toa_nha" class="form-control" id="toa_nha" value="">
                                                            </div>
                                                        </div>
                                                        <div class="form-group can_ho">
                                                            <label for="can_ho" class="col-sm-3 control-label">Căn hộ</label>
                                                            <div class="col-sm-9">
                                                                <input type="text" name="can_ho" class="form-control" id="can_ho" value="">
                                                            </div>
                                                        </div>
                                                        <div class="form-group password">
                                                            <label for="password" class="col-sm-3 control-label">Mật khẩu</label>
                                                            <div class="col-sm-9">
                                                                <input type="text" name="password" class="form-control" id="password" >
                                                            </div>
                                                        </div>
                                                        <div class="form-group ky_hoa_don">
                                                            <label for="ky_hoa_don" class="col-sm-3 control-label">Kỳ hóa đơn</label>
                                                            <div class="col-sm-9">
                                                                <input type="text" name="ky_hoa_don" class="form-control" id="ky_hoa_don" value="">
                                                            </div>
                                                        </div>
                                                        <div class="form-group tong_tien">
                                                            <label for="tong_tien" class="col-sm-3 control-label">Tổng tiền</label>
                                                            <div class="col-sm-9">
                                                                <input type="text" name="tong_tien" class="form-control" id="tong_tien" value="">
                                                            </div>
                                                        </div>
                                                        <div class="form-group du_no_cuoi_ky">
                                                            <label for="du_no_cuoi_ky" class="col-sm-3 control-label">Dư nợ cuối kỳ</label>
                                                            <div class="col-sm-9">
                                                                <input type="text" name="du_no_cuoi_ky" class="form-control" id="du_no_cuoi_ky" value="">
                                                            </div>
                                                        </div>
                                                        <div class="form-group ngay_thanh_toan">
                                                            <label for="ngay_thanh_toan" class="col-sm-3 control-label">Ngày thanh toán</label>
                                                            <div class="col-sm-9">
                                                                <input type="text" name="ngay_thanh_toan" class="form-control" id="ngay_thanh_toan" >
                                                            </div>
                                                        </div>
                                                        <div class="form-group ma_hoa_don">
                                                            <label for="ma_hoa_don" class="col-sm-3 control-label">Mã hóa đơn</label>
                                                            <div class="col-sm-9">
                                                                <input type="text" name="ma_hoa_don" class="form-control" id="ma_hoa_don" value="">
                                                            </div>
                                                        </div>
                                                        <div class="form-group duong_dan_pdf">
                                                            <label for="duong_dan_pdf" class="col-sm-3 control-label">Đường dẫn pdf</label>
                                                            <div class="col-sm-9">
                                                                <input type="text" name="duong_dan_pdf" class="form-control" id="duong_dan_pdf" value="">
                                                            </div>
                                                        </div>
                                                        <div class="form-group noi_dung_nhac_no">
                                                            <label for="noi_dung_nhac_no" class="col-sm-3 control-label">Nội dung Nhắc phí</label>
                                                            <div class="col-sm-9">
                                                                <input type="text" name="noi_dung_nhac_no" class="form-control" id="noi_dung_nhac_no" >
                                                            </div>
                                                        </div>
                                                        <div class="form-group otp">
                                                            <label for="otp" class="col-sm-3 control-label">OTP</label>
                                                            <div class="col-sm-9">
                                                                <input type="text" name="otp" class="form-control" id="otp" value="">
                                                            </div>
                                                        </div>
                                                        <div class="form-group phone">
                                                            <label for="phone" class="col-sm-3 control-label">Số điện thoại</label>
                                                            <div class="col-sm-9">
                                                                <input type="text" name="phone" class="form-control" id="phone" >
                                                            </div>
                                                        </div>
                                                        <div class="form-group dich_vu_doi_tac">
                                                            <label for="dich_vu_doi_tac" class="col-sm-3 control-label">Dịch vụ đối tác</label>
                                                            <div class="col-sm-9">
                                                                <input type="text" name="dich_vu_doi_tac" class="form-control" id="dich_vu_doi_tac" value="">
                                                            </div>
                                                        </div>
                                                        <div class="form-group time_order">
                                                            <label for="time_order" class="col-sm-3 control-label">Thời gian đặt</label>
                                                            <div class="col-sm-9">
                                                                <input type="text" name="time_order" class="form-control" id="time_order" value="">
                                                            </div>
                                                        </div>
                                                        <div class="form-group mo_ta">
                                                            <label for="mo_ta" class="col-sm-3 control-label">Mô tả</label>
                                                            <div class="col-sm-9">
                                                                <input type="text" name="mo_ta" class="form-control" id="mo_ta" value="">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            <div class="row content">
                                                <div class="col-sm-12 col-xs-12 form-group div_title">
                                                    <label class="control-label">Nội dung mail <span class="text-danger">*</span></label>
                                                    <textarea name="content" rows="10" class="form-control mceEditor">
                                                    </textarea>
                                                </div>
                                            </div>
                                            <div class="row nguoi_nhan">
                                                <div class="col-sm-12 col-xs-12 form-group div_title">
                                                    <label for="nguoi_nhan" class="col-sm-3 control-label">Email Người Nhận</label>
                                                    <div class="col-sm-9">
                                                        <input type="email" name="nguoi_nhan" class="form-control" id="nguoi_nhan" value="">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer d-flex justify-content-center">
                                                <button type="submit" class="btn btn-primary add">Gửi email</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </section>
@endsection

@section('javascript')
    <!-- TinyMCE -->
    <script>
        $(document).ready(function() {
            if($("#name_template_send_email").val() == '100'){ // tài khoan mới
               $('.content').hide();
               $('.ky_hoa_don').hide();
               $('.du_no_cuoi_ky').hide();
               $('.noi_dung_nhac_no').hide();
               $('.phone').hide();
               $('.dich_vu_doi_tac').hide();
               $('.mo_ta').hide();
               $('.tong_tien').hide();
               $('.ngay_thanh_toan').hide();
               $('.ma_hoa_don').hide();
               $('.duong_dan_pdf').hide();
               $('.can_ho').hide();
               $('.otp').hide();
               $('.ten_khach_hang').show();
               $('.password').show();
               $('.toa_nha').show();
               $('.time_order').hide();
           }
        });
       $("#name_template_send_email").change(function (e) {
           e.preventDefault();
           if($(this).val() == '33'){ // OTP
               $('.otp').show();
               $('.ten_khach_hang').show();
               $('.ky_hoa_don').hide();
               $('.du_no_cuoi_ky').hide();
               $('.can_ho').hide();
               $('.noi_dung_nhac_no').hide();
               $('.phone').hide();
               $('.dich_vu_doi_tac').hide();
               $('.mo_ta').hide();
               $('.tong_tien').hide();
               $('.ngay_thanh_toan').hide();
               $('.ma_hoa_don').hide();
               $('.duong_dan_pdf').hide();
               $('.content').hide();
               $('.password').hide();
               $('.time_order').hide();
           }
           if($(this).val() == '20'){ // Bài viêt
               $('.content').show();
               $('.ky_hoa_don').hide();
               $('.du_no_cuoi_ky').hide();
               $('.can_ho').hide();
               $('.noi_dung_nhac_no').hide();
               $('.phone').hide();
               $('.dich_vu_doi_tac').hide();
               $('.mo_ta').hide();
               $('.tong_tien').hide();
               $('.ngay_thanh_toan').hide();
               $('.ma_hoa_don').hide();
               $('.duong_dan_pdf').hide();
               $('.otp').hide();
               $('.ten_khach_hang').hide();
               $('.password').hide();
               $('.toa_nha').hide();
               $('.time_order').hide();
           }
           if($(this).val() == '3'){ // quên mật khẩu
               $('.content').hide();
               $('.ky_hoa_don').hide();
               $('.du_no_cuoi_ky').hide();
               $('.can_ho').hide();
               $('.noi_dung_nhac_no').hide();
               $('.phone').hide();
               $('.dich_vu_doi_tac').hide();
               $('.mo_ta').hide();
               $('.tong_tien').hide();
               $('.ngay_thanh_toan').hide();
               $('.ma_hoa_don').hide();
               $('.duong_dan_pdf').hide();
               $('.otp').hide();
               $('.ten_khach_hang').show();
               $('.password').show();
               $('.toa_nha').hide();
               $('.time_order').hide();
           }
           if($(this).val() == '4'){ // căn hộ mới
               $('.content').hide();
               $('.ky_hoa_don').hide();
               $('.du_no_cuoi_ky').hide();
               $('.noi_dung_nhac_no').hide();
               $('.phone').hide();
               $('.dich_vu_doi_tac').hide();
               $('.mo_ta').hide();
               $('.tong_tien').hide();
               $('.ngay_thanh_toan').hide();
               $('.ma_hoa_don').hide();
               $('.duong_dan_pdf').hide();
               $('.password').hide();
               $('.otp').hide();
               $('.can_ho').show();
               $('.ten_khach_hang').show();
               $('.toa_nha').show();
               $('.time_order').hide();
           }
           if($(this).val() == '100'){ // tài khoan mới
               $('.content').hide();
               $('.ky_hoa_don').hide();
               $('.du_no_cuoi_ky').hide();
               $('.noi_dung_nhac_no').hide();
               $('.phone').hide();
               $('.dich_vu_doi_tac').hide();
               $('.mo_ta').hide();
               $('.tong_tien').hide();
               $('.ngay_thanh_toan').hide();
               $('.ma_hoa_don').hide();
               $('.duong_dan_pdf').hide();
               $('.can_ho').hide();
               $('.otp').hide();
               $('.ten_khach_hang').show();
               $('.password').show();
               $('.toa_nha').show();
               $('.time_order').hide();
           }
           if($(this).val() == '70'){ // Nhăc nợ
               $('.content').hide();
               $('.ky_hoa_don').show();
               $('.du_no_cuoi_ky').show();
               $('.can_ho').show();
               $('.noi_dung_nhac_no').show();
               $('.phone').hide();
               $('.dich_vu_doi_tac').hide();
               $('.mo_ta').hide();
               $('.tong_tien').hide();
               $('.ngay_thanh_toan').hide();
               $('.ma_hoa_don').hide();
               $('.duong_dan_pdf').hide();
               $('.otp').hide();
               $('.ten_khach_hang').show();
               $('.password').hide();
               $('.toa_nha').show();
               $('.time_order').hide();
           }
           if($(this).val() == '69'){ // Hóa đơn
               $('.content').hide();
               $('.ky_hoa_don').show();
               $('.du_no_cuoi_ky').hide();
               $('.can_ho').show();
               $('.noi_dung_nhac_no').hide();
               $('.phone').hide();
               $('.dich_vu_doi_tac').hide();
               $('.mo_ta').hide();
               $('.tong_tien').show();
               $('.ngay_thanh_toan').show();
               $('.ma_hoa_don').show();
               $('.duong_dan_pdf').show();
               $('.otp').hide();
               $('.ten_khach_hang').show();
               $('.password').hide();
               $('.toa_nha').show();
               $('.time_order').hide();
           }
           if($(this).val() == '25'){ // Đối tác
               $('.content').hide();
               $('.ky_hoa_don').hide();
               $('.du_no_cuoi_ky').hide();
               $('.can_ho').hide();
               $('.noi_dung_nhac_no').hide();
               $('.phone').show();
               $('.dich_vu_doi_tac').show();
               $('.mo_ta').show();
               $('.tong_tien').hide();
               $('.ngay_thanh_toan').hide();
               $('.ma_hoa_don').hide();
               $('.duong_dan_pdf').hide();
               $('.otp').hide();
               $('.ten_khach_hang').show();
               $('.password').hide();
               $('.toa_nha').show();
               $('.time_order').show();
           }
       })
    </script>
@endsection
