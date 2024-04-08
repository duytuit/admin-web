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
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
               <div class="panel-heading">
                   <a class="btn btn-success" href="{{ route('admin.pricetype.create') }}"> Cập nhật</a>
                </div>
                <div class="panel-body">
                    {!! Form::open(['url' =>[route('admin.pricetype.store') ] , 'method'=> 'POST','files' => true]) !!}
                        <div class="col-lg-6">
                            <div class="card mb-3">
                                <div class="card-header">
                                    <i class="fas fa-chart-area"></i>
                                    <div class="form-group col-md-4">
                                        {!! Form::text('name', old('from'), ['class' => 'form-control', 'placeholder' => 'Tên bảng giá lũy tiến']) !!}
                                    </div>
                                    <button type="button" class="btn btn-success add_progress_price_item" style="float: right">
                                        <i class="fa fa-plus" aria-hidden="true"></i>
                                    </button>
                                </div>
                                <div class="card-body progress_price_list">
                                    <div class="form-row progress_price_items">
                                        <div class="form-group col-md-3">
                                            {!! Form::text('from[0]', old('from'), ['class' => 'form-control', 'placeholder' => 'Từ']) !!}
                                        </div>
                                        <div class="form-group col-md-3">
                                            {!! Form::text('to[0]', old('to'), ['class' => 'form-control', 'placeholder' => 'Đến']) !!}
                                        </div>
                                        <div class="form-group col-md-3">
                                            {!! Form::text('price[0]', old('price'), ['class' => 'form-control', 'placeholder' => 'Giá']) !!}
                                        </div>
                                        <div class="form-group col-md-3">
                                            <button type="button" class="btn btn-danger" onclick="removeProgressPrice(this)">
                                                <i class="fa fa-minus" aria-hidden="true"></i>
                                            </button>        
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>
</section>

@endsection

@section('javascript')

<script>
sidebar('event', 'index');
</script>

<script>
    $(".add_progress_price_item").click(function(e) {
        var avails = $(".progress_price_items");
        //var cnt = avails.length;
        //console.log(cnt);
        var clone = avails.eq(0).clone();
        $(".progress_price_list").append(clone).find(".progress_price_items").each(function(key, value) {
            $(this).find("input").each(function(){  
                this.name = this.name.replace(/\d+/, key);
            });
        });
        e.preventDefault();
    });

    function removeProgressPrice(that)
    {
        $(that).closest(".progress_price_items").remove();
        $(".progress_price_list").find(".progress_price_items").each(function(key, value) {
            $(this).find("input").each(function() {
                this.name = this.name.replace(/\d+/, key);
            });
        });
    }
</script>

@endsection
