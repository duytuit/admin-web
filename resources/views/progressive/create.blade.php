@extends('backend.layouts.master')

@section('stylesheet')
<link rel="stylesheet" href="{{ url('adminLTE/css/bdc-admin.css') }}" />
@endsection

@section('content')

<section class="content-header">
    <h1>
        Bảng giá
    </h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.home') }}"><i class="fa fa-home"></i> Trang chủ</a></li>
        <li class="active">Bảng giá</li>
    </ol>
</section>

<section class="content">
    {!! Form::open(['url' =>[route('admin.progressive.store') ] , 'method'=> 'POST','files' => true]) !!}
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default">
                <div class="panel-heading">
                    <button type="submit" class="btn btn-success"> Cập nhật</button>
                    <a href="{{ route('admin.progressive.index') }}" class="btn btn-success"> Danh sách</a>
                    </div>
                    <div class="panel-body">
                        <div class="col-lg-12">
                            <div class="card mb-3">
                                <div class="card-header">
                                    <i class="fas fa-chart-area"></i>
                                    <div class="form-group col-md-2">
                                        {!! Form::text('name', old('from'), ['class' => 'form-control', 'placeholder' => 'Tên bảng giá']) !!}
                                    </div>
                                    <div class="form-group col-md-2">
                                        {!! Form::select('bdc_price_type_id', $progressives, '1', ['class' => 'form-control bdc_price_type_id']); !!}
                                    </div>
                                    <div class="form-group col-md-2">
                                        {!! Form::select('description', ["Giá tiền/tháng" => "Giá tiền/tháng", "Giá tiền/m2/tháng" => "Giá tiền/m2/tháng"], '1', ['class' => 'form-control']); !!}
                                    </div>
                                    <div class="form-group col-md-3">
                                        {!! Form::select('bdc_service_id', array('default' => '--Dịch vụ--') + $service ,null, ['class' => 'form-control select2']); !!}
                                    </div>
                                    <div class="form-group col-md-2">
                                        {!! Form::text('applicable_date',  old('applicable_date'),['class' => 'form-control date_picker','placeholder' => 'Ngày áp dụng']) !!}
                                    </div>
                                    <button type="button" class="btn btn-success add_progress_price_item" style="float: right">
                                        <i class="fa fa-plus" aria-hidden="true"></i>
                                    </button>
                                </div>
                                <div class="card-body progress_price_list">
                                    <div class="form-row temp_one_progress_price_items">
                                        <div class="form-group col-md-6">
                                            {!! Form::number('price', null, ['step'=>'any', 'min'=>0, 'class' => 'form-control', 'placeholder' => 'Giá tiền']) !!}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    {!! Form::close() !!}
</section>

<div class="progress_price_one" style="display: none">
    <div class="form-row temp_one_progress_price_items">
        <div class="form-group col-md-6">
            {!! Form::number('price', null, ['step'=>'any', 'min'=>0,'class' => 'form-control', 'placeholder' => 'Giá tiền']) !!}
        </div>
    </div>
</div>
<div class="progress_price_utilities" style="display: none">
    <div class="form-row temp_utiltties_progress_price_items">
        <div class="form-group col-md-3">
            {!! Form::text('progressive[option][0]', null, ['class' => 'form-control', 'placeholder' => 'Tiêu đề']) !!}
        </div>
        <div class="form-group col-md-2">
            {!! Form::number('progressive[price][0]', null, ['class' => 'form-control','min'=>0, 'placeholder' => 'Giá']) !!}
        </div>
        <div class="form-group col-md-2">
            {!! Form::number('progressive[quantity][0]', null, ['class' => 'form-control','min'=>0, 'placeholder' => 'Lượt']) !!}
        </div>
        <div class="form-group col-md-2">
            {!! Form::number('progressive[period_quantity][0]', null, ['class' => 'form-control','min'=>0, 'placeholder' => 'Thời gian/lượt']) !!}
        </div>
        <div class="form-group col-md-2">
            {!! Form::number('progressive[date_quantity][0]', null, ['class' => 'form-control','min'=>0, 'placeholder' => 'Ngày']) !!}
        </div>
        <div class="form-group col-md-1">
            <button type="button" class="btn btn-danger remove_progress_price_item" onclick="removeProgressPrice(this)">
                <i class="fa fa-minus" aria-hidden="true"></i>
            </button>        
        </div>
    </div>
