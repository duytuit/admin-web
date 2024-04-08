@extends('backend.layouts.master')

@section('content')

<section class="content-header">
    <h1>
        Cập nhật Mã tài khoản kế toán
    </h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.home') }}"><i class="fa fa-home"></i> Trang chủ</a></li>
        <li class="active">Mã tài khoản kế toán</li>
    </ol>
</section>

<section class="content">
    @if( in_array('admin.accounting.account.save',@$user_access_router))
        <form id="form-building-payment" action="{{ route('admin.accounting.account.save', ['id' => $id]) }}" method="post" autocomplete="off">
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
                                            <label class="control-label">Mã tài khoản kế toán</label>
                                            <textarea name="code" placeholder="Số tài khoản" rows="1" class="form-control input-text">{{ old('code', $accounting_accounts->code) }}</textarea>
                                            @if ($errors->has('code'))
                                            <em class="help-block">{{ $errors->first('code') }}</em>
                                            @endif
                                        </div>
                                        <div class="form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                                            <label class="control-label">Tên tài khoản kế toán</label>
                                            <textarea name="name" placeholder="Số tài khoản" rows="1" class="form-control input-text">{{ old('name', $accounting_accounts->name) }}</textarea>
                                            @if ($errors->has('name'))
                                            <em class="help-block">{{ $errors->first('name') }}</em>
                                            @endif
                                        </div>
                                        <div class="form-group">
                                            @if( in_array('admin.accounting.account.save',@$user_access_router))
                                                <button type="submit" class="btn btn-success" form="form-building-payment" value="submit">{{ $id ? 'Cập nhật' : 'Thêm mới' }}</button>
                                            @endif
                                            @if( in_array('admin.accounting.account.index',@$user_access_router))
                                                <a href="{{ route('admin.accounting.account.index') }}" class="btn btn-danger" form="form-building-payment" value="submit">Quay lại</a>
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