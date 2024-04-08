<div id="phan_loai" class="tab-pane @if(request()->exists('keyword')) active @else fade @endif">
   <div class="box box-primary">
        <div class="box-header with-border">
            <div class="row form-group">
                <div class="col-sm-8 col-xs-12 ">
                    <a href="{{ route('admin.building-handbook.type.create') }}" type="buttom" class="btn btn-info"><i class="fa fa-plus"></i>&nbsp;&nbsp;Thêm mới</a>
                </div>
            </div>
            <form action="{{ route('admin.building-handbook.type.index') }}" method="get" id="form-search">
                <div class="clearfix"></div>
                <div id="search-advance" class="search-advance">
                    <div class="row form-group space-5">
                        <div class="col-sm-4">
                            <input type="text" name="keyword" class="form-control"
                                    placeholder="Nhập nội dung tìm kiếm" value="{{ @$filter['keyword'] }}">
                        </div>
                        <div class="col-sm-2">
                            <button class="btn btn-warning btn-block">Tìm kiếm</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <!-- /.box-header -->
        <div class="box-body">
            <div class="table-responsive">
                <form action="" method="post" id="form-partner-action">
                    {{ csrf_field() }}
                    @method('post')
                    <input type="hidden" name="method" value="" />
                    <input type="hidden" name="status" value="" />

                    <table class="table table-striped table-bordered table-hover">
                        <thead>
                            <tr class="bg-primary">
                                <th width='20px'>
                                    <input class="iCheck checkAll" type="checkbox" data-target=".checkSingle" />
                                </th>
                                <th width='20px'>#</th>
                                <th width='90%'>Tên</th>
                                {{-- <th width='30%'>Xem danh sách cẩm nang</th> --}}
                                <th width='10%'>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>

                            @if(!$handbook_types->isEmpty())
                                @foreach($handbook_types as $bdh_type)
                                <tr>
                                <td><input type="checkbox" class="iCheck checkSingle" value="{{$bdh_type->id}}" name="ids[]" /></td>
                                <td>{{ $bdh_type->id }}</td>
                                <td>
                                    <a href="{{ route('admin.building-handbook.type.edit', ['id' => $bdh_type->id]) }}"> {{ $bdh_type->name }} </a>
                                </td>
                                {{-- <td>
                                    <a href="{{ route('admin.building-handbook.index', ['id' => $bdh_type->id, 'valueOf' => 'type']) }}"> Danh sách </a>
                                </td> --}}
                                <td>
                                    <a href="{{ route('admin.building-handbook.type.edit', ['id' => $bdh_type->id]) }}" type="button" class="btn btn-sm btn-info" title="Sửa"><i class="fa fa-edit"></i></a>

                                    <a title="Xóa" href="javascript:;" data-url="{{ route('admin.building-handbook.type.delete') }}" data-id="{{ $bdh_type->id }}" class="btn btn-sm btn-delete btn-danger"><i class="fa fa-trash"></i></a>
                                </td>
                                </tr>
                                @endforeach
                            @else
                                <tr><td colspan="6" class="text-center"><p>Chưa có Loại cẩm nang nào</p></td></tr>
                            @endif
                        </tbody>
                    </table>
                </form>
            </div>
        </div>
        <!-- /.box-body -->
        <div class="box-footer clearfix">
                <div class="row">
                    <div class="col-sm-3">
                        <span class="record-total">Tổng: {{ $handbook_types->total() }} bản ghi</span>
                    </div>
                    <div class="col-sm-6 text-center">
                        <div class="pagination-panel">
                            {{ $handbook_types->appends(Request::all())->onEachSide(1)->links() }}
                        </div>
                    </div>
                </div>
            </div>
    </div>
</div>