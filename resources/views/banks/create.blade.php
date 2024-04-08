@extends('backend.layouts.master')

@section('content')

    <section class="content-header">
        <h1>
            Thêm mới ngân hàng
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('admin.home') }}"><i class="fa fa-home"></i> Trang chủ</a></li>
            <li class="active">Thêm mới ngân hàng</li>
        </ol>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-heading">Thêm ngân hàng</div>

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
                            <form action="" method="post" id="form-edit-apartment" enctype="multipart/form-data">
                                {{ csrf_field() }}
                                <input type="hidden" name="bdc_building_id"  class="form-control" placeholder="Tên căn hộ" value="{{$building_id}}">
                                <div class="form-group">
                                    <label for="ip-name">Tên ngân hàng</label>
                                    <input type="text" name="title" id="ip-title" class="form-control" placeholder="Tên form mẫu" value="{{old('title')}}">
                                </div>
                                <div class="form-group">
                                    <label for="ip-name">Tên gọi</label>
                                    <input type="text" name="alias" id="ip-alias" class="form-control" placeholder="Tên gọi" value="{{old('alias')}}">
                                </div>
                                <div class="form-group">
                                    <label for="ip-name">Link internet banking</label>
                                    <input type="text" name="url" id="ip-url" class="form-control" placeholder="Link internet banking" value="{{old('url')}}">
                                </div>
                                <div class="form-group">
                                    <label for="ip-floor">Logo</label>
                                    <input type="file" name="logo" id="ip-logo" class="form-control" placeholder="file" value="{{old('logo')}}">
                                </div>
                                <div class="form-group">
                                    <label for="select-status">Tình trạng</label>
                                    <select name="status" id="select-status" class="form-control">
                                        <?php $status = old('status'); ?>
                                        <option value="">Chọn Trạng thái</option>
                                        <option value="0" @if($status == 0) selected @endif>Ẩn</option>
                                        <option value="1" @if($status == 1) selected @endif>Hiện</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <button type="button" class="btn btn-sm btn-success btn-js-action-add" title="Cập nhật">
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
        $(".btn-js-action-add").on('click',function () {
            var _this = $(this);
            $(".alert_pop_add").hide();
            var name = $("#ip-title").val();
            var url = $("#ip-url").val();
            var status = $("#select-status").val();
            var html = '';
            if(name.length <2 || name.length >=45){
                html+='<li>Tên form không được nhỏ hơn 2 hoặc lớn hơn 45 ký tự</li>';
            }if(url == ''){
                html+='<li>Xin chọn file tải lên</li>';
            }if( status.length <=0){
                html+='<li>Trường tình trạng không được để trống</li>';
            }
            if(html){
                $(".alert_pop_add").show();
                $(".alert_pop_add ul").html(html);
            }else{
                $("#form-edit-apartment").submit();
            }
        });
        sidebar('apartments', 'create');
    </script>

@endsection
