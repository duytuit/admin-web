@extends('backend.layouts.master')
@section('content')
    <section class="content-header">
        <h1>
            Thêm mới tòa nhà
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('admin.home') }}"><i class="fa fa-home"></i> Trang chủ</a></li>
            <li class="active">Thêm mới tòa nhà</li>
        </ol>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-heading">Thêm tòa nhà</div>

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
                        <div class="alert alert-danger alert_pop_add" style="display: none;">
                            <ul></ul>
                        </div>
                        <div class="form-group">
                            <form action="" method="post" id="form-edit-building-place">
                                {{ csrf_field() }}
                                <input type="hidden" name="bdc_building_id"  class="form-control" placeholder="Khu tòa nhà" value="{{$building_id}}">
                                <div class="form-group">
                                    <label for="ip-name">Tên tòa nhà</label>
                                    <input type="text" name="name" id="ip-name" class="form-control" placeholder="Tên tòa nhà" value="{{!empty($buildingPlace->name)?$buildingPlace->name:old('name')}}">
                                </div>
                                <div class="form-group">
                                    <label for="ip-name">Mã tòa nhà</label>
                                    <input type="text" name="code" id="ip-code" class="form-control" placeholder="Mã tòa nhà" value="{{!empty($buildingPlace->code)?$buildingPlace->code:old('code')}}">
                                </div>
                                <div class="form-group hidden">
                                    <label for="ip-floor">Email</label>
                                    <input type="text" name="email" id="ip-email" class="form-control" placeholder="Email" value="{{!empty($buildingPlace->email)?$buildingPlace->email:old('email')}}">
                                </div>
                                <div class="form-group hidden">
                                    <label for="ip-acreage">Số điện thoại</label>
                                    <input type="text" name="mobile" id="ip-mobile" class="form-control" placeholder="Số điện thoại" value="{{!empty($buildingPlace->mobile)?$buildingPlace->mobile:old('mobile')}}">
                                </div>
                                <div class="form-group hidden">
                                    <label for="ip-acreage">Địa chỉ</label>
                                    <input type="text" name="address" id="ip-address" class="form-control" placeholder="Địa chỉ tòa nhà" value="{{!empty($buildingPlace->address)?$buildingPlace->address:old('address')}}">
                                </div>
                                <div class="form-group hidden">
                                    <label for="ip-description">Mô tả</label>
                                    <textarea name="description" id="id-description" cols="30" rows="5" placeholder="Mô tả tòa nhà" class="form-control">{{!empty($buildingPlace->description)?$buildingPlace->description:old('description')}}</textarea>
                                </div>
                                <div class="form-group">
                                    <label for="select-status">Tình trạng</label>

                                    <select name="status" id="select-status" class="form-control">
                                        <?php $status = !empty($buildingPlace->status)?$buildingPlace->status:old('status'); ?>
                                        <option value="0" @if($status == 0) selected @endif>Đóng</option>
                                        <option value="1" @if($status == 1) selected @endif>Mở</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <button type="button" class="btn btn-sm btn-success btn-js-action-add" title="Cập nhật">
                                        <i class="fa fa-save"></i>&nbsp;
                                        @if(isset($id))
                                            Cập nhật
                                        @else
                                            Thêm mới
                                        @endif

                                    </button>
                                    <a href="{{ route('admin.buildingplace.index') }}"  class="btn btn-sm btn-success btn-js-action-add">Danh sách tòa nhà</a>
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
        $(".btn-js-action-add").on('click',function () {
            var _this = $(this);
            $(".alert_pop_add").hide();
            var name = $("#ip-name").val();
            var code = $("#ip-code").val();
            var status = $("#select-status").val();
            var html = '';
            if(name.length <1 || name.length >46){
                html+='<li>Tên tòa nhà không được nhỏ hơn 2 hoặc lớn hơn 45 chữ số</li>';
            }
            if(code.length == '' || code.length > 10){
                html+='<li>Trường mã tòa nhà là 1 số không quá 10 chữ số và không bỏ trống</li>';
            }
            if( status.length <=0){
                html+='<li>Trường tình trạng không được để trống</li>';
            }
            if(html){
                $(".alert_pop_add").show();
                $(".alert_pop_add ul").html(html);
            }else{
                $("#form-edit-building-place").submit();
            }
        });
        sidebar('apartments', 'create');
    </script>

@endsection
