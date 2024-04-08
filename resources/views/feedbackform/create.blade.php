@extends('backend.layouts.master')

@section('content')

    <section class="content-header">
        <h1>
            Thêm mới form mẫu
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('admin.home') }}"><i class="fa fa-home"></i> Trang chủ</a></li>
            <li class="active">Thêm mới form mẫu</li>
        </ol>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-heading">Thêm form mẫu</div>

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
                            <div class="form-group">
                                <label>Chọn mẫu form</label>
                                <form role="form">
                                    <select class="form-control selectpicker" name="type" data-live-search="true" onchange='this.form.submit()'>
                                        @foreach ($form_registers as $key => $form)
                                            <option value="{{ $key }}" @if (@$filter['type'] == $key) selected @endif> {{ $form['title'] }}</option>
                                        @endforeach
                                    </select>
                                    <noscript><input type="submit" value="Submit"></noscript>
                                </form>
                            </div>
                            <form action="" method="post" id="form-edit-apartment" enctype="multipart/form-data">
                                {{ csrf_field() }}
                                <input type="hidden" name="bdc_building_id"  class="form-control" placeholder="Tên căn hộ" value="{{$building_id}}">
                                <input type="hidden" name="type"  class="form-control"value="{{$form_type ?? 1}}">
                                <div class="form-group">
                                    <label for="ip-floor">File</label>
                                    <input type="file" name="url" id="ip-url" class="form-control" placeholder="file" value="{{!empty($form_register->url)?$form_register->url:old('url')}}">
                                </div>
                                <div class="form-group">
                                    <div>
                                        <label for="ip-description">Tham số truyền vào</label>
                                    </div>
                                    @if ($param_registers)
                                        @foreach ($param_registers['param'] as $key => $item)
                                           @if ($key != 0)
                                                <span>, {{$item}}</span>
                                           @else
                                                <span>{{$item}}</span>
                                           @endif
                                        @endforeach
                                    @endif
                                </div>
                                <div class="form-group">
                                    <label for="ip-description">Nội dung</label>
                                    <textarea name="content" placeholder="Nội dung" rows="10" class="form-control">{{!empty($form_register->content)?$form_register->content : old('content')}}</textarea>
                                </div>
                                <div class="form-group">
                                    <label for="ip-description">Mô tả</label>
                                    <textarea name="hint" id="id-hint" cols="30" rows="2" placeholder="Mô tả" class="form-control">{{!empty($form_register->hint)?$form_register->hint:old('hint')}}</textarea>
                                </div>
                                <div class="form-group">
                                    <label for="select-status">Tình trạng</label>
                                    <select name="status" id="select-status" class="form-control">
                                        <?php $status = !empty($form_register->status)?$form_register->status:old('status'); ?>
                                        <option value="1" @if($status == 1) selected @endif>Hiện</option>
                                        <option value="0" @if($status === 0) selected @endif>Ẩn</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <button type="submit" class="btn btn-sm btn-success btn-js-action-add" title="Cập nhật">
                                        <i class="fa fa-save"></i>&nbsp;Cập nhật
                                    </button>
                                    <a class="btn btn-sm btn-warning" href="{{ route('admin.feedbackform.index') }}"><i class="fa fa-home"></i> Quay lại</a>
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
        // $(".btn-js-action-add").on('click',function () {
        //     var _this = $(this);
        //     $(".alert_pop_add").hide();
        //     var name = $("#ip-title").val();
        //     var url = $("#ip-url").val();
        //     var status = $("#select-status").val();
        //     var html = '';
        //     if(name.length <2 || name.length >=45){
        //         html+='<li>Tên form không được nhỏ hơn 2 hoặc lớn hơn 45 ký tự</li>';
        //     }if( status.length <=0){
        //         html+='<li>Trường tình trạng không được để trống</li>';
        //     }
        //     if(html){
        //         $(".alert_pop_add").show();
        //         $(".alert_pop_add ul").html(html);
        //     }else{
        //         $("#form-edit-apartment").submit();
        //     }
        // });
        sidebar('apartments', 'create');
    </script>

@endsection
