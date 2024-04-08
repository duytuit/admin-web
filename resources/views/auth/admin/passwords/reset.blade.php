@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Reset mật khẩu</div>

                <div class="card-body">
                    <form method="POST" action="{{ route('password.update') }}" autocomplete="off">
                        @csrf

                        <input type="hidden" name="token" value="{{ $token }}">

                        <div class="form-group row  {{ $errors->has('password') ? 'has-error': '' }}">
                            <label for="password" class="col-md-4 col-form-label text-md-right">Mật khẩu</label>

                            <div class="col-md-6">
                                <input id="password" type="password" class="form-control @if ($errors->has('email')) is-invalid @endif" name="password" required autocomplete="new-password">
                                @if ($errors->has('password'))
                                <em class="invalid-feedback">{{ $errors->first('password') }}</em>
                                @endif
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="password-confirm" class="col-md-4 col-form-label text-md-right">Xác nhận mật khẩu</label>

                            <div class="col-md-6">
                                <input id="password-confirm" type="password" class="form-control @if ($errors->has('email')) is-invalid @endif" name="password_confirmation" required autocomplete="new-password">
                                @if ($errors->has('password_confirmation'))
                                <em class="invalid-feedback">{{ $errors->first('password_confirmation') }}</em>
                                @endif
                            </div>
                        </div>

                        <div class="form-group row mb-0">
                            <div class="col-md-6 offset-md-4">
                                <button class="btn btn-primary">
                                    Reset mật khẩu
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection