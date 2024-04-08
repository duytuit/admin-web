@extends('backend.layouts.master')

@section('content')
<section class="content-header">
    <h1>
        Địa điểm giao dịch
        <small>Cập nhật</small>
    </h1>
    <ol class="breadcrumb">
        <li><a href="{{ url('/admin') }}"><i class="fa fa-home"></i> Trang chủ</a></li>
        <li class="active">Địa điểm giao dịch</li>
    </ol>
</section>

<section class="content">

    <form action="" method="post" id="form-edit-add-exchange" class="form-validate">
        {{ csrf_field() }}

        <div class="row">
            <div class="col-sm-8 col-xs-12">
                <div class="box box-primary">
                    <div class="box-body">
                        <div class="row">
                            <div class="col-sm-6 col-xs-12 form-group {{ $errors->has('name') ? 'has-error': '' }}">
                                <label class="control-label">Tên địa điểm giao dịch <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="name" placeholder="Tên điểm giao dịch" value="{{ $exchange->name ?? old('name') ?? '' }}" />
                                @if ($errors->has('name'))
                                <em class="help-block">{{ $errors->first('name') }}</em>
                                @endif
                            </div>
                            <div class="col-sm-6 col-xs-12 form-group {{ $errors->has('hotline') ? 'has-error': '' }}">
                                <label class="control-label">Hotline <span class="text-danger">*</span></label>
                                <input type="text" name="hotline" class="form-control" value="{{ $exchange->hotline ?? old('hotline') ?? ''}}" placeholder="Hotline">

                                @if ($errors->has('hotline'))
                                <em class="help-block">{{ $errors->first('hotline') }}</em>
                                @endif
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-sm-6 col-xs-12 form-group {{ $errors->has('city') ? 'has-error': '' }}">
                                <label class="control-label">Tỉnh/Thành phố <span class="text-danger">*</span></label>
                                <select id="branch-address-city" class="form-control" name="city" style="width: 100%">
                                    <option value="">Chọn tỉnh/Thành phố</option>
                                    @if(old('city'))
                                        @php
                                            $city = \App\Models\City::findBy([['code', old('city')]])->first();
                                        @endphp
                                        <option value="{{ old('city') }}" selected="">{{ $city->name }}</option>
                                    @elseif(!empty($exchange->city))
                                        @php
                                            $city = \App\Models\City::findBy([['code', $exchange->city]])->first();
                                        @endphp
                                        <option value="{{ $exchange->city }}" selected>{{ $city->name }}</option>
                                    @endif
                                </select>
                                @if ($errors->has('city'))
                                <em class="help-block">{{ $errors->first('city') }}</em>
                                @endif
                            </div>

                            <div class="col-sm-6 col-xs-12 form-group {{ $errors->has('district') ? 'has-error': '' }}">
                                <label class="control-label">Quận/Huyện <span class="text-danger">*</span></label>
                                <select id="branch-address-district" class="form-control" name="district" style="width: 100%">
                                    <option value="">Chọn quận/huyện</option>
                                    @if(old('district'))
                                        @php
                                            $district = \App\Models\District::findBy([['code', old('district')]])->first();
                                        @endphp
                                        <option value="{{ old('district') }}" selected="">{{ $district->name }}</option>
                                    @elseif(!empty($exchange->district))
                                        @php
                                            $district = \App\Models\District::findBy([['code', $exchange->district]])->first();
                                        @endphp
                                        <option value="{{ $exchange->district }}" selected>{{ $district->name }}</option>
                                    @endif
                                </select>
                                @if ($errors->has('district'))
                                <em class="help-block">{{ $errors->first('district') }}</em>
                                @endif
                            </div>
                        </div>

                        <div class="form-group {{ $errors->has('address') ? 'has-error': '' }}">
                            <label class="control-label">Địa chỉ chi tiết <span class="text-danger">*</span></label>
                            <textarea rows="2" class="form-control resize-disabled " name="address" placeholder="Địa chỉ đối tác">{{ $exchange->address ?? old('address') ?? ""}}</textarea>
                            @if ($errors->has('address'))
                            <em class="help-block">{{ $errors->first('address') }}</em>
                            @endif
                        </div>

                        <div class="form-group">
                            <label class="control-label">Trạng thái</label><br />
                            <div>
                                <label class="switch">
                                    <input type="checkbox" name="status" value="1" {{ $exchange->status === '0'  ? '' : 'checked' }} />
                                    <span class="slider round"></span>
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="box-footer">
                        @can('update', app(App\Models\Exchange::class))
                        <button type="submit" class="btn btn-sm btn-success" title="Cập nhật" form="form-edit-add-exchange">
                            <i class="fa fa-save"></i>&nbsp;&nbsp;{{  $id ? 'Cập nhật' : 'Thêm mới'}}
                        </button>
                        @endcan
                    </div>
                </div>
            </div>

            <div class="col-sm-4 col-xs-12">
                <div class="box box-primary">
                    <div class="box-body">
                        <div class="form-group">
                            <input id="lat" name="location[lat]" value="{{ $exchange->location['lat'] ?? old('location[lat]') ?? 21.0196181 }}" type="text" class="form-control" />
                            <input id="long" name="location[long]" value="{{ $exchange->location['long'] ?? old('location[long]') ?? 105.726572 }}" type="text" class="form-control" /><br />

                            <label class="control-label">Bản đồ</label>
                            <div id="map_canvas" style="width: 100%; height: 300px;"></div>
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
{{-- google map api --}}
<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?v=3.exp&libraries=places&key={{ env('GOOGLE_API')}}"></script>

