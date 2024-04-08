<div id="toa-nha" class="tab-pane fade in active">
    <div class="col-sm-12">
        <div class="box box-primary">
            <div class="box-body ">
                <form id="form-search-place" action="" method="post">
                    {{ csrf_field() }}
                    <div class="col-sm-1">
                        <button type="button" data-toggle="dropdown" class="btn btn-primary dropdown-toggle pull-left" style="margin-right: 10px;">Tác vụ&nbsp;<span class="caret"></span></button>
                        <ul class="dropdown-menu">
                            <li>
                                <a href="javascript:" type="button" class="btn-action" data-target="#form-apartmennt-list" data-method="delete">
                                    <i class="fa fa-trash text-danger"></i>&nbsp; Xóa
                                </a>
                            </li>
                        </ul>
                    </div>
                    <div class="col-sm-11">
                        <div id="search-advance" class="search-advance">
                            <div class="row ">
                                <div class="form-group space-5" style="width: calc(100% - 55px);float: left;">
                                    <div class="col-sm-3">
                                        <input type="text" class="form-control" name="name" placeholder="Nhập tên tòa nhà" value="{{ !empty($data_search['name']) ? $data_search['name'] : '' }}">
                                    </div>
                                    <div class="col-sm-3">
                                        <?php $name = '';$ap_role = $data_search['status'] ?? '';
                                        ?>
                                        <select name="status" id="select-ap-role" class="form-control">
                                            <option value="false">Chọn Trạng thái</option>
                                            <option value="0" @if($ap_role == '0') selected @endif>Đóng</option>
                                            <option value="1" @if($ap_role == '1') selected @endif>Mở</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="input-group-btn">
                                    <button type="submit" title="Tìm kiếm" class="btn btn-info" form="form-search-place"><i class="fa fa-search"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form><!-- END #form-search-advance -->
                <div class="clearfix"></div>
                    <form id="form-apartmennt-list" action="{{ route('admin.buildingplace.action') }}" method="post">
                        @csrf
                        <input type="hidden" name="method" value="" />
                        <input type="hidden" name="status" value="" />

                        <div class="table-responsive">
                            <table class="table table-hover table-striped table-bordered">
                                <thead class="bg-primary">
                                <tr>
                                    <th width="1%"><input type="checkbox" class="iCheck checkAll" data-target=".checkSingle" /></th>
                                    <th width="1%">Stt</th>
                                    <th width="120">Tên Tòa nhà</th>
                                    <th width="30">Mã</th>
                                    <th width="30">Tình trạng</th>
                                    <th width="1%">Thao tác</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach ($buildingPlaces as $key => $item)

                                    <tr>
                                        <td><input type="checkbox" name="ids[]" value="{{ $item->id }}" class="iCheck checkSingle" /></td>
                                        <td>{{ $key + 1 }}</td>
                                        <td>{{ @$item->name }}</td>
                                        <td>
                                            {{ @$item->code }}
                                        </td>
                                        <td>
                                            @if($item->status == 0)
                                                Đóng
                                            @elseif($item->status == 1)
                                                Mở
                                            @endif
                                        </td>
                                        <td colspan="" rowspan="" headers="">
                                                <a href="{{route('admin.buildingplace.edit',['id'=>$item->id])}}" class="btn btn-success" title="sửa"><i class="fa fa-edit"></i></a>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="row mbm">
                            <div class="col-sm-3">
                                <span class="record-total">Hiển thị: {{$count_display}} / {{ $buildingPlaces->total() }} Kết quả</span>
                            </div>
                            <div class="col-sm-6 text-center">
                                <div class="pagination-panel">
                                    {{ $buildingPlaces->appends(Request::all())->onEachSide(1)->links() }}
                                </div>
                            </div>
                            <div class="col-sm-3 text-right">
                                <span class="form-inline">
                                    Hiển thị
                                    <select name="per_page" class="form-control" data-target="#form-users">
                                        @php $list = [5,10, 20, 50, 100, 200]; @endphp
                                        @foreach ($list as $num)
                                            <option value="{{ $num }}" {{ $num == $per_page ? 'selected' : '' }}>{{ $num }}</option>
                                        @endforeach
                                    </select>
                                </span>
                            </div>
                        </div>
                    </form><!-- END #form-users -->
            </div>
        </div>
    </div>
</div>
