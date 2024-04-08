@extends('backend.layouts.master')

@section('content')
<section class="content-header">
    <h1>
        Đối tác
        <small>Cập nhật</small>
    </h1>
    <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Trang chủ</a></li>
        <li class="active">Đối tác</li>
    </ol>
</section>

<section class="content">

    <form action="" method="post" id="form-edit-add-partner" class="form-validate">
        {{ csrf_field() }}
        <div class="row">
            <div class="col-lg-8 col-md-8 col-sm-8 col-xs-8">
                <div class="box no-border-top">
                    <div class="box-body no-padding">
                        <div class="nav-tabs-custom no-margin">
                            <ul class="nav nav-tabs">
                                <li class="active"><a href="#partner" data-toggle="tab">Thông tin</a></li>
                                @can('index', app(App\Models\Branch::class))
                                @if( !empty($partner->id) )
                                <li class=""><a href="#branch" data-toggle="tab">Chi nhánh</a></li>
                                @endif
                                @endcan
                            </ul>

                            <div class="tab-content">
                                <!-- Thông tin đối tác -->
                                <div class="tab-pane active" id="partner">
                                    <div class="row">
                                        <div class="col-sm-6 col-xs-12 form-group {{ $errors->has('name') ? 'has-error': '' }}">
                                            <label class="control-label">Tên đối tác <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" name="name" placeholder="Tên đối tác" value="{{ $partner->name ?? old('name') ?? $partner->name ?? ''}}" />
                                            @if ($errors->has('name'))
                                            <em class="help-block">{{ $errors->first('name') }}</em>
                                            @endif
                                        </div>

                                        <div class="col-sm-6 col-xs-12 form-group {{ $errors->has('company_name') ? 'has-error': '' }}">
                                            <label class="control-label">Tên công ty <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" name="company_name" placeholder="Tên công ty" value="{{ $partner->company_name ?? old('company_name') ?? $partner->company_name ?? ''}}" />
                                            @if ($errors->has('company_name'))
                                            <em class="help-block">{{ $errors->first('company_name') }}</em>
                                            @endif

                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label class="control-label">Thông tin đối tác</label>
                                        <textarea name="info" rows="10" class="mceEditor form-control">{{ $partner->info ?? old('info') ?? $partner->info ?? '' }}</textarea>
                                    </div>
                                </div>

                                <!-- Thông tin danh sách chi nhánh -->
                                @can('index', app(App\Models\Branch::class))
                                @if( !empty($partner->id) )
                                <div class="tab-pane fade" id="branch">
                                    <div class="form-group">
                                        <span><strong>Danh sách chi nhánh</strong></span>
                                        @can('update', app(App\Models\Branch::class))
                                        <a class="btn btn-social-icon btn-dropbox btn-sm" data-toggle="modal" data-target="#edit-add-branch"><i class="fa fa-plus"></i></a>
                                        @endcan
                                    </div>
                                    @if($branches->items())
                                    <table class="table table-striped table-bordered table-hover">
                                        <thead>
                                            <tr class="bg-primary">
                                                <th width='15px'>STT</th>
                                                <th>Tên Chi nhánh</th>
                                                <th width='15%'>Người đại diện</th>
                                                <th width='15%'>Hotline</th>
                                                <th width='20%'>Địa chỉ</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php
                                            $index = ($branches->perPage() * ($branches->currentPage() - 1));
                                            @endphp
                                            @foreach($branches as $branche)
                                            <tr>
                                                <td class="text-center">{{ $index += 1 }}</td>
                                                <td>
                                                    <a href='{{ url("/admin/branches/edit/{$branche->id}") }}'> {{ $branche->title }} </a>
                                                </td>
                                                <td>{{ $branche->representative }}</td>
                                                <td>{{ $branche->hotline }}</td>
                                                <td>{!! $branche->address !!}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                    <div class="pull-right link-paginate">
                                        {{ $branches->links() }}
                                    </div>
                                    @else
                                    Hiện chưa có chi nhánh nào.
                                    @endif
                                </div>
                                @endif
                                @endcan
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xs-4">
                <div class="box box-primary">
                    <div class="box-body">
                        <div class="form-group">
                            <label class="control-label">Logo</label>
                            <div class="input-group input-image" data-file="image">
                                <input type="text" name="logo" value="{{ old('image', $partner->logo) }}" class="form-control"><span class="input-group-btn"><button type="button" class="btn btn-primary">Chọn</button></span>
                            </div>
                            @if (old('logo', $partner->logo))
                            <img src="{{ old('logo', $partner->logo) }}" alt="" style="max-width: 200px;" />
                            @endif
                        </div>

                        <div class="form-group {{ $errors->has('city') ? 'has-error': '' }}">
                            <label class="control-label">Tỉnh/Thành phố <span class="text-danger">*</span></label>
                            <select id="address-city" class="form-control" name="city">
                                <option value="">Chọn tỉnh/Thành phố</option>
                                @if(!empty($partner) && ($partner->city || old('city')) )
                                @php
                                $city_id = old('city') ?? $partner->city ?? '' ;
                                $city = \App\Models\City::findBy([['code', $city_id]])->first();
                                @endphp
                                <option value="{{ $city->code }}" selected="">{{ $city->name }}</option>
                                @endif
                            </select>
                            @if ($errors->has('city'))
                            <em class="help-block">{{ $errors->first('city') }}</em>
                            @endif

                        </div>
                        <div class="form-group {{ $errors->has('district') ? 'has-error': '' }}">
                            <label class="control-label">Quận/Huyện <span class="text-danger">*</span></label>
                            <select id="address-district" class="form-control" name="district">
                                <option value="">Chọn quận/huyện</option>
                                @if(!empty($partner) && ($partner->district || old('district')) )
                                @php
                                $district_id = $partner->district ?? old('district') ?? '' ;
                                $district = \App\Models\District::findBy([['code', $district_id]])->first();
                                @endphp
                                <option value="{{ $district->code }}" selected="">{{ $district->name }}</option>
                                @endif
                            </select>
                            @if ($errors->has('district'))
                            <em class="help-block">{{ $errors->first('district') }}</em>
                            @endif

                        </div>

                        <div class="form-group {{ $errors->has('address') ? 'has-error': '' }}">
                            <label class="control-label">Địa chỉ chi tiết <span class="text-danger">*</span></label>
                            <textarea rows="2" class="form-control" name="address" placeholder="Địa chỉ đối tác">{{ $partner->address ? : ""}}</textarea>
                            @if ($errors->has('address'))
                            <em class="help-block">{{ $errors->first('address') }}</em>
                            @endif

                        </div>

                        <div class="form-group {{ $errors->has('representative') ? 'has-error': '' }}">
                            <label class="control-label">Người đại diện <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="representative" placeholder="Tên người đại diện" value="{{ $partner->representative ?? old('representative') ?? $partner->representative ?? ''}}" />
                            @if ($errors->has('representative'))
                            <em class="help-block">{{ $errors->first('representative') }}</em>
                            @endif
                        </div>

                        <div class="form-group {{ $errors->has('hotline') ? 'has-error': '' }}">
                            <label class="control-label">Hotline</label>
                            <input type="text" name="hotline" class="form-control" value="{{ $partner->hotline ?? old('hotline') ?? $partner->hotline ?? ''}}" placeholder="Hotline">
                            @if ($errors->has('hotline'))
                            <em class="help-block">{{ $errors->first('hotline') }}</em>
                            @endif
                        </div>

                        <div class="form-group">
                            <label class="control-label">Trạng thái</label><br />
                            <div>
                                <label class="switch">
                                    <input type="checkbox" name="status" value="1" {{ $partner->status === 0 ? '' : 'checked' }} />
                                    <span class="slider round"></span>
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="box-footer">
                        @can('update', app(App\Models\Partner::class))
                        <button type="submit" class="btn btn-sm btn-success" title="Cập nhật" form="form-edit-add-partner"><i class="fa fa-save"></i>&nbsp;&nbsp;{{!empty($partner) ? 'Cập nhật' : 'Thêm mới'}}</button>
                        @endcan

                        <a href="{{ route('admin.partners.index') }}" class="btn btn-danger btn-sm"><i class="fa fa-reply"></i> Quay lại</a>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <!-- Modal thêm mới chi nhánh -->
    @can('update', app(App\Models\Branch::class))
    @if( !empty($partner->id) )
    <div id="edit-add-branch" class="modal fade" role="dialog">
        <div class="modal-dialog  modal-lg">
            <!-- Modal content-->
            <form action="{{ url('/admin/partners/add-branch') }}" method="post" id="form-edit-add-branch" class="form-validate">
                {{ csrf_field() }}

                <input type="hidden" name="hashtag">

                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">Thêm mới chi nhánh</h4>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-danger print-error-msg" style="display:none">
                            <ul></ul>
                        </div>

                        <input type="hidden" name="branch[partner_id]" value="{{ $partner->id }}" />

                        <div class="box-body">
                            <div class="form-group">
                                <label class="control-label">Tên chi nhánh <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="branch[title]" placeholder="Tên chi nhánh" value="{{ $partner->title ?? old('title') ?? $partner->title ?? ''}}" />
                            </div>

                            <div class="row">
                                <div class="col-sm-6 col-xs-12 form-group">
                                    <label class="control-label">Người đai diện <span class="text-danger">*</span></label>
                                    <textarea rows="1" class="form-control resize-disabled" name="branch[representative]" placeholder="Người đai diện"></textarea>
                                </div>
                                <div class="col-sm-6 col-xs-12 form-group">

                                    <div class="form-group">
                                        <label class="control-label">Hotline</label>
                                        <input type="text" name="branch[hotline]" class="form-control" data-inputmask="&quot;mask&quot;: &quot;(999) 999-9999&quot;" data-mask="" value="">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-sm-6 col-xs-12 form-group">
                                    <label class="control-label">Tỉnh/Thành phố <span class="text-danger">*</span></label>
                                    <select id="branch-address-city" class="form-control" name="branch[city]" style="width: 100%">
                                        <option value="">Chọn tỉnh/Thành phố</option>
                                        @if(old('branch[city]') )
                                        @php
                                        $city_id = old('branch[city]') ? :'' ;
                                        $city = \App\Models\City::findBy([['code', $city_id]])->first();
                                        @endphp
                                        <option value="{{ $city->code }}" selected="">{{ $city->name }}</option>
                                        @endif
                                    </select>
                                </div>

                                <div class="col-sm-6 col-xs-12 form-group">
                                    <label class="control-label">Quận/Huyện <span class="text-danger">*</span></label>
                                    <select id="branch-address-district" class="form-control" name="branch[district]" style="width: 100%">
                                        <option value="">Chọn quận/huyện</option>
                                        @if(old('branch[district]') )
                                        @php
                                        $district_id = old('branch[district]') ?: 0 ;
                                        $district = \App\Models\District::find($district_id);
                                        @endphp
                                        <option value="{{ $district_id }}" selected="">{{ $district->name }}</option>
                                        @endif
                                    </select>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="control-label">Địa chỉ <span class="text-danger">*</span></label>
                                <textarea rows="2" class="form-control resize-disabled" name="branch[address]" placeholder="Địa chỉ chi nhánh"></textarea>
                            </div>

                            <div class="form-group">
                                <label class="control-label">Thông tin chi nhánh</label>
                                <textarea name="branch[info]" rows="10" class="miniEditor form-control"></textarea>
                            </div>

                            <div class="form-group">
                                <label class="control-label">Trạng thái</label><br />
                                <div>
                                    <label class="switch">
                                        <input type="checkbox" name="branch[status]" value="1" {{ $partner->status === 0 ? '' : 'checked' }} />
                                        <span class="slider round"></span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger pull-right" data-dismiss="modal"><i class="fa fa-close"></i>&nbsp;&nbsp;Hủy</button>
                        <button type="submit" class="btn btn-primary btn-js-action" style="margin-right: 5px;"><i class="fa fa-save"></i>&nbsp;&nbsp;Xác nhận</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    @endif
    @endcan

