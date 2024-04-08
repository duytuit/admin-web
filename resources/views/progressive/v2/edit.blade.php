@extends('backend.layouts.master')

@section('stylesheet')
<link rel="stylesheet" href="{{ url('adminLTE/css/bdc-admin.css') }}" />
@endsection

@section('content')

<section class="content-header">
    <h1>
        Bảng giá lũy tiến
    </h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.home') }}"><i class="fa fa-home"></i> Trang chủ</a></li>
        <li class="active">Bảng giá lũy tiến</li>
    </ol>
</section>

<section class="content">
    {!! Form::open(['route' => ['admin.v2.progressive.update', $item->id], 'method'=> 'PUT','files' => true]) !!}
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default">
                <div class="panel-heading">
                    <button type="submit" class="btn btn-success"> Cập nhật</button>
                    <a href="{{ route('admin.v2.progressive.index') }}" class="btn btn-success"> Danh sách</a>
                    </div>
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
                        <div class="col-lg-6">
                            <div class="card mb-3">
                                <div class="card-header">
                                    <i class="fas fa-chart-area"></i>
                                    <div class="form-group col-md-4">
                                        {!! Form::text('name', $item->name, ['class' => 'form-control', 'placeholder' => 'Tên bảng giá lũy tiến']) !!}
                                    </div>
                                    <div class="form-group col-md-4">
                                        {{ Form::hidden('bdc_price_type_id', $selectedRole) }}
                                        {!! Form::select('bdc_price_type_id', $progressives, $selectedRole, ['class' => 'form-control', 'disabled' => true]); !!}
                                    </div>
                                    <div class="form-group col-md-3">
                                        {!! Form::select('description', ["Giá tiền/tháng" => "Giá tiền/tháng", "Giá tiền/m2/tháng" => "Giá tiền/m2/tháng"], $item->description, ['class' => 'form-control']); !!}
                                    </div>
                                    @if ($selectedRole == 2)
                                        <button type="button" class="btn btn-success add_progress_price_item" style="float: right">
                                            <i class="fa fa-plus" aria-hidden="true"></i>
                                        </button>
                                    @endif
                                </div>
                                <div class="card-body progress_price_list">
                                    @foreach ($progressivePrices as $key => $progressivePrice)
                                        @if ($selectedRole == 1)
                                            <div class="form-row temp_one_progress_price_items">
                                                <div class="form-group col-md-6">
                                                    {!! Form::text('price', $progressivePrice->price, ['class' => 'form-control', 'placeholder' => 'Giá tiền']) !!}
                                                </div>
                                            </div>
                                        @else
                                            <div class="form-row progress_price_items">
                                                {{ Form::hidden('progressive[id][' . $key . ']', $progressivePrice->id) }}
                                                <div class="form-group col-md-3">
                                                    {!! Form::text('progressive[from][' . $key . ']', $progressivePrice->from, ['class' => 'form-control', 'placeholder' => 'Từ']) !!}
                                                </div>
                                                <div class="form-group col-md-3">
                                                    {!! Form::text('progressive[to][' . $key . ']', $progressivePrice->to, ['class' => 'form-control', 'placeholder' => 'Đến']) !!}
                                                </div>
                                                <div class="form-group col-md-3">
                                                    {!! Form::text('progressive[price][' . $key . ']', $progressivePrice->price, ['class' => 'form-control', 'placeholder' => 'Giá']) !!}
                                                </div>
                                                <div class="form-group col-md-3">
                                                    <button type="button" class="btn btn-danger" onclick="removeProgressPrice(this)">
                                                        <i class="fa fa-minus" aria-hidden="true"></i>
                                                    </button>        
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    {!! Form::close() !!}
</section>

@endsection

@section('javascript')

<script>
sidebar('event', 'index');
</script>

<script>
    $(".add_progress_price_item").click(function(e) {
        var avails = $(".progress_price_items");
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
