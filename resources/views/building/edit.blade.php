@extends('backend.layouts.master')

@section('content')
<section class="content-header">
    <h1>
        Quản lý chung
        <small>Chỉnh sửa thông tin tòa nhà</small>
    </h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.home') }}"><i class="fa fa-home"></i> Trang chủ</a></li>
        <li class="active">Chỉnh sửa thông tin tòa nhà</li>
    </ol>
</section>
<section class="content">
    <div class="box-body">
        <form action="{{ route('admin.building.update') }}" method="POST" class="form-horizontal">
            {{ csrf_field() }}
            <div class="row">
                <div class="col-md-12">
                    <!-- Horizontal Form -->
                    <div class="box box-info">
                        <div class="box-header with-border">
                            <h3 class="box-title">Sửa đổi thông tin tòa nhà</h3>
                        </div>
                        <!-- /.box-header -->
                        <!-- form start -->
                        <div class="box-body">
                            <input type="hidden" name="id" value="{{ @$building->id }}">
                            <div class="form-group {{ $errors->has('name') ? ' has-error' : '' }}">
                                <label class="col-md-3 control-label">Tên tòa nhà (*)</label>
                                <div class="col-md-6">
                                    <input type="text" class="form-control valid" id="name" name="name"
                                        value="{{ old('name') ?? @$building->name }}">
                                    @if ($errors->has('name'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('name') }}</strong>
                                    </span>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group {{ $errors->has('description') ? ' has-error' : '' }}">
                                <label class="col-md-3 control-label">Mô tả</label>
                                <div class="col-md-6">
                                    <textarea class="form-control valid" id="description" name="description"
                                        rows="5">{{ old('description') ?? @$building->description }}</textarea>
                                    @if ($errors->has('description'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('description') }}</strong>
                                    </span>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group {{ $errors->has('address') ? ' has-error' : '' }}">
                                <label class="col-md-3 control-label">Địa chỉ (*)</label>
                                <div class="col-md-6">
                                    <textarea class="form-control" id="address" name="address"
                                        rows="3">{{ old('address') ?? @$building->address }}</textarea>
                                    @if ($errors->has('address'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('address') }}</strong>
                                    </span>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group {{ $errors->has('address_payment') ? ' has-error' : '' }}">
                                <label class="col-md-3 control-label">Địa chỉ đóng tiền mặt(*)</label>
                                <div class="col-md-6">
                                    <textarea class="form-control" id="address_payment" name="address_payment"
                                        rows="3">{{ old('address_payment') ?? @$building->address_payment }}</textarea>
                                    @if ($errors->has('address_payment'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('address_payment') }}</strong>
                                    </span>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group {{ $errors->has('phone') ? ' has-error' : '' }}">
                                <label class="col-md-3 control-label">Mobile</label>
                                <div class="col-md-6">
                                    <input type="text" class="form-control" id="phone" name="phone"
                                        value="{{old('phone') ?? @$building->phone }}">
                                    @if ($errors->has('phone'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('phone') }}</strong>
                                    </span>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group {{ $errors->has('email') ? ' has-error' : '' }}">
                                <label class="col-md-3 control-label">Email (*)</label>
                                <div class="col-md-6">
                                    <input type="text" class="form-control" id="email" name="email"
                                        value="{{ old('email') ?? @$building->email }}">
                                </div>
                                @if ($errors->has('email'))
                                <span class="help-block">
                                    <strong>{{ $errors->first('email') }}</strong>
                                </span>
                                @endif
                            </div>
                            @if(Auth::user()->isadmin ==1)
                                <div class="form-group {{ $errors->has('day_lock_cycle_name') ? ' has-error' : '' }}">
                                    <label class="col-md-3 control-label">Ngày khóa sổ kế toán (*)</label>
                                    <div class="col-md-6">
                                        <input type="number" class="form-control valid" id="day_lock_cycle_name" min="0" max="31" name="day_lock_cycle_name" value="{{ @$building->day_lock_cycle_name }}">
                                        @if ($errors->has('day_lock_cycle_name'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('day_lock_cycle_name') }}</strong>
                                        </span>
                                        @endif
                                    </div>
                                </div>
                            @endif
                            <div class="form-group {{ $errors->has('bdc_department_id') ? ' has-error' : '' }}">
                                <label class="col-md-3 control-label">Bộ phận giám sát (*)</label>
                                <div class="col-md-6">
                                        <select name="bdc_department_id" id="bdc_department_id" class="form-control">
                                            <option value="" selected>Chọn bộ phận</option>
                                            @foreach($departments as $department)
                                            <option value="{{ $department->id }}" @if($department->id==$building->bdc_department_id) selected @endif>
                                              {{ $department->name }}</option>
                                            @endforeach
                                          </select>
                                    @if ($errors->has('bdc_department_id'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('bdc_department_id') }}</strong>
                                    </span>
                                    @endif
                                </div>
                            </div>
                             <div class="form-group {{ $errors->has('template_mail') ? ' has-error' : '' }}">
                                    <label class="col-md-3 control-label">Template email</label>
                                    <div class="col-md-6">
                                         <select name="template_mail" id="template_mail" class="form-control">
                                                <option value="" selected>Chọn</option>
                                            @foreach ($template_emails as $value)
                                                <option value="{{ $value }}" @if($value == $building->template_mail) selected @endif> {{ $value }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group {{ $errors->has('vnp_secret') ? ' has-error' : '' }}">
                                    <label class="col-md-3 control-label">Hash Key</label>
                                    <div class="col-md-6">
                                        <input type="text" class="form-control" id="vnp_secret" name="vnp_secret"
                                               value="{{ old('vnp_secret') }}">
                                        @if ($errors->has('vnp_secret'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('vnp_secret') }}</strong>
                                        </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- /.box-body -->
                        <div class="box-footer">
                            <a href="/admin/building#thong_tin_lien_he" type="button"
                                class="btn btn-default pull-left">Quay lại</a>
                            <button type="submit" class="btn btn-success pull-right">Cập nhật</button>
                        </div>
                        <!-- /.box-footer -->
                    </div>
                    <!-- /.box -->
                </div>
            </div>
        </form>
    </div>
</section>
@endsection