</section>
@endsection

@section('javascript')

<script>
    $(function() {

});

$(document).ready(function() {
    // Chọn tỉnh/ thành phố
    get_city_select2({
        object: '#address-city',
        url: '{{ url("/admin/cities/ajax-get-city") }}',
        data_id: 'code',
        data_text: 'name',
        title_default: 'Chọn tỉnh/thành phố'
    });
    get_district_select2({
        object: '#address-district',
        city: '#address-city',
        url: '{{ url("/admin/cities/ajax-district") }}',
        data_id: 'code',
        data_text: 'name',
        title_default: 'Chọn quận/huyện'
    });

    // Chọn tỉnh/ thành phố
    get_city_select2({
        object: '#branch-address-city',
        url: '{{ url("/admin/cities/ajax-get-city") }}',
        data_id: 'code',
        data_text: 'name',
        title_default: 'Chọn tỉnh/thành phố'
    });
    get_district_select2({
        object: '#branch-address-district',
        city: '#branch-address-city',
        url: '{{ url("/admin/cities/ajax-district") }}',
        data_id: 'code',
        data_text: 'name',
        title_default: 'Chọn quận/huyện'
    });


    function get_city_select2(options) {
        $(options.object).select2({
            ajax: {
                url: options.url,
                dataType: 'json',
                data: function(params) {
                    var query = {
                        search: params.term,
                    }
                    return query;
                },
                processResults: function(json, params) {
                    var results = [{
                        id: '',
                        text: options.title_default
                    }];

                    for (i in json.data) {
                        var item = json.data[i];
                        results.push({
                            id: item[options.data_id],
                            text: item[options.data_text]
                        });
                    }
                    return {
                        results: results,
                    };
                },
                minimumInputLength: 3,
            }
        });
    }

    function get_district_select2(options) {
        $(options.object).select2({
            ajax: {
                url: options.url,
                dataType: 'json',
                data: function(params) {
                    var city = $(options.city).val();
                    var query = {
                        search: params.term,
                        city: city
                    }
                    return query;
                },
                processResults: function(json, params) {
                    var results = [{
                        id: '',
                        text: options.title_default
                    }];

                    for (i in json.data) {
                        var item = json.data[i];
                        results.push({
                            id: item[options.data_id],
                            text: item[options.data_text]
                        });
                    }
                    return {
                        results: results,
                    };
                },
                minimumInputLength: 3,
            }
        });
    }

    $(".btn-js-action").click(function(e) {
        e.preventDefault();

        var _token = $("[name='_token']").val();
        var partner_id = $("[name='branch[partner_id]']").val();
        var title = $("[name='branch[title]']").val();
        var address = $("[name='branch[address]']").val();
        var representative = $("[name='branch[representative]']").val();
        var city = $("[name='branch[city]']").val();
        var district = $("[name='branch[district]']").val();

        $.ajax({
            url: "{{ url('/admin/partners/validator-add-branch') }}",
            type: 'POST',
            data: {
                _token: _token,
                partner_id: partner_id,
                title: title,
                representative: representative,
                address: address,
                city: city,
                district: district,
            },
            success: function(data) {
                if ($.isEmptyObject(data.error_branches)) {
                    var hash = location.hash;
                    $('input[name="hashtag"]').val(hash);
                    $('#form-edit-add-branch').submit();
                } else {
                    printErrorMsg(data.error_branches);
                }
            }
        });


    });

    function printErrorMsg(msg) {
        $(".print-error-msg").find("ul").html('');
        $(".print-error-msg").css('display', 'block');
        $.each(msg, function(key, value) {
            $(".print-error-msg").find("ul").append('<li>' + value + '</li>');
        });
    }
});
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

sidebar('partners', 'add');
</script>
@endsection