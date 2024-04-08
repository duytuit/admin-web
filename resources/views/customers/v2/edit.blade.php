@extends('backend.layouts.master')

@section('content')

    <section class="content-header">
        <h1>
            Sửa cư dân
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('admin.home') }}"><i class="fa fa-home"></i> Trang chủ</a></li>
            <li class="active">Sửa cư dân</li>
        </ol>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-heading">Sửa cư dân</div>
                    <div class="panel-body">
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        <div class="form-group">
                        <form action="" method="post" id="form-edit-customers" enctype="multipart/form-data">
                                {{ csrf_field() }}
                                <input type="hidden" name="url_Apartment" value="{{ $urlApartment }}">
                                <input type="hidden" name="user_id" id="user_id" value="{{ $user_info->user_id }}">
                                <input type="hidden" name="user_info_id" id="user_info_id" value="{{ $user_info->id }}">
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="ip-name">Tên Cư dân</label>
                                        <input type="text" name="full_name" id="ip-name" class="form-control" placeholder="Tên cư dân" value="{{ $user_info->full_name ?? old('full_name') ?? ''}}">
                                    </div>
                                    <div class="form-group">
                                        <label for="phone_contact">Số điện thoại liên hệ</label>
                                        <input type="text" name="phone_contact" id="phone_contact" class="form-control" placeholder="Số điện thoại liện hệ" value="{{ $user_info->phone_contact ?? old('phone_contact') ?? ''}}">
                                    </div>
                                    <div class="form-group">
                                        <label for="email_contact">Email</label>
                                        <input type="email" name="email_contact" id="email_contact" class="form-control" placeholder="Email liên hệ" value="{{ $user_info->email_contact ?? old('email_contact') ?? ''}}">
                                    </div>
                                    <div class="form-group">
                                        <label for="ip-address">Địa chỉ</label>
                                        <input type="text" name="address" id="ip-address" class="form-control" placeholder="Địa chỉ" value="{{ $user_info->address ?? old('address') ?? ''}}">
                                    </div>
                                    <div class="form-group">
                                        <label for="ip-cmt">CMND/hộ chiếu</label>
                                        <input type="text" name="cmt_number" id="ip-cmt" class="form-control" placeholder="CMND/hộ chiếu" value="{{ $user_info->cmt_number ?? old('cmt_number') ?? ''}}">
                                    </div>
                                    <div class="form-group">
                                        <label for="ip-cmt_nc">Ngày cấp</label>
                                        <?php
                                                $cmt_date = NULL;
                                                if ($user_info->cmt_date <> '0000-00-00 00:00:00'){
                                                    $cmt_date = date('d-m-Y', strtotime($user_info->cmt_date));
                                                }
                                            ?>
                                        <div class="input-group datetimepicker" data-format="DD-MM-YYYY">
                                            <input type="text" name="cmt_date" id="ip-cmt_nc" class="form-control" placeholder="Ngày cấp" value="{{ $cmt_date ?? old('cmt_date') ?? ''}}"><span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="cmt_address">Địa chỉ trên chứng minh thư</label>
                                        <input type="text" name="cmt_address" id="cmt_address" class="form-control" placeholder="Địa chỉ trên chứng minh thư" value="{{ $user_info->cmt_address ?? old('cmt_address') ?? ''}}">
                                    </div>
                                    <div class="form-group">
                                        <label for="cmt_province">Nơi cấp</label>
                                        <input type="text" name="cmt_province" id="cmt_provi" class="form-control" placeholder="Nơi cấp" value="{{ $user_info->cmt_province ?? old('cmt_province') ?? ''}}">
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="ip-birthday">Ngày sinh</label>
                                        <div class="input-group datetimepicker" data-format="DD-MM-YYYY">
                                             <?php
                                                $birthday = NULL;
                                                if ($user_info->birthday <> NULL){
                                                    $birthday = date('d-m-Y', strtotime($user_info->birthday));
                                                }
                                            ?>
                                            <input type="text" name="birthday" id="ip-birthday" class="form-control" placeholder="Ngày sinh" value="{{ $birthday ?? old('birthday') ?? ''}}">
                                            <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="ip-gender">Giới tính</label>
                                        <?php $gender = $user_info->gender ?? old('gender') ?? ''; ?>
                                        <div class="form-control" style="border: none;">
                                            <input type="radio" name="gender" id="ip-genderm" placeholder="Giới tính" value="1" @if($gender == 1) checked @endif> Nam
                                            <input type="radio" name="gender" id="ip-genderf" placeholder="Giới tính" value="2" @if($gender == 2) checked @endif> Nữ
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label for="ip-type">Ảnh đại diện</label>
                                        <div class="input-group">
                                            <input type="file" name="avatar" accept="image/png, image/gif, image/jpeg" value="{{$user_info->avatar ?? old('avatar') ?? ''}}" id="in-avatar" class="form-control"><span class="input-group-btn"><button type="button" id="clear_image" class="btn btn-primary">Xoá</button></span>
                                        </div>
                                         <img src="{{ old('avatar', $user_info->avatar) }}"  id="choose_avatar"  alt="" style="max-width: 200px;" />
                                    </div>
                                    <div class="form-group">
                                        <label for="cmt_img">Ảnh chứng minh thư</label>
                                        <div class="input-group">
                                            <input type="file" name="cmt_img" accept="image/png, image/gif, image/jpeg" value="{{$user_info->cmt_img ?? old('cmt_img') ?? ''}}" id="in-cmt_img" class="form-control"><span class="input-group-btn"><button type="button" id="clear_image_cmt_img" class="btn btn-primary">Xoá</button></span>
                                        </div>
                                         <img src="{{ old('cmt_img', $user_info->cmt_img) }}"  id="choose_avatar_cmt_img"  alt="" style="max-width: 200px;" />
                                    </div>

                                </div>
                                <div class="clearfix"></div>
                                <div class="row box_list_customer">
                                    <p style="font-weight: bold;font-size: 20px;margin-left: 28px;">Thông tin đăng nhập</p>
                                    <div class="col-sm-12">
                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <label for="phone">Số điện thoại</label>
                                                <input type="text" name="phone" id="phone" class="form-control" placeholder="Số điện thoại" value="{{ @$user_info->user->phone ?? old('phone') ?? ''}}">
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <label for="email">Email</label>
                                                <input type="email" name="email" id="email" class="form-control" placeholder="Email" value="{{ $user_info->user->email ?? old('email') ?? ''}}">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-2">
                                    <div class="form-group">
                                        <a href="{{route('admin.v2.customers.index')}}" class="btn btn-sm btn-default"><i class="bx bx-arrow-back"></i><span class="align-middle ml-25">Quay lại</span></a>
                                        <button type="submit" class="btn btn-sm btn-success" title="Cập nhật" form="form-edit-customers">
                                            <i class="fa fa-save"></i>&nbsp;Cập nhật
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="panel panel-default">
                    <div class="panel-body">
                        <p style="font-weight: bold;font-size: 20px;margin-left: 28px;">Danh sách căn hộ <span><button type="button"  data-toggle="modal" data-target="#modal-add-user-apartment" class="btn btn-primary">Thêm căn hộ</button></span></p>
                        @foreach($bdcCustomers as $key => $item)
                            <div class="col-sm-12 list_cus_apartment">
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label for="ip-cmt_nc">căn hộ</label>
                                        <input type="text" readonly  apartment_id="{{@$item->bdcApartment->id}}" class="form-control apartment_id"  value="{{ @$item->bdcApartment->name ?? '' }}">
                                    </div>
                                </div>
                                <div class="col-sm-3">
                                    <div class="form-group">
                                        <?php $type = $item->type ?? old('type') ?? '';?>
                                        <label for="ip-type">Tình trạng</label>
                                            <select name="type" class="form-control choose_type_apartment">
                                                <option value="0" @if($type == 0) selected @endif>Chủ hộ</option>
                                                <option value="1" @if($type == 1) selected @endif>Vợ/Chồng</option>
                                                <option value="2" @if($type == 2) selected @endif>Con</option>
                                                <option value="3" @if($type == 3) selected @endif>Bố mẹ</option>
                                                <option value="4" @if($type == 4) selected @endif>Anh chị em</option>
                                                <option value="5" @if($type == 5) selected @endif>Khác</option>
                                                <option value="6" @if($type == 6) selected @endif>Khách thuê</option>
                                                <option value="8" @if($type == 8) selected @endif>Khách thuê</option>
                                                <option value="7" @if($type == 7) selected @endif>Chủ hộ cũ</option>
                                            </select>
                                    </div>
                                </div>
                                <div class="col-sm-3">
                                    <div class="form-group">
                                        <label for="ip-cmt_nc">Tòa Nhà</label>
                                        <?php 
                                       $building_name = App\Models\Building\Building::where('id',@$item->building_id)->first();
                                        ?>
                                        <input type="text" readonly  class="form-control apartment_id"  value="{{ $building_name ->name ?? '' }}">
                                    </div>
                                </div>
                                <div class="col-sm-2">
                                    <div class="form-group" style="margin-top: 25px;">
                                        <button type="button" onclick="update_user_apartment(this)" class="btn btn-primary">Sửa</button>
                                        <button type="button" onclick="del_user_apartment(this)" class="btn btn-danger">Xoá</button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        <div id="modal-add-user-apartment" class="modal fade" role="dialog">
            <div class="modal-dialog custom-dialog">
                <!-- Modal content-->
                <form id="add-user-apartment" >
                    {{ csrf_field() }}
                    <div class="modal-content">
                        <div class="modal-header bg-primary">
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                            <h4 class="modal-title">Thêm căn hộ</h4>
                        </div>
                        <div class="modal-body">
                            <div class="alert alert-danger alert_pop_add_resident" style="display: none;">
                                <ul></ul>
                            </div>
                            <div class="row">
                                <div class="col-sm-12">
                                    <div class="col-sm-6">
                                        <select name="bdc_apartment_id" id="aprt_id"  class="form-control" style="width: 100%"> 
                                            <option value="">Chọn căn hộ</option>
                                        </select>
                                    </div>
                                    <div class="col-sm-6">
                                        <select name="type_apartment" id="type_apartment" class="form-control">
                                            <option value="0">Chủ hộ</option>
                                            <option value="1">Vợ/Chồng</option>
                                            <option value="2">Con</option>
                                            <option value="3">Bố mẹ</option>
                                            <option value="4">Anh chị em</option>
                                            <option value="5">Khác</option>
                                            <option value="6">Khách thuê</option>
                                            <option value="7">Chủ hộ cũ</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-danger pull-right" data-dismiss="modal"><i class="fa fa-close"></i>&nbsp;&nbsp;Hủy</button>
                            <button type="submit" class="btn btn-primary save_add_apartment" style="margin-right: 5px;"><i class="fa fa-save"></i>&nbsp;&nbsp;Xác nhận</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </section>