</div>
<div class="progress_price_multi" style="display: none">
    <div class="form-row progress_price_items">
        <div class="form-group col-md-3">
            {!! Form::text('progressive[from][0]', old('from'), ['class' => 'form-control', 'placeholder' => 'Từ']) !!}
        </div>
        <div class="form-group col-md-3">
            {!! Form::text('progressive[to][0]', old('to'), ['class' => 'form-control', 'placeholder' => 'Đến']) !!}
        </div>
        <div class="form-group col-md-3">
            {!! Form::number('progressive[price][0]', old('price'), ['step'=>'any', 'min'=>0,'class' => 'form-control', 'placeholder' => 'Giá tiền']) !!}
        </div>
        <div class="form-group col-md-3">
            <button type="button" class="btn btn-danger remove_progress_price_item" onclick="removeProgressPrice(this)">
                <i class="fa fa-minus" aria-hidden="true"></i>
            </button>        
        </div>
    </div>
</div>

@endsection

@section('javascript')

<script>
sidebar('event', 'index');
</script>

<script>
    $('input.date_picker').datepicker({
        autoclose: true,
        dateFormat: "dd-mm-yy"
    }).val();
    $(".add_progress_price_item").click(function(e) {
        var priceTypeValue = $(".bdc_price_type_id").val();
        if (priceTypeValue == 2) {
            var avails = $(".progress_price_items");
            var clone = avails.eq(0).clone();
            $(".progress_price_list").append(clone).find(".progress_price_items").each(function(key, value) {
                $(this).find("input").each(function(){  
                    this.name = this.name.replace(/\d+/, key);
                });
            });
        }
        if (priceTypeValue == 4) {
            var avails = $(".temp_utiltties_progress_price_items");
            var clone = avails.eq(0).clone();
            $(".progress_price_list").append(clone).find(".temp_utiltties_progress_price_items").each(function(key, value) {
                $(this).find("input").each(function(){  
                    this.name = this.name.replace(/\d+/, key);
                });
            });
        }
        e.preventDefault();
    });

    $(".bdc_price_type_id").on("change", function(){
        var priceTypeValue = $(this).val();
        if (priceTypeValue == 1 || priceTypeValue == 3) {
            $(".add_progress_price_item").prop("disabled", true);
            var avails = $(".temp_one_progress_price_items");
            var clone = avails.eq(0).clone();
            $(".progress_price_list").html(clone);
        } 
         if(priceTypeValue == 2) {
            $(".add_progress_price_item").prop("disabled", false);
            var avails = $(".progress_price_items");
            var clone = avails.eq(0).clone();
            $(".progress_price_list").html(clone);
        }
         if(priceTypeValue == 4) {
            $(".add_progress_price_item").prop("disabled", false);
            var avails = $(".temp_utiltties_progress_price_items");
            console.log(avails);
            var clone = avails.eq(0).clone();
            $(".progress_price_list").html(clone);
        }
    });

    function removeProgressPrice(that)
    {
        var priceTypeValue = $(".bdc_price_type_id").val();
        if(priceTypeValue == 4) {
            $(that).closest(".temp_utiltties_progress_price_items").remove();
            $(".progress_price_list").find(".temp_utiltties_progress_price_items").each(function(key, value) {
                $(this).find("input").each(function() {
                    this.name = this.name.replace(/\d+/, key);
                });
            });
        }
        if(priceTypeValue == 2) {
            $(that).closest(".progress_price_items").remove();
            $(".progress_price_list").find(".progress_price_items").each(function(key, value) {
                $(this).find("input").each(function() {
                    this.name = this.name.replace(/\d+/, key);
                });
            });
        }
    }
</script>

@endsection
