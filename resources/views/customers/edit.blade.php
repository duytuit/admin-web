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
                            <form action="" method="post" id="form-edit-customers">
                                {{ csrf_field() }}
                                <input type="hidden" name="url_Apartment" value="{{ $urlApartment }}">
                                <input type="hidden" name="profile_id" value="{{ $customer->id }}">
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="ip-name">Tên Cư dân</label>
                                        <input type="text" name="name" id="ip-name" class="form-control" placeholder="Tên cư dân" value="{{ $customer->display_name ?? old('name') ?? ''}}">
                                    </div>
                                    <div class="form-group">
                                        <label for="ip-phone">Số điện thoại</label>
                                        <input type="text" name="phone" id="ip-phone" class="form-control" placeholder="Số điện thoại" value="{{ $customer->phone ?? old('phone') ?? ''}}">
                                    </div>
                                    <div class="form-group">
                                        <label for="ip-email">Email</label>
                                        <input type="text" name="email" id="ip-email" class="form-control" placeholder="Email" value="{{ $customer->email ?? old('email') ?? ''}}">
                                    </div>
                                    <div class="form-group">
                                        <label for="ip-address">Địa chỉ</label>
                                        <input type="text" name="address" id="ip-address" class="form-control" placeholder="Địa chỉ" value="{{ $customer->address ?? old('address') ?? ''}}">
                                    </div>
                                    <div class="form-group">
                                        <label for="ip-cmt">CMND/hộ chiếu</label>
                                        <input type="text" name="cmt" id="ip-cmt" class="form-control" placeholder="CMND/hộ chiếu" value="{{ $customer->cmt ?? old('cmt') ?? ''}}">
                                    </div>
                                    <div class="form-group">
                                        <label for="ip-cmt_nc">Ngày cấp</label>
                                        <?php
                                                $cmt_nc = NULL;
                                                if ($customer->cmt_nc <> NULL){
                                                    $cmt_nc = date('d-m-Y', strtotime($customer->cmt_nc));
                                                }
                                            ?>
                                        <div class="input-group datetimepicker" data-format="DD-MM-YYYY">
                                            <input type="text" name="cmt_nc" id="ip-cmt_nc" class="form-control" placeholder="Ngày cấp" value="{{ $cmt_nc ?? old('cmt_nc') ?? ''}}"><span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6">


                                    <div class="form-group">
                                        <label for="ip-birthday">Ngày sinh</label>
                                        <div class="input-group datetimepicker" data-format="DD-MM-YYYY">

                                             <?php
                                                $birthday = NULL;
                                                if ($customer->birthday <> NULL){
                                                    $birthday = date('d-m-Y', strtotime($customer->birthday));
                                                }
                                            ?>
                                            <input type="text" name="birthday" id="ip-birthday" class="form-control" placeholder="Ngày sinh" value="{{ $birthday ?? old('birthday') ?? ''}}">



                                            <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="ip-gender">Giới tính</label>
                                        <?php $gender = $customer->gender ?? old('gender') ?? ''; ?>
                                        <div class="form-control" style="border: none;">
                                            <input type="radio" name="gender" id="ip-genderm" placeholder="Giới tính" value="1" @if($gender == 1) checked @endif> Nam
                                            <input type="radio" name="gender" id="ip-genderf" placeholder="Giới tính" value="2" @if($gender == 2) checked @endif> Nữ
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label for="ip-type">Avartar</label>
                                        <div class="input-group input-image" data-file="image">
                                            <input type="text" name="avatar" id="in-avatar" value="{{$customer->avatar ?? old('avatar') ?? ''}}" class="form-control"><span class="input-group-btn"><button type="button" class="btn btn-primary">Chọn</button></span>
                                        </div>
                                        @if (old('avatar', $customer->avatar))
                                            <img src="{{ old('avatar', $customer->avatar) }}" alt="" style="max-width: 200px;" />
                                        @endif
                                    </div>

                                </div>
                                <div class="clearfix"></div>
                                <div class="row box_list_customer">
                                    <p style="font-weight: bold;font-size: 20px;">Danh sách căn hộ</p>
                                    @foreach($bdcCustomers as $key => $item)
                                        <input type="hidden" name="cus_id[]" value="{{$item->id}}">
                                        <div class="col-sm-12">
                                            <div class="col-sm-6">
                                                <div class="form-group">
                                                    <label for="ip-cmt_nc">căn hộ</label>
                                                    <select name="bdc_apartment_id[]" id="ip-ap_id" class="form-control aprt_id" style="width: 100%;">
                                                        <option value="">Chọn căn hộ</option>
                                                        <?php $apartment_id = $item->bdcApartment->id ?? old('bdc_apartment_id') ?? ''; ?>
                                                        @if($apartment_id)
                                                            <option value="{{$apartment_id}}" selected>{{ $item->bdcApartment->name ?? '' }}</option>
                                                        @endif
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-sm-6">
                                                <div class="form-group">
                                                    <?php $type = $item->type ?? old('type') ?? ''; ?>
                                                    <label for="ip-type">Tình trạng</label>
                                                    <select name="type[]" id="select-ap-role" class="form-control">
                                                        <option value="">Chọn Trạng thái</option>
                                                        <option value="0" @if($type == 0) selected @endif>Chủ hộ</option>
                                                        <option value="1" @if($type == 1) selected @endif>Vợ/Chồng</option>
                                                        <option value="2" @if($type == 2) selected @endif>Con</option>
                                                        <option value="3" @if($type == 3) selected @endif>Bố mẹ</option>
                                                        <option value="4" @if($type == 4) selected @endif>Anh chị em</option>
                                                        <option value="5" @if($type == 5) selected @endif>Khác</option>
                                                        <option value="6" @if($type == 6) selected @endif>Khách thuê</option>
                                                        <option value="7" @if($type == 7) selected @endif>Chủ hộ cũ</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                <div class="form-group">
                                    <button type="submit" class="btn btn-sm btn-success" title="Cập nhật" form="form-edit-customers">
                                        <i class="fa fa-save"></i>&nbsp;Cập nhật
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
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
                object: '.aprt_id',
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

        
    </script>

@endsection
