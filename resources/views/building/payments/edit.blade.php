@extends('backend.layouts.master')

@section('content')

<section class="content-header">
    <h1>
        Cập nhật tài khoản ngân hàng
    </h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.home') }}"><i class="fa fa-home"></i> Trang chủ</a></li>
        <li class="active">Tài khoản ngân hàng</li>
    </ol>
</section>

<section class="content">
    @if( in_array('admin.building.info.save',@$user_access_router))
        <form id="form-building-payment" action="{{ route('admin.building.info.save', ['id' => $id]) }}" method="post" autocomplete="off">
            @csrf
            @method('POST')

            @php
            $old = old();
            @endphp

            <div class="row">
                <div class="col-sm-8">
                    <div class="box no-border-top">
                        <div class="box-body no-padding">
                            <div class="nav-tabs-custom no-margin">
                                <!-- Nav tabs -->
                                <ul class="nav nav-tabs">
                                    <li class="active"><a href="#general" data-toggle="tab">Tổng quan</a></li>
                                </ul>
                                <!-- Tab panes -->
                                <div class="tab-content">
                                    <div role="tabpanel" class="tab-pane active" id="general">
                                        <div class="form-group {{ $errors->has('code') ? 'has-error' : '' }}">
                                            <label class="control-label">Mã tài khoản</label>
                                            <textarea name="code" placeholder="Số tài khoản" rows="1" class="form-control input-text">{{ old('code', $payment_info->code) }}</textarea>
                                            @if ($errors->has('code'))
                                            <em class="help-block">{{ $errors->first('code') }}</em>
                                            @endif
                                        </div>
                                        <div class="form-group {{ $errors->has('bank_account') ? 'has-error' : '' }}">
                                            <label class="control-label">Số tài khoản</label>
                                            <textarea name="bank_account" placeholder="Số tài khoản" rows="1" class="form-control input-text">{{ old('bank_account', $payment_info->bank_account) }}</textarea>
                                            @if ($errors->has('bank_account'))
                                            <em class="help-block">{{ $errors->first('bank_account') }}</em>
                                            @endif
                                        </div>
                                        <div class="form-group {{ $errors->has('bank_name') ? 'has-error' : '' }}">
                                            <label class="control-label">Ngân hàng</label>
                                            <textarea name="bank_name" placeholder="Ngân hàng" rows="1" class="form-control input-text">{{ old('bank_name', $payment_info->bank_name) }}</textarea>
                                            @if ($errors->has('bank_name'))
                                            <em class="help-block">{{ $errors->first('bank_name') }}</em>
                                            @endif
                                        </div>
                                        <div class="form-group {{ $errors->has('holder_name') ? 'has-error' : '' }}">
                                            <label class="control-label">Chủ tài khoản</label>
                                            <textarea name="holder_name" placeholder="Chủ tài khoản" rows="1" class="form-control input-text">{{ old('holder_name', $payment_info->holder_name) }}</textarea>
                                            @if ($errors->has('holder_name'))
                                            <em class="help-block">{{ $errors->first('holder_name') }}</em>
                                            @endif
                                        </div>
                                        <div class="form-group {{ $errors->has('branch') ? 'has-error' : '' }}">
                                            <label class="control-label">Chi nhánh</label>
                                            <textarea name="branch" placeholder="Số tài khoản" rows="1" class="form-control input-text">{{ old('branch', $payment_info->branch) }}</textarea>
                                            @if ($errors->has('branch'))
                                            <em class="help-block">{{ $errors->first('branch') }}</em>
                                            @endif
                                        </div>
                                        <div class="form-group">
                                            @if( in_array('admin.building.info.save',@$user_access_router))
                                                <button type="submit" class="btn btn-success" form="form-building-payment" value="submit">{{ $id ? 'Cập nhật' : 'Thêm mới' }}</button>
                                            @endif
                                            @if( in_array('admin.building.info.index',@$user_access_router))
                                                <a href="{{ route('admin.building.info.index') }}" class="btn btn-danger" form="form-building-payment" value="submit">Quay lại</a>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    @endif
</section>

@endsection

@section('javascript')

<!-- Datetime Picker -->
<script src="/adminLTE/plugins/moment/moment.min.js"></script>
@endsection