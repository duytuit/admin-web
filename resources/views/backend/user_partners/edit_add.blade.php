@extends('backend.layouts.master')

@section('content')
<section class="content-header">
    <h1>
        Đối tác
        <small>Tài khoản</small>
    </h1>
    <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Trang chủ</a></li>
        <li><a href='{{ url("/admin/partners") }}'>Đối tác</a></li>
        <li class="active">Tài khoản</li>
    </ol>
</section>

<section class="content">
    <form action="" method="post" id="form-edit-add-user-partner" class="form-validate">
        {{ csrf_field() }}
        <div class="row">
            <div class="col-lg-8 col-md-8 col-sm-8 col-xs-8">
                <div class="box box-primary">
                    <div class="box-body">
                        <!-- Thông tin tài khoản đối tác -->
                        <div class="row">
                            <div class="col-sm-6 col-xs-12 form-group {{ $errors->has('full_name') ? 'has-error': '' }}">
                                <label class="control-label">Họ và tên <span class="text-danger">*</span></label>
                                <input type="text" class="form-control resize-disabled {{ $errors->has('full_name') ? 'has-error': '' }}" name="full_name" placeholder="Họ tên đầy đủ" value="{{ $user_partner->full_name ?? old('full_name') ?? $user_partner->full_name ?? ''}}" />

                                @if ($errors->has('full_name'))
                                <em class="help-block">{{ $errors->first('full_name') }}</em>
                                @endif
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-sm-6 col-xs-12 form-group {{ $errors->has('email') ? 'has-error': '' }}">
                                <label class="control-label">Tài khoản đăng nhập <span class="text-danger">*</span></label>
                                <input type="email" name="email" class="form-control" placeholder="VD: abc@gmail.com" value="{{ $user_partner->email ?? old('email') ?? $user_partner->email ?? '' }}" />
                                @if ($errors->has('email'))
                                <em class="help-block">{{ $errors->first('email') }}</em>
                                @endif
                            </div>

                            <div class="col-sm-6 col-xs-12 form-group {{ $errors->has('phone') ? 'has-error': '' }}">
                                <label class="control-label">SĐT <span class="text-danger">*</span></label>
                                <input type="text" name="phone" class="form-control" placeholder="Nhập số điện thoại" value="{{ !empty($user_partner)?$user_partner->phone:'' }}" />
                                @if ($errors->has('phone'))
                                <em class="help-block">{{ $errors->first('phone') }}</em>
                                @endif
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-sm-6 col-xs-12 form-group {{ $errors->has('partner_id') ? 'has-error': '' }}">
                                <label class="control-label">Đối tác <span class="text-danger">*</span></label>
                                <select class="form-control select2" name="partner_id" id="select-partner">
                                    <option value="">Chọn đối tác</option>
                                    @foreach($partners as $partner)
                                    <option value="{{$partner->id}}" @if( !empty($user_partner) && ($user_partner->partner_id == $partner->id) ) selected @endif>{{$partner->name}}</option>
                                    @endforeach
                                </select>
                                @if ($errors->has('partner_id'))
                                <em class="help-block">{{ $errors->first('partner_id') }}</em>
                                @endif
                            </div>

                            <div class="col-sm-6 col-xs-12 form-group">
                                <label class="control-label">Chi nhánh</label>
                                <select class="form-control" name="branch_id" id="select-branch">
                                    <option value="">Chọn chi nhánh</option>
                                    @if(!empty($user_partner->branch_id))
                                    <option value="{{ $user_partner->branch_id }}" selected>{{ $user_partner->branch_name }}</option>
                                    @endif
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-sm-6 col-xs-12 form-group {{ $errors->has('password') ? 'has-error': '' }}">
                                <label class="control-label">Mật khẩu @if($id == 0)<span class="text-danger">*</span>@endif </label>
                                <span class="comment-title">(Mật khẩu mặc định: 123456)</span></label>
                                <input type="password" name="password" class="form-control" placeholder="Nhập mật khẩu" value="{{ $user_partner->password ? '': '123456' }}" />
                                @if ($errors->has('password'))
                                <em class="help-block">{{ $errors->first('password') }}</em>
                                @endif
                            </div>

                            <div class="col-sm-6 col-xs-12 form-group {{ $errors->has('check_password') ? 'has-error': '' }}">
                                <label class="control-label">Xác nhận mật khẩu @if($id == 0)<span class="text-danger">*</span>@endif </label>
                                <input type="password" name="check_password" class="form-control " placeholder="Xác nhận lại mật khẩu" value="" />
                                @if ($errors->has('check_password'))
                                <em class="help-block">{{ $errors->first('check_password') }}</em>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xs-4">
                <div class="box box-primary">
                    <div class="box-body">
                        <div class="form-group">
                            <label class="control-label">Ảnh đại diện</label>
                            <div class="input-group input-image" data-file="image">
                                <input type="text" name="avatar" value="{{ !empty($user_partner)? $user_partner->avatar : '' }}" class="form-control"><span class="input-group-btn"><button type="button" class="btn btn-primary">Chọn</button></span>
                            </div>
                            <img src="{{ !empty($user_partner) ? $user_partner->avatar : ''}}" alt="" style="max-width: 200px; margin-top: 10px;" />
                        </div>

                        <div class="form-group">
                            <label class="control-label">Trạng thái</label><br />
                            <div>
                                <label class="switch">
                                    <input type="checkbox" name="status" value="1" {{ $user_partner->status === 0 ? '' : 'checked' }} />
                                    <span class="slider round"></span>
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="box-footer">
                        @can('update', app(App\Models\UserPartner::class))
                        <button type="submit" class="btn btn-sm btn-success" title="Cập nhật" form="form-edit-add-user-partner"><i class="fa fa-save"></i>&nbsp;&nbsp;{{!empty($partner) ? 'Cập nhật' : 'Thêm mới'}}</button>
                        @endcan
                    </div>
                </div>
            </div>
        </div>
    </form>

</section>
@endsection

@section('javascript')
<script>
    $(document).ready(function() {
    var selectBranch = $('#select-branch');

    selectBranch.select2({
        ajax: {
            url: '{{ url("/admin/branches/ajax/search-partner-branch") }}',
            dataType: 'json',
            data: function(params) {
                var partner_id = $('#select-partner').val();
                var query = {
                    search: params.term,
                    partner_id: partner_id
                }
                return query;
            },
            processResults: function(data, params) {
                var results = [];
                for (i in data.data) {
                    var item = data.data[i];
                    results.push({
                        id: item.id,
                        text: item.title
                    });
                }
                return {
                    results: results
                };
            },
        }
    });
});

sidebar('partners', 'user-partner');
</script>

<!-- InputMask -->
<script src="/adminLTE/plugins/input-mask/jquery.inputmask.js"></script>
<script src="/adminLTE/plugins/input-mask/jquery.inputmask.date.extensions.js"></script>
<script src="/adminLTE/plugins/input-mask/jquery.inputmask.extensions.js"></script>

<!-- TinyMCE -->
<script src="/adminLTE/plugins/tinymce/tinymce.min.js"></script>
<script src="/adminLTE/plugins/tinymce/config.js"></script>
<script>
    //Money Euro
$('[data-mask]').inputmask();
</script>

<script type="text/javascript" src="{{ asset('vendor/jsvalidation/js/jsvalidation.js')}}"></script>
@endsection