<script>
    //Money Euro
$('[data-mask]').inputmask()


// Chọn tỉnh/ thành phố
get_data_select2({
    object: '#branch-address-city',
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

$('#branch-address-district').select2({
    ajax: {
        url: '{{ url("/admin/cities/ajax-district") }}',
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
            for (i in data.data) {
                var item = data.data[i];
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

// google map api
var map;
var marker;
var lat = "{{ $exchange->location['lat'] ?? old('location[lat]') ?? 21.0196181 }}";
var long = "{{ $exchange->location['long'] ?? old('location[long]') ?? 105.726572 }}";

function initialize() {
    var myLatlng = new google.maps.LatLng(lat, long);

    var myOptions = {
        zoom: {{$id ? 10 : 8}},
        center: myLatlng,
        mapTypeId: google.maps.MapTypeId.ROADMAP
    };

    map = new google.maps.Map(document.getElementById("map_canvas"), myOptions);

    marker = new google.maps.Marker({
        draggable: true,
        position: myLatlng,
        map: map,
        title: "Your location"
    });

    google.maps.event.addListener(marker, 'dragend', function(event) {
        document.getElementById("lat").value = event.latLng.lat();
        document.getElementById("long").value = event.latLng.lng();
    });
}
google.maps.event.addDomListener(window, "load", initialize());

sidebar('exchanges', 'add');
</script>

<script>
    // This example requires the Places library. Include the libraries=places
    // parameter when you first load the API. For example:
    // <script src="https://maps.googleapis.com/maps/api/js?key=YOUR_API_KEY&libraries=places">

    var map;
    var service;
    var infowindow;

    $('#branch-address-district').on('change', function(){
        var district = $('#branch-address-district option:selected').text();
        var city = $('#branch-address-city option:selected').text();
        var query = district + ' ' + city + ' Việt Nam';
        var request = {
            query: query,
            fields: ['name', 'geometry'],
        };
        
        service = new google.maps.places.PlacesService(map);

        service.findPlaceFromQuery(request, function(results, status) {
            if (status === google.maps.places.PlacesServiceStatus.OK) {
                var latlng = results[0].geometry.location;

                $('#lat').val(latlng.lat());
                $('#long').val(latlng.lng());
                
                map.setZoom(10);
                map.setCenter(latlng);
                marker.setPosition(latlng);
            }
        });
    });
</script>

@endsection