@extends('backend.layouts.master')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">Cài Đặt Hệ Thống</div>

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
                    {!! Form::open(['url' =>[route('admin.system.config.store') ] , 'method'=> 'POST','files' => true]) !!}
                    <input type="hidden" name="config_key" value="vnp">
                    <div class="form-group">
                        <div class="row">
                            <div class="col-md-3">
                                {!! Form::label('title', 'Stringee API Key Name') !!}
                            </div>
                            <div class="col-md-9">
                                {!! Form::text('api_key_name', $data ? $data ['api_key_name'] : '', ['class' => 'form-control', 'placeholder' => 'API Key Name']) !!}
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="row">
                            <div class="col-md-3">
                                {!! Form::label('title', 'Stringee API Key SID') !!}
                            </div>
                            <div class="col-md-9">
                                {!! Form::text('api_key_sid', $data ? $data ['api_key_sid'] : '', ['class' => 'form-control', 'placeholder' => 'API Key SID']) !!}
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="row">
                            <div class="col-md-3">
                                {!! Form::label('title', 'Stringee API Key Secret') !!}
                            </div>
                            <div class="col-md-9">
                                {!! Form::text('api_key_secret', $data ? $data ['api_key_secret'] : '', ['class' => 'form-control', 'placeholder' => 'API Key Secret']) !!}
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="row">
                            <div class="col-md-3">
                                {!! Form::label('vpn_merchant_id', 'VPN Merchant ID') !!}
                            </div>
                            <div class="col-md-9">
                                {!! Form::text('vpn_merchant_id', $data ? $data ['vpn_merchant_id'] : '', ['class' => 'form-control', 'placeholder' => 'VPN Merchant ID']) !!}
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="row">
                            <div class="col-md-3">
                                {!! Form::label('vpn_secret', 'VPN Secret') !!}
                            </div>
                            <div class="col-md-9">
                                {!! Form::text('vpn_secret', $data ? $data ['vpn_merchant_id'] : '', ['class' => 'form-control', 'placeholder' => 'VPN Secret']) !!}
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="row">
                            <div class="col-md-3">
                                {!! Form::label('vpn_secret', 'Bộ phận giám sát') !!}
                            </div>

                            <div class="col-md-9">
                                <select name="bdc_department_id" class="form-control">
                                    <option value="" selected>Chọn bộ phận</option>
                                    @foreach($departments as $department)
                                        <option value="{{ $department->id }}" @if($department->id==$building->bdc_department_id) selected @endif>
                                            {{ $department->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="row">
                            <div class="col-md-3">
                                {!! Form::label('vpn_secret', 'Ngày chốt công nợ') !!}
                            </div>
                            <div class="col-md-9">
                                {!! Form::text('debit_date', @$building->debit_date, ['class' => 'form-control', 'placeholder' => 'Ngày chốt công nợ']) !!}
                            </div>
                        </div>
                    </div>
                    {{-- route_name --}}
                    <button type="submit" class="btn btn-default">Submit</button>

                    {!! Form::close() !!}

                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@section('stylesheet')
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
<link rel="stylesheet" href="https://unpkg.com/ionicons@4.5.5/dist/css/ionicons.min.css">

@endsection

@section('javascript')

@endsection
