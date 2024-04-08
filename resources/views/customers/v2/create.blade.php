@extends('backend.layouts.master')

@section('content')

    <section class="content-header">
        <h1>
            Thêm mới cư dân
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('admin.home') }}"><i class="fa fa-home"></i> Trang chủ</a></li>
            <li class="active">Thêm mới cư dân</li>
        </ol>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-heading">Thêm cư dân</div>

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
                            <form action="{{ route('admin.v2.customers.insert') }}" method="post" id="form-add-resident" class="form-validate form-horizontal">
                                {{ csrf_field() }}
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="ip-name">Tên Cư dân</label>
                                        <input type="text" name="name" id="ip-name" class="form-control" placeholder="Tên cư dân" value="">
                                    </div>
                                    <div class="form-group">
                                        <label for="ip-phone">Số điện thoại</label>
                                        <input type="text" name="phone" id="ip-phone" class="form-control" placeholder="Số điện thoại" value="">
                                    </div>
                                    <div class="form-group">
                                        <label for="ip-email">Email</label>
                                        <input type="text" name="email" id="ip-email" class="form-control" autocomplete="nope" placeholder="Email" value="">
                                    </div>
                                    <div class="form-group">
                                        <label for="ip-address">Địa chỉ</label>
                                        <input type="text" name="address" id="ip-address" class="form-control" placeholder="Địa chỉ" value="">
                                    </div>
                                    <div class="form-group">
                                        <label for="ip-cmt">CMND/hộ chiếu</label>
                                        <input type="text" name="cmt" id="ip-cmt" class="form-control" placeholder="CMND/hộ chiếu" value="">
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="ip-cmt_nc">Ngày cấp</label>
                                        <input type="text" name="cmt_nc" id="ip-cmt_nc" class="form-control" placeholder="Ngày cấp" value="">
                                    </div>
                                    <div class="form-group">
                                        <label for="ip-birthday">Ngày sinh</label>
                                        <input type="text" name="birthday" id="ip-birthday" class="form-control" placeholder="Ngày sinh" value="">
                                    </div>
                                    <div class="form-group">
                                        <label for="ip-gender">Giới tính</label>
                                        <input type="text" name="gender" id="ip-gender" class="form-control" placeholder="Giới tính" value="">
                                    </div>
                                    <div class="form-group">
                                        <label for="ip-type">Tình trạng</label>
                                        <select name="type" id="select-ap-role" class="form-control">
                                            <option value="">Chọn Trạng thái</option>
                                            <option value="0">Chủ hộ</option>
                                            <option value="1">Vợ/Chồng</option>
                                            <option value="2">Con</option>
                                            <option value="3">Bố mẹ</option>
                                            <option value="4">Anh chị em</option>
                                            <option value="5">Khác</option>
                                            <option value="6">Khách thuê</option>
                                            <option value="8">Cháu</option>
                                            <option value="7">Chủ hộ cũ</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="ip-type">Avartar</label>
                                        <div class="input-group input-image" data-file="image">
                                            <input type="text" name="avatar" id="in-avatar" value="" class="form-control"><span class="input-group-btn"><button type="button" class="btn btn-primary">Chọn</button></span>
                                        </div>
                                    </div>

                                </div>

                                <div class="form-group">
                                    <button type="submit" class="btn btn-sm btn-success" title="Cập nhật" form="form-edit-apartment">
                                        <i class="fa fa-save"></i>&nbsp;Thêm mới
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

@section('javascript')

    <script>
        sidebar('apartments', 'create');
    </script>

@endsection
