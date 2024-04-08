<div class="modal-header bg-primary">
    <button type="button" class="close" data-dismiss="modal">&times;</button>
    <h4 class="modal-title" style="display: inline-block">Thêm mới chiến dịch</h4>
</div>
<div class="modal-body">
    <form action='{{ url("admin/campaigns/{$id}/save") }}' method="post" id="form-edit-add-campaigns" class="form-validate" autocomplete="off" enctype="multipart/form-data">
        {{ csrf_field() }}
        <div class="row">
            <div class="col-xs-8">
                <div class="box no-border-top">
                    <div class="box-body no-padding">
                        <div class="nav-tabs-custom no-margin">
                           {{-- <ul class="nav nav-tabs">
                                <li class="active"><a href="#detail-campaign" data-toggle="tab">Thông tin cơ bản</a></li>
                            </ul>--}}

                            <div class="tab-content">
                                <!-- Thông tin cơ bản chiến dịch-->
                                <div class="tab-pane active" id="detail-campaign">
                                    {{-- Hiện thị thông báo lỗi --}}
                                    @if(session()->get('errors_user'))
                                        @php
                                            $errors_user = session()->get('errors_user');
                                        @endphp
                                        <div class="alert alert-danger">
                                            <p>{{ $errors_user['messages'] }}</p>
                                            <ul>
                                                @foreach ($errors_user['data'] as $error)
                                                    <li>
                                                        Tk: {{ $error->tvc_account }} - VT: dòng {{ $error->stt }}
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif

                                    @if(session()->get('duplicate'))
                                        @php
                                            $duplicate = session()->get('duplicate');
                                        @endphp
                                        <div class="alert alert-warning">
                                            <p>{{ $duplicate['success'] }}</p>
                                            <ul>
                                                @foreach ($duplicate['data'] as $key => $value)
                                                    @if ($value)
                                                        <p>Thông tin {{ $key == 'staff' ? 'nhân viên' : 'khách hàng' }} bị trùng</p>
                                                        @foreach ($value as $index => $item)
                                                            @if($key == 'staff')
                                                                <li>
                                                                    Tk: {{ $item->tvc_account }} - VT: dòng {{ $index }}
                                                                </li>
                                                            @else
                                                                <li>
                                                                    Tk: Tên: {{ $item['ten_khach_hang'] }}, Email: {{$item['email']}}, SĐT: {{$item['so_dien_thoai']}} - VT: dòng {{ $index }}
                                                                </li>
                                                            @endif
                                                        @endforeach
                                                    @endif
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif

                                    <div class="row">
                                        <div class="col-sm-6 col-xs-12 form-group {{ $errors->has('title') ? 'has-error': '' }}">
                                            <label class="control-label">Tên chiến dịch <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" name="title" placeholder="Tên chiến dịch" value="{{ $campaign->title ?? old('title') ?? ''}}" />
                                            @if ($errors->has('title'))
                                                <em class="help-block">{{ $errors->first('title') }}</em>
                                            @endif
                                        </div>

                                        <div class="col-sm-6 col-xs-12 form-group {{ $errors->has('project_id') ? 'has-error': '' }}">
                                            <label class="control-label">Dự án <span class="text-danger">*</span></label>
                                            <select class="form-control" name="project_id" id="select-bo-project" style="width: 100%;">
                                                <option value="">Chọn dự án</option>
                                                @if(old('project_id') )
                                                    @php
                                                        $project = \App\Models\Campaign::getProjectById(old('project_id'));
                                                    @endphp
                                                    <option value="{{ old('project_id') }}" selected="">{{ $project['cb_title'] }}</option>
                                                @elseif(!empty($campaign->project_id))
                                                    @php
                                                        $project = \App\Models\Campaign::getProjectById($campaign->project_id);
                                                    @endphp
                                                    <option value="{{ $campaign->project_id }}" selected>{{ $project['cb_title'] }}</option>
                                                @endif
                                            </select>
                                            @if ($errors->has('project_id'))
                                                <em class="help-block">{{ $errors->first('project_id') }}</em>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-sm-6 col-xs-12 form-group">
                                            <label class="control-label" style="padding-top: 0px;">File nhân viên - khách hàng</label>
                                            <input type="file" name="file_user_cus" />
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label class="control-label">Ghi chú</label>
                                        <textarea name="description" rows="10" class="miniEditor form-control">{{ $campaign->description ?? old('info') ?? $campaign->description ?? '' }}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xs-4">
                <div class="box box-primary">
                    <div class="box-body">
                        <div>
                            <a href="{{ route('admin.bo-customers.download_file', ['uuid' => '', 'file_name' => 'Import_khach_hang_nhan_vien.xlsx']) }}" class="btn btn-primary"><i class="fa fa-download"></i>&nbsp;&nbsp;File mẫu</a>
                            {{-- <a href="javascript::" class="btn btn-warning" data-toggle="modal" data-target="#file-add-customer"><i class="fa fa-upload"></i>&nbsp;&nbsp;Tải file</a> --}}
                        </div>
                        @if ($campaign->file_user_cus)
                            <div class="form-group">
                                <label class="control-label">File đính kèm</label>
                                <ul>
                                    <li><a href='{{ url("/admin/campaigns/download/{$campaign->file_user_cus['uuid']}") }}'>{{ $campaign->file_user_cus['name'] }}</a></li>
                                </ul>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
<div class="modal-footer" style="display: flex;justify-content: center;">
    <button class="btn btn-primary btn-confirm-assign-ctv" form="form-edit-add-campaigns" style="margin-right: 5px;"><i class="fa fa-save"></i>&nbsp;&nbsp;Thêm mới</button>
    <button type="button" class="btn btn-danger pull-right" data-dismiss="modal"><i class="fa fa-close"></i>&nbsp;&nbsp;Hủy</button>
</div>

<script src="/adminLTE/plugins/moment/moment.min.js"></script>

<!-- TinyMCE -->
<script src="/adminLTE/plugins/tinymce/tinymce.min.js"></script>
<script src="/adminLTE/plugins/tinymce/config.js"></script>

<script>
    $(function() {
        // Chọn dự án
        get_data_select2({
            object: '#select-bo-project',
            url: '{{ route("admin.campaigns.project") }}',
            data_id: 'id',
            data_text: 'title',
            title_default: 'Chọn dự án'
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
    });
</script>
