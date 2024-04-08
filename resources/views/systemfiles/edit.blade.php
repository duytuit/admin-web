@extends('backend.layouts.master')

@section('content')

    <section class="content-header">
        <h1>
            files
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('admin.home') }}"><i class="fa fa-home"></i> Trang chủ</a></li>
            <li class="active">files</li>
        </ol>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-heading">Edit files</div>

                    <div class="panel-body">
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        <div class="form-group">
                            <form action="" method="post" id="form-edit-vehicles">
                                {{ csrf_field() }}
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="ip-name">Tên file</label>
                                        <input type="text" name="name" id="ip-name-file" class="form-control" placeholder="Tên file" value="{{ $file->name ?? old('name') ?? ''}}">
                                    </div>
                                    <div class="form-group">
                                        <label for="ip-cmt_nc">căn hộ</label>
                                        <select name="bdc_apartment_id" id="ip-ap_id" class="form-control" style="width: 100%;">
                                            <option value="">Chọn căn hộ</option>
                                            <?php $apartment_id = $file->more_id ?? old('bdc_apartment_id') ?? ''; ?>
                                            @if($apartment_id)
                                                <option value="{{$apartment_id}}" selected>{{ $apartment->name ?? '' }}</option>
                                            @endif
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="ip-email">file</label>
                                        <a href="{{ route('admin.systemfiles.download',['path'=>$file->url]) }}"><i class="fa fa-download"></i> Tải file</a>
                                        <input type="file" name="file_apartment" id="ip-file" class="form-control" v>
                                    </div>
                                    <div class="form-group">
                                        <label for="ip-address">Mô tả</label>
{{--                                        <input type="text" name="address" id="ip-address" class="form-control" placeholder="Địa chỉ" value="{{ $userProfile->address ?? old('address') ?? ''}}">--}}
                                        <textarea name="description" id="textarea-vc_description" class="form-control" cols="30" rows="5" placeholder="Mô tả file">{{ $vehicle->description ?? old('description') ?? ''}}</textarea>
                                    </div>

                                </div>
                                <div class="clearfix"></div>
                                <div class="form-group">
                                    <button type="submit" class="btn btn-sm btn-success" title="Cập nhật" form="form-edit-vehicles">
                                        <i class="fa fa-save"></i>&nbsp;Cập nhật
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

@endsection
@section('stylesheet')
    <link rel="stylesheet" href="/adminLTE/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css" />
@endsection
@section('javascript')
    <script src="/adminLTE/plugins/moment/moment.min.js"></script>
    <script src="/adminLTE/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js"></script>

    <script>
        $(function () {
            get_data_select({
                object: '#ip-ap_id',
                url: '{{ url('admin/apartments/ajax_get_apartment') }}',
                data_id: 'id',
                data_text: 'name',
                title_default: 'Chọn căn hộ'
            });
            get_data_select({
                object: '#select-vc_type',
                url: '{{ url('admin/vehiclecategory/ajax_get_vehicle_cate') }}',
                data_id: 'id',
                data_text: 'name',
                title_default: 'Chọn loại phương tiện'
            });
            function get_data_select(options) {
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
        });
        sidebar('apartments', 'create');
    </script>

@endsection
