@extends('backend.layouts.master')
@inject('request', 'Illuminate\Http\Request')

@section('content')
<section class="content-header">
    <h1>
        Quản lý tòa nhà
        <small>Danh sách cấu hình</small>
    </h1>
    <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Trang chủ</a></li>
        <li class="active">Danh sách cấu hình</li>
    </ol>
</section>

<section class="content" id="content-partner">
    <div>
        <ul id="errors"></ul>
    </div>
    <form action="{{ route('admin.configs.billPdfPost') }}" method="POST" id="form-create-work" enctype="multipart/form-data">
        {{ csrf_field() }}
        <div class="row">
            <div class="col-lg-12">
                <div class="box box-primary">
                    <div class="box-body">
                        <div class="form-group">
                            <div class="col-lg-4">
                                <input type="hidden" name="id" value="{{@$configReceipt->id}}">
                                <input type="hidden" name="type" value="receipt_view">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="receipt_style" id="inlineRadio1" value="pdf_v5" @if(@$configReceipt->value == "pdf_v5") checked @endif>
                                    <label class="form-check-label" for="inlineRadio1">Mẫu 1</label>
                                </div>
                                <div>
                                    <img src="{{ asset('images/mau_phieu_thu_1.png') }}" style="width: 100%"/>
                                </div>                                
                            </div>
                            <div class="col-lg-4">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="receipt_style" id="inlineRadio2" value="mau_phieu_thu_1" @if(@$configReceipt->value == "mau_phieu_thu_1") checked @endif>
                                    <label class="form-check-label" for="inlineRadio2">Mẫu 2</label>
                                </div>
                                <div>
                                    <img src="{{ asset('images/mau_phieu_thu_2.png') }}" style="width: 100%"/>
                                </div>                                
                            </div>
                        </div>
                    </div>
                    <div class="text-center">
                        <button type="submit" class="btn btn-primary button-js-add">Lưu</button>
                        <a type="button" class="btn btn-danger" href="{{ route('admin.configs.index') }}">
                            Quay lại
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</section>
@endsection

@section('stylesheet')

<link rel="stylesheet" href="/adminLTE/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css" />

@endsection

@section('javascript')
<script src="/adminLTE/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js"></script>
<!-- TinyMCE -->
<script src="/adminLTE/plugins/tinymce/tinymce.min.js"></script>
<script src="/adminLTE/plugins/tinymce/config.js"></script>
<script>
</script>
@endsection