<div class="tab-pane {{ is_null(session('tab')) ? ($tab == 'maintenance_asset' ? 'active' : '') : (session('tab') == 'maintenance_asset' ? 'active' : '') }}"
    id="maintenance_asset">
    <div class="box-header with-border">
        <h3 class="box-title">Lịch bảo trì</h3>
    </div>
    <form id="form-search-maintenance" action="{{ route('admin.assets.index') }}" method="get">
        <div id="search-advance" class="search-advance">
            <div class="row form-group space-5">
                <div class="col-sm-4">
                    <input type="text" name="keyword_maintain" class="form-control" placeholder="Nhập nội dung tìm kiếm"
                        value="{{ @$filter['keyword_maintain'] }}">
                </div>
                <div class="col-sm-3">
                    <div class="input-group date">
                        <div class="input-group-addon">
                            <i class="fa fa-calendar"></i>
                        </div>
                        <input type="text" class="form-control pull-right date_picker" name="maintenance_time"
                            placeholder="Ngày bảo trì" value="{{ @$filter['maintenance_time'] }}">
                    </div>
                </div>
                <div class="col-sm-2">
                    <select name="status" class="form-control">
                        <option value="" selected>Trạng thái</option>
                        <option value="0" @if(@$filter['status']==0) selected @endif>Chưa hoàn thành</option>
                        <option value="1" @if(@$filter['status']==1) selected @endif>Đã hoàn thành</option>
                        <option value="2" @if(@$filter['status']==2) selected @endif>Đã hủy</option>
                    </select>
                </div>
                <div class="col-sm-2">
                    <button class="btn btn-info search-maintenance"><i class="fa fa-search"></i></button>
                </div>
            </div>
        </div>
    </form>
    <form id="form-maintanence" action="{{ route('admin.assets.action') }}" method="post">
        <input type="hidden" name="tab" value="maintenance_asset">
        @csrf
        <div class="table-responsive">
            <table class="table table-hover table-striped table-bordered" id="asset_table">
                <thead class="bg-primary">
                    <tr>
                        <th>STT</th>
                        <th>Tiêu đề</th>
                        <th>Tài sản bảo trì</th>
                        <th>Ngày bảo trì</th>
                        <th>Trạng thái</th>
                        <th>Nhật kí công việc</th>
                        <th>Người đánh dấu bảo trì</th>
                        <th width="10%">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @if($maintenance_assets->count() > 0)
                    @foreach($maintenance_assets as $key => $maintenance)
                    <tr>
                        <td>{{ @($key + 1) + ($maintenance_assets->currentPage() - 1) * $maintenance_assets->perPage() }}
                        </td>
                        <td>{{ @$maintenance->title }}</td>
                        <td><a href="{{ route('admin.assets.show', $maintenance->asset->id) }}"
                                target="_blank">{{ @$maintenance->asset->name }}</a></td>
                        <td>{{ @$maintenance->maintenance_time }}</td>
                        <td>
                            @if($maintenance->status == 2)
                            Đã hủy
                            @else
                                @if(strtotime(date('d-m-Y')) < strtotime($maintenance->maintenance_time))
                                Chưa đến ngày bảo trì
                                @elseif(strtotime(date('d-m-Y')) == strtotime($maintenance->maintenance_time))
                                {{ $maintenance->status == 0 ? 'Chưa hoàn thành' : 'Đã hoàn thành'  }}
                                @else
                                    {{ $maintenance->status == 0 ? 'Đã quá hạn '.((strtotime(date('d-m-Y')) - strtotime($maintenance->maintenance_time))/86400) . ' ngày' : 'Đã hoàn thành'  }}
                                @endif
                            @endif
                        </td>
                        <td>
                            @if($maintenance->workdiary->count())
                                @foreach($maintenance->workdiary as $workd)
                                    <a href="{{route('admin.work-diary.report-work',$workd->id)}}">{{$workd->title}}</a></br>
                                @endforeach
                            @else
                            Chưa có công việc liên quan
                            @endif
                        </td>
                        <td>{{ @$maintenance->user->BDCprofile->display_name }}</td>
                        <td>
                            @if($maintenance->status != 2)
                                <a href=""
                                   class="btn btn-xs btn-primary" title="Tạo công việc"><i
                                            class="fa fa-pencil"></i></a>
                            @endif
                            @if(strtotime(date('d-m-Y')) < strtotime($maintenance->maintenance_time) && $maintenance->status == 0)
                                <a href="{{ route('admin.assets.cancel_check', $maintenance->id) }}"
                                   class="btn btn-xs btn-danger check_done" title="Hủy bảo tri"><i
                                            class="fa fa-trash"></i></a>
                            @endif
                            @if(strtotime(date('d-m-Y')) >= strtotime($maintenance->maintenance_time) && $maintenance->status == 0)
                                <a href="{{ route('admin.assets.maintain_check', $maintenance->id) }}"
                                   class="btn btn-xs btn-warning check_done" title="Đánh dấu hoàn thành"><i
                                            class="fa fa-check"></i></a>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                    @else
                    <tr>
                        <td colspan="12" class="text-center">Không có kết quả tìm kiếm</td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>
        <div class="row mbm">
            <div class="col-sm-3">
                <span class="record-total">Hiển thị {{ $maintenance_assets->count() }} /
                    {{ $maintenance_assets->total() }} kết quả</span>
            </div>
            <div class="col-sm-6 text-center">
                <div class="pagination-panel">
                    {{ $maintenance_assets->appends(request()->except(['page', 'page_m']))->fragment('maintenance_asset')->links() }}
                </div>
            </div>
            <div class="col-sm-3 text-right">
                <span class="form-inline">
                    Hiển thị
                    <select name="per_page_maintenance" class="form-control" data-target="#form-maintanence">
                        @php $list = [10, 20, 50, 100, 200]; @endphp
                        @foreach ($list as $num)
                        <option value="{{ $num }}" {{ $num == $per_page_maintenance ? 'selected' : '' }}>{{ $num }}
                        </option>
                        @endforeach
                    </select>
                </span>
            </div>
        </div>
    </form><!-- END #form-users -->
</div>