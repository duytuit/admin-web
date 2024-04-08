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
                            <div class="col-lg-2">
                                <input type="hidden" name="id" value="{{@$configBangkePdf->id}}">
                                <input type="hidden" name="type" value="bangke_pdf">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="billpdf" id="inlineRadio1" value="detail_bill_mau1" @if(@$configBangkePdf->value == "detail_bill_mau1") checked @endif>
                                    <label class="form-check-label" for="inlineRadio1">Mẫu 1</label>
                                </div>
                                <div>
                                    <img src="{{ asset('form-bang-ke/mau_1.png') }}" style="width: 100%"/>
                                </div>                                
                            </div>
                            <div class="col-lg-2">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="billpdf" id="inlineRadio2" value="detail_bill_mau3" @if(@$configBangkePdf->value == "detail_bill_mau3") checked @endif>
                                    <label class="form-check-label" for="inlineRadio2">Mẫu 2</label>
                                </div>
                                <div>
                                    <img src="{{ asset('form-bang-ke/mau_2.png') }}" style="width: 100%"/>
                                </div>                                
                            </div>
                            <div class="col-lg-3">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="billpdf" id="inlineRadio3" value="detail_bill_mau2" @if(@$configBangkePdf->value == "detail_bill_mau2") checked @endif>
                                    <label class="form-check-label" for="inlineRadio3">Mẫu 3</label>
                                </div>
                                <div>
                                    <img src="{{ asset('form-bang-ke/mau_3.png') }}" style="width: 100%" height="730"/>
                                </div>                                
                            </div>
                            <div class="col-lg-3">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="billpdf" id="inlineRadio4" value="detail_bill_asahi" @if(@$configBangkePdf->value == "detail_bill_asahi") checked @endif>
                                    <label class="form-check-label" for="inlineRadio4">Mẫu Asahi</label> 
                                </div>
                                <div id="result"></div>
                                <div>
                                    <img src="https://i.ibb.co/2qxWwG6/asahiaaaaa.png" style="width: 100%" height="600"/>
                                </div>                                
                            </div>
                            <div class="col-lg-2">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="billpdf" id="inlineRadio5" value="detail_bill_trinity" @if(@$configBangkePdf->value == "detail_bill_trinity") checked @endif>
                                    <label class="form-check-label" for="inlineRadio4">Mẫu Trinity</label> 
                                </div>
                                <div id="result"></div>
                                <div>
                                    <img src="https://cdn.dxmb.vn/media/buildingcare/2023/0908/0249-demo.jpg" style="width: 100%" height="auto"/>
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