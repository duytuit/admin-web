@extends('backend.layouts.master')

@section('content')

<section class="content-header">
    <h1>
        {{$meta_title}}
    </h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.home') }}"><i class="fa fa-home"></i> Trang chủ</a></li>
        <li >Nhân viên</li>
        <li class="active">Thêm mới</li>
    </ol>
</section>

<section class="content">
    <div class="box box-primary">
        <div class="box-body ">
            @if($errors->any())
            <em class="help-block text-red">{{$errors->first()}}</em>
            @endif
            <form id="form-users" action="{{ route('admin.users.create') }}" method="post">
                @csrf
                <input type="hidden" name="method" value="" />
                <input type="hidden" name="status" value="" />
                <div class="col-sm-12 col-xs-12 form-group {{ $errors->has('email') ? 'has-error': '' }}">
                    <label class="control-label">Email đăng nhập <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="email" placeholder="Email đăng nhập" value="{{  old('email') }}" />
                    @if ($errors->has('email'))
                    <em class="help-block">{{ $errors->first('email') }}</em>
                    @endif
                </div>

                <div class="col-sm-12 col-xs-12 form-group">
                    <label class="control-label">Tên nhân viên</label>
                    <input type="text" class="form-control" name="display_name" placeholder="Tên nhân viên" value="{{  old('display_name') }}" />
                </div>

                <div class="col-sm-12 col-xs-12 form-group">
                    <label class="control-label">Mã nhân viên</label>
                    <input type="text" class="form-control" name="code_name" placeholder="Mã nhân viên" value="{{  old('code_name') }}" />
                </div>

                <div class="col-sm-12 col-xs-12 form-group">
                    <label class="control-label">Số điện thoại</label>
                    <input type="text" class="form-control" name="phone" placeholder="Số điện thoại" value="{{  old('phone') }}" />
                </div>

                <div class="col-sm-12 col-xs-12 form-group">
                    <label class="control-label">Chứng minh thư/ hộ chiếu</label>
                    <input type="text" class="form-control" name="cmt" placeholder="Chứng minh thư/ hộ chiếu" value="{{  old('cmt') }}" />
                </div>

                <div class="col-sm-12 col-xs-12 form-group">
                    <label class="control-label">Nơi cấp</label>
                    <input type="text" class="form-control" name="cmt_address" placeholder="Nơi cấp" value="{{  old('cmt_address') }}" />
                </div>
                <div class="col-sm-12 col-xs-12 form-group">
                <label class="control-label">User Version</label>
                <input type="text" readonly name="data_type" value="V2" />
                </div>
                {{--<div class="col-sm-12 col-xs-12 form-group {{ $errors->has('password') ? 'has-error': '' }}">
                    <label class="control-label">Mật khẩu đăng nhập <span class="text-danger">*</span></label>
                    <input type="password" class="form-control" name="password" placeholder="Mật khẩu đăng nhập" value="{{  old('password') }}" />
                    @if ($errors->has('password'))
                    <em class="help-block">{{ $errors->first('password') }}</em>
                    @endif
                </div>--}}
                @if(Auth::user()->isadmin == 1)
                    <div class="col-sm-12 col-xs-12 form-group">
                        <div class="col-md-6">
                            @php
                                $company_id = 0;
                            @endphp
                            @foreach($buildings as $key => $value)
                                 @if($company_id != $value->company_id)
                                    @php
                                        $company = \App\Models\Building\Company::find($value->company_id);
                                    @endphp
                                    <div><h4>{{@$company->name}}</h4></div>
                                     @php
                                         $company_id = $value->company_id;
                                     @endphp
                                 @endif
                                <div class="col-md-12">
                                    <div class="col-sm-8">
                                        <div class="form-group">
                                            <label for="check_per_{{ $value->id }}">{{ $value->name }}</label>
                                        </div>
                                    </div>
                                    <div class="col-sm-2 group_check">
                                        <input type="checkbox" name="building_ids[]" value="{{ $value->id }}" class="iChecked checkSingle"/>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
                <div class="col-sm-12 col-xs-12 form-group ">
                    <button type="submit" class="btn btn-info pull-right">Tạo mới</button>

                </div>

            </form><!-- END #form-users -->
        </div>
    </div>
</section>
@endsection

@section('javascript')

<script>
    sidebar('users', 'users');
</script>

@endsection