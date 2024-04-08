<div class="tab-pane {{ is_null(session('tab')) ? ($tab == 'asset' ? 'active' : '') : (session('tab') == 'asset' ? 'active' : '') }}" id="asset">
    <div class="row form-group">
        <div class="col-sm-12 pull-right">
            <a href="{{ route('admin.assets.create') }}" class="btn btn-success"><i class="fa fa-edit"></i>
                Thêm tài sản</a>
        </div>
    </div>
    <form id="form-search-advance" action="{{ route('admin.assets.index') }}" method="get">
        <div id="search-advance" class="search-advance">
            <div class="row form-group space-5">
                <div class="col-sm-4">
                    <input type="text" name="keyword" class="form-control"
                           placeholder="Nhập nội dung tìm kiếm" value="{{ @$filter['keyword'] }}">
                </div>
                <div class="col-sm-2">
                    <select name="bdc_assets_type_id" class="form-control">
                        <option value="" selected>Loại tài sản</option>
                        @foreach($types as $type)
                            <option value="{{ $type->id }}"
                                    @if(@$filter['bdc_assets_type_id'] ==  $type->id)
                                    selected
                                    @endif>{{ $type->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-sm-2">
                    <select name="bdc_period_id" class="form-control">
                        <option value="" selected>Kì bảo hành</option>
                        @foreach($periods as $period)
                            <option value="{{ $period->id }}"
                                    @if(@$filter['bdc_period_id'] ==  $period->id) selected @endif>{{ $period->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-sm-2">
                    <button class="btn btn-info search-asset"><i class="fa fa-search"></i></button>
                </div>
            </div>
        </div>
    </form><!-- END #form-search-advance -->
    <div class="row form-group">
        <div class="col-sm-8">
                        <span class="btn-group">
                            <a data-action="{{ route('admin.assets.deleteMulti') }}" class="btn btn-danger" id="delete-multi-assets"><i class="fa fa-trash-o"></i> Xóa mục đã chọn</a>
                        </span>
        </div>
    </div>
    <form id="form-permission" action="{{ route('admin.assets.action') }}" method="post">
        @csrf
        <input type="hidden" name="tab" value="asset">
        <div class="table-responsive">
            <table class="table table-hover table-striped table-bordered">
                <thead class="bg-primary">
                <tr>
                    <th><input type="checkbox" class="iCheck checkAll" data-target=".checkSingle" /></th>
                    <th>STT</th>
                    <th>Tên TS-CCDC</th>
                    <th width="8%">Loại</th>
                    <th>Giá mua</th>
                    <th>Số lượng</th>
                    <th>Ngày mua</th>
                    <th width="8%">Kì BT</th>
                    <th>Ngày bắt đầu BT</th>
                    <th>Thời gian sử dụng</th>
                    <th>Nơi đặt</th>
                    <th width="10%">Thao tác</th>
                </tr>
                </thead>
                <tbody>
                @if($assets->count() > 0)
                    @foreach($assets as $key => $asset)
                        <tr>
                            <td><input type="checkbox" name="ids[]" value="{{ $asset->id }}" class="iCheck checkSingle" /></td>
                            <td>{{ @($key + 1) + ($assets->currentPage() - 1) * $assets->perPage() }}</td>
                            <td>{{ @$asset->name }}</td>
                            <td>{{ @$asset->type->name }}</td>
                            <td>{{  number_format(@$asset->price) }}</td>
                            <td>{{ @$asset->quantity }}</td>
                            <td>{{ date("d/m/Y", strtotime($asset->buying_date)) }}</td>
                            <td>{{ @$asset->period->name }}</td>
                            <td>{{ date("d/m/Y", strtotime($asset->maintainance_date)) }}</td>
                            <td>{{ @$asset->using_peroid }} tháng</td>
                            <td>{{ @$asset->place }}</td>
                            <td>
                                <a href="{{ route('admin.assets.edit', $asset->id) }}"
                                   class="btn btn-xs btn-primary" title="Sửa tài sản"><i
                                            class="fa fa-pencil"></i></a>
                                <a class="btn btn-xs btn-danger delete-asset"
                                   data-url="{{ route('admin.assets.destroy', $asset->id) }}"
                                   title="Xóa tài sản"><i class="fa fa-trash"></i></a>
                                <a href="{{ route('admin.assets.show', $asset->id) }}"
                                   class="btn btn-xs btn-success" title="Chi tiết tài sản"><i
                                            class="fa fa-info"></i></a>
                            </td>
                        </tr>
                    @endforeach
                @else
                    <tr><td colspan="12" class="text-center">Không có kết quả tìm kiếm</td></tr>
                @endif
                </tbody>
            </table>
        </div>
        <div class="row mbm">
            <div class="col-sm-3">
                <span class="record-total">Hiển thị {{ $assets->count() }} / {{ $assets->total() }} kết quả</span>
            </div>
            <div class="col-sm-6 text-center">
                <div class="pagination-panel">
                    {{ $assets->appends(request()->input())->links() }}
                </div>
            </div>
            <div class="col-sm-3 text-right">
                <span class="form-inline">
                    Hiển thị
                    <select name="per_page" class="form-control" data-target="#form-permission">
                        @php $list = [10, 20, 50, 100, 200]; @endphp
                        @foreach ($list as $num)
                            <option value="{{ $num }}" {{ $num == $per_page ? 'selected' : '' }}>{{ $num }}</option>
                        @endforeach
                    </select>
                </span>
            </div>
        </div>
    </form><!-- END #form-users -->
</div>