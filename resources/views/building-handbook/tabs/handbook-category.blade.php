<div id="danh_muc" class="tab-pane @if(request()->exists('handbook_categories_keyword')) active @else fade @endif">
    <div class="box-header with-border">
        <div class="row form-group">
            <div class="col-sm-8 col-xs-12 ">
                <button class="btn btn-info" data-toggle="modal" data-target="#createHandbookCategory"><i
                        class="fa fa-plus"></i>&nbsp;&nbsp;Thêm mới danh mục</button>
            </div>
        </div>
        <form action="{{ route('admin.building-handbook.index') }}" method="get" id="form-search">
            <div class="clearfix"></div>
            <div id="search-advance" class="search-advance">
                <div class="row form-group space-5">
                    <div class="col-sm-4">
                        <input type="text" name="handbook_categories_keyword" class="form-control"
                            placeholder="Nhập nội dung tìm kiếm"
                            value="{{ @$filter_handbook_category['handbook_categories_keyword'] }}">
                    </div>
                    <div class="col-sm-2">
                        <select name="bdc_handbook_type_id" class="form-control">
                            <option value="" selected>Phân loại</option>
                            @foreach($types as $type)
                            <option value="{{ $type->id }}"
                                @if(@$filter_handbook_category['bdc_handbook_type_id']==$type->id)
                                selected
                                @endif>{{ $type->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-sm-2">
                        <button class="btn btn-info search-asset"><i class="fa fa-search"></i></button>
                    </div>
                </div>
            </div>
        </form>
        <div class="row form-group">
            <div class="col-sm-8">
                <span class="btn-group">
                    <a data-action="{{ route('admin.building-handbook.category.del_multi') }}" class="btn btn-danger"
                        id="delete-multi-handbook-categories"><i class="fa fa-trash-o"></i> Xóa mục đã chọn</a>
                </span>
            </div>
        </div>
    </div>
    <!-- /.box-header -->
    <form action="{{ route('admin.building-handbook.action') }}" method="post" id="form-handbook-category">
        {{ csrf_field() }}
        @method('post')
        <div class="box-body">
            <div class="table-responsive">
                <table class="table table-striped table-bordered table-hover">
                    <thead>
                        <tr class="bg-primary">
                            <th width='20px'>
                                <input class="iCheck checkAll" type="checkbox" data-target=".checkSingle" />
                            </th>
                            <th width='20px'>STT</th>
                            <th width='30%'>Tiêu đề</th>
                            <th width='20%'>Danh mục cha</th>
                            <th width='20%'>Phân loại</th>
                            <th width='10%'>Điện thoại</th>
                            <th width='10%'>Trạng thái</th>
                            <th width='10%'>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>

                        @if(!$handbook_categories->isEmpty())
                        @foreach($handbook_categories as $key => $bdh_category)
                        <tr>
                            <td><input type="checkbox" class="iCheck checkSingle" value="{{@$bdh_category->id}}"
                                    name="id_categories[]" /></td>
                            <td>{{ @($key + 1) + ($handbooks->currentPage() - 1) * $handbooks->perPage() }}</td>
                            <td>
                                <a data-id="{{$bdh_category->id}}" class="title"> {{ @$bdh_category->name }} </a>
                            </td>
                            @if($bdh_category->parent_id != 0)
                            <td>
                                {{@$bdh_category->parent->name}}
                            </td>
                            @else
                            <td>
                                Không
                            </td>
                            @endif
                            <td>
                                {{@$bdh_category->handbook_type->name}}
                            </td>
                            <td>
                                {{$bdh_category->phone}}
                            </td>
                            <td>
                                @if($bdh_category->status == 1)
                                <small class="btn btn-success btn-xs status" style="border-radius:5px"
                                    data-id="{{ $bdh_category->id }}" data-status={{ $bdh_category->status }}>
                                    Active
                                </small>
                                @else
                                <small class="btn btn-danger btn-xs status" style="border-radius:5px"
                                    data-id="{{ $bdh_category->id }}" data-status={{ $bdh_category->status }}>
                                    Inactive
                                </small>
                                @endif
                            </td>
                            {{-- <td>
                            <a href="{{ route('admin.building-handbook.index', ['id' => $bdh_category->id]) }}"> Danh
                            sách </a>
                            </td> --}}
                            <td>
                                <a id="{{$bdh_category->id}}" type="button" class="btn btn-sm btn-info edit"
                                    title="Sửa"><i class="fa fa-edit"></i></a>

                                <a title="Xóa" href="javascript:;"
                                    data-url="{{ route('admin.building-handbook.category.delete') }}"
                                    data-id="{{ $bdh_category->id }}" class="btn btn-sm btn-delete btn-danger"><i
                                        class="fa fa-trash"></i></a>
                            </td>
                        </tr>
                        @endforeach
                        @else
                        <tr>
                            <td colspan="7" class="text-center">
                                <p>Chưa có Kiểu cẩm nang nào</p>
                            </td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
        <!-- /.box-body -->
        <div class="box-footer clearfix">
            <div class="row">
                <div class="col-sm-3">
                    <span class="record-total">Tổng: {{ $handbook_categories->count() }} /
                        {{ $handbook_categories->total() }} bản ghi</span>
                </div>
                <div class="col-sm-6 text-center">
                    <div class="pagination-panel">
                        {{ $handbook_categories->appends(Request::all())->onEachSide(1)->links() }}
                    </div>
                </div>
                <div class="col-sm-3 text-right">
                    <span class="form-inline">
                        Hiển thị
                        <select name="per_page_handbook_category" class="form-control" data-target="#form-permission">
                            @php $list = [10, 20, 50, 100, 200]; @endphp
                            @foreach ($list as $num)
                            <option value="{{ $num }}" {{ $num == $per_page_handbook ? 'selected' : '' }}>{{ $num }}
                            </option>
                            @endforeach
                        </select>
                    </span>
                </div>
            </div>
        </div>
    </form>
</div>
@include('building-handbook.modal.handbook_category')