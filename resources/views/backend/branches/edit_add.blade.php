@extends('backend.layouts.master')

@section('content')
<section class="content-header">
    <h1>
        Đối tác
        <small>Cập nhật chi nhánh</small>
    </h1>
    <ol class="breadcrumb">
        <li><a href="{{ url('/admin
            ') }}"><i class="fa fa-home"></i> Trang chủ</a></li>
        <li class="active">Chi nhánh</li>
    </ol>
</section>

<section class="content">

    <form action="" method="post" id="form-edit-add-branch" class="form-validate">
        {{ csrf_field() }}

        <div class="row">
            <div class="col-lg-8 col-md-8 col-sm-8 col-xs-8">
                <div class="box box-primary">
                    <div class="box-body">
                        <div class="row">
                            <div class="col-sm-4 col-xs-12 form-group {{ $errors->has('partner_id') ? 'has-error': '' }}">
                                <label class="control-label">Đối tác <span class="text-danger">*</span></label>
                                <select class="form-control select2 " name="partner_id">
                                    @foreach($partners as $partner)
                                    <option value="{{$partner->id}}" @if( ($branch->partner_id == $partner->id) || (old('partner_id') == $partner->id) ) selected @endif>{{$partner->name}}</option>
                                    @endforeach
                                </select>
                                @if ($errors->has('partner_id'))
                                <em class="help-block">{{ $errors->first('partner_id') }}</em>
                                @endif
                            </div>

                            <div class="col-sm-8 col-xs-12 form-group {{ $errors->has('title') ? 'has-error': '' }}">
                                <label class="control-label">Tên chi nhánh <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="title" placeholder="Tên chi nhánh" value="{{ $branch->title ?? old('title') ?? '' }}" />
                                @if ($errors->has('title'))
                                <em class="help-block">{{ $errors->first('title') }}</em>
                                @endif
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="control-label">Thông tin chi nhánh</label>
                            <textarea name="info" rows="10" class="miniEditor form-control">{{ $branch->info ?? old('info') ?? ""}}</textarea>
                        </div>
                    </div>
                    <div class="box-footer">
                        @can('update', app(App\Models\Branch::class))
                        <button type="submit" class="btn btn-sm btn-success" title="Cập nhật" form="form-edit-add-branch"><i class="fa fa-save"></i>&nbsp;&nbsp;{{  $id ? 'Cập nhật' : 'Thêm mới'}}</button>
                        @endcan
                    </div>
                </div>
            </div>

            <div class="col-xs-4">
                <div class="box box-primary">
                    <div class="box-body">
                        <div class="form-group {{ $errors->has('city') ? 'has-error': '' }}">
                            <label class="control-label">Tỉnh/Thành phố <span class="text-danger">*</span></label>
                            <select id="branch-address-city" class="form-control" name="city" style="width: 100%">
                                <option value="">Chọn tỉnh/Thành phố</option>
                                @if(!empty($branch) && ($branch->city || old('city')) )
                                @php
                                $city_id = old('city') ?? $branch->city ?? '' ;
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
                            <select id="branch-address-district" class="form-control" name="district" style="width: 100%">
                                <option value="">Chọn quận/huyện</option>
                                @if(!empty($branch) && ($branch->district || old('district')) )
                                @php
                                $district_id = $branch->district ?? old('district') ?? '' ;
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
                            <label class="control-label">Địa chỉ <span class="text-danger">*</span></label>
                            <textarea rows="2" class="form-control resize-disabled " name="address" placeholder="Địa chỉ đối tác">{{ $branch->address ?? old('address') ?? ""}}</textarea>
                            @if ($errors->has('address'))
                            <em class="help-block">{{ $errors->first('address') }}</em>
                            @endif
                        </div>

                        <div class="form-group {{ $errors->has('representative') ? 'has-error': '' }}">
                            <label class="control-label">Người đại diện <span class="text-danger">*</span></label>
                            <textarea rows="1" class="form-control resize-disabled " name="representative" placeholder="Tên người đại diện">{{ $branch->representative ?? old('representative') ?? ""}}</textarea>
                            @if ($errors->has('representative'))
                            <em class="help-block">{{ $errors->first('representative') }}</em>
                            @endif
                        </div>

                        <div class="form-group">
                            <label class="control-label">Hotline</label>
                            <input type="text" name="hotline" class="form-control " value="{{ $branch->hotline ?? old('hotline') ?? ''}}" placeholder="Hotline">
                        </div>

                        <div class="form-group">
                            <label class="control-label">Trạng thái</label><br />
                            <div>
                                <label class="switch">
                                    <input type="checkbox" name="status" value="1" {{ $branch->status === 0 ? '' : 'checked' }} />
                                    <span class="slider round"></span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</section>
@endsection

@section('javascript')
<!-- InputMask -->
<script src="/adminLTE/plugins/input-mask/jquery.inputmask.js"></script>
<script src="/adminLTE/plugins/input-mask/jquery.inputmask.date.extensions.js"></script>
<script src="/adminLTE/plugins/input-mask/jquery.inputmask.extensions.js"></script>

<!-- TinyMCE -->
<script src="/adminLTE/plugins/tinymce/tinymce.min.js"></script>
<script src="/adminLTE/plugins/tinymce/config.js"></script>

<script>
    $('#datemask').inputmask('dd/mm/yyyy', {
    'placeholder': 'dd/mm/yyyy'
})
//Datemask2 mm/dd/yyyy
$('#datemask2').inputmask('mm/dd/yyyy', {
    'placeholder': 'mm/dd/yyyy'
})
//Money Euro
$('[data-mask]').inputmask()


$('#branch-address-district').select2({
    ajax: {
        url: '{{ url("/admin/branches/ajax/address") }}',
        dataType: 'json',
        data: function(params) {
            var city = $('#branch-address-city').val();
            var query = {
                search: params.term,
                city: city
            }
            return query;
        },
        processResults: function(data, params) {
            var results = [];

            for (i in data) {
                var item = data[i];
                results.push({
                    id: item.code,
                    text: item.name
                });
            }
            return {
                results: results
            };
        },
    }
});
// Chọn tỉnh/ thành phố
get_data_select2({
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

function get_data_select2(options) {
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

sidebar('partners', 'branch');
</script>
@endsection