<style>
#custom-css-group label {
    cursor: pointer;
}
</style>
@php
$criterion = $group->criterion;
@endphp
<div id="custom-css-group">
    <label class="control-label">Tiêu chí xây dựng nhóm</label>
    {{-- Địa chỉ --}}
    <div class="notify-group">
        <label data-toggle="show" data-target="#address">
            <input type="checkbox" name="" class="iCheck" @if ( !empty($criterion['address']) || old('address')['city'] || old('address')['district'] ) checked @endif>
            Địa chỉ
        </label>
        <div id="address" style="{{ ( !empty($criterion['address']) || old('address')['city'] || old('address')['district'] ) ? '' : 'display: none;' }} margin: 5px 0px 15px;">
            <div class="row" style="padding-left: 30px;">
                <div class="col-sm-6 col-xs-12 form-group">
                    <label class="control-label">Tỉnh/Thành phố</label>

                    <select id="criterion-city" class="form-control" name="address[city]" style="width: 100%;">
                        <option value="">Chọn tỉnh/Thành phố</option>
                        @if( !empty($criterion['address']['city']) || old('address')['city'] )
                        @php
                        $code = $criterion['address']['city'] ?? old('address')['city'] ?? 0 ;
                        $city = \App\Models\City::findBy([['code', '=', $code ]])->first();
                        @endphp
                        <option value="{{ $code }}" selected="">{{ $city->name }}</option>
                        @endif
                    </select>
                </div>
                <div class="col-sm-6 col-xs-12 form-group">
                    <label class="control-label">Quận/Huyện</label>
                    <select id="criterion-district" class="form-control" name="address[district]" style="width: 100%;">
                        <option value="">Chọn quận/huyện</option>
                        @if( !empty($criterion['address']['district']) || old('address')['district'] )
                        @php
                        $code = $criterion['address']['district'] ?? old('address')['district'] ?? 0;
                        $district = \App\Models\District::findBy([['code', '=', $code ]])->first();
                        @endphp
                        <option value="{{ $code }}" selected="">{{ $district->name }}</option>
                        @endif
                    </select>
                </div>
            </div>
        </div>
    </div>

    {{-- Ngày sinh --}}
    <div class="notify-group">
        <label data-toggle="show" data-target="#birthday">
            <input type="checkbox" name="" class="iCheck" @if ( !empty($criterion['birthday']) || old('birthday')['from'] || old('birthday')['to'] ) checked @endif />
            Ngày sinh
        </label>
        <div id="birthday" style="{{ ( !empty($criterion['birthday']) || old('birthday')['from'] || old('birthday')['to'] ) ? '' : 'display: none;' }} margin: 5px 0px 15px;">
            <div class="row" style="padding-left: 30px;">
                <div class="col-sm-6 form-group">
                    <label class="control-label">Từ ngày</label>
                    <div class="input-group datetimepicker" data-format="DD-MM-YYYY">
                        <input type="text" name="birthday[from]" value="{{ $criterion['birthday']['from'] ?? old('birthday')['from'] ?? '' }}" class="form-control" placeholder="Ngày sinh">
                        <span class="input-group-addon btn"><i class="fa fa-calendar"></i></span>
                    </div>
                </div>
                <div class="col-sm-6 form-group">
                    <label class="control-label">Đến ngày</label>
                    <div class="input-group datetimepicker" data-format="DD-MM-YYYY">
                        <input type="text" name="birthday[to]" value="{{ $criterion['birthday']['to'] ?? old('birthday')['to'] ?? '' }}" class="form-control" placeholder="Ngày sinh">
                        <span class="input-group-addon btn"><i class="fa fa-calendar"></i></span>
                    </div>
                </div>
            </div>

        </div>
    </div>

    {{-- Mức độ quan tâm --}}
    <div class="notify-group">
        <label data-toggle="show" data-target="#status">
            <input type="checkbox" name="" class="iCheck" @if( isset($criterion['status']) || old('status') !==null ) checked @endif }} />
            Mức độ quan tâm
        </label>
        <div id="status" style="{{ ( isset($criterion['status']) || old('status') !== null) ? '' : 'display: none;' }} margin: 5px 0px 15px;">
            <div style="padding-left: 30px;">
                @php
                $status = $criterion['status'] ?? old('status') ?? '';
                @endphp
                <label style="margin-right: 20px;">
                    <input type="radio" class="iCheck" value="1" name="status" @if ( $status==='1' ) checked @endif />
                    Quan tâm
                </label>
                <label style="margin-right: 20px;">
                    <input type="radio" class="iCheck" value="0" name="status" @if ( $status==='0' ) checked @endif />
                    Không quan tâm
                </label>

                <label>
                    <input type="radio" class="iCheck" value="" name="status" />
                    Loại bỏ tiêu chí này
                </label>
            </div>
        </div>
    </div>

    {{-- Nguồn khách hàng --}}
    <div class="notify-group">
        <label data-toggle="show" data-target="#source">
            <input type="checkbox" name="" class="iCheck" {{ !empty($criterion['cb_source']) || old('cb_source') ? 'checked' : '' }} />
            Nguồn khách hàng
        </label>
        <div id="source" style="{{ ( !empty($criterion['cb_source']) || old('cb_source')) ? '' : 'display: none;' }} margin: 5px 0px 15px;">
            <div style="padding-left: 30px;">
                @php
                $customer_source = App\Models\Setting::config_get('customer-source');
                @endphp
                <select class="form-control select2" name="cb_source" id="select-bo-project" style="width: 100%;">
                    <option value="">Chọn nguồn</option>
                    @php
                    $code = $criterion['cb_source'] ?? old('cb_source') ?? '';
                    @endphp
                    @foreach($customer_source->config_value as $key => $source)
                    <option value="{{ $key }}" @if ( $code==$key ) selected @endif>{{ $source }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    {{-- Dự án --}}
    <div class="notify-group">
        <label data-toggle="show" data-target="#project">
            <input type="checkbox" name="" class="iCheck" {{ (!empty($criterion['project']) || old('project') ) ? 'checked' : '' }} />
            Dự án
        </label>
        <div id="project" style="{{ (!empty($criterion['project'])  || old('project')) ? '' : 'display: none;' }} margin: 5px 0px 15px;">
            <div style="padding-left: 30px;">
                <select name="project[]" class="form-control" id="select-project" style="width: 100%;" multiple>
                    <option value="">Chọn dự án</option>
                    @if(!empty($criterion['project']) || old('project') )
                    @php
                    $projectIds = $criterion['project'] ?? old('project') ?? [];
                    @endphp
                    @foreach($projectIds as $project_id)
                    @php
                    $project = \App\Models\BoCategory::findById($project_id);
                    @endphp
                    <option value="{{ $project->cb_id }}" selected="">{{ $project->cb_title }}</option>
                    @endforeach
                    @endif
                </select>
            </div>
        </div>
    </div>
</div>
@section('javascript')
<script src="/adminLTE/plugins/moment/moment.min.js"></script>
<script src="/adminLTE/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js"></script>
<script>
$(function() {
    // Chọn dự án cho khách hàng
    get_data_select2({
        object: '#select-project',
        url: '{{ url("/admin/bo-customers/ajax/get-all-project") }}',
        data_id: 'cb_id',
        data_text: 'cb_title',
        title_default: 'Chọn dự án'
    });

    // Chọn tỉnh thành
    get_data_select2({
        object: '#criterion-city',
        url: '{{ url("/admin/cities/ajax-get-city") }}',
        data_id: 'code',
        data_text: 'name',
        title_default: 'Chọn tỉnh/thành phố'
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

    $('#criterion-district').select2({
        ajax: {
            url: '{{ url("/admin/branches/ajax/address") }}',
            dataType: 'json',
            data: function(params) {
                var city = $('#criterion-city').val();
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

    $('.btn-delete-group').click(function() {
        if (confirm('Có chắc bạn muốn xóa?')) {
            var hash = location.hash;
            $('input[name="hashtag_group"]').val(hash);
            $('#form-delete-customer').submit();
        }
    });

});

sidebar('bo-customers', 'group');
</script>
@endsection