@endsection
@section('stylesheet')
    <link rel="stylesheet" href="/adminLTE/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css" />
@endsection
@section('javascript')
    <script src="/adminLTE/plugins/moment/moment.min.js"></script>
    <script src="/adminLTE/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js"></script>

    <script>
        $(function () {
            get_data_select_apartment({
                object: '#aprt_id',
                url: '{{ url('admin/apartments/ajax_get_apartment') }}',
                data_id: 'id',
                data_text: 'name',
                title_default: 'Chọn căn hộ'
            });
            function get_data_select_apartment(options) {
                $(options.object).select2({
                    ajax: {
                        url: options.url,
                        dataType: 'json',
                        data: function(params) {
                            var query = {
                                search: params.term,
                            }
                            return query;
                        },
                        processResults: function(json, params) {
                            var results = [{
                                id: '',
                                text: options.title_default
                            }];

                            for (i in json.data) {
                                var item = json.data[i];
                                results.push({
                                    id: item[options.data_id],
                                    text: item[options.data_text]
                                });
                            }
                            return {
                                results: results,
                            };
                        },
                        minimumInputLength: 3,
                    }
                });
            }
        });
        sidebar('apartments', 'create');
        $('#clear_image').click(function (e) {
            e.preventDefault();
            $('#in-avatar').val('');
            $('#choose_avatar').attr('src', '').height(0);
        });
        $('#in-avatar').on('change', function(e) {
            if (e.target.files[0]) {
                var reader = new FileReader();
                reader.onload = function (e) {
                $('#choose_avatar').attr('src', e.target.result).height(200);
                };
                reader.readAsDataURL(e.target.files[0]);
            }
            e.preventDefault();
        });
        $('#clear_image_cmt_img').click(function (e) {
            e.preventDefault();
            $('#in-cmt_img').val('');
            $('#choose_avatar_cmt_img').attr('src', '').height(0);
        });
        $('.save_add_apartment').click(function (e) {
            e.preventDefault();
            $.post('{{ url('/admin/v2/customers/add_user_apartment') }}', {
                        apartment_id:   $('#aprt_id').val(),
                        user_info_id:  $('#user_info_id').val(),
                        type:   $('#type_apartment').val(),
                    }, function(data) {
                        toastr.success(data.message);
                        location.reload()
                     });
        });
        $('#in-cmt_img').on('change', function(e) {
            if (e.target.files[0]) {
                var reader = new FileReader();
                reader.onload = function (e) {
                $('#choose_avatar_cmt_img').attr('src', e.target.result).height(200);
                };
                reader.readAsDataURL(e.target.files[0]);
            }
            e.preventDefault();
        });
        function update_user_apartment(e){
            var apartment_name = $(e).closest('.list_cus_apartment').find('input.apartment_id').val();
            var apartment_id = $(e).closest('.list_cus_apartment').find('input.apartment_id').attr('apartment_id');
            var apartment_type = $(e).closest('.list_cus_apartment').find('.choose_type_apartment').val();
            $.post('{{ url('/admin/v2/customers/add_user_apartment') }}', {
                        apartment_id:  apartment_id,
                        user_info_id:  $('#user_info_id').val(),
                        type:  apartment_type
                    }, function(data) {
                        toastr.success(data.message);
                        location.reload()
            });
        }
        function del_user_apartment(e){
            var apartment_name = $(e).closest('.list_cus_apartment').find('input.apartment_id').val();
            var apartment_id = $(e).closest('.list_cus_apartment').find('input.apartment_id').attr('apartment_id');
            var apartment_type = $(e).closest('.list_cus_apartment').find('.choose_type_apartment').val();
            $.post('{{ url('/admin/v2/customers/del_user_apartment') }}', {
                        apartment_id:  apartment_id,
                        user_info_id:  $('#user_info_id').val(),
                    }, function(data) {
                        toastr.success(data.message);
                        location.reload()
            });
        }
    </script>

@endsection
